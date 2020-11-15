<?php
/**
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
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

namespace OC\User\Sync;

use OCP\UserInterface;
use Test\TestCase;

/**
 * Class BackendUsersIteratorTest
 *
 * @package OC\User\Sync
 *
 * @see http://php.net/manual/en/class.iterator.php for the order of calls on an iterator
 */
class BackendUsersIteratorTest extends TestCase {

	/**
	 * @var UserInterface|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $backend;
	/**
	 * @var \Iterator
	 */
	private $iterator;

	public function setUp(): void {
		parent::setUp();

		$this->backend = $this->createMock(UserInterface::class);

		$this->iterator = new BackendUsersIterator($this->backend);
	}

	/**
	 * Iterators are initialized by a call to rewind
	 */
	public function testRewind() {
		$this->backend->expects($this->once())
			->method('getUsers')
			->with(
				$this->equalTo(''),					// all users
				$this->equalTo(UsersIterator::LIMIT),	// limit 500
				$this->equalTo(0)						// at the beginning
			)
			->willReturn(['user0']);
		$this->iterator->rewind();
		$this->assertTrue($this->iterator->valid());
		$this->assertEquals('user0', $this->iterator->current());
		$this->assertEquals(0, $this->iterator->key());
	}

	/**
	 * test no results case
	 */
	public function testNextNoResults() {
		$this->backend->expects($this->exactly(1))
			->method('getUsers')
			->with(
					$this->equalTo(''),					// all users
					$this->equalTo(UsersIterator::LIMIT),	// limit 500
					$this->equalTo(0)						// at the beginning

			)
			->willReturn([]);

		$this->iterator->rewind();
		$this->assertFalse($this->iterator->valid());
	}

	/**
	 * test three pages of results
	 */
	public function testNext() {

		// create pages for 1001 users (0..1000)
		$page1 = [];
		for ($i=0; $i<500; $i++) {
			$page1[] = "user$i";
		}
		$page2 = [];
		for ($i=500; $i<1000; $i++) {
			$page2[] = "user$i";
		}
		$page3 = ['user1000'];

		$this->backend->expects($this->exactly(3))
			->method('getUsers')
			->withConsecutive(
				[
					$this->equalTo(''),					// all users
					$this->equalTo(UsersIterator::LIMIT),	// limit 500
					$this->equalTo(0)						// at the beginning
				], [
					$this->equalTo(''),					// all users
					$this->equalTo(UsersIterator::LIMIT),	// limit 500
					$this->equalTo(500)					// second page
				], [
					$this->equalTo(''),					// all users
					$this->equalTo(UsersIterator::LIMIT),	// limit 500
					$this->equalTo(1000)					// last page
				]
			)
			->willReturnOnConsecutiveCalls($page1, $page2, $page3);

		// loop over iterator manually to check key() and valid()

		$this->iterator->rewind();
		$this->assertTrue($this->iterator->valid());
		$this->assertEquals('user0', $this->iterator->current());
		$this->assertEquals(0, $this->iterator->key());
		for ($i=1; $i<=1000; $i++) {
			$this->iterator->next();
			$this->assertTrue($this->iterator->valid());
			$this->assertEquals("user$i", $this->iterator->current());
			$this->assertEquals($i, $this->iterator->key());
		}
		$this->iterator->next();
		$this->assertFalse($this->iterator->valid());
	}

	/**
	 * test a page larger than the internal limit / page size of 500
	 */
	public function testOverLimit() {

		// create pages for 1201 users (0..1200)
		$page1 = [];
		for ($i=0; $i<600; $i++) {
			$page1[] = "user$i";
		}
		$page2 = [];
		for ($i=600; $i<1200; $i++) {
			$page2[] = "user$i";
		}
		$page3 = ['user1200'];

		$this->backend->expects($this->exactly(3))
			->method('getUsers')
			->withConsecutive(
				[
					$this->equalTo(''),					// all users
					$this->equalTo(UsersIterator::LIMIT),	// limit 500
					$this->equalTo(0)						// at the beginning
				], [
				$this->equalTo(''),					// all users
				$this->equalTo(UsersIterator::LIMIT),	// limit 500
				$this->equalTo(500)					// second page
			], [
					$this->equalTo(''),					// all users
					$this->equalTo(UsersIterator::LIMIT),	// limit 500
					$this->equalTo(1000)					// last page
				]
			)
			->willReturnOnConsecutiveCalls($page1, $page2, $page3);

		// loop over iterator manually to check key() and valid()

		$this->iterator->rewind();
		$this->assertTrue($this->iterator->valid());
		$this->assertEquals('user0', $this->iterator->current());
		$this->assertEquals(0, $this->iterator->key());
		for ($i=1; $i<=1200; $i++) {
			$this->iterator->next();
			$this->assertTrue($this->iterator->valid());
			$this->assertEquals("user$i", $this->iterator->current());
			$this->assertEquals($i, $this->iterator->key());
		}
		$this->iterator->next();
		$this->assertFalse($this->iterator->valid());
	}
}
