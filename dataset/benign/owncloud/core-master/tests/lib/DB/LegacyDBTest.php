<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\DB;

use OC_DB;

/**
 * Class LegacyDBTest
 *
 * @group DB
 */
class LegacyDBTest extends \Test\TestCase {
	protected $backupGlobals = false;

	protected static $schema_file = 'static://test_db_scheme';
	protected $test_prefix;

	/**
	 * @var string
	 */
	private $table1;

	/**
	 * @var string
	 */
	private $table2;

	/**
	 * @var string
	 */
	private $table3;

	/**
	 * @var string
	 */
	private $table4;

	/**
	 * @var string
	 */
	private $table5;

	/**
	 * @var string
	 */
	private $text_table;

	protected function setUp(): void {
		parent::setUp();

		$dbFile = \OC::$SERVERROOT.'/tests/data/db_structure.xml';

		$r = $this->getUniqueID('_', 4).'_';
		$content = \file_get_contents($dbFile);
		$content = \str_replace('*dbprefix*', '*dbprefix*'.$r, $content);
		\file_put_contents(self::$schema_file, $content);
		OC_DB::createDbFromStructure(self::$schema_file);

		$this->test_prefix = $r;
		$this->table1 = $this->test_prefix.'cntcts_addrsbks';
		$this->table2 = $this->test_prefix.'cntcts_cards';
		$this->table3 = $this->test_prefix.'vcategory';
		$this->table4 = $this->test_prefix.'decimal';
		$this->table5 = $this->test_prefix.'uniconst';
		$this->text_table = $this->test_prefix.'text_table';
	}

	protected function tearDown(): void {
		OC_DB::removeDBStructure(self::$schema_file);
		\unlink(self::$schema_file);

		parent::tearDown();
	}

	public function testQuotes() {
		$query = OC_DB::prepare('SELECT `fullname` FROM `*PREFIX*'.$this->table2.'` WHERE `uri` = ?');
		$result = $query->execute(['uri_1']);
		$this->assertTrue((bool)$result);
		$row = $result->fetchRow();
		$this->assertFalse($row);
		$query = OC_DB::prepare('INSERT INTO `*PREFIX*'.$this->table2.'` (`fullname`,`uri`) VALUES (?,?)');
		$result = $query->execute(['fullname test', 'uri_1']);
		$this->assertEquals(1, $result);
		$query = OC_DB::prepare('SELECT `fullname`,`uri` FROM `*PREFIX*'.$this->table2.'` WHERE `uri` = ?');
		$result = $query->execute(['uri_1']);
		$this->assertTrue((bool)$result);
		$row = $result->fetchRow();
		$this->assertArrayHasKey('fullname', $row);
		$this->assertEquals($row['fullname'], 'fullname test');
		$row = $result->fetchRow();
		$this->assertFalse((bool)$row); //PDO returns false, MDB2 returns null
	}

	/**
	 * @medium
	 */
	public function testNOW() {
		$query = OC_DB::prepare('INSERT INTO `*PREFIX*'.$this->table2.'` (`fullname`,`uri`) VALUES (NOW(),?)');
		$result = $query->execute(['uri_2']);
		$this->assertEquals(1, $result);
		$query = OC_DB::prepare('SELECT `fullname`,`uri` FROM `*PREFIX*'.$this->table2.'` WHERE `uri` = ?');
		$result = $query->execute(['uri_2']);
		$this->assertTrue((bool)$result);
	}

	public function testUNIX_TIMESTAMP() {
		$query = OC_DB::prepare('INSERT INTO `*PREFIX*'.$this->table2.'` (`fullname`,`uri`) VALUES (UNIX_TIMESTAMP(),?)');
		$result = $query->execute(['uri_3']);
		$this->assertEquals(1, $result);
		$query = OC_DB::prepare('SELECT `fullname`,`uri` FROM `*PREFIX*'.$this->table2.'` WHERE `uri` = ?');
		$result = $query->execute(['uri_3']);
		$this->assertTrue((bool)$result);
	}
	
