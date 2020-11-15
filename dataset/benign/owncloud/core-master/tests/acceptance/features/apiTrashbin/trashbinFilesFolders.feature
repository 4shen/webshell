@api @TestAlsoOnExternalUserBackend @files_trashbin-app-required @skipOnOcis @issue-ocis-reva-52
Feature: files and folders exist in the trashbin after being deleted
  As a user
  I want deleted files and folders to be available in the trashbin
  So that I can recover data easily

  Background:
    Given the administrator has enabled DAV tech_preview
    And user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has uploaded file with content "to delete" to "/textfile0.txt"

  @smokeTest
  Scenario Outline: deleting a file moves it to trashbin
    Given using <dav-path> DAV path
    When user "Alice" deletes file "/textfile0.txt" using the WebDAV API
    Then as "Alice" file "/textfile0.txt" should exist in the trashbin
    But as "Alice" file "/textfile0.txt" should not exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: deleting a folder moves it to trashbin
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/tmp"
    When user "Alice" deletes folder "/tmp" using the WebDAV API
    Then as "Alice" folder "/tmp" should exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: deleting a file in a folder moves it to the trashbin root
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/new-folder"
    And user "Alice" has moved file "/textfile0.txt" to "/new-folder/new-file.txt"
    When user "Alice" deletes file "/new-folder/new-file.txt" using the WebDAV API
    Then as "Alice" the file with original path "/new-folder/new-file.txt" should exist in the trashbin
    And as "Alice" file "/new-file.txt" should exist in the trashbin
    But as "Alice" file "/new-folder/new-file.txt" should not exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  @files_sharing-app-required
  Scenario Outline: deleting a file in a shared folder moves it to the trashbin root
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/shared"
    And user "Alice" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Alice" has shared folder "/shared" with user "Brian"
    When user "Alice" deletes file "/shared/shared_file.txt" using the WebDAV API
    Then as "Alice" the file with original path "/shared/shared_file.txt" should exist in the trashbin
    And as "Alice" file "/shared_file.txt" should exist in the trashbin
    But as "Alice" file "/shared/shared_file.txt" should not exist
    Examples:
      | dav-path |
      | old      |
      | new      |

  @files_sharing-app-required
  Scenario Outline: deleting a shared folder moves it to trashbin
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/shared"
    And user "Alice" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Alice" has shared folder "/shared" with user "Brian"
    When user "Alice" deletes folder "/shared" using the WebDAV API
    Then as "Alice" the folder with original path "/shared" should exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest @files_sharing-app-required
  Scenario Outline: deleting a received folder doesn't move it to trashbin
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/shared"
    And user "Alice" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Alice" has shared folder "/shared" with user "Brian"
    And user "Brian" has moved folder "/shared" to "/renamed_shared"
    When user "Brian" deletes folder "/renamed_shared" using the WebDAV API
    Then as "Brian" the folder with original path "/renamed_shared" should not exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  @files_sharing-app-required
  Scenario Outline: deleting a file in a received folder moves it to trashbin
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/shared"
    And user "Alice" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Alice" has shared folder "/shared" with user "Brian"
    And user "Brian" has moved file "/shared" to "/renamed_shared"
    When user "Brian" deletes file "/renamed_shared/shared_file.txt" using the WebDAV API
    Then as "Brian" the file with original path "/renamed_shared/shared_file.txt" should exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  @issue-23151
  # This scenario deletes many files as close together in time as the test can run.
  # On a very slow system, the file deletes might all happen in different seconds.
  # But on "reasonable" systems, some of the files will be deleted in the same second,
  # thus testing the required behavior.
  Scenario Outline: trashbin can store two files with the same name but different origins when the files are deleted close together in time
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/folderA"
    And user "Alice" has created folder "/folderB"
    And user "Alice" has created folder "/folderC"
    And user "Alice" has created folder "/folderD"
    And user "Alice" has copied file "/textfile0.txt" to "/folderA/textfile0.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/folderB/textfile0.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/folderC/textfile0.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/folderD/textfile0.txt"
    When user "Alice" deletes these files without delays using the WebDAV API
      | /textfile0.txt         |
      | /folderA/textfile0.txt |
      | /folderB/textfile0.txt |
      | /folderC/textfile0.txt |
      | /folderD/textfile0.txt |
    # When issue-23151 is fixed, uncomment these lines. They should pass reliably.
    #Then as "Alice" the folder with original path "/folderA/textfile0.txt" should exist in the trashbin
    #And as "Alice" the folder with original path "/folderB/textfile0.txt" should exist in the trashbin
    #And as "Alice" the folder with original path "/folderC/textfile0.txt" should exist in the trashbin
    #And as "Alice" the folder with original path "/folderD/textfile0.txt" should exist in the trashbin
    And as "Alice" the folder with original path "/textfile0.txt" should exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  # Note: the underlying acceptance test code ensures that each delete step is separated by a least 1 second
  Scenario Outline: trashbin can store two files with the same name but different origins when the deletes are separated by at least 1 second
    Given using <dav-path> DAV path
    And user "Alice" has created folder "/folderA"
    And user "Alice" has created folder "/folderB"
    And user "Alice" has copied file "/textfile0.txt" to "/folderA/textfile0.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/folderB/textfile0.txt"
    When user "Alice" deletes file "/folderA/textfile0.txt" using the WebDAV API
    And user "Alice" deletes file "/folderB/textfile0.txt" using the WebDAV API
    And user "Alice" deletes file "/textfile0.txt" using the WebDAV API
    Then as "Alice" the folder with original path "/folderA/textfile0.txt" should exist in the trashbin
    And as "Alice" the folder with original path "/folderB/textfile0.txt" should exist in the trashbin
    And as "Alice" the folder with original path "/textfile0.txt" should exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  @local_storage
    @skipOnEncryptionType:user-keys @encryption-issue-42
    @skip_on_objectstore
  Scenario Outline: Deleting a folder into external storage moves it to the trashbin
    Given using <dav-path> DAV path
    And the administrator has invoked occ command "files:scan --all"
    And user "Alice" has created folder "/local_storage/tmp"
    And user "Alice" has moved file "/textfile0.txt" to "/local_storage/tmp/textfile0.txt"
    When user "Alice" deletes folder "/local_storage/tmp" using the WebDAV API
    Then as "Alice" the folder with original path "/local_storage/tmp" should exist in the trashbin
    Examples:
      | dav-path |
      | old      |
      | new      |

  @skipOnLDAP @skip_on_objectstore @skipOnOcV10.3
  Scenario Outline: Listing other user's trashbin is prohibited
    Given using <dav-path> DAV path
    And user "testtrashbin100" has been created with default attributes and skeleton files
    And user "Brian" has been created with default attributes and without skeleton files
    And user "testtrashbin100" has deleted file "/textfile1.txt"
    When user "Brian" tries to list the trashbin content for user "testtrashbin100"
    Then the HTTP status code should be "401"
    And the last webdav response should not contain the following elements
      | path          | user            |
      | textfile1.txt | testtrashbin100 |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest @skipOnLDAP @skip_on_objectstore @skipOnOcV10.3
  Scenario Outline: Listing other user's trashbin is prohibited
    Given using <dav-path> DAV path
    And user "testtrashbin101" has been created with default attributes and skeleton files
    And user "Brian" has been created with default attributes and without skeleton files
    And user "testtrashbin101" has deleted file "/textfile0.txt"
    And user "testtrashbin101" has deleted file "/textfile2.txt"
    When user "Brian" tries to list the trashbin content for user "testtrashbin101"
    Then the HTTP status code should be "401"
    And the last webdav response should not contain the following elements
      | path          | user            |
      | textfile0.txt | testtrashbin101 |
      | textfile2.txt | testtrashbin101 |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @skipOnLDAP @skip_on_objectstore @skipOnOcV10.3
  Scenario Outline: Listing other user's trashbin is prohibited
    Given using <dav-path> DAV path
    And user "testtrashbin102" has been created with default attributes and skeleton files
    And user "Brian" has been created with default attributes and without skeleton files
    And user "testtrashbin102" has deleted file "/textfile0.txt"
    And user "testtrashbin102" has deleted file "/textfile2.txt"
    And the administrator deletes user "testtrashbin102" using the provisioning API
    And these users have been created with default attributes and skeleton files but not initialized:
      | username         |
      | testtrashbin102  |
    And user "testtrashbin102" has deleted file "/textfile3.txt"
    When user "Brian" tries to list the trashbin content for user "testtrashbin102"
    Then the HTTP status code should be "401"
    And the last webdav response should not contain the following elements
      | path          | user            |
      | textfile0.txt | testtrashbin102 |
      | textfile2.txt | testtrashbin102 |
      | textfile3.txt | testtrashbin102 |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: Get trashbin content with wrong password
    Given using <dav-path> DAV path
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Alice" tries to list the trashbin content for user "Alice" using password "invalid"
    Then the HTTP status code should be "401"
    And the last webdav response should not contain the following elements
      | path           | user  |
      | /textfile0.txt | Alice |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: Get trashbin content without password
    Given using <dav-path> DAV path
    And user "Alice" has deleted file "/textfile0.txt"
    When user "Alice" tries to list the trashbin content for user "Alice" using password ""
    Then the HTTP status code should be "401"
    And the last webdav response should not contain the following elements
      | path           | user  |
      | /textfile0.txt | Alice |
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: user with unusual username deletes a file
    Given user "<username>" has been created with default attributes and without skeleton files
    And user "<username>" has uploaded file with content "to delete" to "/textfile0.txt"
    And using <dav-path> DAV path
    When user "<username>" deletes file "/textfile0.txt" using the WebDAV API
    Then as "<username>" file "/textfile0.txt" should exist in the trashbin
    But as "<username>" file "/textfile0.txt" should not exist
    Examples:
      | dav-path | username |
      | old      | dash-123 |
      | old      | null     |
      | old      | nil      |
      | old      | 123      |
      | old      | -123     |
      | old      | 0.0      |
      | new      | dash-123 |
      | new      | null     |
      | new      | nil      |
      | new      | 123      |
      | new      | -123     |
      | new      | 0.0      |
