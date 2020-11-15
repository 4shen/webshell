<?php
/**
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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
namespace Test\Share20;

use OC\Files\View;
use OC\Share20\Manager;
use OC\Share20\ShareAttributes;
use OCP\Files\File;
use OC\Share20\Share;
use OCP\DB\QueryBuilder\IExpressionBuilder;
use OCP\DB\QueryBuilder\IParameter;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\Mount\IMountManager;
use OCP\Files\Mount\IMountPoint;
use OCP\Files\Storage;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Security\IHasher;
use OCP\Security\ISecureRandom;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IAttributes;
use OCP\Share\IProviderFactory;
use OCP\Share\IShare;
use OCP\Share\IShareProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use Test\Traits\UserTrait;

/**
 * Class ManagerTest
 *
 * @package Test\Share20
 * @group DB
 */
class ManagerTest extends \Test\TestCase {
	use UserTrait;

	/** @var Manager */
	protected $manager;
	/** @var ILogger | \PHPUnit\Framework\MockObject\MockObject */
	protected $logger;
	/** @var IConfig | \PHPUnit\Framework\MockObject\MockObject */
	protected $config;
	/** @var ISecureRandom */
	protected $secureRandom;
	/** @var IHasher */
	protected $hasher;
	/** @var IShareProvider | \PHPUnit\Framework\MockObject\MockObject */
	protected $defaultProvider;
	/** @var  IMountManager */
	protected $mountManager;
	/** @var  IGroupManager */
	protected $groupManager;
	/** @var IL10N | \PHPUnit\Framework\MockObject\MockObject */
	protected $l;
	/** @var DummyFactory */
	protected $factory;
	/** @var IUserManager | \PHPUnit\Framework\MockObject\MockObject */
	protected $userManager;
	/** @var IRootFolder | \PHPUnit\Framework\MockObject\MockObject */
	protected $rootFolder;
	/** @var  EventDispatcher */
	protected $eventDispatcher;
	/** @var  View | \PHPUnit\Framework\MockObject\MockObject */
	protected $view;
	/** @var IDBConnection | \PHPUnit\Framework\MockObject\MockObject */
	protected $connection;
	/** @var IUserSession | \PHPUnit\Framework\MockObject\MockObject */
	protected $userSession;

	public function setUp(): void {
		parent::setUp();

		$this->logger = $this->createMock('\OCP\ILogger');
		$this->config = $this->createMock('\OCP\IConfig');
		$this->secureRandom = $this->createMock('\OCP\Security\ISecureRandom');
		$this->hasher = $this->createMock('\OCP\Security\IHasher');
		$this->mountManager = $this->createMock('\OCP\Files\Mount\IMountManager');
		$this->groupManager = $this->createMock('\OCP\IGroupManager');
		$this->userManager = $this->createMock('\OCP\IUserManager');
		$this->rootFolder = $this->createMock('\OCP\Files\IRootFolder');
		$this->eventDispatcher = new EventDispatcher();
		$this->view = $this->createMock(View::class);
		$this->connection = $this->createMock(IDBConnection::class);
		$this->userSession = $this->createMock(IUserSession::class);

		$this->l = $this->createMock('\OCP\IL10N');
		$this->l->method('t')
			->will($this->returnCallback(function ($text, $parameters = []) {
				return \vsprintf($text, $parameters);
			}));

		$this->factory = new DummyFactory(\OC::$server);

		$this->manager = new Manager(
			$this->logger,
			$this->config,
			$this->secureRandom,
			$this->hasher,
			$this->mountManager,
			$this->groupManager,
			$this->l,
			$this->factory,
			$this->userManager,
			$this->rootFolder,
			$this->eventDispatcher,
			$this->view,
			$this->connection,
			$this->userSession
		);

		$this->defaultProvider = $this->getMockBuilder('\OC\Share20\DefaultShareProvider')
			->disableOriginalConstructor()
			->getMock();
		$this->defaultProvider->method('identifier')->willReturn('default');
		$this->factory->setProvider($this->defaultProvider);
	}

	public function tearDown(): void {
		// clear legacy hook handlers
		\OC_Hook::clear('\OC\Share');
		parent::tearDown();
	}

	/**
	 * @return \PHPUnit\Framework\MockObject\MockBuilder
	 */
	private function createManagerMock() {
		return 	$this->getMockBuilder('\OC\Share20\Manager')
			->setConstructorArgs([
				$this->logger,
				$this->config,
				$this->secureRandom,
				$this->hasher,
				$this->mountManager,
				$this->groupManager,
				$this->l,
				$this->factory,
				$this->userManager,
				$this->rootFolder,
				$this->eventDispatcher,
				$this->view,
				$this->connection
			]);
	}

	/**
	 */
	public function testDeleteNoShareId() {
		$this->expectException(\InvalidArgumentException::class);

		$share = $this->manager->newShare();

		$this->manager->deleteShare($share);
	}

	public function dataTestDelete() {
		$user = $this->createMock('\OCP\IUser');
		$user->method('getUID')->willReturn('sharedWithUser');

		$group = $this->createMock('\OCP\IGroup');
		$group->method('getGID')->willReturn('sharedWithGroup');

		return [
			[\OCP\Share::SHARE_TYPE_USER, 'sharedWithUser'],
			[\OCP\Share::SHARE_TYPE_GROUP, 'sharedWithGroup'],
			[\OCP\Share::SHARE_TYPE_LINK, ''],
			[\OCP\Share::SHARE_TYPE_REMOTE, 'foo@bar.com'],
		];
	}

	/**
	 * @dataProvider dataTestDelete
	 */
	public function testDelete($shareType, $sharedWith) {
		$manager = $this->createManagerMock()
			->setMethods(['getShareById', 'deleteChildren'])
			->getMock();

		$path = $this->createMock('\OCP\Files\File');
		$path->method('getId')->willReturn(1);

		$share = $this->manager->newShare();
		$share->setId(42)
			->setProviderId('prov')
			->setShareType($shareType)
			->setSharedWith($sharedWith)
			->setSharedBy('sharedBy')
			->setNode($path)
			->setTarget('myTarget');

		$manager->expects($this->once())->method('deleteChildren')->with($share);

		$this->defaultProvider
			->expects($this->once())
			->method('delete')
			->with($share);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['pre', 'post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'pre_unshare', $hookListner, 'pre');
		\OCP\Util::connectHook('OCP\Share', 'post_unshare', $hookListner, 'post');

		$hookListnerExpectsPre = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => $shareType,
			'shareWith' => $sharedWith,
			'itemparent' => null,
			'uidOwner' => 'sharedBy',
			'fileSource' => 1,
			'fileTarget' => 'myTarget',
		];

		$hookListnerExpectsPost = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => $shareType,
			'shareWith' => $sharedWith,
			'itemparent' => null,
			'uidOwner' => 'sharedBy',
			'fileSource' => 1,
			'fileTarget' => 'myTarget',
			'deletedShares' => [
				[
					'id' => 42,
					'itemType' => 'file',
					'itemSource' => 1,
					'shareType' => $shareType,
					'shareWith' => $sharedWith,
					'itemparent' => null,
					'uidOwner' => 'sharedBy',
					'fileSource' => 1,
					'fileTarget' => 'myTarget',
				],
			],
		];

		$hookListner
			->expects($this->exactly(1))
			->method('pre')
			->with($hookListnerExpectsPre);
		$hookListner
			->expects($this->exactly(1))
			->method('post')
			->with($hookListnerExpectsPost);

		$calledBeforeEvent = [];
		$this->eventDispatcher->addListener('share.beforeDelete',
			function (GenericEvent $event) use (&$calledBeforeEvent) {
				$calledBeforeEvent[] = 'share.beforeDelete';
				$calledBeforeEvent[] = $event;
			});
		$calledAfterEvent = [];
		$this->eventDispatcher->addListener('share.afterDelete',
			function (GenericEvent $event) use (&$calledAfterEvent) {
				$calledAfterEvent[] = 'share.afterDelete';
				$calledAfterEvent[] = $event;
			});
		$manager->deleteShare($share);
		$this->assertEquals('share.beforeDelete', $calledBeforeEvent[0]);
		$this->assertEquals('share.afterDelete', $calledAfterEvent[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeEvent[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterEvent[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeEvent[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeEvent[1]);
		$this->assertArrayHasKey('shareData', $calledAfterEvent[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterEvent[1]);
	}

	public function testDeleteLazyShare() {
		$manager = $this->createManagerMock()
			->setMethods(['getShareById', 'deleteChildren'])
			->getMock();

		$share = $this->manager->newShare();
		$share->setId(42)
			->setProviderId('prov')
			->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setSharedWith('sharedWith')
			->setSharedBy('sharedBy')
			->setShareOwner('shareOwner')
			->setTarget('myTarget')
			->setNodeId(1)
			->setNodeType('file');

		$this->rootFolder->expects($this->never())->method($this->anything());

		$manager->expects($this->once())->method('deleteChildren')->with($share);

		$this->defaultProvider
			->expects($this->once())
			->method('delete')
			->with($share);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['pre', 'post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'pre_unshare', $hookListner, 'pre');
		\OCP\Util::connectHook('OCP\Share', 'post_unshare', $hookListner, 'post');

		$hookListnerExpectsPre = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => \OCP\Share::SHARE_TYPE_USER,
			'shareWith' => 'sharedWith',
			'itemparent' => null,
			'uidOwner' => 'sharedBy',
			'fileSource' => 1,
			'fileTarget' => 'myTarget',
		];

		$hookListnerExpectsPost = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => \OCP\Share::SHARE_TYPE_USER,
			'shareWith' => 'sharedWith',
			'itemparent' => null,
			'uidOwner' => 'sharedBy',
			'fileSource' => 1,
			'fileTarget' => 'myTarget',
			'deletedShares' => [
				[
					'id' => 42,
					'itemType' => 'file',
					'itemSource' => 1,
					'shareType' => \OCP\Share::SHARE_TYPE_USER,
					'shareWith' => 'sharedWith',
					'itemparent' => null,
					'uidOwner' => 'sharedBy',
					'fileSource' => 1,
					'fileTarget' => 'myTarget',
				],
			],
		];

		$hookListner
			->expects($this->exactly(1))
			->method('pre')
			->with($hookListnerExpectsPre);
		$hookListner
			->expects($this->exactly(1))
			->method('post')
			->with($hookListnerExpectsPost);

		$calledBeforeEvent = [];
		$this->eventDispatcher->addListener('share.beforeDelete',
			function (GenericEvent $event) use (&$calledBeforeEvent) {
				$calledBeforeEvent[] = 'share.beforeDelete';
				$calledBeforeEvent[] = $event;
			});
		$calledAfterEvent = [];
		$this->eventDispatcher->addListener('share.afterDelete',
			function (GenericEvent $event) use (&$calledAfterEvent) {
				$calledAfterEvent[] = 'share.afterDelete';
				$calledAfterEvent[] = $event;
			});
		$manager->deleteShare($share);
		$this->assertEquals('share.beforeDelete', $calledBeforeEvent[0]);
		$this->assertEquals('share.afterDelete', $calledAfterEvent[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeEvent[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterEvent[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeEvent[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeEvent[1]);
		$this->assertArrayHasKey('shareData', $calledAfterEvent[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterEvent[1]);
	}

	public function testDeleteNested() {
		$manager = $this->createManagerMock()
			->setMethods(['getShareById'])
			->getMock();

		$path = $this->createMock('\OCP\Files\File');
		$path->method('getId')->willReturn(1);

		$share1 = $this->manager->newShare();
		$share1->setId(42)
			->setProviderId('prov')
			->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setSharedWith('sharedWith1')
			->setSharedBy('sharedBy1')
			->setNode($path)
			->setTarget('myTarget1');

		$share2 = $this->manager->newShare();
		$share2->setId(43)
			->setProviderId('prov')
			->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setSharedWith('sharedWith2')
			->setSharedBy('sharedBy2')
			->setNode($path)
			->setTarget('myTarget2')
			->setParent(42);

		$share3 = $this->manager->newShare();
		$share3->setId(44)
			->setProviderId('prov')
			->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setSharedBy('sharedBy3')
			->setNode($path)
			->setTarget('myTarget3')
			->setParent(43);

		$this->defaultProvider
			->method('getChildren')
			->will($this->returnValueMap([
				[$share1, [$share2]],
				[$share2, [$share3]],
				[$share3, []],
			]));

		$this->defaultProvider
			->method('delete')
			->withConsecutive([$share3], [$share2], [$share1]);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['pre', 'post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'pre_unshare', $hookListner, 'pre');
		\OCP\Util::connectHook('OCP\Share', 'post_unshare', $hookListner, 'post');

		$hookListnerExpectsPre = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => \OCP\Share::SHARE_TYPE_USER,
			'shareWith' => 'sharedWith1',
			'itemparent' => null,
			'uidOwner' => 'sharedBy1',
			'fileSource' => 1,
			'fileTarget' => 'myTarget1',
		];

		$hookListnerExpectsPost = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => \OCP\Share::SHARE_TYPE_USER,
			'shareWith' => 'sharedWith1',
			'itemparent' => null,
			'uidOwner' => 'sharedBy1',
			'fileSource' => 1,
			'fileTarget' => 'myTarget1',
			'deletedShares' => [
				[
					'id' => 44,
					'itemType' => 'file',
					'itemSource' => 1,
					'shareType' => \OCP\Share::SHARE_TYPE_LINK,
					'shareWith' => '',
					'itemparent' => 43,
					'uidOwner' => 'sharedBy3',
					'fileSource' => 1,
					'fileTarget' => 'myTarget3',
				],
				[
					'id' => 43,
					'itemType' => 'file',
					'itemSource' => 1,
					'shareType' => \OCP\Share::SHARE_TYPE_GROUP,
					'shareWith' => 'sharedWith2',
					'itemparent' => 42,
					'uidOwner' => 'sharedBy2',
					'fileSource' => 1,
					'fileTarget' => 'myTarget2',
				],
				[
					'id' => 42,
					'itemType' => 'file',
					'itemSource' => 1,
					'shareType' => \OCP\Share::SHARE_TYPE_USER,
					'shareWith' => 'sharedWith1',
					'itemparent' => null,
					'uidOwner' => 'sharedBy1',
					'fileSource' => 1,
					'fileTarget' => 'myTarget1',
				],
			],
		];

		$hookListner
			->expects($this->exactly(1))
			->method('pre')
			->with($hookListnerExpectsPre);
		$hookListner
			->expects($this->exactly(1))
			->method('post')
			->with($hookListnerExpectsPost);

		$calledBeforeEvent = [];
		$this->eventDispatcher->addListener('share.beforeDelete',
			function (GenericEvent $event) use (&$calledBeforeEvent) {
				$calledBeforeEvent[] = 'share.beforeDelete';
				$calledBeforeEvent[] = $event;
			});
		$calledAfterEvent = [];
		$this->eventDispatcher->addListener('share.afterDelete',
			function (GenericEvent $event) use (&$calledAfterEvent) {
				$calledAfterEvent[] = 'share.afterDelete';
				$calledAfterEvent[] = $event;
			});
		$manager->deleteShare($share1);
		$this->assertEquals('share.beforeDelete', $calledBeforeEvent[0]);
		$this->assertEquals('share.afterDelete', $calledAfterEvent[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeEvent[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterEvent[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeEvent[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeEvent[1]);
		$this->assertArrayHasKey('shareData', $calledAfterEvent[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterEvent[1]);
	}

	public function testDeleteChildren() {
		$manager = $this->createManagerMock()
			->setMethods(['deleteShare'])
			->getMock();

		$share = $this->createMock('\OCP\Share\IShare');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_USER);

		$child1 = $this->createMock('\OCP\Share\IShare');
		$child1->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_USER);
		$child2 = $this->createMock('\OCP\Share\IShare');
		$child2->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_USER);
		$child3 = $this->createMock('\OCP\Share\IShare');
		$child3->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_USER);

		$shares = [
			$child1,
			$child2,
			$child3,
		];

		$this->defaultProvider
			->expects($this->exactly(4))
			->method('getChildren')
			->will($this->returnCallback(function ($_share) use ($share, $shares) {
				if ($_share === $share) {
					return $shares;
				}
				return [];
			}));

		$this->defaultProvider
			->expects($this->exactly(3))
			->method('delete')
			->withConsecutive([$child1], [$child2], [$child3]);

		$result = $this->invokePrivate($manager, 'deleteChildren', [$share]);
		$this->assertSame($shares, $result);
	}

	public function testGetShareById() {
		$share = $this->createMock('\OCP\Share\IShare');

		$this->defaultProvider
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->willReturn($share);

		$this->assertEquals($share, $this->manager->getShareById('default:42'));
	}

	/**
	 */
	public function testGetExpiredShareById() {
		$this->expectException(\OCP\Share\Exceptions\ShareNotFound::class);

		$manager = $this->createManagerMock()
			->setMethods(['deleteShare'])
			->getMock();

		$date = new \DateTime("yesterday");
		$date->setTime(0, 0, 0);

		$share = $this->manager->newShare();
		$share->setExpirationDate($date)
			->setShareType(\OCP\Share::SHARE_TYPE_LINK);

		$this->defaultProvider->expects($this->once())
			->method('getShareById')
			->with('42')
			->willReturn($share);

		$manager->expects($this->once())
			->method('deleteShare')
			->with($share);

		$manager->getShareById('default:42');
	}

	public function testPasswordMustBeEnforcedForReadOnly() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_delete', 'no', 'yes'],
		]));

		$this->assertTrue($this->invokePrivate($this->manager, 'passwordMustBeEnforced', [\OCP\Constants::PERMISSION_READ]));
	}

	public function testPasswordMustBeEnforcedForReadWrite() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
		]));

		$this->assertTrue($this->invokePrivate($this->manager, 'passwordMustBeEnforced', [\OCP\Constants::PERMISSION_READ | \OCP\Constants::PERMISSION_CREATE]));
	}

