<?php
/**
 * @author Viktar Dubiniuk <dubiniuk@owncloud.com>
 *
 * @copyright Copyright (c) 2019, ownCloud GmbH
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

namespace OCA\Files_Sharing\Controller;

use Exception;
use OC\Files\Filesystem;
use OCP\Constants;
use OC\OCS\Result;
use OCP\AppFramework\OCSController;
use OCP\Defaults;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Lock\ILockingProvider;
use OCP\Lock\LockedException;
use OCP\Share;
use OCP\Share\Exceptions\GenericShareException;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager;
use OCP\Share\IShare;
use OCA\Files_Sharing\Service\NotificationPublisher;
use OCA\Files_Sharing\Helper;
use OCA\Files_Sharing\SharingBlacklist;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Share20OcsController
 *
 * @package OCA\Files_Sharing\Controller
 */
class Share20OcsController extends OCSController {
	/** @var IManager */
	private $shareManager;
	/** @var IGroupManager */
	private $groupManager;
	/** @var IUserManager */
	private $userManager;
	/** @var IRootFolder */
	private $rootFolder;
	/** @var IUserSession */
	private $userSession;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IL10N */
	private $l;
	/** @var IConfig */
	private $config;
	/** @var NotificationPublisher */
	private $notificationPublisher;
	/** @var EventDispatcher  */
	private $eventDispatcher;

	/** @var SharingBlacklist */
	private $sharingBlacklist;
	/**
	 * @var string
	 */
	private $additionalInfoField;

	public function __construct(
		$appName,
		IRequest $request,
		IManager $shareManager,
		IGroupManager $groupManager,
		IUserManager $userManager,
		IRootFolder $rootFolder,
		IURLGenerator $urlGenerator,
		IUserSession $userSession,
		IL10N $l10n,
		IConfig $config,
		NotificationPublisher $notificationPublisher,
		EventDispatcher $eventDispatcher,
		SharingBlacklist $sharingBlacklist
	) {
		parent::__construct($appName, $request);
		$this->request = $request;
		$this->shareManager = $shareManager;
		$this->groupManager = $groupManager;
		$this->userManager = $userManager;
		$this->rootFolder = $rootFolder;
		$this->urlGenerator = $urlGenerator;
		$this->l = $l10n;
		$this->config = $config;
		$this->notificationPublisher = $notificationPublisher;
		$this->eventDispatcher = $eventDispatcher;
		$this->sharingBlacklist = $sharingBlacklist;
		$this->additionalInfoField = $this->config->getAppValue('core', 'user_additional_info_field', '');
		$this->userSession = $userSession;
	}

	/**
	 * Returns the additional info to display behind the display name as configured.
	 *
	 * @param IUser $user user for which to retrieve the additional info
	 * @return string|null additional info or null if none to be displayed
	 */
	private function getAdditionalUserInfo(IUser $user) {
		if ($this->additionalInfoField === 'email') {
			return $user->getEMailAddress();
		} elseif ($this->additionalInfoField === 'id') {
			return $user->getUID();
		}
		return null;
	}

