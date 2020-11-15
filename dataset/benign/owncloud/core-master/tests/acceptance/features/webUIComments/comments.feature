@webUI @insulated @disablePreviews @comments-app-required
Feature: Add, delete and edit comments in files and folders

  As a user
  I would like to add, delete and edit comments in any file/folder
  So that I can provide more information about the file/folder

  Background:
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Alice    |
      | Brian    |
    And user "Alice" has uploaded file with content "does-not-matter" to "/lorem.txt"
    And user "Alice" has logged in using the webUI
    And the user has browsed to the files page

  Scenario Outline: user adds and deletes comment for a file/folder
    When the user browses directly to display the "comments" details of file "lorem.txt" in folder "/"
    And the user comments with content "<comment>" using the webUI
    Then the comment "<comment>" should be listed in the comments tab in details dialog
    When the user deletes the comment "<comment>" using the webUI
    Then the comment "<comment>" should not be listed in the comments tab in details dialog
    Examples:
      | comment     |
      | lorem ipsum |
      | 😀 🤖       |
      | नेपालि      |

  @skipOnFIREFOX @files_sharing-app-required
  Scenario Outline: Add comment on a shared file and check it is shown in other user's UI
    When the user renames file "lorem.txt" to "new-lorem.txt" using the webUI
    And the user browses directly to display the "comments" details of file "new-lorem.txt" in folder "/"
    And the user comments with content "<comment>" using the webUI
    And the user shares file "new-lorem.txt" with user "Brian" using the webUI
    And the user re-logs in as "Brian" using the webUI
    And the user browses directly to display the "comments" details of file "new-lorem.txt" in folder "/"
    Then the comment "<comment>" should be listed in the comments tab in details dialog
    Examples:
      | comment     |
      | lorem ipsum |
      | 😀 🤖       |
      | नेपालि      |
