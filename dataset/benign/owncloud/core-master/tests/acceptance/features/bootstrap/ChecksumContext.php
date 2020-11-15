<?php
/**
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use TestHelpers\HttpRequestHelper;
use PHPUnit\Framework\Assert;
use TestHelpers\WebDavHelper;

require_once 'bootstrap.php';

/**
 * Checksum functions
 */
class ChecksumContext implements Context {

	/**
	 *
	 * @var FeatureContext
	 */
	private $featureContext;

	/**
	 * @When user :user uploads file :source to :destination with checksum :checksum using the WebDAV API
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function userUploadsFileToWithChecksumUsingTheAPI(
		$user, $source, $destination, $checksum
	) {
		$file = \file_get_contents(
			$this->featureContext->acceptanceTestsDirLocation() . $source
		);
		$response = $this->featureContext->makeDavRequest(
			$user,
			'PUT',
			$destination,
			['OC-Checksum' => $checksum],
			$file,
			"files"
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given user :user has uploaded file :source to :destination with checksum :checksum
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function userHasUploadedFileToWithChecksumUsingTheAPI(
		$user, $source, $destination, $checksum
	) {
		$user = $this->featureContext->getActualUsername($user);
		$this->userUploadsFileToWithChecksumUsingTheAPI(
			$user,
			$source,
			$destination,
			$checksum
		);
		$this->featureContext->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When user :user uploads file with content :content and checksum :checksum to :destination using the WebDAV API
	 *
	 * @param string $user
	 * @param string $content
	 * @param string $checksum
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userUploadsFileWithContentAndChecksumToUsingTheAPI(
		$user, $content, $checksum, $destination
	) {
		$response = $this->featureContext->makeDavRequest(
			$user,
			'PUT',
			$destination,
			['OC-Checksum' => $checksum],
			$content,
			"files"
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given user :user has uploaded file with content :content and checksum :checksum to :destination
	 *
	 * @param string $user
	 * @param string $content
	 * @param string $checksum
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userHasUploadedFileWithContentAndChecksumToUsingTheAPI(
		$user, $content, $checksum, $destination
	) {
		$user = $this->featureContext->getActualUsername($user);
		$this->userUploadsFileWithContentAndChecksumToUsingTheAPI(
			$user,
			$content,
			$checksum,
			$destination
		);
		$this->featureContext->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When user :user requests the checksum of :path via propfind
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userRequestsTheChecksumOfViaPropfind($user, $path) {
		$user = $this->featureContext->getActualUsername($user);
		$body = '<?xml version="1.0"?>
<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">
  <d:prop>
    <oc:checksums />
  </d:prop>
</d:propfind>';
		$url = $this->featureContext->getBaseUrl() . '/' .
			WebDavHelper::getDavPath(
				$user, $this->featureContext->getDavPathVersion()
			) . $path;
		$url = WebDavHelper::sanitizeUrl($url);
		$response = HttpRequestHelper::sendRequest(
			$url,
			'PROPFIND',
			$user,
			$this->featureContext->getPasswordForUser($user),
			null,
			$body
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Then the webdav checksum should match :checksum
	 *
	 * @param string $checksum
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theWebdavChecksumShouldMatch($checksum) {
		$service = new Sabre\Xml\Service();
		$parsed = $service->parse(
			$this->featureContext->getResponse()->getBody()->getContents()
		);

		/*
		 * Fetch the checksum array
		 * Maybe we want to do this a bit cleaner ;)
		 */
		$checksums = $parsed[0]['value'][1]['value'][0]['value'][0];
		Assert::assertEquals(
			$checksum,
			$checksums['value'][0]['value'],
			"Expected: webDav checksum should be {$checksum} but got {$checksums['value'][0]['value']}"
		);
	}

	/**
	 * @Then as user :user the webdav checksum of :path via propfind should match :checksum
	 *
	 * @param string $user
	 * @param string $path
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function theWebdavChecksumOfViaPropfindShouldMatch($user, $path, $checksum) {
		$user = $this->featureContext->getActualUsername($user);
		$this->userRequestsTheChecksumOfViaPropfind($user, $path);
		$this->theWebdavChecksumShouldMatch($checksum);
	}

	/**
	 * @Then the header checksum should match :checksum
	 *
	 * @param string $checksum
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theHeaderChecksumShouldMatch($checksum) {
		$headerChecksum
			= $this->featureContext->getResponse()->getHeader('OC-Checksum')[0];
		Assert::assertEquals(
			$checksum,
			$headerChecksum,
			"Expected: header checksum should match {$checksum} but got {$headerChecksum}"
		);
	}

	/**
	 * @Then the webdav checksum should be empty
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theWebdavChecksumShouldBeEmpty() {
		$service = new Sabre\Xml\Service();
		$parsed = $service->parse(
			$this->featureContext->getResponse()->getBody()->getContents()
		);

		/*
		 * Fetch the checksum array
		 * Maybe we want to do this a bit cleaner ;)
		 */
		$status = $parsed[0]['value'][1]['value'][1]['value'];
		$expectedStatus = 'HTTP/1.1 404 Not Found';
		Assert::assertEquals(
			$expectedStatus,
			$status,
			"Expected status to be {$expectedStatus} but got {$status}"
		);
	}

	/**
	 * @Then the OC-Checksum header should not be there
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theOcChecksumHeaderShouldNotBeThere() {
		$isHeader = $this->featureContext->getResponse()->hasHeader('OC-Checksum');
		Assert::assertFalse(
			$isHeader,
			"Expected no checksum header but got "
			. $this->featureContext->getResponse()->getHeader('OC-Checksum')
		);
	}

	/**
	 * @When user :user uploads chunk file :num of :total with :data to :destination with checksum :checksum using the WebDAV API
	 *
	 * @param string $user
	 * @param int $num
	 * @param int $total
	 * @param string $data
	 * @param string $destination
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function userUploadsChunkFileOfWithToWithChecksum(
		$user, $num, $total, $data, $destination, $checksum
	) {
		$user = $this->featureContext->getActualUsername($user);
		$num -= 1;
		$file = "$destination-chunking-42-$total-$num";
		$response = $this->featureContext->makeDavRequest(
			$user,
			'PUT',
			$file,
			['OC-Checksum' => $checksum, 'OC-Chunked' => '1'],
			$data,
			"files"
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @Given user :user has uploaded chunk file :num of :total with :data to :destination with checksum :checksum
	 *
	 * @param string $user
	 * @param int $num
	 * @param int $total
	 * @param string $data
	 * @param string $destination
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function userHasUploadedChunkFileOfWithToWithChecksum(
		$user, $num, $total, $data, $destination, $checksum
	) {
		$this->userUploadsChunkFileOfWithToWithChecksum(
			$user,
			$num,
			$total,
			$data,
			$destination,
			$checksum
		);
		$this->featureContext->theHTTPStatusCodeShouldBe(201);
	}

	/**
	 * This will run before EVERY scenario.
	 * It will set the properties for this object.
	 *
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function before(BeforeScenarioScope $scope) {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
	}
}
