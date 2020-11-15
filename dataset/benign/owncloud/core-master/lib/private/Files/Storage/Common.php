<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author hkjolhede <hkjolhede@gmail.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Martin Mattel <martin.mattel@diemattels.at>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Sam Tuke <mail@samtuke.com>
 * @author scambra <sergio@entrecables.com>
 * @author Stefan Weil <sw@weilnetz.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\Files\Storage;

use OC\Files\Cache\Cache;
use OC\Files\Cache\Propagator;
use OC\Files\Cache\Scanner;
use OC\Files\Cache\Updater;
use OC\Files\Filesystem;
use OC\Files\Cache\Watcher;
use OC\Lock\Persistent\Lock;
use OC\Lock\Persistent\LockManager;
use OCP\Constants;
use OCP\Files\FileInfo;
use OCP\Files\FileNameTooLongException;
use OCP\Files\InvalidCharacterInPathException;
use OCP\Files\InvalidPathException;
use OCP\Files\ReservedWordException;
use OCP\Files\Storage\ILockingStorage;
use OCP\Files\Storage\IPersistentLockingStorage;
use OCP\Files\Storage\IVersionedStorage;
use OCP\Lock\ILockingProvider;
use OCP\Lock\Persistent\ILock;

/**
 * Storage backend class for providing common filesystem operation methods
 * which are not storage-backend specific.
 *
 * \OC\Files\Storage\Common is never used directly; it is extended by all other
 * storage backends, where its methods may be overridden, and additional
 * (backend-specific) methods are defined.
 *
 * Some \OC\Files\Storage\Common methods call functions which are first defined
 * in classes which extend it, e.g. $this->stat() .
 */
abstract class Common implements Storage, ILockingStorage, IVersionedStorage, IPersistentLockingStorage {
	use LocalTempFileTrait;

	protected $cache;
	protected $scanner;
	protected $watcher;
	protected $propagator;
	protected $storageCache;
	protected $updater;

	protected $mountOptions = [];
	protected $owner = null;

	public function __construct($parameters) {
	}

	/**
	 * Remove a file or folder
	 *
	 * @param string $path
	 * @return bool
	 */
	protected function remove($path) {
		if ($this->is_dir($path)) {
			return $this->rmdir($path);
		} elseif ($this->is_file($path)) {
			return $this->unlink($path);
		} else {
			return false;
		}
	}

	public function is_dir($path) {
		return $this->filetype($path) === 'dir';
	}

	public function is_file($path) {
		return $this->filetype($path) === 'file';
	}

	public function filesize($path) {
		if ($this->is_dir($path)) {
			return 0; //by definition
		} else {
			$stat = $this->stat($path);
			if (isset($stat['size'])) {
				return $stat['size'];
			} else {
				return 0;
			}
		}
	}

	public function isReadable($path) {
		// at least check whether it exists
		// subclasses might want to implement this more thoroughly
		return $this->file_exists($path);
	}

	public function isUpdatable($path) {
		// at least check whether it exists
		// subclasses might want to implement this more thoroughly
		// a non-existing file/folder isn't updatable
		return $this->file_exists($path);
	}

	public function isCreatable($path) {
		if ($this->is_dir($path) && $this->isUpdatable($path)) {
			return true;
		}
		return false;
	}

	public function isDeletable($path) {
		if ($path === '' || $path === '/') {
			return false;
		}
		$parent = \dirname($path);
		return $this->isUpdatable($parent) && $this->isUpdatable($path);
	}

	public function isSharable($path) {
		return $this->isReadable($path);
	}

	public function getPermissions($path) {
		$permissions = 0;
		if ($this->isCreatable($path)) {
			$permissions |= Constants::PERMISSION_CREATE;
		}
		if ($this->isReadable($path)) {
			$permissions |= Constants::PERMISSION_READ;
		}
		if ($this->isUpdatable($path)) {
			$permissions |= Constants::PERMISSION_UPDATE;
		}
		if ($this->isDeletable($path)) {
			$permissions |= Constants::PERMISSION_DELETE;
		}
		if ($this->isSharable($path)) {
			$permissions |= Constants::PERMISSION_SHARE;
		}
		return $permissions;
	}

	public function filemtime($path) {
		$stat = $this->stat($path);
		if (isset($stat['mtime'])) {
			return $stat['mtime'];
		} else {
			return 0;
		}
	}

