<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Tobias Kaminsky <tobias@kaminsky.me>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files\AppInfo;

use OC\Search\Provider\File;
use OCA\Files\Capabilities;
use OCA\Files\Collaboration\Resources\Listener;
use OCA\Files\Collaboration\Resources\ResourceProvider;
use OCA\Files\Controller\ApiController;
use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files\Event\LoadSidebar;
use OCA\Files\Listener\LegacyLoadAdditionalScriptsAdapter;
use OCA\Files\Listener\LoadSidebarListener;
use OCA\Files\Notification\Notifier;
use OCA\Files\Search\FilesSearchProvider;
use OCA\Files\Service\TagService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Collaboration\Resources\IProviderManager;
use OCP\IContainer;
use OCP\IL10N;
use OCP\IServerContainer;
use OCP\Notification\IManager;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'files';

	public function __construct(array $urlParams=[]) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		/**
		 * Controllers
		 */
		$context->registerService('APIController', function (IContainer $c) {
			/** @var IServerContainer $server */
			$server = $c->query(IServerContainer::class);

			return new ApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$server->getUserSession(),
				$c->query('TagService'),
				$server->getPreviewManager(),
				$server->getShareManager(),
				$server->getConfig(),
				$server->getUserFolder()
			);
		});

		/**
		 * Services
		 */
		$context->registerService('TagService', function (IContainer $c) {
			/** @var IServerContainer $server */
			$server = $c->query(IServerContainer::class);

			return new TagService(
				$server->getUserSession(),
				$server->getActivityManager(),
				$server->getTagManager()->load(self::APP_ID),
				$server->getUserFolder(),
				$server->getEventDispatcher()
			);
		});

		/*
		 * Register capabilities
		 */
		$context->registerCapability(Capabilities::class);

		$context->registerEventListener(LoadAdditionalScriptsEvent::class, LegacyLoadAdditionalScriptsAdapter::class);
		$context->registerEventListener(LoadSidebar::class, LoadSidebarListener::class);

		$context->registerSearchProvider(FilesSearchProvider::class);
	}

	public function boot(IBootContext $context): void {
		$this->registerCollaboration($context);
		Listener::register($context->getServerContainer()->getEventDispatcher());
		$this->registerNotification($context);
		$this->registerSearchProvider($context);
		$this->registerTemplates();
		$this->registerNavigation($context);
		$this->registerHooks();
	}

	/**
	 * Register Collaboration ResourceProvider
	 */
	private function registerCollaboration(IBootContext $context): void {
		/** @var IProviderManager $providerManager */
		$providerManager = $context->getAppContainer()->query(IProviderManager::class);
		$providerManager->registerResourceProvider(ResourceProvider::class);
	}

	private function registerNotification(IBootContext $context): void {
		/** @var IManager $notifications */
		$notifications = $context->getAppContainer()->query(IManager::class);
		$notifications->registerNotifierService(Notifier::class);
	}

	/**
	 * @param IBootContext $context
	 */
	private function registerSearchProvider(IBootContext $context): void {
		$context->getServerContainer()->getSearch()->registerProvider(File::class, ['apps' => ['files']]);
	}

	private function registerTemplates(): void {
		$templateManager = \OC_Helper::getFileTemplateManager();
		$templateManager->registerTemplate('application/vnd.oasis.opendocument.presentation', 'core/templates/filetemplates/template.odp');
		$templateManager->registerTemplate('application/vnd.oasis.opendocument.text', 'core/templates/filetemplates/template.odt');
		$templateManager->registerTemplate('application/vnd.oasis.opendocument.spreadsheet', 'core/templates/filetemplates/template.ods');
	}

	private function registerNavigation(IBootContext $context): void {
		/** @var IL10N $l10n */
		$l10n = $context->getAppContainer()->query(IL10N::class);
		\OCA\Files\App::getNavigationManager()->add([
			'id' => 'files',
			'appname' => 'files',
			'script' => 'list.php',
			'order' => 0,
			'name' => $l10n->t('All files')
		]);
		\OCA\Files\App::getNavigationManager()->add([
			'id' => 'recent',
			'appname' => 'files',
			'script' => 'recentlist.php',
			'order' => 2,
			'name' => $l10n->t('Recent')
		]);
		\OCA\Files\App::getNavigationManager()->add([
			'id' => 'favorites',
			'appname' => 'files',
			'script' => 'simplelist.php',
			'order' => 5,
			'name' => $l10n->t('Favorites'),
			'expandedState' => 'show_Quick_Access'
		]);
	}

	private function registerHooks(): void {
		Util::connectHook('\OCP\Config', 'js', '\OCA\Files\App', 'extendJsConfig');
	}
}
