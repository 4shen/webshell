<?php

/**
 * ownCloud
 *
 * @author Paurakh Sharma Humagain <paurakh@jankaritech.com>
 * @copyright Copyright (c) 2018 Paurakh Sharma Humagain paurakh@jankaritech.com
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

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\AdminSharingSettingsPage;
use PHPUnit\Framework\Assert;
use TestHelpers\SetupHelper;

require_once 'bootstrap.php';

/**
 * WebUI AdminSharingSettings context.
 */
class WebUIAdminSharingSettingsContext extends RawMinkContext implements Context {
	private $adminSharingSettingsPage;

	/**
	 *
	 * @var WebUIGeneralContext
	 */
	private $webUIGeneralContext;

	/**
	 *
	 * @var FeatureContext
	 */
	private $featureContext;

	/**
	 * WebUIAdminSharingSettingsContext constructor.
	 *
	 * @param AdminSharingSettingsPage $adminSharingSettingsPage
	 */
	public function __construct(
		AdminSharingSettingsPage $adminSharingSettingsPage
	) {
		$this->adminSharingSettingsPage = $adminSharingSettingsPage;
	}

	/**
	 * @When the administrator browses to the admin sharing settings page
	 * @Given the administrator has browsed to the admin sharing settings page
	 *
	 * @return void
	 */
	public function theAdminBrowsesToTheAdminSharingSettingsPage() {
		$this->webUIGeneralContext->adminLogsInUsingTheWebUI();
		$this->adminSharingSettingsPage->open();
		$this->adminSharingSettingsPage->waitTillPageIsLoaded($this->getSession());
		$this->webUIGeneralContext->setCurrentPageObject($this->adminSharingSettingsPage);
	}

	/**
	 * @When /^the administrator (enables|disables) the share API using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesShareApiUsingTheWebui($action) {
		$this->adminSharingSettingsPage->toggleShareApi(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) share via link using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesShareViaLink($action) {
		$this->adminSharingSettingsPage->toggleShareViaLink(
			$this->getSession(), $action
		);
	}

	/**
	 *
	 * @param NodeElement|null $checkbox
	 *
	 * @return void
	 */
	private function assertCheckBoxIsChecked($checkbox) {
		Assert::assertNotNull($checkbox, "checkbox does not exist");
		Assert::assertTrue($checkbox->isChecked(), "checkbox is not checked");
	}

	/**
	 * @Then the default expiration date checkbox for user shares should be enabled on the webUI
	 *
	 * @return void
	 */
	public function setDefaultExpirationDateForUserSharesCheckboxShouldBeEnabled() {
		$checkboxElement = $this->adminSharingSettingsPage->getDefaultExpirationForUserShareElement();
		$this->assertCheckBoxIsChecked($checkboxElement);
	}

	/**
	 * @Then the default expiration date checkbox for group shares should be enabled on the webUI
	 *
	 * @return void
	 */
	public function setDefaultExpirationDateForGroupCheckboxSharesShouldBeEnabled() {
		$checkboxElement = $this->adminSharingSettingsPage->getDefaultExpirationForGroupShareElement();
		$this->assertCheckBoxIsChecked($checkboxElement);
	}

	/**
	 * @Then the enforce maximum expiration date checkbox for user shares should be enabled on the webUI
	 *
	 * @return void
	 */
	public function enforceMaximumExpirationDateForUserSharesCheckboxShouldBeEnabled() {
		$checkboxElement = $this->adminSharingSettingsPage->getEnforceExpireDateUserShareElement();
		$this->assertCheckBoxIsChecked($checkboxElement);
	}

	/**
	 * @Then the enforce maximum expiration date checkbox for group shares should be enabled on the webUI
	 *
	 * @return void
	 */
	public function enforceMaximumExpirationDateForGroupSharesCheckboxShouldBeEnabled() {
		$checkboxElement = $this->adminSharingSettingsPage->getEnforceExpireDateGroupShareElement();
		$this->assertCheckBoxIsChecked($checkboxElement);
	}

