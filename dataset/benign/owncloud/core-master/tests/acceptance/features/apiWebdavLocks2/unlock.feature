@api @TestAlsoOnExternalUserBackend @skipOnOcV10.0 @skipOnOcis @issue-ocis-reva-172
Feature: UNLOCK locked items

  Background:
    Given user "Alice" has been created with default attributes and skeleton files

  @smokeTest
  Scenario Outline: unlock a single lock set by the user itself
    Given using <dav-path> DAV path
    And user "Alice" has locked folder "PARENT" setting following properties
      | lockscope | <lock-scope> |
    When user "Alice" unlocks the last created lock of folder "PARENT" using the WebDAV API
    Then 0 locks should be reported for folder "PARENT" of user "Alice" by the WebDAV API
    And 0 locks should be reported for folder "PARENT/CHILD" of user "Alice" by the WebDAV API
    And 0 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  Scenario Outline: unlock one of multiple locks set by the user itself
    Given using <dav-path> DAV path
    And user "Alice" has locked file "textfile0.txt" setting following properties
      | lockscope | shared |
    And user "Alice" has locked file "textfile0.txt" setting following properties
      | lockscope | shared |
    When user "Alice" unlocks the last created lock of file "textfile0.txt" using the WebDAV API
    Then 1 locks should be reported for file "textfile0.txt" of user "Alice" by the WebDAV API
    Examples:
      | dav-path |
      | old      |
      | new      |

  Scenario Outline: unlocking a file that was locked by the user locking the folder above is not possible
    Given using <dav-path> DAV path
    And user "Alice" has locked folder "PARENT/CHILD" setting following properties
      | lockscope | <lock-scope> |
    When user "Alice" unlocks file "PARENT/CHILD/child.txt" with the last created lock of folder "PARENT/CHILD" using the WebDAV API
    Then 1 locks should be reported for file "PARENT/CHILD/child.txt" of user "Alice" by the WebDAV API
    And 2 locks should be reported for folder "PARENT/CHILD" of user "Alice" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as share receiver unlocking a shared file locked by the file owner is not possible. To unlock use the owners locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has locked file "PARENT/parent.txt" setting following properties
      | lockscope | <lock-scope> |
    And user "Alice" has shared file "PARENT/parent.txt" with user "Brian"
    When user "Brian" unlocks file "parent.txt" with the last created lock of file "PARENT/parent.txt" of user "Alice" using the WebDAV API
    Then the HTTP status code should be "403"
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as share receiver unlocking a file in a share locked by the file owner is not possible. To unlock use the owners locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has locked file "PARENT/parent.txt" setting following properties
      | lockscope | <lock-scope> |
    And user "Alice" has shared folder "PARENT" with user "Brian"
    When user "Brian" unlocks file "PARENT (2)/parent.txt" with the last created lock of file "PARENT/parent.txt" of user "Alice" using the WebDAV API
    Then the HTTP status code should be "403"
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "PARENT (2)/parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as share receiver unlocking a shared folder locked by the file owner is not possible. To unlock use the owners locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has locked folder "PARENT" setting following properties
      | lockscope | <lock-scope> |
    And user "Alice" has shared folder "PARENT" with user "Brian"
    When user "Brian" unlocks folder "PARENT (2)" with the last created lock of folder "PARENT" of user "Alice" using the WebDAV API
    Then the HTTP status code should be "403"
    And 3 locks should be reported for folder "PARENT" of user "Alice" by the WebDAV API
    And 2 locks should be reported for folder "PARENT/CHILD" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 3 locks should be reported for folder "PARENT (2)" of user "Brian" by the WebDAV API
    And 2 locks should be reported for folder "PARENT (2)/CHILD" of user "Brian" by the WebDAV API
    And 1 locks should be reported for file "PARENT (2)/parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as share receiver unlocking a shared file locked by the file owner is not possible. To unlock use the owners locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has locked file "PARENT/parent.txt" setting following properties
      | lockscope | <lock-scope> |
    And user "Alice" has shared file "PARENT/parent.txt" with user "Brian"
    When user "Brian" unlocks file "parent.txt" with the last created lock of file "PARENT/parent.txt" of user "Alice" using the WebDAV API
    Then the HTTP status code should be "403"
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as share receiver unlock a shared file
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has shared file "PARENT/parent.txt" with user "Brian"
    And user "Brian" has locked file "parent.txt" setting following properties
      | lockscope | <lock-scope> |
    When user "Brian" unlocks the last created lock of file "parent.txt" using the WebDAV API
    Then the HTTP status code should be "204"
    And 0 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 0 locks should be reported for file "parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as owner unlocking a shared file locked by the receiver is not possible. To unlock use the receivers locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has shared file "PARENT/parent.txt" with user "Brian"
    And user "Brian" has locked file "parent.txt" setting following properties
      | lockscope | <lock-scope> |
    When user "Alice" unlocks file "PARENT/parent.txt" with the last created lock of file "parent.txt" of user "Brian" using the WebDAV API
    Then the HTTP status code should be "403"
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as owner unlocking a file in a share that was locked by the share receiver is not possible. To unlock use the receivers locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has shared folder "PARENT" with user "Brian"
    And user "Brian" has locked file "PARENT (2)/parent.txt" setting following properties
      | lockscope | <lock-scope> |
    When user "Alice" unlocks file "PARENT/parent.txt" with the last created lock of file "PARENT (2)/parent.txt" of user "Brian" using the WebDAV API
    Then the HTTP status code should be "403"
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "PARENT (2)/parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @files_sharing-app-required
  Scenario Outline: as owner unlocking a shared folder locked by the share receiver is not possible. To unlock use the receivers locktoken
    Given using <dav-path> DAV path
    And user "Brian" has been created with default attributes and skeleton files
    And user "Alice" has shared folder "PARENT" with user "Brian"
    And user "Brian" has locked folder "PARENT (2)" setting following properties
      | lockscope | <lock-scope> |
    When user "Alice" unlocks folder "PARENT" with the last created lock of folder "PARENT (2)" of user "Brian" using the WebDAV API
    Then the HTTP status code should be "403"
    And 3 locks should be reported for folder "PARENT" of user "Alice" by the WebDAV API
    And 2 locks should be reported for folder "PARENT/CHILD" of user "Alice" by the WebDAV API
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    And 3 locks should be reported for folder "PARENT (2)" of user "Brian" by the WebDAV API
    And 2 locks should be reported for folder "PARENT (2)/CHILD" of user "Brian" by the WebDAV API
    And 1 locks should be reported for file "PARENT (2)/parent.txt" of user "Brian" by the WebDAV API
    Examples:
      | dav-path | lock-scope |
      | old      | shared     |
      | old      | exclusive  |
      | new      | shared     |
      | new      | exclusive  |

  @issue-34302 @files_sharing-app-required @skipOnOcV10.3
  Scenario Outline: as public unlocking a file in a share that was locked by the file owner is not possible. To unlock use the owners locktoken
    Given user "Alice" has created a public link share of folder "PARENT" with change permission
    And user "Alice" has locked file "PARENT/parent.txt" setting following properties
      | lockscope | <lock-scope> |
    When the public unlocks file "/parent.txt" with the last created lock of file "PARENT/parent.txt" of user "Alice" using the WebDAV API
    Then the HTTP status code should be "405"
    #Then the HTTP status code should be "403"
    And 1 locks should be reported for file "PARENT/parent.txt" of user "Alice" by the WebDAV API
    Examples:
      | lock-scope |
      | shared     |
      | exclusive  |
