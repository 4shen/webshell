<?php

/**
 * Copyright (c) 2015 Vincent Petry <pvince81@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
*/

namespace Test\SystemTag;

use OC\SystemTag\SystemTagManager;
use OC\SystemTag\SystemTagObjectMapper;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\SystemTag\ISystemTag;
use OCP\SystemTag\ISystemTagManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Test\TestCase;

/**
 * Class TestSystemTagManager
 *
 * @group DB
 * @package Test\SystemTag
 */
class SystemTagManagerTest extends TestCase {

	/**
	 * @var ISystemTagManager
	 **/
	private $tagManager;

	/**
	 * @var IDBConnection
	 */
	private $connection;

	/**
	 * @var IGroupManager
	 */
	private $groupManager;

	/**
	 * @var EventDispatcherInterface
	 */
	private $dispatcher;

	public function setUp(): void {
		parent::setUp();

		$this->connection = \OC::$server->getDatabaseConnection();

		$this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->getMock();

		$this->groupManager = $this->getMockBuilder('\OCP\IGroupManager')->getMock();

		$this->tagManager = new SystemTagManager(
			$this->connection,
			$this->groupManager,
			$this->dispatcher
		);
		$this->pruneTagsTables();
	}

	public function tearDown(): void {
		$this->pruneTagsTables();
		parent::tearDown();
	}

	protected function pruneTagsTables() {
		$query = $this->connection->getQueryBuilder();
		$query->delete(SystemTagObjectMapper::RELATION_TABLE)->execute();
		$query->delete(SystemTagManager::TAG_TABLE)->execute();
	}

	public function getAllTagsDataProvider() {
		return [
			[
				// no tags at all
				[]
			],
			[
				// simple
				[
					['one', false, false, false],
					['two', false, false, false],
				]
			],
			[
				// duplicate names, different flags
				[
					['one', false, false, false],
					['one', true, false, false],
					['one', false, true, true],
					['one', true, true, true],
					['two', false, false, true],
					['two', false, true, false],
				]
			]
		];
	}

	/**
	 * @dataProvider getAllTagsDataProvider
	 */
	public function testGetAllTags($testTags) {
		$testTagsById = [];
		foreach ($testTags as $testTag) {
			$tag = $this->tagManager->createTag($testTag[0], $testTag[1], $testTag[2], $testTag[3]);
			$testTagsById[$tag->getId()] = $tag;
		}

		$tagList = $this->tagManager->getAllTags();

		$this->assertCount(\count($testTags), $tagList);

		foreach ($testTagsById as $testTagId => $testTag) {
			$this->assertArrayHasKey($testTagId, $tagList);
			$this->assertSameTag($tagList[$testTagId], $testTag);
		}
	}

	public function getAllTagsFilteredDataProvider() {
		return [
			[
				[
					// no tags at all
				],
				null,
				null,
				[]
			],
			// filter by visible only
			[
				// none visible
				[
					['one', false, false, false],
					['two', false, false, false],
				],
				true,
				null,
				[]
			],
			[
				// one visible
				[
					['one', true, false, true],
					['two', false, false, true],
				],
				true,
				null,
				[
					['one', true, false, true],
				]
			],
			[
				// one invisible
				[
					['one', true, false, true],
					['two', false, false, true],
				],
				false,
				null,
				[
					['two', false, false, true],
				]
			],
			// filter by name pattern
			[
				[
					['one', true, false, false],
					['one', false, false, false],
					['two', true, false, false],
				],
				null,
				'on',
				[
					['one', true, false, true],
					['one', false, false, true],
				]
			],
			// filter by name pattern and visibility
			[
				// one visible
				[
					['one', true, false, false],
					['two', true, false, false],
					['one', false, false, false],
				],
				true,
				'on',
				[
					['one', true, false, true],
				]
			],
			// filter by name pattern in the middle
			[
				// one visible
				[
					['abcdefghi', true, false, false],
					['two', true, false, false],
				],
				null,
				'def',
				[
					['abcdefghi', true, false, true],
				]
			]
		];
	}

	/**
	 * @dataProvider getAllTagsFilteredDataProvider
	 */
	public function testGetAllTagsFiltered($testTags, $visibilityFilter, $nameSearch, $expectedResults) {
		foreach ($testTags as $testTag) {
			$this->tagManager->createTag($testTag[0], $testTag[1], $testTag[2]);
		}

		$testTagsById = [];
		foreach ($expectedResults as $expectedTag) {
			$tag = $this->tagManager->getTag($expectedTag[0], $expectedTag[1], $expectedTag[2]);
			$testTagsById[$tag->getId()] = $tag;
		}

		$tagList = $this->tagManager->getAllTags($visibilityFilter, $nameSearch);

		$this->assertCount(\count($testTagsById), $tagList);

		foreach ($testTagsById as $testTagId => $testTag) {
			$this->assertArrayHasKey($testTagId, $tagList);
			$this->assertSameTag($tagList[$testTagId], $testTag);
		}
	}

