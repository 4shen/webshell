<?php
/**
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

namespace OCA\Files_External\Tests\Command;

use OC\Files\External\Auth\NullMechanism;
use OC\Files\External\Auth\Password\Password;
use OC\Files\External\Auth\Password\SessionCredentials;
use OC\Files\External\StorageConfig;
use OCA\Files_External\Command\ListCommand;
use OCA\Files_External\Lib\Backend\Local;
use OCP\Files\External\Backend\InvalidBackend;
use Symfony\Component\Console\Output\BufferedOutput;

class ListCommandTest extends CommandTest {
	/**
	 * @return \OCA\Files_External\Command\ListCommand|\PHPUnit\Framework\MockObject\MockObject
	 */
	private function getInstance() {
		/** @var \OCP\Files\External\Service\IGlobalStoragesService|\PHPUnit\Framework\MockObject\MockObject $globalService */
		$globalService = $this->createMock('\OCP\Files\External\Service\IGlobalStoragesService');
		/** @var \OC\Files\External\Service\IUserStoragesService|\PHPUnit\Framework\MockObject\MockObject $userService */
		$userService = $this->createMock('\OCP\Files\External\Service\IUserStoragesService');
		/** @var \OCP\IUserManager|\PHPUnit\Framework\MockObject\MockObject $userManager */
		$userManager = $this->createMock('\OCP\IUserManager');
		/** @var \OCP\IUserSession|\PHPUnit\Framework\MockObject\MockObject $userSession */
		$userSession = $this->createMock('\OCP\IUserSession');

		return new ListCommand($globalService, $userService, $userSession, $userManager);
	}

	public function testListAuthIdentifier() {
		$l10n = $this->createMock('\OCP\IL10N', null, [], '', false);
		$session = $this->createMock('\OCP\ISession');
		$crypto = $this->createMock('\OCP\Security\ICrypto');
		$instance = $this->getInstance();
		// FIXME: use mock of IStorageConfig
		$mount1 = new StorageConfig();
		$mount1->setAuthMechanism(new Password());
		$mount1->setBackend(new Local($l10n, new NullMechanism()));
		$mount2 = new StorageConfig();
		$mount2->setAuthMechanism(new SessionCredentials($session, $crypto));
		$mount2->setBackend(new Local($l10n, new NullMechanism()));
		$input = $this->getInput($instance, [], [
			'output' => 'json'
		]);
		$output = new BufferedOutput();

		$instance->listMounts('', [$mount1, $mount2], $input, $output);
		$output = \json_decode($output->fetch(), true);

		$this->assertNotEquals($output[0]['authentication_type'], $output[1]['authentication_type']);
	}

	public function testDisplayWarningForIncomplete() {
		$l10n = $this->createMock('\OCP\IL10N', null, [], '', false);
		$session = $this->createMock('\OCP\ISession');
		$crypto = $this->createMock('\OCP\Security\ICrypto');
		$instance = $this->getInstance();
		// FIXME: use mock of IStorageConfig
		$mount1 = new StorageConfig();
		$mount1->setAuthMechanism(new Password());
		$mount1->setBackend(new InvalidBackend('InvalidId'));
		$mount2 = new StorageConfig();
		$mount2->setAuthMechanism(new SessionCredentials($session, $crypto));
		$mount2->setBackend(new Local($l10n, new NullMechanism()));
		$input = $this->getInput($instance);
		$output = new BufferedOutput();

		$instance->listMounts('', [$mount1, $mount2], $input, $output);
		$output = $output->fetch();

		$this->assertRegexp('/Number of invalid storages found/', $output);
	}

	public function providesShortView() {
		return [
			[
				['short' => true, 'output' => 'json'],
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'auth' => 'User', 'type' => 'Admin'],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'auth' => 'User', 'type' => 'Personal'],
				],
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'auth' => 'User', 'type' => StorageConfig::MOUNT_TYPE_ADMIN],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'auth' => 'User', 'type' => StorageConfig::MOUNT_TYPE_PERSONAl],
				]
			],
			[
				['short' => true],
				<<<EOS
+----------+-------------+------+----------+
| Mount ID | Mount Point | Auth | Type     |
+----------+-------------+------+----------+
| 1        | /ownCloud   | User | Admin    |
| 2        | /SFTP       | User | Personal |
+----------+-------------+------+----------+

EOS
				,
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'auth' => 'User', 'type' => StorageConfig::MOUNT_TYPE_ADMIN],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'auth' => 'User', 'type' => StorageConfig::MOUNT_TYPE_PERSONAl],
				]
			],
		];
	}

	/**
	 * @dataProvider providesShortView
	 * @param $options
	 * @param $expectedResult
	 * @param $mountOptions
	 */
	public function testShortView($options, $expectedResult, $mountOptions) {
		$l10n = $this->createMock('\OCP\IL10N', null, [], '', false);
		$session = $this->createMock('\OCP\ISession');
		$crypto = $this->createMock('\OCP\Security\ICrypto');
		$instance = $this->getInstance();
		// FIXME: use mock of IStorageConfig
		$mount1 = new StorageConfig();
		$mount1->setId($mountOptions[0]['mount_id']);
		$mount1->setMountPoint($mountOptions[0]['mount_point']);
		$mount1->setType($mountOptions[0]['type']);
		$mount1->setAuthMechanism(new Password());
		$mount1->setBackend(new Local($l10n, new NullMechanism()));
		$mount2 = new StorageConfig();
		$mount2->setId($mountOptions[1]['mount_id']);
		$mount2->setMountPoint($mountOptions[1]['mount_point']);
		$mount2->setType($mountOptions[1]['type']);
		$mount2->setAuthMechanism(new SessionCredentials($session, $crypto));
		$mount2->setBackend(new Local($l10n, new NullMechanism()));
		$input = $this->getInput($instance, ['user_id' => 'user1'], $options);
		$output = new BufferedOutput();

		$instance->listMounts('user1', [$mount1, $mount2], $input, $output);
		$output = $output->fetch();
		if (isset($options['output']) && ($options['output'] === 'json')) {
			$results = \json_decode($output, true);
			$countResults = \count($results);

			for ($i = 0; $i < $countResults; $i++) {
				$this->assertEquals($expectedResult[$i]['mount_id'], $results[$i]['mount_id']);
				$this->assertEquals($expectedResult[$i]['mount_point'], $results[$i]['mount_point']);
				$this->assertStringContainsString($results[$i]['auth'], 'UserSession', true);
				$this->assertEquals($expectedResult[$i]['type'], $results[$i]['type']);
			}
		} else {
			$this->assertEquals($expectedResult, $output);
		}
	}

	public function providesLongView() {
		return [
			[
				['short' => false, 'output' => 'json'],
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'storage' => 'Local', 'authentication_type' => 'Username and password'],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'storage' => 'Local', 'authentication_type' => 'Log-in credentials, save in session'],
				],
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud'],
					['mount_id' => 2, 'mount_point' => '/SFTP'],
				]
			],
			[
				['short' => false],
				<<<EOS
+----------+-------------+---------+-------------------------------------+---------------+---------+
| Mount ID | Mount Point | Storage | Authentication Type                 | Configuration | Options |
+----------+-------------+---------+-------------------------------------+---------------+---------+
| 1        | /ownCloud   |         | Username and password               |               |         |
| 2        | /SFTP       |         | Log-in credentials, save in session |               |         |
+----------+-------------+---------+-------------------------------------+---------------+---------+

EOS
				,
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'storage' => 'Local'],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'storage' => 'Local'],
				]
			],
		];
	}

	/**
	 * @dataProvider providesLongView
	 * @param $options
	 * @param $expectedResult
	 * @param $mountOptions
	 */
	public function testLongView($options, $expectedResult, $mountOptions) {
		$l10n = $this->createMock('\OCP\IL10N', null, [], '', false);
		$session = $this->createMock('\OCP\ISession');
		$crypto = $this->createMock('\OCP\Security\ICrypto');
		$instance = $this->getInstance();
		// FIXME: use mock of IStorageConfig
		$mount1 = new StorageConfig();
		$mount1->setId($mountOptions[0]['mount_id']);
		$mount1->setMountPoint($mountOptions[0]['mount_point']);
		$mount1->setAuthMechanism(new Password());
		$mount1->setBackend(new Local($l10n, new NullMechanism()));
		$mount2 = new StorageConfig();
		$mount2->setId($mountOptions[1]['mount_id']);
		$mount2->setMountPoint($mountOptions[1]['mount_point']);
		$mount2->setAuthMechanism(new SessionCredentials($session, $crypto));
		$mount2->setBackend(new Local($l10n, new NullMechanism()));
		$input = $this->getInput($instance, ['user_id' => 'user1'], $options);
		$output = new BufferedOutput();

		$instance->listMounts('user1', [$mount1, $mount2], $input, $output);
		$output = $output->fetch();
		if (isset($options['output']) && ($options['output'] === 'json')) {
			$results = \json_decode($output, true);
			$countResults = \count($results);

			for ($i = 0; $i < $countResults; $i++) {
				$this->assertEquals($expectedResult[$i]['mount_id'], $results[$i]['mount_id']);
				$this->assertEquals($expectedResult[$i]['mount_point'], $results[$i]['mount_point']);
				$this->assertEquals($expectedResult[$i]['authentication_type'], $results[$i]['authentication_type']);
			}
		} else {
			$this->assertEquals($expectedResult, $output);
		}
	}
	public function providesImportableFormat() {
		return [
			[
				['importable-format' => true, 'short' => false, 'output' => 'json'],
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'storage' => 'Local', 'authentication_type' => 'password::password'],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'storage' => 'Local', 'authentication_type' => 'password::sessioncredentials'],
				],
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud'],
					['mount_id' => 2, 'mount_point' => '/SFTP'],
				]
			],
			[
				['importable-format' => true, 'short' => false],
				<<<EOS
+----------+-------------+-------------------------+------------------------------+---------------+---------+
| Mount ID | Mount Point | Storage                 | Authentication Type          | Configuration | Options |
+----------+-------------+-------------------------+------------------------------+---------------+---------+
| 1        | /ownCloud   | \OC\Files\Storage\Local | password::password           |               |         |
| 2        | /SFTP       | \OC\Files\Storage\Local | password::sessioncredentials |               |         |
+----------+-------------+-------------------------+------------------------------+---------------+---------+

EOS
				,
				[
					['mount_id' => 1, 'mount_point' => '/ownCloud', 'storage' => 'Local'],
					['mount_id' => 2, 'mount_point' => '/SFTP', 'storage' => 'Local'],
				]
			],
		];
	}

	/**
	 * @dataProvider providesImportableFormat
	 * @param $options
	 * @param $expectedResult
	 * @param $mountOptions
	 */
	public function testImportableFormat($options, $expectedResult, $mountOptions) {
		$l10n = $this->createMock('\OCP\IL10N', null, [], '', false);
		$session = $this->createMock('\OCP\ISession');
		$crypto = $this->createMock('\OCP\Security\ICrypto');
		$instance = $this->getInstance();
		// FIXME: use mock of IStorageConfig
		$mount1 = new StorageConfig();
		$mount1->setId($mountOptions[0]['mount_id']);
		$mount1->setMountPoint($mountOptions[0]['mount_point']);
		$mount1->setAuthMechanism(new Password());
		$mount1->setBackend(new Local($l10n, new NullMechanism()));
		$mount2 = new StorageConfig();
		$mount2->setId($mountOptions[1]['mount_id']);
		$mount2->setMountPoint($mountOptions[1]['mount_point']);
		$mount2->setAuthMechanism(new SessionCredentials($session, $crypto));
		$mount2->setBackend(new Local($l10n, new NullMechanism()));
		$input = $this->getInput($instance, ['user_id' => 'user1'], $options);
		$output = new BufferedOutput();

		$instance->listMounts('user1', [$mount1, $mount2], $input, $output);
		$output = $output->fetch();
		if (isset($options['output']) && ($options['output'] === 'json')) {
			$results = \json_decode($output, true);
			$countResults = \count($results);

			for ($i = 0; $i < $countResults; $i++) {
				$this->assertEquals($expectedResult[$i]['mount_id'], $results[$i]['mount_id']);
				$this->assertEquals($expectedResult[$i]['mount_point'], $results[$i]['mount_point']);
				$this->assertEquals($expectedResult[$i]['authentication_type'], $results[$i]['authentication_type']);
			}
		} else {
			$this->assertEquals($expectedResult, $output);
		}
	}
}
