<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JLinkedinOAuth.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       3.2.0
 */
class JLinkedinOAuthTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  3.2.0
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  3.2.0
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  3.2.0
	 */
	protected $input;

	/**
	 * @var    JLinkedinOauth  Authentication object for the Twitter object.
	 * @since  3.2.0
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  3.2.0
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  3.2.0
	 */
	protected $errorString = '{"errorCode":401, "message": "Generic error"}';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 * @since  3.6
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/linkedin_test.php";

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->oauth = new JLinkedinOauth($this->options, $this->client, $this->input);

		$this->oauth->setToken(array('key' => $key, 'secret' => $secret));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->options, $this->input, $this->client, $this->oauth, $this->object);
		parent::tearDown();
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 3.2.0
	*/
	public function seedVerifyCredentials()
	{
		// Code, body, expected
		return array(
			array(200, $this->sampleString, true),
			array(401, $this->errorString, false)
			);
	}

	/**
	 * Tests the verifyCredentials method
	 *
	 * @param   integer  $code      The return code.
	 * @param   string   $body      The JSON string.
	 * @param   boolean  $expected  Expected return value.
	 *
	 * @return  void
	 *
	 * @dataProvider seedVerifyCredentials
	 * @since   3.2.0
	 */
	public function testVerifyCredentials($code, $body, $expected)
	{

		// Set request parameters.
		$data['format'] = 'json';

		$path = 'https://api.linkedin.com/v1/people::(~)';

		$returnData = new stdClass;
		$returnData->code = $code;
		$returnData->body = $body;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->oauth->verifyCredentials(),
			$this->equalTo($expected)
		);
	}
}
