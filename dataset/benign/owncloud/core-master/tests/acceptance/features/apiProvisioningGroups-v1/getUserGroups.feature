@api @provisioning_api-app-required
Feature: get user groups
  As an admin
  I want to be able to get groups
  So that I can manage group membership

  Background:
    Given using OCS API version "1"

  @smokeTest
  Scenario: admin gets groups of an user
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "unused-group" has been created
    And group "brand-new-group" has been created
    And group "0" has been created
    And group "Admin & Finance (NP)" has been created
    And group "admin:Pokhara@Nepal" has been created
    And group "नेपाली" has been created
    And group "😅 😆" has been created
    And user "brand-new-user" has been added to group "brand-new-group"
    And user "brand-new-user" has been added to group "0"
    And user "brand-new-user" has been added to group "Admin & Finance (NP)"
    And user "brand-new-user" has been added to group "admin:Pokhara@Nepal"
    And user "brand-new-user" has been added to group "नेपाली"
    And user "brand-new-user" has been added to group "😅 😆"
    When the administrator gets all the groups of user "brand-new-user" using the provisioning API
    Then the groups returned by the API should be
      | brand-new-group      |
      | 0                    |
      | Admin & Finance (NP) |
      | admin:Pokhara@Nepal  |
      | नेपाली               |
      | 😅 😆                |
    And the OCS status code should be "100"
    And the HTTP status code should be "200"

	# Note: when the issue is fixed, use this scenario and delete the scenario above.
  @issue-31015
  Scenario: admin gets groups of an user, including groups containing a slash
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "unused-group" has been created
    And group "brand-new-group" has been created
    And group "0" has been created
    And group "Admin & Finance (NP)" has been created
    And group "admin:Pokhara@Nepal" has been created
    And group "नेपाली" has been created
    # After fixing issue-31015, change the following steps to "has been created"
    And the administrator sends a group creation request for group "Mgmt/Sydney" using the provisioning API
    And the administrator sends a group creation request for group "var/../etc" using the provisioning API
    And the administrator sends a group creation request for group "priv/subadmins/1" using the provisioning API
    #And group "Mgmt/Sydney" has been created
    #And group "var/../etc" has been created
    #And group "priv/subadmins/1" has been created
    And user "brand-new-user" has been added to group "brand-new-group"
    And user "brand-new-user" has been added to group "0"
    And user "brand-new-user" has been added to group "Admin & Finance (NP)"
    And user "brand-new-user" has been added to group "admin:Pokhara@Nepal"
    And user "brand-new-user" has been added to group "नेपाली"
    And user "brand-new-user" has been added to group "Mgmt/Sydney"
    And user "brand-new-user" has been added to group "var/../etc"
    And user "brand-new-user" has been added to group "priv/subadmins/1"
    When the administrator gets all the groups of user "brand-new-user" using the provisioning API
    Then the groups returned by the API should be
      | brand-new-group      |
      | 0                    |
      | Admin & Finance (NP) |
      | admin:Pokhara@Nepal  |
      | नेपाली               |
      | Mgmt/Sydney          |
      | var/../etc           |
      | priv/subadmins/1     |
    And the OCS status code should be "100"
    And the HTTP status code should be "200"
    # The following steps are needed so that the groups do get cleaned up.
    # After fixing issue-31015, remove the following steps:
    And the administrator deletes group "Mgmt/Sydney" using the occ command
    And the administrator deletes group "var/../etc" using the occ command
    And the administrator deletes group "priv/subadmins/1" using the occ command

  @smokeTest
  Scenario: subadmin tries to get other groups of a user in their group
    Given these users have been created with default attributes and skeleton files:
      | username       |
      | brand-new-user |
      | subadmin       |
    And group "brand-new-group" has been created
    And group "another-new-group" has been created
    And user "subadmin" has been made a subadmin of group "brand-new-group"
    And user "brand-new-user" has been added to group "brand-new-group"
    And user "brand-new-user" has been added to group "another-new-group"
    When user "subadmin" gets all the groups of user "brand-new-user" using the provisioning API
    Then the groups returned by the API should include "brand-new-group"
    And the groups returned by the API should not include "another-new-group"
    And the OCS status code should be "100"
    And the HTTP status code should be "200"

  Scenario: normal user tries to get the groups of another user
    Given these users have been created with default attributes and skeleton files:
      | username         |
      | brand-new-user   |
      | another-new-user |
    And group "brand-new-group" has been created
    And user "brand-new-user" has been added to group "brand-new-group"
    When user "another-new-user" gets all the groups of user "brand-new-user" using the provisioning API
    Then the OCS status code should be "997"
    And the HTTP status code should be "401"
    And the API should not return any data

  Scenario: admin gets groups of an user who is not in any groups
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "unused-group" has been created
    When the administrator gets all the groups of user "brand-new-user" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And the list of groups returned by the API should be empty
