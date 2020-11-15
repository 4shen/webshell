@api @TestAlsoOnExternalUserBackend @skipOnOcis @issue-ocis-reva-56
Feature: upload file using new chunking
  As a user
  I want to be able to upload "large" files in chunks asynchronously
  So that I do not have to wait for the long MOVE operation on assembly to finish

  Background:
    Given using new DAV path
    And user "Alice" has been created with default attributes and skeleton files
    And the owncloud log level has been set to debug
    And the owncloud log has been cleared
    And the administrator has enabled async operations

  Scenario: Upload chunked file ordered asc using async MOVE
    When user "Alice" uploads the following chunks asynchronously to "/myChunkedFile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 1      | AAAAA   |
      | 2      | BBBBB   |
      | 3      | CCCCC   |
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status | /^finished$/      |
      | fileId | /^[0-9a-z]{20,}$/ |
    And the content of file "/myChunkedFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |

  Scenario: Upload chunked file ordered desc using async MOVE
    When user "Alice" uploads the following chunks asynchronously to "/myChunkedFile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 3      | CCCCC   |
      | 2      | BBBBB   |
      | 1      | AAAAA   |
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status | /^finished$/      |
      | fileId | /^[0-9a-z]{20,}$/ |
    And the content of file "/myChunkedFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |

  Scenario: Upload chunked file in random order using async MOVE
    When user "Alice" uploads the following chunks asynchronously to "/myChunkedFile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 2      | BBBBB   |
      | 3      | CCCCC   |
      | 1      | AAAAA   |
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status | /^finished$/      |
      | fileId | /^[0-9a-z]{20,}$/ |
    And the content of file "/myChunkedFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |

  Scenario: Upload chunked file overwriting existing file using async MOVE
    Given user "Alice" has copied file "/textfile0.txt" to "/existingFile.txt"
    When user "Alice" uploads the following chunks asynchronously to "/existingFile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 1      | AAAAA   |
      | 2      | BBBBB   |
      | 3      | CCCCC   |
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status | /^finished$/      |
      | fileId | /^[0-9a-z]{20,}$/ |
    And the content of file "/existingFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |

  Scenario: New chunked upload MOVE using old DAV path should fail
    Given user "Alice" has created a new chunking upload with id "chunking-42"
    And user "Alice" has uploaded new chunk file "2" with "BBBBB" to id "chunking-42"
    And user "Alice" has uploaded new chunk file "3" with "CCCCC" to id "chunking-42"
    And user "Alice" has uploaded new chunk file "1" with "AAAAA" to id "chunking-42"
    When using old DAV path
    And user "Alice" moves new chunk file with id "chunking-42" asynchronously to "/myChunkedFile.txt" using the WebDAV API
    Then the HTTP status code should be "404"

  Scenario: Upload file via new chunking endpoint with wrong size header using async MOVE
    Given user "Alice" has created a new chunking upload with id "chunking-42"
    And user "Alice" has uploaded new chunk file "1" with "AAAAA" to id "chunking-42"
    And user "Alice" has uploaded new chunk file "2" with "BBBBB" to id "chunking-42"
    And user "Alice" has uploaded new chunk file "3" with "CCCCC" to id "chunking-42"
    When user "Alice" moves new chunk file with id "chunking-42" asynchronously to "/myChunkedFile.txt" with size 5 using the WebDAV API
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status       | /^error$/                                         |
      | errorCode    | /^400$/                                           |
      | errorMessage | /^Chunks on server do not sum up to 5 but to 15$/ |

  Scenario: Upload file via new chunking endpoint with correct size header using async MOVE
    Given user "Alice" has created a new chunking upload with id "chunking-42"
    And user "Alice" has uploaded new chunk file "1" with "AAAAA" to id "chunking-42"
    And user "Alice" has uploaded new chunk file "2" with "BBBBB" to id "chunking-42"
    And user "Alice" has uploaded new chunk file "3" with "CCCCC" to id "chunking-42"
    When user "Alice" moves new chunk file with id "chunking-42" asynchronously to "/myChunkedFile.txt" with size 15 using the WebDAV API
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status | /^finished$/      |
      | fileId | /^[0-9a-z]{20,}$/ |
    And as "Alice" file "/myChunkedFile.txt" should exist
    And the content of file "/myChunkedFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |

  Scenario Outline: Upload files with difficult names using new chunking and async MOVE
    When user "Alice" creates a new chunking upload with id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "1" with "AAAAA" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "2" with "BBBBB" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "3" with "CCCCC" to id "chunking-42" using the WebDAV API
    And user "Alice" moves new chunk file with id "chunking-42" asynchronously to "/<file-name>" using the WebDAV API
    Then the HTTP status code should be "202"
    And the following headers should match these regular expressions for user "Alice"
      | OC-JobStatus-Location | /%base_path%\/remote\.php\/dav\/job-status\/%username%\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/ |
    And the oc job status values of last request for user "Alice" should match these regular expressions
      | status | /^finished$/      |
      | fileId | /^[0-9a-z]{20,}$/ |
    And as "Alice" file "/<file-name>" should exist
    And the content of file "/<file-name>" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |
    Examples:
      | file-name |
      | &#?       |
      | TIÄFÜ     |

  Scenario: disabled async operations leads to original behavior
    Given the administrator has disabled async operations
    When user "Alice" uploads the following chunks asynchronously to "/myChunkedFile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 1      | AAAAA   |
      | 2      | BBBBB   |
      | 3      | CCCCC   |
    Then the HTTP status code should be "201"
    And the following headers should not be set
      | header                |
      | OC-JobStatus-Location |
    And the content of file "/myChunkedFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"

  Scenario: enabling async operations does no difference to normal MOVE - Upload chunked file
    When user "Alice" uploads the following chunks to "/myChunkedFile.txt" with new chunking and using the WebDAV API
      | number | content |
      | 1      | AAAAA   |
      | 2      | BBBBB   |
      | 3      | CCCCC   |
    Then the HTTP status code should be "201"
    And the following headers should not be set
      | header                |
      | OC-JobStatus-Location |
    And as "Alice" file "/myChunkedFile.txt" should exist
    And the content of file "/myChunkedFile.txt" for user "Alice" should be "AAAAABBBBBCCCCC"
    And the log file should not contain any log-entries containing these attributes:
      | app |
      | dav |
