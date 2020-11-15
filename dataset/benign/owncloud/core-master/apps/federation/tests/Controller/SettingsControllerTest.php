<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
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

namespace OCA\Federation\Tests\Controller;

use OCA\Federation\Controller\SettingsController;
use OCP\AppFramework\Http\DataResponse;
use Test\TestCase;

class SettingsControllerTest extends TestCase {

	/** @var SettingsController  */
	private $controller;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OCP\IRequest */
	private $request;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OCP\IL10N */
	private $l10n;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OCA\Federation\TrustedServers */
	private $trustedServers;

	public function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock('OCP\IRequest');
		$this->l10n = $this->createMock('OCP\IL10N');
		$this->trustedServers = $this->getMockBuilder('OCA\Federation\TrustedServers')
			->disableOriginalConstructor()->getMock();

		$this->controller = new SettingsController(
			'SettingsControllerTest',
			$this->request,
			$this->l10n,
			$this->trustedServers
		);
	}

	public function testAddServer() {
		$this->trustedServers
			->expects($this->once())
			->method('isTrustedServer')
			->with('url')
			->willReturn(false);
		$this->trustedServers
			->expects($this->once())
			->method('isOwnCloudServer')
			->with('url')
			->willReturn(true);

		$result = $this->controller->addServer('url');
		$this->assertInstanceOf(DataResponse::class, $result);

		$data = $result->getData();
		$this->assertSame(200, $result->getStatus());
		$this->assertSame('url', $data['url']);
		$this->assertArrayHasKey('id', $data);
	}

	/**
	 * @dataProvider checkServerFails
	 *
	 * @param bool $isTrustedServer
	 * @param bool $isOwnCloud
	 */
	public function testAddServerFail($isTrustedServer, $isOwnCloud) {
		$this->expectException(\OC\HintException::class);

		$this->trustedServers
			->expects($this->any())
			->method('isTrustedServer')
			->with('url')
			->willReturn($isTrustedServer);
		$this->trustedServers
			->expects($this->any())
			->method('isOwnCloudServer')
			->with('url')
			->willReturn($isOwnCloud);

		$this->controller->addServer('url');
	}

	public function testRemoveServer() {
		$this->trustedServers->expects($this->once())->method('removeServer')
		->with('url');
		$result = $this->controller->removeServer('url');
		$this->assertInstanceOf(DataResponse::class, $result);
		$this->assertSame(200, $result->getStatus());
	}

	public function testCheckServer() {
		$this->trustedServers
			->expects($this->once())
			->method('isTrustedServer')
			->with('url')
			->willReturn(false);
		$this->trustedServers
			->expects($this->once())
			->method('isOwnCloudServer')
			->with('url')
			->willReturn(true);

		$this->assertTrue(
			$this->invokePrivate($this->controller, 'checkServer', ['url'])
		);
	}

	/**
	 * @dataProvider checkServerFails
	 *
	 * @param bool $isTrustedServer
	 * @param bool $isOwnCloud
	 */
	public function testCheckServerFail($isTrustedServer, $isOwnCloud) {
		$this->expectException(\OC\HintException::class);

		$this->trustedServers
			->expects($this->any())
			->method('isTrustedServer')
			->with('url')
			->willReturn($isTrustedServer);
		$this->trustedServers
			->expects($this->any())
			->method('isOwnCloudServer')
			->with('url')
			->willReturn($isOwnCloud);

		$this->assertTrue(
			$this->invokePrivate($this->controller, 'checkServer', ['url'])
		);
	}

	/**
	 * data to simulate checkServer fails
	 *
	 * @return array
	 */
	public function checkServerFails() {
		return [
			[true, true],
			[false, false]
		];
	}
}