	/**
	 * Convert an IShare to an array for OCS output
	 *
	 * @param IShare $share
	 * @param bool $received whether it's formatting received shares
	 * @return array
	 * @throws NotFoundException In case the node can't be resolved.
	 * @throws \OCP\Files\InvalidPathException
	 * @throws \OCP\Files\StorageNotAvailableException
	 */
	protected function formatShare(IShare $share, $received = false) {
		$sharedBy = $this->userManager->get($share->getSharedBy());
		$shareFileOwner = $this->userManager->get($share->getShareOwner());

		$result = [
			'id' => $share->getId(),
			'share_type' => $share->getShareType(),
			'uid_owner' => $share->getSharedBy(),
			'displayname_owner' => $sharedBy !== null ? $sharedBy->getDisplayName() : $share->getSharedBy(),
			'permissions' => $share->getPermissions(),
			'stime' => $share->getShareTime() ? $share->getShareTime()->getTimestamp() : null,
			'parent' => null,
			'expiration' => null,
			'token' => null,
			'uid_file_owner' => $share->getShareOwner(),
			'displayname_file_owner' => $shareFileOwner !== null ? $shareFileOwner->getDisplayName() : $share->getShareOwner()
		];
		if ($sharedBy !== null) {
			$result['additional_info_owner'] = $this->getAdditionalUserInfo($sharedBy);
		}
		if ($shareFileOwner !== null) {
			$result['additional_info_file_owner'] = $this->getAdditionalUserInfo($shareFileOwner);
		}

		if ($received) {
			// also add state
			$result['state'] = $share->getState();

			// can only fetch path info if mounted already or if owner
			if ($share->getState() === Share::STATE_ACCEPTED || $share->getShareOwner() === $this->userSession->getUser()->getUID()) {
				$userFolder = $this->rootFolder->getUserFolder($this->userSession->getUser()->getUID());
			} else {
				// need to go through owner user for pending shares
				$userFolder = $this->rootFolder->getUserFolder($share->getShareOwner());
			}
		} else {
			$userFolder = $this->rootFolder->getUserFolder($this->userSession->getUser()->getUID());
		}

		$nodes = $userFolder->getById($share->getNodeId(), true);
		$node = $nodes[0] ?? null;
		if ($node === null) {
			throw new NotFoundException();
		}
		$node = $nodes[0];

		$result['path'] = $userFolder->getRelativePath($node->getPath());
		if ($node instanceof Folder) {
			$result['item_type'] = 'folder';
		} else {
			$result['item_type'] = 'file';
		}
		$result['mimetype'] = $node->getMimeType();
		$result['storage_id'] = $node->getStorage()->getId();
		$result['storage'] = $node->getStorage()->getCache()->getNumericStorageId();
		$result['item_source'] = $node->getId();
		$result['file_source'] = $node->getId();
		$result['file_parent'] = $node->getParent()->getId();
		$result['file_target'] = $share->getTarget();

		$expiration = $share->getExpirationDate();
		if ($expiration !== null) {
			$result['expiration'] = $expiration->format('Y-m-d 00:00:00');
		}

		if ($share->getShareType() === Share::SHARE_TYPE_USER) {
			$sharedWith = $this->userManager->get($share->getSharedWith());
			$result['share_with'] = $share->getSharedWith();
			$result['share_with_displayname'] = $sharedWith !== null ? $sharedWith->getDisplayName() : $share->getSharedWith();
			if ($sharedWith !== null) {
				$result['share_with_additional_info'] = $this->getAdditionalUserInfo($sharedWith);
			}
		} elseif ($share->getShareType() === Share::SHARE_TYPE_GROUP) {
			$group = $this->groupManager->get($share->getSharedWith());
			$result['share_with'] = $share->getSharedWith();
			$result['share_with_displayname'] = $group !== null ? $group->getDisplayName() : $share->getSharedWith();
		} elseif ($share->getShareType() === Share::SHARE_TYPE_LINK) {
			if ($share->getPassword() !== null) {
				// Misleading names ahead!: This fields are miss-used to
				// read/write public link password-hashes
				$result['share_with'] = '***redacted***';
				$result['share_with_displayname'] = '***redacted***';
			}
			$result['name'] = $share->getName();

			$result['token'] = $share->getToken();
			if ($share->getToken() !== null) {
				$result['url'] = $this->urlGenerator->linkToRouteAbsolute('files_sharing.sharecontroller.showShare', ['token' => $share->getToken()]);
			}
		} elseif ($share->getShareType() === Share::SHARE_TYPE_REMOTE) {
			$result['share_with'] = $share->getSharedWith();
			$result['share_with_displayname'] = $share->getSharedWith();
			$result['token'] = $share->getToken();
		}

		$result['mail_send'] = $share->getMailSend() ? 1 : 0;

		$result['attributes'] = null;
		if ($attributes = $share->getAttributes()) {
			$result['attributes'] = \json_encode($attributes->toArray());
		}

		return $result;
	}

	/**
	 * Get a specific share by id
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param string $id
	 * @return Result
	 */
	public function getShare($id) {
		if (!$this->shareManager->shareApiEnabled()) {
			return new Result(null, 404, $this->l->t('Share API is disabled'));
		}

		try {
			$share = $this->getShareById($id);
		} catch (ShareNotFound $e) {
			return new Result(null, 404, $this->l->t('Wrong share ID, share doesn\'t exist'));
		}

		if ($this->canAccessShare($share)) {
			try {
				$share = $this->formatShare($share);
				return new Result([$share]);
			} catch (NotFoundException $e) {
				//Fall trough
			}
		}

		return new Result(null, 404, $this->l->t('Wrong share ID, share doesn\'t exist'));
	}

	/**
	 * Delete a share
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param string $id
	 * @return Result
	 * @throws LockedException
	 * @throws NotFoundException
	 * @throws ShareNotFound
	 */
	public function deleteShare($id) {
		if (!$this->shareManager->shareApiEnabled()) {
			return new Result(null, 404, $this->l->t('Share API is disabled'));
		}

		try {
			$share = $this->getShareById($id);
		} catch (ShareNotFound $e) {
			return new Result(null, 404, $this->l->t('Wrong share ID, share doesn\'t exist'));
		}

		try {
			$share->getNode()->lock(ILockingProvider::LOCK_SHARED);
		} catch (LockedException $e) {
			return new Result(null, 404, 'could not delete share');
		}

		if (!$this->canChangeShare($share)) {
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 404, $this->l->t('Could not delete share'));
		}

