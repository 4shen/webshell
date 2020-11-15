<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OC\Files\Config;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\Config\ICachedMountInfo;
use OCP\Files\Config\IUserMountCache;
use OCP\Files\Mount\IMountPoint;
use OCP\Files\NotFoundException;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OC\Cache\CappedMemoryCache;

/**
 * Cache mounts points per user in the cache so we can easilly look them up
 */
class UserMountCache implements IUserMountCache {
	/**
	 * @var IDBConnection
	 */
	private $connection;

	/**
	 * @var IUserManager
	 */
	private $userManager;

	/**
	 * Cached mount info.
	 * Map of $userId to ICachedMountInfo.
	 *
	 * @var CappedMemoryCache
	 **/
	private $mountsForUsers;

	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * @var CappedMemoryCache
	 */
	private $cacheInfoCache;

	/**
	 * UserMountCache constructor.
	 *
	 * @param IDBConnection $connection
	 * @param IUserManager $userManager
	 * @param ILogger $logger
	 */
	public function __construct(IDBConnection $connection, IUserManager $userManager, ILogger $logger) {
		$this->connection = $connection;
		$this->userManager = $userManager;
		$this->logger = $logger;
		$this->cacheInfoCache = new CappedMemoryCache();
		$this->mountsForUsers = new CappedMemoryCache();
	}

	public function registerMounts(IUser $user, array $mounts) {
		/** @var ICachedMountInfo[] $newMounts */
		$newMounts = \array_map(function (IMountPoint $mount) use ($user) {
			// filter out any storages which aren't scanned yet since we aren't interested in files from those storages (yet)
			if ($mount->getStorageRootId() === -1) {
				return null;
			} else {
				return new LazyStorageMountInfo($user, $mount);
			}
		}, $mounts);
		$newMounts = \array_values(\array_filter($newMounts));

		$cachedMounts = $this->getMountsForUser($user);
		$mountDiff = function (ICachedMountInfo $mount1, ICachedMountInfo $mount2) {
			// since we are only looking for mounts for a specific user comparing on root id is enough
			return $mount1->getRootId() - $mount2->getRootId();
		};

		/** @var ICachedMountInfo[] $addedMounts */
		$addedMounts = \array_udiff($newMounts, $cachedMounts, $mountDiff);
		/** @var ICachedMountInfo[] $removedMounts */
		$removedMounts = \array_udiff($cachedMounts, $newMounts, $mountDiff);

		$changedMounts = \array_uintersect($newMounts, $cachedMounts, function (ICachedMountInfo $mount1, ICachedMountInfo $mount2) {
			// filter mounts with the same root id and different mountpoints
			if ($mount1->getRootId() !== $mount2->getRootId()) {
				return -1;
			}
			return ($mount1->getMountPoint() !== $mount2->getMountPoint()) ? 0 : 1;
		});

		foreach ($addedMounts as $mount) {
			$this->addToCache($mount);
			$this->mountsForUsers[$user->getUID()][] = $mount;
		}
		foreach ($removedMounts as $mount) {
			$this->removeFromCache($mount);
			$index = \array_search($mount, $this->mountsForUsers[$user->getUID()]);
			unset($this->mountsForUsers[$user->getUID()][$index]);
		}
		foreach ($changedMounts as $mount) {
			$this->setMountPoint($mount);
		}
	}

	private function addToCache(ICachedMountInfo $mount) {
		if ($mount->getStorageId() !== -1) {
			$this->connection->insertIfNotExist('*PREFIX*mounts', [
				'storage_id' => $mount->getStorageId(),
				'root_id' => $mount->getRootId(),
				'user_id' => $mount->getUser()->getUID(),
				'mount_point' => $mount->getMountPoint()
			], ['root_id', 'user_id']);
		} else {
			// in some cases this is legitimate, like orphaned shares
			$this->logger->debug('Could not get storage info for mount at ' . $mount->getMountPoint());
		}
	}

	private function setMountPoint(ICachedMountInfo $mount) {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->update('mounts')
			->set('mount_point', $builder->createNamedParameter($mount->getMountPoint()))
			->where($builder->expr()->eq('user_id', $builder->createNamedParameter($mount->getUser()->getUID())))
			->andWhere($builder->expr()->eq('root_id', $builder->createNamedParameter($mount->getRootId(), IQueryBuilder::PARAM_INT)));

		$query->execute();
	}

	private function removeFromCache(ICachedMountInfo $mount) {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->delete('mounts')
			->where($builder->expr()->eq('user_id', $builder->createNamedParameter($mount->getUser()->getUID())))
			->andWhere($builder->expr()->eq('root_id', $builder->createNamedParameter($mount->getRootId(), IQueryBuilder::PARAM_INT)));
		$query->execute();
	}

	private function dbRowToMountInfo(array $row) {
		$user = $this->userManager->get($row['user_id']);
		if ($user === null) {
			// user does not exist any more, delete all mounts of that user directly
			$builder = $this->connection->getQueryBuilder();
			$query = $builder->delete('mounts')
				->where($builder->expr()->eq('user_id', $builder->createNamedParameter($row['user_id'])));
			$query->execute();
			return null;
		}
		return new CachedMountInfo($user, (int)$row['storage_id'], (int)$row['root_id'], $row['mount_point']);
	}