	public function file_get_contents($path) {
		$handle = $this->fopen($path, "r");
		if (!$handle) {
			return false;
		}
		$data = \stream_get_contents($handle);
		\fclose($handle);
		return $data;
	}

	public function file_put_contents($path, $data) {
		$handle = $this->fopen($path, "w");
		$this->removeCachedFile($path);
		$count = \fwrite($handle, $data);
		\fclose($handle);
		return $count;
	}

	public function rename($path1, $path2) {
		$this->remove($path2);

		$this->removeCachedFile($path1);
		return $this->copy($path1, $path2) and $this->remove($path1);
	}

	public function copy($path1, $path2) {
		if ($this->is_dir($path1)) {
			$this->remove($path2);
			$dir = $this->opendir($path1);
			$this->mkdir($path2);
			while ($file = \readdir($dir)) {
				if (!Filesystem::isIgnoredDir($file) && !Filesystem::isForbiddenFileOrDir($file)) {
					if (!$this->copy($path1 . '/' . $file, $path2 . '/' . $file)) {
						return false;
					}
				}
			}
			\closedir($dir);
			return true;
		} else {
			$source = $this->fopen($path1, 'r');
			$target = $this->fopen($path2, 'w');
			list(, $result) = \OC_Helper::streamCopy($source, $target);
			$this->removeCachedFile($path2);
			return $result;
		}
	}

	public function getMimeType($path) {
		if ($this->is_dir($path)) {
			return 'httpd/unix-directory';
		} elseif ($this->file_exists($path)) {
			return \OC::$server->getMimeTypeDetector()->detectPath($path);
		} else {
			return false;
		}
	}

	public function hash($type, $path, $raw = false) {
		$fh = $this->fopen($path, 'rb');
		$ctx = \hash_init($type);
		\hash_update_stream($ctx, $fh);
		\fclose($fh);
		return \hash_final($ctx, $raw);
	}

	public function search($query) {
		return $this->searchInDir($query);
	}

	public function getLocalFile($path) {
		return $this->getCachedFile($path);
	}

	/**
	 * @param string $path
	 * @param string $target
	 */
	private function addLocalFolder($path, $target) {
		$dh = $this->opendir($path);
		if (\is_resource($dh)) {
			while (($file = \readdir($dh)) !== false) {
				if (!Filesystem::isIgnoredDir($file)) {
					if ($this->is_dir($path . '/' . $file)) {
						\mkdir($target . '/' . $file);
						$this->addLocalFolder($path . '/' . $file, $target . '/' . $file);
					} else {
						$tmp = $this->toTmpFile($path . '/' . $file);
						\rename($tmp, $target . '/' . $file);
					}
				}
			}
		}
	}

	/**
	 * @param string $query
	 * @param string $dir
	 * @return array
	 */
	protected function searchInDir($query, $dir = '') {
		$files = [];
		$dh = $this->opendir($dir);
		if (\is_resource($dh)) {
			while (($item = \readdir($dh)) !== false) {
				if (Filesystem::isIgnoredDir($item)) {
					continue;
				}
				if (\strstr(\strtolower($item), \strtolower($query)) !== false) {
					$files[] = $dir . '/' . $item;
				}
				if ($this->is_dir($dir . '/' . $item)) {
					$files = \array_merge($files, $this->searchInDir($query, $dir . '/' . $item));
				}
			}
		}
		\closedir($dh);
		return $files;
	}

	/**
	 * check if a file or folder has been updated since $time
	 *
	 * The method is only used to check if the cache needs to be updated. Storage backends that don't support checking
	 * the mtime should always return false here. As a result storage implementations that always return false expect
	 * exclusive access to the backend and will not pick up files that have been added in a way that circumvents
	 * ownClouds filesystem.
	 *
	 * @param string $path
	 * @param int $time
	 * @return bool
	 */
	public function hasUpdated($path, $time) {
		return $this->filemtime($path) > $time;
	}

	public function getCache($path = '', $storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		if (!isset($storage->cache)) {
			$storage->cache = new Cache($storage);
		}
		return $storage->cache;
	}

	public function getScanner($path = '', $storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		if (!isset($storage->scanner)) {
			$storage->scanner = new Scanner($storage);
		}
		return $storage->scanner;
	}

