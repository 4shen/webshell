<?php

/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Clark Tomlinson <fallen013@gmail.com>
 * @author Guillaume AMAT <guillaume.amat@informatique-libre.com>
 * @author Hasso Tepper <hasso@zone.ee>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Owen Winkler <a_github@midnightcircus.com>
 * @author Philipp Schaffrath <github@philipp.schaffrath.email>
 * @author phisch <git@philippschaffrath.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Timo Benk <benk@b1-systems.de>
 * @author Vincent Chan <plus.vincchan@gmail.com>
 * @author Vincent Petry <pvince81@owncloud.com>
 * @author Felix Heidecke <felix@heidecke.me>
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

// Set the content type to Javascript
\header("Content-type: text/javascript");

// Disallow caching
\header("Cache-Control: no-cache, must-revalidate");
\header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Enable l10n support
$l = \OC::$server->getL10N('core');

// Enable OC_Defaults support
$defaults = new OC_Defaults();

// Get the config
$apps_paths = [];
foreach (OC_App::getEnabledApps() as $app) {
	$apps_paths[$app] = OC_App::getAppWebPath($app);
}

$config = \OC::$server->getConfig();
$value = $config->getAppValue('core', 'shareapi_default_expire_date', 'no');
$defaultExpireDateEnabled = ($value === 'yes') ? true :false;
$defaultExpireDate = $enforceDefaultExpireDate = null;
if ($defaultExpireDateEnabled) {
	$defaultExpireDate = (int) $config->getAppValue('core', 'shareapi_expire_after_n_days', '7');
	$value = $config->getAppValue('core', 'shareapi_enforce_expire_date', 'no');
	$enforceDefaultExpireDate = ($value === 'yes') ? true : false;
}
$enforceLinkPasswordReadOnly = $config->getAppValue('core', 'shareapi_enforce_links_password_read_only', 'no') === 'yes';
$enforceLinkPasswordReadWrite = $config->getAppValue('core', 'shareapi_enforce_links_password_read_write', 'no') === 'yes';
$enforceLinkPasswordWriteOnly = $config->getAppValue('core', 'shareapi_enforce_links_password_write_only', 'no') === 'yes';

$value = $config->getAppValue('core', 'shareapi_default_expire_date_user_share', 'no');
$defaultExpireDateUserEnabled = ($value === 'yes') ? true :false;

$defaultExpireDateUser = (int) $config->getAppValue('core', 'shareapi_expire_after_n_days_user_share', '7');

$value = $config->getAppValue('core', 'shareapi_enforce_expire_date_user_share', 'no');
$enforceDefaultExpireDateUser = ($value === 'yes') ? true : false;

$value = $config->getAppValue('core', 'shareapi_default_expire_date_group_share', 'no');
$defaultExpireDateGroupEnabled = ($value === 'yes') ? true :false;

$defaultExpireDateGroup = (int) $config->getAppValue('core', 'shareapi_expire_after_n_days_group_share', '7');

$value = $config->getAppValue('core', 'shareapi_enforce_expire_date_group_share', 'no');
$enforceDefaultExpireDateGroup =  ($value === 'yes') ? true : false;

$enforceLinkPasswordReadWriteDelete = $config->getAppValue('core', 'shareapi_enforce_links_password_read_write_delete', 'no') === 'yes';
$outgoingServer2serverShareEnabled = $config->getAppValue('files_sharing', 'outgoing_server2server_share_enabled', 'yes') === 'yes';

$countOfDataLocation = 0;

$value = $config->getAppValue('core', 'shareapi_enable_link_password_by_default', 'no');

$dataLocation = \str_replace(OC::$SERVERROOT .'/', '', $config->getSystemValue('datadirectory', ''), $countOfDataLocation);
if ($countOfDataLocation !== 1 || !OC_User::isAdminUser(OC_User::getUser())) {
	$dataLocation = false;
}

$previewManager = \OC::$server->getPreviewManager();
'@phan-var \OC\PreviewManager $previewManager';

