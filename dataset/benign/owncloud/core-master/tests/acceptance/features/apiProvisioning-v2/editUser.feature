@api @provisioning_api-app-required
Feature: edit users
  As an admin, subadmin or as myself
  I want to be able to edit user information
  So that I can keep the user information up-to-date

  Background:
    Given using OCS API version "2"

  @smokeTest
  Scenario: the administrator can edit a user email
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the email of user "brand-new-user" to "brand-new-user@example.com" using the provisioning API
    Then the HTTP status code should be "200"
    And the OCS status code should be "200"
    And the email address of user "brand-new-user" should be "brand-new-user@example.com"

  @skipOnOcV10.3
  Scenario Outline: the administrator can edit a user email of an user with special characters in the username
    Given these users have been created with skeleton files:
      | username   | email   |
      | <username> | <email> |
    When the administrator changes the email of user "<username>" to "a-different-email@example.com" using the provisioning API
    Then the HTTP status code should be "200"
    And the OCS status code should be "200"
    And the email address of user "<username>" should be "a-different-email@example.com"
    Examples:
      | username | email               |
      | a@-+_.b  | a.b@example.com     |
      | a space  | a.space@example.com |

  @smokeTest
  Scenario: the administrator can edit a user display (the API allows editing the "display name" by using the key word "display")
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the display of user "brand-new-user" to "A New User" using the provisioning API
    Then the HTTP status code should be "200"
    And the OCS status code should be "200"
    And the display name of user "brand-new-user" should be "A New User"

  Scenario: the administrator can edit a user display name
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the display name of user "brand-new-user" to "A New User" using the provisioning API
    Then the HTTP status code should be "200"
    And the OCS status code should be "200"
    And the display name of user "brand-new-user" should be "A New User"

  Scenario: the administrator can clear a user display name and then it defaults to the username
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And the administrator has changed the display name of user "brand-new-user" to "A New User"
    When the administrator changes the display name of user "brand-new-user" to "" using the provisioning API
    Then the HTTP status code should be "200"
    And the OCS status code should be "200"
    And the display name of user "brand-new-user" should be "brand-new-user"

  @smokeTest
  Scenario: the administrator can edit a user quota
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the quota of user "brand-new-user" to "12MB" using the provisioning API
    Then the HTTP status code should be "200"
    And the OCS status code should be "200"
    And the quota definition of user "brand-new-user" should be "12 MB"

  Scenario: the administrator can override existing user email
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And the administrator has changed the email of user "brand-new-user" to "brand-new-user@gmail.com"
    And the OCS status code should be "200"
    And the HTTP status code should be "200"
    When the administrator changes the email of user "brand-new-user" to "brand-new-user@example.com" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And the email address of user "brand-new-user" should be "brand-new-user@example.com"

  @skipOnOcV10.3 @skipOnOcV10.4
  Scenario: the administrator can clear an existing user email
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And the administrator has changed the email of user "brand-new-user" to "brand-new-user@gmail.com"
    And the OCS status code should be "200"
    And the HTTP status code should be "200"
    When the administrator changes the email of user "brand-new-user" to "" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And the email address of user "brand-new-user" should be ""

  @smokeTest
  Scenario: a subadmin should be able to edit the user information in their group
    Given these users have been created with default attributes and skeleton files:
      | username       |
      | subadmin       |
      | brand-new-user |
    And group "new-group" has been created
    And user "brand-new-user" has been added to group "new-group"
    And user "subadmin" has been made a subadmin of group "new-group"
    When user "subadmin" changes the quota of user "brand-new-user" to "12MB" using the provisioning API
    And user "subadmin" changes the email of user "brand-new-user" to "brand-new-user@example.com" using the provisioning API
    And user "subadmin" changes the display of user "brand-new-user" to "Anne Brown" using the provisioning API
    Then the display name of user "brand-new-user" should be "Anne Brown"
    And the email address of user "brand-new-user" should be "brand-new-user@example.com"
    And the quota definition of user "brand-new-user" should be "12 MB"

  Scenario: a normal user should be able to change their email address
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When user "brand-new-user" changes the email of user "brand-new-user" to "brand-new-user@example.com" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And the attributes of user "brand-new-user" returned by the API should include
      | email | brand-new-user@example.com |
    And the email address of user "brand-new-user" should be "brand-new-user@example.com"

  Scenario Outline: a normal user should be able to change their display name
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When user "brand-new-user" changes the display name of user "brand-new-user" to "<display-name>" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And the attributes of user "brand-new-user" returned by the API should include
      | displayname | <display-name> |
    And the display name of user "brand-new-user" should be "<display-name>"
    Examples:
      | display-name    |
      | Alan Border     |
      | Phil Cyclist 🚴 |

  Scenario: a normal user should not be able to change their quota
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When user "brand-new-user" changes the quota of user "brand-new-user" to "12MB" using the provisioning API
    Then the OCS status code should be "997"
    And the HTTP status code should be "401"
    And the attributes of user "brand-new-user" returned by the API should include
      | quota definition | default |
    And the quota definition of user "brand-new-user" should be "default"
