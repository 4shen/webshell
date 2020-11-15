@webUI @insulated @disablePreviews
Feature: create folders
  As a user
  I want to create folders
  So that I can organise my data structure

  Background:
    Given user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has logged in using the webUI
    And the user has browsed to the files page

  Scenario Outline: Create a folder
    When the user creates a folder with the name "<folder-name>" using the webUI
    Then folder "<folder-name>" should be listed on the webUI
    When the user reloads the current page of the webUI
    Then folder "<folder-name>" should be listed on the webUI
    Examples:
      | folder-name       |
      | folder-!@#$%^&* ! |
      | नेपालि            |

  @smokeTest
  Scenario: Create a folder inside another folder
    When the user creates a folder with the name "top-folder" using the webUI
    And the user opens folder "top-folder" using the webUI
    Then there should be no files/folders listed on the webUI
    When the user creates a folder with the name "sub-folder" using the webUI
    Then folder "sub-folder" should be listed on the webUI
    When the user reloads the current page of the webUI
    Then folder "sub-folder" should be listed on the webUI

  Scenario: Create a folder with existing name
    Given user "Alice" has created folder "/simple-folder"
    And the user has reloaded the current page of the webUI
    When the user creates a folder with the invalid name "simple-folder" using the webUI
    Then near the folder input field a tooltip with the text 'simple-folder already exists' should be displayed on the webUI

  @files_sharing-app-required
  Scenario: Create a folder in a public share
    Given user "Alice" has created folder "/simple-empty-folder"
    And user "Alice" has created a public link share with settings
      | path        | /simple-empty-folder |
      | permissions | read,create          |
    And the public accesses the last created public link using the webUI
    When the user creates a folder with the name "top-folder" using the webUI
    And the user opens folder "top-folder" using the webUI
    Then there should be no files/folders listed on the webUI
    When the user creates a folder with the name "sub-folder" using the webUI
    Then folder "sub-folder" should be listed on the webUI
    When the user reloads the current page of the webUI
    Then folder "sub-folder" should be listed on the webUI

  Scenario: Create folder with name including both double and single quotes
    When the user creates a folder with the following name using the webUI
      | name-parts         |
      | 'single'quotes     |
      | with"double"quotes |
    Then the following folder should be listed on the webUI
      | name-parts         |
      | 'single'quotes     |
      | with"double"quotes |
    When the user opens folder with the following name using the webUI
      | name-parts         |
      | 'single'quotes     |
      | with"double"quotes |
    And the user creates a folder with the following name using the webUI
      | name-parts         |
      | another'single'    |
      | with"double"quotes |
    Then the following folder should be listed on the webUI
      | name-parts         |
      | another'single'    |
      | with"double"quotes |