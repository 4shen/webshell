<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace OC\Core\Command\User;

use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Report extends Command {
	/** @var IUserManager */
	protected $userManager;

	/**
	 * @param IUserManager $userManager
	 */
	public function __construct(IUserManager $userManager) {
		$this->userManager = $userManager;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('user:report')
			->setDescription('shows how many users have access');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = new Table($output);
		$table->setHeaders(['User Report', '']);
		$userCountArray = $this->countUsers();
		if (!empty($userCountArray)) {
			$total = 0;
			$rows = [];
			foreach ($userCountArray as $classname => $users) {
				$total += $users;
				$rows[] = [$classname, $users];
			}

			$rows[] = [' '];
			$rows[] = ['total users', $total];
		} else {
			$rows[] = ['No backend enabled that supports user counting', ''];
		}

		$userDirectoryCount = $this->countUserDirectories();
		$rows[] = [' '];
		$rows[] = ['user directories', $userDirectoryCount];

		$table->setRows($rows);
		$table->render($output);
	}

	private function countUsers() {
		return $this->userManager->countUsers();
	}

	private function countUserDirectories() {
		$dataview = new \OC\Files\View('/');
		$userDirectories = $dataview->getDirectoryContent('/', 'httpd/unix-directory');
		return \count($userDirectories);
	}
}
