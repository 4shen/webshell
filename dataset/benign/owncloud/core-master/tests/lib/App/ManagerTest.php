<?php

/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\App;

use OC\App\AppManager;
use OC\Group\Group;
use OCP\App\IAppManager;
use OCP\IAppConfig;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserSession;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Test\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Class Manager
 *
 * @package Test\App
 * @group DB
 */
class ManagerTest extends TestCase {

	/** @var IUserSession | \PHPUnit\Framework\MockObject\MockObject */
	protected $userSession;
	/** @var IGroupManager | \PHPUnit\Framework\MockObject\MockObject */
	protected $groupManager;
	/** @var IAppConfig */
	protected $appConfig;
	/** @var ICache | \PHPUnit\Framework\MockObject\MockObject */
	protected $cache;
	/** @var ICacheFactory | \PHPUnit\Framework\MockObject\MockObject */
	protected $cacheFactory;
	/** @var IAppManager */
	protected $manager;
	/** @var  EventDispatcherInterface | \PHPUnit\Framework\MockObject\MockObject */
	protected $eventDispatcher;
	/** @var IConfig | \PHPUnit\Framework\MockObject\MockObject */
	private $config;

	/**
	 * @return IAppConfig | \PHPUnit\Framework\MockObject\MockObject
	 */
	protected function getAppConfig() {
		$appConfig = [];
		$config = $this->getMockBuilder(IAppConfig::class)
			->disableOriginalConstructor()
			->getMock();

		$config->expects($this->any())
			->method('getValue')
			->will($this->returnCallback(function ($app, $key, $default) use (&$appConfig) {
				return (isset($appConfig[$app]) and isset($appConfig[$app][$key])) ? $appConfig[$app][$key] : $default;
			}));
		$config->expects($this->any())
			->method('setValue')
			->will($this->returnCallback(function ($app, $key, $value) use (&$appConfig) {
				if (!isset($appConfig[$app])) {
					$appConfig[$app] = [];
				}
				$appConfig[$app][$key] = $value;
			}));
		$config->expects($this->any())
			->method('getValues')
			->will($this->returnCallback(function ($app, $key) use (&$appConfig) {
				if ($app) {
					return $appConfig[$app];
				} else {
					$values = [];
					foreach ($appConfig as $app => $appData) {
						if (isset($appData[$key])) {
							$values[$app] = $appData[$key];
						}
					}
					return $values;
				}
			}));

		return $config;
	}

	protected function setUp(): void {
		parent::setUp();

		$this->userSession = $this->createMock(IUserSession::class);
		$this->config = $this->createMock(IConfig::class);
		$this->groupManager = $this->createMock(IGroupManager::class);
		$this->appConfig = $this->getAppConfig();
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->cache = $this->createMock(ICache::class);
		$this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
		$this->cacheFactory->expects($this->any())
			->method('create')
			->with('settings')
			->willReturn($this->cache);
		$this->manager = new AppManager($this->userSession, $this->appConfig,
			$this->groupManager, $this->cacheFactory, $this->eventDispatcher,
			$this->config);
	}

	protected function expectClearCache() {
		$this->cache->expects($this->once())
			->method('clear')
			->with('listApps');
	}

	public function testEnableApp() {
		$this->expectClearCache();
		// making sure "files_trashbin" is disabled
		if ($this->manager->isEnabledForUser('files_trashbin')) {
			$this->manager->disableApp('files_trashbin');
		}
		$this->manager->enableApp('files_trashbin');
		$this->assertEquals('yes', $this->appConfig->getValue('files_trashbin', 'enabled', 'no'));
	}

