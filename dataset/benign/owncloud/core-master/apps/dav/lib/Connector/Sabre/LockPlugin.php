<?php
/**
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

namespace OCA\DAV\Connector\Sabre;

use OC\Lock\Persistent\LockMapper;
use OCA\DAV\Connector\Sabre\Exception\FileLocked;
use OCP\Lock\ILockingProvider;
use OCP\Lock\LockedException;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\DAV\Locks\LockInfo;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;

class LockPlugin extends ServerPlugin {
	/**
	 * Reference to main server object
	 *
	 * @var \Sabre\DAV\Server
	 */
	private $server;

	/**
	 * {@inheritdoc}
	 */
	public function initialize(\Sabre\DAV\Server $server) {
		$this->server = $server;
		$this->server->on('beforeMethod:*', [$this, 'getLock'], 50);
		$this->server->on('afterMethod:*', [$this, 'releaseLock'], 50);
		$this->server->on('beforeUnlock', [$this, 'beforeUnlock'], 20);
	}

	public function getLock(RequestInterface $request) {
		// we can't listen on 'beforeMethod:PUT' due to order of operations with setting up the tree
		// so instead we limit ourselves to the PUT method manually
		if ($request->getMethod() !== 'PUT' || \OC_FileChunking::isWebdavChunk()) {
			return;
		}
		try {
			$node = $this->server->tree->getNodeForPath($request->getPath());
		} catch (NotFound $e) {
			return;
		}
		if ($node instanceof Node) {
			try {
				$node->acquireLock(ILockingProvider::LOCK_SHARED);
			} catch (LockedException $e) {
				throw new FileLocked($e->getMessage(), $e->getCode(), $e);
			}
		}
	}

	public function releaseLock(RequestInterface $request) {
		if ($request->getMethod() !== 'PUT' || \OC_FileChunking::isWebdavChunk()) {
			return;
		}
		try {
			$node = $this->server->tree->getNodeForPath($request->getPath());
		} catch (NotFound $e) {
			return;
		}
		if ($node instanceof Node) {
			$node->releaseLock(ILockingProvider::LOCK_SHARED);
		}
	}

	/**
	 * @param $uri
	 * @param LockInfo $lock
	 * @throws Forbidden
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws \OCP\AppFramework\QueryException
	 */
	public function beforeUnlock($uri, LockInfo $lock) {
		// no injection of LockMapper and UserSession below because this method
		// is only triggered on UNLOCK which is a no that common operation.
		/** @var LockMapper $mapper */
		$mapper = \OC::$server->query(LockMapper::class);

		$lock = $mapper->getLockByToken($lock->token);
		if ($lock === null || $lock->getOwnerAccountId() === null) {
			return;
		}
		$currentUser = \OC::$server->getUserSession()->getUser();
		if ($currentUser === null || $lock->getOwnerAccountId() !== $currentUser->getAccountId()) {
			throw new Forbidden();
		}
	}
}
