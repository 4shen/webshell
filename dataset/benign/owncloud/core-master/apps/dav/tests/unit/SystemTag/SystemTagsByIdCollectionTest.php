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

use OC\SystemTag\SystemTag;
use OCP\IUser;
use OCP\SystemTag\TagNotFoundException;

class SystemTagsByIdCollectionTest extends \Test\TestCase {

	/**
	 * @var \OCP\SystemTag\ISystemTagManager
	 */
	private $tagManager;

	/**
	 * @var \OCP\IUser
	 */
	private $user;

	protected function setUp(): void {
		parent::setUp();

		$this->tagManager = $this->createMock('\OCP\SystemTag\ISystemTagManager');
	}

	public function getNode($isAdmin = true) {
		$this->user = $this->createMock('\OCP\IUser');
		$this->user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('testuser'));
		$userSession = $this->createMock('\OCP\IUserSession');
		$userSession->expects($this->any())
			->method('getUser')
			->will($this->returnValue($this->user));
		$groupManager = $this->createMock('\OCP\IGroupManager');
		$groupManager->expects($this->any())
			->method('isAdmin')
			->with('testuser')
			->will($this->returnValue($isAdmin));
		return new \OCA\DAV\SystemTag\SystemTagsByIdCollection(
			$this->tagManager,
			$userSession,
			$groupManager
		);
	}

	public function adminFlagProvider() {
		return [[true], [false]];
	}

	/**
	 */
	public function testForbiddenCreateFile() {
		$this->expectException(\Sabre\DAV\Exception\Forbidden::class);

		$this->getNode()->createFile('555');
	}

	/**
	 */
	public function testForbiddenCreateDirectory() {
		$this->expectException(\Sabre\DAV\Exception\Forbidden::class);

		$this->getNode()->createDirectory('789');
	}

	public function testGetChild() {
		$tag = new SystemTag(123, 'Test', true, false);
		$this->tagManager->expects($this->once())
			->method('canUserSeeTag')
			->with($tag)
			->will($this->returnValue(true));

		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['123'])
			->will($this->returnValue([$tag]));

		$childNode = $this->getNode()->getChild('123');

		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $childNode);
		$this->assertEquals('123', $childNode->getName());
		$this->assertEquals($tag, $childNode->getSystemTag());
	}

	/**
	 */
	public function testGetChildInvalidName() {
		$this->expectException(\Sabre\DAV\Exception\BadRequest::class);

		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['invalid'])
			->will($this->throwException(new \InvalidArgumentException()));

		$this->getNode()->getChild('invalid');
	}

	/**
	 */
	public function testGetChildNotFound() {
		$this->expectException(\Sabre\DAV\Exception\NotFound::class);

		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['444'])
			->will($this->throwException(new TagNotFoundException()));

		$this->getNode()->getChild('444');
	}

	/**
	 */
	public function testGetChildUserNotVisible() {
		$this->expectException(\Sabre\DAV\Exception\NotFound::class);

		$tag = new SystemTag(123, 'Test', false, false);

		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['123'])
			->will($this->returnValue([$tag]));

		$this->getNode(false)->getChild('123');
	}

	public function testGetChildrenAdmin() {
		$tag1 = new SystemTag(123, 'One', true, false, false);
		$tag2 = new SystemTag(456, 'Two', true, true, true);

		$this->tagManager->expects($this->once())
			->method('getAllTags')
			->with(null)
			->will($this->returnValue([$tag1, $tag2]));

		$children = $this->getNode(true)->getChildren();

		$this->assertCount(2, $children);

		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[0]);
		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[1]);
		$this->assertEquals($tag1, $children[0]->getSystemTag());
		$this->assertEquals($tag2, $children[1]->getSystemTag());
	}

	public function testGetChildrenNonAdmin() {
		$tag1 = new SystemTag(123, 'One', true, false, false);
		$tag2 = new SystemTag(456, 'Two', true, true, true);

		$this->tagManager->expects($this->once())
			->method('getAllTags')
			->with(true)
			->will($this->returnValue([$tag1, $tag2]));

		$children = $this->getNode(false)->getChildren();

		$this->assertCount(2, $children);

		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[0]);
		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[1]);
		$this->assertEquals($tag1, $children[0]->getSystemTag());
		$this->assertEquals($tag2, $children[1]->getSystemTag());
	}

	/**
	 * This test proves getChildren would provide staticTags if the user has the
	 * privilege to see the static tag
	 */
	public function testGetChildrenWithStaticTagsAndOtherTags() {
		$visibleTag = new SystemTag(123, 'VisibleTag', true, true, true);
		$restrictTag = new SystemTag(456, 'RestrictTag', true, false, false);
		$staticTag = new SystemTag(789, 'StaticTag', true, false, true);

		$this->tagManager->method('getAllTags')
			->with(true)
			->will($this->returnValue([$visibleTag, $restrictTag, $staticTag]));

		$user = $this->createMock(IUser::class);

		$this->tagManager->method('canUserUseStaticTagInGroup')
			->with($staticTag, $user)
			->willReturn(true);

		$children = $this->getNode(false)->getChildren();

		$this->assertCount(3, $children);
		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[0]);
		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[1]);
		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[2]);
		$this->assertEquals($visibleTag, $children[0]->getSystemTag());
		$this->assertEquals($restrictTag, $children[1]->getSystemTag());
		$this->assertEquals($staticTag, $children[2]->getSystemTag());
	}

	/**
	 * This test proves getChildren would prohibit staticTags if the user doesn't
	 * have the privilege to see the static tag
	 */
	public function testGetChildrenWithoutStaticTagsAndOtherTags() {
		$visibleTag = new SystemTag(123, 'VisibleTag', true, true, true);
		$restrictTag = new SystemTag(456, 'RestrictTag', true, false, false);
		$staticTag = new SystemTag(789, 'StaticTag', true, false, true);

		$this->tagManager->method('getAllTags')
			->with(true)
			->will($this->returnValue([$visibleTag, $restrictTag]));

		$user = $this->createMock(IUser::class);

		$this->tagManager->method('canUserUseStaticTagInGroup')
			->with($staticTag, $user)
			->willReturn(false);

		$children = $this->getNode(false)->getChildren();

		$this->assertCount(2, $children);

		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[0]);
		$this->assertInstanceOf('\OCA\DAV\SystemTag\SystemTagNode', $children[1]);
		$this->assertEquals($visibleTag, $children[0]->getSystemTag());
		$this->assertEquals($restrictTag, $children[1]->getSystemTag());
	}

	public function testGetChildrenEmpty() {
		$this->tagManager->expects($this->once())
			->method('getAllTags')
			->with(null)
			->will($this->returnValue([]));
		$this->assertCount(0, $this->getNode()->getChildren());
	}

	public function childExistsProvider() {
		return [
			[true, true],
			[false, false],
		];
	}

	/**
	 * @dataProvider childExistsProvider
	 */
	public function testChildExists($userVisible, $expectedResult) {
		$tag = new SystemTag(123, 'One', $userVisible, false);
		$this->tagManager->expects($this->once())
			->method('canUserSeeTag')
			->with($tag)
			->will($this->returnValue($userVisible));

		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['123'])
			->will($this->returnValue([$tag]));

		$this->assertEquals($expectedResult, $this->getNode()->childExists('123'));
	}

	public function testChildExistsNotFound() {
		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['123'])
			->will($this->throwException(new TagNotFoundException()));

		$this->assertFalse($this->getNode()->childExists('123'));
	}

	/**
	 */
	public function testChildExistsBadRequest() {
		$this->expectException(\Sabre\DAV\Exception\BadRequest::class);

		$this->tagManager->expects($this->once())
			->method('getTagsByIds')
			->with(['invalid'])
			->will($this->throwException(new \InvalidArgumentException()));

		$this->getNode()->childExists('invalid');
	}
}
