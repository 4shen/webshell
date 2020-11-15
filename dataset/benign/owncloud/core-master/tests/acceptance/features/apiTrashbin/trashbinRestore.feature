@api @TestAlsoOnExternalUserBackend @files_trashbin-app-required @skipOnOcis @issue-ocis-reva-52
Feature: Restore deleted files/folders
  As a user
  I would like to restore files/folders
  So that I can recover accidentally deleted files/folders in ownCloud

  Background:
    Given the administrator has enabled DAV tech_preview
    And user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has uploaded file with content "file to delete" to "/textfile0.txt"

  @files_sharing-app-required
  Scenario Outline: deleting a file in a received folder when restored it comes back to the original path
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has created folder "/shared"
    And user "Alice" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Alice" has shared folder "/shared" with user "Brian"
    And user "Brian" has moved file "/shared" to "/renamed_shared"
    And user "Brian" has deleted file "/renamed_shared/shared_file.txt"
    When user "Brian" restores the file with original path "/renamed_shared/shared_file.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Brian"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    And as "Brian" the file with original path "/renamed_shared/shared_file.txt" should not exist in the trashbin
    And user "Brian" should see the following elements
      | /renamed_shared/                |
      | /renamed_shared/shared_file.txt |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: A deleted file can be restored
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/FOLDER"
    And user "Alice" has created folder "/PARENT"
    And user "Alice" has uploaded file with content "to delete" to "/PARENT/parent.txt"
    And user "Alice" has uploaded file with content "to delete" to "/textfile1.txt"
    And user "Alice" has uploaded file with content "to delete" to "/textfile2.txt"
    And user "Alice" has uploaded file with content "to delete" to "/textfile3.txt"
    And user "Alice" has uploaded file with content "to delete" to "/textfile4.txt"
    And user "Alice" has deleted file "/textfile0.txt"
    And as "Alice" file "/textfile0.txt" should exist in the trashbin
    When user "Alice" restores the folder with original path "/textfile0.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Alice"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    And as "Alice" the folder with original path "/textfile0.txt" should not exist in the trashbin
    And user "Alice" should see the following elements
      | /FOLDER/           |
      | /PARENT/           |
      | /PARENT/parent.txt |
      | /textfile0.txt     |
      | /textfile1.txt     |
      | /textfile2.txt     |
      | /textfile3.txt     |
      | /textfile4.txt     |
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: A file deleted from a folder can be restored to the original folder
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/new-folder"
    And user "Alice" has moved file "/textfile0.txt" to "/new-folder/new-file.txt"
    And user "Alice" has deleted file "/new-folder/new-file.txt"
    When user "Alice" restores the file with original path "/new-folder/new-file.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And as "Alice" the file with original path "/new-folder/new-file.txt" should not exist in the trashbin
    And as "Alice" file "/new-folder/new-file.txt" should exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: A file deleted from a folder is restored to the original folder if the original folder was deleted and restored
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/new-folder"
    And user "Alice" has moved file "/textfile0.txt" to "/new-folder/new-file.txt"
    And user "Alice" has deleted file "/new-folder/new-file.txt"
    And user "Alice" has deleted folder "/new-folder"
    When user "Alice" restores the folder with original path "/new-folder" using the trashbin API
    And user "Alice" restores the file with original path "/new-folder/new-file.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And as "Alice" the file with original path "/new-folder/new-file.txt" should not exist in the trashbin
    And as "Alice" file "/new-folder/new-file.txt" should exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  @skipOnFilesClassifier @issue-files-classifier-291
  Scenario Outline: a file is deleted and restored to a new destination
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/PARENT"
    And user "Alice" has created folder "/PARENT/CHILD"
    And user "Alice" has uploaded file with content "to delete" to "<delete-path>"
    And user "Alice" has deleted file "<delete-path>"
    When user "Alice" restores the file with original path "<delete-path>" to "<restore-path>" using the trashbin API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Alice"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    And as "Alice" the file with original path "<delete-path>" should not exist in the trashbin
    And as "Alice" file "<restore-path>" should exist
    And as "Alice" file "<delete-path>" should not exist
    And the content of file "<restore-path>" for user "Alice" should be "to delete"
    Examples:
      | dav-path | delete-path             | restore-path         |
      | old      | /PARENT/parent.txt      | parent.txt           |
      | new      | /PARENT/parent.txt      | parent.txt           |
      | old      | /PARENT/CHILD/child.txt | child.txt            |
      | new      | /PARENT/CHILD/child.txt | child.txt            |
      | old      | /textfile0.txt          | PARENT/textfile0.txt |
      | new      | /textfile0.txt          | PARENT/textfile0.txt |

  @issue-35974
  Scenario Outline: restoring a file to an already existing path overrides the file
    Given user "Alice" has uploaded file with content "file to delete" to "/.hiddenfile0.txt"
    And using <dav-path> DAV path
    And user "Alice" has created folder "/PARENT"
    And user "Alice" has uploaded file with content "PARENT file content" to <upload-path>
    And user "Alice" has deleted file <delete-path>
    When user "Alice" restores the file with original path <delete-path> to <upload-path> using the trashbin API
    Then the HTTP status code should be "204"
    # Sometimes <upload-path> is found in the trashbin. Should it? Or not?
    # That seems to be what happens when the restore-overwrite happens properly,
    # The original <upload-path> seems to be "deleted" and so goes to the trashbin
    #And as "Alice" the file with original path <upload-path> should not exist in the trashbin
    And as "Alice" file <upload-path> should exist
    # sometimes the restore from trashbin does overwrite the existing file, but sometimes it does not. That is also surprising.
    # the current observed behavior is that if the original <upload-path> ended up in the trashbin,
    # then the new <upload-path> has the "file to delete" content.
    # otherwise <upload-path> has its old content
    And the content of file <upload-path> for user "Alice" if the file is also in the trashbin should be "file to delete" otherwise "PARENT file content"
    #And the content of file <upload-path> for user "Alice" should be "file to delete"
    Examples:
      | dav-path | upload-path                | delete-path        |
      | old      | "/PARENT/textfile0.txt"    | "/textfile0.txt"   |
      | new      | "/PARENT/textfile0.txt"    | "/textfile0.txt"   |
      | old      | "/PARENT/.hiddenfile0.txt" | ".hiddenfile0.txt" |
      | new      | "/PARENT/.hiddenfile0.txt" | ".hiddenfile0.txt" |

  @issue-35900 @files_sharing-app-required
  Scenario Outline: restoring a file to a read-only folder
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Brian" has created folder "shareFolderParent"
    And user "Brian" has shared folder "shareFolderParent" with user "Alice" with permissions "read"
    And as "Alice" folder "/shareFolderParent" should exist
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Alice" restores the file with original path "/textfile0.txt" to "/shareFolderParent/textfile0.txt" using the trashbin API
    Then the HTTP status code should be "201"
    #Then the HTTP status code should be "403"
    And as "Alice" the file with original path "/textfile0.txt" should not exist in the trashbin
    #And as "Alice" the file with original path "/textfile0.txt" should exist in the trashbin
    And as "Alice" file "/shareFolderParent/textfile0.txt" should exist
    #And as "Alice" file "/shareFolderParent/textfile0.txt" should not exist
    And as "Brian" file "/shareFolderParent/textfile0.txt" should exist
    #And as "Brian" file "/shareFolderParent/textfile0.txt" should not exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  @issue-35900 @files_sharing-app-required
  Scenario Outline: restoring a file to a read-only sub-folder
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Brian" has created folder "shareFolderParent"
    And user "Brian" has created folder "shareFolderParent/shareFolderChild"
    And user "Brian" has shared folder "shareFolderParent" with user "Alice" with permissions "read"
    And as "Alice" folder "/shareFolderParent/shareFolderChild" should exist
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Alice" restores the file with original path "/textfile0.txt" to "/shareFolderParent/shareFolderChild/textfile0.txt" using the trashbin API
    Then the HTTP status code should be "201"
    #Then the HTTP status code should be "403"
    And as "Alice" the file with original path "/textfile0.txt" should not exist in the trashbin
    #And as "Alice" the file with original path "/textfile0.txt" should exist in the trashbin
    And as "Alice" file "/shareFolderParent/shareFolderChild/textfile0.txt" should exist
    #And as "Alice" file "/shareFolderParent/shareFolderChild/textfile0.txt" should not exist
    And as "Brian" file "/shareFolderParent/shareFolderChild/textfile0.txt" should exist
    #And as "Brian" file "/shareFolderParent/shareFolderChild/textfile0.txt" should not exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: A file deleted from a folder is restored to the original folder if the original folder was deleted and recreated
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/new-folder"
    And user "Alice" has moved file "/textfile0.txt" to "/new-folder/new-file.txt"
    And user "Alice" has deleted file "/new-folder/new-file.txt"
    And user "Alice" has deleted folder "/new-folder"
    When user "Alice" creates folder "/new-folder" using the WebDAV API
    And user "Alice" restores the file with original path "/new-folder/new-file.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Alice"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    And as "Alice" the file with original path "/new-folder/new-file.txt" should not exist in the trashbin
    And as "Alice" file "/new-folder/new-file.txt" should exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  @local_storage
    @skipOnEncryptionType:user-keys @encryption-issue-42
    @skip_on_objectstore
  Scenario Outline: Deleting a file into external storage moves it to the trashbin and can be restored
    Given using <dav-path> DAV path
    And the administrator has invoked occ command "files:scan --all"
    And user "Alice" has created folder "/local_storage/tmp"
    And user "Alice" has moved file "/textfile0.txt" to "/local_storage/tmp/textfile0.txt"
    And user "Alice" has deleted file "/local_storage/tmp/textfile0.txt"
    And as "Alice" the folder with original path "/local_storage/tmp/textfile0.txt" should exist in the trashbin
    When user "Alice" restores the folder with original path "/local_storage/tmp/textfile0.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Alice"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    And as "Alice" the folder with original path "/local_storage/tmp/textfile0.txt" should not exist in the trashbin
    And user "Alice" should see the following elements
      | /local_storage/                  |
      | /local_storage/tmp/              |
      | /local_storage/tmp/textfile0.txt |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @local_storage
  @skipOnEncryptionType:user-keys @encryption-issue-42
  @skip_on_objectstore
  Scenario: Deleting an updated file into external storage moves it to the trashbin and can be restored
    Given using old DAV path
    And the administrator has invoked occ command "files:scan --all"
    And user "Alice" has created folder "/local_storage/tmp"
    And user "Alice" has moved file "/textfile0.txt" to "/local_storage/tmp/textfile0.txt"
    And user "Alice" has uploaded chunk file "1" of "1" with "AA" to "/local_storage/tmp/textfile0.txt"
    And user "Alice" has deleted file "/local_storage/tmp/textfile0.txt"
    And as "Alice" the folder with original path "/local_storage/tmp/textfile0.txt" should exist in the trashbin
    When user "Alice" restores the folder with original path "/local_storage/tmp/textfile0.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And as "Alice" the folder with original path "/local_storage/tmp/textfile0.txt" should not exist in the trashbin
    And the downloaded content when downloading file "/local_storage/tmp/textfile0.txt" for user "Alice" with range "bytes=0-1" should be "AA"

  @local_storage
  @skipOnEncryptionType:user-keys @encryption-issue-42
  @skip_on_objectstore
  Scenario: Deleting an updated file into external storage moves it to the trashbin and can be restored
    Given using new DAV path
    And the administrator has invoked occ command "files:scan --all"
    And user "Alice" has created folder "/local_storage/tmp"
    And user "Alice" has moved file "/textfile0.txt" to "/local_storage/tmp/textfile0.txt"
    And user "Alice" has uploaded the following chunks to "/local_storage/tmp/textfile0.txt" with new chunking
      | number | content |
      | 1      | AA      |
    And user "Alice" has deleted file "/local_storage/tmp/textfile0.txt"
    And as "Alice" the folder with original path "/local_storage/tmp/textfile0.txt" should exist in the trashbin
    When user "Alice" restores the folder with original path "/local_storage/tmp/textfile0.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And as "Alice" the folder with original path "/local_storage/tmp/textfile0.txt" should not exist in the trashbin
    And the downloaded content when downloading file "/local_storage/tmp/textfile0.txt" for user "Alice" with range "bytes=0-1" should be "AA"

  @smokeTest @skipOnOcV10.3
  Scenario Outline: A deleted file cannot be restored by a different user
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Brian" tries to restore the file with original path "/textfile0.txt" from the trashbin of user "Alice" using the trashbin API
    Then the HTTP status code should be "401"
    And as "Alice" the folder with original path "/textfile0.txt" should exist in the trashbin
    And user "Alice" should not see the following elements
      | /textfile0.txt |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: A deleted file cannot be restored with invalid password
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Alice" tries to restore the file with original path "/textfile0.txt" from the trashbin of user "Alice" using the password "invalid" and the trashbin API
    Then the HTTP status code should be "401"
    And as "Alice" the folder with original path "/textfile0.txt" should exist in the trashbin
    And user "Alice" should not see the following elements
      | /textfile0.txt |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: A deleted file cannot be restored without using a password
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Alice" tries to restore the file with original path "/textfile0.txt" from the trashbin of user "Alice" using the password "" and the trashbin API
    Then the HTTP status code should be "401"
    And as "Alice" the folder with original path "/textfile0.txt" should exist in the trashbin
    And user "Alice" should not see the following elements
      | /textfile0.txt |
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Files with strange names can be restored
    Given using <dav-path> DAV path
    And user "Alice" has uploaded file with content "file original content" to "<file-to-upload>"
    And user "Alice" has deleted file "<file-to-upload>"
    And user "Alice" restores the file with original path "<file-to-upload>" using the trashbin API
    Then the HTTP status code should be "201"
    And as "Alice" the file with original path "<file-to-upload>" should not exist in the trashbin
    And as "Alice" file "<file-to-upload>" should exist
    And the content of file "<file-to-upload>" for user "Alice" should be "file original content"
    Examples:
      | dav-path | file-to-upload        |
      | old      | 😛 😜 🐱 🐭 ⌚️ ♀️ 🚴‍♂️     |
      | new      | 😛 😜 🐱 🐭 ⌚️ ♀️ 🚴‍♂️     |
      | old      | strängé नेपाली file     |
      | new      | strängé नेपाली file     |

  Scenario Outline: A file deleted from a multi level sub-folder can be restored to the original folder
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/new-folder"
    And user "Alice" has created folder "/new-folder/folder1/"
    And user "Alice" has created folder "/new-folder/folder1/folder2/"
    And user "Alice" has moved file "/textfile0.txt" to "/new-folder/folder1/folder2/new-file.txt"
    And user "Alice" has deleted file "/new-folder/folder1/folder2/new-file.txt"
    When user "Alice" restores the file with original path "/new-folder/folder1/folder2/new-file.txt" using the trashbin API
    Then the HTTP status code should be "201"
    And as "Alice" the file with original path "/new-folder/folder1/folder2/new-file.txt" should not exist in the trashbin
    And as "Alice" file "/new-folder/folder1/folder2/new-file.txt" should exist
    Examples:
      | dav-path |
      | old      |
      | new      |
