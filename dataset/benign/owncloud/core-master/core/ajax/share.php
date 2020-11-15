<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Craig Morrissey <craig@owncloud.com>
 * @author dampfklon <me@dampfklon.de>
 * @author Felix Böhm <felixboehm@gmx.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Leonardo Diez <leio10@users.noreply.github.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author neumann <node512@gmail.com>
 * @author Ramiro Aparicio <rapariciog@gmail.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Thomas Tanghus <thomas@tanghus.net>
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

OC_JSON::checkLoggedIn();
OCP\JSON::callCheck();

/**
 * @return mixed
 */
function getGroups($search = '', $limit = null, $offset = null) {
	$groups = \OC::$server->getGroupManager()->search($search, $limit, $offset);
	$groupIds = [];
	foreach ($groups as $group) {
		$groupIds[] = $group->getGID();
	}
	return $groupIds;
}

/**
 * @param $gids
 * @param $limit
 * @param $offset
 * @return mixed
 */
function displayNamesInGroups($gids, $search = '', $limit = -1, $offset = 0) {
	$displayNames = [];
	foreach ($gids as $gid) {
		// TODO Need to apply limits to groups as total
		$diff = \array_diff(
			\OC::$server->getGroupManager()->displayNamesInGroup($gid, $search, $limit, $offset),
			$displayNames
		);
		if ($diff) {
			// A fix for LDAP users. array_merge loses keys...
			$displayNames = $diff + $displayNames;
		}
	}
	return $displayNames;
}

