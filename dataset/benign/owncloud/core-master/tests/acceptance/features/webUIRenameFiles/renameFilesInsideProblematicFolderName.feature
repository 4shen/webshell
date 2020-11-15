@webUI @insulated @disablePreviews @skipOnFIREFOX
Feature: Renaming files inside a folder with problematic name
  As a user
  I want to rename a file
  So that I can recognize my file easily

  Background:
    Given user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has logged in using the webUI

  Scenario Outline: Rename the existing file inside a problematic folder
    Given user "Alice" has uploaded file with content "some content" to "/randomfile.txt"
    And user "Alice" has created folder "<folder>"
    And user "Alice" has moved file "/randomfile.txt" to "/<folder>/randomfile.txt"
    And the user reloads the current page of the webUI
    When the user opens folder "<folder>" using the webUI
    And the user renames file "randomfile.txt" to "???.txt" using the webUI
    Then file "???.txt" should be listed on the webUI
    When the user reloads the current page of the webUI
    Then file "???.txt" should be listed on the webUI
    Examples:
      | folder                |
      | 0                     |
      | 'single'quotes        |
      | strängé नेपाली folder |