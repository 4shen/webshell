<?php
/**
 * @author Christoph Wurst <christoph@owncloud.com>
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

namespace OCA\DAV\Connector\Sabre\Exception;

use DOMElement;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Server;

class PasswordLoginForbidden extends NotAuthenticated {
	const NS_OWNCLOUD = 'http://owncloud.org/ns';

	public function getHTTPCode() {
		return 401;
	}

	/**
	 * This method allows the exception to include additional information
	 * into the WebDAV error response
	 *
	 * @param Server $server
	 * @param DOMElement $errorNode
	 * @return void
	 */
	public function serialize(Server $server, DOMElement $errorNode) {

		// set ownCloud namespace
		$errorNode->setAttribute('xmlns:o', self::NS_OWNCLOUD);

		$error = $errorNode->ownerDocument->createElementNS('o:', 'o:hint', 'password login forbidden');
		$errorNode->appendChild($error);
	}
}
