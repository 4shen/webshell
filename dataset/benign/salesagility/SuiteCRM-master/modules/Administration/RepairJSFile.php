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

if (is_admin($current_user)) {
    global $mod_strings;

    
    //echo out warning message and msgDiv
    echo '<br>'.$mod_strings['LBL_REPAIR_JS_FILES_PROCESSING'];
    echo'<div id="msgDiv"></div>';

    //echo out script that will make an ajax call to process the files via callJSRepair.php
    echo "<script>
        var ajxProgress;
        var showMSG = 'true';
        //when called, this function will make ajax call to rebuild/repair js files
        function callJSRepair() {
        
            //begin main function that will be called
            ajaxCall = function(){
                //create success function for callback
                success = function() {              
                    //turn off loading message
                    ajaxStatus.hideStatus();
                    var targetdiv=document.getElementById('msgDiv');
                    targetdiv.innerHTML=SUGAR.language.get('Administration', 'LBL_REPAIR_JS_FILES_DONE_PROCESSING');
                }//end success
        
                        
                        
                //set loading message and create url
                ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_PROCESSING_REQUEST'));
                postData = \"module=Administration&action=callJSRepair&js_admin_repair=".$_REQUEST['type']."&root_directory=".urlencode(getcwd())."\";
                 
    
                        
                //if this is a call already in progress, then just return               
                    if(typeof ajxProgress != 'undefined'){ 
                        return;                            
                    }
                        
                //make asynchronous call to process js files
                var ajxProgress = YAHOO.util.Connect.asyncRequest('POST','index.php', {success: success, failure: success}, postData);
                        
    
            };//end ajaxCall method
    
                    
            //show loading status and make ajax call
//            ajaxStatus.hideStatus();
//            ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_PROCESSING_REQUEST'));
            window.setTimeout('ajaxCall()', 2000);
            return;
    
        }
        //call function, so it runs automatically    
        callJSRepair();
        </script>";
}