	public function oneTagMultipleFlagsProvider() {
		return [
			['one', false, false, false],
			['one', true, false, false],
			['one', false, true, true],
			['one', true, true, true],
		];
	}

	/**
	 * @dataProvider oneTagMultipleFlagsProvider
	 */
	public function testCreateDuplicate($name, $userVisible, $userAssignable) {
		$this->expectException(\OCP\SystemTag\TagAlreadyExistsException::class);

		try {
			$this->tagManager->createTag($name, $userVisible, $userAssignable);
		} catch (\Exception $e) {
			$this->assertTrue(false, 'No exception thrown for the first create call');
		}
		$this->tagManager->createTag($name, $userVisible, $userAssignable);
	}

	/**
	 * @dataProvider oneTagMultipleFlagsProvider
	 */
	public function testGetExistingTag($name, $userVisible, $userAssignable, $userEditable) {
		$tag1 = $this->tagManager->createTag($name, $userVisible, $userAssignable, $userEditable);
		if ($userEditable === false) {
			$userEditable = true;
		}
		$tag2 = $this->tagManager->getTag($name, $userVisible, $userAssignable, $userEditable);

		$this->assertSameTag($tag1, $tag2);
	}

	public function testGetExistingTagById() {
		$tag1 = $this->tagManager->createTag('one', true, false);
		$tag2 = $this->tagManager->createTag('two', false, true);

		$tagList = $this->tagManager->getTagsByIds([$tag1->getId(), $tag2->getId()]);

		$this->assertCount(2, $tagList);

		$this->assertSameTag($tag1, $tagList[$tag1->getId()]);
		$this->assertSameTag($tag2, $tagList[$tag2->getId()]);
	}

	/**
	 */
	public function testGetNonExistingTag() {
		$this->expectException(\OCP\SystemTag\TagNotFoundException::class);

		$this->tagManager->getTag('nonexist', false, false);
	}

	/**
	 */
	public function testGetNonExistingTagsById() {
		$this->expectException(\OCP\SystemTag\TagNotFoundException::class);

		$tag1 = $this->tagManager->createTag('one', true, false);
		$this->tagManager->getTagsByIds([$tag1->getId(), 100, 101]);
	}

	/**
	 */
	public function testGetInvalidTagIdFormat() {
		$this->expectException(\InvalidArgumentException::class);

		$tag1 = $this->tagManager->createTag('one', true, false);
		$this->tagManager->getTagsByIds([$tag1->getId() . 'suffix']);
	}

	public function updateTagProvider() {
		return [
			[
				// update name
				['one', true, true, true],
				['two', true, true, true]
			],
			[
				// update one flag
				['one', false, true, true],
				['one', true, true, true]
			],
			[
				// update all flags
				['one', false, false, false],
				['one', true, true, true]
			],
			[
				// update all
				['one', false, false, false],
				['two', true, true, true]
			],
		];
	}

	/**
	 * @dataProvider updateTagProvider
	 */
	public function testUpdateTag($tagCreate, $tagUpdated) {
		$tag1 = $this->tagManager->createTag(
			$tagCreate[0],
			$tagCreate[1],
			$tagCreate[2],
			$tagCreate[3]
		);
		$this->tagManager->updateTag(
			$tag1->getId(),
			$tagUpdated[0],
			$tagUpdated[1],
			$tagUpdated[2],
			$tagUpdated[3]
		);
		$tag2 = $this->tagManager->getTag(
			$tagUpdated[0],
			$tagUpdated[1],
			$tagUpdated[2],
			$tagUpdated[3]
		);

		$this->assertEquals($tag2->getId(), $tag1->getId());
		$this->assertEquals($tag2->getName(), $tagUpdated[0]);
		$this->assertEquals($tag2->isUserVisible(), $tagUpdated[1]);
		$this->assertEquals($tag2->isUserAssignable(), $tagUpdated[2]);
		$this->assertEquals($tag2->isUserEditable(), $tagUpdated[3]);
	}

