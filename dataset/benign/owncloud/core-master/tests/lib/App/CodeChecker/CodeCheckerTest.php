<?php
/**
 * Copyright (c) 2015 Thomas Müller <deepdiver@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\App\CodeChecker;

use OC\App\CodeChecker\CodeChecker;
use OC\App\CodeChecker\EmptyCheck;
use OC\App\CodeChecker\PrivateCheck;
use Test\TestCase;

class CodeCheckerTest extends TestCase {

	/**
	 * @dataProvider providesFilesToCheck
	 * @param string $expectedErrorToken
	 * @param int $expectedErrorCode
	 * @param string $fileToVerify
	 */
	public function testFindInvalidUsage($expectedErrorToken, $expectedErrorCode, $fileToVerify) {
		$checker = new CodeChecker(
			new PrivateCheck(new EmptyCheck())
		);
		$errors = $checker->analyseFile(\OC::$SERVERROOT . "/tests/data/app/code-checker/$fileToVerify");

		$this->assertCount(1, $errors);
		$this->assertEquals($expectedErrorCode, $errors[0]['errorCode']);
		$this->assertEquals($expectedErrorToken, $errors[0]['disallowedToken']);
	}

	public function providesFilesToCheck() {
		return [
			['OC_Hook', 1000, 'test-extends.php'],
			['oC_Avatar', 1001, 'test-implements.php'],
			['OC_App', 1002, 'test-static-call.php'],
			['OC_API', 1003, 'test-const.php'],
			['OC_AppConfig', 1004, 'test-new.php'],
			['OC_AppConfig', 1006, 'test-use.php'],
		];
	}

	/**
	 * @dataProvider validFilesData
	 * @param string $fileToVerify
	 */
	public function testPassValidUsage($fileToVerify) {
		$checker = new CodeChecker(
			new PrivateCheck(new EmptyCheck())
		);
		$errors = $checker->analyseFile(\OC::$SERVERROOT . "/tests/data/app/code-checker/$fileToVerify");

		$this->assertCount(0, $errors);
	}

	public function validFilesData() {
		return [
			['test-identical-operator.php'],
		];
	}
}
