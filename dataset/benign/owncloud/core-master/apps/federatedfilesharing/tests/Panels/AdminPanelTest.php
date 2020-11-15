<?php
/**
 * @author Tom Needham <tom@owncloud.com>
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

namespace OCA\FederatedFileSharing\Tests\Panels;

use OCA\FederatedFileSharing\Panels\AdminPanel;
use OCA\FederatedFileSharing\FederatedShareProvider;
use OCP\IConfig;

/**
 * @package OCA\FederatedFileSharing\Tests
 */
class AdminPanelTest extends \Test\TestCase {

	/** @var AdminPanel */
	private $panel;
	/** @var  FederatedShareProvider */
	private $shareProvider;
	/** @var IConfig */
	private $config;

	public function setUp(): void {
		parent::setUp();
		$this->shareProvider = $this->getMockBuilder(FederatedShareProvider::class)
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->createMock(IConfig::class);
		$this->panel = new AdminPanel($this->shareProvider, $this->config);
	}

	public function testGetSection() {
		$this->assertEquals('sharing', $this->panel->getSectionID());
	}

	public function testGetPriority() {
		$this->assertIsInt($this->panel->getPriority());
	}

	public function testGetPanel() {
		$this->shareProvider->expects($this->once())->method('isCronjobScanExternalEnabled')->willReturn(false);
		$this->shareProvider->expects($this->once())->method('isOutgoingServer2serverShareEnabled')->willReturn(true);
		$this->shareProvider->expects($this->once())->method('isIncomingServer2serverShareEnabled')->willReturn(true);
		$templateHtml = $this->panel->getPanel()->fetchPage();
	}
}
