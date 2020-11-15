<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann artur@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Psr\Http\Message\ResponseInterface;
use TestHelpers\WebDavHelper;

require_once 'bootstrap.php';

/**
 * context containing favorites related API steps
 */
class FavoritesContext implements Context {

	/**
	 *
	 * @var FeatureContext
	 */
	private $featureContext;

	/**
	 *
	 * @var WebDavPropertiesContext
	 */
	private $webDavPropertiesContext;

	/**
	 * @param $user
	 * @param $path
	 *
	 * @return void
	 */
	public function userFavoritesElement($user, $path) {
		$response = $this->changeFavStateOfAnElement(
			$user,
			$path,
			1
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When user :user favorites element :path using the WebDAV API
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userFavoritesElementUsingWebDavApi($user, $path) {
		$this->userFavoritesElement($user, $path);
	}

	/**
	 * @Given user :user has favorited element :path
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userHasFavoritedElementUsingWebDavApi($user, $path) {
		$this->userFavoritesElement($user, $path);
		$this->featureContext->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @When the user favorites element :path using the WebDAV API
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function theUserFavoritesElement($path) {
		$this->userFavoritesElement(
			$this->featureContext->getCurrentUser(),
			$path
		);
	}

	/**
	 * @Given the user has favorited element :path
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function theUserHasFavoritedElement($path) {
		$this->userFavoritesElement(
			$this->featureContext->getCurrentUser(),
			$path
		);
		$this->featureContext->theHTTPStatusCodeShouldBe(
			207,
			"Expected response status code to be 207 (Multi-status), but not found! "
		);
	}

	/**
	 * @param $user
	 * @param $path
	 *
	 * @return void
	 */
	public function userUnfavoritesElement($user, $path) {
		$response = $this->changeFavStateOfAnElement(
			$user, $path, 0
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When user :user unfavorites element :path using the WebDAV API
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userUnfavoritesElementUsingWebDavApi($user, $path) {
		$this->userUnfavoritesElement($user, $path);
	}

	/**
	 * @Given user :user has unfavorited element :path
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function userHasUnfavoritedElementUsingWebDavApi($user, $path) {
		$this->userUnfavoritesElement($user, $path);
		$this->featureContext->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @Then /^user "([^"]*)" in folder "([^"]*)" should (not|)\s?have favorited the following elements$/
	 *
	 * @param string $user
	 * @param string $folder
	 * @param string $shouldOrNot (not|)
	 * @param TableNode $expectedElements
	 *
	 * @return void
	 */
	public function checkFavoritedElements(
		$user, $folder, $shouldOrNot, $expectedElements
	) {
		$user = $this->featureContext->getActualUsername($user);
		$this->userListsFavoriteOfFolder($user, $folder, null);
		$this->featureContext->propfindResultShouldContainEntries(
			$shouldOrNot, $expectedElements, $user
		);
	}

	/**
	 * @Then /^the user in folder "([^"]*)" should (not|)\s?have favorited the following elements$/
	 *
	 * @param string $folder
	 * @param string $shouldOrNot (not|)
	 * @param TableNode $expectedElements
	 *
	 * @return void
	 */
	public function checkFavoritedElementsForCurrentUser(
		$folder, $shouldOrNot, $expectedElements
	) {
		$this->checkFavoritedElements(
			$this->featureContext->getCurrentUser(),
			$folder, $shouldOrNot, $expectedElements
		);
	}

	/**
	 * @When /^user "([^"]*)" lists the favorites of folder "([^"]*)" and limits the result to ([\d*]) elements using the WebDAV API$/
	 *
	 * @param string $user
	 * @param string $folder
	 * @param int $limit
	 *
	 * @return void
	 */
	public function userListsFavoriteOfFolder($user, $folder, $limit = null) {
		$renamedUser = $this->featureContext->getActualUsername($user);
		$baseUrl = $this->featureContext->getBaseUrl();
		$password = $this->featureContext->getPasswordForUser($user);
		$body
			= "<?xml version='1.0' encoding='utf-8' ?>\n" .
			"	<oc:filter-files xmlns:a='DAV:' xmlns:oc='http://owncloud.org/ns' >\n" .
			"		<a:prop><oc:favorite/></a:prop>\n" .
			"		<oc:filter-rules><oc:favorite>1</oc:favorite></oc:filter-rules>\n";

		if ($limit !== null) {
			$body .= "		<oc:search>\n" .
				"			<oc:limit>$limit</oc:limit>\n" .
				"		</oc:search>\n";
		}

		$body .= "	</oc:filter-files>";
		$response = WebDavHelper::makeDavRequest(
			$baseUrl, $renamedUser, $password, "REPORT", "/", null, $body,
			$this->featureContext->getDavPathVersion()
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When /^the user lists the favorites of folder "([^"]*)" and limits the result to ([\d*]) elements using the WebDAV API$/
	 *
	 * @param string $folder
	 * @param int $limit
	 *
	 * @return void
	 */
	public function listFavoriteOfFolder($folder, $limit = null) {
		$this->userListsFavoriteOfFolder(
			$this->featureContext->getCurrentUser(), $folder, $limit
		);
	}

	/**
	 * @param $path
	 *
	 * @return void
	 */
	public function theUserUnfavoritesElement($path) {
		$this->userUnfavoritesElement(
			$this->featureContext->getCurrentUser(),
			$path
		);
	}

	/**
	 * @When the user unfavorites element :path using the WebDAV API
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function theUserUnfavoritesElementUsingWebDavApi($path) {
		$this->theUserUnfavoritesElement($path);
	}

	/**
	 * @Given the user has unfavorited element :path
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function theUserHasUnfavoritedElementUsingWebDavApi($path) {
		$this->theUserUnfavoritesElement($path);
		$this->featureContext->theHTTPStatusCodeShouldBeSuccess();
	}

	/**
	 * @Then /^as user "([^"]*)" (?:file|folder|entry) "([^"]*)" should be favorited$/
	 *
	 * @param string $user
	 * @param string $path
	 * @param integer $expectedValue 0|1
	 *
	 * @return void
	 */
	public function asUserFileOrFolderShouldBeFavorited($user, $path, $expectedValue = 1) {
		$property = "oc:favorite";
		$this->webDavPropertiesContext->asUserFolderShouldContainAPropertyWithValue(
			$user, $path, $property, $expectedValue
		);
	}

	/**
	 * @Then /^as user "([^"]*)" (?:file|folder|entry) "([^"]*)" should not be favorited$/
	 *
	 * @param string $user
	 * @param string $path
	 *
	 * @return void
	 */
	public function asUserFileShouldNotBeFavorited($user, $path) {
		$this->asUserFileOrFolderShouldBeFavorited($user, $path, 0);
	}

	/**
	 * @Then /^as the user (?:file|folder|entry) "([^"]*)" should be favorited$/
	 *
	 * @param string $path
	 * @param integer $expectedValue 0|1
	 *
	 * @return void
	 */
	public function asTheUserFileOrFolderShouldBeFavorited($path, $expectedValue = 1) {
		$this->asUserFileOrFolderShouldBeFavorited(
			$this->featureContext->getCurrentUser(), $path, $expectedValue
		);
	}

	/**
	 * @Then /^as the user (?:file|folder|entry) "([^"]*)" should not be favorited$/
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function asTheUserFileOrFolderShouldNotBeFavorited($path) {
		$this->asTheUserFileOrFolderShouldBeFavorited($path, 0);
	}

	/**
	 * Set the elements of a proppatch
	 *
	 * @param string $user
	 * @param string $path
	 * @param int $favOrUnfav 1 = favorite, 0 = unfavorite
	 *
	 * @return ResponseInterface
	 */
	public function changeFavStateOfAnElement(
		$user, $path, $favOrUnfav
	) {
		$renamedUser = $this->featureContext->getActualUsername($user);
		return WebDavHelper::proppatch(
			$this->featureContext->getBaseUrl(),
			$renamedUser,
			$this->featureContext->getPasswordForUser($user),
			$path, 'favorite', $favOrUnfav, "oc='http://owncloud.org/ns'",
			$this->featureContext->getDavPathVersion()
		);
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
		$this->webDavPropertiesContext = $environment->getContext(
			'WebDavPropertiesContext'
		);
	}
}
