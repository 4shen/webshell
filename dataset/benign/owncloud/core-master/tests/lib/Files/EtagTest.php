<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files;

use OC\Files\Filesystem;
use OC\Files\Utils\Scanner;
use Test\Traits\UserTrait;

/**
 * Class EtagTest
 *
 * @group DB
 *
 * @package Test\Files
 */
class EtagTest extends \Test\TestCase {
	use UserTrait;

	private $datadir;

	private $tmpDir;

	protected function setUp(): void {
		parent::setUp();

		\OC_Hook::clear('OC_Filesystem', 'setup');
		$application = new \OCA\Files_Sharing\AppInfo\Application();
		$application->registerMountProviders();
		\OCP\Share::registerBackend('file', 'OCA\Files_Sharing\ShareBackend\File');
		\OCP\Share::registerBackend('folder', 'OCA\Files_Sharing\ShareBackend\Folder', 'file');

		$config = \OC::$server->getConfig();
		$this->datadir = $config->getSystemValue('datadirectory');
		$this->tmpDir = \OC::$server->getTempManager()->getTemporaryFolder();
		$config->setSystemValue('datadirectory', $this->tmpDir);
	}

	protected function tearDown(): void {
		\OC::$server->getConfig()->setSystemValue('datadirectory', $this->datadir);

		$this->logout();
		parent::tearDown();
	}

	public function testNewUser() {
		$user1 = $this->getUniqueID('user_');
		$this->createUser($user1, $user1);

		$this->loginAsUser($user1);
		Filesystem::mkdir('/folder');
		Filesystem::mkdir('/folder/subfolder');
		Filesystem::file_put_contents('/foo.txt', 'asd');
		Filesystem::file_put_contents('/folder/bar.txt', 'fgh');
		Filesystem::file_put_contents('/folder/subfolder/qwerty.txt', 'jkl');

		$files = ['/foo.txt', '/folder/bar.txt', '/folder/subfolder', '/folder/subfolder/qwerty.txt'];
		$originalEtags = $this->getEtags($files);

		$scanner = new Scanner($user1, \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		$scanner->backgroundScan('/');

		$newEtags = $this->getEtags($files);
		// loop over array and use assertSame over assertEquals to prevent false positives
		foreach ($originalEtags as $file => $originalEtag) {
			$this->assertSame($originalEtag, $newEtags[$file]);
		}
	}

	/**
	 * @param string[] $files
	 * @return array
	 */
	private function getEtags($files) {
		$etags = [];
		foreach ($files as $file) {
			$info = Filesystem::getFileInfo($file);
			$etags[$file] = $info['etag'];
		}
		return $etags;
	}
}
