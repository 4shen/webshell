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
namespace OCA\DAV\Tests\unit\Connector\Sabre;

/**
 * Copyright (c) 2014 Vincent Petry <pvince81@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
class TagsPluginTest extends \Test\TestCase {
	const TAGS_PROPERTYNAME = \OCA\DAV\Connector\Sabre\TagsPlugin::TAGS_PROPERTYNAME;
	const FAVORITE_PROPERTYNAME = \OCA\DAV\Connector\Sabre\TagsPlugin::FAVORITE_PROPERTYNAME;
	const TAG_FAVORITE = \OCA\DAV\Connector\Sabre\TagsPlugin::TAG_FAVORITE;

	/**
	 * @var \Sabre\DAV\Server
	 */
	private $server;

	/**
	 * @var \Sabre\DAV\Tree
	 */
	private $tree;

	/**
	 * @var \OCP\ITagManager
	 */
	private $tagManager;

	/**
	 * @var \OCP\ITags
	 */
	private $tagger;

	/**
	 * @var \OCA\DAV\Connector\Sabre\TagsPlugin
	 */
	private $plugin;

	public function setUp(): void {
		parent::setUp();
		$this->server = new \Sabre\DAV\Server();
		$this->tree = $this->getMockBuilder('\Sabre\DAV\Tree')
			->disableOriginalConstructor()
			->getMock();
		$this->tagger = $this->createMock('\OCP\ITags');
		$this->tagManager = $this->createMock('\OCP\ITagManager');
		$this->tagManager->expects($this->any())
			->method('load')
			->with('files')
			->will($this->returnValue($this->tagger));
		$this->plugin = new \OCA\DAV\Connector\Sabre\TagsPlugin($this->tree, $this->tagManager);
		$this->plugin->initialize($this->server);
	}

	/**
	 * @dataProvider tagsGetPropertiesDataProvider
	 */
	public function testGetProperties($tags, $requestedProperties, $expectedProperties) {
		$node = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\Node')
			->disableOriginalConstructor()
			->getMock();
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(123));

		$expectedCallCount = 0;
		if (\count($requestedProperties) > 0) {
			$expectedCallCount = 1;
		}

		$this->tagger->expects($this->exactly($expectedCallCount))
			->method('getTagsForObjects')
			->with($this->equalTo([123]))
			->will($this->returnValue([123 => $tags]));

		$propFind = new \Sabre\DAV\PropFind(
			'/dummyPath',
			$requestedProperties,
			0
		);

		$this->plugin->handleGetProperties(
			$propFind,
			$node
		);

		$result = $propFind->getResultForMultiStatus();

		$this->assertEmpty($result[404]);
		unset($result[404]);
		$this->assertEquals($expectedProperties, $result);
	}

	/**
	 * @dataProvider tagsGetPropertiesDataProvider
	 */
	public function testPreloadThenGetProperties($tags, $requestedProperties, $expectedProperties) {
		$node1 = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\File')
			->disableOriginalConstructor()
			->getMock();
		$node1->expects($this->any())
			->method('getId')
			->will($this->returnValue(111));
		$node2 = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\File')
			->disableOriginalConstructor()
			->getMock();
		$node2->expects($this->any())
			->method('getId')
			->will($this->returnValue(222));

		$expectedCallCount = 0;
		if (\count($requestedProperties) > 0) {
			// this guarantees that getTagsForObjects
			// is only called once and then the tags
			// are cached
			$expectedCallCount = 1;
		}

		$node = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\Directory')
			->disableOriginalConstructor()
			->getMock();
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(123));
		$node->expects($this->exactly($expectedCallCount))
			->method('getChildren')
			->will($this->returnValue([$node1, $node2]));

		$this->tagger->expects($this->exactly($expectedCallCount))
			->method('getTagsForObjects')
			->with($this->equalTo([123, 111, 222]))
			->will($this->returnValue(
				[
					111 => $tags,
					123 => $tags
				]
			));

		// simulate sabre recursive PROPFIND traversal
		$propFindRoot = new \Sabre\DAV\PropFind(
			'/subdir',
			$requestedProperties,
			1
		);
		$propFind1 = new \Sabre\DAV\PropFind(
			'/subdir/test.txt',
			$requestedProperties,
			0
		);
		$propFind2 = new \Sabre\DAV\PropFind(
			'/subdir/test2.txt',
			$requestedProperties,
			0
		);

		$this->plugin->handleGetProperties(
			$propFindRoot,
			$node
		);
		$this->plugin->handleGetProperties(
			$propFind1,
			$node1
		);
		$this->plugin->handleGetProperties(
			$propFind2,
			$node2
		);

		$result = $propFind1->getResultForMultiStatus();

		$this->assertEmpty($result[404]);
		unset($result[404]);
		$this->assertEquals($expectedProperties, $result);
	}

	public function tagsGetPropertiesDataProvider() {
		return [
			// request both, receive both
			[
				['tag1', 'tag2', self::TAG_FAVORITE],
				[self::TAGS_PROPERTYNAME, self::FAVORITE_PROPERTYNAME],
				[
					200 => [
						self::TAGS_PROPERTYNAME => new \OCA\DAV\Connector\Sabre\TagList(['tag1', 'tag2']),
						self::FAVORITE_PROPERTYNAME => true,
					]
				]
			],
			// request tags alone
			[
				['tag1', 'tag2', self::TAG_FAVORITE],
				[self::TAGS_PROPERTYNAME],
				[
					200 => [
						self::TAGS_PROPERTYNAME => new \OCA\DAV\Connector\Sabre\TagList(['tag1', 'tag2']),
					]
				]
			],
			// request fav alone
			[
				['tag1', 'tag2', self::TAG_FAVORITE],
				[self::FAVORITE_PROPERTYNAME],
				[
					200 => [
						self::FAVORITE_PROPERTYNAME => true,
					]
				]
			],
			// request none
			[
				['tag1', 'tag2', self::TAG_FAVORITE],
				[],
				[
					200 => []
				],
			],
			// request both with none set, receive both
			[
				[],
				[self::TAGS_PROPERTYNAME, self::FAVORITE_PROPERTYNAME],
				[
					200 => [
						self::TAGS_PROPERTYNAME => new \OCA\DAV\Connector\Sabre\TagList([]),
						self::FAVORITE_PROPERTYNAME => false,
					]
				]
			],
		];
	}

	public function testUpdateTags() {
		// this test will replace the existing tags "tagremove" with "tag1" and "tag2"
		// and keep "tagkeep"
		$node = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\Node')
			->disableOriginalConstructor()
			->getMock();
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(123));

		$this->tree->expects($this->any())
			->method('getNodeForPath')
			->with('/dummypath')
			->will($this->returnValue($node));

		$this->tagger->expects($this->at(0))
			->method('getTagsForObjects')
			->with($this->equalTo([123]))
			->will($this->returnValue([123 => ['tagkeep', 'tagremove', self::TAG_FAVORITE]]));

		// then tag as tag1 and tag2
		$this->tagger->expects($this->at(1))
			->method('tagAs')
			->with(123, 'tag1');
		$this->tagger->expects($this->at(2))
			->method('tagAs')
			->with(123, 'tag2');

		// it will untag tag3
		$this->tagger->expects($this->at(3))
			->method('unTag')
			->with(123, 'tagremove');

		// properties to set
		$propPatch = new \Sabre\DAV\PropPatch([
			self::TAGS_PROPERTYNAME => new \OCA\DAV\Connector\Sabre\TagList(['tag1', 'tag2', 'tagkeep'])
		]);

		$this->plugin->handleUpdateProperties(
			'/dummypath',
			$propPatch
		);

		$propPatch->commit();

		// all requested properties removed, as they were processed already
		$this->assertEmpty($propPatch->getRemainingMutations());

		$result = $propPatch->getResult();
		$this->assertEquals(200, $result[self::TAGS_PROPERTYNAME]);
		$this->assertArrayNotHasKey(self::FAVORITE_PROPERTYNAME, $result);
	}

	public function testUpdateTagsFromScratch() {
		$node = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\Node')
			->disableOriginalConstructor()
			->getMock();
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(123));

		$this->tree->expects($this->any())
			->method('getNodeForPath')
			->with('/dummypath')
			->will($this->returnValue($node));

		$this->tagger->expects($this->at(0))
			->method('getTagsForObjects')
			->with($this->equalTo([123]))
			->will($this->returnValue([]));

		// then tag as tag1 and tag2
		$this->tagger->expects($this->at(1))
			->method('tagAs')
			->with(123, 'tag1');
		$this->tagger->expects($this->at(2))
			->method('tagAs')
			->with(123, 'tag2');

		// properties to set
		$propPatch = new \Sabre\DAV\PropPatch([
			self::TAGS_PROPERTYNAME => new \OCA\DAV\Connector\Sabre\TagList(['tag1', 'tag2', 'tagkeep'])
		]);

		$this->plugin->handleUpdateProperties(
			'/dummypath',
			$propPatch
		);

		$propPatch->commit();

		// all requested properties removed, as they were processed already
		$this->assertEmpty($propPatch->getRemainingMutations());

		$result = $propPatch->getResult();
		$this->assertEquals(200, $result[self::TAGS_PROPERTYNAME]);
		$this->assertFalse(false, isset($result[self::FAVORITE_PROPERTYNAME]));
	}

	public function testUpdateFav() {
		// this test will replace the existing tags "tagremove" with "tag1" and "tag2"
		// and keep "tagkeep"
		$node = $this->getMockBuilder('\OCA\DAV\Connector\Sabre\Node')
			->disableOriginalConstructor()
			->getMock();
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(123));

		$this->tree->expects($this->any())
			->method('getNodeForPath')
			->with('/dummypath')
			->will($this->returnValue($node));

		// set favorite tag
		$this->tagger->expects($this->once())
			->method('tagAs')
			->with(123, self::TAG_FAVORITE);

		// properties to set
		$propPatch = new \Sabre\DAV\PropPatch([
			self::FAVORITE_PROPERTYNAME => true
		]);

		$this->plugin->handleUpdateProperties(
			'/dummypath',
			$propPatch
		);

		$propPatch->commit();

		// all requested properties removed, as they were processed already
		$this->assertEmpty($propPatch->getRemainingMutations());

		$result = $propPatch->getResult();
		$this->assertFalse(false, isset($result[self::TAGS_PROPERTYNAME]));
		$this->assertEquals(200, isset($result[self::FAVORITE_PROPERTYNAME]));

		// unfavorite now
		// set favorite tag
		$this->tagger->expects($this->once())
			->method('unTag')
			->with(123, self::TAG_FAVORITE);

		// properties to set
		$propPatch = new \Sabre\DAV\PropPatch([
			self::FAVORITE_PROPERTYNAME => false
		]);

		$this->plugin->handleUpdateProperties(
			'/dummypath',
			$propPatch
		);

		$propPatch->commit();

		// all requested properties removed, as they were processed already
		$this->assertEmpty($propPatch->getRemainingMutations());

		$result = $propPatch->getResult();
		$this->assertFalse(false, isset($result[self::TAGS_PROPERTYNAME]));
		$this->assertEquals(200, isset($result[self::FAVORITE_PROPERTYNAME]));
	}
}
