<?php
/**
 * @author Lukas Reschke
 * @copyright Copyright (c) 2014 Lukas Reschke lukas@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Tests\Settings\Controller;

use OC\Settings\Application;

/**
 * @package Tests\Settings\Controller
 */
class MailSettingsControllerTest extends \Test\TestCase {
	private $container;

	protected function setUp(): void {
		parent::setUp();

		$app = new Application();
		$this->container = $app->getContainer();
		$this->container['Config'] = $this->getMockBuilder('\OCP\IConfig')
			->disableOriginalConstructor()->getMock();
		$this->container['L10N'] = $this->getMockBuilder('\OCP\IL10N')
			->disableOriginalConstructor()->getMock();
		$this->container['AppName'] = 'settings';
		$this->container['UserSession'] = $this->getMockBuilder('\OC\User\Session')
			->disableOriginalConstructor()->getMock();
		$this->container['MailMessage'] = $this->getMockBuilder('\OCP\Mail\IMessage')
			->disableOriginalConstructor()->getMock();
		$this->container['Mailer'] = $this->getMockBuilder('\OC\Mail\Mailer')
			->setMethods(['send', 'validateMailAddress'])
			->disableOriginalConstructor()->getMock();
		$this->container['Defaults'] = $this->getMockBuilder('\OC_Defaults')
			->disableOriginalConstructor()->getMock();
		$this->container['DefaultMailAddress'] = 'no-reply@owncloud.com';
	}

	public function testSetInvalidMail() {
		$this->container['L10N']
			->expects($this->exactly(2))
			->method('t')
			->will($this->returnValue('Invalid email address'));

		$this->container['Mailer']
			->expects($this->exactly(2))
			->method('validateMailAddress')
			->with('@@owncloud.com')
			->will($this->returnValue(false));

		// With authentication
		$response = $this->container['MailSettingsController']->setMailSettings(
			'owncloud.com',
			'@',
			'smtp',
			'ssl',
			'mx.owncloud.org',
			'NTLM',
			1,
			'25'
		);
		$expectedResponse = ['data' => ['message' =>'Invalid email address'], 'status' => 'error'];
		$this->assertSame($expectedResponse, $response);

		// Without authentication (testing the deletion of the stored password)
		$response = $this->container['MailSettingsController']->setMailSettings(
			'owncloud.com',
			'@',
			'smtp',
			'ssl',
			'mx.owncloud.org',
			'NTLM',
			0,
			'25'
		);
		$expectedResponse = ['data' => ['message' =>'Invalid email address'], 'status' => 'error'];
		$this->assertSame($expectedResponse, $response);
	}

