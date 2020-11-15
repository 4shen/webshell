@webUI @insulated @disablePreviews
Feature: personal general settings
  As a user
  I want to change the ownCloud User Interface to my preferred settings
  So that I can personalise the User Interface

  Background:
    Given user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has logged in using the webUI
    And the user has browsed to the personal general settings page

  @smokeTest
  Scenario: change language
    When the user changes the language to "Русский" using the webUI
    Then the user should be redirected to a webUI page with the title "Настройки - %productname%"

  Scenario: change language and check that file actions menu have been translated
    Given the user has created folder "simple-folder"
    When the user changes the language to "हिन्दी" using the webUI
    And the user browses to the files page
    And the user opens the file action menu of folder "simple-folder" on the webUI
    Then the user should see "Details" file action translated to "विवरण" on the webUI
    And the user should see "Delete" file action translated to "हटाना" on the webUI

  Scenario: change language using the occ command and check that file actions menu have been translated
    Given the user has created folder "simple-folder"
    When the administrator changes the language of user "Alice" to "fr" using the occ command
    And the user browses to the files page
    And the user opens the file action menu of folder "simple-folder" on the webUI
    Then the user should see "Details" file action translated to "Détails" on the webUI
    And the user should see "Delete" file action translated to "Supprimer" on the webUI

  Scenario: change language to invalid language using the occ command and check that the language defaults back to english
    Given the user has created folder "simple-folder"
    When the administrator changes the language of user "Alice" to "not-valid-lan" using the occ command
    And the user browses to the files page
    And the user opens the file action menu of folder "simple-folder" on the webUI
    Then the user should see "Details" file action translated to "Details" on the webUI
    And the user should see "Delete" file action translated to "Delete" on the webUI

  Scenario: user sees displayed version number, groupnames and federated cloud ID on the personal general settings page
    Given group "new-group" has been created
    And group "another-group" has been created
    And user "Alice" has been added to group "new-group"
    And user "Alice" has been added to group "another-group"
    And the user has reloaded the current page of the webUI
    Then the owncloud version should be displayed on the personal general settings page on the webUI
    And the federated cloud id for user "Alice" should be displayed on the personal general settings page on the webUI
    And group "new-group" should be displayed on the personal general settings page on the webUI
    And group "another-group" should be displayed on the personal general settings page on the webUI

  Scenario: User sets profile picture from their existing cloud file
    Given user "Alice" has uploaded file "filesForUpload/testavatar.jpg" to "/testimage.jpg"
    And the user has deleted any existing profile picture
    When the user sets profile picture to "testimage.jpg" from their cloud files using the webUI
    Then the preview of the profile picture should be shown on the webUI

  Scenario: User deletes the existing profile picture
    Given user "Alice" has uploaded file "filesForUpload/testavatar.jpg" to "/testimage.jpg"
    And the user has set profile picture to "testimage.jpg" from their cloud files
    When the user deletes the existing profile picture
    Then the preview of the profile picture should not be shown on the webUI

  Scenario: User uploads new profile picture
    Given user "Alice" has uploaded file "filesForUpload/testavatar.jpg" to "/testimage.jpg"
    And the user has deleted any existing profile picture
    When the user uploads "testavatar.png" as a new profile picture using the webUI
    Then the preview of the profile picture should be shown on the webUI

  Scenario Outline: User tries to upload different files as profile picture
    Given the user has deleted any existing profile picture
    When the user selects "<file_to_upload>" for uploading as a profile picture using the WebUI
    Then the user <should_or_not> be able to upload the selected file as the profile picture
    Examples:
      | file_to_upload | should_or_not |
      | testavatar.png | should        |
      | testavatar.jpg | should        |
      | data.zip       | should not    |
      | new-lorem.txt  | should not    |
      | simple.pdf     | should not    |
      | simple.odt     | should not    |
      | data.tar.gz    | should not    |
