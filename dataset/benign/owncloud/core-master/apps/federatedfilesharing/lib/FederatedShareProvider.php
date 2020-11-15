<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OCA\FederatedFileSharing;

use OC\Share20\Exception\InvalidShare;
use OC\Share20\Share;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUserManager;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IShare;
use OCP\Share\IShareProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class FederatedShareProvider
 *
 * @package OCA\FederatedFileSharing
 */
class FederatedShareProvider implements IShareProvider {
	const SHARE_TYPE_REMOTE = 6;

	/** @var IDBConnection */
	private $dbConnection;

	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	/** @var AddressHandler */
	private $addressHandler;

	/** @var Notifications */
	private $notifications;

	/** @var TokenHandler */
	private $tokenHandler;

	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	/** @var IRootFolder */
	private $rootFolder;

	/** @var IConfig */
	private $config;

	/** @var string */
	private $externalShareTable = 'share_external';

	/** @var string */
	private $shareTable = 'share';

	/** @var IUserManager */
	private $userManager;

	/**
	 * DefaultShareProvider constructor.
	 *
	 * @param IDBConnection $connection
	 * @param EventDispatcherInterface $eventDispatcher
	 * @param AddressHandler $addressHandler
	 * @param Notifications $notifications
	 * @param TokenHandler $tokenHandler
	 * @param IL10N $l10n
	 * @param ILogger $logger
	 * @param IRootFolder $rootFolder
	 * @param IConfig $config
	 * @param IUserManager $userManager
	 */
	public function __construct(
			IDBConnection $connection,
			EventDispatcherInterface $eventDispatcher,
			AddressHandler $addressHandler,
			Notifications $notifications,
			TokenHandler $tokenHandler,
			IL10N $l10n,
			ILogger $logger,
			IRootFolder $rootFolder,
			IConfig $config,
			IUserManager $userManager
	) {
		$this->dbConnection = $connection;
		$this->eventDispatcher = $eventDispatcher;
		$this->addressHandler = $addressHandler;
		$this->notifications = $notifications;
		$this->tokenHandler = $tokenHandler;
		$this->l = $l10n;
		$this->logger = $logger;
		$this->rootFolder = $rootFolder;
		$this->config = $config;
		$this->userManager = $userManager;
	}

	/**
	 * Return the identifier of this provider.
	 *
	 * @return string Containing only [a-zA-Z0-9]
	 */
	public function identifier() {
		return 'ocFederatedSharing';
	}

