<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
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
namespace OCA\DAV\CalDAV;

use OCA\DAV\DAV\Sharing\IShareable;
use OCP\IL10N;
use Sabre\CalDAV\Backend\BackendInterface;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\PropPatch;

class Calendar extends \Sabre\CalDAV\Calendar implements IShareable {
	public function __construct(BackendInterface $caldavBackend, $calendarInfo, IL10N $l10n) {
		parent::__construct($caldavBackend, $calendarInfo);

		if ($this->getName() === BirthdayService::BIRTHDAY_CALENDAR_URI) {
			$this->calendarInfo['{DAV:}displayname'] = $l10n->t('Contact birthdays');
		}
	}

	/**
	 * Updates the list of shares.
	 *
	 * The first array is a list of people that are to be added to the
	 * resource.
	 *
	 * Every element in the add array has the following properties:
	 *   * href - A url. Usually a mailto: address
	 *   * commonName - Usually a first and last name, or false
	 *   * summary - A description of the share, can also be false
	 *   * readOnly - A boolean value
	 *
	 * Every element in the remove array is just the address string.
	 *
	 * @param array $add
	 * @param array $remove
	 * @return void
	 */
	public function updateShares(array $add, array $remove) {
		/** @var CalDavBackend $calDavBackend */
		$calDavBackend = $this->caldavBackend;
		'@phan-var CalDavBackend $calDavBackend';
		$calDavBackend->updateShares($this, $add, $remove);
	}

	/**
	 * Returns the list of people whom this resource is shared with.
	 *
	 * Every element in this array should have the following properties:
	 *   * href - Often a mailto: address
	 *   * commonName - Optional, for example a first + last name
	 *   * status - See the Sabre\CalDAV\SharingPlugin::STATUS_ constants.
	 *   * readOnly - boolean
	 *   * summary - Optional, a description for the share
	 *
	 * @return array
	 */
	public function getShares() {
		/** @var CalDavBackend $calDavBackend */
		$calDavBackend = $this->caldavBackend;
		'@phan-var CalDavBackend $calDavBackend';
		return $calDavBackend->getShares($this->getResourceId());
	}

	/**
	 * @return int
	 */
	public function getResourceId() {
		return $this->calendarInfo['id'];
	}

	/**
	 * @return string
	 */
	public function getPrincipalURI() {
		return $this->calendarInfo['principaluri'];
	}

	public function getACL() {
		$acl =  [
			[
				'privilege' => '{DAV:}read',
				'principal' => $this->getOwner(),
				'protected' => true,
			]];
		if ($this->getName() === BirthdayService::BIRTHDAY_CALENDAR_URI) {
			$acl[] = [
				'privilege' => '{DAV:}write-properties',
				'principal' => $this->getOwner(),
				'protected' => true,
			];
		} else {
			$acl[] = [
				'privilege' => '{DAV:}write',
				'principal' => $this->getOwner(),
				'protected' => true,
			];
		}
		if ($this->getOwner() !== parent::getOwner()) {
			$acl[] =  [
					'privilege' => '{DAV:}read',
					'principal' => parent::getOwner(),
					'protected' => true,
				];
			if ($this->canWrite()) {
				$acl[] = [
					'privilege' => '{DAV:}write',
					'principal' => parent::getOwner(),
					'protected' => true,
				];
			}
		}
		if ($this->isPublic()) {
			$acl[] = [
				'privilege' => '{DAV:}read',
				'principal' => 'principals/system/public',
				'protected' => true,
			];
		}

		/** @var CalDavBackend $calDavBackend */
		$calDavBackend = $this->caldavBackend;
		'@phan-var CalDavBackend $calDavBackend';
		return $calDavBackend->applyShareAcl($this->getResourceId(), $acl);
	}

	public function getChildACL() {
		return $this->getACL();
	}

	public function getOwner() {
		if (isset($this->calendarInfo['{http://owncloud.org/ns}owner-principal'])) {
			return $this->calendarInfo['{http://owncloud.org/ns}owner-principal'];
		}
		return parent::getOwner();
	}

