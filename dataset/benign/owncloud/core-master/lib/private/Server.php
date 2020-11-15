<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Bernhard Reiter <ockham@raz.or.at>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christopher Schäpers <kondou@ts.unde.re>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Philipp Schaffrath <github@philippschaffrath.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Roeland Jago Douma <rullzer@users.noreply.github.com>
 * @author Sander <brantje@gmail.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Thomas Tanghus <thomas@tanghus.net>
 * @author Tom Needham <tom@owncloud.com>
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
namespace OC;

use bantu\IniGetWrapper\IniGetWrapper;
use OC\AppFramework\Http\Request;
use OC\AppFramework\Db\Db;
use OC\AppFramework\Utility\TimeFactory;
use OC\Authentication\AccountModule\Manager as AccountModuleManager;
use OC\Command\AsyncBus;
use OC\Diagnostics\EventLogger;
use OC\Diagnostics\QueryLogger;
use OC\Files\Config\UserMountCache;
use OC\Files\Config\UserMountCacheListener;
use OC\Files\Mount\CacheMountProvider;
use OC\Files\Mount\PreviewsMountProvider;
use OC\Files\Mount\LocalHomeMountProvider;
use OC\Files\Mount\ObjectHomeMountProvider;
use OC\Files\Node\HookConnector;
use OC\Files\Node\LazyRoot;
use OC\Files\Node\Root;
use OC\Files\View;
use OC\Http\Client\ClientService;
use OC\IntegrityCheck\Checker;
use OC\IntegrityCheck\Helpers\AppLocator;
use OC\IntegrityCheck\Helpers\EnvironmentHelper;
use OC\IntegrityCheck\Helpers\FileAccessHelper;
use OC\License\LicenseManager;
use OC\License\LicenseFetcher;
use OC\License\MessageService;
use OC\Lock\DBLockingProvider;
use OC\Lock\MemcacheLockingProvider;
use OC\Lock\NoopLockingProvider;
use OC\Mail\Mailer;
use OC\Memcache\ArrayCache;
use OC\Notification\Manager;
use OC\Security\CertificateManager;
use OC\Security\CSP\ContentSecurityPolicyManager;
use OC\Security\Crypto;
use OC\Security\CSRF\CsrfTokenGenerator;
use OC\Security\CSRF\CsrfTokenManager;
use OC\Security\CSRF\TokenStorage\SessionStorage;
use OC\Security\Hasher;
use OC\Security\CredentialsManager;
use OC\Security\SecureRandom;
use OC\Security\TrustedDomainHelper;
use OC\Session\CryptoWrapper;
use OC\Session\Memory;
use OC\Settings\Panels\Helper;
use OC\Settings\SettingsManager;
use OC\Shutdown\ShutDownManager;
use OC\Tagging\TagMapper;
use OC\Theme\ThemeService;
use OC\User\AccountMapper;
use OC\User\AccountTermMapper;
use OC\User\Session;
use OC\User\SyncService;
use OCP\App\IServiceLoader;
use OCP\AppFramework\QueryException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Events\EventEmitterTrait;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IServerContainer;
use OCP\ISession;
use OCP\IUser;
use OCP\License\ILicenseManager;
use OCP\Security\IContentSecurityPolicyManager;
use OCP\Shutdown\IShutdownManager;
use OCP\Theme\IThemeService;
use OCP\Util\UserSearch;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OC\Files\External\StoragesBackendService;
use OC\Files\External\Service\UserStoragesService;
use OC\Files\External\Service\UserGlobalStoragesService;
use OC\Files\External\Service\GlobalStoragesService;
use OC\Files\External\Service\DBConfigService;
use OC\Http\Client\WebDavClientService;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Server
 *
 * @package OC
 *
 * TODO: hookup all manager classes
 */
class Server extends ServerContainer implements IServerContainer, IServiceLoader {
	use EventEmitterTrait;
	/** @var string */
	private $webRoot;

	/**
	 * cache the logger to prevent querying it over and over again
	 * @var ILogger;
	 */
	private $logger;

