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
namespace OC\Files\Storage\Wrapper;

use Icewind\Streams\CallbackWrapper;
use Doctrine\DBAL\Exception\DriverException;
use OC\Files\Stream\Checksum as ChecksumStream;
use OCP\Files\IHomeStorage;

/**
 * Class Checksum
 *
 * Computes checksums (default: SHA1, MD5, ADLER32) on all files under the /files path.
 * The resulting checksum can be retrieved by call getMetadata($path)
 *
 * If a file is read and has no checksum oc_filecache gets updated accordingly.
 *
 *
 * @package OC\Files\Storage\Wrapper
 */
class Checksum extends Wrapper {

	/** Format of checksum field in filecache */
	const CHECKSUMS_DB_FORMAT = 'SHA1:%s MD5:%s ADLER32:%s';

	const NOT_REQUIRED = 0;
	/** Calculate checksum on write (to be stored in oc_filecache) */
	const PATH_NEW_OR_UPDATED = 1;
	/** File needs to be checksummed on first read because it is already in cache but has no checksum */
	const PATH_IN_CACHE_WITHOUT_CHECKSUM = 2;

	/** @var array */
	private $pathsInCacheWithoutChecksum = [];

	/**
	 * @param string $path
	 * @param string $mode
	 * @return false|resource
	 */
	public function fopen($path, $mode) {
		$stream = $this->getWrapperStorage()->fopen($path, $mode);
		if (!\is_resource($stream) || $this->isReadWriteStream($mode)) {
			// don't wrap on error or mixed mode streams (could cause checksum corruption)
			return $stream;
		}

		$requirement = $this->getChecksumRequirement($path, $mode);

		if ($requirement === self::PATH_NEW_OR_UPDATED) {
			return \OC\Files\Stream\Checksum::wrap($stream, $path);
		}

		// If file is without checksum we save the path and create
		// a callback because we can only calculate the checksum
		// after the client has read the entire filestream once.
		// the checksum is then saved to oc_filecache for subsequent
		// retrieval (see onClose())
		if ($requirement == self::PATH_IN_CACHE_WITHOUT_CHECKSUM) {
			$checksumStream = \OC\Files\Stream\Checksum::wrap($stream, $path);
			return CallbackWrapper::wrap(
				$checksumStream,
				null,
				null,
				[$this, 'onClose']
			);
		}

		return $stream;
	}

	/**
	 * @param $mode
	 * @param $path
	 * @return int
	 */
	private function getChecksumRequirement($path, $mode) {
		$isNormalFile = true;
		if ($this->instanceOfStorage(IHomeStorage::class)) {
			// home storage stores files in "files"
			$isNormalFile = \substr($path, 0, 6) === 'files/';
		}
		$fileIsWritten = $mode !== 'r' && $mode !== 'rb';

		if ($isNormalFile && $fileIsWritten) {
			return self::PATH_NEW_OR_UPDATED;
		}

		// file could be in cache but without checksum for example
		// if mounted from ext. storage
		$cache = $this->getCache($path);

		$cacheEntry = $cache->get($path);

		// Cache entry is sometimes an array (partial) when encryption is enabled without id so
		// we ignore it.
		if ($cacheEntry && empty($cacheEntry['checksum']) && \is_object($cacheEntry)) {
			$this->pathsInCacheWithoutChecksum[$cacheEntry->getId()] = $path;
			return self::PATH_IN_CACHE_WITHOUT_CHECKSUM;
		}

		return self::NOT_REQUIRED;
	}

	/**
	 * @param $mode
	 * @return bool
	 */
	private function isReadWriteStream($mode) {
		return \strpos($mode, '+') !== false;
	}

	/**
	 * Callback registered in fopen
	 */
	public function onClose() {
		$cache = $this->getCache();
		try {
			foreach ($this->pathsInCacheWithoutChecksum as $cacheId => $path) {
				$cache->update(
					$cacheId,
					['checksum' => self::getChecksumsInDbFormat($path)]
				);
			}

			$this->pathsInCacheWithoutChecksum = [];
		} catch (DriverException $ex) {
			\OC::$server->getLogger()->error($ex->getMessage(), ['app' => 'checksum']);
		}
	}

	/**
	 * @param $path
	 * @return string Format like "SHA1:abc MD5:def ADLER32:ghi"
	 */
	private static function getChecksumsInDbFormat($path) {
		$checksums = ChecksumStream::getChecksums($path);

		if (empty($checksums)) {
			return '';
		}

		return \sprintf(
			self::CHECKSUMS_DB_FORMAT,
			$checksums['sha1'],
			$checksums['md5'],
			$checksums['adler32']
		);
	}

	/**
	 * check if the file metadata should not be fetched
	 * NOTE: files with a '.part' extension are ignored as well!
	 *       prevents unfinished put requests to fetch metadata which does not exists
	 *
	 * @param string $file
	 * @return boolean
	 */
	public static function isPartialFile($file) {
		if (\pathinfo($file, PATHINFO_EXTENSION) === 'part') {
			return true;
		}

		return false;
	}

	/**
	 * @param string $path
	 * @param string $data
	 * @return bool
	 */
	public function file_put_contents($path, $data) {
		$memoryStream = \fopen('php://memory', 'r+');
		$checksumStream = \OC\Files\Stream\Checksum::wrap($memoryStream, $path);

		\fwrite($checksumStream, $data);
		\fclose($checksumStream);

		return $this->getWrapperStorage()->file_put_contents($path, $data);
	}

	/**
	 * @param string $path
	 * @return array
	 */
	public function getMetaData($path) {
		// Check if it is partial file. Partial file metadata are only checksums
		$parentMetaData = [];
		if (!self::isPartialFile($path)) {
			$parentMetaData = $this->getWrapperStorage()->getMetaData($path);
			// can be null if entry does not exist
			if ($parentMetaData === null) {
				return null;
			}
		}
		$parentMetaData['checksum'] = self::getChecksumsInDbFormat($path);

		if (!isset($parentMetaData['mimetype'])) {
			$parentMetaData['mimetype'] = 'application/octet-stream';
		}

		return $parentMetaData;
	}
}
