@webUI @insulated @disablePreviews @mailhog
Feature: add users
  As an admin
  I want to add users
  So that unauthorised access is impossible

  Background:
    Given the administrator has logged in using the webUI
    And the administrator has browsed to the users page

  Scenario Outline: use the webUI to create a simple user
    When the administrator creates a user with the name "<username>" and the password "<password>" using the webUI
    And the administrator logs out of the webUI
    And user "<username>" logs in using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"
    Examples:
      | username | password  |
      | guiusr1  | %regular% |
      | user-1   | %regular% |
      | 0.0      | %regular% |
      | 123      | %regular% |
      | -123     | %regular% |
      | null     | %regular% |
      | nil      | %regular% |

  @skipOnOcV10.3
  Scenario: use the webUI to create a user with special valid characters
    When the administrator creates a user with the name "@-+_.'" and the password "%regular%" using the webUI
    And the administrator logs out of the webUI
    And user "@-+_.'" logs in using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  @skipOnOcV10.3
  Scenario: use the webUI to create a user with special invalid characters
    When the administrator attempts to create these users then the notifications should be as listed
      | user | password    | notification                                                                                                    |
      | a#%  | "%regular%" | Error creating user: Only the following characters are allowed in a username: "a-z", "A-Z", "0-9", and "+_.@-'" |
      | a+^  | "%alt1%"    | Error creating user: Only the following characters are allowed in a username: "a-z", "A-Z", "0-9", and "+_.@-'" |
      | a)~  | "%alt2%"    | Error creating user: Only the following characters are allowed in a username: "a-z", "A-Z", "0-9", and "+_.@-'" |
      | a(=  | "%alt3%"    | Error creating user: Only the following characters are allowed in a username: "a-z", "A-Z", "0-9", and "+_.@-'" |
      | a`*^ | "%alt4%"    | Error creating user: Only the following characters are allowed in a username: "a-z", "A-Z", "0-9", and "+_.@-'" |

  @skipOnOcV10.3 @skipOnOcV10.4
  Scenario: use the webUI to create a user with special invalid username
    When the administrator attempts to create these users then the notifications should be as listed
      | user | password    | notification                                                                                                    |
      | meta | "%alt4%"    | Error creating user: The special username meta is not allowed                                                   |

  Scenario: use the webUI to create a user with empty password
    When the administrator attempts to create a user with the name "bijay" and the password "" using the webUI
    Then notifications should be displayed on the webUI with the text
      | Error creating user: A valid password must be provided |
    And the user should be redirected to a webUI page with the title "Users - %productname%"

  Scenario Outline: use the webUI to create a user with less than 3 characters
    When the administrator attempts to create a user with the name <user> and the password <password> using the webUI
    Then notifications should be displayed on the webUI with the text
      | Error creating user: The username must be at least 3 characters long |
    Examples:
      | user | password    |
      | "a"  | "%regular%" |
      | "a1" | "%alt1%"    |
      | "-1" | "%alt1%"    |

  @smokeTest
  Scenario: use the webUI to create a simple user with an Email address but without a password
    When the administrator creates a user with the name "guiusr1" and the email "guiusr1@owncloud" without a password using the webUI
    Then the email address "guiusr1@owncloud" should have received an email with the body containing
      """
      just letting you know that you now have an %productname% account.

      Your username: guiusr1
      Access it:
      """

  @smokeTest @skipOnOcV10.0 @skipOnOcV10.1 @skipOnOcV10.2 @skipOnOcV10.3
  Scenario Outline: user sets his own password after being created with an Email address only
    When the administrator creates a user with the name "<username>" and the email "guiusr1@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "guiusr1@owncloud" using the webUI
    And the user sets the password to "%regular%" and confirms with the same password using the webUI
    Then the user should be redirected to the login page
    And the email address "guiusr1@owncloud" should have received an email with the body containing
      """
      Password changed successfully
      """
    When the user logs in with username "<username>" and password "%regular%" using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"
    Examples:
      | username | comment               |
      | guiusr1  | simple user-name      |
      | a@-+_.'b | complicated user-name |

  @skipOnOcV10.3
  Scenario Outline: user sets his own password but retypes it wrongly after being created with an Email address only
    When the administrator creates a user with the name "<username>" and the email "guiusr1@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "guiusr1@owncloud" using the webUI
    And the user sets the password to "%regular%" and confirms with "foo" using the webUI
    Then the user should see a password mismatch message displayed on the webUI
      """
      Passwords do not match
      """
    Examples:
      | username | comment               |
      | guiusr1  | simple user-name      |
      | a@-+_.'b | complicated user-name |

  Scenario Outline: webUI refuses to create users with invalid Email addresses
    When the administrator creates a user with the name "guiusr1" and the email "<email>" without a password using the webUI
    Then notifications should be displayed on the webUI with the text
      | Error creating user: Invalid mail address |
    Examples:
      | email  | comment        |
      | string | no @ sign      |
      | a@     | no domain name |
      #there would be much more to test here, but its complicated and would be slow
      #see http://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses
      #email address validation would better go into an unit test

  Scenario: webUI refuses to create a user with an empty Email address
    When the administrator creates a user with the name "guiusr1" and the email "" without a password using the webUI
    Then notifications should be displayed on the webUI with the text
      | Error creating user: A valid email must be provided |

  Scenario: changing the user password as an admin invalidates the user sets-password-token
    When the administrator creates a user with the name "guiusr1" and the email "guiusr1@owncloud" without a password using the webUI
    And the administrator changes the password of user "guiusr1" to "123" using the provisioning API
    And the administrator logs out of the webUI
    And the user follows the password set link received by "guiusr1@owncloud" using the webUI
    Then the user should be redirected to the general error webUI page with the title "%productname%"
    And an error should be displayed on the general error webUI page saying "The token provided is invalid."

  Scenario: sets-password-token cannot be used twice
    When the administrator creates a user with the name "guiusr1" and the email "guiusr1@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "guiusr1@owncloud" using the webUI
    And the user sets the password to "%regular%" and confirms with the same password using the webUI
    And the user follows the password set link received by "guiusr1@owncloud" in Email number 2 using the webUI
    Then the user should be redirected to the general error webUI page with the title "%productname%"
    And an error should be displayed on the general error webUI page saying "The token provided is invalid."

  Scenario: recreating a user with same name after deletion sends a new token to new address
    When the administrator creates a user with the name "guiusr1" and the email "mistake@owncloud" without a password using the webUI
    And the administrator deletes user "guiusr1" using the webUI and confirms the deletion using the webUI
    And the administrator creates a user with the name "guiusr1" and the email "correct@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "correct@owncloud" using the webUI
    And the user sets the password to "%regular%" and confirms with the same password using the webUI
    And the user logs in with username "guiusr1" and password "%regular%" using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  Scenario: recreating a user with same name after deletion makes the first token invalid
    When the administrator creates a user with the name "guiusr1" and the email "mistake@owncloud" without a password using the webUI
    And the administrator deletes user "guiusr1" using the webUI and confirms the deletion using the webUI
    And the administrator creates a user with the name "guiusr1" and the email "correct@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "mistake@owncloud" using the webUI
    Then the user should be redirected to the general error webUI page with the title "%productname%"
    And an error should be displayed on the general error webUI page saying "The token provided is invalid."

  Scenario: when recreating a user with same second token can be used even if someone tried to use the first one
    When the administrator creates a user with the name "guiusr1" and the email "mistake@owncloud" without a password using the webUI
    And the administrator deletes user "guiusr1" using the webUI and confirms the deletion using the webUI
    And the administrator creates a user with the name "guiusr1" and the email "correct@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "mistake@owncloud" using the webUI
    And the user follows the password set link received by "correct@owncloud" using the webUI
    And the user sets the password to "%regular%" and confirms with the same password using the webUI
    And the user logs in with username "guiusr1" and password "%regular%" using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"

  Scenario: check if the sender email address is valid
    When the administrator creates a user with the name "Brian" and the email "guiusr1@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "guiusr1@owncloud" using the webUI
    And the user sets the password to "%regular%" and confirms with the same password using the webUI
    Then the user should be redirected to the login page
    And the email address "guiusr1@owncloud" should have received an email with the body containing
      """
      Password changed successfully
      """
    And the reset email to user "Brian" should be from "owncloud@foobar.com"

  Scenario Outline: admin creates a user and sets password containing special characters
    Given user "brand-new-user" has been deleted
    When the administrator creates a user with the name "brand-new-user" and the password "<password>" using the webUI
    And the administrator logs out of the webUI
    And the user logs in with username "brand-new-user" and password "<password>" using the webUI
    Then user "brand-new-user" should exist
    And the user should be redirected to a webUI page with the title "Files - %productname%"
    Examples:
      | password                     | comment                     |
      | !@#$%^&*()-_+=[]{}:;,.<>?~/\ | special characters          |
      | España                       | special European characters |
      | नेपाली                       | Unicode                     |
      | password with spaces         | password with spaces        |

  Scenario Outline: admin creates a user without setting password and user sets password containing special characters
    When the administrator creates a user with the name "brand-new-user" and the email "bnu@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "bnu@owncloud" using the webUI
    And the user sets the password to "<password>" and confirms with the same password using the webUI
    And the user logs in with username "brand-new-user" and password "<password>" using the webUI
    Then user "brand-new-user" should exist
    And the user should be redirected to a webUI page with the title "Files - %productname%"
    Examples:
      | password                     | comment                     |
      | !@#$%^&*()-_+=[]{}:;,.<>?~/\ | special characters          |
      | España                       | special European characters |
      | नेपाली                       | Unicode                     |
      | password with spaces         | password with spaces        |

  Scenario: admin creates a user without setting password and user sets empty spaces as password
    When the administrator creates a user with the name "brand-new-user" and the email "bnu@owncloud" without a password using the webUI
    And the administrator logs out of the webUI
    And the user follows the password set link received by "bnu@owncloud" using the webUI
    And the user sets the password to " " and confirms with the same password using the webUI
    Then the user should be redirected to a webUI page with the title "%productname%"

  Scenario Outline: admin creates a user but logs in with different case username
    When the administrator creates a user with the name "<creation-username>" and the password "password" using the webUI
    And the administrator logs out of the webUI
    And the user logs in with username "<login-username>" and password "password" using the webUI
    Then user "<username>" should exist
    And the user should be redirected to a webUI page with the title "Files - %productname%"
    Examples:
      | creation-username  | login-username  | username        |
      | Mixed-Case-user    | mixed-case-user | MIXED-CASE-USER |
      | mixed-case-user    | Mixed-Case-user | Mixed-Case-user |
      | Mixed-Case-user    | MIXED-CASE-USER | mixed-case-user |
      | mixed-case-user    | Mixed-Case-user | MIXED-CASE-USER |

  Scenario Outline: user names are not case-sensitive, multiple users can't exist with different upper and lower case names
    When the administrator creates a user with the name "<user_id1>" and the password "password" using the webUI
    And the administrator creates a user with the name "<user_id2>" and the password "password" using the webUI
    Then notifications should be displayed on the webUI with the text
      | Error creating user: A user with that name already exists. |
    When the administrator creates a user with the name "<user_id3>" and the password "password" using the webUI
    Then notifications should be displayed on the webUI with the text
      | Error creating user: A user with that name already exists. |
    Examples:
      | user_id1        | user_id2        | user_id3        |
      | Mixed-Case-user | mixed-case-user | MIXED-CASE-USER |
      | mixed-case-user | MIXED-CASE-USER | Mixed-Case-user |
      | MIXED-CASE-USER | Mixed-Case-user | mixed-case-user |
