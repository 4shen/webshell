<?php
/**
 * Copyright (c) 2014 Vincent Petry <pvince81@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test;
use OC\Files\Filesystem;
use OC\Files\Storage\Temporary;
use OCP\IUser;

/**
 * Test the storage functions of OC_Helper
 *
 * @group DB
 */
class HelperStorageTest extends TestCase {
	/** @var string */
	private $user;
	/** @var \OC\Files\Storage\Storage */
	private $storageMock;
	/** @var \OC\Files\Storage\Storage */
	private $storage;
	/** @var IUser */
	private $userObj;

	protected function setUp(): void {
		parent::setUp();

		$this->user = $this->getUniqueID('user_');
		$this->userObj = \OC::$server->getUserManager()->createUser($this->user, $this->user);

		$this->storage = Filesystem::getStorage('/');
		Filesystem::tearDown();
		\OC_User::setUserId($this->user);
		Filesystem::init($this->user, '/' . $this->user . '/files');
		Filesystem::clearMounts();

		$this->storageMock = null;
	}

	protected function tearDown(): void {
		if ($this->storageMock) {
			$this->storageMock->getCache()->clear();
			$this->storageMock = null;
		}
		Filesystem::tearDown();
		Filesystem::mount($this->storage, [], '/');

		\OC_User::setUserId('');
		$user = \OC::$server->getUserManager()->get($this->user);
		if ($user !== null) {
			$user->delete();
		}
		\OC::$server->getConfig()->deleteAllUserValues($this->user);

		parent::tearDown();
	}

	/**
	 * Returns a storage mock that returns the given value as
	 * free space
	 *
	 * @param int $freeSpace free space value
	 * @return \OC\Files\Storage\Storage
	 */
	private function getStorageMock($freeSpace = 12) {
		$this->storageMock = $this->getMockBuilder('\OC\Files\Storage\Temporary')
			->setMethods(['free_space'])
			->setConstructorArgs([''])
			->getMock();

		$this->storageMock->expects($this->once())
			->method('free_space')
			->will($this->returnValue($freeSpace));
		return $this->storageMock;
	}

	/**
	 * Test getting the storage info
	 */
	public function testGetStorageInfo() {
		$homeStorage = $this->getStorageMock(12);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');
		$homeStorage->file_put_contents('test.txt', '01234');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(12, $storageInfo['free']);
		$this->assertEquals(5, $storageInfo['used']);
		$this->assertEquals(17, $storageInfo['total']);
	}

	/**
	 * Test getting the storage info, ignoring extra mount points
	 */
	public function testGetStorageInfoExcludingExtStorage() {
		$homeStorage = $this->getStorageMock(12);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');
		$homeStorage->file_put_contents('test.txt', '01234');

		$extStorage = new Temporary([]);
		$extStorage->file_put_contents('extfile.txt', 'abcdefghijklmnopq');
		$extStorage->getScanner()->scan(''); // update root size

		Filesystem::mount($extStorage, [], '/' . $this->user . '/files/ext');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(12, $storageInfo['free']);
		$this->assertEquals(5, $storageInfo['used']);
		$this->assertEquals(17, $storageInfo['total']);
	}

	/**
	 * Test getting the storage info, including extra mount points
	 */
	public function testGetStorageInfoIncludingExtStorage() {
		$homeStorage = new Temporary([]);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');
		$homeStorage->file_put_contents('test.txt', '01234');

		$extStorage = new Temporary([]);
		$extStorage->file_put_contents('extfile.txt', 'abcdefghijklmnopq');
		$extStorage->getScanner()->scan(''); // update root size

		Filesystem::mount($extStorage, [], '/' . $this->user . '/files/ext');

		$config = \OC::$server->getConfig();
		$oldConfig = $config->getSystemValue('quota_include_external_storage', false);
		$config->setSystemValue('quota_include_external_storage', 'true');

		$this->userObj->setQuota('25');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(3, $storageInfo['free']);
		$this->assertEquals(22, $storageInfo['used']);
		$this->assertEquals(25, $storageInfo['total']);

		$config->setSystemValue('quota_include_external_storage', $oldConfig);
		$config->setUserValue($this->user, 'files', 'quota', 'default');
	}

	/**
	 * Test getting the storage info excluding extra mount points
	 * when user has no quota set, even when quota ext storage option
	 * was set
	 */
	public function testGetStorageInfoIncludingExtStorageWithNoUserQuota() {
		$homeStorage = $this->getStorageMock(12);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');
		$homeStorage->file_put_contents('test.txt', '01234');

		$extStorage = new Temporary([]);
		$extStorage->file_put_contents('extfile.txt', 'abcdefghijklmnopq');
		$extStorage->getScanner()->scan(''); // update root size

		Filesystem::mount($extStorage, [], '/' . $this->user . '/files/ext');

		$config = \OC::$server->getConfig();
		$oldConfig = $config->getSystemValue('quota_include_external_storage', false);
		$config->setSystemValue('quota_include_external_storage', 'true');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(12, $storageInfo['free']);
		$this->assertEquals(5, $storageInfo['used']);
		$this->assertEquals(17, $storageInfo['total']);

		$config->setSystemValue('quota_include_external_storage', $oldConfig);
	}

	/**
	 * Test getting the storage info with quota enabled
	 */
	public function testGetStorageInfoWithQuota() {
		$homeStorage = $this->getStorageMock(12);
		$homeStorage->file_put_contents('test.txt', '01234');
		$homeStorage = new \OC\Files\Storage\Wrapper\Quota(
			[
				'storage' => $homeStorage,
				'quota' => 7
			]
		);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(2, $storageInfo['free']);
		$this->assertEquals(5, $storageInfo['used']);
		$this->assertEquals(7, $storageInfo['total']);
	}

	/**
	 * Test getting the storage info when data exceeds quota
	 */
	public function testGetStorageInfoWhenSizeExceedsQuota() {
		$homeStorage = $this->getStorageMock(12);
		$homeStorage->file_put_contents('test.txt', '0123456789');
		$homeStorage = new \OC\Files\Storage\Wrapper\Quota(
			[
				'storage' => $homeStorage,
				'quota' => 7
			]
		);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(0, $storageInfo['free']);
		$this->assertEquals(10, $storageInfo['used']);
		// total = quota
		$this->assertEquals(7, $storageInfo['total']);
	}

	/**
	 * Test getting the storage info when the remaining
	 * free storage space is less than the quota
	 */
	public function testGetStorageInfoWhenFreeSpaceLessThanQuota() {
		$homeStorage = $this->getStorageMock(12);
		$homeStorage->file_put_contents('test.txt', '01234');
		$homeStorage = new \OC\Files\Storage\Wrapper\Quota(
			[
				'storage' => $homeStorage,
				'quota' => 18
			]
		);
		Filesystem::mount($homeStorage, [], '/' . $this->user . '/files');

		$storageInfo = \OC_Helper::getStorageInfo('');
		$this->assertEquals(12, $storageInfo['free']);
		$this->assertEquals(5, $storageInfo['used']);
		// total = free + used (because quota > total)
		$this->assertEquals(17, $storageInfo['total']);
	}
}
