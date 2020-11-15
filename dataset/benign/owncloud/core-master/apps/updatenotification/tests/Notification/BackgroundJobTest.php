<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OCA\UpdateNotification\Tests\Notification;

use OCA\UpdateNotification\Notification\BackgroundJob;
use OCP\App\IAppManager;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Notification\IManager;
use Test\TestCase;

class BackgroundJobTest extends TestCase {

	/** @var IConfig|\PHPUnit\Framework\MockObject\MockObject */
	protected $config;
	/** @var IManager|\PHPUnit\Framework\MockObject\MockObject */
	protected $notificationManager;
	/** @var IGroupManager|\PHPUnit\Framework\MockObject\MockObject */
	protected $groupManager;
	/** @var IAppManager|\PHPUnit\Framework\MockObject\MockObject */
	protected $appManager;
	/** @var IClientService|\PHPUnit\Framework\MockObject\MockObject */
	protected $client;
	/** @var IURLGenerator|\PHPUnit\Framework\MockObject\MockObject */
	protected $urlGenerator;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock('OCP\IConfig');
		$this->notificationManager = $this->createMock('OCP\Notification\IManager');
		$this->groupManager = $this->createMock('OCP\IGroupManager');
		$this->appManager = $this->createMock('OCP\App\IAppManager');
		$this->client = $this->createMock('OCP\Http\Client\IClientService');
		$this->urlGenerator = $this->createMock('OCP\IURLGenerator');
	}

	/**
	 * @param array $methods
	 * @return BackgroundJob|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected function getJob(array $methods = []) {
		if (empty($methods)) {
			return new BackgroundJob(
				$this->config,
				$this->notificationManager,
				$this->groupManager,
				$this->appManager,
				$this->client,
				$this->urlGenerator
			);
		}
		{
			return $this->getMockBuilder('OCA\UpdateNotification\Notification\BackgroundJob')
				->setConstructorArgs([
					$this->config,
					$this->notificationManager,
					$this->groupManager,
					$this->appManager,
					$this->client,
					$this->urlGenerator,
				])
				->setMethods($methods)
				->getMock();
		}
	}

	public function testRun() {
		$job = $this->getJob(['checkCoreUpdate']);

		$job->expects($this->once())
			->method('checkCoreUpdate');

		$this->invokePrivate($job, 'run', [null]);
	}

	public function dataCheckCoreUpdate() {
		return [
			['daily', null, null],
			['git', null, null],
			['beta', false, null],
			['beta', [
				'version' => '9.2.0',
			], '9.2.0'],
			['stable', false, null],
			['stable', [
				'version' => '9.2.0',
			], '9.2.0'],
			['production', false, null],
			['production', [
				'version' => '9.2.0',
			], '9.2.0'],
		];
	}

	/**
	 * @dataProvider dataCheckCoreUpdate
	 *
	 * @param string $channel
	 * @param mixed $versionCheck
	 * @param null|string $notification
	 */
	public function testCheckCoreUpdate($channel, $versionCheck, $notification) {
		$job = $this->getJob([
			'getChannel',
			'createVersionCheck',
			'createNotifications',
		]);

		$job->expects($this->once())
			->method('getChannel')
			->willReturn($channel);

		if ($versionCheck === null) {
			$job->expects($this->never())
				->method('createVersionCheck');
		} else {
			$check = $this->getMockBuilder('OC\Updater\VersionCheck')
				->disableOriginalConstructor()
				->getMock();
			$check->expects($this->once())
				->method('check')
				->willReturn($versionCheck);

			$job->expects($this->once())
				->method('createVersionCheck')
				->willReturn($check);
		}

		if ($notification === null) {
			$this->urlGenerator->expects($this->never())
				->method('linkToRouteAbsolute');

			$job->expects($this->never())
				->method('createNotifications');
		} else {
			$this->urlGenerator->expects($this->once())
				->method('linkToRouteAbsolute')
				->with('settings.SettingsPage.getAdmin')
				->willReturn('admin-url');

			$job->expects($this->once())
				->method('createNotifications')
				->willReturn('core', $notification, 'admin-url#updater');
		}

		$this->invokePrivate($job, 'checkCoreUpdate');
	}

	public function dataCreateNotifications() {
		return [
			['app1', '1.0.0', 'link1', '1.0.0', false, false, null, null],
			['app2', '1.0.1', 'link2', '1.0.0', '1.0.0', true, ['user1'], [['user1']]],
			['app3', '1.0.1', 'link3', false, false, true, ['user2', 'user3'], [['user2'], ['user3']]],
		];
	}

	/**
	 * @dataProvider dataCreateNotifications
	 *
	 * @param string $app
	 * @param string $version
	 * @param string $url
	 * @param string|false $lastNotification
	 * @param string|false $callDelete
	 * @param bool $createNotification
	 * @param string[]|null $users
	 * @param array|null $userNotifications
	 */
	public function testCreateNotifications($app, $version, $url, $lastNotification, $callDelete, $createNotification, $users, $userNotifications) {
		$job = $this->getJob([
			'deleteOutdatedNotifications',
			'getUsersToNotify',
		]);

		$this->config->expects($this->once())
			->method('getAppValue')
			->with('updatenotification', $app, false)
			->willReturn($lastNotification);

		if ($lastNotification !== $version) {
			$this->config->expects($this->once())
				->method('setAppValue')
				->with('updatenotification', $app, $version);
		}

		if ($callDelete === false) {
			$job->expects($this->never())
				->method('deleteOutdatedNotifications');
		} else {
			$job->expects($this->once())
				->method('deleteOutdatedNotifications')
				->with($app, $callDelete);
		}

		if ($users === null) {
			$job->expects($this->never())
				->method('getUsersToNotify');
		} else {
			$job->expects($this->once())
				->method('getUsersToNotify')
				->willReturn($users);
		}

		if ($createNotification) {
			$notification = $this->createMock('OCP\Notification\INotification');
			$notification->expects($this->once())
				->method('setApp')
				->with('updatenotification')
				->willReturnSelf();
			$notification->expects($this->once())
				->method('setDateTime')
				->willReturnSelf();
			$notification->expects($this->once())
				->method('setObject')
				->with($app, $version)
				->willReturnSelf();
			$notification->expects($this->once())
				->method('setSubject')
				->with('update_available')
				->willReturnSelf();
			$notification->expects($this->once())
				->method('setLink')
				->with($url)
				->willReturnSelf();

			if ($userNotifications !== null) {
				$mockedMethod = $notification->expects($this->exactly(\sizeof($userNotifications)))
					->method('setUser')
					->willReturnSelf();
				\call_user_func_array([$mockedMethod, 'withConsecutive'], $userNotifications);

				$this->notificationManager->expects($this->exactly(\sizeof($userNotifications)))
					->method('notify')
					->willReturn($notification);
			}

			$this->notificationManager->expects($this->once())
				->method('createNotification')
				->willReturn($notification);
		} else {
			$this->notificationManager->expects($this->never())
				->method('createNotification');
		}

		$this->invokePrivate($job, 'createNotifications', [$app, $version, $url]);
	}

	public function dataGetUsersToNotify() {
		return [
			[['g1', 'g2'], ['g1' => null, 'g2' => ['u1', 'u2']], ['u1', 'u2']],
			[['g3', 'g4'], ['g3' => ['u1', 'u2'], 'g4' => ['u2', 'u3']], ['u1', 'u2', 'u3']],
		];
	}

	/**
	 * @dataProvider dataGetUsersToNotify
	 * @param string[] $groups
	 * @param array $groupUsers
	 * @param string[] $expected
	 */
	public function testGetUsersToNotify($groups, array $groupUsers, array $expected) {
		$job = $this->getJob();

		$this->config->expects($this->once())
			->method('getAppValue')
			->with('updatenotification', 'notify_groups', '["admin"]')
			->willReturn(\json_encode($groups));

		$groupMap = [];
		foreach ($groupUsers as $gid => $uids) {
			if ($uids === null) {
				$group = null;
			} else {
				$group = $this->getGroup($gid);
				$group->expects($this->any())
					->method('getUsers')
					->willReturn($this->getUsers($uids));
			}
			$groupMap[] = [$gid, $group];
		}
		$this->groupManager->expects($this->exactly(\sizeof($groups)))
			->method('get')
			->willReturnMap($groupMap);

		$result = $this->invokePrivate($job, 'getUsersToNotify');
		$this->assertEquals($expected, $result);

		// Test caching
		$result = $this->invokePrivate($job, 'getUsersToNotify');
		$this->assertEquals($expected, $result);
	}

	public function dataDeleteOutdatedNotifications() {
		return [
			['app1', '1.1.0'],
			['app2', '1.2.0'],
		];
	}

	/**
	 * @dataProvider dataDeleteOutdatedNotifications
	 * @param string $app
	 * @param string $version
	 */
	public function testDeleteOutdatedNotifications($app, $version) {
		$notification = $this->createMock('OCP\Notification\INotification');
		$notification->expects($this->once())
			->method('setApp')
			->with('updatenotification')
			->willReturnSelf();
		$notification->expects($this->once())
			->method('setObject')
			->with($app, $version)
			->willReturnSelf();

		$this->notificationManager->expects($this->once())
			->method('createNotification')
			->willReturn($notification);
		$this->notificationManager->expects($this->once())
			->method('markProcessed')
			->with($notification);

		$job = $this->getJob();
		$this->invokePrivate($job, 'deleteOutdatedNotifications', [$app, $version]);
	}

	/**
	 * @param string[] $userIds
	 * @return IUser[]|\PHPUnit\Framework\MockObject\MockObject[]
	 */
	protected function getUsers(array $userIds) {
		$users = [];
		foreach ($userIds as $uid) {
			$user = $this->createMock('OCP\IUser');
			$user->expects($this->any())
				->method('getUID')
				->willReturn($uid);
			$users[] = $user;
		}
		return $users;
	}

	/**
	 * @param $gid
	 * @return \OCP\IGroup|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected function getGroup($gid) {
		$group = $this->createMock('OCP\IGroup');
		$group->expects($this->any())
			->method('getGID')
			->willReturn($gid);
		return $group;
	}
}
