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

use OC\Files\External\Service\GlobalStoragesService;
use OC\Files\Filesystem;
use OCP\Files\External\IStorageConfig;

/**
 * @group DB
 */
class GlobalStoragesServiceTest extends StoragesServiceTest {
	public function setUp(): void {
		parent::setUp();
		$this->service = new GlobalStoragesService($this->backendService, $this->dbConfig, $this->mountCache);
	}

	public function tearDown(): void {
		@\unlink($this->dataDir . '/mount.json');
		parent::tearDown();
	}

	protected function makeTestStorageData() {
		return $this->makeStorageConfig([
			'mountPoint' => 'mountpoint',
			'backendIdentifier' => 'identifier:\Test\Files\External\Backend\DummyBackend',
			'authMechanismIdentifier' => 'identifier:\Auth\Mechanism',
			'backendOptions' => [
				'option1' => 'value1',
				'option2' => 'value2',
				'password' => 'testPassword',
			],
			'applicableUsers' => [],
			'applicableGroups' => [],
			'priority' => 15,
			'mountOptions' => [
				'preview' => false,
			]
		]);
	}

	public function storageDataProvider() {
		return [
			// all users
			[
				[
					'mountPoint' => 'mountpoint',
					'backendIdentifier' => 'identifier:\Test\Files\External\Backend\DummyBackend',
					'authMechanismIdentifier' => 'identifier:\Auth\Mechanism',
					'backendOptions' => [
						'option1' => 'value1',
						'option2' => 'value2',
						'password' => 'testPassword',
					],
					'applicableUsers' => [],
					'applicableGroups' => [],
					'priority' => 15,
				],
			],
			// some users
			[
				[
					'mountPoint' => 'mountpoint',
					'backendIdentifier' => 'identifier:\Test\Files\External\Backend\DummyBackend',
					'authMechanismIdentifier' => 'identifier:\Auth\Mechanism',
					'backendOptions' => [
						'option1' => 'value1',
						'option2' => 'value2',
						'password' => 'testPassword',
					],
					'applicableUsers' => ['user1', 'user2'],
					'applicableGroups' => [],
					'priority' => 15,
				],
			],
			// some groups
			[
				[
					'mountPoint' => 'mountpoint',
					'backendIdentifier' => 'identifier:\Test\Files\External\Backend\DummyBackend',
					'authMechanismIdentifier' => 'identifier:\Auth\Mechanism',
					'backendOptions' => [
						'option1' => 'value1',
						'option2' => 'value2',
						'password' => 'testPassword',
					],
					'applicableUsers' => [],
					'applicableGroups' => ['group1', 'group2'],
					'priority' => 15,
				],
			],
			// both users and groups
			[
				[
					'mountPoint' => 'mountpoint',
					'backendIdentifier' => 'identifier:\Test\Files\External\Backend\DummyBackend',
					'authMechanismIdentifier' => 'identifier:\Auth\Mechanism',
					'backendOptions' => [
						'option1' => 'value1',
						'option2' => 'value2',
						'password' => 'testPassword',
					],
					'applicableUsers' => ['user1', 'user2'],
					'applicableGroups' => ['group1', 'group2'],
					'priority' => 15,
				],
			],
		];
	}

