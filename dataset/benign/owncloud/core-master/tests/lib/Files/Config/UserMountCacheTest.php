<?php
/**
 * Copyright (c) 2015 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Config;

use Doctrine\DBAL\Statement;
use OC\DB\QueryBuilder\Literal;
use OC\Files\Config\UserMountCache;
use OC\Files\Mount\MountPoint;
use OC\Log;
use OC\User\Account;
use OC\User\AccountMapper;
use OC\User\Manager;
use OC\User\SyncService;
use OCP\DB\QueryBuilder\IExpressionBuilder;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\Config\ICachedMountInfo;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUserManager;
use Test\TestCase;

/**
 * @group DB
 */
class UserMountCacheTest extends TestCase {
	/**
	 * @var IDBConnection
	 */
	private $connection;

	/**
	 * @var IUserManager
	 */
	private $userManager;

	/**
	 * @var UserMountCache
	 */
	private $cache;

	/**
	 * @var \OCP\Util\UserSearch
	 */
	protected $userSearch;

	private $fileIds = [];

	public function setUp(): void {
		$this->fileIds = [];
		$this->connection = \OC::$server->getDatabaseConnection();
		$this->userSearch = $this->getMockBuilder(\OCP\Util\UserSearch::class)
			->disableOriginalConstructor()
			->getMock();
		$this->userSearch->expects($this->any())
			->method('isSearchable')
			->willReturn(true);

		/** @var IConfig $config */
		$config = $this->createMock(IConfig::class);
		/** @var AccountMapper | \PHPUnit\Framework\MockObject\MockObject $accountMapper */
		$accountMapper = $this->createMock(AccountMapper::class);
		$a1 = new Account();
		$a1->setId(1);
		$a1->setUserId('u1');
		$a2 = new Account();
		$a2->setId(2);
		$a2->setUserId('u2');
		$a3 = new Account();
		$a3->setId(3);
		$a3->setUserId('u3');

		$accountMapper->expects($this->any())->method('getByUid')->willReturnMap([
			['u1', $a1],
			['u2', $a2],
			['u3', $a3],
		]);

		/** @var Log $log */
		$log = $this->createMock(Log::class);
		/** @var SyncService $syncService */
		$syncService = $this->createMock(SyncService::class);
		$this->userManager = new Manager($config, $log, $accountMapper, $syncService, $this->userSearch);
		$this->cache = new UserMountCache($this->connection, $this->userManager, $log);

		// hookup listener
		$this->userManager->listen('\OC\User', 'postDelete', [$this->cache, 'removeUserMounts']);
	}

	public function tearDown(): void {
		$builder = $this->connection->getQueryBuilder();

		$builder->delete('mounts')->execute();

		$builder = $this->connection->getQueryBuilder();

		foreach ($this->fileIds as $fileId) {
			$builder->delete('filecache')
				->where($builder->expr()->eq('fileid', new Literal($fileId)))
				->execute();
		}
	}

	private function getStorage($storageId, $rootId) {
		$storageCache = $this->getMockBuilder('\OC\Files\Cache\Storage')
			->disableOriginalConstructor()
			->getMock();
		$storageCache->expects($this->any())
			->method('getNumericId')
			->will($this->returnValue($storageId));

		$cache = $this->getMockBuilder('\OC\Files\Cache\Cache')
			->disableOriginalConstructor()
			->getMock();
		$cache->expects($this->any())
			->method('getId')
			->will($this->returnValue($rootId));

		$storage = $this->getMockBuilder('\OC\Files\Storage\Storage')
			->disableOriginalConstructor()
			->getMock();
		$storage->expects($this->any())
			->method('getStorageCache')
			->will($this->returnValue($storageCache));
		$storage->expects($this->any())
			->method('getCache')
			->will($this->returnValue($cache));

		return $storage;
	}

	private function clearCache() {
		$this->invokePrivate($this->cache, 'mountsForUsers', [[]]);
	}

