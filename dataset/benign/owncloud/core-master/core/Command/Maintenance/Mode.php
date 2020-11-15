<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author scolebrook <scolebrook@mac.com>
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

namespace OC\Core\Command\Maintenance;

use \OCP\IConfig;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Mode extends Command {

	/** @var IConfig */
	protected $config;

	public function __construct(IConfig $config) {
		$this->config = $config;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maintenance:mode')
			->setDescription('Set maintenance mode.')
			->addOption(
				'on',
				null,
				InputOption::VALUE_NONE,
				'Enable maintenance mode.'
			)
			->addOption(
				'off',
				null,
				InputOption::VALUE_NONE,
				'Disable maintenance mode.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if ($input->getOption('on')) {
			$this->config->setSystemValue('maintenance', true);
			$output->writeln('Maintenance mode enabled');
			$output->writeln('Please also consider to stop the web server on all ownCloud instances');
		} elseif ($input->getOption('off')) {
			$this->config->setSystemValue('maintenance', false);
			$output->writeln('Maintenance mode disabled');
		} else {
			if ($this->config->getSystemValue('maintenance', false)) {
				$output->writeln('Maintenance mode is currently enabled');
			} else {
				$output->writeln('Maintenance mode is currently disabled');
			}
		}
	}
}
