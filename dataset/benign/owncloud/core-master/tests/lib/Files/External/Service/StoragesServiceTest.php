<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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
namespace Test\Files\External\Service;

use OC\Files\Cache\Storage;
use OC\Files\External\Service\StoragesService;
use OC\Files\External\StorageConfig;
use OC\Files\Filesystem;
use OCP\Files\External\IStoragesBackendService;
use OCP\Files\External\NotFoundException;
use Test\TestCase;
use OCP\Files\External\Backend\InvalidBackend;
use OCP\Files\External\Auth\InvalidAuth;
use OCP\Files\External\Backend\Backend;

/**
 * @group DB
 */
abstract class StoragesServiceTest extends TestCase {

	/**
	 * @var StoragesService
	 */
	protected $service;

	/** @var IStoragesBackendService */
	protected $backendService;

	/**
	 * Data directory
	 *
	 * @var string
	 */
	protected $dataDir;

	/** @var  CleaningDBConfig */
	protected $dbConfig;

	/**
	 * Hook calls
	 *
	 * @var array
	 */
	protected static $hookCalls;

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject | \OCP\Files\Config\IUserMountCache
	 */
	protected $mountCache;

	/**
	 * @var Backend[]
	 */
	protected $backends;

	public function setUp(): void {
		parent::setUp();
		$this->dbConfig = new CleaningDBConfig(\OC::$server->getDatabaseConnection(), \OC::$server->getCrypto());
		self::$hookCalls = [];
		$config = \OC::$server->getConfig();
		$this->dataDir = $config->getSystemValue(
			'datadirectory',
			\OC::$SERVERROOT . '/data/'
		);

		$this->mountCache = $this->createMock('OCP\Files\Config\IUserMountCache');

		// prepare IStoragesBackendService mock
		$this->backendService =
			$this->getMockBuilder('\OCP\Files\External\IStoragesBackendService')
				->disableOriginalConstructor()
				->getMock();

		$authMechanisms = [
			'identifier:\Auth\Mechanism' => $this->getAuthMechMock('null', '\Auth\Mechanism'),
			'identifier:\Other\Auth\Mechanism' => $this->getAuthMechMock('null', '\Other\Auth\Mechanism'),
			'identifier:\OC\Files\External\Auth\NullMechanism' => $this->getAuthMechMock(),
		];
		$this->backendService->method('getAuthMechanism')
			->will($this->returnCallback(function ($class) use ($authMechanisms) {
				if (isset($authMechanisms[$class])) {
					return $authMechanisms[$class];
				}
				return null;
			}));
		$this->backendService->method('getAuthMechanismsByScheme')
			->will($this->returnCallback(function ($schemes) use ($authMechanisms) {
				return \array_filter($authMechanisms, function ($authMech) use ($schemes) {
					return \in_array($authMech->getScheme(), $schemes, true);
				});
			}));
		$this->backendService->method('getAuthMechanisms')
			->will($this->returnValue($authMechanisms));

		$sftpBackend = $this->getBackendMock('\OCA\Files_External\Lib\Backend\SFTP', '\OCA\Files_External\Lib\Storage\SFTP');
		$dummyBackend = $this->getBackendMock('\Test\Files\External\Backend\DummyBackend', '\Test\Files\External\Backend\DummyStorage');
		$this->backends = [
			'identifier:\OCA\Files_External\Lib\Backend\SMB' => $this->getBackendMock('\OCA\Files_External\Lib\Backend\SMB', '\OCA\Files_External\Lib\Storage\SMB'),
			'identifier:\OCA\Files_External\Lib\Backend\SFTP' => $sftpBackend,
			'identifier:\Test\Files\External\Backend\DummyBackend' => $dummyBackend,
			'identifier:sftp_alias' => $sftpBackend,
		];
		$this->backendService->method('getBackend')
			->will($this->returnCallback(function ($backendClass) {
				if (isset($this->backends[$backendClass])) {
					return $this->backends[$backendClass];
				}
				return null;
			}));
		$this->backendService->method('getBackends')
			->will($this->returnCallback(function () {
				// in case they changed
				return $this->backends;
			}));

		\OCP\Util::connectHook(
			Filesystem::CLASSNAME,
			Filesystem::signal_create_mount,
			\get_class($this), 'createHookCallback');
		\OCP\Util::connectHook(
			Filesystem::CLASSNAME,
			Filesystem::signal_delete_mount,
			\get_class($this), 'deleteHookCallback');

		$containerMock = $this->createMock('\OCP\AppFramework\IAppContainer');
		$containerMock->method('query')
			->will($this->returnCallback(function ($name) {
				if ($name === 'OCP\Files\External\IStoragesBackendService') {
					return $this->backendService;
				}
			}));
	}

