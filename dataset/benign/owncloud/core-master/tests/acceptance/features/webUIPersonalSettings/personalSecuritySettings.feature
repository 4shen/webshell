@webUI @insulated @disablePreviews
Feature: personal security settings
  As a user
  I want to be able to manage security settings for my account
  So that I can enable, allow and deny access to and from other storage systems or resources

  Background:
    Given user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page

  @smokeTest
  Scenario: login with new app password
    When the user creates a new App password using the webUI
    And the user re-logs in with username "Alice" and generated app password using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  @smokeTest
  Scenario: delete the app password
    When the user creates a new App password using the webUI
    And the user deletes the app password
    And the user re-logs in with username "Alice" and deleted app password using the webUI
    Then the user should be redirected to a webUI page with the title "%productname%"
