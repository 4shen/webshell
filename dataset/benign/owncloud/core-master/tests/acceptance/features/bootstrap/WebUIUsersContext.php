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

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\LoginPage;
use Page\UsersPage;
use Page\OwncloudPage;
use PHPUnit\Framework\Assert;
use TestHelpers\AppConfigHelper;

require_once 'bootstrap.php';

/**
 * WebUI Users context.
 */
class WebUIUsersContext extends RawMinkContext implements Context {
	private $usersPage;

	/**
	 *
	 * @var LoginPage
	 */
	private $loginPage;

	/**
	 *
	 * @var OwncloudPage
	 */
	private $owncloudPage;

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

	private $appParameterValues = null;

	/**
	 * WebUIUsersContext constructor.
	 *
	 * @param UsersPage $usersPage
	 * @param LoginPage $loginPage
	 * @param OwncloudPage $owncloudPage
	 */
	public function __construct(UsersPage $usersPage, LoginPage $loginPage, OwncloudPage $owncloudPage) {
		$this->usersPage = $usersPage;
		$this->loginPage = $loginPage;
		$this->owncloudPage = $owncloudPage;
	}

	/**
	 * @When the user/administrator browses to the users page
	 * @Given the user/administrator has browsed to the users page
	 *
	 * @return void
	 */
	public function theUserBrowsesToTheUsersPage() {
		$this->usersPage->open();
		$this->usersPage->waitTillPageIsLoaded($this->getSession());
	}

	/**
	 * @When the administrator sets/changes the quota of user :username to :quota using the webUI
	 *
	 * @param string $username
	 * @param string $quota
	 *
	 * @return void
	 */
	public function theAdministratorSetsTheQuotaOfUserUsingTheWebUI(
		$username, $quota
	) {
		$username = $this->featureContext->getActualUsername($username);
		$this->usersPage->setQuotaOfUserTo($username, $quota, $this->getSession());
	}

	/**
	 * @When the administrator sets/changes the quota of user :username to the invalid string :quota using the webUI
	 *
	 * @param string $username
	 * @param string $quota
	 *
	 * @return void
	 */
	public function theAdministratorSetsInvalidQuotaOfUserUsingTheWebUI(
		$username, $quota
	) {
		$username = $this->featureContext->getActualUsername($username);
		$this->usersPage->setQuotaOfUserTo(
			$username, $quota, $this->getSession(), false
		);
	}

	/**
	 * @When /^the administrator (attempts to create|creates) a user with the name "([^"]*)" (?:and )?the password "([^"]*)"(?: and the email "([^"]*)")?(?: that is a member of these groups)? using the webUI$/
	 *
	 * @param string $attemptTo
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param TableNode $groupsTable table of groups with a heading | group |
	 *
	 * @return void
	 */
	public function theAdminCreatesAUserUsingTheWebUI(
		$attemptTo, $username, $password, $email = null, TableNode $groupsTable = null
	) {
		$username = $this->featureContext->getActualUsername($username);
		$password = $this->featureContext->getActualPassword($password);
		if ($groupsTable !== null) {
			$groups = $groupsTable->getColumn(0);
			//get rid of the header
			unset($groups[0]);
		} else {
			$groups = null;
		}
		$this->usersPage->createUser(
			$this->getSession(), $username, $password, $email, $groups
		);

		$shouldExist = ($attemptTo === "creates");

		$this->featureContext->addUserToCreatedUsersList(
			$username, $password, $username, $email, $shouldExist
		);
		if (\is_array($groups)) {
			foreach ($groups as $group) {
				$this->featureContext->addGroupToCreatedGroupsList($group);
			}
		}
	}

