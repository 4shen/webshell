<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

// FIXME: disabled for now to be able to inject IGroupManager and also use
// getSubAdmin()
//declare(strict_types=1);

namespace OCA\Settings\Controller;

use OC\Accounts\AccountManager;
use OC\AppFramework\Http;
use OC\Encryption\Exceptions\ModuleDoesNotExistsException;
use OC\ForbiddenException;
use OC\Security\IdentityProof\Manager;
use OCA\Settings\BackgroundJobs\VerifyUserData;
use OCA\User_LDAP\User_Proxy;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\BackgroundJob\IJobList;
use OCP\Encryption\IManager;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Mail\IMailer;
use function in_array;

class UsersController extends Controller {
	/** @var IUserManager */
	private $userManager;
	/** @var IGroupManager */
	private $groupManager;
	/** @var IUserSession */
	private $userSession;
	/** @var IConfig */
	private $config;
	/** @var bool */
	private $isAdmin;
	/** @var IL10N */
	private $l10n;
	/** @var IMailer */
	private $mailer;
	/** @var IFactory */
	private $l10nFactory;
	/** @var IAppManager */
	private $appManager;
	/** @var AccountManager */
	private $accountManager;
	/** @var Manager */
	private $keyManager;
	/** @var IJobList */
	private $jobList;
	/** @var IManager */
	private $encryptionManager;