	public function tearDown(): void {
		self::$hookCalls = [];
		if ($this->dbConfig) {
			$this->dbConfig->clean();
		}
		parent::tearDown();
	}

	protected function getBackendMock($class = '\OCA\Files_External\Lib\Backend\SMB', $storageClass = '\OCA\Files_External\Lib\Storage\SMB') {
		$backend = $this->getMockBuilder('\OCP\Files\External\Backend\Backend')
			->disableOriginalConstructor()
			->getMock();
		$backend->method('getStorageClass')
			->willReturn($storageClass);
		$backend->method('getIdentifier')
			->willReturn('identifier:' . $class);
		$backend->method('wrapStorage')
			->will($this->returnArgument(0));
		return $backend;
	}

	protected function getAuthMechMock($scheme = 'null', $class = '\OC\Files\External\Auth\NullMechanism') {
		$authMech = $this->getMockBuilder('\OCP\Files\External\Auth\AuthMechanism')
			->disableOriginalConstructor()
			->getMock();
		$authMech->method('getScheme')
			->willReturn($scheme);
		$authMech->method('getIdentifier')
			->willReturn('identifier:' . $class);
		$authMech->method('wrapStorage')
			->will($this->returnArgument(0));

		return $authMech;
	}

	/**
	 * Creates a StorageConfig instance based on array data
	 *
	 * @param array $data
	 *
	 * @return StorageConfig storage config instance
	 */
	protected function makeStorageConfig($data) {
		$storage = new StorageConfig();
		if (isset($data['id'])) {
			$storage->setId($data['id']);
		}
		$storage->setMountPoint($data['mountPoint']);
		if (!isset($data['backend'])) {
			// data providers are run before $this->backendService is initialised
			// so $data['backend'] can be specified directly
			$data['backend'] = $this->backendService->getBackend($data['backendIdentifier']);
		}
		if (!isset($data['backend'])) {
			throw new \Exception('oops, no backend');
		}
		if (!isset($data['authMechanism'])) {
			$data['authMechanism'] = $this->backendService->getAuthMechanism($data['authMechanismIdentifier']);
		}
		if (!isset($data['authMechanism'])) {
			throw new \Exception('oops, no auth mechanism');
		}
		$storage->setBackend($data['backend']);
		$storage->setAuthMechanism($data['authMechanism']);
		$storage->setBackendOptions($data['backendOptions']);
		if (isset($data['applicableUsers'])) {
			$storage->setApplicableUsers($data['applicableUsers']);
		}
		if (isset($data['applicableGroups'])) {
			$storage->setApplicableGroups($data['applicableGroups']);
		}
		if (isset($data['priority'])) {
			$storage->setPriority($data['priority']);
		}
		if (isset($data['mountOptions'])) {
			$storage->setMountOptions($data['mountOptions']);
		}
		return $storage;
	}

	/**
	 */
	public function testNonExistingStorage() {
		if ($this->getExpectedException() === null) {
			$this->expectException(\OCP\Files\External\NotFoundException::class);
		}

		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$this->service->updateStorage($storage);
	}

