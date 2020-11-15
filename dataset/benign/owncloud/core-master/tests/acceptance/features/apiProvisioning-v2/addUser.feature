@api @provisioning_api-app-required
Feature: add user
  As an admin
  I want to be able to add users
  So that I can give people controlled individual access to resources on the ownCloud server

  Background:
    Given using OCS API version "2"

  @smokeTest
  Scenario: admin creates a user
    Given user "brand-new-user" has been deleted
    When the administrator sends a user creation request for user "brand-new-user" password "%alt1%" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "brand-new-user" should exist
    And user "brand-new-user" should be able to access a skeleton file

  @skipOnOcV10.3
  Scenario Outline: admin creates a user with special characters in the username
    Given user "<username>" has been deleted
    When the administrator sends a user creation request for user "<username>" password "%alt1%" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "<username>" should exist
    And user "<username>" should be able to access a skeleton file
    Examples:
      | username |
      | a@-+_.b  |
      | a space  |

  Scenario: admin tries to create an existing user
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator sends a user creation request for user "brand-new-user" password "%alt1%" using the provisioning API
    Then the OCS status code should be "400"
    And the HTTP status code should be "400"
    And the API should not return any data

  Scenario: admin tries to create an existing disabled user
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And user "brand-new-user" has been disabled
    When the administrator sends a user creation request for user "brand-new-user" password "%alt1%" using the provisioning API
    Then the OCS status code should be "400"
    And the HTTP status code should be "400"
    And the API should not return any data

  Scenario: Admin creates a new user and adds him directly to a group
    Given group "brand-new-group" has been created
    When the administrator sends a user creation request for user "brand-new-user" password "%alt1%" group "brand-new-group" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "brand-new-user" should belong to group "brand-new-group"
    And user "brand-new-user" should be able to access a skeleton file

  Scenario Outline: admin creates a user and specifies a password with special characters
    Given user "brand-new-user" has been deleted
    When the administrator sends a user creation request for user "brand-new-user" password "<password>" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "brand-new-user" should exist
    And user "brand-new-user" should be able to access a skeleton file
    Examples:
      | password                     | comment                               |
      | !@#$%^&*()-_+=[]{}:;,.<>?~/\ | special characters                    |
      | España§àôœ€                  | special European and other characters |
      | नेपाली                       | Unicode                               |

  Scenario: admin creates a user and specifies an invalid password, containing just space
    Given user "brand-new-user" has been deleted
    When the administrator sends a user creation request for user "brand-new-user" password " " using the provisioning API
    Then the OCS status code should be "400"
    And the HTTP status code should be "400"
    And user "brand-new-user" should not exist

  Scenario: admin creates a user and specifies a password containing spaces
    Given user "brand-new-user" has been deleted
    When the administrator sends a user creation request for user "brand-new-user" password "spaces in my password" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "brand-new-user" should exist
    And user "brand-new-user" should be able to access a skeleton file

  Scenario Outline: admin creates a user with username that contains capital letters
    When the administrator sends a user creation request for user "<display-name>" password "%alt1%" using the provisioning API
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "Brand-New-User" should exist
    And user "BRAND-NEW-USER" should exist
    And user "brand-new-user" should exist
    And user "brand-NEW-user" should exist
    And user "BrAnD-nEw-UsEr" should exist
    And the display name of user "brand-new-user" should be "<display-name>"
    Examples:
      | display-name   |
      | Brand-New-User |
      | BRAND-NEW-USER |
      | brand-new-user |
      | brand-NEW-user |
      | BrAnD-nEw-UsEr |

  Scenario: admin tries to create an existing user but with username containing capital letters
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator sends a user creation request for user "BRAND-NEW-USER" password "%alt1%" using the provisioning API
    Then the OCS status code should be "400"
    And the HTTP status code should be "400"
    And the API should not return any data
