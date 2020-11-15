<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
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

namespace OCA\DAV\Tests\unit\SystemTag;

class SystemTagsObjectTypeCollectionTest extends \Test\TestCase {

	/**
	 * @var \OCA\DAV\SystemTag\SystemTagsObjectTypeCollection
	 */
	private $node;

	/**
	 * @var \OCP\SystemTag\ISystemTagManager
	 */
	private $tagManager;

	/**
	 * @var \OCP\SystemTag\ISystemTagObjectMapper
	 */
	private $tagMapper;

	/**
	 * @var \OCP\Files\Folder
	 */
	private $userFolder;

	protected function setUp(): void {
		parent::setUp();

		$this->tagManager = $this->createMock('\OCP\SystemTag\ISystemTagManager');
		$this->tagMapper = $this->createMock('\OCP\SystemTag\ISystemTagObjectMapper');

		$user = $this->createMock('\OCP\IUser');
		$user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('testuser'));
		$userSession = $this->createMock('\OCP\IUserSession');
		$userSession->expects($this->any())
			->method('getUser')
			->will($this->returnValue($user));
		$groupManager = $this->createMock('\OCP\IGroupManager');
		$groupManager->expects($this->any())
			->method('isAdmin')
			->with('testuser')
			->will($this->returnValue(true));

		$this->userFolder = $this->createMock('\OCP\Files\Folder');

		$fileRoot = $this->createMock('\OCP\Files\IRootFolder');
		$fileRoot->expects($this->any())
			->method('getUserfolder')
			->with('testuser')
			->will($this->returnValue($this->userFolder));

		$this->node = new \OCA\DAV\SystemTag\SystemTagsObjectTypeCollection(
			'files',
			$this->tagManager,
			$this->tagMapper,
			$userSession,
			$groupManager,
			$fileRoot
		);
	}

	/**
	 */
	public function testForbiddenCreateFile() {
		$this->expectException(\Sabre\DAV\Exception\Forbidden::class);

		$this->node->createFile('555');
	}

	/**
	 */
	public function testForbiddenCreateDirectory() {
		$this->expectException(\Sabre\DAV\Exception\Forbidden::class);

		$this->node->createDirectory('789');
	}

	public function testGetChild() {
		$this->userFolder->expects($this->once())
			->method('getById')
			->with('555')
			->will($this->returnValue([true]));
		$childNode = $this->node->getChild('555');

		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagsObjectMappingCollection', $childNode);
		$this->assertEquals('555', $childNode->getName());
	}

	/**
	 */
	public function testGetChildWithoutAccess() {
		$this->expectException(\Sabre\DAV\Exception\NotFound::class);

		$this->userFolder->expects($this->once())
			->method('getById')
			->with('555')
			->will($this->returnValue([]));
		$this->node->getChild('555');
	}

	/**
	 */
	public function testGetChildren() {
		$this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

		$this->node->getChildren();
	}

	public function testChildExists() {
		$this->userFolder->expects($this->once())
			->method('getById')
			->with('123')
			->will($this->returnValue([true]));
		$this->assertTrue($this->node->childExists('123'));
	}

	public function testChildExistsWithoutAccess() {
		$this->userFolder->expects($this->once())
			->method('getById')
			->with('555')
			->will($this->returnValue([]));
		$this->assertFalse($this->node->childExists('555'));
	}

	/**
	 */
	public function testDelete() {
		$this->expectException(\Sabre\DAV\Exception\Forbidden::class);

		$this->node->delete();
	}

	/**
	 */
	public function testSetName() {
		$this->expectException(\Sabre\DAV\Exception\Forbidden::class);

		$this->node->setName('somethingelse');
	}

	public function testGetName() {
		$this->assertEquals('files', $this->node->getName());
	}
}
