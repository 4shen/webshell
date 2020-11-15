<?php
/**
 * @author Daniel Jagszent <daniel@jagszent.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OC\Files\Cache\Wrapper;
use OC\Files\Cache\Cache;

/**
 * Jail to a subdirectory of the wrapped cache
 */
class CacheJail extends CacheWrapper {
	/**
	 * @var string
	 */
	protected $root;

	/**
	 * @param \OCP\Files\Cache\ICache $cache
	 * @param string $root
	 */
	public function __construct($cache, $root) {
		parent::__construct($cache);
		$this->root = $root;
	}

	protected function getSourcePath($path) {
		if ($path === '') {
			return $this->root;
		} else {
			return $this->root . '/' . \ltrim($path, '/');
		}
	}

	/**
	 * @param string $path
	 * @return null|string the jailed path or null if the path is outside the jail
	 */
	protected function getJailedPath($path) {
		if ($this->root === '') {
			return $path;
		}
		$rootLength = \strlen($this->root) + 1;
		if ($path === $this->root) {
			return '';
		} elseif (\substr($path, 0, $rootLength) === $this->root . '/') {
			return \substr($path, $rootLength);
		} else {
			return null;
		}
	}

	/**
	 * @param array $entry
	 * @return array
	 */
	protected function formatCacheEntry($entry) {
		if (isset($entry['path'])) {
			$entry['path'] = $this->getJailedPath($entry['path']);
		}
		return $entry;
	}

	protected function filterCacheEntry($entry) {
		$rootLength = \strlen($this->root) + 1;
		return ($entry['path'] === $this->root) or (\substr($entry['path'], 0, $rootLength) === $this->root . '/');
	}

	/**
	 * get the stored metadata of a file or folder
	 *
	 * @param string /int $file
	 * @return array|false
	 */
	public function get($file) {
		if (\is_string($file) or $file == '') {
			$file = $this->getSourcePath($file);
		}
		return parent::get($file);
	}

	/**
	 * insert meta data for a new file or folder
	 *
	 * @param string $file
	 * @param array $data
	 *
	 * @return int file id
	 * @throws \RuntimeException
	 */
	public function insert($file, array $data) {
		return $this->cache->insert($this->getSourcePath($file), $data);
	}

	/**
	 * update the metadata in the cache
	 *
	 * @param int $id
	 * @param array $data
	 */
	public function update($id, array $data) {
		$this->cache->update($id, $data);
	}

	/**
	 * get the file id for a file
	 *
	 * @param string $file
	 * @return int
	 */
	public function getId($file) {
		return $this->cache->getId($this->getSourcePath($file));
	}

	/**
	 * get the id of the parent folder of a file
	 *
	 * @param string $file
	 * @return int
	 */
	public function getParentId($file) {
		if ($file === '') {
			return -1;
		} else {
			return $this->cache->getParentId($this->getSourcePath($file));
		}
	}

	/**
	 * check if a file is available in the cache
	 *
	 * @param string $file
	 * @return bool
	 */
	public function inCache($file) {
		return $this->cache->inCache($this->getSourcePath($file));
	}

	/**
	 * remove a file or folder from the cache
	 *
	 * @param string $file
	 */
	public function remove($file) {
		$this->cache->remove($this->getSourcePath($file));
	}

	/**
	 * Move a file or folder in the cache
	 *
	 * @param string $source
	 * @param string $target
	 */
	public function move($source, $target) {
		$this->cache->move($this->getSourcePath($source), $this->getSourcePath($target));
	}

	/**
	 * remove all entries for files that are stored on the storage from the cache
	 */
	public function clear() {
		$this->cache->remove($this->root);
	}

	/**
	 * @param string $file
	 *
	 * @return int Cache::NOT_FOUND, Cache::PARTIAL, Cache::SHALLOW or Cache::COMPLETE
	 */
	public function getStatus($file) {
		return $this->cache->getStatus($this->getSourcePath($file));
	}

	private function formatSearchResults($results) {
		$results = \array_filter($results, [$this, 'filterCacheEntry']);
		$results = \array_values($results);
		return \array_map([$this, 'formatCacheEntry'], $results);
	}

	/**
	 * search for files matching $pattern
	 *
	 * @param string $pattern
	 * @return array an array of file data
	 */
	public function search($pattern) {
		$results = $this->cache->search($pattern);
		return $this->formatSearchResults($results);
	}

	/**
	 * search for files by mimetype
	 *
	 * @param string $mimetype
	 * @return array
	 */
	public function searchByMime($mimetype) {
		$results = $this->cache->searchByMime($mimetype);
		return $this->formatSearchResults($results);
	}

	/**
	 * search for files by mimetype
	 *
	 * @param string|int $tag name or tag id
	 * @param string $userId owner of the tags
	 * @return array
	 */
	public function searchByTag($tag, $userId) {
		$results = $this->cache->searchByTag($tag, $userId);
		return $this->formatSearchResults($results);
	}

	/**
	 * update the folder size and the size of all parent folders
	 *
	 * @param string|boolean $path
	 * @param array $data (optional) meta data of the folder
	 */
	public function correctFolderSize($path, $data = null) {
		if ($this->cache instanceof Cache) {
			$this->cache->correctFolderSize($this->getSourcePath($path), $data);
		}
	}

	/**
	 * get the size of a folder and set it in the cache
	 *
	 * @param string $path
	 * @param array $entry (optional) meta data of the folder
	 * @return int
	 */
	public function calculateFolderSize($path, $entry = null) {
		if ($this->cache instanceof Cache) {
			return $this->cache->calculateFolderSize($this->getSourcePath($path), $entry);
		} else {
			return 0;
		}
	}

	/**
	 * get all file ids on the files on the storage
	 *
	 * @return int[]
	 */
	public function getAll() {
		// not supported
		return [];
	}

	/**
	 * find a folder in the cache which has not been fully scanned
	 *
	 * If multiply incomplete folders are in the cache, the one with the highest id will be returned,
	 * use the one with the highest id gives the best result with the background scanner, since that is most
	 * likely the folder where we stopped scanning previously
	 *
	 * @return string|bool the path of the folder or false when no folder matched
	 */
	public function getIncomplete() {
		// not supported
		return false;
	}

	/**
	 * get the path of a file on this storage by it's id
	 *
	 * @param int $id
	 * @return string|null
	 */
	public function getPathById($id) {
		$path = $this->cache->getPathById($id);
		return $this->getJailedPath($path);
	}

	/**
	 * Move a file or folder in the cache
	 *
	 * Note that this should make sure the entries are removed from the source cache
	 *
	 * @param \OCP\Files\Cache\ICache $sourceCache
	 * @param string $sourcePath
	 * @param string $targetPath
	 */
	public function moveFromCache(\OCP\Files\Cache\ICache $sourceCache, $sourcePath, $targetPath) {
		if ($sourceCache === $this) {
			return $this->move($sourcePath, $targetPath);
		}
		return $this->cache->moveFromCache($sourceCache, $sourcePath, $this->getSourcePath($targetPath));
	}

	protected function getMoveInfo($path) {
		return [$this->getNumericStorageId(), $this->getSourcePath($path)];
	}
}
