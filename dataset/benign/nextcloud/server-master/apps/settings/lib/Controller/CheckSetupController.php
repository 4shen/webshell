<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Cthulhux <git@tuxproject.de>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author Derek <derek.kelly27@gmail.com>
 * @author Georg Ehrke <oc.list@georgehrke.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Ko- <k.stoffelen@cs.ru.nl>
 * @author Lauris Binde <laurisb@users.noreply.github.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Weimann <mail@michael-weimann.eu>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Sven Strickroth <email@cs-ware.de>
 * @author Sylvia van Os <sylvia@hackerchick.me>
 * @author Timo Förster <tfoerster@webfoersterei.de>
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

namespace OCA\Settings\Controller;

use bantu\IniGetWrapper\IniGetWrapper;
use DirectoryIterator;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;
use GuzzleHttp\Exception\ClientException;
use OC;
use OC\AppFramework\Http;
use OC\DB\Connection;
use OC\DB\MissingColumnInformation;
use OC\DB\MissingIndexInformation;
use OC\DB\SchemaWrapper;
use OC\IntegrityCheck\Checker;
use OC\Lock\NoopLockingProvider;
use OC\MemoryInfo;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IDBConnection;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Lock\ILockingProvider;
use OCP\Security\ISecureRandom;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CheckSetupController extends Controller {
	/** @var IConfig */
	private $config;
	/** @var IClientService */
	private $clientService;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IL10N */
	private $l10n;
	/** @var Checker */
	private $checker;
	/** @var ILogger */
	private $logger;
	/** @var EventDispatcherInterface */
	private $dispatcher;
	/** @var IDBConnection|Connection */
	private $db;
	/** @var ILockingProvider */
	private $lockingProvider;
	/** @var IDateTimeFormatter */
	private $dateTimeFormatter;
	/** @var MemoryInfo */
	private $memoryInfo;
	/** @var ISecureRandom */
	private $secureRandom;

	public function __construct($AppName,
								IRequest $request,
								IConfig $config,
								IClientService $clientService,
								IURLGenerator $urlGenerator,
								IL10N $l10n,
								Checker $checker,
								ILogger $logger,
								EventDispatcherInterface $dispatcher,
								IDBConnection $db,
								ILockingProvider $lockingProvider,
								IDateTimeFormatter $dateTimeFormatter,
								MemoryInfo $memoryInfo,
								ISecureRandom $secureRandom) {
		parent::__construct($AppName, $request);
		$this->config = $config;
		$this->clientService = $clientService;
		$this->urlGenerator = $urlGenerator;
		$this->l10n = $l10n;
		$this->checker = $checker;
		$this->logger = $logger;
		$this->dispatcher = $dispatcher;
		$this->db = $db;
		$this->lockingProvider = $lockingProvider;
		$this->dateTimeFormatter = $dateTimeFormatter;
		$this->memoryInfo = $memoryInfo;
		$this->secureRandom = $secureRandom;
	}

	/**
	 * Checks if the server can connect to the internet using HTTPS and HTTP
	 * @return bool
	 */
	private function hasInternetConnectivityProblems(): bool {
		if ($this->config->getSystemValue('has_internet_connection', true) === false) {
			return false;
		}

		$siteArray = $this->config->getSystemValue('connectivity_check_domains', [
			'www.nextcloud.com', 'www.startpage.com', 'www.eff.org', 'www.edri.org'
		]);

		foreach ($siteArray as $site) {
			if ($this->isSiteReachable($site)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks if the Nextcloud server can connect to a specific URL using both HTTPS and HTTP
	 * @return bool
	 */
	private function isSiteReachable($sitename) {
		$httpSiteName = 'http://' . $sitename . '/';
		$httpsSiteName = 'https://' . $sitename . '/';

		try {
			$client = $this->clientService->newClient();
			$client->get($httpSiteName);
			$client->get($httpsSiteName);
		} catch (\Exception $e) {
			$this->logger->logException($e, ['app' => 'internet_connection_check']);
			return false;
		}
		return true;
	}

	/**
	 * Checks whether a local memcache is installed or not
	 * @return bool
	 */
	private function isMemcacheConfigured() {
		return $this->config->getSystemValue('memcache.local', null) !== null;
	}

	/**
	 * Whether PHP can generate "secure" pseudorandom integers
	 *
	 * @return bool
	 */
	private function isRandomnessSecure() {
		try {
			$this->secureRandom->generate(1);
		} catch (\Exception $ex) {
			return false;
		}
		return true;
	}

	/**
	 * Public for the sake of unit-testing
	 *
	 * @return array
	 */
	protected function getCurlVersion() {
		return curl_version();
	}

	/**
	 * Check if the used  SSL lib is outdated. Older OpenSSL and NSS versions do
	 * have multiple bugs which likely lead to problems in combination with
	 * functionality required by ownCloud such as SNI.
	 *
	 * @link https://github.com/owncloud/core/issues/17446#issuecomment-122877546
	 * @link https://bugzilla.redhat.com/show_bug.cgi?id=1241172
	 * @return string
	 */
	private function isUsedTlsLibOutdated() {
		// Don't run check when:
		// 1. Server has `has_internet_connection` set to false
		// 2. AppStore AND S2S is disabled
		if (!$this->config->getSystemValue('has_internet_connection', true)) {
			return '';
		}
		if (!$this->config->getSystemValue('appstoreenabled', true)
			&& $this->config->getAppValue('files_sharing', 'outgoing_server2server_share_enabled', 'yes') === 'no'
			&& $this->config->getAppValue('files_sharing', 'incoming_server2server_share_enabled', 'yes') === 'no') {
			return '';
		}

		$versionString = $this->getCurlVersion();
		if (isset($versionString['ssl_version'])) {
			$versionString = $versionString['ssl_version'];
		} else {
			return '';
		}

		$features = (string)$this->l10n->t('installing and updating apps via the app store or Federated Cloud Sharing');
		if (!$this->config->getSystemValue('appstoreenabled', true)) {
			$features = (string)$this->l10n->t('Federated Cloud Sharing');
		}

		// Check if at least OpenSSL after 1.01d or 1.0.2b
		if (strpos($versionString, 'OpenSSL/') === 0) {
			$majorVersion = substr($versionString, 8, 5);
			$patchRelease = substr($versionString, 13, 6);

			if (($majorVersion === '1.0.1' && ord($patchRelease) < ord('d')) ||
				($majorVersion === '1.0.2' && ord($patchRelease) < ord('b'))) {
				return $this->l10n->t('cURL is using an outdated %1$s version (%2$s). Please update your operating system or features such as %3$s will not work reliably.', ['OpenSSL', $versionString, $features]);
			}
		}

		// Check if NSS and perform heuristic check
		if (strpos($versionString, 'NSS/') === 0) {
			try {
				$firstClient = $this->clientService->newClient();
				$firstClient->get('https://nextcloud.com/');

				$secondClient = $this->clientService->newClient();
				$secondClient->get('https://nextcloud.com/');
			} catch (ClientException $e) {
				if ($e->getResponse()->getStatusCode() === 400) {
					return $this->l10n->t('cURL is using an outdated %1$s version (%2$s). Please update your operating system or features such as %3$s will not work reliably.', ['NSS', $versionString, $features]);
				}
			}
		}

		return '';
	}

	/**
	 * Whether the version is outdated
	 *
	 * @return bool
	 */
	protected function isPhpOutdated(): bool {
		return PHP_VERSION_ID < 70300;
	}

	/**
	 * Whether the php version is still supported (at time of release)
	 * according to: https://secure.php.net/supported-versions.php
	 *
	 * @return array
	 */
	private function isPhpSupported(): array {
		return ['eol' => $this->isPhpOutdated(), 'version' => PHP_VERSION];
	}

	/**
	 * Check if the reverse proxy configuration is working as expected
	 *
	 * @return bool
	 */
	private function forwardedForHeadersWorking() {
		$trustedProxies = $this->config->getSystemValue('trusted_proxies', []);
		$remoteAddress = $this->request->getHeader('REMOTE_ADDR');

		if (empty($trustedProxies) && $this->request->getHeader('X-Forwarded-Host') !== '') {
			return false;
		}

		if (\is_array($trustedProxies) && \in_array($remoteAddress, $trustedProxies, true)) {
			return $remoteAddress !== $this->request->getRemoteAddress();
		}

		// either not enabled or working correctly
		return true;
	}

	/**
	 * Checks if the correct memcache module for PHP is installed. Only
	 * fails if memcached is configured and the working module is not installed.
	 *
	 * @return bool
	 */
	private function isCorrectMemcachedPHPModuleInstalled() {
		if ($this->config->getSystemValue('memcache.distributed', null) !== '\OC\Memcache\Memcached') {
			return true;
		}

		// there are two different memcached modules for PHP
		// we only support memcached and not memcache
		// https://code.google.com/p/memcached/wiki/PHPClientComparison
		return !(!extension_loaded('memcached') && extension_loaded('memcache'));
	}

	/**
	 * Checks if set_time_limit is not disabled.
	 *
	 * @return bool
	 */
	private function isSettimelimitAvailable() {
		if (function_exists('set_time_limit')
			&& strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
			return true;
		}

		return false;
	}

	/**
	 * @return RedirectResponse
	 */
	public function rescanFailedIntegrityCheck() {
		$this->checker->runInstanceVerification();
		return new RedirectResponse(
			$this->urlGenerator->linkToRoute('settings.AdminSettings.index', ['section' => 'overview'])
		);
	}

	/**
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	public function getFailedIntegrityCheckFiles() {
		if (!$this->checker->isCodeCheckEnforced()) {
			return new DataDisplayResponse('Integrity checker has been disabled. Integrity cannot be verified.');
		}

		$completeResults = $this->checker->getResults();

		if (!empty($completeResults)) {
			$formattedTextResponse = 'Technical information
=====================
The following list covers which files have failed the integrity check. Please read
the previous linked documentation to learn more about the errors and how to fix
them.

Results
=======
';
			foreach ($completeResults as $context => $contextResult) {
				$formattedTextResponse .= "- $context\n";

				foreach ($contextResult as $category => $result) {
					$formattedTextResponse .= "\t- $category\n";
					if ($category !== 'EXCEPTION') {
						foreach ($result as $key => $results) {
							$formattedTextResponse .= "\t\t- $key\n";
						}
					} else {
						foreach ($result as $key => $results) {
							$formattedTextResponse .= "\t\t- $results\n";
						}
					}
				}
			}

			$formattedTextResponse .= '
Raw output
==========
';
			$formattedTextResponse .= print_r($completeResults, true);
		} else {
			$formattedTextResponse = 'No errors have been found.';
		}


		$response = new DataDisplayResponse(
			$formattedTextResponse,
			Http::STATUS_OK,
			[
				'Content-Type' => 'text/plain',
			]
		);

		return $response;
	}

	/**
	 * Checks whether a PHP opcache is properly set up
	 * @return bool
	 */
	protected function isOpcacheProperlySetup() {
		$iniWrapper = new IniGetWrapper();

		if (!$iniWrapper->getBool('opcache.enable')) {
			return false;
		}

		if (!$iniWrapper->getBool('opcache.save_comments')) {
			return false;
		}

		if ($iniWrapper->getNumeric('opcache.max_accelerated_files') < 10000) {
			return false;
		}

		if ($iniWrapper->getNumeric('opcache.memory_consumption') < 128) {
			return false;
		}

		if ($iniWrapper->getNumeric('opcache.interned_strings_buffer') < 8) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the required FreeType functions are present
	 * @return bool
	 */
	protected function hasFreeTypeSupport() {
		return function_exists('imagettfbbox') && function_exists('imagettftext');
	}

	protected function hasMissingIndexes(): array {
		$indexInfo = new MissingIndexInformation();
		// Dispatch event so apps can also hint for pending index updates if needed
		$event = new GenericEvent($indexInfo);
		$this->dispatcher->dispatch(IDBConnection::CHECK_MISSING_INDEXES_EVENT, $event);

		return $indexInfo->getListOfMissingIndexes();
	}

	protected function hasMissingColumns(): array {
		$indexInfo = new MissingColumnInformation();
		// Dispatch event so apps can also hint for pending index updates if needed
		$event = new GenericEvent($indexInfo);
		$this->dispatcher->dispatch(IDBConnection::CHECK_MISSING_COLUMNS_EVENT, $event);

		return $indexInfo->getListOfMissingColumns();
	}

	protected function isSqliteUsed() {
		return strpos($this->config->getSystemValue('dbtype'), 'sqlite') !== false;
	}

	protected function isReadOnlyConfig(): bool {
		return \OC_Helper::isReadOnlyConfigEnabled();
	}

	protected function hasValidTransactionIsolationLevel(): bool {
		try {
			if ($this->db->getDatabasePlatform() instanceof SqlitePlatform) {
				return true;
			}

			return $this->db->getTransactionIsolation() === Connection::TRANSACTION_READ_COMMITTED;
		} catch (DBALException $e) {
			// ignore
		}

		return true;
	}

	protected function hasFileinfoInstalled(): bool {
		return \OC_Util::fileInfoLoaded();
	}

	protected function hasWorkingFileLocking(): bool {
		return !($this->lockingProvider instanceof NoopLockingProvider);
	}

	protected function getSuggestedOverwriteCliURL(): string {
		$suggestedOverwriteCliUrl = '';
		if ($this->config->getSystemValue('overwrite.cli.url', '') === '') {
			$suggestedOverwriteCliUrl = $this->request->getServerProtocol() . '://' . $this->request->getInsecureServerHost() . \OC::$WEBROOT;
			if (!$this->config->getSystemValue('config_is_read_only', false)) {
				// Set the overwrite URL when it was not set yet.
				$this->config->setSystemValue('overwrite.cli.url', $suggestedOverwriteCliUrl);
				$suggestedOverwriteCliUrl = '';
			}
		}
		return $suggestedOverwriteCliUrl;
	}

	protected function getLastCronInfo(): array {
		$lastCronRun = $this->config->getAppValue('core', 'lastcron', 0);
		return [
			'diffInSeconds' => time() - $lastCronRun,
			'relativeTime' => $this->dateTimeFormatter->formatTimeSpan($lastCronRun),
			'backgroundJobsUrl' => $this->urlGenerator->linkToRoute('settings.AdminSettings.index', ['section' => 'server']) . '#backgroundjobs',
		];
	}

	protected function getCronErrors() {
		$errors = json_decode($this->config->getAppValue('core', 'cronErrors', ''), true);

		if (is_array($errors)) {
			return $errors;
		}

		return [];
	}

	protected function isPHPMailerUsed(): bool {
		return $this->config->getSystemValue('mail_smtpmode', 'smtp') === 'php';
	}

	protected function hasOpcacheLoaded(): bool {
		return extension_loaded('Zend OPcache');
	}

	/**
	 * Iterates through the configured app roots and
	 * tests if the subdirectories are owned by the same user than the current user.
	 *
	 * @return array
	 */
	protected function getAppDirsWithDifferentOwner(): array {
		$currentUser = posix_getuid();
		$appDirsWithDifferentOwner = [[]];

		foreach (OC::$APPSROOTS as $appRoot) {
			if ($appRoot['writable'] === true) {
				$appDirsWithDifferentOwner[] = $this->getAppDirsWithDifferentOwnerForAppRoot($currentUser, $appRoot);
			}
		}

		$appDirsWithDifferentOwner = array_merge(...$appDirsWithDifferentOwner);
		sort($appDirsWithDifferentOwner);

		return $appDirsWithDifferentOwner;
	}

	/**
	 * Tests if the directories for one apps directory are writable by the current user.
	 *
	 * @param int $currentUser The current user
	 * @param array $appRoot The app root config
	 * @return string[] The none writable directory paths inside the app root
	 */
	private function getAppDirsWithDifferentOwnerForAppRoot(int $currentUser, array $appRoot): array {
		$appDirsWithDifferentOwner = [];
		$appsPath = $appRoot['path'];
		$appsDir = new DirectoryIterator($appRoot['path']);

		foreach ($appsDir as $fileInfo) {
			if ($fileInfo->isDir() && !$fileInfo->isDot()) {
				$absAppPath = $appsPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
				$appDirUser = fileowner($absAppPath);
				if ($appDirUser !== $currentUser) {
					$appDirsWithDifferentOwner[] = $absAppPath;
				}
			}
		}

		return $appDirsWithDifferentOwner;
	}

	/**
	 * Checks for potential PHP modules that would improve the instance
	 *
	 * @return string[] A list of PHP modules that is recommended
	 */
	protected function hasRecommendedPHPModules(): array {
		$recommendedPHPModules = [];

		if (!extension_loaded('intl')) {
			$recommendedPHPModules[] = 'intl';
		}

		if (!extension_loaded('bcmath')) {
			$recommendedPHPModules[] = 'bcmath';
		}

		if (!extension_loaded('gmp')) {
			$recommendedPHPModules[] = 'gmp';
		}

		if ($this->config->getAppValue('theming', 'enabled', 'no') === 'yes') {
			if (!extension_loaded('imagick')) {
				$recommendedPHPModules[] = 'imagick';
			}
		}

		return $recommendedPHPModules;
	}

	protected function isMysqlUsedWithoutUTF8MB4(): bool {
		return ($this->config->getSystemValue('dbtype', 'sqlite') === 'mysql') && ($this->config->getSystemValue('mysql.utf8mb4', false) === false);
	}

	protected function hasBigIntConversionPendingColumns(): array {
		// copy of ConvertFilecacheBigInt::getColumnsByTable()
		$tables = [
			'activity' => ['activity_id', 'object_id'],
			'activity_mq' => ['mail_id'],
			'authtoken' => ['id'],
			'bruteforce_attempts' => ['id'],
			'filecache' => ['fileid', 'storage', 'parent', 'mimetype', 'mimepart', 'mtime', 'storage_mtime'],
			'file_locks' => ['id'],
			'jobs' => ['id'],
			'mimetypes' => ['id'],
			'mounts' => ['id', 'storage_id', 'root_id', 'mount_id'],
			'storages' => ['numeric_id'],
		];

		$schema = new SchemaWrapper($this->db);
		$isSqlite = $this->db->getDatabasePlatform() instanceof SqlitePlatform;
		$pendingColumns = [];

		foreach ($tables as $tableName => $columns) {
			if (!$schema->hasTable($tableName)) {
				continue;
			}

			$table = $schema->getTable($tableName);
			foreach ($columns as $columnName) {
				$column = $table->getColumn($columnName);
				$isAutoIncrement = $column->getAutoincrement();
				$isAutoIncrementOnSqlite = $isSqlite && $isAutoIncrement;
				if ($column->getType()->getName() !== Type::BIGINT && !$isAutoIncrementOnSqlite) {
					$pendingColumns[] = $tableName . '.' . $columnName;
				}
			}
		}

		return $pendingColumns;
	}

	protected function isEnoughTempSpaceAvailableIfS3PrimaryStorageIsUsed(): bool {
		$objectStore = $this->config->getSystemValue('objectstore', null);
		$objectStoreMultibucket = $this->config->getSystemValue('objectstore_multibucket', null);

		if (!isset($objectStoreMultibucket) && !isset($objectStore)) {
			return true;
		}

		if (isset($objectStoreMultibucket['class']) && $objectStoreMultibucket['class'] !== 'OC\\Files\\ObjectStore\\S3') {
			return true;
		}

		if (isset($objectStore['class']) && $objectStore['class'] !== 'OC\\Files\\ObjectStore\\S3') {
			return true;
		}

		$tempPath = sys_get_temp_dir();
		if (!is_dir($tempPath)) {
			$this->logger->error('Error while checking the temporary PHP path - it was not properly set to a directory. value: ' . $tempPath);
			return false;
		}
		$freeSpaceInTemp = disk_free_space($tempPath);
		if ($freeSpaceInTemp === false) {
			$this->logger->error('Error while checking the available disk space of temporary PHP path - no free disk space returned. temporary path: ' . $tempPath);
			return false;
		}

		$freeSpaceInTempInGB = $freeSpaceInTemp / 1024 / 1024 / 1024;
		if ($freeSpaceInTempInGB > 50) {
			return true;
		}

		$this->logger->warning('Checking the available space in the temporary path resulted in ' . round($freeSpaceInTempInGB, 1) . ' GB instead of the recommended 50GB. Path: ' . $tempPath);
		return false;
	}

	/**
	 * @return DataResponse
	 */
	public function check() {
		return new DataResponse(
			[
				'isGetenvServerWorking' => !empty(getenv('PATH')),
				'isReadOnlyConfig' => $this->isReadOnlyConfig(),
				'hasValidTransactionIsolationLevel' => $this->hasValidTransactionIsolationLevel(),
				'hasFileinfoInstalled' => $this->hasFileinfoInstalled(),
				'hasWorkingFileLocking' => $this->hasWorkingFileLocking(),
				'suggestedOverwriteCliURL' => $this->getSuggestedOverwriteCliURL(),
				'cronInfo' => $this->getLastCronInfo(),
				'cronErrors' => $this->getCronErrors(),
				'serverHasInternetConnectionProblems' => $this->hasInternetConnectivityProblems(),
				'isMemcacheConfigured' => $this->isMemcacheConfigured(),
				'memcacheDocs' => $this->urlGenerator->linkToDocs('admin-performance'),
				'isRandomnessSecure' => $this->isRandomnessSecure(),
				'securityDocs' => $this->urlGenerator->linkToDocs('admin-security'),
				'isUsedTlsLibOutdated' => $this->isUsedTlsLibOutdated(),
				'phpSupported' => $this->isPhpSupported(),
				'forwardedForHeadersWorking' => $this->forwardedForHeadersWorking(),
				'reverseProxyDocs' => $this->urlGenerator->linkToDocs('admin-reverse-proxy'),
				'isCorrectMemcachedPHPModuleInstalled' => $this->isCorrectMemcachedPHPModuleInstalled(),
				'hasPassedCodeIntegrityCheck' => $this->checker->hasPassedCheck(),
				'codeIntegrityCheckerDocumentation' => $this->urlGenerator->linkToDocs('admin-code-integrity'),
				'isOpcacheProperlySetup' => $this->isOpcacheProperlySetup(),
				'hasOpcacheLoaded' => $this->hasOpcacheLoaded(),
				'phpOpcacheDocumentation' => $this->urlGenerator->linkToDocs('admin-php-opcache'),
				'isSettimelimitAvailable' => $this->isSettimelimitAvailable(),
				'hasFreeTypeSupport' => $this->hasFreeTypeSupport(),
				'missingIndexes' => $this->hasMissingIndexes(),
				'missingColumns' => $this->hasMissingColumns(),
				'isSqliteUsed' => $this->isSqliteUsed(),
				'databaseConversionDocumentation' => $this->urlGenerator->linkToDocs('admin-db-conversion'),
				'isPHPMailerUsed' => $this->isPHPMailerUsed(),
				'mailSettingsDocumentation' => $this->urlGenerator->getAbsoluteURL('index.php/settings/admin'),
				'isMemoryLimitSufficient' => $this->memoryInfo->isMemoryLimitSufficient(),
				'appDirsWithDifferentOwner' => $this->getAppDirsWithDifferentOwner(),
				'recommendedPHPModules' => $this->hasRecommendedPHPModules(),
				'pendingBigIntConversionColumns' => $this->hasBigIntConversionPendingColumns(),
				'isMysqlUsedWithoutUTF8MB4' => $this->isMysqlUsedWithoutUTF8MB4(),
				'isEnoughTempSpaceAvailableIfS3PrimaryStorageIsUsed' => $this->isEnoughTempSpaceAvailableIfS3PrimaryStorageIsUsed(),
				'reverseProxyGeneratedURL' => $this->urlGenerator->getAbsoluteURL('index.php'),
			]
		);
	}
}
