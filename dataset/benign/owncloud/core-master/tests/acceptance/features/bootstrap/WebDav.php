<?php
/**
 * @author Sergio Bertolin <sbertolin@owncloud.com>
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

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Ring\Exception\ConnectException;
use GuzzleHttp\Stream\StreamInterface;
use Guzzle\Http\Exception\BadResponseException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use TestHelpers\OcsApiHelper;
use TestHelpers\SetupHelper;
use TestHelpers\UploadHelper;
use TestHelpers\WebDavHelper;
use TestHelpers\HttpRequestHelper;
use TestHelpers\Asserts\WebDav as WebDavAssert;

/**
 * WebDav functions
 */
trait WebDav {

	/**
	 * @var string
	 */
	private $davPath = "remote.php/webdav";

	/**
	 * @var boolean
	 */
	private $usingOldDavPath = true;

	/**
	 * @var ResponseInterface[]
	 */
	private $uploadResponses;

	/**
	 * @var integer
	 */
	private $storedFileID = null;

	/**
	 * @var int
	 */
	private $lastUploadDeleteTime = null;

	/**
	 * a variable that contains the dav path without "remote.php/(web)dav"
	 * when setting $this->davPath directly by usingDavPath()
	 *
	 * @var string
	 */
	private $customDavPath = null;

	private $oldAsyncSetting = null;

	private $oldDavSlowdownSetting = null;

	/**
	 * response content parsed from XML to an array
	 *
	 * @var array
	 */
	private $responseXml = [];

	/**
	 * response content parsed into a SimpleXMLElement
	 *
	 * @var SimpleXMLElement
	 */
	private $responseXmlObject;

	private $httpRequestTimeout = 0;

	private $chunkingToUse = null;

	/**
	 * @param number $lastUploadDeleteTime
	 *
	 * @return void
	 */
	public function setLastUploadDeleteTime($lastUploadDeleteTime) {
		$this->lastUploadDeleteTime = $lastUploadDeleteTime;
	}

	/**
	 * @return SimpleXMLElement
	 */
	public function getResponseXmlObject() {
		return $this->responseXmlObject;
	}

	/**
	 * @param SimpleXMLElement $responseXmlObject
	 *
	 * @return void
	 */
	public function setResponseXmlObject($responseXmlObject) {
		$this->responseXmlObject = $responseXmlObject;
	}

	/**
	 *
	 * @return string the etag or an empty string if the getetag property does not exist
	 */
	public function getEtagFromResponseXmlObject() {
		$xmlObject = $this->getResponseXmlObject();
		$xmlPart = $xmlObject->xpath("//d:prop/d:getetag");
		if (!\is_array($xmlPart) || (\count($xmlPart) === 0)) {
			return '';
		}
		return $xmlPart[0]->__toString();
	}

