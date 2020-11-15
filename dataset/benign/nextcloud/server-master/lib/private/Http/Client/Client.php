<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Mohammed Abdellatif <m.latief@gmail.com>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Scott Shambarger <devel@shambarger.net>
 *
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\Http\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use OCP\ICertificateManager;
use OCP\IConfig;
use OCP\ILogger;

/**
 * Class Client
 *
 * @package OC\Http
 */
class Client implements IClient {
	/** @var GuzzleClient */
	private $client;
	/** @var IConfig */
	private $config;
	/** @var ILogger */
	private $logger;
	/** @var ICertificateManager */
	private $certificateManager;

	public function __construct(
		IConfig $config,
		ILogger $logger,
		ICertificateManager $certificateManager,
		GuzzleClient $client
	) {
		$this->config = $config;
		$this->logger = $logger;
		$this->client = $client;
		$this->certificateManager = $certificateManager;
	}

	private function buildRequestOptions(array $options): array {
		$proxy = $this->getProxyUri();

		$defaults = [
			RequestOptions::VERIFY => $this->getCertBundle(),
			RequestOptions::TIMEOUT => 30,
		];

		// Only add RequestOptions::PROXY if Nextcloud is explicitly
		// configured to use a proxy. This is needed in order not to override
		// Guzzle default values.
		if ($proxy !== null) {
			$defaults[RequestOptions::PROXY] = $proxy;
		}

		$options = array_merge($defaults, $options);

		if (!isset($options[RequestOptions::HEADERS]['User-Agent'])) {
			$options[RequestOptions::HEADERS]['User-Agent'] = 'Nextcloud Server Crawler';
		}

		if (!isset($options[RequestOptions::HEADERS]['Accept-Encoding'])) {
			$options[RequestOptions::HEADERS]['Accept-Encoding'] = 'gzip';
		}

		return $options;
	}

	private function getCertBundle(): string {
		// If the instance is not yet setup we need to use the static path as
		// $this->certificateManager->getAbsoluteBundlePath() tries to instantiiate
		// a view
		if ($this->config->getSystemValue('installed', false) === false) {
			return \OC::$SERVERROOT . '/resources/config/ca-bundle.crt';
		}

		if ($this->certificateManager->listCertificates() === []) {
			return \OC::$SERVERROOT . '/resources/config/ca-bundle.crt';
		}

		return $this->certificateManager->getAbsoluteBundlePath();
	}

	/**
	 * Returns a null or an associative array specifiying the proxy URI for
	 * 'http' and 'https' schemes, in addition to a 'no' key value pair
	 * providing a list of host names that should not be proxied to.
	 *
	 * @return array|null
	 *
	 * The return array looks like:
	 * [
	 *   'http' => 'username:password@proxy.example.com',
	 *   'https' => 'username:password@proxy.example.com',
	 *   'no' => ['foo.com', 'bar.com']
	 * ]
	 *
	 */
	private function getProxyUri(): ?array {
		$proxyHost = $this->config->getSystemValue('proxy', '');

		if ($proxyHost === '' || $proxyHost === null) {
			return null;
		}

		$proxyUserPwd = $this->config->getSystemValue('proxyuserpwd', '');
		if ($proxyUserPwd !== '' && $proxyUserPwd !== null) {
			$proxyHost = $proxyUserPwd . '@' . $proxyHost;
		}

		$proxy = [
			'http' => $proxyHost,
			'https' => $proxyHost,
		];

		$proxyExclude = $this->config->getSystemValue('proxyexclude', []);
		if ($proxyExclude !== [] && $proxyExclude !== null) {
			$proxy['no'] = $proxyExclude;
		}

		return $proxy;
	}

