<?php
/**
 * @author Aaron Wood <aaronjwood@gmail.com>
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
/*
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE `groups` (
 *   `gid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
 *   PRIMARY KEY (`gid`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 *
 * CREATE TABLE `group_user` (
 *   `gid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
 *   `uid` varchar(64) COLLATE utf8_unicode_ci NOT NULL
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 *
 */

namespace OC\Group;

/**
 * Class for group management in a SQL Database (e.g. MySQL, SQLite)
 */
class Database extends \OC\Group\Backend {

	/** @var string[] */
	private $groupCache = [];

	/** @var \OCP\IDBConnection */
	private $dbConn;

	/** @var \OCP\IConfig */
	private $config;

	/**
	 * \OC\Group\Database constructor.
	 *
	 * @param \OCP\IDBConnection|null $dbConn
	 * @param \OCP\IConfig|null $config
	 */
	public function __construct(
		\OCP\IDBConnection $dbConn = null,
		\OCP\IConfig $config = null
	) {
		$this->dbConn = $dbConn;
		$this->config = $config;
	}

	/**
	 * FIXME: This function should not be required!
	 */
	private function fixDI() {
		if ($this->dbConn === null) {
			$this->dbConn = \OC::$server->getDatabaseConnection();
		}
		if ($this->config === null) {
			$this->config = \OC::$server->getConfig();
		}
	}

	/**
	 * Try to create a new group
	 * @param string $gid The name of the group to create
	 * @return bool
	 *
	 * Tries to create a new group. If the group name already exists, false will
	 * be returned.
	 */
	public function createGroup($gid) {
		$this->fixDI();

		// Add group
		$result = $this->dbConn->insertIfNotExist('*PREFIX*groups', [
			'gid' => $gid,
		]);

		// Add to cache
		$this->groupCache[$gid] = $gid;

		return $result === 1;
	}

	/**
	 * delete a group
	 * @param string $gid gid of the group to delete
	 * @return bool
	 *
	 * Deletes a group and removes it from the group_user-table
	 */
	public function deleteGroup($gid) {
		$this->fixDI();

		// Delete the group
		$qb = $this->dbConn->getQueryBuilder();
		$qb->delete('groups')
			->where($qb->expr()->eq('gid', $qb->createNamedParameter($gid)))
			->execute();

		// Delete the group-user relation
		$qb = $this->dbConn->getQueryBuilder();
		$qb->delete('group_user')
			->where($qb->expr()->eq('gid', $qb->createNamedParameter($gid)))
			->execute();

		// Delete the group-groupadmin relation
		$qb = $this->dbConn->getQueryBuilder();
		$qb->delete('group_admin')
			->where($qb->expr()->eq('gid', $qb->createNamedParameter($gid)))
			->execute();

		// Delete from cache
		unset($this->groupCache[$gid]);

		return true;
	}

