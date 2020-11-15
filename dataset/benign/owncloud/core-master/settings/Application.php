<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Tom Needham <tom@owncloud.com>
 * @author Ujjwal Bhardwaj <ujjwalb1996@gmail.com>
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

namespace OC\Settings;

use OC\Server;
use OC\AppFramework\Utility\TimeFactory;
use OC\Settings\Controller\CorsController;
use OC\Settings\Controller\SettingsPageController;
use OC\Settings\Controller\AppSettingsController;
use OC\Settings\Controller\AuthSettingsController;
use OC\Settings\Controller\CertificateController;
use OC\Settings\Controller\CheckSetupController;
use OC\Settings\Controller\GroupsController;
use OC\Settings\Controller\LegalSettingsController;
use OC\Settings\Controller\LogSettingsController;
use OC\Settings\Controller\MailSettingsController;
use OC\Settings\Controller\UsersController;
use OC\Settings\Middleware\SubadminMiddleware;
use OCP\AppFramework\App;
use OCP\IContainer;
use OCP\Util;
use OC\Files\View;

/**
 * @package OC\Settings
 */
class Application extends App {

	/**
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams=[]) {
		parent::__construct('settings', $urlParams);

		$container = $this->getContainer();

		/**
		 * Controllers
		 */
		$container->registerService('SettingsPageController', function (IContainer $c) {
			return new SettingsPageController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('SettingsManager'),
				$c->query('ServerContainer')->getURLGenerator(),
				$c->query('GroupManager'),
				$c->query('UserSession')
			);
		});
		$container->registerService('MailSettingsController', function (IContainer $c) {
			return new MailSettingsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N'),
				$c->query('Config'),
				$c->query('UserSession'),
				$c->query('Defaults'),
				$c->query('Mailer'),
				$c->query('DefaultMailAddress')
			);
		});
		$container->registerService('AppSettingsController', function (IContainer $c) {
			return new AppSettingsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N'),
				$c->query('Config'),
				$c->query('IAppManager')
			);
		});
		$container->registerService('AuthSettingsController', function (IContainer $c) {
			return new AuthSettingsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('ServerContainer')->query('OC\Authentication\Token\IProvider'),
				$c->query('UserManager'),
				$c->query('ServerContainer')->getSession(),
				$c->query('ServerContainer')->getSecureRandom(),
				$c->query('UserId')
			);
		});
		$container->registerService('CertificateController', function (IContainer $c) {
			return new CertificateController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('CertificateManager'),
				$c->query('SystemCertificateManager'),
				$c->query('L10N'),
				$c->query('IAppManager')
			);
		});
		$container->registerService('GroupsController', function (IContainer $c) {
			return new GroupsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('GroupManager'),
				$c->query('UserSession'),
				$c->query('IsAdmin'),
				$c->query('L10N')
			);
		});
		$container->registerService('UsersController', function (IContainer $c) {
			return new UsersController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('UserManager'),
				$c->query('GroupManager'),
				$c->query('UserSession'),
				$c->query('Config'),
				$c->query('SecureRandom'),
				$c->query('IsAdmin'),
				$c->query('L10N'),
				$c->query('Logger'),
				$c->query('Defaults'),
				$c->query('Mailer'),
				$c->query('TimeFactory'),
				$c->query('DefaultMailAddress'),
				$c->query('URLGenerator'),
				$c->query('OCP\\App\\IAppManager'),
				$c->query('OCP\\IAvatarManager'),
				$c->query('ServerContainer')->getEventDispatcher()
			);
		});
		$container->registerService('LogSettingsController', function (IContainer $c) {
			return new LogSettingsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Config'),
				$c->query('L10N')
			);
		});
		$container->registerService('LegalSettingsController', function (IContainer $c) {
			return new LegalSettingsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N'),
				$c->query('Config')
			);
		});
		$container->registerService('CheckSetupController', function (IContainer $c) {
			return new CheckSetupController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Config'),
				$c->query('ClientService'),
				$c->query('URLGenerator'),
				$c->query('Util'),
				$c->query('L10N'),
				$c->query('Checker')
			);
		});
		$container->registerService('CorsController', function (IContainer $c) {
			return new CorsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('UserSession'),
				$c->query('Logger'),
				$c->query('URLGenerator'),
				$c->query('Config'),
				$c->query('L10N')
			);
		});

		/**
		 * Middleware
		 */
		$container->registerService('SubadminMiddleware', function (IContainer $c) {
			return new SubadminMiddleware(
				$c->query('ControllerMethodReflector'),
				$c->query('IsSubAdmin')
			);
		});
		// Execute middlewares
		$container->registerMiddleware('SubadminMiddleware');

		/**
		 * Core class wrappers
		 */
		$container->registerService('Config', function (IContainer $c) {
			return $c->query('ServerContainer')->getConfig();
		});
		$container->registerService('ICacheFactory', function (IContainer $c) {
			return $c->query('ServerContainer')->getMemCacheFactory();
		});
		$container->registerService('L10N', function (IContainer $c) {
			return $c->query('ServerContainer')->getL10N('settings');
		});
		$container->registerService('GroupManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getGroupManager();
		});
		$container->registerService('UserManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getUserManager();
		});
		$container->registerService('UserSession', function (IContainer $c) {
			return $c->query('ServerContainer')->getUserSession();
		});
		/** FIXME: Remove once OC_User is non-static and mockable */
		$container->registerService('IsAdmin', function (IContainer $c) {
			return \OC_User::isAdminUser(\OC_User::getUser());
		});
		/** FIXME: Remove once OC_SubAdmin is non-static and mockable */
		$container->registerService('IsSubAdmin', function (IContainer $c) {
			$userObject = \OC::$server->getUserSession()->getUser();
			$isSubAdmin = false;
			if ($userObject !== null) {
				$isSubAdmin = \OC::$server->getGroupManager()->getSubAdmin()->isSubAdmin($userObject);
			}
			return $isSubAdmin;
		});
		$container->registerService('Mailer', function (IContainer $c) {
			return $c->query('ServerContainer')->getMailer();
		});
		$container->registerService('Defaults', function (IContainer $c) {
			return new \OC_Defaults;
		});
		$container->registerService('DefaultMailAddress', function (IContainer $c) {
			return Util::getDefaultEmailAddress('no-reply');
		});
		$container->registerService('Logger', function (IContainer $c) {
			return $c->query('ServerContainer')->getLogger();
		});
		$container->registerService('URLGenerator', function (IContainer $c) {
			return $c->query('ServerContainer')->getURLGenerator();
		});
		$container->registerService('ClientService', function (IContainer $c) {
			return $c->query('ServerContainer')->getHTTPClientService();
		});
		$container->registerService('INavigationManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getNavigationManager();
		});
		$container->registerService('IAppManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getAppManager();
		});
		$container->registerService('OcsClient', function (IContainer $c) {
			return $c->query('ServerContainer')->getOcsClient();
		});
		$container->registerService('Util', function (IContainer $c) {
			return new \OC_Util();
		});
		$container->registerService('DatabaseConnection', function (IContainer $c) {
			return $c->query('ServerContainer')->getDatabaseConnection();
		});
		$container->registerService('CertificateManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getCertificateManager();
		});
		$container->registerService('SystemCertificateManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getCertificateManager(null);
		});
		$container->registerService('Checker', function (IContainer $c) {
			/** @var Server $server */
			$server = $c->query('ServerContainer');
			return $server->getIntegrityCodeChecker();
		});
		$container->registerService('TimeFactory', function (IContainer $c) {
			return new TimeFactory();
		});
		$container->registerService('SecureRandom', function (IContainer $c) {
			return $c->query('ServerContainer')->getSecureRandom();
		});
		$container->registerService('SettingsManager', function (IContainer $c) {
			return $c->query('ServerContainer')->getSettingsManager();
		});
	}
}
