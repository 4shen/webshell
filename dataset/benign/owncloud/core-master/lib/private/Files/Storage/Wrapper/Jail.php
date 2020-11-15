<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

namespace OC\Files\Storage\Wrapper;

use OC\Files\Cache\Wrapper\CacheJail;
use OCP\Files\Storage\IVersionedStorage;
use OCP\Lock\ILockingProvider;

/**
 * Jail to a subdirectory of the wrapped storage
 *
 * This restricts access to a subfolder of the wrapped storage with the subfolder becoming the root folder new storage
 */
class Jail extends Wrapper /* implements IVersionedStorage */
{
	/**
	 * @var string
	 */
	protected $rootPath;

	/**
	 * @param array $arguments ['storage' => $storage, 'mask' => $root]
	 *
	 * $storage: The storage that will be wrapper
	 * $root: The folder in the wrapped storage that will become the root folder of the wrapped storage
	 */
	public function __construct($arguments) {
		parent::__construct($arguments);
		// null value is allowed for lazy init, but it must set at earliest
		// before the first file operation
		$this->rootPath = $arguments['root'];
	}

	public function getSourcePath($path) {
		if ($this->rootPath === null) {
			throw new \InvalidArgumentException('Jail rootPath is null');
		}
		if ($path === '') {
			return $this->rootPath;
		} else {
			return $this->rootPath . '/' . $path;
		}
	}

	public function getId() {
		return 'link:' . parent::getId() . ':' . $this->rootPath;
	}

