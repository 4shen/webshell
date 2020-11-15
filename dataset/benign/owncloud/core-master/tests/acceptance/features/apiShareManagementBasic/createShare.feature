@api @TestAlsoOnExternalUserBackend @files_sharing-app-required
Feature: sharing

  Background:
    Given user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has uploaded file with content "ownCloud test text file 0" to "/textfile0.txt"

  @smokeTest
  @skipOnEncryptionType:user-keys @issue-32322
  @skipOnOcis @issue-ocis-reva-11 @issue-ocis-reva-243
  Scenario Outline: Creating a share of a file with a user, the default permissions are read(1)+update(2)+can-share(16)
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    When user "Alice" shares file "textfile0.txt" with user "Brian" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | share_with             | %username%        |
      | share_with_displayname | %displayname%     |
      | file_target            | /textfile0.txt    |
      | path                   | /textfile0.txt    |
      | permissions            | share,read,update |
      | uid_owner              | %username%        |
      | displayname_owner      | %displayname%     |
      | item_type              | file              |
      | mimetype               | text/plain        |
      | storage_id             | ANY_VALUE         |
      | share_type             | user              |
    And the content of file "/textfile0.txt" for user "Brian" should be "ownCloud test text file 0"
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  Scenario Outline: Creating a share of a file with a user and asking for various permission combinations
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    When user "Alice" shares file "textfile0.txt" with user "Brian" with permissions <requested_permissions> using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | share_with             | %username%            |
      | share_with_displayname | %displayname%         |
      | file_target            | /textfile0.txt        |
      | path                   | /textfile0.txt        |
      | permissions            | <granted_permissions> |
      | uid_owner              | %username%            |
      | displayname_owner      | %displayname%         |
      | item_type              | file                  |
      | mimetype               | text/plain            |
      | storage_id             | ANY_VALUE             |
      | share_type             | user                  |
    Examples:
      | ocs_api_version | requested_permissions | granted_permissions | ocs_status_code |
      # Ask for full permissions. You get share plus read plus update. create and delete do not apply to shares of a file
      | 1               | 31                    | 19                  | 100             |
      | 2               | 31                    | 19                  | 200             |
      # Ask for read, share (17), create and delete. You get share plus read
      | 1               | 29                    | 17                  | 100             |
      | 2               | 29                    | 17                  | 200             |
      # Ask for read, update, create, delete. You get read plus update.
      | 1               | 15                    | 3                   | 100             |
      | 2               | 15                    | 3                   | 200             |
      # Ask for just update. You get exactly update (you do not get read or anything else)
      | 1               | 2                     | 2                   | 100             |
      | 2               | 2                     | 2                   | 200             |

  Scenario Outline: Creating a share of a file with no permissions should fail
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has uploaded file with content "Random data" to "randomfile.txt"
    When user "Alice" shares file "randomfile.txt" with user "Brian" with permissions "0" using the sharing API
    Then the OCS status code should be "400"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" file "randomfile.txt" should not exist
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 400              |

  @skipOnOcV10 @issue-ocis-reva-243
  # after fixing the issue, enable for ocis
  Scenario Outline: more tests to demonstrate different ocis-reva issue 243 behaviours
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/home"
    And user "Alice" has uploaded file with content "Random data" to "/home/randomfile.txt"
    When user "Alice" shares file "/home/randomfile.txt" with user "Brian" using the sharing API
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" file "randomfile.txt" should not exist
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 200              |

  Scenario Outline: Creating a share of a folder with no permissions should fail
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/afolder"
    When user "Alice" shares folder "afolder" with user "Brian" with permissions "0" using the sharing API
    Then the OCS status code should be "400"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" folder "afolder" should not exist
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 400              |

  Scenario Outline: Creating a share of a folder with a user, the default permissions are all permissions(31)
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/FOLDER"
    When user "Alice" shares folder "/FOLDER" with user "Brian" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | share_with             | %username%           |
      | share_with_displayname | %displayname%        |
      | file_target            | /FOLDER              |
      | path                   | /FOLDER              |
      | permissions            | all                  |
      | uid_owner              | %username%           |
      | displayname_owner      | %displayname%        |
      | item_type              | folder               |
      | mimetype               | httpd/unix-directory |
      | storage_id             | ANY_VALUE            |
      | share_type             | user                 |
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-34
  Scenario Outline: Creating a share of a file with a group, the default permissions are read(1)+update(2)+can-share(16)
    Given using OCS API version "<ocs_api_version>"
    And group "grp1" has been created
    When user "Alice" shares file "/textfile0.txt" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | share_with             | grp1              |
      | share_with_displayname | grp1              |
      | file_target            | /textfile0.txt    |
      | path                   | /textfile0.txt    |
      | permissions            | share,read,update |
      | uid_owner              | %username%        |
      | displayname_owner      | %displayname%     |
      | item_type              | file              |
      | mimetype               | text/plain        |
      | storage_id             | ANY_VALUE         |
      | share_type             | group             |
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-34
  Scenario Outline: Creating a share of a folder with a group, the default permissions are all permissions(31)
    Given using OCS API version "<ocs_api_version>"
    And group "grp1" has been created
    And user "Alice" has created folder "/FOLDER"
    When user "Alice" shares folder "/FOLDER" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | share_with             | grp1                 |
      | share_with_displayname | grp1                 |
      | file_target            | /FOLDER              |
      | path                   | /FOLDER              |
      | permissions            | all                  |
      | uid_owner              | %username%           |
      | displayname_owner      | %displayname%        |
      | item_type              | folder               |
      | mimetype               | httpd/unix-directory |
      | storage_id             | ANY_VALUE            |
      | share_type             | group                |
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @smokeTest @skipOnOcis @issue-ocis-reva-34 @issue-ocis-reva-243
  Scenario Outline: Share of folder to a group
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Carol" has been added to group "grp1"
    And user "Alice" has created folder "/PARENT"
    And user "Alice" has uploaded file with content "file in parent folder" to "/PARENT/parent.txt"
    When user "Alice" shares folder "/PARENT" with group "grp1" using the sharing API
    Then user "Brian" should see the following elements
      | /PARENT/           |
      | /PARENT/parent.txt |
    And the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And user "Carol" should see the following elements
      | /PARENT/           |
      | /PARENT/parent.txt |
    And the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-34 @issue-ocis-reva-243
  Scenario Outline: sharing again an own file while belonging to a group
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Brian" has uploaded file with content "ownCloud test text file 0" to "/textfile0.txt"
    And user "Brian" has shared file "textfile0.txt" with group "grp1"
    And user "Brian" has deleted the last share
    When user "Brian" shares file "/textfile0.txt" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-21 @issue-ocis-reva-243
  Scenario Outline: sharing subfolder of already shared folder, GET result is correct
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
      | David    |
      | Emily    |
    And user "Alice" has created folder "/folder1"
    And user "Alice" has shared folder "/folder1" with user "Brian"
    And user "Alice" has shared folder "/folder1" with user "Carol"
    And user "Alice" has created folder "/folder1/folder2"
    And user "Alice" has shared folder "/folder1/folder2" with user "David"
    And user "Alice" has shared folder "/folder1/folder2" with user "Emily"
    When user "Alice" sends HTTP method "GET" to OCS API endpoint "/apps/files_sharing/api/v1/shares"
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the response should contain 4 entries
    And folder "/folder1" should be included as path in the response
    And folder "/folder1/folder2" should be included as path in the response
    And user "Alice" sends HTTP method "GET" to OCS API endpoint "/apps/files_sharing/api/v1/shares?path=/folder1/folder2"
    And the response should contain 2 entries
    And folder "/folder1" should not be included as path in the response
    And folder "/folder1/folder2" should be included as path in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-14 @issue-ocis-reva-243
  Scenario Outline: user shares a file with file name longer than 64 chars to another user
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has moved file "textfile0.txt" to "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog.txt"
    When user "Alice" shares file "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog.txt" with user "Brian" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And as "Brian" file "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog.txt" should exist
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-21 @issue-ocis-reva-243
  Scenario Outline: user shares a file with file name longer than 64 chars to a group
    Given using OCS API version "<ocs_api_version>"
    And group "grp1" has been created
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Brian" has been added to group "grp1"
    And user "Alice" has moved file "textfile0.txt" to "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog.txt"
    When user "Alice" shares file "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog.txt" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And as "Brian" file "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog.txt" should exist
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-14 @issue-ocis-reva-243
  Scenario Outline: user shares a folder with folder name longer than 64 chars to another user
    Given using OCS API version "<ocs_api_version>"
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog"
    And user "Alice" has moved file "textfile0.txt" to "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog/textfile0.txt"
    When user "Alice" shares folder "/aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog" with user "Brian" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the downloaded content when downloading file "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog/textfile0.txt" for user "Brian" with range "bytes=1-6" should be "wnClou"
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-21 @issue-ocis-reva-243
  Scenario Outline: user shares a folder with folder name longer than 64 chars to a group
    Given using OCS API version "<ocs_api_version>"
    And group "grp1" has been created
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Brian" has been added to group "grp1"
    And user "Alice" has created folder "/aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog"
    And user "Alice" has moved file "textfile0.txt" to "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog/textfile0.txt"
    When user "Alice" shares folder "/aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the downloaded content when downloading file "aquickbrownfoxjumpsoveraverylazydogaquickbrownfoxjumpsoveralazydog/textfile0.txt" for user "Brian" with range "bytes=1-6" should be "wnClou"
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @issue-35484
  @skipOnOcis @issue-ocis-reva-11
  Scenario: share with user when username contains capital letters
    Given these users have been created without skeleton files:
      | username |
      | brian    |
    And user "Alice" has uploaded file with content "Random data" to "/randomfile.txt"
    When user "Alice" shares file "/randomfile.txt" with user "BRIAN" using the sharing API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "BRIAN" should include
      | share_with  | %username%        |
      | file_target | /randomfile.txt   |
      | path        | /randomfile.txt   |
      | permissions | share,read,update |
      | uid_owner   | %username%        |
    #And user "brian" should see the following elements
    #  | /randomfile.txt |
    #And the content of file "randomfile.txt" for user "brian" should be "Random data"
    And user "brian" should not see the following elements if the upper and lower case username are different
      | /randomfile.txt |

  @skipOnLDAP
  Scenario: creating a new share with user of a group when username contains capital letters
    Given these users have been created without skeleton files:
      | username |
      | Brian    |
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Alice" has uploaded file with content "Random data" to "/randomfile.txt"
    And user "Alice" has shared file "randomfile.txt" with group "grp1"
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "Brian" should see the following elements
      | /randomfile.txt |
    And the content of file "randomfile.txt" for user "Brian" should be "Random data"

  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: Share of folder to a group with emoji in the name
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And group "😀 😁" has been created
    And user "Brian" has been added to group "😀 😁"
    And user "Carol" has been added to group "😀 😁"
    And user "Alice" has created folder "/PARENT"
    And user "Alice" has uploaded file with content "file in parent folder" to "/PARENT/parent.txt"
    When user "Alice" shares folder "/PARENT" with group "😀 😁" using the sharing API
    Then user "Brian" should see the following elements
      | /PARENT/           |
      | /PARENT/parent.txt |
    And the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And user "Carol" should see the following elements
      | /PARENT/           |
      | /PARENT/parent.txt |
    And the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnEncryptionType:user-keys @encryption-issue-132 @skipOnLDAP
  Scenario Outline: share with a group and then add a user to that group
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And these groups have been created:
      | groupname |
      | grp1      |
    And user "Brian" has been added to group "grp1"
    And user "Alice" has uploaded file with content "some content" to "lorem.txt"
    When user "Alice" shares file "lorem.txt" with group "grp1" using the sharing API
    And the administrator adds user "Carol" to group "grp1" using the provisioning API
    Then the content of file "lorem.txt" for user "Brian" should be "some content"
    And the content of file "lorem.txt" for user "Carol" should be "some content"
    Examples:
      | ocs_api_version |
      | 1               |
      | 2               |

  @skipOnLDAP
  # deleting an LDAP group is not relevant or possible using the provisioning API
  Scenario Outline: shares shared to deleted group should not be available
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Carol" has been added to group "grp1"
    And user "Alice" has shared file "/textfile0.txt" with group "grp1"
    When user "Alice" sends HTTP method "GET" to OCS API endpoint "/apps/files_sharing/api/v1/shares"
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | share_with  | grp1           |
      | file_target | /textfile0.txt |
      | path        | /textfile0.txt |
      | uid_owner   | %username%     |
    And as "Brian" file "/textfile0.txt" should exist
    And as "Carol" file "/textfile0.txt" should exist
    When the administrator deletes group "grp1" using the provisioning API
    When user "Alice" sends HTTP method "GET" to OCS API endpoint "/apps/files_sharing/api/v1/shares"
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And file "/textfile0.txt" should not be included as path in the response
    And as "Brian" file "/textfile0.txt" should not exist
    And as "Carol" file "/textfile0.txt" should not exist
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-21 @skipOnFilesClassifier @issue-files-classifier-291 @issue-ocis-reva-243
  Scenario: Share a file by multiple channels and download from sub-folder and direct file share
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Carol" has been added to group "grp1"
    And user "Alice" has created folder "/common"
    And user "Alice" has created folder "/common/sub"
    And user "Alice" has shared folder "common" with group "grp1"
    And user "Brian" has uploaded file with content "ownCloud" to "/textfile0.txt"
    And user "Brian" has shared file "textfile0.txt" with user "Carol"
    And user "Brian" has moved file "/textfile0.txt" to "/common/textfile0.txt"
    And user "Brian" has moved file "/common/textfile0.txt" to "/common/sub/textfile0.txt"
    When user "Carol" uploads file "filesForUpload/file_to_overwrite.txt" to "/textfile0.txt" using the WebDAV API
    And the content of file "/common/sub/textfile0.txt" for user "Carol" should be "BLABLABLA" plus end-of-line
    And the content of file "/textfile0.txt" for user "Carol" should be "BLABLABLA" plus end-of-line
    And user "Carol" should see the following elements
      | /common/sub/textfile0.txt |
      | /textfile0.txt            |

  @skipOnOcis @issue-enterprise-3896 @issue-ocis-reva-243
  Scenario: sharing back to resharer is allowed
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And user "Alice" has created folder "userZeroFolder"
    And user "Alice" has shared folder "userZeroFolder" with user "Brian"
    And user "Brian" has created folder "userZeroFolder/userOneFolder"
    When user "Brian" shares folder "userZeroFolder/userOneFolder" with user "Carol" with permissions "read, share" using the sharing API
    And user "Carol" shares folder "userOneFolder" with user "Brian" using the sharing API
    Then the HTTP status code should be "200"
