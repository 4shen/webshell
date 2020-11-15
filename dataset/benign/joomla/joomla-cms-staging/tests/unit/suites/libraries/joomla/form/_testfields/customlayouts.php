<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldCustomlayouts extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var        string
	 */
	protected $type = 'Customlayouts';

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutPaths()
	{
		return array_merge(
			array(__DIR__),
			parent::getLayoutPaths()
		);
	}
}
