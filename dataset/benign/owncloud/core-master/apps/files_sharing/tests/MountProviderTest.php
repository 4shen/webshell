<?php
/**
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

namespace OCA\Files_Sharing\Tests;

use OCA\Files_Sharing\MountProvider;
use OCP\Files\Storage\IStorageFactory;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IUser;
use OCP\Share\IAttributes as IShareAttributes;
use OCP\Share\IManager;
use OCP\Share\IShare;

/**
 * @group DB
 */
class MountProviderTest extends \Test\TestCase {

	/** @var MountProvider */
	private $provider;

	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	private $config;

	/** @var IUser|\PHPUnit\Framework\MockObject\MockObject */
	private $user;

	/** @var IStorageFactory|\PHPUnit\Framework\MockObject\MockObject */
	private $loader;

	/** @var IManager|\PHPUnit\Framework\MockObject\MockObject */
	private $shareManager;

	/** @var ILogger | \PHPUnit\Framework\MockObject\MockObject */
	private $logger;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock('OCP\IConfig');
		$this->user = $this->createMock('OCP\IUser');
		$this->loader = $this->createMock('OCP\Files\Storage\IStorageFactory');
		$this->shareManager = $this->createMock('\OCP\Share\IManager');
		$this->logger = $this->createMock('\OCP\ILogger');

