<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright Copyright (c) 2012 Bernhard Posselt <dev@bernhard-posselt.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Test\AppFramework\Controller;

use OC\AppFramework\DependencyInjection\DIContainer;
use OC\AppFramework\Http\Request;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Security\ISecureRandom;
use Test\TestCase;

class ChildController extends Controller {
	public function __construct($appName, $request) {
		parent::__construct($appName, $request);
		$this->registerResponder('tom', function ($response) {
			return 'hi';
		});
	}

	public function custom($in) {
		$this->registerResponder('json', function ($response) {
			return new JSONResponse([\strlen($response)]);
		});

		return $in;
	}

	/**
	 * @param $in
	 * @return DataResponse
	 */
	public function customDataResponse($in) {
		$response = new DataResponse($in, 300);
		$response->addHeader('test', 'something');
		return $response;
	}
};

class ControllerTest extends TestCase {

	/** @var ChildController */
	private $controller;
	/** @var DIContainer */
	private $app;

	protected function setUp(): void {
		parent::setUp();

		$request = new Request(
			[
				'get' => ['name' => 'John Q. Public', 'nickname' => 'Joey'],
				'post' => ['name' => 'Jane Doe', 'nickname' => 'Janey'],
				'urlParams' => ['name' => 'Johnny Weissmüller'],
				'files' => ['file' => 'filevalue'],
				'env' => ['PATH' => 'daheim'],
				'session' => ['sezession' => 'kein'],
				'method' => 'hi',
			],
			$this->createMock(ISecureRandom::class),
			$this->createMock(IConfig::class)
		);

		$this->app = $this->getMockBuilder(DIContainer::class)
			->setMethods(['getAppName'])
			->setConstructorArgs(['test'])
			->getMock();
		$this->app->expects($this->any())
				->method('getAppName')
				->will($this->returnValue('apptemplate_advanced'));

		$this->controller = new ChildController($this->app, $request);
	}

	public function testParamsGet() {
		$this->assertEquals('Johnny Weissmüller', $this->controller->params('name', 'Tarzan'));
	}

	public function testParamsGetDefault() {
		$this->assertEquals('Tarzan', $this->controller->params('Ape Man', 'Tarzan'));
	}

	public function testParamsFile() {
		$this->assertEquals('filevalue', $this->controller->params('file', 'filevalue'));
	}

	public function testGetUploadedFile() {
		$this->assertEquals('filevalue', $this->controller->getUploadedFile('file'));
	}

	public function testGetUploadedFileDefault() {
		$this->assertEquals('default', $this->controller->params('files', 'default'));
	}

	public function testGetParams() {
		$params = [
				'name' => 'Johnny Weissmüller',
				'nickname' => 'Janey',
		];

		$this->assertEquals($params, $this->controller->getParams());
	}

	public function testRender() {
		$this->assertInstanceOf(TemplateResponse::class, $this->controller->render(''));
	}

	public function testSetParams() {
		$params = ['john' => 'foo'];
		$response = $this->controller->render('home', $params);

		$this->assertEquals($params, $response->getParams());
	}

	public function testRenderHeaders() {
		$headers = ['one', 'two'];
		$response = $this->controller->render('', [], '', $headers);

		$this->assertContains($headers[0], $response->getHeaders());
		$this->assertContains($headers[1], $response->getHeaders());
	}

	public function testGetRequestMethod() {
		$this->assertEquals('hi', $this->controller->method());
	}

	public function testGetEnvVariable() {
		$this->assertEquals('daheim', $this->controller->env('PATH'));
	}

	/**
	 */
	public function testFormatResonseInvalidFormat() {
		$this->expectException(\DomainException::class);

		$this->controller->buildResponse(null, 'test');
	}

	public function testFormat() {
		/** @var DataResponse $response */
		$response = $this->controller->buildResponse(['hi'], 'json');

		$this->assertEquals(['hi'], $response->getData());
	}

	public function testFormatDataResponseJSON() {
		$expectedHeaders = [
			'test' => 'something',
			'Cache-Control' => 'no-cache, no-store, must-revalidate',
			'Content-Type' => 'application/json; charset=utf-8',
			'Content-Security-Policy' => "default-src 'none';manifest-src 'self';script-src 'self' 'unsafe-eval';style-src 'self' 'unsafe-inline';img-src 'self' data: blob:;font-src 'self';connect-src 'self';media-src 'self'",
		];

		$response = $this->controller->customDataResponse(['hi']);
		/** @var DataResponse $response */
		$response = $this->controller->buildResponse($response, 'json');

		$this->assertEquals(['hi'], $response->getData());
		$this->assertEquals(300, $response->getStatus());
		$this->assertEquals($expectedHeaders, $response->getHeaders());
	}

	public function testCustomFormatter() {
		$response = $this->controller->custom('hi');
		/** @var DataResponse $response */
		$response = $this->controller->buildResponse($response, 'json');

		$this->assertEquals([2], $response->getData());
	}

	public function testDefaultResponderToJSON() {
		$responder = $this->controller->getResponderByHTTPHeader('*/*');

		$this->assertEquals('json', $responder);
	}

	public function testResponderAcceptHeaderParsed() {
		$responder = $this->controller->getResponderByHTTPHeader(
			'*/*, application/tom, application/json'
		);

		$this->assertEquals('tom', $responder);
	}

	public function testResponderAcceptHeaderParsedUpperCase() {
		$responder = $this->controller->getResponderByHTTPHeader(
			'*/*, apPlication/ToM, application/json'
		);

		$this->assertEquals('tom', $responder);
	}
}