	/**
	 * Share a path
	 *
	 * @param IShare $share
	 * @return IShare The share object
	 * @throws ShareNotFound
	 * @throws \Exception
	 */
	public function create(IShare $share) {
		$shareWith = $share->getSharedWith();
		$itemSource = $share->getNodeId();
		$itemType = $share->getNodeType();
		$permissions = $share->getPermissions();
		$sharedBy = $share->getSharedBy();

		/*
		 * Check if file is not already shared with the remote user
		 */
		$alreadyShared = $this->getSharedWith($shareWith, self::SHARE_TYPE_REMOTE, $share->getNode(), 1, 0);
		if (!empty($alreadyShared)) {
			$message = 'Sharing %s failed, because this item is already shared with %s';
			$message_t = $this->l->t('Sharing %s failed, because this item is already shared with %s', [$share->getNode()->getName(), $shareWith]);
			$this->logger->debug(\sprintf($message, $share->getNode()->getName(), $shareWith), ['app' => 'Federated File Sharing']);
			throw new \Exception($message_t);
		}

		// don't allow federated shares if source and target server are the same
		$currentUser = $sharedBy;
		$ownerAddress =  $this->addressHandler->getLocalUserFederatedAddress($currentUser);
		$shareWithAddress = new Address($shareWith);

		if ($ownerAddress->equalTo($shareWithAddress)) {
			$message = 'Not allowed to create a federated share with the same user.';
			$message_t = $this->l->t('Not allowed to create a federated share with the same user');
			$this->logger->debug($message, ['app' => 'Federated File Sharing']);
			throw new \Exception($message_t);
		}

		$share->setSharedWith($shareWithAddress->getCloudId());

		try {
			$remoteShare = $this->getShareFromExternalShareTable($share);
		} catch (ShareNotFound $e) {
			$remoteShare = null;
		}

		if ($remoteShare) {
			try {
				$uidOwner = $remoteShare['owner'] . '@' . $remoteShare['remote'];
				$shareId = $this->addShareToDB($itemSource, $itemType, $shareWith, $sharedBy, $uidOwner, $permissions, 'tmp_token_' . \time());
				$share->setId($shareId);
				list($token, $remoteId) = $this->askOwnerToReShare($shareWith, $share, $shareId);
				// remote share was create successfully if we get a valid token as return
				$send = \is_string($token) && $token !== '';
			} catch (\Exception $e) {
				// fall back to old re-share behavior if the remote server
				// doesn't support flat re-shares (was introduced with ownCloud 9.1)
				$this->removeShareFromTable($share);
				$shareId = $this->createFederatedShare($share);
			}
			if ($send) {
				$this->updateSuccessfulReShare($shareId, $token);
				$this->storeRemoteId($shareId, $remoteId);
			} else {
				$this->removeShareFromTable($share);
				$message_t = $this->l->t('File is already shared with %s', [$shareWith]);
				throw new \Exception($message_t);
			}
		} else {
			$shareId = $this->createFederatedShare($share);
		}

		$data = $this->getRawShare($shareId);
		return $this->createShareObject($data);
	}

	/**
	 * create federated share and inform the recipient
	 *
	 * @param IShare $share
	 * @return int
	 * @throws ShareNotFound
	 * @throws \Exception
	 */
	protected function createFederatedShare(IShare $share) {
		$token = $this->tokenHandler->generateToken();
		$shareId = $this->addShareToDB(
			$share->getNodeId(),
			$share->getNodeType(),
			$share->getSharedWith(),
			$share->getSharedBy(),
			$share->getShareOwner(),
			$share->getPermissions(),
			$token
		);

		try {
			$sharedBy = $share->getSharedBy();
			if ($this->userManager->userExists($sharedBy)) {
				$sharedByAddress = $this->addressHandler->getLocalUserFederatedAddress($sharedBy);
			} else {
				$sharedByAddress = new Address($sharedBy);
			}

			$owner = $share->getShareOwner();
			$ownerAddress = $this->addressHandler->getLocalUserFederatedAddress($owner);
			$sharedWith = $share->getSharedWith();
			$shareWithAddress = new Address($sharedWith);
			$result = $this->notifications->sendRemoteShare(
				$shareWithAddress,
				$ownerAddress,
				$sharedByAddress,
				$token,
				$share->getNode()->getName(),
				$shareId
			);

			/* Check for failure or null return from sending and pick up an error message
			 * if there is one coming from the remote server, otherwise use a generic one.
			 */
			if (\is_bool($result)) {
				$status = $result;
			} elseif (isset($result['ocs']['meta']['status'])) {
				$status = $result['ocs']['meta']['status'];
			} else {
				$status = false;
			}

			if ($status === false) {
				$msg = $result['ocs']['meta']['message'] ?? false;
				if (!$msg) {
					$message_t = $this->l->t('Sharing %s failed, could not find %s, maybe the server is currently unreachable.',
						[$share->getNode()->getName(), $share->getSharedWith()]);
				} else {
					$message_t = $this->l->t("Federated Sharing failed: %s", [$msg]);
				}
				throw new \Exception($message_t);
			}
		} catch (\Exception $e) {
			$this->logger->error('Failed to notify remote server of federated share, removing share (' . $e->getMessage() . ')');
			$this->removeShareFromTableById($shareId);
			throw $e;
		}

		return $shareId;
	}

