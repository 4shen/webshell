<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author macjohnny <estebanmarin@gmx.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Roman Kreisel <mail@romankreisel.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
 * @author voxsim <Simon Vocella>
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

namespace OC\Group;

use OC\Hooks\PublicEmitter;
use OC\User\Manager as UserManager;
use OCP\GroupInterface;
use OCP\IGroupManager;
use OCP\Util\UserSearch;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Manager
 *
 * Hooks available in scope \OC\Group:
 * - preAddUser(\OC\Group\Group $group, \OC\User\User $user)
 * - postAddUser(\OC\Group\Group $group, \OC\User\User $user)
 * - preRemoveUser(\OC\Group\Group $group, \OC\User\User $user)
 * - postRemoveUser(\OC\Group\Group $group, \OC\User\User $user)
 * - preDelete(\OC\Group\Group $group)
 * - postDelete(\OC\Group\Group $group)
 * - preCreate(string $groupId)
 * - postCreate(\OC\Group\Group $group)
 *
 * @package OC\Group
 */
class Manager extends PublicEmitter implements IGroupManager {
	/**
	 * @var GroupInterface[] $backends
	 */
	private $backends = [];

	/**
	 * @var UserManager $userManager
	 */
	private $userManager;

	/**
	 * @var UserSearch $userSearch
	 */
	private $userSearch;

	/**
	 * @var \OC\Group\Group[]
	 */
	private $cachedGroups = [];

	/**
	 * @var \OC\Group\Group[]
	 */
	private $cachedUserGroups = [];

	/** @var \OC\SubAdmin */
	private $subAdmin = null;

	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	/**
	 * @param \OC\User\Manager $userManager
	 * @param UserSearch $userSearch
	 */
	public function __construct(UserManager $userManager, UserSearch $userSearch, EventDispatcherInterface $eventDispatcher) {
		$this->userManager = $userManager;
		$this->userSearch = $userSearch;
		$this->eventDispatcher = $eventDispatcher;
		$cachedGroups = & $this->cachedGroups;
		$cachedUserGroups = & $this->cachedUserGroups;
		$this->listen('\OC\Group', 'postDelete', function ($group) use (&$cachedGroups, &$cachedUserGroups) {
			/**
			 * @var \OC\Group\Group $group
			 */
			unset($cachedGroups[$group->getGID()]);
			$cachedUserGroups = [];
		});
		$this->listen('\OC\Group', 'postAddUser', function ($group) use (&$cachedUserGroups) {
			/**
			 * @var \OC\Group\Group $group
			 */
			$cachedUserGroups = [];
		});
		$this->listen('\OC\Group', 'postRemoveUser', function ($group) use (&$cachedUserGroups) {
			/**
			 * @var \OC\Group\Group $group
			 */
			$cachedUserGroups = [];
		});
	}

