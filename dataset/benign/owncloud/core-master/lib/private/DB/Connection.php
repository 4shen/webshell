<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Philipp Schaffrath <github@philipp.schaffrath.email>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

namespace OC\DB;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use OC\DB\QueryBuilder\QueryBuilder;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\PreConditionNotMetException;

class Connection extends \Doctrine\DBAL\Connection implements IDBConnection {
	/**
	 * @var string $tablePrefix
	 */
	protected $tablePrefix;

	/**
	 * @var \OC\DB\Adapter $adapter
	 */
	protected $adapter;

	protected $lockedTable = null;

	public function connect() {
		try {
			return parent::connect();
		} catch (DBALException $e) {
			// throw a new exception to prevent leaking info from the stacktrace
			throw new DBALException('Failed to connect to the database: ' . $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Returns a QueryBuilder for the connection.
	 *
	 * @return \OCP\DB\QueryBuilder\IQueryBuilder
	 */
	public function getQueryBuilder() {
		return new QueryBuilder($this);
	}

	/**
	 * Gets the QueryBuilder for the connection.
	 *
	 * @return \Doctrine\DBAL\Query\QueryBuilder
	 * @deprecated please use $this->getQueryBuilder() instead
	 */
	public function createQueryBuilder() {
		$backtrace = $this->getCallerBacktrace();
		\OC::$server->getLogger()->debug('Doctrine QueryBuilder retrieved in {backtrace}', ['app' => 'core', 'backtrace' => $backtrace]);
		return parent::createQueryBuilder();
	}

	/**
	 * Gets the ExpressionBuilder for the connection.
	 *
	 * @return \Doctrine\DBAL\Query\Expression\ExpressionBuilder
	 * @deprecated please use $this->getQueryBuilder()->expr() instead
	 */
	public function getExpressionBuilder() {
		$backtrace = $this->getCallerBacktrace();
		\OC::$server->getLogger()->debug('Doctrine ExpressionBuilder retrieved in {backtrace}', ['app' => 'core', 'backtrace' => $backtrace]);
		return parent::getExpressionBuilder();
	}

	/**
	 * Get the file and line that called the method where `getCallerBacktrace()` was used
	 *
	 * @return string
	 */
	protected function getCallerBacktrace() {
		$traces = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

		// 0 is the method where we use `getCallerBacktrace`
		// 1 is the target method which uses the method we want to log
		if (isset($traces[1])) {
			return $traces[1]['file'] . ':' . $traces[1]['line'];
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->tablePrefix;
	}

	/**
	 * Initializes a new instance of the Connection class.
	 *
	 * @param array $params  The connection parameters.
	 * @param \Doctrine\DBAL\Driver $driver
	 * @param \Doctrine\DBAL\Configuration $config
	 * @param \Doctrine\Common\EventManager $eventManager
	 * @throws \Exception
	 */
	public function __construct(array $params, Driver $driver, Configuration $config = null,
		EventManager $eventManager = null) {
		if (!isset($params['adapter'])) {
			throw new \Exception('adapter not set');
		}
		if (!isset($params['tablePrefix'])) {
			throw new \Exception('tablePrefix not set');
		}
		parent::__construct($params, $driver, $config, $eventManager);
		$this->adapter = new $params['adapter']($this);
		$this->tablePrefix = $params['tablePrefix'];

		parent::setTransactionIsolation(parent::TRANSACTION_READ_COMMITTED);
	}

	/**
	 * Prepares an SQL statement.
	 *
	 * @param string $statement The SQL statement to prepare.
	 * @param int $limit
	 * @param int $offset
	 * @return \Doctrine\DBAL\Driver\Statement The prepared statement.
	 */
	public function prepare($statement, $limit=null, $offset=null) {
		if ($limit === -1) {
			$limit = null;
		}
		if ($limit !== null) {
			$platform = $this->getDatabasePlatform();
			$statement = $platform->modifyLimitQuery($statement, $limit, $offset);
		}
		$statement = $this->replaceTablePrefix($statement);
		$statement = $this->adapter->fixupStatement($statement);

		return parent::prepare($statement);
	}

	/**
	 * Executes an, optionally parametrized, SQL query.
	 *
	 * If the query is parametrized, a prepared statement is used.
	 * If an SQLLogger is configured, the execution is logged.
	 *
	 * @param string                                      $query  The SQL query to execute.
	 * @param array                                       $params The parameters to bind to the query, if any.
	 * @param array                                       $types  The types the previous parameters are in.
	 * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp    The query cache profile, optional.
	 *
	 * @return \Doctrine\DBAL\Driver\Statement The executed statement.
	 *
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null) {
		$query = $this->replaceTablePrefix($query);
		$query = $this->adapter->fixupStatement($query);
		return parent::executeQuery($query, $params, $types, $qcp);
	}

	/**
	 * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
	 * and returns the number of affected rows.
	 *
	 * This method supports PDO binding types as well as DBAL mapping types.
	 *
	 * @param string $query  The SQL query.
	 * @param array  $params The query parameters.
	 * @param array  $types  The parameter types.
	 *
	 * @return integer The number of affected rows.
	 *
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function executeUpdate($query, array $params = [], array $types = []) {
		$query = $this->replaceTablePrefix($query);
		$query = $this->adapter->fixupStatement($query);
		return parent::executeUpdate($query, $params, $types);
	}

	/**
	 * Returns the ID of the last inserted row, or the last value from a sequence object,
	 * depending on the underlying driver.
	 *
	 * Note: This method may not return a meaningful or consistent result across different drivers,
	 * because the underlying database may not even support the notion of AUTO_INCREMENT/IDENTITY
	 * columns or sequences.
	 *
	 * @param string $seqName Name of the sequence object from which the ID should be returned.
	 * @return string A string representation of the last inserted ID.
	 */
	public function lastInsertId($seqName = null) {
		if ($seqName) {
			$seqName = $this->replaceTablePrefix($seqName);
		}
		return $this->adapter->lastInsertId($seqName);
	}

	// internal use
	public function realLastInsertId($seqName = null) {
		return parent::lastInsertId($seqName);
	}

	/**
	 * Insert a row if the matching row does not exists.
	 *
	 * @param string $table The table name (will replace *PREFIX* with the actual prefix)
	 * @param array $input data that should be inserted into the table  (column name => value)
	 * @param array|null $compare List of values that should be checked for "if not exists"
	 *				If this is null or an empty array, all keys of $input will be compared
	 *				Please note: text fields (clob) must not be used in the compare array
	 * @return int number of inserted rows
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function insertIfNotExist($table, $input, array $compare = null) {
		return $this->adapter->insertIfNotExist($table, $input, $compare);
	}

	/**
	 * Attempt to update a row, else insert a new one
	 *
	 * @param string $table The table name (will replace *PREFIX* with the actual prefix)
	 * @param array $input data that should be inserted into the table  (column name => value)
	 * @param array|null $compare List of values that should be checked for "if not exists"
	 *				If this is null or an empty array, all keys of $input will be compared
	 *				Please note: text fields (clob) must not be used in the compare array
	 * @return int number of affected rows
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function upsert($table, $input, array $compare = null) {
		return $this->adapter->upsert($table, $input, $compare);
	}

	private function getType($value) {
		if (\is_bool($value)) {
			return IQueryBuilder::PARAM_BOOL;
		} elseif (\is_int($value)) {
			return IQueryBuilder::PARAM_INT;
		} else {
			return IQueryBuilder::PARAM_STR;
		}
	}

	/**
	 * Insert or update a row value.
	 *
	 * @param string $table
	 * @param array $keys (column name => value)
	 * @param array $values (column name => value)
	 * @param array $updatePreconditionValues ensure values match preconditions (column name => value)
	 * @return int number of new rows
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws PreConditionNotMetException
	 */
	public function setValues($table, array $keys, array $values, array $updatePreconditionValues = []) {
		// Try to insert whole record into the table ($toInsert) if predicate NOT EXISTS ($compare) is satisfied
		$toInsert = \array_merge($keys, $values);
		$compare = \array_keys($keys);
		$tableName = $this->tablePrefix . $table;
		$affected = $this->adapter->insertIfNotExist($tableName, $toInsert, $compare);

		if ($affected === 0) {
			// value already exists, try update
			$updateQb = $this->getQueryBuilder();
			$updateQb->update($table);
			foreach ($values as $name => $value) {
				$updateQb->set($name, $updateQb->createNamedParameter($value, $this->getType($value)));
			}
			$where = $updateQb->expr()->andX();
			$whereValues = \array_merge($keys, $updatePreconditionValues);
			foreach ($whereValues as $name => $value) {
				$where->add($updateQb->expr()->eq(
					$name,
					$updateQb->createNamedParameter($value, $this->getType($value)),
					$this->getType($value)
				));
			}
			$updateQb->where($where);
			$affected = $updateQb->execute();

			if ($affected === 0 && !empty($updatePreconditionValues)) {
				throw new PreConditionNotMetException();
			}
		}

		return $affected;
	}

	/**
	 * Create an exclusive read+write lock on a table
	 *
	 * @param string $tableName
	 * @throws \BadMethodCallException When trying to acquire a second lock
	 * @since 9.1.0
	 */
	public function lockTable($tableName) {
		if ($this->lockedTable !== null) {
			throw new \BadMethodCallException('Can not lock a new table until the previous lock is released.');
		}

		$tableName = $this->tablePrefix . $tableName;
		$this->lockedTable = $tableName;
		$this->adapter->lockTable($tableName);
	}

	/**
	 * Release a previous acquired lock again
	 *
	 * @since 9.1.0
	 */
	public function unlockTable() {
		$this->adapter->unlockTable();
		$this->lockedTable = null;
	}

	/**
	 * returns the error code and message as a string for logging
	 * works with DoctrineException
	 * @return string
	 */
	public function getError() {
		$msg = $this->errorCode() . ': ';
		$errorInfo = $this->errorInfo();
		if (\is_array($errorInfo)) {
			$msg .= 'SQLSTATE = '.$errorInfo[0] . ', ';
			$msg .= 'Driver Code = '.$errorInfo[1] . ', ';
			$msg .= 'Driver Message = '.$errorInfo[2];
		}
		return $msg;
	}

	/**
	 * Drop a table from the database if it exists
	 *
	 * @param string $table table name without the prefix
	 */
	public function dropTable($table) {
		$table = $this->tablePrefix . \trim($table);
		$schema = $this->getSchemaManager();
		if ($schema->tablesExist([$table])) {
			$schema->dropTable($table);
		}
	}

	/**
	 * Check if a table exists
	 *
	 * @param string $table table name without the prefix
	 * @return bool
	 */
	public function tableExists($table) {
		$table = $this->tablePrefix . \trim($table);
		$schema = $this->getSchemaManager();
		return $schema->tablesExist([$table]);
	}

	// internal use
	/**
	 * @param string $statement
	 * @return string
	 */
	protected function replaceTablePrefix($statement) {
		return \str_replace('*PREFIX*', $this->tablePrefix, $statement);
	}

	/**
	 * Check if a transaction is active
	 *
	 * @return bool
	 * @since 8.2.0
	 */
	public function inTransaction() {
		return $this->getTransactionNestingLevel() > 0;
	}

	/**
	 * Espace a parameter to be used in a LIKE query
	 *
	 * @param string $param
	 * @return string
	 */
	public function escapeLikeParameter($param) {
		return \addcslashes($param, '\\_%');
	}

	/**
	 * Create the schema of the connected database
	 *
	 * @return Schema
	 */
	public function createSchema() {
		$schemaManager = new MDB2SchemaManager($this);
		$migrator = $schemaManager->getMigrator();
		return $migrator->createSchema();
	}

	/**
	 * Migrate the database to the given schema
	 *
	 * @param Schema $toSchema
	 */
	public function migrateToSchema(Schema $toSchema) {
		$schemaManager = new MDB2SchemaManager($this);
		$migrator = $schemaManager->getMigrator();
		$migrator->migrate($toSchema);
	}

	/**
	 * Are 4-byte characters allowed or only 3-byte
	 *
	 * @return bool
	 * @since 10.0
	 */
	public function allows4ByteCharacters() {
		if (!$this->getDatabasePlatform() instanceof MySqlPlatform) {
			return true;
		}
		if ($this->getParams()['charset'] === 'utf8mb4') {
			return true;
		}
		return false;
	}

	/**
	 * Returns the version of the related platform if applicable.
	 *
	 * Returns null if either the driver is not capable to create version
	 * specific platform instances, no explicit server version was specified
	 * or the underlying driver connection cannot determine the platform
	 * version without having to query it (performance reasons).
	 *
	 * @return null|string
	 */
	public function getDatabaseVersionString() {
		// Automatic platform version detection.
		if ($this->_conn instanceof ServerInfoAwareConnection &&
			! $this->_conn->requiresQueryForServerVersion()
		) {
			return $this->_conn->getServerVersion();
		}

		// Unable to detect platform version.
		return null;
	}
}
