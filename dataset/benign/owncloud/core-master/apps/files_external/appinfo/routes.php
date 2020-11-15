<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Ross Nicoll <jrn@jrn.me.uk>
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

/**
 * @var $this \OCP\Route\IRouter
 **/
\OC_Mount_Config::$app->registerRoutes(
	$this,
	[
		'resources' => [
			'global_storages' => ['url' => '/globalstorages'],
			'user_storages' => ['url' => '/userstorages'],
			'user_global_storages' => ['url' => '/userglobalstorages'],
		],
		'routes' => [
			[
				'name' => 'Ajax#getSshKeys',
				'url' => '/ajax/public_key.php',
				'verb' => 'POST',
				'requirements' => []
			]
		]
	]
);

$this->create('files_external_oauth1', 'ajax/oauth1.php')
	->actionInclude('files_external/ajax/oauth1.php');
$this->create('files_external_oauth2', 'ajax/oauth2.php')
	->actionInclude('files_external/ajax/oauth2.php');

$this->create('files_external_list_applicable', '/applicable')
	->actionInclude('files_external/ajax/applicable.php');

\OCP\API::register('get',
		'/apps/files_external/api/v1/mounts',
		['\OCA\Files_External\Lib\Api', 'getUserMounts'],
		'files_external');
