<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JFormRuleOptions.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       1.7.0
 */
class JFormRuleOptionsTest extends TestCase
{
	/**
	 * Test the JFormRuleEmail::test method.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function testEmail()
	{
		$rule = new JFormRuleOptions;
		$xml = simplexml_load_string(
			'<form><field name="field1"><option value="value1">Value1</option><option value="value2">Value2</option></field></form>'
		);

		// Test fail conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Test pass conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'value1'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' value1 should pass and return true.'
		);

		$this->assertThat(
			$rule->test($xml->field[0], 'value2'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' value2 should pass and return true.'
		);
	}
}
