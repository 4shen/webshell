<?php
/**
 * @author Sergio Bertolin <sbertolin@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\Assert;
use TestHelpers\OcsApiHelper;
use TestHelpers\SetupHelper;
use TestHelpers\UserHelper;
use TestHelpers\HttpRequestHelper;
use TestHelpers\OcisHelper;
use TestHelpers\FileHandlingHelper;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;

/**
 * Functions for provisioning of users and groups
 */
trait Provisioning {

	/**
	 * list of users that were created on the local server during test runs
	 * key is the lowercase username, value is an array of user attributes
	 *
	 * @var array
	 */
	private $createdUsers = [];

	/**
	 * @var string
	 */
	private $ou = "TestGroups";

	/**
	 * list of users that were created on the remote server during test runs
	 * key is the lowercase username, value is an array of user attributes
	 *
	 * @var array
	 */
	private $createdRemoteUsers = [];

	/**
	 * @var array
	 */
	private $enabledApps = [];

	/**
	 * @var array
	 */
	private $disabledApps = [];

	/**
	 * @var array
	 */
	private $createdRemoteGroups = [];

	/**
	 * @var array
	 */
	private $createdGroups = [];

	/**
	 * @var array
	 */
	private $userResponseFields = [
		"enabled", "quota", "email", "displayname", "home", "two_factor_auth_enabled",
		"quota definition", "quota free", "quota user", "quota total", "quota relative"
	];

	/**
	 * Check if this is the admin group. That group is always a local group in
	 * ownCloud10, even if other groups come from LDAP.
	 *
	 * @param string $groupname
	 *
	 * @return boolean
	 */
	public function isLocalAdminGroup($groupname) {
		return ($groupname === "admin");
	}

	/**
	 * Usernames are not case-sensitive, and can generally be specified with any
	 * mix of upper and lower case. For remembering usernames use the normalized
	 * form so that "alice" and "Alice" are remembered as the same user.
	 *
	 * @param string $username
	 *
	 * @return string
	 */
	public function normalizeUsername($username) {
		return \strtolower($username);
	}

	/**
	 * @return array
	 */
	public function getCreatedUsers() {
		return $this->createdUsers;
	}

	/**
	 * @return array
	 */
	public function getCreatedGroups() {
		return $this->createdGroups;
	}

	/**
	 * returns the display name of the current user
	 * if no "Display Name" is set the user-name is returned instead
	 *
	 * @return string
	 */
	public function getCurrentUserDisplayName() {
		return $this->getUserDisplayName($this->getCurrentUser());
	}

	/**
	 * returns the display name of a user
	 * if no "Display Name" is set the username is returned instead
	 *
	 * @param string $username
	 *
	 * @return string
	 */
	public function getUserDisplayName($username) {
		$user = $this->normalizeUsername($username);
		if (isset($this->createdUsers[$user]['displayname'])) {
			$displayName = (string) $this->createdUsers[$user]['displayname'];
			if ($displayName !== '') {
				return $displayName;
			}
		}
		return $username;
	}

	/**
	 * @param string $user
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function rememberUserDisplayName($user, $displayName) {
		$user = $this->normalizeUsername($user);
		if ($this->isAdminUsername($user)) {
			$this->adminDisplayName = $displayName;
		} else {
			if ($this->currentServer === 'LOCAL') {
				if (\array_key_exists($user, $this->createdUsers)) {
					$this->createdUsers[$user]['displayname'] = $displayName;
				} else {
					throw new \Exception(
						__METHOD__ . " tried to remember display name '$displayName' for non-existent local user '$user'"
					);
				}
			} elseif ($this->currentServer === 'REMOTE') {
				if (\array_key_exists($user, $this->createdRemoteUsers)) {
					$this->createdRemoteUsers[$user]['displayname'] = $displayName;
				} else {
					throw new \Exception(
						__METHOD__ . " tried to remember display name '$displayName' for non-existent remote user '$user'"
					);
				}
			}
		}
	}

	/**
	 * @param string $user
	 * @param string $emailAddress
	 *
	 * @return void
	 * @throws Exception
	 */
	public function rememberUserEmailAddress($user, $emailAddress) {
		$user = $this->normalizeUsername($user);
		if ($this->isAdminUsername($user)) {
			$this->adminEmailAddress = $emailAddress;
		} else {
			if ($this->currentServer === 'LOCAL') {
				if (\array_key_exists($user, $this->createdUsers)) {
					$this->createdUsers[$user]['email'] = $emailAddress;
				} else {
					throw new \Exception(
						__METHOD__ . " tried to remember email address '$emailAddress' for non-existent local user '$user'"
					);
				}
			} elseif ($this->currentServer === 'REMOTE') {
				if (\array_key_exists($user, $this->createdRemoteUsers)) {
					$this->createdRemoteUsers[$user]['email'] = $emailAddress;
				} else {
					throw new \Exception(
						__METHOD__ . " tried to remember email address '$emailAddress' for non-existent remote user '$user'"
					);
				}
			}
		}
	}

	/**
	 * returns an array of the user display names, keyed by username
	 * if no "Display Name" is set the user-name is returned instead
	 *
	 * @return array
	 */
	public function getCreatedUserDisplayNames() {
		$result = [];
		foreach ($this->getCreatedUsers() as $username => $user) {
			$result[$username] = $this->getUserDisplayName($username);
		}
		return $result;
	}

	/**
	 * returns an array of the group display names, keyed by group name
	 * currently group name and display name are always the same, so this
	 * function is a convenience for getting the group names in a similar
	 * format to what getCreatedUserDisplayNames() returns
	 *
	 * @return array
	 */
	public function getCreatedGroupDisplayNames() {
		$result = [];
		foreach ($this->getCreatedGroups() as $groupName => $groupData) {
			$result[$groupName] = $groupName;
		}
		return $result;
	}

	/**
	 *
	 * @param string $username
	 *
	 * @return string password
	 * @throws \Exception
	 */
	public function getUserPassword($username) {
		$username = $this->normalizeUsername($username);
		if ($username === $this->getAdminUsername()) {
			$password = $this->getAdminPassword();
		} elseif (\array_key_exists($username, $this->createdUsers)) {
			$password = $this->createdUsers[$username]['password'];
		} elseif (\array_key_exists($username, $this->createdRemoteUsers)) {
			$password = $this->createdRemoteUsers[$username]['password'];
		} else {
			throw new Exception(
				"user '$username' was not created by this test run"
			);
		}

		//make sure the function always returns a string
		return (string) $password;
	}

	/**
	 *
	 * @param string $username
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function theUserShouldExist($username) {
		$username = $this->normalizeUsername($username);
		if (\array_key_exists($username, $this->createdUsers)) {
			return $this->createdUsers[$username]['shouldExist'];
		}

		if (\array_key_exists($username, $this->createdRemoteUsers)) {
			return $this->createdRemoteUsers[$username]['shouldExist'];
		}

		throw new Exception(
			__METHOD__
			. " user '$username' was not created by this test run"
		);
	}

	/**
	 *
	 * @param string $groupname
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function theGroupShouldExist($groupname) {
		if (\array_key_exists($groupname, $this->createdGroups)) {
			if (\array_key_exists('shouldExist', $this->createdGroups[$groupname])) {
				return $this->createdGroups[$groupname]['shouldExist'];
			}
			return false;
		}

		if (\array_key_exists($groupname, $this->createdRemoteGroups)) {
			if (\array_key_exists('shouldExist', $this->createdRemoteGroups[$groupname])) {
				return $this->createdRemoteGroups[$groupname]['shouldExist'];
			}
			return false;
		}

		throw new Exception(
			__METHOD__
			. " group '$groupname' was not created by this test run"
		);
	}

	/**
	 *
	 * @param string $groupname
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function theGroupShouldBeAbleToBeDeleted($groupname) {
		if (\array_key_exists($groupname, $this->createdGroups)) {
			return $this->createdGroups[$groupname]['possibleToDelete'] ?? true;
		}

		if (\array_key_exists($groupname, $this->createdRemoteGroups)) {
			return $this->createdRemoteGroups[$groupname]['possibleToDelete'] ?? true;
		}

		throw new Exception(
			__METHOD__
			. " group '$groupname' was not created by this test run"
		);
	}

	/**
	 * @When /^the administrator creates user "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function adminCreatesUserUsingTheProvisioningApi($user) {
		$this->createUser(
			$user, null, null, null, true, 'api'
		);
	}

	/**
	 * @Given /^user "([^"]*)" has been created with default attributes in the database user backend$/
	 *
	 * @param string $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function userHasBeenCreatedOnDatabaseBackend($user) {
		$this->adminCreatesUserUsingTheProvisioningApi($user);
		$this->userShouldExist($user);
	}

	/**
	 * @Given /^user "([^"]*)" has been created with default attributes and skeleton files$/
	 *
	 * @param string $user
	 * @param boolean $skeleton
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function userHasBeenCreatedWithDefaultAttributes($user, $skeleton = true) {
		$this->createUser($user, null, null, null, true, null, true, $skeleton);
		$this->userShouldExist($user);
	}

	/**
	 * @Given /^user "([^"]*)" has been created with default attributes and without skeleton files$/
	 *
	 * @param string $user
	 *
	 * @return void
	 * @throws Exception
	 */
	public function userHasBeenCreatedWithDefaultAttributesAndWithoutSkeletonFiles($user) {
		$baseUrl = $this->getBaseUrl();
		$path = $this->popSkeletonDirectoryConfig($baseUrl);
		try {
			$this->userHasBeenCreatedWithDefaultAttributes($user, false);
		} finally {
			// restore skeletondirectory even if user creation failed
			$this->runOcc(
				["config:system:set skeletondirectory --value $path"],
				null, null, $baseUrl
			);
		}
	}

	/**
	 * @Given these users have been created with default attributes and without skeleton files:
	 * expects a table of users with the heading
	 * "|username|"
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theseUsersHaveBeenCreatedWithDefaultAttributesAndWithoutSkeletonFiles(TableNode $table) {
		$baseUrl = $this->getBaseUrl();
		$path = $this->popSkeletonDirectoryConfig($baseUrl);
		try {
			$this->theseUsersHaveBeenCreated("default attributes and", "", $table);
		} finally {
			// restore skeletondirectory even if user creation failed
			$this->runOcc(
				["config:system:set skeletondirectory --value $path"],
				null, null, $baseUrl
			);
		}
	}

	/**
	 * @Given these users have been created without skeleton files:
	 * expects a table of users with the heading
	 * "|username|password|displayname|email|"
	 * password, displayname & email are optional
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theseUsersHaveBeenCreatedWithoutSkeletonFiles(TableNode $table) {
		$baseUrl = $this->getBaseUrl();
		$path = $this->popSkeletonDirectoryConfig($baseUrl);
		try {
			$this->theseUsersHaveBeenCreated("", "", $table);
		} finally {
			// restore skeletondirectory even if user creation failed
			$this->runOcc(
				["config:system:set skeletondirectory --value $path"],
				null, null, $baseUrl
			);
		}
	}

	/**
	 * @Given the administrator has set the system language to :defaultLanguage
	 *
	 * @param $defaultLanguage
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorHasSetTheSystemLanguageTo($defaultLanguage) {
		$this->runOcc(
			["config:system:set default_language --value $defaultLanguage"]
		);
	}

	/**
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function importLdifFile($path) {
		$ldifData = \file_get_contents($path);
		$this->importLdifData($ldifData);
	}

	/**
	 * imports an ldif string
	 *
	 * @param string $ldifData
	 *
	 * @return void
	 */
	public function importLdifData($ldifData) {
		$items = Zend\Ldap\Ldif\Encoder::decode($ldifData);
		if (isset($items['dn'])) {
			//only one item in the ldif data
			$this->ldap->add($items['dn'], $items);
		} else {
			foreach ($items as $item) {
				if (isset($item["objectclass"])) {
					if (\in_array("posixGroup", $item["objectclass"])) {
						\array_push($this->ldapCreatedGroups, $item["cn"][0]);
						$this->addGroupToCreatedGroupsList($item["cn"][0]);
					} elseif (\in_array("inetOrgPerson", $item["objectclass"])) {
						\array_push($this->ldapCreatedUsers, $item["uid"][0]);
						$this->addUserToCreatedUsersList($item["uid"][0], $item["userpassword"][0]);
					}
				}
				$this->ldap->add($item['dn'], $item);
			}
		}
	}

