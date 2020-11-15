<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files_Sharing\Tests;

use OCP\Share\Exceptions\ShareNotFound;

/**
 * Class UpdaterTest
 *
 * @group DB
 */
class UpdaterTest extends TestCase {
	const TEST_FOLDER_NAME = '/folder_share_updater_test';

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		\OCA\Files_Sharing\Helper::registerHooks();
	}

	protected function setUp(): void {
		parent::setUp();

		$this->folder = self::TEST_FOLDER_NAME;

		$this->filename = '/share-updater-test.txt';

		// save file with content
		$this->view->file_put_contents($this->filename, $this->data);
		$this->view->mkdir($this->folder);
		$this->view->file_put_contents($this->folder . '/' . $this->filename, $this->data);
	}

	protected function tearDown(): void {
		if ($this->view) {
			$this->view->unlink($this->filename);
			$this->view->deleteAll($this->folder);
		}

		parent::tearDown();
	}

	/**
	 * test deletion of a folder which contains share mount points. Share mount
	 * points should be unshared before the folder gets deleted so
	 * that the mount point doesn't end up at the trash bin
	 */
	public function testDeleteParentFolder() {
		$status = \OC_App::isEnabled('files_trashbin');
		\OC_App::enable('files_trashbin');

		\OCA\Files_Trashbin\Trashbin::registerHooks();

		$fileinfo = \OC\Files\Filesystem::getFileInfo($this->folder);
		$this->assertInstanceOf(\OC\Files\FileInfo::class, $fileinfo);

		$share = $this->share(
			\OCP\Share::SHARE_TYPE_USER,
			$this->folder,
			self::TEST_FILES_SHARING_API_USER1,
			self::TEST_FILES_SHARING_API_USER2,
			\OCP\Constants::PERMISSION_ALL
		);

		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);
		$view = new \OC\Files\View('/' . self::TEST_FILES_SHARING_API_USER2 . '/files');

		// check if user2 can see the shared folder
		$this->assertTrue($view->file_exists($this->folder));

		// share still exists, no exception
		$this->shareManager->getShareById($share->getFullId());

		$view->mkdir("localFolder");
		$view->file_put_contents("localFolder/localFile.txt", "local file");

		$view->rename($this->folder, 'localFolder/' . $this->folder);

		// share mount point should now be moved to the subfolder
		$this->assertFalse($view->file_exists($this->folder));
		$this->assertTrue($view->file_exists('localFolder/' .$this->folder));

		$view->unlink('localFolder');

		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);

		// shared folder should be rejected
		$rejectedShare = $this->shareManager->getShareById($share->getFullId());
		$this->assertEquals(\OCP\Share::STATE_REJECTED, $rejectedShare->getState(), 'after the parent directory was deleted the share should be rejected');

		// trashbin should contain the local file but not the mount point
		$rootView = new \OC\Files\View('/' . self::TEST_FILES_SHARING_API_USER2);
		$trashContent = \OCA\Files_Trashbin\Helper::getTrashFiles('/', self::TEST_FILES_SHARING_API_USER2);
		$this->assertCount(1, $trashContent);
		$firstElement = \reset($trashContent);
		$timestamp = $firstElement['mtime'];
		$this->assertTrue($rootView->file_exists('files_trashbin/files/localFolder.d' . $timestamp . '/localFile.txt'));
		$this->assertFalse($rootView->file_exists('files_trashbin/files/localFolder.d' . $timestamp . '/' . $this->folder));

		//cleanup
		$rootView->deleteAll('files_trashin');

		if ($status === false) {
			\OC_App::disable('files_trashbin');
		}

		\OC\Files\Filesystem::getLoader()->removeStorageWrapper('oc_trashbin');
	}

	public function shareFolderProvider() {
		return [
			['/'],
			['/my_shares'],
		];
	}

	/**
	 * if a file gets shared the etag for the recipients root should change
	 *
	 * @dataProvider shareFolderProvider
	 *
	 * @param string $shareFolder share folder to use
	 */
	public function testShareFile($shareFolder) {
		$config = \OC::$server->getConfig();
		$oldShareFolder = $config->getSystemValue('share_folder');
		$config->setSystemValue('share_folder', $shareFolder);

		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);

		$beforeShareRoot = \OC\Files\Filesystem::getFileInfo('');
		$etagBeforeShareRoot = $beforeShareRoot->getEtag();

		\OC\Files\Filesystem::mkdir($shareFolder);

		$beforeShareDir = \OC\Files\Filesystem::getFileInfo($shareFolder);
		$etagBeforeShareDir = $beforeShareDir->getEtag();

		$this->loginHelper(self::TEST_FILES_SHARING_API_USER1);

		$share = $this->share(
			\OCP\Share::SHARE_TYPE_USER,
			$this->folder,
			self::TEST_FILES_SHARING_API_USER1,
			self::TEST_FILES_SHARING_API_USER2,
			\OCP\Constants::PERMISSION_ALL
		);

		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);

		$afterShareRoot = \OC\Files\Filesystem::getFileInfo('');
		$etagAfterShareRoot = $afterShareRoot->getEtag();

		$afterShareDir = \OC\Files\Filesystem::getFileInfo($shareFolder);
		$etagAfterShareDir = $afterShareDir->getEtag();

		$this->assertIsString($etagBeforeShareRoot);
		$this->assertIsString($etagBeforeShareDir);
		$this->assertIsString($etagAfterShareRoot);
		$this->assertIsString($etagAfterShareDir);
		$this->assertNotSame($etagBeforeShareRoot, $etagAfterShareRoot);
		$this->assertNotSame($etagBeforeShareDir, $etagAfterShareDir);

		// cleanup
		$this->loginHelper(self::TEST_FILES_SHARING_API_USER1);
		$this->shareManager->deleteShare($share);

		$config->setSystemValue('share_folder', $oldShareFolder);
	}

	/**
	 * if a folder gets renamed all children mount points should be renamed too
	 */
	public function testRename() {
		$fileinfo = \OC\Files\Filesystem::getFileInfo($this->folder);

		$share = $this->share(
			\OCP\Share::SHARE_TYPE_USER,
			$this->folder,
			self::TEST_FILES_SHARING_API_USER1,
			self::TEST_FILES_SHARING_API_USER2,
			\OCP\Constants::PERMISSION_ALL
		);

		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);

		// make sure that the shared folder exists
		$this->assertTrue(\OC\Files\Filesystem::file_exists($this->folder));

		\OC\Files\Filesystem::mkdir('oldTarget');
		\OC\Files\Filesystem::mkdir('oldTarget/subfolder');
		\OC\Files\Filesystem::mkdir('newTarget');

		\OC\Files\Filesystem::rename($this->folder, 'oldTarget/subfolder/' . $this->folder);

		// re-login to make sure that the new mount points are initialized
		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);

		\OC\Files\Filesystem::rename('/oldTarget', '/newTarget/oldTarget');

		// re-login to make sure that the new mount points are initialized
		$this->loginHelper(self::TEST_FILES_SHARING_API_USER2);

		$this->assertTrue(\OC\Files\Filesystem::file_exists('/newTarget/oldTarget/subfolder/' . $this->folder));

		// cleanup
		$this->shareManager->deleteShare($share);
	}
}
