<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christopher Schäpers <kondou@ts.unde.re>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Philipp Schaffrath <github@philippschaffrath.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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
try {
	require_once __DIR__ . '/lib/base.php';
	if (\OCP\Util::needUpgrade()) {
		// since the behavior of apps or remotes are unpredictable during
		// an upgrade, return a 503 directly
		OC_Response::setStatus(OC_Response::STATUS_SERVICE_UNAVAILABLE);
		OC_Template::printErrorPage('Service unavailable');
		exit;
	}

	$request = \OC::$server->getRequest();
	OC::checkMaintenanceMode($request);
	OC::checkSingleUserMode(true);
	$pathInfo = $request->getPathInfo();

	if (!$pathInfo && $request->getParam('service', '') === '') {
		\http_response_code(404);
		exit;
	} elseif ($request->getParam('service', '')) {
		$service = $request->getParam('service', '');
	} else {
		$pathInfo = \trim($pathInfo, '/');
		list($service) = \explode('/', $pathInfo);
	}
	$file = \OC::$server->getConfig()->getAppValue('core', 'public_' . \strip_tags($service));
	if ($file === null) {
		\http_response_code(404);
		exit;
	}

	$parts = \explode('/', $file, 2);
	$app = $parts[0];

	// Load all required applications
	\OC::$REQUESTEDAPP = $app;
	OC_App::loadApps(['authentication']);
	OC_App::loadApps(['filesystem', 'logging']);

	if (!\OC::$server->getAppManager()->isInstalled($app)) {
		throw new Exception('App not installed: ' . $app);
	}
	OC_App::loadApp($app);
	OC_User::setIncognitoMode(true);

	$baseuri = OC::$WEBROOT . '/public.php/' . $service . '/';

	require_once OC_App::getAppPath($app) . '/' . $parts[1];
} catch (\Throwable $ex) {
	try {
		if ($ex instanceof \OC\ServiceUnavailableException) {
			OC_Response::setStatus(OC_Response::STATUS_SERVICE_UNAVAILABLE);
		} else {
			OC_Response::setStatus(OC_Response::STATUS_INTERNAL_SERVER_ERROR);
		}
		//show the user a detailed error page
		\OC::$server->getLogger()->logException($ex, ['app' => 'public']);
		OC_Template::printExceptionErrorPage($ex);
	} catch (\Throwable $ex2) {
		// log through the crashLog
		\header("{$_SERVER['SERVER_PROTOCOL']} 599 Broken");
		\OC::crashLog($ex);
		\OC::crashLog($ex2);
	}
}
