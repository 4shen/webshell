<?php

/**
 * ownCloud
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 *
 * @author Bjoern Schiessle <schiessle@owncloud.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Test\Encryption\Keys;

use OC\Encryption\Keys\Storage;
use OC\Encryption\Util;
use OC\Files\View;
use OCP\IUser;
use OCP\IUserSession;
use Test\TestCase;
use Test\Traits\UserTrait;

/**
 * Class StorageTest
 *
 * @group DB
 *
 * @package Test\Encryption\Keys
 */
class StorageTest extends TestCase {
	use UserTrait;

	/** @var Storage */
	protected $storage;

	/** @var \PHPUnit\Framework\MockObject\MockObject | Util */
	protected $util;

	/** @var \PHPUnit\Framework\MockObject\MockObject | View */
	protected $view;

	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $config;

	public function setUp(): void {
		parent::setUp();

		$this->util = $this->getMockBuilder('OC\Encryption\Util')
			->disableOriginalConstructor()
			->getMock();

		$this->util->method('getKeyStorageRoot')->willReturn('');

		$this->view = $this->getMockBuilder('OC\Files\View')
			->disableOriginalConstructor()
			->getMock();

		$this->config = $this->getMockBuilder('OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('user1');
		/** @var IUserSession | \PHPUnit\Framework\MockObject\MockObject $userSession */
		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$this->createUser('user1', '123456');
		$this->createUser('user2', '123456');
		$this->storage = new Storage($this->view, $this->util, $userSession);
	}

	public function tearDown(): void {
		\OC\Files\Filesystem::tearDown();
		parent::tearDown();
	}

	public function testSetFileKey() {
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->willReturn(['user1', '/files/foo.txt']);
		$this->util->expects($this->any())
			->method('stripPartialFileExtension')
			->willReturnArgument(0);
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn(false);
		$this->view->expects($this->once())
			->method('file_put_contents')
			->with($this->equalTo('/user1/files_encryption/keys/files/foo.txt/encModule/fileKey'),
				$this->equalTo('key'))
			->willReturn(\strlen('key'));

		$this->assertTrue(
			$this->storage->setFileKey('user1/files/foo.txt', 'fileKey', 'key', 'encModule')
		);
	}

	public function dataTestGetFileKey() {
		return [
			['/files/foo.txt', '/files/foo.txt', true, 'key'],
			['/files/foo.txt.ocTransferId2111130212.part', '/files/foo.txt', true, 'key'],
			['/files/foo.txt.ocTransferId2111130212.part', '/files/foo.txt', false, 'key2'],
		];
	}

	/**
	 * @dataProvider dataTestGetFileKey
	 *
	 * @param string $path
	 * @param string $strippedPartialName
	 * @param bool $originalKeyExists
	 * @param string $expectedKeyContent
	 */
	public function testGetFileKey($path, $strippedPartialName, $originalKeyExists, $expectedKeyContent) {
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->willReturnMap([
				['user1/files/foo.txt', ['user1', '/files/foo.txt']],
				['user1/files/foo.txt.ocTransferId2111130212.part', ['user1', '/files/foo.txt.ocTransferId2111130212.part']],
			]);
		// we need to strip away the part file extension in order to reuse a
		// existing key if it exists, otherwise versions will break
		$this->util->expects($this->once())
			->method('stripPartialFileExtension')
			->willReturn('user1' . $strippedPartialName);
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn(false);

		$this->view->expects($this->at(0))
			->method('file_exists')
			->with($this->equalTo('/user1/files_encryption/keys' . $strippedPartialName . '/encModule/fileKey'))
			->willReturn($originalKeyExists);

		if (!$originalKeyExists) {
			$this->view->expects($this->at(1))
				->method('file_exists')
				->with($this->equalTo('/user1/files_encryption/keys' . $path . '/encModule/fileKey'))
				->willReturn(true);

			$this->view->expects($this->once())
				->method('file_get_contents')
				->with($this->equalTo('/user1/files_encryption/keys' . $path . '/encModule/fileKey'))
				->willReturn('key2');
		} else {
			$this->view->expects($this->once())
				->method('file_get_contents')
				->with($this->equalTo('/user1/files_encryption/keys' . $strippedPartialName . '/encModule/fileKey'))
				->willReturn('key');
		}

		$this->assertSame($expectedKeyContent,
			$this->storage->getFileKey('user1' . $path, 'fileKey', 'encModule')
		);
	}

	public function testSetFileKeySystemWide() {
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->willReturn(['user1', '/files/foo.txt']);
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn(true);
		$this->util->expects($this->any())
			->method('stripPartialFileExtension')
			->willReturnArgument(0);
		$this->view->expects($this->once())
			->method('file_put_contents')
			->with($this->equalTo('/files_encryption/keys/files/foo.txt/encModule/fileKey'),
				$this->equalTo('key'))
			->willReturn(\strlen('key'));

		$this->assertTrue(
			$this->storage->setFileKey('user1/files/foo.txt', 'fileKey', 'key', 'encModule')
		);
	}

	public function testGetFileKeySystemWide() {
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->willReturn(['user1', '/files/foo.txt']);
		$this->util->expects($this->any())
			->method('stripPartialFileExtension')
			->willReturnArgument(0);
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn(true);
		$this->view->expects($this->once())
			->method('file_get_contents')
			->with($this->equalTo('/files_encryption/keys/files/foo.txt/encModule/fileKey'))
			->willReturn('key');
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/files_encryption/keys/files/foo.txt/encModule/fileKey'))
			->willReturn(true);

		$this->assertSame('key',
			$this->storage->getFileKey('user1/files/foo.txt', 'fileKey', 'encModule')
		);
	}

	public function testSetSystemUserKey() {
		$this->view->expects($this->once())
			->method('file_put_contents')
			->with($this->equalTo('/files_encryption/encModule/shareKey_56884'),
				$this->equalTo('key'))
			->willReturn(\strlen('key'));

		$this->assertTrue(
			$this->storage->setSystemUserKey('shareKey_56884', 'key', 'encModule')
		);
	}

	public function testSetUserKey() {
		$this->view->expects($this->once())
			->method('file_put_contents')
			->with($this->equalTo('/user1/files_encryption/encModule/user1.publicKey'),
				$this->equalTo('key'))
			->willReturn(\strlen('key'));

		$this->assertTrue(
			$this->storage->setUserKey('user1', 'publicKey', 'key', 'encModule')
		);
	}

	public function testGetSystemUserKey() {
		$this->view->expects($this->once())
			->method('file_get_contents')
			->with($this->equalTo('/files_encryption/encModule/shareKey_56884'))
			->willReturn('key');
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/files_encryption/encModule/shareKey_56884'))
			->willReturn(true);

		$this->assertSame('key',
			$this->storage->getSystemUserKey('shareKey_56884', 'encModule')
		);
	}

	public function testGetUserKey() {
		$this->view->expects($this->once())
			->method('file_get_contents')
			->with($this->equalTo('/user1/files_encryption/encModule/user1.publicKey'))
			->willReturn('key');
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/user1/files_encryption/encModule/user1.publicKey'))
			->willReturn(true);

		$this->assertSame('key',
			$this->storage->getUserKey('user1', 'publicKey', 'encModule')
		);
	}

	public function testGetUserKeyShared() {
		$this->view->expects($this->once())
			->method('file_get_contents')
			->with($this->equalTo('/user2/files_encryption/encModule/user2.publicKey'))
			->willReturn('key');
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/user2/files_encryption/encModule/user2.publicKey'))
			->willReturn(true);

		$this->assertFalse($this->isUserHomeMounted('user2'));

		$this->assertSame('key',
			$this->storage->getUserKey('user2', 'publicKey', 'encModule')
		);

		$this->assertTrue($this->isUserHomeMounted('user2'));
	}

	public function testGetUserKeyWhenKeyStorageIsOutsideHome() {
		$this->view->expects($this->once())
			->method('file_get_contents')
			->with($this->equalTo('/enckeys/user2/files_encryption/encModule/user2.publicKey'))
			->willReturn('key');
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/enckeys/user2/files_encryption/encModule/user2.publicKey'))
			->willReturn(true);

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('user1');
		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);
		$util = $this->createMock(\OC\Encryption\Util::class);
		$util->method('getKeyStorageRoot')->willReturn('enckeys');
		$storage = new Storage($this->view, $util, $userSession);

		$this->assertFalse($this->isUserHomeMounted('user2'));

		$this->assertSame('key',
			$storage->getUserKey('user2', 'publicKey', 'encModule')
		);

		$this->assertFalse($this->isUserHomeMounted('user2'), 'mounting was not necessary');
	}

