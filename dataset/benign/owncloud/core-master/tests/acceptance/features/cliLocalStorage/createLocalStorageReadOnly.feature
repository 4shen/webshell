@cli @skipOnLDAP @local_storage
Feature: create read-only local storage from the command line
  As an admin
  I want to create read-only local storage from the command line
  So that local folders on my server can be made visible but read-only to users of ownCloud

  Background:
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Alice    |
      | Brian    |

  Scenario: create read-only local storage that is available to all users
    Given the administrator has created the local storage mount "local_storage2"
    And the administrator has uploaded file with content "this is a file in local storage" to "/local_storage2/file-in-local-storage.txt"
    When the administrator sets the external storage "local_storage2" to read-only using the occ command
    Then the command should have been successful
    And as "Alice" folder "/local_storage2" should exist
    And as "Brian" folder "/local_storage2" should exist
    And the content of file "/local_storage2/file-in-local-storage.txt" for user "Alice" should be "this is a file in local storage"
    And the content of file "/local_storage2/file-in-local-storage.txt" for user "Brian" should be "this is a file in local storage"
    And user "Alice" should not be able to delete file "/local_storage2/file-in-local-storage.txt"
    And user "Alice" should not be able to rename file "/local_storage2/file-in-local-storage.txt" to "/local_storage2/another-name.txt"
    And user "Alice" should not be able to upload file "filesForUpload/textfile.txt" to "/local_storage2/textfile.txt"
