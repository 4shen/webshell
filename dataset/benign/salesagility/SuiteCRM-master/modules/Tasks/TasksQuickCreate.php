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

 
require_once('include/EditView/QuickCreate.php');



class TasksQuickCreate extends QuickCreate
{
    public $javascript;
    
    public function process()
    {
        global $current_user, $timedate, $app_list_strings, $current_language, $mod_strings;
        $mod_strings = return_module_language($current_language, 'Tasks');
        
        parent::process();

        $this->ss->assign("PRIORITY_OPTIONS", get_select_options_with_id($app_list_strings['task_priority_dom'], $app_list_strings['task_priority_default']));
        $this->ss->assign("STATUS_OPTIONS", get_select_options_with_id($app_list_strings['task_status_dom'], $app_list_strings['task_status_default']));
        $this->ss->assign("TIME_FORMAT", '('. $timedate->get_user_time_format().')');
        
        $focus = BeanFactory::newBean('Tasks');
        $time_start_hour = (int)substr($focus->time_start, 0, 2);
        $time_start_minutes = substr($focus->time_start, 3, 5);
        if ($time_start_minutes > 45) {
            $time_start_hour += 1;
        }

        $time_pref = $timedate->get_time_format();
        if (strpos($time_pref, 'a')) {
            if (!isset($focus->meridiem_am_values)) {
                $focus->meridiem_am_values = array('am'=>'am', 'pm'=>'pm');
            }
            $this->ss->assign("TIME_MERIDIEM", get_select_options_with_id($focus->meridiem_am_values, $time_start_hour < 12 ? 'am' : 'pm'));
        } else {
            if (strpos($time_pref, 'A')) {
                if (!isset($focus->meridiem_AM_values)) {
                    $focus->meridiem_AM_values = array('AM'=>'AM', 'PM'=>'PM');
                }
                $this->ss->assign("TIME_MERIDIEM", get_select_options_with_id($focus->meridiem_AM_values, $time_start_hour < 12 ? 'AM' : 'PM'));
            }
        } //if-else

        $this->ss->assign("USER_DATEFORMAT", '('. $timedate->get_user_date_format().')');
        $this->ss->assign("CALENDAR_DATEFORMAT", $timedate->get_cal_date_format());

        
        if ($this->viaAJAX) { // override for ajax call
            $this->ss->assign('saveOnclick', "onclick='if(check_form(\"tasksQuickCreate\")) return SUGAR.subpanelUtils.inlineSave(this.form.id, \"activities\"); else return false;'");
            $this->ss->assign('cancelOnclick', "onclick='return SUGAR.subpanelUtils.cancelCreate(\"subpanel_activities\")';");
        }
        
        $this->ss->assign('viaAJAX', $this->viaAJAX);

        $this->javascript = new javascript();
        $this->javascript->setFormName('tasksQuickCreate');
        
        $focus = BeanFactory::newBean('Tasks');
        $this->javascript->setSugarBean($focus);
        $this->javascript->addAllFields('');

        $this->ss->assign('additionalScripts', $this->javascript->getScript(false));
    }
}
