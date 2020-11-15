<?php
/**
 * @author Olivier Mehani <shtrom-github@ssji.net>
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

namespace OCA\DAV\CardDAV;

use OCP\IImage;
use OCP\IUser;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property\Text;

class Converter {

	/**
	 * @param IUser $user
	 * @return VCard
	 */
	public function createCardFromUser(IUser $user) {
		$uid = $user->getUID();
		$displayName = $user->getDisplayName();
		$displayName = empty($displayName) ? $uid : $displayName;
		$emailAddress = $user->getEMailAddress();
		$cloudId = $user->getCloudId();
		$image = $this->getAvatarImage($user);

		$vCard = new VCard();
		$vCard->VERSION = '3.0';
		$vCard->UID = $uid;
		if (!empty($displayName)) {
			$vCard->FN = $displayName;
			$vCard->N = $this->splitFullName($displayName);
		}
		if (!empty($emailAddress)) {
			$vCard->add(new Text($vCard, 'EMAIL', $emailAddress, ['TYPE' => 'OTHER']));
		}
		if (!empty($cloudId)) {
			$vCard->CLOUD = $cloudId;
		}
		if ($image) {
			$vCard->add('PHOTO', $image->data(), ['ENCODING' => 'b', 'TYPE' => $image->mimeType()]);
		}
		$vCard->validate();

		return $vCard;
	}

	/**
	 * @param VCard $vCard
	 * @param IUser $user
	 * @return bool
	 */
	public function updateCard(VCard $vCard, IUser $user) {
		$uid = $user->getUID();
		$displayName = $user->getDisplayName();
		$displayName = empty($displayName) ? $uid : $displayName;
		$emailAddress = $user->getEMailAddress();
		$cloudId = $user->getCloudId();
		$image = $this->getAvatarImage($user);

		$updated = false;
		if ($this->propertyNeedsUpdate($vCard, 'FN', $displayName)) {
			$vCard->FN = new Text($vCard, 'FN', $displayName);
			unset($vCard->N);
			$vCard->add(new Text($vCard, 'N', $this->splitFullName($displayName)));
			$updated = true;
		}
		if ($this->propertyNeedsUpdate($vCard, 'EMAIL', $emailAddress)) {
			$vCard->EMAIL = new Text($vCard, 'EMAIL', $emailAddress);
			$updated = true;
		}
		if ($this->propertyNeedsUpdate($vCard, 'CLOUD', $cloudId)) {
			$vCard->CLOUD = new Text($vCard, 'CLOUD', $cloudId);
			$updated = true;
		}

		if ($this->propertyNeedsUpdate($vCard, 'PHOTO', $image)) {
			unset($vCard->PHOTO);
			$vCard->add('PHOTO', $image->data(), ['ENCODING' => 'b', 'TYPE' => $image->mimeType()]);
			$updated = true;
		}

		if (empty($emailAddress) && $vCard->EMAIL !== null) {
			unset($vCard->EMAIL);
			$updated = true;
		}
		if (empty($cloudId) && $vCard->CLOUD !== null) {
			unset($vCard->CLOUD);
			$updated = true;
		}
		if (empty($image) && $vCard->PHOTO !== null) {
			unset($vCard->PHOTO);
			$updated = true;
		}

		return $updated;
	}

	/**
	 * @param VCard $vCard
	 * @param string $name
	 * @param string|IImage $newValue
	 * @return bool
	 */
	private function propertyNeedsUpdate(VCard $vCard, $name, $newValue) {
		if ($newValue === null) {
			return false;
		}
		$value = $vCard->__get($name);
		if ($value !== null) {
			$value = $value->getValue();
			$newValue = $newValue instanceof IImage ? $newValue->data() : $newValue;

			return $value !== $newValue;
		}
		return true;
	}

	/**
	 * @param string $fullName
	 * @return string[]
	 */
	public function splitFullName($fullName) {
		// Very basic western style parsing. I'm not gonna implement
		// https://github.com/android/platform_packages_providers_contactsprovider/blob/master/src/com/android/providers/contacts/NameSplitter.java ;)

		$elements = \explode(' ', $fullName);
		$result = ['', '', '', '', ''];
		if (\count($elements) > 2) {
			$result[0] = \implode(' ', \array_slice($elements, \count($elements)-1));
			$result[1] = $elements[0];
			$result[2] = \implode(' ', \array_slice($elements, 1, \count($elements)-2));
		} elseif (\count($elements) === 2) {
			$result[0] = $elements[1];
			$result[1] = $elements[0];
		} else {
			$result[0] = $elements[0];
		}

		return $result;
	}

	/**
	 * @param IUser $user
	 * @return null|IImage
	 */
	private function getAvatarImage(IUser $user) {
		try {
			$image = $user->getAvatarImage(96);
			return $image;
		} catch (\Exception $ex) {
			return null;
		}
	}
}
