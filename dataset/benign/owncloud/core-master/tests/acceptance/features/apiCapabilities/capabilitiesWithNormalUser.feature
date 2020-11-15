@api @TestAlsoOnExternalUserBackend @files_sharing-app-required
Feature: default capabilities for normal user

  Background:
    Given using OCS API version "1"
    And user "Alice" has been created with default attributes and without skeleton files

  @skipOnOcV10 @issue-ocis-reva-175 @issue-ocis-reva-176
  Scenario: getting default capabilities with normal user
    When user "Alice" retrieves the capabilities using the capabilities API
    Then the capabilities should contain
      | capability    | path_to_element                           | value             |
      | core          | pollinterval                              | 60                |
      | core          | webdav-root                               | remote.php/webdav |
      | core          | status@@@edition                          | %edition%         |
      | core          | status@@@productname                      | reva              |
      | core          | status@@@version                          | 10.0.11.5         |
      | core          | status@@@versionstring                    | 10.0.11           |
      | files_sharing | api_enabled                               | 1                 |
      | files_sharing | default_permissions                       | 22                |
      | files_sharing | search_min_length                         | 3                 |
      | files_sharing | public@@@enabled                          | 1                 |
      | files_sharing | public@@@multiple                         | 1                 |
      | files_sharing | public@@@upload                           | 1                 |
      | files_sharing | public@@@supports_upload_only             | 1                 |
      | files_sharing | public@@@send_mail                        | 1                 |
      | files_sharing | public@@@social_share                     | 1                 |
      | files_sharing | public@@@enforced                         | EMPTY             |
      | files_sharing | public@@@enforced_for@@@read_only         | EMPTY             |
      | files_sharing | public@@@enforced_for@@@read_write        | EMPTY             |
      | files_sharing | public@@@enforced_for@@@upload_only       | EMPTY             |
      | files_sharing | public@@@enforced_for@@@read_write_delete | EMPTY             |
      | files_sharing | public@@@expire_date@@@enabled            | 1                 |
      | files_sharing | public@@@defaultPublicLinkShareName       | EMPTY             |
      | files_sharing | resharing                                 | 1                 |
      | files_sharing | federation@@@outgoing                     | 1                 |
      | files_sharing | federation@@@incoming                     | 1                 |
      | files_sharing | group_sharing                             | 1                 |
      | files_sharing | share_with_group_members_only             | 1                 |
      | files_sharing | share_with_membership_groups_only         | 1                 |
      | files_sharing | auto_accept_share                         | 1                 |
      | files_sharing | user_enumeration@@@enabled                | 1                 |
      | files_sharing | user_enumeration@@@group_members_only     | 1                 |
      | files_sharing | user@@@send_mail                          | 1                 |
      | files         | bigfilechunking                           | 0                 |
      | files         | privateLinks                              | 0                 |
      | files         | privateLinksDetailsParam                  | EMPTY             |

  # remove this scenario after fixing tagged issue as its just created to show difference
  # in the response items in different environment (core & ocis-reva)
  @skipOnOcis @issue-ocis-reva-175 @issue-ocis-reva-176
  Scenario: getting default capabilities with normal user
    When user "Alice" retrieves the capabilities using the capabilities API
    Then the capabilities should contain
      | capability    | path_to_element                           | value             |
      | core          | pollinterval                              | 60                |
      | core          | webdav-root                               | remote.php/webdav |
      | core          | status@@@edition                          | %edition%         |
      | core          | status@@@productname                      | %productname%     |
      | core          | status@@@version                          | %version%         |
      | core          | status@@@versionstring                    | %versionstring%   |
      | files_sharing | api_enabled                               | 1                 |
      | files_sharing | default_permissions                       | 31                |
      | files_sharing | search_min_length                         | 2                 |
      | files_sharing | public@@@enabled                          | 1                 |
      | files_sharing | public@@@multiple                         | 1                 |
      | files_sharing | public@@@upload                           | 1                 |
      | files_sharing | public@@@supports_upload_only             | 1                 |
      | files_sharing | public@@@send_mail                        | EMPTY             |
      | files_sharing | public@@@social_share                     | 1                 |
      | files_sharing | public@@@enforced                         | EMPTY             |
      | files_sharing | public@@@enforced_for@@@read_only         | EMPTY             |
      | files_sharing | public@@@enforced_for@@@read_write        | EMPTY             |
      | files_sharing | public@@@enforced_for@@@upload_only       | EMPTY             |
      | files_sharing | public@@@enforced_for@@@read_write_delete | EMPTY             |
      | files_sharing | public@@@expire_date@@@enabled            | EMPTY             |
      | files_sharing | public@@@defaultPublicLinkShareName       | Public link       |
      | files_sharing | resharing                                 | 1                 |
      | files_sharing | federation@@@outgoing                     | 1                 |
      | files_sharing | federation@@@incoming                     | 1                 |
      | files_sharing | group_sharing                             | 1                 |
      | files_sharing | share_with_group_members_only             | EMPTY             |
      | files_sharing | share_with_membership_groups_only         | EMPTY             |
      | files_sharing | auto_accept_share                         | 1                 |
      | files_sharing | user_enumeration@@@enabled                | 1                 |
      | files_sharing | user_enumeration@@@group_members_only     | EMPTY             |
      | files_sharing | user@@@send_mail                          | EMPTY             |
      | files         | bigfilechunking                           | 1                 |
      | files         | privateLinks                              | 1                 |
      | files         | privateLinksDetailsParam                  | 1                 |