	/**
	 * @param string $shareWith
	 * @param IShare $share
	 * @param string $shareId internal share Id
	 * @return array
	 * @throws \Exception
	 */
	protected function askOwnerToReShare($shareWith, IShare $share, $shareId) {
		$remoteShare = $this->getShareFromExternalShareTable($share);
		$token = $remoteShare['share_token'];
		$remoteId = $remoteShare['remote_id'];
		$remote = $remoteShare['remote'];

		list($token, $remoteId) = $this->notifications->requestReShare(
			$token,
			$remoteId,
			$shareId,
			$remote,
			$shareWith,
			$share->getPermissions()
		);

		return [$token, $remoteId];
	}

	/**
	 * get federated share from the share_external table but exclude mounted link shares
	 *
	 * @param IShare $share
	 * @return array
	 * @throws ShareNotFound
	 */
	protected function getShareFromExternalShareTable(IShare $share) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->select('*')->from($this->externalShareTable)
			->where($query->expr()->eq('user', $query->createNamedParameter($share->getShareOwner())))
			->andWhere($query->expr()->eq('mountpoint', $query->createNamedParameter($share->getTarget())));
		$result = $query->execute()->fetchAll();

		if (isset($result[0]) && $result[0]['remote_id'] !== "") {
			return $result[0];
		}

