<?php
/**
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author RealRancor <Fisch.666@gmx.de>
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

namespace OC\IntegrityCheck\Iterator;

class ExcludeFoldersByPathFilterIterator extends \RecursiveFilterIterator {
	private $excludedFolders = [];

	public function __construct(\RecursiveIterator $iterator, $root = '') {
		parent::__construct($iterator);

		$appFolders = \OC::$APPSROOTS;
		foreach ($appFolders as $key => $appFolder) {
			$appFolders[$key] = \rtrim($appFolder['path'], '/');
		}

		$excludedFolders = [
			\rtrim($root . '/data', '/'),
			\rtrim($root . '/themes', '/'),
			\rtrim($root . '/config', '/'),
			\rtrim($root . '/apps', '/'),
			\rtrim($root . '/assets', '/'),
			\rtrim($root . '/lost+found', '/'),
		];
		$customDataDir = \OC::$server->getConfig()->getSystemValue('datadirectory', '');
		if ($customDataDir !== '') {
			$excludedFolders[] = \rtrim($customDataDir, '/');
		}

		$this->excludedFolders = \array_merge($excludedFolders, $appFolders);
	}

	/**
	 * @return bool
	 */
	public function accept() {
		return !\in_array(
			$this->current()->getPathName(),
			$this->excludedFolders,
			true
		);
	}
}
