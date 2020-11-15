<?php

/**
 * Copyright (c) 2014 Thomas Müller <deepdiver@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
*/

namespace Test\Activity\NullSession;

use OC\Activity\Event;
use OCP\Activity\IConsumer;
use OCP\Activity\IEvent;
use OCP\Activity\IExtension;
use OCP\IConfig;
use OCP\IRequest;
use Test\TestCase;

class ManagerTest extends TestCase {

	/** @var \OC\Activity\Manager */
	private $activityManager;
	/** @var \OCP\IRequest|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;
	/** @var \OCP\IConfig|\PHPUnit\Framework\MockObject\MockObject */
	protected $config;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->getMockBuilder(IRequest::class)
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->getMockBuilder(IConfig::class)
			->disableOriginalConstructor()
			->getMock();

		$this->activityManager = new \OC\Activity\Manager(
			$this->request,
			null,
			$this->config
		);

		$this->assertSame([], $this->invokePrivate($this->activityManager, 'getConsumers'));
		$this->assertSame([], $this->invokePrivate($this->activityManager, 'getExtensions'));

		$this->activityManager->registerConsumer(function () {
			return new NoOpConsumer();
		});
		$this->activityManager->registerExtension(function () {
			return new NoOpExtension();
		});
		$this->activityManager->registerExtension(function () {
			return new SimpleExtension();
		});

