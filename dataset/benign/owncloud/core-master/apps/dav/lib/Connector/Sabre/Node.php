<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Jakob Sack <mail@jakobsack.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Klaas Freitag <freitag@owncloud.com>
 * @author Markus Goetz <markus@woboq.com>
 * @author Martin Mattel <martin.mattel@diemattels.at>
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

namespace OCA\DAV\Connector\Sabre;

use OC\Files\Mount\MoveableMount;
use OC\Lock\Persistent\Lock;
use OCA\DAV\Connector\Sabre\Exception\Forbidden;
use OCA\DAV\Connector\Sabre\Exception\InvalidPath;
use OCP\Files\ForbiddenException;
use OCP\Files\Storage\IPersistentLockingStorage;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager;

abstract class Node implements \Sabre\DAV\INode {

	/**
	 * @var \OC\Files\View
	 */
	protected $fileView;

	/**
	 * The path to the current node
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * node properties cache
	 *
	 * @var array
	 */
	protected $property_cache = null;

	/**
	 * @var \OCP\Files\FileInfo
	 */
	protected $info;

	/**
	 * @var IManager
	 */
	protected $shareManager;

	/**
	 * Sets up the node, expects a full path name
	 *
	 * @param \OC\Files\View $view
	 * @param \OCP\Files\FileInfo $info
	 * @param IManager $shareManager
	 */
	public function __construct($view, $info, IManager $shareManager = null) {
		$this->fileView = $view;
		$this->path = $this->fileView->getRelativePath($info->getPath());
		$this->info = $info;
		if ($shareManager) {
			$this->shareManager = $shareManager;
		} else {
			$this->shareManager = \OC::$server->getShareManager();
		}
	}

	protected function refreshInfo() {
		$this->info = $this->fileView->getFileInfo($this->path);
	}

	/**
	 *  Returns the name of the node
	 *
	 * @return string
	 */
	public function getName() {
		return $this->info->getName();
	}

	/**
	 * Returns the full path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @throws \Sabre\DAV\Exception\BadRequest
	 * @throws \Sabre\DAV\Exception\Forbidden
	 * @throws InvalidPath
	 */
	public function setName($name) {

		// rename is only allowed if the update privilege is granted
		if (!$this->info->isUpdateable()) {
			throw new \Sabre\DAV\Exception\Forbidden();
		}

		// verify path of the source
		$this->verifyPath();

		list($parentPath, ) = \Sabre\Uri\split($this->path);
		list(, $newName) = \Sabre\Uri\split($name);

		// verify path of target
		if (\OC\Files\Filesystem::isForbiddenFileOrDir($parentPath . '/' . $newName)) {
			throw new \Sabre\DAV\Exception\Forbidden();
		}

		try {
			$this->fileView->verifyPath($parentPath, $newName);
		} catch (\OCP\Files\InvalidPathException $ex) {
			throw new InvalidPath($ex->getMessage());
		}

		$newPath = $parentPath . '/' . $newName;

		try {
			$result = $this->fileView->rename($this->path, $newPath);
			if ($result === false) {
				throw new Forbidden('Rename operation failed');
			}
		} catch (ForbiddenException $ex) {
			throw new Forbidden($ex->getMessage(), $ex->getRetry());
		}

		$this->path = $newPath;

		$this->refreshInfo();
	}

