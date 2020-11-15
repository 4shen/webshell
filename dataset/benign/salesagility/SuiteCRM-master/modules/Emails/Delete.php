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




$focus = BeanFactory::newBean('Emails');

if (!isset($_REQUEST['record'])) {
    sugar_die("A record number must be specified to delete the email.");
}
$focus->retrieve($_REQUEST['record']);
$email_type = $focus->type;
if (!$focus->ACLAccess('Delete')) {
    ACLController::displayNoAccess(true);
    sugar_cleanup(true);
}
$focus->mark_deleted($_REQUEST['record']);

// make sure assigned_user_id is set - during testing this isn't always set
if (!isset($_REQUEST['assigned_user_id'])) {
    $_REQUEST['assigned_user_id'] = '';
}

if ($email_type == 'archived') {
    global $current_user;
    $loc = 'Location: index.php?module=Emails';
} else {
    $loc = 'Location: index.php?module='.$_REQUEST['return_module'].'&action='.$_REQUEST['return_action'].'&record='.$_REQUEST['return_id'].'&type='.$_REQUEST['type'].'&assigned_user_id='.$_REQUEST['assigned_user_id'];
}

header($loc);
