<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Tom Needham <tom@owncloud.com>
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

namespace OCA\Provisioning_API\Tests;

use OCA\Provisioning_API\Apps;
use OCP\API;
use OCP\App\IAppManager;
use OCP\IUserSession;

/**
 * Class AppsTest
 *
 * @group DB
 *
 * @package OCA\Provisioning_API\Tests
 */
class AppsTest extends TestCase {
	/** @var IAppManager */
	private $appManager;
	/** @var Apps */
	private $api;
	/** @var IUserSession */
	private $userSession;

	protected function setUp(): void {
		parent::setUp();

		$this->appManager = \OC::$server->getAppManager();
		$this->groupManager = \OC::$server->getGroupManager();
		$this->userSession = \OC::$server->getUserSession();
		$this->api = new Apps($this->appManager);
	}

	public function testGetAppInfo() {
		$result = $this->api->getAppInfo(['appid' => 'provisioning_api']);
		$this->assertInstanceOf('OC_OCS_Result', $result);
		$this->assertTrue($result->succeeded());
	}

	public function testGetAppInfoOnBadAppID() {
		$result = $this->api->getAppInfo(['appid' => 'not_provisioning_api']);
		$this->assertInstanceOf('OC_OCS_Result', $result);
		$this->assertFalse($result->succeeded());
		$this->assertEquals(API::RESPOND_NOT_FOUND, $result->getStatusCode());
	}

	public function testGetApps() {
		$user = $this->generateUsers();
		$this->groupManager->get('admin')->addUser($user);
		$this->userSession->setUser($user);

		$result = $this->api->getApps([]);

		$this->assertTrue($result->succeeded());
		$data = $result->getData();
		$this->assertCount(\count(\OC_App::listAllApps(false, true)), $data['apps']);
	}

	public function testGetAppsEnabled() {
		$_GET['filter'] = 'enabled';
		$result = $this->api->getApps(['filter' => 'enabled']);
		$this->assertTrue($result->succeeded());
		$data = $result->getData();
		$this->assertCount(\count(\OC_App::getEnabledApps()), $data['apps']);
	}

	public function testGetAppsDisabled() {
		$_GET['filter'] = 'disabled';
		$result = $this->api->getApps(['filter' => 'disabled']);
		$this->assertTrue($result->succeeded());
		$data = $result->getData();
		$apps = \OC_App::listAllApps(false, true);
		$list =  [];
		foreach ($apps as $app) {
			$list[] = $app['id'];
		}
		$disabled = \array_diff($list, \OC_App::getEnabledApps());
		$this->assertCount(\count($disabled), $data['apps']);
	}

	public function testGetAppsInvalidFilter() {
		$_GET['filter'] = 'foo';
		$result = $this->api->getApps([]);
		$this->assertFalse($result->succeeded());
		$this->assertEquals(101, $result->getStatusCode());
	}
}
