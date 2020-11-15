@cli @skipOnLDAP @local_storage
Feature: import exported local storage mounts from the command line
  As an admin
  I want to import exported local storage mounts from the command line
  So that I can create previously exported local storage mounts

  Background:
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Alice    |

  @skipOnOcV10.3 @skipOnOcV10.4 @skipOnEncryption @issue-encryption-203
  Scenario: import local storage mounts from a file
    Given the administrator has created the local storage mount "local_storage2"
    And the administrator has uploaded file with content "this is a file in local storage2" to "/local_storage2/file-in-local-storage2.txt"
    And the administrator has exported the local storage mounts using the occ command
    And the administrator has created a file "data/exportedMounts.json" with the last exported content using the testing API
    And the administrator has deleted local storage "local_storage" using the occ command
    And the administrator has deleted local storage "local_storage2" using the occ command
    When the administrator imports the local storage mount from file "data/exportedMounts.json" using the occ command
    And the administrator lists the local storage using the occ command
    Then the following local storage should be listed:
      | MountPoint         | Storage | AuthenticationType | Configuration | Options | ApplicableUsers | ApplicableGroups |
      | /local_storage2    | Local   | None               | datadir:      |         | All             |                  |
    And as "Alice" folder "/local_storage2" should exist
    And the content of file "/local_storage2/file-in-local-storage2.txt" for user "Alice" should be "this is a file in local storage2"

  @skipOnOcV10.3 @skipOnOcV10.4 @skipOnEncryption @issue-encryption-203
  Scenario: import local storage mounts from a file with various user and group settings
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And group "grp1" has been created
    And group "grp2" has been created
    And group "grp3" has been created
    And the administrator has created the local storage mount "local_storage2"
    And the administrator has created the local storage mount "local_storage3"
    And the administrator has uploaded file with content "this is a file in local storage2" to "/local_storage2/file-in-local-storage2.txt"
    And the administrator has uploaded file with content "this is a file in local storage3" to "/local_storage3/file-in-local-storage3.txt"
    And the administrator has added user "Alice" as the applicable user for local storage mount "local_storage2"
    And the administrator has added user "Brian" as the applicable user for local storage mount "local_storage3"
    And the administrator has added user "Carol" as the applicable user for local storage mount "local_storage3"
    And the administrator has added group "grp1" as the applicable group for local storage mount "local_storage2"
    And the administrator has added group "grp2" as the applicable group for local storage mount "local_storage3"
    And the administrator has added group "grp3" as the applicable group for local storage mount "local_storage3"
    And the administrator has exported the local storage mounts using the occ command
    And the administrator has created a file "data/exportedMounts.json" with the last exported content using the testing API
    And the administrator has deleted local storage "local_storage" using the occ command
    And the administrator has deleted local storage "local_storage2" using the occ command
    And the administrator has deleted local storage "local_storage3" using the occ command
    When the administrator imports the local storage mount from file "data/exportedMounts.json" using the occ command
    And the administrator lists the local storage using the occ command
    Then the following local storage should be listed:
      | MountPoint      | Storage | AuthenticationType | Configuration | Options | ApplicableUsers | ApplicableGroups |
      | /local_storage2 | Local   | None               | datadir:      |         | Alice           | grp1             |
      | /local_storage3 | Local   | None               | datadir:      |         | Brian, Carol    | grp2, grp3       |
      | /local_storage  | Local   | None               | datadir:      |         | All             |                  |
    And as "Alice" folder "/local_storage2" should exist
    And the content of file "/local_storage2/file-in-local-storage2.txt" for user "Alice" should be "this is a file in local storage2"
    And as "Brian" folder "/local_storage3" should exist
    And the content of file "/local_storage3/file-in-local-storage3.txt" for user "Brian" should be "this is a file in local storage3"
    But as "Alice" folder "/local_storage3" should not exist
    And as "Brian" folder "/local_storage2" should not exist