	/**
	 * Returns whether the home of the given user was mounted
	 *
	 * @param string $userId
	 * @return boolean true if mounted, false otherwise
	 */
	private function isUserHomeMounted($userId) {
		// verify that user2's FS got mounted when retrieving the key
		$mountManager = \OC::$server->getMountManager();
		$mounts = $mountManager->getAll();
		$mounts = \array_filter($mounts, function ($mount) use ($userId) {
			return ($mount->getMountPoint() === "/$userId/");
		});

		return !empty($mounts);
	}

	public function testDeleteUserKey() {
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/user1/files_encryption/encModule/user1.publicKey'))
			->willReturn(true);
		$this->view->expects($this->once())
			->method('unlink')
			->with($this->equalTo('/user1/files_encryption/encModule/user1.publicKey'))
			->willReturn(true);

		$this->assertTrue(
			$this->storage->deleteUserKey('user1', 'publicKey', 'encModule')
		);
	}

	public function testDeleteSystemUserKey() {
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/files_encryption/encModule/shareKey_56884'))
			->willReturn(true);
		$this->view->expects($this->once())
			->method('unlink')
			->with($this->equalTo('/files_encryption/encModule/shareKey_56884'))
			->willReturn(true);

		$this->assertTrue(
			$this->storage->deleteSystemUserKey('shareKey_56884', 'encModule')
		);
	}

