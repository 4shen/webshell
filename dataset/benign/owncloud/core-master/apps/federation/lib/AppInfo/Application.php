<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Roeland Jago Douma <rullzer@users.noreply.github.com>
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

namespace OCA\Federation\AppInfo;

use OCA\Federation\Controller\SettingsController;
use OCA\Federation\DAV\FedAuth;
use OCA\Federation\DbHandler;
use OCA\Federation\Hooks;
use OCA\Federation\Middleware\AddServerMiddleware;
use OCA\Federation\SyncFederationAddressBooks;
use OCA\Federation\TrustedServers;
use OCP\AppFramework\IAppContainer;
use OCP\SabrePluginEvent;
use OCP\Util;
use Sabre\DAV\Auth\Plugin;

class Application extends \OCP\AppFramework\App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = []) {
		parent::__construct('federation', $urlParams);
		$this->registerService();
		$this->registerMiddleware();
	}

	private function registerService() {
		$container = $this->getContainer();

		$container->registerService('addServerMiddleware', function (IAppContainer $c) {
			return new AddServerMiddleware(
				$c->getAppName(),
				\OC::$server->getL10N($c->getAppName()),
				\OC::$server->getLogger()
			);
		});

		$container->registerService('DbHandler', function (IAppContainer $c) {
			return new DbHandler(
				\OC::$server->getDatabaseConnection(),
				\OC::$server->getL10N($c->getAppName())
			);
		});

		$container->registerService('TrustedServers', function (IAppContainer $c) {
			$server = $c->getServer();
			return new TrustedServers(
				$c->query('DbHandler'),
				$server->getHTTPClientService(),
				$server->getLogger(),
				$server->getJobList(),
				$server->getSecureRandom(),
				$server->getConfig(),
				$server->getEventDispatcher()
			);
		});

		$container->registerService('SettingsController', function (IAppContainer $c) {
			$server = $c->getServer();
			return new SettingsController(
				$c->getAppName(),
				$server->getRequest(),
				$server->getL10N($c->getAppName()),
				$c->query('TrustedServers')
			);
		});
	}

	private function registerMiddleware() {
		$container = $this->getContainer();
		$container->registerMiddleWare('addServerMiddleware');
	}

	/**
	 * listen to federated_share_added hooks to auto-add new servers to the
	 * list of trusted servers.
	 */
	public function registerHooks() {
		$container = $this->getContainer();
		$hooksManager = new Hooks($container->query('TrustedServers'));

		Util::connectHook(
				'OCP\Share',
				'federated_share_added',
				$hooksManager,
				'addServerHook'
		);

		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();
		$dispatcher->addListener('OCA\DAV\Connector\Sabre::authInit', function ($event) use ($container) {
			if ($event instanceof SabrePluginEvent) {
				$authPlugin = $event->getServer()->getPlugin('auth');
				if ($authPlugin instanceof Plugin) {
					$h = new DbHandler($container->getServer()->getDatabaseConnection(),
							$container->getServer()->getL10N('federation')
					);
					$authPlugin->addBackend(new FedAuth($h));
				}
			}
		});

		$dispatcher->addListener(
			'remoteshare.received',
			function ($event) use ($container) {
				$remote = $event->getArgument('remote');
				$trustedServers = $container->query('TrustedServers');
				$event->setArgument('autoAddServers', $trustedServers->getAutoAddServers());
				$event->setArgument('isRemoteTrusted', $trustedServers->isTrustedServer($remote));
			}
		);
	}

	/**
	 * @return SyncFederationAddressBooks
	 */
	public function getSyncService() {
		$syncService = \OC::$server->query('CardDAVSyncService');
		$dbHandler = $this->getContainer()->query('DbHandler');
		return new SyncFederationAddressBooks($dbHandler, $syncService);
	}
}
