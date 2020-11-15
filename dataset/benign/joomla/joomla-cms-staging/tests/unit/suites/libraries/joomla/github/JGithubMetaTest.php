<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JGithubMeta.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 * @since       3.2.0
 */
class JGithubMetaTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  3.2.0
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  3.2.0
	 */
	protected $client;

	/**
	 * @var    JHttpResponse  Mock response object.
	 * @since  3.2.0
	 */
	protected $response;

	/**
	 * @var    JGithubMeta  Object under test.
	 * @since  3.2.0
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  3.2.0
	 */
	protected $sampleString = '{"hooks":["127.0.0.1/32","192.168.1.1/32"],"git":["127.0.0.1/32"]}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  3.2.0
	 */
	protected $errorString = '{"message": "Generic Error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client = $this->getMockBuilder('JGithubHttp')->setMethods(array('get', 'post', 'delete', 'patch', 'put'))->getMock();
		$this->response = $this->getMockBuilder('JHttpResponse')->getMock();

		$this->object = new JGithubMeta($this->options, $this->client);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->options, $this->client, $this->response, $this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getMeta method
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function testGetMeta()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$decodedResponse = array(
			'hooks' => array('127.0.0.1', '192.168.1.1'),
			'git' => array('127.0.0.1')
		);

		$this->client->expects($this->once())
			->method('get')
			->with('/meta')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getMeta(),
			$this->equalTo($decodedResponse)
		);
	}

	/**
	 * Tests the getMeta method - failure
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 *
	 * @expectedException  DomainException
	 */
	public function testGetMetaFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/meta')
			->will($this->returnValue($this->response));

		$this->object->getMeta();
	}
}