	public function delete() {
		if (isset($this->calendarInfo['{http://owncloud.org/ns}owner-principal'])) {
			$principal = 'principal:' . parent::getOwner();
			$shares = $this->getShares();
			$shares = \array_filter($shares, function ($share) use ($principal) {
				return $share['href'] === $principal;
			});
			if (empty($shares)) {
				throw new Forbidden();
			}

			/** @var CalDavBackend $calDavBackend */
			$calDavBackend = $this->caldavBackend;
			'@phan-var CalDavBackend $calDavBackend';
			$calDavBackend->updateShares($this, [], [
				$principal
			]);
			return;
		}
		parent::delete();
	}

	public function propPatch(PropPatch $propPatch) {
		$mutations = $propPatch->getMutations();
		// If this is a shared calendar, the user can only change the enabled property, to hide it.
		if (isset($this->calendarInfo['{http://owncloud.org/ns}owner-principal']) && (\sizeof($mutations) !== 1 || !isset($mutations['{http://owncloud.org/ns}calendar-enabled']))) {
			throw new Forbidden();
		}
		parent::propPatch($propPatch);
	}

	public function getChild($name) {
		$obj = $this->caldavBackend->getCalendarObject($this->calendarInfo['id'], $name);

		if (!$obj) {
			throw new NotFound('Calendar object not found');
		}

		if ($this->isShared() && $obj['classification'] === CalDavBackend::CLASSIFICATION_PRIVATE) {
			throw new NotFound('Calendar object not found');
		}

		$obj['acl'] = $this->getChildACL();

		return new CalendarObject($this->caldavBackend, $this->calendarInfo, $obj);
	}

	public function getChildren() {
		$objs = $this->caldavBackend->getCalendarObjects($this->calendarInfo['id']);
		$children = [];
		foreach ($objs as $obj) {
			if ($this->isShared() && $obj['classification'] === CalDavBackend::CLASSIFICATION_PRIVATE) {
				continue;
			}
			$obj['acl'] = $this->getChildACL();
			$children[] = new CalendarObject($this->caldavBackend, $this->calendarInfo, $obj);
		}
		return $children;
	}

	public function getMultipleChildren(array $paths) {
		$objs = $this->caldavBackend->getMultipleCalendarObjects($this->calendarInfo['id'], $paths);
		$children = [];
		foreach ($objs as $obj) {
			if ($this->isShared() && $obj['classification'] === CalDavBackend::CLASSIFICATION_PRIVATE) {
				continue;
			}
			$obj['acl'] = $this->getChildACL();
			$children[] = new CalendarObject($this->caldavBackend, $this->calendarInfo, $obj);
		}
		return $children;
	}

	public function childExists($name) {
		$obj = $this->caldavBackend->getCalendarObject($this->calendarInfo['id'], $name);
		if (!$obj) {
			return false;
		}
		if ($this->isShared() && $obj['classification'] === CalDavBackend::CLASSIFICATION_PRIVATE) {
			return false;
		}

		return true;
	}

	public function calendarQuery(array $filters) {
		$uris = $this->caldavBackend->calendarQuery($this->calendarInfo['id'], $filters);
		if ($this->isShared()) {
			return \array_filter($uris, function ($uri) {
				return $this->childExists($uri);
			});
		}

		return $uris;
	}

	/**
	 * @param boolean $value
	 * @return string|null
	 */
	public function setPublishStatus($value) {
		'@phan-var CalDavBackend $this->calDavBackend';
		$publicUri = $this->caldavBackend->setPublishStatus($value, $this);
		$this->calendarInfo['publicuri'] = $publicUri;
		return $publicUri;
	}

	/**
	 * @return mixed $value
	 */
	public function getPublishStatus() {
		'@phan-var CalDavBackend $this->calDavBackend';
		return $this->caldavBackend->getPublishStatus($this);
	}

	private function canWrite() {
		if (isset($this->calendarInfo['{http://owncloud.org/ns}read-only'])) {
			return !$this->calendarInfo['{http://owncloud.org/ns}read-only'];
		}
		return true;
	}

	private function isPublic() {
		return isset($this->calendarInfo['{http://owncloud.org/ns}public']);
	}

	private function isShared() {
		return isset($this->calendarInfo['{http://owncloud.org/ns}owner-principal']);
	}

	public function isSubscription() {
		return isset($this->calendarInfo['{http://calendarserver.org/ns/}source']);
	}
}
