@cli @skipOnLDAP
Feature: edit users
  As an admin
  I want to be able to edit user information
  So that I can keep the user information up-to-date

  Scenario: the administrator can edit a user email
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the email of user "brand-new-user" to "brand-new-user@example.com" using the occ command
    Then the command should have been successful
    And the command output should contain the text 'The email address of %username% updated to brand-new-user@example.com' about user "brand-new-user"
    And user "brand-new-user" should exist
    And the user attributes returned by the API should include
      | email | brand-new-user@example.com |

  @skipOnOcV10.3 @skipOnOcV10.4
  Scenario: the administrator can clear a user email
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And the administrator has changed the email of user "brand-new-user" to "brand-new-user@example.com"
    When the administrator changes the email of user "brand-new-user" to "''" using the occ command
    Then the command should have been successful
    And the command output should contain the text 'The email address of brand-new-user updated to '
    And user "brand-new-user" should exist
    And the user attributes returned by the API should include
      | email | |

  Scenario: the administrator can edit a user display name
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the display name of user "brand-new-user" to "A New User" using the occ command
    Then the command should have been successful
    And the command output should contain the text 'The displayname of %username% updated to A New User' about user "brand-new-user"
    And user "brand-new-user" should exist
    And the user attributes returned by the API should include
      | displayname | A New User |

  @skipOnOcV10.3 @skipOnOcV10.4
  Scenario: the administrator can clear a user display name and then it defaults to the username
    Given user "brand-new-user" has been created with default attributes and skeleton files
    And the administrator has changed the display name of user "brand-new-user" to "A New User"
    When the administrator changes the display name of user "brand-new-user" to "" using the occ command
    Then the command should have been successful
    And the command output should contain the text 'The displayname of brand-new-user updated to '
    And user "brand-new-user" should exist
    And the user attributes returned by the API should include
      | displayname | brand-new-user |

  @issue-23603
  Scenario: the administrator can edit a user quota
    Given user "brand-new-user" has been created with default attributes and skeleton files
    When the administrator changes the quota of user "brand-new-user" to "12MB" using the occ command
    Then the command should have failed with exit code 1
    #Then the command should have been successful
    And the command output should contain the text 'Supported keys are email, displayname'
    #And the command output should contain the text 'The quota of brand-new-user updated to 12 MB'
    And user "brand-new-user" should exist
    And the user attributes returned by the API should include
      | quota definition | default |
    #And the user attributes returned by the API should include
    #  | quota definition | 12 MB |

  Scenario Outline: Admin resets user password with special characters
    Given user "brand-new-user" has been deleted
    When the administrator creates user "brand-new-user" password "%alt1%" group "brand-new-group" using the occ command
    And the administrator resets the password of user "brand-new-user" to "<password>" using the occ command
    Then the command should have been successful
    And the command output should contain the text 'Successfully reset password for %username%' about user "brand-new-user"
    And user "brand-new-user" should exist
    And the content of file "textfile0.txt" for user "brand-new-user" using password "<password>" should be "ownCloud test text file 0" plus end-of-line
    But user "brand-new-user" using password "%alt1%" should not be able to download file "textfile0.txt"
    Examples:
      | password                     | comment                               |
      | !@#$%^&*()-_+=[]{}:;,.<>?~/\ | special characters                    |
      | España§àôœ€                  | special European and other characters |
      | नेपाली                       | Unicode                               |
      | password with spaces         | password with spaces                  |

  Scenario: admin creates a user and specifies an invalid password, containing just space
    Given user "brand-new-user" has been deleted
    When the administrator creates user "brand-new-user" password "%alt1%" group "brand-new-group" using the occ command
    And the administrator resets the password of user "brand-new-user" to " " using the occ command
    Then the command should have failed with exit code 1
    And the content of file "textfile0.txt" for user "brand-new-user" using password "%alt1%" should be "ownCloud test text file 0" plus end-of-line
    But user "brand-new-user" using password " " should not be able to download file "textfile0.txt"
