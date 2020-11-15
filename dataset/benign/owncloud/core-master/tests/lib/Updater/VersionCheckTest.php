<?php
/**
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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

namespace Test\Updater;

use OC\Updater\VersionCheck;
use OCP\IConfig;
use OCP\Util;
use Test\TestCase;

class VersionCheckTest extends TestCase {
	/** @var IConfig| \PHPUnit\Framework\MockObject\MockObject */
	private $config;
	/** @var VersionCheck | \PHPUnit\Framework\MockObject\MockObject*/
	private $updater;

	public function setUp(): void {
		parent::setUp();
		$this->config = $this->getMockBuilder('\OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$clientService = $this->getMockBuilder('\OCP\Http\Client\IClientService')
			->disableOriginalConstructor()
			->getMock();

		$this->updater = $this->getMockBuilder('\OC\Updater\VersionCheck')
			->setMethods(['getUrlContent'])
			->setConstructorArgs([$clientService, $this->config])
			->getMock();
	}

	/**
	 * @param string $baseUrl
	 * @return string
	 */
	private function buildUpdateUrl($baseUrl) {
		return $baseUrl . '?version='.\implode('x', Util::getVersion()).'xinstalledatxlastupdatedatx'.\OC_Util::getChannel().'x'.\OC_Util::getEditionString().'x'.\OC_Util::getBuild();
	}

	public function testCheckInCache() {
		$expectedResult = [
			'version' => '8.0.4.2',
			'versionstring' => 'ownCloud 8.0.4',
			'url' => 'https://download.owncloud.org/community/owncloud-8.0.4.zip',
			'web' => 'http://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
		];

		$this->config
			->expects($this->at(0))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue(\time()));
		$this->config
			->expects($this->at(1))
			->method('getAppValue')
			->with('core', 'lastupdateResult')
			->will($this->returnValue(\json_encode($expectedResult)));

		$this->assertSame($expectedResult, $this->updater->check());
	}

	public function testCheckWithoutUpdateUrl() {
		$expectedResult = [
			'version' => '8.0.4.2',
			'versionstring' => 'ownCloud 8.0.4',
			'url' => 'https://download.owncloud.org/community/owncloud-8.0.4.zip',
			'web' => 'http://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
		];

		$this->config
			->expects($this->at(0))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue(0));
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('updater.server.url', 'https://updates.owncloud.com/server/')
			->willReturn('https://updates.owncloud.com/server/');
		$this->config
			->expects($this->at(2))
			->method('setAppValue')
			->with('core', 'lastupdatedat', $this->isType('integer'));
		$this->config
			->expects($this->at(4))
			->method('getAppValue')
			->with('core', 'installedat')
			->will($this->returnValue('installedat'));
		$this->config
			->expects($this->at(5))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue('lastupdatedat'));
		$this->config
			->expects($this->at(6))
			->method('setAppValue')
			->with('core', 'lastupdateResult', \json_encode($expectedResult));

		$updateXml = '<?xml version="1.0"?>
<owncloud>
  <version>8.0.4.2</version>
  <versionstring>ownCloud 8.0.4</versionstring>
  <url>https://download.owncloud.org/community/owncloud-8.0.4.zip</url>
  <web>http://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
</owncloud>';
		$this->updater
			->expects($this->once())
			->method('getUrlContent')
			->with($this->buildUpdateUrl('https://updates.owncloud.com/server/'))
			->will($this->returnValue($updateXml));

		$this->assertSame($expectedResult, $this->updater->check());
	}

	public function testCheckWithInvalidXml() {
		$this->config
			->expects($this->at(0))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue(0));
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('updater.server.url', 'https://updates.owncloud.com/server/')
			->willReturn('https://updates.owncloud.com/server/');
		$this->config
			->expects($this->at(2))
			->method('setAppValue')
			->with('core', 'lastupdatedat', $this->isType('integer'));
		$this->config
			->expects($this->at(4))
			->method('getAppValue')
			->with('core', 'installedat')
			->will($this->returnValue('installedat'));
		$this->config
			->expects($this->at(5))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue('lastupdatedat'));
		$this->config
			->expects($this->at(6))
			->method('setAppValue')
			->with('core', 'lastupdateResult', 'false');

		$updateXml = 'Invalid XML Response!';
		$this->updater
			->expects($this->once())
			->method('getUrlContent')
			->with($this->buildUpdateUrl('https://updates.owncloud.com/server/'))
			->will($this->returnValue($updateXml));

		$this->assertSame([], $this->updater->check());
	}

	public function testCheckWithEmptyValidXmlResponse() {
		$expectedResult = [
			'version' => '',
			'versionstring' => '',
			'url' => '',
			'web' => '',
		];

		$this->config
			->expects($this->at(0))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue(0));
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('updater.server.url', 'https://updates.owncloud.com/server/')
			->willReturn('https://updates.owncloud.com/server/');
		$this->config
			->expects($this->at(2))
			->method('setAppValue')
			->with('core', 'lastupdatedat', $this->isType('integer'));
		$this->config
			->expects($this->at(4))
			->method('getAppValue')
			->with('core', 'installedat')
			->will($this->returnValue('installedat'));
		$this->config
			->expects($this->at(5))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue('lastupdatedat'));

		$updateXml = '<?xml version="1.0"?>
<owncloud>
  <version></version>
  <versionstring></versionstring>
  <url></url>
  <web></web>
</owncloud>';
		$this->updater
			->expects($this->once())
			->method('getUrlContent')
			->with($this->buildUpdateUrl('https://updates.owncloud.com/server/'))
			->will($this->returnValue($updateXml));

		$this->assertSame($expectedResult, $this->updater->check());
	}

	public function testCheckWithEmptyInvalidXmlResponse() {
		$expectedResult = [];

		$this->config
			->expects($this->at(0))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue(0));
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('updater.server.url', 'https://updates.owncloud.com/server/')
			->willReturn('https://updates.owncloud.com/server/');
		$this->config
			->expects($this->at(2))
			->method('setAppValue')
			->with('core', 'lastupdatedat', $this->isType('integer'));
		$this->config
			->expects($this->at(4))
			->method('getAppValue')
			->with('core', 'installedat')
			->will($this->returnValue('installedat'));
		$this->config
			->expects($this->at(5))
			->method('getAppValue')
			->with('core', 'lastupdatedat')
			->will($this->returnValue('lastupdatedat'));
		$this->config
			->expects($this->at(6))
			->method('setAppValue')
			->with('core', 'lastupdateResult', \json_encode($expectedResult));

		$updateXml = '';
		$this->updater
			->expects($this->once())
			->method('getUrlContent')
			->with($this->buildUpdateUrl('https://updates.owncloud.com/server/'))
			->will($this->returnValue($updateXml));

		$this->assertSame($expectedResult, $this->updater->check());
	}
}