#    Then the HTTP status code should be "405"
    And as "Brian" folder "userOneFolder" should not exist

  @skipOnOcis @issue-enterprise-3896 @issue-ocis-reva-243
  Scenario: sharing back to original sharer is allowed
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
    And user "Alice" has created folder "userZeroFolder"
    And user "Alice" has shared folder "userZeroFolder" with user "Brian"
    And user "Brian" has created folder "userZeroFolder/userOneFolder"
    When user "Brian" shares folder "userZeroFolder/userOneFolder" with user "Carol" with permissions "read, share" using the sharing API
    And user "Carol" shares folder "userOneFolder" with user "Alice" using the sharing API
    Then the HTTP status code should be "200"
#    Then the HTTP status code should be "405"
    And as "Alice" folder "userOneFolder" should not exist

  @skipOnOcis @issue-enterprise-3896 @issue-ocis-reva-243
  Scenario: sharing a subfolder to a user that already received parent folder share
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Brian    |
      | Carol    |
      | David    |
    And user "Alice" has created folder "userZeroFolder"
    And user "Alice" has shared folder "userZeroFolder" with user "Brian"
    And user "Alice" has shared folder "userZeroFolder" with user "Carol"
    And user "Brian" has created folder "userZeroFolder/userOneFolder"
    When user "Brian" shares folder "userZeroFolder/userOneFolder" with user "David" with permissions "read, share" using the sharing API
    And user "David" shares folder "userOneFolder" with user "Carol" using the sharing API
    Then the HTTP status code should be "200"
#    Then the HTTP status code should be "405"
    And as "Carol" folder "userOneFolder" should not exist
