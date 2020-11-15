<?php
/**
 * @author Andreas Fischer <bantu@owncloud.com>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Frank Karlitschek <frank@karlitschek.de>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Piotr Filiciak <piotr@filiciak.pl>
 * @author Robin Appelman <icewind@owncloud.com>
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

// Check if we are a user
OCP\User::checkLoggedIn();
\OC::$server->getSession()->close();

// files can be an array with multiple "files[]=one.txt&files[]=two.txt" or a single file with "files=filename.txt"
$files_list = isset($_GET['files']) ? $_GET['files'] : '';
$dir = isset($_GET['dir']) ? $_GET['dir']: '';

// in case we get only a single file
if (!\is_array($files_list)) {
	$files_list = [$files_list];
} else {
	$files_list = \array_map(function ($file) {
		return $file;
	}, $files_list);
}

/**
 * this sets a cookie to be able to recognize the start of the download
 * the content must not be longer than 32 characters and must only contain
 * alphanumeric characters
 */
if (isset($_GET['downloadStartSecret'])
	&& !isset($_GET['downloadStartSecret'][32])
	&& \preg_match('!^[a-zA-Z0-9]+$!', $_GET['downloadStartSecret']) === 1) {
	\setcookie('ocDownloadStarted', $_GET['downloadStartSecret'], \time() + 20, '/');
}

$server_params = ['head' => \OC::$server->getRequest()->getMethod() == 'HEAD'];

/**
 * Http range requests support
 */
if (isset($_SERVER['HTTP_RANGE'])) {
	$server_params['range'] = \OC::$server->getRequest()->getHeader('Range');
}

OC_Files::get($dir, $files_list, $server_params);