	/**
	 * @param $suiteParameters
	 *
	 * @return void
	 * @throws \Exception
	 * @throws \LdapException
	 */
	public function connectToLdap($suiteParameters) {
		$useSsl = false;
		if (OcisHelper::isTestingOnOcis()) {
			$this->ldapBaseDN = OcisHelper::getBaseDN();
			$this->ldapHost = OcisHelper::getHostname();
			$this->ldapPort = OcisHelper::getLdapPort();
			$useSsl = OcisHelper::useSsl();
			$this->ldapAdminUser = OcisHelper::getBindDN();
			if ($useSsl === true) {
				\putenv('LDAPTLS_REQCERT=never');
			}
		} else {
			$occResult = SetupHelper::runOcc(
				['ldap:show-config', 'LDAPTestId', '--output=json']
			);

			Assert::assertSame(
				'0', $occResult['code'],
				"could not read current LDAP config. stdOut: " .
				$occResult['stdOut'] .
				" stdErr: " . $occResult['stdErr']
			);

			$ldapConfig = \json_decode(
				$occResult['stdOut'], true
			);
			Assert::assertNotNull(
				$ldapConfig,
				"could not json decode current LDAP config. stdOut: " . $occResult['stdOut']
			);

			$this->ldapBaseDN = (string)$ldapConfig['ldapBase'][0];
			$this->ldapHost = (string)$ldapConfig['ldapHost'];
			$this->ldapPort = (string)$ldapConfig['ldapPort'];
			$this->ldapAdminUser = (string)$ldapConfig['ldapAgentName'];
		}
		$this->ldapAdminPassword = (string)$suiteParameters['ldapAdminPassword'];
		$this->ldapUsersOU = (string)$suiteParameters['ldapUsersOU'];
		$this->ldapGroupsOU = (string)$suiteParameters['ldapGroupsOU'];

		$options = [
			'host' => $this->ldapHost,
			'port' => $this->ldapPort,
			'password' => $this->ldapAdminPassword,
			'bindRequiresDn' => true,
			'useSsl' => $useSsl,
			'baseDn' => $this->ldapBaseDN,
			'username' => $this->ldapAdminUser
		];
		$this->ldap = new Ldap($options);
		$this->ldap->bind();
		$this->importLdifFile(
			__DIR__ . (string)$suiteParameters['ldapInitialUserFilePath']
		);
		$this->theLdapUsersHaveBeenResynced();
	}

	/**
	 * @Given the LDAP users have been resynced
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theLdapUsersHaveBeenReSynced() {
		if (!OcisHelper::isTestingOnOcis()) {
			$occResult = SetupHelper::runOcc(
				['user:sync', 'OCA\User_LDAP\User_Proxy', '-m', 'remove']
			);
			if ($occResult['code'] !== "0") {
				throw new \Exception(__METHOD__ . " could not sync LDAP users " . $occResult['stdErr']);
			}
		}
	}

	/**
	 * @When the LDAP users are resynced
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theLdapUsersAreReSynced() {
		SetupHelper::runOcc(
			['user:sync', 'OCA\User_LDAP\User_Proxy', '-m', 'remove']
		);
	}

	/**
	 * prepares suitable nested array with user-attributes for multiple users to be created
	 *
	 * @param boolean $setDefaultAttributes
	 * @param array $table
	 *
	 * @return array
	 */
	public function buildUsersAttributesArray($setDefaultAttributes, $table) {
		$usersAttributes = [];
		foreach ($table as $row) {
			$userAttribute['userid'] = $this->getActualUsername($row['username']);
			if (isset($row['displayname'])) {
				$userAttribute['displayName'] = $row['displayname'];
			} elseif ($setDefaultAttributes) {
				$userAttribute['displayName'] = $this->getDisplayNameForUser($row['username']);
				if ($userAttribute['displayName'] === null) {
					$userAttribute['displayName'] = $this->getDisplayNameForUser('regularuser');
				}
			} else {
				$userAttribute['displayName'] = null;
			}
			if (isset($row['email'])) {
				$userAttribute['email'] = $row['email'];
			} elseif ($setDefaultAttributes) {
				$userAttribute['email'] = $this->getEmailAddressForUser($row['username']);
				if ($userAttribute['email'] === null) {
					$userAttribute['email'] = $row['username'] . '@owncloud.org';
				}
			} else {
				$userAttribute['email'] = null;
			}

			if (isset($row['password'])) {
				$userAttribute['password'] = $this->getActualPassword($row['password']);
			} else {
				$userAttribute['password'] = $this->getPasswordForUser($row['username']);
			}
			// Add request body to the bodies array. we will use that later to loop through created users.
			\array_push($usersAttributes, $userAttribute);
		}
		return $usersAttributes;
	}

	/**
	 * creates a user in the ldap server
	 * the created user is added to `createdUsersList`
	 * ldap users are re-synced after creating a new user
	 *
	 * @param array $setting
	 *
	 * @return void
	 * @throws Exception
	 */
	public function createLdapUser($setting) {
		$ou = "TestUsers";
		// Some special characters need to be escaped in LDAP DN and attributes
		// The special characters allowed in a username (UID) are +_.@-
		// Of these, only + has to be escaped.
		$userId = \str_replace('+', '\+', $setting["userid"]);
		$newDN = 'uid=' . $userId . ',ou=' . $ou . ',' . 'dc=owncloud,dc=com';

		//pick a high number as uidnumber to make sure there are no conflicts with existing uidnumbers
		$uidNumber = \count($this->ldapCreatedUsers) + 30000;
		$entry = [];
		$entry['cn'] = $userId;
		$entry['sn'] = $userId;
		$entry['homeDirectory'] = '/home/openldap/' . $setting["userid"];
		$entry['objectclass'][] = 'posixAccount';
		$entry['objectclass'][] = 'inetOrgPerson';
		$entry['userPassword'] = $setting["password"];
		if (isset($setting["displayName"])) {
			$entry['displayName'] = $setting["displayName"];
		}
		if (isset($setting["email"])) {
			$entry['mail'] = $setting["email"];
		}
		$entry['gidNumber'] = 5000;
		$entry['uidNumber'] = $uidNumber;

		if ($this->federatedServerExists()) {
			if (!\in_array($setting['userid'], $this->ldapCreatedUsers)) {
				$this->ldap->add($newDN, $entry);
			}
		} else {
			$this->ldap->add($newDN, $entry);
		}
		\array_push($this->ldapCreatedUsers, $setting["userid"]);
		$this->theLdapUsersHaveBeenReSynced();
	}

	/**
	 * @param string $group group name
	 *
	 * @return void
	 * @throws Exception
	 * @throws LdapException
	 */
	public function createLdapGroup($group) {
		$baseDN = $this->getLdapBaseDN();
		$newDN = 'cn=' . $group . ',ou=' . $this->ou . ',' . $baseDN;
		$entry = [];
		$entry['cn'] = $group;
		$entry['objectclass'][] = 'posixGroup';
		$entry['objectclass'][] = 'top';
		$entry['gidNumber'] = 5000;
		$this->ldap->add($newDN, $entry);
		\array_push($this->ldapCreatedGroups, $group);
		// For syncing the ldap groups
		$this->runOcc(['group:list']);
	}

	/**
	 *
	 * @param string $configId
	 * @param string $configKey
	 * @param string $configValue
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function setLdapSetting($configId, $configKey, $configValue) {
		if ($configValue === "") {
			$configValue = "''";
		}
		$substitutions = [
			[
				"code" => "%ldap_host_without_scheme%",
				"function" => [
					$this,
					"getLdapHostWithoutScheme"
				],
				"parameter" => []
			],
			[
				"code" => "%ldap_host%",
				"function" => [
					$this,
					"getLdapHost"
				],
				"parameter" => []
			],
			[
				"code" => "%ldap_port%",
				"function" => [
					$this,
					"getLdapPort"
				],
				"parameter" => []
			]
		];
		$configValue = $this->substituteInLineCodes(
			$configValue, null, [], $substitutions
		);
		$occResult = SetupHelper::runOcc(
			['ldap:set-config', $configId, $configKey, $configValue]
		);
		if ($occResult['code'] !== "0") {
			throw new \Exception(
				__METHOD__ . " could not set LDAP setting " . $occResult['stdErr']
			);
		}
	}

	/**
	 * deletes LDAP users|groups created during test
	 *
	 * @return void
	 * @throws Exception
	 */
	public function deleteLdapUsersAndGroups() {
		//delete created ldap users
		$this->ldap->delete(
			"ou=" . $this->ldapUsersOU . "," . $this->ldapBaseDN, true
		);
		//delete all created ldap groups
		$this->ldap->delete(
			"ou=" . $this->ldapGroupsOU . "," . $this->ldapBaseDN, true
		);
		foreach ($this->ldapCreatedUsers as $user) {
			$this->rememberThatUserIsNotExpectedToExist($user);
		}
		foreach ($this->ldapCreatedGroups as $group) {
			$this->rememberThatGroupIsNotExpectedToExist($group);
		}
		$this->theLdapUsersHaveBeenResynced();
	}

	/**
	 * Sets back old settings
	 *
	 * @return void
	 * @throws Exception
	 */
	public function resetOldLdapConfig() {
		$toDeleteLdapConfig = $this->getToDeleteLdapConfigs();
		foreach ($toDeleteLdapConfig as $configId) {
			SetupHelper::runOcc(['ldap:delete-config', $configId]);
		}
		foreach ($this->oldLdapConfig as $configId => $settings) {
			foreach ($settings as $configKey => $configValue) {
				$this->setLdapSetting($configId, $configKey, $configValue);
			}
		}
		foreach ($this->toDeleteDNs as $dn) {
			$this->getLdap()->delete($dn, true);
		}
	}

	/**
	 * This function will allow us to send user creation requests in parallel.
	 * This will be faster in comparison to waiting for each request to complete before sending another request.
	 *
	 * @param boolean $initialize
	 * @param array $usersAttributes
	 * @param boolean $skeleton
	 *
	 * @return void
	 * @throws Exception
	 */
	public function usersHaveBeenCreated(
		$initialize, $usersAttributes, $skeleton = true
	) {
		$requests = [];
		$client = HttpRequestHelper::createClient(
			$this->getAdminUsername(),
			$this->getAdminPassword()
		);

		foreach ($usersAttributes as $userAttributes) {
			if ($this->isTestingWithLdap()) {
				$this->createLdapUser($userAttributes);
			} else {
				// Create a OCS request for creating the user. The request is not sent to the server yet.
				$request = OcsApiHelper::createOcsRequest(
					$this->getBaseUrl(),
					'POST',
					"/cloud/users",
					$userAttributes
				);
				// Add the request to the $requests array so that they can be sent in parallel.
				\array_push($requests, $request);
			}
		}

		if (!$this->isTestingWithLdap()) {
			$results = HttpRequestHelper::sendBatchRequest($requests, $client);
			// Retrieve all failures.
			foreach ($results as $e) {
				if ($e instanceof ClientException) {
					$responseXml = $this->getResponseXml($e->getResponse());
					$messageText = (string) $responseXml->xpath("/ocs/meta/message")[0];
					$ocsStatusCode = (string) $responseXml->xpath("/ocs/meta/statuscode")[0];
					$httpStatusCode = $e->getResponse()->getStatusCode();
					$reasonPhrase = $e->getResponse()->getReasonPhrase();
					throw new Exception(
						__METHOD__ . "Unexpected failure when creating a user: HTTP status $httpStatusCode HTTP reason $reasonPhrase OCS status $ocsStatusCode OCS message $messageText"
					);
				}
			}
		}

		// Create requests for setting displayname and email for the newly created users.
		// These values cannot be set while creating the user, so we have to edit the newly created user to set these values.
		$users = [];
		$editData = [];
		foreach ($usersAttributes as $userAttributes) {
			\array_push($users, $userAttributes['userid']);
			$this->addUserToCreatedUsersList($userAttributes['userid'], $userAttributes['password'], $userAttributes['displayName'], $userAttributes['email']);
			if (isset($userAttributes['displayName'])) {
				\array_push($editData, ['user' => $userAttributes['userid'], 'key' => 'displayname', 'value' => $userAttributes['displayName']]);
			}
			if (isset($userAttributes['email'])) {
				\array_push($editData, ['user' => $userAttributes['userid'], 'key' => 'email', 'value' => $userAttributes['email']]);
			}
		}
		// Edit the users in parallel to make the process faster.
		if (!$this->isTestingWithLdap() && \count($editData) > 0) {
			UserHelper::editUserBatch(
				$this->getBaseUrl(),
				$editData,
				$this->getAdminUsername(),
				$this->getAdminPassword()
			);
		}

		// If the user should have skeleton files, and we are testing on OCIS
		// then do some work to "manually" put the skeleton files in place.
		// When testing on ownCloud 10 the user is already getting whatever
		// skeleton dir is defined in the server-under-test.
		if ($skeleton && OcisHelper::isTestingOnOcis()) {
			$skeletonDir = \getenv("SKELETON_DIR");
			$revaRoot = \getenv("OCIS_REVA_DATA_ROOT");
			if (!$skeletonDir) {
				throw new Exception('Missing SKELETON_DIR environment variable, cannot copy skeleton files for OCIS');
			}
			if (!$revaRoot && OcisHelper::getDeleteUserDataCommand() !== false) {
				foreach ($usersAttributes as $userAttributes) {
					OcisHelper::recurseUpload(
						$this->getBaseUrl(),
						$skeletonDir,
						$userAttributes['userid'],
						$userAttributes['password']
					);
				}
			} elseif (!$revaRoot) {
				throw new Exception('Missing OCIS_REVA_DATA_ROOT environment variable, cannot copy skeleton files for OCIS');
			} else {
				foreach ($usersAttributes as $userAttributes) {
					$user = $userAttributes['userid'];
					$dataDir = $revaRoot . "data/$user/files";
					if (!\file_exists($dataDir)) {
						\mkdir($dataDir, 0777, true);
					}
					OcisHelper::recurseCopy($skeletonDir, $dataDir);
				}
			}
		}

		if ($initialize) {
			// We need to initialize each user using the individual authentication of each user.
			// That is not possible in Guzzle6 batch mode. So we do it with normal requests in serial.
			$this->initializeUsers($users);
		}
	}

