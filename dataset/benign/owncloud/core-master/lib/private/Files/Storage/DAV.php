<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Carlos Cerrillo <ccerrillo@gmail.com>
 * @author Felix Moeller <mail@felixmoeller.de>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Philipp Kapfer <philipp.kapfer@gmx.at>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OC\Files\Storage;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use OC\Files\Filesystem;
use OC\Files\Stream\Close;
use Icewind\Streams\IteratorDirectory;
use OC\Memcache\ArrayCache;
use OCP\AppFramework\Http;
use OCP\Constants;
use OCP\Files\FileInfo;
use OCP\Files\StorageInvalidException;
use OCP\Files\StorageNotAvailableException;
use OCP\Util;
use Sabre\DAV\Xml\Property\ResourceType;
use Sabre\HTTP\Client;
use Sabre\HTTP\ClientHttpException;
use Sabre\DAV\Exception\InsufficientStorage;
use OCA\DAV\Connector\Sabre\Exception\Forbidden;

/**
 * Class DAV
 *
 * @package OC\Files\Storage
 */
class DAV extends Common {
	/** @var string */
	protected $password;
	/** @var string */
	protected $user;
	/** @var string */
	protected $authType;
	/** @var string */
	protected $host;
	/** @var bool */
	protected $secure;
	/** @var string */
	protected $root;
	/** @var string */
	protected $certPath;
	/** @var bool */
	protected $ready;
	/** @var Client */
	private $client;
	/** @var ArrayCache */
	private $statCache;
	/** @var array */
	private static $tempFiles = [];
	/** @var \OCP\Http\Client\IClientService */
	private $httpClientService;

	/** @var \OCP\Http\Client\IWebDavClientService */
	private $webDavClientService;

	/**
	 * @param array $params
	 * @throws \Exception
	 */
	public function __construct($params) {
		$this->statCache = new ArrayCache();
		$this->httpClientService = \OC::$server->getHTTPClientService();
		$this->webDavClientService = \OC::$server->getWebDavClientService();
		if (isset($params['host'], $params['user'], $params['password'])) {
			$host = $params['host'];
			//remove leading http[s], will be generated in createBaseUri()
			if (\substr($host, 0, 8) == "https://") {
				$host = \substr($host, 8);
			} elseif (\substr($host, 0, 7) == "http://") {
				$host = \substr($host, 7);
			}
			$this->host = $host;
			$this->user = $params['user'];
			$this->password = $params['password'];
			if (isset($params['authType'])) {
				$this->authType = $params['authType'];
			}
			if (isset($params['secure'])) {
				if (\is_string($params['secure'])) {
					$this->secure = ($params['secure'] === 'true');
				} else {
					$this->secure = (bool)$params['secure'];
				}
			} else {
				$this->secure = false;
			}
			$this->root = isset($params['root']) ? $params['root'] : '/';
			if (!$this->root || $this->root[0] != '/') {
				$this->root = '/' . $this->root;
			}
			if (\substr($this->root, -1, 1) != '/') {
				$this->root .= '/';
			}
		} else {
			throw new \InvalidArgumentException('Invalid webdav storage configuration');
		}
	}

	protected function init() {
		if ($this->ready) {
			return;
		}
		$this->ready = true;

		$settings = [
			'baseUri' => $this->createBaseUri(),
			'userName' => $this->user,
			'password' => $this->password
		];
		if (isset($this->authType)) {
			$settings['authType'] = $this->authType;
		}

		$this->client = $this->webDavClientService->newClient($settings);
	}

	/**
	 * Clear the stat cache
	 */
	public function clearStatCache() {
		$this->statCache->clear();
	}

	/** {@inheritdoc} */
	public function getId() {
		return 'webdav::' . $this->user . '@' . $this->host . '/' . $this->root;
	}

	/** {@inheritdoc} */
	public function createBaseUri() {
		$baseUri = 'http';
		if ($this->secure) {
			$baseUri .= 's';
		}
		$baseUri .= '://' . $this->host . $this->root;
		return $baseUri;
	}

