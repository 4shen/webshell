@api @TestAlsoOnExternalUserBackend @skipOnOcis @issue-ocis-reva-56
Feature: users cannot upload a file to or into an excluded directory using new chunking
  As an administrator
  I want to be able to exclude directories (folders) from being processed. Any attempt to upload a file to one of those names should be refused.
  So that I can have directories on my cloud server storage that are not available for syncing.

  Background:
    Given using new DAV path
    And user "Alice" has been created with default attributes and skeleton files
    And the owncloud log level has been set to debug
    And the owncloud log has been cleared
    And the administrator has enabled async operations

  Scenario: Upload to an excluded directory name using new chunking and async MOVE
    When the administrator updates system config key "excluded_directories" with value '[".github"]' and type "json" using the occ command
    And user "Alice" creates a new chunking upload with id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "1" with "AAAAA" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "2" with "BBBBB" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "3" with "CCCCC" to id "chunking-42" using the WebDAV API
    And user "Alice" moves new chunk file with id "chunking-42" asynchronously to "/.github" using the WebDAV API
    Then the HTTP status code should be "403"
    And as "Alice" file "/.github" should not exist

  Scenario: Upload to an excluded directory name inside a parent directory using new chunking and async MOVE
    When the administrator updates system config key "excluded_directories" with value '[".github"]' and type "json" using the occ command
    And user "Alice" creates a new chunking upload with id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "1" with "AAAAA" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "2" with "BBBBB" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "3" with "CCCCC" to id "chunking-42" using the WebDAV API
    And user "Alice" moves new chunk file with id "chunking-42" asynchronously to "/FOLDER/.github" using the WebDAV API
    Then the HTTP status code should be "403"
    And as "Alice" folder "/FOLDER" should exist
    But as "Alice" file "/FOLDER/.github" should not exist

  @skipOnOcV10.3
  Scenario Outline: upload a file to a filename that matches excluded_directories_regex using new chunking and async MOVE
    # Note: we have to write JSON for the value, and to get a backslash in the double-quotes we have to escape it
    # The actual regular expressions end up being endswith\.bad$ and ^\.git
    Given the administrator has updated system config key "excluded_directories_regex" with value '["endswith\\.bad$","^\\.git","containsvirusinthename"]' and type "json"
    When user "Alice" creates a new chunking upload with id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "1" with "AAAAA" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "2" with "BBBBB" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "3" with "CCCCC" to id "chunking-42" using the WebDAV API
    And user "Alice" moves new chunk file with id "chunking-42" asynchronously to "<filename>" using the WebDAV API
    Then the HTTP status code should be "403"
    And as "Alice" file "<filename>" should not exist
    Examples:
      | filename                        |
      | thisendswith.bad                |
      | .github                         |
      | this-containsvirusinthename.txt |

  @skipOnOcV10.3
  Scenario: upload a file to a filename that does not match excluded_directories_regex using new chunking and async MOVE
    # Note: we have to write JSON for the value, and to get a backslash in the double-quotes we have to escape it
    # The actual regular expressions end up being endswith\.bad$ and ^\.git
    Given the administrator has updated system config key "excluded_directories_regex" with value '["endswith\\.bad$","^\\.git","containsvirusinthename"]' and type "json"
    When user "Alice" creates a new chunking upload with id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "1" with "AAAAA" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "2" with "BBBBB" to id "chunking-42" using the WebDAV API
    And user "Alice" uploads new chunk file "3" with "CCCCC" to id "chunking-42" using the WebDAV API
    And user "Alice" moves new chunk file with id "chunking-42" asynchronously to "not-contains-virus-in-the-name.txt" using the WebDAV API
    Then the HTTP status code should be "202"
    And as "Alice" file "not-contains-virus-in-the-name.txt" should exist
