<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\Type;

class SQLiteMigrator extends Migrator {

	/**
	 * @return Schema
	 */
	public function createSchema() {
		$this->registerAdditionalMappings($this->connection);
		return parent::createSchema();
	}

	/**
	 * @param Schema $targetSchema
	 * @param \Doctrine\DBAL\Connection $connection
	 * @return \Doctrine\DBAL\Schema\SchemaDiff
	 */
	protected function getDiff(Schema $targetSchema, \Doctrine\DBAL\Connection $connection) {
		$this->registerAdditionalMappings($connection);

		// with sqlite autoincrement columns is of type integer
		foreach ($targetSchema->getTables() as $table) {
			foreach ($table->getColumns() as $column) {
				if ($column->getType() instanceof BigIntType && $column->getAutoincrement()) {
					$column->setType(Type::getType('integer'));
				}
			}
		}

		return parent::getDiff($targetSchema, $connection);
	}

	/**
	 * @param \Doctrine\DBAL\Connection $connection
	 */
	private function registerAdditionalMappings(\Doctrine\DBAL\Connection $connection) {
		$platform = $connection->getDatabasePlatform();
		$platform->registerDoctrineTypeMapping('tinyint unsigned', 'integer');
		$platform->registerDoctrineTypeMapping('smallint unsigned', 'integer');
		$platform->registerDoctrineTypeMapping('varchar ', 'string');
	}
}
