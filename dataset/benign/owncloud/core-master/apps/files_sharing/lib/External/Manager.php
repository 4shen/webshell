<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Stefan Weil <sw@weilnetz.de>
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

namespace OCA\Files_Sharing\External;

use OC\Files\Filesystem;
use OC\User\NoUserException;
use OCA\Files_Sharing\Helper;
use OCP\Files;
use OCP\Notification\IManager;
use OCP\Share\Events\AcceptShare;
use OCP\Share\Events\DeclineShare;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class Manager {
	const STORAGE = '\OCA\Files_Sharing\External\Storage';

	/**
	 * @var string
	 */
	private $uid;

	/**
	 * @var \OCP\IDBConnection
	 */
	private $connection;

	/**
	 * @var \OC\Files\Mount\Manager
	 */
	private $mountManager;

	/**
	 * @var \OCP\Files\Storage\IStorageFactory
	 */
	private $storageLoader;

	/**
	 * @var IManager
	 */
	private $notificationManager;

	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;

	/**
	 * @param \OCP\IDBConnection $connection
	 * @param \OC\Files\Mount\Manager $mountManager
	 * @param \OCP\Files\Storage\IStorageFactory $storageLoader
	 * @param IManager $notificationManager
	 * @param EventDispatcherInterface $eventDispatcher
	 * @param string $uid
	 */
	public function __construct(\OCP\IDBConnection $connection,
								\OC\Files\Mount\Manager $mountManager,
								\OCP\Files\Storage\IStorageFactory $storageLoader,
								IManager $notificationManager,
								EventDispatcherInterface $eventDispatcher,
								$uid) {
		$this->connection = $connection;
		$this->mountManager = $mountManager;
		$this->storageLoader = $storageLoader;
		$this->uid = $uid;
		$this->notificationManager = $notificationManager;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * add new server-to-server share
	 *
	 * @param string $remote
	 * @param string $token
	 * @param string $password
	 * @param string $name
	 * @param string $owner
	 * @param boolean $accepted
	 * @param string $user
	 * @param string $remoteId
	 * @return Mount|null
	 */
	public function addShare($remote, $token, $password, $name, $owner, $accepted=false, $user = null, $remoteId = -1) {
		$user = $user ? $user : $this->uid;
		$accepted = $accepted ? 1 : 0;
		$name = Filesystem::normalizePath('/' . $name);

		if (!$accepted) {
			// To avoid conflicts with the mount point generation later,
			// we only use a temporary mount point name here. The real
			// mount point name will be generated when accepting the share,
			// using the original share item name.
			$tmpMountPointName = '{{TemporaryMountPointName#' . $name . '}}';
			$mountPoint = $tmpMountPointName;
			$hash = \md5($tmpMountPointName);
			$data = [
				'remote'		=> $remote,
				'share_token'	=> $token,
				'password'		=> $password,
				'name'			=> $name,
				'owner'			=> $owner,
				'user'			=> $user,
				'mountpoint'	=> $mountPoint,
				'mountpoint_hash'	=> $hash,
				'accepted'		=> $accepted,
				'remote_id'		=> $remoteId,
			];

			$i = 1;
			while (!$this->connection->insertIfNotExist('*PREFIX*share_external', $data, ['user', 'mountpoint_hash'])) {
				// The external share already exists for the user
				$data['mountpoint'] = $tmpMountPointName . '-' . $i;
				$data['mountpoint_hash'] = \md5($data['mountpoint']);
				$i++;
			}
			return null;
		}

		$shareFolder = Helper::getShareFolder();
		$mountPoint = Files::buildNotExistingFileName($shareFolder, $name);
		$mountPoint = Filesystem::normalizePath($mountPoint);
		$hash = \md5($mountPoint);

		$query = $this->connection->prepare('
				INSERT INTO `*PREFIX*share_external`
					(`remote`, `share_token`, `password`, `name`, `owner`, `user`, `mountpoint`, `mountpoint_hash`, `accepted`, `remote_id`)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			');
		$query->execute([$remote, $token, $password, $name, $owner, $user, $mountPoint, $hash, $accepted, $remoteId]);

		$options = [
			'remote'	=> $remote,
			'token'		=> $token,
			'password'	=> $password,
			'mountpoint'	=> $mountPoint,
			'owner'		=> $owner
		];
		return $this->mountShare($options);
	}

	/**
	 * get share
	 *
	 * @param int $id share id
	 * @return mixed share of false
	 */
	public function getShare($id) {
		$getShare = $this->connection->prepare('
			SELECT `id`, `remote`, `remote_id`, `share_token`, `name`, `owner`, `user`, `mountpoint`, `accepted`
			FROM  `*PREFIX*share_external`
			WHERE `id` = ? AND `user` = ?');
		$result = $getShare->execute([$id, $this->uid]);

		return $result ? $getShare->fetch() : false;
	}

	/**
	 * accept server-to-server share
	 *
	 * @param int $id
	 * @return bool True if the share could be accepted, false otherwise
	 */
	public function acceptShare($id) {
		$share = $this->getShare($id);

		if ($share) {
			$shareFolder = Helper::getShareFolder();
			$mountPoint = Files::buildNotExistingFileName($shareFolder, $share['name']);
			$mountPoint = Filesystem::normalizePath($mountPoint);
			$hash = \md5($mountPoint);

			$acceptShare = $this->connection->prepare('
				UPDATE `*PREFIX*share_external`
				SET `accepted` = ?,
					`mountpoint` = ?,
					`mountpoint_hash` = ?
				WHERE `id` = ? AND `user` = ?');
			$acceptShare->execute([1, $mountPoint, $hash, $id, $this->uid]);

			$this->eventDispatcher->dispatch(
				AcceptShare::class, new AcceptShare($share)
			);

			$event = new GenericEvent(null, ['sharedItem' => $share['name'], 'shareAcceptedFrom' => $share['owner'],
				'remoteUrl' => $share['remote']]);
			$this->eventDispatcher->dispatch('remoteshare.accepted', $event);
			\OC_Hook::emit('OCP\Share', 'federated_share_added', ['server' => $share['remote']]);

			$this->processNotification($id);
			return true;
		}

		return false;
	}

	/**
	 * decline server-to-server share
	 *
	 * @param int $id
	 * @return bool True if the share could be declined, false otherwise
	 */
	public function declineShare($id) {
		$share = $this->getShare($id);

		if ($share) {
			$removeShare = $this->connection->prepare('
				DELETE FROM `*PREFIX*share_external` WHERE `id` = ? AND `user` = ?');
			$removeShare->execute([$id, $this->uid]);

			$this->eventDispatcher->dispatch(
				DeclineShare::class,
				new DeclineShare($share)
			);

			$event = new GenericEvent(null, ['sharedItem' => $share['name'], 'shareAcceptedFrom' => $share['owner'],
				'remoteUrl' => $share['remote']]);
			$this->eventDispatcher->dispatch('remoteshare.declined', $event);

			$this->processNotification($id);
			return true;
		}

		return false;
	}

	/**
	 * @param int $remoteShare
	 */
	public function processNotification($remoteShare) {
		$filter = $this->notificationManager->createNotification();
		$filter->setApp('files_sharing')
			->setUser($this->uid)
			->setObject('remote_share', (int) $remoteShare);
		$this->notificationManager->markProcessed($filter);
	}

	/**
	 * remove '/user/files' from the path and trailing slashes
	 *
	 * @param string $path
	 * @return string
	 */
	protected function stripPath($path) {
		$prefix = "/{$this->uid}/files";
		return \rtrim(\substr($path, \strlen($prefix)), '/');
	}

	public function getMount($data) {
		$data['manager'] = $this;
		$mountPoint = '/' . $this->uid . '/files' . $data['mountpoint'];
		$data['mountpoint'] = $mountPoint;
		$data['certificateManager'] = \OC::$server->getCertificateManager($this->uid);
		return new Mount(self::STORAGE, $mountPoint, $data, $this, $this->storageLoader);
	}

	/**
	 * @param array $data
	 * @return Mount
	 */
	protected function mountShare($data) {
		$mount = $this->getMount($data);
		$this->mountManager->addMount($mount);
		return $mount;
	}

	/**
	 * @return \OC\Files\Mount\Manager
	 */
	public function getMountManager() {
		return $this->mountManager;
	}

	/**
	 * @param string $source
	 * @param string $target
	 * @return bool
	 */
	public function setMountPoint($source, $target) {
		$source = $this->stripPath($source);
		$target = $this->stripPath($target);
		$sourceHash = \md5($source);
		$targetHash = \md5($target);

		$query = $this->connection->prepare('
			UPDATE `*PREFIX*share_external`
			SET `mountpoint` = ?, `mountpoint_hash` = ?
			WHERE `mountpoint_hash` = ?
			AND `user` = ?
		');
		$result = (bool)$query->execute([$target, $targetHash, $sourceHash, $this->uid]);

		return $result;
	}

	/**
	 * Explicitly set uid when the shares are managed in CLI
	 *
	 * @param string|null $uid
	 */
	public function setUid($uid) {
		// FIXME: External manager should not depend on uid
		$this->uid = $uid;
	}

	/**
	 * @param $mountPoint
	 * @return bool
	 *
	 * @throws NoUserException
	 */
	public function removeShare($mountPoint) {
		if ($this->uid === null) {
			throw new NoUserException();
		}

		$mountPointObj = $this->mountManager->find($mountPoint);
		$id = $mountPointObj->getStorage()->getCache()->getId('');

		$mountPoint = $this->stripPath($mountPoint);
		$hash = \md5($mountPoint);

		$getShare = $this->connection->prepare('
			SELECT `remote`, `share_token`, `remote_id`
			FROM  `*PREFIX*share_external`
			WHERE `mountpoint_hash` = ? AND `user` = ?');
		$result = $getShare->execute([$hash, $this->uid]);

		if ($result) {
			$share = $getShare->fetch();
			if ($share !== false) {
				$this->eventDispatcher->dispatch(
					DeclineShare::class,
					new DeclineShare($share)
				);
			}
		}
		$getShare->closeCursor();

		$query = $this->connection->prepare('
			DELETE FROM `*PREFIX*share_external`
			WHERE `mountpoint_hash` = ?
			AND `user` = ?
		');
		$result = (bool)$query->execute([$hash, $this->uid]);

		if ($result) {
			$this->removeReShares($id);
			$event = new GenericEvent(null, ['user' => $this->uid, 'targetmount' => $mountPoint]);
			$this->eventDispatcher->dispatch('\OCA\Files_Sharing::unshareEvent', $event);
		}

		return $result;
	}

	/**
	 * remove re-shares from share table and mapping in the federated_reshares table
	 *
	 * @param $mountPointId
	 */
	protected function removeReShares($mountPointId) {
		$selectQuery = $this->connection->getQueryBuilder();
		$query = $this->connection->getQueryBuilder();
		$selectQuery->select('id')->from('share')
			->where($selectQuery->expr()->eq('file_source', $query->createNamedParameter($mountPointId)));
		$select = $selectQuery->getSQL();

		$query->delete('federated_reshares')
			->where($query->expr()->in('share_id', $query->createFunction('(' . $select . ')')));
		$query->execute();

		$deleteReShares = $this->connection->getQueryBuilder();
		$deleteReShares->delete('share')
			->where($deleteReShares->expr()->eq('file_source', $deleteReShares->createNamedParameter($mountPointId)));
		$deleteReShares->execute();
	}

	/**
	 * remove all shares for user $uid if the user was deleted
	 *
	 * @param string $uid
	 * @return bool
	 */
	public function removeUserShares($uid) {
		$getShare = $this->connection->prepare('
			SELECT `remote`, `share_token`, `remote_id`
			FROM  `*PREFIX*share_external`
			WHERE `user` = ?');
		$result = $getShare->execute([$uid]);

		if ($result) {
			$shares = $getShare->fetchAll();
			foreach ($shares as $share) {
				$this->eventDispatcher->dispatch(
					DeclineShare::class,
					new DeclineShare($share)
				);
			}
		}

		$query = $this->connection->prepare('
			DELETE FROM `*PREFIX*share_external`
			WHERE `user` = ?
		');
		return (bool)$query->execute([$uid]);
	}

	/**
	 * return a list of shares which are not yet accepted by the user
	 *
	 * @return array list of open server-to-server shares
	 */
	public function getOpenShares() {
		return $this->getShares(false);
	}

	/**
	 * return a list of shares which are accepted by the user
	 *
	 * @return array list of accepted server-to-server shares
	 */
	public function getAcceptedShares() {
		return $this->getShares(true);
	}

	/**
	 * return a list of shares for the user
	 *
	 * @param bool|null $accepted True for accepted only,
	 *                            false for not accepted,
	 *                            null for all shares of the user
	 * @return array list of open server-to-server shares
	 */
	private function getShares($accepted) {
		$query = 'SELECT `id`, `remote`, `remote_id`, `share_token`, `name`, `owner`, `user`, `mountpoint`, `accepted`
		          FROM `*PREFIX*share_external` 
				  WHERE `user` = ?';
		$parameters = [$this->uid];
		if ($accepted !== null) {
			$query .= ' AND `accepted` = ?';
			$parameters[] = (int) $accepted;
		}
		$query .= ' ORDER BY `id` ASC';

		$shares = $this->connection->prepare($query);
		$result = $shares->execute($parameters);

		return $result ? $shares->fetchAll() : [];
	}
}
