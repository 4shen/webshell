<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Jens-Christian Fischer <jens-christian.fischer@switch.ch>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace OC\Core\Command\User;

use OC\User\DeletedUser;
use OC\User\Manager;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Delete extends Command {
	/** @var IUserManager */
	private $userManager;

	/**
	 * @param IUserManager $userManager
	 * @param IRootFolder $rootFolder
	 */
	public function __construct(IUserManager $userManager) {
		$this->userManager = $userManager;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('user:delete')
			->setDescription('Deletes the specified user.')
			->addArgument(
				'uid',
				InputArgument::REQUIRED,
				'The username.'
			)
			->addOption(
				'force',
				'f',
				InputOption::VALUE_NONE,
				'Try to force the deletion of the user data even if the user is missing.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$uid = $input->getArgument('uid');
		$user = $this->userManager->get($uid);
		if ($user === null) {
			if ($input->getOption('force')) {
				$deletedUser = $this->userManager->get($uid, true);
				$deletedUser->delete();
				$output->writeln("<info>User deleted.</info>");
				return 0;
			}
			$output->writeln("<error>User with uid '$uid' does not exist</error>");
			return 1;
		}

		$uid = $user->getUID();
		$displayName = $user->getDisplayName();
		$email = $user->getEMailAddress();
		if ($user->delete()) {
			$output->writeln("<info>User with uid '$uid', display name '$displayName', email '$email' was deleted</info>");
			return 0;
		}

		$output->writeln("<error>User with uid '$uid', display name '$displayName', email '$email' could not be deleted. Please check the logs.</error>");
		return 1;
	}
}
