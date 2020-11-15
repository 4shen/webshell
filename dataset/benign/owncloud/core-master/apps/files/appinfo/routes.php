<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Tobias Kaminsky <tobias@kaminsky.me>
 * @author Tom Needham <tom@owncloud.com>
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
namespace OCA\Files\AppInfo;

$application = new Application();
$application->registerRoutes(
	$this,
	[
		'routes' => [
			[
				'name' => 'API#getThumbnail',
				'url' => '/api/v1/thumbnail/{x}/{y}/{file}',
				'verb' => 'GET',
				'requirements' => ['file' => '.+']
			],
			[
				'name' => 'API#updateFileTags',
				'url' => '/api/v1/files/{path}',
				'verb' => 'POST',
				'requirements' => ['path' => '.+'],
			],
			[
				'name' => 'API#updateFileSorting',
				'url' => '/api/v1/sorting',
				'verb' => 'POST'
			],
			[
				'name' => 'API#showHiddenFiles',
				'url' => '/api/v1/showhidden',
				'verb' => 'POST'
			],
			[
				'name' => 'view#index',
				'url' => '/',
				'verb' => 'GET',
			],

		]
	]
);

/** @var $this \OC\Route\Router */

$this->create('files_ajax_download', 'ajax/download.php')
	->actionInclude('files/ajax/download.php');
$this->create('files_ajax_getstoragestats', 'ajax/getstoragestats.php')
	->actionInclude('files/ajax/getstoragestats.php');
$this->create('files_ajax_list', 'ajax/list.php')
	->actionInclude('files/ajax/list.php');

$this->create('download', 'download{file}')
	->requirements(['file' => '.*'])
	->actionInclude('files/download.php');
