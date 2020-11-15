<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Tom Needham <tom@owncloud.com>
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
use OCP\API;
use OCP\AppFramework\Http;
use OCP\Authentication\Exceptions\AccountCheckException;

/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Björn Schießle <schiessle@owncloud.com>
 * @author Joas Schilling <nickvergessen@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Tom Needham <tom@owncloud.com>
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

class OC_API {

	/**
	 * API authentication levels
	 */

	/** @deprecated Use \OCP\API::GUEST_AUTH instead */
	const GUEST_AUTH = 0;

	/** @deprecated Use \OCP\API::USER_AUTH instead */
	const USER_AUTH = 1;

	/** @deprecated Use \OCP\API::SUBADMIN_AUTH instead */
	const SUBADMIN_AUTH = 2;

	/** @deprecated Use \OCP\API::ADMIN_AUTH instead */
	const ADMIN_AUTH = 3;

	/**
	 * API Response Codes
	 */

	/** @deprecated Use \OCP\API::RESPOND_UNAUTHORISED instead */
	const RESPOND_UNAUTHORISED = 997;

	/** @deprecated Use \OCP\API::RESPOND_SERVER_ERROR instead */
	const RESPOND_SERVER_ERROR = 996;

	/** @deprecated Use \OCP\API::RESPOND_NOT_FOUND instead */
	const RESPOND_NOT_FOUND = 998;

	/** @deprecated Use \OCP\API::RESPOND_UNKNOWN_ERROR instead */
	const RESPOND_UNKNOWN_ERROR = 999;

	/**
	 * api actions
	 */
	protected static $actions = [];
	private static $logoutRequired = false;
	private static $isLoggedIn = false;

	/**
	 * registers an api call
	 * @param string $method the http method
	 * @param string $url the url to match
	 * @param callable $action the function to run
	 * @param string $app the id of the app registering the call
	 * @param int $authLevel the level of authentication required for the call
	 * @param array $defaults
	 * @param array $requirements
	 * @param boolean $cors whether to enable cors for this route
	 */
	public static function register($method, $url, $action, $app,
				$authLevel = API::USER_AUTH,
				$defaults = [],
				$requirements = [], $cors = true) {
		$name = \strtolower($method).$url;
		$name = \str_replace(['/', '{', '}'], '_', $name);
		if (!isset(self::$actions[$name])) {
			$oldCollection = OC::$server->getRouter()->getCurrentCollection();
			OC::$server->getRouter()->useCollection('ocs');
			OC::$server->getRouter()->create($name, $url)
				->method($method)
				->defaults($defaults)
				->requirements($requirements)
				->action('OC_API', 'call');
			self::$actions[$name] = [];
			OC::$server->getRouter()->useCollection($oldCollection);
		}
		self::$actions[$name][] = ['app' => $app, 'action' => $action, 'authlevel' => $authLevel, 'cors' => $cors];
	}

	/**
	 * handles an api call
	 * @param array $parameters
	 */
	public static function call($parameters) {
		$request = \OC::$server->getRequest();
		$method = $request->getMethod();

		// Prepare the request variables
		if ($method === 'PUT') {
			$parameters['_put'] = $request->getParams();
		} elseif ($method === 'DELETE') {
			$parameters['_delete'] = $request->getParams();
		}
		$name = $parameters['_route'];
		// Foreach registered action
		$responses = [];
		foreach (self::$actions[$name] as $action) {
			// Check authentication and availability
			if (!self::isAuthorised($action)) {
				$responses[] = [
					'app' => $action['app'],
					'response' => new \OC\OCS\Result(null, API::RESPOND_UNAUTHORISED, 'Unauthorised'),
					'shipped' => OC_App::isShipped($action['app']),
				];
				continue;
			}
			if (!\is_callable($action['action'])) {
				$responses[] = [
					'app' => $action['app'],
					'response' => new \OC\OCS\Result(null, API::RESPOND_NOT_FOUND, 'Api method not found'),
					'shipped' => OC_App::isShipped($action['app']),
				];
				continue;
			}
			// Run the action
			$responses[] = [
				'app' => $action['app'],
				'response' => \call_user_func($action['action'], $parameters),
				'shipped' => OC_App::isShipped($action['app']),
			];
		}
		$response = self::mergeResponses($responses);

		// If CORS is set to active for some method, try to add CORS headers
		if (self::$actions[$name][0]['cors'] &&
			\OC::$server->getRequest()->getHeader('Origin') !== null) {
			$requesterDomain = \OC::$server->getRequest()->getHeader('Origin');

			// unauthenticated request shall add cors headers as well
			$userId = null;
			if (\OC::$server->getUserSession()->getUser() !== null) {
				$userId = \OC::$server->getUserSession()->getUser()->getUID();
			}
			$headers = \OC_Response::setCorsHeaders($userId, $requesterDomain);
			foreach ($headers as $key => $value) {
				$response->addHeader($key, \implode(',', $value));
			}
		}

		$format = self::requestedFormat();
		if (self::$logoutRequired) {
			\OC::$server->getUserSession()->logout();
		}

		self::respond($response, $format);
	}

