<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Martin Mattel <martin.mattel@diemattels.at>
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace OCA\DAV\Connector\Sabre;

use OC\Files\FileInfo;
use OCA\DAV\Connector\Sabre\Exception\FileLocked;
use OCA\DAV\Connector\Sabre\Exception\Forbidden;
use OCA\DAV\Connector\Sabre\Exception\InvalidPath;
use OCP\Files\ForbiddenException;
use OCP\Files\StorageInvalidException;
use OCP\Files\StorageNotAvailableException;
use OCP\Lock\LockedException;

class ObjectTree extends \Sabre\DAV\Tree {

	/**
	 * @var \OC\Files\View
	 */
	protected $fileView;

	/**
	 * @var  \OCP\Files\Mount\IMountManager
	 */
	protected $mountManager;

	/**
	 * Creates the object
	 */
	public function __construct() {
	}

	/**
	 * @param \Sabre\DAV\INode $rootNode
	 * @param \OC\Files\View $view
	 * @param  \OCP\Files\Mount\IMountManager $mountManager
	 */
	public function init(\Sabre\DAV\INode $rootNode, \OC\Files\View $view, \OCP\Files\Mount\IMountManager $mountManager) {
		$this->rootNode = $rootNode;
		$this->fileView = $view;
		$this->mountManager = $mountManager;
	}

	/**
	 * If the given path is a chunked file name, converts it
	 * to the real file name. Only applies if the OC-CHUNKED header
	 * is present.
	 *
	 * @param string $path chunk file path to convert
	 *
	 * @return string path to real file
	 */
	private function resolveChunkFile($path) {
		if (\OC_FileChunking::isWebdavChunk()) {
			// resolve to real file name to find the proper node
			list($dir, $name) = \Sabre\Uri\split($path);
			if ($dir == '/' || $dir == '.') {
				$dir = '';
			}

			$info = \OC_FileChunking::decodeName($name);
			// only replace path if it was really the chunked file
			if (isset($info['transferid'])) {
				// getNodePath is called for multiple nodes within a chunk
				// upload call
				$path = $dir . '/' . $info['name'];
				$path = \ltrim($path, '/');
			}
		}
		return $path;
	}

	public function cacheNode(Node $node) {
		$this->cache[\trim($node->getPath(), '/')] = $node;
	}

	/**
	 * This function allows you to check if a node exists.
	 *
	 * @param string $path
	 * @return bool
	 */
	public function nodeExists($path) {
		$path = \trim($path, '/');
		if (isset($this->cache[$path]) && $this->cache[$path] === false) {
			// Node is not existing, as it was explicitely set in the cache
			// Next call to getNodeForPath will create cache instance and unset the cached value
			return false;
		}
		return parent::nodeExists($path);
	}

