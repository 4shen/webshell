@api @provisioning_api-app-required
Feature: enable user
  As an admin
  I want to be able to enable a user
  So that I can give a user access to their files and resources again if they are now authorized for that

  Background:
    Given using OCS API version "1"

  @smokeTest
  Scenario: admin enables an user
    Given user "Alice" has been created with default attributes and skeleton files
    And user "Alice" has been disabled
    When the administrator enables user "Alice" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "Alice" should be enabled

  @skipOnOcV10.3
  Scenario Outline: admin enables an user with special characters in the username
    Given these users have been created with skeleton files:
      | username   | email   |
      | <username> | <email> |
    And user "<username>" has been disabled
    When the administrator enables user "<username>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "<username>" should be enabled
    Examples:
      | username | email               |
      | a@-+_.b  | a.b@example.com     |
      | a space  | a.space@example.com |

  Scenario: admin enables another admin user
    Given user "another-admin" has been created with default attributes and skeleton files
    And user "another-admin" has been added to group "admin"
    And user "another-admin" has been disabled
    When the administrator enables user "another-admin" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "another-admin" should be enabled

  Scenario: admin enables subadmins in the same group
    Given user "subadmin" has been created with default attributes and skeleton files
    And group "brand-new-group" has been created
    And user "subadmin" has been added to group "brand-new-group"
    And the administrator has been added to group "brand-new-group"
    And user "subadmin" has been made a subadmin of group "brand-new-group"
    And user "subadmin" has been disabled
    When the administrator enables user "subadmin" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "subadmin" should be enabled

  Scenario: admin tries to enable himself
    And user "another-admin" has been created with default attributes and skeleton files
    And user "another-admin" has been added to group "admin"
    And user "another-admin" has been disabled
    When user "another-admin" tries to enable user "another-admin" using the provisioning API
    Then user "another-admin" should be disabled

  Scenario: normal user tries to enable other user
    Given these users have been created with default attributes and skeleton files:
      | username |
      | Alice    |
      | Brian    |
    And user "Brian" has been disabled
    When user "Alice" tries to enable user "Brian" using the provisioning API
    Then the OCS status code should be "997"
    And the HTTP status code should be "401"
    And user "Brian" should be disabled

  Scenario: subadmin tries to enable himself
    Given user "subadmin" has been created with default attributes and skeleton files
    And group "brand-new-group" has been created
    And user "subadmin" has been added to group "brand-new-group"
    And user "subadmin" has been made a subadmin of group "brand-new-group"
    And user "subadmin" has been disabled
    When user "subadmin" tries to enable user "subadmin" using the provisioning API
    Then the OCS status code should be "997"
    And the HTTP status code should be "401"
    And user "subadmin" should be disabled

  Scenario: Making a web request with an enabled user
    Given user "Alice" has been created with default attributes and skeleton files
    When user "Alice" sends HTTP method "GET" to URL "/index.php/apps/files"
    Then the HTTP status code should be "200"
