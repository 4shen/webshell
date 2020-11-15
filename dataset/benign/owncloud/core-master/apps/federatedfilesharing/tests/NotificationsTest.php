<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Piotr Mrowczynski <piotr@owncloud.com>
 *
 * @copyright Copyright (c) 2020, ownCloud GmbH
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

namespace OCA\FederatedFileSharing\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use OC\AppFramework\Http;
use OCA\FederatedFileSharing\AddressHandler;
use OCA\FederatedFileSharing\DiscoveryManager;
use OCA\FederatedFileSharing\Notifications;
use OCA\FederatedFileSharing\Ocm\NotificationManager;
use OCA\FederatedFileSharing\Ocm\Permissions;
use OCP\BackgroundJob\IJobList;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\IConfig;
use OCA\FederatedFileSharing\BackgroundJob\RetryJob;
use OCP\Share\Events\DeclineShare;

class NotificationsTest extends \Test\TestCase {

	/** @var  AddressHandler | \PHPUnit\Framework\MockObject\MockObject */
	private $addressHandler;

	/** @var  IClientService | \PHPUnit\Framework\MockObject\MockObject*/
	private $httpClientService;

	/** @var  DiscoveryManager | \PHPUnit\Framework\MockObject\MockObject */
	private $discoveryManager;

	/** @var NotificationManager | \PHPUnit\Framework\MockObject\MockObject */
	private $notificationManager;

	/** @var  IJobList | \PHPUnit\Framework\MockObject\MockObject */
	private $jobList;

	/** @var  IConfig | \PHPUnit\Framework\MockObject\MockObject */
	private $config;

	public function setUp(): void {
		parent::setUp();

		$this->jobList = $this->createMock(IJobList::class);
		$this->config = $this->createMock(IConfig::class);
		$this->discoveryManager = $this->getMockBuilder(DiscoveryManager::class)
			->disableOriginalConstructor()->getMock();
		$this->notificationManager = new NotificationManager(
			$this->createMock(Permissions::class)
		);
		$this->httpClientService = $this->createMock(IClientService::class);
		$this->addressHandler = $this->getMockBuilder(AddressHandler::class)
			->disableOriginalConstructor()->getMock();
	}

	/**
	 * get instance of Notifications class
	 *
	 * @param array $mockedMethods methods which should be mocked
	 * @return Notifications | \PHPUnit\Framework\MockObject\MockObject
	 */
	private function getInstance(array $mockedMethods = []) {
		if (empty($mockedMethods)) {
			$instance = new Notifications(
				$this->addressHandler,
				$this->httpClientService,
				$this->discoveryManager,
				$this->notificationManager,
				$this->jobList,
				$this->config
			);
		} else {
			$instance = $this->getMockBuilder(Notifications::class)
				->setConstructorArgs(
					[
						$this->addressHandler,
						$this->httpClientService,
						$this->discoveryManager,
						$this->notificationManager,
						$this->jobList,
						$this->config
					]
				)->setMethods($mockedMethods)->getMock();
		}
		
		return $instance;
	}

	/**
	 * @dataProvider dataTestSendUpdateToRemote
	 *
	 * @param int $try
	 * @param array $ocmHttpRequestResult
	 * @param array $httpRequestResult
	 * @param bool $expected
	 */
	public function testSendUpdateToRemote($try, $ocmHttpRequestResult, $httpRequestResult, $expected) {
		$remote = 'remote';
		$id = 42;
		$timestamp = 63576;
		$token = 'token';
		$instance = $this->getInstance(['tryHttpPostToShareEndpoint', 'getTimestamp']);

		$instance->expects($this->any())->method('getTimestamp')->willReturn($timestamp);

		$instance->expects($this->at(0))->method('tryHttpPostToShareEndpoint')
			->with($remote, '/notifications', $this->anything(), true)
			->willReturn($ocmHttpRequestResult);

		$instance->expects($this->at(1))->method('tryHttpPostToShareEndpoint')
			->with($remote, '/'.$id.'/unshare', ['token' => $token, 'data1Key' => 'data1Value'])
			->willReturn($httpRequestResult);

		$this->addressHandler->method('removeProtocolFromUrl')
			->with($remote)->willReturn($remote);

		// only add background job on first try
		if ($try === 0 && $expected === false) {
			$this->jobList->expects($this->once())->method('add')
				->with(
					RetryJob::class,
					[
						'remote' => $remote,
						'remoteId' => $id,
						'action' => 'unshare',
						'data' => \json_encode(['data1Key' => 'data1Value']),
						'token' => $token,
						'try' => $try,
						'lastRun' => $timestamp
					]
				);
		} else {
			$this->jobList->expects($this->never())->method('add');
		}

		$this->assertSame($expected,
			$instance->sendUpdateToRemote($remote, $id, $token, 'unshare', ['data1Key' => 'data1Value'], $try)
		);
	}

