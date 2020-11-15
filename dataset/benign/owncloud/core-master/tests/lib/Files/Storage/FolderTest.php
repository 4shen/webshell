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

namespace Test\Files\Storage;

use OC\Files\Storage\File;
use OC\Files\Storage\Folder;
use OCP\Files\Storage\IStorage;

/**
 * Class FolderTest
 *
 * @package Test\Files\Storage
 */
class FolderTest extends NodeTest {

	/**
	 * @param $path
	 * @param IStorage|\PHPUnit\Framework\MockObject\MockObject|null $storage
	 * @return Folder
	 */
	protected function createTestNode($path, IStorage $storage = null) {
		if ($storage === null) {
			$storage = $this->storage;
		}
		return new Folder($storage, $path);
	}

	/**
	 */
	public function testGetFullPath() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->getFullPath('/');
	}

	/**
	 */
	public function testGetRelativePath() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->getRelativePath('/');
	}

	/**
	 */
	public function testIsSubNode() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->isSubNode(new Folder($this->storage, '/foo'));
	}

	public function testGetDirectoryContent() {
		$tmpDir = \OC::$server->getTempManager()->getTemporaryFolder();
		$storage = new \OC\Files\Storage\Local(['datadir' => $tmpDir]);
		$storage->mkdir('sub');
		$storage->touch('sub/f1.txt');
		$storage->mkdir('sub/folder1');

		$node = $this->createTestNode('sub', $storage);
		$children = $node->getDirectoryListing();
		$this->assertCount(2, $children);
		foreach ($children as $child) {
			if ($child instanceof File) {
				$this->assertEquals('f1.txt', $child->getName());
			} elseif ($child instanceof Folder) {
				$this->assertEquals('folder1', $child->getName());
			}
		}
	}

	public function testGet() {
		$this->storage->expects($this->exactly(3))
			->method('filetype')
			->willReturnOnConsecutiveCalls('file', 'dir', 'link');

		$node = $this->createTestNode('sub');
		$file = $node->get('file.txt');
		self::assertInstanceOf(File::class, $file);
		self::assertSame('sub/file.txt', $file->getInternalPath());

		$folder = $node->get('folder');
		self::assertInstanceOf(Folder::class, $folder);
		self::assertSame('sub/folder', $folder->getInternalPath());

		self::assertNull($node->get('symbolic link'));
	}

	public function testNodeExists() {
		$this->storage->expects($this->exactly(3))
			->method('file_exists')
			->withConsecutive(['sub/file.txt'], ['sub/folder'], ['sub/folder'])
			->willReturnOnConsecutiveCalls(true, false, false);

		$node = $this->createTestNode('sub');
		self::assertTrue($node->nodeExists('file.txt'));
		self::assertFalse($node->nodeExists('folder'));
		self::assertFalse($node->nodeExists('folder/'));
	}

	public function testNewFolder() {
		$this->storage->expects($this->once())
			->method('mkdir')
			->with('sub/folder');

		$node = $this->createTestNode('sub');
		$folder = $node->newFolder('folder');
		self::assertInstanceOf(Folder::class, $folder);
		self::assertSame('sub/folder', $folder->getInternalPath());
	}

	public function testNewFile() {
		$this->storage->expects($this->once())
			->method('touch')
			->with('sub/file.txt');

		$node = $this->createTestNode('sub');
		$file = $node->newFile('file.txt');
		self::assertInstanceOf(File::class, $file);
		self::assertSame('sub/file.txt', $file->getInternalPath());
	}

	public function testDelete() {
		$this->storage->expects($this->once())
			->method('rmdir')
			->with('/folder');

		$node = $this->createTestNode('/folder');
		$node->delete();
	}

	/**
	 */
	public function testSearch() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->search('/foo');
	}

	/**
	 */
	public function testSearchByMime() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->searchByMime('text/plain');
	}

	/**
	 */
	public function testSearchByTag() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->searchByMime('text/plain');
	}

	/**
	 */
	public function testGetById() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->getById(1);
	}

	public function testGetFreeSpace() {
		$this->storage->expects($this->once())
			->method('free_space')
			->with('/')
			->willReturn(100);

		$folder = $this->createTestNode('/');
		self::assertSame(100, $folder->getFreeSpace());
	}

	/**
	 */
	public function testGetNonExistingName() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/')
			->getNonExistingName('name');
	}
}