	/**
	 * merge the returned result objects into one response
	 * @param array $responses
	 * @return \OC\OCS\Result
	 */
	public static function mergeResponses($responses) {
		// Sort into shipped and third-party
		$shipped = [
			'succeeded' => [],
			'failed' => [],
		];
		$thirdparty = [
			'succeeded' => [],
			'failed' => [],
		];

		foreach ($responses as $response) {
			if ($response['shipped'] || ($response['app'] === 'core')) {
				if ($response['response']->succeeded()) {
					$shipped['succeeded'][$response['app']] = $response;
				} else {
					$shipped['failed'][$response['app']] = $response;
				}
			} else {
				if ($response['response']->succeeded()) {
					$thirdparty['succeeded'][$response['app']] = $response;
				} else {
					$thirdparty['failed'][$response['app']] = $response;
				}
			}
		}

		// Remove any error responses if there is one shipped response that succeeded
		if (!empty($shipped['failed'])) {
			// Which shipped response do we use if they all failed?
			// They may have failed for different reasons (different status codes)
			// Which response code should we return?
			// Maybe any that are not \OCP\API::RESPOND_SERVER_ERROR
			// Merge failed responses if more than one
			$data = [];
			foreach ($shipped['failed'] as $failure) {
				$data = \array_merge_recursive($data, $failure['response']->getData());
			}
			$picked = \reset($shipped['failed']);
			$code = $picked['response']->getStatusCode();
			$meta = $picked['response']->getMeta();
			$headers = $picked['response']->getHeaders();
			$response = new \OC\OCS\Result($data, $code, $meta['message'], $headers);
			return $response;
		} elseif (!empty($shipped['succeeded'])) {
			$responses = \array_merge($shipped['succeeded'], $thirdparty['succeeded']);
		} elseif (!empty($thirdparty['failed'])) {
			// Merge failed responses if more than one
			$data = [];
			foreach ($thirdparty['failed'] as $failure) {
				$data = \array_merge_recursive($data, $failure['response']->getData());
			}
			$picked = \reset($thirdparty['failed']);
			$code = $picked['response']->getStatusCode();
			$meta = $picked['response']->getMeta();
			$headers = $picked['response']->getHeaders();
			$response = new \OC\OCS\Result($data, $code, $meta['message'], $headers);
			return $response;
		} else {
			$responses = $thirdparty['succeeded'];
		}
		// Merge the successful responses
		$data = [];
		$codes = [];
		$header = [];

		foreach ($responses as $response) {
			if ($response['shipped']) {
				$data = \array_merge_recursive($response['response']->getData(), $data);
			} else {
				$data = \array_merge_recursive($data, $response['response']->getData());
			}
			$header = \array_merge_recursive($header, $response['response']->getHeaders());
			$codes[] = ['code' => $response['response']->getStatusCode(),
				'meta' => $response['response']->getMeta()];
		}

		// Use any non 100 status codes
		$statusCode = 100;
		$statusMessage = null;
		foreach ($codes as $code) {
			if ($code['code'] != 100) {
				$statusCode = $code['code'];
				$statusMessage = $code['meta']['message'];
				break;
			}
		}

		return new \OC\OCS\Result($data, $statusCode, $statusMessage, $header);
	}

