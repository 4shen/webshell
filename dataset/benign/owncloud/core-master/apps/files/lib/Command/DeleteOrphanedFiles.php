<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace OCA\Files\Command;

use OCP\IDBConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete all file entries that have no matching entries in the storage table.
 */
class DeleteOrphanedFiles extends Command {
	const CHUNK_SIZE = 200;

	/**
	 * @var IDBConnection
	 */
	protected $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('files:cleanup')
			->setDescription('Deletes orphaned file cache entries.');
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$deletedEntries = 0;

		$query = $this->connection->getQueryBuilder();
		$query->select('fc.fileid')
			->from('filecache', 'fc')
			->where($query->expr()->isNull('s.numeric_id'))
			->leftJoin('fc', 'storages', 's', $query->expr()->eq('fc.storage', 's.numeric_id'))
			->setMaxResults(self::CHUNK_SIZE);

		$deleteQuery = $this->connection->getQueryBuilder();
		$deleteQuery->delete('filecache')
			->where($deleteQuery->expr()->eq('fileid', $deleteQuery->createParameter('objectid')));

		$deletedInLastChunk = self::CHUNK_SIZE;
		while ($deletedInLastChunk === self::CHUNK_SIZE) {
			$deletedInLastChunk = 0;
			$result = $query->execute();
			while ($row = $result->fetch()) {
				$deletedInLastChunk++;
				$deletedEntries += $deleteQuery->setParameter('objectid', (int) $row['fileid'])
					->execute();
			}
			$result->closeCursor();
		}

		$output->writeln("$deletedEntries orphaned file cache entries deleted");
	}
}