	/**
	 * is user in group?
	 * @param string $uid uid of the user
	 * @param string $gid gid of the group
	 * @return bool
	 *
	 * Checks whether the user is member of a group or not.
	 */
	public function inGroup($uid, $gid) {
		$this->fixDI();

		// check
		$qb = $this->dbConn->getQueryBuilder();
		$cursor = $qb->select('uid')
			->from('group_user')
			->where($qb->expr()->eq('gid', $qb->createNamedParameter($gid)))
			->andWhere($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
			->execute();

		$result = $cursor->fetch();
		$cursor->closeCursor();

		return $result ? true : false;
	}

	/**
	 * Add a user to a group
	 * @param string $uid Name of the user to add to group
	 * @param string $gid Name of the group in which add the user
	 * @return bool
	 *
	 * Adds a user to a group.
	 */
	public function addToGroup($uid, $gid) {
		$this->fixDI();

		// No duplicate entries!
		if (!$this->inGroup($uid, $gid)) {
			$qb = $this->dbConn->getQueryBuilder();
			$qb->insert('group_user')
				->setValue('uid', $qb->createNamedParameter($uid))
				->setValue('gid', $qb->createNamedParameter($gid))
				->execute();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes a user from a group
	 * @param string $uid Name of the user to remove from group
	 * @param string $gid Name of the group from which remove the user
	 * @return bool
	 *
	 * removes the user from a group.
	 */
	public function removeFromGroup($uid, $gid) {
		$this->fixDI();

		$qb = $this->dbConn->getQueryBuilder();
		$qb->delete('group_user')
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
			->andWhere($qb->expr()->eq('gid', $qb->createNamedParameter($gid)))
			->execute();

		return true;
	}

	/**
	 * Get all groups a user belongs to
	 * @param string $uid Name of the user
	 * @return array an array of group names
	 *
	 * This function fetches all groups a user belongs to. It does not check
	 * if the user exists at all.
	 */
	public function getUserGroups($uid) {
		$this->fixDI();

		// No magic!
		$qb = $this->dbConn->getQueryBuilder();
		$cursor = $qb->select('gid')
			->from('group_user')
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
			->execute();

		$groups = [];
		while ($row = $cursor->fetch()) {
			$groups[] = $row["gid"];
			$this->groupCache[$row['gid']] = $row['gid'];
		}
		$cursor->closeCursor();

		return $groups;
	}

	/**
	 * get a list of all groups
	 * @param string $search
	 * @param int $limit
	 * @param int $offset
	 * @return array an array of group names
	 *
	 * Returns a list with all groups
	 */
	public function getGroups($search = '', $limit = null, $offset = null) {
		$this->fixDI();

		$parameters = [];
		$searchLike = '';
		if ($search !== '') {
			$search = $this->dbConn->escapeLikeParameter($search);
			$allowMedialSearches = $this->config->getSystemValue("groups.enable_medial_search", true);
			if ($allowMedialSearches) {
				$parameters[] = '%' . $search . '%';
			} else {
				$parameters[] = $search . '%';
			}
			$searchLike = ' WHERE LOWER(`gid`) LIKE LOWER(?)';
		}

		$stmt = \OC_DB::prepare('SELECT `gid` FROM `*PREFIX*groups`' . $searchLike . ' ORDER BY `gid` ASC', $limit, $offset);
		$result = $stmt->execute($parameters);
		$groups = [];
		while ($row = $result->fetchRow()) {
			$groups[] = $row['gid'];
		}
		return $groups;
	}

	/**
	 * check if a group exists
	 * @param string $gid
	 * @return bool
	 */
	public function groupExists($gid) {
		$this->fixDI();

		// Check cache first
		if (isset($this->groupCache[$gid])) {
			return true;
		}

		$qb = $this->dbConn->getQueryBuilder();
		$cursor = $qb->select('gid')
			->from('groups')
			->where($qb->expr()->eq('gid', $qb->createNamedParameter($gid)))
			->execute();
		$result = $cursor->fetch();
		$cursor->closeCursor();

		if ($result !== false) {
			$this->groupCache[$gid] = $gid;
			return true;
		}
		return false;
	}

	/**
	 * get a list of all users in a group
	 * @param string $gid
	 * @param string $search
	 * @param int $limit
	 * @param int $offset
	 * @return array an array of user ids
	 */
	public function usersInGroup($gid, $search = '', $limit = null, $offset = null) {
		$this->fixDI();

		$parameters = [$gid];
		$searchLike = '';
		if ($search !== '') {
			$parameters[] = '%' . $this->dbConn->escapeLikeParameter($search) . '%';
			$searchLike = ' AND `uid` LIKE ?';
		}

		$stmt = \OC_DB::prepare('SELECT `uid` FROM `*PREFIX*group_user` WHERE `gid` = ?' . $searchLike . ' ORDER BY `uid` ASC',
			$limit,
			$offset);
		$result = $stmt->execute($parameters);
		$users = [];
		while ($row = $result->fetchRow()) {
			$users[] = $row['uid'];
		}
		return $users;
	}

	/**
	 * get the number of all users matching the search string in a group
	 * @param string $gid
	 * @param string $search
	 * @return int|false
	 * @throws \OC\DatabaseException
	 */
	public function countUsersInGroup($gid, $search = '') {
		$this->fixDI();

		$parameters = [$gid];
		$searchLike = '';
		if ($search !== '') {
			$parameters[] = '%' . $this->dbConn->escapeLikeParameter($search) . '%';
			$searchLike = ' AND `uid` LIKE ?';
		}

		$stmt = \OC_DB::prepare('SELECT COUNT(`uid`) AS `count` FROM `*PREFIX*group_user` WHERE `gid` = ?' . $searchLike);
		$result = $stmt->execute($parameters);
		$count = $result->fetchOne();
		if ($count !== false) {
			$count = \intval($count);
		}
		return $count;
	}
}