	/**
	 *
	 * @param string|null $eTag if null then get eTag from response XML object
	 *
	 * @return boolean
	 */
	public function isEtagValid($eTag = null) {
		if ($eTag === null) {
			$eTag = $this->getEtagFromResponseXmlObject();
		}
		if (\preg_match("/^\"[a-f0-9:]{1,32}\"$/", $eTag)
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param array $responseXml
	 *
	 * @return void
	 */
	public function setResponseXml($responseXml) {
		$this->responseXml = $responseXml;
	}

	/**
	 * @param ResponseInterface[] $uploadResponses
	 *
	 * @return void
	 */
	public function setUploadResponses($uploadResponses) {
		$this->uploadResponses = $uploadResponses;
	}

	/**
	 * @Given /^using dav path "([^"]*)"$/
	 *
	 * @param string $davPath
	 *
	 * @return void
	 */
	public function usingDavPath($davPath) {
		$this->davPath = $davPath;
		$this->customDavPath = \preg_replace(
			"/remote\.php\/(web)?dav\//", "", $davPath
		);
	}

	/**
	 * @return string
	 */
	public function getOldDavPath() {
		return "remote.php/webdav";
	}

	/**
	 * @return string
	 */
	public function getNewDavPath() {
		return "remote.php/dav";
	}

	/**
	 * @Given /^using (old|new) (?:dav|DAV) path$/
	 *
	 * @param string $oldOrNewDavPath
	 *
	 * @return void
	 */
	public function usingOldOrNewDavPath($oldOrNewDavPath) {
		if ($oldOrNewDavPath === 'old') {
			$this->usingOldDavPath();
		} else {
			$this->usingNewDavPath();
		}
	}

	/**
	 * Select the old DAV path as the default for later scenario steps
	 *
	 * @return void
	 */
	public function usingOldDavPath() {
		$this->davPath = $this->getOldDavPath();
		$this->usingOldDavPath = true;
		$this->customDavPath = null;
	}

	/**
	 * Select the new DAV path as the default for later scenario steps
	 *
	 * @return void
	 */
	public function usingNewDavPath() {
		$this->davPath = $this->getNewDavPath();
		$this->usingOldDavPath = false;
		$this->customDavPath = null;
	}

	/**
	 * gives the dav path of a file including the subfolder of the webserver
	 * e.g. when the server runs in `http://localhost/owncloud/`
	 * this function will return `owncloud/remote.php/webdav/prueba.txt`
	 *
	 * @param string $user
	 *
	 * @return string
	 */
	public function getFullDavFilesPath($user) {
		$path = $this->getBasePath() . "/" .
			WebDavHelper::getDavPath($user, $this->getDavPathVersion());
		$path = WebDavHelper::sanitizeUrl($path);
		return \ltrim($path, "/");
	}

	/**
	 * Select a suitable dav path version number.
	 * Some endpoints have only existed since a certain point in time, so for
	 * those make sure to return a DAV path version that works for that endpoint.
	 * Otherwise return the currently selected DAV path version.
	 *
	 * @param string $for the category of endpoint that the dav path will be used for
	 *
	 * @return int DAV path version (1 or 2) selected, or appropriate for the endpoint
	 */
	public function getDavPathVersion($for = null) {
		if ($for === 'systemtags') {
			// systemtags only exists since dav v2
			return 2;
		}
		if ($for === 'file_versions') {
			// file_versions only exists since dav v2
			return 2;
		}
		if ($this->usingOldDavPath === true) {
			return 1;
		} else {
			return 2;
		}
	}

	/**
	 * Select a suitable dav path.
	 * Some endpoints have only existed since a certain point in time, so for
	 * those make sure to return a DAV path that works for that endpoint.
	 * Otherwise return the currently selected DAV path.
	 *
	 * @param string $for the category of endpoint that the dav path will be used for
	 *
	 * @return string DAV path selected, or appropriate for the endpoint
	 */
	public function getDavPath($for = null) {
		if ($this->getDavPathVersion($for) === 1) {
			return $this->getOldDavPath();
		}

		return $this->getNewDavPath();
	}

	/**
	 * @param string $user
	 * @param string $method
	 * @param string $path
	 * @param array $headers
	 * @param StreamInterface $body
	 * @param string $type
	 * @param string|null $davPathVersion
	 * @param bool $stream Set to true to stream a response rather
	 *                     than download it all up-front.
	 * @param string|null $password
	 * @param array $urlParameter
	 * @param string $doDavRequestAsUser
	 *
	 * @return ResponseInterface
	 */
	public function makeDavRequest(
		$user,
		$method,
		$path,
		$headers,
		$body = null,
		$type = "files",
		$davPathVersion = null,
		$stream = false,
		$password = null,
		$urlParameter = [],
		$doDavRequestAsUser = null
	) {
		$user = $this->getActualUsername($user);
		if ($this->customDavPath !== null) {
			$path = $this->customDavPath . $path;
		}

		if ($davPathVersion === null) {
			$davPathVersion = $this->getDavPathVersion();
		}

		if ($password === null) {
			$password = $this->getPasswordForUser($user);
		}
		return WebDavHelper::makeDavRequest(
			$this->getBaseUrl(),
			$user, $password, $method,
			$path, $headers, $body, $davPathVersion,
			$type, null, "basic", $stream, $this->httpRequestTimeout, null, $urlParameter, $doDavRequestAsUser
		);
	}

	/**
	 * @param $user
	 * @param $path
	 * @param $doDavRequestAsUser
	 * @param $width
	 * @param $height
	 *
	 * @return ResponseInterface
	 */
	public function downloadPreviews($user, $path, $doDavRequestAsUser, $width, $height) {
		$user = $this->getActualUsername($user);
		$doDavRequestAsUser = $this->getActualUsername($doDavRequestAsUser);
		$urlParameter = [
			'x' => $width,
			'y' => $height,
			'forceIcon' => '0',
			'preview' => '1'
		];
		$this->response = $this->makeDavRequest(
			$user, "GET", $path, [], null, "files", 2, false, null, $urlParameter, $doDavRequestAsUser
		);
	}

	/**
	 * @When user :user tries to get versions of file :file from :fileOwner
	 *
	 * @param string $user
	 * @param string $file
	 * @param string $fileOwner
	 *
	 * @return void
	 * @throws Exception
	 */
	public function userTriesToGetFileVersions($user, $file, $fileOwner) {
		$user = $this->getActualUsername($user);
		$fileOwner = $this->getActualUsername($fileOwner);
		$fileId = $this->getFileIdForPath($fileOwner, $file);
		$path = "/meta/" . $fileId . "/v";
		$response = $this->makeDavRequest(
			$user,
			"PROPFIND",
			$path,
			null,
			null,
			null,
			2
		);
		$this->setResponse($response);
	}

	/**
	 * @Then the number of versions should be :arg1
	 *
	 * @param int $number
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theNumberOfVersionsShouldBe($number) {
		$resXml = $this->getResponseXmlObject();
		if ($resXml === null) {
			$resXml = HttpRequestHelper::getResponseXml($this->getResponse());
		}
		$xmlPart = $resXml->xpath("//d:getlastmodified");
		$actualNumber = \count($xmlPart);
		Assert::assertEquals(
			$number,
			$actualNumber,
			"Expected number of versions was '$number', but got '$actualNumber'"
		);
	}

	/**
	 * @Given /^the administrator has (enabled|disabled) async operations$/
	 *
	 * @param string $enabledOrDisabled
	 *
	 * @return void
	 * @throws Exception
	 */
	public function triggerAsyncUpload($enabledOrDisabled) {
		$switch = ($enabledOrDisabled !== "disabled");
		if ($switch) {
			$value = 'true';
		} else {
			$value = 'false';
		}
		if ($this->oldAsyncSetting === null) {
			$oldAsyncSetting = SetupHelper::runOcc(
				['config:system:get', 'dav.enable.async']
			)['stdOut'];
			$this->oldAsyncSetting = \trim($oldAsyncSetting);
		}
		$this->runOcc(
			[
				'config:system:set',
				'dav.enable.async',
				'--type',
				'boolean',
				'--value',
				$value
			]
		);
	}

	/**
	 * @Given the HTTP-Request-timeout is set to :seconds seconds
	 *
	 * @param int $timeout
	 *
	 * @return void
	 */
	public function setHttpTimeout($timeout) {
		$this->httpRequestTimeout = (int) $timeout;
	}

	/**
	 * @Given the :method dav requests are slowed down by :seconds seconds
	 *
	 * @param string $method
	 * @param int $seconds
	 *
	 * @return void
	 * @throws Exception
	 */
	public function slowdownDavRequests($method, $seconds) {
		if ($this->oldDavSlowdownSetting === null) {
			$oldDavSlowdownSetting = SetupHelper::runOcc(
				['config:system:get', 'dav.slowdown']
			)['stdOut'];
			$this->oldDavSlowdownSetting = \trim($oldDavSlowdownSetting);
		}
		OcsApiHelper::sendRequest(
			$this->getBaseUrl(),
			$this->getAdminUsername(),
			$this->getAdminPassword(),
			"PUT",
			"/apps/testing/api/v1/davslowdown/$method/$seconds"
		);
	}

	/**
	 * @param string $user
	 * @param string $fileDestination
	 *
	 * @return string
	 */
	public function destinationHeaderValue($user, $fileDestination) {
		$fullUrl = $this->getBaseUrl() . '/' .
			WebDavHelper::getDavPath($user, $this->getDavPathVersion());
		return $fullUrl . '/' . \ltrim($fileDestination, '/');
	}

	/**
	 * @Given /^user "([^"]*)" has moved (?:file|folder|entry) "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 * @throws Exception
	 */
	public function userHasMovedFile(
		$user, $fileSource, $fileDestination
	) {
		$user = $this->getActualUsername($user);
		$headers['Destination'] = $this->destinationHeaderValue(
			$user, $fileDestination
		);
		$this->response = $this->makeDavRequest(
			$user, "MOVE", $fileSource, $headers
		);
		if ($this->response->getStatusCode() !== 201) {
			throw new Exception(
				__METHOD__ . " Failed moving resource '$fileSource' to '$fileDestination'."
				. " Expected status code was '201' but got '" . $this->response->getStatusCode() . "'"
			);
		}
	}

	/**
	 * @Given /^the user has moved (?:file|folder|entry) "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function theUserHasMovedFile($fileSource, $fileDestination) {
		$this->userHasMovedFile($this->getCurrentUser(), $fileSource, $fileDestination);
	}

	/**
	 * @When /^user "([^"]*)" moves (?:file|folder|entry) "([^"]*)"\s?(asynchronously|) to these (?:filenames|foldernames|entries) using the webDAV API then the results should be as listed$/
	 *
	 * @param string $user
	 * @param string $fileSource
	 * @param string $type "asynchronously" or empty
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function userMovesEntriesUsingTheAPI(
		$user,
		$fileSource,
		$type,
		TableNode $table
	) {
		$user = $this->getActualUsername($user);
		foreach ($table->getHash() as $row) {
			// Allow the "filename" column to be optionally be called "foldername"
			// to help readability of scenarios that test moving folders
			if (isset($row['foldername'])) {
				$targetName = $row['foldername'];
			} else {
				$targetName = $row['filename'];
			}
			$this->userMovesFileUsingTheAPI(
				$user,
				$fileSource,
				$type,
				$targetName
			);
			$this->theHTTPStatusCodeShouldBe(
				$row['http-code'],
				"HTTP status code is not the expected value while trying to move " . $targetName
			);
			if ($row['exists'] === "yes") {
				$this->asFileOrFolderShouldExist($user, "entry", $targetName);
				// The move was successful.
				// Move the file/folder back so the source file/folder exists for the next move
				$this->userMovesFileUsingTheAPI(
					$user,
					$targetName,
					'',
					$fileSource
				);
			} else {
				$this->asFileOrFolderShouldNotExist($user, "entry", $targetName);
			}
		}
	}

	/**
	 * @When /^user "([^"]*)" moves (?:file|folder|entry) "([^"]*)"\s?(asynchronously|) to "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $fileSource
	 * @param string $type "asynchronously" or empty
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function userMovesFileUsingTheAPI(
		$user, $fileSource, $type, $fileDestination
	) {
		$user = $this->getActualUsername($user);
		$headers['Destination'] = $this->destinationHeaderValue(
			$user, $fileDestination
		);
		$stream = false;
		if ($type === "asynchronously") {
			$headers['OC-LazyOps'] = 'true';
			if ($this->httpRequestTimeout > 0) {
				//LazyOps is set and a request timeout, so we want to use stream
				//to be able to read data from the request before its times out
				//when doing LazyOps the server does not close the connection
				//before its really finished
				//but we want to read JobStatus-Location before the end of the job
				//to see if it reports the correct values
				$stream = true;
			}
		}
		try {
			$this->response = $this->makeDavRequest(
				$user, "MOVE", $fileSource, $headers, null, "files", null, $stream
			);
			$this->setResponseXml(
				HttpRequestHelper::parseResponseAsXml($this->response)
			);
		} catch (ConnectException $e) {
		}
	}

	/**
	 * @Then /^user "([^"]*)" should be able to rename (file|folder|entry) "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $entry
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserShouldBeAbleToRenameEntryTo($user, $entry, $source, $destination) {
		$user = $this->getActualUsername($user);
		$this->asFileOrFolderShouldExist($user, $entry, $source);
		$this->userMovesFileUsingTheAPI($user, $source, "", $destination);
		$this->asFileOrFolderShouldNotExist($user, $entry, $source);
		$this->asFileOrFolderShouldExist($user, $entry, $destination);
	}

	/**
	 * @Then /^user "([^"]*)" should not be able to rename (file|folder|entry) "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $entry
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserShouldNotBeAbleToRenameEntryTo($user, $entry, $source, $destination) {
		$this->asFileOrFolderShouldExist($user, $entry, $source);
		$this->userMovesFileUsingTheAPI($user, $source, "", $destination);
		$this->asFileOrFolderShouldExist($user, $entry, $source);
	}

	/**
	 * @When /^user "([^"]*)" on "(LOCAL|REMOTE)" moves (?:file|folder|entry) "([^"]*)" to "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $server
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function userOnMovesFileUsingTheAPI(
		$user, $server, $fileSource, $fileDestination
	) {
		$previousServer = $this->usingServer($server);
		$this->userMovesFileUsingTheAPI($user, $fileSource, "", $fileDestination);
		$this->usingServer($previousServer);
	}

	/**
	 * @When /^user "([^"]*)" copies file "([^"]*)" to "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function userCopiesFileUsingTheAPI(
		$user, $fileSource, $fileDestination
	) {
		$user = $this->getActualUsername($user);
		$headers['Destination'] = $this->destinationHeaderValue(
			$user, $fileDestination
		);
		$this->response = $this->makeDavRequest(
			$user, "COPY", $fileSource, $headers
		);
		$this->setResponseXml(
			HttpRequestHelper::parseResponseAsXml($this->response)
		);
	}

	/**
	 * @Given /^user "([^"]*)" has copied file "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function userHasCopiedFileUsingTheAPI(
		$user, $fileSource, $fileDestination
	) {
		$this->userCopiesFileUsingTheAPI($user, $fileSource, $fileDestination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * @When /^the user copies file "([^"]*)" to "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function theUserCopiesFileUsingTheAPI($fileSource, $fileDestination) {
		$this->userCopiesFileUsingTheAPI($this->getCurrentUser(), $fileSource, $fileDestination);
	}

	/**
	 * @Given /^the user has copied file "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $fileSource
	 * @param string $fileDestination
	 *
	 * @return void
	 */
	public function theUserHasCopiedFileUsingTheAPI($fileSource, $fileDestination) {
		$this->theUserCopiesFileUsingTheAPI($fileSource, $fileDestination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * @When /^the user downloads file "([^"]*)" with range "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $fileSource
	 * @param string $range
	 *
	 * @return void
	 */
	public function downloadFileWithRange($fileSource, $range) {
		$this->userDownloadsFileWithRange(
			$this->currentUser, $fileSource, $range
		);
	}

	/**
	 * @When /^user "([^"]*)" downloads file "([^"]*)" with range "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $fileSource
	 * @param string $range
	 *
	 * @return void
	 */
	public function userDownloadsFileWithRange($user, $fileSource, $range) {
		$user = $this->getActualUsername($user);
		$headers['Range'] = $range;
		$this->response = $this->makeDavRequest(
			$user, "GET", $fileSource, $headers
		);
	}

	/**
	 * @Then /^user "([^"]*)" using password "([^"]*)" should not be able to download file "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $fileName
	 *
	 * @return void
	 */
	public function userUsingPasswordShouldNotBeAbleToDownloadFile(
		$user, $password, $fileName
	) {
		$user = $this->getActualUsername($user);
		$password = $this->getActualPassword($password);
		$this->downloadFileAsUserUsingPassword($user, $fileName, $password);
		Assert::assertGreaterThanOrEqual(
			400, $this->getResponse()->getStatusCode(),
			__METHOD__
			. ' download must fail'
		);
		Assert::assertLessThanOrEqual(
			499, $this->getResponse()->getStatusCode(),
			__METHOD__
			. ' 4xx error expected but got status code "'
			. $this->getResponse()->getStatusCode() . '"'
		);
	}

	/**
	 * @Then /^user "([^"]*)" should be able to access a skeleton file$/
	 *
	 * @param string $user
	 *
	 * @return void
	 */
	public function userShouldBeAbleToAccessASkeletonFile($user) {
		$this->contentOfFileForUserShouldBePlusEndOfLine(
			"textfile0.txt", $user, "ownCloud test text file 0"
		);
	}

	/**
	 * @Then the size of the downloaded file should be :size bytes
	 *
	 * @param string $size
	 *
	 * @return void
	 */
	public function sizeOfDownloadedFileShouldBe($size) {
		$actualSize = \strlen((string) $this->response->getBody());
		Assert::assertEquals(
			$size,
			$actualSize,
			"Expected size of the downloaded file was '$size' but got '$actualSize'"
		);
	}

	/**
	 * @Then /^the downloaded content should end with "([^"]*)"$/
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function downloadedContentShouldEndWith($content) {
		$actualContent = \substr((string) $this->response->getBody(), -\strlen($content));
		Assert::assertEquals(
			$content,
			$actualContent,
			"The downloaded content was expected to end with '$content', but actually ended with '$actualContent'."
		);
	}

	/**
	 * @Then /^the downloaded content should be "([^"]*)"$/
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function downloadedContentShouldBe($content) {
		$actualContent = (string) $this->response->getBody();
		Assert::assertEquals(
			$content,
			$actualContent,
			"The downloaded content was expected to be '$content', but actually is '$actualContent'."
		);
	}

	/**
	 * @Then /^the downloaded content should be "([^"]*)" plus end-of-line$/
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function downloadedContentShouldBePlusEndOfLine($content) {
		$this->downloadedContentShouldBe("$content\n");
	}

	/**
	 * @Then /^the content of file "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $fileName
	 * @param string $content
	 *
	 * @return void
	 */
	public function contentOfFileShouldBe($fileName, $content) {
		$this->theUserDownloadsTheFileUsingTheAPI($fileName);
		$this->downloadedContentShouldBe($content);
	}

	/**
	 * @Then /^the content of file "([^"]*)" should be:$/
	 *
	 * @param string $fileName
	 * @param PyStringNode $content
	 *
	 * @return void
	 */
	public function contentOfFileShouldBePyString(
		$fileName, PyStringNode $content
	) {
		$this->contentOfFileShouldBe($fileName, $content->getRaw());
	}

	/**
	 * @Then /^the content of file "([^"]*)" should be "([^"]*)" plus end-of-line$/
	 *
	 * @param string $fileName
	 * @param string $content
	 *
	 * @return void
	 */
	public function contentOfFileShouldBePlusEndOfLine($fileName, $content) {
		$this->theUserDownloadsTheFileUsingTheAPI($fileName);
		$this->downloadedContentShouldBePlusEndOfLine($content);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $content
	 *
	 * @return void
	 */
	public function contentOfFileForUserShouldBe($fileName, $user, $content) {
		$user = $this->getActualUsername($user);
		$this->downloadFileAsUserUsingPassword($user, $fileName);
		$this->downloadedContentShouldBe($content);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" on server "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $server
	 * @param string $content
	 *
	 * @return void
	 */
	public function theContentOfFileForUserOnServerShouldBe(
		$fileName, $user, $server, $content
	) {
		$previousServer = $this->usingServer($server);
		$this->contentOfFileForUserShouldBe($fileName, $user, $content);
		$this->usingServer($previousServer);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" using password "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $password
	 * @param string $content
	 *
	 * @return void
	 */
	public function contentOfFileForUserUsingPasswordShouldBe(
		$fileName, $user, $password, $content
	) {
		$user = $this->getActualUsername($user);
		$password = $this->getActualPassword($password);
		$this->downloadFileAsUserUsingPassword($user, $fileName, $password);
		$this->downloadedContentShouldBe($content);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" should be:$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param PyStringNode $content
	 *
	 * @return void
	 */
	public function contentOfFileForUserShouldBePyString(
		$fileName, $user, PyStringNode $content
	) {
		$this->contentOfFileForUserShouldBe($fileName, $user, $content->getRaw());
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" using password "([^"]*)" should be:$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $password
	 * @param PyStringNode $content
	 *
	 * @return void
	 */
	public function contentOfFileForUserUsingPasswordShouldBePyString(
		$fileName, $user, $password, PyStringNode $content
	) {
		$this->contentOfFileForUserUsingPasswordShouldBe(
			$fileName, $user, $password, $content->getRaw()
		);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" should be "([^"]*)" plus end-of-line$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $content
	 *
	 * @return void
	 */
	public function contentOfFileForUserShouldBePlusEndOfLine($fileName, $user, $content) {
		$this->contentOfFileForUserShouldBe(
			$fileName, $user, "$content\n"
		);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" on server "([^"]*)" should be "([^"]*)" plus end-of-line$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $server
	 * @param string $content
	 *
	 * @return void
	 */
	public function theContentOfFileForUserOnServerShouldBePlusEndOfLine(
		$fileName, $user, $server, $content
	) {
		$previousServer = $this->usingServer($server);
		$this->contentOfFileForUserShouldBePlusEndOfLine($fileName, $user, $content);
		$this->usingServer($previousServer);
	}

	/**
	 * @Then /^the content of file "([^"]*)" for user "([^"]*)" using password "([^"]*)" should be "([^"]*)" plus end-of-line$/
	 *
	 * @param string $fileName
	 * @param string $user
	 * @param string $password
	 * @param string $content
	 *
	 * @return void
	 */
	public function contentOfFileForUserUsingPasswordShouldBePlusEndOfLine(
		$fileName, $user, $password, $content
	) {
		$user = $this->getActualUsername($user);
		$this->contentOfFileForUserUsingPasswordShouldBe(
			$fileName, $user, $password, "$content\n"
		);
	}

	/**
	 * @Then /^the downloaded content when downloading file "([^"]*)" with range "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $fileSource
	 * @param string $range
	 * @param string $content
	 *
	 * @return void
	 */
	public function downloadedContentWhenDownloadingWithRangeShouldBe(
		$fileSource, $range, $content
	) {
		$this->downloadFileWithRange($fileSource, $range);
		$this->downloadedContentShouldBe($content);
	}

	/**
	 * @Then /^the downloaded content when downloading file "([^"]*)" for user "([^"]*)" with range "([^"]*)" should be "([^"]*)"$/
	 *
	 * @param string $fileSource
	 * @param string $user
	 * @param string $range
	 * @param string $content
	 *
	 * @return void
	 */
	public function downloadedContentWhenDownloadingForUserWithRangeShouldBe(
		$fileSource, $user, $range, $content
	) {
		$user = $this->getActualUsername($user);
		$this->userDownloadsFileWithRange($user, $fileSource, $range);
		$this->downloadedContentShouldBe($content);
	}

	/**
	 * @When the user downloads the file :fileName using the WebDAV API
	 *
	 * @param string $fileName
	 *
	 * @return void
	 */
	public function theUserDownloadsTheFileUsingTheAPI($fileName) {
		$this->downloadFileAsUserUsingPassword($this->currentUser, $fileName);
	}

	/**
	 * @When user :user downloads file :fileName using the WebDAV API
	 *
	 * @param string $user
	 * @param string $fileName
	 *
	 * @return void
	 */
	public function userDownloadsFileUsingTheAPI(
		$user, $fileName
	) {
		$this->downloadFileAsUserUsingPassword($user, $fileName);
	}

	/**
	 * @When user :user using password :password downloads the file :fileName using the WebDAV API
	 *
	 * @param string $user
	 * @param string|null $password
	 * @param string $fileName
	 *
	 * @return void
	 */
	public function userUsingPasswordDownloadsTheFileUsingTheAPI(
		$user, $password, $fileName
	) {
		$this->downloadFileAsUserUsingPassword($user, $fileName, $password);
	}

	/**
	 * @param string $user
	 * @param string $fileName
	 * @param string|null $password
	 * @param array|null $headers
	 *
	 * @return void
	 */
	public function downloadFileAsUserUsingPassword(
		$user, $fileName, $password = null, $headers = []
	) {
		$user = $this->getActualUsername($user);
		$password = $this->getActualPassword($password);
		$this->response = $this->makeDavRequest(
			$user,
			'GET',
			$fileName,
			$headers,
			null,
			"files",
			null,
			false,
			$password
		);
	}

	/**
	 * @When the public gets the size of the last shared public link using the WebDAV API
	 *
	 * @return void
	 * @throws Exception
	 */
	public function publicGetsSizeOfLastSharedPublicLinkUsingTheWebdavApi() {
		$tokenArray = $this->getLastShareData()->data->token;
		$token = (string)$tokenArray[0];
		$url = $this->getBaseUrl() . "/remote.php/dav/public-files/{$token}";
		$this->response = HttpRequestHelper::sendRequest(
			$url, "PROPFIND", null, null, null
		);
	}

	/**
	 * @When user :user gets the size of file :resource using the WebDAV API
	 *
	 * @param $user
	 * @param $resource
	 *
	 * @return void
	 * @throws Exception
	 */
	public function userGetsSizeOfFileUsingTheWebdavApi($user, $resource) {
		$user = $this->getActualUsername($user);
		$password = $this->getPasswordForUser($user);
		$this->response = WebDavHelper::propfind(
			$this->getBaseUrl(), $user, $password, $resource, []
		);
	}

	/**
	 * @Then the size of the file should be :size
	 *
	 * @param $size
	 *
	 * @return void
	 */
	public function theSizeOfTheFileShouldBe($size) {
		$responseXml = HttpRequestHelper::getResponseXml($this->response);
		$xmlPart = $responseXml->xpath("//d:prop/d:getcontentlength");
		$actualSize = (string) $xmlPart[0];
		Assert::assertEquals(
			$size,
			$actualSize,
			__METHOD__
			. " Expected size of the file was '$size', but got '$actualSize' instead."
		);
	}

	/**
	 * @Then the following headers should be set
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theFollowingHeadersShouldBeSet(TableNode $table) {
		$this->verifyTableNodeColumns(
			$table,
			['header', 'value']
		);
		foreach ($table->getColumnsHash() as $header) {
			$headerName = $header['header'];
			$expectedHeaderValue = $header['value'];
			$returnedHeader = $this->response->getHeader($headerName);
			$expectedHeaderValue = $this->substituteInLineCodes($expectedHeaderValue);

			if (\is_array($returnedHeader)) {
				if (empty($returnedHeader)) {
					throw new \Exception(
						\sprintf(
							"Missing expected header '%s'",
							$headerName
						)
					);
				}
				$headerValue = $returnedHeader[0];
			} else {
				$headerValue = $returnedHeader;
			}

			Assert::assertEquals(
				$expectedHeaderValue,
				$headerValue,
				__METHOD__
				. " Expected value for header '$headerName' was '$expectedHeaderValue', but got '$headerValue' instead."
			);
		}
	}

	/**
	 * @Then the downloaded content should start with :start
	 *
	 * @param string $start
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function downloadedContentShouldStartWith($start) {
		Assert::assertEquals(
			0,
			\strpos($this->response->getBody()->getContents(), $start),
			__METHOD__
			. " The downloaded content was expected to start with '$start', but actually started with '{$this->response->getBody()->getContents()}'"
		);
	}

	/**
	 * @Then the oc job status values of last request for user :user should match these regular expressions
	 *
	 * @param string $user
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function jobStatusValuesShouldMatchRegEx($user, $table) {
		$user = $this->getActualUsername($user);
		$this->verifyTableNodeColumnsCount($table, 2);
		$headerArray = $this->response->getHeader("OC-JobStatus-Location");
		$url = $headerArray[0];
		$url = $this->getBaseUrlWithoutPath() . $url;
		$response = HttpRequestHelper::get($url, $user, $this->getPasswordForUser($user));
		$contents = $response->getBody()->getContents();
		$result = \json_decode($contents, true);
		PHPUnit\Framework\Assert::assertNotNull($result, "'$contents' is not valid JSON");
		foreach ($table->getTable() as $row) {
			$expectedKey = $row[0];
			Assert::assertArrayHasKey(
				$expectedKey, $result, "response does not have expected key '$expectedKey'"
			);
			$expectedValue = $this->substituteInLineCodes(
				$row[1], $user, ['preg_quote' => ['/']]
			);
			Assert::assertNotFalse(
				(bool) \preg_match($expectedValue, $result[$expectedKey]),
				"'$expectedValue' does not match '$result[$expectedKey]'"
			);
		}
	}

	/**
	 * @Then /^as "([^"]*)" (file|folder|entry) "([^"]*)" should not exist$/
	 *
	 * @param string $user
	 * @param string $entry
	 * @param string $path
	 * @param string $type
	 *
	 * @return ResponseInterface
	 * @throws \Exception
	 */
	public function asFileOrFolderShouldNotExist(
		$user, $entry, $path, $type = "files"
	) {
		$user = $this->getActualUsername($user);
		$path = $this->substituteInLineCodes($path, $user);
		$response = WebDavHelper::makeDavRequest(
			$this->getBaseUrl(), $this->getActualUsername($user),
			$this->getPasswordForUser($user), 'GET', $path,
			[], null, 2, $type
		);

		if ($response->getStatusCode() < 401 || $response->getStatusCode() > 404) {
			throw new \Exception(
				"$entry '$path' expected to not exist " .
				"(status code {$response->getStatusCode()}, expected 401 - 404)"
			);
		}

		return $response;
	}

	/**
	 * @Then /^as "([^"]*)" (file|folder|entry) "([^"]*)" should exist$/
	 *
	 * @param string $user
	 * @param string $entry
	 * @param string $path
	 * @param string $type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function asFileOrFolderShouldExist(
		$user, $entry, $path, $type = "files"
	) {
		$user = $this->getActualUsername($user);
		$path = $this->substituteInLineCodes($path);
		$this->responseXmlObject = $this->listFolder(
			$user, $path, 0, null, $type
		);
		Assert::assertTrue(
			$this->isEtagValid(),
			"$entry '$path' expected to exist but not found"
		);
	}

	/**
	 * @Then /^as "([^"]*)" exactly one of these (files|folders|entries) should exist$/
	 *
	 * @param string $user
	 * @param string $entries
	 * @param TableNode $table of file, folder or entry paths
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function asExactlyOneOfTheseFilesOrFoldersShouldExist($user, $entries, $table) {
		$numEntriesThatExist = 0;
		foreach ($table->getTable() as $row) {
			$path = $this->substituteInLineCodes($row[0]);
			$this->responseXmlObject = $this->listFolder($user, $path, 0);
			if ($this->isEtagValid()) {
				$numEntriesThatExist = $numEntriesThatExist + 1;
			}
		}
		Assert::assertEquals(
			1,
			$numEntriesThatExist,
			"exactly one of these $entries should exist but found $numEntriesThatExist $entries"
		);
	}

	/**
	 *
	 * @param string $user
	 * @param string $path
	 * @param int $folderDepth requires 1 to see elements without children
	 * @param array|null $properties
	 * @param string $type
	 *
	 * @return SimpleXMLElement
	 */
	public function listFolder(
		$user, $path, $folderDepth, $properties = null, $type = "files"
	) {
		$user = $this->getActualUsername($user);
		if ($this->customDavPath !== null) {
			$path = $this->customDavPath . $path;
		}

		return WebDavHelper::listFolder(
			$this->getBaseUrl(),
			$this->getActualUsername($user),
			$this->getPasswordForUser($user),
			$path, $folderDepth, $properties,
			$type, ($this->usingOldDavPath) ? 1 : 2
		);
	}

	/**
	 * @Then /^user "([^"]*)" should (not|)\s?see the following elements$/
	 *
	 * @param string $user
	 * @param string $shouldOrNot
	 * @param TableNode $elements
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 *
	 */
	public function userShouldSeeTheElements($user, $shouldOrNot, $elements) {
		$should = ($shouldOrNot !== "not");
		$this->checkElementList($user, $elements, $should);
	}

	/**
	 * @Then /^user "([^"]*)" should not see the following elements if the upper and lower case username are different/
	 *
	 * @param string $user
	 * @param TableNode $elements
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 *
	 */
	public function userShouldNotSeeTheElementsIfUpperAndLowerCaseUsernameDifferent($user, $elements) {
		$effectiveUser = $this->getActualUsername($user);
		if (\strtoupper($effectiveUser) === \strtolower($effectiveUser)) {
			$expectedToBeListed = true;
		} else {
			$expectedToBeListed = false;
		}
		$this->checkElementList($user, $elements, $expectedToBeListed);
	}

	/**
	 * asserts that a the user can or cannot see a list of files/folders by propfind
	 *
	 * @param string $user
	 * @param TableNode $elements
	 * @param boolean $expectedToBeListed
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 *
	 */
	public function checkElementList(
		$user, $elements, $expectedToBeListed = true
	) {
		$user = $this->getActualUsername($user);
		$this->verifyTableNodeColumnsCount($elements, 1);
		$responseXmlObject = $this->listFolder($user, "/", "infinity");
		$elementRows = $elements->getRows();
		$elementsSimplified = $this->simplifyArray($elementRows);
		foreach ($elementsSimplified as $expectedElement) {
			// Allow the table of expected elements to have entries that do
			// not have to specify the "implied" leading slash, or have multiple
			// leading slashes, to make scenario outlines more flexible
			$expectedElement = "/" . \ltrim($expectedElement, "/");
			$webdavPath = "/" . $this->getFullDavFilesPath($user) . $expectedElement;
			$element = $responseXmlObject->xpath(
				"//d:response/d:href[text() = \"$webdavPath\"]"
			);
			if ($expectedToBeListed
				&& (!isset($element[0]) || $element[0]->__toString() !== $webdavPath)
			) {
				Assert::fail(
					"$webdavPath is not in propfind answer but should"
				);
			} elseif (!$expectedToBeListed && isset($element[0])
			) {
				Assert::fail(
					"$webdavPath is in propfind answer but should not be"
				);
			}
		}
	}

	/**
	 * @When user :user uploads file :source to :destination using the WebDAV API
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userUploadsAFileTo($user, $source, $destination) {
		$user = $this->getActualUsername($user);
		$file = \fopen($this->acceptanceTestsDirLocation() . $source, 'r');
		$this->pauseUploadDelete();
		$this->response = $this->makeDavRequest(
			$user, "PUT", $destination, [], $file
		);
		$this->lastUploadDeleteTime = \time();
		$this->setResponseXml(
			HttpRequestHelper::parseResponseAsXml($this->response)
		);
	}

	/**
	 * @Given user :user has uploaded file :source to :destination
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userHasUploadedAFileTo($user, $source, $destination) {
		$this->userUploadsAFileTo($user, $source, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * @When the user uploads file :source to :destination using the WebDAV API
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserUploadsAFileTo($source, $destination) {
		$this->userUploadsAFileTo($this->currentUser, $source, $destination);
	}

	/**
	 * @Given the user has uploaded file :source to :destination
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserHasUploadedFileTo($source, $destination) {
		$this->theUserUploadsAFileTo($source, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * @When /^user "([^"]*)" on "(LOCAL|REMOTE)" uploads file "([^"]*)" to "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $server
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userOnUploadsAFileTo($user, $server, $source, $destination) {
		$previousServer = $this->usingServer($server);
		$this->userUploadsAFileTo($user, $source, $destination);
		$this->usingServer($previousServer);
	}

	/**
	 * @Given /^user "([^"]*)" on "(LOCAL|REMOTE)" has uploaded file "([^"]*)" to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $server
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userOnHasUploadedAFileTo($user, $server, $source, $destination) {
		$this->userOnUploadsAFileTo($user, $server, $source, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * Upload file as a user with different headers
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 * @param array $headers
	 * @param int $noOfChunks Only use for chunked upload when $this->chunkingToUse is not null
	 *
	 * @return void
	 */
	public function uploadFileWithHeaders(
		$user,
		$source,
		$destination,
		$headers = [],
		$noOfChunks = 0
	) {
		$chunkingVersion = $this->chunkingToUse;
		if ($noOfChunks <= 0) {
			$chunkingVersion = null;
		}
		try {
			$this->responseXml = [];
			$this->pauseUploadDelete();
			$this->response = UploadHelper::upload(
				$this->getBaseUrl(),
				$this->getActualUsername($user),
				$this->getUserPassword($user),
				$source,
				$destination,
				$headers,
				($this->usingOldDavPath) ? 1 : 2,
				$chunkingVersion,
				$noOfChunks
			);
			$this->lastUploadDeleteTime = \time();
		} catch (BadResponseException $e) {
			// 4xx and 5xx responses cause an exception
			$this->response = $e->getResponse();
		}
	}

	/**
	 * @When /^user "([^"]*)" uploads file "([^"]*)" to "([^"]*)" in (\d+) chunks (?:with (new|old|v1|v2) chunking and)?\s?using the WebDAV API$/
	 * @When user :user uploads file :source to :destination with chunks using the WebDAV API
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 * @param int $noOfChunks
	 * @param string $chunkingVersion old|v1|new|v2 null for autodetect
	 * @param bool $async use asynchronous move at the end or not
	 * @param array $headers
	 *
	 * @return void
	 */
	public function userUploadsAFileToWithChunks(
		$user, $source, $destination, $noOfChunks = 2, $chunkingVersion = null, $async = false, $headers = []
	) {
		$user = $this->getActualUsername($user);
		Assert::assertGreaterThan(
			0, $noOfChunks, "What does it mean to have $noOfChunks chunks?"
		);
		//use the chunking version that works with the set dav version
		if ($chunkingVersion === null) {
			if ($this->usingOldDavPath) {
				$chunkingVersion = "v1";
			} else {
				$chunkingVersion = "v2";
			}
		}
		$this->useSpecificChunking($chunkingVersion);
		Assert::assertTrue(
			WebDavHelper::isValidDavChunkingCombination(
				($this->usingOldDavPath) ? 1 : 2,
				$this->chunkingToUse
			),
			"invalid chunking/webdav version combination"
		);

		if ($async === true) {
			$headers['OC-LazyOps'] = 'true';
		}
		$this->uploadFileWithHeaders(
			$user,
			$this->acceptanceTestsDirLocation() . $source,
			$destination,
			$headers,
			$noOfChunks
		);
	}

	/**
	 * @When /^user "([^"]*)" uploads file "([^"]*)" asynchronously to "([^"]*)" in (\d+) chunks (?:with (new|old|v1|v2) chunking and)?\s?using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 * @param int $noOfChunks
	 * @param string $chunkingVersion old|v1|new|v2 null for autodetect
	 *
	 * @return void
	 */
	public function userUploadsAFileAsyncToWithChunks(
		$user, $source, $destination, $noOfChunks = 2, $chunkingVersion = null
	) {
		$user = $this->getActualUsername($user);
		$this->userUploadsAFileToWithChunks(
			$user, $source, $destination, $noOfChunks, $chunkingVersion, true
		);
	}

	/**
	 * sets the chunking version from human readable format
	 *
	 * @param string $version (no|v1|v2|new|old)
	 *
	 * @return void
	 */
	public function useSpecificChunking($version) {
		if ($version === "v1" || $version === "old") {
			$this->chunkingToUse = 1;
		} elseif ($version === "v2" || $version === "new") {
			$this->chunkingToUse = 2;
		} elseif ($version === "no") {
			$this->chunkingToUse = null;
		} else {
			throw new InvalidArgumentException(
				"cannot set chunking version to $version"
			);
		}
	}

	/**
	 * Uploading with old/new dav and chunked/non-chunked.
	 *
	 * @When user :user uploads file :source to filenames based on :destination with all mechanisms using the WebDAV API
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userUploadsAFileToWithAllMechanisms(
		$user, $source, $destination
	) {
		$user = $this->getActualUsername($user);
		$this->uploadResponses = UploadHelper::uploadWithAllMechanisms(
			$this->getBaseUrl(), $this->getActualUsername($user),
			$this->getUserPassword($user),
			$this->acceptanceTestsDirLocation() . $source, $destination
		);
	}

	/**
	 * Overwriting with old/new dav and chunked/non-chunked.
	 *
	 * @When user :user overwrites from file :source to file :destination with all mechanisms using the WebDAV API
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userOverwritesAFileToWithAllMechanisms(
		$user, $source, $destination
	) {
		$user = $this->getActualUsername($user);
		$this->uploadResponses = UploadHelper::uploadWithAllMechanisms(
			$this->getBaseUrl(), $this->getActualUsername($user),
			$this->getUserPassword($user),
			$this->acceptanceTestsDirLocation() . $source, $destination, true
		);
	}

	/**
	 * @Then /^the HTTP status code of all upload responses should be "([^"]*)"$/
	 *
	 * @param int $statusCode
	 *
	 * @return void
	 */
	public function theHTTPStatusCodeOfAllUploadResponsesShouldBe($statusCode) {
		foreach ($this->uploadResponses as $response) {
			Assert::assertEquals(
				$statusCode,
				$response->getStatusCode(),
				'Response did not return expected status code'
			);
		}
	}

	/**
	 * @Then /^the HTTP reason phrase of all upload responses should be "([^"]*)"$/
	 *
	 * @param string $reasonPhrase
	 *
	 * @return void
	 */
	public function theHTTPReasonPhraseOfAllUploadResponsesShouldBe($reasonPhrase) {
		foreach ($this->uploadResponses as $response) {
			Assert::assertEquals(
				$reasonPhrase,
				$response->getReasonPhrase(),
				'Response did not return expected reason phrase'
			);
		}
	}

	/**
	 * @Then user :user should be able to upload file :source to :destination
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userShouldBeAbleToUploadFileTo($user, $source, $destination) {
		$user = $this->getActualUsername($user);
		$this->userUploadsAFileTo($user, $source, $destination);
		$this->asFileOrFolderShouldExist($user, null, $destination);
	}

	/**
	 * @Then user :user should not be able to upload file :source to :destination
	 *
	 * @param string $user
	 * @param string $source
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserShouldNotBeAbleToUploadFileTo($user, $source, $destination) {
		$this->userUploadsAFileTo($user, $source, $destination);
		$this->asFileOrFolderShouldNotExist($user, null, $destination);
	}

	/**
	 * @Then /^the HTTP status code of all upload responses should be between "(\d+)" and "(\d+)"$/
	 *
	 * @param int $minStatusCode
	 * @param int $maxStatusCode
	 *
	 * @return void
	 */
	public function theHTTPStatusCodeOfAllUploadResponsesShouldBeBetween(
		$minStatusCode, $maxStatusCode
	) {
		foreach ($this->uploadResponses as $response) {
			Assert::assertGreaterThanOrEqual(
				$minStatusCode,
				$response->getStatusCode(),
				'Response did not return expected status code'
			);
			Assert::assertLessThanOrEqual(
				$maxStatusCode,
				$response->getStatusCode(),
				'Response did not return expected status code'
			);
		}
	}

	/**
	 * Check that all the files uploaded with old/new dav and chunked/non-chunked exist.
	 *
	 * @Then /^as "([^"]*)" the files uploaded to "([^"]*)" with all mechanisms should (not|)\s?exist$/
	 *
	 * @param string $user
	 * @param string $destination
	 * @param string $shouldOrNot
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function filesUploadedToWithAllMechanismsShouldExist(
		$user, $destination, $shouldOrNot
	) {
		if ($shouldOrNot !== "not") {
			foreach (['old', 'new'] as $davVersion) {
				foreach (["{$davVersion}dav-regular", "{$davVersion}dav-{$davVersion}chunking"] as $suffix) {
					$this->asFileOrFolderShouldExist(
						$user, 'file', "$destination-$suffix"
					);
				}
			}
		} else {
			foreach (['old', 'new'] as $davVersion) {
				foreach (["{$davVersion}dav-regular", "{$davVersion}dav-{$davVersion}chunking"] as $suffix) {
					$this->asFileOrFolderShouldNotExist(
						$user, 'file', "$destination-$suffix"
					);
				}
			}
		}
	}

	/**
	 * @Then /^as user "([^"]*)" on server "([^"]*)" the files uploaded to "([^"]*)" with all mechanisms should (not|)\s?exist$/
	 *
	 * @param string $user
	 * @param string $server
	 * @param string $destination
	 * @param string $shouldOrNot
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function asUserOnServerTheFilesUploadedToWithAllMechanismsShouldExit(
		$user, $server, $destination, $shouldOrNot
	) {
		$previousServer = $this->usingServer($server);
		$this->filesUploadedToWithAllMechanismsShouldExist($user, $destination, $shouldOrNot);
		$this->usingServer($previousServer);
	}

	/**
	 * @Given user :user has uploaded file :destination of size :bytes bytes
	 *
	 * @param string $user
	 * @param string $destination
	 * @param string $bytes
	 *
	 * @return void
	 */
	public function userHasUploadedFileToOfSizeBytes($user, $destination, $bytes) {
		$user = $this->getActualUsername($user);
		$this->userUploadsAFileToOfSizeBytes($user, $destination, $bytes);
		$expectedElements = new TableNode([["$destination"]]);
		$this->checkElementList($user, $expectedElements);
	}

	/**
	 * @When user :user uploads file :destination of size :bytes bytes
	 *
	 * @param string $user
	 * @param string $destination
	 * @param string $bytes
	 *
	 * @return void
	 */
	public function userUploadsAFileToOfSizeBytes($user, $destination, $bytes) {
		$this->userUploadsAFileToEndingWithOfSizeBytes($user, $destination, 'a', $bytes);
	}

	/**
	 * @Given user :user has uploaded file :destination ending with :text of size :bytes bytes
	 *
	 * @param string $user
	 * @param string $destination
	 * @param string $text
	 * @param string $bytes
	 *
	 * @return void
	 */
	public function userHasUploadedFileToEndingWithOfSizeBytes($user, $destination, $text, $bytes) {
		$this->userUploadsAFileToEndingWithOfSizeBytes($user, $destination, $text, $bytes);
		$expectedElements = new TableNode([["$destination"]]);
		$this->checkElementList($user, $expectedElements);
	}

	/**
	 * @When user :user uploads file :destination ending with :text of size :bytes bytes
	 *
	 * @param string $user
	 * @param string $destination
	 * @param string $text
	 * @param string $bytes
	 *
	 * @return void
	 */
	public function userUploadsAFileToEndingWithOfSizeBytes($user, $destination, $text, $bytes) {
		$filename = "filespecificSize.txt";
		$this->createLocalFileOfSpecificSize($filename, $bytes, $text);
		Assert::assertFileExists($this->workStorageDirLocation() . $filename);
		$this->userUploadsAFileTo(
			$user,
			$this->temporaryStorageSubfolderName() . "/$filename",
			$destination
		);
		$this->removeFile($this->workStorageDirLocation(), $filename);
	}

	/**
	 * @When user :user uploads to these filenames with content :content using the webDAV API then the results should be as listed
	 *
	 * @param string $user
	 * @param string $content
	 * @param TableNode $table
	 *
	 * @return void
	 */
	public function userUploadsFilesWithContentTo(
		$user,
		$content,
		TableNode $table
	) {
		$user = $this->getActualUsername($user);
		foreach ($table->getHash() as $row) {
			$this->userUploadsAFileWithContentTo(
				$user,
				$content,
				$row['filename']
			);
			$this->theHTTPStatusCodeShouldBe(
				$row['http-code'],
				"HTTP status code is not the expected value while trying to upload " . $row['filename']
			);
			if ($row['exists'] === "yes") {
				$this->asFileOrFolderShouldExist($user, "entry", $row['filename']);
			} else {
				$this->asFileOrFolderShouldNotExist($user, "entry", $row['filename']);
			}
		}
	}

	/**
	 * @param string $user
	 * @param string $content
	 * @param string $destination
	 *
	 * @return string
	 */
	public function uploadFileWithContent(
		$user, $content, $destination
	) {
		$user = $this->getActualUsername($user);
		$this->pauseUploadDelete();
		$this->response = $this->makeDavRequest(
			$user, "PUT", $destination, [], $content
		);
		$this->lastUploadDeleteTime = \time();
		return $this->response->getHeader('oc-fileid');
	}

	/**
	 * @param $user
	 * @param $destination
	 * @param $mtime
	 *
	 * @return void
	 */
	public function uploadFileWithMtime(
		$user, $destination, $mtime
	) {
		$user = $this->getActualUsername($user);

		$this->response = $this->makeDavRequest(
			$user, "PUT", $destination, ["X-OC-Mtime" => $mtime]
		);
	}

	/**
	 * @When the administrator uploads file with content :content to :destination using the WebDAV API
	 *
	 * @param string $content
	 * @param string $destination
	 *
	 * @return string
	 */
	public function adminUploadsAFileWithContentTo(
		$content, $destination
	) {
		return $this->uploadFileWithContent($this->getAdminUsername(), $content, $destination);
	}

	/**
	 * @Given the administrator has uploaded file with content :content to :destination
	 *
	 * @param string $content
	 * @param string $destination
	 *
	 * @return string
	 */
	public function adminHasUploadedAFileWithContentTo(
		$content, $destination
	) {
		$fileId = $this->uploadFileWithContent($this->getAdminUsername(), $content, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
		return $fileId;
	}

	/**
	 * @When user :user uploads file with content :content to :destination using the WebDAV API
	 *
	 * @param string $user
	 * @param string $content
	 * @param string $destination
	 *
	 * @return string
	 */
	public function userUploadsAFileWithContentTo(
		$user, $content, $destination
	) {
		return $this->uploadFileWithContent($user, $content, $destination);
	}

	/**
	 * @When user :user uploads file to :destination with mtime :mtime using the WebDAV API
	 *
	 * @param string $user
	 * @param string $destination
	 * @param string $mtime Time in human readable format is taken as input which is converted into milliseconds that is used by API
	 *
	 * @return void
	 */
	public function userUploadsFileWithContentToWithMtimeUsingTheWebdavApi(
		$user, $destination, $mtime
	) {
		$mtime = new DateTime($mtime);
		$mtime = $mtime->format('U');
		return $this->uploadFileWithMtime($user, $destination, $mtime);
	}

	/**
	 * @Then as :user the mtime of the file :resource should be :mtime
	 *
	 * @param string $user
	 * @param string $resource
	 * @param string $mtime
	 *
	 * @return void
	 */
	public function theMtimeOfTheFileShouldBe(
		$user, $resource, $mtime
	) {
		$user = $this->getActualUsername($user);
		$password = $this->getPasswordForUser($user);
		$this->response = WebDavHelper::propfind(
			$this->getBaseUrl(), $user, $password, $resource, ["getlastmodified"]
		);
		$reponseXmlObject = HttpRequestHelper::getResponseXml($this->response);
		$xmlpart = $reponseXmlObject->xpath("//d:getlastmodified");
		Assert::assertEquals(
			$mtime, $xmlpart[0]->__toString()
		);
	}

	/**
	 * @Given user :user has uploaded file with content :content to :destination
	 *
	 * @param string $user
	 * @param string $content
	 * @param string $destination
	 *
	 * @return string
	 */
	public function userHasUploadedAFileWithContentTo(
		$user, $content, $destination
	) {
		$user = $this->getActualUsername($user);
		$fileId = $this->uploadFileWithContent($user, $content, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
		return $fileId;
	}

	/**
	 * @When user :user uploads file with checksum :checksum and content :content to :destination using the WebDAV API
	 *
	 * @param string $user
	 * @param string $checksum
	 * @param string $content
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userUploadsAFileWithChecksumAndContentTo(
		$user, $checksum, $content, $destination
	) {
		$this->pauseUploadDelete();
		$this->response = $this->makeDavRequest(
			$user,
			"PUT",
			$destination,
			['OC-Checksum' => $checksum],
			$content
		);
		$this->lastUploadDeleteTime = \time();
	}

	/**
	 * @Given user :user has uploaded file with checksum :checksum and content :content to :destination
	 *
	 * @param string $user
	 * @param string $checksum
	 * @param string $content
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userHasUploadedAFileWithChecksumAndContentTo(
		$user, $checksum, $content, $destination
	) {
		$this->userUploadsAFileWithChecksumAndContentTo(
			$user, $checksum, $content, $destination
		);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * @Then /^user "([^"]*)" should be able to delete (file|folder|entry) "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $entry
	 * @param string $source
	 *
	 * @return void
	 * @throws Exception
	 */
	public function userShouldBeAbleToDeleteEntry($user, $entry, $source) {
		$user = $this->getActualUsername($user);
		$this->asFileOrFolderShouldExist($user, $entry, $source);
		$this->userDeletesFile($user, $source);
		$this->asFileOrFolderShouldNotExist($user, $entry, $source);
	}

	/**
	 * @Then /^user "([^"]*)" should not be able to delete (file|folder|entry) "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $entry
	 * @param string $source
	 *
	 * @return void
	 */
	public function theUserShouldNotBeAbleToDeleteEntry($user, $entry, $source) {
		$this->asFileOrFolderShouldExist($user, $entry, $source);
		$this->userDeletesFile($user, $source);
		$this->asFileOrFolderShouldExist($user, $entry, $source);
	}

	/**
	 * @Given file :file has been deleted for user :user
	 *
	 * @param string $file
	 * @param string $user
	 *
	 * @return void
	 */
	public function fileHasBeenDeleted($file, $user) {
		$this->userHasDeletedFile($user, $file);
	}

	/**
	 * @When /^user "([^"]*)" (?:deletes|unshares) (?:file|folder) "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $file
	 *
	 * @return void
	 */
	public function userDeletesFile($user, $file) {
		$user = $this->getActualUsername($user);
		$this->pauseUploadDelete();
		$this->response = $this->makeDavRequest($user, 'DELETE', $file, []);
		$this->lastUploadDeleteTime = \time();
	}

	/**
	 * @Given /^user "([^"]*)" has (?:deleted|unshared) (?:file|folder) "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $file
	 *
	 * @return void
	 */
	public function userHasDeletedFile($user, $file) {
		$user = $this->getActualUsername($user);
		$this->userDeletesFile($user, $file);
		// If the file was there and got deleted then we get a 204
		// If the file was already not there then then get a 404
		// Either way, the outcome of the "given" step is OK
		$this->theHTTPStatusCodeShouldBeOr("204", "404");
	}

	/**
	 * @When /^the user (?:deletes|unshares) (?:file|folder) "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $file
	 *
	 * @return void
	 */
	public function theUserDeletesFile($file) {
		$this->userDeletesFile($this->getCurrentUser(), $file);
	}

	/**
	 * @Given /^the user has (?:deleted|unshared) (?:file|folder) "([^"]*)"$/
	 *
	 * @param string $file
	 *
	 * @return void
	 */
	public function theUserHasDeletedFile($file) {
		$this->userHasDeletedFile($this->getCurrentUser(), $file);
	}

	/**
	 * @When /^user "([^"]*)" (?:deletes|unshares) these (?:files|folders|entries) without delays using the WebDAV API$/
	 *
	 * @param string $user
	 * @param TableNode $table of files or folders to delete
	 *
	 * @return void
	 */
	public function userDeletesFilesFoldersWithoutDelays($user, $table) {
		$user = $this->getActualUsername($user);
		$this->verifyTableNodeColumnsCount($table, 1);
		foreach ($table->getTable() as $entry) {
			$entryName = $entry[0];
			$this->response = $this->makeDavRequest($user, 'DELETE', $entryName, []);
		}
		$this->lastUploadDeleteTime = \time();
	}

	/**
	 * @When /^the user (?:deletes|unshares) these (?:files|folders|entries) without delays using the WebDAV API$/
	 *
	 * @param TableNode $table of files or folders to delete
	 *
	 * @return void
	 */
	public function theUserDeletesFilesFoldersWithoutDelays($table) {
		$this->userDeletesFilesFoldersWithoutDelays($this->getCurrentUser(), $table);
	}

	/**
	 * @When /^user "([^"]*)" on "(LOCAL|REMOTE)" (?:deletes|unshares) (?:file|folder) "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $server
	 * @param string $file
	 *
	 * @return void
	 */
	public function userOnDeletesFile($user, $server, $file) {
		$previousServer = $this->usingServer($server);
		$this->userDeletesFile($user, $file);
		$this->usingServer($previousServer);
	}

	/**
	 * @Given /^user "([^"]*)" on "(LOCAL|REMOTE)" has (?:deleted|unshared) (?:file|folder) "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $server
	 * @param string $file
	 *
	 * @return void
	 */
	public function userOnHasDeletedFile($user, $server, $file) {
		$this->userOnDeletesFile($user, $server, $file);
		// If the file was there and got deleted then we get a 204
		// If the file was already not there then then get a 404
		// Either way, the outcome of the "given" step is OK
		$this->theHTTPStatusCodeShouldBeOr("204", "404");
	}

	/**
	 * @When user :user creates folder :destination using the WebDAV API
	 *
	 * @param string $user
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userCreatesFolder($user, $destination) {
		$user = $this->getActualUsername($user);
		$destination = '/' . \ltrim($destination, '/');
		$this->response = $this->makeDavRequest(
			$user, "MKCOL", $destination, []
		);
		$this->setResponseXml(
			HttpRequestHelper::parseResponseAsXml($this->response)
		);
	}

	/**
	 * @Given user :user has created folder :destination
	 *
	 * @param string $user
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userHasCreatedFolder($user, $destination) {
		$user = $this->getActualUsername($user);
		$this->userCreatesFolder($user, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * @When the user creates folder :destination using the WebDAV API
	 *
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserCreatesFolder($destination) {
		$this->userCreatesFolder($this->getCurrentUser(), $destination);
	}

	/**
	 * @Given the user has created folder :destination
	 *
	 * @param string $destination
	 *
	 * @return void
	 */
	public function theUserHasCreatedFolder($destination) {
		$this->theUserCreatesFolder($destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * Old style chunking upload
	 *
	 * @When user :user uploads the following :total chunks to :file with old chunking and using the WebDAV API
	 *
	 * @param string $user
	 * @param string $total
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content with column headings, e.g.
	 *                                | number | content                 |
	 *                                | 1      | first data              |
	 *                                | 2      | followed by second data |
	 *                                Chunks may be numbered out-of-order if desired.
	 *
	 * @return void
	 */
	public function userUploadsTheFollowingTotalChunksUsingOldChunking(
		$user, $total, $file, TableNode $chunkDetails
	) {
		$this->verifyTableNodeColumns($chunkDetails, ['number', 'content']);
		foreach ($chunkDetails->getHash() as $chunkDetail) {
			$chunkNumber = $chunkDetail['number'];
			$chunkContent = $chunkDetail['content'];
			$this->userUploadsChunkedFile($user, $chunkNumber, $total, $chunkContent, $file);
		}
	}

	/**
	 * Old style chunking upload
	 *
	 * @Given user :user has uploaded the following :total chunks to :file with old chunking
	 *
	 * @param string $user
	 * @param string $total
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content with following headings, e.g.
	 *                                | number | content                 |
	 *                                | 1      | first data              |
	 *                                | 2      | followed by second data |
	 *                                Chunks may be numbered out-of-order if desired.
	 *
	 * @return void
	 */
	public function userHasUploadedTheFollowingTotalChunksUsingOldChunking(
		$user, $total, $file, TableNode $chunkDetails
	) {
		$this->verifyTableNodeColumns($chunkDetails, ['number', 'content']);
		foreach ($chunkDetails->getHash() as $chunkDetail) {
			$chunkNumber = $chunkDetail['number'];
			$chunkContent = $chunkDetail['content'];
			$this->userHasUploadedChunkedFile($user, $chunkNumber, $total, $chunkContent, $file);
		}
	}

	/**
	 * Old style chunking upload
	 *
	 * @When user :user uploads the following chunks to :file with old chunking and using the WebDAV API
	 *
	 * @param string $user
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content with column headings, e.g.
	 *                                | number | content                 |
	 *                                | 1      | first data              |
	 *                                | 2      | followed by second data |
	 *                                Chunks may be numbered out-of-order if desired.
	 *
	 * @return void
	 */
	public function userUploadsTheFollowingChunksUsingOldChunking(
		$user, $file, TableNode $chunkDetails
	) {
		$total = \count($chunkDetails->getHash());
		$this->userUploadsTheFollowingTotalChunksUsingOldChunking(
			$user, $total, $file, $chunkDetails
		);
	}

	/**
	 * Old style chunking upload
	 *
	 * @Given user :user has uploaded the following chunks to :file with old chunking
	 *
	 * @param string $user
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content with headings, e.g.
	 *                                | number | content                 |
	 *                                | 1      | first data              |
	 *                                | 2      | followed by second data |
	 *                                Chunks may be numbered out-of-order if desired.
	 *
	 * @return void
	 */
	public function userHasUploadedTheFollowingChunksUsingOldChunking(
		$user, $file, TableNode $chunkDetails
	) {
		$total = \count($chunkDetails->getRows());
		$this->userHasUploadedTheFollowingTotalChunksUsingOldChunking(
			$user, $total, $file, $chunkDetails
		);
	}

	/**
	 * Old style chunking upload
	 *
	 * @When user :user uploads chunk file :num of :total with :data to :destination using the WebDAV API
	 *
	 * @param string $user
	 * @param int $num
	 * @param int $total
	 * @param string $data
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userUploadsChunkedFile(
		$user, $num, $total, $data, $destination
	) {
		$user = $this->getActualUsername($user);
		$num -= 1;
		$file = "$destination-chunking-42-$total-$num";
		$this->pauseUploadDelete();
		$this->response = $this->makeDavRequest(
			$user, 'PUT', $file, ['OC-Chunked' => '1'], $data, "uploads"
		);
		$this->lastUploadDeleteTime = \time();
	}

	/**
	 * Old style chunking upload
	 *
	 * @Given user :user has uploaded chunk file :num of :total with :data to :destination
	 *
	 * @param string $user
	 * @param int $num
	 * @param int $total
	 * @param string $data
	 * @param string $destination
	 *
	 * @return void
	 */
	public function userHasUploadedChunkedFile(
		$user, $num, $total, $data, $destination
	) {
		$user = $this->getActualUsername($user);
		$this->userUploadsChunkedFile($user, $num, $total, $data, $destination);
		$this->theHTTPStatusCodeShouldBeOr("201", "204");
	}

	/**
	 * New style chunking upload
	 *
	 * @When /^user "([^"]*)" uploads the following chunks\s?(asynchronously|) to "([^"]*)" with new chunking and using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $type "asynchronously" or empty
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content, with headings e.g.
	 *                                | number | content      |
	 *                                | 1      | first data   |
	 *                                | 2      | second data  |
	 *                                Chunks may be numbered out-of-order if desired.
	 *
	 * @return void
	 */
	public function userUploadsTheFollowingChunksUsingNewChunking(
		$user, $type, $file, TableNode $chunkDetails
	) {
		$this->uploadTheFollowingChunksUsingNewChunking(
			$user, $type, $file, $chunkDetails
		);
	}

	/**
	 * New style chunking upload
	 *
	 * @Given /^user "([^"]*)" has uploaded the following chunks\s?(asynchronously|) to "([^"]*)" with new chunking$/
	 *
	 * @param string $user
	 * @param string $type "asynchronously" or empty
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content without column headings, e.g.
	 *                                | number | content                 |
	 *                                | 1      | first data              |
	 *                                | 2      | followed by second data |
	 *                                Chunks may be numbered out-of-order if desired.
	 *
	 * @return void
	 */
	public function userHasUploadedTheFollowingChunksUsingNewChunking(
		$user, $type, $file, TableNode $chunkDetails
	) {
		$this->uploadTheFollowingChunksUsingNewChunking(
			$user, $type, $file, $chunkDetails, true
		);
	}

	/**
	 * New style chunking upload
	 *
	 * @param string $user
	 * @param string $type "asynchronously" or empty
	 * @param string $file
	 * @param TableNode $chunkDetails table of 2 columns, chunk number and chunk
	 *                                content with column headings, e.g.
	 *                                | number | content            |
	 *                                | 1      | first data         |
	 *                                | 2      | second data        |
	 *                                Chunks may be numbered out-of-order if desired.
	 * @param bool $checkActions
	 *
	 * @return void
	 */
	public function uploadTheFollowingChunksUsingNewChunking(
		$user, $type, $file, TableNode $chunkDetails, $checkActions = false
	) {
		$user = $this->getActualUsername($user);
		$async = false;
		if ($type === "asynchronously") {
			$async = true;
		}
		$this->verifyTableNodeColumns($chunkDetails, ["number", "content"]);
		$this->userUploadsChunksUsingNewChunking(
			$user, $file, 'chunking-42', $chunkDetails->getHash(), $async, $checkActions
		);
	}

	/**
	 * New style chunking upload
	 *
	 * @param string $user
	 * @param string $file
	 * @param string $chunkingId
	 * @param array $chunkDetails of chunks of the file. Each array entry is
	 *                            itself an array of 2 items:
	 *                            [number] the chunk number
	 *                            [content] data content of the chunk
	 *                            Chunks may be numbered out-of-order if desired.
	 * @param bool $async use asynchronous MOVE at the end or not
	 * @param bool $checkActions
	 *
	 * @return void
	 */
	public function userUploadsChunksUsingNewChunking(
		$user, $file, $chunkingId, $chunkDetails, $async = false, $checkActions = false
	) {
		$this->pauseUploadDelete();
		if ($checkActions) {
			$this->userHasCreatedANewChunkingUploadWithId($user, $chunkingId);
		} else {
			$this->userCreatesANewChunkingUploadWithId($user, $chunkingId);
		}
		foreach ($chunkDetails as $chunkDetail) {
			$chunkNumber = $chunkDetail['number'];
			$chunkContent = $chunkDetail['content'];
			if ($checkActions) {
				$this->userHasUploadedNewChunkFileOfWithToId($user, $chunkNumber, $chunkContent, $chunkingId);
			} else {
				$this->userUploadsNewChunkFileOfWithToId($user, $chunkNumber, $chunkContent, $chunkingId);
			}
		}
		$headers = [];
		if ($async === true) {
			$headers = ['OC-LazyOps' => 'true'];
		}
		$this->moveNewDavChunkToFinalFile($user, $chunkingId, $file, $headers);
		if ($checkActions) {
			$this->theHTTPStatusCodeShouldBeSuccess();
		}
		$this->lastUploadDeleteTime = \time();
	}

	/**
	 * @When user :user creates a new chunking upload with id :id using the WebDAV API
	 *
	 * @param string $user
	 * @param string $id
	 *
	 * @return void
	 */
	public function userCreatesANewChunkingUploadWithId($user, $id) {
		$user = $this->getActualUsername($user);
		$destination = "/uploads/$user/$id";
		$this->response = $this->makeDavRequest(
			$user, 'MKCOL', $destination, [], null, "uploads"
		);
	}

	/**
	 * @Given user :user has created a new chunking upload with id :id
	 *
	 * @param string $user
	 * @param string $id
	 *
	 * @return void
	 */
	public function userHasCreatedANewChunkingUploadWithId($user, $id) {
		$this->userCreatesANewChunkingUploadWithId($user, $id);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When user :user uploads new chunk file :num with :data to id :id using the WebDAV API
	 *
	 * @param string $user
	 * @param int $num
	 * @param string $data
	 * @param string $id
	 *
	 * @return void
	 */
	public function userUploadsNewChunkFileOfWithToId($user, $num, $data, $id) {
		$user = $this->getActualUsername($user);
		$destination = "/uploads/$user/$id/$num";
		$this->response = $this->makeDavRequest(
			$user, 'PUT', $destination, [], $data, "uploads"
		);
	}

	/**
	 * @Given user :user has uploaded new chunk file :num with :data to id :id
	 *
	 * @param string $user
	 * @param int $num
	 * @param string $data
	 * @param string $id
	 *
	 * @return void
	 */
	public function userHasUploadedNewChunkFileOfWithToId($user, $num, $data, $id) {
		$this->userUploadsNewChunkFileOfWithToId($user, $num, $data, $id);
		$this->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When /^user "([^"]*)" moves new chunk file with id "([^"]*)"\s?(asynchronously|) to "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $id
	 * @param string $type "asynchronously" or empty
	 * @param string $dest
	 *
	 * @return void
	 */
	public function userMovesNewChunkFileWithIdToMychunkedfile(
		$user, $id, $type, $dest
	) {
		$headers = [];
		if ($type === "asynchronously") {
			$headers = ['OC-LazyOps' => 'true'];
		}
		$this->moveNewDavChunkToFinalFile($user, $id, $dest, $headers);
	}

	/**
	 * @Given /^user "([^"]*)" has moved new chunk file with id "([^"]*)"\s?(asynchronously|) to "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $id
	 * @param string $type "asynchronously" or empty
	 * @param string $dest
	 *
	 * @return void
	 */
	public function userHasMovedNewChunkFileWithIdToMychunkedfile(
		$user, $id, $type, $dest
	) {
		$this->userMovesNewChunkFileWithIdToMychunkedfile($user, $id, $type, $dest);
		$this->theHTTPStatusCodeShouldBe("201");
	}

	/**
	 * @When user :user cancels chunking-upload with id :id using the WebDAV API
	 *
	 * @param string $user
	 * @param string $id
	 *
	 * @return void
	 */
	public function userCancelsUploadWithId(
		$user, $id
	) {
		$this->deleteUpload($user, $id, []);
	}

	/**
	 * @Given user :user has canceled new chunking-upload with id :id
	 *
	 * @param string $user
	 * @param string $id
	 *
	 * @return void
	 */
	public function userHasCanceledUploadWithId(
		$user, $id
	) {
		$this->userCancelsUploadWithId($user, $id);
		$this->theHTTPStatusCodeShouldBe("201");
	}

	/**
	 * @When /^user "([^"]*)" moves new chunk file with id "([^"]*)"\s?(asynchronously|) to "([^"]*)" with size (.*) using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $id
	 * @param string $type "asynchronously" or empty
	 * @param string $dest
	 * @param int $size
	 *
	 * @return void
	 */
	public function userMovesNewChunkFileWithIdToMychunkedfileWithSize(
		$user, $id, $type, $dest, $size
	) {
		$headers = ['OC-Total-Length' => $size];
		if ($type === "asynchronously") {
			$headers['OC-LazyOps'] = 'true';
		}
		$this->moveNewDavChunkToFinalFile(
			$user, $id, $dest, $headers
		);
	}

	/**
	 * @Given /^user "([^"]*)" has moved new chunk file with id "([^"]*)"\s?(asynchronously|) to "([^"]*)" with size (.*)$/
	 *
	 * @param string $user
	 * @param string $id
	 * @param string $type "asynchronously" or empty
	 * @param string $dest
	 * @param int $size
	 *
	 * @return void
	 */
	public function userHasMovedNewChunkFileWithIdToMychunkedfileWithSize(
		$user, $id, $type, $dest, $size
	) {
		$this->userMovesNewChunkFileWithIdToMychunkedfileWithSize(
			$user, $id, $type, $dest, $size
		);
		$this->theHTTPStatusCodeShouldBe("201");
	}

	/**
	 * @When /^user "([^"]*)" moves new chunk file with id "([^"]*)"\s?(asynchronously|) to "([^"]*)" with checksum "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $id
	 * @param string $type "asynchronously" or empty
	 * @param string $dest
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function userMovesNewChunkFileWithIdToMychunkedfileWithChecksum(
		$user, $id, $type, $dest, $checksum
	) {
		$headers = ['OC-Checksum' => $checksum];
		if ($type === "asynchronously") {
			$headers['OC-LazyOps'] = 'true';
		}
		$this->moveNewDavChunkToFinalFile(
			$user, $id, $dest, $headers
		);
	}

	/**
	 * @Given /^user "([^"]*)" has moved new chunk file with id "([^"]*)"\s?(asynchronously|) to "([^"]*)" with checksum "([^"]*)"
	 *
	 * @param string $user
	 * @param string $id
	 * @param string $type "asynchronously" or empty
	 * @param string $dest
	 * @param string $checksum
	 *
	 * @return void
	 */
	public function userHasMovedNewChunkFileWithIdToMychunkedfileWithChecksum(
		$user, $id, $type, $dest, $checksum
	) {
		$this->userMovesNewChunkFileWithIdToMychunkedfileWithChecksum(
			$user, $id, $type, $dest, $checksum
		);
		$this->theHTTPStatusCodeShouldBe("201");
	}

	/**
	 * Move chunked new dav file to final file
	 *
	 * @param string $user user
	 * @param string $id upload id
	 * @param string $destination destination path
	 * @param array $headers extra headers
	 *
	 * @return void
	 */
	private function moveNewDavChunkToFinalFile($user, $id, $destination, $headers) {
		$user = $this->getActualUsername($user);
		$source = "/uploads/$user/$id/.file";
		$headers['Destination'] = $this->destinationHeaderValue(
			$user, $destination
		);

		$this->response = $this->makeDavRequest(
			$user, 'MOVE', $source, $headers, null, "uploads"
		);
	}

	/**
	 * Delete chunked-upload directory
	 *
	 * @param string $user user
	 * @param string $id upload id
	 * @param array $headers extra headers
	 *
	 * @return void
	 */
	private function deleteUpload($user, $id, $headers) {
		$source = "/uploads/$user/$id";
		$this->response = $this->makeDavRequest(
			$user, 'DELETE', $source, $headers, null, "uploads"
		);
	}

	/**
	 * URL encodes the given path but keeps the slashes
	 *
	 * @param string $path to encode
	 *
	 * @return string encoded path
	 */
	public function encodePath($path) {
		// slashes need to stay
		return \str_replace('%2F', '/', \rawurlencode($path));
	}

	/**
	 * @When an unauthenticated client connects to the dav endpoint using the WebDAV API
	 *
	 * @return void
	 */
	public function connectingToDavEndpoint() {
		$this->response = $this->makeDavRequest(
			null, 'PROPFIND', '', []
		);
	}

	/**
	 * @Given an unauthenticated client has connected to the dav endpoint
	 *
	 * @return void
	 */
	public function hasConnectedToDavEndpoint() {
		$this->connectingToDavEndpoint();
		$this->theHTTPStatusCodeShouldBe("401");
	}

	/**
	 * @Then there should be no duplicate headers
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function thereAreNoDuplicateHeaders() {
		$headers = $this->response->getHeaders();
		foreach ($headers as $headerName => $headerValues) {
			// if a header has multiple values, they must be different
			if (\count($headerValues) > 1
				&& \count(\array_unique($headerValues)) < \count($headerValues)
			) {
				throw new \Exception("Duplicate header found: $headerName");
			}
		}
	}

	/**
	 * @Then the following headers should not be set
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theFollowingHeadersShouldNotBeSet(TableNode $table) {
		$this->verifyTableNodeColumns(
			$table,
			['header']
		);
		foreach ($table->getColumnsHash() as $header) {
			$headerName = $header['header'];
			$headerValue = $this->response->getHeader($headerName);
			//Note: getHeader returns an empty array if the named header does not exist
			if (isset($headerValue[0])) {
				$headerValue0 = $headerValue[0];
			} else {
				$headerValue0 = '';
			}
			Assert::assertEmpty(
				$headerValue,
				"header $headerName should not exist " .
				"but does and is set to $headerValue0"
			);
		}
	}

	/**
	 * @Then the following headers should match these regular expressions
	 *
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function headersShouldMatchRegularExpressions(TableNode $table) {
		$this->verifyTableNodeColumnsCount($table, 2);
		foreach ($table->getTable() as $header) {
			$headerName = $header[0];
			$expectedHeaderValue = $header[1];
			$expectedHeaderValue = $this->substituteInLineCodes(
				$expectedHeaderValue, null, ['preg_quote' => ['/']]
			);

			$returnedHeaders = $this->response->getHeader($headerName);
			$returnedHeader = $returnedHeaders[0];
			Assert::assertNotFalse(
				(bool) \preg_match($expectedHeaderValue, $returnedHeader),
				"'$expectedHeaderValue' does not match '$returnedHeader'"
			);
		}
	}

	/**
	 * @Then the following headers should match these regular expressions for user :user
	 *
	 * @param string $user
	 * @param TableNode $table
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function headersShouldMatchRegularExpressionsForUser($user, TableNode $table) {
		$this->verifyTableNodeColumnsCount($table, 2);
		$user = $this->getActualUsername($user);
		foreach ($table->getTable() as $header) {
			$headerName = $header[0];
			$expectedHeaderValue = $header[1];
			$expectedHeaderValue = $this->substituteInLineCodes(
				$expectedHeaderValue, $user, ['preg_quote' => ['/']]
			);

			$returnedHeaders = $this->response->getHeader($headerName);
			$returnedHeader = $returnedHeaders[0];
			Assert::assertNotFalse(
				(bool) \preg_match($expectedHeaderValue, $returnedHeader),
				"'$expectedHeaderValue' does not match '$returnedHeader'"
			);
		}
	}

	/**
	 * @When /^user "([^"]*)" deletes everything from folder "([^"]*)" using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $folder
	 * @param bool $checkEachDelete
	 *
	 * @return void
	 */
	public function userDeletesEverythingInFolder(
		$user, $folder, $checkEachDelete = false
	) {
		$user = $this->getActualUsername($user);
		$responseXmlObject = $this->listFolder($user, $folder, 1);
		$elementList = $responseXmlObject->xpath("//d:response/d:href");
		if (\is_array($elementList) && \count($elementList)) {
			\array_shift($elementList); //don't delete the folder itself
			$davPrefix = "/" . $this->getFullDavFilesPath($user);
			foreach ($elementList as $element) {
				$element = \substr($element, \strlen($davPrefix));
				if ($checkEachDelete) {
					$this->userHasDeletedFile($user, $element);
				} else {
					$this->userDeletesFile($user, $element);
				}
			}
		}
	}

	/**
	 * @Given /^user "([^"]*)" has deleted everything from folder "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $folder
	 *
	 * @return void
	 */
	public function userHasDeletedEverythingInFolder($user, $folder) {
		$this->userDeletesEverythingInFolder($user, $folder, true);
	}

	/**
	 * @When user :user downloads the preview of :path with width :width and height :height using the WebDAV API
	 *
	 * @param $user
	 * @param $path
	 * @param $width
	 * @param $height
	 *
	 * @return void
	 */
	public function downloadPreviewOfFiles($user, $path, $width, $height) {
		$this->downloadPreviews(
			$user, $path, null, $width, $height
		);
	}

	/**
	 * @When user :user1 downloads the preview of :path of :user2 with width :width and height :height using the WebDAV API
	 *
	 * @param $user1
	 * @param $path
	 * @param $doDavRequestAsUser
	 * @param $width
	 * @param $height
	 *
	 * @return void
	 */
	public function downloadPreviewOfOtherUser($user1, $path, $doDavRequestAsUser, $width, $height) {
		$this->downloadPreviews(
			$user1, $path, $doDavRequestAsUser, $width, $height
		);
	}

	/**
	 * @Then the downloaded image should be :width pixels wide and :height pixels high
	 *
	 * @param $width
	 * @param $height
	 *
	 * @return void
	 */
	public function imageDimensionsShouldBe($width, $height) {
		$size = \getimagesizefromstring($this->response->getBody()->getContents());
		Assert::assertNotFalse($size, "could not get size of image");
		Assert::assertEquals($width, $size[0], "width not as expected");
		Assert::assertEquals($height, $size[1], "height not as expected");
	}
	/**
	 * @param string $user
	 * @param string $path
	 *
	 * @return int
	 */
	public function getFileIdForPath($user, $path) {
		$user = $this->getActualUsername($user);
		try {
			return WebDavHelper::getFileIdForPath(
				$this->getBaseUrl(),
				$user,
				$this->getPasswordForUser($user),
				$path
			);
		} catch (Exception $e) {
			return null;
		}
	}

	/**
	 * @Given /^user "([^"]*)" has stored id of file "([^"]*)"$/
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userStoresFileIdForPath($user, $path) {
		$this->storedFileID = $this->getFileIdForPath($user, $path);
	}

	/**
	 * @Then /^user "([^"]*)" file "([^"]*)" should have the previously stored id$/
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userFileShouldHaveStoredId($user, $path) {
		$user = $this->getActualUsername($user);
		$currentFileID = $this->getFileIdForPath($user, $path);
		Assert::assertEquals(
			$currentFileID,
			$this->storedFileID,
			__METHOD__
			. " User '$user' file '$path' does not have the previously stored id '{$this->storedFileID}', but has '$currentFileID'."
		);
	}

	/**
	 * @Then /^the (?:Cal|Card)?DAV (exception|message|reason) should be "([^"]*)"$/
	 *
	 * @param string $element exception|message|reason
	 * @param string $message
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theDavElementShouldBe($element, $message) {
		WebDavAssert::assertDavResponseElementIs(
			$element, $message, $this->responseXml
		);
	}

	/**
	 * @param string $shouldOrNot (not|)
	 * @param TableNode $expectedFiles
	 * @param string|null $user
	 *
	 * @return void
	 */
	public function propfindResultShouldContainEntries(
		$shouldOrNot, TableNode $expectedFiles, $user = null
	) {
		$this->verifyTableNodeColumnsCount($expectedFiles, 1);
		$elementRows = $expectedFiles->getRows();
		$should = ($shouldOrNot !== "not");

		foreach ($elementRows as $expectedFile) {
			$fileFound = $this->findEntryFromPropfindResponse(
				$expectedFile[0],
				$user
			);
			if ($should) {
				Assert::assertNotEmpty(
					$fileFound,
					"response does not contain the entry '$expectedFile[0]'"
				);
			} else {
				Assert::assertFalse(
					$fileFound,
					"response does contain the entry '$expectedFile[0]' but should not"
				);
			}
		}
	}

	/**
	 * @Then /^the (?:propfind|search) result of user "([^"]*)" should (not|)\s?contain these (?:files|entries):$/
	 *
	 * @param string $user
	 * @param string $shouldOrNot (not|)
	 * @param TableNode $expectedFiles
	 *
	 * @return void
	 */
	public function thePropfindResultShouldContainEntries(
		$user, $shouldOrNot, TableNode $expectedFiles
	) {
		$user = $this->getActualUsername($user);
		$this->propfindResultShouldContainEntries(
			$shouldOrNot, $expectedFiles, $user
		);
	}

	/**
	 * @Then the propfind/search result should contain :numFiles files/entries
	 *
	 * @param int $numFiles
	 *
	 * @return void
	 */
	public function propfindResultShouldContainNumEntries($numFiles) {
		//if we are using that step the second time in a scenario e.g. 'But ... should not'
		//then don't parse the result again, because the result in a ResponseInterface
		if (empty($this->responseXml)) {
			$this->setResponseXml(
				HttpRequestHelper::parseResponseAsXml($this->response)
			);
		}
		$multistatusResults = $this->responseXml["value"];
		if ($multistatusResults === null) {
			$multistatusResults = [];
		}
		Assert::assertEquals(
			(int) $numFiles,
			\count($multistatusResults),
			__METHOD__
			. " Expected result to contain '"
			. (int) $numFiles
			. "' files/entries, but got '"
			. \count($multistatusResults)
			. "' files/entries."
		);
	}

	/**
	 * @Then the propfind/search result should contain any :expectedNumber of these files/entries:
	 *
	 * @param integer $expectedNumber
	 * @param TableNode $expectedFiles
	 *
	 * @return void
	 */
	public function theSearchResultShouldContainAnyOfTheseEntries(
		$expectedNumber, TableNode $expectedFiles
	) {
		$this->theSearchResultOfUserShouldContainAnyOfTheseEntries(
			$this->getCurrentUser(),
			$expectedNumber,
			$expectedFiles
		);
	}

	/**
	 * @Then the propfind/search result of user :user should contain any :expectedNumber of these files/entries:
	 *
	 * @param string $user
	 * @param integer $expectedNumber
	 * @param TableNode $expectedFiles
	 *
	 * @return void
	 */
	public function theSearchResultOfUserShouldContainAnyOfTheseEntries(
		$user, $expectedNumber, TableNode $expectedFiles
	) {
		$user = $this->getActualUsername($user);
		$this->verifyTableNodeColumnsCount($expectedFiles, 1);
		$this->propfindResultShouldContainNumEntries($expectedNumber);
		$elementRows = $expectedFiles->getColumn(0);
		// Remove any "/" from the front (or back) of the expected values passed
		// into the step. findEntryFromPropfindResponse returns entries without
		// any leading (or trailing) slash
		$expectedEntries = \array_map(
			function ($value) {
				return \trim($value, "/");
			},
			$elementRows
		);
		$resultEntries = $this->findEntryFromPropfindResponse(null, $user);
		foreach ($resultEntries as $resultEntry) {
			Assert::assertContains($resultEntry, $expectedEntries);
		}
	}

	/**
	 * @param string|null $user
	 *
	 * @return array
	 */
	public function findEntryFromReportResponse($user) {
		$responseXmlObj = $this->getResponseXmlObject();
		$responseResources = [];
		$hrefs = $responseXmlObj->xpath('//d:href');
		foreach ($hrefs as $href) {
			$hrefParts = \explode("/", $href[0]);
			if (\in_array($user, $hrefParts)) {
				$entry = \urldecode(\end($hrefParts));
				\array_push($responseResources, $entry);
			} else {
				throw new Error("Expected user: $hrefParts[5] but found: $user");
			}
		}
		return $responseResources;
	}

	/**
	 * parses a PROPFIND response from $this->response into xml
	 * and returns found search results if found else returns false
	 *
	 * @param string $entryNameToSearch
	 * @param string|null $user
	 *
	 * @return string|array|boolean
	 * string if $entryNameToSearch is given and is found
	 * array if $entryNameToSearch is not given
	 * boolean false if $entryNameToSearch is given and is not found
	 */
	public function findEntryFromPropfindResponse(
		$entryNameToSearch = null, $user = null
	) {
		//if we are using that step the second time in a scenario e.g. 'But ... should not'
		//then don't parse the result again, because the result in a ResponseInterface
		if (empty($this->responseXml)) {
			$this->setResponseXml(
				HttpRequestHelper::parseResponseAsXml($this->response)
			);
		}
		if ($user === null) {
			$user = $this->getCurrentUser();
		}
		// trim any leading "/" passed by the caller, we can just match the "raw" name
		$trimmedEntryNameToSearch = \trim($entryNameToSearch, "/");

		// topWebDavPath should be something like /remote.php/webdav/ or
		// /remote.php/dav/files/alice/
		$topWebDavPath = "/" . $this->getFullDavFilesPath($user) . "/";
		$multistatusResults = $this->responseXml["value"];
		$results = [];
		if ($multistatusResults !== null) {
			foreach ($multistatusResults as $multistatusResult) {
				$entryPath = $multistatusResult['value'][0]['value'];
				$entryName = \str_replace($topWebDavPath, "", $entryPath);
				$entryName = \rawurldecode($entryName);
				if ($trimmedEntryNameToSearch === $entryName) {
					return $multistatusResult;
				}
				\array_push($results, $entryName);
			}
		}
		if ($entryNameToSearch === null) {
			return $results;
		}
		return false;
	}

	/**
	 * prevent creating two uploads with the same "stime" which is
	 * based on seconds, this prevents creation of uploads with same etag
	 *
	 * @return void
	 */
	public function pauseUploadDelete() {
		$time = \time();
		if ($this->lastUploadDeleteTime !== null && $time - $this->lastUploadDeleteTime < 1) {
			\sleep(1);
		}
	}

	/**
	 * reset settings if they were set in the scenario
	 *
	 * @AfterScenario
	 *
	 * @return void
	 */
	public function resetOldSettingsAfterScenario() {
		if ($this->oldAsyncSetting === "") {
			SetupHelper::runOcc(['config:system:delete', 'dav.enable.async']);
		} elseif ($this->oldAsyncSetting !== null) {
			SetupHelper::runOcc(
				[
					'config:system:set',
					'dav.enable.async',
					'--type',
					'boolean',
					'--value',
					$this->oldAsyncSetting
				]
			);
		}
		if ($this->oldDavSlowdownSetting === "") {
			SetupHelper::runOcc(['config:system:delete', 'dav.slowdown']);
		} elseif ($this->oldDavSlowdownSetting !== null) {
			SetupHelper::runOcc(
				[
					'config:system:set',
					'dav.slowdown',
					'--value',
					$this->oldDavSlowdownSetting
				]
			);
		}
	}
}
