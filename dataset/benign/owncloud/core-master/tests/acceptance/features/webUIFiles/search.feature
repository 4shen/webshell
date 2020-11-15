@webUI @insulated @disablePreviews
Feature: Search

  As a user
  I would like to be able to search for files
  So that I can find needed files quickly

  Background:
    Given user "Alice" has been created with default attributes and skeleton files
    And user "Alice" has logged in using the webUI
    And the user has browsed to the files page

  @smokeTest @TestAlsoOnExternalUserBackend
  Scenario: Simple search
    When the user searches for "lorem" using the webUI
    Then file "lorem.txt" should be listed on the webUI
    And file "lorem-big.txt" should be listed on the webUI
    And file "lorem.txt" with path "/simple-folder" should be listed in the search results in the other folders section on the webUI
    And file "lorem-big.txt" with path "/simple-folder" should be listed in the search results in the other folders section on the webUI
    And file "lorem.txt" with path "/0" should be listed in the search results in the other folders section on the webUI
    And file "lorem.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI
    And file "lorem-big.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI

  Scenario: search for folders
    When the user searches for "folder" using the webUI
    Then folder "simple-folder" should be listed on the webUI
    And folder "strängé नेपाली folder" should be listed on the webUI
    And file "zzzz-must-be-last-file-in-folder.txt" should be listed on the webUI
    And folder "simple-empty-folder" with path "/'single'quotes" should be listed in the search results in the other folders section on the webUI
    And file "zzzz-must-be-last-file-in-folder.txt" with path "/simple-folder" should be listed in the search results in the other folders section on the webUI
    And file "zzzz-must-be-last-file-in-folder.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI
    But file "lorem.txt" should not be listed on the webUI
    And file "lorem.txt" should not be listed in the search results in the other folders section on the webUI

  Scenario: search in sub folder
    When the user opens folder "simple-folder" using the webUI
    And the user searches for "lorem" using the webUI
    Then file "lorem.txt" should be listed on the webUI
    And file "lorem-big.txt" should be listed on the webUI
    And file "lorem.txt" with path "/" should be listed in the search results in the other folders section on the webUI
    And file "lorem-big.txt" with path "/" should be listed in the search results in the other folders section on the webUI
    And file "lorem.txt" with path "/0" should be listed in the search results in the other folders section on the webUI
    And file "lorem.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI
    And file "lorem-big.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI
    But file "lorem.txt" with path "/simple-folder" should not be listed in the search results in the other folders section on the webUI

  @systemtags-app-required
  Scenario: search for a file using a tag
    Given user "Alice" has created a "normal" tag with name "ipsum"
    And user "Alice" has added tag "ipsum" to file "/lorem.txt"
    When the user browses to the tags page
    And the user searches for tag "ipsum" using the webUI
    Then file "lorem.txt" should be listed on the webUI

  @systemtags-app-required
  Scenario: search for a file with multiple tags
    Given user "Alice" has created a "normal" tag with name "lorem"
    And user "Alice" has created a "normal" tag with name "ipsum"
    And user "Alice" has added tag "lorem" to file "/lorem.txt"
    And user "Alice" has added tag "lorem" to file "/testimage.jpg"
    And user "Alice" has added tag "ipsum" to file "/lorem.txt"
    When the user browses to the tags page
    And the user searches for tag "lorem" using the webUI
    And the user searches for tag "ipsum" using the webUI
    Then file "lorem.txt" should be listed on the webUI
    And file "testimage.jpg" should not be listed on the webUI

  @systemtags-app-required
  Scenario: search for a file with tags
    Given user "Alice" has created a "normal" tag with name "lorem"
    And user "Alice" has added tag "lorem" to file "/lorem.txt"
    And user "Alice" has added tag "lorem" to file "/simple-folder/lorem.txt"
    When the user browses to the tags page
    And the user searches for tag "lorem" using the webUI
    Then file "lorem.txt" should be listed on the webUI
    And file "lorem.txt" with path "" should be listed in the tags page on the webUI
    And file "lorem.txt" with path "/simple-folder" should be listed in the tags page on the webUI

  @files_sharing-app-required
  Scenario: Search for a shared file
    Given user "Carol" has been created with default attributes and skeleton files
    When user "Carol" shares file "/lorem.txt" with user "Alice" using the sharing API
    And the user reloads the current page of the webUI
    And the user searches for "lorem" using the webUI
    Then file "lorem (2).txt" should be listed on the webUI

  @files_sharing-app-required
  Scenario: Search for a re-shared file
    Given user "Brian" has been created with default attributes and skeleton files
    And user "Carol" has been created with default attributes and skeleton files
    When user "Brian" shares file "/lorem.txt" with user "Carol" using the sharing API
    And user "Carol" shares file "/lorem (2).txt" with user "Alice" using the sharing API
    And the user reloads the current page of the webUI
    And the user searches for "lorem" using the webUI
    Then file "lorem (2).txt" should be listed on the webUI

  @files_sharing-app-required
  Scenario: Search for a shared folder
    Given user "Carol" has been created with default attributes and skeleton files
    When user "Carol" shares folder "simple-folder" with user "Alice" using the sharing API
    And the user reloads the current page of the webUI
    And the user searches for "simple" using the webUI
    Then folder "simple-folder (2)" should be listed on the webUI

  @skipOnFIREFOX
  Scenario: Search for a file after name is changed
    When the user renames file "lorem.txt" to "torem.txt" using the webUI
    And the user searches for "torem" using the webUI
    Then file "lorem.txt" should not be listed on the webUI
    And file "torem.txt" should be listed on the webUI

  Scenario: Search for a newly uploaded file
    Given user "Alice" has uploaded file with content "does-not-matter" to "torem.txt"
    And user "Alice" has uploaded file with content "does-not-matter" to "simple-folder/another-torem.txt"
    When the user searches for "torem" using the webUI
    Then file "torem.txt" with path "/" should be listed in the search results in the other folders section on the webUI
    And file "another-torem.txt" with path "/simple-folder" should be listed in the search results in the other folders section on the webUI

  Scenario: Search for files with difficult names
    Given user "Alice" has uploaded file with content "does-not-matter" to "/strängéनेपालीloremfile.txt"
    And user "Alice" has uploaded file with content "does-not-matter" to "/strängé नेपाली folder/strängéनेपालीloremfile.txt"
    When the user searches for "lorem" using the webUI
    Then file "strängéनेपालीloremfile.txt" with path "/" should be listed in the search results in the other folders section on the webUI
    And file "strängéनेपालीloremfile.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI

  Scenario: Search for files with difficult names and difficult search phrase
    Given user "Alice" has uploaded file with content "does-not-matter" to "/strängéनेपालीloremfile.txt"
    And user "Alice" has uploaded file with content "does-not-matter" to "/strängé नेपाली folder/strängéनेपालीloremfile.txt"
    When the user searches for "strängéनेपाली" using the webUI
    Then file "strängéनेपालीloremfile.txt" with path "/" should be listed in the search results in the other folders section on the webUI
    And file "strängéनेपालीloremfile.txt" with path "/strängé नेपाली folder" should be listed in the search results in the other folders section on the webUI
