<?php
/**
 * @author Andreas Fischer <bantu@owncloud.com>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files_Sharing\ShareBackend;

use OCA\FederatedFileSharing\FederatedShareProvider;

class File implements \OCP\Share_Backend_File_Dependent {
	const FORMAT_SHARED_STORAGE = 0;
	const FORMAT_GET_FOLDER_CONTENTS = 1;
	const FORMAT_FILE_APP_ROOT = 2;
	const FORMAT_OPENDIR = 3;
	const FORMAT_GET_ALL = 4;
	const FORMAT_PERMISSIONS = 5;
	const FORMAT_TARGET_NAMES = 6;

	private $path;

	/** @var FederatedShareProvider */
	private $federatedShareProvider;

	public function __construct(FederatedShareProvider $federatedShareProvider = null) {
		if ($federatedShareProvider) {
			$this->federatedShareProvider = $federatedShareProvider;
		} else {
			$federatedSharingApp = new \OCA\FederatedFileSharing\AppInfo\Application();
			$this->federatedShareProvider = $federatedSharingApp->getFederatedShareProvider();
		}
	}

	public function isValidSource($itemSource, $uidOwner) {
		try {
			$path = \OC\Files\Filesystem::getPath($itemSource);
			// FIXME: attributes should not be set here,
			// keeping this pattern for now to avoid unexpected
			// regressions
			$this->path = \OC\Files\Filesystem::normalizePath(\basename($path));
			return true;
		} catch (\OCP\Files\NotFoundException $e) {
			return false;
		}
	}

	public function getFilePath($itemSource, $uidOwner) {
		if (isset($this->path)) {
			$path = $this->path;
			$this->path = null;
			return $path;
		} else {
			try {
				$path = \OC\Files\Filesystem::getPath($itemSource);
				return $path;
			} catch (\OCP\Files\NotFoundException $e) {
				return false;
			}
		}
	}

	/**
	 * create unique target
	 * @param string $filePath
	 * @param string $shareWith
	 * @param array $exclude (optional)
	 * @return string
	 */
	public function generateTarget($filePath, $shareWith, $exclude = null) {
		$shareFolder = \OCA\Files_Sharing\Helper::getShareFolder();
		$target = \OC\Files\Filesystem::normalizePath($shareFolder . '/' . \basename($filePath));

		// for group shares we return the target right away
		if ($shareWith === false) {
			return $target;
		}

		\OC\Files\Filesystem::initMountPoints($shareWith);
		$view = new \OC\Files\View('/' . $shareWith . '/files');

		if (!$view->is_dir($shareFolder)) {
			$dir = '';
			$subdirs = \explode('/', $shareFolder);
			foreach ($subdirs as $subdir) {
				$dir = $dir . '/' . $subdir;
				if (!$view->is_dir($dir)) {
					$view->mkdir($dir);
				}
			}
		}

		$excludeList = (\is_array($exclude)) ? $exclude : [];

		return \OCA\Files_Sharing\Helper::generateUniqueTarget($target, $excludeList, $view);
	}

	public function formatItems($items, $format, $parameters = null) {
		if ($format == self::FORMAT_SHARED_STORAGE) {
			// Only 1 item should come through for this format call
			$item = \array_shift($items);
			return [
				'parent' => $item['parent'],
				'path' => $item['path'],
				'storage' => $item['storage'],
				'permissions' => $item['permissions'],
				'uid_owner' => $item['uid_owner'],
			];
		} elseif ($format == self::FORMAT_GET_FOLDER_CONTENTS) {
			$files = [];
			foreach ($items as $item) {
				$file = [];
				$file['fileid'] = $item['file_source'];
				$file['storage'] = $item['storage'];
				$file['path'] = $item['file_target'];
				$file['parent'] = $item['file_parent'];
				$file['name'] = \basename($item['file_target']);
				$file['mimetype'] = $item['mimetype'];
				$file['mimepart'] = $item['mimepart'];
				$file['mtime'] = $item['mtime'];
				$file['encrypted'] = $item['encrypted'];
				$file['etag'] = $item['etag'];
				$file['uid_owner'] = $item['uid_owner'];
				$file['displayname_owner'] = $item['displayname_owner'];

				$storage = \OC\Files\Filesystem::getStorage('/');
				$cache = $storage->getCache();
				$file['size'] = $item['size'];
				$files[] = $file;
			}
			return $files;
		} elseif ($format == self::FORMAT_OPENDIR) {
			$files = [];
			foreach ($items as $item) {
				$files[] = \basename($item['file_target']);
			}
			return $files;
		} elseif ($format == self::FORMAT_GET_ALL) {
			$ids = [];
			foreach ($items as $item) {
				$ids[] = $item['file_source'];
			}
			return $ids;
		} elseif ($format === self::FORMAT_PERMISSIONS) {
			$filePermissions = [];
			foreach ($items as $item) {
				$filePermissions[$item['file_source']] = $item['permissions'];
			}
			return $filePermissions;
		} elseif ($format === self::FORMAT_TARGET_NAMES) {
			$targets = [];
			foreach ($items as $item) {
				$targets[] = $item['file_target'];
			}
			return $targets;
		}
		return [];
	}

	/**
	 * check if server2server share is enabled
	 *
	 * @param int $shareType
	 * @return boolean
	 */
	public function isShareTypeAllowed($shareType) {
		if ($shareType === \OCP\Share::SHARE_TYPE_REMOTE) {
			return $this->federatedShareProvider->isOutgoingServer2serverShareEnabled();
		}

		return true;
	}

	/**
	 * resolve reshares to return the correct source item
	 * @param array $source
	 * @return array source item
	 */
	protected static function resolveReshares($source) {
		if (isset($source['parent'])) {
			$parent = $source['parent'];
			while (isset($parent)) {
				$query = \OCP\DB::prepare('SELECT `parent`, `uid_owner` FROM `*PREFIX*share` WHERE `id` = ?', 1);
				$item = $query->execute([$parent])->fetchRow();
				if (isset($item['parent'])) {
					$parent = $item['parent'];
				} else {
					$fileOwner = $item['uid_owner'];
					break;
				}
			}
		} else {
			$fileOwner = $source['uid_owner'];
		}
		if (isset($fileOwner)) {
			$source['fileOwner'] = $fileOwner;
		} else {
			\OCP\Util::writeLog('files_sharing', "No owner found for reshare", \OCP\Util::ERROR);
		}

		return $source;
	}

	/**
	 * @param string $target
	 * @param array $share
	 * @return array|false source item
	 */
	public static function getSource($target, $share) {
		if ($share['item_type'] === 'folder' && $target !== '') {
			// note: in case of ext storage mount points the path might be empty
			// which would cause a leading slash to appear
			$share['path'] = \ltrim($share['path'] . '/' . $target, '/');
		}
		return self::resolveReshares($share);
	}
}