	/**
	 * Checks whether a given backend is used
	 *
	 * @param string $backendClass Full classname including complete namespace
	 * @return bool
	 */
	public function isBackendUsed($backendClass) {
		$backendClass = \strtolower(\ltrim($backendClass, '\\'));

		foreach ($this->backends as $backend) {
			if (\strtolower(\get_class($backend)) === $backendClass) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param \OCP\GroupInterface $backend
	 */
	public function addBackend($backend) {
		$this->backends[] = $backend;
		$this->clearCaches();
	}

	public function clearBackends() {
		$this->backends = [];
		$this->clearCaches();
	}
	
	protected function clearCaches() {
		$this->cachedGroups = [];
		$this->cachedUserGroups = [];
	}

	/**
	 * @param string $gid
	 * @return \OC\Group\Group|null
	 */
	public function get($gid) {
		if (isset($this->cachedGroups[$gid])) {
			return $this->cachedGroups[$gid];
		}
		return $this->getGroupObject($gid);
	}

	/**
	 * @param string $gid
	 * @param string $displayName
	 * @return \OC\Group\Group|null
	 */
	protected function getGroupObject($gid, $displayName = null) {
		$backends = [];
		foreach ($this->backends as $backend) {
			if ($backend->implementsActions(\OC\Group\Backend::GROUP_DETAILS)) {
				/* @phan-suppress-next-line PhanUndeclaredMethod */
				$groupData = $backend->getGroupDetails($gid);
				if (\is_array($groupData)) {
					// take the display name from the first backend that has a non-null one
					if ($displayName === null && isset($groupData['displayName'])) {
						$displayName = $groupData['displayName'];
					}
					$backends[] = $backend;
				}
			} elseif ($backend->groupExists($gid)) {
				$backends[] = $backend;
			}
		}
		if (\count($backends) === 0) {
			return null;
		}
		$this->cachedGroups[$gid] = new Group($gid, $backends, $this->userManager, $this->eventDispatcher, $this, $displayName);
		return $this->cachedGroups[$gid];
	}

	/**
	 * @param string $gid
	 * @return bool
	 */
	public function groupExists($gid) {
		return $this->get($gid) !== null;
	}

	/**
	 * @param string $gid
	 * @return \OC\Group\Group
	 */
	public function createGroup($gid) {
		if ($gid === '' || $gid === null) {
			return false;
		} elseif ($group = $this->get($gid)) {
			return $group;
		} else {
			$this->emit('\OC\Group', 'preCreate', [$gid]);
			$this->eventDispatcher->dispatch(new GenericEvent(null, ['gid' => $gid]), 'group.preCreate');
			foreach ($this->backends as $backend) {
				if ($backend->implementsActions(\OC\Group\Backend::CREATE_GROUP)) {
					/* @phan-suppress-next-line PhanUndeclaredMethod */
					$backend->createGroup($gid);
					$group = $this->getGroupObject($gid);
					$this->emit('\OC\Group', 'postCreate', [$group]);
					$this->eventDispatcher->dispatch(new GenericEvent($group, ['gid' => $gid]), 'group.postCreate');
					return $group;
				}
			}
			return null;
		}
	}

	/**
	 * @param string $search search string
	 * @param int|null $limit limit
	 * @param int|null $offset offset
	 * @param string|null $scope scope string
	 * @return \OC\Group\Group[] groups
	 */
	public function search($search, $limit = null, $offset = null, $scope = null) {
		$groups = [];
		if ($this->userSearch->isSearchable($search)) {
			foreach ($this->backends as $backend) {
				if (!$backend->isVisibleForScope($scope)) {
					// skip backend
					continue;
				}
				$groupIds = $backend->getGroups($search, $limit, $offset);
				foreach ($groupIds as $groupId) {
					$aGroup = $this->get($groupId);
					if ($aGroup !== null) {
						$groups[$groupId] = $aGroup;
					} else {
						\OC::$server->getLogger()->debug('Group "' . $groupId . '" was returned by search but not found through direct access', ['app' => 'core']);
					}
				}
				if ($limit !== null and $limit <= 0) {
					return \array_values($groups);
				}
			}
		}
		return \array_values($groups);
	}

	/**
	 * @param \OC\User\User|null $user user
	 * @param string|null $scope scope string
	 * @return \OC\Group\Group[]
	 */
	public function getUserGroups($user, $scope = null) {
		if ($user === null) {
			return [];
		}
		return $this->getUserIdGroups($user->getUID(), $scope);
	}

	/**
	 * Gathers a list of backends that opt out of the given scope.
	 *
	 * @param string|null $scope scope string
	 * @return \OCP\GroupInterface[] excluded backends
	 */
	private function getExcludedBackendsForScope($scope) {
		$excludedBackendsForScope = [];
		foreach ($this->backends as $backend) {
			if (!$backend->isVisibleForScope($scope)) {
				$excludedBackendsForScope[] = $backend;
			}
		}
		return $excludedBackendsForScope;
	}

	/**
	 * Filter groups by backends that opt-out of the given scope
	 *
	 * @param \OCP\IGroup[] $groups groups to filter
	 * @param string|null $scope scope string
	 * @return \OCP\IGroup[] filtered groups
	 */
	private function filterExcludedBackendsForScope($groups, $scope) {
		$excludedBackendsForScope = $this->getExcludedBackendsForScope($scope);
		if (!empty($excludedBackendsForScope)) {
			return \array_filter($groups, function ($group) use ($excludedBackendsForScope) {
				return !\in_array($group->getBackend(), $excludedBackendsForScope);
			});
		}
		return $groups;
	}

	/**
	 * @param string $uid the user id
	 * @param string|null $scope scope string
	 * @return \OC\Group\Group[]
	 */
	public function getUserIdGroups($uid, $scope = null) {
		if (!isset($this->cachedUserGroups[$uid])) {
			$groups = [];

			foreach ($this->backends as $backend) {
				$groupIds = $backend->getUserGroups($uid);
				if (\is_array($groupIds)) {
					foreach ($groupIds as $groupId) {
						$aGroup = $this->get($groupId);
						if ($aGroup !== null) {
							$groups[$groupId] = $aGroup;
						} else {
							\OC::$server->getLogger()->debug('User "' . $uid . '" belongs to deleted group: "' . $groupId . '"', ['app' => 'core']);
						}
					}
				}
			}
			$this->cachedUserGroups[$uid] = $groups;
		} else {
			$groups = $this->cachedUserGroups[$uid];
		}

		// filter out groups that must be omitted for the given scope
		return $this->filterExcludedBackendsForScope($groups, $scope);
	}

	/**
	 * Checks if a userId is in the admin group
	 * @param string $userId
	 * @return bool if admin
	 */
	public function isAdmin($userId) {
		return $this->isInGroup($userId, 'admin');
	}

	/**
	 * Checks if a userId is in a group
	 * @param string $userId
	 * @param string $group
	 * @return bool if in group
	 */
	public function isInGroup($userId, $group) {
		return \array_key_exists($group, $this->getUserIdGroups($userId));
	}

	/**
	 * get a list of group ids for a user
	 * @param \OC\User\User $user
	 * @param string|null $scope string
	 * @return array with group ids
	 */
	public function getUserGroupIds($user, $scope = null) {
		return \array_map(function ($value) {
			return (string) $value;
		}, \array_keys($this->getUserGroups($user, $scope)));
	}

	/**
	 * Finds users in a group
	 * @param string $gid
	 * @param string $search
	 * @param int $limit
	 * @param int $offset
	 * @return \OC\User\User[]
	 */
	public function findUsersInGroup($gid, $search = '', $limit = -1, $offset = 0) {
		$group = $this->get($gid);
		if ($group === null) {
			return [];
		}

		$search = \trim($search);
		$groupUsers = [];

		if (!empty($search)) {
			// only user backends have the capability to do a complex search for users
			$searchOffset = 0;
			$searchLimit = $limit * 100;
			if ($limit === -1) {
				$searchLimit = 500;
			}

			do {
				$filteredUsers = $this->userManager->find($search, $searchLimit, $searchOffset);
				foreach ($filteredUsers as $filteredUser) {
					if ($group->inGroup($filteredUser)) {
						$groupUsers[]= $filteredUser;
					}
				}
				$searchOffset += $searchLimit;
			} while (\count($groupUsers) < $searchLimit+$offset && \count($filteredUsers) >= $searchLimit);

			if ($limit === -1) {
				$groupUsers = \array_slice($groupUsers, $offset);
			} else {
				$groupUsers = \array_slice($groupUsers, $offset, $limit);
			}
		} else {
			$groupUsers = $group->searchUsers('', $limit, $offset);
		}

		$matchingUsers = [];
		foreach ($groupUsers as $groupUser) {
			$matchingUsers[$groupUser->getUID()] = $groupUser;
		}

		return $matchingUsers;
	}

	/**
	 * get a list of all display names in a group
	 * @param string $gid
	 * @param string $search
	 * @param int $limit
	 * @param int $offset
	 * @return array an array of display names (value) and user ids (key)
	 */
	public function displayNamesInGroup($gid, $search = '', $limit = -1, $offset = 0) {
		$group = $this->get($gid);
		if ($group === null) {
			return [];
		}

		$search = \trim($search);
		$groupUsers = [];

		if (!empty($search)) {
			// only user backends have the capability to do a complex search for users
			$searchOffset = 0;
			$searchLimit = $limit * 100;
			if ($limit === -1) {
				$searchLimit = 500;
			}

			do {
				$filteredUsers = $this->userManager->searchDisplayName($search, $searchLimit, $searchOffset);
				foreach ($filteredUsers as $filteredUser) {
					if ($group->inGroup($filteredUser)) {
						$groupUsers[]= $filteredUser;
					}
				}
				$searchOffset += $searchLimit;
			} while (\count($groupUsers) < $searchLimit+$offset && \count($filteredUsers) >= $searchLimit);

			if ($limit === -1) {
				$groupUsers = \array_slice($groupUsers, $offset);
			} else {
				$groupUsers = \array_slice($groupUsers, $offset, $limit);
			}
		} else {
			$groupUsers = $group->searchUsers('', $limit, $offset);
		}

		$matchingUsers = [];
		foreach ($groupUsers as $groupUser) {
			$matchingUsers[$groupUser->getUID()] = $groupUser->getDisplayName();
		}
		return $matchingUsers;
	}

	/**
	 * @return \OC\SubAdmin
	 */
	public function getSubAdmin() {
		if (!$this->subAdmin) {
			$this->subAdmin = new \OC\SubAdmin(
				$this->userManager,
				$this,
				\OC::$server->getDatabaseConnection()
			);
		}

		return $this->subAdmin;
	}

	public function inGroup($uid, $gid) {
		$group = $this->get($gid);
		$user = $this->userManager->get($uid);
		if ($group and $user) {
			return $group->inGroup($user);
		}
		return false;
	}
}
