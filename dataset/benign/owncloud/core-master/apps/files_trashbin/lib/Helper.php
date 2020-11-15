<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Robin Appelman <icewind@owncloud.com>
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
namespace OCA\Files_Trashbin;

use OC\Files\FileInfo;
use OCP\Constants;
use OCP\Files\Cache\ICacheEntry;

class Helper {
	/**
	 * Retrieves the contents of a trash bin directory.
	 *
	 * @param string $dir path to the directory inside the trashbin
	 * or empty to retrieve the root of the trashbin
	 * @param string $user
	 * @param string $sortAttribute attribute to sort on or empty to disable sorting
	 * @param bool $sortDescending true for descending sort, false otherwise
	 * @param bool $addExtraData if true, file info will include original path
	 * @return \OCP\Files\FileInfo[]
	 */
	public static function getTrashFiles($dir, $user, $sortAttribute = '', $sortDescending = false, $addExtraData = true) {
		$result = [];
		$timestamp = null;

		$view = new \OC\Files\View('/' . $user . '/files_trashbin/files');

		if (\ltrim($dir, '/') !== '' && !$view->is_dir($dir)) {
			throw new \Exception('Directory does not exists');
		}

		$mount = $view->getMount($dir);
		$storage = $mount->getStorage();
		$absoluteDir = $view->getAbsolutePath($dir);
		$internalPath = $mount->getInternalPath($absoluteDir);

		$originalLocationsCache = null;
		$dirContent = $storage->getCache()->getFolderContents($mount->getInternalPath($view->getAbsolutePath($dir)));
		foreach ($dirContent as $entry) {
			// construct base fileinfo entries
			$entryName = $entry->getName();
			$id = $entry->getId();
			$name = $entryName;
			if ($dir === '' || $dir === '/') {
				$pathparts = \pathinfo($entryName);
				$timestamp = \substr($pathparts['extension'], 1);
				$name = $pathparts['filename'];
			} elseif ($timestamp === null) {
				// for subfolders we need to calculate the timestamp only once
				$parts = \explode('/', \ltrim($dir, '/'));
				$timestamp = \substr(\pathinfo($parts[0], PATHINFO_EXTENSION), 1);
			}

			$i = [
				'name' => $name,
				'mtime' => $timestamp,
				'mimetype' => $entry->getMimeType(),
				'type' => $entry->getMimeType() === ICacheEntry::DIRECTORY_MIMETYPE ? 'dir' : 'file',
				'directory' => ($dir === '/') ? '' : $dir,
				'size' => $entry->getSize(),
				'etag' => '',
				'permissions' => Constants::PERMISSION_ALL - Constants::PERMISSION_SHARE
			];

			// if original file path of the trashed file required, add in extraData
			if ($addExtraData) {
				if (!$originalLocationsCache) {
					$originalLocationsCache = \OCA\Files_Trashbin\Trashbin::getLocations($user);
				}

				$originalName = \substr($entryName, 0, -\strlen($timestamp)-2);
				if (isset($originalLocationsCache[$originalName][$timestamp])) {
					$originalPath = $originalLocationsCache[$originalName][$timestamp];
					if (\substr($originalPath, -1) === '/') {
						$originalPath = \substr($originalPath, 0, -1);
					}

					$i['extraData'] = $originalPath . '/' . $originalName;
				}
			}

			// set FileInfo object on the constructed fileinfo array
			$result[] = new FileInfo($absoluteDir . '/' . $i['name'], $storage, $internalPath . '/' . $i['name'], $i, $mount);
		}

		// if sorting enabled use selected sorting algorithm
		if ($sortAttribute !== '') {
			return \OCA\Files\Helper::sortFiles($result, $sortAttribute, $sortDescending);
		}

		return $result;
	}

	/**
	 * Format file infos for JSON
	 *
	 * @param \OCP\Files\FileInfo[] $fileInfos file infos
	 */
	public static function formatFileInfos($fileInfos) {
		$files = [];
		$id = 0;
		foreach ($fileInfos as $i) {
			$entry = \OCA\Files\Helper::formatFileInfo($i);
			$entry['id'] = $id++;
			$entry['etag'] = $entry['mtime']; // add fake etag, it is only needed to identify the preview image
			$entry['permissions'] = \OCP\Constants::PERMISSION_READ;
			if ($entry['mimetype'] !== 'httpd/unix-directory') {
				$entry['mimetype'] = \OC::$server->getMimeTypeDetector()->detectPath($entry['name']);
			}
			$files[] = $entry;
		}
		return $files;
	}
}