	protected function preventLocalAddress(string $uri, array $options): void {
		if (($options['nextcloud']['allow_local_address'] ?? false) ||
			$this->config->getSystemValueBool('allow_local_remote_servers', false)) {
			return;
		}

		$host = parse_url($uri, PHP_URL_HOST);
		if ($host === false) {
			$this->logger->warning("Could not detect any host in $uri");
			throw new LocalServerException('Could not detect any host');
		}

		$host = strtolower($host);
		// remove brackets from IPv6 addresses
		if (strpos($host, '[') === 0 && substr($host, -1) === ']') {
			$host = substr($host, 1, -1);
		}

		// Disallow localhost and local network
		if ($host === 'localhost' || substr($host, -6) === '.local' || substr($host, -10) === '.localhost') {
			$this->logger->warning("Host $host was not connected to because it violates local access rules");
			throw new LocalServerException('Host violates local access rules');
		}

		// Disallow hostname only
		if (substr_count($host, '.') === 0) {
			$this->logger->warning("Host $host was not connected to because it violates local access rules");
			throw new LocalServerException('Host violates local access rules');
		}

		if ((bool)filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			$this->logger->warning("Host $host was not connected to because it violates local access rules");
			throw new LocalServerException('Host violates local access rules');
		}

		// Also check for IPv6 IPv4 nesting, because that's not covered by filter_var
		if ((bool)filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && substr_count($host, '.') > 0) {
			$delimiter = strrpos($host, ':'); // Get last colon
			$ipv4Address = substr($host, $delimiter + 1);

			if (!filter_var($ipv4Address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				$this->logger->warning("Host $host was not connected to because it violates local access rules");
				throw new LocalServerException('Host violates local access rules');
			}
		}
	}

	/**
	 * Sends a GET request
	 *
	 * @param string $uri
	 * @param array $options Array such as
	 *              'query' => [
	 *                  'field' => 'abc',
	 *                  'other_field' => '123',
	 *                  'file_name' => fopen('/path/to/file', 'r'),
	 *              ],
	 *              'headers' => [
	 *                  'foo' => 'bar',
	 *              ],
	 *              'cookies' => ['
	 *                  'foo' => 'bar',
	 *              ],
	 *              'allow_redirects' => [
	 *                   'max'       => 10,  // allow at most 10 redirects.
	 *                   'strict'    => true,     // use "strict" RFC compliant redirects.
	 *                   'referer'   => true,     // add a Referer header
	 *                   'protocols' => ['https'] // only allow https URLs
	 *              ],
	 *              'save_to' => '/path/to/file', // save to a file or a stream
	 *              'verify' => true, // bool or string to CA file
	 *              'debug' => true,
	 *              'timeout' => 5,
	 * @return IResponse
	 * @throws \Exception If the request could not get completed
	 */
	public function get(string $uri, array $options = []): IResponse {
		$this->preventLocalAddress($uri, $options);
		$response = $this->client->request('get', $uri, $this->buildRequestOptions($options));
		$isStream = isset($options['stream']) && $options['stream'];
		return new Response($response, $isStream);
	}

	/**
	 * Sends a HEAD request
	 *
	 * @param string $uri
	 * @param array $options Array such as
	 *              'headers' => [
	 *                  'foo' => 'bar',
	 *              ],
	 *              'cookies' => ['
	 *                  'foo' => 'bar',
	 *              ],
	 *              'allow_redirects' => [
	 *                   'max'       => 10,  // allow at most 10 redirects.
	 *                   'strict'    => true,     // use "strict" RFC compliant redirects.
	 *                   'referer'   => true,     // add a Referer header
	 *                   'protocols' => ['https'] // only allow https URLs
	 *              ],
	 *              'save_to' => '/path/to/file', // save to a file or a stream
	 *              'verify' => true, // bool or string to CA file
	 *              'debug' => true,
	 *              'timeout' => 5,
	 * @return IResponse
	 * @throws \Exception If the request could not get completed
	 */
	public function head(string $uri, array $options = []): IResponse {
		$this->preventLocalAddress($uri, $options);
		$response = $this->client->request('head', $uri, $this->buildRequestOptions($options));
		return new Response($response);
	}

	/**
	 * Sends a POST request
	 *
	 * @param string $uri
	 * @param array $options Array such as
	 *              'body' => [
	 *                  'field' => 'abc',
	 *                  'other_field' => '123',
	 *                  'file_name' => fopen('/path/to/file', 'r'),
	 *              ],
	 *              'headers' => [
	 *                  'foo' => 'bar',
	 *              ],
	 *              'cookies' => ['
	 *                  'foo' => 'bar',
	 *              ],
	 *              'allow_redirects' => [
	 *                   'max'       => 10,  // allow at most 10 redirects.
	 *                   'strict'    => true,     // use "strict" RFC compliant redirects.
	 *                   'referer'   => true,     // add a Referer header
	 *                   'protocols' => ['https'] // only allow https URLs
	 *              ],
	 *              'save_to' => '/path/to/file', // save to a file or a stream
	 *              'verify' => true, // bool or string to CA file
	 *              'debug' => true,
	 *              'timeout' => 5,
	 * @return IResponse
	 * @throws \Exception If the request could not get completed
	 */
	public function post(string $uri, array $options = []): IResponse {
		$this->preventLocalAddress($uri, $options);

		if (isset($options['body']) && is_array($options['body'])) {
			$options['form_params'] = $options['body'];
			unset($options['body']);
		}
		$response = $this->client->request('post', $uri, $this->buildRequestOptions($options));
		return new Response($response);
	}

	/**
	 * Sends a PUT request
	 *
	 * @param string $uri
	 * @param array $options Array such as
	 *              'body' => [
	 *                  'field' => 'abc',
	 *                  'other_field' => '123',
	 *                  'file_name' => fopen('/path/to/file', 'r'),
	 *              ],
	 *              'headers' => [
	 *                  'foo' => 'bar',
	 *              ],
	 *              'cookies' => ['
	 *                  'foo' => 'bar',
	 *              ],
	 *              'allow_redirects' => [
	 *                   'max'       => 10,  // allow at most 10 redirects.
	 *                   'strict'    => true,     // use "strict" RFC compliant redirects.
	 *                   'referer'   => true,     // add a Referer header
	 *                   'protocols' => ['https'] // only allow https URLs
	 *              ],
	 *              'save_to' => '/path/to/file', // save to a file or a stream
	 *              'verify' => true, // bool or string to CA file
	 *              'debug' => true,
	 *              'timeout' => 5,
	 * @return IResponse
	 * @throws \Exception If the request could not get completed
	 */
	public function put(string $uri, array $options = []): IResponse {
		$this->preventLocalAddress($uri, $options);
		$response = $this->client->request('put', $uri, $this->buildRequestOptions($options));
		return new Response($response);
	}

	/**
	 * Sends a DELETE request
	 *
	 * @param string $uri
	 * @param array $options Array such as
	 *              'body' => [
	 *                  'field' => 'abc',
	 *                  'other_field' => '123',
	 *                  'file_name' => fopen('/path/to/file', 'r'),
	 *              ],
	 *              'headers' => [
	 *                  'foo' => 'bar',
	 *              ],
	 *              'cookies' => ['
	 *                  'foo' => 'bar',
	 *              ],
	 *              'allow_redirects' => [
	 *                   'max'       => 10,  // allow at most 10 redirects.
	 *                   'strict'    => true,     // use "strict" RFC compliant redirects.
	 *                   'referer'   => true,     // add a Referer header
	 *                   'protocols' => ['https'] // only allow https URLs
	 *              ],
	 *              'save_to' => '/path/to/file', // save to a file or a stream
	 *              'verify' => true, // bool or string to CA file
	 *              'debug' => true,
	 *              'timeout' => 5,
	 * @return IResponse
	 * @throws \Exception If the request could not get completed
	 */
	public function delete(string $uri, array $options = []): IResponse {
		$this->preventLocalAddress($uri, $options);
		$response = $this->client->request('delete', $uri, $this->buildRequestOptions($options));
		return new Response($response);
	}

	/**
	 * Sends a options request
	 *
	 * @param string $uri
	 * @param array $options Array such as
	 *              'body' => [
	 *                  'field' => 'abc',
	 *                  'other_field' => '123',
	 *                  'file_name' => fopen('/path/to/file', 'r'),
	 *              ],
	 *              'headers' => [
	 *                  'foo' => 'bar',
	 *              ],
	 *              'cookies' => ['
	 *                  'foo' => 'bar',
	 *              ],
	 *              'allow_redirects' => [
	 *                   'max'       => 10,  // allow at most 10 redirects.
	 *                   'strict'    => true,     // use "strict" RFC compliant redirects.
	 *                   'referer'   => true,     // add a Referer header
	 *                   'protocols' => ['https'] // only allow https URLs
	 *              ],
	 *              'save_to' => '/path/to/file', // save to a file or a stream
	 *              'verify' => true, // bool or string to CA file
	 *              'debug' => true,
	 *              'timeout' => 5,
	 * @return IResponse
	 * @throws \Exception If the request could not get completed
	 */
	public function options(string $uri, array $options = []): IResponse {
		$this->preventLocalAddress($uri, $options);
		$response = $this->client->request('options', $uri, $this->buildRequestOptions($options));
		return new Response($response);
	}
}
