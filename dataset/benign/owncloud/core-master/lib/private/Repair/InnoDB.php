<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OC\Repair;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class InnoDB implements IRepairStep {
	public function getName() {
		return 'Repair MySQL database engine';
	}

	/**
	 * Fix mime types
	 *
	 * @param IOutput $output
	 */
	public function run(IOutput $output) {
		$connection = \OC::$server->getDatabaseConnection();
		if (!$connection->getDatabasePlatform() instanceof MySqlPlatform) {
			$output->info('Not a mysql database -> nothing to do');
			return;
		}

		$tables = $this->getAllMyIsamTables($connection);
		if (\is_array($tables)) {
			foreach ($tables as $table) {
				// On a MySqlPlatform we should have an exec method
				/* @phan-suppress-next-line PhanUndeclaredMethod */
				$connection->exec("ALTER TABLE $table ENGINE=InnoDB;");
				$output->info("Fixed $table");
			}
		}
	}

	/**
	 * @param \Doctrine\DBAL\Connection $connection
	 * @return string[]
	 */
	private function getAllMyIsamTables($connection) {
		$dbPrefix = \OC::$server->getConfig()->getSystemValue("dbtableprefix");
		$dbName = \OC::$server->getConfig()->getSystemValue("dbname");
		$result = $connection->fetchArray(
			"SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND engine = ? AND TABLE_NAME LIKE ?",
			[$dbName, 'MyISAM', $dbPrefix.'%']
		);

		return $result;
	}
}
