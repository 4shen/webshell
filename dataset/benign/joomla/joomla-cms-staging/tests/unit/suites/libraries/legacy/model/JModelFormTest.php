<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JModelForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       3.1.4
 */
class JModelFormTest extends TestCase
{
	/**
	 * @var    JModelForm
	 * @since  3.1.4
	 */
	public $object;

	/**
	 * Setup each test.
	 *
	 * @since   3.1.4
	 *
	 * @return  void
	 */
	public function setUp()
	{
		// Create mock of abstract class JModelForm to test concrete methods in there
		$this->object = $this->getMockForAbstractClass('JModelForm');
		TestReflection::setValue('JEventDispatcher', 'instance', $this->getMockDispatcher());
		TestReflection::setValue('JPluginHelper', 'plugins', array());
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   1.7.0
	 */
	protected function tearDown()
	{
		// Reset the dispatcher instance.
		TestReflection::setValue('JEventDispatcher', 'instance', null);
		TestReflection::setValue('JPluginHelper', 'plugins', null);

		parent::tearDown();
	}

	/**
	 * Test JModelForm::validate
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox validate() returns false on JForm exception
	 */
	public function testValidateReturnsFalseOnJformException()
	{
		$formMock = $this->getMockBuilder('JForm')
			->disableOriginalConstructor()
			->getMock();

		$formMock->method('validate')
			->willReturn(new Exception('Exception Message'));

		$this->assertFalse($this->object->validate($formMock, array()));

		return $this->object;
	}

	/**
	 * Test JModelForm::validate
	 *
	 * @param   JModelForm  $object  object used
	 *
	 * @since   3.4
	 *
	 * @depends testValidateReturnsFalseOnJformException
	 *
	 * @return  void
	 *
	 * @testdox validate() sets error message on exception
	 */
	public function testValidateSetsErrorMessageOnException($object)
	{
		$this->assertEquals('Exception Message', $object->getError());
	}

	/**
	 * Test JModelForm::validate
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox validate() returns false when JForm returns false
	 */
	public function testValidateReturnsFalseOnJformFalse()
	{
		$formMock = $this->getMockBuilder('JForm')
			->disableOriginalConstructor()
			->getMock();

		$formMock->method('validate')
			->willReturn(false);

		$formMock->method('getErrors')
			->willReturn(array("Error Message 1", "Error Message 2"));

		$this->assertFalse($this->object->validate($formMock, array()));

		return $this->object;
	}

	/**
	 * Test JModelForm::validate
	 *
	 * @param   JModelForm  $object  object used
	 *
	 * @since   3.4
	 *
	 * @depends testValidateReturnsFalseOnJformFalse
	 *
	 * @return  void
	 *
	 * @testdox validate() sets error messages when JForm returns false
	 */
	public function testValidateSetsErrorMessageOnJformFalse($object)
	{
		$this->assertEquals(array('Error Message 1', 'Error Message 2'), $object->getErrors());
	}

	/**
	 * Test JModelForm::validate
	 *
	 * @param   array  $data      data before validation
	 * @param   array  $expected  data after validation
	 *
	 * @since   3.4
	 *
	 * @dataProvider validationDataProvider
	 *
	 * @return  void
	 *
	 * @testdox validate() modifies tags array
	 */
	public function testValidateModifiesTagsArray($data, $expected)
	{
		$formMock = $this->getMockBuilder('JForm')
			->disableOriginalConstructor()
			->getMock();

		$formMock->method('validate')
			->willReturn(true);

		$formMock->method('filter')
			->will($this->returnArgument(0));

		$this->assertEquals($this->object->validate($formMock, $data), $expected);
	}

	/**
	 * Test JModelForm::validate
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox validate() passes data and group to JForm
	 */
	public function testValidatePassesDataAndGroupToJform()
	{
		$data = array("a" => "b");
		$group = "groupy";

		$formMock = $this->getMockBuilder('JForm')
			->disableOriginalConstructor()
			->getMock();

		$formMock->expects($this->once())
			->method('validate')
			->with(
				$data,
				$group
			)
			->willReturn(true);

		$formMock->expects($this->once())
			->method('filter')
			->with(
				$data
			)
			->will($this->returnArgument(0));

		$this->object->validate($formMock, $data, $group);
	}

	/**
	 * validation data
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function validationDataProvider()
	{
		return array(
			array(
				array("tags" => "1"),
				array("tags" => "1")
			),
			array(
				array("metadata" => array("tags" => "1")),
				array("metadata" => array("tags" => "1"), "tags" => "1")
			),
			array(
				array("tags" => "1", "metadata" => array("tags" => "2")),
				array("tags" => "1", "metadata" => array("tags" => "2")),
			),
		);
	}
}
