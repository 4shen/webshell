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

namespace OC\DB;

use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\Common\EventSubscriber;

class SQLiteSessionInit implements EventSubscriber {
	/**
	 * @var bool
	 */
	private $caseSensitiveLike;

	/**
	 * @var string
	 */
	private $journalMode;

	/**
	 * Configure case sensitive like for each connection
	 *
	 * @param bool $caseSensitiveLike
	 * @param string $journalMode
	 */
	public function __construct($caseSensitiveLike, $journalMode) {
		$this->caseSensitiveLike = $caseSensitiveLike;
		$this->journalMode = $journalMode;
	}

	/**
	 * @param ConnectionEventArgs $args
	 * @return void
	 */
	public function postConnect(ConnectionEventArgs $args) {
		$sensitive = ($this->caseSensitiveLike) ? 'true' : 'false';
		$args->getConnection()->executeUpdate('PRAGMA case_sensitive_like = ' . $sensitive);
		$args->getConnection()->executeUpdate('PRAGMA journal_mode = ' . $this->journalMode);
	}

	public function getSubscribedEvents() {
		return [Events::postConnect];
	}
}
