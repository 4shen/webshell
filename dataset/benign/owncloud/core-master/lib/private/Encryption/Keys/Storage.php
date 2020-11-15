<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OC\Encryption\Keys;

use OC\Encryption\Util;
use OC\Files\Filesystem;
use OC\Files\View;
use OCP\Encryption\Keys\IStorage;
use OCP\IUserSession;
use OC\User\NoUserException;

class Storage implements IStorage {

	// hidden file which indicate that the folder is a valid key storage
	const KEY_STORAGE_MARKER = '.oc_key_storage';

	/** @var View */
	private $view;

	/** @var Util */
	private $util;

	// base dir where all the file related keys are stored
	/** @var string */
	private $keys_base_dir;

	// root of the key storage default is empty which means that we use the data folder
	/** @var string */
	private $root_dir;

	/** @var string */
	private $encryption_base_dir;

	/** @var array */
	private $keyCache = [];

	/** @var string */
	private $currentUser = null;

	/**
	 * @param View $view view
	 * @param Util $util encryption util class
	 * @param IUserSession $session user session
	 */
	public function __construct(View $view, Util $util, IUserSession $session) {
		$this->view = $view;
		$this->util = $util;

		$this->encryption_base_dir = '/files_encryption';
		$this->keys_base_dir = $this->encryption_base_dir .'/keys';
		$this->root_dir = $this->util->getKeyStorageRoot();

		if ($session !== null && $session->getUser() !== null) {
			$this->currentUser = $session->getUser()->getUID();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getUserKey($uid, $keyId, $encryptionModuleId) {
		$path = $this->constructUserKeyPath($encryptionModuleId, $keyId, $uid);
		return $this->getKey($path);
	}

	/**
	 * @inheritdoc
	 */
	public function getFileKey($path, $keyId, $encryptionModuleId) {
		$realFile = $this->util->stripPartialFileExtension($path);
		$keyDir = $this->getFileKeyDir($encryptionModuleId, $realFile);
		$key = $this->getKey($keyDir . $keyId);

		if ($key === '' && $realFile !== $path) {
			// Check if the part file has keys and use them, if no normal keys
			// exist. This is required to fix copyBetweenStorage() when we
			// rename a .part file over storage borders.
			$keyDir = $this->getFileKeyDir($encryptionModuleId, $path);
			$key = $this->getKey($keyDir . $keyId);
		}

		return $key;
	}

	/**
	 * @inheritdoc
	 */
	public function getSystemUserKey($keyId, $encryptionModuleId) {
		$path = $this->constructUserKeyPath($encryptionModuleId, $keyId, null);
		return $this->getKey($path);
	}

	/**
	 * @inheritdoc
	 */
	public function setUserKey($uid, $keyId, $key, $encryptionModuleId) {
		$path = $this->constructUserKeyPath($encryptionModuleId, $keyId, $uid);
		return $this->setKey($path, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function setFileKey($path, $keyId, $key, $encryptionModuleId) {
		$keyDir = $this->getFileKeyDir($encryptionModuleId, $path);
		return $this->setKey($keyDir . $keyId, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function setSystemUserKey($keyId, $key, $encryptionModuleId) {
		$path = $this->constructUserKeyPath($encryptionModuleId, $keyId, null);
		return $this->setKey($path, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteUserKey($uid, $keyId, $encryptionModuleId) {
		try {
			$path = $this->constructUserKeyPath($encryptionModuleId, $keyId, $uid);
			return !$this->view->file_exists($path) || $this->view->unlink($path);
		} catch (NoUserException $e) {
			// this exception can come from initMountPoints() from setupUserMounts()
			// for a deleted user.
			//
			// It means, that:
			// - we are not running in alternative storage mode because we don't call
			// initMountPoints() in that mode
			// - the keys were in the user's home but since the user was deleted, the
			// user's home is gone and so are the keys
			//
			// So there is nothing to do, just ignore.
		}
	}

	/**
	 * @inheritdoc
	 */
	public function deleteFileKey($path, $keyId, $encryptionModuleId) {
		$keyDir = $this->getFileKeyDir($encryptionModuleId, $path);
		return !$this->view->file_exists($keyDir . $keyId) || $this->view->unlink($keyDir . $keyId);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteAllFileKeys($path) {
		$keyDir = $this->getFileKeyDir('', $path);
		return !$this->view->file_exists($keyDir) || $this->view->deleteAll($keyDir);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteSystemUserKey($keyId, $encryptionModuleId) {
		$path = $this->constructUserKeyPath($encryptionModuleId, $keyId, null);
		return !$this->view->file_exists($path) || $this->view->unlink($path);
	}

	/**
	 * @inheritdoc
	 */

	public function deleteAltUserStorageKeys($uid) {
		if (\OC::$server->getEncryptionManager()->isEnabled()) {
			/**
			 * If the key storage is not the default
			 * location, then we need to remove the keys
			 * in the alternate key location
			 */
			$keyStorageRoot = $this->util->getKeyStorageRoot();
			if ($keyStorageRoot !== '') {
				$this->view->rmdir($keyStorageRoot . '/' . $uid);
				return true;
			}

			return false;
		}
	}

	/**
	 * construct path to users key
	 *
	 * @param string $encryptionModuleId
	 * @param string $keyId
	 * @param string $uid
	 * @return string
	 */
	protected function constructUserKeyPath($encryptionModuleId, $keyId, $uid) {
		if ($uid === null) {
			$path = $this->root_dir . '/' . $this->encryption_base_dir . '/' . $encryptionModuleId . '/' . $keyId;
		} else {
			$this->setupUserMounts($uid);
			$path = $this->root_dir . '/' . $uid . $this->encryption_base_dir . '/'
				. $encryptionModuleId . '/' . $uid . '.' . $keyId;
		}

		return \OC\Files\Filesystem::normalizePath($path);
	}

	/**
	 * read key from hard disk
	 *
	 * @param string $path to key
	 * @return string
	 */
	private function getKey($path) {
		$key = '';

		if ($this->view->file_exists($path)) {
			if (isset($this->keyCache[$path])) {
				$key =  $this->keyCache[$path];
			} else {
				$key = $this->view->file_get_contents($path);
				$this->keyCache[$path] = $key;
			}
		}

		return $key;
	}

	/**
	 * write key to disk
	 *
	 *
	 * @param string $path path to key directory
	 * @param string $key key
	 * @return bool
	 */
	private function setKey($path, $key) {
		$this->keySetPreparation(\dirname($path));

		$result = $this->view->file_put_contents($path, $key);

		if (\is_int($result) && $result > 0) {
			$this->keyCache[$path] = $key;
			return true;
		}

		return false;
	}

	/**
	 * get path to key folder for a given file
	 *
	 * @param string $encryptionModuleId
	 * @param string $path path to the file, relative to data/
	 * @return string
	 */
	private function getFileKeyDir($encryptionModuleId, $path) {
		list($owner, $filename) = $this->util->getUidAndFilename($path);

		// in case of system wide mount points the keys are stored directly in the data directory
		if ($this->util->isSystemWideMountPoint($filename, $owner)) {
			$keyPath = $this->root_dir . '/' . $this->keys_base_dir . $filename . '/';
		} else {
			$this->setupUserMounts($owner);
			$keyPath = $this->root_dir . '/' . $owner . $this->keys_base_dir . $filename . '/';
		}

		return Filesystem::normalizePath($keyPath . $encryptionModuleId . '/', false);
	}

	/**
	 * move keys if a file was renamed
	 *
	 * @param string $source
	 * @param string $target
	 * @return boolean
	 */
	public function renameKeys($source, $target) {
		$sourcePath = $this->getPathToKeys($source);
		$targetPath = $this->getPathToKeys($target);

		if ($this->view->file_exists($sourcePath)) {
			$this->keySetPreparation(\dirname($targetPath));
			$this->view->rename($sourcePath, $targetPath);

			return true;
		}

		return false;
	}

	/**
	 * copy keys if a file was renamed
	 *
	 * @param string $source
	 * @param string $target
	 * @return boolean
	 */
	public function copyKeys($source, $target) {
		$sourcePath = $this->getPathToKeys($source);
		$targetPath = $this->getPathToKeys($target);

		if ($this->view->file_exists($sourcePath)) {
			$this->keySetPreparation(\dirname($targetPath));
			$this->view->copy($sourcePath, $targetPath);
			return true;
		}

		return false;
	}

	/**
	 * get system wide path and detect mount points
	 *
	 * @param string $path
	 * @return string
	 */
	protected function getPathToKeys($path) {
		list($owner, $relativePath) = $this->util->getUidAndFilename($path);
		$systemWideMountPoint = $this->util->isSystemWideMountPoint($relativePath, $owner);

		if ($systemWideMountPoint) {
			$systemPath = $this->root_dir . '/' . $this->keys_base_dir . $relativePath . '/';
		} else {
			$this->setupUserMounts($owner);
			$systemPath = $this->root_dir . '/' . $owner . $this->keys_base_dir . $relativePath . '/';
		}

		return  Filesystem::normalizePath($systemPath, false);
	}

	/**
	 * Make preparations to filesystem for saving a key file
	 *
	 * @param string $path relative to the views root
	 */
	protected function keySetPreparation($path) {
		// If the file resides within a subdirectory, create it
		if (!$this->view->file_exists($path)) {
			$sub_dirs = \explode('/', \ltrim($path, '/'));
			$dir = '';
			foreach ($sub_dirs as $sub_dir) {
				$dir .= '/' . $sub_dir;
				if (!$this->view->is_dir($dir)) {
					$this->view->mkdir($dir);
				}
			}
		}
	}

	/**
	 * Setup the mounts of the given user if different than
	 * the current user.
	 *
	 * This is needed because in many cases the keys are stored
	 * within the user's home storage.
	 *
	 * @param string $uid user id
	 */
	protected function setupUserMounts($uid) {
		if ($this->root_dir !== '') {
			// this means that the keys are stored outside of the user's homes,
			// so we don't need to mount anything
			return;
		}
		if ($uid !== null && $uid !== '' && $uid !== $this->currentUser) {
			\OC\Files\Filesystem::initMountPoints($uid);
		}
	}
}