	/**
	 * @When the administrator attempts to create these users then the notifications should be as listed
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theAdministratorAttemptsToCreateAUser(TableNode $table) {
		foreach ($table->getHash() as $row) {
			$user = $row['user'];
			$this->theAdminCreatesAUserUsingTheWebUI(
				"attempts to create", $user, $row['password']
			);
			$expectedNotification = $row['notification'];
			$actualNotification = $this->owncloudPage->getNotifications();
			Assert::assertNotEmpty($actualNotification, " User " . $user . " has been created ");
			Assert::assertSame(
				$expectedNotification,
				$actualNotification[0],
				"Got unexpected notification while creating user " . $user
			);
			$this->theUserReloadsTheUsersPage();
			$this->featureContext->userShouldNotExist($user);
		}
	}

	/**
	 * @When /^the administrator (attempts to create|creates) a user with the name "([^"]*)" and the email "([^"]*)" without a password(?: that is a member of these groups)? using the webUI$/
	 *
	 * @param string $attemptTo
	 * @param string $username
	 * @param string $email
	 * @param TableNode $groupsTable table of groups with a heading | group |
	 *
	 * @return void
	 */
	public function theAdminCreatesAUserUsingWithoutAPasswordTheWebUI(
		$attemptTo, $username, $email, TableNode $groupsTable = null
	) {
		$this->theAdminCreatesAUserUsingTheWebUI(
			$attemptTo, $username, null, $email, $groupsTable
		);
	}

	/**
	 *
	 * @When the administrator deletes the group named :name using the webUI and confirms the deletion using the webUI
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function theAdminDeletesTheGroupUsingTheWebUI($name) {
		$this->usersPage->deleteGroup($name, $this->getSession(), true);
		$this->featureContext->rememberThatGroupIsNotExpectedToExist($name);
	}

	/**
	 * @When the administrator deletes the group named :name using the webUI and cancels the deletion using the webUI
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function theAdminDeletesDoesNotDeleteGroupUsingWebUI($name) {
		$this->usersPage->deleteGroup($name, $this->getSession(), false);
	}

	/**
	 * @When the administrator deletes these groups and confirms the deletion using the webUI:
	 * expects a table of groups with the heading "groupname"
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdminDeletesTheseGroupsUsingTheWebUI(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['groupname']);
		foreach ($table as $row) {
			$this->theAdminDeletesTheGroupUsingTheWebUI($row['groupname']);
		}
	}

	/**
	 * @When the administrator deletes these groups and and cancels the deletion using the webUI:
	 * expects a table of groups with the heading "groupname"
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdminDeletesDoesNotTheseGroupsUsingTheWebUI(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['groupname']);
		foreach ($table as $row) {
			$this->theAdminDeletesDoesNotDeleteGroupUsingWebUI($row['groupname']);
		}
	}

	/**
	 * @Then the group name :groupName should be listed on the webUI
	 *
	 * @param string $groupName
	 *
	 * @return void
	 */
	public function theGroupNameShouldBeListed($groupName) {
		$groups = $this->usersPage->getAllGroups();
		Assert::assertContains(
			$groupName, $groups, "Expected '" . $groupName . "' does not exist"
		);
	}

	/**
	 * @Then the group name :name should not be listed on the webUI
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function theGroupNamedShouldNotBeListedOnTheWebUI($name) {
		Assert::assertFalse(
			\in_array($name, $this->usersPage->getAllGroups(), true),
			"group '" . $name . "' is listed but should not be"
		);
	}

	/**
	 * @Then /^these groups should (not|)\s?be listed on the webUI:$/
	 * expects a table of groups with the heading "groupname"
	 *
	 * @param string $shouldOrNot (not|)
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theseGroupsShouldBeListedOnTheWebUI(
		$shouldOrNot, TableNode $table
	) {
		$should = ($shouldOrNot !== "not");
		$groups = $this->usersPage->getAllGroups();
		$this->featureContext->verifyTableNodeColumns($table, ['groupname']);
		foreach ($table as $row) {
			Assert::assertEquals(
				\in_array($row['groupname'], $groups, true),
				$should,
				"group '" . $row['groupname'] .
				"' is" . ($should ? " not" : "") .
				" listed but should" . ($should ? "" : " not") . " be"
			);
		}
	}

	/**
	 * @When the user/administrator reloads the users page
	 * @Given the user/administrator has reloaded the users page
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theUserReloadsTheUsersPage() {
		$this->getSession()->reload();
		$this->usersPage->waitTillPageIsLoaded($this->getSession());
	}

	/**
	 * @When the administrator/user disables user :username using the webUI
	 *
	 * @param string $username
	 *
	 * @return void
	 */
	public function theAdminDisablesUserUsingTheWebui($username) {
		$username = $this->featureContext->getActualUsername($username);
		$this->usersPage->openAppSettingsMenu();
		$this->usersPage->setSetting("Show enabled/disabled option");
		$this->usersPage->disableUser($username);
	}

