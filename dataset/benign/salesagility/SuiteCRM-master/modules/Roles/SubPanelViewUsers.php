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







global $app_strings;
//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'Users');

global $currentModule;

global $theme;
global $focus;
global $action;




// focus_list is the means of passing data to a SubPanelView.
global $focus_list;

$button  = "<form action='index.php' method='post' name='UsersForm' id='UsersForm'>\n";
$button .= "<input type='hidden' name='module' value='Users'>\n";
if ($currentModule == 'Roles') {
    $button .= "<input type='hidden' name='record' value='$focus->id'>\n";
}
$button .= "<input type='hidden' name='return_module' value='".$currentModule."'>\n";
$button .= "<input type='hidden' name='return_action' value='".$action."'>\n";
$button .= "<input type='hidden' name='return_id' value='".$focus->id."'>\n";
$button .= "<input type='hidden' name='action'>\n";
$button .= "<input type='hidden' name='report_module'>\n";
$button .= "<input type='hidden' name='id'>\n";

if ($currentModule == 'Roles') {
    $button .= "<input title='".$app_strings['LBL_SELECT_BUTTON_TITLE']." ' type='button' class='button' value='  ".$app_strings['LBL_SELECT_BUTTON_LABEL']."  ' name='button' LANGUAGE=javascript onclick='window.open(\"index.php?module=Users&action=PopupUsers&html=Popup_Users_picker&form=UsersForm&record=".$focus->id."&form_submit=true&query=true\",\"new\",\"width=600,height=400,resizable=1,scrollbars=1\");'>\n";
}

$button .= "</form>\n";



$ListView = new ListView();
$ListView->initNewXTemplate('modules/Roles/SubPanelViewUsers.html', $current_module_strings);

$ListView->xTemplateAssign("ROLE_RECORD", $focus->id);
$ListView->xTemplateAssign("EDIT_INLINE_PNG", SugarThemeRegistry::current()->getImage('edit_inline', 'align="absmiddle" border="0"', null, null, '.gif', $app_strings['LNK_EDIT']));
$ListView->xTemplateAssign("REMOVE_INLINE_PNG", SugarThemeRegistry::current()->getImage('delete_inline', 'align="absmiddle" border="0"', null, null, '.gif', $app_strings['LNK_REMOVE']));

$ListView->xTemplateAssign("RETURN_URL", "&return_module=".$currentModule."&return_action=DetailView&return_id=".$focus->id);
$ListView->setHeaderTitle($current_module_strings['LBL_MODULE_NAME']);
$ListView->setQuery("", "", "user_name", "USER");
$ListView->setHeaderText($button);
$ListView->processListView($focus_list, "main", "USER");
