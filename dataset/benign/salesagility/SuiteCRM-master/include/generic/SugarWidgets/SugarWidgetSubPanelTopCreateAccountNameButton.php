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

 




class SugarWidgetSubPanelTopCreateAccountNameButton extends SugarWidgetSubPanelTopButtonQuickCreate
{
    public function getWidgetId($buttonSuffix = true)
    {
        return parent::getWidgetId();
    }

    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        global $app_strings;
        global $currentModule;

        $title = $app_strings['LBL_NEW_BUTTON_TITLE'];
        //$accesskey = $app_strings['LBL_NEW_BUTTON_KEY'];
        $value = $app_strings['LBL_NEW_BUTTON_LABEL'];
        $this->module = 'Contacts';
        if (ACLController::moduleSupportsACL($defines['module'])  && !ACLController::checkAccess($defines['module'], 'edit', true)) {
            $button = "<input title='$title'class='button' type='button' name='button' value='  $value  ' disabled/>\n";
            return $button;
        }
        
        $additionalFormFields = array();
        if (isset($defines['focus']->billing_address_street)) {
            $additionalFormFields['primary_address_street'] = $defines['focus']->billing_address_street;
        }
        if (isset($defines['focus']->billing_address_city)) {
            $additionalFormFields['primary_address_city'] = $defines['focus']->billing_address_city;
        }
        if (isset($defines['focus']->billing_address_state)) {
            $additionalFormFields['primary_address_state'] = $defines['focus']->billing_address_state;
        }
        if (isset($defines['focus']->billing_address_country)) {
            $additionalFormFields['primary_address_country'] = $defines['focus']->billing_address_country;
        }
        if (isset($defines['focus']->billing_address_postalcode)) {
            $additionalFormFields['primary_address_postalcode'] = $defines['focus']->billing_address_postalcode;
        }
        if (isset($defines['focus']->phone_office)) {
            $additionalFormFields['phone_work'] = $defines['focus']->phone_office;
        }
        
        
        $button = $this->_get_form($defines, $additionalFormFields);
        $button .= "<input title='$title' class='button' type='submit' name='{$this->getWidgetId()}' id='{$this->getWidgetId()}' value='  $value  '/>\n";
        $button .= "</form>";
        return $button;
    }
}
