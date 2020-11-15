<?php

/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2017 Artur Neumann artur@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Page\FilesPageElement;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Page\FilesPageElement\SharingDialogElement\PublicLinkTab;
use Page\OwncloudPage;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use Page\OwncloudPageElement\OCDialog;
use WebDriver\Exception\StaleElementReference;

/**
 * The Sharing Dialog
 *
 */
class SharingDialog extends OwncloudPage {

	/**
	 *
	 * @var string $path
	 */
	protected $path = '/index.php/apps/files/';
	private $shareWithFieldXpath = ".//*[contains(@class,'shareWithField')]";
	private $shareWithTooltipXpath = "/..//*[@class='tooltip-inner']";
	private $shareWithAutocompleteListXpath = ".//ul[contains(@class,'ui-autocomplete')]";
	private $autocompleteItemsTextXpath = "//*[@class='autocomplete-item-text']";
	private $suffixToIdentifyGroups = " Group";
	private $suffixToIdentifyUsers = " User";
	private $suffixToIdentifyRemoteUsers = " Federated";
	private $sharerInformationXpath = ".//*[@class='reshare']";
	private $sharedWithAndByRegEx = "Shared with you(?: and the group (.*))? by (.*)$";
	private $permissionsFieldByUserNameWithExtraInfo = "//*[@id='shareWithList']//span[contains(text(), '%s')]/parent::li[@data-share-with='%s']";
	private $permissionsFieldByUserName = "//*[@id='shareWithList']//span[contains(text(), '%s')]/parent::li";
	private $permissionsFieldByGroupName = "//*[@id='shareWithList']//span[contains(text(), '%s (group)')]/parent::li";
	private $permissionLabelXpath = ".//label[@for='%s']";
	private $showCrudsXpath = ".//span[@class='icon icon-settings-dark']";
	private $shareOptionsXpath = "//div[@class='shareOption']";
	private $publicLinksShareTabXpath = ".//li[contains(@class,'subtab-publicshare')]";
	private $publicLinksTabContentXpath = "//div[@id='shareDialogLinkList']";
	private $noSharingMessageXpath = "//div[@class='noSharingPlaceholder']";
	private $publicLinkRemoveBtnXpath = "//div[contains(@class, 'removeLink')]";
	private $publicLinkTitleXpath = "//span[@class='link-entry--title']";
	private $notifyByEmailBtnXpath = "//input[@name='mailNotification']";
	private $shareWithExpirationFieldXpath = "//*[@id='shareWithList']//span[@class='has-tooltip username' and .='%s']/..//input[contains(@class, 'expiration')]";
	private $shareWithClearExpirationFieldXpath = "/following-sibling::button[@class='removeExpiration']"; // in relation to $shareWithExpirationFieldXpath
	private $shareWithListXpath = "//ul[@id='shareWithList']/li";
	private $shareWithListDetailsXpath = "//div[@class='shareWithList__details']";
	private $userOrGroupNameSpanXpath = "//span[contains(@class,'username')]";
	private $unShareTabXpath = "//a[contains(@class,'unshare')]";
	private $sharedWithGroupAndSharerName = null;
	private $publicLinkRemoveDeclineMsg = "No";
	private $shareTreeItemByNameAndPathXpath = "//li[@class='shareTree-item' and strong/text()='%s' and span/text()='via %s']";
	private $userAndGroupsShareTabXpath = "//div[@class='dialogContainer']//li[contains(text(), 'User and Groups')]";

	/**
	 * @var string
	 */
	private $groupFramework = "%s (group)";

	/**
	 *
	 * @return NodeElement|NULL
	 * @throws ElementNotFoundException
	 */
	private function findShareWithField() {
		$shareWithField = $this->find("xpath", $this->shareWithFieldXpath);
		$this->assertElementNotNull(
			$shareWithField,
			__METHOD__ .
			" xpath $this->shareWithFieldXpath could not find share-with-field"
		);
		return $shareWithField;
	}

	/**
	 * @param string $user
	 * @param string $type
	 *
	 * @return null|NodeElement
	 */
	private function getExpirationFieldFor($user, $type = 'user') {
		if ($type === "group") {
			$user = \sprintf($this->groupFramework, $user);
		}
		return $this->find("xpath", \sprintf($this->shareWithExpirationFieldXpath, $user));
	}

