<?php
/**
 * Copyright (c) 2015 Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Http\Client;

use GuzzleHttp\Psr7\Response;
use OC\Http\Client\Client;
use OC\Security\CertificateManager;
use OCP\Http\Client\LocalServerException;
use OCP\ICertificateManager;
use OCP\IConfig;
use OCP\ILogger;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ClientTest
 */
class ClientTest extends \Test\TestCase {
	/** @var \GuzzleHttp\Client|MockObject */
	private $guzzleClient;
	/** @var CertificateManager|MockObject */
	private $certificateManager;
	/** @var Client */
	private $client;
	/** @var IConfig|MockObject */
	private $config;
	/** @var ILogger|MockObject */
	private $logger;
	/** @var array */
	private $defaultRequestOptions;

	protected function setUp(): void {
		parent::setUp();
		$this->config = $this->createMock(IConfig::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->guzzleClient = $this->createMock(\GuzzleHttp\Client::class);
		$this->certificateManager = $this->createMock(ICertificateManager::class);
		$this->client = new Client(
			$this->config,
			$this->logger,
			$this->certificateManager,
			$this->guzzleClient
		);
	}

	public function testGetProxyUri(): void {
		$this->config
			->expects($this->at(0))
			->method('getSystemValue')
			->with('proxy', null)
			->willReturn(null);
		$this->assertNull(self::invokePrivate($this->client, 'getProxyUri'));
	}

	public function testGetProxyUriProxyHostEmptyPassword(): void {
		$this->config
			->expects($this->at(0))
			->method('getSystemValue')
			->with('proxy', null)
			->willReturn('foo');
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('proxyuserpwd', null)
			->willReturn(null);
		$this->config
			->expects($this->at(2))
			->method('getSystemValue')
			->with('proxyexclude', [])
			->willReturn([]);
		$this->assertEquals([
			'http' => 'foo',
			'https' => 'foo'
		], self::invokePrivate($this->client, 'getProxyUri'));
	}

	public function testGetProxyUriProxyHostWithPassword(): void {
		$this->config
			->expects($this->at(0))
			->method('getSystemValue')
			->with(
				$this->equalTo('proxy'),
				$this->callback(function ($input) {
					return $input === '';
				})
			)
			->willReturn('foo');
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with(
				$this->equalTo('proxyuserpwd'),
				$this->callback(function ($input) {
					return $input === '';
				})
			)
			->willReturn('username:password');
		$this->config
			->expects($this->at(2))
			->method('getSystemValue')
			->with(
				$this->equalTo('proxyexclude'),
				$this->callback(function ($input) {
					return $input === [];
				})
			)
			->willReturn([]);
		$this->assertEquals([
			'http' => 'username:password@foo',
			'https' => 'username:password@foo'
		], self::invokePrivate($this->client, 'getProxyUri'));
	}

	public function testGetProxyUriProxyHostWithPasswordAndExclude(): void {
		$this->config
			->expects($this->at(0))
			->method('getSystemValue')
			->with(
				$this->equalTo('proxy'),
				$this->callback(function ($input) {
					return $input === '';
				})
			)
			->willReturn('foo');
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with(
				$this->equalTo('proxyuserpwd'),
				$this->callback(function ($input) {
					return $input === '';
				})
			)
			->willReturn('username:password');
		$this->config
			->expects($this->at(2))
			->method('getSystemValue')
			->with(
				$this->equalTo('proxyexclude'),
				$this->callback(function ($input) {
					return $input === [];
				})
			)
			->willReturn(['bar']);
		$this->assertEquals([
			'http' => 'username:password@foo',
			'https' => 'username:password@foo',
			'no' => ['bar']
		], self::invokePrivate($this->client, 'getProxyUri'));
	}

	public function dataPreventLocalAddress():array {
		return [
			['localhost/foo.bar'],
			['localHost/foo.bar'],
			['random-host/foo.bar'],
			['[::1]/bla.blub'],
			['[::]/bla.blub'],
			['192.168.0.1'],
			['172.16.42.1'],
			['[fdf8:f53b:82e4::53]/secret.ics'],
			['[fe80::200:5aee:feaa:20a2]/secret.ics'],
			['[0:0:0:0:0:0:10.0.0.1]/secret.ics'],
			['[0:0:0:0:0:ffff:127.0.0.0]/secret.ics'],
			['10.0.0.1'],
			['another-host.local'],
			['service.localhost'],
			['!@#$'], // test invalid url
		];
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddress(string $uri): void {
		$this->expectException(LocalServerException::class);
		self::invokePrivate($this->client, 'preventLocalAddress', ['http://' . $uri, []]);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressDisabledByGlobalConfig(string $uri): void {
		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('allow_local_remote_servers', false)
			->willReturn(true);

//		$this->expectException(LocalServerException::class);

		self::invokePrivate($this->client, 'preventLocalAddress', ['http://' . $uri, []]);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressDisabledByOption(string $uri): void {
		$this->config->expects($this->never())
			->method('getSystemValueBool');

//		$this->expectException(LocalServerException::class);

		self::invokePrivate($this->client, 'preventLocalAddress', ['http://' . $uri, [
			'nextcloud' => ['allow_local_address' => true],
		]]);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressOnGet(string $uri): void {
		$this->expectException(LocalServerException::class);
		$this->client->get('http://' . $uri);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressOnHead(string $uri): void {
		$this->expectException(LocalServerException::class);
		$this->client->head('http://' . $uri);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressOnPost(string $uri): void {
		$this->expectException(LocalServerException::class);
		$this->client->post('http://' . $uri);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressOnPut(string $uri): void {
		$this->expectException(LocalServerException::class);
		$this->client->put('http://' . $uri);
	}

	/**
	 * @dataProvider dataPreventLocalAddress
	 * @param string $uri
	 */
	public function testPreventLocalAddressOnDelete(string $uri): void {
		$this->expectException(LocalServerException::class);
		$this->client->delete('http://' . $uri);
	}

	private function setUpDefaultRequestOptions(): void {
		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('allow_local_remote_servers', false)
			->willReturn(true);
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('proxy', null)
			->willReturn('foo');
		$this->config
			->expects($this->at(2))
			->method('getSystemValue')
			->with('proxyuserpwd', null)
			->willReturn(null);
		$this->config
			->expects($this->at(3))
			->method('getSystemValue')
			->with('proxyexclude', [])
			->willReturn([]);
		$this->certificateManager
			->expects($this->once())
			->method('getAbsoluteBundlePath')
			->with(null)
			->willReturn('/my/path.crt');

		$this->defaultRequestOptions = [
			'verify' => '/my/path.crt',
			'proxy' => [
				'http' => 'foo',
				'https' => 'foo'
			],
			'headers' => [
				'User-Agent' => 'Nextcloud Server Crawler',
				'Accept-Encoding' => 'gzip',
			],
			'timeout' => 30,
		];
	}

	public function testGet(): void {
		$this->setUpDefaultRequestOptions();

		$this->guzzleClient->method('request')
			->with('get', 'http://localhost/', $this->defaultRequestOptions)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->get('http://localhost/', [])->getStatusCode());
	}

	public function testGetWithOptions(): void {
		$this->setUpDefaultRequestOptions();

		$options = array_merge($this->defaultRequestOptions, [
			'verify' => false,
			'proxy' => [
				'http' => 'bar',
				'https' => 'bar'
			],
		]);

		$this->guzzleClient->method('request')
			->with('get', 'http://localhost/', $options)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->get('http://localhost/', $options)->getStatusCode());
	}

	public function testPost(): void {
		$this->setUpDefaultRequestOptions();

		$this->guzzleClient->method('request')
			->with('post', 'http://localhost/', $this->defaultRequestOptions)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->post('http://localhost/', [])->getStatusCode());
	}

	public function testPostWithOptions(): void {
		$this->setUpDefaultRequestOptions();

		$options = array_merge($this->defaultRequestOptions, [
			'verify' => false,
			'proxy' => [
				'http' => 'bar',
				'https' => 'bar'
			],
		]);

		$this->guzzleClient->method('request')
			->with('post', 'http://localhost/', $options)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->post('http://localhost/', $options)->getStatusCode());
	}

	public function testPut(): void {
		$this->setUpDefaultRequestOptions();

		$this->guzzleClient->method('request')
			->with('put', 'http://localhost/', $this->defaultRequestOptions)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->put('http://localhost/', [])->getStatusCode());
	}

	public function testPutWithOptions(): void {
		$this->setUpDefaultRequestOptions();

		$options = array_merge($this->defaultRequestOptions, [
			'verify' => false,
			'proxy' => [
				'http' => 'bar',
				'https' => 'bar'
			],
		]);

		$this->guzzleClient->method('request')
			->with('put', 'http://localhost/', $options)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->put('http://localhost/', $options)->getStatusCode());
	}

	public function testDelete(): void {
		$this->setUpDefaultRequestOptions();

		$this->guzzleClient->method('request')
			->with('delete', 'http://localhost/', $this->defaultRequestOptions)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->delete('http://localhost/', [])->getStatusCode());
	}

	public function testDeleteWithOptions(): void {
		$this->setUpDefaultRequestOptions();

		$options = array_merge($this->defaultRequestOptions, [
			'verify' => false,
			'proxy' => [
				'http' => 'bar',
				'https' => 'bar'
			],
		]);

		$this->guzzleClient->method('request')
			->with('delete', 'http://localhost/', $options)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->delete('http://localhost/', $options)->getStatusCode());
	}

