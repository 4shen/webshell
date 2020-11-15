<?php
/**
 * @author martin-rueegg <martin.rueegg@metaworx.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author tbelau666 <thomas.belau@gmx.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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

use \Doctrine\DBAL\DBALException;
use \Doctrine\DBAL\Schema\Index;
use \Doctrine\DBAL\Schema\Table;
use \Doctrine\DBAL\Schema\Schema;
use \Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use OCP\IConfig;
use OCP\Security\ISecureRandom;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class Migrator {

	/** @var \Doctrine\DBAL\Connection $connection */
	protected $connection;

	/** @var ISecureRandom */
	private $random;

	/** @var IConfig */
	protected $config;

	/** @var EventDispatcher  */
	private $dispatcher;

	/** @var bool */
	private $noEmit = false;

	/**
	 * @param \Doctrine\DBAL\Connection|Connection $connection
	 * @param ISecureRandom $random
	 * @param IConfig $config
	 * @param EventDispatcher $dispatcher
	 */
	public function __construct(\Doctrine\DBAL\Connection $connection,
								ISecureRandom $random,
								IConfig $config,
								EventDispatcher $dispatcher = null) {
		$this->connection = $connection;
		$this->random = $random;
		$this->config = $config;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 */
	public function migrate(Schema $targetSchema) {
		$this->noEmit = true;
		$this->applySchema($targetSchema);
	}

	/**
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 * @return string
	 */
	public function generateChangeScript(Schema $targetSchema) {
		$schemaDiff = $this->getDiff($targetSchema, $this->connection);

		$script = '';
		$sqls = $schemaDiff->toSql($this->connection->getDatabasePlatform());
		foreach ($sqls as $sql) {
			$script .= $this->convertStatementToScript($sql);
		}

		return $script;
	}

	/**
	 * Create a unique name for the temporary table
	 *
	 * @param string $name
	 * @return string
	 */
	protected function generateTemporaryTableName($name) {
		return $this->config->getSystemValue('dbtableprefix', 'oc_') . $name . '_' . $this->random->generate(13, ISecureRandom::CHAR_LOWER . ISecureRandom::CHAR_DIGITS);
	}

	/**
	 * @param \Doctrine\DBAL\Schema\Table $table
	 * @param string $newName
	 * @return \Doctrine\DBAL\Schema\Table
	 */
	protected function renameTableSchema(Table $table, $newName) {
		/**
		 * @var \Doctrine\DBAL\Schema\Index[] $indexes
		 */
		$indexes = $table->getIndexes();
		$newIndexes = [];
		foreach ($indexes as $index) {
			if ($index->isPrimary()) {
				// do not rename primary key
				$indexName = $index->getName();
			} else {
				// avoid conflicts in index names
				$indexName = $this->config->getSystemValue('dbtableprefix', 'oc_') . $this->random->generate(13, ISecureRandom::CHAR_LOWER);
			}
			$newIndexes[] = new Index($indexName, $index->getColumns(), $index->isUnique(), $index->isPrimary());
		}

		// foreign keys are not supported so we just set it to an empty array
		return new Table($newName, $table->getColumns(), $newIndexes, [], 0, $table->getOptions());
	}

	public function createSchema() {
		$filterExpression = $this->getFilterExpression();
		$this->connection->getConfiguration()->setFilterSchemaAssetsExpression($filterExpression);
		return $this->connection->getSchemaManager()->createSchema();
	}

	/**
	 * @param Schema $targetSchema
	 * @param \Doctrine\DBAL\Connection $connection
	 * @return \Doctrine\DBAL\Schema\SchemaDiff
	 * @throws DBALException
	 */
	protected function getDiff(Schema $targetSchema, \Doctrine\DBAL\Connection $connection) {
		// adjust varchar columns with a length higher then getVarcharMaxLength to clob
		foreach ($targetSchema->getTables() as $table) {
			foreach ($table->getColumns() as $column) {
				if ($column->getType() instanceof StringType) {
					if ($column->getLength() > $connection->getDatabasePlatform()->getVarcharMaxLength()) {
						$column->setType(Type::getType('text'));
						$column->setLength(null);
					}
				}
			}
		}

		$filterExpression = $this->getFilterExpression();
		$this->connection->getConfiguration()->setFilterSchemaAssetsExpression($filterExpression);
		$sourceSchema = $connection->getSchemaManager()->createSchema();

		// remove tables we don't know about
		/** @var $table \Doctrine\DBAL\Schema\Table */
		foreach ($sourceSchema->getTables() as $table) {
			if (!$targetSchema->hasTable($table->getName())) {
				$sourceSchema->dropTable($table->getName());
			}
		}
		// remove sequences we don't know about
		foreach ($sourceSchema->getSequences() as $table) {
			if (!$targetSchema->hasSequence($table->getName())) {
				$sourceSchema->dropSequence($table->getName());
			}
		}

		$comparator = new Comparator();
		return $comparator->compare($sourceSchema, $targetSchema);
	}

	/**
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 * @param \Doctrine\DBAL\Connection $connection
	 */
	protected function applySchema(Schema $targetSchema, \Doctrine\DBAL\Connection $connection = null) {
		if ($connection === null) {
			$connection = $this->connection;
		}

		$schemaDiff = $this->getDiff($targetSchema, $connection);

		$connection->beginTransaction();
		$sqls = $schemaDiff->toSql($connection->getDatabasePlatform());
		$step = 0;
		foreach ($sqls as $sql) {
			$this->emit($sql, $step++, \count($sqls));
			$connection->query($sql);
		}
		$connection->commit();
	}

	/**
	 * @param string $sourceName
	 * @param string $targetName
	 */
	protected function copyTable($sourceName, $targetName) {
		$quotedSource = $this->connection->quoteIdentifier($sourceName);
		$quotedTarget = $this->connection->quoteIdentifier($targetName);

		$this->connection->exec('CREATE TABLE ' . $quotedTarget . ' (LIKE ' . $quotedSource . ')');
		$this->connection->exec('INSERT INTO ' . $quotedTarget . ' SELECT * FROM ' . $quotedSource);
	}

	/**
	 * @param string $name
	 */
	protected function dropTable($name) {
		$this->connection->exec('DROP TABLE ' . $this->connection->quoteIdentifier($name));
	}

	/**
	 * @param $statement
	 * @return string
	 */
	protected function convertStatementToScript($statement) {
		$script = $statement . ';';
		$script .= PHP_EOL;
		$script .= PHP_EOL;
		return $script;
	}

	protected function getFilterExpression() {
		return '/^' . \preg_quote($this->config->getSystemValue('dbtableprefix', 'oc_')) . '/';
	}

	protected function emit($sql, $step, $max) {
		if ($this->noEmit) {
			return;
		}
		if ($this->dispatcher === null) {
			return;
		}
		$this->dispatcher->dispatch(new GenericEvent($sql, [$step+1, $max]), '\OC\DB\Migrator::executeSql');
	}

	private function emitCheckStep($tableName, $step, $max) {
		if ($this->dispatcher === null) {
			return;
		}
		$this->dispatcher->dispatch(new GenericEvent($tableName, [$step+1, $max]), '\OC\DB\Migrator::checkTable');
	}
}
