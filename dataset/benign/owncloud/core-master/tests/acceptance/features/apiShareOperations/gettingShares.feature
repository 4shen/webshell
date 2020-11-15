@api @TestAlsoOnExternalUserBackend @files_sharing-app-required
Feature: sharing

  Background:
    Given these users have been created with default attributes and skeleton files:
      | username |
      | Alice    |
      | Brian    |

  @skipOnOcis @smokeTest @issue-ocis-reva-262
  Scenario Outline: getting all shares of a user using that user
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has moved file "/textfile0.txt" to "/file_to_share.txt"
    And user "Alice" has shared file "file_to_share.txt" with user "Brian"
    When user "Alice" gets all shares shared by him using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And file "file_to_share.txt" should be included in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-65
  Scenario Outline: getting all shares of a user using another user
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has shared file "textfile0.txt" with user "Brian"
    When the administrator gets all shares shared by him using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And file "textfile0.txt" should not be included in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @smokeTest
  Scenario Outline: getting all shares of a file
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and skeleton files:
      | username |
      | Carol    |
      | David    |
    And user "Alice" has shared file "textfile0.txt" with user "Brian"
    And user "Alice" has shared file "textfile0.txt" with user "Carol"
    When user "Alice" gets all the shares from the file "textfile0.txt" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And user "Brian" should be included in the response
    And user "Carol" should be included in the response
    And user "David" should not be included in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @smokeTest @issue-ocis-reva-243 @issue-ocis-reva-21
  Scenario Outline: getting all shares of a file with reshares
    Given using OCS API version "<ocs_api_version>"
    And these users have been created with default attributes and skeleton files:
      | username |
      | Carol    |
      | David    |
    And user "Alice" has shared file "textfile0.txt" with user "Brian"
    And user "Brian" has shared file "textfile0 (2).txt" with user "Carol"
    When user "Alice" gets all the shares with reshares from the file "textfile0.txt" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And user "Brian" should be included in the response
    And user "Carol" should be included in the response
    And user "David" should not be included in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @smokeTest @issue-ocis-reva-243 @issue-ocis-reva-194
  Scenario Outline: User's own shares reshared to him don't appear when getting "shared with me" shares
    Given using OCS API version "<ocs_api_version>"
    And group "grp1" has been created
    And user "Carol" has been created with default attributes and skeleton files
    And user "Carol" has been added to group "grp1"
    And user "Carol" has created folder "/shared"
    And user "Carol" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Carol" has shared folder "/shared" with user "Brian"
    And user "Brian" has shared folder "/shared" with group "grp1"
    When user "Carol" gets all the shares shared with him using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the last share_id should not be included in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @smokeTest @issue-ocis-reva-249
  Scenario Outline: getting share info of a share
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has moved file "/textfile0.txt" to "/file_to_share.txt"
    And user "Alice" has shared file "file_to_share.txt" with user "Brian"
    When user "Alice" gets the info of the last share using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the fields of the last response to user "Alice" sharing with user "Brian" should include
      | id                     | A_NUMBER           |
      | item_type              | file               |
      | item_source            | A_NUMBER           |
      | share_type             | user               |
      | share_with             | %username%         |
      | file_source            | A_NUMBER           |
      | file_target            | /file_to_share.txt |
      | path                   | /file_to_share.txt |
      | permissions            | share,read,update  |
      | stime                  | A_NUMBER           |
      | storage                | A_NUMBER           |
      | mail_send              | 0                  |
      | uid_owner              | %username%         |
      | file_parent            | A_NUMBER           |
      | share_with_displayname | %displayname%      |
      | displayname_owner      | %displayname%      |
      | mimetype               | text/plain         |
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: Get a share with a user that didn't receive the share
    Given using OCS API version "<ocs_api_version>"
    And user "Carol" has been created with default attributes and without skeleton files
    And user "Alice" has shared file "textfile0.txt" with user "Brian"
    When user "Carol" gets the info of the last share using the sharing API
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |

  @skipOnLDAP @skipOnOcis @issue-ocis-reva-194
  Scenario: Share of folder to a group, remove user from that group
    Given using OCS API version "1"
    And user "Carol" has been created with default attributes and skeleton files
    And group "group0" has been created
    And user "Brian" has been added to group "group0"
    And user "Carol" has been added to group "group0"
    And user "Alice" has shared folder "/PARENT" with group "group0"
    When the administrator removes user "Carol" from group "group0" using the provisioning API
    Then user "Brian" should see the following elements
      | /FOLDER/                 |
      | /PARENT/                 |
      | /PARENT/parent.txt       |
      | /PARENT%20(2)/           |
      | /PARENT%20(2)/parent.txt |
    And user "Carol" should see the following elements
      | /FOLDER/           |
      | /PARENT/           |
      | /PARENT/parent.txt |
    But user "Carol" should not see the following elements
      | /PARENT%20(2)/           |
      | /PARENT%20(2)/parent.txt |

  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: getting all the shares inside the folder
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has shared file "PARENT/parent.txt" with user "Brian"
    When user "Alice" gets all the shares inside the folder "PARENT" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And file "parent.txt" should be included in the response
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |
