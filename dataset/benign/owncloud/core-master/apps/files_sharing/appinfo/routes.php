<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

use OCP\API;

$application = new \OCA\Files_Sharing\AppInfo\Application();
$application->registerRoutes($this, [
	'resources' => [
		'ExternalShares' => ['url' => '/api/externalShares'],
	],
	'routes' => [
		[
			'name' => 'externalShares#testRemote',
			'url' => '/testremote',
			'verb' => 'GET'
		],
		[
			'name' => 'PersonalSettings#setUserConfig',
			'url' => '/personalsettings/setuserconfig',
			'verb' => 'POST'
		],
	],
	'ocs' => [
		[
			'name' => 'sharees#search',
			'url' => '/api/v1/sharees',
			'verb' => 'GET',
		],
		[
			'name' => 'Share20Ocs#getShares',
			'url' => '/api/v1/shares',
			'verb' => 'GET'
		],
		[
			'name' => 'Share20Ocs#createShare',
			'url' => '/api/v1/shares',
			'verb' => 'POST'
		],
		[
			'name' => 'Share20Ocs#acceptShare',
			'url' => '/api/v1/shares/pending/{id}',
			'verb' => 'POST'
		],
		[
			'name' => 'Share20Ocs#declineShare',
			'url' => '/api/v1/shares/pending/{id}',
			'verb' => 'DELETE'
		],
		[
			'name' => 'Share20Ocs#getShare',
			'url' => '/api/v1/shares/{id}',
			'verb' => 'GET'
		],
		[
			'name' => 'Share20Ocs#updateShare',
			'url' => '/api/v1/shares/{id}',
			'verb' => 'PUT'
		],
		[
			'name' => 'Share20Ocs#deleteShare',
			'url' => '/api/v1/shares/{id}',
			'verb' => 'DELETE'
		],
		[
			'name' => 'Notification#notifyRecipients',
			'url' => '/api/v1/notification/send',
			'verb' => 'POST'
		],
		[
			'name' => 'Notification#notifyRecipientsDisabled',
			'url' => '/api/v1/notification/marksent',
			'verb' => 'POST'
		],
		[
			'name' => 'Notification#notifyPublicLinkRecipientsByEmail',
			'url' => '/api/v1/notification/notify-public-link-by-email',
			'verb' => 'POST'
		],
		[
			'name' => 'RemoteOcs#getShares',
			'url' => '/api/v1/remote_shares',
			'verb' => 'GET'
		],
		[
			'name' => 'RemoteOcs#getAllShares',
			'url' => '/api/v1/remote_shares/all',
			'verb' => 'GET'
		],
		[
			'name' => 'RemoteOcs#getOpenShares',
			'url' => '/api/v1/remote_shares/pending',
			'verb' => 'GET'
		],
		[
			'name' => 'RemoteOcs#acceptShare',
			'url' => '/api/v1/remote_shares/pending/{id}',
			'verb' => 'POST'
		],
		[
			'name' => 'RemoteOcs#declineShare',
			'url' => '/api/v1/remote_shares/pending/{id}',
			'verb' => 'DELETE'
		],
		[
			'name' => 'RemoteOcs#getShare',
			'url' => '/api/v1/remote_shares/{id}',
			'verb' => 'GET'
		],
		[
			'name' => 'RemoteOcs#unshare',
			'url' => '/api/v1/remote_shares/{id}',
			'verb' => 'DELETE'
		],
	]
]);

/** @var $this \OCP\Route\IRouter */
$this->create('core_ajax_public_preview', '/publicpreview')->action(
	function () {
		require_once __DIR__ . '/../ajax/publicpreview.php';
	});

$this->create('files_sharing_ajax_list', 'ajax/list.php')
	->actionInclude('files_sharing/ajax/list.php');
$this->create('files_sharing_ajax_publicpreview', 'ajax/publicpreview.php')
	->actionInclude('files_sharing/ajax/publicpreview.php');
$this->create('sharing_external_shareinfo', '/shareinfo')
	->actionInclude('files_sharing/ajax/shareinfo.php');
$this->create('sharing_external_add', '/external')
	->actionInclude('files_sharing/ajax/external.php');