	/**
	 */
	public function testEnableSecondAppTheme() {
		$this->expectException(\OCP\App\AppManagerException::class);

		$appThemeName = 'theme-one';
		$manager = $this->getMockBuilder(AppManager::class)
			->setMethods(['isTheme', 'getAppInfo', 'getAppPath'])
			->setConstructorArgs([$this->userSession, $this->appConfig,
				$this->groupManager, $this->cacheFactory, $this->eventDispatcher,
				$this->config])
			->getMock();

		$manager->expects($this->once())
			->method('getAppInfo')
			->willReturn(['types'=>['theme']]);

		$manager->expects($this->once())
			->method('isTheme')
			->willReturn(true);

		$manager->expects($this->once())
			->method('getAppPath')
			->with($appThemeName)
			->willReturn('path');

		$manager->enableApp($appThemeName);
	}

	public function testEnableTheSameThemeTwice() {
		$appThemeName = 'theme-one';
		$manager = $this->getMockBuilder(AppManager::class)
			->setMethods(['isTheme', 'getAppInfo', 'getAppPath', 'getInstalledApps'])
			->setConstructorArgs([$this->userSession, $this->appConfig,
				$this->groupManager, $this->cacheFactory, $this->eventDispatcher,
				$this->config])
			->getMock();

		$manager->expects($this->once())
			->method('getInstalledApps')
			->willReturn([$appThemeName]);
		$manager->expects($this->once())
			->method('getAppInfo')
			->willReturn(['types'=>['theme']]);

		$manager->expects($this->any())
			->method('isTheme')
			->will(
				$this->returnCallback(
					function ($appId) use ($appThemeName) {
						return $appId === $appThemeName;
					}
				)
			);

		$manager->expects($this->once())
			->method('getAppPath')
			->with($appThemeName)
			->willReturn('path');

		$manager->enableApp($appThemeName);
	}

	public function testDisableApp() {
		$this->expectClearCache();
		$this->manager->disableApp('files_trashbin');
		$this->assertEquals('no', $this->appConfig->getValue('files_trashbin', 'enabled', 'no'));
	}

	/**
	 */
	public function testNotEnableIfNotInstalled() {
		$this->expectException(\Exception::class);

		$this->manager->enableApp('some_random_name_which_i_hope_is_not_an_app');
		$this->assertEquals('no', $this->appConfig->getValue(
			'some_random_name_which_i_hope_is_not_an_app', 'enabled', 'no'
		));
	}

	public function testEnableAppForGroups() {
		$groups = [
			new Group('group1', [], null, $this->eventDispatcher),
			new Group('group2', [], null, $this->eventDispatcher)
		];
		$this->expectClearCache();
		$this->manager->enableAppForGroups('test', $groups);
		$this->assertEquals('["group1","group2"]', $this->appConfig->getValue('test', 'enabled', 'no'));
	}

	public function dataEnableAppForGroupsAllowedTypes() {
		return [
			[[]],
			[[
				'types' => [],
			]],
			[[
				'types' => ['nickvergessen'],
			]],
		];
	}

	/**
	 * @dataProvider dataEnableAppForGroupsAllowedTypes
	 *
	 * @param array $appInfo
	 */
	public function testEnableAppForGroupsAllowedTypes(array $appInfo) {
		$groups = [
			new Group('group1', [], null, $this->eventDispatcher),
			new Group('group2', [], null, $this->eventDispatcher)
		];
		$this->expectClearCache();

		/** @var AppManager|\PHPUnit\Framework\MockObject\MockObject $manager */
		$manager = $this->getMockBuilder('OC\App\AppManager')
			->setConstructorArgs([
				$this->userSession, $this->appConfig, $this->groupManager,
				$this->cacheFactory, $this->eventDispatcher, $this->config
			])
			->setMethods([
				'getAppInfo'
			])
			->getMock();

		$manager->expects($this->once())
			->method('getAppInfo')
			->with('test')
			->willReturn($appInfo);

		$manager->enableAppForGroups('test', $groups);
		$this->assertEquals('["group1","group2"]', $this->appConfig->getValue('test', 'enabled', 'no'));
	}