		throw new ShareNotFound('share not found in share_external table');
	}

	/**
	 * add share to the database and return the ID
	 *
	 * @param int $itemSource
	 * @param string $itemType
	 * @param string $shareWith
	 * @param string $sharedBy
	 * @param string $uidOwner
	 * @param int $permissions
	 * @param string $token
	 * @return int
	 */
	private function addShareToDB($itemSource, $itemType, $shareWith, $sharedBy, $uidOwner, $permissions, $token) {
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->insert($this->shareTable)
			->setValue('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE))
			->setValue('item_type', $qb->createNamedParameter($itemType))
			->setValue('item_source', $qb->createNamedParameter($itemSource))
			->setValue('file_source', $qb->createNamedParameter($itemSource))
			->setValue('share_with', $qb->createNamedParameter($shareWith))
			->setValue('uid_owner', $qb->createNamedParameter($uidOwner))
			->setValue('uid_initiator', $qb->createNamedParameter($sharedBy))
			->setValue('permissions', $qb->createNamedParameter($permissions))
			->setValue('token', $qb->createNamedParameter($token))
			->setValue('stime', $qb->createNamedParameter(\time()));

		/*
		 * Added to fix https://github.com/owncloud/core/issues/22215
		 * Can be removed once we get rid of ajax/share.php
		 */
		$qb->setValue('file_target', $qb->createNamedParameter(''));

		$qb->execute();
		$id = $qb->getLastInsertId();

		return (int)$id;
	}

	/**
	 * Update a share
	 *
	 * @param IShare $share
	 * @return IShare The share object
	 */
	public function update(IShare $share) {
		/*
		 * We allow updating the permissions of federated shares
		 */
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->update($this->shareTable)
				->where($qb->expr()->eq('id', $qb->createNamedParameter($share->getId())))
				->set('permissions', $qb->createNamedParameter($share->getPermissions()))
				->set('uid_owner', $qb->createNamedParameter($share->getShareOwner()))
				->set('uid_initiator', $qb->createNamedParameter($share->getSharedBy()))
				->execute();

		// send the updated permission to the owner/initiator
		if ($this->shouldNotifyRemote($share)) {
			$this->sendPermissionUpdate($share);
		}

		return $share;
	}

	/**
	 * User based check
	 *
	 * @param IShare $share
	 * @return bool
	 */
	protected function shouldNotifyRemote($share) {
		// We notify owner/initiator, if they are not the same user and ANY of them is NOT a local user
		// they could be both local e.g. if recipient of local share shared it via federation
		$isRemoteUserInvolved =  $this->userManager->userExists($share->getShareOwner()) == false
			|| $this->userManager->userExists($share->getSharedBy()) == false;
		return $isRemoteUserInvolved && $share->getShareOwner() !== $share->getSharedBy();
	}

	/**
	 * send the updated permission to the owner/initiator, if they are not the same
	 *
	 * @param IShare $share
	 * @throws ShareNotFound
	 * @throws \OC\HintException
	 */
	protected function sendPermissionUpdate(IShare $share) {
		$remoteId = $this->getRemoteId($share);
		// if the local user is the owner we send the permission change to the initiator
		if ($this->userManager->userExists($share->getShareOwner())) {
			list(, $remote) = $this->addressHandler->splitUserRemote($share->getSharedBy());
		} else { // ... if not we send the permission change to the owner
			list(, $remote) = $this->addressHandler->splitUserRemote($share->getShareOwner());
		}
		$this->notifications->sendPermissionChange($remote, $remoteId, $share->getToken(), $share->getPermissions());
	}

	/**
	 * update successful reShare with the correct token
	 *
	 * @param int $shareId
	 * @param string $token
	 */
	protected function updateSuccessfulReShare($shareId, $token) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->update($this->shareTable)
			->where($query->expr()->eq('id', $query->createNamedParameter($shareId)))
			->set('token', $query->createNamedParameter($token))
			->execute();
	}

	/**
	 * store remote ID in federated reShare table
	 *
	 * @param $shareId
	 * @param $remoteId
	 */
	public function storeRemoteId($shareId, $remoteId) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->insert('federated_reshares')
			->values(
				[
					'share_id' =>  $query->createNamedParameter($shareId),
					'remote_id' => $query->createNamedParameter($remoteId),
				]
			);
		$query->execute();
	}

	/**
	 * get share ID on remote server for federated re-shares
	 *
	 * @param IShare $share
	 * @return string
	 * @throws ShareNotFound
	 */
	public function getRemoteId(IShare $share) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->select('remote_id')->from('federated_reshares')
			->where($query->expr()->eq('share_id', $query->createNamedParameter((int)$share->getId())));
		$data = $query->execute()->fetch();

		if (!\is_array($data) || !isset($data['remote_id'])) {
			throw new ShareNotFound();
		}

		return $data['remote_id'];
	}

	/**
	 * @inheritdoc
	 */
	public function move(IShare $share, $recipient) {
		/*
		 * This function does nothing yet as it is just for outgoing
		 * federated shares.
		 */
		return $share;
	}

	/**
	 * Get all children of this share
	 *
	 * @param IShare $parent
	 * @return IShare[]
	 */
	public function getChildren(IShare $parent) {
		$children = [];

		$qb = $this->dbConnection->getQueryBuilder();
		$qb->select('*')
			->from($this->shareTable)
			->where($qb->expr()->eq('parent', $qb->createNamedParameter($parent->getId())))
			->andWhere($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)))
			->orderBy('id');

		$cursor = $qb->execute();
		while ($data = $cursor->fetch()) {
			$children[] = $this->createShareObject($data);
		}
		$cursor->closeCursor();

		return $children;
	}

	/**
	 * Delete a share (owner unShares the file)
	 *
	 * @param IShare $share
	 */
	public function delete(IShare $share) {
		list(, $remote) = $this->addressHandler->splitUserRemote($share->getSharedWith());

		$isOwner = false;

		// if the local user is the owner we can send the unShare request directly...
		if ($this->userManager->userExists($share->getShareOwner())) {
			$this->notifications->sendRemoteUnShare($remote, $share->getId(), $share->getToken());
			$this->revokeShare($share, true);
			$isOwner = true;
		} else { // ... if not we need to correct ID for the unShare request
			$remoteId = $this->getRemoteId($share);
			$this->notifications->sendRemoteUnShare($remote, $remoteId, $share->getToken());
			$this->revokeShare($share, false);
		}

		// send revoke notification to the other user
		if ($this->shouldNotifyRemote($share)) {
			$remoteId = $this->getRemoteId($share);
			if ($isOwner) {
				list(, $remote) = $this->addressHandler->splitUserRemote($share->getSharedBy());
			} else {
				list(, $remote) = $this->addressHandler->splitUserRemote($share->getShareOwner());
			}
			$this->notifications->sendRevokeShare($remote, $remoteId, $share->getToken());
		}
		$this->removeShareFromTable($share);
	}

	/**
	 * in case of a re-share we need to send the other use (initiator or owner)
	 * a message that the file was unshared
	 *
	 * @param IShare $share
	 * @param bool $isOwner the user can either be the owner or the user who re-sahred it
	 * @throws ShareNotFound
	 * @throws \OC\HintException
	 */
	protected function revokeShare($share, $isOwner) {
		// also send a unShare request to the initiator
		if ($this->shouldNotifyRemote($share)) {
			if ($isOwner) {
				list(, $remote) = $this->addressHandler->splitUserRemote($share->getSharedBy());
			} else {
				list(, $remote) = $this->addressHandler->splitUserRemote($share->getShareOwner());
			}
			$remoteId = $this->getRemoteId($share);
			$this->notifications->sendRevokeShare($remote, $remoteId, $share->getToken());
		}
	}

	/**
	 * remove share from table
	 *
	 * @param IShare $share
	 */
	public function removeShareFromTable(IShare $share) {
		$this->removeShareFromTableById($share->getId());
	}

	/**
	 * remove share from table
	 *
	 * @param string $shareId
	 */
	private function removeShareFromTableById($shareId) {
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->delete($this->shareTable)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($shareId)));
		$qb->execute();

		$qb->delete('federated_reshares')
			->where($qb->expr()->eq('share_id', $qb->createNamedParameter($shareId)));
		$qb->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function deleteFromSelf(IShare $share, $recipient) {
		// nothing to do here. Technically deleteFromSelf in the context of federated
		// shares is a umount of a external storage. This is handled here
		// apps/files_sharing/lib/external/manager.php
		// TODO move this code over to this app
		return;
	}

	/**
	 * @inheritdoc
	 */
	public function getAllSharesBy($userId, $shareTypes, $nodeIDs, $reshares) {
		$shares = [];

		$qb = $this->dbConnection->getQueryBuilder();
		$qb->select('*')
			->from($this->shareTable);

		// In federated sharing currently we have only one share_type_remote
		$qb->andWhere($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)));

		$qb->andWhere($qb->expr()->in('file_source', $qb->createParameter('file_source_ids')));

		/**
		 * Reshares for this user are shares where they are the owner.
		 */
		if ($reshares === false) {
			//Special case for old shares created via the web UI
			$or1 = $qb->expr()->andX(
				$qb->expr()->eq('uid_owner', $qb->createNamedParameter($userId)),
				$qb->expr()->isNull('uid_initiator')
			);

			$qb->andWhere(
				$qb->expr()->orX(
					$qb->expr()->eq('uid_initiator', $qb->createNamedParameter($userId)),
					$or1
				)
			);
		} else {
			$qb->andWhere(
				$qb->expr()->orX(
					$qb->expr()->eq('uid_owner', $qb->createNamedParameter($userId)),
					$qb->expr()->eq('uid_initiator', $qb->createNamedParameter($userId))
				)
			);
		}

		$qb->orderBy('id');

		$nodeIdsChunks = \array_chunk($nodeIDs, 900);
		foreach ($nodeIdsChunks as $nodeIdsChunk) {
			$qb->setParameter('file_source_ids', $nodeIdsChunk, IQueryBuilder::PARAM_INT_ARRAY);

			$cursor = $qb->execute();
			while ($data = $cursor->fetch()) {
				$shares[] = $this->createShareObject($data);
			}
			$cursor->closeCursor();
		}

		return $shares;
	}

	/**
	 * @inheritdoc
	 */
	public function getSharesBy($userId, $shareType, $node, $reshares, $limit, $offset) {
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->select('*')
			->from($this->shareTable);

		$qb->andWhere($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)));

		/**
		 * Reshares for this user are shares where they are the owner.
		 */
		if ($reshares === false) {
			//Special case for old shares created via the web UI
			$or1 = $qb->expr()->andX(
				$qb->expr()->eq('uid_owner', $qb->createNamedParameter($userId)),
				$qb->expr()->isNull('uid_initiator')
			);

			$qb->andWhere(
				$qb->expr()->orX(
					$qb->expr()->eq('uid_initiator', $qb->createNamedParameter($userId)),
					$or1
				)
			);
		} else {
			$qb->andWhere(
				$qb->expr()->orX(
					$qb->expr()->eq('uid_owner', $qb->createNamedParameter($userId)),
					$qb->expr()->eq('uid_initiator', $qb->createNamedParameter($userId))
				)
			);
		}

		if ($node !== null) {
			$qb->andWhere($qb->expr()->eq('file_source', $qb->createNamedParameter($node->getId())));
		}

		if ($limit !== -1) {
			$qb->setMaxResults($limit);
		}

		$qb->setFirstResult($offset);
		$qb->orderBy('id');

		$cursor = $qb->execute();
		$shares = [];
		while ($data = $cursor->fetch()) {
			$shares[] = $this->createShareObject($data);
		}
		$cursor->closeCursor();

		return $shares;
	}

	/**
	 * @inheritdoc
	 */
	public function getShareById($id, $recipientId = null) {
		$qb = $this->dbConnection->getQueryBuilder();

		$qb->select('*')
			->from($this->shareTable)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)));

		$cursor = $qb->execute();
		$data = $cursor->fetch();
		$cursor->closeCursor();

		if ($data === false) {
			throw new ShareNotFound();
		}

		try {
			$share = $this->createShareObject($data);
		} catch (InvalidShare $e) {
			throw new ShareNotFound();
		}

		return $share;
	}

	/**
	 * Get shares for a given path
	 *
	 * @param \OCP\Files\Node $path
	 * @return IShare[]
	 */
	public function getSharesByPath(Node $path) {
		$qb = $this->dbConnection->getQueryBuilder();

		$cursor = $qb->select('*')
			->from($this->shareTable)
			->andWhere($qb->expr()->eq('file_source', $qb->createNamedParameter($path->getId())))
			->andWhere($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)))
			->execute();

		$shares = [];
		while ($data = $cursor->fetch()) {
			$shares[] = $this->createShareObject($data);
		}
		$cursor->closeCursor();

		return $shares;
	}

	/**
	 * @inheritdoc
	 */
	public function getAllSharedWith($userId, $node) {
		return $this->getSharedWith($userId, self::SHARE_TYPE_REMOTE, $node, -1, 0);
	}

	/**
	 * @inheritdoc
	 */
	public function getSharedWith($userId, $shareType, $node, $limit, $offset) {
		/** @var IShare[] $shares */
		$shares = [];

		//Get shares directly with this user
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->select('*')
			->from($this->shareTable);

		// Order by id
		$qb->orderBy('id');

		// Set limit and offset
		if ($limit !== -1) {
			$qb->setMaxResults($limit);
		}
		$qb->setFirstResult($offset);

		$qb->where($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)));
		$qb->andWhere($qb->expr()->eq('share_with', $qb->createNamedParameter($userId)));

		// Filter by node if provided
		if ($node !== null) {
			$qb->andWhere($qb->expr()->eq('file_source', $qb->createNamedParameter($node->getId())));
		}

		$cursor = $qb->execute();

		while ($data = $cursor->fetch()) {
			$shares[] = $this->createShareObject($data);
		}
		$cursor->closeCursor();

		return $shares;
	}

	/**
	 * Get a share by token
	 *
	 * @param string $token
	 * @return IShare
	 * @throws ShareNotFound
	 */
	public function getShareByToken($token) {
		$qb = $this->dbConnection->getQueryBuilder();

		$cursor = $qb->select('*')
			->from($this->shareTable)
			->where($qb->expr()->eq('share_type', $qb->createNamedParameter(self::SHARE_TYPE_REMOTE)))
			->andWhere($qb->expr()->eq('token', $qb->createNamedParameter($token)))
			->execute();

		$data = $cursor->fetch();

		if ($data === false) {
			throw new ShareNotFound();
		}

		try {
			$share = $this->createShareObject($data);
		} catch (InvalidShare $e) {
			throw new ShareNotFound();
		}

		return $share;
	}

	/**
	 * get database row of a give share
	 *
	 * @param $id
	 * @return array
	 * @throws ShareNotFound
	 */
	private function getRawShare($id) {

		// Now fetch the inserted share and create a complete share object
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->select('*')
			->from($this->shareTable)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));

		$cursor = $qb->execute();
		$data = $cursor->fetch();
		$cursor->closeCursor();

		if ($data === false) {
			throw new ShareNotFound;
		}

		return $data;
	}

	/**
	 * Create a share object from an database row
	 *
	 * @param array $data
	 * @return IShare
	 * @throws InvalidShare
	 * @throws ShareNotFound
	 */
	private function createShareObject($data) {
		$share = new Share($this->rootFolder, $this->userManager);
		$share->setId((int)$data['id'])
			->setShareType((int)$data['share_type'])
			->setPermissions((int)$data['permissions'])
			->setTarget($data['file_target'])
			->setMailSend((bool)$data['mail_send'])
			->setToken($data['token']);

		$shareTime = new \DateTime();
		$shareTime->setTimestamp((int)$data['stime']);
		$share->setShareTime($shareTime);
		$share->setSharedWith($data['share_with']);

		if ($data['uid_initiator'] !== null) {
			$share->setShareOwner($data['uid_owner']);
			$share->setSharedBy($data['uid_initiator']);
		} else {
			//OLD SHARE
			$share->setSharedBy($data['uid_owner']);
			$path = $this->getNode($share->getSharedBy(), (int)$data['file_source']);

			$owner = $path->getOwner();
			$share->setShareOwner($owner->getUID());
		}

		$share->setNodeId((int)$data['file_source']);
		$share->setNodeType($data['item_type']);

		$share->setProviderId($this->identifier());

		return $share;
	}

	/**
	 * Get the node with file $id for $user
	 *
	 * @param string $userId
	 * @param int $id
	 * @return \OCP\Files\File|\OCP\Files\Folder
	 * @throws InvalidShare
	 */
	private function getNode($userId, $id) {
		try {
			$userFolder = $this->rootFolder->getUserFolder($userId);
		} catch (NotFoundException $e) {
			throw new InvalidShare();
		}

		$nodes = $userFolder->getById($id, true);

		if (empty($nodes)) {
			throw new InvalidShare();
		}

		return $nodes[0];
	}

	/**
	 * A user is deleted from the system
	 * So clean up the relevant shares.
	 *
	 * @param string $uid
	 * @param int $shareType
	 */
	public function userDeleted($uid, $shareType) {
		//TODO: probabaly a good idea to send unshare info to remote servers

		$qb = $this->dbConnection->getQueryBuilder();

		$qb->delete($this->shareTable)
			->where($qb->expr()->eq('share_type', $qb->createNamedParameter(\OCP\Share::SHARE_TYPE_REMOTE)))
			->andWhere($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)))
			->execute();
	}

	/**
	 * This provider does not handle groups
	 *
	 * @param string $gid
	 */
	public function groupDeleted($gid) {
		// We don't handle groups here
		return;
	}

	/**
	 * This provider does not handle groups
	 *
	 * @param string $uid
	 * @param string $gid
	 */
	public function userDeletedFromGroup($uid, $gid) {
		// We don't handle groups here
		return;
	}

	/**
	 * check if scan of federated shares from other ownCloud instances should be performed
	 *
	 * @return bool
	 */
	public function isCronjobScanExternalEnabled() {
		$result = $this->config->getAppValue('files_sharing', 'cronjob_scan_external_enabled', 'no');
		return ($result === 'yes') ? true : false;
	}

	/**
	 * check if users from other ownCloud instances are allowed to mount public links share by this instance
	 *
	 * @return bool
	 */
	public function isOutgoingServer2serverShareEnabled() {
		$result = $this->config->getAppValue('files_sharing', 'outgoing_server2server_share_enabled', 'yes');
		return ($result === 'yes') ? true : false;
	}

	/**
	 * check if users are allowed to mount public links from other ownClouds
	 *
	 * @return bool
	 */
	public function isIncomingServer2serverShareEnabled() {
		$result = $this->config->getAppValue('files_sharing', 'incoming_server2server_share_enabled', 'yes');
		return ($result === 'yes') ? true : false;
	}

	/**
	 * @inheritdoc
	 */
	public function updateForRecipient(IShare $share, $recipient) {
		/*
		 * This function does nothing yet as it is just for outgoing
		 * federated shares.
		 */
		return $share;
	}

	/**
	 * @param string $remoteId
	 * @param string $shareToken
	 * @return mixed
	 */
	public function unshare($remoteId, $shareToken) {
		$query = $this->dbConnection->getQueryBuilder();
		$query->select('*')->from($this->externalShareTable)
			->where(
				$query->expr()->eq(
					'remote_id', $query->createNamedParameter($remoteId)
				)
			)
			->andWhere(
				$query->expr()->eq(
					'share_token',
					$query->createNamedParameter($shareToken)
				)
			);
		$shareRow = $query->execute()->fetch();
		if ($shareRow !== false) {
			$query = $this->dbConnection->getQueryBuilder();
			$query->delete($this->externalShareTable)
				->where(
					$query->expr()->eq(
						'remote_id',
						$query->createNamedParameter($shareRow['remote_id'])
					)
				)
				->andWhere(
					$query->expr()->eq(
						'share_token',
						$query->createNamedParameter($shareRow['share_token'])
					)
				);
			$query->execute();
		}
		return $shareRow;
	}

	/**
	 * @param $remote
	 * @param $token
	 * @param $name
	 * @param $owner
	 * @param $shareWith
	 * @param $remoteId
	 *
	 * @return int
	 */
	public function addShare($remote, $token, $name, $owner, $shareWith, $remoteId) {
		\OC_Util::setupFS($shareWith);
		$externalManager = new \OCA\Files_Sharing\External\Manager(
			$this->dbConnection,
			\OC\Files\Filesystem::getMountManager(),
			\OC\Files\Filesystem::getLoader(),
			\OC::$server->getNotificationManager(),
			\OC::$server->getEventDispatcher(),
			$shareWith
		);
		$externalManager->addShare(
			$remote,
			$token,
			'',
			$name,
			$owner,
			$this->getAccepted($remote, $shareWith),
			$shareWith,
			$remoteId
		);
		return $this->dbConnection->lastInsertId("*PREFIX*{$this->externalShareTable}");
	}

	/**
	 * @param string $remote
	 * @param string $shareWith
	 *
	 * @return bool
	 */
	public function getAccepted($remote, $shareWith) {
		$event = $this->eventDispatcher->dispatch(
			'remoteshare.received',
			new GenericEvent('', ['remote' => $remote])
		);
		'@phan-var GenericEvent $event';
		if ($event->getArgument('autoAddServers')) {
			return false;
		}
		$globalAutoAcceptValue  = $this->config->getAppValue(
			'federatedfilesharing',
			'auto_accept_trusted',
			'no'
		);
		if ($globalAutoAcceptValue !== 'yes') {
			return false;
		}
		$autoAccept = $this->config->getUserValue(
			$shareWith,
			'federatedfilesharing',
			'auto_accept_share_trusted',
			$globalAutoAcceptValue
		);
		if ($autoAccept !== 'yes') {
			return false;
		}

		return $event->getArgument('isRemoteTrusted') === true;
	}

	/**
	 * @inheritdoc
	 */
	public function getProviderCapabilities() {
		return [
			\OCP\Share::CONVERT_SHARE_TYPE_TO_STRING[\OCP\Share::SHARE_TYPE_REMOTE] => [],
		];
	}
}