	/**
	 * authenticate the api call
	 * @param array $action the action details as supplied to OC_API::register()
	 * @return bool
	 */
	private static function isAuthorised($action) {
		$level = $action['authlevel'];
		switch ($level) {
			case API::GUEST_AUTH:
				// Anyone can access
				return true;
			case API::USER_AUTH:
				// User required
				return self::loginUser();
			case API::SUBADMIN_AUTH:
				// Check for subadmin
				$user = self::loginUser();
				if (!$user) {
					return false;
				} else {
					$userObject = \OC::$server->getUserSession()->getUser();
					if ($userObject === null) {
						return false;
					}
					$isSubAdmin = \OC::$server->getGroupManager()->getSubAdmin()->isSubAdmin($userObject);
					$admin = OC_User::isAdminUser($user);
					if ($isSubAdmin || $admin) {
						return true;
					} else {
						return false;
					}
				}
				// no break
			case API::ADMIN_AUTH:
				// Check for admin
				$user = self::loginUser();
				if (!$user) {
					return false;
				} else {
					return OC_User::isAdminUser($user);
				}
				// no break
			default:
				// oops looks like invalid level supplied
				return false;
		}
	}

	/**
	 * http basic auth
	 * @return string|false (username, or false on failure)
	 */
	private static function loginUser() {
		if (self::$isLoggedIn === true) {
			return \OC_User::getUser();
		}

		// reuse existing login
		$userSession = \OC::$server->getUserSession();
		$request = \OC::$server->getRequest();
		$loggedIn = $userSession->isLoggedIn();
		if ($loggedIn === true) {
			if (\OC::$server->getTwoFactorAuthManager()->needsSecondFactor()) {
				// Do not allow access to OCS until the 2FA challenge was solved successfully
				return false;
			}
			try {
				\OC::$server->getAccountModuleManager()->check($userSession->getUser());
			} catch (AccountCheckException $ex) {
				// Deny login if any IAuthModule check fails
				return false;
			}
			if ($userSession->verifyAuthHeaders($request)) {
				$ocsApiRequest = isset($_SERVER['HTTP_OCS_APIREQUEST']) ? $_SERVER['HTTP_OCS_APIREQUEST'] === 'true' : false;
				if ($ocsApiRequest) {

					// initialize the user's filesystem
					\OC_Util::setupFS(\OC_User::getUser());
					self::$isLoggedIn = true;

					return OC_User::getUser();
				}

				return false;
			}
		}

		// basic auth - because OC_User::login will create a new session we shall only try to login
		// if user and pass are set
		try {
			if (OC_User::handleApacheAuth()) {
				self::$logoutRequired = false;
			} elseif ($userSession->tryTokenLogin($request)
				|| $userSession->tryAuthModuleLogin($request)
				|| $userSession->tryBasicAuthLogin($request)) {
				self::$logoutRequired = true;
			} else {
				return false;
			}
			try {
				\OC::$server->getAccountModuleManager()->check($userSession->getUser());
			} catch (AccountCheckException $ex) {
				// Deny login if any IAuthModule check fails
				return false;
			}
			// initialize the user's filesystem
			\OC_Util::setupFS(\OC_User::getUser());
			self::$isLoggedIn = true;

			return \OC_User::getUser();
		} catch (\OC\User\LoginException $e) {
			return false;
		}
	}

	/**
	 * respond to a call
	 * @param \OC\OCS\Result $result
	 * @param string $format the format xml|json
	 */
	public static function respond($result, $format='xml') {
		$request = \OC::$server->getRequest();

		// Send 401 headers if unauthorised
		if ($result->getStatusCode() === API::RESPOND_UNAUTHORISED) {
			// If request comes from JS return dummy auth request
			if ($request->getHeader('X-Requested-With') === 'XMLHttpRequest') {
				\header('WWW-Authenticate: DummyBasic realm="Authorisation Required"');
			} else {
				\header('WWW-Authenticate: Basic realm="Authorisation Required"');
			}
			\http_response_code(401);
		}

		foreach ($result->getHeaders() as $name => $value) {
			\header($name . ': ' . $value);
		}

		$meta = $result->getMeta();
		$data = $result->getData();
		if (self::isV2($request)) {
			$statusCode = self::mapStatusCodes($result->getStatusCode());
			if ($statusCode !== null) {
				$meta['statuscode'] = $statusCode;
				OC_Response::setStatus($statusCode);
			}
		}

		self::setContentType($format);
		$body = self::renderResult($format, $meta, $data);
		echo $body;
	}