	/**
	 * @param string $webRoot
	 * @param \OC\Config $config
	 */
	public function __construct($webRoot, \OC\Config $config) {
		parent::__construct();
		$this->webRoot = $webRoot;

		$this->registerService('SettingsManager', function (Server $c) {
			return new SettingsManager(
				$c->getL10N('lib'),
				$c->getAppManager(),
				$c->getUserSession(),
				$c->getLogger(),
				$c->getGroupManager(),
				$c->getConfig(),
				new \OCP\Defaults(),
				$c->getURLGenerator(),
				new Helper(),
				$c->getLockingProvider(),
				$c->getDatabaseConnection(),
				$c->getLicenseManager(),
				$c->getCertificateManager(),
				$c->getL10NFactory()

			);
		});

		$this->registerService('ContactsManager', function ($c) {
			return new ContactsManager();
		});

		$this->registerService('PreviewManager', function (Server $c) {
			return new PreviewManager($c->getConfig(),
				$c->getLazyRootFolder(),
				$c->getUserSession());
		});

		$this->registerService('EncryptionManager', function (Server $c) {
			$view = new View();
			$util = new Encryption\Util(
				$view,
				$c->getUserManager(),
				$c->getGroupManager(),
				$c->getConfig()
			);
			return new Encryption\Manager(
				$c->getConfig(),
				$c->getLogger(),
				$c->getL10N('lib'),
				new View(),
				$util,
				new ArrayCache()
			);
		});

		$this->registerService('EncryptionFileHelper', function (Server $c) {
			$util = new Encryption\Util(
				new View(),
				$c->getUserManager(),
				$c->getGroupManager(),
				$c->getConfig()
			);
			return new Encryption\File($util);
		});

		$this->registerService('EncryptionKeyStorage', function (Server $c) {
			$view = new View();
			$util = new Encryption\Util(
				$view,
				$c->getUserManager(),
				$c->getGroupManager(),
				$c->getConfig()
			);

			return new Encryption\Keys\Storage(
				$view,
				$util,
				$c->getUserSession()
			);
		});
		$this->registerService('TagMapper', function (Server $c) {
			return new TagMapper($c->getDatabaseConnection());
		});
		$this->registerService('TagManager', function (Server $c) {
			$tagMapper = $c->query('TagMapper');
			return new TagManager($tagMapper, $c->getUserSession());
		});
		$this->registerService('SystemTagManagerFactory', function (Server $c) {
			$config = $c->getConfig();
			$factoryClass = $config->getSystemValue('systemtags.managerFactory', '\OC\SystemTag\ManagerFactory');
			/** @var \OC\SystemTag\ManagerFactory $factory */
			$factory = new $factoryClass($this);
			return $factory;
		});
		$this->registerService('SystemTagManager', function (Server $c) {
			return $c->query('SystemTagManagerFactory')->getManager();
		});
		$this->registerService('SystemTagObjectMapper', function (Server $c) {
			return $c->query('SystemTagManagerFactory')->getObjectMapper();
		});
		$this->registerService('RootFolder', function () {
			$manager = \OC\Files\Filesystem::getMountManager(null);
			$view = new View();
			$root = new Root($manager, $view, null);
			$connector = new HookConnector($root, $view);
			$connector->viewToNode();
			return $root;
		});
		$this->registerService('LazyRootFolder', function (Server $c) {
			return new LazyRoot(function () use ($c) {
				return $c->getRootFolder();
			});
		});
		$this->registerService('AccountMapper', function (Server $c) {
			return new AccountMapper($c->getConfig(), $c->getDatabaseConnection(), new AccountTermMapper($c->getDatabaseConnection()));
		});
		$this->registerService('UserManager', function (Server $c) {
			return new \OC\User\Manager(
				$c->getConfig(),
				$c->getLogger(),
				$c->getAccountMapper(),
				new SyncService(
					$c->getConfig(),
					$c->getLogger(),
					$c->getAccountMapper()
				),
				new UserSearch(
					$c->getConfig()
				)
			);
		});
		$this->registerService('GroupManager', function (Server $c) {
			$groupManager = new \OC\Group\Manager(
				$this->getUserManager(),
				new UserSearch(
					$c->getConfig()
				),
				$this->getEventDispatcher()
			);

			$groupManager->listen('\OC\Group', 'preCreate', function ($gid) {
				\OC_Hook::emit('OC_Group', 'pre_createGroup', ['run' => true, 'gid' => $gid]);
			});
			$groupManager->listen('\OC\Group', 'postCreate', function (\OC\Group\Group $gid) {
				\OC_Hook::emit('OC_User', 'post_createGroup', ['gid' => $gid->getGID()]);
			});
			$groupManager->listen('\OC\Group', 'preDelete', function (\OC\Group\Group $group) {
				\OC_Hook::emit('OC_Group', 'pre_deleteGroup', ['run' => true, 'gid' => $group->getGID()]);
			});
			$groupManager->listen('\OC\Group', 'postDelete', function (\OC\Group\Group $group) {
				\OC_Hook::emit('OC_User', 'post_deleteGroup', ['gid' => $group->getGID()]);
			});
			$groupManager->listen('\OC\Group', 'preAddUser', function (\OC\Group\Group $group, \OC\User\User $user) {
				\OC_Hook::emit('OC_Group', 'pre_addToGroup', ['run' => true, 'uid' => $user->getUID(), 'gid' => $group->getGID()]);
			});
			$groupManager->listen('\OC\Group', 'postAddUser', function (\OC\Group\Group $group, \OC\User\User $user) {
				\OC_Hook::emit('OC_Group', 'post_addToGroup', ['uid' => $user->getUID(), 'gid' => $group->getGID()]);
				//Minimal fix to keep it backward compatible TODO: clean up all the GroupManager hooks
				\OC_Hook::emit('OC_User', 'post_addToGroup', ['uid' => $user->getUID(), 'gid' => $group->getGID()]);
			});
			return $groupManager;
		});
		$this->registerService('OC\Authentication\Token\DefaultTokenMapper', function (Server $c) {
			$dbConnection = $c->getDatabaseConnection();
			return new Authentication\Token\DefaultTokenMapper($dbConnection);
		});
		$this->registerService('OC\Authentication\Token\DefaultTokenProvider', function (Server $c) {
			$mapper = $c->query('OC\Authentication\Token\DefaultTokenMapper');
			$crypto = $c->getCrypto();
			$config = $c->getConfig();
			$logger = $c->getLogger();
			$timeFactory = new TimeFactory();
			return new \OC\Authentication\Token\DefaultTokenProvider($mapper, $crypto, $config, $logger, $timeFactory);
		});
		$this->registerAlias('OC\Authentication\Token\IProvider', 'OC\Authentication\Token\DefaultTokenProvider');
		$this->registerService('TimeFactory', function () {
			return new TimeFactory();
		});
		$this->registerAlias('OCP\AppFramework\Utility\ITimeFactory', 'TimeFactory');
		$this->registerService('UserSession', function (Server $c) {
			$manager = $c->getUserManager();
			$session = new Memory();
			$timeFactory = new TimeFactory();
			// Token providers might require a working database. This code
			// might however be called when ownCloud is not yet setup.
			if (\OC::$server->getSystemConfig()->getValue('installed', false)) {
				$defaultTokenProvider = $c->query('OC\Authentication\Token\IProvider');
			} else {
				$defaultTokenProvider = null;
			}

			$userSyncService = new SyncService($c->getConfig(), $c->getLogger(), $c->getAccountMapper());

			$userSession = new Session($manager, $session, $timeFactory,
				$defaultTokenProvider, $c->getConfig(), $c->getLogger(), $this, $userSyncService, $c->getEventDispatcher());
			$userSession->listen('\OC\User', 'preCreateUser', function ($uid, $password) {
				\OC_Hook::emit('OC_User', 'pre_createUser', ['run' => true, 'uid' => $uid, 'password' => $password]);
			});
			$userSession->listen('\OC\User', 'postCreateUser', function ($user, $password) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'post_createUser', ['uid' => $user->getUID(), 'password' => $password]);
			});
			$userSession->listen('\OC\User', 'preDelete', function ($user) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'pre_deleteUser', ['run' => true, 'uid' => $user->getUID()]);
			});
			$userSession->listen('\OC\User', 'postDelete', function ($user) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'post_deleteUser', ['uid' => $user->getUID()]);
				$this->emittingCall(function () {
					return true;
				}, ['before' => [], 'after' => ['uid' => $user->getUID()]], 'user', 'delete');
			});
			$userSession->listen('\OC\User', 'preSetPassword', function ($user, $password, $recoveryPassword) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'pre_setPassword', ['run' => true, 'uid' => $user->getUID(), 'password' => $password, 'recoveryPassword' => $recoveryPassword]);
			});
			$userSession->listen('\OC\User', 'postSetPassword', function ($user, $password, $recoveryPassword) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'post_setPassword', ['run' => true, 'uid' => $user->getUID(), 'password' => $password, 'recoveryPassword' => $recoveryPassword]);
			});
			$userSession->listen('\OC\User', 'preLogin', function ($uid, $password) {
				\OC_Hook::emit('OC_User', 'pre_login', ['run' => true, 'uid' => $uid, 'password' => $password]);
			});
			$userSession->listen('\OC\User', 'postLogin', function ($user, $password) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'post_login', ['run' => true, 'uid' => $user->getUID(), 'password' => $password]);
			});
			$userSession->listen('\OC\User', 'preLogout', function () {
				$event = new GenericEvent(null, []);
				\OC::$server->getEventDispatcher()->dispatch($event, '\OC\User\Session::pre_logout');
			});
			$userSession->listen('\OC\User', 'logout', function () {
				\OC_Hook::emit('OC_User', 'logout', []);
			});
			$userSession->listen('\OC\User', 'changeUser', function ($user, $feature, $value) {
				/** @var $user \OC\User\User */
				\OC_Hook::emit('OC_User', 'changeUser', ['run' => true, 'user' => $user, 'feature' => $feature, 'value' => $value]);
				$this->emittingCall(function () {
					return true;
				}, [
					'before' => ['run' => true, 'user' => $user, 'feature' => $feature, 'value' => $value],
					'after' => ['run' => true, 'user' => $user, 'feature' => $feature, 'value' => $value]
				], 'user', 'featurechange');
			});
			return $userSession;
		});

		$this->registerService('\OC\Authentication\TwoFactorAuth\Manager', function (Server $c) {
			return new \OC\Authentication\TwoFactorAuth\Manager($c->getAppManager(), $c->getSession(), $c->getConfig(), $c->getRequest(), $c->getLogger());
		});

		$this->registerService('NavigationManager', function (Server $c) {
			return new \OC\NavigationManager($c->getAppManager(),
				$c->getURLGenerator(),
				$c->getL10NFactory(),
				$c->getUserSession(),
				$c->getGroupManager(),
				$c->getConfig());
		});
		$this->registerAlias(IConfig::class, 'AllConfig');
		$this->registerService('AllConfig', function (Server $c) {
			return new \OC\AllConfig(
				$c->getSystemConfig(),
				$c->getEventDispatcher()
			);
		});
		$this->registerService('SystemConfig', function ($c) use ($config) {
			return new \OC\SystemConfig($config);
		});
		$this->registerService('AppConfig', function (Server $c) {
			return new \OC\AppConfig($c->getDatabaseConnection());
		});
		$this->registerService('L10NFactory', function (Server $c) {
			return new \OC\L10N\Factory(
				$c->getConfig(),
				$c->getRequest(),
				$c->getThemeService(),
				$c->getUserSession(),
				\OC::$SERVERROOT
			);
		});
		$this->registerService('URLGenerator', function (Server $c) {
			$config = $c->getConfig();
			$cacheFactory = $c->getMemCacheFactory();
			$router = $c->getRouter();
			return new URLGenerator(
				$config,
				$cacheFactory,
				$router,
				new \OC\Helper\EnvironmentHelper()
			);
		});
		$this->registerService('AppHelper', function ($c) {
			return new \OC\AppHelper();
		});
		$this->registerService('UserCache', function ($c) {
			return new Cache\File();
		});
		$this->registerService('MemCacheFactory', function (Server $c) {
			$config = $c->getConfig();

			if ($config->getSystemValue('installed', false) && !(\defined('PHPUNIT_RUN') && PHPUNIT_RUN)) {
				$v = \OC_App::getAppVersions();
				$v['core'] = \md5(\file_get_contents(\OC::$SERVERROOT . '/version.php'));
				$version = \implode(',', $v);
				$instanceId = \OC_Util::getInstanceId();
				$path = \OC::$SERVERROOT;
				$prefix = \md5($instanceId . '-' . $version . '-' . $path);
				return new \OC\Memcache\Factory($prefix, $c->getLogger(),
					$config->getSystemValue('memcache.local', null),
					$config->getSystemValue('memcache.distributed', null),
					$config->getSystemValue('memcache.locking', null)
				);
			}

			return new \OC\Memcache\Factory('', $c->getLogger(),
				'\\OC\\Memcache\\ArrayCache',
				'\\OC\\Memcache\\ArrayCache',
				'\\OC\\Memcache\\ArrayCache'
			);
		});
		$this->registerService('RedisFactory', function (Server $c) {
			$systemConfig = $c->getSystemConfig();
			return new RedisFactory($systemConfig);
		});
		$this->registerService('ActivityManager', function (Server $c) {
			return new \OC\Activity\Manager(
				$c->getRequest(),
				$c->getUserSession(),
				$c->getConfig()
			);
		});
		$this->registerService('AvatarManager', function (Server $c) {
			return new AvatarManager(
				$c->getUserManager(),
				$c->getLazyRootFolder(),  // initialize the root folder lazily
				$c->getL10N('lib'),
				$c->getLogger()
			);
		});
		$this->registerAlias(ILogger::class, 'Logger');
		$this->registerService('Logger', function (Server $c) {
			$logClass = $c->query('AllConfig')->getSystemValue('log_type', 'owncloud');
			$logger = 'OC\\Log\\' . \ucfirst($logClass);
			\call_user_func([$logger, 'init']);

			return new Log($logger);
		});
		$this->registerService('JobList', function (Server $c) {
			$config = $c->getConfig();
			return new \OC\BackgroundJob\JobList(
				$c->getDatabaseConnection(),
				$config,
				new TimeFactory(),
				$c->getLogger()
			);
		});
		$this->registerService('Router', function (Server $c) {
			$cacheFactory = $c->getMemCacheFactory();
			$logger = $c->getLogger();
			if ($cacheFactory->isAvailable()) {
				$router = new \OC\Route\CachingRouter($cacheFactory->create('route'), $logger);
			} else {
				$router = new \OC\Route\Router($logger);
			}
			return $router;
		});
		$this->registerService('Search', function ($c) {
			return new Search();
		});
		$this->registerService('SecureRandom', function ($c) {
			return new SecureRandom();
		});
		$this->registerService('Crypto', function (Server $c) {
			return new Crypto($c->getConfig(), $c->getSecureRandom());
		});
		$this->registerAlias('OCP\Security\ICrypto', 'Crypto');
		$this->registerService('Hasher', function (Server $c) {
			return new Hasher($c->getConfig());
		});
		$this->registerService('CredentialsManager', function (Server $c) {
			return new CredentialsManager($c->getCrypto(), $c->getDatabaseConnection());
		});
		$this->registerService('DatabaseConnection', function (Server $c) {
			$systemConfig = $c->getSystemConfig();
			$keys = $systemConfig->getKeys();
			if (!isset($keys['dbname']) && !isset($keys['dbhost']) && isset($keys['dbtableprefix'])) {
				throw new \OC\DatabaseException('No database configured');
			}

			$factory = new \OC\DB\ConnectionFactory($systemConfig);
			$type = $systemConfig->getValue('dbtype', 'sqlite');
			if (!$factory->isValidType($type)) {
				throw new \OC\DatabaseException('Invalid database type');
			}
			$connectionParams = $factory->createConnectionParams();
			$connection = $factory->getConnection($type, $connectionParams);
			$connection->getConfiguration()->setSQLLogger($c->getQueryLogger());
			return $connection;
		});
		$this->registerAlias(IDBConnection::class, 'DatabaseConnection');
		$this->registerService('Db', function (Server $c) {
			return new Db($c->getDatabaseConnection());
		});
		$this->registerService('HTTPHelper', function (Server $c) {
			$config = $c->getConfig();
			return new HTTPHelper(
				$config,
				$c->getHTTPClientService()
			);
		});
		$this->registerService('HttpClientService', function (Server $c) {
			$user = \OC_User::getUser();
			$uid = $user ? $user : null;
			return new ClientService(
				$c->getConfig(),
				new \OC\Security\CertificateManager($uid, new View(), $c->getConfig())
			);
		});
		$this->registerService('WebDavClientService', function (Server $c) {
			$user = \OC_User::getUser();
			$uid = $user ? $user : null;
			return new WebDavClientService(
				$c->getConfig(),
				new \OC\Security\CertificateManager($uid, new View(), $c->getConfig())
			);
		});
		$this->registerService('EventLogger', function (Server $c) {
			$eventLogger = new EventLogger();
			if ($c->getSystemConfig()->getValue('debug', false)) {
				// In debug mode, module is being activated by default
				$eventLogger->activate();
			}
			return $eventLogger;
		});
		$this->registerService('QueryLogger', function (Server $c) {
			$queryLogger = new QueryLogger();
			if ($c->getSystemConfig()->getValue('debug', false)) {
				// In debug mode, module is being activated by default
				$queryLogger->activate();
			}
			return $queryLogger;
		});
		$this->registerService('TempManager', function (Server $c) {
			return new TempManager(
				$c->getLogger(),
				$c->getConfig()
			);
		});
		$this->registerService('AppManager', function (Server $c) {
			if (\OC::$server->getSystemConfig()->getValue('installed', false)) {
				$appConfig = $c->getAppConfig();
				$groupManager = $c->getGroupManager();
			} else {
				$appConfig = null;
				$groupManager = null;
			}
			return new \OC\App\AppManager(
				$c->getUserSession(),
				$appConfig,
				$groupManager,
				$c->getMemCacheFactory(),
				$c->getEventDispatcher(),
				$c->getConfig()
			);
		});
		$this->registerService('DateTimeZone', function (Server $c) {
			return new DateTimeZone(
				$c->getConfig(),
				$c->getSession()
			);
		});
		$this->registerService('DateTimeFormatter', function (Server $c) {
			$language = $c->getConfig()->getUserValue($c->getSession()->get('user_id'), 'core', 'lang', null);

			return new DateTimeFormatter(
				$c->getDateTimeZone()->getTimeZone(),
				$c->getL10N('lib', $language)
			);
		});
		$this->registerService('UserMountCache', function (Server $c) {
			$mountCache = new UserMountCache($c->getDatabaseConnection(), $c->getUserManager(), $c->getLogger());
			$listener = new UserMountCacheListener($mountCache);
			$listener->listen($c->getUserManager());
			return $mountCache;
		});
		$this->registerService('MountConfigManager', function (Server $c) {
			$loader = \OC\Files\Filesystem::getLoader();
			$mountCache = $c->query('UserMountCache');
			$manager =  new \OC\Files\Config\MountProviderCollection($loader, $mountCache);

			// builtin providers

			$config = $c->getConfig();
			$manager->registerProvider(new CacheMountProvider($config));
			$manager->registerProvider(new PreviewsMountProvider($config));
			$manager->registerHomeProvider(new LocalHomeMountProvider());
			$manager->registerHomeProvider(new ObjectHomeMountProvider($config));

			// external storage
			if ($config->getAppValue('core', 'enable_external_storage', 'no') === 'yes') {
				$manager->registerProvider(new \OC\Files\External\ConfigAdapter(
					$c->query('AllConfig'),
					$c->query('UserStoragesService'),
					$c->query('UserGlobalStoragesService'),
					$c->getSession()
				));
			}

			return $manager;
		});
		$this->registerService('IniWrapper', function ($c) {
			return new IniGetWrapper();
		});
		$this->registerService('AsyncCommandBus', function (Server $c) {
			$jobList = $c->getJobList();
			return new AsyncBus($jobList);
		});
		$this->registerService('TrustedDomainHelper', function ($c) {
			return new TrustedDomainHelper($this->getConfig());
		});
		$this->registerService('IntegrityCodeChecker', function (Server $c) {
			// IConfig and IAppManager requires a working database. This code
			// might however be called when ownCloud is not yet setup.
			if (\OC::$server->getSystemConfig()->getValue('installed', false)) {
				$config = $c->getConfig();
				$appManager = $c->getAppManager();
			} else {
				$config = null;
				$appManager = null;
			}

			return new Checker(
					new EnvironmentHelper(),
					new FileAccessHelper(),
					new AppLocator(),
					$config,
					$c->getMemCacheFactory(),
					$appManager,
					$c->getTempManager()
			);
		});
		$this->registerService('Request', function ($c) {
			if (isset($this['urlParams'])) {
				$urlParams = $this['urlParams'];
			} else {
				$urlParams = [];
			}

			if (\defined('PHPUNIT_RUN') && PHPUNIT_RUN
				&& \in_array('fakeinput', \stream_get_wrappers())
			) {
				$stream = 'fakeinput://data';
			} else {
				$stream = 'php://input';
			}

			return new Request(
				[
					'get' => $_GET,
					'post' => $_POST,
					'files' => $_FILES,
					'server' => $_SERVER,
					'env' => $_ENV,
					'cookies' => $_COOKIE,
					'method' => (isset($_SERVER, $_SERVER['REQUEST_METHOD']))
						? $_SERVER['REQUEST_METHOD']
						: null,
					'urlParams' => $urlParams,
				],
				$this->getSecureRandom(),
				$this->getConfig(),
				$this->getCsrfTokenManager(),
				$stream
			);
		});
		$this->registerService('Mailer', function (Server $c) {
			return new Mailer(
				$c->getConfig(),
				$c->getLogger(),
				new \OC_Defaults()
			);
		});
		$this->registerService('LockingProvider', function (Server $c) {
			$ini = $c->getIniWrapper();
			$config = $c->getConfig();
			$ttl = $config->getSystemValue('filelocking.ttl', \max(3600, $ini->getNumeric('max_execution_time')));
			if ($config->getSystemValue('filelocking.enabled', true) or (\defined('PHPUNIT_RUN') && PHPUNIT_RUN)) {
				/** @var \OC\Memcache\Factory $memcacheFactory */
				$memcacheFactory = $c->getMemCacheFactory();
				'@phan-var \OC\Memcache\Factory $memcacheFactory';
				$memcache = $memcacheFactory->createLocking('lock');
				if (!($memcache instanceof \OC\Memcache\NullCache)) {
					return new MemcacheLockingProvider($memcache, $ttl);
				}
				return new DBLockingProvider($c->getDatabaseConnection(), $c->getLogger(), new TimeFactory(), $ttl);
			}
			return new NoopLockingProvider();
		});
		$this->registerAlias('OCP\Lock\ILockingProvider', 'LockingProvider');
		$this->registerService('MountManager', function () {
			return new \OC\Files\Mount\Manager();
		});
		$this->registerService('MimeTypeDetector', function (Server $c) {
			return new \OC\Files\Type\Detection(
				$c->getURLGenerator(),
				\OC::$SERVERROOT . '/config/',
				\OC::$SERVERROOT . '/resources/config/'
			);
		});
		$this->registerService('MimeTypeLoader', function (Server $c) {
			return new \OC\Files\Type\Loader(
				$c->getDatabaseConnection()
			);
		});
		$this->registerAlias('OCP\Files\IMimeTypeLoader', 'MimeTypeLoader');
		$this->registerService('NotificationManager', function (Server $c) {
			return new Manager($c->getEventDispatcher());
		});
		$this->registerService('CapabilitiesManager', function (Server $c) {
			$manager = new \OC\CapabilitiesManager();
			$manager->registerCapability(function () use ($c) {
				return new \OC\OCS\CoreCapabilities($c->getConfig());
			});
			return $manager;
		});
		$this->registerService('CommentsManager', function (Server $c) {
			$config = $c->getConfig();
			$factoryClass = $config->getSystemValue('comments.managerFactory', '\OC\Comments\ManagerFactory');
			/** @var \OCP\Comments\ICommentsManagerFactory $factory */
			$factory = new $factoryClass($this);
			return $factory->getManager();
		});
		$this->registerService('EventDispatcher', function () {
			return new EventDispatcher();
		});
		$this->registerService('CryptoWrapper', function (Server $c) {
			// FIXME: Instantiiated here due to cyclic dependency
			$request = new Request(
				[
					'get' => $_GET,
					'post' => $_POST,
					'files' => $_FILES,
					'server' => $_SERVER,
					'env' => $_ENV,
					'cookies' => $_COOKIE,
					'method' => (isset($_SERVER, $_SERVER['REQUEST_METHOD']))
						? $_SERVER['REQUEST_METHOD']
						: null,
				],
				$c->getSecureRandom(),
				$c->getConfig()
			);

			return new CryptoWrapper(
				$c->getConfig(),
				$c->getCrypto(),
				$c->getSecureRandom(),
				$request
			);
		});
		$this->registerService('CsrfTokenManager', function (Server $c) {
			$tokenGenerator = new CsrfTokenGenerator($c->getSecureRandom());
			$sessionStorage = new SessionStorage($c->getSession());

			return new CsrfTokenManager(
				$tokenGenerator,
				$sessionStorage
			);
		});
		$this->registerService('ContentSecurityPolicyManager', function (Server $c) {
			return new ContentSecurityPolicyManager();
		});
		$this->registerService('StoragesDBConfigService', function (Server $c) {
			return new DBConfigService(
				$c->getDatabaseConnection(),
				$c->getCrypto()
			);
		});
		$this->registerService('StoragesBackendService', function (Server $c) {
			$service = new StoragesBackendService($c->query('AllConfig'));

			// register auth mechanisms provided by core
			$provider = new \OC\Files\External\Auth\CoreAuthMechanismProvider($c, [
				// AuthMechanism::SCHEME_NULL mechanism
				'OC\Files\External\Auth\NullMechanism',

				// AuthMechanism::SCHEME_BUILTIN mechanism
				'OC\Files\External\Auth\Builtin',

				// AuthMechanism::SCHEME_PASSWORD mechanisms
				'OC\Files\External\Auth\Password\Password',
			]);

			$service->registerAuthMechanismProvider($provider);

			// force-load the session one as it will register hooks...
			// TODO: obsolete it and use the TokenProvider to get the user's password from the session
			$sessionCreds = new \OC\Files\External\Auth\Password\SessionCredentials(
				$c->getSession(),
				$c->getCrypto()
			);
			$service->registerAuthMechanism($sessionCreds);

			return $service;
		});
		$this->registerAlias('OCP\Files\External\IStoragesBackendService', 'StoragesBackendService');
		$this->registerService('GlobalStoragesService', function (Server $c) {
			return new GlobalStoragesService(
				$c->query('StoragesBackendService'),
				$c->query('StoragesDBConfigService'),
				$c->query('UserMountCache')
			);
		});
		$this->registerAlias('OCP\Files\External\Service\IGlobalStoragesService', 'GlobalStoragesService');
		$this->registerService('UserGlobalStoragesService', function (Server $c) {
			return new UserGlobalStoragesService(
				$c->query('StoragesBackendService'),
				$c->query('StoragesDBConfigService'),
				$c->query('UserSession'),
				$c->query('GroupManager'),
				$c->query('UserMountCache')
			);
		});
		$this->registerAlias('OCP\Files\External\Service\IUserGlobalStoragesService', 'UserGlobalStoragesService');
		$this->registerService('UserStoragesService', function (Server $c) {
			return new UserStoragesService(
				$c->query('StoragesBackendService'),
				$c->query('StoragesDBConfigService'),
				$c->query('UserSession'),
				$c->query('UserMountCache')
			);
		});
		$this->registerAlias('OCP\Files\External\Service\IUserStoragesService', 'UserStoragesService');
		$this->registerService('ShareManager', function (Server $c) {
			$config = $c->getConfig();
			$factoryClass = $config->getSystemValue('sharing.managerFactory', '\OC\Share20\ProviderFactory');
			/** @var \OCP\Share\IProviderFactory $factory */
			$factory = new $factoryClass($this);

			$manager = new \OC\Share20\Manager(
				$c->getLogger(),
				$c->getConfig(),
				$c->getSecureRandom(),
				$c->getHasher(),
				$c->getMountManager(),
				$c->getGroupManager(),
				$c->getL10N('lib'),
				$factory,
				$c->getUserManager(),
				$c->getLazyRootFolder(),
				$c->getEventDispatcher(),
				new View('/'),
				$c->getDatabaseConnection(),
				$c->getUserSession()
			);

			return $manager;
		});

		$this->registerService('ThemeService', function ($c) {
			return new ThemeService(
				$this->getSystemConfig()->getValue('theme'),
				$c->getAppManager(),
				new \OC\Helper\EnvironmentHelper()
			);
		});
		$this->registerAlias('OCP\Theme\IThemeService', 'ThemeService');
		$this->registerAlias('OCP\IUserSession', 'UserSession');
		$this->registerAlias('OCP\Security\ICrypto', 'Crypto');

		$this->registerService(IServiceLoader::class, function () {
			return $this;
		});

		$this->registerService(ILicenseManager::class, function ($c) {
			return new LicenseManager(
				$c->query(LicenseFetcher::class),
				// can't query for MessageService because there is no implementation
				// registered for the \OCP\L10N\IFactory interface in the server.
				new MessageService(
					$c->getL10NFactory()
				),
				$c->getAppManager(),
				$c->getConfig(),
				$c->getTimeFactory(),
				$c->getLogger()
			);
		});
	}

	/**
	 * @return \OCP\Contacts\IManager
	 */
	public function getContactsManager() {
		return $this->query('ContactsManager');
	}

	/**
	 * @return \OC\Encryption\Manager
	 */
	public function getEncryptionManager() {
		return $this->query('EncryptionManager');
	}

	/**
	 * @return \OC\Encryption\File
	 */
	public function getEncryptionFilesHelper() {
		return $this->query('EncryptionFileHelper');
	}

	/**
	 * @return \OCP\Encryption\Keys\IStorage
	 */
	public function getEncryptionKeyStorage() {
		return $this->query('EncryptionKeyStorage');
	}

	/**
	 * The current request object holding all information about the request
	 * currently being processed is returned from this method.
	 * In case the current execution was not initiated by a web request null is returned
	 *
	 * @return \OCP\IRequest
	 */
	public function getRequest() {
		return $this->query('Request');
	}

	/**
	 * Returns the preview manager which can create preview images for a given file
	 *
	 * @return \OCP\IPreview
	 */
	public function getPreviewManager() {
		return $this->query('PreviewManager');
	}

	/**
	 * Returns the tag manager which can get and set tags for different object types
	 *
	 * @see \OCP\ITagManager::load()
	 * @return \OCP\ITagManager
	 */
	public function getTagManager() {
		return $this->query('TagManager');
	}

	/**
	 * Returns the system-tag manager
	 *
	 * @return \OCP\SystemTag\ISystemTagManager
	 *
	 * @since 9.0.0
	 */
	public function getSystemTagManager() {
		return $this->query('SystemTagManager');
	}

	/**
	 * Returns the system-tag object mapper
	 *
	 * @return \OCP\SystemTag\ISystemTagObjectMapper
	 *
	 * @since 9.0.0
	 */
	public function getSystemTagObjectMapper() {
		return $this->query('SystemTagObjectMapper');
	}

	/**
	 * Returns the avatar manager, used for avatar functionality
	 *
	 * @return \OCP\IAvatarManager
	 */
	public function getAvatarManager() {
		return $this->query('AvatarManager');
	}

	/**
	 * Returns the root folder of ownCloud's data directory
	 *
	 * @return \OCP\Files\IRootFolder
	 */
	public function getRootFolder() {
		return $this->query('RootFolder');
	}

	/**
	 * Returns the root folder of ownCloud's data directory
	 * This is the lazy variant so this gets only initialized once it
	 * is actually used.
	 *
	 * @return \OCP\Files\IRootFolder
	 */
	public function getLazyRootFolder() {
		return $this->query('LazyRootFolder');
	}

	/**
	 * Returns a view to ownCloud's files folder
	 *
	 * @param string $userId user ID
	 * @return \OCP\Files\Folder|null
	 */
	public function getUserFolder($userId = null) {
		if ($userId === null) {
			$user = $this->getUserSession()->getUser();
			if (!$user) {
				return null;
			}
			$userId = $user->getUID();
		}
		$root = $this->getRootFolder();
		return $root->getUserFolder($userId);
	}

	/**
	 * Returns an app-specific view in ownClouds data directory
	 *
	 * @return \OCP\Files\Folder
	 */
	public function getAppFolder() {
		$dir = '/' . \OC_App::getCurrentApp();
		$root = $this->getRootFolder();
		if (!$root->nodeExists($dir)) {
			$folder = $root->newFolder($dir);
		} else {
			$folder = $root->get($dir);
		}
		return $folder;
	}

	/**
	 * @return \OC\User\Manager
	 */
	public function getUserManager() {
		return $this->query('UserManager');
	}

	/**
	 * @return \OC\User\AccountMapper
	 */
	public function getAccountMapper() {
		return $this->query('AccountMapper');
	}

	/**
	 * @return \OC\Group\Manager
	 */
	public function getGroupManager() {
		return $this->query('GroupManager');
	}

	/**
	 * @return \OC\User\Session
	 */
	public function getUserSession() {
		if ($this->getConfig()->getSystemValue('installed', false) === false) {
			return null;
		}
		return $this->query('UserSession');
	}

	/**
	 * @return ISession
	 */
	public function getSession() {
		$userSession = $this->getUserSession();
		if ($userSession === null) {
			return new Memory();
		}
		return $userSession->getSession();
	}

	/**
	 * @param ISession $session
	 */
	public function setSession(ISession $session) {
		$userSession = $this->getUserSession();
		if ($userSession === null) {
			return;
		}
		$userSession->setSession($session);
	}

	/**
	 * @return \OC\Authentication\TwoFactorAuth\Manager
	 */
	public function getTwoFactorAuthManager() {
		return $this->query('\OC\Authentication\TwoFactorAuth\Manager');
	}

	/**
	 * @return \OC\Authentication\AccountModule\Manager
	 */
	public function getAccountModuleManager() {
		return $this->query(AccountModuleManager::class);
	}

	/**
	 * @return \OC\NavigationManager
	 */
	public function getNavigationManager() {
		return $this->query('NavigationManager');
	}

	/**
	 * @return \OCP\IConfig
	 */
	public function getConfig() {
		return $this->query('AllConfig');
	}

	/**
	 * @internal For internal use only
	 * @return \OC\SystemConfig
	 */
	public function getSystemConfig() {
		return $this->query('SystemConfig');
	}

	/**
	 * Returns the app config manager
	 *
	 * @return \OCP\IAppConfig
	 */
	public function getAppConfig() {
		return $this->query('AppConfig');
	}

	/**
	 * @return \OCP\L10N\IFactory
	 */
	public function getL10NFactory() {
		return $this->query('L10NFactory');
	}

	/**
	 * get an L10N instance
	 *
	 * @param string $app appid
	 * @param string $lang
	 * @return IL10N
	 */
	public function getL10N($app, $lang = null) {
		return $this->getL10NFactory()->get($app, $lang);
	}

	/**
	 * @return \OCP\IURLGenerator
	 */
	public function getURLGenerator() {
		return $this->query('URLGenerator');
	}

	/**
	 * @return \OCP\IHelper
	 */
	public function getHelper() {
		return $this->query('AppHelper');
	}

	/**
	 * Returns an ICache instance. Since 8.1.0 it returns a fake cache. Use
	 * getMemCacheFactory() instead.
	 *
	 * @return \OCP\ICache
	 * @deprecated 8.1.0 use getMemCacheFactory to obtain a proper cache
	 */
	public function getCache() {
		return $this->query('UserCache');
	}

	/**
	 * Returns an \OCP\CacheFactory instance
	 *
	 * @return \OCP\ICacheFactory
	 */
	public function getMemCacheFactory() {
		return $this->query('MemCacheFactory');
	}

	/**
	 * Returns an \OC\RedisFactory instance
	 *
	 * @return \OC\RedisFactory
	 */
	public function getGetRedisFactory() {
		return $this->query('RedisFactory');
	}

	/**
	 * Returns the current session
	 *
	 * @return \OCP\IDBConnection
	 */
	public function getDatabaseConnection() {
		return $this->query('DatabaseConnection');
	}

	/**
	 * Returns the activity manager
	 *
	 * @return \OCP\Activity\IManager
	 */
	public function getActivityManager() {
		return $this->query('ActivityManager');
	}

	/**
	 * Returns an job list for controlling background jobs
	 *
	 * @return \OCP\BackgroundJob\IJobList
	 */
	public function getJobList() {
		return $this->query('JobList');
	}

	/**
	 * Returns a logger instance
	 *
	 * @return \OCP\ILogger
	 */
	public function getLogger() {
		if ($this->logger === null) {
			$this->logger = $this->query('Logger');
		}
		return $this->logger;
	}

	/**
	 * Returns a logger instance
	 *
	 * @return \OCP\Settings\ISettingsManager
	 */
	public function getSettingsManager() {
		return $this->query('SettingsManager');
	}

	/**
	 * Returns a router for generating and matching urls
	 *
	 * @return \OCP\Route\IRouter
	 */
	public function getRouter() {
		return $this->query('Router');
	}

	/**
	 * Returns a search instance
	 *
	 * @return \OCP\ISearch
	 */
	public function getSearch() {
		return $this->query('Search');
	}

	/**
	 * Returns a SecureRandom instance
	 *
	 * @return \OCP\Security\ISecureRandom
	 */
	public function getSecureRandom() {
		return $this->query('SecureRandom');
	}

	/**
	 * Returns a Crypto instance
	 *
	 * @return \OCP\Security\ICrypto
	 */
	public function getCrypto() {
		return $this->query('Crypto');
	}

	/**
	 * Returns a Hasher instance
	 *
	 * @return \OCP\Security\IHasher
	 */
	public function getHasher() {
		return $this->query('Hasher');
	}

	/**
	 * Returns a CredentialsManager instance
	 *
	 * @return \OCP\Security\ICredentialsManager
	 */
	public function getCredentialsManager() {
		return $this->query('CredentialsManager');
	}

	/**
	 * Returns an instance of the db facade
	 *
	 * @deprecated use getDatabaseConnection, will be removed in ownCloud 10
	 * @return \OCP\IDb
	 */
	public function getDb() {
		return $this->query('Db');
	}

	/**
	 * Returns an instance of the HTTP helper class
	 *
	 * @deprecated Use getHTTPClientService()
	 * @return \OC\HTTPHelper
	 */
	public function getHTTPHelper() {
		return $this->query('HTTPHelper');
	}

	/**
	 * Get the certificate manager for the user
	 *
	 * @param string $userId (optional) if not specified the current loggedin user is used, use null to get the system certificate manager
	 * @return \OCP\ICertificateManager | null if $uid is null and no user is logged in
	 */
	public function getCertificateManager($userId = '') {
		if ($userId === '') {
			$userSession = $this->getUserSession();
			$user = $userSession->getUser();
			if ($user === null) {
				return null;
			}
			$userId = $user->getUID();
		}
		return new CertificateManager($userId, new View(), $this->getConfig());
	}

	/**
	 * Returns an instance of the HTTP client service
	 *
	 * @return \OCP\Http\Client\IClientService
	 */
	public function getHTTPClientService() {
		return $this->query('HttpClientService');
	}

	/**
	 * Returns an instance of the Webdav client service
	 *
	 * @return \OCP\Http\Client\IWebDavClientService
	 */
	public function getWebDavClientService() {
		return $this->query('WebDavClientService');
	}

	/**
	 * Create a new event source
	 *
	 * @return \OCP\IEventSource
	 */
	public function createEventSource() {
		return new \OC_EventSource();
	}

	/**
	 * Get the active event logger
	 *
	 * The returned logger only logs data when debug mode is enabled
	 *
	 * @return \OCP\Diagnostics\IEventLogger
	 */
	public function getEventLogger() {
		return $this->query('EventLogger');
	}

	/**
	 * Get the active query logger
	 *
	 * The returned logger only logs data when debug mode is enabled
	 *
	 * @return \OCP\Diagnostics\IQueryLogger
	 */
	public function getQueryLogger() {
		return $this->query('QueryLogger');
	}

	/**
	 * Get the manager for temporary files and folders
	 *
	 * @return \OCP\ITempManager
	 */
	public function getTempManager() {
		return $this->query('TempManager');
	}

	/**
	 * Get the app manager
	 *
	 * @return \OCP\App\IAppManager
	 */
	public function getAppManager() {
		return $this->query('AppManager');
	}

	/**
	 * Creates a new mailer
	 *
	 * @return \OCP\Mail\IMailer
	 */
	public function getMailer() {
		return $this->query('Mailer');
	}

	/**
	 * Get the webroot
	 *
	 * @return string
	 */
	public function getWebRoot() {
		return $this->webRoot;
	}

	/**
	 * @return \OCP\IDateTimeZone
	 */
	public function getDateTimeZone() {
		return $this->query('DateTimeZone');
	}

	/**
	 * @return \OCP\IDateTimeFormatter
	 */
	public function getDateTimeFormatter() {
		return $this->query('DateTimeFormatter');
	}

	/**
	 * @return \OCP\Files\Config\IMountProviderCollection
	 */
	public function getMountProviderCollection() {
		return $this->query('MountConfigManager');
	}

	/**
	 * Get the IniWrapper
	 *
	 * @return IniGetWrapper
	 */
	public function getIniWrapper() {
		return $this->query('IniWrapper');
	}

	/**
	 * @return \OCP\Command\IBus
	 */
	public function getCommandBus() {
		return $this->query('AsyncCommandBus');
	}

	/**
	 * Get the trusted domain helper
	 *
	 * @return TrustedDomainHelper
	 */
	public function getTrustedDomainHelper() {
		return $this->query('TrustedDomainHelper');
	}

	/**
	 * Get the locking provider
	 *
	 * @return \OCP\Lock\ILockingProvider
	 * @since 8.1.0
	 */
	public function getLockingProvider() {
		return $this->query('LockingProvider');
	}

	/**
	 * @return \OCP\Files\Mount\IMountManager
	 **/
	public function getMountManager() {
		return $this->query('MountManager');
	}

	/**
	 * Get the MimeTypeDetector
	 *
	 * @return \OCP\Files\IMimeTypeDetector
	 */
	public function getMimeTypeDetector() {
		return $this->query('MimeTypeDetector');
	}

	/**
	 * Get the MimeTypeLoader
	 *
	 * @return \OCP\Files\IMimeTypeLoader
	 */
	public function getMimeTypeLoader() {
		return $this->query('MimeTypeLoader');
	}

	/**
	 * Get the manager of all the capabilities
	 *
	 * @return \OC\CapabilitiesManager
	 */
	public function getCapabilitiesManager() {
		return $this->query('CapabilitiesManager');
	}

	/**
	 * Get the EventDispatcher
	 *
	 * @return EventDispatcherInterface
	 * @since 8.2.0
	 */
	public function getEventDispatcher() {
		return $this->query('EventDispatcher');
	}

	/**
	 * Get the Notification Manager
	 *
	 * @return \OCP\Notification\IManager
	 * @since 8.2.0
	 */
	public function getNotificationManager() {
		return $this->query('NotificationManager');
	}

	/**
	 * @return \OCP\Comments\ICommentsManager
	 */
	public function getCommentsManager() {
		return $this->query('CommentsManager');
	}

	/**
	 * @return \OC\IntegrityCheck\Checker
	 */
	public function getIntegrityCodeChecker() {
		return $this->query('IntegrityCodeChecker');
	}

	/**
	 * @return \OC\Session\CryptoWrapper
	 */
	public function getSessionCryptoWrapper() {
		return $this->query('CryptoWrapper');
	}

	/**
	 * @return CsrfTokenManager
	 */
	public function getCsrfTokenManager() {
		return $this->query('CsrfTokenManager');
	}

	/**
	 * @return IContentSecurityPolicyManager
	 */
	public function getContentSecurityPolicyManager() {
		return $this->query('ContentSecurityPolicyManager');
	}

	/**
	 * @return \OCP\Files\External\IStoragesBackendService
	 */
	public function getStoragesBackendService() {
		return $this->query('StoragesBackendService');
	}

	/**
	 * @return \OCP\Files\External\Service\IGlobalStoragesService
	 */
	public function getGlobalStoragesService() {
		return $this->query('GlobalStoragesService');
	}

	/**
	 * @return \OCP\Files\External\Service\IUserGlobalStoragesService
	 */
	public function getUserGlobalStoragesService() {
		return $this->query('UserGlobalStoragesService');
	}

	/**
	 * @return \OCP\Files\External\Service\IUserStoragesService
	 */
	public function getUserStoragesService() {
		return $this->query('UserStoragesService');
	}

	/**
	 * @return \OCP\Share\IManager
	 */
	public function getShareManager() {
		return $this->query('ShareManager');
	}

	/**
	 * @return IThemeService
	 */
	public function getThemeService() {
		return $this->query('\OCP\Theme\IThemeService');
	}

	/**
	 * @return ITimeFactory
	 */
	public function getTimeFactory() {
		return $this->query('\OCP\AppFramework\Utility\ITimeFactory');
	}

	/**
	 * @return IServiceLoader
	 */
	public function getServiceLoader() {
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function load(array $xmlPath, IUser $user = null) {
		$appManager = $this->getAppManager();
		$allApps = $appManager->getEnabledAppsForUser($user);

		foreach ($allApps as $appId) {
			$info = $appManager->getAppInfo($appId);
			if ($info === null) {
				$info = [];
			}

			foreach ($xmlPath as $xml) {
				$info = isset($info[$xml]) ? $info[$xml] : [];
			}
			if (!\is_array($info)) {
				$info = [$info];
			}

			foreach ($info as $class) {
				try {
					if (!\OC_App::isAppLoaded($appId)) {
						\OC_App::loadApp($appId);
					}

					yield $this->query($class);
				} catch (QueryException $exc) {
					throw new \Exception("Could not load service $class");
				}
			}
		}
	}

	/**
	 * @return IShutdownManager
	 * @throws QueryException
	 * @since 10.0.10
	 */
	public function getShutdownHandler() {
		return $this->query(ShutDownManager::class);
	}

	public function getLicenseManager() {
		return $this->query(ILicenseManager::class);
	}
}
