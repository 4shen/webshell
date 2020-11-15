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

use OC\Files\Storage\Folder;
use OC\Files\Storage\Node;
use OCP\Files\Storage\IStorage;
use Test\TestCase;

/**
 * Class NodeTest
 *
 * @package Test\Files\Storage
 */
abstract class NodeTest extends TestCase {
	protected $viewDeleteMethod = 'unlink';
	protected $user;
	/**
	 * @var IStorage|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected $storage;

	protected function setUp(): void {
		parent::setUp();
		$this->storage = $this->createMock(IStorage::class);
		$this->storage->expects($this->any())
			->method('getId')
			->will($this->returnValue('test::storage'));
	}

	/**
	 * @param $path
	 * @param IStorage $storage
	 * @return Node
	 */
	abstract protected function createTestNode($path, IStorage $storage = null);

	public function testGetMimetype() {
		$this->storage->expects($this->once())
			->method('getMimeType')
			->with('/path')
			->willReturn('node/type');

		$node = $this->createTestNode('/path');
		self::assertSame('node/type', $node->getMimetype());
	}

	public function testGetMimePart() {
		$this->storage->expects($this->once())
			->method('getMimeType')
			->with('/path')
			->willReturn('node/type');

		$node = $this->createTestNode('/path');
		self::assertSame('node', $node->getMimePart());
	}

	public function testIsEncrypted() {
		$node = $this->createTestNode('/path');
		self::assertFalse($node->isEncrypted());
	}

	public function testGetType() {
		$this->storage->expects($this->exactly(2))
			->method('filetype')
			->willReturnOnConsecutiveCalls('file', 'dir');

		self::assertSame(\OCP\Files\FileInfo::TYPE_FILE, $this->createTestNode('/f1.txt')->getType());
		self::assertSame(\OCP\Files\FileInfo::TYPE_FOLDER, $this->createTestNode('/folder')->getType());
	}

	/**
	 */
	public function testGetTypeUnexpected() {
		$this->expectException(\UnexpectedValueException::class);

		$this->storage->expects($this->once())
			->method('filetype')
			->willReturn('link');

		$this->createTestNode('/f1.txt')->getType();
	}

	public function testIsCreatable() {
		$this->storage->expects($this->once())
			->method('isCreatable')
			->with('/path')
			->willReturn(true);

		$node = $this->createTestNode('/path');
		self::assertTrue($node->isCreatable());
	}

	public function testIsShared() {
		$node = $this->createTestNode('/path');
		self::assertFalse($node->isShared());
	}

	/**
	 */
	public function testIsMounted() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->isMounted();
	}

	/**
	 */
	public function testGetMountPoint() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->getMountPoint();
	}

	/**
	 */
	public function testGetOwner() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->getOwner();
	}

	/**
	 */
	public function testGetChecksum() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->getChecksum();
	}

	/**
	 */
	public function testMove() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->move('/target');
	}

	/**
	 */
	public function testCopy() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->copy('/target');
	}

	public function testTouch() {
		$this->storage->expects($this->once())
			->method('touch')
			->with('/path', 123);

		$node = $this->createTestNode('/path');
		$node->touch(123);
	}

	public function testGetStorage() {
		$node = $this->createTestNode('/path');
		self::assertSame($this->storage, $node->getStorage());
	}

	/**
	 */
	public function testGetPath() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->getPath();
	}

	public function testGetInternalPath() {
		$node = $this->createTestNode('/path');
		self::assertSame('/path', $node->getInternalPath());
	}

	/**
	 */
	public function testGetId() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->getId();
	}

	public function testStat() {
		$stat = ['inode' => 1];

		$this->storage->expects($this->once())
			->method('stat')
			->with('/path')
			->willReturn($stat);

		$node = $this->createTestNode('/path');
		self::assertSame($stat, $node->stat());
	}

	public function testGetMTime() {
		$this->storage->expects($this->once())
			->method('filemtime')
			->with('/path')
			->willReturn(123);

		$node = $this->createTestNode('/path');
		self::assertSame(123, $node->getMTime());
	}

	public function testGetSize() {
		$this->storage->expects($this->once())
			->method('filesize')
			->with('/path')
			->willReturn(123);

		$node = $this->createTestNode('/path');
		self::assertSame(123, $node->getSize());
	}

	public function testGetEtag() {
		$this->storage->expects($this->once())
			->method('getETag')
			->with('/path')
			->willReturn('"foo"');

		$node = $this->createTestNode('/path');
		self::assertSame('"foo"', $node->getEtag());
	}

	public function testGetPermissions() {
		$this->storage->expects($this->once())
			->method('getPermissions')
			->with('/path')
			->willReturn(32);

		$node = $this->createTestNode('/path');
		self::assertSame(32, $node->getPermissions());
	}

	public function testIsReadable() {
		$this->storage->expects($this->once())
			->method('isReadable')
			->with('/path')
			->willReturn(true);

		$node = $this->createTestNode('/path');
		self::assertTrue($node->isReadable());
	}

	public function testIsUpdateable() {
		$this->storage->expects($this->once())
			->method('isUpdatable')
			->with('/path')
			->willReturn(true);

		$node = $this->createTestNode('/path');
		self::assertTrue($node->isUpdateable());
	}

	public function testIsDeletable() {
		$this->storage->expects($this->once())
			->method('isDeletable')
			->with('/path')
			->willReturn(true);

		$node = $this->createTestNode('/path');
		self::assertTrue($node->isDeletable());
	}

	public function testIsShareable() {
		$node = $this->createTestNode('/path');
		self::assertFalse($node->isShareable());
	}

	public function testGetParent() {
		$node = $this->createTestNode('/path');
		$root = $node->getParent();
		self::assertInstanceOf(Folder::class, $root);
		self::assertSame('/', $root->getInternalPath());
	}

	/**
	 */
	public function testGetParentNotExisting() {
		$this->expectException(\OCP\Files\NotFoundException::class);

		$root = $this->createTestNode('');
		$root->getParent();
	}

	public function testGetName() {
		$node = $this->createTestNode('/path');
		self::assertSame('path', $node->getName());
	}

	/**
	 */
	public function testLock() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->lock(\OCP\Lock\ILockingProvider::LOCK_SHARED);
	}

	/**
	 */
	public function testChangeLock() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->changeLock(\OCP\Lock\ILockingProvider::LOCK_SHARED);
	}

	/**
	 */
	public function testUnlock() {
		$this->expectException(\OCP\Files\NotPermittedException::class);

		$this->createTestNode('/path')
			->unlock(\OCP\Lock\ILockingProvider::LOCK_SHARED);
	}
}
