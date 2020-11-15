@api @provisioning_api-app-required
Feature: remove a user from a group
  As an admin
  I want to be able to remove a user from a group
  So that I can manage user access to group resources

  Background:
    Given using OCS API version "1"

  @smokeTest
  Scenario Outline: admin removes a user from a group
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "<group_id>" has been created
    And user "brand-new-user" has been added to group "<group_id>"
    When the administrator removes user "brand-new-user" from group "<group_id>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "<group_id>"
    Examples:
      | group_id        | comment                               |
      | brand-new-group | nothing special here                  |
      | España§àôœ€     | special European and other characters |
      | नेपाली          | Unicode group name                    |

  Scenario Outline: admin removes a user from a group
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "<group_id>" has been created
    And user "brand-new-user" has been added to group "<group_id>"
    When the administrator removes user "brand-new-user" from group "<group_id>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "<group_id>"
    Examples:
      | group_id            | comment                                 |
      | brand-new-group     | dash                                    |
      | the.group           | dot                                     |
      | left,right          | comma                                   |
      | 0                   | The "false" group                       |
      | Finance (NP)        | Space and brackets                      |
      | Admin&Finance       | Ampersand                               |
      | admin:Pokhara@Nepal | Colon and @                             |
      | maintenance#123     | Hash sign                               |
      | maint+eng           | Plus sign                               |
      | $x<=>[y*z^2]!       | Maths symbols                           |
      | Mgmt\Middle         | Backslash                               |
      | 50%pass             | Percent sign (special escaping happens) |
      | 50%25=0             | %25 literal looks like an escaped "%"   |
      | 50%2Eagle           | %2E literal looks like an escaped "."   |
      | 50%2Fix             | %2F literal looks like an escaped slash |
      | staff?group         | Question mark                           |
      | 😁 😂               | emoji                                   |

  @issue-31015
  Scenario Outline: admin removes a user from a group that has a forward-slash in the group name
    Given user "brand-new-user" has been created with default attributes and skeleton files
    # After fixing issue-31015, change the following step to "has been created"
    And the administrator sends a group creation request for group "<group_id>" using the provisioning API
    #And group "<group_id>" has been created
    And user "brand-new-user" has been added to group "<group_id>"
    When the administrator removes user "brand-new-user" from group "<group_id>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "<group_id>"
    # The following step is needed so that the group does get cleaned up.
    # After fixing issue-31015, remove the following step:
    And the administrator deletes group "<group_id>" using the occ command
    Examples:
      | group_id         | comment                            |
      | Mgmt/Sydney      | Slash (special escaping happens)   |
      | Mgmt//NSW/Sydney | Multiple slash                     |
      | var/../etc       | using slash-dot-dot                |
      | priv/subadmins/1 | Subadmins mentioned not at the end |

  Scenario Outline: remove a user from a group using mixes of upper and lower case in user and group names
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "<group_id1>" has been created
    And group "<group_id2>" has been created
    And group "<group_id3>" has been created
    And user "brand-new-user" has been added to group "<group_id1>"
    And user "brand-new-user" has been added to group "<group_id2>"
    And user "brand-new-user" has been added to group "<group_id3>"
    When the administrator removes user "<user_id>" from group "<group_id1>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "<group_id1>"
    But user "brand-new-user" should belong to group "<group_id2>"
    And user "brand-new-user" should belong to group "<group_id3>"
    Examples:
      | user_id        | group_id1   | group_id2   | group_id3   |
      | BRAND-NEW-USER | Mixed-Group | mixed-group | MIXED-GROUP |
      | Brand-New-User | mixed-group | MIXED-GROUP | Mixed-Group |
      | brand-new-user | MIXED-GROUP | Mixed-Group | mixed-group |

  Scenario: admin tries to remove a user from a group which does not exist
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "nonexistentgroup" has been deleted
    When the administrator removes user "brand-new-user" from group "nonexistentgroup" using the provisioning API
    Then the OCS status code should be "102"
    And the HTTP status code should be "200"
    And the API should not return any data

  @smokeTest
  Scenario: a subadmin can remove users from groups the subadmin is responsible for
    Given these users have been created with default attributes and skeleton files:
      | username       |
      | brand-new-user |
      | subadmin       |
    And group "brand-new-group" has been created
    And user "brand-new-user" has been added to group "brand-new-group"
    And user "subadmin" has been made a subadmin of group "brand-new-group"
    When user "subadmin" removes user "brand-new-user" from group "brand-new-group" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "brand-new-group"

  Scenario: a subadmin cannot remove users from groups the subadmin is not responsible for
    Given these users have been created with default attributes and skeleton files:
      | username         |
      | brand-new-user   |
      | another-subadmin |
    And group "brand-new-group" has been created
    And group "another-new-group" has been created
    And user "brand-new-user" has been added to group "brand-new-group"
    And user "another-subadmin" has been made a subadmin of group "another-new-group"
    When user "another-subadmin" removes user "brand-new-user" from group "brand-new-group" using the provisioning API
    Then the OCS status code should be "104"
    And the HTTP status code should be "200"
    And user "brand-new-user" should belong to group "brand-new-group"

  Scenario: normal user tries to remove a user in their group
    Given these users have been created with default attributes and skeleton files:
      | username         |
      | brand-new-user   |
      | another-new-user |
    And group "brand-new-group" has been created
    And user "brand-new-user" has been added to group "brand-new-group"
    And user "another-new-user" has been added to group "brand-new-group"
    When user "brand-new-user" tries to remove user "another-new-user" from group "brand-new-group" using the provisioning API
    Then the OCS status code should be "997"
    And the HTTP status code should be "401"
    And user "another-new-user" should belong to group "brand-new-group"
