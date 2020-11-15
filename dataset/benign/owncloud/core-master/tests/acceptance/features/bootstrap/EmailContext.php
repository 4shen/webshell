<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018, ownCloud GmbH
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
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use TestHelpers\EmailHelper;

require_once 'bootstrap.php';

/**
 * context file for email related steps.
 */
class EmailContext implements Context {
	private $localMailhogUrl = null;

	/**
	 *
	 * @var FeatureContext
	 */
	private $featureContext;

	/**
	 * @return string
	 */
	public function getLocalMailhogUrl() {
		return $this->localMailhogUrl;
	}

	/**
	 * @param string $address
	 * @param PyStringNode $content
	 * @param string|null $sender
	 *
	 * @return void
	 * @throws Exception
	 */
	public function assertThatEmailContains($address, PyStringNode $content, $sender = null) {
		$expectedContent = \str_replace("\r\n", "\n", $content->getRaw());
		$expectedContent = $this->featureContext->substituteInLineCodes(
			$expectedContent, $sender
		);
		$emailBody = EmailHelper::getBodyOfLastEmail($this->localMailhogUrl, $address);
		Assert::assertStringContainsString(
			$expectedContent,
			$emailBody,
			"The email address {$address} should have received an email with the body containing {$expectedContent}
			but the received email is {$emailBody}"
		);
	}

	/**
	 * @Then the email address of user :user should have received an email from user :sender with the body containing
	 *
	 * @param string $user
	 * @param string $sender
	 * @param PyStringNode $content
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theUserShouldHaveReceivedAnEmailFromUserWithBodyContaining($user, $sender, PyStringNode $content) {
		$address = $this->featureContext->getEmailAddressForUser($user);
		$this->assertThatEmailContains($address, $content, $sender);
	}

	/**
	 * @Then /^the email address "(?P<address>[^"]*)" should have received an email ?(?:from user "(?P<user>[^"]*)")? with the body containing$/
	 *
	 * @param string $address
	 * @param PyStringNode $content
	 * @param string|null $user
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function emailAddressShouldHaveReceivedAnEmailWithBodyContaining($address, $content, $user = null) {
		$user = $this->featureContext->getActualUsername($user);
		$this->assertThatEmailContains($address, $content, $user);
	}

	/**
	 * @Then the email address of user :user should have received an email with the body containing
	 *
	 * @param string $user
	 * @param PyStringNode $content
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function theUserShouldHaveReceivedAnEmailWithBodyContaining($user, PyStringNode $content) {
		$address = $this->featureContext->getEmailAddressForUser($user);
		$this->assertThatEmailContains($address, $content);
	}

	/**
	 * @Then the reset email to user :user should be from :senderAddress
	 *
	 * @param string $user receiver
	 * @param string $senderAddress
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theResetEmailSenderEmailAddressShouldBe($user, $senderAddress) {
		$user = $this->featureContext->getActualUsername($user);
		$receiverAddress = $this->featureContext->getEmailAddressForUser($user);
		$actualSenderAddress = EmailHelper::getSenderOfEmail($this->localMailhogUrl, $receiverAddress);
		Assert::assertStringContainsString(
			$senderAddress,
			$actualSenderAddress,
			"The sender address is expected to be {$senderAddress} but the actual sender is {$actualSenderAddress}"
		);
	}

	/**
	 * @Then the email address :address should not have received an email
	 *
	 * @param string $address
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function assertThatEmailDoesntExistWithTheAddress($address) {
		Assert::assertFalse(
			EmailHelper::emailReceived(
				EmailHelper::getLocalMailhogUrl(), $address
			),
			"Email exists with email address: {$address} but was not expected to be."
		);
	}

	/**
	 * @BeforeScenario @mailhog
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function setUpScenario(BeforeScenarioScope $scope) {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		$this->localMailhogUrl = EmailHelper::getLocalMailhogUrl();
		$this->clearMailHogMessages();
	}

	/**
	 *
	 * @return void
	 */
	protected function clearMailHogMessages() {
		try {
			EmailHelper::deleteAllMessages($this->getLocalMailhogUrl());
		} catch (Exception $e) {
			echo __METHOD__ .
				" could not delete mailhog messages, is mailhog set up?\n" .
				$e->getMessage();
		}
	}
}
