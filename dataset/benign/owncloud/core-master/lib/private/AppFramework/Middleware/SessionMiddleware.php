<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <rullzer@users.noreply.github.com>
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

namespace OC\AppFramework\Middleware;

use OC\AppFramework\Utility\ControllerMethodReflector;
use OCP\IRequest;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\ISession;

class SessionMiddleware extends Middleware {

	/** @var IRequest */
	private $request;

	/** @var ControllerMethodReflector */
	private $reflector;

	/** @var ISession */
	private $session;

	/**
	 * @param IRequest $request
	 * @param ControllerMethodReflector $reflector
	 */
	public function __construct(IRequest $request,
								ControllerMethodReflector $reflector,
								ISession $session
) {
		$this->request = $request;
		$this->reflector = $reflector;
		$this->session = $session;
	}

	/**
	 * @param \OCP\AppFramework\Controller $controller
	 * @param string $methodName
	 */
	public function beforeController($controller, $methodName) {
		$useSession = $this->reflector->hasAnnotation('UseSession');
		if (!$useSession) {
			$this->session->close();
		}
	}

	/**
	 * @param \OCP\AppFramework\Controller $controller
	 * @param string $methodName
	 * @param Response $response
	 * @return Response
	 */
	public function afterController($controller, $methodName, Response $response) {
		$useSession = $this->reflector->hasAnnotation('UseSession');
		if ($useSession) {
			$this->session->close();
		}
		return $response;
	}
}