	/**
	 * open the dropdown for share actions in the sidebar
	 *
	 * @param string $type
	 * @param string $receiver
	 *
	 * @return void
	 */
	public function openShareActionsDropDown($type, $receiver) {
		$this->openShareActionsIfNotOpen($type, $receiver);
		$this->waitTillElementIsNotNull($this->shareWithListDetailsXpath);
	}

	/**
	 * @param string $user
	 * @param string $type
	 *
	 * @return bool
	 */
	public function isExpirationFieldVisible($user, $type = 'user') {
		$field = $this->getExpirationFieldFor($user, $type);
		$visible = ($field and $field->isVisible());
		return $visible;
	}

	/**
	 * @param string $user
	 * @param string $type
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getExpirationDateFor($user, $type = 'user') {
		$field = $this->getExpirationFieldFor($user, $type);
		$this->assertElementNotNull(
			$field,
			"Could not find expiration field for " . $type . " '$user'"
		);
		return $field->getValue();
	}

	/**
	 * @param Session $session
	 * @param string $user
	 * @param string $type
	 * @param string $value
	 *
	 * @return void
	 */
	public function setExpirationDateFor(Session $session, $user, $type = 'user', $value = '') {
		$field = $this->getExpirationFieldFor($user, $type);
		$this->assertElementNotNull(
			$field,
			"Could not find expiration field for " . $type . " '$user'"
		);
		$field->setValue($value . "\n");
		// you need to click somewhere to make the calendar widget go away
		// here we click at the sharing dialog's "User and Groups" tab
		$this->find("xpath", $this->userAndGroupsShareTabXpath)->click();
		$this->waitForAjaxCallsToStartAndFinish($session);
	}

	/**
	 * @param Session $session
	 * @param string $user
	 * @param string $type
	 *
	 * @return void
	 */
	public function clearExpirationDateFor(Session $session, $user, $type = 'user') {
		$field = $this->getExpirationFieldFor($user, $type);
		$this->assertElementNotNull(
			$field,
			"Could not find expiration field for " . $type . " '$user'"
		);
		$removeBtn = $field->find("xpath", $this->shareWithClearExpirationFieldXpath);
		$this->assertElementNotNull(
			$removeBtn,
			"Could not find expiration field remove button for " . $type . " '$user'"
		);
		$removeBtn->click();
		$this->waitForAjaxCallsToStartAndFinish($session);
	}

	/**
	 * checks if the share-with field is visible
	 *
	 * @return bool
	 */
	public function isShareWithFieldVisible() {
		try {
			$visible = $this->findShareWithField()->isVisible();
		} catch (ElementNotFoundException $e) {
			$visible = false;
		}
		return $visible;
	}

	/**
	 * fills the "share-with" input field
	 *
	 * @param string $input
	 * @param Session $session
	 * @param int $timeout_msec how long to wait till the autocomplete comes back
	 *
	 * @return NodeElement AutocompleteElement
	 */
	public function fillShareWithField(
		$input, Session $session, $timeout_msec = STANDARD_UI_WAIT_TIMEOUT_MILLISEC
	) {
		$shareWithField = $this->findShareWithField();
		$this->fillFieldAndKeepFocus($shareWithField, $input, $session);
		$this->waitForAjaxCallsToStartAndFinish($session, $timeout_msec);
		return $this->getAutocompleteNodeElement();
	}

	/**
	 * get no sharing message
	 *
	 * @param Session $session
	 *
	 * @return string
	 */
	public function getNoSharingMessage(Session $session) {
		$noSharingMessage = $this->find("xpath", $this->noSharingMessageXpath);
		$this->assertElementNotNull(
			$noSharingMessage,
			__METHOD__ .
			" xpath $this->noSharingMessageXpath " .
			"could not find no sharing message"
		);
		return $this->getTrimmedText($noSharingMessage);
	}

