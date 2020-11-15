<?php

/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test;

use OC\Log;

class NullLogger extends Log {
	public function __construct($logger = null) {
		//disable original constructor
	}

	public function log($level, $message, array $context = []) {
		//noop
	}
}

class TempManagerTest extends \Test\TestCase {
	protected $baseDir = null;

	protected function setUp(): void {
		parent::setUp();

		$this->baseDir = $this->getManager()->getTempBaseDir() . $this->getUniqueID('/oc_tmp_test');
		if (!\is_dir($this->baseDir)) {
			\mkdir($this->baseDir);
		}
	}

	protected function tearDown(): void {
		\OC_Helper::rmdirr($this->baseDir);
		$this->baseDir = null;
		parent::tearDown();
	}

	/**
	 * @param  \OCP\ILogger $logger
	 * @param  \OCP\IConfig $config
	 * @return \OC\TempManager
	 */
	protected function getManager($logger = null, $config = null) {
		if (!$logger) {
			$logger = new NullLogger();
		}
		if (!$config) {
			$config = $this->createMock('\OCP\IConfig');
			$config->method('getSystemValue')
				->with('tempdirectory', null)
				->willReturn('/tmp');
		}
		$manager = new \OC\TempManager($logger, $config);
		if ($this->baseDir) {
			$manager->overrideTempBaseDir($this->baseDir);
		}
		return $manager;
	}

	public function testGetFile() {
		$manager = $this->getManager();
		$file = $manager->getTemporaryFile('txt');
		$this->assertStringEndsWith('.txt', $file);
		$this->assertTrue(\is_file($file));
		$this->assertTrue(\is_writable($file));

		\file_put_contents($file, 'bar');
		$this->assertStringEqualsFile($file, 'bar');
	}

	public function testGetFolder() {
		$manager = $this->getManager();
		$folder = $manager->getTemporaryFolder();
		$this->assertStringEndsWith('/', $folder);
		$this->assertTrue(\is_dir($folder));
		$this->assertTrue(\is_writable($folder));

		\file_put_contents($folder . 'foo.txt', 'bar');
		$this->assertStringEqualsFile($folder . 'foo.txt', 'bar');
	}

	public function testCleanFiles() {
		$manager = $this->getManager();
		$file1 = $manager->getTemporaryFile('txt');
		$file2 = $manager->getTemporaryFile('txt');
		$this->assertFileExists($file1);
		$this->assertFileExists($file2);

		$manager->clean();

		$this->assertFileNotExists($file1);
		$this->assertFileNotExists($file2);
	}

	public function testCleanFolder() {
		$manager = $this->getManager();
		$folder1 = $manager->getTemporaryFolder();
		$folder2 = $manager->getTemporaryFolder();
		\touch($folder1 . 'foo.txt');
		\touch($folder1 . 'bar.txt');
		$this->assertFileExists($folder1);
		$this->assertFileExists($folder2);
		$this->assertFileExists($folder1 . 'foo.txt');
		$this->assertFileExists($folder1 . 'bar.txt');

		$manager->clean();

		$this->assertFileNotExists($folder1);
		$this->assertFileNotExists($folder2);
		$this->assertFileNotExists($folder1 . 'foo.txt');
		$this->assertFileNotExists($folder1 . 'bar.txt');
	}

	public function testCleanOld() {
		$manager = $this->getManager();
		$oldFile = $manager->getTemporaryFile('txt');
		$newFile = $manager->getTemporaryFile('txt');
		$folder = $manager->getTemporaryFolder();
		$nonOcFile = $this->baseDir . '/foo.txt';
		\file_put_contents($nonOcFile, 'bar');

		$past = \time() - 2 * 3600;
		\touch($oldFile, $past);
		\touch($folder, $past);
		\touch($nonOcFile, $past);

		$manager2 = $this->getManager();
		$manager2->cleanOld();
		$this->assertFileNotExists($oldFile);
		$this->assertFileNotExists($folder);
		$this->assertFileExists($nonOcFile);
		$this->assertFileExists($newFile);
	}

	public function testLogCantCreateFile() {
		if ($this->getCurrentUser() === 'root') {
			$this->markTestSkipped('You are running tests as root - this test will not work in this case.');
		}
		$logger = $this->createMock('\Test\NullLogger');
		$manager = $this->getManager($logger);
		\chmod($this->baseDir, 0500);
		$logger->expects($this->once())
			->method('warning')
			->with($this->stringContains('Can not create a temporary file in directory'));
		$this->assertFalse($manager->getTemporaryFile('txt'));
	}

	public function testLogCantCreateFolder() {
		if ($this->getCurrentUser() === 'root') {
			$this->markTestSkipped('You are running tests as root - this test will not work in this case.');
		}
		$logger = $this->createMock('\Test\NullLogger');
		$manager = $this->getManager($logger);
		\chmod($this->baseDir, 0500);
		$logger->expects($this->once())
			->method('warning')
			->with($this->stringContains('Can not create a temporary folder in directory'));
		$this->assertFalse($manager->getTemporaryFolder());
	}

	public function testBuildFileNameWithPostfix() {
		$logger = $this->createMock('\Test\NullLogger');
		$tmpManager = self::invokePrivate(
			$this->getManager($logger),
			'buildFileNameWithSuffix',
			['/tmp/myTemporaryFile', 'postfix']
		);

		$this->assertEquals('/tmp/myTemporaryFile-.postfix', $tmpManager);
	}

	public function testBuildFileNameWithoutPostfix() {
		$logger = $this->createMock('\Test\NullLogger');
		$tmpManager = self::invokePrivate(
			$this->getManager($logger),
					'buildFileNameWithSuffix',
			['/tmp/myTemporaryFile', '']
		);

		$this->assertEquals('/tmp/myTemporaryFile', $tmpManager);
	}

	public function testBuildFileNameWithSuffixPathTraversal() {
		$logger = $this->createMock('\Test\NullLogger');
		$tmpManager = self::invokePrivate(
			$this->getManager($logger),
			'buildFileNameWithSuffix',
			['foo', '../Traversal\\../FileName']
		);

		$this->assertStringEndsNotWith('./Traversal\\../FileName', $tmpManager);
		$this->assertStringEndsWith('.Traversal..FileName', $tmpManager);
	}

	public function testGetTempBaseDirFromConfig() {
		$dir = $this->getManager()->getTemporaryFolder();

		$config = $this->createMock('\OCP\IConfig');
		$config->expects($this->once())
			->method('getSystemValue')
			->with('tempdirectory', null)
			->willReturn($dir);

		$this->baseDir = null; // prevent override
		$tmpManager = $this->getManager(null, $config);

		$this->assertEquals($dir, $tmpManager->getTempBaseDir());
	}
}
