<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Jakob Sack <mail@jakobsack.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OC;

use OCP\Events\EventEmitterTrait;
use OCP\IAppConfig;
use OCP\IDBConnection;

/**
 * This class provides an easy way for apps to store config values in the
 * database.
 */
class AppConfig implements IAppConfig {
	use EventEmitterTrait;
	/** @var \OCP\IDBConnection $conn */
	protected $conn;

	private $cache = [];

	private $configLoaded;

	/**
	 * @param IDBConnection $conn
	 */
	public function __construct(IDBConnection $conn) {
		$this->conn = $conn;
		$this->configLoaded = false;
	}

	/**
	 * @param string $app
	 * @return array
	 */
	private function getAppValues($app) {
		$this->loadConfigValues();

		if (isset($this->cache[$app])) {
			return $this->cache[$app];
		}

		return [];
	}

	/**
	 * Get all apps using the config
	 *
	 * @return array an array of app ids
	 *
	 * This function returns a list of all apps that have at least one
	 * entry in the appconfig table.
	 */
	public function getApps() {
		$this->loadConfigValues();

		return $this->getSortedKeys($this->cache);
	}

	/**
	 * Get the available keys for an app
	 *
	 * @param string $app the app we are looking for
	 * @return array an array of key names
	 *
	 * This function gets all keys of an app. Please note that the values are
	 * not returned.
	 */
	public function getKeys($app) {
		$this->loadConfigValues();

		if (isset($this->cache[$app])) {
			return $this->getSortedKeys($this->cache[$app]);
		}

		return [];
	}

	public function getSortedKeys($data) {
		$keys = \array_keys($data);
		\sort($keys);
		return $keys;
	}

	/**
	 * Gets the config value
	 *
	 * @param string $app app
	 * @param string $key key
	 * @param string $default = null, default value if the key does not exist
	 * @return string the value or $default
	 *
	 * This function gets a value from the appconfig table. If the key does
	 * not exist the default value will be returned
	 */
	public function getValue($app, $key, $default = null) {
		$this->loadConfigValues();

		if ($this->hasKey($app, $key)) {
			return $this->cache[$app][$key];
		}

		return $default;
	}

	/**
	 * check if a key is set in the appconfig
	 *
	 * @param string $app
	 * @param string $key
	 * @return bool
	 */
	public function hasKey($app, $key) {
		$this->loadConfigValues();

		return isset($this->cache[$app][$key]);
	}

	/**
	 * Sets a value. If the key did not exist before it will be created.
	 *
	 * @param string $app app
	 * @param string $key key
	 * @param string|float|int $value value
	 * @return bool True if the value was inserted or updated, false if the value was the same
	 */
	public function setValue($app, $key, $value) {
		return $this->emittingCall(function (&$afterArray) use (&$app, &$key, &$value) {
			if (!$this->hasKey($app, $key)) {
				$inserted = (bool) $this->conn->insertIfNotExist('*PREFIX*appconfig', [
					'appid' => $app,
					'configkey' => $key,
					'configvalue' => $value,
				], [
					'appid',
					'configkey',
				]);

				if ($inserted) {
					if (!isset($this->cache[$app])) {
						$this->cache[$app] = [];
					}

					$this->cache[$app][$key] = $value;
					return true;
				}
			}

			$sql = $this->conn->getQueryBuilder();
			$sql->update('appconfig')
				->set('configvalue', $sql->createParameter('configvalue'))
				->where($sql->expr()->eq('appid', $sql->createParameter('app')))
				->andWhere($sql->expr()->eq('configkey', $sql->createParameter('configkey')))
				->setParameter('configvalue', $value)
				->setParameter('app', $app)
				->setParameter('configkey', $key);

			/*
			 * Only limit to the existing value for non-Oracle DBs:
			 * http://docs.oracle.com/cd/E11882_01/server.112/e26088/conditions002.htm#i1033286
			 * > Large objects (LOBs) are not supported in comparison conditions.
			 */
			if (!($this->conn instanceof \OC\DB\OracleConnection)) {
				// Only update the value when it is not the same
				$sql->andWhere($sql->expr()->neq('configvalue', $sql->createParameter('configvalue')))
					->setParameter('configvalue', $value);
			}

			if (isset($this->cache[$app], $this->cache[$app][$key])) {
				$afterArray['update'] = true;
				$afterArray['oldvalue'] = $this->cache[$app][$key];
			}

			$changedRow = (bool) $sql->execute();

			$this->cache[$app][$key] = $value;

			return $changedRow;
		}, [
			'before' => ['key' => $key, 'value' => $value, 'app' => $app],
			'after' => ['key' => $key, 'value' => $value, 'app' => $app, 'update' => false, 'oldvalue' => null]
		], 'appconfig', 'setvalue');
	}

