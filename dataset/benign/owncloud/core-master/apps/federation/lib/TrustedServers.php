<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
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

namespace OCA\Federation;

use OC\HintException;
use OCP\AppFramework\Http;
use OCP\BackgroundJob\IJobList;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\ILogger;
use OCP\Security\ISecureRandom;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class TrustedServers {

	/** after a user list was exchanged at least once successfully */
	const STATUS_OK = 1;
	/** waiting for shared secret or initial user list exchange */
	const STATUS_PENDING = 2;
	/** something went wrong, misconfigured server, software bug,... user interaction needed */
	const STATUS_FAILURE = 3;
	/** remote server revoked access */
	const STATUS_ACCESS_REVOKED = 4;

	/** @var DbHandler dbHandler */
	private $dbHandler;

	/** @var IClientService */
	private $httpClientService;

	/** @var ILogger */
	private $logger;

	/** @var IJobList */
	private $jobList;

	/** @var ISecureRandom */
	private $secureRandom;

	/** @var IConfig */
	private $config;

	/** @var EventDispatcherInterface */
	private $dispatcher;

	/**
	 * @param DbHandler $dbHandler
	 * @param IClientService $httpClientService
	 * @param ILogger $logger
	 * @param IJobList $jobList
	 * @param ISecureRandom $secureRandom
	 * @param IConfig $config
	 * @param EventDispatcherInterface $dispatcher
	 */
	public function __construct(
		DbHandler $dbHandler,
		IClientService $httpClientService,
		ILogger $logger,
		IJobList $jobList,
		ISecureRandom $secureRandom,
		IConfig $config,
		EventDispatcherInterface $dispatcher
	) {
		$this->dbHandler = $dbHandler;
		$this->httpClientService = $httpClientService;
		$this->logger = $logger;
		$this->jobList = $jobList;
		$this->secureRandom = $secureRandom;
		$this->config = $config;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * add server to the list of trusted ownCloud servers
	 *
	 * @param $url
	 * @return int server id
	 */
	public function addServer($url) {
		$url = $this->updateProtocol($url);
		$result = $this->dbHandler->addServer($url);
		if ($result) {
			$token = $this->secureRandom->generate(16);
			$this->dbHandler->addToken($url, $token);
			$this->jobList->add(
				'OCA\Federation\BackgroundJob\RequestSharedSecret',
				[
					'url' => $url,
					'token' => $token
				]
			);
		}

		return $result;
	}

	/**
	 * enable/disable to automatically add servers to the list of trusted servers
	 * once a federated share was created and accepted successfully
	 *
	 * @param bool $status
	 */
	public function setAutoAddServers($status) {
		$value = $status ? '1' : '0';
		$this->config->setAppValue('federation', 'autoAddServers', $value);
	}

	/**
	 * return if we automatically add servers to the list of trusted servers
	 * once a federated share was created and accepted successfully
	 *
	 * @return bool
	 */
	public function getAutoAddServers() {
		$value = $this->config->getAppValue('federation', 'autoAddServers', '0');
		return $value === '1';
	}

	/**
	 * get shared secret for the given server
	 *
	 * @param string $url
	 * @return string
	 */
	public function getSharedSecret($url) {
		return $this->dbHandler->getSharedSecret($url);
	}

	/**
	 * add shared secret for the given server
	 *
	 * @param string $url
	 * @param $sharedSecret
	 */
	public function addSharedSecret($url, $sharedSecret) {
		$this->dbHandler->addSharedSecret($url, $sharedSecret);
	}

	/**
	 * remove server from the list of trusted ownCloud servers
	 *
	 * @param int $id
	 */
	public function removeServer($id) {
		$server = $this->dbHandler->getServerById($id);
		$this->dbHandler->removeServer($id);
		$event = new GenericEvent($server['url_hash']);
		$this->dispatcher->dispatch('OCP\Federation\TrustedServerEvent::remove', $event);
	}

	/**
	 * get all trusted servers
	 *
	 * @return array
	 */
	public function getServers() {
		return $this->dbHandler->getAllServer();
	}

	/**
	 * check if given server is a trusted ownCloud server
	 *
	 * @param string $url
	 * @return bool
	 */
	public function isTrustedServer($url) {
		return $this->dbHandler->serverExists($url);
	}

	/**
	 * set server status
	 *
	 * @param string $url
	 * @param int $status
	 */
	public function setServerStatus($url, $status) {
		$this->dbHandler->setServerStatus($url, $status);
	}

	/**
	 * @param string $url
	 * @return int
	 */
	public function getServerStatus($url) {
		return $this->dbHandler->getServerStatus($url);
	}

	/**
	 * check if URL point to a ownCloud server
	 *
	 * @param string $url
	 * @return bool
	 */
	public function isOwnCloudServer($url) {
		$isValidOwnCloud = false;
		$client = $this->httpClientService->newClient();
		$result = $client->get(
			$url . '/status.php',
			[
				'timeout' => 3,
				'connect_timeout' => 3,
			]
		);
		if ($result->getStatusCode() === Http::STATUS_OK) {
			$isValidOwnCloud = $this->checkOwnCloudVersion($result->getBody());
		}

		return $isValidOwnCloud;
	}

	/**
	 * check if ownCloud version is >= 9.0
	 *
	 * @param $status
	 * @return bool
	 * @throws HintException
	 */
	protected function checkOwnCloudVersion($status) {
		$decoded = \json_decode($status, true);
		if (!empty($decoded) && isset($decoded['version'])) {
			if (!\version_compare($decoded['version'], '9.0.0', '>=')) {
				throw new HintException('Remote server version is too low. ownCloud 9.0 is required.');
			}
			return true;
		}
		return false;
	}

	/**
	 * check if the URL contain a protocol, if not add https
	 *
	 * @param string $url
	 * @return string
	 */
	protected function updateProtocol($url) {
		if (
			\strpos($url, 'https://') === 0
			|| \strpos($url, 'http://') === 0
		) {
			return $url;
		}

		return 'https://' . $url;
	}
}
