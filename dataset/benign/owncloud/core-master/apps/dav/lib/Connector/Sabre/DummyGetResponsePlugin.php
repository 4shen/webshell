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
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * Class DummyGetResponsePlugin is a plugin used to not show a "Not implemented"
 * error to clients that rely on verifying the functionality of the ownCloud
 * WebDAV backend using a simple GET to /.
 *
 * This is considered a legacy behaviour and implementers should consider sending
 * a PROPFIND request instead to verify whether the WebDAV component is working
 * properly.
 *
 * FIXME: Remove once clients are all compliant.
 *
 * @package OCA\DAV\Connector\Sabre
 */
class DummyGetResponsePlugin extends \Sabre\DAV\ServerPlugin {
	/** @var \Sabre\DAV\Server */
	protected $server;

	/**
	 * @param \Sabre\DAV\Server $server
	 * @return void
	 */
	public function initialize(\Sabre\DAV\Server $server) {
		$this->server = $server;
		$this->server->on('method:GET', [$this, 'httpGet'], 200);
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @return false
	 */
	public function httpGet(RequestInterface $request, ResponseInterface $response) {
		$string = 'This is the WebDAV interface. It can only be accessed by ' .
			'WebDAV clients such as the ownCloud desktop sync client.';
		$stream = \fopen('php://memory', 'r+');
		\fwrite($stream, $string);
		\rewind($stream);

		$response->setStatus(200);
		$response->setBody($stream);

		return false;
	}
}
