<?php
/**
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Olivier Paroz <github@oparoz.com>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OC\Files\Utils;

use OC\Files\Cache\Cache;
use OC\Files\Filesystem;
use OC\ForbiddenException;
use OC\Hooks\PublicEmitter;
use OC\Lock\DBLockingProvider;
use OCP\Files\Storage\IStorage;
use OCP\Files\StorageNotAvailableException;
use OCP\ILogger;

/**
 * Class Scanner
 *
 * Hooks available in scope \OC\Utils\Scanner
 *  - scanFile(string $absolutePath)
 *  - scanFolder(string $absolutePath)
 *
 * @package OC\Files\Utils
 */
class Scanner extends PublicEmitter {
	const MAX_ENTRIES_TO_COMMIT = 10000;

	/**
	 * @var string $user
	 */
	private $user;

	/**
	 * @var \OCP\IDBConnection
	 */
	protected $db;

	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 * Whether to use a DB transaction
	 *
	 * @var bool
	 */
	protected $useTransaction;

	/**
	 * Number of entries scanned to commit
	 *
	 * @var int
	 */
	protected $entriesToCommit;

	/**
	 * @param string $user
	 * @param \OCP\IDBConnection $db
	 * @param ILogger $logger
	 */
	public function __construct($user, $db, ILogger $logger) {
		$this->logger = $logger;
		$this->user = $user;
		$this->db = $db;
		// when DB locking is used, no DB transactions will be used
		$this->useTransaction = !(\OC::$server->getLockingProvider() instanceof DBLockingProvider);
	}

	/**
	 * get all storages for $dir
	 *
	 * @param string $dir
	 * @return \OC\Files\Mount\MountPoint[]
	 */
	protected function getMounts($dir) {
		//TODO: move to the node based fileapi once that's done
		\OC_Util::tearDownFS();
		\OC_Util::setupFS($this->user);

		$mountManager = Filesystem::getMountManager();
		$mounts = $mountManager->findIn($dir);
		$mounts[] = $mountManager->find($dir);
		$mounts = \array_reverse($mounts); //start with the mount of $dir

		return $mounts;
	}

	/**
	 * attach listeners to the scanner
	 *
	 * @param \OC\Files\Mount\MountPoint $mount
	 */
	protected function attachListener($mount) {
		$scanner = $mount->getStorage()->getScanner();
		$emitter = $this;
		$scanner->listen('\OC\Files\Cache\Scanner', 'scanFile', function ($path) use ($mount, $emitter) {
			$emitter->emit('\OC\Files\Utils\Scanner', 'scanFile', [$mount->getMountPoint() . $path]);
		});
		$scanner->listen('\OC\Files\Cache\Scanner', 'scanFolder', function ($path) use ($mount, $emitter) {
			$emitter->emit('\OC\Files\Utils\Scanner', 'scanFolder', [$mount->getMountPoint() . $path]);
		});
		$scanner->listen('\OC\Files\Cache\Scanner', 'postScanFile', function ($path) use ($mount, $emitter) {
			$emitter->emit('\OC\Files\Utils\Scanner', 'postScanFile', [$mount->getMountPoint() . $path]);
		});
		$scanner->listen('\OC\Files\Cache\Scanner', 'postScanFolder', function ($path) use ($mount, $emitter) {
			$emitter->emit('\OC\Files\Utils\Scanner', 'postScanFolder', [$mount->getMountPoint() . $path]);
		});
	}

	/**
	 * @param string $dir
	 */
	public function backgroundScan($dir) {
		$mounts = $this->getMounts($dir);
		foreach ($mounts as $mount) {
			if (!$this->shouldScan($mount)) {
				continue;
			}

			$storage = $mount->getStorage();

			// don't scan the root storage
			if ($storage->instanceOfStorage('\OC\Files\Storage\Local') && $mount->getMountPoint() === '/') {
				continue;
			}

			$scanner = $storage->getScanner();
			$this->attachListener($mount);

			$scanner->listen('\OC\Files\Cache\Scanner', 'removeFromCache', function ($path) use ($storage) {
				$this->triggerPropagator($storage, $path);
			});
			$scanner->listen('\OC\Files\Cache\Scanner', 'updateCache', function ($path) use ($storage) {
				$this->triggerPropagator($storage, $path);
			});
			$scanner->listen('\OC\Files\Cache\Scanner', 'addToCache', function ($path) use ($storage) {
				$this->triggerPropagator($storage, $path);
			});

			$propagator = $storage->getPropagator();
			$propagator->beginBatch();
			$scanner->backgroundScan();
			$propagator->commitBatch();
		}
	}