	public function testSetMailSettings() {
		$this->container['L10N']
			->expects($this->exactly(2))
			->method('t')
			->will($this->returnValue('Saved'));

		/**
		 * FIXME: Use the following block once Jenkins uses PHPUnit >= 4.1
		 */
		/*
		$this->container['Config']
			->expects($this->exactly(15))
			->method('setSystemValue')
			->withConsecutive(
				array($this->equalTo('mail_domain'), $this->equalTo('owncloud.com')),
				array($this->equalTo('mail_from_address'), $this->equalTo('demo')),
				array($this->equalTo('mail_smtpmode'), $this->equalTo('smtp')),
				array($this->equalTo('mail_smtpsecure'), $this->equalTo('ssl')),
				array($this->equalTo('mail_smtphost'), $this->equalTo('mx.owncloud.org')),
				array($this->equalTo('mail_smtpauthtype'), $this->equalTo('NTLM')),
				array($this->equalTo('mail_smtpauth'), $this->equalTo(1)),
				array($this->equalTo('mail_smtpport'), $this->equalTo('25')),
				array($this->equalTo('mail_domain'), $this->equalTo('owncloud.com')),
				array($this->equalTo('mail_from_address'), $this->equalTo('demo@owncloud.com')),
				array($this->equalTo('mail_smtpmode'), $this->equalTo('smtp')),
				array($this->equalTo('mail_smtpsecure'), $this->equalTo('ssl')),
				array($this->equalTo('mail_smtphost'), $this->equalTo('mx.owncloud.org')),
				array($this->equalTo('mail_smtpauthtype'), $this->equalTo('NTLM')),
				array($this->equalTo('mail_smtpport'), $this->equalTo('25'))
			);
		 */

		$this->container['Mailer']
			->expects($this->exactly(2))
			->method('validateMailAddress')
			->with('demo@owncloud.com')
			->will($this->returnValue(true));

		/** @var \PHPUnit\Framework\MockObject\MockObject $config */
		$config = $this->container['Config'];
		$config->expects($this->exactly(2))
			->method('setSystemValues');
		/**
		 * FIXME: Use the following block once Jenkins uses PHPUnit >= 4.1
			->withConsecutive(
				[[
					'mail_domain' => 'owncloud.com',
					'mail_from_address' => 'demo',
					'mail_smtpmode' => 'smtp',
					'mail_smtpsecure' => 'ssl',
					'mail_smtphost' => 'mx.owncloud.org',
					'mail_smtpauthtype' => 'NTLM',
					'mail_smtpauth' => 1,
					'mail_smtpport' => '25',
				]],
				[[
					'mail_domain' => 'owncloud.com',
					'mail_from_address' => 'demo',
					'mail_smtpmode' => 'smtp',
					'mail_smtpsecure' => 'ssl',
					'mail_smtphost' => 'mx.owncloud.org',
					'mail_smtpauthtype' => 'NTLM',
					'mail_smtpauth' => null,
					'mail_smtpport' => '25',
					'mail_smtpname' => null,
					'mail_smtppassword' => null,
				]]
			);
		 */

		// With authentication
		$response = $this->container['MailSettingsController']->setMailSettings(
			'owncloud.com',
			'demo',
			'smtp',
			'ssl',
			'mx.owncloud.org',
			'NTLM',
			1,
			'25'
		);
		$expectedResponse = ['data' => ['message' =>'Saved'], 'status' => 'success'];
		$this->assertSame($expectedResponse, $response);

		// Without authentication (testing the deletion of the stored password)
		$response = $this->container['MailSettingsController']->setMailSettings(
			'owncloud.com',
			'demo',
			'smtp',
			'ssl',
			'mx.owncloud.org',
			'NTLM',
			0,
			'25'
		);
		$expectedResponse = ['data' => ['message' =>'Saved'], 'status' => 'success'];
		$this->assertSame($expectedResponse, $response);
	}

	public function testStoreCredentials() {
		$this->container['L10N']
			->expects($this->once())
			->method('t')
			->will($this->returnValue('Saved'));

		$this->container['Config']
			->expects($this->once())
			->method('setSystemValues')
			->with([
				'mail_smtpname' => 'UsernameToStore',
				'mail_smtppassword' => 'PasswordToStore',
			]);

		$response = $this->container['MailSettingsController']->storeCredentials('UsernameToStore', 'PasswordToStore');
		$expectedResponse = ['data' => ['message' =>'Saved'], 'status' => 'success'];

		$this->assertSame($expectedResponse, $response);
	}

	public function testSendTestMail() {
		$user = $this->getMockBuilder('\OC\User\User')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->any())
			->method('getUID')
			->will($this->returnValue('Werner'));
		$user->expects($this->any())
			->method('getDisplayName')
			->will($this->returnValue('Werner Brösel'));

		$this->container['L10N']
			->expects($this->any())
			->method('t')
			->will(
				$this->returnValueMap(
					[
						['You need to set your user email before being able to send test emails.', [],
							'You need to set your user email before being able to send test emails.'],
						['A problem occurred while sending the e-mail. Please revisit your settings.', [],
							'A problem occurred while sending the e-mail. Please revisit your settings.'],
						['Email sent', [], 'Email sent'],
						['test email settings', [], 'test email settings'],
						['If you received this email, the settings seem to be correct.', [],
							'If you received this email, the settings seem to be correct.']
					]
				));
		$this->container['UserSession']
			->expects($this->any())
			->method('getUser')
			->will($this->returnValue($user));

		// Ensure that it fails when no mail address has been specified
		$response = $this->container['MailSettingsController']->sendTestMail();
		$expectedResponse = ['data' => ['message' =>'You need to set your user email before being able to send test emails.'], 'status' => 'error'];
		$this->assertSame($expectedResponse, $response);

		$user->expects($this->any())
			->method('getEMailAddress')
			->will($this->returnValue('mail@example.invalid'));
		$response = $this->container['MailSettingsController']->sendTestMail();
		$expectedResponse = ['data' => ['message' =>'Email sent'], 'status' => 'success'];
		$this->assertSame($expectedResponse, $response);
	}
}