	public function __construct(string $appName,
								IRequest $request,
								IUserManager $userManager,
								IGroupManager $groupManager,
								IUserSession $userSession,
								IConfig $config,
								bool $isAdmin,
								IL10N $l10n,
								IMailer $mailer,
								IFactory $l10nFactory,
								IAppManager $appManager,
								AccountManager $accountManager,
								Manager $keyManager,
								IJobList $jobList,
								IManager $encryptionManager) {
		parent::__construct($appName, $request);
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->config = $config;
		$this->isAdmin = $isAdmin;
		$this->l10n = $l10n;
		$this->mailer = $mailer;
		$this->l10nFactory = $l10nFactory;
		$this->appManager = $appManager;
		$this->accountManager = $accountManager;
		$this->keyManager = $keyManager;
		$this->jobList = $jobList;
		$this->encryptionManager = $encryptionManager;
	}


	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * Display users list template
	 *
	 * @return TemplateResponse
	 */
	public function usersListByGroup() {
		return $this->usersList();
	}

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * Display users list template
	 *
	 * @return TemplateResponse
	 */
	public function usersList() {
		$user = $this->userSession->getUser();
		$uid = $user->getUID();

		\OC::$server->getNavigationManager()->setActiveEntry('core_users');

		/* SORT OPTION: SORT_USERCOUNT or SORT_GROUPNAME */
		$sortGroupsBy = \OC\Group\MetaData::SORT_USERCOUNT;
		$isLDAPUsed = false;
		if ($this->config->getSystemValue('sort_groups_by_name', false)) {
			$sortGroupsBy = \OC\Group\MetaData::SORT_GROUPNAME;
		} else {
			if ($this->appManager->isEnabledForUser('user_ldap')) {
				$isLDAPUsed =
					$this->groupManager->isBackendUsed('\OCA\User_LDAP\Group_Proxy');
				if ($isLDAPUsed) {
					// LDAP user count can be slow, so we sort by group name here
					$sortGroupsBy = \OC\Group\MetaData::SORT_GROUPNAME;
				}
			}
		}

		$canChangePassword = $this->canAdminChangeUserPasswords();

		/* GROUPS */
		$groupsInfo = new \OC\Group\MetaData(
			$uid,
			$this->isAdmin,
			$this->groupManager,
			$this->userSession
		);

		$groupsInfo->setSorting($sortGroupsBy);
		list($adminGroup, $groups) = $groupsInfo->get();

		if (!$isLDAPUsed && $this->appManager->isEnabledForUser('user_ldap')) {
			$isLDAPUsed = (bool)array_reduce($this->userManager->getBackends(), function ($ldapFound, $backend) {
				return $ldapFound || $backend instanceof User_Proxy;
			});
		}

		$disabledUsers = -1;
		$userCount = 0;

		if (!$isLDAPUsed) {
			if ($this->isAdmin) {
				$disabledUsers = $this->userManager->countDisabledUsers();
				$userCount = array_reduce($this->userManager->countUsers(), function ($v, $w) {
					return $v + (int)$w;
				}, 0);
			} else {
				// User is subadmin !
				// Map group list to names to retrieve the countDisabledUsersOfGroups
				$userGroups = $this->groupManager->getUserGroups($user);
				$groupsNames = [];

				foreach ($groups as $key => $group) {
					// $userCount += (int)$group['usercount'];
					array_push($groupsNames, $group['name']);
					// we prevent subadmins from looking up themselves
					// so we lower the count of the groups he belongs to
					if (array_key_exists($group['id'], $userGroups)) {
						$groups[$key]['usercount']--;
						$userCount -= 1; // we also lower from one the total count
					}
				};
				$userCount += $this->userManager->countUsersOfGroups($groupsInfo->getGroups());
				$disabledUsers = $this->userManager->countDisabledUsersOfGroups($groupsNames);
			}

			$userCount -= $disabledUsers;
		}

		$disabledUsersGroup = [
			'id' => 'disabled',
			'name' => 'Disabled users',
			'usercount' => $disabledUsers
		];

		/* QUOTAS PRESETS */
		$quotaPreset = $this->parseQuotaPreset($this->config->getAppValue('files', 'quota_preset', '1 GB, 5 GB, 10 GB'));
		$defaultQuota = $this->config->getAppValue('files', 'default_quota', 'none');

		\OC::$server->getEventDispatcher()->dispatch('OC\Settings\Users::loadAdditionalScripts');

		/* LANGUAGES */
		$languages = $this->l10nFactory->getLanguages();

		/* FINAL DATA */
		$serverData = [];
		// groups
		$serverData['groups'] = array_merge_recursive($adminGroup, [$disabledUsersGroup], $groups);
		// Various data
		$serverData['isAdmin'] = $this->isAdmin;
		$serverData['sortGroups'] = $sortGroupsBy;
		$serverData['quotaPreset'] = $quotaPreset;
		$serverData['userCount'] = $userCount;
		$serverData['languages'] = $languages;
		$serverData['defaultLanguage'] = $this->config->getSystemValue('default_language', 'en');
		$serverData['forceLanguage'] = $this->config->getSystemValue('force_language', false);
		// Settings
		$serverData['defaultQuota'] = $defaultQuota;
		$serverData['canChangePassword'] = $canChangePassword;
		$serverData['newUserGenerateUserID'] = $this->config->getAppValue('core', 'newUser.generateUserID', 'no') === 'yes';
		$serverData['newUserRequireEmail'] = $this->config->getAppValue('core', 'newUser.requireEmail', 'no') === 'yes';
		$serverData['newUserSendEmail'] = $this->config->getAppValue('core', 'newUser.sendEmail', 'yes') === 'yes';

		return new TemplateResponse('settings', 'settings-vue', ['serverData' => $serverData]);
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return JSONResponse
	 */
	public function setPreference(string $key, string $value): JSONResponse {
		$allowed = ['newUser.sendEmail'];
		if (!in_array($key, $allowed, true)) {
			return new JSONResponse([], Http::STATUS_FORBIDDEN);
		}

		$this->config->setAppValue('core', $key, $value);

		return new JSONResponse([]);
	}

	/**
	 * Parse the app value for quota_present
	 *
	 * @param string $quotaPreset
	 * @return array
	 */
	protected function parseQuotaPreset(string $quotaPreset): array {
		// 1 GB, 5 GB, 10 GB => [1 GB, 5 GB, 10 GB]
		$presets = array_filter(array_map('trim', explode(',', $quotaPreset)));
		// Drop default and none, Make array indexes numerically
		return array_values(array_diff($presets, ['default', 'none']));
	}

	/**
	 * check if the admin can change the users password
	 *
	 * The admin can change the passwords if:
	 *
	 *   - no encryption module is loaded and encryption is disabled
	 *   - encryption module is loaded but it doesn't require per user keys
	 *
	 * The admin can not change the passwords if:
	 *
	 *   - an encryption module is loaded and it uses per-user keys
	 *   - encryption is enabled but no encryption modules are loaded
	 *
	 * @return bool
	 */
	protected function canAdminChangeUserPasswords() {
		$isEncryptionEnabled = $this->encryptionManager->isEnabled();
		try {
			$noUserSpecificEncryptionKeys =!$this->encryptionManager->getEncryptionModule()->needDetailedAccessList();
			$isEncryptionModuleLoaded = true;
		} catch (ModuleDoesNotExistsException $e) {
			$noUserSpecificEncryptionKeys = true;
			$isEncryptionModuleLoaded = false;
		}

		$canChangePassword = ($isEncryptionEnabled && $isEncryptionModuleLoaded  && $noUserSpecificEncryptionKeys)
			|| (!$isEncryptionEnabled && !$isEncryptionModuleLoaded)
			|| (!$isEncryptionEnabled && $isEncryptionModuleLoaded && $noUserSpecificEncryptionKeys);

		return $canChangePassword;
	}

	/**
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param string $avatarScope
	 * @param string $displayname
	 * @param string $displaynameScope
	 * @param string $phone
	 * @param string $phoneScope
	 * @param string $email
	 * @param string $emailScope
	 * @param string $website
	 * @param string $websiteScope
	 * @param string $address
	 * @param string $addressScope
	 * @param string $twitter
	 * @param string $twitterScope
	 * @return DataResponse
	 */
	public function setUserSettings($avatarScope,
									$displayname,
									$displaynameScope,
									$phone,
									$phoneScope,
									$email,
									$emailScope,
									$website,
									$websiteScope,
									$address,
									$addressScope,
									$twitter,
									$twitterScope
	) {
		if (!empty($email) && !$this->mailer->validateMailAddress($email)) {
			return new DataResponse(
				[
					'status' => 'error',
					'data' => [
						'message' => $this->l10n->t('Invalid mail address')
					]
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}
		$user = $this->userSession->getUser();
		$data = $this->accountManager->getUser($user);
		$data[AccountManager::PROPERTY_AVATAR] = ['scope' => $avatarScope];
		if ($this->config->getSystemValue('allow_user_to_change_display_name', true) !== false) {
			$data[AccountManager::PROPERTY_DISPLAYNAME] = ['value' => $displayname, 'scope' => $displaynameScope];
			$data[AccountManager::PROPERTY_EMAIL] = ['value' => $email, 'scope' => $emailScope];
		}
		if ($this->appManager->isEnabledForUser('federatedfilesharing')) {
			$federatedFileSharing = new \OCA\FederatedFileSharing\AppInfo\Application();
			$shareProvider = $federatedFileSharing->getFederatedShareProvider();
			if ($shareProvider->isLookupServerUploadEnabled()) {
				$data[AccountManager::PROPERTY_WEBSITE] = ['value' => $website, 'scope' => $websiteScope];
				$data[AccountManager::PROPERTY_ADDRESS] = ['value' => $address, 'scope' => $addressScope];
				$data[AccountManager::PROPERTY_PHONE] = ['value' => $phone, 'scope' => $phoneScope];
				$data[AccountManager::PROPERTY_TWITTER] = ['value' => $twitter, 'scope' => $twitterScope];
			}
		}
		try {
			$this->saveUserSettings($user, $data);
			return new DataResponse(
				[
					'status' => 'success',
					'data' => [
						'userId' => $user->getUID(),
						'avatarScope' => $data[AccountManager::PROPERTY_AVATAR]['scope'],
						'displayname' => $data[AccountManager::PROPERTY_DISPLAYNAME]['value'],
						'displaynameScope' => $data[AccountManager::PROPERTY_DISPLAYNAME]['scope'],
						'email' => $data[AccountManager::PROPERTY_EMAIL]['value'],
						'emailScope' => $data[AccountManager::PROPERTY_EMAIL]['scope'],
						'website' => $data[AccountManager::PROPERTY_WEBSITE]['value'],
						'websiteScope' => $data[AccountManager::PROPERTY_WEBSITE]['scope'],
						'address' => $data[AccountManager::PROPERTY_ADDRESS]['value'],
						'addressScope' => $data[AccountManager::PROPERTY_ADDRESS]['scope'],
						'message' => $this->l10n->t('Settings saved')
					]
				],
				Http::STATUS_OK
			);
		} catch (ForbiddenException $e) {
			return new DataResponse([
				'status' => 'error',
				'data' => [
					'message' => $e->getMessage()
				],
			]);
		}
	}
	/**
	 * update account manager with new user data
	 *
	 * @param IUser $user
	 * @param array $data
	 * @throws ForbiddenException
	 */
	protected function saveUserSettings(IUser $user, array $data) {
		// keep the user back-end up-to-date with the latest display name and email
		// address
		$oldDisplayName = $user->getDisplayName();
		$oldDisplayName = is_null($oldDisplayName) ? '' : $oldDisplayName;
		if (isset($data[AccountManager::PROPERTY_DISPLAYNAME]['value'])
			&& $oldDisplayName !== $data[AccountManager::PROPERTY_DISPLAYNAME]['value']
		) {
			$result = $user->setDisplayName($data[AccountManager::PROPERTY_DISPLAYNAME]['value']);
			if ($result === false) {
				throw new ForbiddenException($this->l10n->t('Unable to change full name'));
			}
		}
		$oldEmailAddress = $user->getEMailAddress();
		$oldEmailAddress = is_null($oldEmailAddress) ? '' : $oldEmailAddress;
		if (isset($data[AccountManager::PROPERTY_EMAIL]['value'])
			&& $oldEmailAddress !== $data[AccountManager::PROPERTY_EMAIL]['value']
		) {
			// this is the only permission a backend provides and is also used
			// for the permission of setting a email address
			if (!$user->canChangeDisplayName()) {
				throw new ForbiddenException($this->l10n->t('Unable to change email address'));
			}
			$user->setEMailAddress($data[AccountManager::PROPERTY_EMAIL]['value']);
		}
		$this->accountManager->updateUser($user, $data);
	}

	/**
	 * Set the mail address of a user
	 *
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param string $account
	 * @param bool $onlyVerificationCode only return verification code without updating the data
	 * @return DataResponse
	 */
	public function getVerificationCode(string $account, bool $onlyVerificationCode): DataResponse {
		$user = $this->userSession->getUser();

		if ($user === null) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		$accountData = $this->accountManager->getUser($user);
		$cloudId = $user->getCloudId();
		$message = 'Use my Federated Cloud ID to share with me: ' . $cloudId;
		$signature = $this->signMessage($user, $message);

		$code = $message . ' ' . $signature;
		$codeMd5 = $message . ' ' . md5($signature);

		switch ($account) {
			case 'verify-twitter':
				$accountData[AccountManager::PROPERTY_TWITTER]['verified'] = AccountManager::VERIFICATION_IN_PROGRESS;
				$msg = $this->l10n->t('In order to verify your Twitter account, post the following tweet on Twitter (please make sure to post it without any line breaks):');
				$code = $codeMd5;
				$type = AccountManager::PROPERTY_TWITTER;
				$data = $accountData[AccountManager::PROPERTY_TWITTER]['value'];
				$accountData[AccountManager::PROPERTY_TWITTER]['signature'] = $signature;
				break;
			case 'verify-website':
				$accountData[AccountManager::PROPERTY_WEBSITE]['verified'] = AccountManager::VERIFICATION_IN_PROGRESS;
				$msg = $this->l10n->t('In order to verify your Website, store the following content in your web-root at \'.well-known/CloudIdVerificationCode.txt\' (please make sure that the complete text is in one line):');
				$type = AccountManager::PROPERTY_WEBSITE;
				$data = $accountData[AccountManager::PROPERTY_WEBSITE]['value'];
				$accountData[AccountManager::PROPERTY_WEBSITE]['signature'] = $signature;
				break;
			default:
				return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		if ($onlyVerificationCode === false) {
			$this->accountManager->updateUser($user, $accountData);

			$this->jobList->add(VerifyUserData::class,
				[
					'verificationCode' => $code,
					'data' => $data,
					'type' => $type,
					'uid' => $user->getUID(),
					'try' => 0,
					'lastRun' => $this->getCurrentTime()
				]
			);
		}

		return new DataResponse(['msg' => $msg, 'code' => $code]);
	}

	/**
	 * get current timestamp
	 *
	 * @return int
	 */
	protected function getCurrentTime(): int {
		return time();
	}

	/**
	 * sign message with users private key
	 *
	 * @param IUser $user
	 * @param string $message
	 *
	 * @return string base64 encoded signature
	 */
	protected function signMessage(IUser $user, string $message): string {
		$privateKey = $this->keyManager->getKey($user)->getPrivate();
		openssl_sign(json_encode($message), $signature, $privateKey, OPENSSL_ALGO_SHA512);
		return base64_encode($signature);
	}
}
