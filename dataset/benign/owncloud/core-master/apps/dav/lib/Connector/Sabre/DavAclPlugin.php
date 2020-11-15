<?php
/**
 * @author Lukas Reschke <lukas@statuscode.ch>
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

namespace OCA\DAV\Connector\Sabre;

use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\INode;
use Sabre\DAVACL\Plugin;

/**
 * Class DavAclPlugin is a wrapper around \Sabre\DAVACL\Plugin that returns 404
 * responses in case the resource to a response has been forbidden instead of
 * a 403. This is used to prevent enumeration of valid resources.
 *
 * @see https://github.com/owncloud/core/issues/22578
 * @package OCA\DAV\Connector\Sabre
 */
class DavAclPlugin extends Plugin {
	public function __construct() {
		$this->hideNodesFromListings = true;
		$this->allowUnauthenticatedAccess = false;
	}

	public function checkPrivileges($uri, $privileges, $recursion = self::R_PARENT, $throwExceptions = true) {
		// within public-files throwing the exception NeedPrivileges is desired
		$shallThrowExceptions = false;
		$elements = \explode('/', $uri);
		if ($elements[0] === 'public-files') {
			$shallThrowExceptions = true;
		}

		$access = parent::checkPrivileges($uri, $privileges, $recursion, $shallThrowExceptions);
		if ($access === false && $throwExceptions) {
			/** @var INode $node */
			$node = $this->server->tree->getNodeForPath($uri);

			switch (\get_class($node)) {
				case 'OCA\DAV\CardDAV\AddressBook':
					$type = 'Addressbook';
					break;
				default:
					$type = 'Node';
					break;
			}
			throw new NotFound(
				\sprintf(
					"%s with name '%s' could not be found",
					$type,
					$node->getName()
				)
			);
		}

		return $access;
	}
}
