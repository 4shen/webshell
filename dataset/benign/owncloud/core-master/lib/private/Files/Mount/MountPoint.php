<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OC\Files\Mount;

use \OC\Files\Filesystem;
use OC\Files\Storage\StorageFactory;
use OC\Files\Storage\Storage;
use OCP\Files\Mount\IMountPoint;

class MountPoint implements IMountPoint {
	/**
	 * @var \OC\Files\Storage\Storage $storage
	 */
	protected $storage = null;
	protected $class;
	protected $storageId;

	/**
	 * Configuration options for the storage backend
	 *
	 * @var array
	 */
	protected $arguments = [];
	protected $mountPoint;

	/**
	 * Mount specific options
	 *
	 * @var array
	 */
	protected $mountOptions = [];

	/**
	 * @var \OC\Files\Storage\StorageFactory $loader
	 */
	private $loader;

	/**
	 * Specified whether the storage is invalid after failing to
	 * instantiate it.
	 *
	 * @var bool
	 */
	private $invalidStorage = false;

	/**
	 * @param string|\OC\Files\Storage\Storage $storage
	 * @param string $mountpoint
	 * @param array $arguments (optional) configuration for the storage backend
	 * @param \OCP\Files\Storage\IStorageFactory $loader
	 * @param array $mountOptions mount specific options
	 */
	public function __construct($storage, $mountpoint, $arguments = null, $loader = null, $mountOptions = null) {
		if ($arguments === null) {
			$arguments = [];
		}
		if ($loader === null) {
			$this->loader = new StorageFactory();
		} else {
			$this->loader = $loader;
		}

		if ($mountOptions !== null) {
			$this->mountOptions = $mountOptions;
		}

		$mountpoint = $this->formatPath($mountpoint);
		$this->mountPoint = $mountpoint;
		// FIXME: this should also check for IStorage, and the public Storage interface
		if ($storage instanceof Storage) {
			$this->class = \get_class($storage);
			// IStorageFactory does not have wrap
			// But StorageFactory does have wrap
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$this->storage = $this->loader->wrap($this, $storage);
		} else {
			// Update old classes to new namespace
			if (\strpos($storage, 'OC_Filestorage_') !== false) {
				$storage = '\OC\Files\Storage\\' . \substr($storage, 15);
			}
			$this->class = $storage;
			$this->arguments = $arguments;
		}
	}

	/**
	 * get complete path to the mount point, relative to data/
	 *
	 * @return string
	 */
	public function getMountPoint() {
		return $this->mountPoint;
	}

	/**
	 * Sets the mount point path, relative to data/
	 *
	 * @param string $mountPoint new mount point
	 */
	public function setMountPoint($mountPoint) {
		$this->mountPoint = $this->formatPath($mountPoint);
	}

	/**
	 * create the storage that is mounted
	 *
	 * @return \OC\Files\Storage\Storage
	 */
	private function createStorage() {
		if ($this->invalidStorage) {
			return null;
		}

		if (\class_exists($this->class)) {
			try {
				return $this->loader->getInstance($this, $this->class, $this->arguments);
			} catch (\Exception $exception) {
				$this->invalidStorage = true;
				if ($this->mountPoint === '/') {
					// the root storage could not be initialized, show the user!
					throw new \Exception('The root storage could not be initialized. Please contact your local administrator.', $exception->getCode(), $exception);
				} else {
					\OCP\Util::writeLog('core', $exception->getMessage(), \OCP\Util::ERROR);
				}
				return null;
			}
		} else {
			\OCP\Util::writeLog('core', 'storage backend ' . $this->class . ' not found', \OCP\Util::ERROR);
			$this->invalidStorage = true;
			return null;
		}
	}

	/**
	 * @return \OC\Files\Storage\Storage
	 */
	public function getStorage() {
		if ($this->storage === null) {
			$this->storage = $this->createStorage();
		}
		return $this->storage;
	}

	/**
	 * @return string
	 */
	public function getStorageId() {
		if (!$this->storageId) {
			if ($this->storage === null) {
				$storage = $this->createStorage(); //FIXME: start using exceptions
				if ($storage === null) {
					return null;
				}

				$this->storage = $storage;
			}
			$this->storageId = $this->storage->getId();
			if (\strlen($this->storageId) > 64) {
				$this->storageId = \md5($this->storageId);
			}
		}
		return $this->storageId;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function getInternalPath($path) {
		$path = Filesystem::normalizePath($path, true, false, true);
		if ($this->mountPoint === $path or $this->mountPoint . '/' === $path) {
			$internalPath = '';
		} else {
			$internalPath = \substr($path, \strlen($this->mountPoint));
		}
		// substr returns false instead of an empty string, we always want a string
		return (string)$internalPath;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function formatPath($path) {
		$path = Filesystem::normalizePath($path);
		if (\strlen($path) > 1) {
			$path .= '/';
		}
		return $path;
	}

	/**
	 * @param callable $wrapper
	 */
	public function wrapStorage($wrapper) {
		$storage = $this->getStorage();
		// storage can be null if it couldn't be initialized
		if ($storage != null) {
			$this->storage = $wrapper($this->mountPoint, $storage, $this);
		}
	}

	/**
	 * Get a mount option
	 *
	 * @param string $name Name of the mount option to get
	 * @param mixed $default Default value for the mount option
	 * @return mixed
	 */
	public function getOption($name, $default) {
		return isset($this->mountOptions[$name]) ? $this->mountOptions[$name] : $default;
	}

	/**
	 * Get all options for the mount
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->mountOptions;
	}

	/**
	 * Get the file id of the root of the storage
	 *
	 * @return int storage numeric id or -1 in case of invalid storage
	 */
	public function getStorageRootId() {
		$storage = $this->getStorage();
		if ($storage === null || $this->invalidStorage) {
			return -1;
		}

		$cache = $storage->getCache();

		if ($cache === null) {
			return -1;
		}

		return (int)$cache->getId('');
	}
}
