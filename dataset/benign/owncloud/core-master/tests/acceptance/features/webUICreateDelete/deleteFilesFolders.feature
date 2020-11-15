@webUI @insulated @disablePreviews
Feature: deleting files and folders
  As a user
  I want to delete files and folders
  So that I can keep my filing system clean and tidy

  Background:
    Given these users have been created with default attributes and skeleton files:
      | username |
      | Alice    |
    And user "Alice" has logged in using the webUI
    And the user has browsed to the files page

  @smokeTest
  Scenario: Delete files & folders one by one and check its existence after page reload
    When the user deletes the following elements using the webUI
      | name                                  |
      | simple-folder                         |
      | lorem.txt                             |
      | strängé नेपाली folder                 |
      | strängé filename (duplicate #2 &).txt |
    Then as "Alice" folder "simple-folder" should not exist
    And as "Alice" file "lorem.txt" should not exist
    And as "Alice" folder "strängé नेपाली folder" should not exist
    And as "Alice" file "strängé filename (duplicate #2 &).txt" should not exist
    And the deleted elements should not be listed on the webUI
    And the deleted elements should not be listed on the webUI after a page reload

  @skipOnFIREFOX
  Scenario: Delete a file with problematic characters
    When the user renames the following file using the webUI
      | from-name-parts | to-name-parts   |
      | lorem.txt       | 'single'        |
      |                 | "double" quotes |
      |                 | question?       |
      |                 | &and#hash       |
    And the user reloads the current page of the webUI
    Then the following file should be listed on the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |
    And the user deletes the following file using the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |
    Then the following file should not be listed on the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |
    When the user reloads the current page of the webUI
    Then the following file should not be listed on the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |

  @smokeTest
  @skipOnEncryption @encryption-issue-74
  Scenario: Delete multiple files at once
    When the user batch deletes these files using the webUI
      | name          |
      | data.zip      |
      | lorem.txt     |
      | simple-folder |
    Then as "Alice" file "data.zip" should not exist
    And as "Alice" file "lorem.txt" should not exist
    And as "Alice" folder "simple-folder" should not exist
    And the deleted elements should not be listed on the webUI
    And the deleted elements should not be listed on the webUI after a page reload

  @skipOnEncryption @encryption-issue-74
  Scenario: Delete all files at once
    When the user marks all files for batch action using the webUI
    And the user batch deletes the marked files using the webUI
    # Check just some example files/folders that should not exist any more
    Then as "Alice" file "data.zip" should not exist
    And as "Alice" file "lorem.txt" should not exist
    And as "Alice" folder "simple-folder" should not exist
    And the folder should be empty on the webUI
    And the folder should be empty on the webUI after a page reload

  @skipOnEncryption @encryption-issue-74
  Scenario: Delete all except for a few files at once
    When the user marks all files for batch action using the webUI
    And the user unmarks these files for batch action using the webUI
      | name          |
      | lorem.txt     |
      | simple-folder |
    And the user batch deletes the marked files using the webUI
    Then as "Alice" file "lorem.txt" should exist
    And as "Alice" folder "simple-folder" should exist
    And folder "simple-folder" should be listed on the webUI
    And file "lorem.txt" should be listed on the webUI
    # Check just an example of a file that should not exist any more
    But as "Alice" file "data.zip" should not exist
    And file "data.zip" should not be listed on the webUI

  Scenario: Delete an empty folder
    When the user creates a folder with the name "my-empty-folder" using the webUI
    And the user creates a folder with the name "my-other-empty-folder" using the webUI
    And the user deletes folder "my-empty-folder" using the webUI
    Then as "Alice" folder "my-other-empty-folder" should exist
    And folder "my-other-empty-folder" should be listed on the webUI
    But as "Alice" folder "my-empty-folder" should not exist
    And folder "my-empty-folder" should not be listed on the webUI

  Scenario: Delete the last file in a folder
    When the user deletes file "zzzz-must-be-last-file-in-folder.txt" using the webUI
    Then as "Alice" file "zzzz-must-be-last-file-in-folder.txt" should not exist
    And file "zzzz-must-be-last-file-in-folder.txt" should not be listed on the webUI

  @files_sharing-app-required
  Scenario: delete files from shared with others page
    Given user "Brian" has been created with default attributes and without skeleton files
    And the user has shared file "lorem.txt" with user "Brian"
    And the user has shared folder "simple-folder" with user "Brian"
    And the user has browsed to the shared-with-others page
    When the user deletes file "lorem.txt" using the webUI
    And the user deletes folder "simple-folder" using the webUI
    Then as "Alice" file "lorem.txt" should not exist
    And as "Alice" folder "simple-folder" should not exist
    And file "lorem.txt" should not be listed on the webUI
    And folder "simple-folder" should not be listed on the webUI
    When the user browses to the files page
    Then file "lorem.txt" should not be listed on the webUI
    And folder "simple-folder" should not be listed on the webUI

  @public_link_share-feature-required @files_sharing-app-required
  Scenario: delete files from shared by link page
    Given the user has created a public link share of file "lorem.txt"
    And the user has browsed to the shared-by-link page
    Then file "lorem.txt" should be listed on the webUI
    When the user deletes file "lorem.txt" using the webUI
    Then as "Alice" file "lorem.txt" should not exist
    And file "lorem.txt" should not be listed on the webUI
    When the user browses to the files page
    Then file "lorem.txt" should not be listed on the webUI

  @systemtags-app-required
  Scenario: delete files from tags page
    Given user "Alice" has created a "normal" tag with name "lorem"
    And user "Alice" has added tag "lorem" to file "/lorem.txt"
    When the user browses to the tags page
    And the user searches for tag "lorem" using the webUI
    Then file "lorem.txt" should be listed on the webUI
    When the user deletes file "lorem.txt" using the webUI
    Then as "Alice" file "lorem.txt" should not exist
    And file "lorem.txt" should not be listed on the webUI
    When the user browses to the files page
    Then file "lorem.txt" should not be listed on the webUI

  @files_sharing-app-required
  Scenario: delete a file on a public share
    Given user "Alice" has created a public link share with settings
      | path        | /simple-folder            |
      | permissions | read,update,create,delete |
    And the public accesses the last created public link using the webUI
    When the user deletes the following elements using the webUI
      | name                                  |
      | simple-empty-folder                   |
      | lorem.txt                             |
      | strängé filename (duplicate #2 &).txt |
    Then as "Alice" file "simple-folder/lorem.txt" should not exist
    And as "Alice" folder "simple-folder/simple-empty-folder" should not exist
    And as "Alice" file "simple-folder/strängé filename (duplicate #2 &).txt" should not exist
    And the deleted elements should not be listed on the webUI
    And the deleted elements should not be listed on the webUI after a page reload

  @skipOnFIREFOX @files_sharing-app-required
  Scenario: delete a file on a public share with problematic characters
    Given user "Alice" has created a public link share with settings
      | path        | /simple-folder |
      | permissions | read,change    |
    And the public accesses the last created public link using the webUI
    When the user renames the following file using the webUI
      | from-name-parts | to-name-parts   |
      | lorem.txt       | 'single'        |
      |                 | "double" quotes |
      |                 | question?       |
      |                 | &and#hash       |
    And the user deletes the following file using the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |
    Then the following file should not be listed on the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |
    When the user reloads the current page of the webUI
    Then the following file should not be listed on the webUI
      | name-parts      |
      | 'single'        |
      | "double" quotes |
      | question?       |
      | &and#hash       |

  @skipOnEncryption @encryption-issue-74 @files_sharing-app-required
  Scenario: Delete multiple files at once on a public share
    Given user "Alice" has created a public link share with settings
      | path        | /simple-folder            |
      | permissions | read,update,create,delete |
    And the public accesses the last created public link using the webUI
    When the user batch deletes these files using the webUI
      | name                |
      | data.zip            |
      | lorem.txt           |
      | simple-empty-folder |
    Then as "Alice" file "simple-folder/data.zip" should not exist
    And as "Alice" file "simple-folder/lorem.txt" should not exist
    And as "Alice" folder "simple-folder/simple-empty-folder" should not exist
    And the deleted elements should not be listed on the webUI
    And the deleted elements should not be listed on the webUI after a page reload

  @files_sharing-app-required
  Scenario Outline: delete a folder when there is a default folder for received shares
    Given the administrator has set the default folder for received shares to "<share_folder>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Brian" has created folder "/ShareThis"
    And user "Alice" has created folder "<other_folder1>"
    And user "Alice" has created folder "<top_folder2>"
    And user "Alice" has created folder "<top_folder2>/<other_folder2>"
    And user "Brian" has shared folder "/ShareThis" with user "Alice"
    And the user reloads the current page of the webUI
    When the user deletes folder "<other_folder1>" using the webUI
    Then as "Alice" folder "<other_folder1>" should not exist
    When the user opens folder "<top_folder2>" using the webUI
    And the user deletes folder "<other_folder2>" using the webUI
    Then as "Alice" folder "<top_folder2>/<other_folder2>" should not exist
    When the user browses to the files page
    And the user deletes folder "<top_folder2>" using the webUI
    Then as "Alice" folder "<top_folder2>" should not exist
    And it should not be possible to delete folder "<top_share_folder_on_ui>" using the webUI
    And as "Alice" folder "<share_folder>/ShareThis" should exist
    Examples:
      | share_folder        | top_share_folder_on_ui | other_folder1 | top_folder2 | other_folder2  |
      | /ReceivedShares     | ReceivedShares         | Received      | Top         | ReceivedShares |
      | ReceivedShares      | ReceivedShares         | Received      | Top         | ReceivedShares |
      | /My/Received/Shares | My                     | M             | Received    | Shares         |
