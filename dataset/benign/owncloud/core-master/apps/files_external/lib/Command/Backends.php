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
use OCP\Files\External\Auth\AuthMechanism;
use OCP\Files\External\Backend\Backend;
use OCP\Files\External\DefinitionParameter;
use OCP\Files\External\IStoragesBackendService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Backends extends Base {
	/** @var IStoragesBackendService */
	private $backendService;

	public function __construct(IStoragesBackendService $backendService
	) {
		parent::__construct();

		$this->backendService = $backendService;
	}

	protected function configure() {
		$this
			->setName('files_external:backends')
			->setDescription('Show available authentication and storage backends')
			->addArgument(
				'type',
				InputArgument::OPTIONAL,
				'only show backends of a certain type. Possible values are "authentication" or "storage"'
			)->addArgument(
				'backend',
				InputArgument::OPTIONAL,
				'only show information of a specific backend'
			);
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$authBackends = $this->backendService->getAuthMechanisms();
		$storageBackends = $this->backendService->getBackends();

		$data = [
			'authentication' => \array_map([$this, 'serializeAuthBackend'], $authBackends),
			'storage' => \array_map([$this, 'serializeAuthBackend'], $storageBackends)
		];

		$type = $input->getArgument('type');
		$backend = $input->getArgument('backend');
		if ($type) {
			if (!isset($data[$type])) {
				$output->writeln('<error>Invalid type "' . $type . '". Possible values are "authentication" or "storage"</error>');
				return 1;
			}
			$data = $data[$type];

			if ($backend) {
				if (!isset($data[$backend])) {
					$output->writeln('<error>Unknown backend "' . $backend . '" of type  "' . $type . '"</error>');
					return 1;
				}
				$data = $data[$backend];
			}
		}

		$this->writeArrayInOutputFormat($input, $output, $data);
	}

	private function serializeAuthBackend(\JsonSerializable $backend) {
		$data = $backend->jsonSerialize();
		$result = [
			'name' => $data['name'],
			'identifier' => $data['identifier'],
			'configuration' => $this->formatConfiguration($data['configuration'])
		];
		if ($backend instanceof Backend) {
			$result['storage_class'] = $backend->getStorageClass();
			$authBackends = $this->backendService->getAuthMechanismsByScheme(\array_keys($backend->getAuthSchemes()));
			$result['supported_authentication_backends'] = \array_keys($authBackends);
			$authConfig = \array_map(function (AuthMechanism $auth) {
				return $this->serializeAuthBackend($auth)['configuration'];
			}, $authBackends);
			$result['authentication_configuration'] = \array_combine(\array_keys($authBackends), $authConfig);
		}
		return $result;
	}

	/**
	 * @param DefinitionParameter[] $parameters
	 * @return string[]
	 */
	private function formatConfiguration(array $parameters) {
		$configuration = \array_filter($parameters, function (DefinitionParameter $parameter) {
			return $parameter->getType() !== DefinitionParameter::VALUE_HIDDEN;
		});
		return \array_map(function (DefinitionParameter $parameter) {
			return $parameter->getTypeName();
		}, $configuration);
	}
}
