@api @TestAlsoOnExternalUserBackend @files_sharing-app-required @public_link_share-feature-required @skipOnOcis @issue-ocis-reva-49
@issue-ocis-reva-233 @issue-ocis-reva-243
Feature: reshare as public link
  As a user
  I want to create public link shares from files/folders shared with me
  So that I can give controlled access to others

  Background:
    Given user "Alice" has been created with default attributes and skeleton files
    And user "Brian" has been created with default attributes and without skeleton files

  Scenario Outline: creating a public link from a share with read permission only is not allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "read"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test |
      | publicUpload | false |
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |

  Scenario Outline: creating a public link from a share with share+read only permissions is allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has uploaded file with content "some content" to "/test/file.txt"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "share,read"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test |
      | publicUpload | false |
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the public should be able to download file "file.txt" from inside the last public shared folder using the old public WebDAV API and the content should be "some content"
    And the public should be able to download file "file.txt" from inside the last public shared folder using the new public WebDAV API and the content should be "some content"
    But uploading a file should not work using the old public WebDAV API
    And uploading a file should not work using the new public WebDAV API
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  Scenario Outline: creating an upload public link from a share with share+read only permissions is not allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "share,read"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test                     |
      | permissions  | read,update,create,delete |
      | publicUpload | true                      |
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |

  Scenario Outline: creating a public link from a share with read+write permissions only is not allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "change"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test |
      | publicUpload | true  |
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |

  Scenario Outline: creating a public link from a share with share+read+write permissions is allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has uploaded file with content "some content" to "/test/file.txt"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "all"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test |
      | publicUpload | false |
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the public should be able to download file "file.txt" from inside the last public shared folder using the old public WebDAV API and the content should be "some content"
    And the public should be able to download file "file.txt" from inside the last public shared folder using the new public WebDAV API and the content should be "some content"
    But uploading a file should not work using the old public WebDAV API
    And uploading a file should not work using the new public WebDAV API
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  Scenario Outline: creating an upload public link from a share with share+read+write permissions is allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has uploaded file with content "some content" to "/test/file.txt"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "all"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test                     |
      | permissions  | read,update,create,delete |
      | publicUpload | true                      |
    Then the OCS status code should be "<ocs_status_code>"
    And the HTTP status code should be "200"
    And the public should be able to download file "file.txt" from inside the last public shared folder using the old public WebDAV API and the content should be "some content"
    And the public should be able to download file "file.txt" from inside the last public shared folder using the new public WebDAV API and the content should be "some content"
    And uploading a file should work using the old public WebDAV API
    And uploading a file should work using the new public WebDAV API
    Examples:
      | ocs_api_version | ocs_status_code |
      | 1               | 100             |
      | 2               | 200             |

  Scenario Outline: creating an upload public link from a sub-folder of a share with share+read only permissions is not allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has created folder "/test/sub"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "share,read"
    When user "Brian" creates a public link share using the sharing API with settings
      | path         | /test/sub                 |
      | permissions  | read,update,create,delete |
      | publicUpload | true                      |
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |

  Scenario Outline: increasing permissions of a public link of a share with share+read only permissions is not allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has created folder "/test/sub"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "share,read"
    And user "Brian" has created a public link share with settings
      | path         | /test |
      | permissions  | read  |
      | publicUpload | false |
    When user "Brian" updates the last share using the sharing API with
      | permissions | read,update,create,delete |
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    And uploading a file should not work using the old public WebDAV API
    And uploading a file should not work using the new public WebDAV API
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |

  Scenario Outline: increasing permissions of a public link from a sub-folder of a share with share+read only permissions is not allowed
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has created folder "/test"
    And user "Alice" has created folder "/test/sub"
    And user "Alice" has shared folder "/test" with user "Brian" with permissions "share,read"
    And user "Brian" has created a public link share with settings
      | path         | /test/sub |
      | permissions  | read      |
      | publicUpload | false     |
    And uploading a file should not work using the old public WebDAV API
    And uploading a file should not work using the new public WebDAV API
    When user "Brian" updates the last share using the sharing API with
      | permissions | read,update,create,delete |
    Then the OCS status code should be "404"
    And the HTTP status code should be "<http_status_code>"
    And uploading a file should not work using the old public WebDAV API
    And uploading a file should not work using the new public WebDAV API
    Examples:
      | ocs_api_version | http_status_code |
      | 1               | 200              |
      | 2               | 404              |