	public function getWatcher($path = '', $storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		if (!isset($this->watcher)) {
			$this->watcher = new Watcher($storage);
			$globalPolicy = \OC::$server->getConfig()->getSystemValue('filesystem_check_changes', Watcher::CHECK_NEVER);
			$this->watcher->setPolicy((int)$this->getMountOption('filesystem_check_changes', $globalPolicy));
		}
		return $this->watcher;
	}

	/**
	 * get a propagator instance for the cache
	 *
	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the watcher
	 * @return \OC\Files\Cache\Propagator
	 */
	public function getPropagator($storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		if (!isset($storage->propagator)) {
			$storage->propagator = new Propagator($storage, \OC::$server->getDatabaseConnection());
		}
		return $storage->propagator;
	}

	public function getUpdater($storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		if (!isset($storage->updater)) {
			$storage->updater = new Updater($storage);
		}
		return $storage->updater;
	}

	public function getStorageCache($storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		if (!isset($this->storageCache)) {
			$this->storageCache = new \OC\Files\Cache\Storage($storage);
		}
		return $this->storageCache;
	}

	/**
	 * get the owner of a path
	 *
	 * @param string $path The path to get the owner
	 * @return string|false uid or false
	 */
	public function getOwner($path) {
		if ($this->owner === null) {
			$this->owner = \OC_User::getUser();
		}

		return $this->owner;
	}

	/**
	 * get the ETag for a file or folder
	 *
	 * @param string $path
	 * @return string
	 */
	public function getETag($path) {
		return \uniqid();
	}

	/**
	 * clean a path, i.e. remove all redundant '.' and '..'
	 * making sure that it can't point to higher than '/'
	 *
	 * @param string $path The path to clean
	 * @return string cleaned path
	 */
	public function cleanPath($path) {
		if (\strlen($path) == 0 or $path[0] != '/') {
			$path = '/' . $path;
		}

		$output = [];
		foreach (\explode('/', $path) as $chunk) {
			if ($chunk == '..') {
				\array_pop($output);
			} elseif ($chunk == '.') {
			} else {
				$output[] = $chunk;
			}
		}
		return \implode('/', $output);
	}

	/**
	 * Test a storage for availability
	 *
	 * @return bool
	 */
	public function test() {
		if ($this->stat('')) {
			return true;
		}
		return false;
	}

