@webUI @insulated @disablePreviews
Feature: login users
  As a user
  I want to be able to log into my account
  So that I have access to my files

  As an admin
  I want only authorised users to log in
  So that unauthorised access is impossible

  Scenario: login page username and password field placeholder text
    When the user browses to the login page
    Then the username field on the login page should have placeholder text "Username or email"
    And the password field on the login page should have placeholder text "Password"

  @skipOnOcV10.3 @skipOnOcV10.4
  Scenario: login page username and password field placeholder text when strict_login_enforced is set
    Given the administrator has added system config key "strict_login_enforced" with value "true" and type "boolean"
    When the user browses to the login page
    Then the username field on the login page should have placeholder text "Login"
    And the password field on the login page should have placeholder text "Password"

  @TestAlsoOnExternalUserBackend
  Scenario: simple user login
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Alice    |
    When user "Alice" logs in using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  @TestAlsoOnExternalUserBackend @skipOnOcV10.3 @skipOnOcV10.4
  Scenario: simple user login should work when strict_login_enforced is set
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Alice    |
    And the administrator has added system config key "strict_login_enforced" with value "true" and type "boolean"
    When user "Alice" logs in using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  @smokeTest @TestAlsoOnExternalUserBackend
  Scenario: admin login
    When the administrator logs in using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  @smokeTest @TestAlsoOnExternalUserBackend
  Scenario: admin login with invalid password
    Given the user has browsed to the login page
    When the administrator tries to login with an invalid password "%regular%" using the webUI
    Then the user should be redirected to a webUI page with the title "%productname%"

  Scenario: access the personal general settings page when not logged in
    When the user attempts to browse to the personal general settings page
    Then the user should be redirected to a webUI page with the title "%productname%"
    When the administrator logs in using the webUI after a redirect from the "personal general settings" page
    Then the user should be redirected to a webUI page with the title "Settings - %productname%"

  Scenario: access the personal general settings page when not logged in using incorrect then correct password
    When the user attempts to browse to the personal general settings page
    Then the user should be redirected to a webUI page with the title "%productname%"
    When the administrator tries to login with an invalid password "%regular%" using the webUI
    Then the user should be redirected to a webUI page with the title "%productname%"
    When the administrator logs in using the webUI after a redirect from the "personal general settings" page
    Then the user should be redirected to a webUI page with the title "Settings - %productname%"
