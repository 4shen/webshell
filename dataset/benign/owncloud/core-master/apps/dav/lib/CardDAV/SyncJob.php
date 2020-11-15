<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\DAV\CardDAV;

use OC\BackgroundJob\TimedJob;
use OCA\DAV\AppInfo\Application;

class SyncJob extends TimedJob {
	public function __construct() {
		// Run once a day
		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument) {
		$app = new Application();
		/** @var SyncService $ss */
		$ss = $app->getSyncService();
		$ss->syncInstance();
	}
}
