<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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
namespace OCA\DAV\Tests\unit\Connector;

use OCP\IRequest;
use OCP\ISession;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager;

/**
 * Class PublicAuthTest
 *
 * @group DB
 *
 * @package OCA\DAV\Tests\unit\Connector
 */
class PublicAuthTest extends \Test\TestCase {

	/** @var ISession|\PHPUnit\Framework\MockObject\MockObject */
	private $session;
	/** @var IRequest|\PHPUnit\Framework\MockObject\MockObject */
	private $request;
	/** @var IManager|\PHPUnit\Framework\MockObject\MockObject */
	private $shareManager;
	/** @var \OCA\DAV\Connector\PublicAuth */
	private $auth;

	/** @var string */
	private $oldUser;

	protected function setUp(): void {
		parent::setUp();

		$this->session = $this->createMock('\OCP\ISession');
		$this->request = $this->createMock('\OCP\IRequest');
		$this->shareManager = $this->createMock('\OCP\Share\IManager');

		$this->auth = new \OCA\DAV\Connector\PublicAuth(
			$this->request,
			$this->shareManager,
			$this->session
		);

		// Store current user
		$this->oldUser = \OC_User::getUser();
	}

	protected function tearDown(): void {
		\OC_User::setIncognitoMode(false);

		// Set old user
		\OC_User::setUserId($this->oldUser);
		\OC_Util::setupFS($this->oldUser);

		parent::tearDown();
	}

	public function testNoShare() {
		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willThrowException(new ShareNotFound());

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertFalse($result);
	}

	public function testShareNoPassword() {
		$share = $this->createMock('OCP\Share\IShare');
		$share->method('getPassword')->willReturn(null);

		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willReturn($share);

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertTrue($result);
	}

	public function testSharePasswordFancyShareType() {
		$share = $this->createMock('OCP\Share\IShare');
		$share->method('getPassword')->willReturn('password');
		$share->method('getShareType')->willReturn(42);

		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willReturn($share);

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertFalse($result);
	}

	public function testSharePasswordRemote() {
		$share = $this->createMock('OCP\Share\IShare');
		$share->method('getPassword')->willReturn('password');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_REMOTE);

		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willReturn($share);

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertTrue($result);
	}

	public function testSharePasswordLinkValidPassword() {
		$share = $this->createMock('OCP\Share\IShare');
		$share->method('getPassword')->willReturn('password');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);

		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willReturn($share);

		$this->shareManager->expects($this->once())
			->method('checkPassword')->with(
			$this->equalTo($share),
			$this->equalTo('password')
		)->willReturn(true);

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertTrue($result);
	}

	public function testSharePasswordLinkValidSession() {
		$share = $this->createMock('OCP\Share\IShare');
		$share->method('getPassword')->willReturn('password');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$share->method('getId')->willReturn('42');

		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willReturn($share);

		$this->shareManager->method('checkPassword')
			->with(
				$this->equalTo($share),
				$this->equalTo('password')
			)->willReturn(false);

		$this->session->method('exists')->with('public_link_authenticated')->willReturn(true);
		$this->session->method('get')->with('public_link_authenticated')->willReturn('42');

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertTrue($result);
	}

	public function testSharePasswordLinkInvalidSession() {
		$share = $this->createMock('OCP\Share\IShare');
		$share->method('getPassword')->willReturn('password');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$share->method('getId')->willReturn('42');

		$this->shareManager->expects($this->once())
			->method('getShareByToken')
			->willReturn($share);

		$this->shareManager->method('checkPassword')
			->with(
				$this->equalTo($share),
				$this->equalTo('password')
			)->willReturn(false);

		$this->session->method('exists')->with('public_link_authenticated')->willReturn(true);
		$this->session->method('get')->with('public_link_authenticated')->willReturn('43');

		$result = $this->invokePrivate($this->auth, 'validateUserPass', ['username', 'password']);

		$this->assertFalse($result);
	}
}