	public function testNewMounts() {
		$user = $this->userManager->get('u1');

		$storage = $this->getStorage(10, 20);
		$mount = new MountPoint($storage, '/asd/');

		$this->cache->registerMounts($user, [$mount]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForUser($user);

		$this->assertCount(1, $cachedMounts);
		$cachedMount = $cachedMounts[0];
		$this->assertEquals('/asd/', $cachedMount->getMountPoint());
		$this->assertEquals($user, $cachedMount->getUser());
		$this->assertEquals($storage->getCache()->getId(''), $cachedMount->getRootId());
		$this->assertEquals($storage->getStorageCache()->getNumericId(), $cachedMount->getStorageId());
	}

	public function testSameMounts() {
		$user = $this->userManager->get('u1');

		$storage = $this->getStorage(10, 20);
		$mount = new MountPoint($storage, '/asd/');

		$this->cache->registerMounts($user, [$mount]);

		$this->clearCache();

		$this->cache->registerMounts($user, [$mount]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForUser($user);

		$this->assertCount(1, $cachedMounts);
		$cachedMount = $cachedMounts[0];
		$this->assertEquals('/asd/', $cachedMount->getMountPoint());
		$this->assertEquals($user, $cachedMount->getUser());
		$this->assertEquals($storage->getCache()->getId(''), $cachedMount->getRootId());
		$this->assertEquals($storage->getStorageCache()->getNumericId(), $cachedMount->getStorageId());
	}

	public function testRemoveMounts() {
		$user = $this->userManager->get('u1');

		$storage = $this->getStorage(10, 20);
		$mount = new MountPoint($storage, '/asd/');

		$this->cache->registerMounts($user, [$mount]);

		$this->clearCache();

		$this->cache->registerMounts($user, []);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForUser($user);

		$this->assertCount(0, $cachedMounts);
	}

	public function testChangeMounts() {
		$user = $this->userManager->get('u1');

		$storage = $this->getStorage(10, 20);
		$mount = new MountPoint($storage, '/foo/');

		$this->cache->registerMounts($user, [$mount]);

		$this->clearCache();

		$this->cache->registerMounts($user, [$mount]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForUser($user);

		$this->assertCount(1, $cachedMounts);
		$cachedMount = $cachedMounts[0];
		$this->assertEquals('/foo/', $cachedMount->getMountPoint());
	}

	public function testGetMountsForUser() {
		$user1 = $this->userManager->get('u1');
		$user2 = $this->userManager->get('u2');
		$user3 = $this->userManager->get('u3');

		$mount1 = new MountPoint($this->getStorage(1, 2), '/foo/');
		$mount2 = new MountPoint($this->getStorage(3, 4), '/bar/');

		$this->cache->registerMounts($user1, [$mount1, $mount2]);
		$this->cache->registerMounts($user2, [$mount2]);
		$this->cache->registerMounts($user3, [$mount2]);

		$this->clearCache();

		$user3->delete();

		$cachedMounts = $this->cache->getMountsForUser($user1);

		$this->assertCount(2, $cachedMounts);
		$this->assertEquals('/foo/', $cachedMounts[0]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[0]->getUser());
		$this->assertEquals(2, $cachedMounts[0]->getRootId());
		$this->assertEquals(1, $cachedMounts[0]->getStorageId());

		$this->assertEquals('/bar/', $cachedMounts[1]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[1]->getUser());
		$this->assertEquals(4, $cachedMounts[1]->getRootId());
		$this->assertEquals(3, $cachedMounts[1]->getStorageId());

		$cachedMounts = $this->cache->getMountsForUser($user3);
		$this->assertEmpty($cachedMounts);
	}

	public function testGetMountsByStorageId() {
		$user1 = $this->userManager->get('u1');
		$user2 = $this->userManager->get('u2');

		$mount1 = new MountPoint($this->getStorage(1, 2), '/foo/');
		$mount2 = new MountPoint($this->getStorage(3, 4), '/bar/');

		$this->cache->registerMounts($user1, [$mount1, $mount2]);
		$this->cache->registerMounts($user2, [$mount2]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForStorageId(3);
		$this->sortMounts($cachedMounts);

		$this->assertCount(2, $cachedMounts);

		$this->assertEquals('/bar/', $cachedMounts[0]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[0]->getUser());
		$this->assertEquals(4, $cachedMounts[0]->getRootId());
		$this->assertEquals(3, $cachedMounts[0]->getStorageId());

		$this->assertEquals('/bar/', $cachedMounts[1]->getMountPoint());
		$this->assertEquals($user2, $cachedMounts[1]->getUser());
		$this->assertEquals(4, $cachedMounts[1]->getRootId());
		$this->assertEquals(3, $cachedMounts[1]->getStorageId());
	}

	public function testGetMountsByRootId() {
		$user1 = $this->userManager->get('u1');
		$user2 = $this->userManager->get('u2');

		$mount1 = new MountPoint($this->getStorage(1, 2), '/foo/');
		$mount2 = new MountPoint($this->getStorage(3, 4), '/bar/');

		$this->cache->registerMounts($user1, [$mount1, $mount2]);
		$this->cache->registerMounts($user2, [$mount2]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForRootId(4);
		$this->sortMounts($cachedMounts);

		$this->assertCount(2, $cachedMounts);

		$this->assertEquals('/bar/', $cachedMounts[0]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[0]->getUser());
		$this->assertEquals(4, $cachedMounts[0]->getRootId());
		$this->assertEquals(3, $cachedMounts[0]->getStorageId());

		$this->assertEquals('/bar/', $cachedMounts[1]->getMountPoint());
		$this->assertEquals($user2, $cachedMounts[1]->getUser());
		$this->assertEquals(4, $cachedMounts[1]->getRootId());
		$this->assertEquals(3, $cachedMounts[1]->getStorageId());
	}

	private function sortMounts(&$mounts) {
		\usort($mounts, function (ICachedMountInfo $a, ICachedMountInfo $b) {
			return \strcmp($a->getUser()->getUID(), $b->getUser()->getUID());
		});
	}

	private function createCacheEntry($internalPath, $storageId) {
		$this->connection->insertIfNotExist('*PREFIX*filecache', [
			'storage' => $storageId,
			'path' => $internalPath,
			'path_hash' => \md5($internalPath),
			'parent' => -1,
			'name' => \basename($internalPath),
			'mimetype' => 0,
			'mimepart' => 0,
			'size' => 0,
			'storage_mtime' => 0,
			'encrypted' => 0,
			'unencrypted_size' => 0,
			'etag' => '',
			'permissions' => 31
		], ['storage', 'path_hash']);
		$id = (int)$this->connection->lastInsertId('*PREFIX*filecache');
		$this->fileIds[] = $id;
		return $id;
	}

	public function testGetMountsForFileIdRootId() {
		$user1 = $this->userManager->get('u1');

		$rootId = $this->createCacheEntry('', 2);

		$mount1 = new MountPoint($this->getStorage(2, $rootId), '/foo/');

		$this->cache->registerMounts($user1, [$mount1]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForFileId($rootId);

		$this->assertCount(1, $cachedMounts);

		$this->assertEquals('/foo/', $cachedMounts[0]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[0]->getUser());
		$this->assertEquals($rootId, $cachedMounts[0]->getRootId());
		$this->assertEquals(2, $cachedMounts[0]->getStorageId());
	}

	public function testGetMountsForFileIdSubFolder() {
		$user1 = $this->userManager->get('u1');

		$rootId = $this->createCacheEntry('', 2);
		$fileId = $this->createCacheEntry('/foo/bar', 2);

		$mount1 = new MountPoint($this->getStorage(2, $rootId), '/foo/');

		$this->cache->registerMounts($user1, [$mount1]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForFileId($fileId);

		$this->assertCount(1, $cachedMounts);

		$this->assertEquals('/foo/', $cachedMounts[0]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[0]->getUser());
		$this->assertEquals($rootId, $cachedMounts[0]->getRootId());
		$this->assertEquals(2, $cachedMounts[0]->getStorageId());
	}

	public function testGetMountsForFileIdSubFolderMount() {
		$user1 = $this->userManager->get('u1');

		$this->createCacheEntry('', 2);
		$folderId = $this->createCacheEntry('/foo', 2);
		$fileId = $this->createCacheEntry('/foo/bar', 2);

		$mount1 = new MountPoint($this->getStorage(2, $folderId), '/foo/');

		$this->cache->registerMounts($user1, [$mount1]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForFileId($fileId);

		$this->assertCount(1, $cachedMounts);

		$this->assertEquals('/foo/', $cachedMounts[0]->getMountPoint());
		$this->assertEquals($user1, $cachedMounts[0]->getUser());
		$this->assertEquals($folderId, $cachedMounts[0]->getRootId());
		$this->assertEquals(2, $cachedMounts[0]->getStorageId());
	}

	public function testGetMountsForFileIdSubFolderMountOutside() {
		$user1 = $this->userManager->get('u1');

		$this->createCacheEntry('', 2);
		$folderId = $this->createCacheEntry('/foo', 2);
		$fileId = $this->createCacheEntry('/bar/asd', 2);

		$mount1 = new MountPoint($this->getStorage(2, $folderId), '/foo/');

		$this->cache->registerMounts($user1, [$mount1]);

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForFileId($fileId);

		$this->assertCount(0, $cachedMounts);
	}

	public function testGetMountsForFileIdDeletedUser() {
		$user1 = $this->userManager->get('u1');

		$rootId = $this->createCacheEntry('', 2);

		$mount1 = new MountPoint($this->getStorage(2, $rootId), '/foo/');

		$this->cache->registerMounts($user1, [$mount1]);

		$user1->delete();

		$this->clearCache();

		$cachedMounts = $this->cache->getMountsForFileId($rootId);

		$this->assertEmpty($cachedMounts);
	}

	/**
	 * Oracle returns null for empty path, strict comparison fails on null
	 */
	public function testEmptyPathCacheInfoFromFileId() {
		$statement = $this->createMock(Statement::class);
		$statement->method('fetch')->willReturn([ 'storage' => 55, 'path' => null]);

		$eb = $this->createMock(IExpressionBuilder::class);
		$qb = $this->createMock(IQueryBuilder::class);
		$qb->method('expr')->willReturn($eb);
		$qb->method('select')->willReturnSelf();
		$qb->method('from')->willReturnSelf();
		$qb->method('where')->willReturnSelf();
		$qb->method('execute')->willReturn($statement);

		$conn = $this->createMock(IDBConnection::class);
		$conn->method('getQueryBuilder')->willReturn($qb);
		$userManager = $this->createMock(IUserManager::class);
		$logger = $this->createMock(ILogger::class);

		$cache = new UserMountCache($conn, $userManager, $logger);
		$cacheInfo = $this->invokePrivate($cache, 'getCacheInfoFromFileId', [55]);
		$this->assertEquals([55, ''], $cacheInfo);
	}
}