		$this->assertNotEmpty($this->invokePrivate($this->activityManager, 'getConsumers'));
		$this->assertNotEmpty($this->invokePrivate($this->activityManager, 'getConsumers'));
		$this->assertNotEmpty($this->invokePrivate($this->activityManager, 'getExtensions'));
		$this->assertNotEmpty($this->invokePrivate($this->activityManager, 'getExtensions'));
	}

	public function testGetConsumers() {
		$consumers = $this->invokePrivate($this->activityManager, 'getConsumers');

		$this->assertNotEmpty($consumers);
	}

	/**
	 */
	public function testGetConsumersInvalidConsumer() {
		$this->expectException(\InvalidArgumentException::class);

		$this->activityManager->registerConsumer(function () {
			return new \stdClass();
		});

		$this->invokePrivate($this->activityManager, 'getConsumers');
	}

	public function testGetExtensions() {
		$extensions = $this->invokePrivate($this->activityManager, 'getExtensions');

		$this->assertNotEmpty($extensions);
	}

	/**
	 */
	public function testGetExtensionsInvalidExtension() {
		$this->expectException(\InvalidArgumentException::class);

		$this->activityManager->registerExtension(function () {
			return new \stdClass();
		});

		$this->invokePrivate($this->activityManager, 'getExtensions');
	}

	public function testNotificationTypes() {
		$result = $this->activityManager->getNotificationTypes('en');
		$this->assertIsArray($result);
		$this->assertCount(2, $result);
	}

	public function testDefaultTypes() {
		$result = $this->activityManager->getDefaultTypes('stream');
		$this->assertIsArray($result);
		$this->assertCount(1, $result);

		$result = $this->activityManager->getDefaultTypes('email');
		$this->assertIsArray($result);
		$this->assertCount(0, $result);
	}

	public function testTypeIcon() {
		$result = $this->activityManager->getTypeIcon('NT1');
		$this->assertEquals('icon-nt-one', $result);

		$result = $this->activityManager->getTypeIcon('NT2');
		$this->assertEquals('', $result);
	}

	public function testTranslate() {
		$result = $this->activityManager->translate('APP0', '', [], false, false, 'en');
		$this->assertEquals('Stupid translation', $result);

		$result = $this->activityManager->translate('APP1', '', [], false, false, 'en');
		$this->assertFalse($result);
	}

	public function testGetSpecialParameterList() {
		$result = $this->activityManager->getSpecialParameterList('APP0', '');
		$this->assertEquals([0 => 'file', 1 => 'username'], $result);

		$result = $this->activityManager->getSpecialParameterList('APP1', '');
		$this->assertFalse($result);
	}

	public function testGroupParameter() {
		$result = $this->activityManager->getGroupParameter([]);
		$this->assertEquals(5, $result);
	}

	public function testNavigation() {
		$result = $this->activityManager->getNavigation();
		$this->assertCount(4, $result['apps']);
		$this->assertCount(2, $result['top']);
	}

	public function testIsFilterValid() {
		$result = $this->activityManager->isFilterValid('fv01');
		$this->assertTrue($result);

		$result = $this->activityManager->isFilterValid('InvalidFilter');
		$this->assertFalse($result);
	}

	public function testFilterNotificationTypes() {
		$result = $this->activityManager->filterNotificationTypes(['NT0', 'NT1', 'NT2', 'NT3'], 'fv01');
		$this->assertIsArray($result);
		$this->assertCount(3, $result);

		$result = $this->activityManager->filterNotificationTypes(['NT0', 'NT1', 'NT2', 'NT3'], 'InvalidFilter');
		$this->assertIsArray($result);
		$this->assertCount(4, $result);
	}

	public function testQueryForFilter() {
		// Register twice, to test the created sql part
		$this->activityManager->registerExtension(function () {
			return new SimpleExtension();
		});

		$result = $this->activityManager->getQueryForFilter('fv01');
		$this->assertEquals(
			[
				' and ((`app` = ? and `message` like ?) or (`app` = ? and `message` like ?))',
				['mail', 'ownCloud%', 'mail', 'ownCloud%']
			], $result
		);

		$result = $this->activityManager->getQueryForFilter('InvalidFilter');
		$this->assertEquals([null, null], $result);
	}

	public function getUserFromTokenThrowInvalidTokenData() {
		return [
			[null, []],
			['', []],
			['12345678901234567890123456789', []],
			['1234567890123456789012345678901', []],
			['123456789012345678901234567890', []],
			['123456789012345678901234567890', ['user1', 'user2']],
		];
	}

	/**
	 * @dataProvider getUserFromTokenThrowInvalidTokenData
	 *
	 * @param string $token
	 * @param array $users
	 */
	public function testGetUserFromTokenThrowInvalidToken($token, $users) {
		$this->expectException(\UnexpectedValueException::class);

		$this->mockRSSToken($token, $token, $users);
		self::invokePrivate($this->activityManager, 'getUserFromToken');
	}

	public function getUserFromTokenData() {
		return [
			['123456789012345678901234567890', 'user1'],
		];
	}

	/**
	 * @dataProvider getUserFromTokenData
	 *
	 * @param string $token
	 * @param string $expected
	 */
	public function testGetUserFromToken($token, $expected) {
		$this->mockRSSToken($token, '123456789012345678901234567890', ['user1']);

		$this->assertEquals($expected, $this->activityManager->getCurrentUserId());
	}

	protected function mockRSSToken($requestToken, $userToken, $users) {
		if ($requestToken !== null) {
			$this->request->expects($this->any())
				->method('getParam')
				->with('token', '')
				->willReturn($requestToken);
		}

		$this->config->expects($this->any())
			->method('getUsersForUserValue')
			->with('activity', 'rsstoken', $userToken)
			->willReturn($users);
	}

	/**
	 */
	public function testPublishExceptionNoApp() {
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('App not set');
		$this->expectExceptionCode(10);

		$event = new Event();
		$this->activityManager->publish($event);
	}

	/**
	 */
	public function testPublishExceptionNoType() {
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Type not set');
		$this->expectExceptionCode(11);

		$event = new Event();
		$event->setApp('test');
		$this->activityManager->publish($event);
	}

	/**
	 */
	public function testPublishExceptionNoAffectedUser() {
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Affected user not set');
		$this->expectExceptionCode(12);

		$event = new Event();
		$event->setApp('test')
			->setType('test_type');
		$this->activityManager->publish($event);
	}

	/**
	 */
	public function testPublishExceptionNoSubject() {
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Subject not set');
		$this->expectExceptionCode(13);

		$event = new Event();
		$event->setApp('test')
			->setType('test_type')
			->setAffectedUser('test_affected');
		$this->activityManager->publish($event);
	}

	public function testPublish() {
		$author = null;
		$event = new Event();
		$event->setApp('test')
			->setType('test_type')
			->setSubject('test_subject', [])
			->setAffectedUser('test_affected');

		$consumer = $this->getMockBuilder('OCP\Activity\IConsumer')
			->disableOriginalConstructor()
			->getMock();
		$consumer->expects($this->once())
			->method('receive')
			->with($event)
			->willReturnCallback(function (IEvent $event) use ($author) {
				$this->assertLessThanOrEqual(\time() + 2, $event->getTimestamp(), 'Timestamp not set correctly');
				$this->assertGreaterThanOrEqual(\time() - 2, $event->getTimestamp(), 'Timestamp not set correctly');
				$this->assertSame($author, $event->getAuthor(), 'Author name not set correctly');
			});
		$this->activityManager->registerConsumer(function () use ($consumer) {
			return $consumer;
		});

		$this->activityManager->publish($event);
	}

	public function testPublishAllManually() {
		$event = new Event();
		$event->setApp('test_app')
			->setType('test_type')
			->setAffectedUser('test_affected')
			->setAuthor('test_author')
			->setTimestamp(1337)
			->setSubject('test_subject', ['test_subject_param'])
			->setMessage('test_message', ['test_message_param'])
			->setObject('test_object_type', 42, 'test_object_name')
			->setLink('test_link')
		;

		$consumer = $this->getMockBuilder('OCP\Activity\IConsumer')
			->disableOriginalConstructor()
			->getMock();
		$consumer->expects($this->once())
			->method('receive')
			->willReturnCallback(function (IEvent $event) {
				$this->assertSame('test_app', $event->getApp(), 'App not set correctly');
				$this->assertSame('test_type', $event->getType(), 'Type not set correctly');
				$this->assertSame('test_affected', $event->getAffectedUser(), 'Affected user not set correctly');
				$this->assertSame('test_author', $event->getAuthor(), 'Author not set correctly');
				$this->assertSame(1337, $event->getTimestamp(), 'Timestamp not set correctly');
				$this->assertSame('test_subject', $event->getSubject(), 'Subject not set correctly');
				$this->assertSame(['test_subject_param'], $event->getSubjectParameters(), 'Subject parameter not set correctly');
				$this->assertSame('test_message', $event->getMessage(), 'Message not set correctly');
				$this->assertSame(['test_message_param'], $event->getMessageParameters(), 'Message parameter not set correctly');
				$this->assertSame('test_object_type', $event->getObjectType(), 'Object type not set correctly');
				$this->assertSame(42, $event->getObjectId(), 'Object ID not set correctly');
				$this->assertSame('test_object_name', $event->getObjectName(), 'Object name not set correctly');
				$this->assertSame('test_link', $event->getLink(), 'Link not set correctly');
			});
		$this->activityManager->registerConsumer(function () use ($consumer) {
			return $consumer;
		});

		$this->activityManager->publish($event);
	}

	public function testDeprecatedPublishActivity() {
		$event = new Event();
		$event->setApp('test_app')
			->setType('test_type')
			->setAffectedUser('test_affected')
			->setAuthor('test_author')
			->setTimestamp(1337)
			->setSubject('test_subject', ['test_subject_param'])
			->setMessage('test_message', ['test_message_param'])
			->setObject('test_object_type', 42, 'test_object_name')
			->setLink('test_link')
		;

		$consumer = $this->getMockBuilder('OCP\Activity\IConsumer')
			->disableOriginalConstructor()
			->getMock();
		$consumer->expects($this->once())
			->method('receive')
			->willReturnCallback(function (IEvent $event) {
				$this->assertSame('test_app', $event->getApp(), 'App not set correctly');
				$this->assertSame('test_type', $event->getType(), 'Type not set correctly');
				$this->assertSame('test_affected', $event->getAffectedUser(), 'Affected user not set correctly');
				$this->assertSame('test_subject', $event->getSubject(), 'Subject not set correctly');
				$this->assertSame(['test_subject_param'], $event->getSubjectParameters(), 'Subject parameter not set correctly');
				$this->assertSame('test_message', $event->getMessage(), 'Message not set correctly');
				$this->assertSame(['test_message_param'], $event->getMessageParameters(), 'Message parameter not set correctly');
				$this->assertSame('test_object_name', $event->getObjectName(), 'Object name not set correctly');
				$this->assertSame('test_link', $event->getLink(), 'Link not set correctly');

				// The following values can not be used via publishActivity()
				$this->assertLessThanOrEqual(\time() + 2, $event->getTimestamp(), 'Timestamp not set correctly');
				$this->assertGreaterThanOrEqual(\time() - 2, $event->getTimestamp(), 'Timestamp not set correctly');
				$this->assertNull($event->getAuthor(), 'Author not set correctly');
				$this->assertSame('', $event->getObjectType(), 'Object type should not be set');
				$this->assertSame(0, $event->getObjectId(), 'Object ID should not be set');
			});
		$this->activityManager->registerConsumer(function () use ($consumer) {
			return $consumer;
		});

		$this->activityManager->publishActivity(
			$event->getApp(),
			$event->getSubject(), $event->getSubjectParameters(),
			$event->getMessage(), $event->getMessageParameters(),
			$event->getObjectName(),
			$event->getLink(),
			$event->getAffectedUser(),
			$event->getType(),
			IExtension::PRIORITY_MEDIUM
		);
	}
}

