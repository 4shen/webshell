<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OC\Core\Command\Encryption;

use OCP\Encryption\IManager;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Enable extends Command {
	/** @var IConfig */
	protected $config;

	/** @var IManager */
	protected $encryptionManager;

	/**
	 * @param IConfig $config
	 * @param IManager $encryptionManager
	 */
	public function __construct(IConfig $config, IManager $encryptionManager) {
		parent::__construct();

		$this->encryptionManager = $encryptionManager;
		$this->config = $config;
	}

	protected function configure() {
		$this
			->setName('encryption:enable')
			->setDescription('Enable encryption.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if ($this->encryptionManager->isEnabled()) {
			$output->writeln('Encryption is already enabled');
		} else {
			// TODO add and use api to enable encryption
			$this->config->setAppValue('core', 'encryption_enabled', 'yes');
			$output->writeln('<info>Encryption enabled</info>');
		}
		$output->writeln('');

		$modules = $this->encryptionManager->getEncryptionModules();
		if (empty($modules)) {
			$output->writeln('<error>No encryption module is loaded</error>');
		} else {
			$defaultModule = $this->encryptionManager->getDefaultEncryptionModuleId();
			if ($defaultModule === '') {
				$output->writeln('<error>No default module is set</error>');
			} elseif (!isset($modules[$defaultModule])) {
				$output->writeln('<error>The current default module does not exist: ' . $defaultModule . '</error>');
			} else {
				$output->writeln('Default module: ' . $defaultModule);
			}
		}
	}
}