	/**
	 * get the free space in the storage
	 *
	 * @param string $path
	 * @return int|false
	 */
	public function free_space($path) {
		return FileInfo::SPACE_UNKNOWN;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isLocal() {
		// the common implementation returns a temporary file by
		// default, which is not local
		return false;
	}

	/**
	 * Check if the storage is an instance of $class or is a wrapper for a storage that is an instance of $class
	 *
	 * @param string $class
	 * @return bool
	 */
	public function instanceOfStorage($class) {
		if (\ltrim($class, '\\') === 'OC\Files\Storage\Shared') {
			// FIXME Temporary fix to keep existing checks working
			$class = '\OCA\Files_Sharing\SharedStorage';
		}
		return \is_a($this, $class);
	}

	/**
	 * A custom storage implementation can return an url for direct download of a give file.
	 *
	 * For now the returned array can hold the parameter url - in future more attributes might follow.
	 *
	 * @param string $path
	 * @return array|false
	 */
	public function getDirectDownload($path) {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function verifyPath($path, $fileName) {
		if (isset($fileName[255])) {
			throw new FileNameTooLongException();
		}

		$this->verifyPosixPath($fileName);
	}

	/**
	 * @param string $fileName
	 * @throws InvalidPathException
	 */
	protected function verifyPosixPath($fileName) {
		$fileName = \trim($fileName);
		$this->scanForInvalidCharacters($fileName, "\\/");
		$reservedNames = ['*'];
		if (\in_array($fileName, $reservedNames)) {
			throw new ReservedWordException();
		}
	}

	/**
	 * @param string $fileName
	 * @param string $invalidChars
	 * @throws InvalidPathException
	 */
	private function scanForInvalidCharacters($fileName, $invalidChars) {
		foreach (\str_split($invalidChars) as $char) {
			if (\strpos($fileName, $char) !== false) {
				throw new InvalidCharacterInPathException();
			}
		}

		$sanitizedFileName = \filter_var($fileName, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
		if ($sanitizedFileName !== $fileName) {
			throw new InvalidCharacterInPathException();
		}
	}

	/**
	 * @param array $options
	 */
	public function setMountOptions(array $options) {
		$this->mountOptions = $options;
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getMountOption($name, $default = null) {
		return isset($this->mountOptions[$name]) ? $this->mountOptions[$name] : $default;
	}

	/**
	 * @param \OCP\Files\Storage $sourceStorage
	 * @param string $sourceInternalPath
	 * @param string $targetInternalPath
	 * @param bool $preserveMtime
	 * @return bool
	 */
	public function copyFromStorage(\OCP\Files\Storage $sourceStorage, $sourceInternalPath, $targetInternalPath, $preserveMtime = false) {
		if ($sourceStorage === $this) {
			return $this->copy($sourceInternalPath, $targetInternalPath);
		}

		if ($sourceStorage->is_dir($sourceInternalPath)) {
			$dh = $sourceStorage->opendir($sourceInternalPath);
			$result = $this->mkdir($targetInternalPath);
			if (\is_resource($dh)) {
				while ($result and ($file = \readdir($dh)) !== false) {
					if (!Filesystem::isIgnoredDir($file) && !Filesystem::isForbiddenFileOrDir($file)) {
						$result &= $this->copyFromStorage($sourceStorage, $sourceInternalPath . '/' . $file, $targetInternalPath . '/' . $file);
					}
				}
			}
		} else {
			$source = $sourceStorage->fopen($sourceInternalPath, 'r');
			// TODO: call fopen in a way that we execute again all storage wrappers
			// to avoid that we bypass storage wrappers which perform important actions
			// for this operation. Same is true for all other operations which
			// are not the same as the original one.Once this is fixed we also
			// need to adjust the encryption wrapper.
			$target = $this->fopen($targetInternalPath, 'w');
			list(, $result) = \OC_Helper::streamCopy($source, $target);
			if ($result and $preserveMtime) {
				$this->touch($targetInternalPath, $sourceStorage->filemtime($sourceInternalPath));
			}
			\fclose($source);
			\fclose($target);

			if (!$result) {
				// delete partially written target file
				$this->unlink($targetInternalPath);
				// delete cache entry that was created by fopen
				$this->getCache()->remove($targetInternalPath);
			}
		}
		return (bool)$result;
	}

	/**
	 * @param \OCP\Files\Storage $sourceStorage
	 * @param string $sourceInternalPath
	 * @param string $targetInternalPath
	 * @return bool
	 */
	public function moveFromStorage(\OCP\Files\Storage $sourceStorage, $sourceInternalPath, $targetInternalPath) {
		if ($sourceStorage === $this) {
			return $this->rename($sourceInternalPath, $targetInternalPath);
		}

		if (!$sourceStorage->isDeletable($sourceInternalPath)) {
			return false;
		}

		$result = $this->copyFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath, true);
		if ($result) {
			if ($sourceStorage->is_dir($sourceInternalPath)) {
				$result &= $sourceStorage->rmdir($sourceInternalPath);
			} else {
				$result &= $sourceStorage->unlink($sourceInternalPath);
			}
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getMetaData($path) {
		$permissions = $this->getPermissions($path);
		if (!$permissions & Constants::PERMISSION_READ) {
			//can't read, nothing we can do
			return null;
		}

		$data = [];
		$data['mimetype'] = $this->getMimeType($path);
		$data['mtime'] = $this->filemtime($path);
		if ($data['mtime'] === false) {
			$data['mtime'] = \time();
		}
		if ($data['mimetype'] == 'httpd/unix-directory') {
			$data['size'] = -1; //unknown
		} else {
			$data['size'] = $this->filesize($path);
		}
		$data['etag'] = $this->getETag($path);
		$data['storage_mtime'] = $data['mtime'];
		$data['permissions'] = $permissions;

		return $data;
	}

	/**
	 * @param string $path
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @param \OCP\Lock\ILockingProvider $provider
	 * @throws \OCP\Lock\LockedException
	 */
	public function acquireLock($path, $type, ILockingProvider $provider) {
		$provider->acquireLock('files/' . \md5($this->getId() . '::' . \trim($path, '/')), $type);
	}

	/**
	 * @param string $path
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @param \OCP\Lock\ILockingProvider $provider
	 */
	public function releaseLock($path, $type, ILockingProvider $provider) {
		$provider->releaseLock('files/' . \md5($this->getId() . '::' . \trim($path, '/')), $type);
	}

	/**
	 * @param string $path
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @param \OCP\Lock\ILockingProvider $provider
	 * @throws \OCP\Lock\LockedException
	 */
	public function changeLock($path, $type, ILockingProvider $provider) {
		$provider->changeLock('files/' . \md5($this->getId() . '::' . \trim($path, '/')), $type);
	}

	/**
	 * @return array [ available, last_checked ]
	 */
	public function getAvailability() {
		return $this->getStorageCache()->getAvailability();
	}

	/**
	 * @param bool $isAvailable
	 */
	public function setAvailability($isAvailable) {
		$this->getStorageCache()->setAvailability($isAvailable);
	}
	public function getVersions($internalPath) {
		// KISS implementation
		if (!\OC_App::isEnabled('files_versions')) {
			return [];
		}
		list($uid, $filename) =  $this->convertInternalPathToGlobalPath($internalPath);

		return \array_map(function ($version) use ($internalPath) {
			$version['mimetype'] = $this->getMimeType($internalPath);
			return $version;
		}, \array_values(
			\OCA\Files_Versions\Storage::getVersions($uid, $filename)));
	}

	/**
	 * @param $internalPath
	 * @return array
	 */
	public function convertInternalPathToGlobalPath($internalPath) {
		$mounts = \OC::$server->getMountManager()->findByStorageId($this->getId());

		$selectedMount = \end($mounts);
		foreach ($mounts as $mount) {
			$o = \explode('/', $mount->getMountPoint());
			if ($o[1] === $this->owner) {
				$selectedMount = $mount;
				break;
			}
		}

		$o = \explode('/', $mount->getMountPoint());
		$p = $selectedMount->getMountPoint() . $internalPath;
		$p = \explode('/', \ltrim($p, '/'));
		\array_shift($p);
		\array_shift($p);
		$p = \implode('/', $p);
		return [$o[1], $p];
	}

	public function getVersion($internalPath, $versionId) {
		$versions = $this->getVersions($internalPath);
		$versions = \array_filter($versions, function ($version) use ($versionId) {
			return $version['version'] === $versionId;
		});
		return \array_shift($versions);
	}

	public function getContentOfVersion($internalPath, $versionId) {
		$v = $this->getVersion($internalPath, $versionId);
		return \OCA\Files_Versions\Storage::getContentOfVersion($v['owner'], $v['storage_location']);
	}

	public function restoreVersion($internalPath, $versionId) {
		// KISS implementation
		if (!\OC_App::isEnabled('files_versions')) {
			return false;
		}
		$v = $this->getVersion($internalPath, $versionId);
		return \OCA\Files_Versions\Storage::restoreVersion($v['owner'], $v['path'], $v['storage_location'], $versionId);
	}

	public function saveVersion($internalPath) {
		// returning false here will trigger the fallback implementation
		return false;
	}

	public function lockNodePersistent($internalPath, array $lockInfo) {
		/** @var LockManager $locksManager */
		$locksManager = \OC::$server->query(LockManager::class);
		$storageId = $this->getCache()->getNumericStorageId();
		$fileId = $this->getCache()->getId($internalPath);
		return $locksManager->lock($storageId, $internalPath, $fileId, $lockInfo);
	}

	public function unlockNodePersistent($internalPath, array $lockInfo) {
		/** @var LockManager $locksManager */
		$locksManager = \OC::$server->query(LockManager::class);
		$fileId = $this->getCache()->getId($internalPath);
		return $locksManager->unlock($fileId, $lockInfo['token']);
	}

	public function getLocks($internalPath, $returnChildLocks = false) {

		/** @var LockManager $locksManager */
		$locksManager = \OC::$server->query(LockManager::class);
		$storageId = $this->getCache()->getNumericStorageId();
		$locks = $locksManager->getLocks($storageId, $internalPath, $returnChildLocks);

		return \array_map(function (Lock $lock) {
			list($uid, $fileName) = $this->convertInternalPathToGlobalPath($lock->getPath());
			$lock->setDavUserId($uid);
			$lock->setAbsoluteDavPath($fileName);
			return $lock;
		}, $locks);
	}
}
