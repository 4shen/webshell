@api @TestAlsoOnExternalUserBackend @files_sharing-app-required @skipOnOcis @issue-ocis-reva-11 @issue-ocis-reva-243
Feature: sharing

  Background:
    Given user "Alice" has been created with default attributes and skeleton files

  Scenario: Uploading file to a user read-only share folder does not work
    Given user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | user   |
      | permissions | read   |
      | shareWith   | Brian  |
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/textfile.txt" using the WebDAV API
    Then the HTTP status code should be "403"

  Scenario Outline: Uploading file to a group read-only share folder does not work
    Given using <dav-path> DAV path
    Given user "Brian" has been created with default attributes and skeleton files
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | group  |
      | permissions | read   |
      | shareWith   | grp1   |
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/textfile.txt" using the WebDAV API
    Then the HTTP status code should be "403"
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading file to a user upload-only share folder works
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | user   |
      | permissions | create |
      | shareWith   | Brian  |
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/textfile.txt" using the WebDAV API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Brian"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading file to a group upload-only share folder works
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | group  |
      | permissions | create |
      | shareWith   | grp1   |
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/textfile.txt" using the WebDAV API
    Then the HTTP status code should be "201"
    And the following headers should match these regular expressions for user "Brian"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: Uploading file to a user read/write share folder works
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | user   |
      | permissions | change |
      | shareWith   | Brian  |
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/textfile.txt" using the WebDAV API
    Then the HTTP status code should be "201"
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading file to a group read/write share folder works
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | group  |
      | permissions | change |
      | shareWith   | grp1   |
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/textfile.txt" using the WebDAV API
    Then the HTTP status code should be "201"
    Examples:
      | dav-path |
      | old      |
      | new      |

  @smokeTest
  Scenario Outline: Check quota of owners parent directory of a shared file
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And the quota of user "Brian" has been set to "0"
    And user "Alice" has moved file "/welcome.txt" to "/myfile.txt"
    And user "Alice" has shared file "myfile.txt" with user "Brian"
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "/myfile.txt" using the WebDAV API
    Then the HTTP status code should be "204"
    And the following headers should match these regular expressions for user "Brian"
      | ETag | /^"[a-f0-9:]{1,32}"$/ |
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading to a user shared folder with read/write permission when the sharer has unsufficient quota does not work
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | user   |
      | permissions | change |
      | shareWith   | Brian  |
    And the quota of user "Alice" has been set to "0"
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/myfile.txt" using the WebDAV API
    Then the HTTP status code should be "507"
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading to a group shared folder with read/write permission when the sharer has unsufficient quota does not work
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | group  |
      | permissions | change |
      | shareWith   | grp1   |
    And the quota of user "Alice" has been set to "0"
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/myfile.txt" using the WebDAV API
    Then the HTTP status code should be "507"
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading to a user shared folder with upload-only permission when the sharer has insufficient quota does not work
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | user   |
      | permissions | create |
      | shareWith   | Brian  |
    And the quota of user "Alice" has been set to "0"
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/myfile.txt" using the WebDAV API
    Then the HTTP status code should be "507"
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: Uploading to a group shared folder with upload-only permission when the sharer has insufficient quota does not work
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And group "grp1" has been created
    And user "Brian" has been added to group "grp1"
    And user "Alice" has created a share with settings
      | path        | FOLDER |
      | shareType   | group  |
      | permissions | create |
      | shareWith   | grp1   |
    And the quota of user "Alice" has been set to "0"
    When user "Brian" uploads file "filesForUpload/textfile.txt" to "FOLDER (2)/myfile.txt" using the WebDAV API
    Then the HTTP status code should be "507"
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario: Uploading a file in to a shared folder without edit permissions
    Given using new DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" creates folder "/READ_ONLY" using the WebDAV API
    And user "Alice" has created a share with settings
      | path        | /READ_ONLY |
      | shareType   | user       |
      | permissions | read       |
      | shareWith   | Brian      |
    When user "Brian" uploads the following chunks to "/READ_ONLY/myfile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 1      | hallo   |
      | 2      | welt    |
    Then the HTTP status code should be "403"
