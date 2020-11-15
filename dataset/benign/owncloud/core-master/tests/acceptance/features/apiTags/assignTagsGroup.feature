@api @systemtags-app-required @TestAlsoOnExternalUserBackend @skipOnOcis @issue-ocis-reva-51
Feature: Title of your feature
  I want to use this template for my feature file

  Background:
    Given user "Alice" has been created with default attributes and skeleton files

  Scenario: User can assign tags when in the tag's groups
    Given group "grp1" has been created
    And user "Alice" has been added to group "grp1"
    When the administrator creates a "not user-assignable" tag with name "TagWithGroups" and groups "grp1|group2" using the WebDAV API
    Then the HTTP status code should be "201"
    And user "Alice" should be able to assign the "not user-assignable" tag with name "TagWithGroups"

  Scenario: User can assign static tags when in the tag's groups
    Given group "grp1" has been created
    And user "Alice" has been added to group "grp1"
    When the administrator creates a "static" tag with name "TagWithGroups" and groups "grp1|group2" using the WebDAV API
    Then the HTTP status code should be "201"
    And user "Alice" should be able to assign the "static" tag with name "TagWithGroups"

  Scenario: User cannot assign tags when not in the tag's groups
    When the administrator creates a "not user-assignable" tag with name "TagWithGroups" and groups "grp2|group2" using the WebDAV API
    Then the HTTP status code should be "201"
    And user "Alice" should not be able to assign the "not user-assignable" tag with name "TagWithGroups"