	/**
	 * gets the NodeElement of the autocomplete list
	 *
	 * @return NodeElement
	 * @throws ElementNotFoundException
	 */
	public function getAutocompleteNodeElement() {
		$autocompleteNodeElement = $this->find(
			"xpath",
			$this->shareWithAutocompleteListXpath
		);
		$this->assertElementNotNull(
			$autocompleteNodeElement,
			__METHOD__ .
			" xpath $this->shareWithAutocompleteListXpath " .
			"could not find autocompleteNodeElement"
		);
		return $autocompleteNodeElement;
	}

	/**
	 * returns the user names as they could appear in an autocomplete list
	 *
	 * @param string|array $userNames
	 *
	 * @return string|array
	 */
	public function userStringsToMatchAutoComplete($userNames) {
		if (\is_array($userNames)) {
			$autocompleteStrings = [];
			foreach ($userNames as $userName => $userDisplayName) {
				$autocompleteStrings[$userName] = $userDisplayName . $this->suffixToIdentifyUsers;
			}
		} else {
			$autocompleteStrings = $userNames . $this->suffixToIdentifyUsers;
		}
		return $autocompleteStrings;
	}

	/**
	 * returns the group names as they could appear in an autocomplete list
	 *
	 * @param string|array $groupNames
	 *
	 * @return string|array
	 */
	public function groupStringsToMatchAutoComplete($groupNames) {
		if (\is_array($groupNames)) {
			$autocompleteStrings = [];
			foreach ($groupNames as $groupName => $groupData) {
				$autocompleteStrings[$groupName] = $groupName . $this->suffixToIdentifyGroups;
			}
		} else {
			$autocompleteStrings = $groupNames . $this->suffixToIdentifyGroups;
		}
		return $autocompleteStrings;
	}

	/**
	 * gets the items (users, groups) listed in the autocomplete list as an array
	 *
	 * @return array
	 * @throws ElementNotFoundException
	 */
	public function getAutocompleteItemsList() {
		$itemsArray = [];
		$itemElements = $this->getAutocompleteNodeElement()->findAll(
			"xpath",
			$this->autocompleteItemsTextXpath
		);
		foreach ($itemElements as $item) {
			\array_push($itemsArray, $this->getTrimmedText($item));
		}
		return $itemsArray;
	}

	/**
	 *
	 * @param string $nameToType what to type in the share with field
	 * @param string $nameToMatch what exact item to select
	 * @param Session $session
	 * @param int $maxRetries
	 * @param boolean $quiet
	 *
	 * @return void
	 * @throws ElementNotFoundException
	 */
	public function shareWithUserOrGroup(
		$nameToType, $nameToMatch, Session $session, $maxRetries = 5, $quiet = false
	) {
		$userFound = false;
		for ($retryCounter = 0; $retryCounter < $maxRetries; $retryCounter++) {
			$autocompleteNodeElement = $this->fillShareWithField(
				$nameToType, $session
			);
			$userElements = $autocompleteNodeElement->findAll(
				"xpath", $this->autocompleteItemsTextXpath
			);
			$userFound = false;
			foreach ($userElements as $user) {
				if ($this->getTrimmedText($user) === $nameToMatch) {
					$user->click();
					$this->waitForAjaxCallsToStartAndFinish($session);
					$userFound = true;
					break;
				}
			}
			if ($userFound === true) {
				break;
			} elseif ($quiet === false) {
				\error_log("Error while sharing file");
			}
		}
		if ($retryCounter > 0 && $quiet === false) {
			$message = "INFORMATION: retried to share file $retryCounter times";
			echo $message;
			\error_log($message);
		}
		if ($userFound !== true) {
			throw new ElementNotFoundException(
				__METHOD__ . " could not share with '$nameToMatch'"
			);
		}
	}

	/**
	 *
	 * @param string $name
	 * @param Session $session
	 * @param int $maxRetries
	 * @param boolean $quiet
	 *
	 * @return void
	 * @throws ElementNotFoundException
	 */
	public function shareWithUser(
		$name, Session $session, $maxRetries = 5, $quiet = false
	) {
		$this->shareWithUserOrGroup(
			$name, $name . $this->suffixToIdentifyUsers,
			$session, $maxRetries, $quiet
		);
	}