	public function testPasswordMustBeEnforcedForReadWriteDelete() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write_delete', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
		]));

		$this->assertTrue($this->invokePrivate($this->manager, 'passwordMustBeEnforced',
			[\OCP\Constants::PERMISSION_READ | \OCP\Constants::PERMISSION_UPDATE | \OCP\Constants::PERMISSION_CREATE | \OCP\Constants::PERMISSION_DELETE]));
	}

	public function testPasswordMustBeEnforcedForWriteOnly() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write_delete', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
		]));

		$this->assertTrue($this->invokePrivate($this->manager, 'passwordMustBeEnforced', [\OCP\Constants::PERMISSION_CREATE]));
	}

	public function testPasswordMustBeEnforcedForReadOnlyNotEnforced() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'no'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write_delete', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
		]));

		$this->assertFalse($this->invokePrivate($this->manager, 'passwordMustBeEnforced', [\OCP\Constants::PERMISSION_READ]));
	}

	public function testPasswordMustBeEnforcedForReadWriteNotEnforced() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'no'],
			['core', 'shareapi_enforce_links_password_read_write_delete', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
		]));

		$this->assertFalse($this->invokePrivate($this->manager, 'passwordMustBeEnforced', [\OCP\Constants::PERMISSION_ALL]));
	}

	public function testPasswordMustBeEnforcedForReadWriteDeleteNotEnforced() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'no'],
			['core', 'shareapi_enforce_links_password_read_write_delete', 'no', 'no'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'yes'],
		]));

		$this->assertFalse($this->invokePrivate($this->manager, 'passwordMustBeEnforced',
			[\OCP\Constants::PERMISSION_READ | \OCP\Constants::PERMISSION_UPDATE | \OCP\Constants::PERMISSION_CREATE | \OCP\Constants::PERMISSION_DELETE]));
	}

	public function testPasswordMustBeEnforcedForWriteOnlyNotEnforced() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password_read_only', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_read_write_delete', 'no', 'yes'],
			['core', 'shareapi_enforce_links_password_write_only', 'no', 'no'],
		]));

		$this->assertFalse($this->invokePrivate($this->manager, 'passwordMustBeEnforced', [\OCP\Constants::PERMISSION_CREATE]));
	}

	public function testVerifyPasswordHook() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password', 'no', 'no'],
			['core', 'shareapi_disable_enforce_links_password_for_upload_only', 'no', 'no'],
		]));

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listner'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyPassword', $hookListner, 'listner');

		$hookListner->expects($this->once())
			->method('listner')
			->with([
				'password' => 'password',
				'accepted' => true,
				'message' => ''
			]);

		$result = $this->invokePrivate($this->manager, 'verifyPassword', ['password']);
		$this->assertNull($result);
	}

	/**
	 */
	public function testVerifyPasswordHookFails() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('password not accepted');

		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password', 'no', 'no'],
			['core', 'shareapi_disable_enforce_links_password_for_upload_only', 'no', 'no'],
		]));

		$dummy = new DummyPassword();
		\OCP\Util::connectHook('\OC\Share', 'verifyPassword', $dummy, 'listner');
		$this->invokePrivate($this->manager, 'verifyPassword', ['password']);
	}

	public function createShare($id, $type, $node, $sharedWith, $sharedBy, $shareOwner,
		$permissions, $expireDate = null, $password = null, $attributes = null) {
		$share = $this->createMock(IShare::class);

		$share->method('getShareType')->willReturn($type);
		$share->method('getSharedWith')->willReturn($sharedWith);
		$share->method('getSharedBy')->willReturn($sharedBy);
		$share->method('getShareOwner')->willReturn($shareOwner);
		$share->method('getNode')->willReturn($node);
		$share->method('getPermissions')->willReturn($permissions);
		$share->method('getAttributes')->willReturn($attributes);
		$share->method('getExpirationDate')->willReturn($expireDate);
		$share->method('getPassword')->willReturn($password);

		return $share;
	}

	public function testVerifyPasswordEvent() {
		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_enforce_links_password', 'no', 'no'],
			['core', 'shareapi_disable_enforce_links_password_for_upload_only', 'no', 'no'],
		]));

		$event = null;
		$this->eventDispatcher->addListener('OCP\Share::validatePassword',
			function (GenericEvent $receivedEvent) use (&$event) {
				$event = $receivedEvent;
			});

		$result = $this->invokePrivate($this->manager, 'verifyPassword', ['somepw']);
		$this->assertNull($result);

		$this->assertEquals('somepw', $event->getArgument('password'));
	}

	public function dataGeneralChecks() {
		$user0 = 'user0';
		$user1 = 'user1';
		$group0 = 'group0';

		$file = $this->createMock('\OCP\Files\File');
		$node = $this->createMock('\OCP\Files\Node');

		$data = [
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, null, $user0, $user0, 31, null, null), 'SharedWith is not a valid user', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, $group0, $user0, $user0, 31, null, null), 'SharedWith is not a valid user', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, 'foo@bar.com', $user0, $user0, 31, null, null), 'SharedWith is not a valid user', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $file, null, $user0, $user0, 31, null, null), 'SharedWith is not a valid group', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $file, $user1, $user0, $user0, 31, null, null), 'SharedWith is not a valid group', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $file, 'foo@bar.com', $user0, $user0, 31, null, null), 'SharedWith is not a valid group', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $file, $user1, $user0, $user0, 31, null, null), 'SharedWith should be empty', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $file, $group0, $user0, $user0, 31, null, null), 'SharedWith should be empty', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $file, 'foo@bar.com', $user0, $user0, 31, null, null), 'SharedWith should be empty', true],
			[$this->createShare(null, -1, $file, null, $user0, $user0, 31, null, null), 'Unknown share type', true],

			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, $user1, null, $user0, 31, null, null), 'SharedBy should be set', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $file, $group0, null, $user0, 31, null, null), 'SharedBy should be set', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $file, null, null, $user0, 31, null, null), 'SharedBy should be set', true],

			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, $user0, $user0, $user0, 31, null, null), 'Can\'t share with yourself', true],

			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, null, $user1, $user0, $user0, 31, null, null), 'Path should be set', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, null, $group0, $user0, $user0, 31, null, null), 'Path should be set', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, null, null, $user0, $user0, 31, null, null), 'Path should be set', true],

			[$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $node, $user1, $user0, $user0, 31, null, null), 'Path should be either a file or a folder', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $node, $group0, $user0, $user0, 31, null, null), 'Path should be either a file or a folder', true],
			[$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $node, null, $user0, $user0, 31, null, null), 'Path should be either a file or a folder', true],
		];

		$nonShareAble = $this->createMock('\OCP\Files\Folder');
		$nonShareAble->method('isShareable')->willReturn(false);
		$nonShareAble->method('getPath')->willReturn('path');

		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $nonShareAble, $user1, $user0, $user0, 31, null, null), 'You are not allowed to share path', true];
		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $nonShareAble, $group0, $user0, $user0, 31, null, null), 'You are not allowed to share path', true];
		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $nonShareAble, null, $user0, $user0, 31, null, null), 'You are not allowed to share path', true];

		$rootFolder = $this->createMock(Folder::class);
		$rootFolder->method('isShareable')->willReturn(true);
		$rootFolder->method('getPermissions')->willReturn(\OCP\Constants::PERMISSION_ALL);
		$rootFolder->method('getPath')->willReturn('myrootfolder');

		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $rootFolder, $user1, $user0, $user0, 30, null, null), 'You can\'t share your root folder', true];
		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_GROUP, $rootFolder, $group0, $user0, $user0, 2, null, null), 'You can\'t share your root folder', true];
		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_LINK, $rootFolder, null, $user0, $user0, 16, null, null), 'You can\'t share your root folder', true];

		$fileFullPermission = $this->createMock(File::class);
		$fileFullPermission->method('getPermissions')->willReturn(31);
		$fileFullPermission->method('isShareable')->willReturn(true);

		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $fileFullPermission, $user0, $user1, $user1, null, null, null), 'A share requires permissions', true];

		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $fileFullPermission, $user0, $user1, $user1, -1, null, null), 'Invalid permissions', true];
		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $fileFullPermission, $user0, $user1, $user1, 100, null, null), 'Invalid permissions', true];

		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $fileFullPermission, $user0, $user1, $user1, 0, null, null), 'Cannot remove all permissions', true];

		$data[] = [$this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $fileFullPermission, $user0, $user1, $user1, 31, null, null), null, false];

		return $data;
	}

	/**
	 * @dataProvider dataGeneralChecks
	 *
	 * @param $share
	 * @param $exceptionMessage
	 * @param $exception
	 */
	public function testGeneralChecks($share, $exceptionMessage, $exception) {
		$thrown = null;

		$this->userManager->method('userExists')->will($this->returnValueMap([
			['user0', true],
			['user1', true],
		]));

		$this->groupManager->method('groupExists')->will($this->returnValueMap([
			['group0', true],
		]));

		$userFolder = $this->createMock('\OCP\Files\Folder');
		$userFolder->method('getPath')->willReturn('myrootfolder');
		$this->rootFolder->method('getUserFolder')->willReturn($userFolder);

		try {
			$this->invokePrivate($this->manager, 'generalChecks', [$share]);
			$thrown = false;
		} catch (\OCP\Share\Exceptions\GenericShareException $e) {
			$this->assertEquals($exceptionMessage, $e->getHint());
			$thrown = true;
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals($exceptionMessage, $e->getMessage());
			$thrown = true;
		}

		$this->assertSame($exception, $thrown);
	}

	public function dataShareNotEnoughPermissions() {
		$file = $this->createMock(File::class);
		$file->method('getPermissions')->willReturn(17);
		$file->method('getName')->willReturn('sharedfile');
		$file->method('getPath')->willReturn('/user1/sharedfile');

		// Normal share (not re-share) should just use share node permission
		// exception when trying to share with more permission than share has
		$share = $this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, 'user0', 'user1', 'user1', 31, null, null);
		$data[] = [$share, null, 'Cannot set the requested share permissions for sharedfile', true];

		// Federated reshare should just use share node permission
		// exception when trying to share with more permission than node has
		$fileExternalStorage = $this->createMock('OCA\Files_Sharing\External\Storage');
		$fileExternalStorage->method('instanceOfStorage')
			->willReturnCallback(function ($storageClass) {
				return ($storageClass === 'OCA\Files_Sharing\External\Storage');
			});
		$fileExternal = $this->createMock(File::class);
		$fileExternal->method('getStorage')->willReturn($fileExternalStorage);
		$share = $this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, 'user0', 'user1', 'user2', 31, null, null);
		$data[] = [$share, $fileExternal, 'Cannot set the requested share permissions for sharedfile', true];

		// Normal reshare should just use supershare node permission
		// exception when trying to share with more permission than supershare has
		$superShare = $this->createMock(IShare::class);
		$superShare->method('getPermissions')->willReturn(1);
		$fileReshareStorage = $this->createMock('OCA\Files_Sharing\SharedStorage');
		$fileReshareStorage->method('instanceOfStorage')
			->willReturnCallback(function ($storageClass) {
				return ($storageClass === 'OCA\Files_Sharing\SharedStorage');
			});
		$fileReshareStorage->method('getShare')->willReturn($superShare);
		$fileReshare = $this->createMock(File::class);
		$fileReshare->method('getStorage')->willReturn($fileReshareStorage);
		$share = $this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, 'user0', 'user1', 'user2', 17, null, null);
		$data[] = [$share, $fileReshare, 'Cannot set the requested share permissions for sharedfile', true];

		// Normal reshare should just use supershare node attributes
		// exception when trying to remove share attributes that supershare has
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test', true);
		$shareAttributes = new ShareAttributes();
		$superShare = $this->createMock(IShare::class);
		$superShare->method('getPermissions')->willReturn(17);
		$superShare->method('getAttributes')->willReturn($superShareAttributes);
		$fileReshareStorage = $this->createMock('OCA\Files_Sharing\SharedStorage');
		$fileReshareStorage->method('instanceOfStorage')
			->willReturnCallback(function ($storageClass) {
				return ($storageClass === 'OCA\Files_Sharing\SharedStorage');
			});
		$fileReshareStorage->method('getShare')->willReturn($superShare);
		$fileReshare = $this->createMock(File::class);
		$fileReshare->method('getStorage')->willReturn($fileReshareStorage);
		$share = $this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, 'user0', 'user1', 'user2', 17, null, null, $shareAttributes);
		$data[] = [$share, $fileReshare, 'Cannot set the requested share attributes for sharedfile', true];

		return $data;
	}

	/**
	 * @dataProvider dataShareNotEnoughPermissions
	 *
	 * @param $share
	 * @param $superShareNode
	 * @param $exceptionMessage
	 * @param $exception
	 */
	public function testShareNotEnoughPermissions($share, $superShareNode, $exceptionMessage, $exception) {
		$sharer = $this->createMock(IUser::class);
		$sharer->method('getUID')->willReturn($share->getSharedBy());
		$this->userSession->method('getUser')->willReturn($sharer);

		$userFolder = $this->createMock(Folder::class);
		$userFolder->method('getById')->willReturn([$superShareNode]);
		$this->rootFolder->method('getUserFolder')->willReturn($userFolder);

		try {
			$this->invokePrivate($this->manager, 'validatePermissions', [$share]);
			$thrown = false;
		} catch (\OCP\Share\Exceptions\GenericShareException $e) {
			$this->assertEquals($exceptionMessage, $e->getHint());
			$thrown = true;
		}

		$this->assertSame($exception, $thrown);
	}

	/**
	 * Non-movable mount share can be shared with delete and update permission
	 * even if permission for file do not have this permission
	 */
	public function testShareNonMovableMountPermissions() {
		$nonMovableMountPoint = $this->createMock(IMountPoint::class);
		$file = $this->createMock(File::class);
		$file->method('getPermissions')->willReturn(1);
		$file->method('getMountPoint')->willReturn($nonMovableMountPoint);
		$share = $this->createShare(null, \OCP\Share::SHARE_TYPE_USER, $file, 'user0', 'user1', 'user1', 11, null, null);

		try {
			$this->invokePrivate($this->manager, 'validatePermissions', [$share]);
			$thrown = false;
		} catch (\Exception $e) {
			$thrown = true;
		}

		$this->assertSame(false, $thrown);
	}

	/**
	 */
	public function testValidateExpirationDateInPast() {
		$this->expectException(\OCP\Share\Exceptions\GenericShareException::class);
		$this->expectExceptionMessage('Expiration date is in the past');

		// Expire date in the past
		$past = new \DateTime();
		$past->sub(new \DateInterval('P1D'));

		$share = $this->manager->newShare();
		$share->setExpirationDate($past);

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);
	}

	/**
	 */
	public function testValidateExpirationDateToday() {
		$today = new \DateTime();
		$today->setTime(0, 0, 0);
		$share = $this->manager->newShare();
		$share->setExpirationDate($today);

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);
		$this->assertEquals($today, $share->getExpirationDate());
	}

	/**
	 */
	public function testvalidateExpirationDateEnforceButNotSet() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Expiration date is enforced');

		$share = $this->manager->newShare();
		$share->setId(43)
			->setProviderId('prov')
			->setShareType(\OCP\Share::SHARE_TYPE_LINK);

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_default_expire_date', 'no', 'yes'],
				['core', 'shareapi_enforce_expire_date', 'no', 'yes'],
			]));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);
	}

	public function testvalidateExpirationDateEnforceButNotEnabledAndNotSet() {
		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_LINK);

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_enforce_expire_date', 'no', 'yes'],
			]));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$this->assertNull($share->getExpirationDate());
	}

	public function testvalidateExpirationDateEnforceButNotSetNewShare() {
		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_LINK);

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_enforce_expire_date', 'no', 'yes'],
				['core', 'shareapi_expire_after_n_days', '7', '3'],
				['core', 'shareapi_default_expire_date', 'no', 'yes'],
			]));

		$expected = new \DateTime();
		$expected->setTime(0, 0, 0);
		$expected->add(new \DateInterval('P3D'));
		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);
		$this->assertNotNull($share->getExpirationDate());
		$this->assertEquals($expected, $share->getExpirationDate());
	}

	public function testvalidateExpirationDateEnforceToFarIntoFuture() {
		// Expire date in the past
		$future = new \DateTime();
		$future->add(new \DateInterval('P7D'));

		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setExpirationDate($future);

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_default_expire_date', 'no', 'yes'],
				['core', 'shareapi_enforce_expire_date', 'no', 'yes'],
				['core', 'shareapi_expire_after_n_days', '7', '3'],
			]));

		try {
			$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);
			$this->fail("expected GenericShareException but no exception was thrown");
		} catch (\OCP\Share\Exceptions\GenericShareException $e) {
			$this->assertEquals('Cannot set expiration date more than 3 days in the future', $e->getMessage());
			$this->assertEquals('Cannot set expiration date more than 3 days in the future', $e->getHint());
			$this->assertEquals(404, $e->getCode());
		}
	}

	public function testvalidateExpirationDateEnforceValid() {
		// Expire date in the past
		$future = new \DateTime();
		$future->add(new \DateInterval('P2D'));
		$future->setTime(0, 0, 0);

		$expected = clone $future;
		$future->setTime(1, 2, 3);

		$share = $this->manager->newShare();
		$share->setExpirationDate($future);

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_enforce_expire_date', 'no', 'yes'],
				['core', 'shareapi_expire_after_n_days', '7', '3'],
			]));

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listener'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyExpirationDate', $hookListner, 'listener');
		$hookListner->expects($this->once())->method('listener')->with($this->callback(function ($data) use ($future) {
			return $data['expirationDate'] == $future;
		}));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$this->assertEquals($expected, $share->getExpirationDate());
	}

	public function testvalidateExpirationDateNoDateNoDefaultNull() {
		$date = new \DateTime();
		$date->add(new \DateInterval('P5D'));

		$expected = clone $date;
		$expected->setTime(0, 0, 0);

		$share = $this->manager->newShare();
		$share->setExpirationDate($date);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listener'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyExpirationDate', $hookListner, 'listener');
		$hookListner->expects($this->once())->method('listener')->with($this->callback(function ($data) use ($expected) {
			return $data['expirationDate'] == $expected && $data['passwordSet'] === false;
		}));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$this->assertEquals($expected, $share->getExpirationDate());
	}

	public function testvalidateExpirationDateNoDateNoDefault() {
		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listener'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyExpirationDate', $hookListner, 'listener');
		$hookListner->expects($this->once())->method('listener')->with($this->callback(function ($data) {
			return $data['expirationDate'] === null && $data['passwordSet'] === true;
		}));

		$share = $this->manager->newShare();
		$share->setPassword('password');

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$this->assertNull($share->getExpirationDate());
	}

	public function testvalidateExpirationDateNoDateDefault() {
		$future = new \DateTime();
		$future->add(new \DateInterval('P3D'));
		$future->setTime(0, 0, 0);

		$expected = clone $future;

		$share = $this->manager->newShare();
		$share->setExpirationDate($future);

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_default_expire_date', 'no', 'yes'],
				['core', 'shareapi_expire_after_n_days', '7', '3'],
			]));

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listener'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyExpirationDate', $hookListner, 'listener');
		$hookListner->expects($this->once())->method('listener')->with($this->callback(function ($data) use ($expected) {
			return $data['expirationDate'] == $expected;
		}));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$this->assertEquals($expected, $share->getExpirationDate());
	}

	public function testValidateExpirationDateHookModification() {
		$nextWeek = new \DateTime();
		$nextWeek->add(new \DateInterval('P7D'));
		$nextWeek->setTime(0, 0, 0);

		$save = clone $nextWeek;

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listener'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyExpirationDate', $hookListner, 'listener');
		$hookListner->expects($this->once())->method('listener')->will($this->returnCallback(function ($data) {
			$data['expirationDate']->sub(new \DateInterval('P2D'));
		}));

		$share = $this->manager->newShare();
		$share->setExpirationDate($nextWeek);

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$save->sub(new \DateInterval('P2D'));
		$this->assertEquals($save, $share->getExpirationDate());
	}

	/**
	 */
	public function testValidateExpirationDateHookException() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Invalid date!');

		$nextWeek = new \DateTime();
		$nextWeek->add(new \DateInterval('P7D'));
		$nextWeek->setTime(0, 0, 0);

		$share = $this->manager->newShare();
		$share->setExpirationDate($nextWeek);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listener'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyExpirationDate', $hookListner, 'listener');
		$hookListner->expects($this->once())->method('listener')->will($this->returnCallback(function ($data) {
			$data['accepted'] = false;
			$data['message'] = 'Invalid date!';
		}));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);
	}

	public function testValidateExpirationDateExistingShareNoDefault() {
		$share = $this->manager->newShare();

		$share->setId('42')->setProviderId('foo');

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_default_expire_date', 'no', 'yes'],
				['core', 'shareapi_expire_after_n_days', '7', '6'],
			]));

		$this->invokePrivate($this->manager, 'validateExpirationDate', [$share]);

		$this->assertNull($share->getExpirationDate());
	}

	/**
	 */
	public function testUserCreateChecksShareWithGroupMembersOnlyDifferentGroups() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Only sharing with group members is allowed');

		$share = $this->manager->newShare();

		$sharedBy = $this->createMock('\OCP\IUser');
		$sharedWith = $this->createMock('\OCP\IUser');
		$share->setSharedBy('sharedBy')->setSharedWith('sharedWith');

		$this->groupManager
			->method('getUserGroupIds')
			->will(
				$this->returnValueMap([
					[$sharedBy, null, ['group1']],
					[$sharedWith, null, ['group2']],
				])
			);

		$this->userManager->method('get')->will($this->returnValueMap([
			['sharedBy', false, $sharedBy],
			['sharedWith', false, $sharedWith],
		]));

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_only_share_with_group_members', 'no', 'yes'],
			]));

		$this->invokePrivate($this->manager, 'userCreateChecks', [$share]);
	}

	public function testUserCreateChecksShareWithGroupMembersOnlySharedGroup() {
		$share = $this->manager->newShare();

		$sharedBy = $this->createMock('\OCP\IUser');
		$sharedWith = $this->createMock('\OCP\IUser');
		$share->setSharedBy('sharedBy')->setSharedWith('sharedWith');

		$path = $this->createMock('\OCP\Files\Node');
		$share->setNode($path);

		$this->groupManager
			->method('getUserGroupIds')
			->will(
				$this->returnValueMap([
					[$sharedBy, null, ['group1', 'group3']],
					[$sharedWith, null, ['group2', 'group3']],
				])
			);

		$this->userManager->method('get')->will($this->returnValueMap([
			['sharedBy', false, $sharedBy],
			['sharedWith', false, $sharedWith],
		]));

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_only_share_with_group_members', 'no', 'yes'],
			]));

		$this->defaultProvider
			->method('getSharesByPath')
			->with($path)
			->willReturn([]);

		$this->assertNull(
			$this->invokePrivate($this->manager, 'userCreateChecks', [$share])
		);
	}

	/**
	 */
	public function testUserCreateChecksIdenticalShareExists() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Path already shared with this user');

		$share  = $this->manager->newShare();
		$share2 = $this->manager->newShare();

		$path = $this->createMock('\OCP\Files\Node');

		$share->setSharedWith('sharedWith')->setNode($path)
			->setProviderId('foo')->setId('bar')
			->setShareType(\OCP\Share::SHARE_TYPE_USER);

		$share2->setSharedWith('sharedWith')->setNode($path)
			->setProviderId('foo')->setId('baz')
			->setShareType(\OCP\Share::SHARE_TYPE_USER);

		$this->defaultProvider
			->method('getSharesByPath')
			->with($path)
			->willReturn([$share2]);

		$this->invokePrivate($this->manager, 'userCreateChecks', [$share]);
	}

	/**
	 */
	public function testUserCreateChecksIdenticalPathSharedViaGroup() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Path already shared with this user');

		$share  = $this->manager->newShare();

		$sharedWith = $this->createMock('\OCP\IUser');
		$sharedWith->method('getUID')->willReturn('sharedWith');

		$this->userManager->method('get')->with('sharedWith')->willReturn($sharedWith);

		$path = $this->createMock('\OCP\Files\Node');

		$share->setSharedWith('sharedWith')
			->setNode($path)
			->setShareOwner('shareOwner')
			->setProviderId('foo')
			->setId('bar');

		$share2 = $this->manager->newShare();
		$share2->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setShareOwner('shareOwner2')
			->setProviderId('foo')
			->setId('baz')
			->setSharedWith('group');

		$group = $this->createMock('\OCP\IGroup');
		$group->method('inGroup')
			->with($sharedWith)
			->willReturn(true);

		$this->groupManager->method('get')->with('group')->willReturn($group);

		$this->defaultProvider
			->method('getSharesByPath')
			->with($path)
			->willReturn([$share2]);

		$this->invokePrivate($this->manager, 'userCreateChecks', [$share]);
	}

	public function testUserCreateChecksIdenticalPathSharedViaDeletedGroup() {
		$share  = $this->manager->newShare();

		$sharedWith = $this->createMock(IUser::class);
		$sharedWith->method('getUID')->willReturn('sharedWith');

		$this->userManager->method('get')->with('sharedWith')->willReturn($sharedWith);

		$path = $this->createMock(\OCP\Files\Node::class);

		$share->setSharedWith('sharedWith')
			->setNode($path)
			->setShareOwner('shareOwner')
			->setProviderId('foo')
			->setId('bar');

		$share2 = $this->manager->newShare();
		$share2->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setShareOwner('shareOwner2')
			->setProviderId('foo')
			->setId('baz')
			->setSharedWith('group');

		$this->groupManager->method('get')->with('group')->willReturn(null);

		$this->defaultProvider
			->method('getSharesByPath')
			->with($path)
			->willReturn([$share2]);

		$this->assertNull($this->invokePrivate($this->manager, 'userCreateChecks', [$share]));
	}

	public function testUserCreateChecksIdenticalPathNotSharedWithUser() {
		$share = $this->manager->newShare();
		$sharedWith = $this->createMock('\OCP\IUser');
		$path = $this->createMock('\OCP\Files\Node');
		$share->setSharedWith('sharedWith')
			->setNode($path)
			->setShareOwner('shareOwner')
			->setProviderId('foo')
			->setId('bar');

		$this->userManager->method('get')->with('sharedWith')->willReturn($sharedWith);

		$share2 = $this->manager->newShare();
		$share2->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setShareOwner('shareOwner2')
			->setProviderId('foo')
			->setId('baz');

		$group = $this->createMock('\OCP\IGroup');
		$group->method('inGroup')
			->with($sharedWith)
			->willReturn(false);

		$this->groupManager->method('get')->with('group')->willReturn($group);

		$share2->setSharedWith('group');

		$this->defaultProvider
			->method('getSharesByPath')
			->with($path)
			->willReturn([$share2]);

		$this->assertNull(
			$this->invokePrivate($this->manager, 'userCreateChecks', [$share])
		);
	}

	/**
	 */
	public function testGroupCreateChecksShareWithGroupMembersGroupSharingNotAllowed() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Group sharing is not allowed');

		$share = $this->manager->newShare();

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_group_sharing', 'yes', 'no'],
			]));

		$this->invokePrivate($this->manager, 'groupCreateChecks', [$share]);
	}

	/**
	 */
	public function testGroupCreateChecksShareWithGroupMembersOnlyNotInGroup() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Only sharing within your own groups is allowed');

		$share = $this->manager->newShare();

		$user = $this->createMock('\OCP\IUser');
		$group = $this->createMock('\OCP\IGroup');
		$share->setSharedBy('user')->setSharedWith('group');

		$group->method('inGroup')->with($user)->willReturn(false);

		$this->groupManager->method('get')->with('group')->willReturn($group);
		$this->userManager->method('get')->with('user')->willReturn($user);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_only_share_with_membership_groups', 'no', 'yes'],
				['core', 'shareapi_allow_group_sharing', 'yes', 'yes'],
			]));

		$this->invokePrivate($this->manager, 'groupCreateChecks', [$share]);
	}

	/**
	 */
	public function testGroupCreateChecksShareWithGroupMembersOnlyNullGroup() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Only sharing within your own groups is allowed');

		$share = $this->manager->newShare();

		$user = $this->createMock('\OCP\IUser');
		$share->setSharedBy('user')->setSharedWith('group');

		$this->groupManager->method('get')->with('group')->willReturn(null);
		$this->userManager->method('get')->with('user')->willReturn($user);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_only_share_with_membership_groups', 'no', 'yes'],
				['core', 'shareapi_allow_group_sharing', 'yes', 'yes'],
			]));

		$this->assertNull($this->invokePrivate($this->manager, 'groupCreateChecks', [$share]));
	}

	public function testGroupCreateChecksShareWithGroupMembersOnlyInGroup() {
		$share = $this->manager->newShare();

		$user = $this->createMock('\OCP\IUser');
		$group = $this->createMock('\OCP\IGroup');
		$share->setSharedBy('user')->setSharedWith('group');

		$this->userManager->method('get')->with('user')->willReturn($user);
		$this->groupManager->method('get')->with('group')->willReturn($group);

		$group->method('inGroup')->with($user)->willReturn(true);

		$path = $this->createMock('\OCP\Files\Node');
		$share->setNode($path);

		$this->defaultProvider->method('getSharesByPath')
			->with($path)
			->willReturn([]);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_only_share_with_membership_groups', 'no', 'yes'],
				['core', 'shareapi_allow_group_sharing', 'yes', 'yes'],
			]));

		$this->assertNull(
			$this->invokePrivate($this->manager, 'groupCreateChecks', [$share])
		);
	}

	/**
	 */
	public function testGroupCreateChecksPathAlreadySharedWithSameGroup() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Path already shared with this group');

		$share = $this->manager->newShare();

		$path = $this->createMock('\OCP\Files\Node');
		$share->setSharedWith('sharedWith')
			->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setNode($path)
			->setProviderId('foo')
			->setId('bar');

		$share2 = $this->manager->newShare();
		$share2->setSharedWith('sharedWith')
			->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setProviderId('foo')
			->setId('baz');

		$this->defaultProvider->method('getSharesByPath')
			->with($path)
			->willReturn([$share2]);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_group_sharing', 'yes', 'yes'],
			]));

		$this->invokePrivate($this->manager, 'groupCreateChecks', [$share]);
	}

	public function testGroupCreateChecksPathAlreadySharedWithDifferentGroup() {
		$share = $this->manager->newShare();

		$share->setSharedWith('sharedWith');

		$path = $this->createMock('\OCP\Files\Node');
		$share->setNode($path);

		$share2 = $this->manager->newShare();
		$share2->setSharedWith('sharedWith2');

		$this->defaultProvider->method('getSharesByPath')
			->with($path)
			->willReturn([$share2]);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_group_sharing', 'yes', 'yes'],
			]));

		$this->assertNull(
			$this->invokePrivate($this->manager, 'groupCreateChecks', [$share])
		);
	}

	/**
	 */
	public function testLinkCreateChecksNoLinkSharesAllowed() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Link sharing not allowed');

		$share = $this->manager->newShare();

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_links', 'yes', 'no'],
			]));

		$this->invokePrivate($this->manager, 'linkCreateChecks', [$share]);
	}

	/**
	 */
	public function testLinkCreateChecksSharePermissions() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Link shares can\'t have reshare permissions');

		$share = $this->manager->newShare();

		$share->setPermissions(\OCP\Constants::PERMISSION_SHARE);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_links', 'yes', 'yes'],
			]));

		$this->invokePrivate($this->manager, 'linkCreateChecks', [$share]);
	}

	/**
	 */
	public function testLinkCreateChecksNoPublicUpload() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Public upload not allowed');

		$share = $this->manager->newShare();

		$share->setPermissions(\OCP\Constants::PERMISSION_CREATE | \OCP\Constants::PERMISSION_UPDATE);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_links', 'yes', 'yes'],
				['core', 'shareapi_allow_public_upload', 'yes', 'no']
			]));

		$this->invokePrivate($this->manager, 'linkCreateChecks', [$share]);
	}

	public function testLinkCreateChecksPublicUpload() {
		$share = $this->manager->newShare();

		$share->setPermissions(\OCP\Constants::PERMISSION_CREATE | \OCP\Constants::PERMISSION_UPDATE);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_links', 'yes', 'yes'],
				['core', 'shareapi_allow_public_upload', 'yes', 'yes']
			]));

		$this->assertNull(
			$this->invokePrivate($this->manager, 'linkCreateChecks', [$share])
		);
	}

	public function testLinkCreateChecksReadOnly() {
		$share = $this->manager->newShare();

		$share->setPermissions(\OCP\Constants::PERMISSION_READ);

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_links', 'yes', 'yes'],
				['core', 'shareapi_allow_public_upload', 'yes', 'no']
			]));

		$this->assertNull(
			$this->invokePrivate($this->manager, 'linkCreateChecks', [$share])
		);
	}

	/**
	 */
	public function testPathCreateChecksContainsSharedMount() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Path contains files shared with you');

		$path = $this->createMock('\OCP\Files\Folder');
		$path->method('getPath')->willReturn('path');

		$mount = $this->createMock('\OCP\Files\Mount\IMountPoint');
		$storage = $this->createMock('\OCP\Files\Storage');
		$mount->method('getStorage')->willReturn($storage);
		$storage->method('instanceOfStorage')->with('\OCA\Files_Sharing\ISharedStorage')->willReturn(true);

		$this->mountManager->method('findIn')->with('path')->willReturn([$mount]);

		$this->invokePrivate($this->manager, 'pathCreateChecks', [$path]);
	}

	public function testPathCreateChecksContainsNoSharedMount() {
		$path = $this->createMock('\OCP\Files\Folder');
		$path->method('getPath')->willReturn('path');

		$mount = $this->createMock('\OCP\Files\Mount\IMountPoint');
		$storage = $this->createMock('\OCP\Files\Storage');
		$mount->method('getStorage')->willReturn($storage);
		$storage->method('instanceOfStorage')->with('\OCA\Files_Sharing\ISharedStorage')->willReturn(false);

		$this->mountManager->method('findIn')->with('path')->willReturn([$mount]);

		$this->assertNull(
			$this->invokePrivate($this->manager, 'pathCreateChecks', [$path])
		);
	}

	public function testPathCreateChecksContainsNoFolder() {
		$path = $this->createMock('\OCP\Files\File');

		$this->assertNull(
			$this->invokePrivate($this->manager, 'pathCreateChecks', [$path])
		);
	}

	public function dataIsSharingDisabledForUser() {
		$data = [];

		// No exclude groups
		$data[] = ['no', null, null, null, false];

		// empty exclude list, user no groups
		$data[] = ['yes', '', \json_encode(['']), [], false];

		// empty exclude list, user groups
		$data[] = ['yes', '', \json_encode(['']), ['group1', 'group2'], false];

		// Convert old list to json
		$data[] = ['yes', 'group1,group2', \json_encode(['group1', 'group2']), [], false];

		// Old list partly groups in common

		$data[] = ['yes', 'group1,group2', \json_encode(['group1', 'group2']), ['group1', 'group3'], true];

		// Old list only groups in common
		$data[] = ['yes', 'group1,group2', \json_encode(['group1', 'group2']), ['group1'], true];

		// New list partly in common
		$data[] = ['yes', \json_encode(['group1', 'group2']), null, ['group1', 'group3'], true];

		// New list only groups in common
		$data[] = ['yes', \json_encode(['group1', 'group2']), null, ['group2'], true];

		// New list partly in common, group names containing comma
		$data[] = ['yes', \json_encode(['group1,a', 'group2']), null, ['group1,a', 'group3'], true];
		$data[] = ['yes', \json_encode(['group1,a', 'group2,b']), null, ['group1,a', 'group3'], true];
		$data[] = ['yes', \json_encode(['group1,a', 'group2']), null, ['group1,a', 'group3,c'], true];

		// New list only groups in common, group names containing comma
		$data[] = ['yes', \json_encode(['group1', 'group2,a']), null, ['group2,a'], true];
		$data[] = ['yes', \json_encode(['group1,a', 'group2,a']), null, ['group2,a'], true];

		return $data;
	}

	/**
	 * @dataProvider dataIsSharingDisabledForUser
	 *
	 * @param string $excludeGroups
	 * @param string $groupList
	 * @param string $setList
	 * @param string[] $groupIds
	 * @param bool $expected
	 */
	public function testIsSharingDisabledForUser($excludeGroups, $groupList, $setList, $groupIds, $expected) {
		$user = $this->createMock('\OCP\IUser');

		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_exclude_groups', 'no', $excludeGroups],
				['core', 'shareapi_exclude_groups_list', '', $groupList],
			]));

		if ($setList !== null) {
			$this->config->expects($this->once())
				->method('setAppValue')
				->with('core', 'shareapi_exclude_groups_list', $setList);
		} else {
			$this->config->expects($this->never())
				->method('setAppValue');
		}

		$this->groupManager->method('getUserGroupIds')
			->with($user)
			->willReturn($groupIds);

		$this->userManager->method('get')->with('user')->willReturn($user);

		$res = $this->manager->sharingDisabledForUser('user');
		$this->assertEquals($expected, $res);
	}

	public function dataCanShare() {
		$data = [];

		/*
		 * [expected, sharing enabled, disabled for user]
		 */

		$data[] = [false, 'no', false];
		$data[] = [false, 'no', true];
		$data[] = [true, 'yes', false];
		$data[] = [false, 'yes', true];

		return $data;
	}

	/**
	 * @dataProvider dataCanShare
	 *
	 * @param bool $expected
	 * @param string $sharingEnabled
	 * @param bool $disabledForUser
	 */
	public function testCanShare($expected, $sharingEnabled, $disabledForUser) {
		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_enabled', 'yes', $sharingEnabled],
			]));

		$manager = $this->createManagerMock()
			->setMethods(['sharingDisabledForUser'])
			->getMock();

		$manager->method('sharingDisabledForUser')
			->with('user')
			->willReturn($disabledForUser);

		$share = $this->manager->newShare();
		$share->setSharedBy('user');

		$exception = false;
		try {
			$res = $this->invokePrivate($manager, 'canShare', [$share]);
		} catch (\Exception $e) {
			$exception = true;
		}

		$this->assertEquals($expected, !$exception);
	}

	public function provideTransferShareData() {
		return [
			['link'],
			['user'],
			['anotherusertest'],
			['group'],
			['remote']
		];
	}

	/**
	 * @dataProvider provideTransferShareData
	 */
	public function testTransferShare($sharetype) {
		$this->createUser('user1');
		$this->createUser('user2');
		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks',
				'pathCreateChecks', 'deleteChildren', 'groupCreateChecks', 'updateShare'])
			->getMock();

		$this->loginAsUser('user1');
		$this->loginAsUser('user2');
		$user1Folder = \OC::$server->getUserFolder('user1');
		$user2Folder = \OC::$server->getUserFolder('user2');
		$user1Folder->newFolder('test_share');
		$user2Folder->newFolder('user1_transferred');

		$share = $this->manager->newShare();
		$share2 = $this->manager->newShare();
		$share->setProviderId('foo')
			->setId('22')
			->setSharedBy('user1')
			->setShareOwner('user1');

		if ($sharetype === 'link') {
			$share->setShareType(\OCP\Share::SHARE_TYPE_LINK)
				->setName('test_share link');
		}
		if ($sharetype === 'user') {
			$share->setShareType(\OCP\Share::SHARE_TYPE_USER)
				->setName('usershare');
			$share->setSharedWith('user2');

			$user1File = $user1Folder->newFile('test_share/somefile.txt');
			$user1File->putContent('A test file');
			$share2->setProviderId('publink')
				->setId('29')
				->setSharedBy('user1')
				->setShareOwner('user1')
				->setShareType(\OCP\Share::SHARE_TYPE_LINK)
				->setName('somefile link');

			$expressionBuilder = $this->createMock(IExpressionBuilder::class);
			$expressionBuilder->expects($this->once())
				->method('eq')
				->willReturn(true);
			$queryBuilder = $this->createMock(IQueryBuilder::class);
			$queryBuilder->expects($this->once())
				->method('update')
				->willReturn($queryBuilder);
			$queryBuilder->expects($this->once())
				->method('set')
				->willReturn($queryBuilder);
			$queryBuilder->expects($this->once())
				->method('where')
				->willReturn($queryBuilder);
			$queryBuilder->expects($this->once())
				->method('expr')
				->willReturn($expressionBuilder);
			$queryBuilder->expects($this->once())
				->method('createNamedParameter')
				->willReturn($this->createMock(IParameter::class));
			$this->connection->expects($this->once())
				->method('getQueryBuilder')
				->willReturn($queryBuilder);

			$manager->expects($this->once())->method('deleteChildren')->with($share);
			$this->defaultProvider
				->expects($this->once())
				->method('delete')
				->with($share);

			$hookListnerUnshare = $this->getMockBuilder('Dummy')->setMethods(['pre', 'post'])->getMock();
			\OCP\Util::connectHook('OCP\Share', 'pre_unshare', $hookListnerUnshare, 'pre');
			\OCP\Util::connectHook('OCP\Share', 'post_unshare', $hookListnerUnshare, 'post');

			$mountPoint = $this->createMock(IMountPoint::class);
			$this->mountManager->expects($this->once())
				->method('find')
				->willReturn($mountPoint);
		}
		if ($sharetype === 'anotherusertest') {
			$share->setShareType(\OCP\Share::SHARE_TYPE_USER);
			$share->setSharedWith('completelydifferentuser');
		}
		if ($sharetype === 'group') {
			$share->setShareType(\OCP\Share::SHARE_TYPE_GROUP);
			$share->setSharedWith('foo');
		}
		if ($sharetype === 'remote') {
			$share->setShareType(\OCP\Share::SHARE_TYPE_REMOTE);
			$share->setSharedWith('differentUser@server.com');
		}

		$shareOwner = $this->createMock('\OCP\IUser');
		$shareOwner->method('getUID')->willReturn('user1');

		$this->defaultProvider
			->expects($this->any())
			->method('getChildren')
			->will($this->returnCallback(function () use ($sharetype, $share2) {
				if ($sharetype === 'user') {
					return [$share2];
				}
				return [];
			}));

		$storage = $this->createMock(Storage::class);
		$path = $this->createMock(File::class);
		$path->method('getOwner')->willReturn($shareOwner);
		$path->method('getName')->willReturn('test_share');
		$path->method('getId')->willReturn(1);
		$path->method('getStorage')->willReturn($storage);

		$share->setNode($path)
			->setPermissions(15);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listner'])->getMock();
		\OCP\Util::connectHook('\OC\Share', 'verifyPassword', $hookListner, 'listner');
		$hookListner->expects($this->any())
			->method('listner')
			->will($this->returnCallback(function (array $array) {
				$array['accepted'] = true;
				$array['message'] = 'password accepted';
			}));

		$this->config
			->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_allow_links', 'yes', 'yes'],
				['core', 'shareapi_allow_public_upload', 'yes', 'yes'],
				['core', 'shareapi_allow_group_sharing', 'yes', 'yes']
			]));

		$this->defaultProvider
			->expects($this->once())
			->method('create')
			->with($share)
			->will($this->returnArgument(0));

		if ($sharetype === 'group') {
			$manager->expects($this->any())
				->method('groupCreateChecks')
				->with($share);
		}

		$share = $manager->createShare($share);
		$view = new View('/');
		$view->rename($user1Folder->getPath() . '/test_share', $user2Folder->getPath() . '/user1_transferred/test_share');
		$node = $user2Folder->get('user1_transferred/test_share');
		$share->setNode($node);

		$this->defaultProvider->expects($this->any())
			->method('getShareById')
			->will($this->returnValueMap([
				[22, $share],
				[29, $share2]
			]));
		$this->defaultProvider->expects($this->any())
			->method('update')
			->with($share)
			->willReturn($share);

		$compareToken = $share->getToken();

		$this->view->expects($this->any())
			->method('file_exists')
			->willReturn(true);

		$user1 = $this->createMock(IUser::class);
		$user2 = $this->createMock(IUser::class);
		$this->userManager->expects($this->any())
			->method('get')
			->will($this->returnValueMap([
				['user1', false, $user1],
				['user2', false, $user2]
			]));

		if ($sharetype === 'user') {
			$hookListnerUnshareExpectsPre = [
				'id' => '22',
				'itemType' => 'folder',
				'itemSource' => $share->getNodeId(),
				'shareType' => \OCP\Share::SHARE_TYPE_USER,
				'shareWith' => 'user2',
				'itemparent' => null,
				'uidOwner' => 'user1',
				'fileSource' => $share->getNodeId(),
				'fileTarget' => '/test_share'
			];
			$hookListnerUnshareExpectsPost = [
				'id' => '22',
				'itemType' => 'folder',
				'itemSource' => $share->getNodeId(),
				'shareType' => \OCP\Share::SHARE_TYPE_USER,
				'shareWith' => 'user2',
				'itemparent' => null,
				'uidOwner' => 'user1',
				'fileSource' => $share->getNodeId(),
				'fileTarget' => '/test_share',
				'deletedShares' => [
					[
						'id' => 22,
						'itemType' => 'folder',
						'itemSource' => $share->getNodeId(),
						'shareType' => \OCP\Share::SHARE_TYPE_USER,
						'shareWith' => 'user2',
						'itemparent' => null,
						'uidOwner' => 'user1',
						'fileSource' => $share->getNodeId(),
						'fileTarget' => '/test_share',
					]
				],
			];
			$hookListnerUnshare
				->expects($this->exactly(1))
				->method('pre')
				->with($hookListnerUnshareExpectsPre);
			$hookListnerUnshare
				->expects($this->exactly(1))
				->method('post')
				->with($hookListnerUnshareExpectsPost);
		}

		$manager->transferShare($share, 'user1', 'user2', \ltrim($user2Folder->getPath() . '/user1_transferred', '/'));

		if ($sharetype === 'link') {
			$this->assertEquals($share->getShareOwner(), 'user2');
			$this->assertEquals($share->getSharedBy(), 'user2');
			$this->assertEquals($share->getTarget(), '/user1_transferred/test_share');
			$this->assertEquals($share->getName(), 'test_share link');
			$this->assertEquals($share->getToken(), $compareToken);
		}

		if ($sharetype === 'anotherusertest') {
			$this->assertEquals($share->getId(), '22');
			$this->assertEquals($share->getSharedWith(), 'completelydifferentuser');
			$this->assertEquals($share->getSharedBy(), 'user2');
			$this->assertEquals($share->getShareOwner(), 'user2');
			$this->assertEquals($share->getTarget(), '/user1_transferred/test_share');
		}

		if ($sharetype === 'group') {
			$this->assertEquals($share->getId(), '22');
			$this->assertEquals($share->getSharedWith(), 'foo');
			$this->assertEquals($share->getSharedBy(), 'user2');
			$this->assertEquals($share->getShareOwner(), 'user2');
			$this->assertEquals($share->getTarget(), '/user1_transferred/test_share');
		}

		if ($sharetype === 'remote') {
			$this->assertEquals($share->getId(), '22');
			$this->assertEquals($share->getSharedWith(), 'differentUser@server.com');
			$this->assertEquals($share->getShareOwner(), 'user2');
			$this->assertEquals($share->getSharedBy(), 'user2');
			$this->assertEquals($share->getTarget(), '/user1_transferred/test_share');
		}
	}

	/**
	 */
	public function testTransferShareNoOldOwner() {
		$this->expectException(\OCP\Share\Exceptions\TransferSharesException::class);
		$this->expectExceptionMessage('The current owner of the share user1 doesn\'t exist');

		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks',
				'pathCreateChecks', 'deleteChildren', 'groupCreateChecks'])
			->getMock();
		$this->userManager->expects($this->once())
			->method('get')
			->with('user1')
			->willReturn(null);
		$share = $this->createShare('23', \OCP\Share::SHARE_TYPE_USER,
			'/foo', 'user2', 'user1', 'user1',
			15);
		$manager->transferShare($share, 'user1', 'user2', 'user1/files/transferred');
	}

	/**
	 */
	public function testTransferShareNoNewOwner() {
		$this->expectException(\OCP\Share\Exceptions\TransferSharesException::class);
		$this->expectExceptionMessage('The future owner user2, where the share has to be moved doesn\'t exist');

		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks',
				'pathCreateChecks', 'deleteChildren', 'groupCreateChecks'])
			->getMock();
		$this->userManager->method('get')
			->will($this->returnValueMap([
				['user1', false, $this->createMock(IUser::class)],
				['user2', false, null]
			]));
		$share = $this->createShare('23', \OCP\Share::SHARE_TYPE_USER,
			'/foo', 'user2', 'user1', 'user1',
			15);
		$manager->transferShare($share, 'user1', 'user2', 'user1/files/transferred');
	}

	/**
	 */
	public function testTransferShareNoSameOwner() {
		$this->expectException(\OCP\Share\Exceptions\TransferSharesException::class);
		$this->expectExceptionMessage('The current owner of the share and the future owner of the share are same');

		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks',
				'pathCreateChecks', 'deleteChildren', 'groupCreateChecks'])
			->getMock();
		$this->userManager->method('get')
			->will($this->returnValueMap([
				['user1', false, $this->createMock(IUser::class)],
			]));
		$share = $this->createShare('23', \OCP\Share::SHARE_TYPE_USER,
			'/foo', 'user2', 'user1', 'user1',
			15);
		$manager->transferShare($share, 'user1', 'user1', 'user1/files/transferred');
	}

	/**
	 */
	public function testTransferShareNonExistingFinalTarget() {
		$this->expectException(\OCP\Files\NotFoundException::class);
		$this->expectExceptionMessage('The target location user1/files/transferred doesn\'t exist');

		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks',
				'pathCreateChecks', 'deleteChildren', 'groupCreateChecks'])
			->getMock();
		$this->userManager->method('get')
			->will($this->returnValueMap([
				['user1', false, $this->createMock(IUser::class)],
				['user2', false, $this->createMock(IUser::class)],
			]));
		$this->view->expects($this->once())
			->method('file_exists')
			->willReturn(false);
		$share = $this->createShare('23', \OCP\Share::SHARE_TYPE_USER,
			'/foo', 'user2', 'user1', 'user1',
			15);
		$manager->transferShare($share, 'user1', 'user2', 'user1/files/transferred');
	}

	public function testCreateShareUser() {
		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks', 'pathCreateChecks'])
			->getMock();

		$shareOwner = $this->createMock('\OCP\IUser');
		$shareOwner->method('getUID')->willReturn('shareOwner');

		$storage = $this->createMock('\OCP\Files\Storage');
		$path = $this->createMock('\OCP\Files\File');
		$path->method('getOwner')->willReturn($shareOwner);
		$path->method('getName')->willReturn('target');
		$path->method('getStorage')->willReturn($storage);

		$share = $this->createShare(
			null,
			\OCP\Share::SHARE_TYPE_USER,
			$path,
			'sharedWith',
			'sharedBy',
			null,
			\OCP\Constants::PERMISSION_ALL);

		$manager->expects($this->once())
			->method('canShare')
			->with($share)
			->willReturn(true);
		$manager->expects($this->once())
			->method('generalChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('userCreateChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('pathCreateChecks')
			->with($path);

		$this->defaultProvider
			->expects($this->once())
			->method('create')
			->with($share)
			->will($this->returnArgument(0));

		$share->expects($this->once())
			->method('setShareOwner')
			->with('shareOwner');
		$share->expects($this->once())
			->method('setTarget')
			->with('/target');

		$calledBeforeShareCreate = [];
		$this->eventDispatcher->addListener('share.beforeCreate',
			function (GenericEvent $event) use (&$calledBeforeShareCreate) {
				$calledBeforeShareCreate[] = 'share.beforeCreate';
				$calledBeforeShareCreate[] = $event;
			});
		$calledAfterShareCreate = [];
		$this->eventDispatcher->addListener('share.afterCreate',
			function (GenericEvent $event) use (&$calledAfterShareCreate) {
				$calledAfterShareCreate[] = 'share.afterCreate';
				$calledAfterShareCreate[] = $event;
			});

		$manager->createShare($share);

		$this->assertEquals('share.beforeCreate', $calledBeforeShareCreate[0]);
		$this->assertEquals('share.afterCreate', $calledAfterShareCreate[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeShareCreate[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeShareCreate[1]);
	}

	public function testCreateShareGroup() {
		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'groupCreateChecks', 'pathCreateChecks'])
			->getMock();

		$shareOwner = $this->createMock('\OCP\IUser');
		$shareOwner->method('getUID')->willReturn('shareOwner');

		$storage = $this->createMock('\OCP\Files\Storage');
		$path = $this->createMock('\OCP\Files\File');
		$path->method('getOwner')->willReturn($shareOwner);
		$path->method('getName')->willReturn('target');
		$path->method('getStorage')->willReturn($storage);

		$share = $this->createShare(
			null,
			\OCP\Share::SHARE_TYPE_GROUP,
			$path,
			'sharedWith',
			'sharedBy',
			null,
			\OCP\Constants::PERMISSION_ALL);

		$manager->expects($this->once())
			->method('canShare')
			->with($share)
			->willReturn(true);
		$manager->expects($this->once())
			->method('generalChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('groupCreateChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('pathCreateChecks')
			->with($path);

		$this->defaultProvider
			->expects($this->once())
			->method('create')
			->with($share)
			->will($this->returnArgument(0));

		$share->expects($this->once())
			->method('setShareOwner')
			->with('shareOwner');
		$share->expects($this->once())
			->method('setTarget')
			->with('/target');

		$calledBeforeShareCreate = [];
		$this->eventDispatcher->addListener('share.beforeCreate',
			function (GenericEvent $event) use (&$calledBeforeShareCreate) {
				$calledBeforeShareCreate[] = 'share.beforeCreate';
				$calledBeforeShareCreate[] = $event;
			});
		$calledAfterShareCreate = [];
		$this->eventDispatcher->addListener('share.afterCreate',
			function (GenericEvent $event) use (&$calledAfterShareCreate) {
				$calledAfterShareCreate[] = 'share.afterCreate';
				$calledAfterShareCreate[] = $event;
			});

		$manager->createShare($share);

		$this->assertEquals('share.beforeCreate', $calledBeforeShareCreate[0]);
		$this->assertEquals('share.afterCreate', $calledAfterShareCreate[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeShareCreate[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeShareCreate[1]);
	}

	public function providesDataToHashPassword() {
		return [
			[true],
			[false]
		];
	}

	/**
	 * @dataProvider providesDataToHashPassword
	 */
	public function testCreateShareLink($shouldHashPassword) {
		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'generalChecks',
				'linkCreateChecks',
				'pathCreateChecks',
				'validateExpirationDate',
				'verifyPassword',
				'setLinkParent',
			])
			->getMock();

		$shareOwner = $this->createMock('\OCP\IUser');
		$shareOwner->method('getUID')->willReturn('shareOwner');

		$storage = $this->createMock('\OCP\Files\Storage');
		$path = $this->createMock('\OCP\Files\File');
		$path->method('getOwner')->willReturn($shareOwner);
		$path->method('getName')->willReturn('target');
		$path->method('getId')->willReturn(1);
		$path->method('getStorage')->willReturn($storage);

		$date = new \DateTime();

		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setNode($path)
			->setSharedBy('sharedBy')
			->setPermissions(\OCP\Constants::PERMISSION_ALL)
			->setExpirationDate($date)
			->setPassword('password');

		if ($shouldHashPassword === false) {
			$share->setShouldHashPassword($shouldHashPassword);
		}

		$manager->expects($this->once())
			->method('canShare')
			->with($share)
			->willReturn(true);
		$manager->expects($this->once())
			->method('generalChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('linkCreateChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('pathCreateChecks')
			->with($path);
		$manager->expects($this->once())
			->method('validateExpirationDate')
			->with($share);
		$manager->expects($this->once())
			->method('verifyPassword')
			->with('password');
		$manager->expects($this->once())
			->method('setLinkParent')
			->with($share);

		if ($shouldHashPassword === true) {
			$this->hasher->expects($this->once())
				->method('hash')
				->with('password')
				->willReturn('hashed');
		}

		$this->secureRandom->method('getMediumStrengthGenerator')
			->will($this->returnSelf());
		$this->secureRandom->method('generate')
			->willReturn('token');

		$this->defaultProvider
			->expects($this->once())
			->method('create')
			->with($share)
			->will($this->returnCallback(function (Share $share) {
				return $share->setId(42);
			}));

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['pre', 'post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'pre_shared', $hookListner, 'pre');
		\OCP\Util::connectHook('OCP\Share', 'post_shared', $hookListner, 'post');

		$hookListnerExpectsPre = [
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => \OCP\Share::SHARE_TYPE_LINK,
			'uidOwner' => 'sharedBy',
			'permissions' => 31,
			'fileSource' => 1,
			'expiration' => $date,
			'token' => 'token',
			'run' => true,
			'error' => '',
			'itemTarget' => '/target',
			'shareWith' => null,
			'attributes' => null,
		];

		$hookListnerExpectsPost = [
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => \OCP\Share::SHARE_TYPE_LINK,
			'uidOwner' => 'sharedBy',
			'permissions' => 31,
			'fileSource' => 1,
			'expiration' => $date,
			'token' => 'token',
			'id' => 42,
			'itemTarget' => '/target',
			'fileTarget' => '/target',
			'shareWith' => null,
			'attributes' => null,
			'passwordEnabled' => true,
		];

		$hookListner->expects($this->once())
			->method('pre')
			->with($this->equalTo($hookListnerExpectsPre));
		$hookListner->expects($this->once())
			->method('post')
			->with($this->equalTo($hookListnerExpectsPost));

		$calledBeforeShareCreate = [];
		$this->eventDispatcher->addListener('share.beforeCreate',
			function (GenericEvent $event) use (&$calledBeforeShareCreate) {
				$calledBeforeShareCreate[] = 'share.beforeCreate';
				$calledBeforeShareCreate[] = $event;
			});
		$calledAfterShareCreate = [];
		$this->eventDispatcher->addListener('share.afterCreate',
			function (GenericEvent $event) use (&$calledAfterShareCreate) {
				$calledAfterShareCreate[] = 'share.afterCreate';
				$calledAfterShareCreate[] = $event;
			});

		/** @var IShare $share */
		$share = $manager->createShare($share);

		$this->assertSame('shareOwner', $share->getShareOwner());
		$this->assertEquals('/target', $share->getTarget());
		$this->assertSame($date, $share->getExpirationDate());
		$this->assertEquals('token', $share->getToken());
		if ($shouldHashPassword === true) {
			$this->assertEquals('hashed', $share->getPassword());
		} else {
			$this->assertEquals('password', $share->getPassword());
		}

		$this->assertEquals('share.beforeCreate', $calledBeforeShareCreate[0]);
		$this->assertEquals('share.afterCreate', $calledAfterShareCreate[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeShareCreate[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeShareCreate[1]);
	}

	/**
	 */
	public function testCreateShareHookError() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('I won\'t let you share');

		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'generalChecks',
				'userCreateChecks',
				'pathCreateChecks',
			])
			->getMock();

		$shareOwner = $this->createMock('\OCP\IUser');
		$shareOwner->method('getUID')->willReturn('shareOwner');

		$storage = $this->createMock('\OCP\Files\Storage');
		$path = $this->createMock('\OCP\Files\File');
		$path->method('getOwner')->willReturn($shareOwner);
		$path->method('getName')->willReturn('target');
		$path->method('getStorage')->willReturn($storage);

		$share = $this->createShare(
			null,
			\OCP\Share::SHARE_TYPE_USER,
			$path,
			'sharedWith',
			'sharedBy',
			null,
			\OCP\Constants::PERMISSION_ALL);

		$manager->expects($this->once())
			->method('canShare')
			->with($share)
			->willReturn(true);
		$manager->expects($this->once())
			->method('generalChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('userCreateChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('pathCreateChecks')
			->with($path);

		$share->expects($this->once())
			->method('setShareOwner')
			->with('shareOwner');
		$share->expects($this->once())
			->method('setTarget')
			->with('/target');

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['pre'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'pre_shared', $hookListner, 'pre');
		$hookListner->expects($this->once())
			->method('pre')
			->will($this->returnCallback(function (array $data) {
				$data['run'] = false;
				$data['error'] = 'I won\'t let you share!';
			}));

		$manager->createShare($share);
	}

	public function testCreateShareOfIncomingFederatedShare() {
		$manager = $this->createManagerMock()
			->setMethods(['canShare', 'generalChecks', 'userCreateChecks', 'pathCreateChecks'])
			->getMock();

		$shareOwner = $this->createMock('\OCP\IUser');
		$shareOwner->method('getUID')->willReturn('shareOwner');

		$storage = $this->createMock('\OCP\Files\Storage');
		$storage->method('instanceOfStorage')
			->with('OCA\Files_Sharing\External\Storage')
			->willReturn(true);

		$storage2 = $this->createMock('\OCP\Files\Storage');
		$storage2->method('instanceOfStorage')
			->with('OCA\Files_Sharing\External\Storage')
			->willReturn(false);

		$path = $this->createMock('\OCP\Files\File');
		$path->expects($this->never())->method('getOwner');
		$path->method('getName')->willReturn('target');
		$path->method('getStorage')->willReturn($storage);

		$parent = $this->createMock('\OCP\Files\Folder');
		$parent->method('getStorage')->willReturn($storage);

		$parentParent = $this->createMock('\OCP\Files\Folder');
		$parentParent->method('getStorage')->willReturn($storage2);
		$parentParent->method('getOwner')->willReturn($shareOwner);

		$path->method('getParent')->willReturn($parent);
		$parent->method('getParent')->willReturn($parentParent);

		$share = $this->createShare(
			null,
			\OCP\Share::SHARE_TYPE_USER,
			$path,
			'sharedWith',
			'sharedBy',
			null,
			\OCP\Constants::PERMISSION_ALL);

		$manager->expects($this->once())
			->method('canShare')
			->with($share)
			->willReturn(true);
		$manager->expects($this->once())
			->method('generalChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('userCreateChecks')
			->with($share);
		;
		$manager->expects($this->once())
			->method('pathCreateChecks')
			->with($path);

		$this->defaultProvider
			->expects($this->once())
			->method('create')
			->with($share)
			->will($this->returnArgument(0));

		$share->expects($this->once())
			->method('setShareOwner')
			->with('shareOwner');
		$share->expects($this->once())
			->method('setTarget')
			->with('/target');

		$calledBeforeShareCreate = [];
		$this->eventDispatcher->addListener('share.beforeCreate',
			function (GenericEvent $event) use (&$calledBeforeShareCreate) {
				$calledBeforeShareCreate[] = 'share.beforeCreate';
				$calledBeforeShareCreate[] = $event;
			});
		$calledAfterShareCreate = [];
		$this->eventDispatcher->addListener('share.afterCreate',
			function (GenericEvent $event) use (&$calledAfterShareCreate) {
				$calledAfterShareCreate[] = 'share.afterCreate';
				$calledAfterShareCreate[] = $event;
			});

		$manager->createShare($share);

		$this->assertEquals('share.beforeCreate', $calledBeforeShareCreate[0]);
		$this->assertEquals('share.afterCreate', $calledAfterShareCreate[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeShareCreate[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledAfterShareCreate[1]);
		$this->assertArrayHasKey('shareData', $calledBeforeShareCreate[1]);
		$this->assertArrayHasKey('shareObject', $calledBeforeShareCreate[1]);
	}

	public function testGetAllSharesBy() {
		$share = $this->manager->newShare();

		$node = $this->createMock('OCP\Files\Folder');
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(0));

		$nodes = [$node->getId()];

		for ($i = 1; $i <= 201; $i++) {
			$node = $this->createMock('OCP\Files\File');
			$node->expects($this->any())
				->method('getId')
				->will($this->returnValue($i));
			\array_push($nodes, $node->getId());
		}

		// Test chunking here
		$this->defaultProvider->expects($this->any())
			->method('getAllSharesBy')
			->with(
				$this->equalTo('user'),
				$this->anything(),
				$this->anything(),
				$this->equalTo(true)
			)->willReturn(\array_fill(0, 201, $share));

		$shares = $this->manager->getAllSharesBy('user', [\OCP\Share::SHARE_TYPE_USER], $nodes, true);

		$this->assertCount(201, $shares);
		$this->assertSame($share, $shares[0]);
		$this->assertSame($share, $shares[100]);
		$this->assertSame($share, $shares[200]);
	}

	public function testGetAllSharesByExpiration() {
		$manager = $this->createManagerMock()
			->setMethods(['deleteShare'])
			->getMock();

		$share = $this->manager->newShare();

		$shareExpired = $this->createMock(IShare::class);
		$shareExpired->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$shareExpired->method('getNodeId')->willReturn(201);
		$yesterday = new \DateTime("yesterday");
		$shareExpired->method('getExpirationDate')->willReturn($yesterday);

		$node = $this->createMock('OCP\Files\Folder');
		$node->expects($this->any())
			->method('getId')
			->will($this->returnValue(0));

		$nodes = [$node->getId()];

		for ($i = 1; $i <= 201; $i++) {
			$node = $this->createMock('OCP\Files\File');
			$node->expects($this->any())
				->method('getId')
				->will($this->returnValue($i));
			\array_push($nodes, $node->getId());
		}

		// Add 201 shares, including expired
		$fillShares = \array_fill(0, 200, $share);
		$fillShares[] = $shareExpired;

		// Test chunking here
		$this->defaultProvider->expects($this->any())
			->method('getAllSharesBy')
			->with(
				$this->equalTo('user'),
				$this->anything(),
				$this->anything(),
				$this->equalTo(true)
			)->willReturn($fillShares);

		$manager->expects($this->any())
			->method('deleteShare')
			->will($this->throwException(new \OCP\Files\NotFoundException));

		$shares = $manager->getAllSharesBy('user', [\OCP\Share::SHARE_TYPE_USER], $nodes, true);

		// One share whould be expired
		$this->assertCount(200, $shares);
		$this->assertSame($share, $shares[0]);
		$this->assertSame($share, $shares[100]);
		$this->assertNotContains($shareExpired, $shares);
	}

	/**
	 */
	public function testGetAllSharesByException() {
		$this->expectException(\InvalidArgumentException::class);

		$shares = $this->manager->getAllSharesBy('user', [\OCP\Share::SHARE_TYPE_USER], [], true);
	}

	public function testGetSharesBy() {
		$share = $this->manager->newShare();

		$node = $this->createMock('OCP\Files\Folder');

		$this->defaultProvider->expects($this->once())
			->method('getSharesBy')
			->with(
				$this->equalTo('user'),
				$this->equalTo(\OCP\Share::SHARE_TYPE_USER),
				$this->equalTo($node),
				$this->equalTo(true),
				$this->equalTo(1),
				$this->equalTo(1)
			)->willReturn([$share]);

		$shares = $this->manager->getSharesBy('user', \OCP\Share::SHARE_TYPE_USER, $node, true, 1, 1);

		$this->assertCount(1, $shares);
		$this->assertSame($share, $shares[0]);
	}

	/**
	 * Test to ensure we correctly remove expired link shares
	 *
	 * We have 8 Shares and we want the 3 first valid shares.
	 * share 3-6 and 8 are expired. Thus at the end of this test we should
	 * have received share 1,2 and 7. And from the manager. Share 3-6 should be
	 * deleted (as they are evaluated). but share 8 should still be there.
	 */
	public function testGetSharesByExpiredLinkShares() {
		$manager = $this->createManagerMock()
			->setMethods(['deleteShare'])
			->getMock();

		/** @var \OCP\Share\IShare[] $shares */
		$shares = [];

		/*
		 * This results in an array of 8 IShare elements
		 */
		for ($i = 0; $i < 8; $i++) {
			$share = $this->manager->newShare();
			$share->setId($i);
			$shares[] = $share;
		}

		$yesterday = new \DateTime("yesterday");
		$yesterday->setTime(0, 0, 0);

		/*
		 * Set the expiration date to yesterday for some shares
		 */
		$shares[2]->setExpirationDate($yesterday);
		$shares[3]->setExpirationDate($yesterday);
		$shares[4]->setExpirationDate($yesterday);
		$shares[5]->setExpirationDate($yesterday);

		/** @var \OCP\Share\IShare[] $i */
		$shares2 = [];
		for ($i = 0; $i < 8; $i++) {
			$shares2[] = clone $shares[$i];
		}

		$node = $this->createMock('OCP\Files\File');

		/*
		 * Simulate the getSharesBy call.
		 */
		$this->defaultProvider
			->method('getSharesBy')
			->will($this->returnCallback(function ($uid, $type, $node, $reshares, $limit, $offset) use (&$shares2) {
				return \array_slice($shares2, $offset, $limit);
			}));

		/*
		 * Simulate the deleteShare call.
		 */
		$manager->method('deleteShare')
			->will($this->returnCallback(function ($share) use (&$shares2) {
				for ($i = 0; $i < \count($shares2); $i++) {
					if ($shares2[$i]->getId() === $share->getId()) {
						\array_splice($shares2, $i, 1);
						break;
					}
				}
			}));

		$res = $manager->getSharesBy('user', \OCP\Share::SHARE_TYPE_LINK, $node, true, 3, 0);

		$this->assertCount(3, $res);
		$this->assertEquals($shares[0]->getId(), $res[0]->getId());
		$this->assertEquals($shares[1]->getId(), $res[1]->getId());
		$this->assertEquals($shares[6]->getId(), $res[2]->getId());

		$this->assertCount(4, $shares2);
		$this->assertEquals(0, $shares2[0]->getId());
		$this->assertEquals(1, $shares2[1]->getId());
		$this->assertEquals(6, $shares2[2]->getId());
		$this->assertEquals(7, $shares2[3]->getId());
		$this->assertSame($yesterday, $shares[3]->getExpirationDate());
	}

	public function testGetShareByToken() {
		$factory = $this->createMock('\OCP\Share\IProviderFactory');

		$manager = new Manager(
			$this->logger,
			$this->config,
			$this->secureRandom,
			$this->hasher,
			$this->mountManager,
			$this->groupManager,
			$this->l,
			$factory,
			$this->userManager,
			$this->rootFolder,
			$this->eventDispatcher,
			$this->view,
			$this->connection
		);

		$share = $this->createMock('\OCP\Share\IShare');

		$factory->expects($this->once())
			->method('getProviderForType')
			->with(\OCP\Share::SHARE_TYPE_LINK)
			->willReturn($this->defaultProvider);

		$this->defaultProvider->expects($this->once())
			->method('getShareByToken')
			->with('token')
			->willReturn($share);

		$ret = $manager->getShareByToken('token');
		$this->assertSame($share, $ret);
	}

	public function testGetShareByTokenWithException() {
		$factory = $this->createMock('\OCP\Share\IProviderFactory');

		$manager = new Manager(
			$this->logger,
			$this->config,
			$this->secureRandom,
			$this->hasher,
			$this->mountManager,
			$this->groupManager,
			$this->l,
			$factory,
			$this->userManager,
			$this->rootFolder,
			$this->eventDispatcher,
			$this->view,
			$this->connection
		);

		$share = $this->createMock('\OCP\Share\IShare');

		$factory->expects($this->at(0))
			->method('getProviderForType')
			->with(\OCP\Share::SHARE_TYPE_LINK)
			->willReturn($this->defaultProvider);
		$factory->expects($this->at(1))
			->method('getProviderForType')
			->with(\OCP\Share::SHARE_TYPE_REMOTE)
			->willReturn($this->defaultProvider);

		$this->defaultProvider->expects($this->at(0))
			->method('getShareByToken')
			->with('token')
			->will($this->throwException(new ShareNotFound()));
		$this->defaultProvider->expects($this->at(1))
			->method('getShareByToken')
			->with('token')
			->willReturn($share);

		$ret = $manager->getShareByToken('token');
		$this->assertSame($share, $ret);
	}

	/**
	 */
	public function testGetShareByTokenExpired() {
		$this->expectException(\OCP\Share\Exceptions\ShareNotFound::class);

		$manager = $this->createManagerMock()
			->setMethods(['deleteShare'])
			->getMock();

		$date = new \DateTime("yesterday");
		$date->setTime(0, 0, 0);
		$share = $this->manager->newShare();
		$share->setExpirationDate($date);

		$this->defaultProvider->expects($this->once())
			->method('getShareByToken')
			->with('expiredToken')
			->willReturn($share);

		$manager->expects($this->once())
			->method('deleteShare')
			->with($this->equalTo($share));

		$manager->getShareByToken('expiredToken');
	}

	public function testGetShareByTokenNotExpired() {
		$date = new \DateTime();
		$date->setTime(0, 0, 0);
		$date->add(new \DateInterval('P2D'));
		$share = $this->manager->newShare();
		$share->setExpirationDate($date);

		$this->defaultProvider->expects($this->once())
			->method('getShareByToken')
			->with('expiredToken')
			->willReturn($share);

		$res = $this->manager->getShareByToken('expiredToken');

		$this->assertSame($share, $res);
	}

	public function testGetShareByTokenPublicSharingDisabled() {
		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setPermissions(\OCP\Constants::PERMISSION_READ | \OCP\Constants::PERMISSION_CREATE | \OCP\Constants::PERMISSION_UPDATE);

		$this->config->method('getAppValue')->will($this->returnValueMap([
			['core', 'shareapi_allow_public_upload', 'yes', 'no'],
		]));

		$this->defaultProvider->expects($this->once())
			->method('getShareByToken')
			->willReturn('validToken')
			->willReturn($share);

		$res = $this->manager->getShareByToken('validToken');

		$this->assertSame(\OCP\Constants::PERMISSION_READ, $res->getPermissions());
	}

	public function testGetSharesByPath() {
		$factory = $this->createMock('\OCP\Share\IProviderFactory');

		$manager = new Manager(
			$this->logger,
			$this->config,
			$this->secureRandom,
			$this->hasher,
			$this->mountManager,
			$this->groupManager,
			$this->l,
			$factory,
			$this->userManager,
			$this->rootFolder,
			$this->eventDispatcher,
			$this->view,
			$this->connection
		);

		$provider1 = $this->getMockBuilder('\OC\Share20\DefaultShareProvider')
			->disableOriginalConstructor()
			->getMock();
		$provider1->method('identifier')->willReturn('provider1');
		$provider2 = $this->getMockBuilder('\OC\Share20\DefaultShareProvider')
			->disableOriginalConstructor()
			->getMock();
		$provider2->method('identifier')->willReturn('provider2');

		$factory->expects($this->any())
			->method('getProviderForType')
			->will($this->returnValueMap([
				[\OCP\Share::SHARE_TYPE_USER, $provider1],
				[\OCP\Share::SHARE_TYPE_GROUP, $provider1],
				[\OCP\Share::SHARE_TYPE_LINK, $provider2],
			]));

		$share1 = $this->manager->newShare();
		$share1->setId(42);

		$share2 = $this->manager->newShare();
		$share2->setId(43);

		$share3 = $this->manager->newShare();
		$share3->setId(44);

		$node = $this->createMock('\OCP\Files\Folder');

		$provider1->expects($this->once())
			->method('getSharesByPath')
			->with($node)
			->willReturn([$share1, $share2]);
		$provider2->expects($this->never())->method('getSharesByPath');

		$shares = $manager->getSharesByPath($node);

		$this->assertEquals([$share1, $share2], $shares);
	}

	public function testCheckPasswordNoLinkShare() {
		$share = $this->createMock('\OCP\Share\IShare');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_USER);
		$this->assertFalse($this->manager->checkPassword($share, 'password'));
	}

	public function testCheckPasswordNoPassword() {
		$share = $this->createMock('\OCP\Share\IShare');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$this->assertFalse($this->manager->checkPassword($share, 'password'));

		$share->method('getPassword')->willReturn('password');
		$this->assertFalse($this->manager->checkPassword($share, null));
	}

	public function testCheckPasswordInvalidPassword() {
		$share = $this->createMock('\OCP\Share\IShare');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$share->method('getPassword')->willReturn('password');

		$this->hasher->method('verify')->with('invalidpassword', 'password', '')->willReturn(false);

		$calledBeforeEvent = [];
		$this->eventDispatcher->addListener('share.beforepasswordcheck',
			function (GenericEvent $event) use (&$calledBeforeEvent) {
				$calledBeforeEvent[] = 'share.beforepasswordcheck';
				$calledBeforeEvent[] = $event;
			});
		$calledFailEvent = [];
		$this->eventDispatcher->addListener('share.failedpasswordcheck',
			function (GenericEvent $event) use (&$calledFailEvent) {
				$calledFailEvent[] = 'share.failedpasswordcheck';
				$calledFailEvent[] = $event;
			});
		$this->assertFalse($this->manager->checkPassword($share, 'invalidpassword'));
		$this->assertEquals('share.beforepasswordcheck', $calledBeforeEvent[0]);
		$this->assertEquals('share.failedpasswordcheck', $calledFailEvent[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeEvent[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledFailEvent[1]);
	}

	public function testCheckPasswordValidPassword() {
		$share = $this->createMock('\OCP\Share\IShare');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$share->method('getPassword')->willReturn('passwordHash');

		$calledBeforeEvent = [];
		$this->eventDispatcher->addListener('share.beforepasswordcheck',
			function (GenericEvent $event) use (&$calledBeforeEvent) {
				$calledBeforeEvent[] = 'share.beforepasswordcheck';
				$calledBeforeEvent[] = $event;
			});
		$calledAfterEvent = [];
		$this->eventDispatcher->addListener('share.afterpasswordcheck',
			function (GenericEvent $event) use (&$calledAfterEvent) {
				$calledAfterEvent[] = 'share.afterpasswordcheck';
				$calledAfterEvent[] = $event;
			});

		$this->hasher->method('verify')->with('password', 'passwordHash', '')->willReturn(true);

		$this->assertTrue($this->manager->checkPassword($share, 'password'));
		$this->assertEquals('share.beforepasswordcheck', $calledBeforeEvent[0]);
		$this->assertEquals('share.afterpasswordcheck', $calledAfterEvent[0]);
		$this->assertInstanceOf(GenericEvent::class, $calledBeforeEvent[1]);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterEvent[1]);
	}

	public function testCheckPasswordUpdateShare() {
		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setPassword('passwordHash');

		$this->hasher->method('verify')->with('password', 'passwordHash', '')
			->will($this->returnCallback(function ($pass, $hash, &$newHash) {
				$newHash = 'newHash';

				return true;
			}));

		$this->defaultProvider->expects($this->once())
			->method('update')
			->with($this->callback(function (\OCP\Share\IShare $share) {
				return $share->getPassword() === 'newHash';
			}));

		$this->assertTrue($this->manager->checkPassword($share, 'password'));
	}

	/**
	 */
	public function testUpdateShareCantChangeShareType() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Can\'t change share type');

		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'getShareById'
			])
			->getMock();

		$originalShare = $this->manager->newShare();
		$originalShare->setShareType(\OCP\Share::SHARE_TYPE_GROUP);

		$manager->expects($this->once())->method('canShare')->willReturn(true);
		$manager->expects($this->once())->method('getShareById')->with('foo:42')->willReturn($originalShare);

		$share = $this->manager->newShare();
		$share->setProviderId('foo')
			->setId('42')
			->setShareType(\OCP\Share::SHARE_TYPE_USER);

		$manager->updateShare($share);
	}

	/**
	 */
	public function testUpdateShareCantChangeRecipientForGroupShare() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Can only update recipient on user shares');

		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'getShareById'
			])
			->getMock();

		$originalShare = $this->manager->newShare();
		$originalShare->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setSharedWith('origGroup');

		$manager->expects($this->once())->method('canShare')->willReturn(true);
		$manager->expects($this->once())->method('getShareById')->with('foo:42')->willReturn($originalShare);

		$share = $this->manager->newShare();
		$share->setProviderId('foo')
			->setId('42')
			->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setSharedWith('newGroup');

		$manager->updateShare($share);
	}

	/**
	 */
	public function testUpdateShareCantShareWithOwner() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Can\'t share with the share owner');

		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'getShareById'
			])
			->getMock();

		$originalShare = $this->manager->newShare();
		$originalShare->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setSharedWith('sharedWith');

		$manager->expects($this->once())->method('canShare')->willReturn(true);
		$manager->expects($this->once())->method('getShareById')->with('foo:42')->willReturn($originalShare);

		$share = $this->manager->newShare();
		$share->setProviderId('foo')
			->setId('42')
			->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setSharedWith('newUser')
			->setShareOwner('newUser');

		$manager->updateShare($share);
	}

	public function testUpdateShareUser() {
		$this->userManager->expects($this->any())->method('userExists')->willReturn(true);

		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'getShareById',
				'generalChecks',
				'userCreateChecks',
				'pathCreateChecks',
			])
			->getMock();

		$originalShare = $this->manager->newShare();
		$originalShare->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setSharedWith('origUser')
			->setPermissions(1);

		$node = $this->createMock('\OCP\Files\File');
		$node->method('getId')->willReturn(100);
		$node->method('getPath')->willReturn('/newUser/files/myPath');

		$manager->expects($this->once())->method('canShare')->willReturn(true);
		$manager->expects($this->once())->method('getShareById')->with('foo:42')->willReturn($originalShare);

		$share = $this->manager->newShare();
		$attrs = $this->manager->newShare()->newAttributes();
		$attrs->setAttribute('app1', 'perm1', true);
		$share->setProviderId('foo')
			->setId('42')
			->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setSharedWith('origUser')
			->setShareOwner('newUser')
			->setSharedBy('sharer')
			->setPermissions(31)
			->setAttributes($attrs)
			->setNode($node);

		$this->defaultProvider->expects($this->once())
			->method('update')
			->with($share)
			->willReturn($share);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'post_set_expiration_date', $hookListner, 'post');
		$hookListner->expects($this->never())->method('post');

		$this->rootFolder->method('getUserFolder')->with('newUser')->will($this->returnSelf());
		$this->rootFolder->method('getRelativePath')->with('/newUser/files/myPath')->willReturn('/myPath');

		$hookListner2 = $this->getMockBuilder('Dummy')->setMethods(['post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'post_update_permissions', $hookListner2, 'post');
		$hookListner2->expects($this->once())->method('post')->with([
			'itemType' => 'file',
			'itemSource' => 100,
			'shareType' => \OCP\Share::SHARE_TYPE_USER,
			'shareWith' => 'origUser',
			'uidOwner' => 'sharer',
			'permissions' => 31,
			'path' => '/myPath',
		]);

		$calledAfterUpdate = [];
		$this->eventDispatcher->addListener('share.afterupdate',
			function (GenericEvent $event) use (&$calledAfterUpdate) {
				$calledAfterUpdate[] = 'share.afterupdate';
				$calledAfterUpdate[] = $event;
			});

		$manager->updateShare($share);
		$this->assertInstanceOf(GenericEvent::class, $calledAfterUpdate[1]);
		$this->assertEquals('share.afterupdate', $calledAfterUpdate[0]);
		$this->assertArrayHasKey('permissionupdate', $calledAfterUpdate[1]);
		$this->assertArrayHasKey('attributesupdate', $calledAfterUpdate[1]);
		$this->assertTrue($calledAfterUpdate[1]->getArgument('permissionupdate'));
		$this->assertArrayHasKey('oldpermissions', $calledAfterUpdate[1]);
		$this->assertEquals(1, $calledAfterUpdate[1]->getArgument('oldpermissions'));
		$this->assertArrayHasKey('shareobject', $calledAfterUpdate[1]);
		$this->assertInstanceOf(Share::class, $calledAfterUpdate[1]->getArgument('shareobject'));
		$this->assertArrayHasKey('path', $calledAfterUpdate[1]);
	}

	public function testUpdateShareGroup() {
		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'getShareById',
				'generalChecks',
				'groupCreateChecks',
				'pathCreateChecks',
			])
			->getMock();

		$originalShare = $this->manager->newShare();
		$originalShare->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setSharedWith('origUser')
			->setPermissions(31);

		$manager->expects($this->once())->method('canShare')->willReturn(true);
		$manager->expects($this->once())->method('getShareById')->with('foo:42')->willReturn($originalShare);

		$node = $this->createMock('\OCP\Files\File');

		$share = $this->manager->newShare();
		$share->setProviderId('foo')
			->setId('42')
			->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setSharedWith('origUser')
			->setShareOwner('owner')
			->setNode($node)
			->setPermissions(31);

		$this->defaultProvider->expects($this->once())
			->method('update')
			->with($share)
			->willReturn($share);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'post_set_expiration_date', $hookListner, 'post');
		$hookListner->expects($this->never())->method('post');

		$hookListner2 = $this->getMockBuilder('Dummy')->setMethods(['post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'post_update_permissions', $hookListner2, 'post');
		$hookListner2->expects($this->never())->method('post');

		$manager->updateShare($share);
	}

	/**
	 * @dataProvider providesDataToHashPassword
	 */
	public function testUpdateShareLink($shouldHashPassword) {
		$manager = $this->createManagerMock()
			->setMethods([
				'canShare',
				'getShareById',
				'generalChecks',
				'linkCreateChecks',
				'pathCreateChecks',
				'verifyPassword',
				'validateExpirationDate',
			])
			->getMock();

		$originalShare = $this->manager->newShare();
		$originalShare->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setPermissions(15);

		$tomorrow = new \DateTime();
		$tomorrow->setTime(0, 0, 0);
		$tomorrow->add(new \DateInterval('P1D'));

		$file = $this->createMock('OCP\Files\File', [], [], 'File');
		$file->method('getId')->willReturn(100);

		$share = $this->manager->newShare();
		$share->setProviderId('foo')
			->setId('42')
			->setName('newname')
			->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setSharedBy('owner')
			->setShareOwner('owner')
			->setPassword('password')
			->setExpirationDate($tomorrow)
			->setNode($file)
			->setPermissions(15);

		if ($shouldHashPassword === false) {
			$share->setShouldHashPassword(false);
		} else {
			$this->hasher->expects($this->once())
				->method('hash')
				->with('password')
				->willReturn('hashed');
		}

		$manager->expects($this->once())->method('canShare')->willReturn(true);
		$manager->expects($this->once())->method('getShareById')->with('foo:42')->willReturn($originalShare);
		$manager->expects($this->once())->method('validateExpirationDate')->with($share);

		$this->defaultProvider->expects($this->once())
			->method('update')
			->with($share)
			->willReturn($share);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'post_set_expiration_date', $hookListner, 'post');
		$hookListner->expects($this->once())->method('post')->with([
			'itemType' => 'file',
			'itemSource' => 100,
			'date' => $tomorrow,
			'uidOwner' => 'owner',
		]);

		$hookListner2 = $this->getMockBuilder('Dummy')->setMethods(['post'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'post_update_permissions', $hookListner2, 'post');
		$hookListner2->expects($this->never())->method('post');

		$calledAfterUpdate = [];
		$this->eventDispatcher->addListener('share.afterupdate',
			function (GenericEvent $event) use (&$calledAfterUpdate) {
				$calledAfterUpdate[] = 'share.afterupdate';
				$calledAfterUpdate[] = $event;
			});
		$manager->updateShare($share);

		$this->assertInstanceOf(GenericEvent::class, $calledAfterUpdate[1]);
		$this->assertEquals('share.afterupdate', $calledAfterUpdate[0]);
		$this->assertArrayHasKey('expirationdateupdated', $calledAfterUpdate[1]);
		$this->assertTrue($calledAfterUpdate[1]->getArgument('expirationdateupdated'));
		$this->assertArrayHasKey('oldexpirationdate', $calledAfterUpdate[1]);
		$this->assertNull($calledAfterUpdate[1]->getArgument('oldexpirationdate'));
		$this->assertArrayHasKey('sharenameupdated', $calledAfterUpdate[1]);
		$this->assertTrue($calledAfterUpdate[1]->getArgument('sharenameupdated'));
		$this->assertArrayHasKey('oldname', $calledAfterUpdate[1]);
		$this->assertNull($calledAfterUpdate[1]->getArgument('oldname'));
		$this->assertArrayHasKey('shareobject', $calledAfterUpdate[1]);
		$this->assertInstanceOf(Share::class, $calledAfterUpdate[1]->getArgument('shareobject'));
		if ($shouldHashPassword === true) {
			$this->assertEquals('hashed', $calledAfterUpdate[1]->getArgument('shareobject')->getPassword());
		} else {
			$this->assertEquals('password', $calledAfterUpdate[1]->getArgument('shareobject')->getPassword());
		}
	}

	public function testUpdateShareLinkNoPasswordChange() {
		$this->config->method('getAppValue')
			->will($this->returnValueMap([
				['core', 'shareapi_enabled', 'yes', 'yes'],
				['core', 'shareapi_exclude_groups', 'no', 'no'],
				['core', 'shareapi_allow_links', 'yes', 'yes'],
				['core', 'shareapi_allow_public_upload', 'yes', 'yes'],
			]));

		$user1 = $this->createMock(IUser::class);
		$user1->method('getUID')->willReturn('user1');

		$this->userManager->method('userExists')
			->will($this->returnValueMap([
				['user1', true],
			]));

		$userFolder = $this->createMock(Folder::class);
		$userFolder->method('getPath')->willReturn('/user1/files');

		$this->rootFolder->method('getUserFolder')->willReturn($userFolder);

		$node = $this->createMock(File::class);
		$node->method('getPath')->willReturn('/user1/files/path/to/share');
		$node->method('isShareable')->willReturn(true);
		$node->method('getPermissions')->willReturn(\OCP\Constants::PERMISSION_ALL);
		$node->method('getOwner')->willReturn($user1);

		$originalShare = $this->createMock(IShare::class);
		$originalShare->method('getId')->willReturn(10);
		$originalShare->method('getFullId')->willReturn('foo:10');
		$originalShare->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$originalShare->method('getPassword')->willReturn('123456');
		$originalShare->method('getPermissions')->willReturn(\OCP\Constants::PERMISSION_READ);
		$originalShare->method('getSharedBy')->willReturn('user1');
		$originalShare->method('getNode')->willReturn($node);

		$provider = $this->createMock(IShareProvider::class);
		$provider->method('getShareById')
			->with($this->equalTo(10), $this->anything())
			->willReturn($originalShare);

		// TODO: Mocking the factory should be done in the setup.
		$this->factory = $this->createMock(IProviderFactory::class);
		$this->factory->method('getProvider')->willReturn($provider);
		$this->factory->method('getProviderForType')->willReturn($provider);

		$this->manager = new Manager(
			$this->logger,
			$this->config,
			$this->secureRandom,
			$this->hasher,
			$this->mountManager,
			$this->groupManager,
			$this->l,
			$this->factory,
			$this->userManager,
			$this->rootFolder,
			$this->eventDispatcher,
			$this->view,
			$this->connection
		);

		$share = $this->createMock(IShare::class);
		$share->method('getId')->willReturn(10);
		$share->method('getFullId')->willReturn('foo:10');
		$share->method('getShareType')->willReturn(\OCP\Share::SHARE_TYPE_LINK);
		$share->method('getPassword')->willReturn('123456');
		$share->method('getPermissions')->willReturn(\OCP\Constants::PERMISSION_UPDATE);
		$share->method('getSharedBy')->willReturn('user1');
		$share->method('getNode')->willReturn($node);

		$share->expects($this->never())
			->method('setPassword');

		$provider->expects($this->once())
			->method('update')
			->with($share)
			->will($this->returnArgument(0));

		$this->manager->updateShare($share);
	}

	public function testMoveCallsUpdateShareForRecipient() {
		$manager = $this->createManagerMock()
			->setMethods(['updateShareForRecipient'])
			->getMock();

		$share = $this->manager->newShare();

		$manager->expects($this->once())
			->method('updateShareForRecipient')
			->with($share, 'recipient1')
			->will($this->returnArgument(0));

		$returnedShare = $manager->moveShare($share, 'recipient1');

		$this->assertSame($share, $returnedShare);
	}

	public function testUpdateShareForRecipient() {
		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setId('42')
			->setProviderId('foo');

		$share->setSharedWith('recipient');

		$this->defaultProvider->method('move')->with($share, 'recipient')->will($this->returnArgument(0));

		$this->assertNull(
			$this->manager->updateShareForRecipient($share, 'recipient')
		);
	}

	public function testGetSharedWith() {
		$user = $this->createMock(IUser::class);

		$share = $this->manager->newShare();
		$share->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setId('42')
			->setProviderId('foo');
		$this->defaultProvider->method('getSharedWith')->with($user, \OCP\Share::SHARE_TYPE_GROUP, null, -1, 0)->willReturn([$share]);

		$shares = $this->manager->getSharedWith($user, \OCP\Share::SHARE_TYPE_GROUP, null, -1, 0);
		$this->assertCount(1, $shares);
		$returnedShare = $shares[0];
		$this->assertSame($returnedShare->getId(), $share->getId());
	}

	public function testGetAllSharedWith() {
		$user = $this->createMock(IUser::class);

		$share1 = $this->manager->newShare();
		$share1->setShareType(\OCP\Share::SHARE_TYPE_USER)
			->setId('42')
			->setProviderId('foo');
		$share2 = $this->manager->newShare();
		$share2->setShareType(\OCP\Share::SHARE_TYPE_GROUP)
			->setId('43')
			->setProviderId('foo');
		$this->defaultProvider->method('getAllSharedWith')
			->with($user, null)
			->willReturn([$share1, $share2]);

		$shares = $this->manager->getAllSharedWith($user, [\OCP\Share::SHARE_TYPE_GROUP, \OCP\Share::SHARE_TYPE_USER]);
		$this->assertCount(2, $shares);
		$this->assertSame($shares, [$share1, $share2]);
	}

	/**
	 * @dataProvider strictSubsetOfAttributesDataProvider
	 *
	 * @param IAttributes $requiredAttributes
	 * @param IAttributes $currentAttributes
	 * @param boolean $expected
	 */
	public function testStrictSubsetOfAttributes($requiredAttributes, $currentAttributes, $expected) {
		$this->assertEquals(
			$expected,
			$this->invokePrivate(
				$this->manager,
				'strictSubsetOfAttributes',
				[$requiredAttributes, $currentAttributes]
			)
		);
	}

	public function strictSubsetOfAttributesDataProvider() {
		// no exception - supershare and share are equal
		$superShareAttributes = new ShareAttributes();
		$shareAttributes = new ShareAttributes();
		$data[] = [$superShareAttributes,$shareAttributes, true];

		// no exception - supershare and share are equal
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test', true);
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test', true);
		$data[] = [$superShareAttributes,$shareAttributes, true];

		// no exception - adding an attribute while supershare has none
		$superShareAttributes = new ShareAttributes();
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test', true);
		$data[] = [$superShareAttributes,$shareAttributes, true];

		// exception - disabling attribute that supershare has enabled
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test', true);
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test', false);
		$data[] = [$superShareAttributes,$shareAttributes, false];

		// exception - enabling attribute that supershare has disabled
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test', false);
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test', true);
		$data[] = [$superShareAttributes,$shareAttributes, false];

		// exception - removing attribute of that supershare has enabled
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test', true);
		$shareAttributes = new ShareAttributes();
		$data[] = [$superShareAttributes,$shareAttributes, false];

		// exception - removing attribute of that supershare has disabled
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test', false);
		$shareAttributes = new ShareAttributes();
		$data[] = [$superShareAttributes,$shareAttributes, false];

		// exception - removing one of attributes of supershare
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test1', false);
		$superShareAttributes->setAttribute('test', 'test2', false);
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test1', false);
		$data[] = [$superShareAttributes,$shareAttributes, false];

		// exception - disabling one of attributes of supershare
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test1', false);
		$superShareAttributes->setAttribute('test', 'test2', false);
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test1', false);
		$shareAttributes->setAttribute('test', 'test2', true);
		$data[] = [$superShareAttributes,$shareAttributes, false];

		// exception - swaping one of attributes of supershare
		$superShareAttributes = new ShareAttributes();
		$superShareAttributes->setAttribute('test', 'test1', false);
		$superShareAttributes->setAttribute('test', 'test01', false);
		$superShareAttributes->setAttribute('test', 'test3', true);
		$superShareAttributes->setAttribute('test', 'test4', null);
		$shareAttributes = new ShareAttributes();
		$shareAttributes->setAttribute('test', 'test1', false);
		$shareAttributes->setAttribute('test', 'test10', false);
		$superShareAttributes->setAttribute('test', 'test3', null);
		$superShareAttributes->setAttribute('test', 'test4', true);
		$data[] = [$superShareAttributes,$shareAttributes, false];

		return $data;
	}

	/**
	 * @dataProvider strictSubsetOfPermissionsDataProvider
	 *
	 * @param int $allowedPermissions
	 * @param int $newPermissions
	 * @param boolean $expected
	 */
	public function testStrictSubsetOfPermissions($allowedPermissions, $newPermissions, $expected) {
		$this->assertEquals(
			$expected,
			$this->invokePrivate(
				$this->manager,
				'strictSubsetOfPermissions',
				[$allowedPermissions, $newPermissions]
			)
		);
	}

	public function strictSubsetOfPermissionsDataProvider() {
		return [
			[\bindec('11111'), \bindec('0111'), true],
			[\bindec('01101'), \bindec('01001'), true],
			[\bindec('01111'), \bindec('11111'), false],
			[\bindec('10001'), \bindec('01111'), false],
		];
	}
}

class DummyPassword {
	public function listner($array) {
		$array['accepted'] = false;
		$array['message'] = 'password not accepted';
	}
}

class DummyFactory implements IProviderFactory {

	/** @var IShareProvider */
	private $provider;

	public function __construct(\OCP\IServerContainer $serverContainer) {
	}

	public function getProviders() {
		return [$this->provider];
	}

	/**
	 * @param IShareProvider $provider
	 */
	public function setProvider($provider) {
		$this->provider = $provider;
	}

	/**
	 * @param string $id
	 * @return IShareProvider
	 */
	public function getProvider($id) {
		return $this->provider;
	}

	/**
	 * @param int $shareType
	 * @return IShareProvider
	 */
	public function getProviderForType($shareType) {
		return $this->provider;
	}
}
