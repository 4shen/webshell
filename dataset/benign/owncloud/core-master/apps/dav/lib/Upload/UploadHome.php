<?php
/**
 * @author Lukas Reschke <lukas@statuscode.ch>
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
namespace OCA\DAV\Upload;

use OC\Files\Filesystem;
use OC\Files\View;
use OCA\DAV\Connector\Sabre\Directory;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\ICollection;

class UploadHome implements ICollection {
	private $principalInfo;

	/**
	 * UploadHome constructor.
	 *
	 * @param array $principalInfo
	 */
	public function __construct($principalInfo) {
		$this->principalInfo = $principalInfo;
	}

	public function createFile($name, $data = null) {
		throw new Forbidden('Permission denied to create file (filename ' . $name . ')');
	}

	public function createDirectory($name) {
		$this->impl()->createDirectory($name);
	}

	public function getChild($name) {
		return new UploadFolder($this->impl()->getChild($name));
	}

	public function getChildren() {
		return \array_map(function ($node) {
			return new UploadFolder($node);
		}, $this->impl()->getChildren());
	}

	public function childExists($name) {
		return $this->getChild($name) !== null;
	}

	public function delete() {
		$this->impl()->delete();
	}

	public function getName() {
		return 'uploads';
	}

	public function setName($name) {
		throw new Forbidden('Permission denied to rename this folder');
	}

	public function getLastModified() {
		return $this->impl()->getLastModified();
	}

	/**
	 * @return Directory
	 */
	private function impl() {
		$rootView = new View();
		if (isset($this->principalInfo['user'])) {
			$user = $this->principalInfo['user'];
		} else {
			$user = \OC::$server->getUserSession()->getUser();
		}
		Filesystem::initMountPoints($user->getUID());
		if (!$rootView->file_exists('/' . $user->getUID() . '/uploads')) {
			$rootView->mkdir('/' . $user->getUID() . '/uploads');
		}
		$view = new View('/' . $user->getUID() . '/uploads');
		$rootInfo = $view->getFileInfo('');
		$impl = new Directory($view, $rootInfo);
		return $impl;
	}
}
