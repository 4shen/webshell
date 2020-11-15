<?php
/**
 * @author Jesús Macias <jmacias@solidgear.es>
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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\External\Auth\AuthMechanism;
use OCP\Files\External\Backend\Backend;
use OCP\Files\External\InsufficientDataForMeaningfulAnswerException;
use OCP\Files\External\IStorageConfig;
use OCP\Files\External\NotFoundException;
use OCP\Files\External\Service\IStoragesService;
use OCP\Files\StorageNotAvailableException;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;

/**
 * Base class for storages controllers
 */
abstract class StoragesController extends Controller {

	/**
	 * L10N service
	 *
	 * @var IL10N
	 */
	protected $l10n;

	/**
	 * Storages service
	 *
	 * @var IStoragesService
	 */
	protected $service;

	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 * Creates a new storages controller.
	 *
	 * @param string $AppName application name
	 * @param IRequest $request request object
	 * @param IL10N $l10n l10n service
	 * @param IStoragesService $storagesService storage service
	 * @param ILogger $logger
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		IL10N $l10n,
		IStoragesService $storagesService,
		ILogger $logger
	) {
		parent::__construct($AppName, $request);
		$this->l10n = $l10n;
		$this->service = $storagesService;
		$this->logger = $logger;
	}

	/**
	 * Create a storage from its parameters
	 *
	 * @param string $mountPoint storage mount point
	 * @param string $backend backend identifier
	 * @param string $authMechanism authentication mechanism identifier
	 * @param array $backendOptions backend-specific options
	 * @param array|null $mountOptions mount-specific options
	 * @param array|null $applicableUsers users for which to mount the storage
	 * @param array|null $applicableGroups groups for which to mount the storage
	 * @param int|null $priority priority
	 *
	 * @return IStorageConfig|DataResponse
	 */
	protected function createStorage(
		$mountPoint,
		$backend,
		$authMechanism,
		$backendOptions,
		$mountOptions = null,
		$applicableUsers = null,
		$applicableGroups = null,
		$priority = null
	) {
		try {
			return $this->service->createStorage(
				$mountPoint,
				$backend,
				$authMechanism,
				$backendOptions,
				$mountOptions,
				$applicableUsers,
				$applicableGroups,
				$priority
			);
		} catch (\InvalidArgumentException $e) {
			$this->logger->logException($e);
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Invalid backend or authentication mechanism class')
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}
	}

	/**
	 * Validate storage config
	 *
	 * @param IStorageConfig $storage storage config
	 *1
	 * @return DataResponse|null returns response in case of validation error
	 */
	protected function validate(IStorageConfig $storage) {
		$mountPoint = $storage->getMountPoint();
		if ($mountPoint === '' || $mountPoint === '/') {
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Invalid mount point')
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		if ($storage->getBackendOption('objectstore')) {
			// objectstore must not be sent from client side
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Objectstore forbidden')
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		/** @var Backend */
		$backend = $storage->getBackend();
		/** @var AuthMechanism */
		$authMechanism = $storage->getAuthMechanism();
		if ($backend->checkDependencies()) {
			// invalid backend
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Invalid storage backend "%s"', [
						$backend->getIdentifier()
					])
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		if (!$backend->isVisibleFor($this->service->getVisibilityType())) {
			// not permitted to use backend
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Not permitted to use backend "%s"', [
						$backend->getIdentifier()
					])
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}
		if (!$authMechanism->isVisibleFor($this->service->getVisibilityType())) {
			// not permitted to use auth mechanism
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Not permitted to use authentication mechanism "%s"', [
						$authMechanism->getIdentifier()
					])
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		if (!$backend->validateStorage($storage)) {
			// unsatisfied parameters
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Unsatisfied backend parameters')
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}
		if (!$authMechanism->validateStorage($storage)) {
			// unsatisfied parameters
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Unsatisfied authentication mechanism parameters')
				],
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		return null;
	}

	protected function manipulateStorageConfig(IStorageConfig $storage) {
		/** @var AuthMechanism */
		$authMechanism = $storage->getAuthMechanism();
		$authMechanism->manipulateStorageConfig($storage);
		/** @var Backend */
		$backend = $storage->getBackend();
		$backend->manipulateStorageConfig($storage);
	}

	/**
	 * Check whether the given storage is available / valid.
	 *
	 * Note that this operation can be time consuming depending
	 * on whether the remote storage is available or not.
	 *
	 * @param IStorageConfig $storage storage configuration
	 * @param bool $testOnly whether to storage should only test the connection or do more things
	 */
	protected function updateStorageStatus(IStorageConfig &$storage, $testOnly = true) {
		try {
			$this->manipulateStorageConfig($storage);

			/** @var Backend */
			$backend = $storage->getBackend();
			// update status (can be time-consuming)
			$storage->setStatus(
				\OC_Mount_Config::getBackendStatus(
					$backend->getStorageClass(),
					$storage->getBackendOptions(),
					false,
					$testOnly
				)
			);
		} catch (InsufficientDataForMeaningfulAnswerException $e) {
			$status = $e->getCode() ? $e->getCode() : StorageNotAvailableException::STATUS_INDETERMINATE;
			$storage->setStatus(
				$status,
				$this->l10n->t('Insufficient data: %s', [$e->getMessage()])
			);
		} catch (StorageNotAvailableException $e) {
			$storage->setStatus(
				$e->getCode(),
				$this->l10n->t('%s', [$e->getMessage()])
			);
		} catch (\Exception $e) {
			// FIXME: convert storage exceptions to StorageNotAvailableException
			$storage->setStatus(
				StorageNotAvailableException::STATUS_ERROR,
				\get_class($e).': '.$e->getMessage()
			);
		}
	}

	/**
	 * Get all storage entries
	 *
	 * @return DataResponse
	 */
	public function index() {
		$storages = $this->service->getStorages();

		return new DataResponse(
			$storages,
			Http::STATUS_OK
		);
	}

	/**
	 * Get an external storage entry.
	 *
	 * @param int $id storage id
	 * @param bool $testOnly whether to storage should only test the connection or do more things
	 *
	 * @return DataResponse
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

		return new DataResponse(
			$storage,
			Http::STATUS_OK
		);
	}

	/**
	 * Deletes the storage with the given id.
	 *
	 * @param int $id storage id
	 *
	 * @return DataResponse
	 */
	public function destroy($id) {
		try {
			$this->service->removeStorage($id);
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Storage with id "%i" not found', [$id])
				],
				Http::STATUS_NOT_FOUND
			);
		}

		return new DataResponse([], Http::STATUS_NO_CONTENT);
	}
}