	/**
	 * Returns whether the given mount point should be scanned
	 *
	 * @param $mount mount point
	 * @return bool true to scan, false to skip
	 */
	private function shouldScan($mount) {
		$storage = $mount->getStorage();
		if ($storage === null) {
			return false;
		}

		// don't bother scanning failed storages (shortcut for same result)
		if ($storage->instanceOfStorage('OC\Files\Storage\FailedStorage')) {
			return false;
		}

		// if the home storage isn't readable then the scanner is run as the wrong user
		// (guests will have a read-only home storage)
		if ($storage->instanceOfStorage('\OC\Files\Storage\Home') and
			(!$storage->isReadable('') or !$storage->isReadable('files'))
		) {
			if ($storage->file_exists('') or $storage->getCache()->inCache('')) {
				throw new ForbiddenException();
			} else {// if the root exists in neither the cache nor the storage the user isn't setup yet
				return false;
			}
		}

		// don't scan received local shares, these can be scanned when scanning the owner's storage
		if ($storage->instanceOfStorage('OCA\Files_Sharing\ISharedStorage')) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $dir
	 * @throws \OC\ForbiddenException
	 */
	public function scan($dir = '') {
		if (!Filesystem::isValidPath($dir)) {
			throw new \InvalidArgumentException('Invalid path to scan');
		}
		$mounts = $this->getMounts($dir);
		foreach ($mounts as $mount) {
			if (!$this->shouldScan($mount)) {
				continue;
			}

			$storage = $mount->getStorage();

			$this->emit('\OC\Files\Utils\Scanner', 'beforeScanStorage', [$storage]);

			$relativePath = $mount->getInternalPath($dir);
			$scanner = $storage->getScanner();
			$scanner->setUseTransactions(false);
			$this->attachListener($mount);

			$scanner->listen('\OC\Files\Cache\Scanner', 'removeFromCache', function ($path) use ($storage) {
				$this->postProcessEntry($storage, $path);
			});
			$scanner->listen('\OC\Files\Cache\Scanner', 'updateCache', function ($path) use ($storage) {
				$this->postProcessEntry($storage, $path);
			});
			$scanner->listen('\OC\Files\Cache\Scanner', 'addToCache', function ($path) use ($storage) {
				$this->postProcessEntry($storage, $path);
			});

			if ($this->useTransaction) {
				$this->db->beginTransaction();
			}
			try {
				$propagator = $storage->getPropagator();
				$propagator->beginBatch();
				$scanner->scan($relativePath, \OC\Files\Cache\Scanner::SCAN_RECURSIVE, \OC\Files\Cache\Scanner::REUSE_ETAG | \OC\Files\Cache\Scanner::REUSE_SIZE);
				$cache = $storage->getCache();
				if ($cache instanceof Cache) {
					// only re-calculate for the root folder we scanned, anything below that is taken care of by the scanner
					$cache->correctFolderSize($relativePath);
				}
				$propagator->commitBatch();
			} catch (StorageNotAvailableException $e) {
				$this->logger->error('Storage ' . $storage->getId() . ' not available');
				$this->logger->logException($e);
				$this->emit('\OC\Files\Utils\Scanner', 'StorageNotAvailable', [$e]);
			}
			if ($this->useTransaction) {
				$this->db->commit();
			}
			$this->emit('\OC\Files\Utils\Scanner', 'afterScanStorage', [$storage]);
		}
	}

	private function triggerPropagator(IStorage $storage, $internalPath) {
		$storage->getPropagator()->propagateChange($internalPath, \time());
	}

	private function postProcessEntry(IStorage $storage, $internalPath) {
		$this->triggerPropagator($storage, $internalPath);
		if ($this->useTransaction) {
			$this->entriesToCommit++;
			if ($this->entriesToCommit >= self::MAX_ENTRIES_TO_COMMIT) {
				$propagator = $storage->getPropagator();
				$this->entriesToCommit = 0;
				$this->db->commit();
				$propagator->commitBatch();
				$this->db->beginTransaction();
				$propagator->beginBatch();
			}
		}
	}
}