	/**
	 * @When the disabled user :username tries to login using the password :password from the webUI
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 */
	public function theDisabledUserTriesToLogin($username, $password) {
		$password = $this->featureContext->substituteInLineCodes($password, $username);
		$username = $this->featureContext->getActualUsername($username);
		$this->webUIGeneralContext->theUserLogsOutOfTheWebUI();
		$password = $this->featureContext->getActualPassword($password);
		/**
		 *
		 * @var DisabledUserPage $disabledPage
		 */
		$disabledPage = $this->loginPage->loginAs(
			$username, $password, 'DisabledUserPage'
		);
		$disabledPage->waitTillPageIsLoaded($this->getSession());
	}

	/**
	 * @When the administrator deletes user :username using the webUI and confirms the deletion using the webUI
	 *
	 * @param string $username
	 *
	 * @return void
	 */
	public function theAdministratorDeletesTheUser($username) {
		$username = $this->featureContext->getActualUsername($username);
		$this->usersPage->deleteUser($username, true);
		$this->featureContext->rememberThatUserIsNotExpectedToExist($username);
	}

	/**
	 * @When the administrator deletes user :username using the webUI and cancels the deletion using the webUI
	 *
	 * @param string $username
	 *
	 * @return void
	 */
	public function theAdministratorDoesNotDeleteTheUser($username) {
		$username = $this->featureContext->getActualUsername($username);
		$this->usersPage->deleteUser($username, false);
	}

	/**
	 * @When the deleted user :username tries to login using the password :password using the webUI
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 */
	public function theDeletedUserTriesToLogin($username, $password) {
		$this->webUIGeneralContext->theUserLogsOutOfTheWebUI();
		$password = $this->featureContext->substituteInLineCodes($password, $username);
		$username = $this->featureContext->getActualUsername($username);
		$this->loginPage->loginAs($username, $password, 'LoginPage');
	}

	/**
	 * @When /^the administrator (enables|disables) the setting "([^"]*)" in the User Management page using the webUI$/
	 *
	 * @param string $action
	 * @param string $setting
	 *
	 * @return void
	 */
	public function enableOrDisableSettings($action, $setting) {
		$value = $action === 'enables' ? true : false;
		$this->usersPage->setSetting($setting, $value);
	}

	/**
	 * @Then the quota of user :username should be set to :quota on the webUI
	 *
	 * @param string $username
	 * @param string $quota
	 *
	 * @return void
	 */
	public function quotaOfUserShouldBeSetToOnTheWebUI($username, $quota) {
		$username = $this->featureContext->getActualUsername($username);
		$setQuota = $this->usersPage->getQuotaOfUser($username);
		Assert::assertEquals(
			$quota,
			$setQuota,
			'Users quota is set to "' . $setQuota .
			'" but expected "' . $quota . '"'
		);
	}