	/**
	 * @Then the expiration date for user shares should set to :days days on the webUI
	 *
	 * @param int $days
	 *
	 * @return void
	 */
	public function expirationDateForUserSharesShouldBeSetToXDays($days) {
		$expirationDays = $this->adminSharingSettingsPage->getUserShareExpirationDays();
		Assert::assertEquals(
			$days,
			$expirationDays,
			__METHOD__
			. " The expiration date for user shares was expected to be set to '$days' days, "
			. "but was actually set to '$expirationDays' days"
		);
	}

	/**
	 * @Then the expiration date for group shares should set to :days days on the webUI
	 *
	 * @param int $days
	 *
	 * @return void
	 */
	public function expirationDateForGroupSharesShouldBeSetToXDays($days) {
		$expirationDays = $this->adminSharingSettingsPage->getGroupShareExpirationDays();
		Assert::assertEquals(
			$days,
			$expirationDays,
			__METHOD__
			. " The expiration date for group shares was expected to be set to '$days' days, "
			. "but was actually set to '$expirationDays' days"
		);
	}

	/**
	 * @When /^the administrator (enables|disables) public uploads using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesPublicUpload($action) {
		$this->adminSharingSettingsPage->togglePublicUpload(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) mail notification on public link share using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesMailNotificationOnPublicLinkShare($action) {
		$this->adminSharingSettingsPage->toggleMailNotification(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) social media share on public link share using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesSocialShareOnPublicLinkShare($action) {
		$this->adminSharingSettingsPage->toggleSocialShareOnPublicLinkShare(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) enforce password protection for read-only links using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesEnforcePasswordProtectionForReadOnlyLinks($action) {
		$this->adminSharingSettingsPage->toggleEnforcePasswordProtectionForReadOnlyLinks(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) enforce password protection for read and write links using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesEnforcePasswordProtectionForReadWriteLinks($action) {
		$this->adminSharingSettingsPage->toggleEnforcePasswordProtectionForReadWriteLinks(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) enforce password protection for read and write and delete links using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesEnforcePasswordProtectionForReadWriteDeleteLinks($action) {
		$this->adminSharingSettingsPage->toggleEnforcePasswordProtectionForReadWriteDeleteLinks(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) enforce password protection for upload only links using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesEnforcePasswordProtectionForWriteOnlyLinks($action) {
		$this->adminSharingSettingsPage->toggleEnforcePasswordProtectionForWriteOnlyLinks(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) resharing using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesDisableResharing($action) {
		$this->adminSharingSettingsPage->toggleResharing(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) sharing with groups using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function adminTogglesSharingWithGroupUsingTheWebui($action) {
		$this->adminSharingSettingsPage->toggleGroupSharing(
			$this->getSession(), $action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) restrict users to only share with their group members using the webUI$/
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function theAdminRestrictsUsersToOnlyShareWithTheirGroupMemberUsingTheWebui($action) {
		$this->adminSharingSettingsPage->toggleRestrictUsersToOnlyShareWithTheirGroupMembers(
			$this->getSession(), $action
		);
	}

	/**
	 * @When the administrator enables exclude groups from sharing using the webUI
	 *
	 * @return void
	 */
	public function theAdministratorEnablesExcludeGroupsFromSharingUsingTheWebui() {
		$this->adminSharingSettingsPage->enableExcludeGroupFromSharing(
			$this->getSession()
		);
	}

	/**
	 * @When the administrator enables default expiration date for user shares using the webUI
	 *
	 * @return void
	 */
	public function administratorEnablesDefaultExpirationDateForUserShares() {
		$this->adminSharingSettingsPage->enableDefaultExpirationDateForUserShares(
			$this->getSession()
		);
	}

	/**
	 * @When the administrator enforces maximum expiration date for user shares using the webUI
	 *
	 * @return void
	 */
	public function administratorEnforcesMaximumExpirationDateForUserShares() {
		$this->adminSharingSettingsPage->enforceMaximumExpirationDateForUserShares(
			$this->getSession()
		);
	}

	/**
	 * @When the administrator enforces maximum expiration date for group shares using the webUI
	 *
	 * @return void
	 */
	public function administratorEnforcesMaximumExpirationDateForGroupShares() {
		$this->adminSharingSettingsPage->enforceMaximumExpirationDateForGroupShares(
			$this->getSession()
		);
	}

	/**
	 * @When the administrator updates the user share expiration date to :days days using the webUI
	 *
	 * @param int $days
	 *
	 * @return void
	 */
	public function administratorUpdatesUserShareExpirationTo($days) {
		$this->adminSharingSettingsPage->setExpirationDaysForUserShare($days, $this->getSession());
	}