	public function testDeleteFileKeySystemWide() {
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->willReturn(['user1', '/files/foo.txt']);
		$this->util->expects($this->any())
			->method('stripPartialFileExtension')
			->willReturnArgument(0);
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn(true);
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/files_encryption/keys/files/foo.txt/encModule/fileKey'))
			->willReturn(true);
		$this->view->expects($this->once())
			->method('unlink')
			->with($this->equalTo('/files_encryption/keys/files/foo.txt/encModule/fileKey'))
			->willReturn(true);

		$this->assertTrue(
			$this->storage->deleteFileKey('user1/files/foo.txt', 'fileKey', 'encModule')
		);
	}

	public function testDeleteFileKey() {
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->willReturn(['user1', '/files/foo.txt']);
		$this->util->expects($this->any())
			->method('stripPartialFileExtension')
			->willReturnArgument(0);
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn(false);
		$this->view->expects($this->once())
			->method('file_exists')
			->with($this->equalTo('/user1/files_encryption/keys/files/foo.txt/encModule/fileKey'))
			->willReturn(true);
		$this->view->expects($this->once())
			->method('unlink')
			->with($this->equalTo('/user1/files_encryption/keys/files/foo.txt/encModule/fileKey'))
			->willReturn(true);

		$this->assertTrue(
			$this->storage->deleteFileKey('user1/files/foo.txt', 'fileKey', 'encModule')
		);
	}