$array = [
	"oc_debug" => $config->getSystemValue('debug', false) ? 'true' : 'false',
	"oc_isadmin" => OC_User::isAdminUser(OC_User::getUser()) ? 'true' : 'false',
	"oc_dataURL" => \is_string($dataLocation) ? "\"".$dataLocation."\"" : 'false',
	"oc_webroot" => "\"".OC::$WEBROOT."\"",
	"oc_appswebroots" =>  \str_replace('\\/', '/', \json_encode($apps_paths)), // Ugly unescape slashes waiting for better solution
	"datepickerFormatDate" => \json_encode($l->l('jsdate', null)),
	"dayNames" =>  \json_encode(
		[
			(string)$l->t('Sunday'),
			(string)$l->t('Monday'),
			(string)$l->t('Tuesday'),
			(string)$l->t('Wednesday'),
			(string)$l->t('Thursday'),
			(string)$l->t('Friday'),
			(string)$l->t('Saturday')
		]
	),
	"dayNamesShort" =>  \json_encode(
		[
			(string)$l->t('Sun.'),
			(string)$l->t('Mon.'),
			(string)$l->t('Tue.'),
			(string)$l->t('Wed.'),
			(string)$l->t('Thu.'),
			(string)$l->t('Fri.'),
			(string)$l->t('Sat.')
		]
	),
	"dayNamesMin" =>  \json_encode(
		[
			(string)$l->t('Su'),
			(string)$l->t('Mo'),
			(string)$l->t('Tu'),
			(string)$l->t('We'),
			(string)$l->t('Th'),
			(string)$l->t('Fr'),
			(string)$l->t('Sa')
		]
	),
	"monthNames" => \json_encode(
		[
			(string)$l->t('January'),
			(string)$l->t('February'),
			(string)$l->t('March'),
			(string)$l->t('April'),
			(string)$l->t('May'),
			(string)$l->t('June'),
			(string)$l->t('July'),
			(string)$l->t('August'),
			(string)$l->t('September'),
			(string)$l->t('October'),
			(string)$l->t('November'),
			(string)$l->t('December')
		]
	),
	"monthNamesShort" => \json_encode(
		[
			(string)$l->t('Jan.'),
			(string)$l->t('Feb.'),
			(string)$l->t('Mar.'),
			(string)$l->t('Apr.'),
			(string)$l->t('May.'),
			(string)$l->t('Jun.'),
			(string)$l->t('Jul.'),
			(string)$l->t('Aug.'),
			(string)$l->t('Sep.'),
			(string)$l->t('Oct.'),
			(string)$l->t('Nov.'),
			(string)$l->t('Dec.')
		]
	),
	"firstDay" => \json_encode($l->l('firstday', null)) ,
	"oc_config" => [
			'session_lifetime'	=> \min(\OC::$server->getConfig()->getSystemValue('session_lifetime', OC::$server->getIniWrapper()->getNumeric('session.gc_maxlifetime')), OC::$server->getIniWrapper()->getNumeric('session.gc_maxlifetime')),
			'session_keepalive'	=> \OC::$server->getConfig()->getSystemValue('session_keepalive', true),
			'enable_avatars'	=> \OC::$server->getConfig()->getSystemValue('enable_avatars', true) === true,
			'lost_password_link'	=> \OC::$server->getConfig()->getSystemValue('lost_password_link', null),
			'modRewriteWorking'	=> (\getenv('front_controller_active') === 'true'),
			'blacklist_files_regex'	=> \OCP\Files\FileInfo::BLACKLIST_FILES_REGEX
		],
	"oc_appconfig" => [
			"core" => [
				'defaultExpireDateEnabled' => $defaultExpireDateEnabled,
				'defaultExpireDate' => $defaultExpireDate,
				'defaultExpireDateEnforced' => $enforceDefaultExpireDate,
				'enforceLinkPasswordReadOnly' => $enforceLinkPasswordReadOnly,
				'enforceLinkPasswordReadWrite' => $enforceLinkPasswordReadWrite,
				'enforceLinkPasswordReadWriteDelete' => $enforceLinkPasswordReadWriteDelete,
				'enforceLinkPasswordWriteOnly' => $enforceLinkPasswordWriteOnly,
				
				'defaultExpireDateUserEnabled' => $defaultExpireDateUserEnabled,
				'defaultExpireDateUser' => $defaultExpireDateUser,
				'enforceDefaultExpireDateUser' => $enforceDefaultExpireDateUser,
				
				'defaultExpireDateGroupEnabled' => $defaultExpireDateGroupEnabled,
				'defaultExpireDateGroup' => $defaultExpireDateGroup,
				'enforceDefaultExpireDateGroup' => $enforceDefaultExpireDateGroup,

				'sharingDisabledForUser' => \OCP\Util::isSharingDisabledForUser(),
				'resharingAllowed' => \OCP\Share::isResharingAllowed(),
				'remoteShareAllowed' => $outgoingServer2serverShareEnabled,
				'allowGroupSharing' => \OC::$server->getShareManager()->allowGroupSharing(),
				'previewsEnabled' => \OC::$server->getConfig()->getSystemValue('enable_previews', true) === true,
				'enabledPreviewProviders' => $previewManager->getSupportedMimes()
			]
		],
	"oc_defaults" => [
			'entity' => $defaults->getEntity(),
			'name' => $defaults->getName(),
			'title' => $defaults->getTitle(),
			'baseUrl' => $defaults->getBaseUrl(),
			'syncClientUrl' => $defaults->getSyncClientUrl(),
			'slogan' => $defaults->getSlogan(),
			'logoClaim' => $defaults->getLogoClaim(),
			'shortFooter' => $defaults->getShortFooter(),
			'longFooter' => $defaults->getLongFooter(),
			'folder' => OC_Util::getTheme()->getName()
	],
	'theme' => \json_encode(
		[
			'name' => OC_Util::getTheme()->getName(),
			'directory' => OC_Util::getTheme()->getDirectory()
		]
	)
];

if (\OC::$server->getUserSession() !== null && \OC::$server->getUserSession()->isLoggedIn()) {
	$array['oc_appconfig']['federatedCloudShareDoc'] = \OC::$server->getURLGenerator()->linkToDocs('user-sharing-federated');
	$array['oc_config']['version'] = \implode('.', \OCP\Util::getVersion());
	$array['oc_config']['versionstring'] = OC_Util::getVersionString();
	$array['oc_defaults']['docBaseUrl'] = $defaults->getDocBaseUrl();
	$array['oc_defaults']['docPlaceholderUrl'] = $defaults->buildDocLinkToKey('PLACEHOLDER');
	$caps = \OC::$server->getCapabilitiesManager()->getCapabilities();
	// remove status.php info as we already have the version above
	unset($caps['core']['status']);
	$array['oc_capabilities'] = \json_encode($caps);

	$user = \OC::$server->getUserSession()->getUser();
	if ($user !== null) {
		$array['oc_user'] = \json_encode([
			'uid' => $user->getUID(),
			'displayName' => $user->getDisplayName(),
			'email' => $user->getEMailAddress()
		]);
	}
}

// Allow hooks to modify the output values
OC_Hook::emit('\OCP\Config', 'js', ['array' => &$array]);

$array['oc_appconfig'] = \json_encode($array['oc_appconfig']);
$array['oc_config'] = \json_encode($array['oc_config']);
$array['oc_defaults'] = \json_encode($array['oc_defaults']);

// Echo it
foreach ($array as  $setting => $value) {
	echo("var ". $setting ."=".$value.";\n");
}
