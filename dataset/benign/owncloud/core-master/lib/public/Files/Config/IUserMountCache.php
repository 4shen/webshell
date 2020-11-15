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

namespace OCP\Files\Config;

use OCP\Files\Mount\IMountPoint;
use OCP\IUser;

/**
 * Cache mounts points per user in the cache so we can easily look them up
 *
 * @since 9.0.0
 */
interface IUserMountCache {
	/**
	 * Register mounts for a user to the cache
	 *
	 * @param IUser $user
	 * @param IMountPoint[] $mounts
	 * @since 9.0.0
	 */
	public function registerMounts(IUser $user, array $mounts);

	/**
	 * Get all cached mounts for a user
	 *
	 * @param IUser $user
	 * @return ICachedMountInfo[]
	 * @since 9.0.0
	 */
	public function getMountsForUser(IUser $user);

	/**
	 * Get all cached mounts by storage
	 *
	 * @param int $numericStorageId
	 * @return ICachedMountInfo[]
	 * @since 9.0.0
	 */
	public function getMountsForStorageId($numericStorageId);

	/**
	 * Get all cached mounts by root
	 *
	 * @param int $rootFileId
	 * @return ICachedMountInfo[]
	 * @since 9.0.0
	 */
	public function getMountsForRootId($rootFileId);

	/**
	 * Get all cached mounts that contain a file
	 *
	 * @param int $fileId
	 * @return ICachedMountInfo[]
	 * @since 9.0.0
	 */
	public function getMountsForFileId($fileId);

	/**
	 * Remove all cached mounts for a user
	 *
	 * @param IUser $user
	 * @since 9.0.0
	 */
	public function removeUserMounts(IUser $user);

	/**
	 * Remove all mounts for a user and storage
	 *
	 * @param $storageId
	 * @param string $userId
	 * @return mixed
	 * @since 9.0.0
	 */
	public function removeUserStorageMount($storageId, $userId);

	/**
	 * Remove all cached mounts for a storage
	 *
	 * @param $storageId
	 * @return mixed
	 * @since 9.0.0
	 */
	public function remoteStorageMounts($storageId);
}
