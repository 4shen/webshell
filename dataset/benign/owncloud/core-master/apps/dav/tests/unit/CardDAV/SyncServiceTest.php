<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
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

namespace OCA\DAV\Tests\unit\CardDAV;

use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\CardDAV\SyncService;
use OCP\IUser;
use OCP\IUserManager;
use Test\TestCase;

class SyncServiceTest extends TestCase {
	public function testEmptySync() {
		$backend = $this->getBackendMock(0, 0, 0);

		$ss = $this->getSyncServiceMock($backend, []);
		$return = $ss->syncRemoteAddressBook('', 'system', '1234567890', null, '1', 'principals/system/system', []);
		$this->assertEquals('sync-token-1', $return);
	}

	public function testSyncWithNewElement() {
		$backend = $this->getBackendMock(1, 0, 0);
		$backend->method('getCard')->willReturn(false);

		$ss = $this->getSyncServiceMock($backend, ['0' => [200 => '']]);
		$return = $ss->syncRemoteAddressBook('', 'system', '1234567890', null, '1', 'principals/system/system', []);
		$this->assertEquals('sync-token-1', $return);
	}

	public function testSyncWithUpdatedElement() {
		$backend = $this->getBackendMock(0, 1, 0);
		$backend->method('getCard')->willReturn(true);

		$ss = $this->getSyncServiceMock($backend, ['0' => [200 => '']]);
		$return = $ss->syncRemoteAddressBook('', 'system', '1234567890', null, '1', 'principals/system/system', []);
		$this->assertEquals('sync-token-1', $return);
	}

	public function testSyncWithDeletedElement() {
		$backend = $this->getBackendMock(0, 0, 1);

		$ss = $this->getSyncServiceMock($backend, ['0' => [404 => '']]);
		$return = $ss->syncRemoteAddressBook('', 'system', '1234567890', null, '1', 'principals/system/system', []);
		$this->assertEquals('sync-token-1', $return);
	}

	public function testEnsureSystemAddressBookExists() {
		/** @var CardDavBackend | \PHPUnit\Framework\MockObject\MockObject $backend */
		$backend = $this->getMockBuilder(CardDavBackend::class)->disableOriginalConstructor()->getMock();
		$backend->expects($this->exactly(1))->method('createAddressBook');
		$backend->expects($this->at(0))->method('getAddressBooksByUri')->willReturn(null);
		$backend->expects($this->at(1))->method('getAddressBooksByUri')->willReturn([]);

		/** @var IUserManager $userManager */
		$userManager = $this->getMockBuilder('OCP\IUserManager')->disableOriginalConstructor()->getMock();
		$logger = $this->getMockBuilder('OCP\ILogger')->disableOriginalConstructor()->getMock();
		$ss = new SyncService($backend, $userManager, $logger);
		$book = $ss->ensureSystemAddressBookExists('principals/users/adam', 'contacts', []);
	}

	public function testUpdateAndDeleteUser() {
		/** @var CardDavBackend | \PHPUnit\Framework\MockObject\MockObject $backend */
		$backend = $this->getMockBuilder(CardDavBackend::class)->disableOriginalConstructor()->getMock();
		$logger = $this->getMockBuilder('OCP\ILogger')->disableOriginalConstructor()->getMock();

		$backend->expects($this->once())->method('createCard');
		$backend->expects($this->once())->method('updateCard');
		$backend->expects($this->once())->method('deleteCard');

		$backend->method('getCard')->willReturnOnConsecutiveCalls(false, [
			'carddata' => "BEGIN:VCARD\r\nVERSION:3.0\r\nPRODID:-//Sabre//Sabre VObject 3.4.8//EN\r\nUID:test-user\r\nFN:test-user\r\nN:test-user;;;;\r\nEND:VCARD\r\n\r\n"
		]);
		$backend->method('getAddressBooksByUri')->willReturn([
			'id'  => 40,
			'uri' => 'contacts',
			'principaluri' => 'principals/users/admin',
			'{DAV:}displayname' => 'Contacts',
			'{urn:ietf:params:xml:ns:carddav}addressbook-description' => null,
			'{http://calendarserver.org/ns/}getctag' => 1,
			'{http://sabredav.org/ns}sync-token' => 1,
		]);

		/** @var IUserManager | \PHPUnit\Framework\MockObject\MockObject $userManager */
		$userManager = $this->getMockBuilder('OCP\IUserManager')->disableOriginalConstructor()->getMock();

		/** @var IUser | \PHPUnit\Framework\MockObject\MockObject $user */
		$user = $this->getMockBuilder('OCP\IUser')->disableOriginalConstructor()->getMock();
		$user->method('getBackendClassName')->willReturn('unittest');
		$user->method('getUID')->willReturn('test-user');

		$ss = new SyncService($backend, $userManager, $logger);
		$ss->updateUser($user);

		$user->method('getDisplayName')->willReturn('A test user for unit testing');

		$ss->updateUser($user);

		$ss->deleteUser($user);
	}

