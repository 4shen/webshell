<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace Tests\Core\Command\Group;

use OC\Core\Command\Group\RemoveMember;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;
use Test\Traits\UserTrait;

/**
 * Class RemoveMemberTest
 *
 * @group DB
 */
class RemoveMemberTest extends TestCase {
	use UserTrait;

	/** @var CommandTester */
	private $commandTester;

	protected function setUp(): void {
		parent::setUp();

		$command = new RemoveMember(\OC::$server->getGroupManager(), \OC::$server->getUserManager());
		$this->commandTester = new CommandTester($command);

		$user1 = $this->createUser('user1');
		$this->createUser('user2');
		\OC::$server->getGroupManager()->createGroup('group1');
		\OC::$server->getGroupManager()->get('group1')->addUser($user1);
	}

	/**
	 * @dataProvider inputProvider
	 * @param array $input
	 * @param string $expectedOutput
	 */
	public function testCommandInput($input, $expectedOutput) {
		$this->commandTester->execute($input);
		$output = $this->commandTester->getDisplay();
		$this->assertStringContainsString($expectedOutput, $output);
	}

	public function inputProvider() {
		return [
			[['group' => 'groupUnknown', '--member' => ['user2']], 'does not exist'],
			[['group' => 'group1', '--member' => []], 'No members specified'],
			[['group' => 'group1', '--member' => ['user1']], 'removed from group'],
			[['group' => 'group1', '--member' => ['user2']], 'could not be found in group'],
			[['group' => 'group1', '--member' => ['userUnknown']], 'does not exist - not removed from group'],
		];
	}
}
