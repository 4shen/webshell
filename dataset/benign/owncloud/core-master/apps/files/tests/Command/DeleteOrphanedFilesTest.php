<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files\Tests\Command;

use OC\Files\View;
use OCA\Files\Command\DeleteOrphanedFiles;
use OCP\Files\StorageNotAvailableException;
use Test\TestCase;

/**
 * Class DeleteOrphanedFilesTest
 *
 * @group DB
 *
 * @package OCA\Files\Tests\Command
 */
class DeleteOrphanedFilesTest extends TestCase {

	/**
	 * @var DeleteOrphanedFiles
	 */
	private $command;

	/**
	 * @var \OCP\IDBConnection
	 */
	private $connection;

	/**
	 * @var string
	 */
	private $user1;

	protected function setUp(): void {
		parent::setUp();

		$this->connection = \OC::$server->getDatabaseConnection();

		$this->user1 = $this->getUniqueID('user1_');

		$userManager = \OC::$server->getUserManager();
		$userManager->createUser($this->user1, 'pass');

		$this->command = new DeleteOrphanedFiles($this->connection);
	}

	protected function tearDown(): void {
		$userManager = \OC::$server->getUserManager();
		$user1 = $userManager->get($this->user1);
		if ($user1) {
			$user1->delete();
		}

		$this->logout();

		parent::tearDown();
	}

	protected function getFile($fileId) {
		$stmt = $this->connection->executeQuery('SELECT * FROM `*PREFIX*filecache` WHERE `fileid` = ?', [$fileId]);
		return $stmt->fetchAll();
	}

	/**
	 * Test clearing orphaned files
	 */
	public function testClearFiles() {
		$input = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
			->disableOriginalConstructor()
			->getMock();
		$output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$this->loginAsUser($this->user1);

		$view = new View('/' . $this->user1 . '/');
		$view->mkdir('files/test');

		$fileInfo = $view->getFileInfo('files/test');

		$storageId = $fileInfo->getStorage()->getId();

		$this->assertCount(1, $this->getFile($fileInfo->getId()), 'Asserts that file is available');

		$this->command->execute($input, $output);

		$this->assertCount(1, $this->getFile($fileInfo->getId()), 'Asserts that file is still available');

		$deletedRows = $this->connection->executeUpdate('DELETE FROM `*PREFIX*storages` WHERE `id` = ?', [$storageId]);
		$this->assertNotNull($deletedRows, 'Asserts that storage got deleted');
		$this->assertSame(1, $deletedRows, 'Asserts that storage got deleted');

		// parent folder, `files`, ´test` and `welcome.txt` => 4 elements
		$output
			->expects($this->once())
			->method('writeln')
			->with('3 orphaned file cache entries deleted');

		$this->command->execute($input, $output);

		$this->assertCount(0, $this->getFile($fileInfo->getId()), 'Asserts that file gets cleaned up');

		// since we deleted the storage it might throw a (valid) StorageNotAvailableException
		try {
			$view->unlink('files/test');
		} catch (StorageNotAvailableException $e) {
		}
	}
}
