<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Georg Ehrke <georg@owncloud.com>
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

use InvalidArgumentException;
use OCA\DAV\CardDAV\AddressBook;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\Connector\Sabre\Principal;
use OCA\DAV\DAV\GroupPrincipalBackend;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Sabre\DAV\PropPatch;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property\Text;
use Test\TestCase;

/**
 * Class CardDavBackendTest
 *
 * @group DB
 *
 * @package OCA\DAV\Tests\unit\CardDAV
 */
class CardDavBackendTest extends TestCase {

	/** @var CardDavBackend */
	private $backend;

	/** @var Principal | \PHPUnit\Framework\MockObject\MockObject */
	private $principal;

	/** @var GroupPrincipalBackend | \PHPUnit\Framework\MockObject\MockObject */
	private $groupPrincipal;

	/** @var  IDBConnection */
	private $db;

	/** @var string */
	private $dbCardsTable = 'cards';

	/** @var string */
	private $dbCardsPropertiesTable = 'cards_properties';

	const UNIT_TEST_USER = 'principals/users/carddav-unit-test';
	const UNIT_TEST_USER1 = 'principals/users/carddav-unit-test1';
	const UNIT_TEST_GROUP = 'principals/groups/carddav-unit-test-group';

	public function setUp(): void {
		parent::setUp();

		$this->principal = $this->getMockBuilder(Principal::class)
			->disableOriginalConstructor()
			->setMethods(['getPrincipalByPath', 'getGroupMembership'])
			->getMock();
		$this->principal->method('getPrincipalByPath')
			->willReturn([
				'uri' => 'principals/best-friend'
			]);
		$this->principal->method('getGroupMembership')
			->withAnyParameters()
			->willReturn([self::UNIT_TEST_GROUP]);

		$this->groupPrincipal = $this->createMock(GroupPrincipalBackend::class);

		$this->db = \OC::$server->getDatabaseConnection();

		$this->backend = new CardDavBackend($this->db, $this->principal, $this->groupPrincipal, null);

		// start every test with a empty cards_properties and cards table
		$query = $this->db->getQueryBuilder();
		$query->delete('cards_properties')->execute();
		$query = $this->db->getQueryBuilder();
		$query->delete('cards')->execute();

		$this->tearDown();
	}

	public function tearDown(): void {
		parent::tearDown();

		if ($this->backend === null) {
			return;
		}
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		foreach ($books as $book) {
			$this->backend->deleteAddressBook($book['id']);
		}
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testAddressBookOperations() {

		// create a new address book
		$this->backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);

		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);
		$this->assertEquals('Example', $books[0]['{DAV:}displayname']);

