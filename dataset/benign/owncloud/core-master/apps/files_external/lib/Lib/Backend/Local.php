<?php
/**
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OCA\Files_External\Lib\Backend;

use OCP\Files\External\Auth\AuthMechanism;
use OCP\Files\External\Backend\Backend as ExternalBackend;
use OCP\Files\External\DefinitionParameter;
use OCP\Files\External\IStoragesBackendService;
use OCP\IL10N;

class Local extends ExternalBackend {
	public function __construct(IL10N $l) {
		$this
			->setIdentifier('local')
			->addIdentifierAlias('\OC\Files\Storage\Local') // legacy compat
			->setStorageClass('\OC\Files\Storage\Local')
			->setText($l->t('Local'))
			->addParameters([
				(new DefinitionParameter('datadir', $l->t('Location'))),
			])
			->setAllowedVisibility(IStoragesBackendService::VISIBILITY_ADMIN)
			->setPriority(IStoragesBackendService::PRIORITY_DEFAULT + 50)
			->addAuthScheme(AuthMechanism::SCHEME_NULL)
		;
	}
}
