<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();
\OC::$server->getSession()->close();

$folder = isset($_POST['dir']) ? $_POST['dir'] : '/';

// "empty trash" command
if (isset($_POST['allfiles']) && (string)$_POST['allfiles'] === 'true') {
	$deleteAll = true;
	if ($folder === '/' || $folder === '') {
		OCA\Files_Trashbin\Trashbin::deleteAll();
		$list = [];
	} else {
		$list[] = $folder;
		$folder = \dirname($folder);
	}
} else {
	$deleteAll = false;
	$files = (string)$_POST['files'];
	$list = \json_decode($files);
}

$folder = \rtrim($folder, '/') . '/';
$error = [];
$success = [];

$i = 0;
foreach ($list as $file) {
	if ($folder === '/') {
		$file = \ltrim($file, '/');
		$delimiter = \strrpos($file, '.d');
		$filename = \substr($file, 0, $delimiter);
		$timestamp =  \substr($file, $delimiter+2);
	} else {
		$filename = $folder . '/' . $file;
		$timestamp = null;
	}

	OCA\Files_Trashbin\Trashbin::delete($filename, \OCP\User::getUser(), $timestamp);
	if (OCA\Files_Trashbin\Trashbin::file_exists($filename, $timestamp)) {
		$error[] = $filename;
		\OCP\Util::writeLog('files_trashbin', 'can\'t delete ' . $filename . ' permanently.', \OCP\Util::ERROR);
	}
	// only list deleted files if not deleting everything
	elseif (!$deleteAll) {
		$success[$i]['filename'] = $file;
		$success[$i]['timestamp'] = $timestamp;
		$i++;
	}
}

if ($error) {
	$filelist = '';
	foreach ($error as $e) {
		$filelist .= $e.', ';
	}
	$l = \OC::$server->getL10N('files_trashbin');
	$message = $l->t("Couldn't delete %s permanently", [\rtrim($filelist, ', ')]);
	OCP\JSON::error(["data" => ["message" => $message,
										   "success" => $success, "error" => $error]]);
} else {
	OCP\JSON::success(["data" => ["success" => $success]]);
}
