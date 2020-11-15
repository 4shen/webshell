<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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
namespace OCA\Files_External\Tests\Controller;

use OCA\Files_External\Controller\GlobalStoragesController;
use OCP\Files\External\IStoragesBackendService;

class GlobalStoragesControllerTest extends StoragesControllerTest {
	public function setUp(): void {
		parent::setUp();
		$this->service = $this->createMock('\OCP\Files\External\Service\IGlobalStoragesService');

		$this->service->method('getVisibilityType')
			->willReturn(IStoragesBackendService::VISIBILITY_ADMIN);

		$this->controller = new GlobalStoragesController(
			'files_external',
			$this->createMock('\OCP\IRequest'),
			$this->createMock('\OCP\IL10N'),
			$this->service,
			$this->createMock('\OCP\ILogger')
		);
	}
}
