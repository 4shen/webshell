<?php

declare(strict_types = 1);
/**
 * @copyright Copyright (c) 2018 Michael Weimann <mail@michael-weimann.eu>
 *
 * @author Michael Weimann <mail@michael-weimann.eu>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tests\Core\Controller;

use OC\AppFramework\Http;
use OC\Core\Controller\SvgController;
use OC\Template\IconsCacher;
use OCP\App\AppPathNotFoundException;
use OCP\App\IAppManager;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use Test\TestCase;

/**
 * This class provides test cases for the svg controller
 */
class SvgControllerTest extends TestCase {
	public const TEST_IMAGES_SOURCE_PATH = __DIR__ . '/../../data/svg';
	public const TEST_IMAGES_PATH = __DIR__ . '/../../../core/img/testImages';
	public const TEST_IMAGE_MIXED = 'mixed-source.svg';
	public const TEST_IMAGE_RECT = 'rect-black.svg';
	public const TEST_IMAGES = [
		self::TEST_IMAGE_MIXED,
		self::TEST_IMAGE_RECT,
	];

	/** @var IAppManager|\PHPUnit_Framework_MockObject_MockObject */
	private $appManager;

	/**
	 * @var SvgController
	 */
	private $svgController;

	/**
	 * Copy test svgs into the core img "test" directory.
	 *
	 * @beforeClass
	 * @return void
	 */
	public static function copyTestImagesIntoPlace() {
		mkdir(self::TEST_IMAGES_PATH);
		foreach (self::TEST_IMAGES as $testImage) {
			copy(
				self::TEST_IMAGES_SOURCE_PATH .'/' . $testImage,
				self::TEST_IMAGES_PATH . '/' . $testImage
			);
		}
	}

	/**
	 * Removes the test svgs from the core img "test" directory.
	 *
	 * @afterClass
	 * @return void
	 */
	public static function removeTestImages() {
		foreach (self::TEST_IMAGES as $testImage) {
			unlink(self::TEST_IMAGES_PATH . '/' . $testImage);
		}
		rmdir(self::TEST_IMAGES_PATH);
	}

	/**
	 * Setups a SVG controller instance for tests.
	 *
	 * @before
	 * @return void
	 */
	public function setupSvgController() {
		/** @var IRequest */
		$request = $this->getMockBuilder(IRequest::class)->getMock();
		/** @var ITimeFactory $timeFactory */
		$timeFactory = $this->getMockBuilder(ITimeFactory::class)->getMock();
		/** @var IAppManager */
		$this->appManager = $this->getMockBuilder(IAppManager::class)->getMock();
		/** @var IconsCacher $iconsCacher */
		$iconsCacher = $this->getMockBuilder(IconsCacher::class)->disableOriginalConstructor()->setMethods(['__construct'])->getMock();
		$this->svgController = new SvgController('core', $request, $timeFactory, $this->appManager, $iconsCacher);
	}

	/**
	 * Checks that requesting an unknown image results in a 404.
	 *
	 * @return void
	 */
	public function testGetSvgFromCoreNotFound() {
		$response = $this->svgController->getSvgFromCore('huhuu', '2342', '#ff0000');
		self::assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	/**
	 * Provides svg coloring test data.
	 *
	 * @return array
	 */
	public function provideGetSvgFromCoreTestData(): array {
		return [
			'mixed' => ['mixed-source', 'f00', file_get_contents(self::TEST_IMAGES_SOURCE_PATH . '/mixed-red.svg')],
			'black rect' => ['rect-black', 'f00', file_get_contents(self::TEST_IMAGES_SOURCE_PATH . '/rect-red.svg')],
		];
	}

	/**
	 * Tests that retrieving a colored SVG works.
	 *
	 * @dataProvider provideGetSvgFromCoreTestData
	 * @param string $name The requested svg name
	 * @param string $color The requested color
	 * @param string $expected The expected svg
	 * @return void
	 */
	public function testGetSvgFromCore(string $name, string $color, string $expected) {
		$response = $this->svgController->getSvgFromCore('testImages', $name, $color);

		self::assertEquals(Http::STATUS_OK, $response->getStatus());

		$headers = $response->getHeaders();
		self::assertArrayHasKey('Content-Type', $headers);
		self::assertEquals($headers['Content-Type'], 'image/svg+xml');

		self::assertEquals($expected, $response->getData());
	}

	/**
	 * Checks that requesting an unknown image results in a 404.
	 */
	public function testGetSvgFromAppNotFound(): void {
		$this->appManager->expects($this->once())
			->method('getAppPath')
			->with('invalid_app')
			->willThrowException(new AppPathNotFoundException('Could not find path for invalid_app'));

		$response = $this->svgController->getSvgFromApp('invalid_app', 'some-icon', '#ff0000');
		self::assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	/**
	 * Provides svg coloring test data.
	 *
	 * @return array
	 */
	public function provideGetSvgFromAppTestData(): array {
		return [
			'settings admin' => ['settings', 'admin', 'f00', file_get_contents(self::TEST_IMAGES_SOURCE_PATH . '/settings-admin-red.svg')],
			'files app' => ['files', 'app', 'f00', file_get_contents(self::TEST_IMAGES_SOURCE_PATH . '/files-app-red.svg')],
		];
	}

	/**
	 * Tests that retrieving a colored SVG works.
	 *
	 * @dataProvider provideGetSvgFromAppTestData
	 * @param string $appName
	 * @param string $name The requested svg name
	 * @param string $color The requested color
	 * @param string $expected
	 */
	public function testGetSvgFromApp(string $appName, string $name, string $color, string $expected): void {
		$this->appManager->expects($this->once())
			->method('getAppPath')
			->with($appName)
			->willReturn(__DIR__ . '/../../../apps/' . $appName);

		$response = $this->svgController->getSvgFromApp($appName, $name, $color);

		self::assertEquals(Http::STATUS_OK, $response->getStatus());

		$headers = $response->getHeaders();
		self::assertArrayHasKey('Content-Type', $headers);
		self::assertEquals($headers['Content-Type'], 'image/svg+xml');

		self::assertEquals($expected, $response->getData());
	}
}
