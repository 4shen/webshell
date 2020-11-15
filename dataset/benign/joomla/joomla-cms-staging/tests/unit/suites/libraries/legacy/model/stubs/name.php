<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Stub to test JModelLegacy.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       3.1.4
 */
class NokeywordInName extends JModelLegacy
{
	/**
	 * Override parent __construct, so we bypass the Exception
	 * thrown when 'Model' is not in the class name so we can
	 * test the getName() function fully
	 *
	 * @since   3.1.4
	 */
	public function __construct()
	{
	}
}
