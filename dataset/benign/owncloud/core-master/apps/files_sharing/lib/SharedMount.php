<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
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

use OC\Files\Filesystem;
use OC\Files\Mount\MountPoint;
use OC\Files\Mount\MoveableMount;
use OC\Files\View;

/**
 * Shared mount points can be moved by the user
 */
class SharedMount extends MountPoint implements MoveableMount {
	/**
	 * @var \OCA\Files_Sharing\SharedStorage $storage
	 */
	protected $storage = null;

	/**
	 * @var \OC\Files\View
	 */
	private $recipientView;

	/**
	 * @var string
	 */
	private $user;

	/** @var \OCP\Share\IShare */
	private $superShare;

	/** @var \OCP\Share\IShare[] */
	private $groupedShares;

	/**
	 * @param string $storage
	 * @param SharedMount[] $mountpoints
	 * @param array|null $arguments
	 * @param \OCP\Files\Storage\IStorageFactory $loader
	 */
	public function __construct($storage, array $mountpoints, $arguments = null, $loader = null) {
		$this->user = $arguments['user'];
		$this->recipientView = new View('/' . $this->user . '/files');

		$this->superShare = $arguments['superShare'];
		$this->groupedShares = $arguments['groupedShares'];

		$newMountPoint = $this->verifyMountPoint($this->superShare, $mountpoints);
		$absMountPoint = '/' . $this->user . '/files' . $newMountPoint;
		$arguments['ownerView'] = new View('/' . $this->superShare->getShareOwner() . '/files');
		parent::__construct($storage, $absMountPoint, $arguments, $loader);
	}

	/**
	 * check if the parent folder exists otherwise move the mount point up
	 *
	 * @param \OCP\Share\IShare $share
	 * @param SharedMount[] $mountpoints
	 * @return string
	 */
	private function verifyMountPoint(\OCP\Share\IShare $share, array $mountpoints) {
		$mountPoint = \basename($share->getTarget());
		$parent = \dirname($share->getTarget());

		if (!$this->recipientView->is_dir($parent)) {
			$parent = Helper::getShareFolder($this->recipientView);
		}

		$newMountPoint = $this->generateUniqueTarget(
			\OC\Files\Filesystem::normalizePath($parent . '/' . $mountPoint),
			$this->recipientView,
			$mountpoints
		);

		if ($newMountPoint !== $share->getTarget()) {
			$this->updateFileTarget($newMountPoint, $share);
		}

		return $newMountPoint;
	}

	/**
	 * update fileTarget in the database if the mount point changed
	 *
	 * @param string $newPath
	 * @param \OCP\Share\IShare $share
	 * @return bool
	 */
	private function updateFileTarget($newPath, &$share) {
		$share->setTarget($newPath);

		foreach ($this->groupedShares as $share) {
			$share->setTarget($newPath);
			\OC::$server->getShareManager()->moveShare($share, $this->user);
		}
	}

	/**
	 * @param string $path
	 * @param View $view
	 * @param SharedMount[] $mountpoints
	 * @return mixed
	 */
	private function generateUniqueTarget($path, $view, array $mountpoints) {
		$pathinfo = \pathinfo($path);
		$ext = (isset($pathinfo['extension'])) ? '.'.$pathinfo['extension'] : '';
		$name = $pathinfo['filename'];
		$dir = $pathinfo['dirname'];

		// Helper function to find existing mount points
		$mountpointExists = function ($path) use ($mountpoints) {
			foreach ($mountpoints as $mountpoint) {
				if ($mountpoint->getShare()->getTarget() === $path) {
					return true;
				}
			}
			return false;
		};

		$i = 2;
		while ($view->file_exists($path) || $mountpointExists($path)) {
			$path = Filesystem::normalizePath($dir . '/' . $name . ' ('.$i.')' . $ext);
			$i++;
		}

		return $path;
	}

