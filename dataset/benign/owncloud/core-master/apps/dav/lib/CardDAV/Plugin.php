<?php
/**
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

use OCA\DAV\CardDAV\Xml\Groups;
use Sabre\DAV\INode;
use Sabre\DAV\PropFind;
use Sabre\DAV\Server;

class Plugin extends \Sabre\CardDAV\Plugin {
	public function initialize(Server $server) {
		$server->on('propFind', [$this, 'propFind']);
		parent::initialize($server);
	}

	/**
	 * Returns the addressbook home for a given principal
	 *
	 * @param string $principal
	 * @return string
	 */
	protected function getAddressbookHomeForPrincipal($principal) {
		if (\strrpos($principal, 'principals/users', -\strlen($principal)) !== false) {
			list(, $principalId) = \Sabre\Uri\split($principal);
			return self::ADDRESSBOOK_ROOT . '/users/' . $principalId;
		}
		if (\strrpos($principal, 'principals/groups', -\strlen($principal)) !== false) {
			list(, $principalId) = \Sabre\Uri\split($principal);
			return self::ADDRESSBOOK_ROOT . '/groups/' . $principalId;
		}
		if (\strrpos($principal, 'principals/system', -\strlen($principal)) !== false) {
			list(, $principalId) = \Sabre\Uri\split($principal);
			return self::ADDRESSBOOK_ROOT . '/system/' . $principalId;
		}

		throw new \LogicException('This is not supposed to happen');
	}

	/**
	 * Adds all CardDAV-specific properties
	 *
	 * @param PropFind $propFind
	 * @param INode $node
	 * @return void
	 */
	public function propFind(PropFind $propFind, INode $node) {
		$ns = '{http://owncloud.org/ns}';

		if ($node instanceof AddressBook) {
			$propFind->handle($ns . 'groups', function () use ($node) {
				return new Groups($node->getContactsGroups());
			});
		}
	}
}
