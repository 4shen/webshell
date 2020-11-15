<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
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
namespace OC\SystemTag;

use OCP\IServerContainer;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagManagerFactory;
use OCP\SystemTag\ISystemTagObjectMapper;

/**
 * Default factory class for system tag managers
 *
 * @package OCP\SystemTag
 * @since 9.0.0
 */
class ManagerFactory implements ISystemTagManagerFactory {

	/**
	 * Server container
	 *
	 * @var IServerContainer
	 */
	private $serverContainer;

	/**
	 * Constructor for the system tag manager factory
	 *
	 * @param IServerContainer $serverContainer server container
	 */
	public function __construct(IServerContainer $serverContainer) {
		$this->serverContainer = $serverContainer;
	}

	/**
	 * Creates and returns an instance of the system tag manager
	 *
	 * @return ISystemTagManager
	 * @since 9.0.0
	 */
	public function getManager() {
		return new SystemTagManager(
			$this->serverContainer->getDatabaseConnection(),
			$this->serverContainer->getGroupManager(),
			$this->serverContainer->getEventDispatcher()
		);
	}

	/**
	 * Creates and returns an instance of the system tag object
	 * mapper
	 *
	 * @return ISystemTagObjectMapper
	 * @since 9.0.0
	 */
	public function getObjectMapper() {
		return new SystemTagObjectMapper(
			$this->serverContainer->getDatabaseConnection(),
			$this->getManager(),
			$this->serverContainer->getEventDispatcher()
		);
	}
}
