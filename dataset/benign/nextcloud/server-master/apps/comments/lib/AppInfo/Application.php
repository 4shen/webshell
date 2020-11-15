<?php
/**
 * @copyright Copyright (c) 2016, Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Joas Schilling <coding@schilljs.com>
 * @author John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
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

namespace OCA\Comments\AppInfo;

use OCA\Comments\Capabilities;
use OCA\Comments\Controller\Notifications;
use OCA\Comments\EventHandler;
use OCA\Comments\JSSettingsHelper;
use OCA\Comments\Listener\CommentsEntityEventListener;
use OCA\Comments\Listener\LoadAdditionalScripts;
use OCA\Comments\Listener\LoadSidebarScripts;
use OCA\Comments\Notification\Notifier;
use OCA\Comments\Search\LegacyProvider;
use OCA\Comments\Search\Provider;
use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files\Event\LoadSidebar;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Comments\CommentsEntityEvent;
use OCP\IServerContainer;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'comments';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerCapability(Capabilities::class);

		$context->registerServiceAlias('NotificationsController', Notifications::class);

		$context->registerEventListener(
			LoadAdditionalScriptsEvent::class,
			LoadAdditionalScripts::class
		);
		$context->registerEventListener(
			LoadSidebar::class,
			LoadSidebarScripts::class
		);
		$context->registerEventListener(
			CommentsEntityEvent::EVENT_ENTITY,
			CommentsEntityEventListener::class
		);
		$context->registerSearchProvider(Provider::class);
	}

	public function boot(IBootContext $context): void {
		$this->registerNotifier($context->getServerContainer());
		$this->registerCommentsEventHandler($context->getServerContainer());

		$jsSettingsHelper = new JSSettingsHelper($context->getServerContainer());
		Util::connectHook('\OCP\Config', 'js', $jsSettingsHelper, 'extend');

		$context->getServerContainer()->getSearch()->registerProvider(LegacyProvider::class, ['apps' => ['files']]);
	}

	protected function registerNotifier(IServerContainer $container) {
		$container->getNotificationManager()->registerNotifierService(Notifier::class);
	}

	protected function registerCommentsEventHandler(IServerContainer $container) {
		$container->getCommentsManager()->registerEventHandler(function () {
			return $this->getContainer()->query(EventHandler::class);
		});
	}
}
