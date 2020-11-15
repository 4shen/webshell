<?php
/**
 * @author Achim Königs <garfonso@tratschtante.de>
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

use Exception;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\DAV\GroupPrincipalBackend;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\DateTimeParser;
use Sabre\VObject\Document;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\Property\VCard\DateAndOrTime;
use Sabre\VObject\Reader;
use Sabre\VObject\UUIDUtil;

class BirthdayService {
	const BIRTHDAY_CALENDAR_URI = 'contact_birthdays';

	/** @var GroupPrincipalBackend */
	private $principalBackend;

	/** @var CardDavBackend  */
	private $cardDavBackEnd;

	/** @var CalDavBackend  */
	private $calDavBackEnd;

	/**
	 * BirthdayService constructor.
	 *
	 * @param CalDavBackend $calDavBackEnd
	 * @param CardDavBackend $cardDavBackEnd
	 * @param GroupPrincipalBackend $principalBackend
	 */
	public function __construct(CalDavBackend $calDavBackEnd, CardDavBackend $cardDavBackEnd, GroupPrincipalBackend $principalBackend) {
		$this->calDavBackEnd = $calDavBackEnd;
		$this->cardDavBackEnd = $cardDavBackEnd;
		$this->principalBackend = $principalBackend;
	}

	/**
	 * @param int $addressBookId
	 * @param string $cardUri
	 * @param string $cardData
	 */
	public function onCardChanged($addressBookId, $cardUri, $cardData) {
		$targetPrincipals = $this->getAllAffectedPrincipals($addressBookId);

		$book = $this->cardDavBackEnd->getAddressBookById($addressBookId);
		$targetPrincipals[] = $book['principaluri'];
		$datesToSync = [
			['postfix' => '', 'field' => 'BDAY', 'symbol' => '*'],
			['postfix' => '-death', 'field' => 'DEATHDATE', 'symbol' => "†"],
			['postfix' => '-anniversary', 'field' => 'ANNIVERSARY', 'symbol' => "⚭"],
		];
		foreach ($targetPrincipals as $principalUri) {
			$calendar = $this->ensureCalendarExists($principalUri);
			foreach ($datesToSync as $type) {
				$this->updateCalendar($cardUri, $cardData, $book, $calendar['id'], $type);
			}
		}
	}

	/**
	 * @param int $addressBookId
	 * @param string $cardUri
	 */
	public function onCardDeleted($addressBookId, $cardUri) {
		$targetPrincipals = $this->getAllAffectedPrincipals($addressBookId);
		$book = $this->cardDavBackEnd->getAddressBookById($addressBookId);
		$targetPrincipals[] = $book['principaluri'];
		foreach ($targetPrincipals as $principalUri) {
			$calendar = $this->ensureCalendarExists($principalUri);
			foreach (['', '-death', '-anniversary'] as $tag) {
				$objectUri = $book['uri'] . '-' . $cardUri . $tag .'.ics';
				$this->calDavBackEnd->deleteCalendarObject($calendar['id'], $objectUri);
			}
		}
	}

	/**
	 * @param string $principal
	 * @return array|null
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function ensureCalendarExists($principal) {
		$book = $this->calDavBackEnd->getCalendarByUri($principal, self::BIRTHDAY_CALENDAR_URI);
		if ($book !== null) {
			return $book;
		}
		$this->calDavBackEnd->createCalendar($principal, self::BIRTHDAY_CALENDAR_URI, [
			'{DAV:}displayname' => 'Contact birthdays',
			'{http://apple.com/ns/ical/}calendar-color' => '#FFFFCA',
			'components'   => 'VEVENT',
		]);

		return $this->calDavBackEnd->getCalendarByUri($principal, self::BIRTHDAY_CALENDAR_URI);
	}

	/**
	 * @param string $cardData
	 * @param string $dateField
	 * @param string $summarySymbol
	 * @return null|VCalendar
	 */
	public function buildDateFromContact($cardData, $dateField, $summarySymbol) {
		if (empty($cardData)) {
			return null;
		}
		try {
			$doc = Reader::read($cardData);
			// We're always converting to vCard 4.0 so we can rely on the
			// VCardConverter handling the X-APPLE-OMIT-YEAR property for us.
			if (!$doc instanceof VCard) {
				return null;
			}
			$doc = $doc->convert(Document::VCARD40);
		} catch (Exception $e) {
			return null;
		}

		if (!isset($doc->{$dateField})) {
			return null;
		}
		if (!isset($doc->FN)) {
			return null;
		}
		$birthday = $doc->{$dateField};
		if (!(string)$birthday) {
			return null;
		}
		// Skip if the BDAY property is not of the right type.
		if (!$birthday instanceof DateAndOrTime) {
			return null;
		}

		// Skip if we can't parse the BDAY value.
		try {
			$dateParts = DateTimeParser::parseVCardDateTime($birthday->getValue());
		} catch (InvalidDataException $e) {
			return null;
		}

		$unknownYear = false;
		if (!$dateParts['year']) {
			$birthday = '1900-' . $dateParts['month'] . '-' . $dateParts['date'];

			$unknownYear = true;
		}

		try {
			$date = new \DateTime($birthday);
		} catch (Exception $e) {
			return null;
		}
		if ($unknownYear) {
			$summary = $doc->FN->getValue() . ' ' . $summarySymbol;
		} else {
			$year = (int)$date->format('Y');
			$summary = $doc->FN->getValue() . " ($summarySymbol$year)";
		}
		$vCal = new VCalendar();
		$vCal->VERSION = '2.0';
		$vEvent = $vCal->createComponent('VEVENT');
		$vEvent->add('DTSTART');
		$vEvent->DTSTART->setDateTime(
			$date
		);
		$vEvent->DTSTART['VALUE'] = 'DATE';
		$vEvent->add('DTEND');
		$date->add(new \DateInterval('P1D'));
		$vEvent->DTEND->setDateTime(
			$date
		);
		$vEvent->DTEND['VALUE'] = 'DATE';
		$vEvent->{'UID'} = UUIDUtil::getUUID();
		$vEvent->{'RRULE'} = 'FREQ=YEARLY';
		$vEvent->{'SUMMARY'} = $summary;
		$vEvent->{'TRANSP'} = 'TRANSPARENT';
		$alarm = $vCal->createComponent('VALARM');
		$alarm->add($vCal->createProperty('TRIGGER', '-PT0M', ['VALUE' => 'DURATION']));
		$alarm->add($vCal->createProperty('ACTION', 'DISPLAY'));
		$alarm->add($vCal->createProperty('DESCRIPTION', $vEvent->{'SUMMARY'}));
		$vEvent->add($alarm);
		$vCal->add($vEvent);
		return $vCal;
	}

	/**
	 * @param string $user
	 */
	public function syncUser($user) {
		$principal = 'principals/users/'.$user;
		$this->ensureCalendarExists($principal);
		$books = $this->cardDavBackEnd->getAddressBooksForUser($principal);
		foreach ($books as $book) {
			$cards = $this->cardDavBackEnd->getCards($book['id']);
			foreach ($cards as $card) {
				$this->onCardChanged($book['id'], $card['uri'], $card['carddata']);
			}
		}
	}

	/**
	 * @param string $existingCalendarData
	 * @param VCalendar $newCalendarData
	 * @return bool
	 */
	public function birthdayEvenChanged($existingCalendarData, $newCalendarData) {
		try {
			$existingBirthday = Reader::read($existingCalendarData);
		} catch (Exception $ex) {
			return true;
		}
		if ($newCalendarData->VEVENT->DTSTART->getValue() !== $existingBirthday->VEVENT->DTSTART->getValue() ||
			$newCalendarData->VEVENT->SUMMARY->getValue() !== $existingBirthday->VEVENT->SUMMARY->getValue()
		) {
			return true;
		}
		return false;
	}

	/**
	 * @param integer $addressBookId
	 * @return mixed
	 */
	protected function getAllAffectedPrincipals($addressBookId) {
		$targetPrincipals = [];
		$shares = $this->cardDavBackEnd->getShares($addressBookId);
		foreach ($shares as $share) {
			if ($share['{http://owncloud.org/ns}group-share']) {
				$users = $this->principalBackend->getGroupMemberSet($share['{http://owncloud.org/ns}principal']);
				foreach ($users as $user) {
					$targetPrincipals[] = $user['uri'];
				}
			} else {
				$targetPrincipals[] = $share['{http://owncloud.org/ns}principal'];
			}
		}
		return \array_values(\array_unique($targetPrincipals, SORT_STRING));
	}

	/**
	 * @param string $cardUri
	 * @param string  $cardData
	 * @param array $book
	 * @param int $calendarId
	 * @param string $type
	 */
	private function updateCalendar($cardUri, $cardData, $book, $calendarId, $type) {
		$objectUri = $book['uri'] . '-' . $cardUri . $type['postfix'] . '.ics';
		$calendarData = $this->buildDateFromContact($cardData, $type['field'], $type['symbol']);
		$existing = $this->calDavBackEnd->getCalendarObject($calendarId, $objectUri);
		if ($calendarData === null) {
			if ($existing !== null) {
				$this->calDavBackEnd->deleteCalendarObject($calendarId, $objectUri);
			}
		} else {
			if ($existing === null) {
				$this->calDavBackEnd->createCalendarObject($calendarId, $objectUri, $calendarData->serialize());
			} else {
				if ($this->birthdayEvenChanged($existing['calendardata'], $calendarData)) {
					$this->calDavBackEnd->updateCalendarObject($calendarId, $objectUri, $calendarData->serialize());
				}
			}
		}
	}
}
