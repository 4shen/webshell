<?php

/**
 * This provides helper functions for the billing report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

class BillingReport
{
    public static function generateTheQueryPart()
    {
        global $query_part, $query_part2, $billstring, $auth;
        //Search Criteria section.
        $billstring = '';
        $auth = '';
        $query_part = '';
        $query_part2 = '';
        if (isset($_REQUEST['final_this_page_criteria'])) {
            foreach ($_REQUEST['final_this_page_criteria'] as $criteria_key => $criteria_value) {
                $criteria_value = self::prepareSearchItem($criteria_value); // this escapes for sql
                $SplitArray = array();
                //---------------------------------------------------------
                if (strpos($criteria_value, "billing.billed = '1'") !== false) {
                    $billstring .= ' AND ' . $criteria_value;
                } elseif (strpos($criteria_value, "billing.billed = '0'") !== false) {
                    //3 is an error condition
                    $billstring .= ' AND ' . "(billing.billed is null or billing.billed = '0' or (billing.billed = '1' and billing.bill_process = '3'))";
                } elseif (strpos($criteria_value, "billing.billed = '7'") !== false) {
                    $billstring .= ' AND ' . "billing.bill_process = '7'";
                } elseif (strpos($criteria_value, "billing.id = 'null'") !== false) {
                    $billstring .= ' AND ' . "billing.id is null";
                } elseif (strpos($criteria_value, "billing.id = 'not null'") !== false) {
                    $billstring .= ' AND ' . "billing.id is not null";
                } elseif (strpos($criteria_value, "patient_data.fname") !== false) {
                    $SplitArray = explode(' like ', $criteria_value);
                    $query_part .= " AND ($criteria_value or patient_data.lname like " . $SplitArray[1] . ")";
                } elseif (strpos($criteria_value, "billing.authorized") !== false) {
                    $auth = ' AND ' . $criteria_value;
                } elseif (strpos($criteria_value, "form_encounter.pid") !== false) {//comes like '781,780'
                    $SplitArray = explode(" = '", $criteria_value);//comes like 781,780'
                    $SplitArray[1] = substr($SplitArray[1], 0, -1);//comes like 781,780
                    $query_part .= ' AND form_encounter.pid in (' . $SplitArray[1] . ')';
                    $query_part2 .= ' AND pid in (' . $SplitArray[1] . ')';
                } elseif (strpos($criteria_value, "form_encounter.encounter") !== false) {//comes like '781,780'
                    $SplitArray = explode(" = '", $criteria_value);//comes like 781,780'
                    $SplitArray[1] = substr($SplitArray[1], 0, -1);//comes like 781,780
                    $query_part .= ' AND form_encounter.encounter in (' . $SplitArray[1] . ')';
                } elseif (strpos($criteria_value, "insurance_data.provider = '1'") !== false) {
                    $query_part .= ' AND ' . "insurance_data.provider > '0' and (insurance_data.date <= form_encounter.date OR insurance_data.date IS NULL)";
                } elseif (strpos($criteria_value, "insurance_data.provider = '0'") !== false) {
                    $query_part .= ' AND ' . "(insurance_data.provider = '0' or insurance_data.date > form_encounter.date)";
                } else {
                    $query_part .= ' AND ' . $criteria_value;
                }
            }
        }
    }

    //date must be in nice format (e.g. 2002-07-11)
    public static function getBillsBetween(
        $code_type,
        $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
    ) {
        self::generateTheQueryPart();
        global $query_part, $billstring, $auth;
        // Selecting by the date in the billing table is wrong, because that is
        // just the data entry date; instead we want to go by the encounter date
        // which is the date in the form_encounter table.
        //
        $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
            "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
            "FROM form_encounter " .
            "LEFT OUTER JOIN billing ON " .
            "billing.encounter = form_encounter.encounter AND " .
            "billing.pid = form_encounter.pid AND " .
            "billing.code_type LIKE ? AND " .
            "billing.activity = 1 " .
            "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
            "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
            "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
            "WHERE 1=1 $query_part  " . " $auth " . " $billstring " .
            "ORDER BY form_encounter.provider_id, form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
        //echo $sql;
        $res = sqlStatement($sql, array($code_type));
        $all = false;
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }

        return $all;
    }

    public static function getBillsBetweenReport(
        $code_type,
        $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
    ) {
        self::generateTheQueryPart();
        global $query_part, $query_part2, $billstring, $auth;
        // Selecting by the date in the billing table is wrong, because that is
        // just the data entry date; instead we want to go by the encounter date
        // which is the date in the form_encounter table.
        //
        $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
            "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
            "FROM form_encounter " .
            "LEFT OUTER JOIN billing ON " .
            "billing.encounter = form_encounter.encounter AND " .
            "billing.pid = form_encounter.pid AND " .
            "billing.code_type LIKE ? AND " .
            "billing.activity = 1 " .
            "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
            "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
            "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
            "WHERE 1=1 $query_part  " . " $auth " . " $billstring " .
            "ORDER BY form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
        //echo $sql;
        $res = sqlStatement($sql, array($code_type));
        $all = false;
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }

        $query = sqlStatement("SELECT pid, 'COPAY' AS code_type, pay_amount AS code, date(post_time) AS date " .
            "FROM ar_activity where 1=1 $query_part2 and payer_type=0 and account_code='PCP'");
        //new fees screen copay gives account_code='PCP' openemr payment screen copay gives code='CO-PAY'
        for ($iter; $row = sqlFetchArray($query); $iter++) {
            $all[$iter] = $row;
        }

        return $all;
    }

    public static function getBillsListBetween(
        $code_type,
        $cols = "billing.id, form_encounter.date, billing.pid, billing.code_type, billing.code, billing.user"
    ) {
        self::generateTheQueryPart();
        global $query_part, $billstring, $auth;
        // See above comment in self::getBillsBetween().
        //
        $sql = "select distinct $cols " .
            "from form_encounter, billing, patient_data, claims, insurance_data where " .
            "billing.encounter = form_encounter.encounter and " .
            "billing.pid = form_encounter.pid and " .
            "patient_data.pid = form_encounter.pid and " .
            "claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter and " .
            "insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
            $auth .
            $billstring . $query_part . " and " .
            "billing.code_type like ? and " .
            "billing.activity = 1 " .
            "order by billing.pid, billing.date ASC";

        $res = sqlStatement($sql, array($code_type));
        $array = array();
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            array_push($array, $row["id"]);
        }

        return $array;
    }

    public static function billCodesList($list, $skip = [])
    {
        if (empty($list)) {
            return;
        }

        $sqlBindArray = array_diff($list, $skip);
        if (empty($sqlBindArray)) {
            return;
        }

        $in = str_repeat('?,', count($sqlBindArray) - 1) . '?';
        sqlStatement("update billing set billed=1 where id in ($in)", $sqlBindArray);

        return;
    }

    public static function returnOFXSql()
    {
        self::generateTheQueryPart();
        global $query_part, $billstring, $auth;

        $sql = "SELECT distinct billing.*, concat(patient_data.fname, ' ', patient_data.lname) as name from billing "
            . "join patient_data on patient_data.pid = billing.pid "
            . "join form_encounter on "
            . "billing.encounter = form_encounter.encounter AND "
            . "billing.pid = form_encounter.pid "
            . "join claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter "
            . "join insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' "
            . "where billed = '1' "
            . "$auth "
            . "$billstring  $query_part  "
            . "order by billing.pid,billing.encounter";

        return $sql;
    }

    public static function prepareSearchItem($SearchItem)
    {
        $SplitArray = explode(' like ', $SearchItem);
        if (isset($SplitArray[1])) {
            $SplitArray[1] = substr($SplitArray[1], 0, -1);
            $SplitArray[1] = substr($SplitArray[1], 1);
            $SearchItem = $SplitArray[0] . ' like ' . "'" . add_escape_custom($SplitArray[1]) . "'";
        } else {
            $SplitArray = explode(' = ', $SearchItem);
            if (isset($SplitArray[1])) {
                $SplitArray[1] = substr($SplitArray[1], 0, -1);
                $SplitArray[1] = substr($SplitArray[1], 1);
                $SearchItem = $SplitArray[0] . ' = ' . "'" . add_escape_custom($SplitArray[1]) . "'";
            }
        }

        return($SearchItem);
    }

    //Parses the database value and prepares for display.
    public static function buildArrayForReport($Query)
    {
        $array_data = array();
        $res = sqlStatement($Query);
        while ($row = sqlFetchArray($res)) {
            $array_data[$row['id']] = attr($row['name']);
        }

        return $array_data;
    }

    //The criteria  "Insurance Company" is coded here.The ajax one
    public static function insuranceCompanyDisplay()
    {

        // TPS = This Page Search
        global $TPSCriteriaDisplay, $TPSCriteriaKey, $TPSCriteriaIndex, $web_root;

        echo '<table width="140" border="0" cellspacing="0" cellpadding="0">' .
            '<tr>' .
            '<td width="140" colspan="2">' .
            '<iframe id="frame_to_hide" style="position:absolute;display:none; width:240px; height:100px" frameborder=0' .
            'scrolling=no marginwidth=0 src="" marginheight=0>hello</iframe>' .
            '<input type="hidden" id="hidden_ajax_close_value" value="' . attr($_POST['type_code']) . '" /><input name="type_code"  id="type_code" class="text "' .
            'style=" width:140px;"  title="' . xla("Type Id or Name.3 characters minimum (including spaces).") . '"' .
            'onfocus="hide_frame_to_hide();appendOptionTextCriteria(' . attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]) . ',' .
            '' . attr_js($TPSCriteriaKey[$TPSCriteriaIndex]) . ',' .
            'document.getElementById(\'type_code\').value,document.getElementById(\'div_insurance_or_patient\').innerHTML,' .
            '\' = \',' .
            '\'text\')" onblur="show_frame_to_hide()" onKeyDown="PreventIt(event)" value="' . attr($_POST['type_code']) . '"  autocomplete="off"   /><br />' .
            '<!--onKeyUp="ajaxFunction(event,\'non\',\'search_payments.php\');"-->' .
            '<div id="ajax_div_insurance_section">' .
            '<div id="ajax_div_insurance_error">            </div>' .
            '<div id="ajax_div_insurance" style="display:none;"></div>' .
            '</div>' .
            '</div>        </td>' .
            '</tr>' .
            '<tr height="5"><td colspan="2"></td></tr>' .
            '<tr>' .
            '<td><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  style="border:1px solid black; padding-left:5px; width:50px; height:17px;">' . attr($_POST['hidden_type_code']) . '</div><input type="hidden" name="description"  id="description" /></td>' .
            '<td><a href="#" onClick="CleanUpAjax(' . attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]) . ',' .
            attr_js($TPSCriteriaKey[$TPSCriteriaIndex]) . ',\' = \')"><img src="' . $web_root . '/interface/pic/Clear.gif" border="0" /></a></td>' .
            '</tr>' .
            '</table>' .
            '<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="' . attr($_POST['hidden_type_code']) . '"/>';
    }
}