	public function dataEnableAppForGroupsForbiddenTypes() {
		return [
			['filesystem'],
			['prelogin'],
			['authentication'],
			['logging'],
			['prevent_group_restriction'],
		];
	}

	/**
	 * @dataProvider dataEnableAppForGroupsForbiddenTypes
	 *
	 * @param string $type
	 *
	 */
	public function testEnableAppForGroupsForbiddenTypes($type) {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('test can\'t be enabled for groups.');

		$groups = [
			new Group('group1', [], null, $this->eventDispatcher),
			new Group('group2', [], null, $this->eventDispatcher)
		];

		/** @var AppManager|\PHPUnit\Framework\MockObject\MockObject $manager */
		$manager = $this->getMockBuilder('OC\App\AppManager')
			->setConstructorArgs([
				$this->userSession, $this->appConfig, $this->groupManager,
				$this->cacheFactory, $this->eventDispatcher, $this->config
			])
			->setMethods([
				'getAppInfo'
			])
			->getMock();

		$manager->expects($this->once())
			->method('getAppInfo')
			->with('test')
			->willReturn([
				'types' => [$type],
			]);

		$manager->enableAppForGroups('test', $groups);
	}

	public function testIsInstalledEnabled() {
		$this->appConfig->setValue('test', 'enabled', 'yes');
		$this->assertTrue($this->manager->isInstalled('test'));
	}

	public function testIsInstalledDisabled() {
		$this->appConfig->setValue('test', 'enabled', 'no');
		$this->assertFalse($this->manager->isInstalled('test'));
	}

	public function testIsInstalledEnabledForGroups() {
		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertTrue($this->manager->isInstalled('test'));
	}

