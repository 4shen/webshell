<?php

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
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

namespace OC\Files\Stream;

use Icewind\Streams\Wrapper;
use OC\Cache\CappedMemoryCache;

/**
 * Computes the checksum of the wrapped stream. The checksum can be retrieved with
 * getChecksum after the stream is closed.
 *
 *
 * @package OC\Files\Stream
 */
class Checksum extends Wrapper {

	/**
	 * To stepwise compute a hash on a continuous stream
	 * of data a "context" is required which stores the intermediate
	 * hash result while the stream has not finished.
	 *
	 * After the stream ends the hashing contexts needs to be finalized
	 * to compute the final checksum.
	 *
	 * @var  resource[]
	 */
	private $hashingContexts;

	/** @var CappedMemoryCache Key is path, value is array of checksums */
	private static $checksums;

	public function __construct(array $algos = ['sha1', 'md5', 'adler32']) {
		foreach ($algos as $algo) {
			$this->hashingContexts[$algo] = \hash_init($algo);
		}

		if (!self::$checksums) {
			self::$checksums = new CappedMemoryCache();
		}
	}

	/**
	 * @param $source
	 * @param $path
	 * @return resource
	 */
	public static function wrap($source, $path) {
		$context = \stream_context_create([
			'occhecksum' => [
				'source' => $source,
				'path' => $path
			]
		]);

		return Wrapper::wrapSource(
			$source, $context, 'occhecksum', self::class
		);
	}

	/**
	 * @param string $path
	 * @param array $options
	 * @return bool
	 */
	public function dir_opendir($path, $options) {
		return true;
	}

	/**
	 * @param string $path
	 * @param string $mode
	 * @param int $options
	 * @param string $opened_path
	 * @return bool
	 */
	public function stream_open($path, $mode, $options, &$opened_path) {
		$context = parent::loadContext('occhecksum');
		$this->setSourceStream($context['source']);

		return true;
	}

	/**
	 * @param int $count
	 * @return string
	 */
	public function stream_read($count) {
		$data = parent::stream_read($count);
		$this->updateHashingContexts($data);

		return $data;
	}

	/**
	 * @param string $data
	 * @return int
	 */
	public function stream_write($data) {
		$this->updateHashingContexts($data);

		return parent::stream_write($data);
	}

	private function updateHashingContexts($data) {
		foreach ($this->hashingContexts as $ctx) {
			\hash_update($ctx, $data);
		}
	}

	/**
	 * Remove .part extension from a file path
	 * @param string $path Path that may identify a .part file
	 * @return string File path without .part extension
	 */
	private function stripPartialFileExtension($path) {
		$extension = \pathinfo($path, PATHINFO_EXTENSION);

		if ($extension === 'part') {
			$newLength = \strlen($path) - 5; // 5 = strlen(".part")
			$fPath = \substr($path, 0, $newLength);

			// if path also contains a transaction id, we remove it too
			$extension = \pathinfo($fPath, PATHINFO_EXTENSION);
			if (\substr($extension, 0, 12) === 'ocTransferId') { // 12 = strlen("ocTransferId")
				$newLength = \strlen($fPath) - \strlen($extension) -1;
				$fPath = \substr($fPath, 0, $newLength);
			}
			return $fPath;
		} else {
			return $path;
		}
	}

	/**
	 * Make checksums available for part files and the original file for which part file has been created
	 * @return bool
	 */
	public function stream_close() {
		$currentPath = $this->getPathFromStreamContext();
		$checksum = $this->finalizeHashingContexts();
		self::$checksums[$currentPath] = $checksum;

		// If current path belongs to part file, save checksum for original file
		// As a result, call to getChecksums for original file (of this part file) will
		// fetch checksum from cache
		$originalFilePath = $this->stripPartialFileExtension($currentPath);
		if ($originalFilePath !== $currentPath) {
			self::$checksums[$originalFilePath] = $checksum;
		}

		return parent::stream_close();
	}

	/**
	 * @return array
	 */
	private function finalizeHashingContexts() {
		$hashes = [];

		foreach ($this->hashingContexts as $algo => $ctx) {
			$hashes[$algo] = \hash_final($ctx);
		}

		return $hashes;
	}

	public function dir_closedir() {
		if (!isset($this->source)) {
			return false;
		}
		return parent::dir_closedir();
	}

	/**
	 * @return mixed
	 * @return string
	 */
	private function getPathFromStreamContext() {
		$ctx = \stream_context_get_options($this->context);

		return $ctx['occhecksum']['path'];
	}

	/**
	 * @param $path
	 * @return array
	 */
	public static function getChecksums($path) {
		if (!isset(self::$checksums[$path])) {
			return [];
		}

		return self::$checksums[$path];
	}

	/**
	 * For debugging
	 *
	 * @return CappedMemoryCache
	 */
	public static function getChecksumsForAllPaths() {
		if (!self::$checksums) {
			self::$checksums = new CappedMemoryCache();
		}

		return self::$checksums;
	}
}