	/**
	 * see http://php.net/manual/en/function.mkdir.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function mkdir($path) {
		return $this->getWrapperStorage()->mkdir($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.rmdir.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function rmdir($path) {
		return $this->getWrapperStorage()->rmdir($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.opendir.php
	 *
	 * @param string $path
	 * @return resource
	 */
	public function opendir($path) {
		return $this->getWrapperStorage()->opendir($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.is_dir.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function is_dir($path) {
		return $this->getWrapperStorage()->is_dir($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.is_file.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function is_file($path) {
		return $this->getWrapperStorage()->is_file($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.stat.php
	 * only the following keys are required in the result: size and mtime
	 *
	 * @param string $path
	 * @return array
	 */
	public function stat($path) {
		return $this->getWrapperStorage()->stat($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.filetype.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function filetype($path) {
		return $this->getWrapperStorage()->filetype($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.filesize.php
	 * The result for filesize when called on a folder is required to be 0
	 *
	 * @param string $path
	 * @return int
	 */
	public function filesize($path) {
		return $this->getWrapperStorage()->filesize($this->getSourcePath($path));
	}

	/**
	 * check if a file can be created in $path
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isCreatable($path) {
		return $this->getWrapperStorage()->isCreatable($this->getSourcePath($path));
	}

	/**
	 * check if a file can be read
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isReadable($path) {
		return $this->getWrapperStorage()->isReadable($this->getSourcePath($path));
	}

	/**
	 * check if a file can be written to
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isUpdatable($path) {
		return $this->getWrapperStorage()->isUpdatable($this->getSourcePath($path));
	}

	/**
	 * check if a file can be deleted
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isDeletable($path) {
		return $this->getWrapperStorage()->isDeletable($this->getSourcePath($path));
	}

	/**
	 * check if a file can be shared
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isSharable($path) {
		return $this->getWrapperStorage()->isSharable($this->getSourcePath($path));
	}

	/**
	 * get the full permissions of a path.
	 * Should return a combination of the PERMISSION_ constants defined in lib/public/constants.php
	 *
	 * @param string $path
	 * @return int
	 */
	public function getPermissions($path) {
		return $this->getWrapperStorage()->getPermissions($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.file_exists.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function file_exists($path) {
		return $this->getWrapperStorage()->file_exists($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.filemtime.php
	 *
	 * @param string $path
	 * @return int
	 */
	public function filemtime($path) {
		return $this->getWrapperStorage()->filemtime($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.file_get_contents.php
	 *
	 * @param string $path
	 * @return string
	 */
	public function file_get_contents($path) {
		return $this->getWrapperStorage()->file_get_contents($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.file_put_contents.php
	 *
	 * @param string $path
	 * @param string $data
	 * @return bool
	 */
	public function file_put_contents($path, $data) {
		return $this->getWrapperStorage()->file_put_contents($this->getSourcePath($path), $data);
	}

	/**
	 * see http://php.net/manual/en/function.unlink.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function unlink($path) {
		return $this->getWrapperStorage()->unlink($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.rename.php
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return bool
	 */
	public function rename($path1, $path2) {
		return $this->getWrapperStorage()->rename($this->getSourcePath($path1), $this->getSourcePath($path2));
	}

	/**
	 * see http://php.net/manual/en/function.copy.php
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return bool
	 */
	public function copy($path1, $path2) {
		return $this->getWrapperStorage()->copy($this->getSourcePath($path1), $this->getSourcePath($path2));
	}

	/**
	 * see http://php.net/manual/en/function.fopen.php
	 *
	 * @param string $path
	 * @param string $mode
	 * @return resource
	 */
	public function fopen($path, $mode) {
		return $this->getWrapperStorage()->fopen($this->getSourcePath($path), $mode);
	}

	/**
	 * get the mimetype for a file or folder
	 * The mimetype for a folder is required to be "httpd/unix-directory"
	 *
	 * @param string $path
	 * @return string
	 */
	public function getMimeType($path) {
		return $this->getWrapperStorage()->getMimeType($this->getSourcePath($path));
	}

	/**
	 * see http://php.net/manual/en/function.hash.php
	 *
	 * @param string $type
	 * @param string $path
	 * @param bool $raw
	 * @return string
	 */
	public function hash($type, $path, $raw = false) {
		return $this->getWrapperStorage()->hash($type, $this->getSourcePath($path), $raw);
	}

	/**
	 * see http://php.net/manual/en/function.free_space.php
	 *
	 * @param string $path
	 * @return int
	 */
	public function free_space($path) {
		return $this->getWrapperStorage()->free_space($this->getSourcePath($path));
	}

	/**
	 * search for occurrences of $query in file names
	 *
	 * @param string $query
	 * @return array
	 */
	public function search($query) {
		$wrapperStorage = $this->getWrapperStorage();
		'@phan-var \OC\Files\Storage\Common $wrapperStorage';
		return $wrapperStorage->search($query);
	}

	/**
	 * see http://php.net/manual/en/function.touch.php
	 * If the backend does not support the operation, false should be returned
	 *
	 * @param string $path
	 * @param int $mtime
	 * @return bool
	 */
	public function touch($path, $mtime = null) {
		return $this->getWrapperStorage()->touch($this->getSourcePath($path), $mtime);
	}

	/**
	 * get the path to a local version of the file.
	 * The local version of the file can be temporary and doesn't have to be persistent across requests
	 *
	 * @param string $path
	 * @return string
	 */
	public function getLocalFile($path) {
		return $this->getWrapperStorage()->getLocalFile($this->getSourcePath($path));
	}

	/**
	 * check if a file or folder has been updated since $time
	 *
	 * @param string $path
	 * @param int $time
	 * @return bool
	 *
	 * hasUpdated for folders should return at least true if a file inside the folder is add, removed or renamed.
	 * returning true for other changes in the folder is optional
	 */
	public function hasUpdated($path, $time) {
		return $this->getWrapperStorage()->hasUpdated($this->getSourcePath($path), $time);
	}

	/**
	 * get a cache instance for the storage
	 *
	 * @param string $path
	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the cache
	 * @return \OC\Files\Cache\Cache
	 */
	public function getCache($path = '', $storage = null) {
		if ($this->rootPath === null) {
			throw new \InvalidArgumentException('Jail rootPath is null');
		}
		if (!$storage) {
			$storage = $this;
		}
		$sourceCache = $this->getWrapperStorage()->getCache($this->getSourcePath($path), $storage);
		return new CacheJail($sourceCache, $this->rootPath);
	}

	/**
	 * get the user id of the owner of a file or folder
	 *
	 * @param string $path
	 * @return string
	 */
	public function getOwner($path) {
		return $this->getWrapperStorage()->getOwner($this->getSourcePath($path));
	}

	/**
	 * get a watcher instance for the cache
	 *
	 * @param string $path
	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the watcher
	 * @return \OC\Files\Cache\Watcher
	 */
	public function getWatcher($path = '', $storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		return $this->getWrapperStorage()->getWatcher($this->getSourcePath($path), $storage);
	}

	/**
	 * get the ETag for a file or folder
	 *
	 * @param string $path
	 * @return string
	 */
	public function getETag($path) {
		return $this->getWrapperStorage()->getETag($this->getSourcePath($path));
	}

	/**
	 * @param string $path
	 * @return array
	 */
	public function getMetaData($path) {
		return $this->getWrapperStorage()->getMetaData($this->getSourcePath($path));
	}

	/**
	 * @param string $path
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @param \OCP\Lock\ILockingProvider $provider
	 * @throws \OCP\Lock\LockedException
	 */
	public function acquireLock($path, $type, ILockingProvider $provider) {
		$this->getWrapperStorage()->acquireLock($this->getSourcePath($path), $type, $provider);
	}

	/**
	 * @param string $path
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @param \OCP\Lock\ILockingProvider $provider
	 */
	public function releaseLock($path, $type, ILockingProvider $provider) {
		$this->getWrapperStorage()->releaseLock($this->getSourcePath($path), $type, $provider);
	}

	/**
	 * @param string $path
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @param \OCP\Lock\ILockingProvider $provider
	 */
	public function changeLock($path, $type, ILockingProvider $provider) {
		$this->getWrapperStorage()->changeLock($this->getSourcePath($path), $type, $provider);
	}

	/**
	 * Resolve the path for the source of the share
	 *
	 * @param string $path
	 * @return array
	 */
	public function resolvePath($path) {
		return [$this->getWrapperStorage(), $this->getSourcePath($path)];
	}

	/**
	 * @param \OCP\Files\Storage $sourceStorage
	 * @param string $sourceInternalPath
	 * @param string $targetInternalPath
	 * @return bool
	 */
	public function copyFromStorage(\OCP\Files\Storage $sourceStorage, $sourceInternalPath, $targetInternalPath) {
		if ($sourceStorage === $this) {
			return $this->copy($sourceInternalPath, $targetInternalPath);
		}
		return $this->getWrapperStorage()->copyFromStorage($sourceStorage, $sourceInternalPath, $this->getSourcePath($targetInternalPath));
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
		return $this->getWrapperStorage()->moveFromStorage($sourceStorage, $sourceInternalPath, $this->getSourcePath($targetInternalPath));
	}

	/**
	 * Get the content of a given version of a given file as stream resource
	 *
	 * @param string $internalPath
	 * @param string $versionId
	 * @return resource
	 * @since 10.0.9
	 */
	public function getContentOfVersion($internalPath, $versionId) {
		$wrapperStorage = $this->getWrapperStorage();
		'@phan-var \OC\Files\Storage\Common $wrapperStorage';
		return $wrapperStorage->getContentOfVersion($this->getSourcePath($internalPath), $versionId);
	}

	/**
	 * Restore the given version of a given file
	 *
	 * @param string $internalPath
	 * @param string $versionId
	 * @return boolean
	 * @since 10.0.9
	 */
	public function restoreVersion($internalPath, $versionId) {
		$wrapperStorage = $this->getWrapperStorage();
		'@phan-var \OC\Files\Storage\Common $wrapperStorage';
		return $wrapperStorage->restoreVersion($this->getSourcePath($internalPath), $versionId);
	}

	/**
	 * Tells the storage to explicitly create a version of a given file
	 *
	 * @param string $internalPath
	 * @return bool
	 * @since 10.0.9
	 */
	public function saveVersion($internalPath) {
		$wrapperStorage = $this->getWrapperStorage();
		'@phan-var \OC\Files\Storage\Common $wrapperStorage';
		return $wrapperStorage->saveVersion($this->getSourcePath($internalPath));
	}

	/**
	 * List all versions for the given file
	 *
	 * @param string $internalPath
	 * @return array
	 * @since 10.0.9
	 */
	public function getVersions($internalPath) {
		$wrapperStorage = $this->getWrapperStorage();
		'@phan-var \OC\Files\Storage\Common $wrapperStorage';
		return $wrapperStorage->getVersions($this->getSourcePath($internalPath));
	}

	/**
	 * Get one explicit version for the given file
	 *
	 * @param string $internalPath
	 * @param string $versionId
	 * @return array
	 * @since 10.0.9
	 */
	public function getVersion($internalPath, $versionId) {
		$wrapperStorage = $this->getWrapperStorage();
		'@phan-var \OC\Files\Storage\Common $wrapperStorage';
		return $wrapperStorage->getVersion($this->getSourcePath($internalPath), $versionId);
	}
}
