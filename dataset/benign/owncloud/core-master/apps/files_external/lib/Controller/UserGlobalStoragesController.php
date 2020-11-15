<?php
/**
 * @author Juan Pablo Villafáñez <jvillafanez@solidgear.es>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
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

namespace OCA\Files_External\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\External\Auth\AuthMechanism;
use OCP\Files\External\Auth\IUserProvided;
use OCP\Files\External\Backend\Backend;
use OCP\Files\External\InsufficientDataForMeaningfulAnswerException;
use OCP\Files\External\IStorageConfig;
use OCP\Files\External\NotFoundException;
use OCP\Files\External\Service\IUserGlobalStoragesService;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * User global storages controller
 */
class UserGlobalStoragesController extends StoragesController {
	/**
	 * @var IUserSession
	 */
	private $userSession;

	/**
	 * Creates a new user global storages controller.
	 *
	 * @param string $AppName application name
	 * @param IRequest $request request object
	 * @param IL10N $l10n l10n service
	 * @param IUserGlobalStoragesService $userGlobalStoragesService storage service
	 * @param IUserSession $userSession
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		IL10N $l10n,
		IUserGlobalStoragesService $userGlobalStoragesService,
		IUserSession $userSession,
		ILogger $logger
	) {
		parent::__construct(
			$AppName,
			$request,
			$l10n,
			$userGlobalStoragesService,
			$logger
		);
		$this->userSession = $userSession;
	}

	/**
	 * Get all storage entries
	 *
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 */
	public function index() {
		$service = $this->service;
		'@phan-var \OCP\Files\External\Service\IUserGlobalStoragesService $service';
		$storages = $service->getUniqueStorages();

		// remove configuration data, this must be kept private
		foreach ($storages as $storage) {
			$this->sanitizeStorage($storage);
		}

		return new DataResponse(
			$storages,
			Http::STATUS_OK
		);
	}

	protected function manipulateStorageConfig(IStorageConfig $storage) {
		/** @var AuthMechanism */
		$authMechanism = $storage->getAuthMechanism();
		$authMechanism->manipulateStorageConfig($storage, $this->userSession->getUser());
		/** @var Backend */
		$backend = $storage->getBackend();
		$backend->manipulateStorageConfig($storage, $this->userSession->getUser());
	}

	/**
	 * Get an external storage entry.
	 *
	 * @param int $id storage id
	 * @param bool $testOnly whether to storage should only test the connection or do more things
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 */
	public function show($id, $testOnly = true) {
		try {
			$storage = $this->service->getStorage($id);

			$this->updateStorageStatus($storage, $testOnly);
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Storage with id "%i" not found', [$id])
				],
				Http::STATUS_NOT_FOUND
			);
		}

		$this->sanitizeStorage($storage);

		return new DataResponse(
			$storage,
			Http::STATUS_OK
		);
	}

	/**
	 * Update an external storage entry.
	 * Only allows setting user provided backend fields
	 *
	 * @param int $id storage id
	 * @param array $backendOptions backend-specific options
	 * @param bool $testOnly whether to storage should only test the connection or do more things
	 *
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 */
	public function update(
		$id,
		$backendOptions,
		$testOnly = true
	) {
		try {
			$storage = $this->service->getStorage($id);
			$authMechanism = $storage->getAuthMechanism();
			if ($authMechanism instanceof IUserProvided) {
				$authMechanism->saveBackendOptions($this->userSession->getUser(), $id, $backendOptions);
				'@phan-var \OCP\Files\External\Auth\AuthMechanism $authMechanism';
				$authMechanism->manipulateStorageConfig($storage, $this->userSession->getUser());
			} else {
				return new DataResponse(
					[
						'message' => (string)$this->l10n->t('Storage with id "%i" is not user editable', [$id])
					],
					Http::STATUS_FORBIDDEN
				);
			}
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Storage with id "%i" not found', [$id])
				],
				Http::STATUS_NOT_FOUND
			);
		}

		$this->updateStorageStatus($storage, $testOnly);
		$this->sanitizeStorage($storage);

		return new DataResponse(
			$storage,
			Http::STATUS_OK
		);
	}

	/**
	 * Remove sensitive data from a IStorageConfig before returning it to the user
	 *
	 * @param IStorageConfig $storage
	 */
	protected function sanitizeStorage(IStorageConfig $storage) {
		$storage->setBackendOptions([]);
		$storage->setMountOptions([]);

		if ($storage->getAuthMechanism() instanceof IUserProvided) {
			try {
				$storage->getAuthMechanism()->manipulateStorageConfig($storage, $this->userSession->getUser());
			} catch (InsufficientDataForMeaningfulAnswerException $e) {
				// not configured yet
			}
		}
	}
}
