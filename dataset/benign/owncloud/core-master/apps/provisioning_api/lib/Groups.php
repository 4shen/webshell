<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Tom Needham <tom@owncloud.com>
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

namespace OCA\Provisioning_API;

use OC_OCS_Result;
use OCP\IGroup;
use OCP\IUser;

class Groups {

	/** @var \OCP\IGroupManager */
	private $groupManager;

	/** @var \OCP\IUserSession */
	private $userSession;

	/** @var \OCP\IRequest */
	private $request;

	/**
	 * @param \OCP\IGroupManager $groupManager
	 * @param \OCP\IUserSession $userSession
	 * @param \OCP\IRequest $request
	 */
	public function __construct(\OCP\IGroupManager $groupManager,
								\OCP\IUserSession $userSession,
								\OCP\IRequest $request) {
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->request = $request;
	}

	/**
	 * returns a list of groups
	 *
	 * @param array $parameters
	 * @return OC_OCS_Result
	 */
	public function getGroups($parameters) {
		$search = $this->request->getParam('search', '');
		$limit = $this->request->getParam('limit');
		$offset = $this->request->getParam('offset');

		if ($limit !== null) {
			$limit = (int)$limit;
		}
		if ($offset !== null) {
			$offset = (int)$offset;
		}

		$groups = $this->groupManager->search($search, $limit, $offset, 'management');
		$groups = \array_map(function ($group) {
			/** @var IGroup $group */
			return $group->getGID();
		}, $groups);

		return new OC_OCS_Result(['groups' => $groups]);
	}

	/**
	 * returns an array of users in the group specified
	 *
	 * @param array $parameters
	 * @return OC_OCS_Result
	 */
	public function getGroup($parameters) {
		// Check if user is logged in
		$user = $this->userSession->getUser();
		if ($user === null) {
			return new OC_OCS_Result(null, \OCP\API::RESPOND_UNAUTHORISED);
		}

		$groupId = $parameters['groupid'];

		// Check the group exists
		if (!$this->groupManager->groupExists($groupId)) {
			return new OC_OCS_Result(null, \OCP\API::RESPOND_NOT_FOUND, 'The requested group could not be found');
		}

		$isSubadminOfGroup = false;
		$group = $this->groupManager->get($groupId);
		if ($group !== null) {
			'@phan-var \OC\Group\Manager $this->groupManager';
			$isSubadminOfGroup =$this->groupManager->getSubAdmin()->isSubAdminofGroup($user, $group);
		}

		// Check subadmin has access to this group
		if ($this->groupManager->isAdmin($user->getUID())
		   || $isSubadminOfGroup) {
			$users = $this->groupManager->get($groupId)->getUsers();
			$users =  \array_map(function ($user) {
				/** @var IUser $user */
				return $user->getUID();
			}, $users);
			$users = \array_values($users);
			return new OC_OCS_Result(['users' => $users]);
		}
		return new OC_OCS_Result(null, \OCP\API::RESPOND_UNAUTHORISED, 'User does not have access to specified group');
	}

	/**
	 * creates a new group
	 *
	 * @param array $parameters
	 * @return OC_OCS_Result
	 */
	public function addGroup($parameters) {
		// Validate name
		$groupId = $this->request->getParam('groupid', '');
		if (($groupId === '') || $groupId === null || ($groupId === false)) {
			\OCP\Util::writeLog('provisioning_api', 'Group name not supplied', \OCP\Util::ERROR);
			return new OC_OCS_Result(null, 101, 'Invalid group name');
		}
		// Check if it exists
		if ($this->groupManager->groupExists($groupId)) {
			return new OC_OCS_Result(null, 102);
		}
		$user = $this->userSession->getUser();
		if ($user === null) {
			return new OC_OCS_Result(null, 102);
		}
		// Only admin has got privilege to create group
		if ($this->groupManager->isAdmin($user->getUID())) {
			$this->groupManager->createGroup($groupId);
			return new OC_OCS_Result(null, 100);
		}

		return new OC_OCS_Result(null, 997);
	}

	/**
	 * @param array $parameters
	 * @return OC_OCS_Result
	 */
	public function deleteGroup($parameters) {
		// Check it exists
		if (!$this->groupManager->groupExists($parameters['groupid'])) {
			return new OC_OCS_Result(null, 101);
		}
		if ($parameters['groupid'] === 'admin' || !$this->groupManager->get($parameters['groupid'])->delete()) {
			// Cannot delete admin group
			return new OC_OCS_Result(null, 102);
		}
		return new OC_OCS_Result(null, 100);
	}

	/**
	 * @param array $parameters
	 * @return OC_OCS_Result
	 */
	public function getSubAdminsOfGroup($parameters) {
		$group = $parameters['groupid'];
		// Check group exists
		$targetGroup = $this->groupManager->get($group);
		if ($targetGroup === null) {
			return new OC_OCS_Result(null, 101, 'Group does not exist');
		}

		'@phan-var \OC\Group\Manager $this->groupManager';
		$subadmins = $this->groupManager->getSubAdmin()->getGroupsSubAdmins($targetGroup);
		// New class returns IUser[] so convert back
		$uids = [];
		foreach ($subadmins as $user) {
			$uids[] = $user->getUID();
		}

		return new OC_OCS_Result($uids);
	}
}
