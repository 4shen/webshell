<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Roeland Jago Douma <rullzer@users.noreply.github.com>
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

$application = new \OCA\Federation\AppInfo\Application();

$application->registerRoutes(
	$this,
	[
		'routes' => [
			[
				'name' => 'Settings#addServer',
				'url' => '/trusted-servers',
				'verb' => 'POST'
			],
			[
				'name' => 'Settings#removeServer',
				'url' => '/trusted-servers/{id}',
				'verb' => 'DELETE'
			],
			[
				'name' => 'Settings#autoAddServers',
				'url' => '/auto-add-servers',
				'verb' => 'POST'
			],
		],
		'ocs' => [
			[
				'name' => 'OCSAuthAPI#getSharedSecret',
				'url' => '/api/v1/shared-secret',
				'verb' => 'GET'
			],
			[
				'name' => 'OCSAuthAPI#requestSharedSecret',
				'url' => '/api/v1/request-shared-secret',
				'verb' => 'POST'
			]
		]
	]
);