if (isset($_GET['fetch'])) {
	switch ($_GET['fetch']) {
		case 'getItemsSharedStatuses':
			if (isset($_GET['itemType'])) {
				$return = OCP\Share::getItemsShared((string)$_GET['itemType'], OCP\Share::FORMAT_STATUSES);
				\is_array($return) ? OC_JSON::success(['data' => $return]) : OC_JSON::error();
			}
			break;
		case 'getItem':
			if (isset($_GET['itemType'], $_GET['itemSource'], $_GET['checkReshare'], $_GET['checkShares'])
				 
				 ) {
				if ($_GET['checkReshare'] == 'true') {
					$reshare = OCP\Share::getItemSharedWithBySource(
						(string)$_GET['itemType'],
						(string)$_GET['itemSource'],
						OCP\Share::FORMAT_NONE,
						null,
						true
					);
				} else {
					$reshare = false;
				}
				if ($_GET['checkShares'] == 'true') {
					$shares = OCP\Share::getItemShared(
						(string)$_GET['itemType'],
						(string)$_GET['itemSource'],
						OCP\Share::FORMAT_NONE,
						null,
						true
					);
				} else {
					$shares = false;
				}
				OC_JSON::success(['data' => ['reshare' => $reshare, 'shares' => $shares]]);
			}
			break;
		case 'getShareWithEmail':
			$result = [];
			if (isset($_GET['search'])) {
				$cm = OC::$server->getContactsManager();

				$config = OC::$server->getConfig();
				$userEnumerationAllowed = $config
					->getAppValue('core', 'shareapi_allow_share_dialog_user_enumeration', 'yes') === 'yes';
				$pattern = (string)$_GET['search'];
				$searchConfig =  new \OCP\Util\UserSearch($config);
				if (!$searchConfig->isSearchable($pattern)) {
					OC_JSON::error();
					return;
				}

				if ($cm !== null && $cm->isEnabled() && $userEnumerationAllowed) {
					$contacts = $cm->search($pattern, ['FN', 'EMAIL']);
					foreach ($contacts as $contact) {
						// We don't want contacts from system address books
						if (isset($contact['isSystemBook'])) {
							continue;
						}
						if (!isset($contact['EMAIL'])) {
							continue;
						}

						$emails = $contact['EMAIL'];
						if (!\is_array($emails)) {
							$emails = [$emails];
						}

						foreach ($emails as $email) {
							$result[] = [
								'email' => $email,
								'displayname' => $contact['FN'],
							];
						}
					}
				}
			}
			OC_JSON::success(['data' => $result]);
			break;
		case 'getShareWith':
			if (isset($_GET['search'])) {
				$shareWithinGroupOnly = OC\Share\Share::shareWithGroupMembersOnly();
				$shareWith = [];
				$groups = getGroups((string)$_GET['search']);
				if ($shareWithinGroupOnly) {
					$usergroups = \OC::$server->getGroupManager()->getUserIdGroups(OC_User::getUser());
					$usergroups = \array_values(\array_map(function (\OCP\IGroup $g) {
						return $g->getGID();
					}, $usergroups));
					$groups = \array_intersect($groups, $usergroups);
				}

				$sharedUsers = [];
				$sharedGroups = [];
				if (isset($_GET['itemShares'])) {
					if (isset($_GET['itemShares'][OCP\Share::SHARE_TYPE_USER]) &&
						\is_array($_GET['itemShares'][OCP\Share::SHARE_TYPE_USER])) {
						$sharedUsers = $_GET['itemShares'][OCP\Share::SHARE_TYPE_USER];
					}

					if (isset($_GET['itemShares'][OCP\Share::SHARE_TYPE_GROUP]) &&
						\is_array($_GET['itemShares'][OCP\Share::SHARE_TYPE_GROUP])) {
						$sharedGroups = $_GET['itemShares'][OCP\Share::SHARE_TYPE_GROUP];
					}
				}

				$count = 0;
				$users = [];
				$limit = 0;
				$offset = 0;
				// limit defaults to 15 if not specified via request parameter and can be no larger than 500
				$request_limit = \min((int)$_GET['limit'] ?: 15, 500);
				while ($count < $request_limit && \count($users) == $limit) {
					$limit = $request_limit - $count;
					if ($shareWithinGroupOnly) {
						$users = displayNamesInGroups($usergroups, (string)$_GET['search'], $limit, $offset);
					} else {
						$users = OC_User::getDisplayNames((string)$_GET['search'], $limit, $offset);
					}

					$offset += $limit;
					foreach ($users as $uid => $displayName) {
						if (\in_array($uid, $sharedUsers)) {
							continue;
						}

						if ((!isset($_GET['itemShares'])
							|| !\is_array($_GET['itemShares'][OCP\Share::SHARE_TYPE_USER])
							|| !\in_array($uid, $_GET['itemShares'][OCP\Share::SHARE_TYPE_USER]))
							&& $uid != OC_User::getUser()) {
							$shareWith[] = [
								'label' => $displayName,
								'value' => [
									'shareType' => OCP\Share::SHARE_TYPE_USER,
									'shareWith' => $uid]
							];
							$count++;
						}
					}
				}
				$count = 0;

				// enable l10n support
				$l = \OC::$server->getL10N('core');

				foreach ($groups as $group) {
					if (\in_array($group, $sharedGroups)) {
						continue;
					}

					if ($count < $request_limit) {
						if (!isset($_GET['itemShares'])
							|| !isset($_GET['itemShares'][OCP\Share::SHARE_TYPE_GROUP])
							|| !\is_array($_GET['itemShares'][OCP\Share::SHARE_TYPE_GROUP])
							|| !\in_array($group, $_GET['itemShares'][OCP\Share::SHARE_TYPE_GROUP])) {
							$shareWith[] = [
								'label' => $group,
								'value' => [
									'shareType' => OCP\Share::SHARE_TYPE_GROUP,
									'shareWith' => $group
								]
							];
							$count++;
						}
					} else {
						break;
					}
				}

				// allow user to add unknown remote addresses for server-to-server share
				$backend = \OCP\Share::getBackend((string)$_GET['itemType']);
				if ($backend->isShareTypeAllowed(\OCP\Share::SHARE_TYPE_REMOTE)) {
					if (\substr_count((string)$_GET['search'], '@') >= 1) {
						$shareWith[] = [
							'label' => (string)$_GET['search'],
							'value' => [
								'shareType' => \OCP\Share::SHARE_TYPE_REMOTE,
								'shareWith' => (string)$_GET['search']
							]
						];
					}
					$contactManager = \OC::$server->getContactsManager();
					$addressBookContacts = $contactManager->search($_GET['search'], ['CLOUD', 'FN']);
					foreach ($addressBookContacts as $contact) {
						if (isset($contact['CLOUD'])) {
							foreach ($contact['CLOUD'] as $cloudId) {
								$shareWith[] = [
									'label' => $contact['FN'] . ' (' . $cloudId . ')',
									'value' => [
										'shareType' => \OCP\Share::SHARE_TYPE_REMOTE,
										'shareWith' => $cloudId
									]
								];
							}
						}
					}
				}

				$sharingAutocompletion = \OC::$server->getConfig()
					->getAppValue('core', 'shareapi_allow_share_dialog_user_enumeration', 'yes');

				if ($sharingAutocompletion !== 'yes') {
					$searchTerm = \strtolower($_GET['search']);
					$shareWith = \array_filter($shareWith, function ($user) use ($searchTerm) {
						return \strtolower($user['label']) === $searchTerm
							|| \strtolower($user['value']['shareWith']) === $searchTerm;
					});
				}

				$sorter = new \OC\Share\SearchResultSorter((string)$_GET['search'],
														   'label',
														   \OC::$server->getLogger());
				\usort($shareWith, [$sorter, 'sort']);
				OC_JSON::success(['data' => $shareWith]);
			}
			break;
	}
}