	public function testIsEnabledForUserEnabled() {
		$this->appConfig->setValue('test', 'enabled', 'yes');
		$user = $this->createMock(IUser::class);
		$this->assertTrue($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserDisabled() {
		$this->appConfig->setValue('test', 'enabled', 'no');
		$user = $this->createMock(IUser::class);
		$this->assertFalse($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserEnabledForGroup() {
		$user = $this->createMock(IUser::class);
		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($user)
			->will($this->returnValue(['foo', 'bar']));

		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertTrue($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserDisabledForGroup() {
		$user = $this->createMock(IUser::class);
		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($user)
			->will($this->returnValue(['bar']));

		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertFalse($this->manager->isEnabledForUser('test', $user));
	}

	public function testIsEnabledForUserLoggedOut() {
		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertFalse($this->manager->IsEnabledForUser('test'));
	}

	public function testIsEnabledForUserLoggedIn() {
		$user = $this->createMock(IUser::class);

		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($user)
			->will($this->returnValue(['foo', 'bar']));

		$this->appConfig->setValue('test', 'enabled', '["foo"]');
		$this->assertTrue($this->manager->isEnabledForUser('test'));
	}

	public function testGetInstalledApps() {
		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'no');
		$this->appConfig->setValue('test3', 'enabled', '["foo"]');
		$this->assertEquals([
			'dav',
			'federatedfilesharing',
			'files',
			'files_external',
			'test1',
			'test3'
		], $this->manager->getInstalledApps());
	}

	public function testGetAppsForUser() {
		$user = $this->createMock(IUser::class);
		$this->groupManager->expects($this->any())
			->method('getUserGroupIds')
			->with($user)
			->will($this->returnValue(['foo', 'bar']));

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'no');
		$this->appConfig->setValue('test3', 'enabled', '["foo"]');
		$this->appConfig->setValue('test4', 'enabled', '["asd"]');
		$this->assertEquals([
			'dav',
			'federatedfilesharing',
			'files',
			'files_external',
			'test1',
			'test3'
		], $this->manager->getEnabledAppsForUser($user));
	}

	public function testGetAppsNeedingUpgrade() {
		$this->manager = $this->getMockBuilder('\OC\App\AppManager')
			->setConstructorArgs([$this->userSession, $this->appConfig,
				$this->groupManager, $this->cacheFactory, $this->eventDispatcher, $this->config])
			->setMethods(['getAppInfo'])
			->getMock();

		$appInfos = [
			'dav' => ['id' => 'dav'],
			'files' => ['id' => 'files'],
			'files_external' => ['id' => 'files_external'],
			'federatedfilesharing' => ['id' => 'federatedfilesharing'],
			'test1' => ['id' => 'test1', 'version' => '1.0.1', 'requiremax' => '9.0.0'],
			'test2' => ['id' => 'test2', 'version' => '1.0.0', 'requiremin' => '8.2.0'],
			'test3' => ['id' => 'test3', 'version' => '1.2.4', 'requiremin' => '9.0.0'],
			'test4' => ['id' => 'test4', 'version' => '3.0.0', 'requiremin' => '8.1.0'],
			'testnoversion' => ['id' => 'testnoversion', 'requiremin' => '8.2.0'],
		];

		$this->manager->expects($this->any())
			->method('getAppInfo')
			->will($this->returnCallback(
				function ($appId) use ($appInfos) {
					return $appInfos[$appId];
				}
		));

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test1', 'installed_version', '1.0.0');
		$this->appConfig->setValue('test2', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'installed_version', '1.0.0');
		$this->appConfig->setValue('test3', 'enabled', 'yes');
		$this->appConfig->setValue('test3', 'installed_version', '1.0.0');
		$this->appConfig->setValue('test4', 'enabled', 'yes');
		$this->appConfig->setValue('test4', 'installed_version', '2.4.0');

		$apps = $this->manager->getAppsNeedingUpgrade('8.2.0');

		$this->assertCount(2, $apps);
		$this->assertEquals('test1', $apps[0]['id']);
		$this->assertEquals('test4', $apps[1]['id']);
	}

	public function testGetIncompatibleApps() {
		$this->manager = $this->getMockBuilder('\OC\App\AppManager')
			->setConstructorArgs([$this->userSession, $this->appConfig,
				$this->groupManager, $this->cacheFactory, $this->eventDispatcher, $this->config])
			->setMethods(['getAppInfo'])
			->getMock();

		$appInfos = [
			'dav' => ['id' => 'dav'],
			'files' => ['id' => 'files'],
			'files_external' => ['id' => 'files_external'],
			'federatedfilesharing' => ['id' => 'federatedfilesharing'],
			'test1' => ['id' => 'test1', 'version' => '1.0.1', 'requiremax' => '8.0.0'],
			'test2' => ['id' => 'test2', 'version' => '1.0.0', 'requiremin' => '8.2.0'],
			'test3' => ['id' => 'test3', 'version' => '1.2.4', 'requiremin' => '9.0.0'],
			'testnoversion' => ['id' => 'testnoversion', 'requiremin' => '8.2.0'],
		];

		$this->manager->expects($this->any())
			->method('getAppInfo')
			->will($this->returnCallback(
				function ($appId) use ($appInfos) {
					return $appInfos[$appId];
				}
		));

		$this->appConfig->setValue('test1', 'enabled', 'yes');
		$this->appConfig->setValue('test2', 'enabled', 'yes');
		$this->appConfig->setValue('test3', 'enabled', 'yes');

		$apps = $this->manager->getIncompatibleApps('8.2.0');

		$this->assertCount(2, $apps);
		$this->assertEquals('test1', $apps[0]['id']);
		$this->assertEquals('test3', $apps[1]['id']);
	}

	/**
	 * @dataProvider providesDataForCanInstall
	 * @param bool $canInstall
	 * @param string $opsMode
	 */
	public function testCanInstall($canInstall, $opsMode) {
		$this->config->expects($this->once())->method('getSystemValue')->willReturn($opsMode);
		$this->assertEquals($canInstall, $this->manager->canInstall());
	}

	public function providesDataForCanInstall() {
		return [
			[true, 'single-instance'],
			[false, 'clustered-instance'],
		];
	}

	/**
	 * @dataProvider appInfoDataProvider
	 *
	 * @param string $firstDirVersion
	 * @param string $secondDirVersion
	 * @param bool $isFirstWinner
	 */
	public function testTheMostRecentAppIsFound($firstDirVersion, $secondDirVersion, $isFirstWinner) {
		$appId = 'bogusapp';
		$appsParentDir = vfsStream::setup();
		$firstAppDir = vfsStream::newDirectory('apps')->at($appsParentDir);
		$appDir1 = vfsStream::newDirectory($appId)->at($firstAppDir);
		$secondAppDir = vfsStream::newDirectory('apps2')->at($appsParentDir);
		$appDir2 = vfsStream::newDirectory($appId)->at($secondAppDir);

		$appManager = $this->getMockBuilder(AppManager::class)
			->setMethods(['getAppVersionByPath', 'getAppRoots'])
			->disableOriginalConstructor()
			->getMock();

		$appManager->expects($this->any())
			->method('getAppRoots')
			->willReturn([
				[
					'path' => $firstAppDir->url(),
					'url' => $firstAppDir->url(),
				],
				[
					'path' => $secondAppDir->url(),
					'url' => $secondAppDir->url(),
				]
			]);

		$appManager->expects($this->any())
			->method('getAppVersionByPath')
			->will($this->onConsecutiveCalls($firstDirVersion, $secondDirVersion));

		$expected = $isFirstWinner ? $appDir1->url() : $appDir2->url();
		$appPath = $appManager->getAppPath($appId);
		$this->assertEquals($expected, $appPath);
	}

	public function appInfoDataProvider() {
		return [
			[ '1.2.3', '3.2.4', false ],
			[ '2.2.3', '2.2.1', true ]
		];
	}

	public function testPathIsNotCachedForNotFoundApp() {
		$appId = 'notexistingapp';

		$appManager = $this->getMockBuilder(AppManager::class)
			->setMethods(['getAppVersionByPath', 'getAppRoots', 'saveAppPath'])
			->disableOriginalConstructor()
			->getMock();

		$appManager->expects($this->any())
			->method('getAppRoots')
			->willReturn([]);

		$appManager->expects($this->never())
			->method('saveAppPath');

		$appPath = $appManager->getAppPath($appId);
		$this->assertFalse($appPath);
	}

	/**
	 * @dataProvider appAboveWebRootDataProvider
	 *
	 * @param string $ocWebRoot
	 * @param string[] $appData
	 * @param string $expectedAppWebPath
	 */
	public function testAppWebRootAboveOcWebRoot($ocWebRoot, $appData,
		$expectedAppWebPath) {
		$appId = 'notexistingapp';

		$appManager = $this->getMockBuilder(AppManager::class)
			->setMethods(['findAppInDirectories', 'getOcWebRoot'])
			->disableOriginalConstructor()
			->getMock();

		$appManager->expects($this->any())
			->method('getOcWebRoot')
			->willReturn($ocWebRoot);

		$appManager->expects($this->any())
			->method('findAppInDirectories')
			->with($appId)
			->willReturn($appData);

		$appWebPath = $appManager->getAppWebPath($appId);
		$this->assertEquals($expectedAppWebPath, $appWebPath);
	}

	public function appAboveWebRootDataProvider() {
		return [
			[
				'/some/host/path',
				[
					'path' => '/not/essential',
					'url' => '../../relative',
				],
				'/some/relative'
			],
			[
				'/some/host/path',
				[
					'path' => '/not/essential',
					'url' => '../relative',
				],
				'/some/host/relative'
			],
			[
				'/some/hostPath',
				[
					'path' => '/not/essential',
					'url' => '../relative',
				],
				'/some/relative'
			],
			[
				'/someHostPath',
				[
					'path' => '/not/essential',
					'url' => '../relative',
				],
				'/relative'
			],
		];
	}
}
