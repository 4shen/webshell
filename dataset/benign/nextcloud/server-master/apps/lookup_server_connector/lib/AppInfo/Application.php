<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2019 Joas Schilling <coding@schilljs.com>
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\LookupServerConnector\AppInfo;

use OCA\LookupServerConnector\UpdateLookupServer;
use OCP\AppFramework\App;
use OCP\IUser;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App {
	public function __construct() {
		parent::__construct('lookup_server_connector');
	}

	/**
	 * Register the different app parts
	 */
	public function register(): void {
		$this->registerHooksAndEvents();
	}

	/**
	 * Register the hooks and events
	 */
	public function registerHooksAndEvents(): void {
		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();
		$dispatcher->addListener('OC\AccountManager::userUpdated', static function (GenericEvent $event) {
			/** @var IUser $user */
			$user = $event->getSubject();

			/** @var UpdateLookupServer $updateLookupServer */
			$updateLookupServer = \OC::$server->query(UpdateLookupServer::class);
			$updateLookupServer->userUpdated($user);
		});
	}
}
