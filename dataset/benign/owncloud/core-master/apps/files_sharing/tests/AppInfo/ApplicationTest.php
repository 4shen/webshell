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

namespace OCA\Files_Sharing\Tests\AppInfo;

use OCA\Files_Sharing\AppInfo\Application;
use OCA\Files_Sharing\Tests\TestCase;

/**
 * Class ApplicationTest
 * @group DB
 */
class ApplicationTest extends TestCase {
	public function testConstructor() {
		$app = new Application();
		$this->assertNotNull(
			$app->getContainer()->query('Share20OcsController')
		);
		$this->assertNotNull(
			$app->getContainer()->query('Hooks')
		);
	}
}