	public function deleteStorageDataProvider() {
		return [
			// regular case, can properly delete the oc_storages entry
			[
				[
					'share' => 'share',
					'host' => 'example.com',
					'user' => 'test',
					'password' => 'testPassword',
					'root' => 'someroot',
				],
				'smb::test@example.com/share/someroot',
				0
			],
			// special case with $user vars, cannot auto-remove the oc_storages entry
			[
				[
					'share' => 'share',
					'host' => 'example.com',
					'user' => '$user',
					'password' => 'testPassword',
					'root' => 'someroot',
				],
				'smb::someone@example.com/share/someroot',
				1
			],
		];
	}

	/**
	 * @dataProvider deleteStorageDataProvider
	 */
	public function testDeleteStorage($backendOptions, $rustyStorageId, $expectedCountAfterDeletion) {
		$backend = $this->backendService->getBackend('identifier:\Test\Files\External\Backend\DummyBackend');
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions($backendOptions);

		$newStorage = $this->service->addStorage($storage);
		$id = $newStorage->getId();

		// manually trigger storage entry because normally it happens on first
		// access, which isn't possible within this test
		$storageCache = new \OC\Files\Cache\Storage($rustyStorageId);

		// get numeric id for later check
		$numericId = $storageCache->getNumericId();

		$this->service->removeStorage($id);

		$caught = false;
		try {
			$this->service->getStorage(1);
		} catch (NotFoundException $e) {
			$caught = true;
		}

		$this->assertTrue($caught);

		// storage id was removed from oc_storages
		$qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
		$storageCheckQuery = $qb->select('*')
			->from('storages')
			->where($qb->expr()->eq('numeric_id', $qb->expr()->literal($numericId)));
		$this->assertCount($expectedCountAfterDeletion, $storageCheckQuery->execute()->fetchAll());
		Storage::remove($rustyStorageId);
	}

	/**
	 */
	public function testDeleteUnexistingStorage() {
		if ($this->getExpectedException() === null) {
			$this->expectException(\OCP\Files\External\NotFoundException::class);
		}

		$this->service->removeStorage(255);
	}

	public function testCreateStorage() {
		$mountPoint = 'mount';
		$backendIdentifier = 'identifier:\OCA\Files_External\Lib\Backend\SMB';
		$authMechanismIdentifier = 'identifier:\Auth\Mechanism';
		$backendOptions = ['param' => 'foo', 'param2' => 'bar'];
		$mountOptions = ['option' => 'foobar'];
		$applicableUsers = ['user1', 'user2'];
		$applicableGroups = ['group'];
		$priority = 123;

		$backend = $this->backendService->getBackend($backendIdentifier);
		$authMechanism = $this->backendService->getAuthMechanism($authMechanismIdentifier);

		$storage = $this->service->createStorage(
			$mountPoint,
			$backendIdentifier,
			$authMechanismIdentifier,
			$backendOptions,
			$mountOptions,
			$applicableUsers,
			$applicableGroups,
			$priority
		);

		$this->assertEquals('/' . $mountPoint, $storage->getMountPoint());
		$this->assertEquals($backend, $storage->getBackend());
		$this->assertEquals($authMechanism, $storage->getAuthMechanism());
		$this->assertEquals($backendOptions, $storage->getBackendOptions());
		$this->assertEquals($mountOptions, $storage->getMountOptions());
		$this->assertEquals($applicableUsers, $storage->getApplicableUsers());
		$this->assertEquals($applicableGroups, $storage->getApplicableGroups());
		$this->assertEquals($priority, $storage->getPriority());
	}

	/**
	 */
	public function testCreateStorageInvalidClass() {
		$storageConfig = $this->service->createStorage(
			'mount',
			'identifier:\OC\Not\A\Backend',
			'identifier:\Auth\Mechanism',
			[]
		);

		$this->assertInstanceOf(InvalidBackend::class, $storageConfig->getBackend());
	}

