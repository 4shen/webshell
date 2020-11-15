<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace OC\Files\Config;

use OC\Files\Filesystem;
use OCP\Files\Config\ICachedMountInfo;
use OCP\Files\Node;
use OCP\IUser;

class CachedMountInfo implements ICachedMountInfo {
	/**
	 * @var IUser
	 */
	protected $user;

	/**
	 * @var int
	 */
	protected $storageId;

	/**
	 * @var int
	 */
	protected $rootId;

	/**
	 * @var string
	 */
	protected $mountPoint;

	/**
	 * CachedMountInfo constructor.
	 *
	 * @param IUser $user
	 * @param int $storageId
	 * @param int $rootId
	 * @param string $mountPoint
	 */
	public function __construct(IUser $user, $storageId, $rootId, $mountPoint) {
		$this->user = $user;
		$this->storageId = $storageId;
		$this->rootId = $rootId;
		$this->mountPoint = $mountPoint;
	}

	/**
	 * @return IUser
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return int the numeric storage id of the mount
	 */
	public function getStorageId() {
		return $this->storageId;
	}

	/**
	 * @return int the fileid of the root of the mount
	 */
	public function getRootId() {
		return $this->rootId;
	}

	/**
	 * @return Node the root node of the mount
	 */
	public function getMountPointNode() {
		// TODO injection etc
		Filesystem::initMountPoints($this->getUser()->getUID());
		$userNode = \OC::$server->getUserFolder($this->getUser()->getUID());
		$nodes = $userNode->getParent()->getById($this->getRootId(), true);
		return $nodes[0] ?? null;
	}

	/**
	 * @return string the mount point of the mount for the user
	 */
	public function getMountPoint() {
		return $this->mountPoint;
	}
}
