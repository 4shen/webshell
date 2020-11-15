<?php
/**
 * @author Björn Schießle <schiessle@owncloud.com>
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

namespace Tests\Core\Command\Encryption;

use OC\Core\Command\Encryption\DecryptAll;
use Test\TestCase;

class DecryptAllTest extends TestCase {

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OCP\IConfig */
	protected $config;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OCP\Encryption\IManager  */
	protected $encryptionManager;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OCP\App\IAppManager  */
	protected $appManager;

	/** @var \PHPUnit\Framework\MockObject\MockObject  | \Symfony\Component\Console\Input\InputInterface */
	protected $consoleInput;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \Symfony\Component\Console\Output\OutputInterface */
	protected $consoleOutput;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \Symfony\Component\Console\Helper\QuestionHelper */
	protected $questionHelper;

	/** @var \PHPUnit\Framework\MockObject\MockObject | \OC\Encryption\DecryptAll */
	protected $decryptAll;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->getMockBuilder('OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$this->encryptionManager = $this->getMockBuilder('OCP\Encryption\IManager')
			->disableOriginalConstructor()
			->getMock();
		$this->appManager = $this->getMockBuilder('OCP\App\IAppManager')
			->disableOriginalConstructor()
			->getMock();
		$this->questionHelper = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
			->disableOriginalConstructor()
			->getMock();
		$this->decryptAll = $this->getMockBuilder('OC\Encryption\DecryptAll')
			->disableOriginalConstructor()->getMock();
		$this->consoleInput = $this->createMock('Symfony\Component\Console\Input\InputInterface');
		$this->consoleOutput = $this->createMock('Symfony\Component\Console\Output\OutputInterface');

		$this->config->expects($this->any())
			->method('getSystemValue')
			->with('singleuser', false)
			->willReturn(false);
		$this->appManager->expects($this->any())
			->method('isEnabledForUser')
			->with('files_trashbin')->willReturn(true);
	}

	public function testSingleUserAndTrashbin() {

		// on construct we enable single-user-mode and disable the trash bin
		$this->config->expects($this->at(1))
			->method('setSystemValue')
			->with('singleuser', true);
		$this->appManager->expects($this->once())
			->method('disableApp')
			->with('files_trashbin');

		// on destruct we disable single-user-mode again and enable the trash bin
		$this->config->expects($this->at(2))
			->method('setSystemValue')
			->with('singleuser', false);
		$this->appManager->expects($this->once())
			->method('enableApp')
			->with('files_trashbin');

		$instance = new DecryptAll(
			$this->encryptionManager,
			$this->appManager,
			$this->config,
			$this->decryptAll,
			$this->questionHelper
		);
		$this->invokePrivate($instance, 'forceSingleUserAndTrashbin');

		$this->assertTrue(
			$this->invokePrivate($instance, 'wasTrashbinEnabled')
		);

		$this->assertFalse(
			$this->invokePrivate($instance, 'wasSingleUserModeEnabled')
		);
		$this->invokePrivate($instance, 'resetSingleUserAndTrashbin');
	}

	/**
	 * @dataProvider dataTestExecute
	 */
	public function testExecute($encryptionEnabled, $continue) {
		$instance = new DecryptAll(
			$this->encryptionManager,
			$this->appManager,
			$this->config,
			$this->decryptAll,
			$this->questionHelper
		);

		$this->consoleInput->expects($this->once())
			->method('getOption')
			->with('continue')
			->willReturn('no');

		$this->encryptionManager->expects($this->once())
			->method('isEnabled')
			->willReturn($encryptionEnabled);

		$this->consoleInput->expects($this->any())
			->method('getArgument')
			->with('user')
			->willReturn('user1');

		if ($encryptionEnabled) {
			$this->config->expects($this->at(0))
				->method('setAppValue')
				->with('core', 'encryption_enabled', 'no');
			$this->questionHelper->expects($this->once())
				->method('ask')
				->willReturn($continue);
			if ($continue) {
				$this->decryptAll->expects($this->once())
					->method('decryptAll')
					->with($this->consoleInput, $this->consoleOutput, 'user1');
			} else {
				$this->decryptAll->expects($this->never())->method('decryptAll');
				$this->config->expects($this->at(1))
					->method('setAppValue')
					->with('core', 'encryption_enabled', 'yes');
			}
		} else {
			$this->config->expects($this->never())->method('setAppValue');
			$this->decryptAll->expects($this->never())->method('decryptAll');
			$this->questionHelper->expects($this->never())->method('ask');
		}

		$this->invokePrivate($instance, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}

	public function dataTestExecute() {
		return [
			[true, true],
			[true, false],
			[false, true],
			[false, false]
		];
	}

	/**
	 */
	public function testExecuteFailure() {
		$this->expectException(\Exception::class);

		$instance = new DecryptAll(
			$this->encryptionManager,
			$this->appManager,
			$this->config,
			$this->decryptAll,
			$this->questionHelper
		);

		$this->consoleInput->expects($this->once())
			->method('getOption')
			->with('continue')
			->willReturn('no');

		$this->config->expects($this->at(0))
			->method('setAppValue')
			->with('core', 'encryption_enabled', 'no');

		// make sure that we enable encryption again after an exception was thrown
		$this->config->expects($this->at(3))
			->method('setAppValue')
			->with('core', 'encryption_enabled', 'yes');

		$this->encryptionManager->expects($this->once())
			->method('isEnabled')
			->willReturn(true);

		$this->consoleInput->expects($this->any())
			->method('getArgument')
			->with('user')
			->willReturn('user1');

		$this->questionHelper->expects($this->once())
			->method('ask')
			->willReturn(true);

		$this->decryptAll->expects($this->once())
			->method('decryptAll')
			->with($this->consoleInput, $this->consoleOutput, 'user1')
			->willReturnCallback(function () {
				throw new \Exception();
			});

		$this->invokePrivate($instance, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}

	public function providesConfirmVal() {
		return [
			['yes'],
			['no'],
			['foo']
		];
	}

	/**
	 * @dataProvider providesConfirmVal
	 * @param $confirmVal
	 */

	public function testExecuteConfirm($confirmVal) {
		$instance = new DecryptAll(
			$this->encryptionManager,
			$this->appManager,
			$this->config,
			$this->decryptAll,
			$this->questionHelper
		);

		$this->consoleInput->expects($this->once())
			->method('getOption')
			->with('continue')
			->willReturn($confirmVal);

		$this->encryptionManager->expects($this->any())
			->method('isEnabled')
			->willReturn(true);

		$this->assertNull($this->invokePrivate($instance, 'execute', [$this->consoleInput, $this->consoleOutput]));
	}
}
