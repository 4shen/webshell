<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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
namespace OC\Comments;

use OCP\Comments\ICommentsManager;
use OCP\Comments\ICommentsManagerFactory;
use OCP\IServerContainer;

class ManagerFactory implements ICommentsManagerFactory {

	/**
	 * Server container
	 *
	 * @var IServerContainer
	 */
	private $serverContainer;

	/**
	 * Constructor for the comments manager factory
	 *
	 * @param IServerContainer $serverContainer server container
	 */
	public function __construct(IServerContainer $serverContainer) {
		$this->serverContainer = $serverContainer;
	}

	/**
	 * creates and returns an instance of the ICommentsManager
	 *
	 * @return ICommentsManager
	 * @since 9.0.0
	 */
	public function getManager() {
		return new Manager(
			$this->serverContainer->getDatabaseConnection(),
			$this->serverContainer->getLogger(),
			$this->serverContainer->getConfig(),
			$this->serverContainer->getEventDispatcher()
		);
	}
}
