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


class AdministrationViewGlobalsearchsettings extends SugarView
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        global $mod_strings;

        return array(
           "<a href='index.php?module=Administration&action=index'>".translate('LBL_MODULE_NAME', 'Administration')."</a>",
           $mod_strings['LBL_GLOBAL_SEARCH_SETTINGS']
           );
    }

    /**
     * @see SugarView::_getModuleTab()
     */
    protected function _getModuleTab()
    {
        return 'Administration';
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        require_once('modules/Home/UnifiedSearchAdvanced.php');
        $usa = new UnifiedSearchAdvanced();
        global $mod_strings, $app_strings, $app_list_strings;

        $sugar_smarty = new Sugar_Smarty();
        $sugar_smarty->assign('APP', $app_strings);
        $sugar_smarty->assign('MOD', $mod_strings);
        $sugar_smarty->assign('moduleTitle', $this->getModuleTitle(false));

        $modules = $usa->retrieveEnabledAndDisabledModules();

        $sugar_smarty->assign('enabled_modules', json_encode($modules['enabled']));
        $sugar_smarty->assign('disabled_modules', json_encode($modules['disabled']));
        $tpl = 'modules/Administration/templates/GlobalSearchSettings.tpl';
        if (file_exists('custom/' . $tpl)) {
            $tpl = 'custom/' . $tpl;
        }
        echo $sugar_smarty->fetch($tpl);
    }
    /*
        protected function isFTSConnectionValid()
        {
            require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
            $searchEngine = SugarSearchEngineFactory::getInstance();
            $result = $searchEngine->getServerStatus();
            if($result['valid'])
                return TRUE;
            else
                return FALSE;
        }
    	*/
}
