<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Jakob Sack <mail@jakobsack.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Markus Goetz <markus@woboq.com>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
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
namespace OCA\DAV\Connector\Sabre;

use Exception;
use OC\AppFramework\Http\Request;
use OC\Authentication\Exceptions\PasswordLoginForbiddenException;
use OC\Authentication\TwoFactorAuth\Manager;
use OC\Authentication\AccountModule\Manager as AccountModuleManager;
use OC\User\LoginException;
use OC\User\Session;
use OCA\DAV\Connector\Sabre\Exception\PasswordLoginForbidden;
use OCP\Authentication\Exceptions\AccountCheckException;
use OCP\IRequest;
use OCP\ISession;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Exception\ServiceUnavailable;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class Auth extends AbstractBasic {
	const DAV_AUTHENTICATED = 'AUTHENTICATED_TO_DAV_BACKEND';

	/** @var ISession */
	private $session;
	/** @var Session */
	private $userSession;
	/** @var IRequest */
	private $request;
	/** @var string */
	private $currentUser;
	/** @var Manager */
	private $twoFactorManager;
	/** @var AccountModuleManager */
	private $accountModuleManager;

	/**
	 * @param ISession $session
	 * @param Session $userSession
	 * @param IRequest $request
	 * @param Manager $twoFactorManager
	 * @param AccountModuleManager $accountModuleManager
	 * @param string $principalPrefix
	 */
	public function __construct(ISession $session,
								Session $userSession,
								IRequest $request,
								Manager $twoFactorManager,
								AccountModuleManager $accountModuleManager,
								$principalPrefix = 'principals/users/') {
		$this->session = $session;
		$this->userSession = $userSession;
		$this->twoFactorManager = $twoFactorManager;
		$this->accountModuleManager = $accountModuleManager;
		$this->request = $request;
		$this->principalPrefix = $principalPrefix;

		// setup realm
		$defaults = new \OC_Defaults();
		$this->realm = $defaults->getName();
	}

	/**
	 * Whether the user has initially authenticated via DAV
	 *
	 * This is required for WebDAV clients that resent the cookies even when the
	 * account was changed.
	 *
	 * @see https://github.com/owncloud/core/issues/13245
	 *
	 * @param string $username
	 * @return bool
	 */
	public function isDavAuthenticated($username) {
		return $this->session->get(self::DAV_AUTHENTICATED) !== null &&
		$this->session->get(self::DAV_AUTHENTICATED) === $username;
	}

	/**
	 * Validates a username and password
	 *
	 * This method should return true or false depending on if login
	 * succeeded.
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	protected function validateUserPass($username, $password) {
		if (\trim($username) === '') {
			return false;
		}
		if ($this->userSession->isLoggedIn() &&
			$this->userSession->verifyAuthHeaders($this->request) &&
			$this->isDavAuthenticated($this->userSession->getUser()->getUID())
		) {
			\OC_Util::setupFS($this->userSession->getUser()->getUID());
			$this->session->close();
			return true;
		} else {
			\OC_Util::setupFS(); //login hooks may need early access to the filesystem
			try {
				if ($this->userSession->logClientIn($username, $password, $this->request)) {
					\OC_Util::setupFS($this->userSession->getUser()->getUID());
					$this->session->set(self::DAV_AUTHENTICATED, $this->userSession->getUser()->getUID());
					$this->session->close();
					return true;
				} else {
					$this->session->close();
					return false;
				}
			} catch (PasswordLoginForbiddenException $ex) {
				$this->session->close();
				throw new PasswordLoginForbidden();
			}
		}
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @return array
	 * @throws NotAuthenticated
	 * @throws ServiceUnavailable
	 */
	public function check(RequestInterface $request, ResponseInterface $response) {
		try {
			return $this->auth($request, $response);
		} catch (LoginException $e) {
			throw new NotAuthenticated($e->getMessage(), $e->getCode(), $e);
		} catch (NotAuthenticated $e) {
			throw $e;
		} catch (Exception $e) {
			$class = \get_class($e);
			$msg = $e->getMessage();
			throw new ServiceUnavailable("$class: $msg");
		}
	}

	/**
	 * Checks whether a CSRF check is required on the request
	 *
	 * @return bool
	 */
	private function requiresCSRFCheck() {
		// If not POST no check is required
		if ($this->request->getMethod() !== 'POST') {
			return false;
		}

		// Official ownCloud clients require no checks
		if ($this->request->isUserAgent([
			Request::USER_AGENT_OWNCLOUD_DESKTOP,
			Request::USER_AGENT_OWNCLOUD_ANDROID,
			Request::USER_AGENT_OWNCLOUD_IOS,
		])) {
			return false;
		}

		// If not logged-in no check is required
		if (!$this->userSession->isLoggedIn()) {
			return false;
		}

		// If logged-in AND DAV authenticated no check is required
		if ($this->userSession->isLoggedIn() &&
			$this->isDavAuthenticated($this->userSession->getUser()->getUID())) {
			return false;
		}

		return true;
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @return array
	 * @throws NotAuthenticated
	 * @throws ServiceUnavailable
	 */
	private function auth(RequestInterface $request, ResponseInterface $response) {
		$forcedLogout = false;
		if (!$this->request->passesCSRFCheck() &&
			$this->requiresCSRFCheck()) {
			// In case of a fail with POST we need to recheck the credentials
			$forcedLogout = true;
		}

		if ($forcedLogout) {
			$this->userSession->logout();
		} else {
			if ($this->twoFactorManager->needsSecondFactor()) {
				throw new NotAuthenticated('2FA challenge not passed.');
			}
			if (\OC_User::handleApacheAuth() ||
				//Fix for broken webdav clients
				($this->userSession->isLoggedIn() && $this->session->get(self::DAV_AUTHENTICATED) === null) ||
				//Well behaved clients that only send the cookie are allowed
				($this->userSession->isLoggedIn() && $this->session->get(self::DAV_AUTHENTICATED) === $this->userSession->getUser()->getUID() && ($request->getHeader('Authorization') === null || $request->getHeader('Authorization') === ''))
			) {
				$user = $this->userSession->getUser();
				$this->checkAccountModule($user);
				$uid = $user->getUID();
				\OC_Util::setupFS($uid);
				$this->currentUser = $uid;
				$this->session->close();
				return [true, $this->principalPrefix . $uid];
			}
		}

		$data = parent::check($request, $response);
		if ($data[0] === true) {
			$user = $this->userSession->getUser();
			if ($user === null) {
				throw new \LogicException('Logged in but no user -> :boom:');
			}
			$this->checkAccountModule($user);
			$startPos = \strrpos($data[1], '/') + 1;
			$data[1] = \substr_replace($data[1], $user->getUID(), $startPos);
		}
		return $data;
	}

	/**
	 * @param $user
	 * @throws ServiceUnavailable
	 */
	private function checkAccountModule($user) {
		if ($user === null) {
			throw new \UnexpectedValueException('No user in session');
		}
		try {
			$this->accountModuleManager->check($user);
		} catch (AccountCheckException $ex) {
			throw new ServiceUnavailable($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	public function challenge(RequestInterface $request, ResponseInterface $response) {
		$schema = 'Basic';
		// do not re-authenticate over ajax, use dummy auth name to prevent browser popup
		if (\in_array('XMLHttpRequest', \explode(',', $request->getHeader('X-Requested-With')), true)) {
			$schema = 'DummyBasic';
		}

		$response->addHeader('WWW-Authenticate', "$schema realm=\"{$this->realm}\", charset=\"UTF-8\"");
		$response->setStatus(401);
	}
}