	/**
	 * Format a path to be relative to the /user/files/ directory
	 *
	 * @param string $path the absolute path
	 * @return string e.g. turns '/admin/files/test.txt' into '/test.txt'
	 * @throws \OCA\Files_Sharing\Exceptions\BrokenPath
	 */
	protected function stripUserFilesPath($path) {
		$trimmed = \ltrim($path, '/');
		$split = \explode('/', $trimmed);

		// it is not a file relative to data/user/files
		if (\count($split) < 3 || $split[1] !== 'files') {
			\OCP\Util::writeLog('files_sharing',
				'Can not strip userid and "files/" from path: ' . $path,
				\OCP\Util::ERROR);
			throw new \OCA\Files_Sharing\Exceptions\BrokenPath('Path does not start with /user/files', 10);
		}

		// skip 'user' and 'files'
		$sliced = \array_slice($split, 2);
		$relPath = \implode('/', $sliced);

		return '/' . $relPath;
	}

	/**
	 * Check whether it is allowed to move a mount point to a given target.
	 * It is not allowed to move a mount point into a different mount point or
	 * into an already shared folder
	 *
	 * @param string $target absolute target path
	 * @return bool true if allowed, false otherwise
	 */
	public function isTargetAllowed($target) {
		list($targetStorage, $targetInternalPath) = \OC\Files\Filesystem::resolvePath($target);
		// note: cannot use the view because the target is already locked
		$fileId = (int)$targetStorage->getCache()->getId($targetInternalPath);
		if ($fileId === -1) {
			// target might not exist, need to check parent instead
			$fileId = (int)$targetStorage->getCache()->getId(\dirname($targetInternalPath));
		}

		$targetNodes = \OC::$server->getRootFolder()->getById($fileId, true);
		$targetNode = $targetNodes[0] ?? null;
		if ($targetNode === null) {
			return false;
		}

		$shareManager = \OC::$server->getShareManager();
		// FIXME: make it stop earlier in '/$userId/files'
		while ($targetNode !== null && $targetNode->getPath() !== '/') {
			'@phan-var \OC\Share20\Manager $shareManager';
			$shares = $shareManager->getSharesByPath($targetNode);

			foreach ($shares as $share) {
				if ($this->user === $share->getShareOwner()) {
					\OCP\Util::writeLog('files_sharing',
						'It is not allowed to move one mount point into a shared folder',
						\OCP\Util::DEBUG);
					return false;
				}
			}

			$targetNode = $targetNode->getParent();
		}

		return true;
	}

	/**
	 * Move the mount point to $target
	 *
	 * @param string $target the target mount point
	 * @return bool
	 */
	public function moveMount($target) {
		if (!$this->isTargetAllowed($target)) {
			return false;
		}

		$relTargetPath = $this->stripUserFilesPath($target);
		/* @phan-suppress-next-line PhanUndeclaredMethod */
		$share = $this->getStorage()->getShare();

		$result = true;

		try {
			$this->updateFileTarget($relTargetPath, $share);
			$this->setMountPoint($target);
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$this->getStorage()->setMountPoint($relTargetPath);
		} catch (\Exception $e) {
			\OCP\Util::writeLog('files_sharing',
				'Could not rename mount point for shared folder "' . $this->getMountPoint() . '" to "' . $target . '"',
				\OCP\Util::ERROR);
		}

		return $result;
	}

	/**
	 * Remove the mount points
	 *
	 * @return bool
	 */
	public function removeMount() {
		$mountManager = \OC\Files\Filesystem::getMountManager();
		/** @var $storage \OCA\Files_Sharing\SharedStorage */
		$storage = $this->getStorage();
		'@phan-var \OCA\Files_Sharing\SharedStorage $storage';
		$result = $storage->unshareStorage();
		$mountManager->removeMount($this->mountPoint);

		return $result;
	}

	/**
	 * @return \OCP\Share\IShare
	 */
	public function getShare() {
		return $this->superShare;
	}

	/**
	 * Get the file id of the root of the storage
	 *
	 * @return int
	 */
	public function getStorageRootId() {
		return $this->getShare()->getNodeId();
	}

	public function getStorageNumericId() {
		$query = \OC::$server->getDatabaseConnection()->getQueryBuilder();
		$query->select('storage')
			->from('filecache')
			->where($query->expr()->eq('fileid', $query->createNamedParameter($this->getStorageRootId())));

		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();
		if ($row) {
			return $row['storage'];
		}
		return -1;
	}
}