	/**
	 */
	public function testCreateStorageInvalidAuthMechanismClass() {
		$storageConfig = $this->service->createStorage(
			'mount',
			'identifier:\OCA\Files_External\Lib\Backend\SMB',
			'identifier:\Not\An\Auth\Mechanism',
			[]
		);
		$this->assertInstanceOf(InvalidAuth::class, $storageConfig->getAuthMechanism());
	}

	public function testGetStoragesBackendNotVisible() {
		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$backend->expects($this->once())
			->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(false);
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$authMechanism->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(true);

		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$newStorage = $this->service->addStorage($storage);

		$this->assertCount(1, $this->service->getAllStorages());
		$this->assertEmpty($this->service->getStorages());
	}

	public function testGetStoragesAuthMechanismNotVisible() {
		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$backend->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(true);
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$authMechanism->expects($this->once())
			->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(false);

		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$newStorage = $this->service->addStorage($storage);

		$this->assertCount(1, $this->service->getAllStorages());
		$this->assertEmpty($this->service->getStorages());
	}

	public static function createHookCallback($params) {
		self::$hookCalls[] = [
			'signal' => Filesystem::signal_create_mount,
			'params' => $params
		];
	}

	public static function deleteHookCallback($params) {
		self::$hookCalls[] = [
			'signal' => Filesystem::signal_delete_mount,
			'params' => $params
		];
	}

	/**
	 * Asserts hook call
	 *
	 * @param array $callData hook call data to check
	 * @param string $signal signal name
	 * @param string $mountPath mount path
	 * @param string $mountType mount type
	 * @param string $applicable applicable users
	 */
	protected function assertHookCall($callData, $signal, $mountPath, $mountType, $applicable) {
		$this->assertEquals($signal, $callData['signal']);
		$params = $callData['params'];
		$this->assertEquals(
			$mountPath,
			$params[Filesystem::signal_param_path]
		);
		$this->assertEquals(
			$mountType,
			$params[Filesystem::signal_param_mount_type]
		);
		$this->assertEquals(
			$applicable,
			$params[Filesystem::signal_param_users]
		);
	}

	public function testUpdateStorageMountPoint() {
		$backend = $this->backendService->getBackend('identifier:\Test\Files\External\Backend\DummyBackend');
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');

		$storage = new StorageConfig();
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$savedStorage = $this->service->addStorage($storage);

		$newAuthMechanism = $this->backendService->getAuthMechanism('identifier:\Other\Auth\Mechanism');

		$updatedStorage = new StorageConfig($savedStorage->getId());
		$updatedStorage->setMountPoint('mountpoint2');
		$updatedStorage->setBackend($backend);
		$updatedStorage->setAuthMechanism($newAuthMechanism);
		$updatedStorage->setBackendOptions(['password' => 'password2']);

		$this->service->updateStorage($updatedStorage);

		$savedStorage = $this->service->getStorage($updatedStorage->getId());

		$this->assertEquals('/mountpoint2', $savedStorage->getMountPoint());
		$this->assertEquals($newAuthMechanism, $savedStorage->getAuthMechanism());
		$this->assertEquals('password2', $savedStorage->getBackendOption('password'));
	}

	/**
	 */
	public function testCannotEditInvalidBackend() {
		$this->expectException(\OCP\Files\External\NotFoundException::class);

		$backend = $this->backendService->getBackend('identifier:\Test\Files\External\Backend\DummyBackend');
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');

		$storage = new StorageConfig();
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$savedStorage = $this->service->addStorage($storage);

		// make it invalid
		$this->backends['identifier:\Test\Files\External\Backend\DummyBackend'] = new InvalidBackend('identifier:\Test\Files\External\Backend\DummyBackend');

		$updatedStorage = new StorageConfig($savedStorage->getId());
		$updatedStorage->setBackendOptions(['password' => 'password2']);

		$this->service->updateStorage($updatedStorage);
	}
}