	/**
	 * @dataProvider updateTagProvider
	 */
	public function testUpdateTagDuplicate($tagCreate, $tagUpdated) {
		$this->expectException(\OCP\SystemTag\TagAlreadyExistsException::class);

		$this->tagManager->createTag(
			$tagCreate[0],
			$tagCreate[1],
			$tagCreate[2],
			$tagCreate[3]
		);
		$tag2 = $this->tagManager->createTag(
			$tagUpdated[0],
			$tagUpdated[1],
			$tagUpdated[2],
			$tagUpdated[3]
		);

		if ($tagCreate[3] === false) {
			$tagCreate[3] = true;
		}
		// update to match the first tag
		$this->tagManager->updateTag(
			$tag2->getId(),
			$tagCreate[0],
			$tagCreate[1],
			$tagCreate[3],
			$tagCreate[2]
		);
	}

	public function testDeleteTags() {
		$tag1 = $this->tagManager->createTag('one', true, false);
		$tag2 = $this->tagManager->createTag('two', false, true);

		$this->tagManager->deleteTags([$tag1->getId(), $tag2->getId()]);

		$this->assertEmpty($this->tagManager->getAllTags());
	}

	/**
	 */
	public function testDeleteNonExistingTag() {
		$this->expectException(\OCP\SystemTag\TagNotFoundException::class);

		$this->tagManager->deleteTags([100]);
	}

	public function testDeleteTagRemovesRelations() {
		$tag1 = $this->tagManager->createTag('one', true, false);
		$tag2 = $this->tagManager->createTag('two', true, true);

		$tagMapper = new SystemTagObjectMapper($this->connection, $this->tagManager, $this->dispatcher);

		$tagMapper->assignTags(1, 'testtype', $tag1->getId());
		$tagMapper->assignTags(1, 'testtype', $tag2->getId());
		$tagMapper->assignTags(2, 'testtype', $tag1->getId());

		$this->tagManager->deleteTags($tag1->getId());

		$tagIdMapping = $tagMapper->getTagIdsForObjects(
			[1, 2],
			'testtype'
		);

		$this->assertEquals([
			1 => [$tag2->getId()],
			2 => [],
		], $tagIdMapping);
	}

	public function visibilityCheckProvider() {
		return [
			[false, false, false, false],
			[true, false, false, true],
			[false, false, true, true],
			[true, false, true, true],
		];
	}

	/**
	 * @dataProvider visibilityCheckProvider
	 */
	public function testVisibilityCheck($userVisible, $userAssignable, $isAdmin, $expectedResult) {
		$user = $this->getMockBuilder('\OCP\IUser')->getMock();
		$user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('test'));
		$tag1 = $this->tagManager->createTag('one', $userVisible, $userAssignable);

		$this->groupManager->expects($this->any())
			->method('isAdmin')
			->with('test')
			->will($this->returnValue($isAdmin));