	public function testLastInsertId() {
		$query = OC_DB::prepare('INSERT INTO `*PREFIX*'.$this->table2.'` (`fullname`,`uri`) VALUES (?,?)');
		$result1 = OC_DB::executeAudited($query, ['insertid 1','uri_1']);
		$id1 = \OC::$server->getDatabaseConnection()->lastInsertId('*PREFIX*'.$this->table2);
		
		// we don't know the id we should expect, so insert another row
		$result2 = OC_DB::executeAudited($query, ['insertid 2','uri_2']);
		$id2 = \OC::$server->getDatabaseConnection()->lastInsertId('*PREFIX*'.$this->table2);
		// now we can check if the two ids are in correct order
		$this->assertGreaterThan($id1, $id2);
	}
	
	public function testinsertIfNotExist() {
		$categoryEntries = [
				['user' => 'test', 'type' => 'contact', 'category' => 'Family',    'expectedResult' => 1],
				['user' => 'test', 'type' => 'contact', 'category' => 'Friends',   'expectedResult' => 1],
				['user' => 'test', 'type' => 'contact', 'category' => 'Coworkers', 'expectedResult' => 1],
				['user' => 'test', 'type' => 'contact', 'category' => 'Coworkers', 'expectedResult' => 0],
				['user' => 'test', 'type' => 'contact', 'category' => 'School',    'expectedResult' => 1],
		];

		foreach ($categoryEntries as $entry) {
			$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table3,
				[
					'uid' => $entry['user'],
					'type' => $entry['type'],
					'category' => $entry['category'],
				]);
			$this->assertEquals($entry['expectedResult'], $result);
		}

