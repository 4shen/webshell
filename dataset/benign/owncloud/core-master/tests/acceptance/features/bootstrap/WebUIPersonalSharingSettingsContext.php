<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2019 Artur Neumann artur@jankaritech.com
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

require_once 'bootstrap.php';

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\PersonalSharingSettingsPage;
use PHPUnit\Framework\Assert;

/**
 * steps for personal sharing settings
 */
class WebUIPersonalSharingSettingsContext extends RawMinkContext implements Context {
	private $personalSharingSettingsPage;

	/**
	 *
	 * @param PersonalSharingSettingsPage $personalSharingSettingsPage
	 */
	public function __construct(
		PersonalSharingSettingsPage $personalSharingSettingsPage
	) {
		$this->personalSharingSettingsPage = $personalSharingSettingsPage;
	}

	/**
	 * @When the user browses to the personal sharing settings page
	 * @Given the user has browsed to the personal sharing settings page
	 *
	 * @return void
	 */
	public function theUserBrowsesToThePersonalSharingSettingsPage() {
		$this->personalSharingSettingsPage->open();
		$this->personalSharingSettingsPage->waitTillPageIsLoaded(
			$this->getSession()
		);
	}

	/**
	 * @When /^the user (disables|enables) automatically accepting new incoming local shares$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function switchAutoAcceptingLocalShares($action) {
		$this->personalSharingSettingsPage->toggleAutoAcceptingLocalShares(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the user (disables|enables) automatically accepting remote shares from trusted servers$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function switchAutoAcceptingFederatedShares($action) {
		$this->personalSharingSettingsPage->toggleAutoAcceptingFederatedShares(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the user (disables|enables) allow finding you via autocomplete in share dialog$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function switchAllowFindingYouViaAutocompleteInShareDialog($action) {
		$this->personalSharingSettingsPage->toggleFindingYouViaAutocomplete(
			$this->getSession(), $action
		);
	}

	/**
	 * @Then User-based auto accepting checkbox should not be displayed on the personal sharing settings page on the webUI
	 *
	 * @return void
	 */
	public function autoAcceptingCheckboxShouldNotBeDisplayedOnThePersonalSharingSettingsPageOnTheWebui() {
		Assert::assertFalse(
			$this->personalSharingSettingsPage->isAutoAcceptLocalSharesCheckboxDisplayed(),
			__METHOD__
			. " User-based auto accepting checkbox is displayed on the personal sharing settings page."
		);
	}

	/**
	 * @Then User-based auto accepting from trusted servers checkbox should not be displayed on the personal sharing settings page on the webUI
	 *
	 * @return void
	 */
	public function autoAcceptingFederatedCheckboxShouldNotBeDisplayedOnThePersonalSharingSettingsPageOnTheWebui() {
		Assert::assertFalse(
			$this->personalSharingSettingsPage->isAutoAcceptFederatedSharesCheckboxDisplayed(),
			__METHOD__
			. " User-based auto accepting from trusted servers checkbox is displayed on the personal sharing settings page."
		);
	}

	/**
	 * @Then allow finding you via autocomplete checkbox should not be displayed on the personal sharing settings page
	 *
	 * @return void
	 */
	public function allowFindingYouViaAutocompleteCheckboxShouldNotBeDisplayedOnThePersonalSharingSettingsPage() {
		Assert::assertFalse(
			$this->personalSharingSettingsPage->isAllowFindingYouViaAutocompleteCheckboxDisplayed(),
			__METHOD__
			. " Allow finding you via autocomplete checkbox is displayed on the personal sharing settings page."
		);
	}
}
