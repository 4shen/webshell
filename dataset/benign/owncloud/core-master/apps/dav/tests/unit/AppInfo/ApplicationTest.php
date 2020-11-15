<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OCA\DAV\Tests\unit\AppInfo;

use OCA\DAV\AppInfo\Application;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\CardDAV\ContactsManager;
use OCP\Contacts\IManager;
use Test\TestCase;

/**
 * Class ApplicationTest
 *
 * @group DB
 *
 * @package OCA\DAV\Tests\Unit\AppInfo
 */
class ApplicationTest extends TestCase {
	public function test() {
		$app = new Application();
		$c = $app->getContainer();

		// assert service instances in the container are properly setup
		$s = $c->query(ContactsManager::class);
		$this->assertInstanceOf('OCA\DAV\CardDAV\ContactsManager', $s);
		$s = $c->query(CardDavBackend::class);
		$this->assertInstanceOf('OCA\DAV\CardDAV\CardDavBackend', $s);
	}

	public function testContactsManagerSetup() {
		$app = new Application();
		$c = $app->getContainer();
		$c->registerService(CardDavBackend::class, function ($c) {
			$service = $this->getMockBuilder('OCA\DAV\CardDAV\CardDavBackend')->disableOriginalConstructor()->getMock();
			$service->method('getAddressBooksForUser')->willReturn([]);
			return $service;
		});

		// assert setupContactsProvider() is proper
		/** @var IManager | \PHPUnit\Framework\MockObject\MockObject $cm */
		$cm = $this->getMockBuilder('OCP\Contacts\IManager')->disableOriginalConstructor()->getMock();
		$app->setupContactsProvider($cm, 'xxx');
		$this->assertTrue(true);
	}
}
