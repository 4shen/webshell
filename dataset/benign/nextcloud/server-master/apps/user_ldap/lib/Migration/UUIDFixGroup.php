<?php
/**
 * @copyright Copyright (c) 2017 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Vinicius Cubas Brand <vinicius@eita.org.br>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\User_LDAP\Migration;

use OCA\User_LDAP\Helper;
use OCA\User_LDAP\LDAP;
use OCA\User_LDAP\Mapping\GroupMapping;
use OCA\User_LDAP\User_Proxy;
use OCP\IConfig;

class UUIDFixGroup extends UUIDFix {
	public function __construct(GroupMapping $mapper, LDAP $ldap, IConfig $config, Helper $helper) {
		$this->mapper = $mapper;
		$this->proxy = new User_Proxy($helper->getServerConfigurationPrefixes(true), $ldap, $config,
			\OC::$server->getNotificationManager(), \OC::$server->getUserSession(),
			\OC::$server->query('LDAPUserPluginManager'));
	}
}
