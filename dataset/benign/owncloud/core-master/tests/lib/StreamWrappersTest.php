<?php
/**
 * ownCloud
 *
 * @author Robin Appelman
 * @copyright Copyright (c) 2012 Robin Appelman icewind@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Test;

/**
 * Class StreamWrappersTest
 *
 * @group DB
 */
class StreamWrappersTest extends \Test\TestCase {
	private static $trashBinStatus;

	public static function setUpBeforeClass(): void {
		self::$trashBinStatus = \OC_App::isEnabled('files_trashbin');
		\OC_App::disable('files_trashbin');
	}

	public static function tearDownAfterClass(): void {
		if (self::$trashBinStatus) {
			\OC_App::enable('files_trashbin');
		}
	}

	public function testFakeDir() {
		$items = ['foo', 'bar'];
		\OC\Files\Stream\Dir::register('test', $items);
		$dh = \opendir('fakedir://test');
		$result = [];
		while ($file = \readdir($dh)) {
			$result[] = $file;
			$this->assertContains($file, $items);
		}
		$this->assertCount(\count($items), $result);
	}

	public function testCloseStream() {
		//ensure all basic stream stuff works
		$sourceFile = \OC::$SERVERROOT . '/tests/data/lorem.txt';
		$tmpFile = \OC::$server->getTempManager()->getTemporaryFile('.txt');
		$file = 'close://' . $tmpFile;
		$this->assertFileExists($file);
		\file_put_contents($file, \file_get_contents($sourceFile));
		$this->assertFileEquals($sourceFile, $file);
		\unlink($file);
		\clearstatcache();
		$this->assertFileNotExists($file);

		//test callback
		$tmpFile = \OC::$server->getTempManager()->getTemporaryFile('.txt');
		$file = 'close://' . $tmpFile;
		$actual = false;
		$callback = function ($path) use (&$actual) {
			$actual = $path;
		};
		\OC\Files\Stream\Close::registerCallback($tmpFile, $callback);
		$fh = \fopen($file, 'w');
		\fwrite($fh, 'asd');
		\fclose($fh);
		$this->assertSame($tmpFile, $actual);
	}
}