	public function testDeleteUserByUidIfUnique() {
		$logger = $this->createMock('OCP\ILogger');
		$userManager = $this->createMock('OCP\IUserManager');

		$backend = $this->createMock(CardDavBackend::class);
		$backend->method('getAddressBooksByUri')->willReturn([
			'id'  => 40,
			'uri' => 'contacts',
			'principaluri' => 'principals/users/admin',
			'{DAV:}displayname' => 'Contacts',
			'{urn:ietf:params:xml:ns:carddav}addressbook-description' => null,
			'{http://calendarserver.org/ns/}getctag' => 1,
			'{http://sabredav.org/ns}sync-token' => 1,
		]);
		$backend->method('searchEx')->willReturn([
			[
				'uri' => 'backend:userId.vcf',
				'carddata' => "BEGIN:VCARD\r\nVERSION:3.0\r\nPRODID:-//Sabre//Sabre VObject 3.4.8//EN\r\nUID:userId\r\nFN:userId\r\nN:userId;;;;\r\nEND:VCARD\r\n\r\n"
			]
		]);
		$backend->expects($this->once())
			->method('deleteCard')
			->with(40, 'backend:userId.vcf');

		$ss = new SyncService($backend, $userManager, $logger);
		$this->assertTrue($ss->deleteUserByUidIfUnique('userId'));
	}

	public function testDeleteUserByUidIfUniqueNoData() {
		$logger = $this->createMock('OCP\ILogger');
		$userManager = $this->createMock('OCP\IUserManager');

		$backend = $this->createMock(CardDavBackend::class);
		$backend->method('getAddressBooksByUri')->willReturn([
			'id'  => 40,
			'uri' => 'contacts',
			'principaluri' => 'principals/users/admin',
			'{DAV:}displayname' => 'Contacts',
			'{urn:ietf:params:xml:ns:carddav}addressbook-description' => null,
			'{http://calendarserver.org/ns/}getctag' => 1,
			'{http://sabredav.org/ns}sync-token' => 1,
		]);
		$backend->method('searchEx')->willReturn([]);
		$backend->expects($this->never())
			->method('deleteCard');

		$ss = new SyncService($backend, $userManager, $logger);
		$this->assertNull($ss->deleteUserByUidIfUnique('userId'));
	}

	public function testDeleteUserByUidIfUniqueMultiple() {
		$logger = $this->createMock('OCP\ILogger');
		$userManager = $this->createMock('OCP\IUserManager');

		$backend = $this->createMock(CardDavBackend::class);
		$backend->method('getAddressBooksByUri')->willReturn([
			'id'  => 40,
			'uri' => 'contacts',
			'principaluri' => 'principals/users/admin',
			'{DAV:}displayname' => 'Contacts',
			'{urn:ietf:params:xml:ns:carddav}addressbook-description' => null,
			'{http://calendarserver.org/ns/}getctag' => 1,
			'{http://sabredav.org/ns}sync-token' => 1,
		]);
		$backend->method('searchEx')->willReturn([
			[
				'uri' => 'backend:userId.vcf',
				'carddata' => "BEGIN:VCARD\r\nVERSION:3.0\r\nPRODID:-//Sabre//Sabre VObject 3.4.8//EN\r\nUID:userId\r\nFN:userId\r\nN:userId;;;;\r\nEND:VCARD\r\n\r\n"
			],
			[
				'uri' => 'backend2:userId.vcf',
				'carddata' => "BEGIN:VCARD\r\nVERSION:3.0\r\nPRODID:-//Sabre//Sabre VObject 3.4.8//EN\r\nUID:userId\r\nFN:userId\r\nN:userId;;;;\r\nEND:VCARD\r\n\r\n"
			]
		]);
		$backend->expects($this->never())
			->method('deleteCard');

		$ss = new SyncService($backend, $userManager, $logger);
		$this->assertFalse($ss->deleteUserByUidIfUnique('userId'));
	}

	/**
	 * @param int $createCount
	 * @param int $updateCount
	 * @param int $deleteCount
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	private function getBackendMock($createCount, $updateCount, $deleteCount) {
		$backend = $this->getMockBuilder(CardDavBackend::class)
			->disableOriginalConstructor()
			->getMock();
		$backend->expects($this->exactly($createCount))->method('createCard');
		$backend->expects($this->exactly($updateCount))->method('updateCard');
		$backend->expects($this->exactly($deleteCount))->method('deleteCard');
		return $backend;
	}

	/**
	 * @param $backend
	 * @param $response
	 * @return SyncService|\PHPUnit\Framework\MockObject\MockObject
	 */
	private function getSyncServiceMock($backend, $response) {
		$userManager = $this->getMockBuilder('OCP\IUserManager')->disableOriginalConstructor()->getMock();
		$logger = $this->getMockBuilder('OCP\ILogger')->disableOriginalConstructor()->getMock();
		/** @var SyncService | \PHPUnit\Framework\MockObject\MockObject $ss */
		$ss = $this->getMockBuilder(SyncService::class)
			->setMethods(['ensureSystemAddressBookExists', 'requestSyncReport', 'download'])
			->setConstructorArgs([$backend, $userManager, $logger])
			->getMock();
		$ss->method('requestSyncReport')->withAnyParameters()->willReturn(['response' => $response, 'token' => 'sync-token-1']);
		$ss->method('ensureSystemAddressBookExists')->willReturn(['id' => 1]);
		$ss->method('download')->willReturn([
			'body' => '',
			'statusCode' => 200,
			'headers' => []
		]);
		return $ss;
	}
}
