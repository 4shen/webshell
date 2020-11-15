<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files_Trashbin\Command;

use OC\Command\FileAccess;
use OCA\Files_Trashbin\Expiration;
use OCA\Files_Trashbin\Quota;
use OCA\Files_Trashbin\TrashExpiryManager;
use OCP\Command\ICommand;

class Expire implements ICommand {
	use FileAccess;

	/**
	 * @var string
	 */
	private $user;

	/**
	 * @param string $user
	 */
	public function __construct($user) {
		$this->user = $user;
	}

	public function handle() {
		$userManager = \OC::$server->getUserManager();
		$trashExpiryManager = $this->getTrashExpiryManager();

		if (!$trashExpiryManager->expiryEnabled()) {
			return;
		}

		if (!$userManager->userExists($this->user)) {
			// User has been deleted already
			return;
		}

		\OC_Util::tearDownFS();
		\OC_Util::setupFS($this->user);

		$trashExpiryManager->expireTrash($this->user);

		\OC_Util::tearDownFS();
	}

	private function getTrashExpiryManager() {
		$expiration = new Expiration(
			\OC::$server->getConfig(),
			\OC::$server->getTimeFactory()
		);
		$quota = new Quota(
			\OC::$server->getUserManager(),
			\OC::$server->getConfig()
		);
		return new TrashExpiryManager(
			$expiration,
			$quota,
			\OC::$server->getLogger()
		);
	}
}
