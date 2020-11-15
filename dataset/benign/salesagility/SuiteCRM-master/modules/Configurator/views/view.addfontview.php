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

require_once('include/MVC/View/SugarView.php');
require_once('include/Sugarpdf/FontManager.php');
class ConfiguratorViewAddFontView extends SugarView
{
   
    /**
     * Constructor
     */
    public function AddFontView()
    {
        parent::__construct();
    }
    /**
     * display the form
     */
    public function display()
    {
        global $mod_strings, $app_list_strings, $app_strings, $current_user;
        if (!is_admin($current_user)) {
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
        }
        $this->ss->assign(
            "MODULE_TITLE",
            getClassicModuleTitle(
                $mod_strings['LBL_MODULE_ID'],
                array($mod_strings['LBL_ADDFONT_TITLE']),
                true
                )
            );
        if (!empty($_REQUEST['error'])) {
            $this->ss->assign("error", $_REQUEST['error']);
        }
        $this->ss->assign("MOD", $mod_strings);
        $this->ss->assign("APP", $app_strings);
        if (isset($_REQUEST['return_action'])) {
            $this->ss->assign("RETURN_ACTION", $_REQUEST['return_action']);
        } else {
            $this->ss->assign("RETURN_ACTION", 'FontManager');
        }
        $this->ss->assign("STYLE_LIST", array(
                "regular"=>$mod_strings["LBL_FONT_REGULAR"],
                "italic"=>$mod_strings["LBL_FONT_ITALIC"],
                "bold"=>$mod_strings["LBL_FONT_BOLD"],
                "boldItalic"=>$mod_strings["LBL_FONT_BOLDITALIC"]
         ));
        $this->ss->assign("ENCODING_TABLE", array_combine(explode(",", PDF_ENCODING_TABLE_LIST), explode(",", PDF_ENCODING_TABLE_LABEL_LIST)));
        
        //display
        $this->ss->display('modules/Configurator/tpls/addFontView.tpl');
    }
}
