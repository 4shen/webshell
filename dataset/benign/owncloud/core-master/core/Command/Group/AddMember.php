<?php
/**
 * @author Phil Davis <phil@jankaritech.com>
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

namespace OC\Core\Command\Group;

use OCP\IGroupManager;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AddMember extends Command {
	/** @var \OCP\IGroupManager */
	protected $groupManager;

	/** @var IUserManager  */
	private $userManager;

	/**
	 * @param IGroupManager $groupManager
	 */
	public function __construct(IGroupManager $groupManager, IUserManager $userManager) {
		parent::__construct();
		$this->groupManager = $groupManager;
		$this->userManager = $userManager;
	}

	protected function configure() {
		$this
			->setName('group:add-member')
			->setDescription('Add members to a group.')
			->addArgument(
				'group',
				InputArgument::REQUIRED,
				'Name of the group.'
			)
			->addOption(
				'member',
				'm',
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'The member that should be added to the group.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$groupName = $input->getArgument('group');
		$group = $this->groupManager->get($groupName);
		if (!$group) {
			$output->writeln('<error>Group "' . $groupName . '" does not exist</error>');
			return 1;
		}

		$members = $input->getOption('member');

		if (!\count($members)) {
			$output->writeln('<error>No members specified</error>');
			return 1;
		}

		$memberExistsError = false;

		foreach ($members as $userName) {
			$user = $this->userManager->get($userName);
			if ($user) {
				if ($group->inGroup($user)) {
					$output->writeln('<info>User "' . $user->getUID() . '" is already a member of group "' . $group->getGID() . '"</info>');
				} else {
					$group->addUser($user);
					$output->writeln('<info>User "' . $user->getUID() . '" added to group "' . $group->getGID() . '"</info>');
				}
			} else {
				$output->writeln('<error>User "' . $userName . '" could not be found - not added to group "' . $group->getGID() . '"</error>');
				$memberExistsError = true;
			}
		}
		
		if ($memberExistsError) {
			return 1;
		}
	}
}