	/**
	 *
	 * @param string $name
	 * @param Session $session
	 * @param int $maxRetries
	 * @param boolean $quiet
	 *
	 * @return void
	 * @throws ElementNotFoundException
	 */
	public function shareWithRemoteUser(
		$name, Session $session, $maxRetries = 5, $quiet = false
	) {
		$this->shareWithUserOrGroup(
			$name, $name . $this->suffixToIdentifyRemoteUsers,
			$session, $maxRetries, $quiet
		);
	}

	/**
	 *
	 * @param string $name
	 * @param Session $session
	 * @param int $maxRetries
	 * @param boolean $quiet
	 *
	 * @return void
	 * @throws ElementNotFoundException
	 */
	public function shareWithGroup(
		$name, Session $session, $maxRetries = 5, $quiet = false
	) {
		$this->shareWithUserOrGroup(
			$name, $name . $this->suffixToIdentifyGroups,
			$session, $maxRetries, $quiet
		);
	}

	/**
	 *
	 * @param string $userOrGroup
	 * @param string $shareReceiverName
	 *
	 * @return string
	 * @throws ElementNotFoundException
	 */
	public function openShareActionsIfNotOpen($userOrGroup, $shareReceiverName) {
		if ($userOrGroup == "group") {
			$xpathLocator = \sprintf(
				$this->permissionsFieldByGroupName, $shareReceiverName
			);
		} else {
			// For users having same display name we pass $shareReceiverName in the format "displayName (userName)"
			// Let's hope display name of user in other cases is not in the above mentioned format, otherwise this will fail
			if (\preg_match('/\(*\)/', $shareReceiverName)
				&& !\strpos($shareReceiverName, '(federated)')
			) {
				$nameDisplayNameArray = \explode('(', $shareReceiverName);
				$shareReceiverName = \trim($nameDisplayNameArray[0]);
				$additionalInfo = \trim($nameDisplayNameArray[1], '()');
				$xpathLocator = \sprintf(
					$this->permissionsFieldByUserNameWithExtraInfo, $shareReceiverName, $additionalInfo
				);
			} else {
				$xpathLocator = \sprintf(
					$this->permissionsFieldByUserName, $shareReceiverName
				);
			}
		}
		$permissionsField = $this->waitTillElementIsNotNull($xpathLocator);
		$this->assertElementNotNull(
			$permissionsField,
			__METHOD__
			. " xpath $xpathLocator could not find share permissions field for user "
			. $shareReceiverName
		);
		$shareOptionsLocator = $permissionsField->find("xpath", $this->shareOptionsXpath);
		$this->assertElementNotNull(
			$shareOptionsLocator,
			__METHOD__
			. " xpath $this->shareOptionsXpath could not find share options for user "
			. $shareReceiverName
		);
		$shareOptionsStyle = $shareOptionsLocator->getAttribute("style");
		// check if showCrudsBtn is already clicked i.e. the permissions and expiration field are displayed
		// if showCrudsBtn is already clicked no need to click again
		if ($shareOptionsStyle === "display: inline-block;") {
			return $permissionsField;
		}

		$showCrudsBtn = $permissionsField->find("xpath", $this->showCrudsXpath);
		$this->assertElementNotNull(
			$showCrudsBtn,
			__METHOD__
			. " xpath $this->showCrudsXpath could not find show-cruds button for user "
			. $shareReceiverName
		);
		$showCrudsBtn->click();
		return $permissionsField;
	}

