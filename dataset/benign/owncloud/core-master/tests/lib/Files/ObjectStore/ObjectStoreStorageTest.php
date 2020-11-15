<?php
/**
 * @author Jörn Friedrich Dreyer
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
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

namespace Test\Files\ObjectStore;

/**
 * Class ObjectStoreStorageTest
 *
 * @group DB
 *
 * @package Test\Files\Cache\ObjectStore
 */
abstract class ObjectStoreStorageTest extends \Test\Files\Storage\Storage {
	public function testStat() {
		$textFile = \OC::$SERVERROOT . '/tests/data/lorem.txt';
		$ctimeStart = \time();
		$this->instance->file_put_contents('/lorem.txt', \file_get_contents($textFile));
		$this->assertTrue($this->instance->isReadable('/lorem.txt'));
		$ctimeEnd = \time();
		$mTime = $this->instance->filemtime('/lorem.txt');

		// check that ($ctimeStart - 5) <= $mTime <= ($ctimeEnd + 1)
		$this->assertGreaterThanOrEqual(($ctimeStart - 5), $mTime);
		$this->assertLessThanOrEqual(($ctimeEnd + 1), $mTime);
		$this->assertEquals(\filesize($textFile), $this->instance->filesize('/lorem.txt'));

		$stat = $this->instance->stat('/lorem.txt');
		//only size and mtime are required in the result
		$this->assertEquals($stat['size'], $this->instance->filesize('/lorem.txt'));
		$this->assertEquals($stat['mtime'], $mTime);

		if ($this->instance->touch('/lorem.txt', 100) !== false) {
			$mTime = $this->instance->filemtime('/lorem.txt');
			$this->assertEquals($mTime, 100);
		}
	}

	public function testCheckUpdate() {
		$this->markTestSkipped('Detecting external changes is not supported on object storages');
	}

	/**
	 * @dataProvider copyAndMoveProvider
	 */
	public function testMove($source, $target) {
		$this->initSourceAndTarget($source);
		$sourceId = $this->instance->getCache()->getId(\ltrim('/', $source));
		$this->assertNotEquals(-1, $sourceId);

		$this->instance->rename($source, $target);

		$this->assertTrue($this->instance->file_exists($target), $target.' was not created');
		$this->assertFalse($this->instance->file_exists($source), $source.' still exists');
		$this->assertSameAsLorem($target);

		$targetId = $this->instance->getCache()->getId(\ltrim('/', $target));
		$this->assertSame($sourceId, $targetId, 'fileid must be stable on move or shares will break');
	}

	public function testRenameDirectory() {
		$this->instance->mkdir('source');
		$this->instance->file_put_contents('source/test1.txt', 'foo');
		$this->instance->file_put_contents('source/test2.txt', 'qwerty');
		$this->instance->mkdir('source/subfolder');
		$this->instance->file_put_contents('source/subfolder/test.txt', 'bar');
		$sourceId = $this->instance->getCache()->getId('source');
		$this->assertNotEquals(-1, $sourceId);
		$this->instance->rename('source', 'target');

		$this->assertFalse($this->instance->file_exists('source'));
		$this->assertFalse($this->instance->file_exists('source/test1.txt'));
		$this->assertFalse($this->instance->file_exists('source/test2.txt'));
		$this->assertFalse($this->instance->file_exists('source/subfolder'));
		$this->assertFalse($this->instance->file_exists('source/subfolder/test.txt'));

		$this->assertTrue($this->instance->file_exists('target'));
		$this->assertTrue($this->instance->file_exists('target/test1.txt'));
		$this->assertTrue($this->instance->file_exists('target/test2.txt'));
		$this->assertTrue($this->instance->file_exists('target/subfolder'));
		$this->assertTrue($this->instance->file_exists('target/subfolder/test.txt'));

		$this->assertEquals('foo', $this->instance->file_get_contents('target/test1.txt'));
		$this->assertEquals('qwerty', $this->instance->file_get_contents('target/test2.txt'));
		$this->assertEquals('bar', $this->instance->file_get_contents('target/subfolder/test.txt'));
		$targetId = $this->instance->getCache()->getId('target');
		$this->assertSame($sourceId, $targetId, 'fileid must be stable on move or shares will break');
	}

	public function testRenameOverWriteDirectory() {
		$this->instance->mkdir('source');
		$this->instance->file_put_contents('source/test1.txt', 'foo');
		$sourceId = $this->instance->getCache()->getId('source');
		$this->assertNotEquals(-1, $sourceId);

		$this->instance->mkdir('target');
		$this->instance->file_put_contents('target/test1.txt', 'bar');
		$this->instance->file_put_contents('target/test2.txt', 'bar');

		$this->instance->rename('source', 'target');

		$this->assertFalse($this->instance->file_exists('source'));
		$this->assertFalse($this->instance->file_exists('source/test1.txt'));
		$this->assertFalse($this->instance->file_exists('target/test2.txt'));
		$this->assertEquals('foo', $this->instance->file_get_contents('target/test1.txt'));
		$targetId = $this->instance->getCache()->getId('target');
		$this->assertSame($sourceId, $targetId, 'fileid must be stable on move or shares will break');
	}

	public function testRenameOverWriteDirectoryOverFile() {
		$this->instance->mkdir('source');
		$this->instance->file_put_contents('source/test1.txt', 'foo');
		$sourceId = $this->instance->getCache()->getId('source');
		$this->assertNotEquals(-1, $sourceId);

		$this->instance->file_put_contents('target', 'bar');

		$this->instance->rename('source', 'target');

		$this->assertFalse($this->instance->file_exists('source'));
		$this->assertFalse($this->instance->file_exists('source/test1.txt'));
		$this->assertEquals('foo', $this->instance->file_get_contents('target/test1.txt'));
		$targetId = $this->instance->getCache()->getId('target');
		$this->assertSame($sourceId, $targetId, 'fileid must be stable on move or shares will break');
	}
}