	/**
	 * @dataProvider dataProviderCopyRename
	 */
	public function testRenameKeys($source, $target, $systemWideMountSource, $systemWideMountTarget, $expectedSource, $expectedTarget) {
		$this->view->expects($this->any())
			->method('file_exists')
			->willReturn(true);
		$this->view->expects($this->any())
			->method('is_dir')
			->willReturn(true);
		$this->view->expects($this->once())
			->method('rename')
			->with(
				$this->equalTo($expectedSource),
				$this->equalTo($expectedTarget))
			->willReturn(true);
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->will($this->returnCallback([$this, 'getUidAndFilenameCallback']));
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturnCallback(function ($path, $owner) use ($systemWideMountSource, $systemWideMountTarget) {
				if (\strpos($path, 'source.txt') !== false) {
					return $systemWideMountSource;
				}
				return $systemWideMountTarget;
			});

		$this->storage->renameKeys($source, $target);
	}

	/**
	 * @dataProvider dataProviderCopyRename
	 */
	public function testCopyKeys($source, $target, $systemWideMountSource, $systemWideMountTarget, $expectedSource, $expectedTarget) {
		$this->view->expects($this->any())
			->method('file_exists')
			->willReturn(true);
		$this->view->expects($this->any())
			->method('is_dir')
			->willReturn(true);
		$this->view->expects($this->once())
			->method('copy')
			->with(
				$this->equalTo($expectedSource),
				$this->equalTo($expectedTarget))
			->willReturn(true);
		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->will($this->returnCallback([$this, 'getUidAndFilenameCallback']));
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturnCallback(function ($path, $owner) use ($systemWideMountSource, $systemWideMountTarget) {
				if (\strpos($path, 'source.txt') !== false) {
					return $systemWideMountSource;
				}
				return $systemWideMountTarget;
			});

		$this->storage->copyKeys($source, $target);
	}

	public function getUidAndFilenameCallback() {
		$args = \func_get_args();

		$path = $args[0];
		$parts = \explode('/', $path);

		return [$parts[1], '/' . \implode('/', \array_slice($parts, 2))];
	}

	public function dataProviderCopyRename() {
		return [
			['/user1/files/source.txt', '/user1/files/target.txt', false, false,
				'/user1/files_encryption/keys/files/source.txt/', '/user1/files_encryption/keys/files/target.txt/'],
			['/user1/files/foo/source.txt', '/user1/files/target.txt', false, false,
				'/user1/files_encryption/keys/files/foo/source.txt/', '/user1/files_encryption/keys/files/target.txt/'],
			['/user1/files/source.txt', '/user1/files/foo/target.txt', false, false,
				'/user1/files_encryption/keys/files/source.txt/', '/user1/files_encryption/keys/files/foo/target.txt/'],
			['/user1/files/source.txt', '/user1/files/foo/target.txt', true, true,
				'/files_encryption/keys/files/source.txt/', '/files_encryption/keys/files/foo/target.txt/'],
			['/user1/files/source.txt', '/user1/files/target.txt', false, true,
				'/user1/files_encryption/keys/files/source.txt/', '/files_encryption/keys/files/target.txt/'],
			['/user1/files/source.txt', '/user1/files/target.txt', true, false,
				'/files_encryption/keys/files/source.txt/', '/user1/files_encryption/keys/files/target.txt/'],

			['/user2/files/source.txt', '/user1/files/target.txt', false, false,
				'/user2/files_encryption/keys/files/source.txt/', '/user1/files_encryption/keys/files/target.txt/'],
			['/user2/files/foo/source.txt', '/user1/files/target.txt', false, false,
				'/user2/files_encryption/keys/files/foo/source.txt/', '/user1/files_encryption/keys/files/target.txt/'],
			['/user2/files/source.txt', '/user1/files/foo/target.txt', false, false,
				'/user2/files_encryption/keys/files/source.txt/', '/user1/files_encryption/keys/files/foo/target.txt/'],
			['/user2/files/source.txt', '/user1/files/foo/target.txt', true, true,
				'/files_encryption/keys/files/source.txt/', '/files_encryption/keys/files/foo/target.txt/'],
			['/user2/files/source.txt', '/user1/files/target.txt', false, true,
				'/user2/files_encryption/keys/files/source.txt/', '/files_encryption/keys/files/target.txt/'],
			['/user2/files/source.txt', '/user1/files/target.txt', true, false,
				'/files_encryption/keys/files/source.txt/', '/user1/files_encryption/keys/files/target.txt/'],
		];
	}

