@api @TestAlsoOnExternalUserBackend @files_sharing-app-required @skipOnOcis @issue-ocis-reva-41 @issue-ocis-reva-243
Feature: cannot share resources outside the group when share with membership groups is enabled

  Background:
    Given user "Alice" has been created with default attributes and skeleton files

  Scenario Outline: sharer should not be able to share a folder to a group which he/she is not member of when share with only member group is enabled
    Given using OCS API version "<ocs_api_version>"
    And parameter "shareapi_only_share_with_membership_groups" of app "core" has been set to "yes"
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp0" has been created
    And group "grp1" has been created
    And user "Alice" has been added to group "grp0"
    And user "Brian" has been added to group "grp1"
    When user "Alice" shares folder "/PARENT" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" folder "/PARENT (2)" should not exist
    Examples:
      | ocs_api_version | ocs_status_code | http_status_code |
      | 1               | 403             | 200              |
      | 2               | 403             | 403              |

  Scenario Outline: sharer should be able to share a folder to a user who is not member of sharer group when share with only member group is enabled
    Given using OCS API version "<ocs_api_version>"
    And parameter "shareapi_only_share_with_membership_groups" of app "core" has been set to "yes"
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp0" has been created
    And user "Alice" has been added to group "grp0"
    When user "Alice" shares folder "/PARENT" with user "Brian" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" folder "/PARENT (2)" should exist
    Examples:
      | ocs_api_version | ocs_status_code | http_status_code |
      | 1               | 100             | 200              |
      | 2               | 200             | 200              |

  Scenario Outline: sharer should be able to share a folder to a group which he/she is member of when share with only member group is enabled
    Given using OCS API version "<ocs_api_version>"
    And parameter "shareapi_only_share_with_membership_groups" of app "core" has been set to "yes"
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp0" has been created
    And user "Alice" has been added to group "grp0"
    And user "Brian" has been added to group "grp0"
    When user "Alice" shares folder "/PARENT" with group "grp0" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" folder "/PARENT (2)" should exist
    Examples:
      | ocs_api_version | ocs_status_code | http_status_code |
      | 1               | 100             | 200              |
      | 2               | 200             | 200              |

  Scenario Outline: sharer should not be able to share a file to a group which he/she is not member of when share with only member group is enabled
    Given using OCS API version "<ocs_api_version>"
    And parameter "shareapi_only_share_with_membership_groups" of app "core" has been set to "yes"
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp0" has been created
    And group "grp1" has been created
    And user "Alice" has been added to group "grp0"
    And user "Brian" has been added to group "grp1"
    When user "Alice" shares file "/textfile0.txt" with group "grp1" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" file "/textfile0 (2).txt" should not exist
    Examples:
      | ocs_api_version | ocs_status_code | http_status_code |
      | 1               | 403             | 200              |
      | 2               | 403             | 403              |

  Scenario Outline: sharer should be able to share a file to a group which he/she is member of when share with only member group is enabled
    Given using OCS API version "<ocs_api_version>"
    And parameter "shareapi_only_share_with_membership_groups" of app "core" has been set to "yes"
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp0" has been created
    And user "Alice" has been added to group "grp0"
    And user "Brian" has been added to group "grp0"
    When user "Alice" shares folder "/textfile0.txt" with group "grp0" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" file "/textfile0 (2).txt" should exist
    Examples:
      | ocs_api_version | ocs_status_code | http_status_code |
      | 1               | 100             | 200              |
      | 2               | 200             | 200              |

  Scenario Outline: sharer should be able to share a file to a user who is not a member of sharer group when share with only member group is enabled
    Given using OCS API version "<ocs_api_version>"
    And parameter "shareapi_only_share_with_membership_groups" of app "core" has been set to "yes"
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp0" has been created
    And user "Alice" has been added to group "grp0"
    When user "Alice" shares folder "/textfile0.txt" with user "Brian" using the sharing API
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "<http_status_code>"
    And as "Brian" file "/textfile0 (2).txt" should exist
    Examples:
      | ocs_api_version | ocs_status_code | http_status_code |
      | 1               | 100             | 200              |
      | 2               | 200             | 200              |
