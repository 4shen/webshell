<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Michael Jobst <mjobst+github@tecratech.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OCA\DAV\Connector\Sabre;

use OC\AppFramework\Http\Request;
use OCA\DAV\Files\IProvidesAdditionalHeaders;
use OCA\DAV\Meta\MetaFile;
use OCP\Files\ForbiddenException;
use OCP\Files\StorageNotAvailableException;
use OCP\IConfig;
use OCP\IRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\IFile;
use Sabre\DAV\PropFind;
use Sabre\DAV\PropPatch;
use Sabre\DAV\ServerPlugin;
use Sabre\DAV\Tree;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class FilesPlugin extends ServerPlugin {

	// namespace
	const NS_OWNCLOUD = 'http://owncloud.org/ns';
	const FILEID_PROPERTYNAME = '{http://owncloud.org/ns}id';
	const INTERNAL_FILEID_PROPERTYNAME = '{http://owncloud.org/ns}fileid';
	const PERMISSIONS_PROPERTYNAME = '{http://owncloud.org/ns}permissions';
	const SHARE_PERMISSIONS_PROPERTYNAME = '{http://open-collaboration-services.org/ns}share-permissions';
	const DOWNLOADURL_PROPERTYNAME = '{http://owncloud.org/ns}downloadURL';
	const SIZE_PROPERTYNAME = '{http://owncloud.org/ns}size';
	const GETETAG_PROPERTYNAME = '{DAV:}getetag';
	const LASTMODIFIED_PROPERTYNAME = '{DAV:}lastmodified';
	const OWNER_ID_PROPERTYNAME = '{http://owncloud.org/ns}owner-id';
	const OWNER_DISPLAY_NAME_PROPERTYNAME = '{http://owncloud.org/ns}owner-display-name';
	const CHECKSUMS_PROPERTYNAME = '{http://owncloud.org/ns}checksums';
	const DATA_FINGERPRINT_PROPERTYNAME = '{http://owncloud.org/ns}data-fingerprint';
	const PRIVATE_LINK_PROPERTYNAME = '{http://owncloud.org/ns}privatelink';

	/**
	 * Reference to main server object
	 *
	 * @var \Sabre\DAV\Server
	 */
	private $server;

	/**
	 * @var Tree
	 */
	private $tree;

	/**
	 * Whether this is public webdav.
	 * If true, some returned information will be stripped off.
	 *
	 * @var bool
	 */
	private $isPublic;

	/**
	 * @var bool
	 */
	private $downloadAttachment;

	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 * @param Tree $tree
	 * @param IConfig $config
	 * @param IRequest $request
	 * @param bool $isPublic
	 * @param bool $downloadAttachment
	 */
	public function __construct(Tree $tree,
								IConfig $config,
								IRequest $request,
								$isPublic = false,
								$downloadAttachment = true) {
		$this->tree = $tree;
		$this->config = $config;
		$this->request = $request;
		$this->isPublic = $isPublic;
		$this->downloadAttachment = $downloadAttachment;
	}

	/**
	 * This initializes the plugin.
	 *
	 * This function is called by \Sabre\DAV\Server, after
	 * addPlugin is called.
	 *
	 * This method should set up the required event subscriptions.
	 *
	 * @param \Sabre\DAV\Server $server
	 * @return void
	 */
	public function initialize(\Sabre\DAV\Server $server) {
		$server->xml->namespaceMap[self::NS_OWNCLOUD] = 'oc';
		$server->protectedProperties[] = self::FILEID_PROPERTYNAME;
		$server->protectedProperties[] = self::INTERNAL_FILEID_PROPERTYNAME;
		$server->protectedProperties[] = self::PERMISSIONS_PROPERTYNAME;
		$server->protectedProperties[] = self::SHARE_PERMISSIONS_PROPERTYNAME;
		$server->protectedProperties[] = self::SIZE_PROPERTYNAME;
		$server->protectedProperties[] = self::DOWNLOADURL_PROPERTYNAME;
		$server->protectedProperties[] = self::OWNER_ID_PROPERTYNAME;
		$server->protectedProperties[] = self::OWNER_DISPLAY_NAME_PROPERTYNAME;
		$server->protectedProperties[] = self::CHECKSUMS_PROPERTYNAME;
		$server->protectedProperties[] = self::DATA_FINGERPRINT_PROPERTYNAME;
		$server->protectedProperties[] = self::PRIVATE_LINK_PROPERTYNAME;

		// normally these cannot be changed (RFC4918), but we want them modifiable through PROPPATCH
		$allowedProperties = ['{DAV:}getetag'];
		$server->protectedProperties = \array_diff($server->protectedProperties, $allowedProperties);

		$this->server = $server;
		$this->server->on('propFind', [$this, 'handleGetProperties']);
		$this->server->on('propPatch', [$this, 'handleUpdateProperties']);
		$this->server->on('afterBind', [$this, 'sendFileIdHeader']);
		$this->server->on('afterWriteContent', [$this, 'sendFileIdHeader']);
		$this->server->on('afterMethod:GET', [$this, 'httpGet']);
		$this->server->on('afterMethod:GET', [$this, 'handleDownloadToken']);
		$this->server->on('exception', [$this, 'handleDownloadFailure']);
		$this->server->on('afterResponse', function ($request, ResponseInterface $response) {
			$body = $response->getBody();
			if (\is_resource($body)) {
				\fclose($body);
			}
		});
		$this->server->on('beforeMove', [$this, 'checkMove']);
	}

	/**
	 * Plugin that checks if a move can actually be performed.
	 *
	 * @param string $source source path
	 * @param string $destination destination path
	 * @throws Forbidden
	 * @throws NotFound
	 */
	public function checkMove($source, $destination) {
		$sourceNode = $this->tree->getNodeForPath($source);
		if (!$sourceNode instanceof Node) {
			return;
		}
		list($sourceDir, ) = \Sabre\Uri\split($source);
		list($destinationDir, ) = \Sabre\Uri\split($destination);

		if ($sourceDir !== $destinationDir) {
			$sourceNodeFileInfo = $sourceNode->getFileInfo();
			if ($sourceNodeFileInfo === null) {
				throw new NotFound($source . ' does not exist');
			}

			if (!$sourceNodeFileInfo->isDeletable()) {
				throw new Forbidden($source . " cannot be deleted");
			}
		}
	}

	/**
	 * This sets a cookie to be able to recognize the failure of the download
	 *
	 * @param \Throwable $ex
	 */
	public function handleDownloadFailure(\Throwable $ex) {
		$queryParams = $this->server->httpRequest->getQueryParameters();

		if (isset($queryParams['downloadStartSecret'])) {
			$this->setSecretCookie('ocDownloadStarted', '-1');
		}
	}

	/**
	 * This sets a cookie to be able to recognize the start of the download
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 */
	public function handleDownloadToken(RequestInterface $request, ResponseInterface $response) {
		$queryParams = $request->getQueryParameters();

		if (isset($queryParams['downloadStartSecret'])) {
			$this->setSecretCookie('ocDownloadStarted', $queryParams['downloadStartSecret']);
		}
	}

	/**
	 * Add headers to file download
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 */
	public function httpGet(RequestInterface $request, ResponseInterface $response) {
		// Only handle valid files
		$node = $this->tree->getNodeForPath($request->getPath());
		if (!($node instanceof IFile)) {
			return;
		}

		// adds a 'Content-Disposition: attachment' header
		if ($this->downloadAttachment) {
			$filename = $node->getName();
			if ($node instanceof IProvidesAdditionalHeaders) {
				$filename = $node->getContentDispositionFileName();
			}
			if ($this->request->isUserAgent(
				[
					Request::USER_AGENT_IE,
					Request::USER_AGENT_ANDROID_MOBILE_CHROME,
					Request::USER_AGENT_FREEBOX,
				])) {
				$response->setHeader('Content-Disposition', 'attachment; filename="' . \rawurlencode($filename) . '"');
			} else {
				$response->setHeader('Content-Disposition', 'attachment; filename*=UTF-8\'\'' . \rawurlencode($filename)
													 . '; filename="' . \rawurlencode($filename) . '"');
			}
		}

		if ($node instanceof \OCA\DAV\Connector\Sabre\File) {
			//Add OC-Checksum header
			/** @var $node File */
			$checksum = $node->getChecksum('sha1');
			if ($checksum !== null && $checksum !== '') {
				$response->addHeader('OC-Checksum', $checksum);
			}
			// disable nginx buffering so big downloads through ownCloud won't
			// cause memory problems in the nginx process.
			$response->addHeader('X-Accel-Buffering', 'no');
		}

		if ($node instanceof IProvidesAdditionalHeaders) {
			$headers = $node->getHeaders();
			if (\is_array($headers)) {
				$response->addHeaders($headers);
			}
		}
	}

	/**
	 * Adds all ownCloud-specific properties
	 *
	 * @param PropFind $propFind
	 * @param \Sabre\DAV\INode $node
	 * @return void
	 */
	public function handleGetProperties(PropFind $propFind, \Sabre\DAV\INode $node) {
		$httpRequest = $this->server->httpRequest;

		if ($node instanceof \OCA\DAV\Connector\Sabre\Node) {
			if (!$node->getFileInfo()->isReadable()) {
				// avoid detecting files through this means
				throw new NotFound();
			}

			$propFind->handle(self::FILEID_PROPERTYNAME, function () use ($node) {
				return $node->getFileId();
			});

			$propFind->handle(self::INTERNAL_FILEID_PROPERTYNAME, function () use ($node) {
				return $node->getInternalFileId();
			});

			$propFind->handle(self::PERMISSIONS_PROPERTYNAME, function () use ($node) {
				$perms = $node->getDavPermissions();
				if ($this->isPublic) {
					// remove mount information
					$perms = \str_replace(['S', 'M'], '', $perms);
				}
				return $perms;
			});

			$propFind->handle(self::SHARE_PERMISSIONS_PROPERTYNAME, function () use ($node, $httpRequest) {
				try {
					return $node->getSharePermissions(
						$httpRequest->getRawServerValue('PHP_AUTH_USER')
					);
				} catch (StorageNotAvailableException $ex) {
					return null;
				}
			});

			$propFind->handle(self::GETETAG_PROPERTYNAME, function () use ($node) {
				return $node->getETag();
			});

			$propFind->handle(self::OWNER_ID_PROPERTYNAME, function () use ($node) {
				$owner = $node->getOwner();
				return $owner->getUID();
			});
			$propFind->handle(self::OWNER_DISPLAY_NAME_PROPERTYNAME, function () use ($node) {
				$owner = $node->getOwner();
				$displayName = $owner->getDisplayName();
				return $displayName;
			});
			$propFind->handle(self::SIZE_PROPERTYNAME, function () use ($node) {
				return $node->getSize();
			});

			$propFind->handle(self::PRIVATE_LINK_PROPERTYNAME, function () use ($node) {
				return \OC::$server->getURLGenerator()
					->linkToRouteAbsolute('files.viewcontroller.showFile', ['fileId' => $node->getInternalFileId()]);
			});
		}

		if ($node instanceof \OCA\DAV\Connector\Sabre\Node) {
			$propFind->handle(self::DATA_FINGERPRINT_PROPERTYNAME, function () {
				return $this->config->getSystemValue('data-fingerprint', '');
			});
		}

		if ($node instanceof \OCA\DAV\Connector\Sabre\File) {
			$propFind->handle(self::DOWNLOADURL_PROPERTYNAME, function () use ($node) {
				/** @var $node \OCA\DAV\Connector\Sabre\File */
				try {
					$directDownloadUrl = $node->getDirectDownload();
					if (isset($directDownloadUrl['url'])) {
						return $directDownloadUrl['url'];
					}
				} catch (StorageNotAvailableException $e) {
					return false;
				} catch (ForbiddenException $e) {
					return false;
				}
				return false;
			});

			$propFind->handle(self::CHECKSUMS_PROPERTYNAME, function () use ($node) {
				$checksum = $node->getChecksum();
				if ($checksum === null || $checksum === '') {
					return null;
				}

				return new ChecksumList($checksum);
			});
		}

		if ($node instanceof \OCA\DAV\Connector\Sabre\Directory) {
			$propFind->handle(self::SIZE_PROPERTYNAME, function () use ($node) {
				return $node->getSize();
			});
		}
	}

	/**
	 * Update ownCloud-specific properties
	 *
	 * @param string $path
	 * @param PropPatch $propPatch
	 *
	 * @return void
	 */
	public function handleUpdateProperties($path, PropPatch $propPatch) {
		$node = $this->tree->getNodeForPath($path);
		if (!($node instanceof \OCA\DAV\Connector\Sabre\Node)) {
			return;
		}

		$propPatch->handle(self::LASTMODIFIED_PROPERTYNAME, function ($time) use ($node) {
			if (empty($time)) {
				return false;
			}
			$node->touch($time);
			return true;
		});
		$propPatch->handle(self::GETETAG_PROPERTYNAME, function ($etag) use ($node) {
			if (empty($etag)) {
				return false;
			}
			if ($node->setEtag($etag) !== -1) {
				return true;
			}
			return false;
		});
	}

	/**
	 * @param string $filePath
	 * @param \Sabre\DAV\INode $node
	 * @throws \Sabre\DAV\Exception\BadRequest
	 */
	public function sendFileIdHeader($filePath, \Sabre\DAV\INode $node = null) {
		// chunked upload handling
		if (\OC_FileChunking::isWebdavChunk()) {
			list($path, $name) = \Sabre\Uri\split($filePath);
			$info = \OC_FileChunking::decodeName($name);
			if (!empty($info)) {
				$filePath = $path . '/' . $info['name'];
			}
		}

		// we get the node for the given $filePath here because in case of afterCreateFile $node is the parent folder
		if (!$this->server->tree->nodeExists($filePath)) {
			return;
		}
		$node = $this->server->tree->getNodeForPath($filePath);
		if ($node instanceof \OCA\DAV\Connector\Sabre\Node) {
			$fileId = $node->getFileId();
			if ($fileId !== null) {
				$this->server->httpResponse->setHeader('OC-FileId', $fileId);
			}
		}
	}

	/**
	 * This sets a cookie, which content must not be longer than 32 characters and must only contain
	 * alphanumeric characters if request is successfuk, or -1 if request failed.
	 *
	 * @param $secretName
	 * @param $secretToken
	 */
	private function setSecretCookie($secretName, $secretToken) {
		if ($secretToken === '-1' || (!isset($secretToken[32])
			&& \preg_match('!^[a-zA-Z0-9]+$!', $secretToken) === 1)) {
			// FIXME: use $response->setHeader() instead
			\setcookie($secretName, $secretToken, \time() + 20, '/');
		}
	}
}
