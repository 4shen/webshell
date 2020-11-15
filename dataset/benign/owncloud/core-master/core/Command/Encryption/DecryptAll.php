<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author davitol <dtoledo@solidgear.es>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Sergio Bertolín <sbertolin@solidgear.es>
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

namespace OC\Core\Command\Encryption;

use OCP\App\IAppManager;
use OCP\Encryption\IManager;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DecryptAll extends Command {

	/** @var IManager */
	protected $encryptionManager;

	/** @var  IAppManager */
	protected $appManager;

	/** @var IConfig */
	protected $config;

	/** @var  QuestionHelper */
	protected $questionHelper;

	/** @var bool */
	protected $wasTrashbinEnabled;

	/** @var  bool */
	protected $wasSingleUserModeEnabled;

	/** @var \OC\Encryption\DecryptAll */
	protected $decryptAll;

	/**
	 * @param IManager $encryptionManager
	 * @param IAppManager $appManager
	 * @param IConfig $config
	 * @param \OC\Encryption\DecryptAll $decryptAll
	 * @param QuestionHelper $questionHelper
	 */
	public function __construct(
		IManager $encryptionManager,
		IAppManager $appManager,
		IConfig $config,
		\OC\Encryption\DecryptAll $decryptAll,
		QuestionHelper $questionHelper
	) {
		parent::__construct();

		$this->appManager = $appManager;
		$this->encryptionManager = $encryptionManager;
		$this->config = $config;
		$this->decryptAll = $decryptAll;
		$this->questionHelper = $questionHelper;
	}

	/**
	 * Set single user mode and disable the trashbin app
	 */
	protected function forceSingleUserAndTrashbin() {
		$this->wasTrashbinEnabled = $this->appManager->isEnabledForUser('files_trashbin');
		$this->wasSingleUserModeEnabled = $this->config->getSystemValue('singleuser', false);
		$this->config->setSystemValue('singleuser', true);
		$this->appManager->disableApp('files_trashbin');
	}

	/**
	 * Reset the single user mode and re-enable the trashbin app
	 */
	protected function resetSingleUserAndTrashbin() {
		$this->config->setSystemValue('singleuser', $this->wasSingleUserModeEnabled);
		if ($this->wasTrashbinEnabled) {
			$this->appManager->enableApp('files_trashbin');
		}
	}

	protected function configure() {
		parent::configure();

		$this->setName('encryption:decrypt-all');
		$this->setDescription('Disable server-side encryption and decrypt all files.');
		$this->setHelp(
			'This will disable server-side encryption and decrypt all files for '
			. 'all users if it is supported by your encryption module. '
			. 'Please make sure that no user access his files during this process!'
		);
		$this->addArgument(
			'user',
			InputArgument::OPTIONAL,
			'User for whom you want to decrypt all files (optional).',
			''
		);
		$this->addOption(
			'method',
			'm',
			InputOption::VALUE_OPTIONAL,
			'Use recovery or password. If recovery method is chosen then the recovery password will be used to decrypt files. If password method is chosen then individual user passwords will be used to decrypt files.'
		);
		$this->addOption(
			'continue',
			'c',
			InputOption::VALUE_OPTIONAL,
			'Provide yes or no. Whether to ask for permission to continue.',
			'no'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$confirmed = $input->getOption('continue');
		if (($confirmed !== 'yes') && ($confirmed !== 'no')) {
			$output->writeln('Continue can accept either yes or no');
			return null;
		}

		try {
			if ($this->encryptionManager->isEnabled() === true) {
				$output->write('Disable server side encryption... ');
				$this->config->setAppValue('core', 'encryption_enabled', 'no');
				$output->writeln('done.');
			} else {
				$output->writeln('Server side encryption not enabled. Nothing to do.');
				return;
			}

			$uid = $input->getArgument('user');
			if ($uid === '') {
				$message = 'your ownCloud';
			} else {
				$message = "$uid's account";
			}

			$output->writeln("\n");
			$output->writeln("You are about to start to decrypt all files stored in $message.");
			$output->writeln('It will depend on the encryption module and your setup if this is possible.');
			$output->writeln('Depending on the number and size of your files this can take some time');
			$output->writeln('Please make sure that no user access his files during this process!');
			$output->writeln('');
			$question = new ConfirmationQuestion('Do you really want to continue? (y/n) ', false);
			if (($confirmed === 'yes') || $this->questionHelper->ask($input, $output, $question)) {
				$this->forceSingleUserAndTrashbin();
				$user = $input->getArgument('user');
				$result = $this->decryptAll->decryptAll($input, $output, $user);
				if ($result === false) {
					$output->writeln(' aborted.');
					$output->writeln('Server side encryption remains enabled');
					$this->config->setAppValue('core', 'encryption_enabled', 'yes');
				} elseif ($uid !== '') {
					$output->writeln('Server side encryption remains enabled');
					$this->config->setAppValue('core', 'encryption_enabled', 'yes');
				}
				$this->resetSingleUserAndTrashbin();
			} else {
				$output->write('Enable server side encryption... ');
				$this->config->setAppValue('core', 'encryption_enabled', 'yes');
				$output->writeln('done.');
				$output->writeln('aborted');
			}
		} catch (\Exception $e) {
			// enable server side encryption again if something went wrong
			$this->config->setAppValue('core', 'encryption_enabled', 'yes');
			$this->resetSingleUserAndTrashbin();
			throw $e;
		}
	}
}
