<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OC\Cache;

use OCP\ICache;

/**
 * In-memory cache with a capacity limit to keep memory usage in check
 *
 * Uses a simple FIFO expiry mechanism
 */
class CappedMemoryCache implements ICache, \ArrayAccess {
	private $capacity;
	private $cache = [];

	public function __construct($capacity = 512) {
		$this->capacity = $capacity;
	}

	public function hasKey($key) {
		return isset($this->cache[$key]);
	}

	public function get($key) {
		return isset($this->cache[$key]) ? $this->cache[$key] : null;
	}

	public function set($key, $value, $ttl = 0) {
		$this->cache[$key] = $value;
		$this->garbageCollect();
	}

	public function remove($key) {
		unset($this->cache[$key]);
		return true;
	}

	public function clear($prefix = '') {
		$this->cache = [];
		return true;
	}

	public function offsetExists($offset) {
		return $this->hasKey($offset);
	}

	public function &offsetGet($offset) {
		return $this->cache[$offset];
	}

	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}

	public function offsetUnset($offset) {
		$this->remove($offset);
	}

	private function garbageCollect() {
		while (\count($this->cache) > $this->capacity) {
			\reset($this->cache);
			$key = \key($this->cache);
			$this->remove($key);
		}
	}
}
