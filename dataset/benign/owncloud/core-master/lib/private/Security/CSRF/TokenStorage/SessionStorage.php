<?php
/**
 * @author Lukas Reschke <lukas@statuscode.ch>
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

namespace OC\Security\CSRF\TokenStorage;

use OCP\ISession;

/**
 * Class SessionStorage provides the session storage
 *
 * @package OC\Security\CSRF\TokenStorage
 */
class SessionStorage {
	/** @var ISession */
	private $session;

	/**
	 * @param ISession $session
	 */
	public function __construct(ISession $session = null) {
		$this->session = $session;
	}

	/**
	 * Returns the current token or throws an exception if none is found.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getToken() {
		$token = $this->session->get('requesttoken');
		if (empty($token)) {
			throw new \Exception('Session does not contain a requesttoken');
		}

		return $token;
	}

	/**
	 * Set the valid current token to $value.
	 *
	 * @param string $value
	 */
	public function setToken($value) {
		$this->session->set('requesttoken', $value);
	}

	/**
	 * Removes the current token.
	 */
	public function removeToken() {
		$this->session->remove('requesttoken');
	}
	/**
	 * Whether the storage has a storage.
	 *
	 * @return bool
	 */
	public function hasToken() {
		return $this->session->exists('requesttoken');
	}
}
