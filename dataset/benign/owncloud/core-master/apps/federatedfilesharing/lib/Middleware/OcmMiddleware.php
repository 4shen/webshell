<?php
/**
 * @author Viktar Dubiniuk <dubiniuk@owncloud.com>
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

namespace OCA\FederatedFileSharing\Middleware;

use OCA\FederatedFileSharing\Address;
use OCA\FederatedFileSharing\AddressHandler;
use OCA\FederatedFileSharing\FederatedShareProvider;
use OCA\FederatedFileSharing\Ocm\Exception\BadRequestException;
use OCA\FederatedFileSharing\Ocm\Exception\ForbiddenException;
use OCA\FederatedFileSharing\Ocm\Exception\NotImplementedException;
use OCP\Constants;
use OCP\App\IAppManager;
use OCP\ILogger;
use OCP\IUserManager;
use OCP\Share;
use OCP\Share\IShare;

/**
 * Class OcmMiddleware
 *
 * @package OCA\FederatedFileSharing\Controller\Middleware
 */
class OcmMiddleware {
	/**
	 * @var FederatedShareProvider
	 */
	protected $federatedShareProvider;

	/**
	 * @var IAppManager
	 */
	protected $appManager;

	/**
	 * @var IUserManager
	 */
	protected $userManager;

	/**
	 * @var AddressHandler
	 */
	protected $addressHandler;

	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 * constructor.
	 *
	 * @param FederatedShareProvider $federatedShareProvider
	 * @param IAppManager $appManager
	 * @param IUserManager $userManager
	 * @param AddressHandler $addressHandler
	 * @param ILogger $logger
	 */
	public function __construct(
								FederatedShareProvider $federatedShareProvider,
								IAppManager $appManager,
								IUserManager $userManager,
								AddressHandler $addressHandler,
								ILogger $logger
	) {
		$this->federatedShareProvider = $federatedShareProvider;
		$this->appManager = $appManager;
		$this->userManager = $userManager;
		$this->addressHandler = $addressHandler;
		$this->logger = $logger;
	}

	/**
	 * Check if value an array has any null item
	 *
	 * @param string[] $params
	 *
	 * @return void
	 *
	 * @throws BadRequestException
	 */
	public function assertNotNull($params) {
		if (\is_array($params)) {
			$nullKeys = \array_keys(
				\array_filter(
					$params,
					function ($b) {
						return $b === null;
					}
				)
			);
			if (\count($nullKeys) > 0) {
				$nullKeysAsString = \implode(',', $nullKeys);
				throw new BadRequestException(
					"Required parameters are missing: $nullKeysAsString"
				);
			}
		}
	}

	/**
	 * Get share by id, validate its type and token
	 *
	 * @param int $id
	 * @param string $sharedSecret
	 *
	 * @return IShare
	 *
	 * @throws BadRequestException
	 * @throws ForbiddenException
	 */
	public function getValidShare($id, $sharedSecret) {
		try {
			$share = $this->federatedShareProvider->getShareById($id);
		} catch (Share\Exceptions\ShareNotFound $e) {
			throw new BadRequestException("Share with id {$id} does not exist");
		}
		if ($share->getShareType() !== FederatedShareProvider::SHARE_TYPE_REMOTE) {
			throw new BadRequestException("Share with id {$id} does not exist");
		}

		if ($share->getToken() !== $sharedSecret) {
			throw new ForbiddenException("The secret does not match");
		}
		return $share;
	}

	/**
	 * @param IShare $share
	 *
	 * @return void
	 *
	 * @throws BadRequestException
	 */
	public function assertSharingPermissionSet(IShare $share) {
		$reSharingAllowed = $share->getPermissions() & Constants::PERMISSION_SHARE;
		if (!$reSharingAllowed) {
			throw new BadRequestException("Owner restricted sharing for this resource");
		}
	}

	/**
	 * @param Address $user1
	 * @param Address $user2
	 *
	 * @return void
	 *
	 * @throws ForbiddenException
	 */
	public function assertNotSameUser(Address $user1, Address $user2) {
		if ($user1->equalTo($user2)) {
			throw new ForbiddenException('Sharing back to the owner is not allowed');
		}
	}

	/**
	 * Make sure that incoming shares are enabled
	 *
	 * @return void
	 *
	 * @throws NotImplementedException
	 */
	public function assertIncomingSharingEnabled() {
		if (!$this->appManager->isEnabledForUser('files_sharing')
			|| !$this->federatedShareProvider->isIncomingServer2serverShareEnabled()
		) {
			throw new NotImplementedException();
		}
	}

	/**
	 * Make sure that outgoing shares are enabled
	 *
	 * @return void
	 *
	 * @throws NotImplementedException
	 */
	public function assertOutgoingSharingEnabled() {
		if (!$this->appManager->isEnabledForUser('files_sharing')
			|| !$this->federatedShareProvider->isOutgoingServer2serverShareEnabled()
		) {
			throw new NotImplementedException();
		}
	}

	/**
	 * Drop unused bits for permissions
	 *
	 * @param int $permissions
	 *
	 * @return int
	 */
	public function normalizePermissions($permissions) {
		$mask = Constants::PERMISSION_READ
			| Constants::PERMISSION_CREATE
			| Constants::PERMISSION_UPDATE
			| Constants::PERMISSION_SHARE;
		return $mask & $permissions;
	}
}
