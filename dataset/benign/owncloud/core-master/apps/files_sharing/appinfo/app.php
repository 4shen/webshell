<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Gadzy <dev@gadzy.fr>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
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

\OCA\Files_Sharing\Helper::registerHooks();

\OCP\Share::registerBackend('file', 'OCA\Files_Sharing\ShareBackend\File');
\OCP\Share::registerBackend('folder', 'OCA\Files_Sharing\ShareBackend\Folder', 'file');

$application = new \OCA\Files_Sharing\AppInfo\Application();
$application->registerMountProviders();
$application->registerNotifier();
$application->registerEvents();

// TODO: move to "Hooks" class
$eventDispatcher = \OC::$server->getEventDispatcher();
$eventDispatcher->addListener(
	'OCA\Files::loadAdditionalScripts',
	function () {
		\OCP\Util::addScript('files_sharing', 'share');
		\OCP\Util::addScript('files_sharing', 'sharetabview');
		if (\OC::$server->getConfig()->getAppValue('files_sharing', 'incoming_server2server_share_enabled', 'yes') === 'yes') {
			\OCP\Util::addScript('files_sharing', 'external');
		}
		\OCP\Util::addStyle('files_sharing', 'sharetabview');
	}
);

// \OCP\Util::addStyle('files_sharing', 'sharetabview');

\OC::$server->getActivityManager()->registerExtension(function () {
	return new \OCA\Files_Sharing\Activity(
			\OC::$server->query('L10NFactory'),
			\OC::$server->getURLGenerator(),
			\OC::$server->getActivityManager()
		);
});

$config = \OC::$server->getConfig();
if (\class_exists('OCA\Files\App') && $config->getAppValue('core', 'shareapi_enabled', 'yes') === 'yes') {
	\OCA\Files\App::getNavigationManager()->add(function () {
		$l = \OC::$server->getL10N('files_sharing');
		return [
			'id' => 'sharingin',
			'appname' => 'files_sharing',
			'script' => 'list.php',
			'order' => 10,
			'name' => $l->t('Shared with you'),
		];
	});

	if (\OCP\Util::isSharingDisabledForUser() === false) {
		\OCA\Files\App::getNavigationManager()->add(function () {
			$l = \OC::$server->getL10N('files_sharing');
			return [
				'id' => 'sharingout',
				'appname' => 'files_sharing',
				'script' => 'list.php',
				'order' => 15,
				'name' => $l->t('Shared with others'),
			];
		});
		// Check if sharing by link is enabled
		if ($config->getAppValue('core', 'shareapi_allow_links', 'yes') === 'yes') {
			\OCA\Files\App::getNavigationManager()->add(function () {
				$l = \OC::$server->getL10N('files_sharing');
				return [
					'id' => 'sharinglinks',
					'appname' => 'files_sharing',
					'script' => 'list.php',
					'order' => 20,
					'name' => $l->t('Shared by link'),
				];
			});
		}
	}
}