	private function assertEqualsArrays($expected, $actual) {
		\sort($expected);
		\sort($actual);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider storageDataProvider
	 */
	public function testAddStorage($storageParams) {
		$storage = $this->makeStorageConfig($storageParams);
		$newStorage = $this->service->addStorage($storage);

		$baseId = $newStorage->getId();

		$newStorage = $this->service->getStorage($baseId);

		$this->assertEquals($storage->getMountPoint(), $newStorage->getMountPoint());
		$this->assertEquals($storage->getBackend(), $newStorage->getBackend());
		$this->assertEquals($storage->getAuthMechanism(), $newStorage->getAuthMechanism());
		$this->assertEqualsArrays($storage->getBackendOptions(), $newStorage->getBackendOptions());
		$this->assertEqualsArrays($storage->getApplicableUsers(), $newStorage->getApplicableUsers());
		$this->assertEqualsArrays($storage->getApplicableGroups(), $newStorage->getApplicableGroups());
		$this->assertEquals($storage->getPriority(), $newStorage->getPriority());
		$this->assertEquals(0, $newStorage->getStatus());

		$nextStorage = $this->service->addStorage($storage);
		$this->assertEquals($baseId + 1, $nextStorage->getId());
	}

	/**
	 * @dataProvider storageDataProvider
	 */
	public function testUpdateStorage($updatedStorageParams) {
		$updatedStorage = $this->makeStorageConfig($updatedStorageParams);
		$storage = $this->makeStorageConfig([
			'mountPoint' => 'mountpoint',
			'backendIdentifier' => 'identifier:\Test\Files\External\Backend\DummyBackend',
			'authMechanismIdentifier' => 'identifier:\Auth\Mechanism',
			'backendOptions' => [
				'option1' => 'value1',
				'option2' => 'value2',
				'password' => 'testPassword',
			],
			'applicableUsers' => [],
			'applicableGroups' => [],
			'priority' => 15,
		]);

		$newStorage = $this->service->addStorage($storage);
		$id = $newStorage->getId();

		$updatedStorage->setId($id);

		$this->service->updateStorage($updatedStorage);
		$newStorage = $this->service->getStorage($id);

		$this->assertEquals($updatedStorage->getMountPoint(), $newStorage->getMountPoint());
		$this->assertEquals($updatedStorage->getBackendOptions()['password'], $newStorage->getBackendOptions()['password']);
		$this->assertEqualsArrays($updatedStorage->getApplicableUsers(), $newStorage->getApplicableUsers());
		$this->assertEqualsArrays($updatedStorage->getApplicableGroups(), $newStorage->getApplicableGroups());
		$this->assertEquals($updatedStorage->getPriority(), $newStorage->getPriority());
		$this->assertEquals(0, $newStorage->getStatus());
	}

	public function hooksAddStorageDataProvider() {
		return [
			// applicable all
			[
				[],
				[],
				// expected hook calls
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'all'
					],
				],
			],
			// single user
			[
				['user1'],
				[],
				// expected hook calls
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user1',
					],
				],
			],
			// single group
			[
				[],
				['group1'],
				// expected hook calls
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group1',
					],
				],
			],
			// multiple users
			[
				['user1', 'user2'],
				[],
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user1',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user2',
					],
				],
			],
			// multiple groups
			[
				[],
				['group1', 'group2'],
				// expected hook calls
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group1'
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group2'
					],
				],
			],
			// mixed groups and users
			[
				['user1', 'user2'],
				['group1', 'group2'],
				// expected hook calls
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user1',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user2',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group1'
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group2'
					],
				],
			],
		];
	}

	/**
	 * @dataProvider hooksAddStorageDataProvider
	 */
	public function testHooksAddStorage($applicableUsers, $applicableGroups, $expectedCalls) {
		$storage = $this->makeTestStorageData();
		$storage->setApplicableUsers($applicableUsers);
		$storage->setApplicableGroups($applicableGroups);
		$this->service->addStorage($storage);

		$this->assertCount(\count($expectedCalls), self::$hookCalls);

		foreach ($expectedCalls as $index => $call) {
			$this->assertHookCall(
				self::$hookCalls[$index],
				$call[0],
				$storage->getMountPoint(),
				$call[1],
				$call[2]
			);
		}
	}

	public function hooksUpdateStorageDataProvider() {
		return [
			[
				// nothing to multiple users and groups
				[],
				[],
				['user1', 'user2'],
				['group1', 'group2'],
				// expected hook calls
				[
					// delete the "all entry"
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'all',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user1',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user2',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group1'
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group2'
					],
				],
			],
			[
				// adding a user and a group
				['user1'],
				['group1'],
				['user1', 'user2'],
				['group1', 'group2'],
				// expected hook calls
				[
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user2',
					],
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group2'
					],
				],
			],
			[
				// removing a user and a group
				['user1', 'user2'],
				['group1', 'group2'],
				['user1'],
				['group1'],
				// expected hook calls
				[
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user2',
					],
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group2'
					],
				],
			],
			[
				// removing all
				['user1'],
				['group1'],
				[],
				[],
				// expected hook calls
				[
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user1',
					],
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group1'
					],
					// create the "all" entry
					[
						Filesystem::signal_create_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'all'
					],
				],
			],
			[
				// no changes
				['user1'],
				['group1'],
				['user1'],
				['group1'],
				// no hook calls
				[]
			]
		];
	}

	/**
	 * @dataProvider hooksUpdateStorageDataProvider
	 */
	public function testHooksUpdateStorage(
		$sourceApplicableUsers,
		$sourceApplicableGroups,
		$updatedApplicableUsers,
		$updatedApplicableGroups,
		$expectedCalls) {
		$storage = $this->makeTestStorageData();
		$storage->setApplicableUsers($sourceApplicableUsers);
		$storage->setApplicableGroups($sourceApplicableGroups);
		$storage = $this->service->addStorage($storage);

		$storage->setApplicableUsers($updatedApplicableUsers);
		$storage->setApplicableGroups($updatedApplicableGroups);

		// reset calls
		self::$hookCalls = [];

		$this->service->updateStorage($storage);

		$this->assertCount(\count($expectedCalls), self::$hookCalls);

		foreach ($expectedCalls as $index => $call) {
			$this->assertHookCall(
				self::$hookCalls[$index],
				$call[0],
				'/mountpoint',
				$call[1],
				$call[2]
			);
		}
	}

	/**
	 */
	public function testHooksRenameMountPoint() {
		$storage = $this->makeTestStorageData();
		$storage->setApplicableUsers(['user1', 'user2']);
		$storage->setApplicableGroups(['group1', 'group2']);
		$storage = $this->service->addStorage($storage);

		$storage->setMountPoint('renamedMountpoint');

		// reset calls
		self::$hookCalls = [];

		$this->service->updateStorage($storage);

		$expectedCalls = [
			// deletes old mount
			[
				Filesystem::signal_delete_mount,
				'/mountpoint',
				IStorageConfig::MOUNT_TYPE_USER,
				'user1',
			],
			[
				Filesystem::signal_delete_mount,
				'/mountpoint',
				IStorageConfig::MOUNT_TYPE_USER,
				'user2',
			],
			[
				Filesystem::signal_delete_mount,
				'/mountpoint',
				IStorageConfig::MOUNT_TYPE_GROUP,
				'group1',
			],
			[
				Filesystem::signal_delete_mount,
				'/mountpoint',
				IStorageConfig::MOUNT_TYPE_GROUP,
				'group2',
			],
			// creates new one
			[
				Filesystem::signal_create_mount,
				'/renamedMountpoint',
				IStorageConfig::MOUNT_TYPE_USER,
				'user1',
			],
			[
				Filesystem::signal_create_mount,
				'/renamedMountpoint',
				IStorageConfig::MOUNT_TYPE_USER,
				'user2',
			],
			[
				Filesystem::signal_create_mount,
				'/renamedMountpoint',
				IStorageConfig::MOUNT_TYPE_GROUP,
				'group1',
			],
			[
				Filesystem::signal_create_mount,
				'/renamedMountpoint',
				IStorageConfig::MOUNT_TYPE_GROUP,
				'group2',
			],
		];

		$this->assertCount(\count($expectedCalls), self::$hookCalls);

		foreach ($expectedCalls as $index => $call) {
			$this->assertHookCall(
				self::$hookCalls[$index],
				$call[0],
				$call[1],
				$call[2],
				$call[3]
			);
		}
	}

	public function hooksDeleteStorageDataProvider() {
		return [
			[
				['user1', 'user2'],
				['group1', 'group2'],
				// expected hook calls
				[
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user1',
					],
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'user2',
					],
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group1'
					],
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_GROUP,
						'group2'
					],
				],
			],
			[
				// deleting "all" entry
				[],
				[],
				[
					[
						Filesystem::signal_delete_mount,
						IStorageConfig::MOUNT_TYPE_USER,
						'all',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider hooksDeleteStorageDataProvider
	 */
	public function testHooksDeleteStorage(
		$sourceApplicableUsers,
		$sourceApplicableGroups,
		$expectedCalls) {
		$storage = $this->makeTestStorageData();
		$storage->setApplicableUsers($sourceApplicableUsers);
		$storage->setApplicableGroups($sourceApplicableGroups);
		$storage = $this->service->addStorage($storage);

		// reset calls
		self::$hookCalls = [];

		$this->service->removeStorage($storage->getId());

		$this->assertCount(\count($expectedCalls), self::$hookCalls);

		foreach ($expectedCalls as $index => $call) {
			$this->assertHookCall(
				self::$hookCalls[$index],
				$call[0],
				'/mountpoint',
				$call[1],
				$call[2]
			);
		}
	}
}
