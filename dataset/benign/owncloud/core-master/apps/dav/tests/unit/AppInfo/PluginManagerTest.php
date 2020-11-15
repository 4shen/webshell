<?php
/**
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

namespace OCA\DAV\Tests\unit\AppInfo;

use OC\ServerContainer;
use OCA\DAV\AppInfo\PluginManager;
use OCP\App\IAppManager;
use Test\TestCase;

/**
 * Class PluginManagerTest
 *
 * @package OCA\DAV\Tests\Unit\AppInfo
 */
class PluginManagerTest extends TestCase {
	public function test() {
		$server = $this->createMock(ServerContainer::class);

		$appManager = $this->createMock(IAppManager::class);
		$appManager->method('getInstalledApps')
			->willReturn(['adavapp', 'adavapp2']);

		$appInfo1 = [
			'types' => ['dav'],
			'sabre' => [
				'plugins' => [
					'plugin' => [
						'\OCA\DAV\ADavApp\PluginOne',
						'\OCA\DAV\ADavApp\PluginTwo',
					],
				],
				'collections' => [
					'collection' => [
						'\OCA\DAV\ADavApp\CollectionOne',
						'\OCA\DAV\ADavApp\CollectionTwo',
					]
				],
			],
		];
		$appInfo2 = [
			'types' => ['logging', 'dav'],
			'sabre' => [
				'plugins' => [
					'plugin' => '\OCA\DAV\ADavApp2\PluginOne',
				],
				'collections' => [
					'collection' => '\OCA\DAV\ADavApp2\CollectionOne',
				],
			],
		];

		$appManager->method('getAppInfo')
			->will($this->returnValueMap([
				['adavapp', $appInfo1],
				['adavapp2', $appInfo2],
			]));

		$pluginManager = new PluginManager($server, $appManager);

		$server->method('query')
			->will($this->returnValueMap([
				['\OCA\DAV\ADavApp\PluginOne', 'dummyplugin1'],
				['\OCA\DAV\ADavApp\PluginTwo', 'dummyplugin2'],
				['\OCA\DAV\ADavApp\CollectionOne', 'dummycollection1'],
				['\OCA\DAV\ADavApp\CollectionTwo', 'dummycollection2'],
				['\OCA\DAV\ADavApp2\PluginOne', 'dummy2plugin1'],
				['\OCA\DAV\ADavApp2\CollectionOne', 'dummy2collection1'],
			]));

		$expectedPlugins = [
			'dummyplugin1',
			'dummyplugin2',
			'dummy2plugin1',
		];
		$expectedCollections = [
			'dummycollection1',
			'dummycollection2',
			'dummy2collection1',
		];

		$this->assertEquals($expectedPlugins, $pluginManager->getAppPlugins());
		$this->assertEquals($expectedCollections, $pluginManager->getAppCollections());
	}
}
