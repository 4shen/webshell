<?php
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


$dictionary['Reminder'] = array(
    'table' => 'reminders',
    'audited' => false,
    'fields' => array(
        'popup' => array(
            'name' => 'popup',
            'vname' => 'LBL_POPUP',
            'type' => 'bool',
            'required' => false,
            'massupdate' => false,
            'studio' => false,
        ),
        'email' => array(
            'name' => 'email',
            'vname' => 'LBL_EMAIL',
            'type' => 'bool',
            'required' => false,
            'massupdate' => false,
            'studio' => false,
        ),
        'email_sent' => array(
            'name' => 'email_sent',
            'vname' => 'LBL_EMAIL_SENT',
            'type' => 'bool',
            'required' => false,
            'massupdate' => false,
            'studio' => false,
        ),
        'timer_popup' => array(
            'name' => 'timer_popup',
            'vname' => 'LBL_TIMER_POPUP',
            'type' => 'varchar',
            'len' => 32,
            'required' => true,
            'massupdate' => false,
            'studio' => false,
        ),
        'timer_email' => array(
            'name' => 'timer_email',
            'vname' => 'LBL_TIMER_EMAIL',
            'type' => 'varchar',
            'len' => 32,
            'required' => true,
            'massupdate' => false,
            'studio' => false,
        ),
        'related_event_module' => array(
            'name' => 'related_event_module',
            'vname' => 'LBL_RELATED_EVENT_MODULE',
            'type' => 'varchar',
            'len' => 32,
            'required' => true,
            'massupdate' => false,
            'studio' => false,
        ),
        'related_event_module_id' => array(
            'name' => 'related_event_module_id',
            'vname' => 'LBL_RELATED_EVENT_MODULE_ID',
            'type' => 'id',
            'required' => true,
            'massupdate' => false,
            'studio' => false,
        ),
        'date_willexecute' => array(
            'name' => 'date_willexecute',
            'vname' => 'LBL_DATE_WILLEXECUTE',
            'type' => 'int',
            'default' => -1,
            'len' => 60,
            'required' => false,
            'massupdate' => false,
            'studio' => false,
        ),
        'popup_viewed' => array(
            'name' => 'popup_viewed',
            'type' => 'bool',
            'default' => '0',
            'importable' => true,
            'duplicate_merge' => 'disabled',
        ),
    ),
    'indices' => array(
        array('name' => 'idx_reminder_name', 'type' => 'index', 'fields' => array('name')),
        array('name' => 'idx_reminder_deleted', 'type' => 'index', 'fields' => array('deleted')),
        array(
            'name' => 'idx_reminder_related_event_module',
            'type' => 'index',
            'fields' => array('related_event_module')
        ),
        array(
            'name' => 'idx_reminder_related_event_module_id',
            'type' => 'index',
            'fields' => array('related_event_module_id')
        ),
    )
);

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('Reminders', 'Reminder', array('basic','assignable'));