	/**
	 * @dataProvider dataTestGetPathToKeys
	 *
	 * @param string $path
	 * @param boolean $systemWideMountPoint
	 * @param string $storageRoot
	 * @param string $expected
	 */
	public function testGetPathToKeys($path, $systemWideMountPoint, $storageRoot, $expected) {
		$this->invokePrivate($this->storage, 'root_dir', [$storageRoot]);

		$this->util->expects($this->any())
			->method('getUidAndFilename')
			->will($this->returnCallback([$this, 'getUidAndFilenameCallback']));
		$this->util->expects($this->any())
			->method('isSystemWideMountPoint')
			->willReturn($systemWideMountPoint);

		$this->assertSame($expected,
			self::invokePrivate($this->storage, 'getPathToKeys', [$path])
		);
	}

	public function dataTestGetPathToKeys() {
		return [
			['/user1/files/source.txt', false, '', '/user1/files_encryption/keys/files/source.txt/'],
			['/user1/files/source.txt', true, '', '/files_encryption/keys/files/source.txt/'],
			['/user1/files/source.txt', false, 'storageRoot', '/storageRoot/user1/files_encryption/keys/files/source.txt/'],
			['/user1/files/source.txt', true, 'storageRoot', '/storageRoot/files_encryption/keys/files/source.txt/'],
		];
	}

	public function testKeySetPreparation() {
		$this->view->expects($this->any())
			->method('file_exists')
			->willReturn(false);
		$this->view->expects($this->any())
			->method('is_dir')
			->willReturn(false);
		$this->view->expects($this->any())
			->method('mkdir')
			->will($this->returnCallback([$this, 'mkdirCallback']));

		$this->mkdirStack = [
			'/user1/files_encryption/keys/foo',
			'/user1/files_encryption/keys',
			'/user1/files_encryption',
			'/user1'];

		self::invokePrivate($this->storage, 'keySetPreparation', ['/user1/files_encryption/keys/foo']);
	}

	public function mkdirCallback() {
		$args = \func_get_args();
		$expected = \array_pop($this->mkdirStack);
		$this->assertSame($expected, $args[0]);
	}

	/**
	 * @dataProvider dataTestGetFileKeyDir
	 *
	 * @param bool $isSystemWideMountPoint
	 * @param string $storageRoot
	 * @param string $expected
	 */
	public function testGetFileKeyDir($isSystemWideMountPoint, $storageRoot, $expected) {
		$path = '/user1/files/foo/bar.txt';
		$owner = 'user1';
		$relativePath = '/foo/bar.txt';

		$this->invokePrivate($this->storage, 'root_dir', [$storageRoot]);

		$this->util->expects($this->once())->method('isSystemWideMountPoint')
			->willReturn($isSystemWideMountPoint);
		$this->util->expects($this->once())->method('getUidAndFilename')
			->with($path)->willReturn([$owner, $relativePath]);

		$this->assertSame($expected,
			$this->invokePrivate($this->storage, 'getFileKeyDir', ['OC_DEFAULT_MODULE', $path])
		);
	}

	public function dataTestGetFileKeyDir() {
		return [
			[false, '', '/user1/files_encryption/keys/foo/bar.txt/OC_DEFAULT_MODULE/'],
			[true, '', '/files_encryption/keys/foo/bar.txt/OC_DEFAULT_MODULE/'],
			[false, 'newStorageRoot', '/newStorageRoot/user1/files_encryption/keys/foo/bar.txt/OC_DEFAULT_MODULE/'],
			[true, 'newStorageRoot', '/newStorageRoot/files_encryption/keys/foo/bar.txt/OC_DEFAULT_MODULE/'],
		];
	}
}
