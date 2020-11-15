@api @TestAlsoOnExternalUserBackend
Feature: favorite

  Background:
    Given using OCS API version "1"
    And user "Alice" has been created with default attributes and without skeleton files
    And user "Alice" has uploaded file with content "some data" to "/textfile0.txt"
    And user "Alice" has uploaded file with content "some data" to "/textfile1.txt"
    And user "Alice" has uploaded file with content "some data" to "/textfile2.txt"
    And user "Alice" has uploaded file with content "some data" to "/textfile3.txt"
    And user "Alice" has uploaded file with content "some data" to "/textfile4.txt"
    And user "Alice" has created folder "/FOLDER"
    And user "Alice" has created folder "/PARENT"
    And user "Alice" has uploaded file with content "some data" to "/PARENT/parent.txt"

  @skipOnOcis-EOS-Storage @issue-ocis-reva-276
  Scenario Outline: Favorite a folder
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/FOLDER" using the WebDAV API
    Then the HTTP status code should be "207"
    And as user "Alice" folder "/FOLDER" should be favorited
    When user "Alice" gets the following properties of folder "/FOLDER" using the WebDAV API
      | propertyName |
      | oc:favorite  |
    Then the single response should contain a property "oc:favorite" with value "1"
    Examples:
      | dav_version |
      | old         |
      | new         |

  @skipOnOcV10 @skipOnOcis-OC-Storage @issue-ocis-reva-276
  #after fixing the issues delete this Scenario and use the one above
  Scenario Outline: Favorite a folder
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/FOLDER" using the WebDAV API
    Then the HTTP status code should be "500"
    Examples:
      | dav_version |
      | old         |
      | new         |

  @skipOnOcis-EOS-Storage @issue-ocis-reva-276
  Scenario Outline: Favorite and unfavorite a folder
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/FOLDER" using the WebDAV API
    And user "Alice" unfavorites element "/FOLDER" using the WebDAV API
    Then the HTTP status code should be "207"
    And as user "Alice" folder "/FOLDER" should not be favorited
    When user "Alice" gets the following properties of folder "/FOLDER" using the WebDAV API
      | propertyName |
      | oc:favorite  |
    Then the single response should contain a property "oc:favorite" with value "0"
    Examples:
      | dav_version |
      | old         |
      | new         |

  @smokeTest @skipOnOcis-EOS-Storage @issue-ocis-reva-276
  Scenario Outline: Favorite a file
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/textfile0.txt" using the WebDAV API
    Then the HTTP status code should be "207"
    And as user "Alice" file "/textfile0.txt" should be favorited
    When user "Alice" gets the following properties of file "/textfile0.txt" using the WebDAV API
      | propertyName |
      | oc:favorite  |
    Then the single response should contain a property "oc:favorite" with value "1"
    Examples:
      | dav_version |
      | old         |
      | new         |

  @smokeTest @skipOnOcis-EOS-Storage @issue-ocis-reva-276
  Scenario Outline: Favorite and unfavorite a file
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/textfile0.txt" using the WebDAV API
    And user "Alice" unfavorites element "/textfile0.txt" using the WebDAV API
    Then the HTTP status code should be "207"
    And as user "Alice" file "/textfile0.txt" should not be favorited
    When user "Alice" gets the following properties of file "/textfile0.txt" using the WebDAV API
      | propertyName |
      | oc:favorite  |
    Then the single response should contain a property "oc:favorite" with value "0"
    Examples:
      | dav_version |
      | old         |
      | new         |

  @smokeTest
  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: Get favorited elements of a folder
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/FOLDER" using the WebDAV API
    And user "Alice" favorites element "/textfile0.txt" using the WebDAV API
    And user "Alice" favorites element "/textfile1.txt" using the WebDAV API
    Then the HTTP status code should be "207"
    And user "Alice" in folder "/" should have favorited the following elements
      | /FOLDER        |
      | /textfile0.txt |
      | /textfile1.txt |
    Examples:
      | dav_version |
      | old         |
      | new         |

  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: Get favorited elements of a subfolder
    Given using <dav_version> DAV path
    And user "Alice" has created folder "/subfolder"
    And user "Alice" has uploaded file with content "some data" to "/subfolder/textfile0.txt"
    And user "Alice" has uploaded file with content "some data" to "/subfolder/textfile1.txt"
    And user "Alice" has uploaded file with content "some data" to "/subfolder/textfile2.txt"
    When user "Alice" favorites element "/subfolder/textfile0.txt" using the WebDAV API
    And user "Alice" favorites element "/subfolder/textfile1.txt" using the WebDAV API
    And user "Alice" favorites element "/subfolder/textfile2.txt" using the WebDAV API
    And user "Alice" unfavorites element "/subfolder/textfile1.txt" using the WebDAV API
    Then the HTTP status code should be "207"
    And user "Alice" in folder "/subfolder" should have favorited the following elements
      | /subfolder/textfile0.txt |
      | /subfolder/textfile2.txt |
    And user "Alice" in folder "/subfolder" should not have favorited the following elements
      | /subfolder/textfile1.txt |
    Examples:
      | dav_version |
      | old         |
      | new         |

  @files_sharing-app-required
  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: moving a favorite file out of a share keeps favorite state
    Given using <dav_version> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has created folder "/shared"
    And user "Alice" has moved file "/textfile0.txt" to "/shared/shared_file.txt"
    And user "Alice" has shared folder "/shared" with user "Brian"
    And user "Brian" has favorited element "/shared/shared_file.txt"
    When user "Brian" moves file "/shared/shared_file.txt" to "/taken_out.txt" using the WebDAV API
    Then user "Brian" in folder "/" should have favorited the following elements
      | /taken_out.txt |
    Examples:
      | dav_version |
      | old         |
      | new         |

  @issue-33840
  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: Get favorited elements and limit count of entries
    Given using <dav_version> DAV path
    And user "Alice" has favorited element "/textfile0.txt"
    And user "Alice" has favorited element "/textfile1.txt"
    And user "Alice" has favorited element "/textfile2.txt"
    And user "Alice" has favorited element "/textfile3.txt"
    And user "Alice" has favorited element "/textfile4.txt"
    When user "Alice" lists the favorites of folder "/" and limits the result to 3 elements using the WebDAV API
    #Then the search result of "Alice" should contain any "3" of these entries:
    Then the search result should contain any "0" of these entries:
      | /textfile0.txt |
      | /textfile1.txt |
      | /textfile2.txt |
      | /textfile3.txt |
      | /textfile4.txt |
    Examples:
      | dav_version |
      | old         |
      | new         |

  @issue-33840
  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: Get favorited elements paginated in subfolder
    Given using <dav_version> DAV path
    And user "Alice" has created folder "/subfolder"
    And user "Alice" has copied file "/textfile0.txt" to "/subfolder/textfile0.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/subfolder/textfile1.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/subfolder/textfile2.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/subfolder/textfile3.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/subfolder/textfile4.txt"
    And user "Alice" has copied file "/textfile0.txt" to "/subfolder/textfile5.txt"
    And user "Alice" has favorited element "/subfolder/textfile0.txt"
    And user "Alice" has favorited element "/subfolder/textfile1.txt"
    And user "Alice" has favorited element "/subfolder/textfile2.txt"
    And user "Alice" has favorited element "/subfolder/textfile3.txt"
    And user "Alice" has favorited element "/subfolder/textfile4.txt"
    And user "Alice" has favorited element "/subfolder/textfile5.txt"
    When user "Alice" lists the favorites of folder "/" and limits the result to 3 elements using the WebDAV API
    #Then the search result of "Alice" should contain any "3" of these entries:
    Then the search result should contain any "0" of these entries:
      | /subfolder/textfile0.txt |
      | /subfolder/textfile1.txt |
      | /subfolder/textfile2.txt |
      | /subfolder/textfile3.txt |
      | /subfolder/textfile4.txt |
    Examples:
      | dav_version |
      | old         |
      | new         |

  @files_sharing-app-required
  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: sharer file favorite state should not change the favorite state of sharee
    Given using <dav_version> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has moved file "/textfile0.txt" to "/favoriteFile.txt"
    And user "Alice" has shared file "/favoriteFile.txt" with user "Brian"
    When user "Alice" favorites element "/favoriteFile.txt" using the WebDAV API
    Then the HTTP status code should be "207"
    And as user "Brian" file "/favoriteFile.txt" should not be favorited
    Examples:
      | dav_version |
      | old         |
      | new         |

  @files_sharing-app-required
  @skipOnOcis @issue-ocis-reva-21
  Scenario Outline: sharee file favorite state should not change the favorite state of sharer
    Given using <dav_version> DAV path
    And user "Brian" has been created with default attributes and without skeleton files
    And user "Alice" has moved file "/textfile0.txt" to "/favoriteFile.txt"
    And user "Alice" has shared file "/favoriteFile.txt" with user "Brian"
    When user "Brian" favorites element "/favoriteFile.txt" using the WebDAV API
    Then the HTTP status code should be "207"
    And as user "Alice" file "/favoriteFile.txt" should not be favorited
    Examples:
      | dav_version |
      | old         |
      | new         |

  @skipOnOcis @issue-ocis-reva-39
  Scenario Outline: favoriting a folder does not change the favorite state of elements inside the folder
    Given using <dav_version> DAV path
    When user "Alice" favorites element "/PARENT/parent.txt" using the WebDAV API
    And user "Alice" favorites element "/PARENT" using the WebDAV API
    Then the HTTP status code should be "207"
    And user "Alice" in folder "/" should have favorited the following elements
      | /PARENT            |
      | /PARENT/parent.txt |
    Examples:
      | dav_version |
      | old         |
      | new         |