		$this->shareManager->deleteShare($share);

		$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);

		return new Result();
	}

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @return Result
	 * @throws LockedException
	 * @throws NotFoundException
	 * @throws \OCP\Files\InvalidPathException
	 */
	public function createShare() {
		$share = $this->shareManager->newShare();

		if (!$this->shareManager->shareApiEnabled()) {
			return new Result(null, 404, $this->l->t('Share API is disabled'));
		}

		$name = $this->request->getParam('name', null);

		// Verify path
		$path = $this->request->getParam('path', null);
		if ($path === null) {
			return new Result(null, 404, $this->l->t('Please specify a file or folder path'));
		}

		$userFolder = $this->rootFolder->getUserFolder($this->userSession->getUser()->getUID());

		try {
			$path = $userFolder->get($path);
		} catch (NotFoundException $e) {
			return new Result(null, 404, $this->l->t('Wrong path, file/folder doesn\'t exist'));
		}

		$share->setNode($path);

		try {
			$share->getNode()->lock(ILockingProvider::LOCK_SHARED);
		} catch (LockedException $e) {
			return new Result(null, 404, 'Could not create share');
		}

		$shareType = (int)$this->request->getParam('shareType', '-1');

		// Parse permissions (if available)
		$permissions = $this->getPermissionsFromRequest();
		if ($permissions === null) {
			if ($shareType !== Share::SHARE_TYPE_LINK) {
				$permissions = $this->config->getAppValue('core', 'shareapi_default_permissions', Constants::PERMISSION_ALL);
			} else {
				$permissions = Constants::PERMISSION_ALL;
			}
		} else {
			$permissions = (int)$permissions;
		}

		/*
		 * Hack for https://github.com/owncloud/core/issues/22587
		 * We check the permissions via webdav. But the permissions of the mount point
		 * do not equal the share permissions. Here we fix that for federated mounts.
		 */
		if ($path->getStorage()->instanceOfStorage('OCA\Files_Sharing\External\Storage')) {
			$permissions &= ~($permissions & ~$path->getPermissions());
		}

		//Expire date
		$expireDate = $this->request->getParam('expireDate', '');
		if ($expireDate !== '') {
			try {
				$expireDate = $this->parseDate($expireDate);
				$share->setExpirationDate($expireDate);
			} catch (Exception $e) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 404, $this->l->t('Invalid date, date format must be YYYY-MM-DD'));
			}
		}

		$shareWith = $this->request->getParam('shareWith', null);

		$globalAutoAccept = $this->config->getAppValue('core', 'shareapi_auto_accept_share', 'yes') === 'yes';
		if ($shareType === Share::SHARE_TYPE_USER) {
			$userAutoAccept = false;
			if ($globalAutoAccept) {
				$userAutoAccept = $this->config->getUserValue($shareWith, 'files_sharing', 'auto_accept_share', 'yes') === 'yes';
			}
			// Valid user is required to share
			if (!\is_string($shareWith) || !$this->userManager->userExists($shareWith)) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 404, $this->l->t('Please specify a valid user'));
			}
			$share->setSharedWith($shareWith);
			$share->setPermissions($permissions);
			if ($userAutoAccept) {
				$share->setState(Share::STATE_ACCEPTED);
			} else {
				$share->setState(Share::STATE_PENDING);
			}
		} elseif ($shareType === Share::SHARE_TYPE_GROUP) {
			if (!$this->shareManager->allowGroupSharing()) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 404, $this->l->t('Group sharing is disabled by the administrator'));
			}

			// Valid group is required to share
			if (!\is_string($shareWith) || !$this->groupManager->groupExists($shareWith)) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 404, $this->l->t('Please specify a valid group'));
			}
			if ($this->sharingBlacklist->isGroupBlacklisted($this->groupManager->get($shareWith))) {
				return new Result(null, 403, $this->l->t('The group is blacklisted for sharing'));
			}
			$share->setSharedWith($shareWith);
			$share->setPermissions($permissions);
			if ($globalAutoAccept) {
				$share->setState(Share::STATE_ACCEPTED);
			} else {
				$share->setState(Share::STATE_PENDING);
			}
		} elseif ($shareType === Share::SHARE_TYPE_LINK) {
			//Can we even share links?
			if (!$this->shareManager->shareApiAllowLinks()) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 404, $this->l->t('Public link sharing is disabled by the administrator'));
			}

			// legacy way, expecting that this won't be used together with "create-only" shares
			$publicUpload = $this->request->getParam('publicUpload', null);
			// a few permission checks
			if ($publicUpload === 'true' || $permissions === Constants::PERMISSION_CREATE) {
				// Check if public upload is allowed
				if (!$this->shareManager->shareApiLinkAllowPublicUpload()) {
					$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
					return new Result(null, 403, $this->l->t('Public upload disabled by the administrator'));
				}

				// Public upload can only be set for folders
				if ($path instanceof File) {
					$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
					return new Result(null, 404, $this->l->t('Public upload is only possible for publicly shared folders'));
				}
			}

			// convert to permissions
			if ($publicUpload === 'true') {
				$share->setPermissions(
					Constants::PERMISSION_READ |
					Constants::PERMISSION_CREATE |
					Constants::PERMISSION_UPDATE |
					Constants::PERMISSION_DELETE
				);
			} elseif ($permissions === Constants::PERMISSION_CREATE ||
				$permissions === (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE | Constants::PERMISSION_DELETE) ||
				$permissions === (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE)) {
				$share->setPermissions($permissions);
			} else {
				// because when "publicUpload" is passed usually no permissions are set,
				// which defaults to ALL. But in the case of link shares we default to READ...
				$share->setPermissions(Constants::PERMISSION_READ);
			}

			// set name only if passed as parameter, empty string is allowed
			if ($name !== null) {
				$share->setName($name);
			}

			// Set password
			$password = $this->request->getParam('password', '');

			if ($password !== '') {
				$share->setPassword($password);
			}
		} elseif ($shareType === Share::SHARE_TYPE_REMOTE) {
			if (!$this->shareManager->outgoingServer2ServerSharesAllowed()) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 403, $this->l->t('Sharing %s failed because the back end does not allow shares from type %s', [$path->getPath(), $shareType]));
			}
			if (!\is_string($shareWith)) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 404, $this->l->t('shareWith parameter must be a string'));
			}
			$share->setSharedWith($shareWith);
			$share->setPermissions($permissions);
		} else {
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 400, $this->l->t('Unknown share type'));
		}

		$share->setShareType($shareType);
		$share->setSharedBy($this->userSession->getUser()->getUID());

		// set attributes array from request, or if not provided set empty array
		$newAttributes = $this->request->getParam('attributes', null);
		if ($newAttributes !== null) {
			$share = $this->setShareAttributes($share, $newAttributes);
		} else {
			$share = $this->setShareAttributes($share, []);
		}

		try {
			$share = $this->shareManager->createShare($share);
			/**
			 * If auto accept enabled by admin and it is a group share,
			 * create sub-share for auto accept disabled users in pending state.
			 */
			if ($share->getShareType() === Share::SHARE_TYPE_GROUP && $globalAutoAccept) {
				$subShare = $share;
				$group = $this->groupManager->get($share->getSharedWith());
				foreach ($group->getUsers() as $user) {
					$userAutoAccept = $this->config->getUserValue($user->getUID(), 'files_sharing', 'auto_accept_share', 'yes') === 'yes';
					if (!$userAutoAccept) {
						$subShare->setState(Share::STATE_PENDING);
						$this->shareManager->updateShareForRecipient($subShare, $user->getUID());
					}
				}
			}
		} catch (GenericShareException $e) {
			$code = $e->getCode() === 0 ? 403 : $e->getCode();
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, $code, $e->getHint());
		} catch (Exception $e) {
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 403, $e->getMessage());
		}

		$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);

		$formattedShareAfterCreate = $this->formatShare($share);

		return new Result($formattedShareAfterCreate);
	}

	/**
	 * @param File|Folder $node
	 * @param boolean $includeTags include tags in response
	 * @param int|null $stateFilter state filter or empty for all, defaults to 0 (accepted)
	 * @return Result
	 */
	private function getSharedWithMe($node = null, $includeTags, $stateFilter = 0) {
		$userShares = $this->shareManager->getSharedWith($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_USER, $node, -1, 0);
		$groupShares = $this->shareManager->getSharedWith($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_GROUP, $node, -1, 0);

		$shares = \array_merge($userShares, $groupShares);

		$shares = \array_filter($shares, function (IShare $share) {
			return $share->getShareOwner() !== $this->userSession->getUser()->getUID();
		});

		$formatted = [];
		foreach ($shares as $share) {
			if (($stateFilter === null || $share->getState() === $stateFilter) &&
				$this->canAccessShare($share)) {
				try {
					/**
					 * Check if the group to which the user belongs is not allowed
					 * to reshare
					 */
					if ($this->shareManager->sharingDisabledForUser($this->userSession->getUser()->getUID())) {
						/**
						 * Now set the permission to 15. Which will allow not to reshare.
						 */
						$permissionEvaluated = $share->getPermissions() & ~Constants::PERMISSION_SHARE;
						$share->setPermissions($permissionEvaluated);
					}
					$formatted[] = $this->formatShare($share, true);
				} catch (NotFoundException $e) {
					// Ignore this share
				}
			}
		}

		if ($includeTags) {
			$formatted = \OCA\Files\Helper::populateTagsForShares($formatted);
		}

		return new Result($formatted);
	}

	/**
	 * @param Folder $folder
	 * @return Result
	 * @throws NotFoundException
	 */
	private function getSharesInDir($folder) {
		if (!($folder instanceof Folder)) {
			return new Result(null, 400, $this->l->t('Not a directory'));
		}

		$nodes = $folder->getDirectoryListing();
		/** @var IShare[] $shares */
		$shares = [];
		foreach ($nodes as $node) {
			$shares = \array_merge($shares, $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_USER, $node, false, -1, 0));
			$shares = \array_merge($shares, $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_GROUP, $node, false, -1, 0));
			$shares = \array_merge($shares, $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_LINK, $node, false, -1, 0));
			if ($this->shareManager->outgoingServer2ServerSharesAllowed()) {
				$shares = \array_merge($shares, $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_REMOTE, $node, false, -1, 0));
			}
		}

		$formatted = [];
		foreach ($shares as $share) {
			try {
				$formatted[] = $this->formatShare($share);
			} catch (NotFoundException $e) {
				//Ignore this share
			}
		}

		return new Result($formatted);
	}

	/**
	 * The getShares function.
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * - Get shares by the current user
	 * - Get shares by the current user and reshares (?reshares=true)
	 * - Get shares with the current user (?shared_with_me=true)
	 * - Get shares for a specific path (?path=...)
	 * - Get all shares in a folder (?subfiles=true&path=..)
	 *
	 * @return Result
	 * @throws LockedException
	 */
	public function getShares() {
		if (!$this->shareManager->shareApiEnabled()) {
			return new Result();
		}

		$sharedWithMe = $this->request->getParam('shared_with_me', null);
		$reshares = $this->request->getParam('reshares', null);
		$subfiles = $this->request->getParam('subfiles');
		$path = $this->request->getParam('path', null);

		$includeTags = $this->request->getParam('include_tags', false);

		if ($path !== null) {
			$userFolder = $this->rootFolder->getUserFolder($this->userSession->getUser()->getUID());
			try {
				$path = $userFolder->get($path);
				$path->lock(ILockingProvider::LOCK_SHARED);
			} catch (NotFoundException $e) {
				return new Result(null, 404, $this->l->t('Wrong path, file/folder doesn\'t exist'));
			} catch (LockedException $e) {
				return new Result(null, 404, $this->l->t('Could not lock path'));
			}
		}

		if ($sharedWithMe === 'true') {
			$stateFilter = $this->request->getParam('state', Share::STATE_ACCEPTED);
			if ($stateFilter === '') {
				$stateFilter = Share::STATE_ACCEPTED;
			} elseif ($stateFilter === 'all') {
				$stateFilter = null; // which means all
			} else {
				$stateFilter = (int)$stateFilter;
			}
			$result = $this->getSharedWithMe($path, $includeTags, $stateFilter);
			if ($path !== null) {
				$path->unlock(ILockingProvider::LOCK_SHARED);
			}
			return $result;
		}

		if ($subfiles === 'true') {
			$result = $this->getSharesInDir($path);
			if ($path !== null) {
				$path->unlock(ILockingProvider::LOCK_SHARED);
			}
			return $result;
		}

		if ($reshares === 'true') {
			$reshares = true;
		} else {
			$reshares = false;
		}

		// Get all shares
		$userShares = $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_USER, $path, $reshares, -1, 0);
		$groupShares = $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_GROUP, $path, $reshares, -1, 0);
		$linkShares = $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_LINK, $path, $reshares, -1, 0);
		$shares = \array_merge($userShares, $groupShares, $linkShares);

		if ($this->shareManager->outgoingServer2ServerSharesAllowed()) {
			$federatedShares = $this->shareManager->getSharesBy($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_REMOTE, $path, $reshares, -1, 0);
			$shares = \array_merge($shares, $federatedShares);
		}

		$formatted = [];
		foreach ($shares as $share) {
			try {
				$formatted[] = $this->formatShare($share);
			} catch (NotFoundException $e) {
				//Ignore share
			}
		}

		if ($includeTags) {
			$formatted = \OCA\Files\Helper::populateTagsForShares($formatted);
		}

		if ($path !== null) {
			$path->unlock(ILockingProvider::LOCK_SHARED);
		}

		return new Result($formatted);
	}

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return Result
	 * @throws LockedException
	 * @throws NotFoundException
	 */
	public function updateShare($id) {
		if (!$this->shareManager->shareApiEnabled()) {
			return new Result(null, 404, $this->l->t('Share API is disabled'));
		}

		try {
			$share = $this->getShareById($id);
		} catch (ShareNotFound $e) {
			return new Result(null, 404, $this->l->t('Wrong share ID, share doesn\'t exist'));
		}

		$share->getNode()->lock(ILockingProvider::LOCK_SHARED);

		if (!$this->canChangeShare($share)) {
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 404, $this->l->t('Could not update share'));
		}

		$permissions = $this->getPermissionsFromRequest();
		$password = $this->request->getParam('password', null);
		$publicUpload = $this->request->getParam('publicUpload', null);
		$expireDate = $this->request->getParam('expireDate', null);
		$name = $this->request->getParam('name', null);

		/*
		 * expirationdate, password and publicUpload only make sense for link shares
		 */
		if ($share->getShareType() === Share::SHARE_TYPE_LINK) {
			if ($permissions === null && $password === null && $publicUpload === null && $expireDate === null && $name === null) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 400, 'Wrong or no update parameter given');
			}

			$newPermissions = null;
			if ($publicUpload === 'true') {
				$newPermissions = Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE | Constants::PERMISSION_DELETE;
			} elseif ($publicUpload === 'false') {
				$newPermissions = Constants::PERMISSION_READ;
			}

			if ($permissions !== null) {
				$newPermissions = (int)$permissions;
			}

			if ($newPermissions !== null &&
				$newPermissions !== Constants::PERMISSION_READ &&
				$newPermissions !== Constants::PERMISSION_CREATE &&
				$newPermissions !== (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE) &&
				// legacy
				$newPermissions !== (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE) &&
				// correct
				$newPermissions !== (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE | Constants::PERMISSION_DELETE)
			) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 400, $this->l->t('Can\'t change permissions for public share links'));
			}

			if (
				// legacy
				$newPermissions === (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE) ||
				// correct
				$newPermissions === (Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE | Constants::PERMISSION_DELETE)
			) {
				if (!$this->shareManager->shareApiLinkAllowPublicUpload()) {
					$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
					return new Result(null, 403, $this->l->t('Public upload disabled by the administrator'));
				}

				if (!($share->getNode() instanceof Folder)) {
					$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
					return new Result(null, 400, $this->l->t('Public upload is only possible for publicly shared folders'));
				}
			}

			// create-only (upload-only)
			if (
				$newPermissions === Constants::PERMISSION_CREATE
			) {
				if (!$this->shareManager->shareApiLinkAllowPublicUpload()) {
					$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
					return new Result(null, 403, $this->l->t('Public upload disabled by the administrator'));
				}

				if (!($share->getNode() instanceof Folder)) {
					$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
					return new Result(null, 400, $this->l->t('Public upload is only possible for publicly shared folders'));
				}
			}

			// set name only if passed as parameter, empty string is allowed
			if ($name !== null) {
				$share->setName($name);
			}

			if ($newPermissions !== null) {
				$share->setPermissions($newPermissions);
			}

			if ($password === '') {
				$share->setPassword(null);
			} elseif ($password !== null) {
				$share->setPassword($password);
			}
		} else {
			// For other shares only permissions is valid.
			if ($permissions !== null) {
				$newPermissions = (int)$permissions;
				$share->setPermissions($newPermissions);
			}
		}

		if ($expireDate === '') {
			$share->setExpirationDate(null);
		} elseif ($expireDate !== null) {
			try {
				$expireDate = $this->parseDate($expireDate);
			} catch (Exception $e) {
				$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
				return new Result(null, 400, $e->getMessage());
			}
			$share->setExpirationDate($expireDate);
		}

		// update attributes if provided
		$newAttributes = $this->request->getParam('attributes', null);
		if ($newAttributes !== null) {
			$share = $this->setShareAttributes($share, $newAttributes);
		}

		try {
			$share = $this->shareManager->updateShare($share);
		} catch (GenericShareException $e) {
			$code = $e->getCode() === 0 ? 403 : $e->getCode();
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, $code, $e->getHint());
		} catch (Exception $e) {
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 400, $e->getMessage());
		}

		$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);

		return new Result($this->formatShare($share));
	}

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return Result
	 */
	public function acceptShare($id) {
		return $this->updateShareState($id, Share::STATE_ACCEPTED);
	}

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return Result
	 */
	public function declineShare($id) {
		return $this->updateShareState($id, Share::STATE_REJECTED);
	}

	/**
	 * @param $id
	 * @param $state
	 * @return Result
	 * @throws LockedException
	 * @throws NotFoundException
	 */
	private function updateShareState($id, $state) {
		$eventName = '';
		if ($state === Share::STATE_ACCEPTED) {
			$eventName = 'accept';
		} elseif ($state === Share::STATE_REJECTED) {
			$eventName = 'reject';
		}

		if (!$this->shareManager->shareApiEnabled()) {
			return new Result(null, 404, $this->l->t('Share API is disabled'));
		}

		try {
			$share = $this->getShareById($id, $this->userSession->getUser()->getUID());
			$this->eventDispatcher->dispatch('share.before' . $eventName, new GenericEvent(null, ['share' => $share]));
		} catch (ShareNotFound $e) {
			return new Result(null, 404, $this->l->t('Wrong share ID, share doesn\'t exist'));
		}

		$node = $share->getNode();
		$node->lock(ILockingProvider::LOCK_SHARED);

		// this checks that we are either the owner or recipient
		if (!$this->canAccessShare($share)) {
			$node->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 404, $this->l->t('Wrong share ID, share doesn\'t exist'));
		}

		// only recipient can accept/reject share
		if ($share->getShareOwner() === $this->userSession->getUser()->getUID() ||
			$share->getSharedBy() === $this->userSession->getUser()->getUID()) {
			$node->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 403, $this->l->t('Only recipient can change accepted state'));
		}

		if ($share->getState() === $state) {
			if ($eventName !== '') {
				$this->eventDispatcher->dispatch('share.after' . $eventName, new GenericEvent(null, ['share' => $share]));
			}
			// if there are no changes in the state, just return the share as if the change was successful
			$node->unlock(ILockingProvider::LOCK_SHARED);
			return new Result([$this->formatShare($share, true)]);
		}

		// we actually want to update all shares related to the node in case there are multiple
		// incoming shares for the same node (ex: receiving simultaneously through group share and user share)
		$allShares = $this->shareManager->getSharedWith($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_USER, $node, -1, 0);
		$allShares = \array_merge($allShares, $this->shareManager->getSharedWith($this->userSession->getUser()->getUID(), Share::SHARE_TYPE_GROUP, $node, -1, 0));

		// resolve and deduplicate target if accepting
		if ($state === Share::STATE_ACCEPTED) {
			$share = $this->deduplicateShareTarget($share);
		}

		$share->setState($state);

		try {
			foreach ($allShares as $aShare) {
				$aShare->setState($share->getState());
				$aShare->setTarget($share->getTarget());
				$this->shareManager->updateShareForRecipient($aShare, $this->userSession->getUser()->getUID());
			}
		} catch (Exception $e) {
			$share->getNode()->unlock(ILockingProvider::LOCK_SHARED);
			return new Result(null, 400, $e->getMessage());
		}

		$node->unlock(ILockingProvider::LOCK_SHARED);

		// FIXME: needs public API!
		Filesystem::tearDown();
		// FIXME: trigger mount for user to make sure the new node is mounted already
		// before formatShare resolves it
		$this->rootFolder->getUserFolder($this->userSession->getUser()->getUID());

		$this->notificationPublisher->discardNotificationForUser($share, $this->userSession->getUser()->getUID());

		if ($eventName !== '') {
			$this->eventDispatcher->dispatch('share.after' . $eventName, new GenericEvent(null, ['share' => $share]));
		}
		return new Result([$this->formatShare($share, true)]);
	}

	/**
	 * Deduplicate the share target in the current user home folder,
	 * based on configured share folder
	 *
	 * @param IShare $share share target to deduplicate
	 * @return IShare same share with target updated if necessary
	 */
	private function deduplicateShareTarget(IShare $share) {
		$userFolder = $this->rootFolder->getUserFolder($this->userSession->getUser()->getUID());
		$parentDir = \dirname($share->getTarget());
		if (!$userFolder->nodeExists($parentDir)) {
			$parentDir = Helper::getShareFolder();
			$pathAttempt = Filesystem::normalizePath($parentDir . '/' . $share->getTarget());
		} else {
			$pathAttempt = Filesystem::normalizePath($share->getTarget());
		}

		$pathinfo = \pathinfo($pathAttempt);
		$ext = isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '';
		$name = $pathinfo['filename'];

		$i = 2;
		while ($userFolder->nodeExists($pathAttempt)) {
			$pathAttempt = Filesystem::normalizePath($parentDir . '/' . $name . ' ('.$i.')' . $ext);
			$i++;
		}

		$share->setTarget($pathAttempt);

		return $share;
	}

	/**
	 * Check session user is owner or sharer of the share
	 *
	 * @param IShare $share
	 * @return bool
	 */
	protected function canChangeShare(IShare $share) {
		// Only owner or the sharer of the file can update or delete share
		if ($share->getShareOwner() === $this->userSession->getUser()->getUID() ||
			$share->getSharedBy() === $this->userSession->getUser()->getUID()
		) {
			return true;
		}
		return false;
	}

	/**
	 * @param IShare $share
	 * @return bool
	 */
	protected function canAccessShare(IShare $share) {
		// A file with permissions 0 can't be accessed by us,
		// unless it's a rejected sub-group share in which case we want it visible to let the user accept it again
		if ($share->getPermissions() === 0
			&& !($share->getShareType() === Share::SHARE_TYPE_GROUP && $share->getState() === Share::STATE_REJECTED)) {
			return false;
		}

		// Owner of the file and the sharer of the file can always get share
		if ($share->getShareOwner() === $this->userSession->getUser()->getUID() ||
			$share->getSharedBy() === $this->userSession->getUser()->getUID()
		) {
			return true;
		}

		// If the share is shared with you (or a group you are a member of)
		if ($share->getShareType() === Share::SHARE_TYPE_USER &&
			$share->getSharedWith() === $this->userSession->getUser()->getUID()) {
			return true;
		}

		if ($share->getShareType() === Share::SHARE_TYPE_GROUP) {
			$sharedWith = $this->groupManager->get($share->getSharedWith());
			if ($sharedWith !== null && $sharedWith->inGroup($this->userSession->getUser())) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Make sure that the passed date is valid ISO 8601
	 * So YYYY-MM-DD
	 * If not throw an exception
	 *
	 * @param string $expireDate
	 *
	 * @throws Exception
	 * @return \DateTime
	 */
	private function parseDate($expireDate) {
		try {
			$date = new \DateTime($expireDate);
		} catch (Exception $e) {
			throw new Exception('Invalid date. Format must be YYYY-MM-DD');
		}

		if ($date === false) {
			throw new Exception('Invalid date. Format must be YYYY-MM-DD');
		}

		$date->setTime(0, 0, 0);

		return $date;
	}

	/**
	 * Since we have multiple providers but the OCS Share API v1 does
	 * not support this we need to check all backends.
	 *
	 * @param string $id
	 * @param null $recipient
	 * @return IShare
	 * @throws ShareNotFound
	 */
	private function getShareById($id, $recipient = null) {
		$share = null;

		// First check if it is an internal share.
		try {
			$share = $this->shareManager->getShareById('ocinternal:'.$id, $recipient);
		} catch (ShareNotFound $e) {
			if (!$this->shareManager->outgoingServer2ServerSharesAllowed()) {
				throw new ShareNotFound();
			}

			$share = $this->shareManager->getShareById('ocFederatedSharing:' . $id, $recipient);
		}

		return $share;
	}

	/**
	 * @param IShare $share
	 * @param string[][] $formattedShareAttributes
	 * @return IShare modified share
	 */
	private function setShareAttributes(IShare $share, $formattedShareAttributes) {
		$newShareAttributes = $this->shareManager->newShare()->newAttributes();
		foreach ($formattedShareAttributes as $formattedAttr) {
			$value = isset($formattedAttr['value']) ? $formattedAttr['value'] : null;
			if (isset($formattedAttr['enabled'])) {
				$value = (bool) \json_decode($formattedAttr['enabled']);
			}
			if ($value !== null) {
				$newShareAttributes->setAttribute(
					$formattedAttr['scope'],
					$formattedAttr['key'],
					$value
				);
			}
		}
		$share->setAttributes($newShareAttributes);

		return $share;
	}

	/**
	 * @return mixed
	 */
	private function getPermissionsFromRequest() {
		// int-based permissions are set -> use them
		$permissions = $this->request->getParam('permissions', null);
		if ($permissions !== null) {
			return $permissions;
		}
		// have permissions been set via attributes?
		$attributes = $this->request->getParam('attributes', null);
		if ($attributes === null) {
			return null;
		}
		$permission = 0;
		foreach ($attributes as $attribute) {
			if ($attribute['scope'] === 'ownCloud') {
				$key = $attribute['key'];
				$value = $attribute['value'];
				if ($key === 'create' && $value === 'true') {
					$permission |= Constants::PERMISSION_CREATE;
				}
				if ($key === 'read' && $value === 'true') {
					$permission |= Constants::PERMISSION_READ;
				}
				if ($key === 'update' && $value === 'true') {
					$permission |= Constants::PERMISSION_UPDATE;
				}
				if ($key === 'delete' && $value === 'true') {
					$permission |= Constants::PERMISSION_DELETE;
				}
				if ($key === 'share' && $value === 'true') {
					$permission |= Constants::PERMISSION_SHARE;
				}
			}
		}

		return $permission;
	}
}