	/** {@inheritdoc} */
	public function mkdir($path) {
		$this->init();
		$path = $this->cleanPath($path);
		$result = $this->simpleResponse('MKCOL', $path, null, 201);
		if ($result) {
			$this->statCache->set($path, true);
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function rmdir($path) {
		$this->init();
		$path = $this->cleanPath($path);
		// FIXME: some WebDAV impl return 403 when trying to DELETE
		// a non-empty folder
		$result = $this->simpleResponse('DELETE', $path . '/', null, 204);
		$this->statCache->clear($path . '/');
		$this->statCache->remove($path);
		return $result;
	}

	/** {@inheritdoc} */
	public function opendir($path) {
		$this->init();
		$path = $this->cleanPath($path);
		try {
			// client propfind is in \OC\HTTP\Client
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$response = $this->client->propfind(
				$this->encodePath($path),
				[],
				1
			);
			if ($response === false) {
				return false;
			}
			$content = [];
			$files = \array_keys($response);
			\array_shift($files); //the first entry is the current directory

			if (!$this->statCache->hasKey($path)) {
				$this->statCache->set($path, true);
			}
			foreach ($files as $file) {
				$file = \urldecode($file);
				// do not store the real entry, we might not have all properties
				if (!$this->statCache->hasKey($path)) {
					$this->statCache->set($file, true);
				}
				$file = \basename($file);
				$content[] = $file;
			}
			return IteratorDirectory::wrap($content);
		} catch (ClientHttpException $e) {
			if ($e->getHttpStatus() === Http::STATUS_NOT_FOUND) {
				$this->statCache->clear($path . '/');
				$this->statCache->set($path, false);
				return false;
			}
			$this->convertException($e, $path);
		} catch (\Exception $e) {
			$this->convertException($e, $path);
		}
		return false;
	}

	/**
	 * Propfind call with cache handling.
	 *
	 * First checks if information is cached.
	 * If not, request it from the server then store to cache.
	 *
	 * @param string $path path to propfind
	 *
	 * @return array|boolean propfind response or false if the entry was not found
	 *
	 * @throws ClientHttpException
	 */
	protected function propfind($path) {
		$path = $this->cleanPath($path);
		$cachedResponse = $this->statCache->get($path);
		// we either don't know it, or we know it exists but need more details
		if ($cachedResponse === null || $cachedResponse === true) {
			$this->init();
			$response = false;
			try {
				// client propfind is in \OC\HTTP\Client
				/* @phan-suppress-next-line PhanUndeclaredMethod */
				$response = $this->client->propfind(
					$this->encodePath($path),
					[
						'{DAV:}getlastmodified',
						'{DAV:}getcontentlength',
						'{DAV:}getcontenttype',
						'{http://owncloud.org/ns}permissions',
						'{http://open-collaboration-services.org/ns}share-permissions',
						'{DAV:}resourcetype',
						'{DAV:}getetag',
					]
				);
				$this->statCache->set($path, $response);
			} catch (ClientHttpException $e) {
				if ($e->getHttpStatus() === Http::STATUS_NOT_FOUND) {
					$this->statCache->clear($path . '/');
					$this->statCache->set($path, false);
				} else {
					$this->convertException($e, $path);
				}
			} catch (\Exception $e) {
				if ($e->getCode() === \CURLE_COULDNT_CONNECT || $e->getCode() === \CURLE_OPERATION_TIMEDOUT) {
					\OC::$server->getLogger()->warning(
						'Storage is not available due to the connection timeout to {hostName}',
						['hostName' => $this->host]
					);
				} else {
					$this->convertException($e, $path);
				}
			}
		} else {
			$response = $cachedResponse;
		}
		return $response;
	}

	/** {@inheritdoc} */
	public function filetype($path) {
		try {
			$response = $this->propfind($path);
			if ($response === false) {
				return false;
			}
			$responseType = [];
			if (isset($response["{DAV:}resourcetype"])) {
				/** @var ResourceType[] $response */
				$responseType = $response["{DAV:}resourcetype"]->getValue();
			}
			return (\count($responseType) > 0 and $responseType[0] == "{DAV:}collection") ? 'dir' : 'file';
		} catch (\Exception $e) {
			$this->convertException($e, $path);
		}
		return false;
	}

	/** {@inheritdoc} */
	public function file_exists($path) {
		try {
			$path = $this->cleanPath($path);
			$cachedState = $this->statCache->get($path);
			if ($cachedState === false) {
				// we know the file doesn't exist
				return false;
			} elseif ($cachedState !== null) {
				return true;
			}
			// need to get from server
			return ($this->propfind($path) !== false);
		} catch (\Exception $e) {
			$this->convertException($e, $path);
		}
		return false;
	}

	/** {@inheritdoc} */
	public function unlink($path) {
		$this->init();
		$path = $this->cleanPath($path);
		$result = $this->simpleResponse('DELETE', $path, null, 204);
		$this->statCache->clear($path . '/');
		$this->statCache->remove($path);
		return $result;
	}

	/** {@inheritdoc} */
	public function fopen($path, $mode) {
		$this->init();
		$path = $this->cleanPath($path);
		switch ($mode) {
			case 'r':
			case 'rb':
				try {
					$response = $this->httpClientService
							->newClient()
							->get($this->createBaseUri() . $this->encodePath($path), [
									'auth' => [$this->user, $this->password],
									'stream' => true,
									'config' => [
										'stream_context' => [
											'http' => [
												'request_fulluri' => true
											]
										],
									],
							]);
				} catch (RequestException $e) {
					if ($e->getResponse() instanceof ResponseInterface
						&& $e->getResponse()->getStatusCode() === Http::STATUS_NOT_FOUND) {
						return false;
					} else {
						$this->convertException($e);
					}
				}

				if ($response->getStatusCode() !== Http::STATUS_OK) {
					if ($response->getStatusCode() === Http::STATUS_LOCKED) {
						throw new \OCP\Lock\LockedException($path);
					} else {
						Util::writeLog("webdav client", 'Guzzle get returned status code ' . $response->getStatusCode(), Util::ERROR);
						// FIXME: why not returning false here ?!
					}
				}

				return $response->getBody();
			case 'w':
			case 'wb':
			case 'a':
			case 'ab':
			case 'r+':
			case 'w+':
			case 'wb+':
			case 'a+':
			case 'x':
			case 'x+':
			case 'c':
			case 'c+':
				//emulate these
				$tempManager = \OC::$server->getTempManager();
				if (\strrpos($path, '.') !== false) {
					$ext = \substr($path, \strrpos($path, '.'));
				} else {
					$ext = '';
				}
				if ($this->file_exists($path)) {
					if (!$this->isUpdatable($path)) {
						return false;
					}
					if ($mode === 'w' or $mode === 'w+') {
						$tmpFile = $tempManager->getTemporaryFile($ext);
					} else {
						$tmpFile = $this->getCachedFile($path);
					}
				} else {
					if (!$this->isCreatable(\dirname($path))) {
						return false;
					}
					$tmpFile = $tempManager->getTemporaryFile($ext);
				}
				Close::registerCallback($tmpFile, [$this, 'writeBack']);
				self::$tempFiles[$tmpFile] = $path;
				return \fopen('close://' . $tmpFile, $mode);
		}
	}

	/**
	 * @param string $tmpFile
	 */
	public function writeBack($tmpFile) {
		if (isset(self::$tempFiles[$tmpFile])) {
			$this->uploadFile($tmpFile, self::$tempFiles[$tmpFile]);
			\unlink($tmpFile);
		}
	}

	/** {@inheritdoc} */
	public function free_space($path) {
		$this->init();
		$path = $this->cleanPath($path);
		try {
			// TODO: cacheable ?
			// client propfind is in \OC\HTTP\Client
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$response = $this->client->propfind($this->encodePath($path), ['{DAV:}quota-available-bytes']);
			if ($response === false) {
				return FileInfo::SPACE_UNKNOWN;
			}
			if (isset($response['{DAV:}quota-available-bytes'])) {
				return (int)$response['{DAV:}quota-available-bytes'];
			} else {
				return FileInfo::SPACE_UNKNOWN;
			}
		} catch (\Exception $e) {
			return FileInfo::SPACE_UNKNOWN;
		}
	}

	/** {@inheritdoc} */
	public function touch($path, $mtime = null) {
		$this->init();
		if ($mtime === null) {
			$mtime = \OC::$server->getTimeFactory()->getTime();
		}
		$path = $this->cleanPath($path);

		// if file exists, update the mtime, else create a new empty file
		if ($this->file_exists($path)) {
			try {
				$this->statCache->remove($path);
				// client proppatch is in \OC\HTTP\Client
				/* @phan-suppress-next-line PhanUndeclaredMethod */
				$this->client->proppatch($this->encodePath($path), ['{DAV:}lastmodified' => $mtime]);
				// non-owncloud clients might not have accepted the property, need to recheck it
				// client propfind is in \OC\HTTP\Client
				/* @phan-suppress-next-line PhanUndeclaredMethod */
				$response = $this->client->propfind($this->encodePath($path), ['{DAV:}getlastmodified'], 0);
				if ($response === false) {
					// file disappeared since ?
					return false;
				}
				if (isset($response['{DAV:}getlastmodified'])) {
					$remoteMtime = \strtotime($response['{DAV:}getlastmodified']);
					if ($remoteMtime !== $mtime) {
						// server has not accepted the mtime
						return false;
					}
				}
			} catch (ClientHttpException $e) {
				if ($e->getHttpStatus() === 501) {
					return false;
				}
				$this->convertException($e, $path);
				return false;
			} catch (\Exception $e) {
				$this->convertException($e, $path);
				return false;
			}
		} else {
			$this->file_put_contents($path, '');
		}
		return true;
	}

	/**
	 * @param string $path
	 * @param string $data
	 * @return int
	 */
	public function file_put_contents($path, $data) {
		$path = $this->cleanPath($path);
		$result = parent::file_put_contents($path, $data);
		$this->statCache->remove($path);
		return $result;
	}

	/**
	 * @param string $path
	 * @param string $target
	 */
	protected function uploadFile($path, $target) {
		$this->init();

		// invalidate
		$target = $this->cleanPath($target);
		$this->statCache->remove($target);
		$source = \fopen($path, 'r');

		$this->removeCachedFile($target);
		try {
			$this->httpClientService
				->newClient()
				->put($this->createBaseUri() . $this->encodePath($target), [
					'body' => $source,
					'auth' => [$this->user, $this->password]
				]);
		} catch (\Exception $e) {
			$this->convertException($e);
		}
	}

	/** {@inheritdoc} */
	public function rename($path1, $path2) {
		$this->init();
		$path1 = $this->cleanPath($path1);
		$path2 = $this->cleanPath($path2);
		try {
			// overwrite directory ?
			if ($this->is_dir($path2)) {
				// needs trailing slash in destination
				$path2 = \rtrim($path2, '/') . '/';
			}
			// client request is in \OC\HTTP\Client
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$this->client->request(
				'MOVE',
				$this->encodePath($path1),
				null,
				[
					'Destination' => $this->createBaseUri() . $this->encodePath($path2),
				]
			);
			$this->statCache->clear($path1 . '/');
			$this->statCache->clear($path2 . '/');
			$this->statCache->set($path1, false);
			$this->statCache->set($path2, true);
			$this->removeCachedFile($path1);
			$this->removeCachedFile($path2);
			return true;
		} catch (\Exception $e) {
			$this->convertException($e);
		}
		return false;
	}

	/** {@inheritdoc} */
	public function copy($path1, $path2) {
		$this->init();
		$path1 = $this->cleanPath($path1);
		$path2 = $this->cleanPath($path2);
		try {
			// overwrite directory ?
			if ($this->is_dir($path2)) {
				// needs trailing slash in destination
				$path2 = \rtrim($path2, '/') . '/';
			}
			// client request is in \OC\HTTP\Client
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$this->client->request(
				'COPY',
				$this->encodePath($path1),
				null,
				[
					'Destination' => $this->createBaseUri() . $this->encodePath($path2),
				]
			);
			$this->statCache->clear($path2 . '/');
			$this->statCache->set($path2, true);
			$this->removeCachedFile($path2);
			return true;
		} catch (\Exception $e) {
			$this->convertException($e);
		}
		return false;
	}

	/** {@inheritdoc} */
	public function stat($path) {
		try {
			$response = $this->propfind($path);
			if ($response === false) {
				return [];
			}
			return [
				'mtime' => \strtotime($response['{DAV:}getlastmodified']),
				'size' => (int)isset($response['{DAV:}getcontentlength']) ? $response['{DAV:}getcontentlength'] : 0,
			];
		} catch (\Exception $e) {
			$this->convertException($e, $path);
		}
		return [];
	}

	/** {@inheritdoc} */
	public function getMimeType($path) {
		try {
			$response = $this->propfind($path);
			if ($response === false) {
				return false;
			}
			$responseType = [];
			if (isset($response["{DAV:}resourcetype"])) {
				/** @var ResourceType[] $response */
				$responseType = $response["{DAV:}resourcetype"]->getValue();
			}
			$type = (\count($responseType) > 0 and $responseType[0] == "{DAV:}collection") ? 'dir' : 'file';
			if ($type == 'dir') {
				return 'httpd/unix-directory';
			} elseif (isset($response['{DAV:}getcontenttype'])) {
				return $response['{DAV:}getcontenttype'];
			} else {
				return false;
			}
		} catch (\Exception $e) {
			$this->convertException($e, $path);
		}
		return false;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function cleanPath($path) {
		if ($path === '') {
			return $path;
		}
		$path = Filesystem::normalizePath($path);
		// remove leading slash
		return \substr($path, 1);
	}

	/**
	 * URL encodes the given path but keeps the slashes
	 *
	 * @param string $path to encode
	 * @return string encoded path
	 */
	private function encodePath($path) {
		// slashes need to stay
		return \str_replace('%2F', '/', \rawurlencode($path));
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param string|resource|null $body
	 * @param int $expected
	 * @return bool
	 * @throws StorageInvalidException
	 * @throws StorageNotAvailableException
	 */
	private function simpleResponse($method, $path, $body, $expected) {
		$path = $this->cleanPath($path);
		try {
			// client request is in \OC\HTTP\Client
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			$response = $this->client->request($method, $this->encodePath($path), $body);
			return isset($response['statusCode']) && $response['statusCode'] == $expected;
		} catch (ClientHttpException $e) {
			if ($e->getHttpStatus() === Http::STATUS_NOT_FOUND && $method === 'DELETE') {
				$this->statCache->clear($path . '/');
				$this->statCache->set($path, false);
				return false;
			}
			if ($e->getHttpStatus() === Http::STATUS_METHOD_NOT_ALLOWED && $method === 'MKCOL') {
				return false;
			}

			$this->convertException($e, $path);
		} catch (\Exception $e) {
			$this->convertException($e, $path);
		}
		return false;
	}

	/**
	 * check if curl is installed
	 */
	public static function checkDependencies() {
		return true;
	}

	/** {@inheritdoc} */
	public function isUpdatable($path) {
		return (bool)($this->getPermissions($path) & Constants::PERMISSION_UPDATE);
	}

	/** {@inheritdoc} */
	public function isCreatable($path) {
		return (bool)($this->getPermissions($path) & Constants::PERMISSION_CREATE);
	}

	/** {@inheritdoc} */
	public function isSharable($path) {
		return (bool)($this->getPermissions($path) & Constants::PERMISSION_SHARE);
	}

	/** {@inheritdoc} */
	public function isDeletable($path) {
		return (bool)($this->getPermissions($path) & Constants::PERMISSION_DELETE);
	}

	/** {@inheritdoc} */
	public function getPermissions($path) {
		$this->init();
		$path = $this->cleanPath($path);
		$response = $this->propfind($path);
		if ($response === false) {
			return 0;
		}
		if (isset($response['{http://owncloud.org/ns}permissions'])) {
			return $this->parsePermissions($response['{http://owncloud.org/ns}permissions']);
		} elseif ($this->is_dir($path)) {
			return Constants::PERMISSION_ALL;
		} elseif ($this->file_exists($path)) {
			return Constants::PERMISSION_ALL - Constants::PERMISSION_CREATE;
		} else {
			return 0;
		}
	}

	/** {@inheritdoc} */
	public function getETag($path) {
		$this->init();
		$path = $this->cleanPath($path);
		$response = $this->propfind($path);
		if ($response === false) {
			return null;
		}
		if (isset($response['{DAV:}getetag'])) {
			return \trim($response['{DAV:}getetag'], '"');
		}
		return parent::getETag($path);
	}

	/**
	 * @param string $permissionsString
	 * @return int
	 */
	protected function parsePermissions($permissionsString) {
		$permissions = Constants::PERMISSION_READ;
		if (\strpos($permissionsString, 'R') !== false) {
			$permissions |= Constants::PERMISSION_SHARE;
		}
		if (\strpos($permissionsString, 'D') !== false) {
			$permissions |= Constants::PERMISSION_DELETE;
		}
		if (\strpos($permissionsString, 'W') !== false) {
			$permissions |= Constants::PERMISSION_UPDATE;
		}
		if (\strpos($permissionsString, 'CK') !== false) {
			$permissions |= Constants::PERMISSION_CREATE;
			$permissions |= Constants::PERMISSION_UPDATE;
		}
		return $permissions;
	}

	/**
	 * check if a file or folder has been updated since $time
	 *
	 * @param string $path
	 * @param int $time
	 * @throws \OCP\Files\StorageNotAvailableException
	 * @return bool
	 */
	public function hasUpdated($path, $time) {
		$this->init();
		$path = $this->cleanPath($path);
		try {
			// force refresh for $path
			$this->statCache->remove($path);
			$response = $this->propfind($path);
			if ($response === false) {
				if ($path === '') {
					// if root is gone it means the storage is not available
					throw new StorageNotAvailableException('remote root vanished');
				}
				return false;
			}
			if (isset($response['{DAV:}getetag'])) {
				$etag = \trim($response['{DAV:}getetag'], '"');
				$cachedData = $this->getCache()->get($path);
				$cachedEtag = $cachedData['etag'] ?? null;
				if (!empty($etag) && $cachedEtag !== $etag) {
					return true;
				} elseif (isset($response['{http://open-collaboration-services.org/ns}share-permissions'])) {
					$sharePermissions = (int)$response['{http://open-collaboration-services.org/ns}share-permissions'];
					return $sharePermissions !== $cachedData['permissions'];
				} elseif (isset($response['{http://owncloud.org/ns}permissions'])) {
					$permissions = $this->parsePermissions($response['{http://owncloud.org/ns}permissions']);
					$cachedPermissions = $cachedData['permissions'] ?? null;
					return $permissions !== $cachedPermissions;
				} else {
					return false;
				}
			} else {
				$remoteMtime = \strtotime($response['{DAV:}getlastmodified']);
				return $remoteMtime > $time;
			}
		} catch (ClientHttpException $e) {
			if ($e->getHttpStatus() === 405) {
				if ($path === '') {
					// if root is gone it means the storage is not available
					throw new StorageNotAvailableException(\get_class($e) . ': ' . $e->getMessage());
				}
				return false;
			}
			$this->convertException($e, $path);
			return false;
		} catch (\Exception $e) {
			$this->convertException($e, $path);
			return false;
		}
	}

	/**
	 * Interpret the given exception and decide whether it is due to an
	 * unavailable storage, invalid storage or other.
	 * This will either throw StorageInvalidException, StorageNotAvailableException
	 * or do nothing.
	 *
	 * @param Exception $e sabre exception
	 * @param string $path optional path from the operation
	 *
	 * @throws StorageInvalidException if the storage is invalid, for example
	 * when the authentication expired or is invalid
	 * @throws StorageNotAvailableException if the storage is not available,
	 * which might be temporary
	 */
	private function convertException(Exception $e, $path = '') {
		\OC::$server->getLogger()->logException($e);
		Util::writeLog('files_external', $e->getMessage(), Util::ERROR);
		if ($e instanceof ClientHttpException) {
			$this->throwByStatusCode($e->getHttpStatus(), $e, $path);
		} elseif ($e instanceof \GuzzleHttp\Exception\RequestException) {
			if ($e->getResponse() instanceof ResponseInterface) {
				$this->throwByStatusCode($e->getResponse()->getStatusCode(), $e);
			}
			// connection timeout or refused, server could be temporarily down
			throw new StorageNotAvailableException(\get_class($e) . ': ' . $e->getMessage());
		} elseif ($e instanceof \InvalidArgumentException) {
			// parse error because the server returned HTML instead of XML,
			// possibly temporarily down
			throw new StorageNotAvailableException(\get_class($e) . ': ' . $e->getMessage());
		} elseif (($e instanceof StorageNotAvailableException)
			|| ($e instanceof StorageInvalidException)
			|| ($e instanceof \Sabre\DAV\Exception
		)) {
			// rethrow
			throw $e;
		}

		// TODO: only log for now, but in the future need to wrap/rethrow exception
	}

	/**
	 * Throw exception by status code
	 *
	 * @param int $statusCode status code
	 * @param string $path optional path for some exceptions
	 * @throws \Exception Sabre or ownCloud exceptions
	 */
	private function throwByStatusCode($statusCode, $e, $path = '') {
		switch ($statusCode) {
			case Http::STATUS_LOCKED:
				throw new \OCP\Lock\LockedException($path);
			case Http::STATUS_UNAUTHORIZED:
				// either password was changed or was invalid all along
				throw new StorageInvalidException(\get_class($e) . ': ' . $e->getMessage());
			case Http::STATUS_INSUFFICIENT_STORAGE:
				throw new InsufficientStorage();
			case Http::STATUS_FORBIDDEN:
				throw new Forbidden('Forbidden');
		}
		throw new StorageNotAvailableException(\get_class($e) . ': ' . $e->getMessage());
	}
}