	/**
	 * @param XMLWriter $writer
	 */
	private static function toXML($array, $writer) {
		foreach ($array as $k => $v) {
			if (isset($k[0]) && ($k[0] === '@')) {
				if (\is_array($v)) {
					foreach ($v as $name => $value) {
						$writer->writeAttribute($name, $value);
					}
				} else {
					$writer->writeAttribute(\substr($k, 1), $v);
				}
				continue;
			} elseif (\is_numeric($k)) {
				$k = 'element';
			}
			if (\is_array($v)) {
				$writer->startElement($k);
				self::toXML($v, $writer);
				$writer->endElement();
			} else {
				$writer->writeElement($k, $v);
			}
		}
	}

	/**
	 * @return string
	 */
	public static function requestedFormat() {
		$formats = ['json', 'xml'];

		$format = !empty($_GET['format']) && \in_array($_GET['format'], $formats) ? $_GET['format'] : 'xml';
		return $format;
	}

	/**
	 * Based on the requested format the response content type is set
	 * @param string $format
	 */
	public static function setContentType($format = null) {
		$format = $format === null ? self::requestedFormat() : $format;
		if ($format === 'xml') {
			\header('Content-type: text/xml; charset=UTF-8');
			return;
		}

		if ($format === 'json') {
			\header('Content-Type: application/json; charset=utf-8');
			return;
		}

		\header('Content-Type: application/octet-stream; charset=utf-8');
	}

	/**
	 * @param \OCP\IRequest $request
	 * @return bool
	 */
	protected static function isV2(\OCP\IRequest $request) {
		$script = $request->getScriptName();

		return \substr($script, -11) === '/ocs/v2.php';
	}

	/**
	 * @param integer $sc
	 * @return int
	 */
	public static function mapStatusCodes($sc) {
		switch ($sc) {
			case API::RESPOND_NOT_FOUND:
				return Http::STATUS_NOT_FOUND;
			case API::RESPOND_SERVER_ERROR:
				return Http::STATUS_INTERNAL_SERVER_ERROR;
			case API::RESPOND_UNKNOWN_ERROR:
				return Http::STATUS_INTERNAL_SERVER_ERROR;
			case API::RESPOND_UNAUTHORISED:
				// already handled for v1
				return null;
			case 100:
				return Http::STATUS_OK;
			case 104:
				return Http::STATUS_FORBIDDEN;
		}
		// any 2xx, 4xx and 5xx will be used as is
		if ($sc >= 200 && $sc < 600) {
			return $sc;
		}

		return Http::STATUS_BAD_REQUEST;
	}

	/**
	 * @param string $format
	 * @return string
	 */
	public static function renderResult($format, $meta, $data) {
		$response = [
			'ocs' => [
				'meta' => $meta,
				'data' => $data,
			],
		];
		if ($format == 'json') {
			return OC_JSON::encode($response);
		}

		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument();
		self::toXML($response, $writer);
		$writer->endDocument();
		return $writer->outputMemory(true);
	}

	/**
	 * Called when a not existing OCS endpoint has been called
	 */
	public static function notFound() {
		$format = \OC::$server->getRequest()->getParam('format', 'xml');
		$txt='Invalid query, please check the syntax. API specifications are here:'
			.' http://www.freedesktop.org/wiki/Specifications/open-collaboration-services. DEBUG OUTPUT:'."\n";
		OC_API::respond(new \OC\OCS\Result(null, API::RESPOND_UNKNOWN_ERROR, $txt), $format);
	}
}
