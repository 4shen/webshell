<?php
/**
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

namespace OC\DB;

use OC\IntegrityCheck\Helpers\AppLocator;
use OC\Migration\SimpleOutput;
use OCP\AppFramework\QueryException;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\ISchemaMigration;
use OCP\Migration\ISimpleMigration;
use OCP\Migration\ISqlMigration;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use OCP\ILogger;

class MigrationService {

	/** @var boolean */
	private $migrationTableCreated;
	/** @var array */
	private $migrations;
	/** @var IOutput */
	private $output;
	/** @var Connection */
	private $connection;
	/** @var string */
	private $appName;
	/** @var ILogger */
	private $logger;
	/** @var string  */
	private $migrationsPath;
	/** @var string  */
	private $migrationsNamespace;

	/**
	 * MigrationService constructor.
	 *
	 * @param $appName
	 * @param IDBConnection $connection
	 * @param IOutput|null $output
	 * @param AppLocator $appLocator
	 * @param ILogger|null $logger
	 * @throws \OC\NeedsUpdateException
	 */
	public function __construct($appName,
						IDBConnection $connection,
						IOutput $output = null,
						AppLocator $appLocator = null,
						ILogger $logger = null) {
		$this->appName = $appName;
		$this->connection = $connection;
		$this->output = $output;
		if ($this->output === null) {
			$this->output = new SimpleOutput(\OC::$server->getLogger(), $appName);
		}
		$this->logger = $logger;
		if ($this->logger === null) {
			$this->logger = \OC::$server->getLogger();
		}

		if ($appName === 'core') {
			$this->migrationsPath = \OC::$SERVERROOT . '/core/Migrations';
			$this->migrationsNamespace = 'OC\\Migrations';
		} else {
			if ($appLocator === null) {
				$appLocator = new AppLocator();
			}
			$appPath = $appLocator->getAppPath($appName);
			$this->migrationsPath = "$appPath/appinfo/Migrations";
			$this->migrationsNamespace = "OCA\\$appName\\Migrations";
		}

		if (!\is_dir($this->migrationsPath)) {
			if (!\mkdir($this->migrationsPath)) {
				throw new \Exception("Could not create migration folder \"{$this->migrationsPath}\"");
			}
		}

		// load the app so that app code can be used during migrations
		\OC_App::loadApp($this->appName, false);
	}

	private static function requireOnce($file) {
		require_once $file;
	}

	/**
	 * Returns the name of the app for which this migration is executed
	 *
	 * @return string
	 */
	public function getApp() {
		return $this->appName;
	}

	/**
	 * @return bool
	 * @codeCoverageIgnore - this will implicitly tested on installation
	 */
	private function createMigrationTable() {
		if ($this->migrationTableCreated) {
			return false;
		}

		if ($this->connection->tableExists('migrations')) {
			$this->migrationTableCreated = true;
			return false;
		}

		$tableName = $this->connection->getPrefix() . 'migrations';
		$tableName = $this->connection->getDatabasePlatform()->quoteIdentifier($tableName);

		$columns = [
			// Length = max indexable char length - length of other columns = 191 - 14
			'app' => new Column($this->connection->getDatabasePlatform()->quoteIdentifier('app'), Type::getType('string'), ['length' => 177]),
			// Datetime string. Eg: 20172605104128
			'version' => new Column($this->connection->getDatabasePlatform()->quoteIdentifier('version'), Type::getType('string'), ['length' => 14]),
		];
		$table = new Table($tableName, $columns);
		$table->setPrimaryKey([
			$this->connection->getDatabasePlatform()->quoteIdentifier('app'),
			$this->connection->getDatabasePlatform()->quoteIdentifier('version')]);
		$this->connection->getSchemaManager()->createTable($table);

		$this->migrationTableCreated = true;

		return true;
	}

	/**
	 * Returns all versions which have already been applied
	 *
	 * @return string[]
	 * @codeCoverageIgnore - no need to test this
	 */
	public function getMigratedVersions() {
		$this->createMigrationTable();
		$qb = $this->connection->getQueryBuilder();

		$qb->select('version')
			->from('migrations')
			->where($qb->expr()->eq('app', $qb->createNamedParameter($this->getApp())))
			->orderBy('version');

		$result = $qb->execute();
		$rows = $result->fetchAll(\PDO::FETCH_COLUMN);
		$result->closeCursor();

		return $rows;
	}

	/**
	 * Returns all versions which are available in the migration folder
	 *
	 * @return array
	 */
	public function getAvailableVersions() {
		$this->ensureMigrationsAreLoaded();
		return \array_keys($this->migrations);
	}

	protected function findMigrations() {
		$directory = \realpath($this->migrationsPath);
		$iterator = new \RegexIterator(
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::LEAVES_ONLY
			),
			'#^.+\\/Version[^\\/]{1,255}\\.php$#i',
			\RegexIterator::GET_MATCH);

		$files = \array_keys(\iterator_to_array($iterator));
		\uasort($files, function ($a, $b) {
			return (\basename($a) < \basename($b)) ? -1 : 1;
		});

		$migrations = [];

		foreach ($files as $file) {
			static::requireOnce($file);
			$className = \basename($file, '.php');
			$version = (string) \substr($className, 7);
			if ($version === '0') {
				throw new \InvalidArgumentException(
					"Cannot load a migrations with the name '$version' because it is a reserved number"
				);
			}
			$migrations[$version] = \sprintf('%s\\%s', $this->migrationsNamespace, $className);
		}

		return $migrations;
	}

	/**
	 * @param string $to
	 * @return array
	 */
	private function getMigrationsToExecute($to) {
		$knownMigrations = $this->getMigratedVersions();
		$availableMigrations = $this->getAvailableVersions();

		$toBeExecuted = [];
		foreach ($availableMigrations as $v) {
			if ($to !== 'latest' && $v > $to) {
				continue;
			}
			if ($this->shallBeExecuted($v, $knownMigrations)) {
				$toBeExecuted[] = $v;
			}
		}

		return $toBeExecuted;
	}

	/**
	 * @param string $m
	 * @param string[] $knownMigrations
	 * @return bool
	 */
	private function shallBeExecuted($m, $knownMigrations) {
		if (\in_array($m, $knownMigrations)) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $version
	 */
	private function markAsExecuted($version) {
		$this->connection->insertIfNotExist('*PREFIX*migrations', [
			'app' => $this->appName,
			'version' => $version
		]);
	}

	/**
	 * Returns the name of the table which holds the already applied versions
	 *
	 * @return string
	 */
	public function getMigrationsTableName() {
		return $this->connection->getPrefix() . 'migrations';
	}

	/**
	 * Returns the namespace of the version classes
	 *
	 * @return string
	 */
	public function getMigrationsNamespace() {
		return $this->migrationsNamespace;
	}

	/**
	 * Returns the directory which holds the versions
	 *
	 * @return string
	 */
	public function getMigrationsDirectory() {
		return $this->migrationsPath;
	}

	/**
	 * Return the explicit version for the aliases; current, next, prev, latest
	 *
	 * @param string $alias
	 * @return mixed|null|string
	 */
	public function getMigration($alias) {
		switch ($alias) {
			case 'current':
				return $this->getCurrentVersion();
			case 'next':
				return $this->getRelativeVersion($this->getCurrentVersion(), 1);
			case 'prev':
				return $this->getRelativeVersion($this->getCurrentVersion(), -1);
			case 'latest':
				$this->ensureMigrationsAreLoaded();

				return @\end($this->getAvailableVersions());
		}
		return '0';
	}

	/**
	 * @param string $version
	 * @param int $delta
	 * @return null|string
	 */
	private function getRelativeVersion($version, $delta) {
		$this->ensureMigrationsAreLoaded();

		$versions = $this->getAvailableVersions();
		\array_unshift($versions, 0);
		$offset = \array_search($version, $versions);
		if ($offset === false || !isset($versions[$offset + $delta])) {
			// Unknown version or delta out of bounds.
			return null;
		}

		return (string) $versions[$offset + $delta];
	}

	/**
	 * @return string
	 */
	private function getCurrentVersion() {
		$m = $this->getMigratedVersions();
		if (\count($m) === 0) {
			return '0';
		}
		return @\end(\array_values($m));
	}

	/**
	 * @param string $version
	 * @return string
	 */
	private function getClass($version) {
		$this->ensureMigrationsAreLoaded();

		if (isset($this->migrations[$version])) {
			return $this->migrations[$version];
		}

		throw new \InvalidArgumentException("Version $version is unknown.");
	}

	/**
	 * Allows to set an IOutput implementation which is used for logging progress and messages
	 *
	 * @param IOutput $output
	 */
	public function setOutput(IOutput $output) {
		$this->output = $output;
	}

	/**
	 * Applies all not yet applied versions up to $to
	 *
	 * @param string $to
	 */
	public function migrate($to = 'latest') {
		// read known migrations
		$toBeExecuted = $this->getMigrationsToExecute($to);
		foreach ($toBeExecuted as $version) {
			$this->executeStep($version);
		}
	}

	/**
	 * @param string $version
	 * @return mixed
	 * @throws \Exception
	 */
	protected function createInstance($version) {
		$class = $this->getClass($version);
		try {
			$s = \OC::$server->query($class);
		} catch (QueryException $e) {
			if (\class_exists($class)) {
				$s = new $class();
			} else {
				throw new \Exception("Migration step '$class' is unknown");
			}
		}

		return $s;
	}

	/**
	 * Executes one explicit version
	 *
	 * @param string $version
	 */
	public function executeStep($version) {
		$this->logger->debug("Migrations: starting $version from app {$this->appName}", ['app' => 'core']);
		$instance = $this->createInstance($version);
		if ($instance instanceof ISimpleMigration) {
			$instance->run($this->output);
		}
		if ($instance instanceof ISqlMigration) {
			$sqls = $instance->sql($this->connection);
			if (\is_array($sqls)) {
				foreach ($sqls as $s) {
					$this->connection->executeQuery($s);
				}
			}
		}
		if ($instance instanceof ISchemaMigration) {
			$toSchema = $this->connection->createSchema();
			$instance->changeSchema($toSchema, ['tablePrefix' => $this->connection->getPrefix()]);
			$this->connection->migrateToSchema($toSchema);
		}
		$this->markAsExecuted($version);
		$this->logger->debug("Migrations: completed $version from app {$this->appName}", ['app' => 'core']);
	}

	private function ensureMigrationsAreLoaded() {
		if (empty($this->migrations)) {
			$this->migrations = $this->findMigrations();
		}
	}
}