	/**
	 *
	 * @param string $userOrGroup
	 * @param string $shareReceiverName
	 * @param array $permissions [['permission' => 'yes|no']]
	 * @param Session $session
	 *
	 * @return void
	 * @throws ElementNotFoundException
	 */
	public function setSharingPermissions(
		$userOrGroup,
		$shareReceiverName,
		$permissions,
		Session $session
	) {
		$permissionsField = $this->openShareActionsIfNotOpen($userOrGroup, $shareReceiverName);
		foreach ($permissions as $permission => $value) {
			$value = \strtolower($value);

			//to find where to click is a little bit complicated
			//just setting the checkbox does not work
			//because the actual checkbox is not visible (left: -10000px;)
			//so we first find the checkbox, then get its id and find the label
			//that is associated with that id, that label is finally what we click
			$permissionCheckBox = $permissionsField->findField($permission);
			$this->assertElementNotNull(
				$permissionCheckBox,
				__METHOD__ .
				"could not find the permission check box for permission " .
				"'$permission' and user '$shareReceiverName'"
			);
			$checkBoxId = $permissionCheckBox->getAttribute("id");
			$this->assertElementNotNull(
				$checkBoxId,
				__METHOD__ .
				"could not find the id of the permission check box of " .
				"permission '$permission' and user '$shareReceiverName'"
			);

			$xpathLocator = \sprintf($this->permissionLabelXpath, $checkBoxId);
			$permissionLabel = $permissionsField->find("xpath", $xpathLocator);

			$this->assertElementNotNull(
				$permissionLabel,
				__METHOD__ .
				" xpath $xpathLocator " .
				"could not find the label of the permission check box of " .
				"permission '$permission' and user '$shareReceiverName'"
			);

			if (($value === "yes" && !$permissionCheckBox->isChecked())
				|| ($value === "no" && $permissionCheckBox->isChecked())
			) {
				// Some times when we try to click the label it gives StaleElementReference.
				try {
					$permissionLabel->click();
				} catch (StaleElementReference $e) {
				}
				$this->waitForAjaxCallsToStartAndFinish($session);
				// We recheck the value to make sure that the permission has changed since clicking it.
				if (($value === "yes" && !$permissionCheckBox->isChecked())
					|| ($value === "no" && $permissionCheckBox->isChecked())
				) {
					throw new \Exception("The checkbox for permission {$permissionLabel->getText()} could not be clicked.");
				}
			}
		}
	}

	/**
	 *
	 * @param string $userOrGroup
	 * @param string $shareReceiverName
	 * @param array $permissions [['permission' => 'yes|no']]
	 * @param Session $session
	 *
	 * @return void
	 * @throws ElementNotFoundException
	 */
	public function checkSharingPermissions(
		$userOrGroup,
		$shareReceiverName,
		$permissions,
		Session $session
	) {
		$permissionsField = $this->openShareActionsIfNotOpen($userOrGroup, $shareReceiverName);
		foreach ($permissions as $permission => $value) {
			$permissionCheckBox = $permissionsField->findField($permission);

			$this->waitForAjaxCallsToStartAndFinish($session);

			if ($value === "yes" && !$permissionCheckBox->isChecked()) {
				throw new \Exception("The checkbox for permission {$permission} is not enabled.");
			} elseif ($value === "no" && $permissionCheckBox->isChecked()) {
				throw new \Exception("The checkbox for permission {$permission} is enabled.");
			}
		}
	}

	/**
	 * gets the text of the tooltip associated with the "share-with" input
	 *
	 * @return string
	 * @throws ElementNotFoundException
	 */
	public function getShareWithTooltip() {
		$shareWithField = $this->findShareWithField();
		$shareWithTooltip = $shareWithField->find(
			"xpath", $this->shareWithTooltipXpath
		);
		$this->assertElementNotNull(
			$shareWithTooltip,
			__METHOD__ .
			" xpath $this->shareWithTooltipXpath " .
			"could not find share-with-tooltip"
		);
		return $this->getTrimmedText($shareWithTooltip);
	}

	/**
	 * gets the Element with the information about who has shared the current
	 * file/folder. This Element will contain the Avatar and some text.
	 *
	 * @return NodeElement
	 * @throws ElementNotFoundException
	 */
	public function findSharerInformationItem() {
		$sharerInformation = $this->find("xpath", $this->sharerInformationXpath);
		$this->assertElementNotNull(
			$sharerInformation,
			__METHOD__ .
			" xpath $this->sharerInformationXpath " .
			"could not find sharer information"
		);
		return $sharerInformation;
	}

	/**
	 * gets the group that the file/folder was shared with
	 * and the user that shared it
	 *
	 * @return array ["sharedWithGroup" => string, "sharer" => string]
	 * @throws \Exception
	 */
	public function getSharedWithGroupAndSharerName() {
		if ($this->sharedWithGroupAndSharerName === null) {
			$text = $this->getTrimmedText($this->findSharerInformationItem());
			if (\preg_match("/$this->sharedWithAndByRegEx/", $text, $matches)) {
				$this->sharedWithGroupAndSharerName = [
					"sharedWithGroup" => $matches [1],
					"sharer" => $matches [2]
				];
			} else {
				throw new \Exception(
					__METHOD__ .
					"could not find shared with group or sharer name"
				);
			}
		}
		return $this->sharedWithGroupAndSharerName;
	}