	public function dataTestSendUpdateToRemote() {
		return [
			// test if background job is added correctly
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => true, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 200]]])], true],
			[1, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => true, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 200]]])], true],
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => false, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 200]]])], false],
			[1, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => false, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 200]]])], false],
			// test all combinations of 'statuscode' and 'success'
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => true, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 200]]])], true],
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => true, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 100]]])], true],
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => true, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 400]]])], false],
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => false, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 200]]])], false],
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => false, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 100]]])], false],
			[0, ['statusCode' => Http::STATUS_NOT_IMPLEMENTED], ['success' => false, 'result' => \json_encode(['ocs' => ['meta' => ['statuscode' => 400]]])], false],
		];
	}

	public function testOcmRequestsAreJson() {
		$notifications = $this->getInstance();
		$responseMock = $this->createMock(IResponse::class);
		$clientMock = $this->createMock(IClient::class);
		$clientMock->expects($this->once())
			->method('post')
			->with(
				$this->anything(),
				$this->callback(
					function ($options) {
						return isset($options['json'])
						  && !isset($options['body']);
					}
				)
			)
			->willReturn($responseMock);
		$this->httpClientService->method('newClient')->willReturn($clientMock);
		$this->invokePrivate(
			$notifications,
			'tryHttpPostToShareEndpoint',
			[
				'domain',
				'/notifications',
				['key' => 'value'],
				true
			]
		);
	}

	/**
	 * @dataProvider dataTryHttpPostToShareEndpointInException
	 *
	 * @param $exception
	 * @param $isDiscoveryException
	 * @param $allowHttpFallback
	 * @param $expectedTries
	 * @param array $expected
	 */
	public function testTryHttpPostToShareEndpointInException($exception, $isDiscoveryException, $allowHttpFallback, $expectedTries, $expected) {
		$notifications = $this->getInstance();

		if ($isDiscoveryException) {
			$this->discoveryManager->expects($this->exactly($expectedTries))->method('getShareEndpoint')
				->willThrowException($exception);
			$this->createMock(IClient::class)
				->expects($this->exactly(0))->method('post');
		} else {
			$this->discoveryManager->expects($this->exactly($expectedTries))->method('getShareEndpoint');
			$clientMock = $this->createMock(IClient::class);
			$clientMock->expects($this->exactly($expectedTries))->method('post')
				->willThrowException($exception);
			$this->httpClientService->method('newClient')->willReturn($clientMock);
		}

		$this->config->expects($this->any())
			->method('getSystemValue')
			->with('sharing.federation.allowHttpFallback', false)
			->willReturn($allowHttpFallback);

		try {
			$result = $this->invokePrivate(
				$notifications,
				'tryHttpPostToShareEndpoint',
				[
					'domain',
					'/notifications',
					[]
				]
			);

			$this->assertEquals($expected, $result);
		} catch (\Exception $e) {
			$this->assertNull($expected);
		}
	}

	public function dataTryHttpPostToShareEndpointInException() {
		$responseMock = $this->createMock(IResponse::class);
		$responseMock->method('getBody')
			->willReturn('User does not exist');

		$discExceptionMock = $this->createMock(ClientException::class);
		$discExceptionMock->method('getResponse')->willReturn($responseMock);

		$postExceptionMock = $this->createMock(ClientException::class);
		$postExceptionMock->method('getResponse')->willReturn($responseMock);

		$exceptionMock = new \Exception('Internal Server Error', 500);

		$httpExceptionMock = new \Exception('Invalid SSL Certificate', 526);

		return [
			// expect client exception in post with 2 tries
			[
				$postExceptionMock,
				false,
				false,
				2,
				[
					'success' => false,
					'result' => 'User does not exist',
				]
			],
			// expect client exception in discovery with 2 tries
			[
				$discExceptionMock,
				true,
				false,
				2,
				[
					'success' => false,
					'result' => 'User does not exist',
				]
			],
			// expect http exception in post with 1 try as http fallback not allowed
			[
				$httpExceptionMock,
				false,
				false,
				1,
				[
					'success' => false,
					'result' => '',
				]
			],
			// expect http exception in discovery with 1 try as http fallback not allowed
			[
				$httpExceptionMock,
				true,
				false,
				1,
				[
					'success' => false,
					'result' => '',
				]
			],
			// expect http exception in discovery with 2 tries as http fallback allowed
			[
				$httpExceptionMock,
				true,
				true,
				2,
				[
					'success' => false,
					'result' => '',
				]
			],
			// expect internal server error with error thrown in test
			[
				$exceptionMock,
				false,
				false,
				1,
				null
			],
		];
	}

	public function testDeclineEvent() {
		$dispatcher = \OC::$server->getEventDispatcher();
		$event = $dispatcher->dispatch(
			DeclineShare::class,
			new DeclineShare(
				[
					'remote_id' => '4354353',
					'remote' => 'http://localhost',
					'share_token' => 'ohno'
				]
			)
		);
		$this->assertInstanceOf(DeclineShare::class, $event);
	}
}
