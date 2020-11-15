<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Utils;

use OC\Files\Filesystem;
use OC\Files\Mount\MountPoint;
use OC\Files\Storage\Temporary;
use OCP\Files\Storage\IStorageFactory;
use OCP\IUser;
use Test\Traits\UserTrait;
use OCP\Files\Cache\ICache;
use OC\ForbiddenException;
use OC\Files\Storage\Storage;

class TestScanner extends \OC\Files\Utils\Scanner {
	/**
	 * @var \OC\Files\Mount\MountPoint[] $mounts
	 */
	private $mounts = [];

	/**
	 * @param \OC\Files\Mount\MountPoint $mount
	 */
	public function addMount($mount) {
		$this->mounts[] = $mount;
	}

	protected function getMounts($dir) {
		return $this->mounts;
	}
}

/**
 * Class ScannerTest
 *
 * @group DB
 *
 * @package Test\Files\Utils
 */
class ScannerTest extends \Test\TestCase {
	use UserTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->loginAsUser();
	}

	protected function tearDown(): void {
		$this->logout();
		parent::tearDown();
	}

	public function testReuseExistingRoot() {
		$storage = new Temporary([]);
		$mount = new MountPoint($storage, '');
		Filesystem::getMountManager()->addMount($mount);
		$cache = $storage->getCache();

		$storage->mkdir('folder');
		$storage->file_put_contents('foo.txt', 'qwerty');
		$storage->file_put_contents('folder/bar.txt', 'qwerty');

		$scanner = new TestScanner('', \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->addMount($mount);

		$scanner->scan('');
		$this->assertTrue($cache->inCache('folder/bar.txt'));
		$oldRoot = $cache->get('');

		$scanner->scan('');
		$newRoot = $cache->get('');
		$this->assertEquals($oldRoot, $newRoot);
	}

	public function testReuseExistingFile() {
		$storage = new Temporary([]);
		$mount = new MountPoint($storage, '');
		Filesystem::getMountManager()->addMount($mount);
		$cache = $storage->getCache();

		$storage->mkdir('folder');
		$storage->file_put_contents('foo.txt', 'qwerty');
		$storage->file_put_contents('folder/bar.txt', 'qwerty');

		$scanner = new TestScanner('', \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->addMount($mount);

		$scanner->scan('');
		$this->assertTrue($cache->inCache('folder/bar.txt'));
		$old = $cache->get('folder/bar.txt');

		$scanner->scan('');
		$new = $cache->get('folder/bar.txt');
		$this->assertEquals($old, $new);
	}

	public function testScanSubMount() {
		$uid = $this->getUniqueID();
		$this->createUser($uid);

		$mountProvider = $this->createMock('\OCP\Files\Config\IMountProvider');

		$storage = new Temporary([]);
		$mount = new MountPoint($storage, '/' . $uid . '/files/foo');

		$mountProvider->expects($this->any())
			->method('getMountsForUser')
			->will($this->returnCallback(function (IUser $user, IStorageFactory $storageFactory) use ($mount, $uid) {
				if ($user->getUID() === $uid) {
					return [$mount];
				} else {
					return [];
				}
			}));

		\OC::$server->getMountProviderCollection()->registerProvider($mountProvider);
		$cache = $storage->getCache();

		$storage->mkdir('folder');
		$storage->file_put_contents('foo.txt', 'qwerty');
		$storage->file_put_contents('folder/bar.txt', 'qwerty');

		$scanner = new \OC\Files\Utils\Scanner($uid, \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());

		$this->assertFalse($cache->inCache('folder/bar.txt'));
		$scanner->scan('/' . $uid . '/files/foo');
		$this->assertTrue($cache->inCache('folder/bar.txt'));
	}

	/**
	 * @return array
	 */
	public function invalidPathProvider() {
		return [
			[
				'../',
			],
			[
				'..\\',
			],
			[
				'../..\\../',
			],
		];
	}

	/**
	 * @dataProvider invalidPathProvider
	 * @param string $invalidPath
	 */
	public function testInvalidPathScanning($invalidPath) {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid path to scan');

		$scanner = new TestScanner('', \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->scan($invalidPath);
	}

	public function testPropagateEtag() {
		$storage = new Temporary([]);
		$mount = new MountPoint($storage, '');
		Filesystem::getMountManager()->addMount($mount);
		$cache = $storage->getCache();

		$storage->mkdir('folder');
		$storage->file_put_contents('folder/bar.txt', 'qwerty');
		$storage->touch('folder/bar.txt', \time() - 200);

		$scanner = new TestScanner('', \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->addMount($mount);

		$scanner->scan('');
		$this->assertTrue($cache->inCache('folder/bar.txt'));
		$oldRoot = $cache->get('');

		$storage->file_put_contents('folder/bar.txt', 'qwerty');
		$scanner->scan('');
		$newRoot = $cache->get('');

		$this->assertNotEquals($oldRoot->getEtag(), $newRoot->getEtag());
	}

	public function testSkipLocalShares() {
		$sharedStorage = $this->createMock('OCA\Files_Sharing\SharedStorage');
		$sharedMount = new MountPoint($sharedStorage, '/share');
		Filesystem::getMountManager()->addMount($sharedMount);

		$sharedStorage->expects($this->any())
			->method('instanceOfStorage')
			->will($this->returnValueMap([
				['OCA\Files_Sharing\ISharedStorage', true],
			]));
		$sharedStorage->expects($this->never())
			->method('getScanner');

		$scanner = new TestScanner('', \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->addMount($sharedMount);
		$scanner->scan('');

		$scanner->backgroundScan('');
	}

	public function nonReadyHomesProvider() {
		return [
			[[['', false], ['files', false]], false, false, false, false],
			[[['', true ], ['files', false]], false, false, false, false],
			[[['', true ], ['files', false]], true, false, true, false],
			[[['', false], ['files', true ]], false, true, true, false],
			[[['', true ], ['files', false]], true, true, true, false],
		];
	}

	/**
	 * @dataProvider nonReadyHomesProvider
	 */
	public function testSkipNonReadyHomes($isCreatableValues, $rootFileExists = false, $rootInCache = false, $expectedException = false) {
		$homeStorage = $this->createMock(Storage::class);
		$homeCache = $this->createMock(ICache::class);
		$homeMount = new MountPoint($homeStorage, '/user1/');
		Filesystem::getMountManager()->addMount($homeMount);

		$homeStorage->expects($this->any())
			->method('instanceOfStorage')
			->will($this->returnValueMap([
				['\OC\Files\Storage\Home', true],
			]));

		$homeStorage->expects($this->never())
			->method('getScanner');

		$homeStorage->method('getCache')->willReturn($homeCache);
		$homeCache->method('inCache')->with('')->willReturn($rootInCache);

		$homeStorage->method('isCreatable')
			->will($this->returnValueMap($isCreatableValues));
		$homeStorage->method('file_exists')
			->with('')
			->willReturn($rootFileExists);

		$scanner = new TestScanner('', \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->addMount($homeMount);

		try {
			$scanner->scan('');
			$this->assertFalse($expectedException, 'Exception must not be thrown');
		} catch (ForbiddenException $e) {
			$this->assertTrue($expectedException, 'Exception must be thrown');
		}

		try {
			$scanner->backgroundScan('');
			$this->assertFalse($expectedException, 'Exception must not be thrown');
		} catch (ForbiddenException $e) {
			$this->assertTrue($expectedException, 'Exception must be thrown');
		}
	}
}
