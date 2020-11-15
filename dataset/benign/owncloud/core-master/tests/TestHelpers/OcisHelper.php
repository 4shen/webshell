<?php
/**
 * ownCloud
 *
 * @author    Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2020 Artur Neumann artur@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace TestHelpers;

use PHPUnit\Framework\Assert;

/**
 * Class OcisHelper
 *
 * Helper functions that are needed to run tests on OCIS
 *
 * @package TestHelpers
 */
class OcisHelper {

	/**
	 * @return bool
	 */
	public static function isTestingOnOcis() {
		return (\getenv("TEST_OCIS") === "true");
	}

	/**
	 * @return bool
	 */
	public static function getDeleteUserDataCommand() {
		return (\getenv("DELETE_USER_DATA_CMD"));
	}

	/**
	 * @param string $user
	 *
	 * @return void
	 */
	public static function deleteRevaUserData($user = "") {
		$deleteCmd = self::getDeleteUserDataCommand();
		if ($deleteCmd !== false) {
			$deleteCmd = \sprintf($deleteCmd, $user);
			\exec($deleteCmd);
		} else {
			self::recurseRmdir(self::getOcisRevaDataRoot() . "/data/" . $user);
		}
	}

	/**
	 * Helper for Recursive Copy of file/folder
	 * For more info check this out https://gist.github.com/gserrano/4c9648ec9eb293b9377b
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 *
	 */
	public static function recurseCopy($source, $destination) {
		$dir = \opendir($source);
		@\mkdir($destination);
		while (($file = \readdir($dir)) !== false) {
			if (($file != '.') && ($file != '..')) {
				if (\is_dir($source . '/' . $file)) {
					self::recurseCopy($source . '/' . $file, $destination . '/' . $file);
				} else {
					\copy($source . '/' . $file, $destination . '/' . $file);
				}
			}
		}
		\closedir($dir);
	}

	/**
	 * Helper for Recursive Upload of file/folder
	 *
	 * @param string $baseUrl
	 * @param string $source
	 * @param string $userId
	 * @param string $password
	 * @param string $destination
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function recurseUpload($baseUrl, $source, $userId, $password, $destination = '') {
		if ($destination !== '') {
			$response = WebDavHelper::makeDavRequest(
				$baseUrl,
				$userId,
				$password,
				"MKCOL",
				$destination,
				[]
			);
			if ($response->getStatusCode() !== 201) {
				throw new \Exception("Could not create folder destination" . $response->getBody()->getContents());
			}
		}

		$dir = \opendir($source);
		while (($file = \readdir($dir)) !== false) {
			if (($file != '.') && ($file != '..')) {
				if (\is_dir($source . '/' . $file)) {
					self::recurseUpload(
						$baseUrl,
						$source . '/' . $file,
						$userId,
						$password,
						$destination . '/' . $file
					);
				} else {
					$response = UploadHelper::upload(
						$baseUrl,
						$userId,
						$password,
						$source . '/' . $file,
						$destination . '/' . $file
					);
					if ($response->getStatusCode() !== 201) {
						throw new \Exception("Could not upload skeleton file" . $response->getBody()->getContents());
					}
				}
			}
		}
		\closedir($dir);
	}

	/**
	 * @return int
	 */
	public static function getLdapPort() {
		$port = \getenv("REVA_LDAP_PORT");
		return $port ? (int)$port : 636;
	}

	/**
	 * @return bool
	 */
	public static function useSsl() {
		return (self::getLdapPort() === 636);
	}

	/**
	 * @return string
	 */
	public static function getBaseDN() {
		$dn = \getenv("REVA_LDAP_BASE_DN");
		return $dn ? $dn : "dc=owncloud,dc=com";
	}

	/**
	 * @return string
	 */
	public static function getHostname() {
		$hostname = \getenv("REVA_LDAP_HOSTNAME");
		return $hostname ? $hostname : "localhost";
	}

	/**
	 * @return string
	 */
	public static function getBindDN() {
		$dn = \getenv("REVA_LDAP_BIND_DN");
		return $dn ? $dn : "cn=admin,dc=owncloud,dc=com";
	}

	/**
	 * @return string
	 */
	private static function getOcisRevaDataRoot() {
		$root = \getenv("OCIS_REVA_DATA_ROOT");
		if (($root === false || $root === "") && self::isTestingOnOcis()) {
			$root = "/var/tmp/reva/";
		}
		return $root;
	}

	/**
	 * @param string $dir
	 *
	 * @return bool
	 */
	private static function recurseRmdir($dir) {
		if (\file_exists($dir) === true) {
			$files = \array_diff(\scandir($dir), ['.', '..']);
			foreach ($files as $file) {
				if (\is_dir("$dir/$file")) {
					self::recurseRmdir("$dir/$file");
				} else {
					\unlink("$dir/$file");
				}
			}
			return \rmdir($dir);
		}
		return true;
	}
}