		$this->assertEquals($expectedResult, $this->tagManager->canUserSeeTag($tag1, $user));
	}

	public function assignabilityCheckProvider() {
		return [
			// no groups
			[false, false, false, false, false],
			[true, false, true, false, false],
			[true, true, true, false, true],
			[false, true, true, false, false],
			// admin rulez
			[false, false, true, true, true],
			[false, true, true, true, true],
			[true, false, true, true, true],
			[true, true, true, true, true],
			// ignored groups
			[false, false, true, false, false, ['group1'], ['group1']],
			[true, true, true, false, true, ['group1'], ['group1']],
			[true, true, false, false, true, ['group1'], ['anothergroup']],
			[false, true, true, false, false, ['group1'], ['group1']],
			// admin has precedence over groups
			[false, false, true, true, true, ['group1'], ['anothergroup']],
			[false, true, true, true, true, ['group1'], ['anothergroup']],
			[true, false, true, true, true, ['group1'], ['anothergroup']],
			[true, true, true, true, true, ['group1'], ['anothergroup']],
			// groups only checked when visible and user non-assignable and non-admin
			[true, false, true, false, false, ['group1'], ['anothergroup1']],
			[true, false, true, false, true, ['group1'], ['group1']],
			[true, false, true, false, true, ['group1', 'group2'], ['group2', 'group3']],
		];
	}

	/**
	 * @dataProvider assignabilityCheckProvider
	 */
	public function testAssignabilityCheck($userVisible, $userAssignable, $userEditable, $isAdmin, $expectedResult, $userGroupIds = [], $tagGroupIds = []) {
		$user = $this->getMockBuilder('\OCP\IUser')->getMock();
		$user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('test'));
		$tag1 = $this->tagManager->createTag('one', $userVisible, $userAssignable, $userEditable);
		$this->tagManager->setTagGroups($tag1, $tagGroupIds);

		$this->groupManager->expects($this->any())
			->method('isAdmin')
			->with('test')
			->will($this->returnValue($isAdmin));
		$this->groupManager->expects($this->any())
			->method('getUserGroupIds')
			->with($user)
			->will($this->returnValue($userGroupIds));

		$this->assertEquals($expectedResult, $this->tagManager->canUserAssignTag($tag1, $user));
	}

	public function testTagGroups() {
		$tag1 = $this->tagManager->createTag('tag1', true, false);
		$tag2 = $this->tagManager->createTag('tag2', true, false);
		$this->tagManager->setTagGroups($tag1, ['group1', 'group2']);
		$this->tagManager->setTagGroups($tag2, ['group2', 'group3']);

		$this->assertEquals(['group1', 'group2'], $this->tagManager->getTagGroups($tag1));
		$this->assertEquals(['group2', 'group3'], $this->tagManager->getTagGroups($tag2));

		// change groups
		$this->tagManager->setTagGroups($tag1, ['group3', 'group4']);
		$this->tagManager->setTagGroups($tag2, []);

		$this->assertEquals(['group3', 'group4'], $this->tagManager->getTagGroups($tag1));
		$this->assertEquals([], $this->tagManager->getTagGroups($tag2));
	}

	/**
	 * empty groupIds should be ignored
	 */
	public function testEmptyTagGroup() {
		$tag1 = $this->tagManager->createTag('tag1', true, false);
		$this->tagManager->setTagGroups($tag1, ['']);
		$this->assertEquals([], $this->tagManager->getTagGroups($tag1));
	}

	/**
	 * @param ISystemTag $tag1
	 * @param ISystemTag $tag2
	 */
	private function assertSameTag($tag1, $tag2) {
		$this->assertEquals($tag1->getId(), $tag2->getId());
		$this->assertEquals($tag1->getName(), $tag2->getName());
		$this->assertEquals($tag1->isUserVisible(), $tag2->isUserVisible());
		$this->assertEquals($tag1->isUserAssignable(), $tag2->isUserAssignable());
	}

	public function provideCanUserUseStaticTagInGroup() {
		return [
			[['group1'], true, ['group1', 'group2'], true],
			[['group1'], false, ['group1', 'group2'], true],
			[['group1'], false, ['group2', 'group3'], false]
		];
	}

	/**
	 * @param $userGroups
	 * @param $isAdmin
	 * @param $tagGroups
	 * @param $expectedResult
	 * @dataProvider provideCanUserUseStaticTagInGroup
	 */
	public function testCanUserUseStaticTagInGroup($userGroups, $isAdmin, $tagGroups, $expectedResult) {
		$tag1 = $this->tagManager->createTag('tag1', true, false, true);
		if (!empty($tagGroups)) {
			$this->tagManager->setTagGroups($tag1, $tagGroups);
		}
		$user = $this->createMock(IUser::class);
		$this->groupManager
			->method('isAdmin')
			->willReturn($isAdmin);
		$this->groupManager
			->method('getUserGroupIds')
			->with($user)
			->willReturn($userGroups);
		$result = $this->tagManager->canUserUseStaticTagInGroup($tag1, $user);
		$this->assertEquals($result, $expectedResult);
	}

	public function provideTagsWithDefaultParams() {
		return [
			['visibleTag', true, true, ['invisibleTag', false, false]],
			['invisibleTag', false, false, ['visibleTag', true, true]],
			['restrictedTag', true, false, ['visibleTag', true, true]],
		];
	}

	/**
	 * @dataProvider provideTagsWithDefaultParams
	 */
	public function testCreateAndGetAndUpdateAPIWithoutLastArg($tagName, $userVisible, $userAssignable, $updateTag) {
		$createdTag = $this->tagManager->createTag($tagName, $userVisible, $userAssignable);
		$resultTag = $this->tagManager->getTag($tagName, $userVisible, $userAssignable);

		$this->assertEquals($createdTag->getName(), $resultTag->getName());
		$this->assertEquals($createdTag->getId(), $resultTag->getId());
		$this->assertEquals($createdTag->isUserVisible(), $resultTag->isUserVisible());
		$this->assertEquals($createdTag->isUserAssignable(), $resultTag->isUserAssignable());
		$this->assertEquals($createdTag->isUserEditable(), $resultTag->isUserEditable());

		$this->tagManager->updateTag($resultTag->getId(), $updateTag[0], $updateTag[1], $updateTag[2]);
		$retrieveTag = $this->tagManager->getTag($updateTag[0], $updateTag[1], $updateTag[2]);
		$this->assertEquals($retrieveTag->getName(), $updateTag[0]);
		$this->assertEquals($retrieveTag->isUserVisible(), $updateTag[1]);
		$this->assertEquals($retrieveTag->isUserAssignable(), $updateTag[2]);
	}
}
