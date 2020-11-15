<?php
/**
 * @author Adam Williamson <awilliam@redhat.com>
 * @author Christopher Schäpers <kondou@ts.unde.re>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

OCP\JSON::checkAppEnabled('files_external');
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();
$l = \OC::$server->getL10N('files_external');

// FIXME: currently hard-coded to Google Drive
if (isset($_POST['client_id'], $_POST['client_secret'], $_POST['redirect'])) {
	$client = new Google_Client();
	$client->setClientId((string)$_POST['client_id']);
	$client->setClientSecret((string)$_POST['client_secret']);
	$client->setRedirectUri((string)$_POST['redirect']);
	$client->setScopes(['https://www.googleapis.com/auth/drive']);
	$client->setApprovalPrompt('force');
	$client->setAccessType('offline');
	if (isset($_POST['step'])) {
		$step = $_POST['step'];
		if ($step == 1) {
			try {
				$authUrl = $client->createAuthUrl();
				OCP\JSON::success(['data' => [
					'url' => $authUrl
				]]);
			} catch (Exception $exception) {
				OCP\JSON::error(['data' => [
					'message' => $l->t('Step 1 failed. Exception: %s', [$exception->getMessage()])
				]]);
			}
		} elseif ($step == 2 && isset($_POST['code'])) {
			try {
				$token = $client->authenticate((string)$_POST['code']);
				OCP\JSON::success(['data' => [
					'token' => \json_encode($token)
				]]);
			} catch (Exception $exception) {
				OCP\JSON::error(['data' => [
					'message' => $l->t('Step 2 failed. Exception: %s', [$exception->getMessage()])
				]]);
			}
		}
	}
}
