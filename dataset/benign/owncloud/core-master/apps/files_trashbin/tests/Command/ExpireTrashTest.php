<?php
/**
 * @author Viktar Dubiniuk <dubiniuk@owncloud.com>
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

namespace OCA\Files_Trashbin\Tests\Command;

use OCA\Files_Trashbin\Command\ExpireTrash;
use OCA\Files_Trashbin\TrashExpiryManager;
use OCP\IUserManager;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class ExpireTrashTest
 *
 * @group DB
 *
 * @package OCA\Files_Trashbin\Tests\Command
 */
class ExpireTrashTest extends TestCase {

	/** @var CommandTester */
	private $commandTester;

	private $userManager;

	private $expiration;

	public function setUp(): void {
		parent::setUp();

		$this->userManager = $this->createMock(IUserManager::class);
		$this->expiration = $this->getMockBuilder(TrashExpiryManager::class)->disableOriginalConstructor()->getMock();
		$command = new ExpireTrash($this->userManager, $this->expiration);
		$this->commandTester = new CommandTester($command);
	}

	public function testExpireNoMaxRetention() {
		$this->expiration->expects($this->any())->method('retentionEnabled')
			->willReturn(false);
		$this->commandTester->execute([]);
		$output = $this->commandTester->getDisplay();
		$this->assertStringContainsString('Auto expiration is configured', $output);
	}
}
