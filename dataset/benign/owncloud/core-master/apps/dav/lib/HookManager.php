<?php
/**
 * @author Thomas Citharel <tcit@tcit.fr>
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
namespace OCA\DAV;

use OCA\DAV\CalDAV\BirthdayService;
use OCA\DAV\CalDAV\CalDavBackend;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\CardDAV\SyncService;
use OCP\IL10N;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Util;

class HookManager {

	/** @var IUserManager */
	private $userManager;

	/** @var SyncService */
	private $syncService;

	/** @var IUser[] */
	private $usersToDelete;

	/** @var CalDavBackend */
	private $calDav;

	/** @var CardDavBackend */
	private $cardDav;

	/** @var IL10N */
	private $l10n;

	/** @var array */
	private $calendarsToDelete;

	/** @var array */
	private $addressBooksToDelete;

	public function __construct(IUserManager $userManager,
								SyncService $syncService,
								CalDavBackend $calDav,
								CardDavBackend $cardDav,
								IL10N $l10n) {
		$this->userManager = $userManager;
		$this->syncService = $syncService;
		$this->calDav = $calDav;
		$this->cardDav = $cardDav;
		$this->l10n = $l10n;
	}

	public function setup() {
		Util::connectHook('OC_User',
			'post_createUser',
			$this,
			'postCreateUser');
		Util::connectHook('OC_User',
			'pre_deleteUser',
			$this,
			'preDeleteUser');
		Util::connectHook('OC_User',
			'post_deleteUser',
			$this,
			'postDeleteUser');
		Util::connectHook('OC_User',
			'changeUser',
			$this,
			'changeUser');
	}

	public function postCreateUser($params) {
		$user = $this->userManager->get($params['uid']);
		$this->syncService->updateUser($user);
	}

	public function preDeleteUser($params) {
		$uid = $params['uid'];
		$this->usersToDelete[$uid] = $this->userManager->get($uid);
		$this->calendarsToDelete = $this->calDav->getUsersOwnCalendars('principals/users/' . $uid);
		$this->addressBooksToDelete = $this->cardDav->getUsersOwnAddressBooks('principals/users/' . $uid);
	}

	public function postDeleteUser($params) {
		$uid = $params['uid'];
		if (isset($this->usersToDelete[$uid])) {
			$this->syncService->deleteUser($this->usersToDelete[$uid]);
		} else {
			// the user wasn't found in the userManager. Assume it was deleted before
			// and try to delete from the syncService if it's the only one.
			$this->syncService->deleteUserByUidIfUnique($uid);  // not interested in the result
		}

		foreach ($this->calendarsToDelete as $calendar) {
			$this->calDav->deleteCalendar($calendar['id']);
		}
		$this->calDav->deleteAllSharesForUser('principals/users/' . $uid);

		foreach ($this->addressBooksToDelete as $addressBook) {
			$this->cardDav->deleteAddressBook($addressBook['id']);
		}
	}

	public function changeUser($params) {
		$user = $params['user'];
		$this->syncService->updateUser($user);
	}

	public function firstLogin(IUser $user = null) {
		if ($user !== null) {
			$principal = 'principals/users/' . $user->getUID();
			$calendars = $this->calDav->getCalendarsForUser($principal);
			if (empty($calendars) || (\count($calendars) === 1 && $calendars[0]['uri'] === BirthdayService::BIRTHDAY_CALENDAR_URI)) {
				try {
					$this->calDav->createCalendar($principal, 'personal', [
						'{DAV:}displayname' => $this->l10n->t('Personal'),
						'{http://apple.com/ns/ical/}calendar-color' => '#1d2d44']);
				} catch (\Exception $ex) {
					\OC::$server->getLogger()->logException($ex);
				}
			}
			$books = $this->cardDav->getAddressBooksForUser($principal);
			if (empty($books)) {
				try {
					$this->cardDav->createAddressBook($principal, 'contacts', [
						'{DAV:}displayname' => $this->l10n->t('Contacts')]);
				} catch (\Exception $ex) {
					\OC::$server->getLogger()->logException($ex);
				}
			}
		}
	}
}