	/**
	 * @Then /^the administrator should be able to see the email of these users in the User Management page:$/
	 *
	 * @param TableNode $table table of usernames with a heading | username |
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorShouldBeAbleToSeeEmailOfTheseUsers(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['username']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$expectedEmail = $this->featureContext->getEmailAddressForUser($user);
			$userEmail = $this->usersPage->getEmailOfUser($user);
			Assert::assertEquals(
				$expectedEmail,
				$userEmail,
				__METHOD__
				. " The email of user '"
				. $user
				. "' is expected to be '"
				. $expectedEmail
				. "', but got '$userEmail' instead."
			);
		}
	}

	/**
	 * @Then /^the administrator should be able to see the quota of these users in the User Management page:$/
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorShouldBeAbleToSeeQuotaOfTheseUsers(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['username', 'quota']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$visible = $this->usersPage->isQuotaColumnOfUserVisible($user);
			Assert::assertEquals(
				true,
				$visible,
				__METHOD__
				. " The quota of user '"
				. $user
				. "' was expected to be visible to the administrator in the User Management page, but is not."
			);
		}
	}

	/**
	 * @Then /^the administrator should not be able to see the quota of these users in the User Management page:$/
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorShouldNotBeAbleToSeeQuotaOfTheseUsers(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['username']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$visible = $this->usersPage->isQuotaColumnOfUserVisible($user);
			Assert::assertEquals(
				false,
				$visible,
				__METHOD__
				. " The quota of user '"
				. $user
				. "' was expected not to be visible to the administrator in the User Management page, but is visible."
			);
		}
	}

	/**
	 * @Then /^the administrator should be able to see the password of these users in the User Management page:$/
	 *
	 * @param TableNode $table table of usernames column with a heading | username |
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorShouldBeAbleToSeePasswordColumnOfTheseUsers(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['username']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$visible = $this->usersPage->isPasswordColumnOfUserVisible($user);
			Assert::assertEquals(
				true,
				$visible,
				__METHOD__
				. " The password of user '"
				. $user
				. "' was expected to be visible to the administrator in the User Management page, but is not."
			);
		}
	}

	/**
	 * @Then /^the administrator should not be able to see the password of these users in the User Management page:$/
	 *
	 * @param TableNode $table table of usernames column with a heading | username |
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorShouldNotBeAbleToSeePasswordColumnOfTheseUsers(TableNode $table) {
		$this->featureContext->verifyTableNodeColumns($table, ['username']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$visible = $this->usersPage->isPasswordColumnOfUserVisible($user);
			Assert::assertEquals(
				false,
				$visible,
				__METHOD__
				. " The password of user '"
				. $user
				. "' was expected not to be visible to the administrator in the User Management page, but is visible."
			);
		}
	}

	/**
	 * @Then /^the administrator should be able to see the storage location of these users in the User Management page:$/
	 *
	 * @param TableNode $table table of usernames and storage locations with a heading | username | and | storage location |
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorShouldBeAbleToSeeStorageLocationOfTheseUsers(
		TableNode $table
	) {
		$this->featureContext->verifyTableNodeColumns($table, ['username', 'storage location']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$userStorageLocation = $this->usersPage->getStorageLocationOfUser($user);
			$expectedUserStorageLocation = $row["storage location"];
			if ($this->featureContext->isTestingReplacingUsernames()) {
				$expectedUserStorageLocationSplitted = \explode("/", $expectedUserStorageLocation);
				$lastIndexOfArray = \count($expectedUserStorageLocationSplitted) - 1;
				$expectedUserStorageLocationSplitted[$lastIndexOfArray] = $user;
				$expectedUserStorageLocation = \implode("/", $expectedUserStorageLocationSplitted);
			}
			Assert::assertStringContainsString(
				$expectedUserStorageLocation,
				$userStorageLocation,
				__METHOD__
				. "'"
				. $expectedUserStorageLocation
				. "' is not contained in the storage location of '"
				. $user
				. "'."
			);
		}
	}

	/**
	 * @Then /^the administrator should be able to see the last login of these users in the User Management page:$/
	 *
	 * @param TableNode $table table of usernames and last logins with a heading | username | and | last logins |
	 *
	 * @return void
	 */
	public function theAdministratorShouldBeAbleToSeeLastLoginOfTheseUsers(
		TableNode $table
	) {
		$this->featureContext->verifyTableNodeColumns($table, ['username', 'last login']);
		foreach ($table as $row) {
			$user = $this->featureContext->getActualUsername($row['username']);
			$userLastLogin = $this->usersPage->getLastLoginOfUser($user);

			Assert::assertStringContainsString(
				$row['last login'],
				$userLastLogin,
				__METHOD__
				. "'"
				. $row['last login']
				. "' is not contained in the last login of '"
				. $user
				. "'."
			);
		}
	}