	/**
	 * gets the group that the file/folder was shared with
	 *
	 * @return mixed
	 */
	public function getSharedWithGroupName() {
		return $this->getSharedWithGroupAndSharerName()["sharedWithGroup"];
	}

	/**
	 * gets the display name of the user that has shared the current file/folder
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getSharerName() {
		return $this->getSharedWithGroupAndSharerName()["sharer"];
	}

	/**
	 * @param Session $session
	 *
	 * @return PublicLinkTab
	 * @throws ElementNotFoundException
	 */
	public function openPublicShareTab(Session $session) {
		$publicLinksShareTab = $this->find("xpath", $this->publicLinksShareTabXpath);
		$this->assertElementNotNull(
			$publicLinksShareTab,
			__METHOD__ .
			" xpath $this->publicLinksShareTabXpath " .
			"could not find public links share tab"
		);
		$publicLinksShareTab->click();
		/**
		 *
		 * @var PublicLinkTab $publicLinkTab
		 */
		$publicLinkTab = $this->getPage(
			"FilesPageElement\\SharingDialogElement\\PublicLinkTab"
		);
		$publicLinkTab->waitTillPageIsLoaded(
			$session,
			STANDARD_UI_WAIT_TIMEOUT_MILLISEC,
			$this->publicLinksTabContentXpath
		);
		return $publicLinkTab;
	}

	/**
	 * @param Session $session
	 * @param integer $number
	 *
	 * @return void
	 */
	public function removePublicLink(Session $session, $number = 1) {
		$this->clickRemoveBtn($session, $number);
		$ocDialog = $this->getLastOcDialog($session);
		$ocDialog->accept($session);
		$this->waitForAjaxCallsToStartAndFinish($session);
	}

	/**
	 * @param Session $session
	 *
	 * @return void
	 */
	public function cancelRemovePublicLinkOperation(Session $session) {
		$this->clickRemoveBtn($session);
		$ocDialog = $this->getLastOcDialog($session);
		$ocDialog->clickButton($session, $this->publicLinkRemoveDeclineMsg);
		$this->waitForAjaxCallsToStartAndFinish($session);
	}

	/**
	 * @param Session $session
	 * @param integer $number
	 *
	 * @return void
	 */
	private function clickRemoveBtn(Session $session, $number = 1) {
		$publicLinkRemoveBtns = $this->findAll("xpath", $this->publicLinkRemoveBtnXpath);
		$this->assertElementNotNull(
			$publicLinkRemoveBtns,
			__METHOD__ .
			" xpath $this->publicLinkRemoveBtnXpath " .
			"could not find public link remove buttons"
		);
		if ($number < 1) {
			throw new \Exception("Position cannot be less than 1");
		}
		$publicLinkRemoveBtn = $publicLinkRemoveBtns[$number - 1];
		$this->assertElementNotNull(
			$publicLinkRemoveBtn,
			__METHOD__ .
			" xpath $this->publicLinkRemoveBtnXpath " .
			"could not find public link remove button"
		);
		$publicLinkRemoveBtn->click();
	}

	/**
	 * @param Session $session
	 *
	 * @return OCDialog
	 */
	private function getLastOcDialog(Session $session) {
		$ocDialogs = $this->getOcDialogs();
		return \end($ocDialogs);
	}

	/**
	 *
	 * @param Session $session
	 * @param string $entryName
	 *
	 * @return void
	 * @throws \Exception
	 *
	 */
	public function checkThatNameIsNotInPublicLinkList(Session $session, $entryName) {
		$publicLinkTitles = $this->findAll("xpath", $this->publicLinkTitleXpath);
		foreach ($publicLinkTitles as $publicLinkTitle) {
			if ($entryName === $publicLinkTitle->getText()) {
				throw new \Exception("Public link with title" . $entryName . "is present");
			}
		}
	}