	public function setPropertyCache($property_cache) {
		$this->property_cache = $property_cache;
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int timestamp as integer
	 */
	public function getLastModified() {
		$timestamp = $this->info->getMtime();
		if (!empty($timestamp)) {
			return (int)$timestamp;
		}
		return $timestamp;
	}

	/**
	 *  sets the last modification time of the file (mtime) to the value given
	 *  in the second parameter or to now if the second param is empty.
	 *  Even if the modification time is set to a custom value the access time is set to now.
	 */
	public function touch($mtime) {
		$mtime = $this->sanitizeMtime($mtime);
		$this->fileView->touch($this->path, $mtime);
		$this->refreshInfo();
	}

	/**
	 * Returns the ETag for a file
	 *
	 * An ETag is a unique identifier representing the current version of the
	 * file. If the file changes, the ETag MUST change.  The ETag is an
	 * arbitrary string, but MUST be surrounded by double-quotes.
	 *
	 * Return null if the ETag can not effectively be determined
	 *
	 * @return string
	 */
	public function getETag() {
		return '"' . $this->info->getEtag() . '"';
	}

	/**
	 * Sets the ETag
	 *
	 * @param string $etag
	 *
	 * @return int file id of updated file or -1 on failure
	 */
	public function setETag($etag) {
		return $this->fileView->putFileInfo($this->path, ['etag' => $etag]);
	}

	/**
	 * Returns the size of the node, in bytes, or null if the size is unknown
	 * (GoogleDrive documents returns \OCP\Files\FileInfo::SPACE_UNKNOWN for their
	 * size because the actual size value isn't returned by google. This function
	 * will return null in this case)
	 *
	 * @return integer|null
	 */
	public function getSize() {
		$size = $this->info->getSize();
		if ($size < 0) {
			return null;
		} else {
			return $size;
		}
	}

	/**
	 * Returns the cache's file id
	 *
	 * @return int
	 */
	public function getId() {
		return $this->info->getId();
	}

	/**
	 * @return string|null
	 */
	public function getFileId() {
		if ($this->info->getId()) {
			$instanceId = \OC_Util::getInstanceId();
			$id = \sprintf('%08d', $this->info->getId());
			return $id . $instanceId;
		}

		return null;
	}

	/**
	 * @return integer
	 */
	public function getInternalFileId() {
		return $this->info->getId();
	}

	/**
	 * @param string $user
	 * @return int
	 */
	public function getSharePermissions($user) {

		// check of we access a federated share
		if ($user !== null) {
			try {
				$share = $this->shareManager->getShareByToken($user);
				return $share->getPermissions();
			} catch (ShareNotFound $e) {
				// ignore
			}
		}

		$storage = $this->info->getStorage();

		$path = $this->info->getInternalPath();

		if ($storage->instanceOfStorage('\OCA\Files_Sharing\SharedStorage')) {
			/** @var \OCA\Files_Sharing\SharedStorage $storage */
			'@phan-var \OCA\Files_Sharing\SharedStorage $storage';
			$permissions = (int)$storage->getShare()->getPermissions();
		} else {
			$permissions = $storage->getPermissions($path);
		}

		/*
		 * We can always share non moveable mount points with DELETE and UPDATE
		 * Eventually we need to do this properly
		 */
		$mountpoint = $this->info->getMountPoint();
		if (!($mountpoint instanceof MoveableMount)) {
			$mountpointpath = $mountpoint->getMountPoint();
			if (\substr($mountpointpath, -1) === '/') {
				$mountpointpath = \substr($mountpointpath, 0, -1);
			}

			if ($mountpointpath === $this->info->getPath()) {
				$permissions |= \OCP\Constants::PERMISSION_DELETE | \OCP\Constants::PERMISSION_UPDATE;
			}
		}

		/*
		 * Files can't have create or delete permissions
		 */
		if ($this->info->getType() === \OCP\Files\FileInfo::TYPE_FILE) {
			$permissions &= ~(\OCP\Constants::PERMISSION_CREATE | \OCP\Constants::PERMISSION_DELETE);
		}

		return $permissions;
	}

	/**
	 * @return string
	 */
	public function getDavPermissions() {
		$p = '';
		if ($this->info->isShared()) {
			$p .= 'S';
		}
		if ($this->info->isShareable()) {
			$p .= 'R';
		}
		if ($this->info->isMounted()) {
			$p .= 'M';
		}
		if ($this->info->isDeletable()) {
			$p .= 'D';
		}
		if ($this->info->isUpdateable()) {
			$p .= 'NV'; // Renameable, Moveable
		}
		if ($this->info->getType() === \OCP\Files\FileInfo::TYPE_FILE) {
			if ($this->info->isUpdateable()) {
				$p .= 'W';
			}
		} else {
			if ($this->info->isCreatable()) {
				$p .= 'CK';
			}
		}
		return $p;
	}

	public function getOwner() {
		return $this->info->getOwner();
	}

	protected function verifyPath() {
		if (\OC\Files\Filesystem::isForbiddenFileOrDir($this->info->getPath())) {
			throw new \Sabre\DAV\Exception\Forbidden();
		}

		try {
			$fileName = \basename($this->info->getPath());
			$this->fileView->verifyPath($this->path, $fileName);
		} catch (\OCP\Files\InvalidPathException $ex) {
			throw new InvalidPath($ex->getMessage());
		}
	}

	/**
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 */
	public function acquireLock($type) {
		$this->fileView->lockFile($this->path, $type);
	}

	/**
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 */
	public function releaseLock($type) {
		$this->fileView->unlockFile($this->path, $type);
	}

	/**
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 *
	 * @throws \OCP\Lock\LockedException
	 */
	public function changeLock($type) {
		$this->fileView->changeLock($this->path, $type);
	}

	/**
	 * @return \OCP\Files\FileInfo
	 */
	public function getFileInfo() {
		return $this->info;
	}

	protected function sanitizeMtime($mtimeFromRequest) {
		$mtime = (float) $mtimeFromRequest;
		if ($mtime >= PHP_INT_MAX) {
			$mtime = PHP_INT_MAX;
		} elseif ($mtime <= (PHP_INT_MAX*-1)) {
			$mtime = (PHP_INT_MAX*-1);
		} else {
			$mtime = (int) $mtimeFromRequest;
		}
		return $mtime;
	}
}
