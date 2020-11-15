<?php
/**
 * Copyright (c) 2014 Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Security;

use OC\Security\CertificateManager;

/**
 * Class CertificateManagerTest
 *
 * @group DB
 */
class CertificateManagerTest extends \Test\TestCase {
	use \Test\Traits\UserTrait;
	use \Test\Traits\MountProviderTrait;

	/** @var CertificateManager */
	private $certificateManager;
	/** @var String */
	private $username;

	protected function setUp(): void {
		parent::setUp();

		$this->username = $this->getUniqueID('', 20);
		$this->createUser($this->username);

		$storage = new \OC\Files\Storage\Temporary();
		$this->registerMount($this->username, $storage, '/' . $this->username . '/');

		\OC_Util::tearDownFS();
		\OC_User::setUserId('');
		\OC\Files\Filesystem::tearDown();
		\OC_Util::setupFS($this->username);

		$config = $this->createMock('OCP\IConfig');
		$config->expects($this->any())->method('getSystemValue')
			->with('installed', false)->willReturn(true);

		$this->certificateManager = new CertificateManager($this->username, new \OC\Files\View(), $config);
	}

	protected function assertEqualsArrays($expected, $actual) {
		\sort($expected);
		\sort($actual);

		$this->assertEquals($expected, $actual);
	}

	public function testListCertificates() {
		// Test empty certificate bundle
		$this->assertSame([], $this->certificateManager->listCertificates());

		// Add some certificates
		$this->certificateManager->addCertificate(\file_get_contents(__DIR__ . '/../../data/certificates/goodCertificate.crt'), 'GoodCertificate');
		$certificateStore = [];
		$certificateStore[] = new \OC\Security\Certificate(\file_get_contents(__DIR__ . '/../../data/certificates/goodCertificate.crt'), 'GoodCertificate');
		$this->assertEqualsArrays($certificateStore, $this->certificateManager->listCertificates());

		// Add another certificates
		$this->certificateManager->addCertificate(\file_get_contents(__DIR__ . '/../../data/certificates/expiredCertificate.crt'), 'ExpiredCertificate');
		$certificateStore[] = new \OC\Security\Certificate(\file_get_contents(__DIR__ . '/../../data/certificates/expiredCertificate.crt'), 'ExpiredCertificate');
		$this->assertEqualsArrays($certificateStore, $this->certificateManager->listCertificates());
	}

	/**
	 */
	public function testAddInvalidCertificate() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Certificate could not get parsed.');

		$this->certificateManager->addCertificate('InvalidCertificate', 'invalidCertificate');
	}

	/**
	 * @return array
	 */
	public function dangerousFileProvider() {
		return [
			['.htaccess'],
			['../../foo.txt'],
			['..\..\foo.txt'],
		];
	}

	/**
	 * @dataProvider dangerousFileProvider
	 * @param string $filename
	 */
	public function testAddDangerousFile($filename) {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Filename is not valid');

		$this->certificateManager->addCertificate(\file_get_contents(__DIR__ . '/../../data/certificates/expiredCertificate.crt'), $filename);
	}

	public function testRemoveDangerousFile() {
		$this->assertFalse($this->certificateManager->removeCertificate('../../foo.txt'));
	}

	public function testRemoveExistingFile() {
		$this->certificateManager->addCertificate(\file_get_contents(__DIR__ . '/../../data/certificates/goodCertificate.crt'), 'GoodCertificate');
		$this->assertTrue($this->certificateManager->removeCertificate('GoodCertificate'));
	}

	public function testGetCertificateBundle() {
		$this->assertSame('/' . $this->username . '/files_external/rootcerts.crt', $this->certificateManager->getCertificateBundle());
	}
}