	/**
	 * Convert DB rows to CachedMountInfo
	 *
	 * @param array $rows DB rows
	 * @return CachedMountInfo[]
	 */
	private function convertRows($rows) {
		$mountInfos = [];
		foreach ($rows as $row) {
			$mountInfo = $this->dbRowToMountInfo($row);
			if ($mountInfo !== null) {
				$mountInfos[] = $mountInfo;
			}
		}
		return $mountInfos;
	}

	/**
	 * @param IUser $user
	 * @return ICachedMountInfo[]
	 */
	public function getMountsForUser(IUser $user) {
		if (!isset($this->mountsForUsers[$user->getUID()])) {
			$builder = $this->connection->getQueryBuilder();
			$query = $builder->select('storage_id', 'root_id', 'user_id', 'mount_point')
				->from('mounts')
				->where($builder->expr()->eq('user_id', $builder->createPositionalParameter($user->getUID())))
				->orderBy('storage_id');

			$rows = $query->execute()->fetchAll();

			$this->mountsForUsers[$user->getUID()] = $this->convertRows($rows);
		}
		return $this->mountsForUsers[$user->getUID()];
	}

	/**
	 * @param int $numericStorageId
	 * @return CachedMountInfo[]
	 */
	public function getMountsForStorageId($numericStorageId) {
		$builder = $this->connection->getQueryBuilder();
		$query = $builder->select('storage_id', 'root_id', 'user_id', 'mount_point')
			->from('mounts')
			->where($builder->expr()->eq('storage_id', $builder->createPositionalParameter($numericStorageId, IQueryBuilder::PARAM_INT)));

		$rows = $query->execute()->fetchAll();

		return $this->convertRows($rows);
	}

	/**
	 * @param int $rootFileId
	 * @return CachedMountInfo[]
	 */
	public function getMountsForRootId($rootFileId) {
		$builder = $this->connection->getQueryBuilder();
		$query = $builder->select('storage_id', 'root_id', 'user_id', 'mount_point')
			->from('mounts')
			->where($builder->expr()->eq('root_id', $builder->createPositionalParameter($rootFileId, IQueryBuilder::PARAM_INT)));

		$rows = $query->execute()->fetchAll();

		return $this->convertRows($rows);
	}

	/**
	 * @param $fileId
	 * @return array
	 * @throws \OCP\Files\NotFoundException
	 */
	private function getCacheInfoFromFileId($fileId) {
		if (!isset($this->cacheInfoCache[$fileId])) {
			$builder = $this->connection->getQueryBuilder();
			$query = $builder->select('storage', 'path')
				->from('filecache')
				->where($builder->expr()->eq('fileid', $builder->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)));

			$row = $query->execute()->fetch();
			if (\is_array($row)) {
				$this->cacheInfoCache[$fileId] = [
					(int)$row['storage'],
					(string)$row['path']
				];
			} else {
				throw new NotFoundException('File with id "' . $fileId . '" not found');
			}
		}
		return $this->cacheInfoCache[$fileId];
	}

	/**
	 * @param int $fileId
	 * @return ICachedMountInfo[]
	 * @since 9.0.0
	 */
	public function getMountsForFileId($fileId) {
		try {
			list($storageId, $internalPath) = $this->getCacheInfoFromFileId($fileId);
		} catch (NotFoundException $e) {
			return [];
		}
		$mountsForStorage = $this->getMountsForStorageId($storageId);

		// filter mounts that are from the same storage but a different directory
		return \array_filter($mountsForStorage, function (ICachedMountInfo $mount) use ($internalPath, $fileId) {
			if ($fileId === $mount->getRootId()) {
				return true;
			}
			try {
				list(, $internalMountPath) = $this->getCacheInfoFromFileId($mount->getRootId());
			} catch (NotFoundException $e) {
				return false;
			}

			return $internalMountPath === '' || \substr($internalPath, 0, \strlen($internalMountPath) + 1) === $internalMountPath . '/';
		});
	}

	/**
	 * Remove all cached mounts for a user
	 *
	 * @param IUser $user
	 */
	public function removeUserMounts(IUser $user) {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->delete('mounts')
			->where($builder->expr()->eq('user_id', $builder->createNamedParameter($user->getUID())));
		$query->execute();
	}

	public function removeUserStorageMount($storageId, $userId) {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->delete('mounts')
			->where($builder->expr()->eq('user_id', $builder->createNamedParameter($userId)))
			->andWhere($builder->expr()->eq('storage_id', $builder->createNamedParameter($storageId, IQueryBuilder::PARAM_INT)));
		$query->execute();
	}

	public function remoteStorageMounts($storageId) {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->delete('mounts')
			->where($builder->expr()->eq('storage_id', $builder->createNamedParameter($storageId, IQueryBuilder::PARAM_INT)));
		$query->execute();
	}
}