	/**
	 * This will run before EVERY scenario.
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
		$this->featureContext = $environment->getContext('FeatureContext');
		$this->webUIGeneralContext = $environment->getContext('WebUIGeneralContext');

		// user_management app configs
		$configs = [
			'umgmt_send_email' => '',
			'umgmt_set_password' => '',
			'umgmt_show_backend' => '',
			'umgmt_show_email' => '',
			'umgmt_show_is_enabled' => '',
			'umgmt_show_last_login' => '',
			'umgmt_show_storage_location' => ''
		];

		if ($this->appParameterValues === null) {
			// Get app config values
			$appConfigs = AppConfigHelper::getAppConfigs(
				$this->featureContext->getBaseUrl(),
				$this->featureContext->getAdminUsername(),
				$this->featureContext->getAdminPassword(),
				'core'
			);
			$results = [];
			foreach ($appConfigs as $appConfig) {
				if (\is_string($appConfig['configkey']) && isset($configs[$appConfig['configkey']])) {
					$results[] = $appConfig;
				}
			}
			// Save the app configs
			$this->appParameterValues = $results;
		}
	}

	/**
	 * After Scenario
	 *
	 * @AfterScenario @webUI
	 *
	 * @param AfterScenarioScope $afterScenarioScope
	 *
	 * @return void
	 */
	public function restoreScenario(AfterScenarioScope $afterScenarioScope) {
		// Restore app config settings
		AppConfigHelper::modifyAppConfigs(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			$this->appParameterValues
		);
	}

	/**
	 * @When the administrator adds group :groupName using the webUI
	 *
	 * @param string $groupName
	 *
	 * @return void
	 */
	public function theAdminAddsGroupUsingTheWebUI($groupName) {
		$this->usersPage->addGroup($groupName, $this->getSession());
		$this->featureContext->addGroupToCreatedGroupsList($groupName);
	}

	/**
	 * @When the administrator changes the display name of user :user to :displayName using the webUI
	 *
	 * @param string $user
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorChangesTheDisplayNameOfUserToUsingTheWebui($user, $displayName) {
		$user = $this->featureContext->getActualUsername($user);
		$this->usersPage->setDisplayNameofUserTo($this->getSession(), $user, $displayName);
		$this->featureContext->rememberUserDisplayName($user, $displayName);
	}

	/**
	 * @When the administrator changes the password of user :user to :password using the webUI
	 *
	 * @param string $user
	 * @param string $password
	 *
	 * @return void
	 */
	public function theAdministratorChangesThePasswordOfUserToUsingTheWebui($user, $password) {
		$user = $this->featureContext->getActualUsername($user);
		$this->usersPage->changeUserPassword($this->getSession(), $user, $password);
	}

	/**
	 * @When the administrator adds user :user to group :group using the webUI
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function addsUserToGroupUsingTheWebui($user, $group) {
		$user = $this->featureContext->getActualUsername($user);
		$this->usersPage->addOrRemoveUserToGroup($this->getSession(), $user, $group);
	}

	/**
	 * @When the administrator removes user :user from group :group using the webUI
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorRemovesUserFromGroupUsingTheWebui($user, $group) {
		$user = $this->featureContext->getActualUsername($user);
		$this->usersPage->addOrRemoveUserToGroup($this->getSession(), $user, $group, false);
	}

	/**
	 * @When the administrator changes the email of user :username to :email using the webUI
	 *
	 * @param string $username
	 * @param string $email
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorChangesTheEmailOfUserToUsingTheWebui($username, $email) {
		$username = $this->featureContext->getActualUsername($username);
		$this->usersPage->openAppSettingsMenu();
		$this->usersPage->setSetting('Show email address');
		$this->usersPage->changeUserEmail($this->getSession(), $username, $email);
		$this->featureContext->rememberUserEmailAddress($username, $email);
	}

	/**
	 * @Then /^the user count of group "([^"]*)" should display (\d+) users on the webUI$/
	 *
	 * @param string $group
	 * @param int $count
	 *
	 * @return void
	 */
	public function theUserCountOfGroupShouldDisplayUsersOnTheWebUI($group, $count) {
		$expectedCount = (int) $count;
		$actualCount = $this->usersPage->getUserCountOfGroup($group);
		Assert::assertEquals(
			$expectedCount,
			$actualCount,
			__METHOD__
			. " The user count of group '$group' is expected to display '$expectedCount' users, '"
			. "but got '$actualCount' users instead."
		);
	}

	/**
	 * @Then the user count of group :group should not be displayed on the webUI
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theUserCountOfGroupShouldNotBeDisplayedOnTheWebui($group) {
		$count = $this->usersPage->getUserCountOfGroup($group);
		Assert::assertNull(
			$count, "Failed asserting that user count of group $group is not displayed"
		);
	}
}
