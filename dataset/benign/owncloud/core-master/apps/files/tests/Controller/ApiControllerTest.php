<?php
/**
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

namespace OCA\Files\Controller;

use OCA\Files\Service\TagService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\NotFoundException;
use OCP\Files\StorageNotAvailableException;
use OCP\Image;
use OCP\IPreview;
use OCP\IRequest;
use Test\TestCase;

/**
 * Class ApiController
 *
 * @package OCA\Files\Controller
 */
class ApiControllerTest extends TestCase {
	/** @var string */
	private $appName = 'files';
	/** @var \OCP\IUser */
	private $user;
	/** @var IRequest */
	private $request;
	/** @var TagService */
	private $tagService;
	/** @var IPreview */
	private $preview;
	/** @var ApiController */
	private $apiController;
	/** @var \OCP\Share\IManager */
	private $shareManager;
	/** @var \OCP\IConfig */
	private $config;

	public function setUp(): void {
		$this->request = $this->getMockBuilder('\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->user = $this->createMock('\OCP\IUser');
		$this->user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('user1'));
		$userSession = $this->createMock('\OCP\IUserSession');
		$userSession->expects($this->any())
			->method('getUser')
			->will($this->returnValue($this->user));
		$this->tagService = $this->getMockBuilder('\OCA\Files\Service\TagService')
			->disableOriginalConstructor()
			->getMock();
		$this->shareManager = $this->getMockBuilder('\OCP\Share\IManager')
			->disableOriginalConstructor()
			->getMock();
		$this->preview = $this->getMockBuilder('\OCP\IPreview')
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->createMock('\OCP\IConfig');

		$this->apiController = new ApiController(
			$this->appName,
			$this->request,
			$userSession,
			$this->tagService,
			$this->preview,
			$this->shareManager,
			$this->config
		);
	}

	public function testUpdateFileTagsEmpty() {
		$expected = new DataResponse([]);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt'));
	}

	public function testUpdateFileTagsWorking() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2']);

		$expected = new DataResponse([
			'tags' => [
				'Tag1',
				'Tag2'
			],
		]);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsNotFoundException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new NotFoundException('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsStorageNotAvailableException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new StorageNotAvailableException('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_SERVICE_UNAVAILABLE);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsStorageGenericException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new \Exception('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testGetThumbnailInvalidSize() {
		$expected = new DataResponse(['message' => 'Requested size must be numeric and a positive value.'], Http::STATUS_BAD_REQUEST);
		$this->assertEquals($expected, $this->apiController->getThumbnail(0, 0, ''));
	}

	public function testGetThumbnailInvaidImage() {
		$this->preview->expects($this->once())
			->method('createPreview')
			->with('files/unknown.jpg', 10, 10, true)
			->willReturn(new Image);
		$expected = new DataResponse(['message' => 'File not found.'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->getThumbnail(10, 10, 'unknown.jpg'));
	}

	public function testGetThumbnail() {
		$this->preview->expects($this->once())
			->method('createPreview')
			->with('files/known.jpg', 10, 10, true)
			->willReturn(new Image(\OC::$SERVERROOT.'/tests/data/testimage.jpg'));

		$ret = $this->apiController->getThumbnail(10, 10, 'known.jpg');

		$this->assertEquals(Http::STATUS_OK, $ret->getStatus());
	}

	public function testUpdateFileSorting() {
		$mode = 'mtime';
		$direction = 'desc';

		$this->config->expects($this->at(0))
			->method('setUserValue')
			->with($this->user->getUID(), 'files', 'file_sorting', $mode);
		$this->config->expects($this->at(1))
			->method('setUserValue')
			->with($this->user->getUID(), 'files', 'file_sorting_direction', $direction);

		$expected = new HTTP\Response();
		$actual = $this->apiController->updateFileSorting($mode, $direction);
		$this->assertEquals($expected, $actual);
	}

	public function invalidSortingModeData() {
		return [
			['color', 'asc'],
			['name', 'size'],
			['foo', 'bar']
		];
	}

	/**
	 * @dataProvider invalidSortingModeData
	 */
	public function testUpdateInvalidFileSorting($mode, $direction) {
		$this->config->expects($this->never())
			->method('setUserValue');

		$expected = new Http\Response(null);
		$expected->setStatus(Http::STATUS_UNPROCESSABLE_ENTITY);

		$result = $this->apiController->updateFileSorting($mode, $direction);

		$this->assertEquals($expected, $result);
	}

	public function testShowHiddenFiles() {
		$show = false;

		$this->config->expects($this->once())
			->method('setUserValue')
			->with($this->user->getUID(), 'files', 'show_hidden', $show);

		$expected = new Http\Response();
		$actual = $this->apiController->showHiddenFiles($show);

		$this->assertEquals($expected, $actual);
	}
}
