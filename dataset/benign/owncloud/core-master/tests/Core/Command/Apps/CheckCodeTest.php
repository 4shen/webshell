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

namespace Tests\Core\Command\Config;

use OC\App\InfoParser;
use OC\Core\Command\App\CheckCode;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class CheckCodeTest
 *
 * @group DB
 */
class CheckCodeTest extends TestCase {
	/** @var CommandTester */
	private $commandTester;

	/**
	 */
	public function testWrongAppId() {
		$this->expectException(\RuntimeException::class);

		$command = new CheckCode(new InfoParser, \OC::$server->getAppManager());
		$this->commandTester = new CommandTester($command);
		$this->commandTester->execute(['app-id' => 'hui-buh']);
	}
}
