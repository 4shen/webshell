<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
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

OCP\JSON::callCheck();
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('files_sharing');

$l = \OC::$server->getL10N('files_sharing');

$federatedSharingApp = new \OCA\FederatedFileSharing\AppInfo\Application();
$federatedShareProvider = $federatedSharingApp->getFederatedShareProvider();

// check if server admin allows to mount public links from other servers
if ($federatedShareProvider->isIncomingServer2serverShareEnabled() === false) {
	\OCP\JSON::error(['data' => ['message' => $l->t('Server to server sharing is not enabled on this server')]]);
	exit();
}

$token = $_POST['token'];
// cut query and|or anchor part off
$remote = \strtok($_POST['remote'], '?#');
$owner = $_POST['owner'];
$ownerDisplayName = $_POST['ownerDisplayName'];
$name = $_POST['name'];
$password = $_POST['password'];

// Check for invalid name
if (!\OCP\Util::isValidFileName($name)) {
	\OCP\JSON::error(['data' => ['message' => $l->t('The mountpoint name contains invalid characters.')]]);
	exit();
}

$currentUser = \OC::$server->getUserSession()->getUser()->getUID();
$currentServer = \OC::$server->getURLGenerator()->getAbsoluteURL('/');
if (\OC\Share\Helper::isSameUserOnSameServer($owner, $remote, $currentUser, $currentServer)) {
	\OCP\JSON::error(['data' => ['message' => $l->t('Not allowed to create a federated share with the same user server')]]);
	exit();
}

$externalManager = new \OCA\Files_Sharing\External\Manager(
		\OC::$server->getDatabaseConnection(),
		\OC\Files\Filesystem::getMountManager(),
		\OC\Files\Filesystem::getLoader(),
		\OC::$server->getNotificationManager(),
		\OC::$server->getEventDispatcher(),
		\OC::$server->getUserSession()->getUser()->getUID()
);

// check for ssl cert
if (\substr($remote, 0, 5) === 'https') {
	try {
		\OC::$server->getHTTPClientService()->newClient()->get($remote, [
			'timeout' => 10,
			'connect_timeout' => 10,
		])->getBody();
	} catch (\Exception $e) {
		\OCP\JSON::error(['data' => ['message' => $l->t('Invalid or untrusted SSL certificate')]]);
		exit;
	}
}

$mount = $externalManager->addShare($remote, $token, $password, $name, $ownerDisplayName, true);

/**
 * @var \OCA\Files_Sharing\External\Storage $storage
 */
$storage = $mount->getStorage();
'@phan-var \OCA\Files_Sharing\External\Storage $storage';
try {
	// check if storage exists
	$storage->checkStorageAvailability();
} catch (\OCP\Files\StorageInvalidException $e) {
	// note: checkStorageAvailability will already remove the invalid share
	\OCP\Util::writeLog(
		'files_sharing',
		'Invalid remote storage: ' . \get_class($e) . ': ' . $e->getMessage(),
		\OCP\Util::DEBUG
	);
	\OCP\JSON::error(
		[
			'data' => [
				'message' => $l->t('Could not authenticate to remote share, password might be wrong')
			]
		]
	);
	exit();
} catch (\Exception $e) {
	\OCP\Util::writeLog(
		'files_sharing',
		'Invalid remote storage: ' . \get_class($e) . ': ' . $e->getMessage(),
		\OCP\Util::DEBUG
	);
	$externalManager->removeShare($mount->getMountPoint());
	\OCP\JSON::error(['data' => ['message' => $l->t('Storage not valid')]]);
	exit();
}

try {
	$result = $storage->file_exists('');
	if (!$result) {
		$externalManager->removeShare($mount->getMountPoint());
		\OCP\Util::writeLog(
			'files_sharing',
			'Couldn\'t add remote share',
			\OCP\Util::DEBUG
		);
	}
} catch (\OCP\Files\StorageInvalidException $e) {
	\OCP\Util::writeLog(
		'files_sharing',
		'Invalid remote storage: ' . \get_class($e) . ': ' . $e->getMessage(),
		\OCP\Util::DEBUG
	);
	\OCP\JSON::error(['data' => ['message' => $l->t('Storage not valid')]]);
} catch (\Exception $e) {
	\OCP\Util::writeLog(
		'files_sharing',
		'Invalid remote storage: ' . \get_class($e) . ': ' . $e->getMessage(),
		\OCP\Util::DEBUG
	);
	\OCP\JSON::error(['data' => ['message' => $l->t('Couldn\'t add remote share')]]);
}