	public function testOptions(): void {
		$this->setUpDefaultRequestOptions();

		$this->guzzleClient->method('request')
			->with('options', 'http://localhost/', $this->defaultRequestOptions)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->options('http://localhost/', [])->getStatusCode());
	}

	public function testOptionsWithOptions(): void {
		$this->setUpDefaultRequestOptions();

		$options = array_merge($this->defaultRequestOptions, [
			'verify' => false,
			'proxy' => [
				'http' => 'bar',
				'https' => 'bar'
			],
		]);

		$this->guzzleClient->method('request')
			->with('options', 'http://localhost/', $options)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->options('http://localhost/', $options)->getStatusCode());
	}

	public function testHead(): void {
		$this->setUpDefaultRequestOptions();

		$this->guzzleClient->method('request')
			->with('head', 'http://localhost/', $this->defaultRequestOptions)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->head('http://localhost/', [])->getStatusCode());
	}

	public function testHeadWithOptions(): void {
		$this->setUpDefaultRequestOptions();

		$options = array_merge($this->defaultRequestOptions, [
			'verify' => false,
			'proxy' => [
				'http' => 'bar',
				'https' => 'bar'
			],
		]);

		$this->guzzleClient->method('request')
			->with('head', 'http://localhost/', $options)
			->willReturn(new Response(418));
		$this->assertEquals(418, $this->client->head('http://localhost/', $options)->getStatusCode());
	}

	public function testSetDefaultOptionsWithNotInstalled(): void {
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('installed', false)
			->willReturn(false);
		$this->certificateManager
			->expects($this->never())
			->method('listCertificates');

		$this->assertEquals([
			'verify' => \OC::$SERVERROOT . '/resources/config/ca-bundle.crt',
			'headers' => [
				'User-Agent' => 'Nextcloud Server Crawler',
				'Accept-Encoding' => 'gzip',
			],
			'timeout' => 30,
		], self::invokePrivate($this->client, 'buildRequestOptions', [[]]));
	}

	public function testSetDefaultOptionsWithProxy(): void {
		$this->config
			->expects($this->at(0))
			->method('getSystemValue')
			->with('proxy', null)
			->willReturn('foo');
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('proxyuserpwd', null)
			->willReturn(null);
		$this->config
			->expects($this->at(2))
			->method('getSystemValue')
			->with('proxyexclude', [])
			->willReturn([]);
		$this->certificateManager
			->expects($this->once())
			->method('getAbsoluteBundlePath')
			->with(null)
			->willReturn('/my/path.crt');

		$this->assertEquals([
			'verify' => '/my/path.crt',
			'proxy' => [
				'http' => 'foo',
				'https' => 'foo'
			],
			'headers' => [
				'User-Agent' => 'Nextcloud Server Crawler',
				'Accept-Encoding' => 'gzip',
			],
			'timeout' => 30,
		], self::invokePrivate($this->client, 'buildRequestOptions', [[]]));
	}

	public function testSetDefaultOptionsWithProxyAndExclude(): void {
		$this->config
			->expects($this->at(0))
			->method('getSystemValue')
			->with('proxy', null)
			->willReturn('foo');
		$this->config
			->expects($this->at(1))
			->method('getSystemValue')
			->with('proxyuserpwd', null)
			->willReturn(null);
		$this->config
			->expects($this->at(2))
			->method('getSystemValue')
			->with('proxyexclude', [])
			->willReturn(['bar']);
		$this->certificateManager
			->expects($this->once())
			->method('getAbsoluteBundlePath')
			->with(null)
			->willReturn('/my/path.crt');

		$this->assertEquals([
			'verify' => '/my/path.crt',
			'proxy' => [
				'http' => 'foo',
				'https' => 'foo',
				'no' => ['bar']
			],
			'headers' => [
				'User-Agent' => 'Nextcloud Server Crawler',
				'Accept-Encoding' => 'gzip',
			],
			'timeout' => 30,
		], self::invokePrivate($this->client, 'buildRequestOptions', [[]]));
	}
}
