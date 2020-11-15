<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Piotr Mrowczynski <piotr@owncloud.com>
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

namespace OC\Diagnostics;

use OCP\Diagnostics\IQueryLogger;

class QueryLogger implements IQueryLogger {
	/**
	 * @var \OC\Diagnostics\Query
	 */
	protected $activeQuery;

	/**
	 * @var \OC\Diagnostics\Query[]
	 */
	protected $queries = [];

	/**
	 * @var bool - Module needs to be activated by some app
	 */
	private $activated = false;

	/**
	 * @inheritdoc
	 */
	public function startQuery($sql, array $params = null, array $types = null) {
		if ($this->activated) {
			$this->activeQuery = new Query($sql, $params, \microtime(true));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function stopQuery() {
		if ($this->activated && $this->activeQuery) {
			$this->activeQuery->end(\microtime(true));
			$this->queries[] = $this->activeQuery;
			$this->activeQuery = null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getQueries() {
		return $this->queries;
	}

	/**
	 * @inheritdoc
	 */
	public function activate() {
		$this->activated = true;
	}
}
