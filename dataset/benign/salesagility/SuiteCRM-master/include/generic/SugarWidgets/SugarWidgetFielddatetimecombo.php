<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */




class SugarWidgetFieldDateTimecombo extends SugarWidgetFieldDateTime
{
    public $reporter;
    public $assigned_user=null;

    public function __construct(&$layout_manager)
    {
        parent::__construct($layout_manager);
        $this->reporter = $this->layout_manager->getAttribute('reporter');
    }

    /**
     * @deprecated deprecated since version 7.6, PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code, use __construct instead
     */
    public function SugarWidgetFieldDateTimecombo(&$layout_manager)
    {
        $deprecatedMessage = 'PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code';
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->deprecated($deprecatedMessage);
        } else {
            trigger_error($deprecatedMessage, E_USER_DEPRECATED);
        }
        self::__construct($layout_manager);
    }

    //TODO:now for date time field , we just search from date start to date end. The time is from 00:00:00 to 23:59:59
    //If there is requirement, we can modify report.js::addFilterInputDatetimesBetween and this function
    public function queryFilterBetween_Datetimes(& $layout_def)
    {
        global $timedate;
        if ($this->getAssignedUser()) {
            $begin = $timedate->handle_offset($layout_def['input_name0'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
            $end = $timedate->handle_offset($layout_def['input_name2'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
        } else {
            $begin = $layout_def['input_name0'];
            $end = $layout_def['input_name1'];
        }
        return "(".$this->_get_column_select($layout_def).">=".$this->reporter->db->convert($this->reporter->db->quoted($begin), "datetime").
            " AND\n ".$this->_get_column_select($layout_def)."<=".$this->reporter->db->convert($this->reporter->db->quoted($end), "datetime").
            ")\n";
    }
}