	/**
	 * Deletes a key
	 *
	 * @param string $app app
	 * @param string $key key
	 * @return boolean|null
	 */
	public function deleteKey($app, $key) {
		$this->emittingCall(function () use (&$app, &$key) {
			$this->loadConfigValues();

			$sql = $this->conn->getQueryBuilder();
			$sql->delete('appconfig')
				->where($sql->expr()->eq('appid', $sql->createParameter('app')))
				->andWhere($sql->expr()->eq('configkey', $sql->createParameter('configkey')))
				->setParameter('app', $app)
				->setParameter('configkey', $key);
			$sql->execute();

			unset($this->cache[$app][$key]);
			return true;
		}, [
			'before' => ['app' => $app, 'key' => $key],
			'after' => ['app' => $app, 'key' => $key]
		], 'appconfig', 'deletevalue');
	}

	/**
	 * Remove app from appconfig
	 *
	 * @param string $app app
	 * @return boolean|null
	 *
	 * Removes all keys in appconfig belonging to the app.
	 */
	public function deleteApp($app) {
		$this->emittingCall(function () use (&$app) {
			$this->loadConfigValues();

			$sql = $this->conn->getQueryBuilder();
			$sql->delete('appconfig')
				->where($sql->expr()->eq('appid', $sql->createParameter('app')))
				->setParameter('app', $app);
			$sql->execute();

			unset($this->cache[$app]);
			return true;
		}, [
			'before' => ['app' => $app],
			'after' => ['app' => $app]
		], 'appconfig', 'deleteapp');
	}

	/**
	 * get multiple values, either the app or key can be used as wildcard by setting it to false
	 *
	 * @param string|false $app
	 * @param string|false $key
	 * @return array|false
	 */
	public function getValues($app, $key) {
		if (($app !== false) === ($key !== false)) {
			return false;
		}

		if ($key === false) {
			return $this->getAppValues($app);
		} else {
			$appIds = $this->getApps();
			$values = \array_map(function ($appId) use ($key) {
				return isset($this->cache[$appId][$key]) ? $this->cache[$appId][$key] : null;
			}, $appIds);
			$result = \array_combine($appIds, $values);

			return \array_filter($result);
		}
	}

	/**
	 * Load all the app config values
	 */
	protected function loadConfigValues() {
		if ($this->configLoaded) {
			return;
		}

		$this->cache = [];

		$sql = $this->conn->getQueryBuilder();
		$sql->select('*')
			->from('appconfig');
		$result = $sql->execute();

		// we are going to store the result in memory anyway
		$rows = $result->fetchAll();
		foreach ($rows as $row) {
			if (!isset($this->cache[$row['appid']])) {
				$this->cache[$row['appid']] = [];
			}

			// check if installed_version matches the pattern
			// one_or_more_digits-dot-one_or_more_digits-any-other-characters
			if ($row['configkey'] === 'installed_version'
				&& \preg_match('/\d+\.\d+.*$/', $row['configvalue']) !== 1
			) {
				$row['configvalue'] = '0.0.1';
			}
			$this->cache[$row['appid']][$row['configkey']] = $row['configvalue'];
		}
		$result->closeCursor();

		$this->configLoaded = true;
	}
}
