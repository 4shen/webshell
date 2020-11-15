<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Archive;

use OC\Archive\TAR;

class TARTest extends TestBase {
	protected function setUp(): void {
		parent::setUp();
	}

	protected function getExisting() {
		return new TAR($this->getArchiveTestDataDir() . '/data.tar.gz');
	}

	protected function getNew() {
		return new TAR(\OCP\Files::tmpFile('.tar.gz'));
	}
}
