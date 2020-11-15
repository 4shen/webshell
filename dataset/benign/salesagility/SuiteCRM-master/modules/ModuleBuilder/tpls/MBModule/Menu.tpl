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

 if (!defined('sugarEntry') || !sugarEntry) {literal}{
    die('Not A Valid Entry Point');
}{/literal}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('{$moduleName}', 'edit', true)){literal}{{/literal}
    $module_menu[]=array('index.php?module={$moduleName}&action=EditView&return_module={$moduleName}&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', '{$moduleName}');
{literal}}{/literal}
{if $showvCard}if(ACLController::checkAccess('{$moduleName}', 'edit', true)){literal}{{/literal}
    $module_menu[]=array('index.php?module={$moduleName}&action=ImportVCard', $mod_strings['LNK_IMPORT_VCARD'], 'Create_Contact_Vcard', '{$moduleName}');
{literal}}{/literal}{/if}
if(ACLController::checkAccess('{$moduleName}', 'list', true)){literal}{{/literal}
    $module_menu[]=array('index.php?module={$moduleName}&action=index&return_module={$moduleName}&return_action=DetailView', $mod_strings['LNK_LIST'],'View', '{$moduleName}');
{literal}}{/literal}
{if $showimport}if(ACLController::checkAccess('{$moduleName}', 'import', true)){literal}{{/literal}
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module={$moduleName}&return_module={$moduleName}&return_action=index', $app_strings['LBL_IMPORT'], 'Import', '{$moduleName}');
{literal}}{/literal}{/if}
