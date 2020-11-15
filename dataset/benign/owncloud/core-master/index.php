<?php
/**
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Philipp Schaffrath <github@philippschaffrath.de>
 * @author RealRancor <fisch.666@gmx.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Sergio Bertolín <sbertolin@solidgear.es>
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

// Show warning if a PHP version below 7.2.0 is used, this has to happen here
// because base.php will already use 7.2 syntax.
if (\version_compare(PHP_VERSION, '7.2.0') === -1) {
	echo 'This version of ownCloud requires at least PHP 7.2.0<br/>';
	echo 'You are currently running PHP ' . PHP_VERSION . '. Please update your PHP version.';
	return;
}

// Show warning if PHP 7.5 or later is used as ownCloud is not compatible with PHP 7.5
if (\version_compare(PHP_VERSION, '7.5.0alpha1') !== -1) {
	echo 'This version of ownCloud is not compatible with PHP 7.5<br/>';
	echo 'You are currently running PHP ' . PHP_VERSION . '.';
	return;
}

// running oC on Windows is unsupported since 8.1, this has to happen here because
// is seems that the autoloader on Windows fails later and just throws an exception.
if (\stripos(PHP_OS, 'WIN') === 0) {
	echo 'ownCloud Server does not support Microsoft Windows.';
	return;
}

try {
	require_once __DIR__ . '/lib/base.php';
	OC::handleRequest();
} catch (\OC\ServiceUnavailableException $ex) {
	\OC::$server->getLogger()->logException($ex, ['app' => 'index']);

	//show the user a detailed error page
	OC_Response::setStatus(OC_Response::STATUS_SERVICE_UNAVAILABLE);
	OC_Template::printExceptionErrorPage($ex);
} catch (\OC\HintException $ex) {
	OC_Response::setStatus(OC_Response::STATUS_SERVICE_UNAVAILABLE);
	OC_Template::printErrorPage($ex->getMessage(), $ex->getHint());
} catch (\OC\User\LoginException $ex) {
	OC_Response::setStatus(OC_Response::STATUS_FORBIDDEN);
	OC_Template::printErrorPage($ex->getMessage());
} catch (\OCP\Files\ForbiddenException $ex) {
	OC_Response::setStatus(OC_Response::STATUS_FORBIDDEN);
	OC_Template::printErrorPage($ex->getMessage());
} catch (\Throwable $ex) {
	try {
		\OC::$server->getLogger()->logException($ex, ['app' => 'index']);

		//show the user a detailed error page
		OC_Response::setStatus(OC_Response::STATUS_INTERNAL_SERVER_ERROR);
		OC_Template::printExceptionErrorPage($ex);
	} catch (\Throwable $ex2) {
		// with some env issues, it can happen that the logger couldn't log properly,
		// so print out the exception directly
		// NOTE: If we've reached this point, something has gone really wrong because
		// we couldn't even get the logger, so don't rely on ownCloud here.
		\header("{$_SERVER['SERVER_PROTOCOL']} 599 Broken");
		\OC::crashLog($ex);
		\OC::crashLog($ex2);
	}
}
