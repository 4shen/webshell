<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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
namespace OC\Console;

use OC\NeedsUpdateException;
use OC_App;
use OC_Defaults;
use OCP\AppFramework\QueryException;
use OCP\Console\ConsoleEvent;
use OCP\IConfig;
use OCP\IRequest;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Application {
	/** @var IConfig */
	private $config;
	/** @var EventDispatcherInterface */
	private $dispatcher;
	/** @var IRequest */
	private $request;

	/** @var SymfonyApplication  */
	private $application;

	/**
	 * @param IConfig $config
	 * @param EventDispatcherInterface $dispatcher
	 * @param IRequest $request
	 */
	public function __construct(IConfig $config, EventDispatcherInterface $dispatcher, IRequest $request) {
		$defaults = new OC_Defaults;
		$this->config = $config;
		$this->application = new SymfonyApplication($defaults->getName(), \OC_Util::getVersionString());
		$this->dispatcher = $dispatcher;
		$this->request = $request;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \Exception
	 */
	public function loadCommands(InputInterface $input, OutputInterface $output) {
		// $application is required to be defined in the register_command scripts
		$application = $this->application;
		$inputDefinition = $application->getDefinition();
		$inputDefinition->addOption(
			new InputOption(
				'no-warnings',
				null,
				InputOption::VALUE_NONE,
				'Skip global warnings, show command output only',
				null
			)
		);
		try {
			$input->bind($inputDefinition);
		} catch (\RuntimeException $e) {
			//expected if there are extra options
		}
		if ($input->getOption('no-warnings')) {
			$output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
		}
		$input = new ArgvInput();
		try {
			require_once __DIR__ . '/../../../core/register_command.php';
			if ($this->config->getSystemValue('installed', false)) {
				if (\OCP\Util::needUpgrade()) {
					throw new NeedsUpdateException();
				} elseif ($this->config->getSystemValue('maintenance', false)) {
					'@phan-var \Symfony\Component\Console\Output\ConsoleOutputInterface $output';
					$errOutput = $output->getErrorOutput();
					$errOutput->writeln('<comment>ownCloud is in maintenance mode - no app have been loaded</comment>' . PHP_EOL);
				} else {
					OC_App::loadApps();
					foreach (\OC::$server->getAppManager()->getInstalledApps() as $app) {
						$appPath = \OC_App::getAppPath($app);
						if ($appPath === false) {
							continue;
						}
						// load commands using info.xml
						$info = \OC_App::getAppInfo($app);
						if (isset($info['commands'])) {
							$this->loadCommandsFromInfoXml($info['commands']);
						}
						// load from register_command.php
						\OC_App::registerAutoloading($app, $appPath);
						$file = $appPath . '/appinfo/register_command.php';
						if (\file_exists($file)) {
							require $file;
						}
					}
				}
			} else {
				if ($input->getFirstArgument() !== 'maintenance:install') {
					$output->writeln("ownCloud is not installed - only a limited number of commands are available");
				}
			}
		} catch (NeedsUpdateException $ex) {
			$output->writeln("ownCloud or one of the apps require upgrade - only a limited number of commands are available");
			$output->writeln("You may use your browser or the occ upgrade command to do the upgrade");
		};
		if ($input->getFirstArgument() !== 'check') {
			$errors = \OC_Util::checkServer(\OC::$server->getConfig());
			if (!empty($errors)) {
				foreach ($errors as $error) {
					$output->writeln((string)$error['error']);
					$output->writeln((string)$error['hint']);
					$output->writeln('');
				}
				throw new \Exception("Environment not properly prepared.");
			}
		}
	}

	/**
	 * Sets whether to automatically exit after a command execution or not.
	 *
	 * @param bool $boolean Whether to automatically exit after a command execution or not
	 */
	public function setAutoExit($boolean) {
		$this->application->setAutoExit($boolean);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 * @throws \Exception
	 */
	public function run(InputInterface $input = null, OutputInterface $output = null) {
		$args = isset($this->request->server['argv']) ? $this->request->server['argv'] : [];
		$this->dispatcher->dispatch(
			new ConsoleEvent(ConsoleEvent::EVENT_RUN, $args),
			ConsoleEvent::EVENT_RUN
		);
		return $this->application->run($input, $output);
	}

	private function loadCommandsFromInfoXml($commands) {
		foreach ($commands as $command) {
			try {
				$c = \OC::$server->query($command);
			} catch (QueryException $e) {
				if (\class_exists($command)) {
					$c = new $command();
				} else {
					throw new \Exception("Console command '$command' is unknown and could not be loaded");
				}
			}

			$this->application->add($c);
		}
	}
}
