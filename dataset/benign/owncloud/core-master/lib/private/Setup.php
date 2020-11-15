<?php
/**
 * @author Administrator <Administrator@WINDOWS-2012>
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Brice Maron <brice@bmaron.net>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author François Kubler <francois@kubler.org>
 * @author Jakob Sack <mail@jakobsack.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Martin Mattel <martin.mattel@diemattels.at>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Sean Comeau <sean@ftlnetworks.ca>
 * @author Serge Martin <edb@sigluy.net>
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

namespace OC;

use bantu\IniGetWrapper\IniGetWrapper;
use Exception;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use OCP\Security\ISecureRandom;

class Setup {
	/** @var \OCP\IConfig */
	protected $config;
	/** @var IniGetWrapper */
	protected $iniWrapper;
	/** @var IL10N */
	protected $l10n;
	/** @var \OC_Defaults */
	protected $defaults;
	/** @var ILogger */
	protected $logger;
	/** @var ISecureRandom */
	protected $random;

	/**
	 * @param IConfig $config
	 * @param IniGetWrapper $iniWrapper
	 * @param \OC_Defaults $defaults
	 * @param ILogger $logger
	 * @param ISecureRandom $random
	 */
	public function __construct(IConfig $config,
						 IniGetWrapper $iniWrapper,
						 IL10N $l10n,
						 \OC_Defaults $defaults,
						 ILogger $logger,
						 ISecureRandom $random
		) {
		$this->config = $config;
		$this->iniWrapper = $iniWrapper;
		$this->l10n = $l10n;
		$this->defaults = $defaults;
		$this->logger = $logger;
		$this->random = $random;
	}

	public static $dbSetupClasses = [
		'mysql' => \OC\Setup\MySQL::class,
		'pgsql' => \OC\Setup\PostgreSQL::class,
		'oci'   => \OC\Setup\OCI::class,
		'sqlite' => \OC\Setup\Sqlite::class,
		'sqlite3' => \OC\Setup\Sqlite::class,
	];

	/**
	 * Wrapper around the "class_exists" PHP function to be able to mock it
	 * @param string $name
	 * @return bool
	 */
	protected function IsClassExisting($name) {
		return \class_exists($name);
	}

	/**
	 * Wrapper around the "is_callable" PHP function to be able to mock it
	 * @param string $name
	 * @return bool
	 */
	protected function is_callable($name) {
		return \is_callable($name);
	}

	/**
	 * Wrapper around \PDO::getAvailableDrivers
	 *
	 * @return array
	 */
	protected function getAvailableDbDriversForPdo() {
		return \PDO::getAvailableDrivers();
	}

	/**
	 * Get the available and supported databases of this instance
	 *
	 * @param bool $allowAllDatabases
	 * @return array
	 * @throws Exception
	 */
	public function getSupportedDatabases($allowAllDatabases = false) {
		$availableDatabases = [
			'sqlite' =>  [
				'type' => 'class',
				'call' => 'SQLite3',
				'name' => 'SQLite'
			],
			'mysql' => [
				'type' => 'pdo',
				'call' => 'mysql',
				'name' => 'MySQL/MariaDB'
			],
			'pgsql' => [
				'type' => 'function',
				'call' => 'pg_connect',
				'name' => 'PostgreSQL'
			],
			'oci' => [
				'type' => 'function',
				'call' => 'oci_connect',
				'name' => 'Oracle'
			]
		];
		if ($allowAllDatabases) {
			$configuredDatabases = \array_keys($availableDatabases);
		} else {
			$configuredDatabases = $this->config->getSystemValue('supportedDatabases',
				['sqlite', 'mysql', 'pgsql']);
		}
		if (!\is_array($configuredDatabases)) {
			throw new Exception('Supported databases are not properly configured.');
		}

		$supportedDatabases = [];

		foreach ($configuredDatabases as $database) {
			if (\array_key_exists($database, $availableDatabases)) {
				$working = false;
				$type = $availableDatabases[$database]['type'];
				$call = $availableDatabases[$database]['call'];

				if ($type === 'class') {
					$working = $this->IsClassExisting($call);
				} elseif ($type === 'function') {
					$working = $this->is_callable($call);
				} elseif ($type === 'pdo') {
					$working = \in_array($call, $this->getAvailableDbDriversForPdo(), true);
				}
				if ($working) {
					$supportedDatabases[$database] = $availableDatabases[$database]['name'];
				}
			}
		}

		return $supportedDatabases;
	}

	/**
	 * Gathers system information like database type and does
	 * a few system checks.
	 *
	 * @return array of system info, including an "errors" value
	 * in case of errors/warnings
	 */
	public function getSystemInfo($allowAllDatabases = false) {
		$databases = $this->getSupportedDatabases($allowAllDatabases);

		$dataDir = $this->config->getSystemValue('datadirectory', \OC::$SERVERROOT.'/data');

		$errors = [];

		// Create data directory to test whether the .htaccess works
		// Notice that this is not necessarily the same data directory as the one
		// that will effectively be used.
		if (!\file_exists($dataDir)) {
			@\mkdir($dataDir);
		}
		if (\is_dir($dataDir) && \is_writable($dataDir)) {
			// Protect data directory here, so we can test if the protection is working
			\OC\Setup::protectDataDirectory();
		}

		if (!\OC_Util::runningOn('linux')) {
			if (\OC_Util::runningOn('mac')) {
				$os = 'Mac OS X';
			} else {
				$os = PHP_OS;
			}

			$errors[] = [
				'error' => $this->l10n->t(
					'%s is not supported and %s will not work properly on this platform. ' .
					'Use it at your own risk! ',
					[$os, $this->defaults->getName()]
				),
				'hint' => $this->l10n->t('For the best results, please consider using a GNU/Linux server instead.')
			];
		}

		if ($this->iniWrapper->getString('open_basedir') !== '' && PHP_INT_SIZE === 4) {
			$errors[] = [
				'error' => $this->l10n->t(
					'It seems that this %s instance is running on a 32-bit PHP environment and the open_basedir has been configured in php.ini. ' .
					'This will lead to problems with files over 4 GB and is highly discouraged.',
					$this->defaults->getName()
				),
				'hint' => $this->l10n->t('Please remove the open_basedir setting within your php.ini or switch to 64-bit PHP.')
			];
		}

		return [
			'hasSQLite' => isset($databases['sqlite']),
			'hasMySQL' => isset($databases['mysql']),
			'hasPostgreSQL' => isset($databases['pgsql']),
			'hasOracle' => isset($databases['oci']),
			'databases' => $databases,
			'directory' => $dataDir,
			'errors' => $errors,
		];
	}

	/**
	 * @param $options
	 * @return array
	 */
	public function install($options) {
		$l = $this->l10n;

		$error = [];
		$dbType = $options['dbtype'];

		if (empty($options['adminlogin'])) {
			$error[] = $l->t('Set an admin username.');
		}
		if (empty($options['adminpass'])) {
			$error[] = $l->t('Set an admin password.');
		}
		if (empty($options['directory'])) {
			$options['directory'] = \OC::$SERVERROOT."/data";
		}

		if (!isset(self::$dbSetupClasses[$dbType])) {
			$dbType = 'sqlite';
		}

		$username = \htmlspecialchars_decode($options['adminlogin']);
		$password = \htmlspecialchars_decode($options['adminpass']);
		$dataDir = \htmlspecialchars_decode($options['directory']);

		$class = self::$dbSetupClasses[$dbType];
		/** @var \OC\Setup\AbstractDatabase $dbSetup */
		$dbSetup = new $class($l, 'db_structure.xml', $this->config,
			$this->logger, $this->random);
		$error = \array_merge($error, $dbSetup->validate($options));

		// validate the data directory
		if (
			(!\is_dir($dataDir) and !\mkdir($dataDir)) or
			!\is_writable($dataDir)
		) {
			$error[] = $l->t("Can't create or write into the data directory %s", [$dataDir]);
		}

		// create the apps-external directory only during installation
		$appsExternalDir = \OC::$SERVERROOT.'/apps-external';
		if (!\file_exists($appsExternalDir)) {
			@\mkdir($appsExternalDir);
		}

		// validate the apps-external directory
		if (
			(!\is_dir($appsExternalDir) and !\mkdir($appsExternalDir)) or
			!\is_writable($appsExternalDir)
		) {
			$htmlAppsExternalDir = \htmlspecialchars_decode($appsExternalDir);
			$error[] = $l->t("Can't create or write into the apps-external directory %s", $htmlAppsExternalDir);
		}

		if (\count($error) != 0) {
			return $error;
		}

		$request = \OC::$server->getRequest();

		//no errors, good
		if (isset($options['trusted_domains'])
			&& \is_array($options['trusted_domains'])) {
			$trustedDomains = $options['trusted_domains'];
		} else {
			$trustedDomains = [$request->getInsecureServerHost()];
		}

		//use sqlite3 when available, otherwise sqlite2 will be used.
		if ($dbType=='sqlite' and $this->IsClassExisting('SQLite3')) {
			$dbType='sqlite3';
		}

		//generate a random salt that is used to salt the local user passwords
		$salt = $this->random->generate(30);
		// generate a secret
		$secret = $this->random->generate(48);

		//write the config file
		$this->config->setSystemValues([
			'passwordsalt'		=> $salt,
			'secret'			=> $secret,
			'trusted_domains'	=> $trustedDomains,
			'datadirectory'		=> $dataDir,
			'overwrite.cli.url'	=> $request->getServerProtocol() . '://' . $request->getInsecureServerHost() . \OC::$WEBROOT,
			'dbtype'			=> $dbType,
			'version'			=> \implode('.', \OCP\Util::getVersion()),
		]);

		try {
			$dbSetup->initialize($options);
			$dbSetup->setupDatabase($username);
			// apply necessary migrations
			$dbSetup->runMigrations();
		} catch (\OC\DatabaseSetupException $e) {
			$error[] = [
				'error' => $e->getMessage(),
				'hint' => $e->getHint()
			];
			return($error);
		} catch (Exception $e) {
			$error[] = [
				'error' => 'Error while trying to create admin user: ' . $e->getMessage(),
				'hint' => ''
			];
			return($error);
		}

		//create the user and group
		$user =  null;
		try {
			\OC::$server->getUserManager()->registerBackend(new \OC\User\Database());
			$user = \OC::$server->getUserManager()->createUser($username, $password);
			if (!$user) {
				$error[] = "User <$username> could not be created.";
			}
		} catch (Exception $exception) {
			$error[] = $exception->getMessage();
		}

		if (\count($error) == 0) {
			$config = \OC::$server->getConfig();
			$config->setAppValue('core', 'installedat', \microtime(true));
			$config->setAppValue('core', 'lastupdatedat', \microtime(true));

			\OC::$server->getGroupManager()->addBackend(new \OC\Group\Database());

			$group =\OC::$server->getGroupManager()->createGroup('admin');
			$group->addUser($user);

			//guess what this does
			Installer::installShippedApps();

			// create empty file in data dir, so we can later find
			// out that this is indeed an ownCloud data directory
			\file_put_contents($config->getSystemValue('datadirectory', \OC::$SERVERROOT.'/data').'/.ocdata', '');

			// check if we can write .htaccess
			if (\is_file(self::pathToHtaccess())
				&& \is_writable(self::pathToHtaccess())
			) {
				// Update .htaccess files
				Setup::updateHtaccess();
			}
			Setup::protectDataDirectory();

			//try to write logtimezone
			if (\date_default_timezone_get()) {
				$config->setSystemValue('logtimezone', \date_default_timezone_get());
			}

			// add the apps-external directory to config using apps_path
			// add the key only if it does not exist (protect against overwriting)
			$apps2Key = \OC::$server->getSystemConfig()->getValue('apps_paths', false);

			if ($apps2Key === false) {
				$defaultAppsPaths = [
					'apps_paths' => [
						[
							"path" => \OC::$SERVERROOT . '/apps',
							"url" => "/apps",
							"writable" => false
						],
						[
							"path" => \OC::$SERVERROOT . '/apps-external',
							"url" => "/apps-external",
							"writable" => true
						]
					]
				];

				$config->setSystemValues($defaultAppsPaths);
			}

			self::installBackgroundJobs();

			// save the origin version that we installed at
			$config->setAppValue('core', 'first_install_version', \implode('.', \OCP\Util::getVersion()));

			//and we are done
			$config->setSystemValue('installed', true);

			// finished initial setup
		}

		return $error;
	}

	public static function installBackgroundJobs() {
		\OC::$server->getJobList()->add('\OC\Authentication\Token\DefaultTokenCleanupJob');
	}

	/**
	 * @return string Absolute path to htaccess
	 */
	public static function pathToHtaccess() {
		return \OC::$SERVERROOT.'/.htaccess';
	}

	/**
	 * Append the correct ErrorDocument path for Apache hosts
	 */
	public static function updateHtaccess() {
		$config = \OC::$server->getConfig();
		$il10n = \OC::$server->getL10N('lib');

		// For CLI read the value from overwrite.cli.url
		if (\OC::$CLI) {
			$webRoot = $config->getSystemValue('overwrite.cli.url', '');
			if ($webRoot === '') {
				return;
			}
			$webRoot = \parse_url($webRoot, PHP_URL_PATH);
			$webRoot = \rtrim($webRoot, '/');
		} else {
			$webRoot = !empty(\OC::$WEBROOT) ? \OC::$WEBROOT : '/';
		}

		$htaccessPath = self::pathToHtaccess();
		if (\is_dir($htaccessPath)) {
			throw new \Exception(
				$il10n->t("Can't update %s - it is a directory", [$htaccessPath])
			);
		}
		$htaccessContent = \file_get_contents($htaccessPath);
		$content = "#### DO NOT CHANGE ANYTHING ABOVE THIS LINE ####\n";
		$htaccessContent = \explode($content, $htaccessContent, 2)[0];

		//custom 403 error page
		$content.= "\nErrorDocument 403 ".$webRoot."/core/templates/403.php";

		//custom 404 error page
		$content.= "\nErrorDocument 404 ".$webRoot."/core/templates/404.php";

		// Add rewrite rules if the RewriteBase is configured
		$rewriteBase = $config->getSystemValue('htaccess.RewriteBase', '');
		if ($rewriteBase !== '') {
			$content .= "\n<IfModule mod_rewrite.c>";
			$content .= "\n  Options -MultiViews";
			$content .= "\n  RewriteRule ^favicon.ico$ core/img/favicon.ico [L]";
			$content .= "\n  RewriteRule ^core/js/oc.js$ index.php [PT,E=PATH_INFO:$1]";
			$content .= "\n  RewriteRule ^core/preview.png$ index.php [PT,E=PATH_INFO:$1]";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !\\.(css|js|svg|gif|png|html|ttf|woff|ico|jpg|jpeg|json)$";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !core/img/favicon.ico$";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/robots.txt";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/remote.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/public.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/cron.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/core/ajax/update.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/status.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/ocs/v1.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/ocs/v2.php";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/updater/";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/ocs-provider/";
			$content .= "\n  RewriteCond %{REQUEST_FILENAME} !/ocm-provider/";
			$content .= "\n  RewriteCond %{REQUEST_URI} !^/.well-known/(acme-challenge|pki-validation)/.*";
			$content .= "\n  RewriteRule . index.php [PT,E=PATH_INFO:$1]";
			$content .= "\n  RewriteBase " . $rewriteBase;
			$content .= "\n  <IfModule mod_env.c>";
			$content .= "\n    SetEnv front_controller_active true";
			$content .= "\n    <IfModule mod_dir.c>";
			$content .= "\n      DirectorySlash off";
			$content .= "\n    </IfModule>";
			$content .= "\n  </IfModule>";
			$content .= "\n</IfModule>";
		}

		if ($content !== '') {
			$fileWriteResult = @\file_put_contents(
				$htaccessPath, $htaccessContent . $content . "\n"
			);
			if ($fileWriteResult === false) {
				throw new \Exception(
					$il10n->t("Can't update %s", [$htaccessPath])
				);
			}
		}
	}

	public static function protectDataDirectory() {
		//Require all denied
		$now =  \date('Y-m-d H:i:s');
		$content = "# Generated by ownCloud on $now\n";
		$content.= "# line below if for Apache 2.4\n";
		$content.= "<ifModule mod_authz_core.c>\n";
		$content.= "Require all denied\n";
		$content.= "</ifModule>\n\n";
		$content.= "# line below if for Apache 2.2\n";
		$content.= "<ifModule !mod_authz_core.c>\n";
		$content.= "deny from all\n";
		$content.= "Satisfy All\n";
		$content.= "</ifModule>\n\n";
		$content.= "# section for Apache 2.2 and 2.4\n";
		$content.= "<ifModule mod_autoindex.c>\n";
		$content.= "IndexIgnore *\n";
		$content.= "</ifModule>\n";

		$baseDir = \OC::$server->getConfig()->getSystemValue('datadirectory', \OC::$SERVERROOT . '/data');
		\file_put_contents($baseDir . '/.htaccess', $content);
		\file_put_contents($baseDir . '/index.html', '');
	}
}
