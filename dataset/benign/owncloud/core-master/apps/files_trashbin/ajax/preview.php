<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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
\OC_Util::checkLoggedIn();
\OC::$server->getSession()->close();

if (!\OC_App::isEnabled('files_trashbin')) {
	exit;
}

$file = \array_key_exists('file', $_GET) ? (string) $_GET['file'] : '';
$maxX = \array_key_exists('x', $_GET) ? (int) $_GET['x'] : '44';
$maxY = \array_key_exists('y', $_GET) ? (int) $_GET['y'] : '44';
$scalingUp = \array_key_exists('scalingup', $_GET) ? (bool) $_GET['scalingup'] : true;

if ($file === '') {
	\OC_Response::setStatus(400); //400 Bad Request
	\OCP\Util::writeLog('core-preview', 'No file parameter was passed', \OCP\Util::DEBUG);
	exit;
}

if ($maxX === 0 || $maxY === 0) {
	\OC_Response::setStatus(400); //400 Bad Request
	\OCP\Util::writeLog('core-preview', 'x and/or y set to 0', \OCP\Util::DEBUG);
	exit;
}

try {
	$userFolder = \OC::$server->getRootFolder()->getUserFolder(\OC_User::getUser());
	/** @var \OCP\Files\Folder $trashFolder */
	$trashFolder = $userFolder->getParent()->get('files_trashbin/files');
	'@phan-var \OCP\Files\Folder $trashFolder';
	/** @var \OCP\Files\File | \OCP\Files\IPreviewNode $file */
	$fileNode = $trashFolder->get($file);
	'@phan-var \OCP\Files\FileInfo | \OCP\Files\IPreviewNode $fileNode';

	if ($fileNode->getType() === \OCP\Files\FileInfo::TYPE_FOLDER) {
		$mimetype = 'httpd/unix-directory';
	} else {
		$pathInfo = \pathinfo(\ltrim($file, '/'));
		$fileName = $pathInfo['basename'];
		// if in root dir
		if ($pathInfo['dirname'] === '.') {
			// cut off the .d* suffix
			$i = \strrpos($fileName, '.');
			if ($i !== false) {
				$fileName = \substr($fileName, 0, $i);
			}
		}
		$mimetype = \OC::$server->getMimeTypeDetector()->detectPath($fileName);
	}

	$image = $fileNode->getThumbnail([
		'x' => $maxX,
		'y' => $maxY,
		'scalingup' => $scalingUp,
		'mimeType' => $mimetype
	]);

	$image->show();
} catch (\Exception $e) {
	\OC_Response::setStatus(500);
	\OCP\Util::writeLog('core', $e->getmessage(), \OCP\Util::DEBUG);
}
