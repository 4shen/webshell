<?php
/**
 * @author Olivier Paroz <owncloud@interfasys.ch>
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

namespace Test\Preview;

use OCP\Files\File;
use OCP\Files\Node;
use OCP\Preview\IProvider2;
use Test\Traits\UserTrait;

abstract class Provider extends \Test\TestCase {
	use UserTrait;

	/** @var File */
	protected $imgPath;
	/** @var int */
	protected $width;
	/** @var int */
	protected $height;
	/** @var IProvider2 */
	protected $provider;
	/** @var int */
	protected $maxWidth = 1024;
	/** @var int */
	protected $maxHeight = 1024;
	/** @var bool */
	protected $scalingUp = false;
	/** @var int */
	protected $userId;
	/** @var \OC\Files\View */
	protected $rootView;

	protected function setUp(): void {
		parent::setUp();

		$userManager = \OC::$server->getUserManager();
		$userManager->clearBackends();

		$userId = $this->getUniqueID();
		$this->createUser($userId, $userId);
		$this->loginAsUser($userId);

		$this->rootView = new \OC\Files\View('');

		$this->userId = $userId;
	}

	protected function tearDown(): void {
		$this->logout();

		parent::tearDown();
	}

	public static function dimensionsDataProvider() {
		return [
			[-\random_int(5, 100), -\random_int(5, 100)],
			[\random_int(5, 100), \random_int(5, 100)],
			[-\random_int(5, 100), \random_int(5, 100)],
			[\random_int(5, 100), -\random_int(5, 100)],
		];
	}

	/**
	 * Launches all the tests we have
	 *
	 * @dataProvider dimensionsDataProvider
	 * @requires extension imagick
	 *
	 * @param int $widthAdjustment
	 * @param int $heightAdjustment
	 */
	public function testGetThumbnail($widthAdjustment, $heightAdjustment) {
		$ratio = \round($this->width / $this->height, 2);
		$this->maxWidth = $this->width - $widthAdjustment;
		$this->maxHeight = $this->height - $heightAdjustment;

		// Testing code
		/*print_r("w $this->width ");
		print_r("h $this->height ");
		print_r("r $ratio ");*/

		$preview = $this->getPreview($this->provider);
		// The TXT provider uses the max dimensions to create its canvas,
		// so the ratio will always be the one of the max dimension canvas
		if (!$this->provider instanceof \OC\Preview\TXT) {
			$this->doesRatioMatch($preview, $ratio);
		}
		$this->doesPreviewFit($preview);
	}

	/**
	 * Adds the test file to the filesystem
	 *
	 * @param string $fileName name of the file to create
	 * @param string $fileContent path to file to use for test
	 *
	 * @return Node
	 * @throws \Exception
	 * @throws \OCP\Files\NotFoundException
	 */
	protected function prepareTestFile($fileName, $fileContent) {
		$imgData = \file_get_contents($fileContent);
		$imgPath = '/' . $this->userId . '/files/' . $fileName;
		$this->rootView->file_put_contents($imgPath, $imgData);

		return \OC::$server->getUserFolder($this->userId)->get($fileName);
	}

	/**
	 * Retrieves a max size thumbnail can be created
	 *
	 * @param IProvider2 $provider
	 *
	 * @return bool|\OCP\IImage
	 * @throws \OCP\Files\NotPermittedException
	 */
	private function getPreview($provider) {
		$preview = $provider->getThumbnail($this->imgPath, $this->maxWidth, $this->maxHeight, $this->scalingUp);

		$this->assertNotFalse($preview);
		$this->assertTrue($preview->valid());

		// test that the file still exists
		$this->assertNotNull($this->imgPath->getContent());

		return $preview;
	}

	/**
	 * Checks if the preview ratio matches the original ratio
	 *
	 * @param \OCP\IImage $preview
	 * @param int $ratio
	 */
	private function doesRatioMatch($preview, $ratio) {
		$previewRatio = \round($preview->width() / $preview->height(), 2);
		$this->assertEquals($ratio, $previewRatio);
	}

	/**
	 * Tests if a max size preview of smaller dimensions can be created
	 *
	 * @param \OCP\IImage $preview
	 */
	private function doesPreviewFit($preview) {
		$maxDimRatio = \round($this->maxWidth / $this->maxHeight, 2);
		$previewRatio = \round($preview->width() / $preview->height(), 2);

		// Testing code
		/*print_r("mw $this->maxWidth ");
		print_r("mh $this->maxHeight ");
		print_r("mr $maxDimRatio ");
		$pw = $preview->width();
		$ph = $preview->height();
		print_r("pw $pw ");
		print_r("ph $ph ");
		print_r("pr $previewRatio ");*/

		if ($maxDimRatio < $previewRatio) {
			$this->assertLessThanOrEqual($this->maxWidth, $preview->width());
			$this->assertLessThan($this->maxHeight, $preview->height());
		} elseif ($maxDimRatio > $previewRatio) {
			$this->assertLessThan($this->maxWidth, $preview->width());
			$this->assertLessThanOrEqual($this->maxHeight, $preview->height());
		} else { // Original had to be resized
			$this->assertLessThanOrEqual($this->maxWidth, $preview->width());
			$this->assertLessThanOrEqual($this->maxHeight, $preview->height());
		}
	}
}
