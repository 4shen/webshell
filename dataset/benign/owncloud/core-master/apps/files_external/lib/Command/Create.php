<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files_External\Command;

use OC\Core\Command\Base;
use OC\Files\Filesystem;
use OC\User\NoUserException;
use OCP\Files\External\Auth\AuthMechanism;
use OCP\Files\External\Backend\Backend;
use OCP\Files\External\DefinitionParameter;
use OCP\Files\External\IStorageConfig;
use OCP\Files\External\IStoragesBackendService;
use OCP\Files\External\Service\IGlobalStoragesService;
use OCP\Files\External\Service\IUserStoragesService;
use OCP\IUserManager;
use OCP\IUserSession;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends Base {
	/**
	 * @var IGlobalStoragesService
	 */
	private $globalService;

	/**
	 * @var IUserStoragesService
	 */
	private $userService;

	/**
	 * @var IUserManager
	 */
	private $userManager;

	/** @var IStoragesBackendService */
	private $backendService;

	/** @var IUserSession */
	private $userSession;

	public function __construct(IGlobalStoragesService $globalService,
						 IUserStoragesService $userService,
						 IUserManager $userManager,
						 IUserSession $userSession,
						 IStoragesBackendService $backendService
	) {
		parent::__construct();
		$this->globalService = $globalService;
		$this->userService = $userService;
		$this->userManager = $userManager;
		$this->userSession = $userSession;
		$this->backendService = $backendService;
	}

	protected function configure() {
		$this
			->setName('files_external:create')
			->setDescription('Create a new mount configuration')
			->addOption(
				'user',
				null,
				InputOption::VALUE_OPTIONAL,
				'user to add the mount configuration for, if not set the mount will be added as system mount'
			)
			->addArgument(
				'mount_point',
				InputArgument::REQUIRED,
				'mount point for the new mount'
			)
			->addArgument(
				'storage_backend',
				InputArgument::REQUIRED,
				'storage backend identifier for the new mount, see `occ files_external:backends` for possible values'
			)
			->addArgument(
				'authentication_backend',
				InputArgument::REQUIRED,
				'authentication backend identifier for the new mount, see `occ files_external:backends` for possible values'
			)
			->addOption(
				'config',
				'c',
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'Mount configuration option in key=value format'
			)
			->addOption(
				'dry',
				null,
				InputOption::VALUE_NONE,
				'Don\'t save the created mount, only list the new mount'
			);
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$user = $input->getOption('user');
		$mountPoint = $input->getArgument('mount_point');
		$storageIdentifier = $input->getArgument('storage_backend');
		$authIdentifier = $input->getArgument('authentication_backend');
		$configInput = $input->getOption('config');

		$storageBackend = $this->backendService->getBackend($storageIdentifier);
		$authBackend = $this->backendService->getAuthMechanism($authIdentifier);

		if (!Filesystem::isValidPath($mountPoint)) {
			$output->writeln('<error>Invalid mountpoint "' . $mountPoint . '"</error>');
			return 1;
		}
		if ($storageBackend === null) {
			$output->writeln('<error>Storage backend with identifier "' . $storageIdentifier . '" not found (see `occ files_external:backends` for possible values)</error>');
			return 404;
		}
		if ($authBackend === null) {
			$output->writeln('<error>Authentication backend with identifier "' . $authIdentifier . '" not found (see `occ files_external:backends` for possible values)</error>');
			return 404;
		}
		$supportedSchemes = \array_keys($storageBackend->getAuthSchemes());
		if (!\in_array($authBackend->getScheme(), $supportedSchemes)) {
			$output->writeln('<error>Authentication backend "' . $authIdentifier . '" not valid for storage backend "' . $storageIdentifier . '" (see `occ files_external:backends storage ' . $storageIdentifier . '` for possible values)</error>');
			return 1;
		}

		$config = [];
		foreach ($configInput as $configOption) {
			if (!\strpos($configOption, '=')) {
				$output->writeln('<error>Invalid mount configuration option "' . $configOption . '"</error>');
				return 1;
			}
			list($key, $value) = \explode('=', $configOption, 2);
			if (!$this->validateParam($key, $value, $storageBackend, $authBackend)) {
				$output->writeln('<error>Unknown configuration for backends "' . $key . '"</error>');
				return 1;
			}
			$config[$key] = $value;
		}

		$mount = $this->getStorageService($user)->createConfig();
		$mount->setMountPoint($mountPoint);
		$mount->setBackend($storageBackend);
		$mount->setAuthMechanism($authBackend);
		$mount->setBackendOptions($config);

		if ($user) {
			if (!$this->userManager->userExists($user)) {
				$output->writeln('<error>User "' . $user . '" not found</error>');
				return 1;
			}
			$mount->setApplicableUsers([$user]);
		}

		if ($input->getOption('dry')) {
			$this->showMount($user, $mount, $input, $output);
		} else {
			$this->getStorageService($user)->addStorage($mount);
			if ($input->getOption('output') === self::OUTPUT_FORMAT_PLAIN) {
				$output->writeln('<info>Storage created with id ' . $mount->getId() . '</info>');
			} else {
				$output->writeln($mount->getId());
			}
		}
		return 0;
	}

	private function validateParam($key, &$value, Backend $storageBackend, AuthMechanism $authBackend) {
		$params = \array_merge($storageBackend->getParameters(), $authBackend->getParameters());
		foreach ($params as $param) {
			/** @var DefinitionParameter $param */
			if ($param->getName() === $key) {
				if ($param->getType() === DefinitionParameter::VALUE_BOOLEAN) {
					$value = ($value === 'true');
				}
				return true;
			}
		}
		return false;
	}

	private function showMount($user, IStorageConfig $mount, InputInterface $input, OutputInterface $output) {
		$listCommand = new ListCommand($this->globalService, $this->userService, $this->userSession, $this->userManager);
		$listInput = new ArrayInput([], $listCommand->getDefinition());
		$listInput->setOption('output', $input->getOption('output'));
		$listInput->setOption('show-password', true);
		$listCommand->listMounts($user, [$mount], $listInput, $output);
	}

	protected function getStorageService($userId) {
		if (!empty($userId)) {
			$user = $this->userManager->get($userId);
			if ($user === null) {
				throw new NoUserException("user $userId not found");
			}
			$this->userSession->setUser($user);
			return $this->userService;
		} else {
			return $this->globalService;
		}
	}
}
