<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
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

class RedisFactory {
	/** @var \Redis | \RedisCluster */
	private $instance;

	/** @var  SystemConfig */
	private $config;

	/**
	 * RedisFactory constructor.
	 *
	 * @param SystemConfig $config
	 */
	public function __construct(SystemConfig $config) {
		$this->config = $config;
	}

	private function create() {
		if ($config = $this->config->getValue('redis.cluster', [])) {
			if (!\class_exists('RedisCluster')) {
				throw new \Exception('Redis Cluster support is not available');
			}
			// cluster config
			if (isset($config['timeout'])) {
				$timeout = $config['timeout'];
			} else {
				$timeout = null;
			}
			if (isset($config['read_timeout'])) {
				$readTimeout = $config['read_timeout'];
			} else {
				$readTimeout = null;
			}
			$this->instance = new \RedisCluster(null, $config['seeds'], $timeout, $readTimeout);

			if (isset($config['failover_mode'])) {
				$this->instance->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, $config['failover_mode']);
			}
		} else {
			$this->instance = new \Redis();
			$config = $this->config->getValue('redis', []);
			if (isset($config['host'])) {
				$host = $config['host'];
			} else {
				$host = '127.0.0.1';
			}
			if (isset($config['port'])) {
				$port = $config['port'];
			} else {
				$port = 6379;
			}
			if (isset($config['timeout'])) {
				$timeout = $config['timeout'];
			} else {
				$timeout = 0.0; // unlimited
			}

			@$this->instance->connect($host, $port, $timeout);
			if (isset($config['password']) && $config['password'] !== '') {
				$this->instance->auth($config['password']);
			}

			if (isset($config['dbindex'])) {
				$this->instance->select($config['dbindex']);
			}
		}
	}

	/**
	 * @return \Redis|\RedisCluster
	 * @throws \Exception
	 */
	public function getInstance() {
		if (!$this->isAvailable()) {
			throw new \Exception('Redis support is not available');
		}
		if (!$this->instance instanceof \Redis) {
			$this->create();
		}

		return $this->instance;
	}

	public function isAvailable() {
		return \extension_loaded('redis')
		&& (\version_compare(\phpversion('redis'), '2.2.5', '>=')
		|| \strcmp(\phpversion('redis'), 'develop')==0);
	}
}