		$query = OC_DB::prepare('SELECT * FROM `*PREFIX*'.$this->table3.'`');
		$result = $query->execute();
		$this->assertTrue((bool)$result);
		$this->assertCount(4, $result->fetchAll());
	}

	public function testInsertIfNotExistNull() {
		$categoryEntries = [
			['addressbookid' => 123, 'fullname' => null, 'expectedResult' => 1],
			['addressbookid' => 123, 'fullname' => null, 'expectedResult' => 0],
			['addressbookid' => 123, 'fullname' => 'test', 'expectedResult' => 1],
		];

		foreach ($categoryEntries as $entry) {
			$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table2,
				[
					'addressbookid' => $entry['addressbookid'],
					'fullname' => $entry['fullname'],
				]);
			$this->assertEquals($entry['expectedResult'], $result);
		}

		$query = OC_DB::prepare('SELECT * FROM `*PREFIX*'.$this->table2.'`');
		$result = $query->execute();
		$this->assertTrue((bool)$result);
		$this->assertCount(2, $result->fetchAll());
	}

	public function testInsertIfNotExistDonTOverwrite() {
		$fullName = 'fullname test';
		$uri = 'uri_1';
		$carddata = 'This is a vCard';

		// Normal test to have same known data inserted.
		$query = OC_DB::prepare('INSERT INTO `*PREFIX*'.$this->table2.'` (`fullname`, `uri`, `carddata`) VALUES (?, ?, ?)');
		$result = $query->execute([$fullName, $uri, $carddata]);
		$this->assertEquals(1, $result);
		$query = OC_DB::prepare('SELECT `fullname`, `uri`, `carddata` FROM `*PREFIX*'.$this->table2.'` WHERE `uri` = ?');
		$result = $query->execute([$uri]);
		$this->assertTrue((bool)$result);
		$rowset = $result->fetchAll();
		$this->assertCount(1, $rowset);
		$this->assertArrayHasKey('carddata', $rowset[0]);
		$this->assertEquals($carddata, $rowset[0]['carddata']);

		// Try to insert a new row
		$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table2,
			[
				'fullname' => $fullName,
				'uri' => $uri,
			]);
		$this->assertEquals(0, $result);

		$query = OC_DB::prepare('SELECT `fullname`, `uri`, `carddata` FROM `*PREFIX*'.$this->table2.'` WHERE `uri` = ?');
		$result = $query->execute([$uri]);
		$this->assertTrue((bool)$result);
		// Test that previously inserted data isn't overwritten
		// And that a new row hasn't been inserted.
		$rowset = $result->fetchAll();
		$this->assertCount(1, $rowset);
		$this->assertArrayHasKey('carddata', $rowset[0]);
		$this->assertEquals($carddata, $rowset[0]['carddata']);
	}

	public function testInsertIfNotExistsViolating() {
		$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table5,
			[
				'storage' => 1,
				'path_hash' => \md5('welcome.txt'),
				'etag' => $this->getUniqueID()
			]);
		$this->assertEquals(1, $result);

		$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table5,
			[
				'storage' => 1,
				'path_hash' => \md5('welcome.txt'),
				'etag' => $this->getUniqueID()
			], ['storage', 'path_hash']);

		$this->assertEquals(0, $result);
	}

	public function insertIfNotExistsViolatingThrows() {
		return [
			[null],
			[['etag']],
		];
	}

	/**
	 * @dataProvider insertIfNotExistsViolatingThrows
	 *
	 * @param array $compareKeys
	 */
	public function testInsertIfNotExistsViolatingThrows($compareKeys) {
		$this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

		$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table5,
			[
				'storage' => 1,
				'path_hash' => \md5('welcome.txt'),
				'etag' => $this->getUniqueID()
			]);
		$this->assertEquals(1, $result);

		$result = \OCP\DB::insertIfNotExist('*PREFIX*'.$this->table5,
			[
				'storage' => 1,
				'path_hash' => \md5('welcome.txt'),
				'etag' => $this->getUniqueID()
			], $compareKeys);

		$this->assertEquals(0, $result);
	}

	public function testUtf8Data() {
		$table = "*PREFIX*{$this->table2}";
		$expected = "Ћö雙喜\xE2\x80\xA2";

		$query = OC_DB::prepare("INSERT INTO `$table` (`fullname`, `uri`, `carddata`) VALUES (?, ?, ?)");
		$result = $query->execute([$expected, 'uri_1', 'This is a vCard']);
		$this->assertEquals(1, $result);

		$actual = OC_DB::prepare("SELECT `fullname` FROM `$table`")->execute()->fetchOne();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Insert, select and delete decimal(12,2) values
	 * @dataProvider decimalData
	 */
	public function testDecimal($insert, $expect1, $expect2 = null) {
		$table = "*PREFIX*" . $this->table4;
		$rowname = 'decimaltest';

		$query = OC_DB::prepare('INSERT INTO `' . $table . '` (`' . $rowname . '`) VALUES (?)');
		$result = $query->execute([$insert]);
		$this->assertEquals(1, $result);
		$query = OC_DB::prepare('SELECT `' . $rowname . '` FROM `' . $table . '`');
		$result = $query->execute();
		$this->assertTrue((bool)$result);
		$row = $result->fetchRow();
		$this->assertArrayHasKey($rowname, $row);
		if ($expect2 === null) {
			$this->assertEquals($expect1, $row[$rowname]);
		} else {
			$this->assertThat(
				$row[$rowname],
				$this->logicalOr(
					$this->equalTo($expect1),
					$this->equalTo($expect2)
				)
			);
		}
		$query = OC_DB::prepare('DELETE FROM `' . $table . '`');
		$result = $query->execute();
		$this->assertTrue((bool)$result);
	}

	public function decimalData() {
		return [
			['1337133713.37', '1337133713.37'],
			['1234567890', '1234567890.00', '1234567890'],
		];
	}

	public function testUpdateAffectedRowsNoMatch() {
		$this->insertCardData('fullname1', 'uri1');
		// The WHERE clause does not match any rows
		$this->assertSame(0, $this->updateCardData('fullname3', 'uri2'));
	}

	public function testUpdateAffectedRowsDifferent() {
		$this->insertCardData('fullname1', 'uri1');
		// The WHERE clause matches a single row and the value we are updating
		// is different from the one already present.
		$this->assertSame(1, $this->updateCardData('fullname1', 'uri2'));
	}

	public function testUpdateAffectedRowsSame() {
		$this->insertCardData('fullname1', 'uri1');
		// The WHERE clause matches a single row and the value we are updating
		// to is the same as the one already present. MySQL reports 0 here when
		// the PDO::MYSQL_ATTR_FOUND_ROWS flag is not specified.
		$this->assertSame(1, $this->updateCardData('fullname1', 'uri1'));
	}

	public function testUpdateAffectedRowsMultiple() {
		$this->insertCardData('fullname1', 'uri1');
		$this->insertCardData('fullname2', 'uri2');
		// The WHERE clause matches two rows. One row contains a value that
		// needs to be updated, the other one already contains the value we are
		// updating to. MySQL reports 1 here when the PDO::MYSQL_ATTR_FOUND_ROWS
		// flag is not specified.
		$query = OC_DB::prepare("UPDATE `*PREFIX*{$this->table2}` SET `uri` = ?");
		$this->assertSame(2, $query->execute(['uri1']));
	}

	protected function insertCardData($fullname, $uri) {
		$query = OC_DB::prepare("INSERT INTO `*PREFIX*{$this->table2}` (`fullname`, `uri`, `carddata`) VALUES (?, ?, ?)");
		$this->assertSame(1, $query->execute([$fullname, $uri, $this->getUniqueID()]));
	}

	protected function updateCardData($fullname, $uri) {
		$query = OC_DB::prepare("UPDATE `*PREFIX*{$this->table2}` SET `uri` = ? WHERE `fullname` = ?");
		return $query->execute([$uri, $fullname]);
	}

	public function testILIKE() {
		$table = "*PREFIX*{$this->table2}";

		$query = OC_DB::prepare("INSERT INTO `$table` (`fullname`, `uri`, `carddata`) VALUES (?, ?, ?)");
		$query->execute(['fooBAR', 'foo', 'bar']);

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` LIKE ?");
		$result = $query->execute(['foobar']);
		$this->assertCount(0, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` ILIKE ?");
		$result = $query->execute(['foobar']);
		$this->assertCount(1, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` ILIKE ?");
		$result = $query->execute(['foo']);
		$this->assertCount(0, $result->fetchAll());
	}

	public function testILIKEWildcard() {
		$table = "*PREFIX*{$this->table2}";

		$query = OC_DB::prepare("INSERT INTO `$table` (`fullname`, `uri`, `carddata`) VALUES (?, ?, ?)");
		$query->execute(['FooBAR', 'foo', 'bar']);

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` LIKE ?");
		$result = $query->execute(['%bar']);
		$this->assertCount(0, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` LIKE ?");
		$result = $query->execute(['foo%']);
		$this->assertCount(0, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` LIKE ?");
		$result = $query->execute(['%ba%']);
		$this->assertCount(0, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` ILIKE ?");
		$result = $query->execute(['%bar']);
		$this->assertCount(1, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` ILIKE ?");
		$result = $query->execute(['foo%']);
		$this->assertCount(1, $result->fetchAll());

		$query = OC_DB::prepare("SELECT * FROM `$table` WHERE `fullname` ILIKE ?");
		$result = $query->execute(['%ba%']);
		$this->assertCount(1, $result->fetchAll());
	}

	/**
	 * @dataProvider insertAndSelectDataProvider
	 */
	public function testInsertAndSelectData($expected, $requires4ByteSupport) {
		$connection = \OC::$server->getDatabaseConnection();
		if ($requires4ByteSupport && !$connection->allows4ByteCharacters()) {
			$this->markTestSkipped('Test requires 4-byte support which is not present');
		}

		$table = "*PREFIX*{$this->text_table}";

		$query = OC_DB::prepare("INSERT INTO `$table` (`textfield`) VALUES (?)");
		$result = $query->execute([$expected]);
		$this->assertEquals(1, $result);

		$actual = OC_DB::prepare("SELECT `textfield` FROM `$table`")->execute()->fetchOne();
		$this->assertSame($expected, $actual);
	}

	public function insertAndSelectDataProvider() {
		return [
			['abcdefghijklmnopqrstuvwxyzABCDEFGHIKLMNOPQRSTUVWXYZ', false],
			['0123456789', false],
			['äöüÄÖÜß!"§$%&/()=?#\'+*~°^`´', false],
			['²³¼½¬{[]}\\', false],
			['♡⚗', false],
			['💩', true], # :hankey: on github
		];
	}
}
