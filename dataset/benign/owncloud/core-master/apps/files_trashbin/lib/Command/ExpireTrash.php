<?php
/**
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OCA\Files_Trashbin\Command;

use OCA\Files_Trashbin\TrashExpiryManager;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireTrash extends Command {

	/**
	 * @var TrashExpiryManager
	 */
	private $trashExpiryManager;
	
	/**
	 * @var IUserManager
	 */
	private $userManager;

	/**
	 * @param IUserManager|null $userManager
	 * @param TrashExpiryManager|null $trashExpiryManager
	 */
	public function __construct(IUserManager $userManager = null,
								TrashExpiryManager $trashExpiryManager = null) {
		parent::__construct();

		$this->userManager = $userManager;
		$this->trashExpiryManager = $trashExpiryManager;
	}

	protected function configure() {
		$this
			->setName('trashbin:expire')
			->setDescription('Expires the users trashbin')
			->addArgument(
				'user_id',
				InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
				'expires the trashbin of the given user(s), if no user is given the trash for all users will be expired'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$retentionEnabled = $this->trashExpiryManager->retentionEnabled();
		if (!$retentionEnabled) {
			$output->writeln("Auto expiration is configured - expiration will be handled automatically.");
			return;
		}

		$users = $input->getArgument('user_id');
		if (!empty($users)) {
			foreach ($users as $user) {
				if ($this->userManager->userExists($user)) {
					$output->writeln("Remove deleted files of   <info>$user</info>");
					$userObject = $this->userManager->get($user);
					$this->expireTrashForUser($userObject);
				} else {
					$output->writeln("<error>Unknown user $user</error>");
				}
			}
		} else {
			$p = new ProgressBar($output);
			$p->start();
			$this->userManager->callForSeenUsers(function (IUser $user) use ($p) {
				$p->advance();
				$this->expireTrashForUser($user);
			});
			$p->finish();
			$output->writeln('');
		}
	}

	public function expireTrashForUser(IUser $user) {
		$uid = $user->getUID();
		if (!$this->setupFS($uid)) {
			return;
		}

		$this->trashExpiryManager->expireTrashByRetention($uid);
	}

	/**
	 * Act on behalf on trash item owner
	 * @param string $user
	 * @return boolean
	 */
	protected function setupFS($user) {
		\OC_Util::tearDownFS();
		\OC_Util::setupFS($user);

		// Check if this user has a trashbin directory
		$view = new \OC\Files\View('/' . $user);
		if (!$view->is_dir('/files_trashbin/files')) {
			return false;
		}

		return true;
	}
}