class SimpleExtension implements IExtension {
	public function getNotificationTypes($languageCode) {
		return ['NT1', 'NT2'];
	}

	public function getDefaultTypes($method) {
		if ($method === 'stream') {
			return ['DT0'];
		}

		return [];
	}

	public function getTypeIcon($type) {
		if ($type === 'NT1') {
			return 'icon-nt-one';
		}
		return '';
	}

	public function translate($app, $text, $params, $stripPath, $highlightParams, $languageCode) {
		if ($app === 'APP0') {
			return "Stupid translation";
		}

		return false;
	}

	public function getSpecialParameterList($app, $text) {
		if ($app === 'APP0') {
			return [0 => 'file', 1 => 'username'];
		}

		return false;
	}

	public function getGroupParameter($activity) {
		return 5;
	}

	public function getNavigation() {
		return [
			'apps' => ['nav1', 'nav2', 'nav3', 'nav4'],
			'top'  => ['top1', 'top2']
		];
	}

	public function isFilterValid($filterValue) {
		if ($filterValue === 'fv01') {
			return true;
		}

		return false;
	}

	public function filterNotificationTypes($types, $filter) {
		if ($filter === 'fv01') {
			unset($types[0]);
		}
		return $types;
	}

	public function getQueryForFilter($filter) {
		if ($filter === 'fv01') {
			return ['`app` = ? and `message` like ?', ['mail', 'ownCloud%']];
		}

		return false;
	}
}

class NoOpExtension implements IExtension {
	public function getNotificationTypes($languageCode) {
		return false;
	}

	public function getDefaultTypes($method) {
		return false;
	}

	public function getTypeIcon($type) {
		return false;
	}

	public function translate($app, $text, $params, $stripPath, $highlightParams, $languageCode) {
		return false;
	}

	public function getSpecialParameterList($app, $text) {
		return false;
	}

	public function getGroupParameter($activity) {
		return false;
	}

	public function getNavigation() {
		return false;
	}

	public function isFilterValid($filterValue) {
		return false;
	}

	public function filterNotificationTypes($types, $filter) {
		return false;
	}

	public function getQueryForFilter($filter) {
		return false;
	}
}

class NoOpConsumer implements IConsumer {
	public function receive(IEvent $event) {
	}
}
