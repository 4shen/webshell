<?php
/**
 * @author Lukas Reschke <lukas@statuscode.ch>
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

namespace OCA\UpdateNotification;

use OC\AppFramework\Utility\TimeFactory;
use OC\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;

/**
 * Class ResetTokenBackgroundJob deletes any configured token all 24 hours for
 *
 *
 * @package OCA\UpdateNotification
 */
class ResetTokenBackgroundJob extends TimedJob {
	/** @var IConfig */
	private $config;
	/** @var ITimeFactory */
	private $timeFactory;

	/**
	 * @param IConfig|null $config
	 * @param ITimeFactory|null $timeFactory
	 */
	public function __construct(IConfig $config = null,
								ITimeFactory $timeFactory = null) {
		// Run all 10 minutes
		$this->setInterval(60 * 10);

		if ($config instanceof IConfig && $timeFactory instanceof ITimeFactory) {
			$this->config = $config;
			$this->timeFactory = $timeFactory;
		} else {
			$this->fixDIForJobs();
		}
	}

	/**
	 * DI for jobs
	 */
	private function fixDIForJobs() {
		$this->config = \OC::$server->getConfig();
		$this->timeFactory = new TimeFactory();
	}

	/**
	 * @param $argument
	 */
	protected function run($argument) {
		// Delete old tokens after 2 days
		if ($this->timeFactory->getTime() - $this->config->getAppValue('core', 'updater.secret.created', $this->timeFactory->getTime()) >= 172800) {
			$this->config->deleteSystemValue('updater.secret');
		}
	}
}
