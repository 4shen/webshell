<?php
/**
 * @author cetra3 <peter@parashift.com.au>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Phil Davis <phil.davis@inf.org>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OC\Session;

use OC\AppFramework\Http\Request;
use OCP\Session\Exceptions\SessionNotAvailableException;

/**
 * Class Internal
 *
 * wrap php's internal session handling into the Session interface
 *
 * @package OC\Session
 */
class Internal extends Session {
	/**
	 * @param string $name
	 * @throws \Exception
	 */
	public function __construct($name) {
		\session_name($name);
		\set_error_handler([$this, 'trapError']);
		try {
			$this->start();
		} catch (\Exception $e) {
			\setcookie(\session_name(), null, -1, \OC::$WEBROOT ? : '/');
		}
		\restore_error_handler();
		if ($_SESSION === null) {
			throw new \Exception('Failed to start session');
		}
	}

	/**
	 * @param string $key
	 * @param integer $value
	 * @throws \Exception
	 */
	public function set($key, $value) {
		$this->validateSession();
		$_SESSION[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if (!$this->exists($key)) {
			return null;
		}
		return $_SESSION[$key];
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists($key) {
		return isset($_SESSION[$key]);
	}

	/**
	 * @param string $key
	 */
	public function remove($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	public function clear() {
		\session_unset();
		$this->regenerateId();
		@\session_destroy();
		@\session_start();
		$_SESSION = [];
	}

	public function close() {
		\session_write_close();
		parent::close();
	}

	/**
	 * Wrapper around session_regenerate_id
	 *
	 * @param bool $deleteOldSession Whether to delete the old associated session file or not.
	 * @return void
	 */
	public function regenerateId($deleteOldSession = true) {
		@\session_regenerate_id($deleteOldSession);
	}

	/**
	 * Wrapper around session_id
	 *
	 * @return string
	 * @throws SessionNotAvailableException
	 * @since 9.1.0
	 */
	public function getId() {
		$id = @\session_id();
		if ($id === '') {
			throw new SessionNotAvailableException();
		}
		return $id;
	}

	/**
	 * @throws \Exception
	 */
	public function reopen() {
		throw new \Exception('The session cannot be reopened - reopen() is ony to be used in unit testing.');
	}

	/**
	 * @param int $errorNumber
	 * @param string $errorString
	 * @throws \ErrorException
	 */
	public function trapError($errorNumber, $errorString) {
		throw new \ErrorException($errorString);
	}

	/**
	 * @throws \Exception
	 */
	private function validateSession() {
		if ($this->sessionClosed) {
			throw new SessionNotAvailableException('Session has been closed - no further changes to the session are allowed');
		}
	}

	private function start() {
		if (@\session_id() === '') {
			// prevents javascript from accessing php session cookies
			\ini_set('session.cookie_httponly', true);

			// set the cookie path to the ownCloud directory
			$cookie_path = \OC::$WEBROOT ? : '/';
			\ini_set('session.cookie_path', $cookie_path);

			if ($this->getServerProtocol() === 'https') {
				\ini_set('session.cookie_secure', true);
			}

			//try to set the session lifetime
			$sessionLifeTime = self::getSessionLifeTime();
			@\ini_set('session.gc_maxlifetime', (string)$sessionLifeTime);
		}
		\session_start();
	}

	/**
	 * @return string
	 */
	private static function getSessionLifeTime() {
		return \OC::$server->getConfig()->getSystemValue('session_lifetime', 60 * 20);
	}

	private function getServerProtocol() {
		$method = null;
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		$req = new Request(
			[
				'get' => $_GET,
				'post' => $_POST,
				'files' => $_FILES,
				'server' => $_SERVER,
				'env' => $_ENV,
				'cookies' => $_COOKIE,
				'method' => $method,
				'urlParams' => [],
			],
			null,
			\OC::$server->getConfig(),
			null
		);

		return $req->getServerProtocol();
	}
}
