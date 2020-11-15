<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

namespace OCA\Files_Sharing;

use OC\Files\ObjectStore\NoopScanner;

/**
 * Scanner for SharedStorage
 */
class Scanner extends \OC\Files\Cache\Scanner {
	private $sourceScanner;

	/**
	 * Returns metadata from the shared storage, but
	 * with permissions from the source storage.
	 *
	 * @param string $path path of the file for which to retrieve metadata
	 *
	 * @return array an array of metadata of the file
	 */
	public function getData($path) {
		$data = parent::getData($path);
		if ($data === null) {
			return null;
		}
		'@phan-var \OC\Files\Storage\Storage $this->storage';
		list($sourceStorage, $internalPath) = $this->storage->resolvePath($path);
		$data['permissions'] = $sourceStorage->getPermissions($internalPath);
		return $data;
	}

	private function getSourceScanner() {
		if ($this->sourceScanner) {
			return $this->sourceScanner;
		}
		if ($this->storage->instanceOfStorage('\OCA\Files_Sharing\SharedStorage')) {
			/** @var \OC\Files\Storage\Storage $storage */
			'@phan-var \OC\Files\Storage\Storage $this->storage';
			list($storage) = $this->storage->resolvePath('');
			$this->sourceScanner = $storage->getScanner();
			return $this->sourceScanner;
		} else {
			return null;
		}
	}

	/**
	 * scan a folder and all it's children,  use source scanner if needed
	 *
	 * @inheritdoc
	 */
	public function scan($path, $recursive = self::SCAN_RECURSIVE, $reuse = -1, $lock = true) {
		$sourceScanner = $this->getSourceScanner();
		if ($sourceScanner instanceof NoopScanner) {
			'@phan-var \OC\Files\Storage\Storage $this->storage';
			list(, $internalPath) = $this->storage->resolvePath($path);
			return $sourceScanner->scan($internalPath, $recursive, $reuse, $lock);
		} else {
			return parent::scan($path, $recursive, $reuse, $lock);
		}
	}

	/**
	 * scan a single file and use source scanner if needed
	 *
	 * @inheritdoc
	 */
	public function scanFile($file, $reuseExisting = 0, $parentId = -1, $cacheData = null, $lock = true) {
		$sourceScanner = $this->getSourceScanner();
		if ($sourceScanner instanceof NoopScanner) {
			'@phan-var \OC\Files\Storage\Storage $this->storage';
			list(, $internalPath) = $this->storage->resolvePath($file);
			return parent::scan($internalPath, self::SCAN_SHALLOW, $reuseExisting, $lock);
		} else {
			return parent::scanFile($file, $reuseExisting, $parentId, $cacheData, $lock);
		}
	}
}