	/**
	 * @When /^the administrator creates these users with ?(default attributes and|) skeleton files ?(but not initialized|):$/
	 *
	 * expects a table of users with the heading
	 * "|username|password|displayname|email|"
	 * password, displayname & email are optional
	 *
	 * @param string $setDefaultAttributes
	 * @param string $doNotInitialize
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theAdministratorCreatesTheseUsers($setDefaultAttributes, $doNotInitialize, TableNode $table) {
		$this->verifyTableNodeColumns($table, ['username'], ['displayname', 'email', 'password']);
		$table = $table->getColumnsHash();
		$setDefaultAttributes = $setDefaultAttributes !== "";
		$initialize = $doNotInitialize === "";
		$usersAttributes = $this->buildUsersAttributesArray($setDefaultAttributes, $table);
		$this->usersHaveBeenCreated(
			$initialize,
			$usersAttributes
		);
	}

	/**
	 * @Given /^these users have been created with ?(default attributes and|) skeleton files ?(but not initialized|):$/
	 *
	 * expects a table of users with the heading
	 * "|username|password|displayname|email|"
	 * password, displayname & email are optional
	 *
	 * @param string $setDefaultAttributes
	 * @param string $doNotInitialize
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theseUsersHaveBeenCreated($setDefaultAttributes, $doNotInitialize, TableNode $table) {
		$this->verifyTableNodeColumns($table, ['username'], ['displayname', 'email', 'password']);
		$table = $table->getColumnsHash();
		$setDefaultAttributes = $setDefaultAttributes !== "";
		$initialize = $doNotInitialize === "";
		$usersAttributes = $this->buildUsersAttributesArray($setDefaultAttributes, $table);
		$this->usersHaveBeenCreated(
			$initialize,
			$usersAttributes
		);
		foreach ($usersAttributes as $expectedUser) {
			$this->userShouldExist($expectedUser["userid"]);
		}
	}

	/**
	 * @When the administrator changes the password of user :user to :password using the provisioning API
	 *
	 * @param string $user
	 * @param string $password
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function adminChangesPasswordOfUserToUsingTheProvisioningApi(
		$user, $password
	) {
		$this->response = UserHelper::editUser(
			$this->getBaseUrl(),
			$user,
			'password',
			$password,
			$this->getAdminUsername(),
			$this->getAdminPassword()
		);
	}

	/**
	 * @Given the administrator has changed the password of user :user to :password
	 *
	 * @param string $user
	 * @param string $password
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function adminHasChangedPasswordOfUserTo(
		$user, $password
	) {
		$this->adminChangesPasswordOfUserToUsingTheProvisioningApi(
			$user, $password
		);
		$this->theHTTPStatusCodeShouldBe(
			200,
			"could not change password of user $user"
		);
	}

	/**
	 * @When /^user "([^"]*)" (enables|disables) app "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $action enables or disables
	 * @param string $app
	 *
	 * @return void
	 */
	public function userEnablesOrDisablesApp($user, $action, $app) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/apps/$app";
		if ($action === 'enables') {
			$this->response = HttpRequestHelper::post(
				$fullUrl, $user, $this->getPasswordForUser($user)
			);
		} else {
			$this->response = HttpRequestHelper::delete(
				$fullUrl, $user, $this->getPasswordForUser($user)
			);
		}
	}

	/**
	 * @When /^the administrator (enables|disables) app "([^"]*)"$/
	 *
	 * @param string $action enables or disables
	 * @param string $app
	 *
	 * @return void
	 */
	public function adminEnablesOrDisablesApp($action, $app) {
		$this->userEnablesOrDisablesApp(
			$this->getAdminUsername(), $action, $app
		);
	}

	/**
	 * @Given /^app "([^"]*)" has been (enabled|disabled)$/
	 *
	 * @param string $app
	 * @param string $action enabled or disabled
	 *
	 * @return void
	 */
	public function appHasBeenDisabled($app, $action) {
		if ($action === 'enabled') {
			$action = 'enables';
		} else {
			$action = 'disables';
		}
		$this->userEnablesOrDisablesApp(
			$this->getAdminUsername(), $action, $app
		);
	}

	/**
	 * @When the administrator gets the info of app :app
	 *
	 * @param string $app
	 *
	 * @return void
	 */
	public function theAdministratorGetsTheInfoOfApp($app) {
		$this->ocsContext->userSendsToOcsApiEndpoint(
			$this->getAdminUsername(),
			"GET",
			"/cloud/apps/$app"
		);
	}

	/**
	 * @When the administrator gets all enabled apps using the provisioning API
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllEnabledAppsUsingTheProvisioningApi() {
		$this->getEnabledApps();
	}

	/**
	 * @When the administrator sends a user creation request with the following attributes using the provisioning API:
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminSendsUserCreationRequestWithFollowingAttributesUsingTheProvisioningApi(TableNode $table) {
		$this->verifyTableNodeRows($table, ["username", "password"], ["email", "displayname"]);
		$table = $table->getRowsHash();
		$username = $this->getActualUsername($table["username"]);
		$password = $this->getActualPassword($table["password"]);
		$displayname = \array_key_exists("displayname", $table) ? $table["displayname"] : null;
		$email = \array_key_exists("email", $table) ? $table["email"] : null;
		$userAttributes = [
			"userid" => $username,
			"password" => $password,
			"displayname" => $displayname,
			"email" => $email
		];
		$this->ocsContext->userSendsHTTPMethodToOcsApiEndpointWithBody(
			$this->getAdminUsername(),
			"POST",
			"/cloud/users",
			$userAttributes
		);
		$this->addUserToCreatedUsersList(
			$username, $password, $displayname, $email
		);
	}

	/**
	 * @When /^the administrator sends a user creation request for user "([^"]*)" password "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $password
	 *
	 * @return void
	 */
	public function adminSendsUserCreationRequestUsingTheProvisioningApi($user, $password) {
		$user = $this->getActualUsername($user);
		$password = $this->getActualPassword($password);
		$bodyTable = new TableNode([['userid', $user], ['password', $password]]);
		$this->ocsContext->userSendsHTTPMethodToOcsApiEndpointWithBody(
			$this->getAdminUsername(),
			"POST",
			"/cloud/users",
			$bodyTable
		);
		$this->addUserToCreatedUsersList($user, $password);
	}

	/**
	 * @When /^the administrator sends a user creation request for user "([^"]*)" password "([^"]*)" group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorCreatesUserPasswordGroupUsingTheProvisioningApi(
		$user, $password, $group
	) {
		$user = $this->getActualUsername($user);
		$password = $this->getActualPassword($password);
		$bodyTable = new TableNode(
			[['userid', $user], ['password', $password], ['groups[]', $group]]
		);
		$this->ocsContext->userSendsHTTPMethodToOcsApiEndpointWithBody(
			$this->getAdminUsername(),
			"POST",
			"/cloud/users",
			$bodyTable
		);
		$this->addUserToCreatedUsersList($user, $password);
	}

	/**
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 */
	public function resetUserPasswordAsAdminUsingTheProvisioningApi($username, $password) {
		$this->userResetUserPasswordUsingProvisioningApi(
			$this->getAdminUsername(),
			$username,
			$password
		);
	}

	/**
	 * @When the administrator resets the password of user :username to :password using the provisioning API
	 *
	 * @param string $username of the user whose password is reset
	 * @param string $password
	 *
	 * @return void
	 */
	public function adminResetsPasswordOfUserUsingTheProvisioningApi($username, $password) {
		$this->resetUserPasswordAsAdminUsingTheProvisioningApi(
			$username,
			$password
		);
	}

	/**
	 * @Given the administrator has reset the password of user :username to :password
	 *
	 * @param string $username of the user whose password is reset
	 * @param string $password
	 *
	 * @return void
	 */
	public function adminHasResetPasswordOfUserUsingTheProvisioningApi($username, $password) {
		$this->resetUserPasswordAsAdminUsingTheProvisioningApi(
			$username,
			$password
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @param string $user
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 */
	public function userResetUserPasswordUsingProvisioningApi($user, $username, $password) {
		$password = $this->getActualPassword($password);
		$this->userTriesToResetUserPasswordUsingTheProvisioningApi(
			$user, $username, $password
		);
		$this->rememberUserPassword($username, $password);
	}

	/**
	 * @When user :user resets the password of user :username to :password using the provisioning API
	 *
	 * @param string $user that does the password reset
	 * @param string $username of the user whose password is reset
	 * @param string $password
	 *
	 * @return void
	 */
	public function userResetsPasswordOfUserUsingTheProvisioningApi($user, $username, $password) {
		$this->userResetUserPasswordUsingProvisioningApi(
			$user,
			$username,
			$password
		);
	}

	/**
	 * @Given user :user has reset the password of user :username to :password
	 *
	 * @param string $user that does the password reset
	 * @param string $username of the user whose password is reset
	 * @param string $password
	 *
	 * @return void
	 */
	public function userHasResetPasswordOfUserUsingTheProvisioningApi($user, $username, $password) {
		$this->userResetUserPasswordUsingProvisioningApi(
			$user, $username, $password
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @param string $user
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 */
	public function userTriesToResetUserPasswordUsingTheProvisioningApi($user, $username, $password) {
		$password = $this->getActualPassword($password);
		$bodyTable = new TableNode([['key', 'password'], ['value', $password]]);
		$this->ocsContext->userSendsHTTPMethodToOcsApiEndpointWithBody(
			$user,
			"PUT",
			"/cloud/users/$username",
			$bodyTable
		);
	}

	/**
	 * @When user :user tries to reset the password of user :username to :password using the provisioning API
	 *
	 * @param string $user that does the password reset
	 * @param string $username of the user whose password is reset
	 * @param string $password
	 *
	 * @return void
	 */
	public function userTriesToResetPasswordOfUserUsingTheProvisioningApi($user, $username, $password) {
		$this->userTriesToResetUserPasswordUsingTheProvisioningApi(
			$user,
			$username,
			$password
		);
	}

	/**
	 * @Given user :user has tried to reset the password of user :username to :password
	 *
	 * @param string $user that does the password reset
	 * @param string $username of the user whose password is reset
	 * @param string $password
	 *
	 * @return void
	 */
	public function userHasTriedToResetPasswordOfUserUsingTheProvisioningApi($user, $username, $password) {
		$this->userTriesToResetUserPasswordUsingTheProvisioningApi(
			$user,
			$username,
			$password
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When /^the administrator deletes user "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theAdminDeletesUserUsingTheProvisioningApi($user) {
		$user = $this->getActualUsername($user);
		$this->deleteTheUserUsingTheProvisioningApi($user);
		$this->rememberThatUserIsNotExpectedToExist($user);
	}

	/**
	 * @When user :user deletes user :anotheruser using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 *
	 * @return void
	 */
	public function userDeletesUserUsingTheProvisioningApi(
		$user, $anotheruser
	) {
		$user = $this->getActualUsername($user);
		$anotheruser = $this->getActualUsername($anotheruser);

		$this->response = UserHelper::deleteUser(
			$this->getBaseUrl(),
			$anotheruser,
			$user,
			$this->getUserPassword($user),
			$this->ocsApiVersion
		);
	}

	/**
	 * @When /^the administrator changes the email of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $email
	 *
	 * @return void
	 */
	public function adminChangesTheEmailOfUserToUsingTheProvisioningApi(
		$user, $email
	) {
		$user = $this->getActualUsername($user);
		$this->response = UserHelper::editUser(
			$this->getBaseUrl(),
			$user,
			'email',
			$email,
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			$this->ocsApiVersion
		);
		$this->rememberUserEmailAddress($user, $email);
	}

	/**
	 * @Given /^the administrator has changed the email of user "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $email
	 *
	 * @return void
	 */
	public function adminHasChangedTheEmailOfUserTo($user, $email) {
		$this->adminChangesTheEmailOfUserToUsingTheProvisioningApi(
			$user, $email
		);
		$this->theHTTPStatusCodeShouldBe(
			200,
			"could not change email of user $user"
		);
	}

	/**
	 * @Given the administrator has changed their own email address to :email
	 *
	 * @param string $email
	 *
	 * @return void
	 */
	public function theAdministratorHasChangedTheirOwnEmailAddressTo($email) {
		$admin = $this->getAdminUsername();
		$this->adminHasChangedTheEmailOfUserTo($admin, $email);
	}

	/**
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $email
	 *
	 * @return void
	 */
	public function userChangesUserEmailUsingProvisioningApi(
		$requestingUser, $targetUser, $email
	) {
		$this->response = UserHelper::editUser(
			$this->getBaseUrl(),
			$this->getActualUsername($targetUser),
			'email',
			$email,
			$this->getActualUsername($requestingUser),
			$this->getPasswordForUser($requestingUser),
			$this->ocsApiVersion
		);
	}

	/**
	 * @When /^user "([^"]*)" changes the email of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $email
	 *
	 * @return void
	 */
	public function userChangesTheEmailOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $email
	) {
		$requestingUser = $this->getActualUsername($requestingUser);
		$targetUser = $this->getActualUsername($targetUser);
		$this->userChangesUserEmailUsingProvisioningApi(
			$requestingUser,
			$targetUser,
			$email
		);
		$this->rememberUserEmailAddress($targetUser, $email);
	}

	/**
	 * @Given /^user "([^"]*)" has changed the email of user "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $email
	 *
	 * @return void
	 */
	public function userHasChangedTheEmailOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $email
	) {
		$requestingUser = $this->getActualUsername($requestingUser);
		$targetUser = $this->getActualUsername($targetUser);
		$this->userChangesUserEmailUsingProvisioningApi(
			$requestingUser,
			$targetUser,
			$email
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
		$this->rememberUserEmailAddress($targetUser, $email);
	}

	/**
	 * Edit the "display name" of a user by sending the key "displayname" to the API end point.
	 *
	 * This is the newer and consistent key name.
	 *
	 * @see https://github.com/owncloud/core/pull/33040
	 *
	 * @When /^the administrator changes the display name of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminChangesTheDisplayNameOfUserUsingTheProvisioningApi(
		$user, $displayName
	) {
		$user = $this->getActualUsername($user);
		$this->adminChangesTheDisplayNameOfUserUsingKey(
			$user, 'displayname', $displayName
		);
		$this->rememberUserDisplayName($user, $displayName);
	}

	/**
	 * @Given /^the administrator has changed the display name of user "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminHasChangedTheDisplayNameOfUser(
		$user, $displayName
	) {
		$user = $this->getActualUsername($user);
		if ($this->isTestingWithLdap()) {
			$this->editLdapUserDisplayName(
				$user, $displayName
			);
		} else {
			$this->adminChangesTheDisplayNameOfUserUsingKey(
				$user, 'displayname', $displayName
			);
		}
		$response = UserHelper::getUser(
			$this->getBaseUrl(),
			$user,
			$this->getAdminUsername(),
			$this->getAdminPassword()
		);
		$this->setResponse($response);
		$this->theDisplayNameReturnedByTheApiShouldBe($displayName);
		$this->rememberUserDisplayName($user, $displayName);
	}

	/**
	 * As the administrator, edit the "display name" of a user by sending the key "display" to the API end point.
	 *
	 * This is the older and inconsistent key name, which remains for backward-compatibility.
	 *
	 * @see https://github.com/owncloud/core/pull/33040
	 *
	 * @When /^the administrator changes the display of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminChangesTheDisplayOfUserUsingTheProvisioningApi(
		$user, $displayName
	) {
		$user = $this->getActualUsername($user);
		$this->adminChangesTheDisplayNameOfUserUsingKey(
			$user, 'display', $displayName
		);
		$this->rememberUserDisplayName($user, $displayName);
	}

	/**
	 *
	 * @param string $user
	 * @param string $key
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminChangesTheDisplayNameOfUserUsingKey(
		$user, $key, $displayName
	) {
		$result = UserHelper::editUser(
			$this->getBaseUrl(),
			$this->getActualUsername($user),
			$key,
			$displayName,
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			$this->ocsApiVersion
		);
		$this->response = $result;
		if ($result->getStatusCode() !== 200) {
			throw new \Exception(
				__METHOD__ . " could not change display name of user using key $key "
				. $result->getStatusCode() . " " . $result->getBody()
			);
		}
	}

	/**
	 * As a user, edit the "display name" of a user by sending the key "displayname" to the API end point.
	 *
	 * This is the newer and consistent key name.
	 *
	 * @see https://github.com/owncloud/core/pull/33040
	 *
	 * @When /^user "([^"]*)" changes the display name of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $displayName
	 *
	 * @return void
	 */
	public function userChangesTheDisplayNameOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $displayName
	) {
		$requestingUser = $this->getActualUsername($requestingUser);
		$targetUser = $this->getActualUsername($targetUser);
		$this->userChangesTheDisplayNameOfUserUsingKey(
			$requestingUser, $targetUser, 'displayname', $displayName
		);
		$this->rememberUserDisplayName($targetUser, $displayName);
	}

	/**
	 * As a user, edit the "display name" of a user by sending the key "display" to the API end point.
	 *
	 * This is the older and inconsistent key name.
	 *
	 * @see https://github.com/owncloud/core/pull/33040
	 *
	 * @When /^user "([^"]*)" changes the display of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $displayName
	 *
	 * @return void
	 */
	public function userChangesTheDisplayOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $displayName
	) {
		$requestingUser = $this->getActualUsername($requestingUser);
		$targetUser = $this->getActualUsername($targetUser);
		$this->userChangesTheDisplayNameOfUserUsingKey(
			$requestingUser, $targetUser, 'display', $displayName
		);
		$this->rememberUserDisplayName($targetUser, $displayName);
	}

	/**
	 * @Given /^user "([^"]*)" has changed the display name of user "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $displayName
	 *
	 * @return void
	 */
	public function userHasChangedTheDisplayNameOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $displayName
	) {
		$requestingUser = $this->getActualUsername($requestingUser);
		$targetUser = $this->getActualUsername($targetUser);
		$this->userChangesTheDisplayNameOfUserUsingKey(
			$requestingUser, $targetUser, 'displayname', $displayName
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
		$this->rememberUserDisplayName($targetUser, $displayName);
	}
	/**
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $key
	 * @param string $displayName
	 *
	 * @return void
	 */
	public function userChangesTheDisplayNameOfUserUsingKey(
		$requestingUser, $targetUser, $key, $displayName
	) {
		$result = UserHelper::editUser(
			$this->getBaseUrl(),
			$this->getActualUsername($targetUser),
			$key,
			$displayName,
			$this->getActualUsername($requestingUser),
			$this->getPasswordForUser($requestingUser),
			$this->ocsApiVersion
		);
		$this->response = $result;
	}

	/**
	 * @When /^the administrator changes the quota of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $quota
	 *
	 * @return void
	 */
	public function adminChangesTheQuotaOfUserUsingTheProvisioningApi(
		$user, $quota
	) {
		$result = UserHelper::editUser(
			$this->getBaseUrl(),
			$this->getActualUsername($user),
			'quota',
			$quota,
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			$this->ocsApiVersion
		);
		$this->response = $result;
	}

	/**
	 * @Given /^the administrator has (?:changed|set) the quota of user "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $quota
	 *
	 * @return void
	 */
	public function adminHasChangedTheQuotaOfUserTo(
		$user, $quota
	) {
		$user = $this->getActualUsername($user);
		$this->adminChangesTheQuotaOfUserUsingTheProvisioningApi(
			$user, $quota
		);
		$this->theHTTPStatusCodeShouldBe(
			200,
			"could not change quota of user $user"
		);
	}

	/**
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $quota
	 *
	 * @return void
	 */
	public function userChangeQuotaOfUserUsingProvisioningApi(
		$requestingUser, $targetUser, $quota
	) {
		$result = UserHelper::editUser(
			$this->getBaseUrl(),
			$this->getActualUsername($targetUser),
			'quota',
			$quota,
			$this->getActualUsername($requestingUser),
			$this->getPasswordForUser($requestingUser),
			$this->ocsApiVersion
		);
		$this->response = $result;
	}

	/**
	 * @When /^user "([^"]*)" changes the quota of user "([^"]*)" to "([^"]*)" using the provisioning API$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $quota
	 *
	 * @return void
	 */
	public function userChangesTheQuotaOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $quota
	) {
		$this->userChangeQuotaOfUserUsingProvisioningApi(
			$requestingUser,
			$targetUser,
			$quota
		);
	}

	/**
	 * @Given /^user "([^"]*)" has changed the quota of user "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 * @param string $quota
	 *
	 * @return void
	 */
	public function userHasChangedTheQuotaOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser, $quota
	) {
		$this->userChangeQuotaOfUserUsingProvisioningApi(
			$requestingUser,
			$targetUser,
			$quota
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @param string $user
	 *
	 * @return void
	 */
	public function retrieveUserInformationAsAdminUsingProvisioningApi(
		$user
	) {
		$result = UserHelper::getUser(
			$this->getBaseUrl(),
			$this->getActualUsername($user),
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			$this->ocsApiVersion
		);
		$this->response = $result;
	}

	/**
	 * @When /^the administrator retrieves the information of user "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function adminRetrievesTheInformationOfUserUsingTheProvisioningApi(
		$user
	) {
		$user = $this->getActualUsername($user);
		$this->retrieveUserInformationAsAdminUsingProvisioningApi(
			$user
		);
	}

	/**
	 * @Given /^the administrator has retrieved the information of user "([^"]*)"$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function adminHasRetrievedTheInformationOfUserUsingTheProvisioningApi(
		$user
	) {
		$this->retrieveUserInformationAsAdminUsingProvisioningApi($user);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @param string $requestingUser
	 * @param string $targetUser
	 *
	 * @return void
	 */
	public function userRetrieveUserInformationUsingProvisioningApi(
		$requestingUser, $targetUser
	) {
		$result = UserHelper::getUser(
			$this->getBaseUrl(),
			$this->getActualUsername($targetUser),
			$this->getActualUsername($requestingUser),
			$this->getPasswordForUser($requestingUser),
			$this->ocsApiVersion
		);
		$this->response = $result;
	}

	/**
	 * @When /^user "([^"]*)" retrieves the information of user "([^"]*)" using the provisioning API$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 *
	 * @return void
	 */
	public function userRetrievesTheInformationOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser
	) {
		$this->userRetrieveUserInformationUsingProvisioningApi(
			$requestingUser,
			$targetUser
		);
	}

	/**
	 * @Given /^user "([^"]*)" has retrieved the information of user "([^"]*)"$/
	 *
	 * @param string $requestingUser
	 * @param string $targetUser
	 *
	 * @return void
	 */
	public function userHasRetrievedTheInformationOfUserUsingTheProvisioningApi(
		$requestingUser, $targetUser
	) {
		$this->userRetrieveUserInformationUsingProvisioningApi(
			$requestingUser,
			$targetUser
		);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @Then /^user "([^"]*)" should exist$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userShouldExist($user) {
		$user = $this->getActualUsername($user);
		Assert::assertTrue(
			$this->userExists($user),
			"User '$user' should exist but does not exist"
		);
	}

	/**
	 * @Then /^user "([^"]*)" should not exist$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userShouldNotExist($user) {
		$user = $this->getActualUsername($user);
		Assert::assertFalse(
			$this->userExists($user),
			"User '$user' should not exist but does exist"
		);
		$this->rememberThatUserIsNotExpectedToExist($user);
	}

	/**
	 * @Then /^group "([^"]*)" should exist$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws Exception
	 */
	public function groupShouldExist($group) {
		Assert::assertTrue(
			$this->groupExists($group),
			"Group '$group' should exist but does not exist"
		);
	}

	/**
	 * @Then /^group "([^"]*)" should not exist$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws Exception
	 */
	public function groupShouldNotExist($group) {
		Assert::assertFalse(
			$this->groupExists($group),
			"Group '$group' should not exist but does exist"
		);
	}

	/**
	 * @Then /^these groups should (not|)\s?exist:$/
	 * expects a table of groups with the heading "groupname"
	 *
	 * @param string $shouldOrNot (not|)
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theseGroupsShouldNotExist($shouldOrNot, TableNode $table) {
		$should = ($shouldOrNot !== "not");
		$groups = SetupHelper::getGroups();
		$this->verifyTableNodeColumns($table, ['groupname']);
		foreach ($table as $row) {
			if (\in_array($row['groupname'], $groups, true) !== $should) {
				throw new Exception(
					"group '" . $row['groupname'] .
					"' does" . ($should ? " not" : "") .
					" exist but should" . ($should ? "" : " not")
				);
			}
		}
	}

	/**
	 * @Given /^user "([^"]*)" has been deleted$/
	 *
	 * @param string $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function userHasBeenDeleted($user) {
		$user = $this->getActualUsername($user);
		if ($this->userExists($user)) {
			if ($this->isTestingWithLdap() && \in_array($user, $this->ldapCreatedUsers)) {
				$this->deleteLdapUser($user);
			} else {
				$this->deleteTheUserUsingTheProvisioningApi($user);
			}
		}
		$this->userShouldNotExist($user);
	}

	/**
	 * @Given these users have been initialized:
	 * expects a table of users with the heading
	 * "|username|password|"
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function theseUsersHaveBeenInitialized(TableNode $table) {
		foreach ($table as $row) {
			if (!isset($row ['password'])) {
				$password = $this->getPasswordForUser($row ['username']);
			} else {
				$password = $row ['password'];
			}
			$this->initializeUser(
				$row ['username'],
				$password
			);
		}
	}

	/**
	 * @When the administrator gets all the members of group :group using the provisioning API
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllTheMembersOfGroupUsingTheProvisioningApi($group) {
		$this->userGetsAllTheMembersOfGroupUsingTheProvisioningApi(
			$this->getAdminUsername(), $group
		);
	}

	/**
	 * @When /^user "([^"]*)" gets all the members of group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userGetsAllTheMembersOfGroupUsingTheProvisioningApi($user, $group) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v{$this->ocsApiVersion}.php/cloud/groups/$group";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getActualUsername($user), $this->getPasswordForUser($user)
		);
	}

	/**
	 * @When the administrator gets all the groups using the provisioning API
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllTheGroupsUsingTheProvisioningApi() {
		$fullUrl = $this->getBaseUrl() . "/ocs/v{$this->ocsApiVersion}.php/cloud/groups";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
	}

	/**
	 * @When the administrator gets all the groups of user :user using the provisioning API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllTheGroupsOfUser($user) {
		$this->userGetsAllTheGroupsOfUser($this->getAdminUsername(), $user);
	}

	/**
	 * @When user :user gets all the groups of user :anotheruser using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 *
	 * @return void
	 */
	public function userGetsAllTheGroupsOfUser($user, $anotheruser) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$anotheruser/groups";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getActualUsername($user), $this->getUserPassword($user)
		);
	}

	/**
	 * @When the administrator gets the list of all users using the provisioning API
	 *
	 * @return void
	 */
	public function theAdministratorGetsTheListOfAllUsersUsingTheProvisioningApi() {
		$this->userGetsTheListOfAllUsersUsingTheProvisioningApi($this->getAdminUsername());
	}

	/**
	 * @When user :user gets the list of all users using the provisioning API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userGetsTheListOfAllUsersUsingTheProvisioningApi($user) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v{$this->ocsApiVersion}.php/cloud/users";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getActualUsername($user), $this->getUserPassword($user)
		);
	}

	/**
	 * Make a request about the user. That will force the server to fully
	 * initialize the user, including their skeleton files.
	 *
	 * @param string $user
	 * @param string $password
	 *
	 * @return void
	 */
	public function initializeUser($user, $password) {
		$url = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$user";
		HttpRequestHelper::get($url, $user, $password);
		$this->lastUploadTime = \time();
	}

	/**
	 * Touch an API end-point for each user so that their file-system gets setup
	 *
	 * @param array $users
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function initializeUsers($users) {
		$url = "/cloud/users/%s";
		foreach ($users as $user) {
			$response = OcsApiHelper::sendRequest(
				$this->getBaseUrl(),
				$user,
				$this->getPasswordForUser($user),
				'GET',
				\sprintf($url, $user)
			);
			$this->setResponse($response);
			$this->theHTTPStatusCodeShouldBe(200);
		}
	}

	/**
	 * adds a user to the list of users that were created during test runs
	 * makes it possible to use this list in other test steps
	 * or to delete them at the end of the test
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $displayName
	 * @param string $email
	 * @param bool $shouldExist
	 *
	 * @return void
	 */
	public function addUserToCreatedUsersList(
		$user, $password, $displayName = null, $email = null, $shouldExist = true
	) {
		$user = $this->getActualUsername($user);
		$user = $this->normalizeUsername($user);
		$userData = [
			"password" => $password,
			"displayname" => $displayName,
			"email" => $email,
			"shouldExist" => $shouldExist
		];

		if ($this->currentServer === 'LOCAL') {
			// Only remember this user creation if it was expected to have been successful
			// or the user has not been processed before. Some tests create a user the
			// first time (successfully) and then purposely try to create the user again.
			// The 2nd user creation is expected to fail, and in that case we want to
			// still remember the details of the first user creation.
			if ($shouldExist || !\array_key_exists($user, $this->createdUsers)) {
				$this->createdUsers[$user] = $userData;
			}
		} elseif ($this->currentServer === 'REMOTE') {
			// See comment above about the LOCAL case. The logic is the same for the remote case.
			if ($shouldExist || !\array_key_exists($user, $this->createdRemoteUsers)) {
				$this->createdRemoteUsers[$user] = $userData;
			}
		}
	}

	/**
	 * remember the password of a user that already exists so that you can use
	 * ordinary test steps after changing their password.
	 *
	 * @param string $user
	 * @param string $password
	 *
	 * @return void
	 */
	public function rememberUserPassword(
		$user, $password
	) {
		$user = $this->normalizeUsername($user);
		if ($this->currentServer === 'LOCAL') {
			if (\array_key_exists($user, $this->createdUsers)) {
				$this->createdUsers[$user]['password'] = $password;
			}
		} elseif ($this->currentServer === 'REMOTE') {
			if (\array_key_exists($user, $this->createdRemoteUsers)) {
				$this->createdRemoteUsers[$user]['password'] = $password;
			}
		}
	}

	/**
	 * Remembers that a user from the list of users that were created during
	 * test runs is no longer expected to exist. Useful if a user was created
	 * during the setup phase but was deleted in a test run. We don't expect
	 * this user to exist in the tear-down phase, so remember that fact.
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function rememberThatUserIsNotExpectedToExist($user) {
		$user = $this->getActualUsername($user);
		$user = $this->normalizeUsername($user);
		if (\array_key_exists($user, $this->createdUsers)) {
			$this->createdUsers[$user]['shouldExist'] = false;
		}
	}

	/**
	 * creates a single user
	 *
	 * @param string $user
	 * @param string|null $password if null, then select a password
	 * @param string|null $displayName
	 * @param string|null $email
	 * @param bool $initialize initialize the user skeleton files etc
	 * @param string|null $method how to create the user api|occ, default api
	 * @param bool $setDefault sets the missing values to some default
	 * @param bool $skeleton
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function createUser(
		$user,
		$password = null,
		$displayName = null,
		$email = null,
		$initialize = true,
		$method = null,
		$setDefault = true,
		$skeleton = null
	) {
		if ($password === null) {
			$password = $this->getPasswordForUser($user);
		}

		if ($displayName === null && $setDefault === true) {
			$displayName = $this->getDisplayNameForUser($user);
			if ($displayName === null) {
				$displayName = $this->getDisplayNameForUser('regularuser');
			}
		}

		if ($email === null && $setDefault === true) {
			$email = $this->getEmailAddressForUser($user);

			if ($email === null) {
				// escape @ & space if present in userId
				$email = \str_replace(["@", " "], "", $user) . '@owncloud.org';
			}
		}

		$user = $this->getActualUsername($user);

		if ($method === null && $this->isTestingWithLdap()) {
			//guess yourself
			$method = "ldap";
		} elseif ($method === null) {
			$method = "api";
		}
		$user = \trim($user);
		$method = \trim(\strtolower($method));
		switch ($method) {
			case "api":
				$results = UserHelper::createUser(
					$this->getBaseUrl(),
					$user,
					$password,
					$this->getAdminUsername(),
					$this->getAdminPassword(),
					$displayName, $email
				);
				foreach ($results as $result) {
					if ($result->getStatusCode() !== 200) {
						$message = $this->getResponseXml($result)->xpath("/ocs/meta/message");
						if ($message && (string) $message[0] === "User already exists") {
							Assert::fail(
								'Could not create user as it already exists. ' .
								'Please delete the user to run tests again.'
							);
						}
						throw new Exception(
							__METHOD__ . " could not create user. "
							. $result->getStatusCode() . " " . $result->getBody()
						);
					}
				}
				break;
			case "occ":
				$result = SetupHelper::createUser(
					$user, $password, $displayName, $email
				);
				if ($result["code"] !== "0") {
					throw new Exception(
						__METHOD__ . " could not create user. {$result['stdOut']} {$result['stdErr']}"
					);
				}
				break;
			case "ldap":
				$settings = [];
				$setting["userid"] = $user;
				$setting["displayName"] = $displayName;
				$setting["password"] = $password;
				$setting["email"] = $email;
				\array_push($settings, $setting);
				try {
					$this->usersHaveBeenCreated(
						$initialize,
						$settings,
						$skeleton
					);
				} catch (LdapException $exception) {
					throw new Exception(
						__METHOD__ . " cannot create a LDAP user with provided data. Error: {$exception}"
					);
				}
				break;
			default:
				throw new InvalidArgumentException(
					__METHOD__ . " Invalid method to create a user"
				);
		}

		$this->addUserToCreatedUsersList($user, $password, $displayName, $email);
		if ($initialize) {
			$this->initializeUser($user, $password);
		}
	}

	/**
	 * @param string $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function deleteUser($user) {
		if (OcisHelper::isTestingOnOcis()) {
			OcisHelper::deleteRevaUserData($user);
		} else {
			$this->deleteTheUserUsingTheProvisioningApi($user);
		}

		$this->userShouldNotExist($user);
	}

	/**
	 * Try to delete the group, catching anything bad that might happen.
	 * Use this method only in places where you want to try as best you
	 * can to delete the group, but do not want to error if there is a problem.
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function cleanupGroup($group) {
		try {
			$this->deleteTheGroupUsingTheProvisioningApi($group);
		} catch (\Exception $e) {
			\error_log(
				"INFORMATION: There was an unexpected problem trying to delete group " .
				"'$group' message '" . $e->getMessage() . "'"
			);
		}

		if ($this->theGroupShouldBeAbleToBeDeleted($group)
			&& $this->groupExists($group)
		) {
			\error_log(
				"INFORMATION: tried to delete group '$group'" .
				" at the end of the scenario but it seems to still exist. " .
				"There might be problems with later scenarios."
			);
		}
	}

	/**
	 * @param string $user
	 *
	 * @return bool
	 */
	public function userExists($user) {
		// in OCIS there is no admin user and in oC10 there are issues when
		// sending the username in lowercase in the auth but in uppercase in
		// the URL see https://github.com/owncloud/core/issues/36822
		$user = $this->getActualUsername($user);
		if (OcisHelper::isTestingOnOcis()) {
			$requestingUser = $this->getActualUsername($user);
			$requestingPassword = $this->getPasswordForUser($requestingUser);
		} else {
			$requestingUser = $this->getAdminUsername();
			$requestingPassword = $this->getAdminPassword();
		}
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/users/$user";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $requestingUser, $requestingPassword
		);
		if ($this->response->getStatusCode() >= 400) {
			return false;
		}
		return true;
	}

	/**
	 * @Then /^user "([^"]*)" should belong to group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userShouldBelongToGroup($user, $group) {
		$user = $this->getActualUsername($user);
		$this->theAdministratorGetsAllTheGroupsOfUser($user);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		\sort($respondedArray);
		Assert::assertContains(
			$group,
			$respondedArray,
			__METHOD__ . " Group '$group' does not exist in '"
			. \implode(', ', $respondedArray)
			. "'"
		);
		Assert::assertEquals(
			200,
			$this->response->getStatusCode(),
			__METHOD__
			. " Expected status code is '200' but got '"
			. $this->response->getStatusCode()
			. "'"
		);
	}

	/**
	 * @param string $group
	 *
	 * @return array
	 */
	public function getUsersOfLdapGroup($group) {
		$ou = $this->getLdapGroupsOU();
		$entry = 'cn=' . $group . ',ou=' . $ou . ',' . 'dc=owncloud,dc=com';
		$ldapResponse = $this->ldap->getEntry($entry);
		return $ldapResponse["memberuid"];
	}

	/**
	 * @Then /^user "([^"]*)" should not belong to group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userShouldNotBelongToGroup($user, $group) {
		$user = $this->getActualUsername($user);
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/users/$user/groups";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		\sort($respondedArray);
		Assert::assertNotContains($group, $respondedArray);
		Assert::assertEquals(
			200, $this->response->getStatusCode()
		);
	}

	/**
	 * @Then group :group should not contain user :username
	 *
	 * @param string $group
	 * @param string $username
	 *
	 * @return void
	 */
	public function groupShouldNotContainUser($group, $username) {
		$username = $this->getActualUsername($username);
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/groups/$group";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		$this->theUsersReturnedByTheApiShouldNotInclude($username);
	}

	/**
	 * @param string $user
	 * @param string $group
	 *
	 * @return bool
	 */
	public function userBelongsToGroup($user, $group) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/users/$user/groups";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);

		if (\in_array($group, $respondedArray)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @When /^the administrator adds user "([^"]*)" to group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function adminAddsUserToGroupUsingTheProvisioningApi($user, $group) {
		$this->addUserToGroup($user, $group, "api");
	}

	/**
	 * @When user :user tries to add user :anotheruser to group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 * @param string $group
	 *
	 * @return void
	 */
	public function userTriesToAddUserToGroupUsingTheProvisioningApi($user, $anotheruser, $group) {
		$result = UserHelper::addUserToGroup(
			$this->getBaseUrl(),
			$anotheruser, $group,
			$this->getActualUsername($user),
			$this->getUserPassword($user),
			$this->ocsApiVersion
		);
		$this->response = $result;
	}

	/**
	 * @When user :user tries to add himself to group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userTriesToAddHimselfToGroupUsingTheProvisioningApi($user, $group) {
		$this->userTriesToAddUserToGroupUsingTheProvisioningApi($user, $user, $group);
	}

	/**
	 * @When the administrator tries to add user :user to group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorTriesToAddUserToGroupUsingTheProvisioningApi(
		$user, $group
	) {
		$this->userTriesToAddUserToGroupUsingTheProvisioningApi(
			$this->getAdminUsername(),
			$user,
			$group
		);
	}

	/**
	 * @Given /^user "([^"]*)" has been added to group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function userHasBeenAddedToGroup($user, $group) {
		$user = $this->getActualUsername($user);
		$this->addUserToGroup($user, $group, null, true);
	}

	/**
	 * @Given /^user "([^"]*)" has been added to database backend group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function userHasBeenAddedToDatabaseBackendGroup($user, $group) {
		$this->addUserToGroup($user, $group, 'api', true);
	}

	/**
	 * @param string $user
	 * @param string $group
	 * @param string $method how to add the user to the group api|occ
	 * @param bool $checkResult if true, then check the status of the operation. default false.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function addUserToGroup($user, $group, $method = null, $checkResult = false) {
		$user = $this->getActualUsername($user);
		if ($method === null
			&& $this->isTestingWithLdap()
			&& !$this->isLocalAdminGroup($group)
		) {
			//guess yourself
			$method = "ldap";
		} elseif ($method === null) {
			$method = "api";
		}
		$method = \trim(\strtolower($method));
		switch ($method) {
			case "api":
				$result = UserHelper::addUserToGroup(
					$this->getBaseUrl(),
					$user, $group,
					$this->getAdminUsername(),
					$this->getAdminPassword(),
					$this->ocsApiVersion
				);
				if ($checkResult && ($result->getStatusCode() !== 200)) {
					throw new Exception(
						"could not add user to group. "
						. $result->getStatusCode() . " " . $result->getBody()
					);
				}
				$this->response = $result;
				break;
			case "occ":
				$result = SetupHelper::addUserToGroup($group, $user);
				if ($checkResult && ($result["code"] !== "0")) {
					throw new Exception(
						"could not add user to group. {$result['stdOut']} {$result['stdErr']}"
					);
				}
				break;
			case "ldap":
				try {
					$this->addUserToLdapGroup(
						$user,
						$group
					);
				} catch (LdapException $exception) {
					throw new Exception(
						"User " . $user . " cannot be added to " . $group . " . Error: {$exception}"
					);
				};
				break;
			default:
				throw new InvalidArgumentException(
					"Invalid method to add a user to a group"
				);
		}
	}

	/**
	 * @Given the administrator has been added to group :group
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theAdministratorHasBeenAddedToGroup($group) {
		$admin = $this->getAdminUsername();
		$this->addUserToGroup($admin, $group, null, true);
	}

	/**
	 * @param string $group
	 * @param bool $shouldExist - true if the group should exist
	 * @param bool $possibleToDelete - true if it is possible to delete the group
	 *
	 * @return void
	 */
	public function addGroupToCreatedGroupsList(
		$group, $shouldExist = true, $possibleToDelete = true
	) {
		$groupData = [
			"shouldExist" => $shouldExist,
			"possibleToDelete" => $possibleToDelete
		];

		if ($this->currentServer === 'LOCAL') {
			$this->createdGroups[$group] = $groupData;
		} elseif ($this->currentServer === 'REMOTE') {
			$this->createdRemoteGroups[$group] = $groupData;
		}
	}

	/**
	 * Remembers that a group from the list of groups that were created during
	 * test runs is no longer expected to exist. Useful if a group was created
	 * during the setup phase but was deleted in a test run. We don't expect
	 * this group to exist in the tear-down phase, so remember that fact.
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function rememberThatGroupIsNotExpectedToExist($group) {
		if (\array_key_exists($group, $this->createdGroups)) {
			$this->createdGroups[$group]['shouldExist'] = false;
		}
	}

	/**
	 * @When /^the administrator creates group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function adminCreatesGroupUsingTheProvisioningApi($group) {
		if (!$this->groupExists($group)) {
			$this->createTheGroup($group, 'api');
		}
		$this->groupShouldExist($group);
	}

	/**
	 * @Given /^group "([^"]*)" has been created$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function groupHasBeenCreated($group) {
		$this->createTheGroup($group);
		$this->groupShouldExist($group);
	}

	/**
	 * @Given /^group "([^"]*)" has been created in the database user backend$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function groupHasBeenCreatedOnDatabaseBackend($group) {
		$this->adminCreatesGroupUsingTheProvisioningApi($group);
	}

	/**
	 * @Given these groups have been created:
	 * expects a table of groups with the heading "groupname"
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theseGroupsHaveBeenCreated(TableNode $table) {
		$this->verifyTableNodeColumns($table, ['groupname']);
		foreach ($table as $row) {
			$this->createTheGroup($row['groupname']);
		}
	}

	/**
	 * @When /^the administrator sends a group creation request for group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $group
	 * @param string $user
	 *
	 * @return void
	 */
	public function adminSendsGroupCreationRequestUsingTheProvisioningApi(
		$group, $user = null
	) {
		$bodyTable = new TableNode([['groupid', $group]]);
		$user = $user === null ? $this->getAdminUsername() : $user;
		$this->ocsContext->userSendsHTTPMethodToOcsApiEndpointWithBody(
			$user,
			"POST",
			"/cloud/groups",
			$bodyTable
		);
		$this->addGroupToCreatedGroupsList($group);
	}

	/**
	 * @When /^the administrator tries to send a group creation request for group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function adminTriesToSendGroupCreationRequestUsingTheAPI($group) {
		$this->adminSendsGroupCreationRequestUsingTheProvisioningApi($group);
		$this->rememberThatGroupIsNotExpectedToExist($group);
	}

	/**
	 * @When /^user "([^"]*)" tries to send a group creation request for group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userTriesToSendGroupCreationRequestUsingTheAPI($user, $group) {
		$this->adminSendsGroupCreationRequestUsingTheProvisioningApi($group, $user);
		$this->rememberThatGroupIsNotExpectedToExist($group);
	}

	/**
	 * creates a single group
	 *
	 * @param string $group
	 * @param string $method how to create the group api|occ
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function createTheGroup($group, $method = null) {
		//guess yourself
		if ($method === null && $this->isTestingWithLdap()) {
			$method = "ldap";
		} elseif ($method === null) {
			$method = "api";
		}
		$group = \trim($group);
		$method = \trim(\strtolower($method));
		$groupCanBeDeleted = false;
		switch ($method) {
			case "api":
				$result = UserHelper::createGroup(
					$this->getBaseUrl(),
					$group,
					$this->getAdminUsername(),
					$this->getAdminPassword()
				);
				if ($result->getStatusCode() === 200) {
					$groupCanBeDeleted = true;
				} else {
					throw new Exception(
						"could not create group. "
						. $result->getStatusCode() . " " . $result->getBody()
					);
				}
				break;
			case "occ":
				$result = SetupHelper::createGroup($group);
				if ($result["code"] == 0) {
					$groupCanBeDeleted = true;
				} else {
					throw new Exception(
						"could not create group. {$result['stdOut']} {$result['stdErr']}"
					);
				}
				break;
			case "ldap":
				try {
					$this->createLdapGroup($group);
				} catch (LdapException $e) {
					throw new Exception(
						"could not create group. Error: {$e}"
					);
				}
				break;
			default:
				throw new InvalidArgumentException(
					"Invalid method to create a group"
				);
		}

		$this->addGroupToCreatedGroupsList($group, true, $groupCanBeDeleted);
	}

	/**
	 * @param string $attribute
	 * @param string $entry
	 * @param string $value
	 * @param bool $append
	 *
	 * @return void
	 */
	public function setTheLdapAttributeOfTheEntryTo(
		$attribute, $entry, $value, $append=false
	) {
		$ldapEntry = $this->ldap->getEntry($entry . "," . $this->ldapBaseDN);
		Zend\Ldap\Attribute::setAttribute($ldapEntry, $attribute, $value, $append);
		$this->ldap->update($entry . "," . $this->ldapBaseDN, $ldapEntry);
		$this->theLdapUsersHaveBeenReSynced();
	}

	/**
	 * @param string $user
	 * @param string $group
	 * @param string|null $ou
	 *
	 * @return void
	 */
	public function addUserToLdapGroup($user, $group, $ou = null) {
		if ($ou === null) {
			$ou = $this->getLdapGroupsOU();
		}
		$this->setTheLdapAttributeOfTheEntryTo(
			"memberUid",
			"cn=$group,ou=$ou",
			$user,
			true
		);
	}

	/**
	 * @param string $value
	 * @param string $attribute
	 * @param string $entry
	 *
	 * @return void
	 */
	public function deleteValueFromLdapAttribute($value, $attribute, $entry) {
		$this->ldap->deleteAttributes(
			$entry . "," . $this->ldapBaseDN, [$attribute => [$value]]
		);
	}

	/**
	 * @param string $user
	 * @param string $group
	 * @param null $ou
	 *
	 * @return void
	 * @throws Exception
	 */
	public function removeUserFromLdapGroup($user, $group, $ou = null) {
		if ($ou === null) {
			$ou = $this->getLdapGroupsOU();
		}
		$this->deleteValueFromLdapAttribute(
			$user, "memberUid", "cn=$group,ou=$ou"
		);
		$this->theLdapUsersHaveBeenReSynced();
	}

	/**
	 * @param string $entry
	 *
	 * @return void
	 * @throws Exception
	 */
	public function deleteTheLdapEntry($entry) {
		$this->ldap->delete($entry . "," . $this->ldapBaseDN);
		$this->theLdapUsersHaveBeenReSynced();
	}

	/**
	 * @param string $group
	 * @param null $ou
	 *
	 * @return void
	 * @throws LdapException
	 * @throws Exception
	 */
	public function deleteLdapGroup($group, $ou = null) {
		if ($ou === null) {
			$ou = $this->getLdapGroupsOU();
		}
		$this->deleteTheLdapEntry("cn=$group,ou=$ou");
		$this->theLdapUsersHaveBeenReSynced();
		$key = \array_search($group, $this->ldapCreatedGroups);
		if ($key !== false) {
			unset($this->ldapCreatedGroups[$key]);
		}
		$this->rememberThatGroupIsNotExpectedToExist($group);
	}

	/**
	 * @param string $username
	 * @param null $ou
	 *
	 * @return void
	 * @throws Exception
	 */
	public function deleteLdapUser($username, $ou = null) {
		if (!\in_array($username, $this->ldapCreatedUsers)) {
			throw new Error(
				"User " . $username . " was not created using Ldap and does not exist as an Ldap User"
			);
		}
		if ($ou === null) {
			$ou = $this->getLdapUsersOU();
		}
		$entry = "uid=$username,ou=$ou";
		$this->deleteTheLdapEntry($entry);
		$key = \array_search($username, $this->ldapCreatedUsers);
		if ($key !== false) {
			unset($this->ldapCreatedUsers[$key]);
		}
		$this->rememberThatUserIsNotExpectedToExist($username);
	}

	/**
	 * @param string $user
	 * @param string $displayName
	 *
	 * @return void
	 * @throws Exception
	 */
	public function editLdapUserDisplayName($user, $displayName) {
		$entry = "uid=" . $user . ",ou=" . $this->getLdapUsersOU();
		$this->setTheLdapAttributeOfTheEntryTo(
			'displayname',
			$entry,
			$displayName
		);
		$this->theLdapUsersHaveBeenReSynced();
	}

	/**
	 * @When /^the administrator disables user "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function adminDisablesUserUsingTheProvisioningApi($user) {
		$user = $this->getActualUsername($user);
		$this->disableOrEnableUser($this->getAdminUsername(), $user, 'disable');
	}

	/**
	 * @Given /^user "([^"]*)" has been disabled$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function adminHasDisabledUserUsingTheProvisioningApi($user) {
		$user = $this->getActualUsername($user);
		$this->disableOrEnableUser($this->getAdminUsername(), $user, 'disable');
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When user :user disables user :anotheruser using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 *
	 * @return void
	 */
	public function userDisablesUserUsingTheProvisioningApi($user, $anotheruser) {
		$user = $this->getActualUsername($user);
		$anotheruser = $this->getActualUsername($anotheruser);
		$this->disableOrEnableUser($user, $anotheruser, 'disable');
	}

	/**
	 * @When the administrator enables user :user using the provisioning API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorEnablesUserUsingTheProvisioningApi($user) {
		$this->disableOrEnableUser($this->getAdminUsername(), $user, 'enable');
	}

	/**
	 * @When /^user "([^"]*)" (enables|tries to enable) user "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $anotheruser
	 *
	 * @return void
	 */
	public function userTriesToEnableUserUsingTheProvisioningApi(
		$user, $anotheruser
	) {
		$this->disableOrEnableUser($user, $anotheruser, 'enable');
	}

	/**
	 * @param string $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function deleteTheUserUsingTheProvisioningApi($user) {
		// Always try to delete the user
		$this->response = UserHelper::deleteUser(
			$this->getBaseUrl(),
			$user,
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			$this->ocsApiVersion
		);

		// Only log a message if the test really expected the user to have been
		// successfully created (i.e. the delete is expected to work) and
		// there was a problem deleting the user. Because in this case there
		// might be an effect on later tests.
		if ($this->theUserShouldExist($user)
			&& ($this->response->getStatusCode() !== 200)
		) {
			\error_log(
				"INFORMATION: could not delete user '$user' "
				. $this->response->getStatusCode() . " " . $this->response->getBody()
			);
		}

		$this->rememberThatUserIsNotExpectedToExist($user);
	}

	/**
	 * @param string $group group name
	 *
	 * @return void
	 * @throws Exception
	 * @throws LdapException
	 */
	public function deleteGroup($group) {
		if ($this->groupExists($group)) {
			if ($this->isTestingWithLdap() && \in_array($group, $this->ldapCreatedGroups)) {
				$this->deleteLdapGroup($group);
			} else {
				$this->deleteTheGroupUsingTheProvisioningApi($group);
			}
		}
	}

	/**
	 * @Given /^group "([^"]*)" has been deleted$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function groupHasBeenDeleted($group) {
		$this->deleteGroup($group);
		$this->groupShouldNotExist($group);
	}

	/**
	 * @When /^the administrator deletes group "([^"]*)" from the default user backend$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function adminDeletesGroup($group) {
		$this->deleteGroup($group);
	}

	/**
	 * @When /^the administrator deletes group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $group
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function deleteTheGroupUsingTheProvisioningApi($group) {
		$this->response = UserHelper::deleteGroup(
			$this->getBaseUrl(),
			$group,
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			$this->ocsApiVersion
		);

		if ($this->theGroupShouldExist($group)
			&& $this->theGroupShouldBeAbleToBeDeleted($group)
			&& ($this->response->getStatusCode() !== 200)
		) {
			\error_log(
				"INFORMATION: could not delete group '$group'"
				. $this->response->getStatusCode() . " " . $this->response->getBody()
			);
		}

		$this->rememberThatGroupIsNotExpectedToExist($group);
	}

	/**
	 * @When user :user tries to delete group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userTriesToDeleteGroupUsingTheProvisioningApi($user, $group) {
		$this->response = UserHelper::deleteGroup(
			$this->getBaseUrl(),
			$group,
			$this->getActualUsername($user),
			$this->getActualPassword($user),
			$this->ocsApiVersion
		);
	}

	/**
	 * @param string $group
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function groupExists($group) {
		if ($this->isTestingWithLdap() && OcisHelper::isTestingOnOcis()) {
			$baseDN = $this->getLdapBaseDN();
			$newDN = 'cn=' . $group . ',ou=' . $this->ou . ',' . $baseDN;
			if ($this->ldap->getEntry($newDN) !== null) {
				return true;
			}
			return false;
		}
		$group = \rawurlencode($group);
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/groups/$group";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		if ($this->response->getStatusCode() >= 400) {
			return false;
		}
		return true;
	}

	/**
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 * @throws Exception
	 */
	public function removeUserFromGroupAsAdminUsingTheProvisioningApi($user, $group) {
		$this->userRemovesUserFromGroupUsingTheProvisioningApi(
			$this->getAdminUsername(), $user, $group
		);
	}

	/**
	 * @When the administrator removes user :user from group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminRemovesUserFromGroupUsingTheProvisioningApi($user, $group) {
		$user = $this->getActualUsername($user);
		$this->removeUserFromGroupAsAdminUsingTheProvisioningApi(
			$user, $group
		);
	}

	/**
	 * @Given user :user has been removed from group :group
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 * @throws Exception
	 */
	public function adminHasRemovedUserFromGroup($user, $group) {
		if ($this->isTestingWithLdap()
			&& !$this->isLocalAdminGroup($group)
			&& \in_array($group, $this->ldapCreatedGroups)
		) {
			$this->removeUserFromLdapGroup($user, $group);
		} else {
			$this->removeUserFromGroupAsAdminUsingTheProvisioningApi(
				$user, $group
			);
		}
		$this->userShouldNotBelongToGroup($user, $group);
	}

	/**
	 * @When user :user removes user :anotheruser from group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 * @param string $group
	 *
	 * @return void
	 */
	public function userRemovesUserFromGroupUsingTheProvisioningApi(
		$user, $anotheruser, $group
	) {
		$this->userTriesToRemoveUserFromGroupUsingTheProvisioningApi(
			$user, $anotheruser, $group
		);

		if ($this->response->getStatusCode() !== 200) {
			\error_log(
				"INFORMATION: could not remove user '$user' from group '$group'"
				. $this->response->getStatusCode() . " " . $this->response->getBody()
			);
		}
	}

	/**
	 * @When user :user tries to remove user :anotheruser from group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 * @param string $group
	 *
	 * @return void
	 */
	public function userTriesToRemoveUserFromGroupUsingTheProvisioningApi(
		$user, $anotheruser, $group
	) {
		$this->response = UserHelper::removeUserFromGroup(
			$this->getBaseUrl(),
			$anotheruser,
			$group,
			$this->getActualUsername($user),
			$this->getUserPassword($user),
			$this->ocsApiVersion
		);
	}

	/**
	 * @When /^the administrator makes user "([^"]*)" a subadmin of group "([^"]*)" using the provisioning API$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function adminMakesUserSubadminOfGroupUsingTheProvisioningApi(
		$user, $group
	) {
		$user = $this->getActualUsername($user);
		$this->userMakesUserASubadminOfGroupUsingTheProvisioningApi(
			$this->getAdminUsername(), $user, $group
		);
	}

	/**
	 * @When user :user makes user :anotheruser a subadmin of group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 * @param string $group
	 *
	 * @return void
	 */
	public function userMakesUserASubadminOfGroupUsingTheProvisioningApi(
		$user, $anotheruser, $group
	) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$anotheruser/subadmins";
		$body = ['groupid' => $group];
		$this->response = HttpRequestHelper::post(
			$fullUrl,
			$this->getActualUsername($user),
			$this->getUserPassword($user),
			null,
			$body
		);
	}

	/**
	 * @When the administrator gets all the groups where user :user is subadmin using the provisioning API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllTheGroupsWhereUserIsSubadminUsingTheProvisioningApi($user) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$user/subadmins";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
	}

	/**
	 * @Given /^user "([^"]*)" has been made a subadmin of group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userHasBeenMadeSubadminOfGroup(
		$user, $group
	) {
		$this->adminMakesUserSubadminOfGroupUsingTheProvisioningApi(
			$user, $group
		);
		Assert::assertEquals(
			200, $this->response->getStatusCode()
		);
	}

	/**
	 * @When the administrator gets all the subadmins of group :group using the provisioning API
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorGetsAllTheSubadminsOfGroupUsingTheProvisioningApi($group) {
		$this->userGetsAllTheSubadminsOfGroupUsingTheProvisioningApi(
			$this->getAdminUsername(), $group
		);
	}

	/**
	 * @When user :user gets all the subadmins of group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userGetsAllTheSubadminsOfGroupUsingTheProvisioningApi($user, $group) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/groups/$group/subadmins";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getActualUsername($user), $this->getUserPassword($user)
		);
	}

	/**
	 * @When the administrator removes user :user from being a subadmin of group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function theAdministratorRemovesUserFromBeingASubadminOfGroupUsingTheProvisioningApi(
		$user, $group
	) {
		$this->userRemovesUserFromBeingASubadminOfGroupUsingTheProvisioningApi(
			$this->getAdminUsername(), $user, $group
		);
	}

	/**
	 * @When user :user removes user :anotheruser from being a subadmin of group :group using the provisioning API
	 *
	 * @param string $user
	 * @param string $anotheruser
	 * @param string $group
	 *
	 * @return void
	 */
	public function userRemovesUserFromBeingASubadminOfGroupUsingTheProvisioningApi(
		$user, $anotheruser, $group
	) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$anotheruser/subadmins";
		$this->response = HttpRequestHelper::delete(
			$fullUrl,
			$this->getActualUsername($user),
			$this->getUserPassword($user),
			null,
			['groupid' => $group]
		);
	}

	/**
	 * @Then /^the users returned by the API should be$/
	 *
	 * @param TableNode $usersList
	 *
	 * @return void
	 */
	public function theUsersShouldBe($usersList) {
		$this->verifyTableNodeColumnsCount($usersList, 1);
		$users = $usersList->getRows();
		$usersSimplified = \array_map(
			function ($user) {
				return $this->getActualUsername($user);
			},
			$this->simplifyArray($users)
		);
		$respondedArray = $this->getArrayOfUsersResponded($this->response);
		Assert::assertEqualsCanonicalizing(
			$usersSimplified, $respondedArray
		);
	}

	/**
	 * @Then /^the groups returned by the API should be$/
	 *
	 * @param TableNode $groupsList
	 *
	 * @return void
	 */
	public function theGroupsShouldBe($groupsList) {
		$this->verifyTableNodeColumnsCount($groupsList, 1);
		$groups = $groupsList->getRows();
		$groupsSimplified = $this->simplifyArray($groups);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		Assert::assertEqualsCanonicalizing(
			$groupsSimplified, $respondedArray
		);
	}

	/**
	 * @Then /^the groups returned by the API should include "([^"]*)"$/
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theGroupsReturnedByTheApiShouldInclude($group) {
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		Assert::assertContains($group, $respondedArray);
	}

	/**
	 * @Then /^the groups returned by the API should not include "([^"]*)"$/
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function theGroupsReturnedByTheApiShouldNotInclude($group) {
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		Assert::assertNotContains($group, $respondedArray);
	}

	/**
	 * @Then /^the users returned by the API should not include "([^"]*)"$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function theUsersReturnedByTheApiShouldNotInclude($user) {
		$respondedArray = $this->getArrayOfUsersResponded($this->response);
		Assert::assertNotContains($user, $respondedArray);
	}

	/**
	 * @param TableNode|null $groupsOrUsersList
	 *
	 * @return void
	 */
	public function checkSubadminGroupsOrUsersTable($groupsOrUsersList) {
		$this->verifyTableNodeColumnsCount($groupsOrUsersList, 1);
		$tableRows = $groupsOrUsersList->getRows();
		$simplifiedTableRows = $this->simplifyArray($tableRows);
		$respondedArray = $this->getArrayOfSubadminsResponded($this->response);
		Assert::assertEqualsCanonicalizing(
			$simplifiedTableRows, $respondedArray
		);
	}

	/**
	 * @Then /^the subadmin groups returned by the API should be$/
	 *
	 * @param TableNode|null $groupsList
	 *
	 * @return void
	 */
	public function theSubadminGroupsShouldBe($groupsList) {
		$this->checkSubadminGroupsOrUsersTable($groupsList);
	}

	/**
	 * @Then /^the subadmin users returned by the API should be$/
	 *
	 * @param TableNode|null $usersList
	 *
	 * @return void
	 */
	public function theSubadminUsersShouldBe($usersList) {
		$this->checkSubadminGroupsOrUsersTable($usersList);
	}

	/**
	 * @Then /^the apps returned by the API should include$/
	 *
	 * @param TableNode|null $appList
	 *
	 * @return void
	 */
	public function theAppsShouldInclude($appList) {
		$this->verifyTableNodeColumnsCount($appList, 1);
		$apps = $appList->getRows();
		$appsSimplified = $this->simplifyArray($apps);
		$respondedArray = $this->getArrayOfAppsResponded($this->response);
		foreach ($appsSimplified as $app) {
			Assert::assertContains($app, $respondedArray);
		}
	}

	/**
	 * @Then /^app "([^"]*)" should not be in the apps list$/
	 *
	 * @param string $appName
	 *
	 * @return void
	 */
	public function appShouldNotBeInTheAppsList($appName) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/apps";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		$respondedArray = $this->getArrayOfAppsResponded($this->response);
		Assert::assertNotContains($appName, $respondedArray);
	}

	/**
	 * @Then /^user "([^"]*)" should be a subadmin of group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userShouldBeASubadminOfGroup($user, $group) {
		$this->theAdministratorGetsAllTheSubadminsOfGroupUsingTheProvisioningApi($group);
		Assert::assertEquals(
			200, $this->response->getStatusCode()
		);
		$listOfSubadmins = $this->getArrayOfSubadminsResponded($this->response);
		Assert::assertContains(
			$user,
			$listOfSubadmins
		);
	}

	/**
	 * @Then /^user "([^"]*)" should not be a subadmin of group "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $group
	 *
	 * @return void
	 */
	public function userShouldNotBeASubadminOfGroup($user, $group) {
		$this->theAdministratorGetsAllTheSubadminsOfGroupUsingTheProvisioningApi($group);
		$listOfSubadmins = $this->getArrayOfSubadminsResponded($this->response);
		Assert::assertNotContains(
			$user,
			$listOfSubadmins
		);
	}

	/**
	 * @Then /^the display name returned by the API should be "([^"]*)"$/
	 *
	 * @param string $expectedDisplayName
	 *
	 * @return void
	 */
	public function theDisplayNameReturnedByTheApiShouldBe($expectedDisplayName) {
		$responseDisplayName = (string) $this->getResponseXml()->data[0]->displayname;
		Assert::assertEquals(
			$expectedDisplayName,
			$responseDisplayName
		);
	}

	/**
	 * @Then /^the display name of user "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $displayname
	 *
	 * @return void
	 */
	public function theDisplayNameOfUserShouldBe($user, $displayname) {
		$this->retrieveUserInformationAsAdminUsingProvisioningApi($user);
		$this->theDisplayNameReturnedByTheApiShouldBe($displayname);
	}

	/**
	 * @Then /^the email address returned by the API should be "([^"]*)"$/
	 *
	 * @param string $expectedEmailAddress
	 *
	 * @return void
	 */
	public function theEmailAddressReturnedByTheApiShouldBe($expectedEmailAddress) {
		$responseEmailAddress = (string) $this->getResponseXml()->data[0]->email;
		Assert::assertEquals(
			$expectedEmailAddress,
			$responseEmailAddress
		);
	}

	/**
	 * @Then /^the email address of user "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $expectedEmailAddress
	 *
	 * @return void
	 */
	public function theEmailAddressOfUserShouldBe($user, $expectedEmailAddress) {
		$user = $this->getActualUsername($user);
		$this->retrieveUserInformationAsAdminUsingProvisioningApi($user);
		$this->theEmailAddressReturnedByTheApiShouldBe($expectedEmailAddress);
	}

	/**
	 * @Then /^the quota definition returned by the API should be "([^"]*)"$/
	 *
	 * @param string $expectedQuotaDefinition a string that describes the quota
	 *
	 * @return void
	 */
	public function theQuotaDefinitionReturnedByTheApiShouldBe($expectedQuotaDefinition) {
		$responseQuotaDefinition = (string) $this->getResponseXml()->data[0]->quota->definition;
		Assert::assertEquals(
			$expectedQuotaDefinition,
			$responseQuotaDefinition
		);
	}

	/**
	 * @Then /^the quota definition of user "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $expectedQuotaDefinition
	 *
	 * @return void
	 */
	public function theQuotaDefinitionOfUserShouldBe($user, $expectedQuotaDefinition) {
		$this->retrieveUserInformationAsAdminUsingProvisioningApi($user);
		$this->theQuotaDefinitionReturnedByTheApiShouldBe($expectedQuotaDefinition);
	}

	/**
	 * Parses the xml answer to get the array of users returned.
	 *
	 * @param ResponseInterface $resp
	 *
	 * @return array
	 */
	public function getArrayOfUsersResponded($resp) {
		$listCheckedElements
			= $this->getResponseXml($resp)->data[0]->users[0]->element;
		$extractedElementsArray
			= \json_decode(\json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of groups returned.
	 *
	 * @param ResponseInterface $resp
	 *
	 * @return array
	 */
	public function getArrayOfGroupsResponded($resp) {
		$listCheckedElements
			= $this->getResponseXml($resp)->data[0]->groups[0]->element;
		$extractedElementsArray
			= \json_decode(\json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of apps returned.
	 *
	 * @param ResponseInterface $resp
	 *
	 * @return array
	 */
	public function getArrayOfAppsResponded($resp) {
		$listCheckedElements
			= $this->getResponseXml($resp)->data[0]->apps[0]->element;
		$extractedElementsArray
			= \json_decode(\json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of subadmins returned.
	 *
	 * @param ResponseInterface $resp
	 *
	 * @return array
	 */
	public function getArrayOfSubadminsResponded($resp) {
		$listCheckedElements
			= $this->getResponseXml($resp)->data[0]->element;
		$extractedElementsArray
			= \json_decode(\json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of app info returned for an app.
	 *
	 * @param ResponseInterface $resp
	 *
	 * @return array
	 */
	public function getArrayOfAppInfoResponded($resp) {
		$listCheckedElements
			= $this->getResponseXml($resp)->data[0];
		$extractedElementsArray
			= \json_decode(\json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * @Then /^app "([^"]*)" should be disabled$/
	 *
	 * @param string $app
	 *
	 * @return void
	 */
	public function appShouldBeDisabled($app) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v2.php/cloud/apps?filter=disabled";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		$respondedArray = $this->getArrayOfAppsResponded($this->response);
		Assert::assertContains($app, $respondedArray);
		Assert::assertEquals(
			200, $this->response->getStatusCode()
		);
	}

	/**
	 * @Then /^app "([^"]*)" should be enabled$/
	 *
	 * @param string $app
	 *
	 * @return void
	 */
	public function appShouldBeEnabled($app) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/apps?filter=enabled";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		$respondedArray = $this->getArrayOfAppsResponded($this->response);
		Assert::assertContains($app, $respondedArray);
		Assert::assertEquals(
			200, $this->response->getStatusCode()
		);
	}

	/**
	 * @Then /^the information for app "([^"]*)" should have a valid version$/
	 *
	 * @param string $app
	 *
	 * @return void
	 */
	public function theInformationForAppShouldHaveAValidVersion($app) {
		$fullUrl = $this->getBaseUrl() . "/ocs/v2.php/cloud/apps/$app";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		Assert::assertEquals(
			200, $this->response->getStatusCode()
		);
		$respondedArray = $this->getArrayOfAppInfoResponded($this->response);
		Assert::assertArrayHasKey(
			'version',
			$respondedArray,
			"app info returned for $app app does not have a version"
		);
		$appVersion = $respondedArray['version'];
		Assert::assertTrue(
			\substr_count($appVersion, '.') > 1,
			"app version '$appVersion' returned in app info is not a valid version string"
		);
	}

	/**
	 * @Then /^user "([^"]*)" should be disabled$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userShouldBeDisabled($user) {
		$user = $this->getActualUsername($user);
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$user";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		Assert::assertEquals(
			"false", $this->getResponseXml()->data[0]->enabled
		);
	}

	/**
	 * @Then /^user "([^"]*)" should be enabled$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function useShouldBeEnabled($user) {
		$user = $this->getActualUsername($user);
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$user";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		Assert::assertEquals(
			"true", $this->getResponseXml()->data[0]->enabled
		);
	}

	/**
	 * @When the administrator sets the quota of user :user to :quota using the provisioning API
	 *
	 * @param string $user
	 * @param string $quota
	 *
	 * @return void
	 */
	public function adminSetsUserQuotaToUsingTheProvisioningApi($user, $quota) {
		$user = $this->getActualUsername($user);
		$body
			= [
			'key' => 'quota',
			'value' => $quota,
		];

		$this->response = OcsApiHelper::sendRequest(
			$this->getBaseUrl(),
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			"PUT",
			"/cloud/users/$user",
			$body,
			2
		);
	}

	/**
	 * @Given the quota of user :user has been set to :quota
	 *
	 * @param string $user
	 * @param string $quota
	 *
	 * @return void
	 */
	public function theQuotaOfUserHasBeenSetTo($user, $quota) {
		$this->adminSetsUserQuotaToUsingTheProvisioningApi($user, $quota);
		$this->theHTTPStatusCodeShouldBe(200);
	}

	/**
	 * @When the administrator gives unlimited quota to user :user using the provisioning API
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function adminGivesUnlimitedQuotaToUserUsingTheProvisioningApi($user) {
		$this->adminSetsUserQuotaToUsingTheProvisioningApi($user, 'none');
	}

	/**
	 * @Given user :user has been given unlimited quota
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userHasBeenGivenUnlimitedQuota($user) {
		$this->theQuotaOfUserHasBeenSetTo($user, 'none');
	}

	/**
	 * Returns home path of the given user
	 *
	 * @param string $user
	 *
	 * @return string
	 */
	public function getUserHome($user) {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$user";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		return $this->getResponseXml()->data[0]->home;
	}

	/**
	 * @Then /^the user attributes returned by the API should include$/
	 *
	 * @param TableNode|null $body
	 *
	 * @return void
	 */
	public function checkUserAttributes($body) {
		$this->verifyTableNodeRows($body, [], $this->userResponseFields);
		$bodyRows = $body->getRowsHash();
		foreach ($bodyRows as $field => $value) {
			$data = $this->getResponseXml()->data[0];
			$field_array = \explode(' ', $field);
			foreach ($field_array as $field_name) {
				$data = $data->$field_name;
			}
			if ($data != $value) {
				Assert::fail(
					"$field has value $data"
				);
			}
		}
	}

	/**
	 * @Then the attributes of user :user returned by the API should include
	 *
	 * @param string $user
	 * @param TableNode $body
	 *
	 * @return void
	 */
	public function checkAttributesForUser($user, $body) {
		$user = $this->getActualUsername($user);
		$this->verifyTableNodeColumnsCount($body, 2);
		$this->ocsContext->userSendsHTTPMethodToOcsApiEndpointWithBody(
			$this->getAdminUsername(), "GET", "/cloud/users/$user",
			null
		);
		$this->checkUserAttributes($body);
	}

	/**
	 * @Then /^the API should not return any data$/
	 *
	 * @return void
	 */
	public function theApiShouldNotReturnAnyData() {
		$responseData = $this->getResponseXml()->data[0];
		Assert::assertEmpty(
			$responseData,
			"Response data is not empty but it should be empty"
		);
	}

	/**
	 * @Then /^the list of users returned by the API should be empty$/
	 *
	 * @return void
	 */
	public function theListOfUsersReturnedByTheApiShouldBeEmpty() {
		$usersList = $this->getResponseXml()->data[0]->users[0];
		Assert::assertEmpty(
			$usersList,
			"Users list is not empty but it should be empty"
		);
	}

	/**
	 * @Then /^the list of groups returned by the API should be empty$/
	 *
	 * @return void
	 */
	public function theListOfGroupsReturnedByTheApiShouldBeEmpty() {
		$groupsList = $this->getResponseXml()->data[0]->groups[0];
		Assert::assertEmpty(
			$groupsList,
			"Groups list is not empty but it should be empty"
		);
	}

	/**
	 * @AfterScenario
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function afterScenario() {
		$this->restoreParametersAfterScenario();
		if ($this->isTestingWithLdap()) {
			$this->deleteLdapUsersAndGroups();
		}

		if (OcisHelper::isTestingOnOcis()) {
			OcisHelper::deleteRevaUserData("");
		} else {
			$this->cleanupDatabaseUsers();
			$this->cleanupDatabaseGroups();
			$this->resetAdminUserAttributes();
		}
	}

	/**
	 *
	 * @return void
	 */
	public function resetAdminUserAttributes() {
		if ($this->adminDisplayName !== '') {
			$this->adminChangesTheDisplayNameOfUserUsingTheProvisioningApi(
				$this->getAdminUsername(),
				''
			);
		}
		if ($this->adminEmailAddress !== '') {
			$this->adminChangesTheEmailOfUserToUsingTheProvisioningApi(
				$this->getAdminUsername(),
				''
			);
		}
	}

	/**
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function cleanupDatabaseUsers() {
		$this->authContext->deleteTokenAuthEnforcedAfterScenario();
		$previousServer = $this->currentServer;
		$this->usingServer('LOCAL');
		foreach ($this->createdUsers as $user => $userData) {
			$this->deleteUser($user);
		}
		$this->usingServer('REMOTE');
		foreach ($this->createdRemoteUsers as $remoteUser => $userData) {
			$this->deleteUser($remoteUser);
		}
		$this->usingServer($previousServer);
	}

	/**
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function cleanupDatabaseGroups() {
		$this->authContext->deleteTokenAuthEnforcedAfterScenario();
		$previousServer = $this->currentServer;
		$this->usingServer('LOCAL');
		foreach ($this->createdGroups as $group => $groupData) {
			$this->cleanupGroup($group);
		}
		$this->usingServer('REMOTE');
		foreach ($this->createdRemoteGroups as $remoteGroup => $groupData) {
			$this->cleanupGroup($remoteGroup);
		}
		$this->usingServer($previousServer);
	}

	/**
	 * @BeforeScenario
	 *
	 * @return void
	 */
	public function rememberAppEnabledDisabledState() {
		if (!OcisHelper::isTestingOnOcis()) {
			SetupHelper::init(
				$this->getAdminUsername(),
				$this->getAdminPassword(),
				$this->getBaseUrl(),
				$this->getOcPath()
			);
			$this->runOcc(['app:list', '--output json']);
			$apps = \json_decode($this->getStdOutOfOccCommand(), true);
			$this->enabledApps = \array_keys($apps["enabled"]);
			$this->disabledApps = \array_keys($apps["disabled"]);
		}
	}

	/**
	 * @AfterScenario
	 *
	 * @return void
	 */
	public function restoreAppEnabledDisabledState() {
		if (!OcisHelper::isTestingOnOcis()) {
			$this->runOcc(['app:list', '--output json']);
			$apps = \json_decode($this->getStdOutOfOccCommand(), true);
			$currentlyEnabledApps = \array_keys($apps["enabled"]);
			$currentlyDisabledApps = \array_keys($apps["disabled"]);

			foreach ($currentlyDisabledApps as $disabledApp) {
				if (\in_array($disabledApp, $this->enabledApps)) {
					$this->adminEnablesOrDisablesApp('enables', $disabledApp);
				}
			}

			foreach ($currentlyEnabledApps as $enabledApp) {
				if (\in_array($enabledApp, $this->disabledApps)) {
					$this->adminEnablesOrDisablesApp('disables', $enabledApp);
				}
			}
		}
	}

	/**
	 * disable or enable user
	 *
	 * @param string $user
	 * @param string $anotheruser
	 * @param string $action
	 *
	 * @return void
	 */
	public function disableOrEnableUser($user, $anotheruser, $action) {
		$user = $this->getActualUsername($user);
		$anotheruser = $this->getActualUsername($anotheruser);

		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/users/$anotheruser/$action";
		$this->response = HttpRequestHelper::put(
			$fullUrl,
			$this->getActualUsername($user),
			$this->getPasswordForUser($user)
		);
	}

	/**
	 * Returns array of enabled apps
	 *
	 * @return array
	 */
	public function getEnabledApps() {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/apps?filter=enabled";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		return ($this->getArrayOfAppsResponded($this->response));
	}

	/**
	 * Returns array of disabled apps
	 *
	 * @return array
	 */
	public function getDisabledApps() {
		$fullUrl = $this->getBaseUrl()
			. "/ocs/v{$this->ocsApiVersion}.php/cloud/apps?filter=disabled";
		$this->response = HttpRequestHelper::get(
			$fullUrl, $this->getAdminUsername(), $this->getAdminPassword()
		);
		return ($this->getArrayOfAppsResponded($this->response));
	}

	/**
	 * Removes skeleton directory config from config.php and returns the config value
	 *
	 * @param string $baseUrl
	 *
	 * @return string
	 */
	public function popSkeletonDirectoryConfig($baseUrl = null) {
		$this->runOcc(
			["config:system:get skeletondirectory"],
			null, null, $baseUrl
		);
		$path = \trim($this->getStdOutOfOccCommand());
		$this->runOcc(
			["config:system:delete skeletondirectory"],
			null, null, $baseUrl
		);
		return $path;
	}
}
