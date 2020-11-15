@api @provisioning_api-app-required
Feature: add users to group
  As a admin
  I want to be able to add users to a group
  So that I can give a user access to the resources of the group

  Background:
    Given using OCS API version "1"

  @smokeTest @skipOnLDAP
  Scenario Outline: adding a user to a group
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "<group_id>" has been created
    When the administrator adds user "brand-new-user" to group "<group_id>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    Examples:
      | group_id    | comment                               |
      | simplegroup | nothing special here                  |
      | España§àôœ€ | special European and other characters |
      | नेपाली      | Unicode group name                    |

  @skipOnLDAP
  Scenario Outline: adding a user to a group
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "<group_id>" has been created
    When the administrator adds user "brand-new-user" to group "<group_id>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
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

  @issue-31015 @skipOnLDAP
  Scenario Outline: adding a user to a group that has a forward-slash in the group name
    Given user "brand-new-user" has been created with default attributes and skeleton files
    # After fixing issue-31015, change the following step to "has been created"
    And the administrator sends a group creation request for group "<group_id>" using the provisioning API
    #And group "<group_id>" has been created
    When the administrator adds user "brand-new-user" to group "<group_id>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    # The following step is needed so that the group does get cleaned up.
    # After fixing issue-31015, remove the following step:
    And the administrator deletes group "<group_id>" using the occ command
    Examples:
      | group_id         | comment                            |
      | Mgmt/Sydney      | Slash (special escaping happens)   |
      | Mgmt//NSW/Sydney | Multiple slash                     |
      | var/../etc       | using slash-dot-dot                |
      | priv/subadmins/1 | Subadmins mentioned not at the end |

  @skipOnLDAP
  Scenario Outline: adding a user to a group using mixes of upper and lower case in user and group names
    Given user "mixed-case-user" has been created with default attributes and skeleton files
    And group "<group_id1>" has been created
    And group "<group_id2>" has been created
    And group "<group_id3>" has been created
    When the administrator adds user "<user_id>" to group "<group_id1>" using the provisioning API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "mixed-case-user" should belong to group "<group_id1>"
    But user "mixed-case-user" should not belong to group "<group_id2>"
    And user "mixed-case-user" should not belong to group "<group_id3>"
    Examples:
      | user_id         | group_id1            | group_id2            | group_id3            |
      | Mixed-Case-USER | Case-Sensitive-Group | case-sensitive-group | CASE-SENSITIVE-GROUP |
      | Mixed-Case-User | case-sensitive-group | CASE-SENSITIVE-GROUP | Case-Sensitive-Group |
      | mixed-case-user | CASE-SENSITIVE-GROUP | Case-Sensitive-Group | case-sensitive-group |

  @skipOnLDAP
  Scenario: normal user tries to add himself to a group
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When user "brand-new-user" tries to add himself to group "brand-new-group" using the provisioning API
    Then the OCS status code should be "997"
    And the HTTP status code should be "401"
    And the API should not return any data

  @skipOnLDAP
  Scenario: admin tries to add user to a group which does not exist
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And group "nonexistentgroup" has been deleted
    When the administrator tries to add user "brand-new-user" to group "nonexistentgroup" using the provisioning API
    Then the OCS status code should be "102"
    And the HTTP status code should be "200"
    And the API should not return any data

  @skipOnLDAP
  Scenario: admin tries to add user to a group without sending the group
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator tries to add user "brand-new-user" to group "" using the provisioning API
    Then the OCS status code should be "101"
    And the HTTP status code should be "200"
    And the API should not return any data

  @skipOnLDAP
  Scenario: admin tries to add a user which does not exist to a group
    Given user "nonexistentuser" has been deleted
    And group "brand-new-group" has been created
    When the administrator tries to add user "nonexistentuser" to group "brand-new-group" using the provisioning API
    Then the OCS status code should be "103"
    And the HTTP status code should be "200"
    And the API should not return any data

  @skipOnLDAP
  Scenario: a subadmin cannot add users to groups the subadmin is responsible for
    Given these users have been created with default attributes and skeleton files:
      | username       |
      | brand-new-user |
      | subadmin       |
    And group "brand-new-group" has been created
    And user "subadmin" has been made a subadmin of group "brand-new-group"
    When user "subadmin" tries to add user "brand-new-user" to group "brand-new-group" using the provisioning API
    Then the OCS status code should be "104"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "brand-new-group"

  @skipOnLDAP
  Scenario: a subadmin cannot add users to groups the subadmin is not responsible for
    Given these users have been created with default attributes and skeleton files:
      | username         |
      | brand-new-user   |
      | another-subadmin |
    And group "brand-new-group" has been created
    And group "another-new-group" has been created
    And user "another-subadmin" has been made a subadmin of group "another-new-group"
    When user "another-subadmin" tries to add user "brand-new-user" to group "brand-new-group" using the provisioning API
    Then the OCS status code should be "104"
    And the HTTP status code should be "200"
    And user "brand-new-user" should not belong to group "brand-new-group"
