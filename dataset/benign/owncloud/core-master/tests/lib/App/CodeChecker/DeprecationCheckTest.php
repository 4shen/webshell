<?php
/**
 * Copyright (c) 2015 Joas Schilling <nickvergessen@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\App\CodeChecker;

use OC\App\CodeChecker\CodeChecker;
use OC\App\CodeChecker\DeprecationCheck;
use OC\App\CodeChecker\EmptyCheck;
use Test\TestCase;

class DeprecationCheckTest extends TestCase {

	/**
	 * @dataProvider providesFilesToCheck
	 * @param string $expectedErrorToken
	 * @param int $expectedErrorCode
	 * @param string $fileToVerify
	 */
	public function testFindInvalidUsage($expectedErrorToken, $expectedErrorCode, $fileToVerify) {
		$checker = new CodeChecker(
			new DeprecationCheck(new EmptyCheck())
		);
		$errors = $checker->analyseFile(\OC::$SERVERROOT . "/tests/data/app/code-checker/$fileToVerify");

		$this->assertCount(1, $errors);
		$this->assertEquals($expectedErrorCode, $errors[0]['errorCode']);
		$this->assertEquals($expectedErrorToken, $errors[0]['disallowedToken']);
	}

	public function providesFilesToCheck() {
		return [
			['OCP\AppFramework\IApi', 1006, 'test-deprecated-use.php'],
			['OCP\AppFramework\IApi', 1006, 'test-deprecated-use-alias.php'],
			['AppFramework\IApi', 1001, 'test-deprecated-use-sub.php'],
			['OAF\IApi', 1001, 'test-deprecated-use-sub-alias.php'],
			['OC_API::ADMIN_AUTH', 1003, 'test-const.php'],
		];
	}

	/**
	 * @dataProvider validFilesData
	 * @param string $fileToVerify
	 */
	public function testPassValidUsage($fileToVerify) {
		$checker = new CodeChecker(
			new DeprecationCheck(new EmptyCheck())
		);
		$errors = $checker->analyseFile(\OC::$SERVERROOT . "/tests/data/app/code-checker/$fileToVerify");

		$this->assertCount(0, $errors);
	}

	public function validFilesData() {
		return [
			['test-equal.php'],
			['test-not-equal.php'],
			['test-extends.php'],
			['test-implements.php'],
			['test-static-call.php'],
			['test-new.php'],
			['test-use.php'],
			['test-identical-operator.php'],
		];
	}
}