	/**
	 * Returns the INode object for the requested path
	 *
	 * @param string $path
	 * @return \Sabre\DAV\INode
	 * @throws InvalidPath
	 * @throws \Sabre\DAV\Exception\Locked
	 * @throws \Sabre\DAV\Exception\NotFound
	 * @throws \Sabre\DAV\Exception\Forbidden
	 * @throws \Sabre\DAV\Exception\ServiceUnavailable
	 */
	public function getNodeForPath($path) {
		if (!$this->fileView) {
			throw new \Sabre\DAV\Exception\ServiceUnavailable('filesystem not setup');
		}

		$path = \trim($path, '/');

		if (isset($this->cache[$path]) && $this->cache[$path] !== false) {
			return $this->cache[$path];
		}

		// check the path, also called when the path has been entered manually eg via a file explorer
		if (\OC\Files\Filesystem::isForbiddenFileOrDir($path)) {
			throw new \Sabre\DAV\Exception\Forbidden();
		}

		if ($path !== '') {
			try {
				$this->fileView->verifyPath($path, \basename($path));
			} catch (\OCP\Files\InvalidPathException $ex) {
				throw new InvalidPath($ex->getMessage());
			}
		}

		// Is it the root node?
		if (!\strlen($path)) {
			return $this->rootNode;
		}

		if (\pathinfo($path, PATHINFO_EXTENSION) === 'part') {
			// read from storage
			$absPath = $this->fileView->getAbsolutePath($path);
			$mount = $this->fileView->getMount($path);
			$storage = $mount->getStorage();
			$internalPath = $mount->getInternalPath($absPath);
			if ($storage && $storage->file_exists($internalPath)) {
				/**
				 * @var \OC\Files\Storage\Storage $storage
				 */
				// get data directly
				$data = $storage->getMetaData($internalPath);
				$info = new FileInfo($absPath, $storage, $internalPath, $data, $mount);
			} else {
				$info = null;
			}
		} else {
			// resolve chunk file name to real name, if applicable
			$path = $this->resolveChunkFile($path);

			// read from cache
			try {
				$info = $this->fileView->getFileInfo($path);
			} catch (StorageNotAvailableException $e) {
				throw new \Sabre\DAV\Exception\ServiceUnavailable('Storage is temporarily not available', 0, $e);
			} catch (StorageInvalidException $e) {
				throw new \Sabre\DAV\Exception\NotFound('Storage ' . $path . ' is invalid');
			} catch (LockedException $e) {
				throw new \Sabre\DAV\Exception\Locked();
			} catch (ForbiddenException $e) {
				throw new \Sabre\DAV\Exception\Forbidden();
			}
		}

		if (!$info) {
			$this->cache[$path] = false;
			throw new \Sabre\DAV\Exception\NotFound('File with name ' . $path . ' could not be located');
		}

		if ($info->getType() === 'dir') {
			$node = new \OCA\DAV\Connector\Sabre\Directory($this->fileView, $info, $this);
		} else {
			$node = new \OCA\DAV\Connector\Sabre\File($this->fileView, $info);
		}

		$this->cache[$path] = $node;
		return $node;
	}

	/**
	 * Copies a file or directory.
	 *
	 * This method must work recursively and delete the destination
	 * if it exists
	 *
	 * @param string $source
	 * @param string $destination
	 * @throws \Sabre\DAV\Exception\ServiceUnavailable
	 * @return void
	 */
	public function copy($source, $destination) {
		if (!$this->fileView) {
			throw new \Sabre\DAV\Exception\ServiceUnavailable('filesystem not setup');
		}

		# check the destination path, for source see below
		if (\OC\Files\Filesystem::isForbiddenFileOrDir($destination)) {
			throw new \Sabre\DAV\Exception\Forbidden();
		}

		// at getNodeForPath we also check the path for isForbiddenFileOrDir
		// with that we have covered both source and destination
		$this->getNodeForPath($source);

		list($destinationDir, $destinationName) = \Sabre\Uri\split($destination);
		try {
			$this->fileView->verifyPath($destinationDir, $destinationName);
		} catch (\OCP\Files\InvalidPathException $ex) {
			throw new InvalidPath($ex->getMessage());
		}

		// Webdav's copy will implicitly do a delete+create, so only create+delete permissions are required
		try {
			$isCreatable = $this->fileView->isCreatable($destinationDir);
		} catch (ForbiddenException $ex) {
			throw new Forbidden($ex->getMessage(), $ex->getRetry());
		}
		if (!$isCreatable) {
			throw new \Sabre\DAV\Exception\Forbidden();
		}

		try {
			$this->fileView->copy($source, $destination);
		} catch (StorageNotAvailableException $e) {
			throw new \Sabre\DAV\Exception\ServiceUnavailable($e->getMessage());
		} catch (ForbiddenException $ex) {
			throw new Forbidden($ex->getMessage(), $ex->getRetry());
		} catch (LockedException $e) {
			throw new FileLocked($e->getMessage(), $e->getCode(), $e);
		}

		list($destinationDir, ) = \Sabre\Uri\split($destination);
		$this->markDirty($destinationDir);
	}

	public function getView() {
		return $this->fileView;
	}
}
