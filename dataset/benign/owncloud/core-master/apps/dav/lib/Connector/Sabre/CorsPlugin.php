<?php
/**
 * @author Noveen Sachdeva <noveen.sachdeva@research.iiit.ac.in>
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

use OCP\IUserSession;
use OCP\Util;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * Class CorsPlugin is a plugin which adds CORS headers to the responses
 */
class CorsPlugin extends ServerPlugin {

	/**
	 * Reference to main server object
	 *
	 * @var \Sabre\DAV\Server
	 */
	private $server;

	/**
	 * Reference to logged in user's session
	 *
	 * @var IUserSession
	 */
	private $userSession;

	/** @var array */
	private $extraHeaders;
	/**
	 * @var bool
	 */
	private $alreadyExecuted = false;

	/**
	 * @param IUserSession $userSession
	 */
	public function __construct(IUserSession $userSession) {
		$this->userSession = $userSession;
	}

	private function getExtraHeaders(RequestInterface $request) {
		if ($this->extraHeaders === null) {
			if ($this->userSession->getUser() === null) {
				$this->extraHeaders['Access-Control-Allow-Methods'] = [
					'OPTIONS',
					'GET',
					'HEAD',
					'DELETE',
					'PROPFIND',
					'PUT',
					'PROPPATCH',
					'COPY',
					'MOVE',
					'REPORT'
				];
			} else {
				$this->extraHeaders['Access-Control-Allow-Methods'] = $this->server->getAllowedMethods($request->getPath());
			}
		}
		return $this->extraHeaders;
	}

	/**
	 * This initializes the plugin.
	 *
	 * This function is called by \Sabre\DAV\Server, after
	 * addPlugin is called.
	 *
	 * This method should set up the required event subscriptions.
	 *
	 * @param \Sabre\DAV\Server $server
	 * @return void
	 */
	public function initialize(\Sabre\DAV\Server $server) {
		$this->server = $server;

		$request = $this->server->httpRequest;
		if (!$request->hasHeader('Origin')) {
			return;
		}
		$originHeader = $request->getHeader('Origin');
		if ($this->ignoreOriginHeader($originHeader)) {
			return;
		}
		if (Util::isSameDomain($originHeader, $request->getAbsoluteUrl())) {
			return;
		}

		$this->server->on('beforeMethod:*', [$this, 'setCorsHeaders']);
		$this->server->on('exception', [$this, 'onException']);
		$this->server->on('beforeMethod:OPTIONS', [$this, 'setOptionsRequestHeaders'], 5);
	}

	public function onException(\Throwable $ex) {
		$this->setCorsHeaders($this->server->httpRequest, $this->server->httpResponse);
	}
	/**
	 * This method sets the cors headers for all requests
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @return void
	 */
	public function setCorsHeaders(RequestInterface $request, ResponseInterface $response) {
		if ($request->getHeader('origin') === null) {
			return;
		}
		if ($this->alreadyExecuted) {
			return;
		}
		$this->alreadyExecuted = true;
		$requesterDomain = $request->getHeader('origin');
		// unauthenticated request shall add cors headers as well
		$userId = null;
		if ($this->userSession->getUser() !== null) {
			$userId = $this->userSession->getUser()->getUID();
		}

		$headers = \OC_Response::setCorsHeaders($userId, $requesterDomain, null, $this->getExtraHeaders($request));
		foreach ($headers as $key => $value) {
			$response->addHeader($key, \implode(',', $value));
		}
	}

	/**
	 * Handles the OPTIONS request
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 *
	 * @return false
	 * @throws \InvalidArgumentException
	 */
	public function setOptionsRequestHeaders(RequestInterface $request, ResponseInterface $response) {
		$authorization = $request->getHeader('Authorization');
		if ($authorization === null || $authorization === '') {
			// Set the proper response
			$response->setStatus(200);
			$response = \OC_Response::setOptionsRequestHeaders($response, $this->getExtraHeaders($request));

			// Since All OPTIONS requests are unauthorized, we will have to return false from here
			// If we don't return false, due to no authorization, a 401-Unauthorized will be thrown
			// Which we don't want here
			// Hence this sendResponse
			$this->server->sapi->sendResponse($response);
			return false;
		}
	}

	/**
	 * in addition to schemas used by extensions we ignore empty origin header
	 * values as well as 'null' which is not valid by the specification but used
	 * by some clients.
	 * @link https://github.com/owncloud/core/pull/32120#issuecomment-407008243
	 *
	 * @param string $originHeader
	 * @return bool
	 */
	public function ignoreOriginHeader($originHeader) {
		if (\in_array($originHeader, ['', null, 'null'], true)) {
			return true;
		}
		$schema = \parse_url($originHeader, PHP_URL_SCHEME);
		return \in_array(\strtolower($schema), ['moz-extension', 'chrome-extension']);
	}
}
