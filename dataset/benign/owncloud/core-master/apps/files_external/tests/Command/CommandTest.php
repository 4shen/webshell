<?php
/**
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

namespace OCA\Files_External\Tests\Command;

use OC\Files\External\StorageConfig;
use OCP\Files\External\IStorageConfig;
use OCP\Files\External\NotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\BufferedOutput;
use Test\TestCase;

abstract class CommandTest extends TestCase {
	/**
	 * @param IStorageConfig[] $mounts
	 * @return \OCP\Files\External\Service\IGlobalStoragesService|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected function getGlobalStorageService(array $mounts = []) {
		$mock = $this->createMock('OCP\Files\External\Service\IGlobalStoragesService');

		$this->bindMounts($mock, $mounts);

		return $mock;
	}

	/**
	 * @param \PHPUnit\Framework\MockObject\MockObject $mock
	 * @param IStorageConfig[] $mounts
	 */
	protected function bindMounts(\PHPUnit\Framework\MockObject\MockObject $mock, array $mounts) {
		$mock->expects($this->any())
			->method('getStorage')
			->will($this->returnCallback(function ($id) use ($mounts) {
				foreach ($mounts as $mount) {
					if ($mount->getId() === $id) {
						return $mount;
					}
				}
				throw new NotFoundException();
			}));
	}

	/**
	 * @param $id
	 * @param $mountPoint
	 * @param $backendClass
	 * @param string $applicableIdentifier
	 * @param array $config
	 * @param array $options
	 * @param array $users
	 * @param array $groups
	 * @return IStorageConfig
	 */
	protected function getMount($id, $mountPoint, $backendClass, $applicableIdentifier = 'password::password', $config = [], $options = [], $users = [], $groups = []) {
		// FIXME: use mock
		$mount = new StorageConfig($id);

		$mount->setMountPoint($mountPoint);
		$mount->setBackendOptions($config);
		$mount->setMountOptions($options);
		$mount->setApplicableUsers($users);
		$mount->setApplicableGroups($groups);

		return $mount;
	}

	protected function getInput(Command $command, array $arguments = [], array $options = []) {
		$input = new ArrayInput([]);
		$input->bind($command->getDefinition());
		foreach ($arguments as $key => $value) {
			$input->setArgument($key, $value);
		}
		foreach ($options as $key => $value) {
			$input->setOption($key, $value);
		}
		return $input;
	}

	protected function executeCommand(Command $command, Input $input) {
		$output = new BufferedOutput();
		$this->invokePrivate($command, 'execute', [$input, $output]);
		return $output->fetch();
	}
}
