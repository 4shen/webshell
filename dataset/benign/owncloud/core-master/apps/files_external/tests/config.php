<?php
/**
 * @author Christian Berendt <berendt@b1-systems.de>
 * @author hkjolhede <hkjolhede@gmail.com>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Robin Appelman <icewind@owncloud.com>
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
// in case there are private configurations in the users home -> use them
$privateConfigFile = $_SERVER['HOME'] . '/owncloud-extfs-test-config.php';
if (\file_exists($privateConfigFile)) {
	$config = include($privateConfigFile);
	return $config;
}

// this is now more a template now for your private configurations
return [
	'webdav'=> [
		'run'=>false,
		'host'=>'localhost',
		'user'=>'test',
		'password'=>'test',
		'root'=>'',
		// wait delay in seconds after write operations
		// (only in tests)
		// set to higher value for lighttpd webdav
		'wait'=> 0
	],
	'owncloud'=> [
		'run'=>false,
		'host'=>'localhost/owncloud',
		'user'=>'test',
		'password'=>'test',
		'root'=>'',
	],
	'google'=> [
		'run'=> false,
		'configured' => 'true',
		'client_id' => '',
		'client_secret' => '',
		'token' => '',
	],
	'smb'=> [
		'run'=>false,
		'user'=>'test',
		'password'=>'test',
		'host'=>'localhost',
		'share'=>'/test',
		'root'=>'/test/',
	],
	'sftp' => [
		'run'=>false,
		'host'=>'localhost',
		'user'=>'test',
		'password'=>'test',
		'root'=>'/test'
	],
	'sftp_key' => [
				'run'=>false,
				'host'=>'localhost',
				'user'=>'test',
				'public_key'=>'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAAgQDJPTvz3OLonF2KSGEKP/nd4CPmRYvemG2T4rIiNYjDj0U5y+2sKEWbjiUlQl2bsqYuVoJ+/UNJlGQbbZ08kQirFeo1GoWBzqioaTjUJfbLN6TzVVKXxR9YIVmH7Ajg2iEeGCndGgbmnPfj+kF9TR9IH8vMVvtubQwf7uEwB0ALhw== phpseclib-generated-key',
		'private_key'=>'test',
				'root'=>'/test'
	],
];
