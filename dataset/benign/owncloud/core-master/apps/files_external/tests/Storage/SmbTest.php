<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OCA\Files_External\Tests\Storage;

use OCA\Files_External\Lib\Storage\SMB;

/**
 * Class SmbTest
 *
 * @group DB
 *
 * @package OCA\Files_External\Tests\Storage
 */
class SmbTest extends \Test\Files\Storage\Storage {
	protected function setUp(): void {
		parent::setUp();
		$id = $this->getUniqueID();
		$config = include 'files_external/tests/config.smb.php';
		if (!\is_array($config) or !$config['run']) {
			$this->markTestSkipped('Samba backend not configured');
		}
		if (\substr($config['root'], -1, 1) != '/') {
			$config['root'] .= '/';
		}
		$config['root'] .= $id; //make sure we have an new empty folder to work in
		$this->instance = new SMB($config);
		$this->assertTrue($this->instance->mkdir('/'), 'Failed to create a root dir');
	}

	protected function tearDown(): void {
		if ($this->instance) {
			$this->instance->rmdir('');
			// force disconnect of the client
			unset($this->instance);
		}

		parent::tearDown();
	}

	public function directoryProvider() {
		// doesn't support leading/trailing spaces
		return [['folder']];
	}

	public function testRenameWithSpaces() {
		$this->instance->mkdir('with spaces');
		$this->assertTrue($this->instance->is_dir('with spaces'), 'Failed to create directory with spaces');
		$result = $this->instance->rename('with spaces', 'foo bar');
		$this->assertTrue($result, 'Failed to rename dir');
		$this->assertTrue($this->instance->is_dir('foo bar'), 'Failed to locate the dir with a new name');
	}

	public function testStorageId() {
		$this->instance = new SMB([
			'host' => 'testhost',
			'user' => 'testuser',
			'password' => 'somepass',
			'share' => 'someshare',
			'root' => 'someroot',
		]);
		$this->assertEquals('smb::testuser@testhost//someshare//someroot/', $this->instance->getId());
		$this->instance = null;
	}

	public function testRenameRoot() {
		// root can't be renamed
		$this->assertFalse($this->instance->rename('', 'foo1'));

		$this->instance->mkdir('foo2');
		$this->assertFalse($this->instance->rename('foo2', ''));
		$this->instance->rmdir('foo2');
	}

	public function testUnlinkRoot() {
		// root can't be deleted
		$this->assertFalse($this->instance->unlink(''));
	}

	public function testRmdirRoot() {
		// root can't be deleted
		$this->assertFalse($this->instance->rmdir(''));
	}
}