	/**
	 * @When the administrator updates the group share expiration date to :days days using the webUI
	 *
	 * @param int $days
	 *
	 * @return void
	 */
	public function administratorUpdatesGroupShareExpirationTo($days) {
		$this->adminSharingSettingsPage->setExpirationDaysForGroupShare($days, $this->getSession());
	}

	/**
	 * @When the administrator enables default expiration date for group shares using the webUI
	 *
	 * @return void
	 */
	public function theAdministratorEnablesDefaultExpirationDateForGroupShares() {
		$this->adminSharingSettingsPage->enableDefaultExpirationDateForGroupShares(
			$this->getSession()
		);
	}

	/**
	 * @When the administrator enables restrict users to only share with groups they are member of using the webUI
	 *
	 * @return void
	 */
	public function theAdministratorEnablesRestrictUsersToOnlyShareWithGroupsTheyAreMemberOfUsingTheWebui() {
		$this->adminSharingSettingsPage->restrictUserToOnlyShareWithMembershipGroup(
			$this->getSession()
		);
	}

	/**
	 * @When the administrator adds group :group to the exclude group from sharing list using the webUI
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorAddsGroupToTheExcludeGroupFromSharingList($group) {
		$this->adminSharingSettingsPage->addGroupToExcludeGroupsFromSharingList(
			$this->getSession(), $group
		);
	}

	/**
	 * @When the administrator excludes group :group from receiving shares using the webUI
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorExcludesGroupFromReceivingSharesUsingTheWebui($group) {
		$this->adminSharingSettingsPage->addGroupToExcludedFromReceivingShares(
			$this->getSession(), $group
		);
	}

	/**
	 * @When /^the administrator (enables|disables) add server automatically once a federation share was created successfully using the webUI$/
	 *
	 * @param string (enable | disable) $action
	 *
	 * @return void
	 */
	public function theAdministratorEnablesAddServerAutomatically($action) {
		$this->adminSharingSettingsPage->toggleAutoAddServer(
			$this->getSession(),
			$action
		);
	}

	/**
	 * @When /^the administrator (enables|disables) permission (create|change|delete|share) for default user and group share using the webUI$/
	 *
	 * @param string (enable | disable) $action
	 * @param string $permissionValue
	 *
	 * @return void
	 */
	public function theAdministratorEnablesDefaultSharePermission($action, $permissionValue) {
		$this->adminSharingSettingsPage->toggleDefaultSharePermissions(
			$this->getSession(),
			$action,
			$permissionValue
		);
	}

	/**
	 * @When the administrator adds :url as a trusted server using the webUI
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public function theAdministratorAddsAsATrustedServerUsingTheWebui($url) {
		$this->adminSharingSettingsPage->addTrustedServer(
			$this->getSession(),
			$this->featureContext->substituteInLineCodes($url)
		);
	}

	/**
	 * @When the administrator deletes url :url from the trusted server list using the webUI
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public function theAdministratorDeletesAsATrustedServerUsingTheWebui($url) {
		$this->adminSharingSettingsPage->deleteTrustedServer(
			$this->getSession(),
			$this->featureContext->substituteInLineCodes($url)
		);
	}

	/**
	 * @Then a trusted server error message should be displayed on the webUI with the text :text
	 *
	 * @param string $text
	 *
	 * @return void
	 */
	public function aErrorMessageForTrustedServerShouldContain($text) {
		$msg = $this->adminSharingSettingsPage->getTrustedServerErrorMsg();
		Assert::assertStringContainsString(
			$text,
			$msg,
			__METHOD__
			. " The text in the trusted server error message was expected to be '$text', but got '$msg' instead "
		);
	}

	/**
	 * This will run before EVERY scenario.
	 * It will set the properties for this object.
	 *
	 * @BeforeScenario @webUI
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function before(BeforeScenarioScope $scope) {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->webUIGeneralContext = $environment->getContext('WebUIGeneralContext');
		$this->featureContext = $environment->getContext('FeatureContext');
		SetupHelper::runOcc(
			['config:app:set files_sharing blacklisted_receiver_groups --value=']
		);
	}
}