	/**
	 * get the list of shared with users
	 *
	 * @return NodeElement
	 */
	public function getShareWithList() {
		return $this->findAll('xpath', $this->shareWithListXpath);
	}

	/**
	 * delete user/group from shared with list
	 *
	 * @param Session $session
	 * @param string $userOrGroup
	 * @param string $username
	 *
	 * @return void
	 */
	public function deleteShareWith(Session $session, $userOrGroup, $username) {
		$shareWithList = $this->getShareWithList();
		foreach ($shareWithList as $item) {
			if ($userOrGroup == "group") {
				$username = \sprintf($this->groupFramework, $username);
			}
			if ($item->find('xpath', $this->userOrGroupNameSpanXpath)->getHtml() === $username) {
				$item->find('xpath', $this->unShareTabXpath)->click();
				$this->waitForAjaxCallsToStartAndFinish($session);
			}
		}
	}

	/**
	 *
	 * @param Session $session
	 * @param integer $count
	 *
	 * @return void
	 * @throws \Exception
	 *
	 */
	public function checkPublicLinkCount(Session $session, $count) {
		$publicLinkTitles = $this->findAll("xpath", $this->publicLinkTitleXpath);
		$publicLinkTitlesCount = \count($publicLinkTitles);
		if ($publicLinkTitlesCount != $count) {
			throw new \Exception("Found $publicLinkTitlesCount public link entries but expected $count");
		}
	}

	/**
	 * check if user with the given username is present in the shared with list
	 *
	 * @param string $username
	 *
	 * @return bool
	 */
	public function isUserPresentInShareWithList($username) {
		$shareWithList = $this->getShareWithList();
		foreach ($shareWithList as $user) {
			$actualUsername = $user->find('xpath', $this->userOrGroupNameSpanXpath);
			if ($actualUsername->getHtml() === $username) {
				return true;
			}
		}
		return false;
	}

	/**
	 * check if group with the given groupName is present in the shared with list
	 *
	 * @param string $groupName
	 *
	 * @return bool
	 */
	public function isGroupPresentInShareWithList($groupName) {
		$shareWithList = $this->getShareWithList();
		foreach ($shareWithList as $group) {
			$actualGroupName = $group->find('xpath', $this->userOrGroupNameSpanXpath);
			return $actualGroupName->getHtml() === \sprintf($this->groupFramework, $groupName);
		}
		return false;
	}

	/**
	 * send share notification by email to the sharee
	 *
	 * @param Session $session
	 *
	 * @return void
	 */
	public function sendShareNotificationByEmail($session) {
		$notifyByEmailBtn = $this->find("xpath", $this->notifyByEmailBtnXpath);
		$this->assertElementNotNull(
			$notifyByEmailBtn,
			__METHOD__ .
			" xpath $this->notifyByEmailBtnXpath " .
			"could not find notify by email button"
		);
		$notifyByEmailBtn->click();
		$this->waitForAjaxCallsToStartAndFinish($session);
	}

	/**
	 * @param string $type user|group|public link with name
	 * @param string $name user or group name
	 * @param string $path
	 *
	 * @return NodeElement
	 */
	public function getShareTreeItem($type, $name, $path) {
		if ($type === "group") {
			$name = \sprintf($this->groupFramework, $name);
		}
		$fullXpath = \sprintf($this->shareTreeItemByNameAndPathXpath, $name, $path);
		$item = $this->find("xpath", $fullXpath);
		$this->assertElementNotNull(
			$item,
			__METHOD__ .
			"\ncannot find item with name '$name' and path '$path'"
		);
		return $item;
	}

	/**
	 * waits for the dialog to appear
	 *
	 * @param Session $session
	 * @param int $timeout_msec
	 * @param string $xpath the xpath of the element to wait for
	 *                      required to be set
	 *
	 * @return void
	 */
	public function waitTillPageIsLoaded(
		Session $session,
		$timeout_msec = STANDARD_UI_WAIT_TIMEOUT_MILLISEC,
		$xpath = null
	) {
		if ($xpath === null) {
			throw new \InvalidArgumentException('$xpath needs to be set');
		}
		$this->waitForOutstandingAjaxCalls($session);
		$this->waitTillXpathIsVisible($xpath, $timeout_msec);
	}
}
