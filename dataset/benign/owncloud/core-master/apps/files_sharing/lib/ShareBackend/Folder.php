<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

class Folder extends File implements \OCP\Share_Backend_Collection {

	/**
	 * get shared parents
	 *
	 * @param int $itemSource item source ID
	 * @param string $shareWith with whom should the item be shared
	 * @param string $owner owner of the item
	 * @return array with shares
	 */
	public function getParents($itemSource, $shareWith = null, $owner = null) {
		$result = [];
		$parent = $this->getParentId($itemSource);
		while ($parent) {
			$shares = \OCP\Share::getItemSharedWithUser('folder', $parent, $shareWith, $owner);
			if ($shares) {
				foreach ($shares as $share) {
					$name = \basename($share['path']);
					$share['collection']['path'] = $name;
					$share['collection']['item_type'] = 'folder';
					$share['file_path'] = $name;
					$displayNameOwner = \OCP\User::getDisplayName($share['uid_owner']);
					$displayNameShareWith = \OCP\User::getDisplayName($share['share_with']);
					$share['displayname_owner'] = ($displayNameOwner) ? $displayNameOwner : $share['uid_owner'];
					$share['share_with_displayname'] = ($displayNameShareWith) ? $displayNameShareWith : $share['uid_owner'];

					$result[] = $share;
				}
			}
			$parent = $this->getParentId($parent);
		}

		return $result;
	}

	/**
	 * get file cache ID of parent
	 *
	 * @param int $child file cache ID of child
	 * @return mixed parent ID or null
	 */
	private function getParentId($child) {
		$query = \OCP\DB::prepare('SELECT `parent` FROM `*PREFIX*filecache` WHERE `fileid` = ?');
		$result = $query->execute([$child]);
		$row = $result->fetchRow();
		$parent = ($row) ? $row['parent'] : null;

		return $parent;
	}

	public function getChildren($itemSource) {
		$children = [];
		$parents = [$itemSource];
		$query = \OCP\DB::prepare('SELECT `id` FROM `*PREFIX*mimetypes` WHERE `mimetype` = ?');
		$result = $query->execute(['httpd/unix-directory']);
		if ($row = $result->fetchRow()) {
			$mimetype = $row['id'];
		} else {
			$mimetype = -1;
		}
		while (!empty($parents)) {
			$parents = "'".\implode("','", $parents)."'";
			$query = \OCP\DB::prepare('SELECT `fileid`, `name`, `mimetype` FROM `*PREFIX*filecache`'
				.' WHERE `parent` IN ('.$parents.')');
			$result = $query->execute();
			$parents = [];
			while ($file = $result->fetchRow()) {
				$children[] = ['source' => $file['fileid'], 'file_path' => $file['name']];
				// If a child folder is found look inside it
				if ($file['mimetype'] == $mimetype) {
					$parents[] = $file['fileid'];
				}
			}
		}
		return $children;
	}
}
