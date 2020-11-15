<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
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

namespace OC\Files\ObjectStore;
use \OC\Files\Cache\Scanner;
use \OC\Files\Storage\Storage;

class NoopScanner extends Scanner {
	public function __construct(Storage $storage) {
		$this->storage = $storage;
		//we don't need the storage, so do nothing here
	}

	/**
	 * scan a single file and store it in the cache
	 *
	 * @param string $file
	 * @param int $reuseExisting
	 * @param int $parentId
	 * @param array|null $cacheData existing data in the cache for the file to be scanned
	 * @return array an array of metadata of the scanned file
	 */
	public function scanFile($file, $reuseExisting = 0, $parentId = -1, $cacheData = null, $lock = true) {
		$this->updateChecksums($file);
		return [];
	}

	/**
	 * scan a folder and all it's children
	 *
	 * @param string $path
	 * @param bool $recursive
	 * @param int $reuse
	 * @return array with the meta data of the scanned file or folder
	 */
	public function scan($path, $recursive = self::SCAN_RECURSIVE, $reuse = -1, $lock = true) {
		// we only update the checksums - still returning no data
		$this->updateChecksums($path);
		return [];
	}

	/**
	 * walk over any folders that are not fully scanned yet and scan them
	 */
	public function backgroundScan() {
		//noop
	}

	/**
	 * scan all the files and folders in a folder
	 *
	 * @param string $path
	 * @param bool $recursive
	 * @param int $reuse
	 * @param array $folderData existing cache data for the folder to be scanned
	 * @return int the size of the scanned folder or -1 if the size is unknown at this stage
	 */
	protected function scanChildren($path, $recursive = self::SCAN_RECURSIVE, $reuse = -1, $folderData = null, $lock = true) {
		return 0;
	}

	/**
	 * Update file checksums
	 *
	 * @param string $path
	 */
	private function updateChecksums($path) {
		$meta = $this->storage->getMetaData($path);
		if (!empty($meta['checksum'])) {
			$this->storage->getCache()->put(
				$path,
				['checksum' => $meta['checksum']]
			);
		}
	}
}
