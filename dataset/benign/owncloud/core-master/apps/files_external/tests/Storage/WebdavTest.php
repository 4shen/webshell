<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OCA\Files_External\Tests\Storage;

use OC\Files\Storage\DAV;

/**
 * Class WebdavTest
 *
 * @group DB
 *
 * @package OCA\Files_External\Tests\Storage
 */
class WebdavTest extends \Test\Files\Storage\Storage {
	protected function setUp(): void {
		parent::setUp();

		$id = $this->getUniqueID();
		$config = include('files_external/tests/config.webdav.php');
		if (! \is_array($config) or !$config['run']) {
			$this->markTestSkipped('WebDAV backend not configured');
		}
		if (isset($config['wait'])) {
			$this->waitDelay = $config['wait'];
		}
		$config['root'] .= '/' . $id; //make sure we have an new empty folder to work in
		$this->instance = new DAV($config);
		$this->instance->mkdir('/');
	}

	protected function tearDown(): void {
		if ($this->instance) {
			$this->instance->rmdir('/');
		}

		parent::tearDown();
	}
}