		$this->provider = new MountProvider($this->config, $this->shareManager, $this->logger);
	}

	private function makeMockShareAttributes($attrs) {
		if ($attrs === null) {
			return null;
		}

		$shareAttributes = $this->createMock(IShareAttributes::class);
		$shareAttributes->method('toArray')->willReturn($attrs);
		$shareAttributes->method('getAttribute')->will(
			$this->returnCallback(function ($scope, $key) use ($attrs) {
				$result = null;
				foreach ($attrs as $attr) {
					if ($attr['key'] === $key && $attr['scope'] === $scope) {
						$result = $attr['enabled'];
					}
				}
				return $result;
			})
		);
		return $shareAttributes;
	}

	private function makeMockShare($id, $nodeId, $owner = 'user2', $target = null, $permissions = 31, $attributes = [], $state = null) {
		$share = $this->createMock(IShare::class);
		$share->expects($this->any())
			->method('getPermissions')
			->will($this->returnValue($permissions));
		$share->expects($this->any())
			->method('getAttributes')
			->will($this->returnValue($this->makeMockShareAttributes($attributes)));
		$share->expects($this->any())
			->method('getShareOwner')
			->will($this->returnValue($owner));
		$share->expects($this->any())
			->method('getTarget')
			->will($this->returnValue($target));
		$share->expects($this->any())
			->method('getId')
			->will($this->returnValue($id));
		$share->expects($this->any())
			->method('getNodeId')
			->will($this->returnValue($nodeId));
		$share->expects($this->any())
			->method('getShareTime')
			->will($this->returnValue(
				// compute share time based on id, simulating share order
				new \DateTime('@' . (1469193980 + 1000 * $id))
			));

		if ($state === null) {
			$state = \OCP\Share::STATE_ACCEPTED;
		}
		$share->expects($this->any())
			->method('getState')
			->willReturn($state);
		return $share;
	}

	/**
	 * Tests excluding shares from the current view. This includes:
	 * - shares that were opted out of (permissions === 0)
	 * - shares with a group in which the owner is already in
	 * - rejected shares
	 * - pending shares
	 */
	public function testExcludeShares() {
		$rootFolder = $this->createMock('\OCP\Files\IRootFolder');
		$userManager = $this->createMock('\OCP\IUserManager');

		$attr1 = [];
		$attr2 = [["scope" => "permission", "key" => "download", "enabled" => true]];
		$userShares = [
			$this->makeMockShare(1, 100, 'user2', '/share2', 0, $attr1),
			$this->makeMockShare(2, 100, 'user2', '/share2', 31, $attr2),
			$this->makeMockShare(6, 100, 'user2', '/share2', 31, $attr2, \OCP\Share::STATE_PENDING),
			$this->makeMockShare(7, 100, 'user2', '/share2', 31, $attr2, \OCP\Share::STATE_REJECTED),
		];

		$groupShares = [
			$this->makeMockShare(3, 100, 'user2', '/share2', 0, $attr1),
			$this->makeMockShare(4, 101, 'user2', '/share4', 31, $attr2),
			$this->makeMockShare(5, 100, 'user1', '/share4', 31, $attr2),
		];

		$userGroupUserShares = \array_merge($userShares, $groupShares);

		$this->user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('user1'));

		$requiredShareTypes = [\OCP\Share::SHARE_TYPE_USER, \OCP\Share::SHARE_TYPE_GROUP];
		$this->shareManager->expects($this->once())
			->method('getAllSharedWith')
			->with('user1', $requiredShareTypes, null)
			->will($this->returnValue($userGroupUserShares));
		$this->shareManager->expects($this->never())
			->method('getSharedWith');
		$this->shareManager->expects($this->any())
			->method('newShare')
			->will($this->returnCallback(function () use ($rootFolder, $userManager) {
				return new \OC\Share20\Share($rootFolder, $userManager);
			}));

		$mounts = $this->provider->getMountsForUser($this->user, $this->loader);

		$this->assertCount(2, $mounts);
		$this->assertInstanceOf('OCA\Files_Sharing\SharedMount', $mounts[0]);
		$this->assertInstanceOf('OCA\Files_Sharing\SharedMount', $mounts[1]);

		$mountedShare1 = $mounts[0]->getShare();

		$this->assertEquals('2', $mountedShare1->getId());
		$this->assertEquals('user2', $mountedShare1->getShareOwner());
		$this->assertEquals(100, $mountedShare1->getNodeId());
		$this->assertEquals('/share2', $mountedShare1->getTarget());
		$this->assertEquals(31, $mountedShare1->getPermissions());
		$this->assertEquals(true, $mountedShare1->getAttributes()->getAttribute('permission', 'download'));

		$mountedShare2 = $mounts[1]->getShare();
		$this->assertEquals('4', $mountedShare2->getId());
		$this->assertEquals('user2', $mountedShare2->getShareOwner());
		$this->assertEquals(101, $mountedShare2->getNodeId());
		$this->assertEquals('/share4', $mountedShare2->getTarget());
		$this->assertEquals(31, $mountedShare2->getPermissions());
		$this->assertEquals(true, $mountedShare2->getAttributes()->getAttribute('permission', 'download'));
	}

	public function mergeSharesDataProvider() {
		// note: the user in the specs here is the shareOwner not recipient
		// the recipient is always "user1"
		return [
			// #0: share as outsider with "group1" and "user1" with same permissions
			[
				[
					[1, 100, 'user2', '/share2', 31, null],
				],
				[
					[2, 100, 'user2', '/share2', 31, null],
				],
				[
					// combined, user share has higher priority
					['1', 100, 'user2', '/share2', 31, []],
				],
			],
			// #1: share as outsider with "group1" and "user1" with different permissions
			[
				[
					[1, 100, 'user2', '/share', 31, [["scope" => "permission", "key" => "download", "enabled" => true], ["scope" => "app", "key" => "attribute1", "enabled" => true]]],
				],
				[
					[2, 100, 'user2', '/share', 15, [["scope" => "permission", "key" => "download", "enabled" => false], ["scope" => "app", "key" => "attribute2", "enabled" => false]]],
				],
				[
					// use highest permissions
					['1', 100, 'user2', '/share', 31, [["scope" => "permission", "key" => "download", "enabled" => true], ["scope" => "app", "key" => "attribute1", "enabled" => true], ["scope" => "app", "key" => "attribute2", "enabled" => false]]],
				],
			],
			// #2: share as outsider with "group1" and "group2" with same permissions
			[
				[
				],
				[
					[1, 100, 'user2', '/share', 31, null],
					[2, 100, 'user2', '/share', 31, []],
				],
				[
					// combined, first group share has higher priority
					['1', 100, 'user2', '/share', 31, []],
				],
			],
			// #3: share as outsider with "group1" and "group2" with different permissions
			[
				[
				],
				[
					[1, 100, 'user2', '/share', 31, [["scope" => "permission", "key" => "download", "enabled" => false]]],
					[2, 100, 'user2', '/share', 15, [["scope" => "permission", "key" => "download", "enabled" => true]]],
				],
				[
					// use higher permissions (most permissive)
					['1', 100, 'user2', '/share', 31, [["scope" => "permission", "key" => "download", "enabled" => true]]],
				],
			],
			// #4: share as insider with "group1"
			[
				[
				],
				[
					[1, 100, 'user1', '/share', 31, []],
				],
				[
					// no received share since "user1" is the sharer/owner
				],
			],
			// #5: share as insider with "group1" and "group2" with different permissions
			[
				[
				],
				[
					[1, 100, 'user1', '/share', 31, [["scope" => "permission", "key" => "download", "enabled" => true]]],
					[2, 100, 'user1', '/share', 15, [["scope" => "permission", "key" => "download", "enabled" => false]]],
				],
				[
					// no received share since "user1" is the sharer/owner
				],
			],
			// #6: share as outside with "group1", recipient opted out
			[
				[
				],
				[
					[1, 100, 'user2', '/share', 0, []],
				],
				[
					// no received share since "user1" opted out
				],
			],
			// #7: share as outsider with "group1" and "user1" where recipient renamed in between
			[
				[
					[1, 100, 'user2', '/share2-renamed', 31, []],
				],
				[
					[2, 100, 'user2', '/share2', 31, []],
				],
				[
					// use target of least recent share
					['1', 100, 'user2', '/share2-renamed', 31, []],
				],
			],
			// #8: share as outsider with "group1" and "user1" where recipient renamed in between
			[
				[
					[2, 100, 'user2', '/share2', 31, []],
				],
				[
					[1, 100, 'user2', '/share2-renamed', 31, []],
				],
				[
					// use target of least recent share
					['1', 100, 'user2', '/share2-renamed', 31, []],
				],
			],
			// #9: share as outsider with "nullgroup" and "user1" where recipient renamed in between
			[
				[
					[2, 100, 'user2', '/share2', 31, []],
				],
				[
					[1, 100, 'nullgroup', '/share2-renamed', 31, []],
				],
				[
					// use target of least recent share
					['1', 100, 'nullgroup', '/share2-renamed', 31, []],
				],
				true
			],
		];
	}

	/**
	 * Tests merging shares.
	 *
	 * Happens when sharing the same entry to a user through multiple ways,
	 * like several groups and also direct shares at the same time.
	 *
	 * @dataProvider mergeSharesDataProvider
	 *
	 * @param array $userShares array of user share specs
	 * @param array $groupShares array of group share specs
	 * @param array $expectedShares array of expected supershare specs
	 */
	public function testMergeShares($userShares, $groupShares, $expectedShares, $moveFails = false) {
		$rootFolder = $this->createMock('\OCP\Files\IRootFolder');
		$userManager = $this->createMock('\OCP\IUserManager');

		$userShares = \array_map(function ($shareSpec) {
			return $this->makeMockShare($shareSpec[0], $shareSpec[1], $shareSpec[2], $shareSpec[3], $shareSpec[4], $shareSpec[5]);
		}, $userShares);
		$groupShares = \array_map(function ($shareSpec) {
			return $this->makeMockShare($shareSpec[0], $shareSpec[1], $shareSpec[2], $shareSpec[3], $shareSpec[4], $shareSpec[5]);
		}, $groupShares);

		$this->user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('user1'));
		
		$userGroupUserShares = \array_merge($userShares, $groupShares);
		$requiredShareTypes = [\OCP\Share::SHARE_TYPE_USER, \OCP\Share::SHARE_TYPE_GROUP];
		$this->shareManager->expects($this->once())
			->method('getAllSharedWith')
			->with('user1', $requiredShareTypes, null)
			->will($this->returnValue($userGroupUserShares));

		$this->shareManager->expects($this->never())
			->method('getSharedWith');

		$this->shareManager->expects($this->any())
			->method('newShare')
			->will($this->returnCallback(function () use ($rootFolder, $userManager) {
				return new \OC\Share20\Share($rootFolder, $userManager);
			}));

		if ($moveFails) {
			$this->shareManager->expects($this->any())
				->method('moveShare')
				->will($this->throwException(new \InvalidArgumentException()));
		}

		$mounts = $this->provider->getMountsForUser($this->user, $this->loader);

		$this->assertCount(\count($expectedShares), $mounts);

		foreach ($mounts as $index => $mount) {
			$expectedShare = $expectedShares[$index];
			$this->assertInstanceOf('OCA\Files_Sharing\SharedMount', $mount);

			// supershare
			$share = $mount->getShare();

			$this->assertEquals($expectedShare[0], $share->getId());
			$this->assertEquals($expectedShare[1], $share->getNodeId());
			$this->assertEquals($expectedShare[2], $share->getShareOwner());
			$this->assertEquals($expectedShare[3], $share->getTarget());
			$this->assertEquals($expectedShare[4], $share->getPermissions());
			if ($expectedShare[5] === null) {
				$this->assertNull($share->getAttributes());
			} else {
				$this->assertEquals($expectedShare[5], $share->getAttributes()->toArray());
			}
		}
	}
}