		// update it's display name
		$patch = new PropPatch([
			'{DAV:}displayname' => 'Unit test',
			'{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'Addressbook used for unit testing'
		]);
		$this->backend->updateAddressBook($books[0]['id'], $patch);
		$patch->commit();
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);
		$this->assertEquals('Unit test', $books[0]['{DAV:}displayname']);
		$this->assertEquals('Addressbook used for unit testing', $books[0]['{urn:ietf:params:xml:ns:carddav}addressbook-description']);

		// delete the address book
		$this->backend->deleteAddressBook($books[0]['id']);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(0, $books);
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testAddressBookSharing() {
		$this->backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);
		$addressBook = new AddressBook($this->backend, $books[0]);
		$this->backend->updateShares($addressBook, [
			[
				'href' => 'principal:' . self::UNIT_TEST_USER1,
			],
			[
				'href' => 'principal:' . self::UNIT_TEST_GROUP,
			]
		], []);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER1);
		$this->assertCount(1, $books);

		// delete the address book
		$this->backend->deleteAddressBook($books[0]['id']);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(0, $books);
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testCardOperations() {

		/** @var CardDavBackend | \PHPUnit\Framework\MockObject\MockObject $backend */
		$backend = $this->getMockBuilder(CardDavBackend::class)
				->setConstructorArgs([$this->db, $this->principal, $this->groupPrincipal, null])
				->setMethods(['updateProperties', 'purgeProperties'])->getMock();

		// create a new address book
		$backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);
		$books = $backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);
		$bookId = $books[0]['id'];

		$uri = static::getUniqueID('card');
		// updateProperties is expected twice, once for createCard and once for updateCard
		$backend->expects($this->at(0))->method('updateProperties')->with($bookId, $uri, '');
		$backend->expects($this->at(1))->method('updateProperties')->with($bookId, $uri, '***');
		// create a card
		$backend->createCard($bookId, $uri, '');

		// get all the cards
		$cards = $backend->getCards($bookId);
		$this->assertCount(1, $cards);
		$this->assertEquals('', $cards[0]['carddata']);

		// get the cards
		$card = $backend->getCard($bookId, $uri);
		$this->assertNotNull($card);
		$this->assertArrayHasKey('id', $card);
		$this->assertArrayHasKey('uri', $card);
		$this->assertArrayHasKey('lastmodified', $card);
		$this->assertArrayHasKey('etag', $card);
		$this->assertArrayHasKey('size', $card);
		$this->assertEquals('', $card['carddata']);

		// update the card
		$backend->updateCard($bookId, $uri, '***');
		$card = $backend->getCard($bookId, $uri);
		$this->assertEquals('***', $card['carddata']);

		// delete the card
		$backend->expects($this->once())->method('purgeProperties')->with($bookId, $card['id']);
		$backend->deleteCard($bookId, $uri);
		$cards = $backend->getCards($bookId);
		$this->assertCount(0, $cards);
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testMultiCard() {
		$this->backend = $this->getMockBuilder(CardDavBackend::class)
			->setConstructorArgs([$this->db, $this->principal, $this->groupPrincipal, null])
			->setMethods(['updateProperties'])->getMock();

		// create a new address book
		$this->backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);
		$bookId = $books[0]['id'];

		// create a card
		$uri0 = static::getUniqueID('card');
		$this->backend->createCard($bookId, $uri0, '');
		$uri1 = static::getUniqueID('card');
		$this->backend->createCard($bookId, $uri1, '');
		$uri2 = static::getUniqueID('card');
		$this->backend->createCard($bookId, $uri2, '');

		// get all the cards
		$cards = $this->backend->getCards($bookId);
		$this->assertCount(3, $cards);
		$this->assertEquals('', $cards[0]['carddata']);
		$this->assertEquals('', $cards[1]['carddata']);
		$this->assertEquals('', $cards[2]['carddata']);

		// get the cards
		$cards = $this->backend->getMultipleCards($bookId, [$uri1, $uri2]);
		$this->assertCount(2, $cards);
		foreach ($cards as $card) {
			$this->assertArrayHasKey('id', $card);
			$this->assertArrayHasKey('uri', $card);
			$this->assertArrayHasKey('lastmodified', $card);
			$this->assertArrayHasKey('etag', $card);
			$this->assertArrayHasKey('size', $card);
			$this->assertEquals('', $card['carddata']);
		}

		// delete the card
		$this->backend->deleteCard($bookId, $uri0);
		$this->backend->deleteCard($bookId, $uri1);
		$this->backend->deleteCard($bookId, $uri2);
		$cards = $this->backend->getCards($bookId);
		$this->assertCount(0, $cards);
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testDeleteWithoutCard() {
		$this->backend = $this->getMockBuilder(CardDavBackend::class)
			->setConstructorArgs([$this->db, $this->principal, $this->groupPrincipal, null])
			->setMethods([
				'getCardId',
				'addChange',
				'purgeProperties',
				'updateProperties',
			])
			->getMock();

		// create a new address book
		$this->backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);
		$books = $this->backend->getUsersOwnAddressBooks(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);

		$bookId = $books[0]['id'];
		$uri = static::getUniqueID('card');

		// create a new address book
		$this->backend->expects($this->once())
			->method('getCardId')
			->with($bookId, $uri)
			->willThrowException(new \InvalidArgumentException());
		$this->backend->expects($this->exactly(2))
			->method('addChange')
			->withConsecutive(
				[$bookId, $uri, 1],
				[$bookId, $uri, 3]
			);
		$this->backend->expects($this->never())
			->method('purgeProperties');

		// create a card
		$this->backend->createCard($bookId, $uri, '');

		// delete the card
		$this->assertTrue($this->backend->deleteCard($bookId, $uri));
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testSyncSupport() {
		$this->backend = $this->getMockBuilder(CardDavBackend::class)
			->setConstructorArgs([$this->db, $this->principal, $this->groupPrincipal, null])
			->setMethods(['updateProperties'])->getMock();

		// create a new address book
		$this->backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);
		$bookId = $books[0]['id'];

		// fist call without synctoken
		$changes = $this->backend->getChangesForAddressBook($bookId, '', 1, 1000);
		$syncToken = $changes['syncToken'];

		// add a change
		$uri0 = static::getUniqueID('card');
		$this->backend->createCard($bookId, $uri0, '');

		// look for changes
		$changes = $this->backend->getChangesForAddressBook($bookId, $syncToken, 1, 1000);
		$this->assertEquals($uri0, $changes['added'][0]);
	}

	/**
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function testSharing() {
		$this->backend->createAddressBook(self::UNIT_TEST_USER, 'Example', []);
		$books = $this->backend->getAddressBooksForUser(self::UNIT_TEST_USER);
		$this->assertCount(1, $books);

		$exampleBook = new AddressBook($this->backend, $books[0]);
		$this->backend->updateShares($exampleBook, [['href' => 'principal:principals/best-friend']], []);

		$shares = $this->backend->getShares($exampleBook->getResourceId());
		$this->assertCount(1, $shares);

		// adding the same sharee again has no effect
		$this->backend->updateShares($exampleBook, [['href' => 'principal:principals/best-friend']], []);

		$shares = $this->backend->getShares($exampleBook->getResourceId());
		$this->assertCount(1, $shares);

		$books = $this->backend->getAddressBooksForUser('principals/best-friend');
		$this->assertCount(1, $books);

		$this->backend->updateShares($exampleBook, [], ['principal:principals/best-friend']);

		$shares = $this->backend->getShares($exampleBook->getResourceId());
		$this->assertCount(0, $shares);

		$books = $this->backend->getAddressBooksForUser('principals/best-friend');
		$this->assertCount(0, $books);
	}

	public function testUpdateProperties() {
		$bookId = 42;
		$cardUri = 'card-uri';
		$cardId = 2;

		$backend = $this->getMockBuilder(CardDavBackend::class)
			->setConstructorArgs([$this->db, $this->principal, $this->groupPrincipal, null])
			->setMethods(['getCardId'])->getMock();

		$backend->expects($this->any())->method('getCardId')->willReturn($cardId);

		// add properties for new vCard
		$vCard = new VCard();
		$vCard->UID = $cardUri;
		$vCard->FN = 'John Doe';
		static::invokePrivate($backend, 'updateProperties', [$bookId, $cardUri, $vCard->serialize()]);

		$query = $this->db->getQueryBuilder();
		$result = $query->select('*')->from('cards_properties')->execute()->fetchAll();

		$this->assertCount(2, $result);

		$this->assertSame('UID', $result[0]['name']);
		$this->assertSame($cardUri, $result[0]['value']);
		$this->assertSame($bookId, (int)$result[0]['addressbookid']);
		$this->assertSame($cardId, (int)$result[0]['cardid']);

		$this->assertSame('FN', $result[1]['name']);
		$this->assertSame('John Doe', $result[1]['value']);
		$this->assertSame($bookId, (int)$result[1]['addressbookid']);
		$this->assertSame($cardId, (int)$result[1]['cardid']);

		// update properties for existing vCard
		$vCard = new VCard();
		$vCard->UID = $cardUri;
		static::invokePrivate($backend, 'updateProperties', [$bookId, $cardUri, $vCard->serialize()]);

		$query = $this->db->getQueryBuilder();
		$result = $query->select('*')->from('cards_properties')->execute()->fetchAll();

		$this->assertCount(1, $result);

		$this->assertSame('UID', $result[0]['name']);
		$this->assertSame($cardUri, $result[0]['value']);
		$this->assertSame($bookId, (int)$result[0]['addressbookid']);
		$this->assertSame($cardId, (int)$result[0]['cardid']);
	}

	public function testPurgeProperties() {
		$query = $this->db->getQueryBuilder();
		$query->insert('cards_properties')
			->values(
				[
					'addressbookid' => $query->createNamedParameter(1),
					'cardid' => $query->createNamedParameter(1),
					'name' => $query->createNamedParameter('name1'),
					'value' => $query->createNamedParameter('value1'),
					'preferred' => $query->createNamedParameter(0)
				]
			);
		$query->execute();

		$query = $this->db->getQueryBuilder();
		$query->insert('cards_properties')
			->values(
				[
					'addressbookid' => $query->createNamedParameter(1),
					'cardid' => $query->createNamedParameter(2),
					'name' => $query->createNamedParameter('name2'),
					'value' => $query->createNamedParameter('value2'),
					'preferred' => $query->createNamedParameter(0)
				]
			);
		$query->execute();

		static::invokePrivate($this->backend, 'purgeProperties', [1, 1]);

		$query = $this->db->getQueryBuilder();
		$result = $query->select('*')->from('cards_properties')->execute()->fetchAll();
		$this->assertCount(1, $result);
		$this->assertSame(1, (int)$result[0]['addressbookid']);
		$this->assertSame(2, (int)$result[0]['cardid']);
	}

	public function testGetCardId() {
		$query = $this->db->getQueryBuilder();

		$query->insert('cards')
			->values(
				[
					'addressbookid' => $query->createNamedParameter(1),
					'carddata' => $query->createNamedParameter(''),
					'uri' => $query->createNamedParameter('uri'),
					'lastmodified' => $query->createNamedParameter(4738743),
					'etag' => $query->createNamedParameter('etag'),
					'size' => $query->createNamedParameter(120)
				]
			);
		$query->execute();
		$id = $query->getLastInsertId();

		$this->assertSame($id,
			static::invokePrivate($this->backend, 'getCardId', [1, 'uri']));
	}

	/**
	 */
	public function testGetCardIdFailed() {
		$this->expectException(\InvalidArgumentException::class);

		static::invokePrivate($this->backend, 'getCardId', [1, 'uri']);
	}

	/**
	 * @dataProvider dataTestSearch
	 *
	 * @param string $pattern
	 * @param array $properties
	 * @param array $matchModes
	 * @param array $expected
	 * @param array $limit
	 * @param array $offset
	 */
	public function testSearch($pattern, $properties, $matchModes, $expected, $limit, $offset) {
		/** @var VCard $vCards */
		$vCards = [];
		$vCards[0] = new VCard();
		$vCards[0]->add(new Text($vCards[0], 'UID', 'uid'));
		$vCards[0]->add(new Text($vCards[0], 'FN', 'John Doe'));
		$vCards[0]->add(new Text($vCards[0], 'CLOUD', 'john@owncloud.org'));
		$vCards[1] = new VCard();
		$vCards[1]->add(new Text($vCards[1], 'UID', 'uid'));
		$vCards[1]->add(new Text($vCards[1], 'FN', 'John M. Doe'));

		$vCardIds = [];
		$query = $this->db->getQueryBuilder();
		for ($i=0; $i<2; $i++) {
			$query->insert($this->dbCardsTable)
					->values(
							[
									'addressbookid' => $query->createNamedParameter(0),
									'carddata' => $query->createNamedParameter($vCards[$i]->serialize(), IQueryBuilder::PARAM_LOB),
									'uri' => $query->createNamedParameter('uri' . $i),
									'lastmodified' => $query->createNamedParameter(\time()),
									'etag' => $query->createNamedParameter('etag' . $i),
									'size' => $query->createNamedParameter(120),
							]
					);
			$query->execute();
			$vCardIds[] = $query->getLastInsertId();
		}

		$query->insert($this->dbCardsPropertiesTable)
			->values(
				[
					'addressbookid' => $query->createNamedParameter(0),
					'cardid' => $query->createNamedParameter($vCardIds[0]),
					'name' => $query->createNamedParameter('FN'),
					'value' => $query->createNamedParameter('John Doe'),
					'preferred' => $query->createNamedParameter(0)
				]
			);
		$query->execute();
		$query->insert($this->dbCardsPropertiesTable)
				->values(
						[
								'addressbookid' => $query->createNamedParameter(0),
								'cardid' => $query->createNamedParameter($vCardIds[0]),
								'name' => $query->createNamedParameter('CLOUD'),
								'value' => $query->createNamedParameter('John@owncloud.org'),
								'preferred' => $query->createNamedParameter(0)
						]
				);
		$query->execute();
		$query->insert($this->dbCardsPropertiesTable)
			->values(
				[
					'addressbookid' => $query->createNamedParameter(0),
					'cardid' => $query->createNamedParameter($vCardIds[1]),
					'name' => $query->createNamedParameter('FN'),
					'value' => $query->createNamedParameter('John M. Doe'),
					'preferred' => $query->createNamedParameter(0)
				]
			);
		$query->execute();
		
		foreach ($matchModes as $matchMode) {
			if ($matchMode !== null) {
				$result = $this->backend->searchEx(0, $pattern, $properties, ['matchMode' => $matchMode], $limit, $offset);
			} else {
				$result = $this->backend->search(0, $pattern, $properties, $limit, $offset);
			}

			// check result
			$this->assertCount(\count($expected), $result);

			$found = [];
			foreach ($result as $r) {
				foreach ($expected as $exp) {
					if ($r['uri'] === $exp[0] && \strpos($r['carddata'], $exp[1]) > 0) {
						$found[$exp[1]] = true;
						break;
					}
				}
			}

			$this->assertCount(\count($expected), $found);
		}
	}

	public function dataTestSearch() {
		return [
				// test medial and end wildcard with name
				['John', ['FN'], [null, 'START', 'ANY'], [['uri0', 'John Doe'], ['uri1', 'John M. Doe']], 100, 0],
				// test start wildcard or exact with just name will return no results
				['John', ['FN'], ['END', 'EXACT'], [], 100, 0],
				// test start wildcard with name ending will return results
				['Doe', ['FN'], ['END'], [['uri0', 'John Doe'], ['uri1', 'John M. Doe']], 100, 0],
				// test exact with full name will return result
				['John Doe', ['FN'], ['EXACT'], [['uri0', 'John Doe']], 100, 0],
				// test medial with surname
				['M. Doe', ['FN'], [ null ], [['uri1', 'John M. Doe']], 100, 0],
				// test medial with part of surname
				['Do', ['FN'], [ null ], [['uri0', 'John Doe'], ['uri1', 'John M. Doe']], 100, 0],
				// check if duplicates are handled correctly
				['John', ['FN', 'CLOUD'], [ null ], [['uri0', 'John Doe'], ['uri1', 'John M. Doe']], 100, 0],
				// case insensitive
				['john', ['FN'], [ null ], [['uri0', 'John Doe'], ['uri1', 'John M. Doe']], 100, 0],
				// search limit
				['John', ['FN'], [ null ], [['uri0', 'John Doe']], 1, 0],
				// search offset
				['John', ['FN'], [ null ], [['uri1', 'John M. Doe']], 1, 1],
		];
	}

	public function testGetCardUri() {
		$query = $this->db->getQueryBuilder();
		$query->insert($this->dbCardsTable)
				->values(
						[
								'addressbookid' => $query->createNamedParameter(1),
								'carddata' => $query->createNamedParameter('carddata', IQueryBuilder::PARAM_LOB),
								'uri' => $query->createNamedParameter('uri'),
								'lastmodified' => $query->createNamedParameter(5489543),
								'etag' => $query->createNamedParameter('etag'),
								'size' => $query->createNamedParameter(120),
						]
				);
		$query->execute();

		$id = $query->getLastInsertId();

		$this->assertSame('uri', $this->backend->getCardUri($id));
	}

	/**
	 */
	public function testGetCardUriFailed() {
		$this->expectException(\InvalidArgumentException::class);

		$this->backend->getCardUri(1);
	}

	public function testGetContact() {
		$query = $this->db->getQueryBuilder();
		for ($i=0; $i<2; $i++) {
			$query->insert($this->dbCardsTable)
					->values(
							[
									'addressbookid' => $query->createNamedParameter($i),
									'carddata' => $query->createNamedParameter('carddata' . $i, IQueryBuilder::PARAM_LOB),
									'uri' => $query->createNamedParameter('uri' . $i),
									'lastmodified' => $query->createNamedParameter(5489543),
									'etag' => $query->createNamedParameter('etag' . $i),
									'size' => $query->createNamedParameter(120),
							]
					);
			$query->execute();
		}

		$result = $this->backend->getContact(0, 'uri0');
		$this->assertCount(7, $result);
		$this->assertSame(0, (int)$result['addressbookid']);
		$this->assertSame('uri0', $result['uri']);
		$this->assertSame(5489543, (int)$result['lastmodified']);
		$this->assertSame('etag0', $result['etag']);
		$this->assertSame(120, (int)$result['size']);

		// this shouldn't return any result because 'uri1' is in address book 1
		$result = $this->backend->getContact(0, 'uri1');
		$this->assertEmpty($result);
	}

	public function testGetContactFail() {
		$this->assertEmpty($this->backend->getContact(0, 'uri'));
	}

	public function testCollectCardProperties() {
		$query = $this->db->getQueryBuilder();
		$query->insert($this->dbCardsPropertiesTable)
			->values(
				[
					'addressbookid' => $query->createNamedParameter(666),
					'cardid' => $query->createNamedParameter(777),
					'name' => $query->createNamedParameter('FN'),
					'value' => $query->createNamedParameter('John Doe'),
					'preferred' => $query->createNamedParameter(0)
				]
			)
		->execute();

		$result = $this->backend->collectCardProperties(666, 'FN');
		$this->assertEquals(['John Doe'], $result);
	}

	public function testHugeMultiGet() {
		$bookId = 1;
		$urls = \array_map(function ($number) {
			return "url-$number";
		}, \range(0, 2000));
		$multipleCards = $this->backend->getMultipleCards($bookId, $urls);
		$this->assertEquals([], $multipleCards);
	}
}
