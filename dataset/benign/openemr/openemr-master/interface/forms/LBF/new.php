<?php

/**
 * LBF form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2019 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once("$srcdir/FeeSheetHtml.class.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$CPR = 4; // cells per row

$pprow = array();

$alertmsg = '';

function end_cell()
{
    global $item_count, $cell_count, $historical_ids;
    if ($item_count > 0) {
        // echo "&nbsp;</td>";
        echo "</td>";

        foreach ($historical_ids as $key => $dummy) {
            // $historical_ids[$key] .= "&nbsp;</td>";
            $historical_ids[$key] .= "</td>";
        }

        $item_count = 0;
    }
}

function end_row()
{
    global $cell_count, $CPR, $historical_ids;
    end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR; ++$cell_count) {
            echo "<td></td>";
            foreach ($historical_ids as $key => $dummy) {
                $historical_ids[$key] .= "<td></td>";
            }
        }

        foreach ($historical_ids as $key => $dummy) {
            echo $historical_ids[$key];
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

// $is_lbf is defined in trend_form.php and indicates that we are being
// invoked from there; in that case the current encounter is irrelevant.
$from_trend_form = !empty($is_lbf);
// Yet another invocation from somewhere other than encounter.
// don't show any action buttons.
$from_lbf_edit = isset($_GET['isShow']) ? true : false;
// This is true if the page is loaded into an iframe in add_edit_issue.php.
$from_issue_form = !empty($_REQUEST['from_issue_form']);

$formname = isset($_GET['formname']) ? $_GET['formname'] : '';
$formid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$portalid = isset($_GET['portalid']) ? intval($_GET['portalid']) : 0;

$visitid = intval(empty($_GET['visitid']) ? $encounter : $_GET['visitid']);

// If necessary get the encounter from the forms table entry for this form.
if ($formid && !$visitid) {
    $frow = sqlQuery(
        "SELECT pid, encounter FROM forms WHERE " .
        "form_id = ? AND formdir = ? AND deleted = 0",
        array($formid, $formname)
    );
    $visitid = intval($frow['encounter']);
    if ($frow['pid'] != $pid) {
        die("Internal error: patient ID mismatch!");
    }
}

if (!$from_trend_form && !$visitid && !$from_lbf_edit) {
    die("Internal error: we do not seem to be in an encounter!");
}

$grparr = array();
getLayoutProperties($formname, $grparr, '*');
$lobj = $grparr[''];
$formtitle = $lobj['grp_title'];
$formhistory = 0 + $lobj['grp_repeats'];
if (!empty($lobj['grp_columns'])) {
    $CPR = intval($lobj['grp_columns']);
}
if (!empty($lobj['grp_size'])) {
    $FONTSIZE = intval($lobj['grp_size']);
}
if (!empty($lobj['grp_issue_type'])) {
    $LBF_ISSUE_TYPE = $lobj['grp_issue_type'];
}
if (!empty($lobj['grp_aco_spec'])) {
    $LBF_ACO = explode('|', $lobj['grp_aco_spec']);
}
if ($lobj['grp_services']) {
    $LBF_SERVICES_SECTION = $lobj['grp_services'] == '*' ? '' : $lobj['grp_services'];
}
if ($lobj['grp_products']) {
    $LBF_PRODUCTS_SECTION = $lobj['grp_products'] == '*' ? '' : $lobj['grp_products'];
}
if ($lobj['grp_diags']) {
    $LBF_DIAGS_SECTION = $lobj['grp_diags'] == '*' ? '' : $lobj['grp_diags'];
}

// Check access control.
if (!AclMain::aclCheckCore('admin', 'super') && !empty($LBF_ACO)) {
    $auth_aco_write = AclMain::aclCheckCore($LBF_ACO[0], $LBF_ACO[1], '', 'write');
    $auth_aco_addonly = AclMain::aclCheckCore($LBF_ACO[0], $LBF_ACO[1], '', 'addonly');
    // echo "\n<!-- '$auth_aco_write' '$auth_aco_addonly' -->\n"; // debugging
    if (!$auth_aco_write && !($auth_aco_addonly && !$formid)) {
        die(xlt('Access denied'));
    }
}

if (isset($LBF_SERVICES_SECTION) || isset($LBF_PRODUCTS_SECTION) || isset($LBF_DIAGS_SECTION)) {
    $fs = new FeeSheetHtml($pid, $visitid);
}

if (!$from_trend_form) {
    $fname = $GLOBALS['OE_SITE_DIR'] . "/LBF/$formname.plugin.php";
    if (file_exists($fname)) {
        include_once($fname);
    }
}

// If Save was clicked, save the info.
//
if (!empty($_POST['bn_save']) || !empty($_POST['bn_save_print']) || !empty($_POST['bn_save_continue'])) {
    $newid = 0;
    if (!$formid) {
        // Creating a new form. Get the new form_id by inserting and deleting a dummy row.
        // This is necessary to create the form instance even if it has no native data.
        $newid = sqlInsert("INSERT INTO lbf_data " .
            "( field_id, field_value ) VALUES ( '', '' )");
        sqlStatement("DELETE FROM lbf_data WHERE form_id = ? AND " .
            "field_id = ''", array($newid));
        addForm($visitid, $formtitle, $newid, $formname, $pid, $userauthorized);
    }

    $my_form_id = $formid ? $formid : $newid;

    // If there is an issue ID, update it in the forms table entry.
    if (isset($_POST['form_issue_id'])) {
        sqlStatement(
            "UPDATE forms SET issue_id = ? WHERE formdir = ? AND form_id = ? AND deleted = 0",
            array($_POST['form_issue_id'], $formname, $my_form_id)
        );
    }

    // If there is a provider ID, update it in the forms table entry.
    if (isset($_POST['form_provider_id'])) {
        sqlStatement(
            "UPDATE forms SET provider_id = ? WHERE formdir = ? AND form_id = ? AND deleted = 0",
            array($_POST['form_provider_id'], $formname, $my_form_id)
        );
    }

    $sets = "";
    $fres = sqlStatement("SELECT * FROM layout_options " .
        "WHERE form_id = ? AND uor > 0 AND field_id != '' AND " .
        "edit_options != 'H' AND edit_options NOT LIKE '%0%' " .
        "ORDER BY group_id, seq", array($formname));
    while ($frow = sqlFetchArray($fres)) {
        $field_id = $frow['field_id'];
        $data_type = $frow['data_type'];
        // If the field was not in the web form, skip it.
        // Except if it's checkboxes, if unchecked they are not returned.
        //
        // if ($data_type != 21 && !isset($_POST["form_$field_id"])) continue;
        //
        // The above statement commented out 2015-01-12 because a LBF plugin might conditionally
        // disable a field that is not applicable, and we need the ability to clear out the old
        // garbage in there so it does not show up in the "report" view of the data.  So we will
        // trust that it's OK to clear any field that is defined in the layout but not returned
        // by the form.
        //
        if ($data_type == 31) {
            continue; // skip static text fields
        }
        $value = get_layout_form_value($frow);
        // If edit option P or Q, save to the appropriate different table and skip the rest.
        $source = $frow['source'];
        if ($source == 'D' || $source == 'H') {
            // Save to patient_data, employer_data or history_data.
            if ($source == 'H') {
                $new = array($field_id => $value);
                updateHistoryData($pid, $new);
            } elseif (strpos($field_id, 'em_') === 0) {
                $field_id = substr($field_id, 3);
                $new = array($field_id => $value);
                updateEmployerData($pid, $new);
            } else {
                $esc_field_id = escape_sql_column_name($field_id, array('patient_data'));
                sqlStatement(
                    "UPDATE patient_data SET `$esc_field_id` = ? WHERE pid = ?",
                    array($value, $pid)
                );
            }

            continue;
        } elseif ($source == 'E') {
            // Save to shared_attributes. Can't delete entries for empty fields because with the P option
            // it's important to know when a current empty value overrides a previous value.
            sqlStatement(
                "REPLACE INTO shared_attributes SET " .
                "pid = ?, encounter = ?, field_id = ?, last_update = NOW(), " .
                "user_id = ?, field_value = ?",
                array($pid, $visitid, $field_id, $_SESSION['authUserID'], $value)
            );
            continue;
        } elseif ($source == 'V') {
            // Save to form_encounter.
            $esc_field_id = escape_sql_column_name($field_id, array('form_encounter'));
            sqlStatement(
                "UPDATE form_encounter SET `$esc_field_id` = ? WHERE " .
                "pid = ? AND encounter = ?",
                array($value, $pid, $visitid)
            );
            continue;
        }

        // It's a normal form field, save to lbf_data.
        if ($formid) { // existing form
            if ($value === '') {
                $query = "DELETE FROM lbf_data WHERE " .
                    "form_id = ? AND field_id = ?";
                sqlStatement($query, array($formid, $field_id));
            } else {
                $query = "REPLACE INTO lbf_data SET field_value = ?, " .
                    "form_id = ?, field_id = ?";
                sqlStatement($query, array($value, $formid, $field_id));
            }
        } else { // new form
            if ($value !== '') {
                sqlStatement(
                    "INSERT INTO lbf_data " .
                    "( form_id, field_id, field_value ) VALUES ( ?, ?, ? )",
                    array($newid, $field_id, $value)
                );
            }
        }
    }

    if ($portalid) {
        // Delete the request from the portal.
        $result = cms_portal_call(array('action' => 'delpost', 'postid' => $portalid));
        if ($result['errmsg']) {
            die(text($result['errmsg']));
        }
    }

    if (isset($fs)) {
        $bill = is_array($_POST['form_fs_bill']) ? $_POST['form_fs_bill'] : null;
        $prod = is_array($_POST['form_fs_prod']) ? $_POST['form_fs_prod'] : null;
        $alertmsg = $fs->checkInventory($prod);
        // If there is an inventory error then no services or products will be saved, and
        // the form will be redisplayed with an error alert and everything else saved.
        if (!$alertmsg) {
            $fs->save($bill, $prod, null, null);
            $fs->updatePriceLevel($_POST['form_fs_pricelevel']);
        }
    }

    if (!$formid) {
        $formid = $newid;
    }

    if (!$alertmsg && !$from_issue_form && empty($_POST['bn_save_continue'])) {
        // Support custom behavior at save time, such as going to another form.
        if (function_exists($formname . '_save_exit')) {
            if (call_user_func($formname . '_save_exit')) {
                exit;
            }
        }
        formHeader("Redirecting....");
        // If Save and Print, write the JavaScript to open a window for printing.
        if (!empty($_POST['bn_save_print'])) {
            echo "<script language='Javascript'>\n" .
                "top.restoreSession();\n" .
                "window.open('$rootdir/forms/LBF/printable.php?" .
                "formname=" . attr_url($formname) .
                "&formid=" . attr_url($formid) .
                "&visitid=" . attr_url($visitid) .
                "&patientid=" . attr_url($pid) .
                "', '_blank');\n" .
                "</script>\n";
        }
        formJump();
        formFooter();
        exit;
    }
}

?>
<html>
<head>
    <?php Header::setupHeader(['opener', 'common', 'datetime-picker', 'jquery-ui',]); ?>

    <style>

        td, input, select, textarea {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
        }

        .table > tbody > tr > td {
            border-top: 0;
        }

        div.section {
            border: solid;
            border-width: 1px;
            border-color: #0000ff;
            margin: 0 0 0 10pt;
            padding: 5pt;
        }

        .form-control {
            width: auto;
            display: inline;
            height: auto;
        }

        .RS {
            border-style: solid;
            border-width: 0 0 1px 0;
            border-color: #999999;
        }

        .RO {
            border-style: solid;
            border-width: 1px 1px 1px 1px !important;
            border-color: #999999;
        }

    </style>

    <?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

    <!-- LiterallyCanvas support -->
    <?php echo lbf_canvas_head(); ?>
    <?php echo signer_head(); ?>

    <script>

        // Support for beforeunload handler.
        var somethingChanged = false;

        function verifyCancel() {
            if (somethingChanged) {
                if (!confirm(<?php echo xlj('You have unsaved changes. Do you really want to close this form?'); ?>)) {
                    return false;
                }
            }
            somethingChanged = false;
            parent.closeTab(window.name, false);
        }

        $(function () {

            if (window.tabbify) {
                tabbify();
            }
            if (window.checkSkipConditions) {
                checkSkipConditions();
            }

            $(".iframe_medium").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let url = $(this).attr('href');
                url = encodeURI(url);
                dlgopen('', '', 950, 550, '', '', {
                    buttons: [
                        {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
                    ],
                    type: 'iframe',
                    url: url
                });
            });

            // Support for beforeunload handler.
            $('.lbfdata input, .lbfdata select, .lbfdata textarea').change(function () {
                somethingChanged = true;
            });
            window.addEventListener("beforeunload", function (e) {
                if (somethingChanged && !top.timed_out) {
                    var msg = <?php echo xlj('You have unsaved changes.'); ?>;
                    e.returnValue = msg;     // Gecko, Trident, Chrome 34+
                    return msg;              // Gecko, WebKit, Chrome <34
                }
            });

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datepicker-past').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datetimepicker-past').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = false; ?>
                <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datepicker-future').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = '-1970/01/01'; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datetimepicker-future').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php $datetimepicker_minDate = '-1970/01/01'; ?>
                <?php $datetimepicker_maxDate = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

        // Supports customizable forms.
        function divclick(cb, divid) {
            var divstyle = document.getElementById(divid).style;
            if (cb.checked) {
                divstyle.display = 'block';
            } else {
                divstyle.display = 'none';
            }
            return true;
        }

        // The ID of the input element to receive a found code.
        var current_sel_name = '';

        // This is for callback by the find-code popup.
        // Appends to or erases the current list of related codes.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            <?php if (isset($fs)) { ?>
            // This is the case of selecting a code for the Fee Sheet:
            if (!current_sel_name) {
                if (code) {
                    $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                        '?codetype=' + encodeURIComponent(codetype) +
                        '&code=' + encodeURIComponent(code) +
                        '&selector=' + encodeURIComponent(selector) +
                        '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel ? f.form_fs_pricelevel.value : "") +
                        '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
                }
                return '';
            }
            <?php } ?>
            // frc will be the input element containing the codes.
            // frcd, if set, will be the input element containing their descriptions.
            var frc = f[current_sel_name];
            var frcd;
            var matches = current_sel_name.match(/^(.*)__desc$/);
            if (matches) {
                frcd = frc;
                frc = f[matches[1]];
            }
            // For LBFs we will allow only one code in a field.
            var s = ''; // frc.value;
            var sd = ''; // frcd ? frcd.value : s;
            if (code) {
                if (s.length > 0) {
                    s += ';';
                    sd += ';';
                }
                s += codetype + ':' + code;
                sd += codedesc;
            } else {
                s = '';
                sd = '';
            }
            frc.value = s;
            if (frcd) frcd.value = sd;
            return '';
        }

        // This invokes the "dynamic" find-code popup.
        function sel_related(elem, codetype) {
            current_sel_name = elem ? elem.name : '';
            var url = '<?php echo $rootdir ?>/patient_file/encounter/find_code_dynamic.php';
            if (codetype) url += '?codetype=' + encodeURIComponent(codetype);
            dlgopen(url, '_blank', 800, 500);
        }

        // Compute the length of a string without leading and trailing spaces.
        function trimlen(s) {
            var i = 0;
            var j = s.length - 1;
            for (; i <= j && s.charAt(i) == ' '; ++i) ;
            for (; i <= j && s.charAt(j) == ' '; --j) ;
            if (i > j) return 0;
            return j + 1 - i;
        }

        // This capitalizes the first letter of each word in the passed input
        // element.  It also strips out extraneous spaces.
        function capitalizeMe(elem) {
            var a = elem.value.split(' ');
            var s = '';
            for (var i = 0; i < a.length; ++i) {
                if (a[i].length > 0) {
                    if (s.length > 0) s += ' ';
                    s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
                }
            }
            elem.value = s;
        }

        // Validation logic for form submission.
        function validate(f) {
            <?php generate_layout_validation($formname); ?>
            // Validation for Fee Sheet stuff. Skipping this because CV decided (2015-11-03)
            // that these warning messages are not appropriate for layout based visit forms.
            //
            // if (window.jsLineItemValidation && !jsLineItemValidation(f)) return false;
            somethingChanged = false; // turn off "are you sure you want to leave"
            top.restoreSession();
            return true;
        }

        <?php
        if (isset($fs)) {
        // jsLineItemValidation() function for the fee sheet stuff.
            echo $fs->jsLineItemValidation('form_fs_bill', 'form_fs_prod');
            ?>

        // Add a service line item.
        function fs_append_service(code_type, code, desc, price) {
            var telem = document.getElementById('fs_services_table');
            var lino = telem.rows.length - 1;
            var trelem = telem.insertRow(telem.rows.length);
            trelem.innerHTML =
                "<td class='text'>" + code + "&nbsp;</td>" +
                "<td class='text'>" + desc + "&nbsp;</td>" +
                "<td class='text'>" +
                "<select name='form_fs_bill[" + lino + "][provid]'>" +
                "<?php echo addslashes($fs->genProviderOptionList('-- ' . xl('Default') . ' --')) ?>" +
                "</select>&nbsp;" +
                "</td>" +
                "<td class='text' align='right'>" + price + "&nbsp;</td>" +
                "<td class='text' align='right'>" +
                "<input type='checkbox' name='form_fs_bill[" + lino + "][del]' value='1' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code_type]' value='" + code_type + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code]'      value='" + code + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][price]'     value='" + price + "' />" +
                "</td>";
        }

        // Add a product line item.
        function fs_append_product(code_type, code, desc, price, warehouses) {
            var telem = document.getElementById('fs_products_table');
            if (!telem) {
                alert(<?php echo xlj('A product was selected but there is no product section in this form.'); ?>);
                return;
            }
            var lino = telem.rows.length - 1;
            var trelem = telem.insertRow(telem.rows.length);
            trelem.innerHTML =
                "<td class='text'>" + desc + "&nbsp;</td>" +
                "<td class='text'>" +
                "<select name='form_fs_prod[" + lino + "][warehouse]'>" + warehouses + "</select>&nbsp;" +
                "</td>" +
                "<td class='text' align='right'>" +
                "<input type='text' name='form_fs_prod[" + lino + "][units]' size='3' value='1' />&nbsp;" +
                "</td>" +
                "<td class='text' align='right'>" + price + "&nbsp;</td>" +
                "<td class='text' align='right'>" +
                "<input type='checkbox' name='form_fs_prod[" + lino + "][del]'     value='1' />" +
                "<input type='hidden'   name='form_fs_prod[" + lino + "][drug_id]' value='" + code + "' />" +
                "<input type='hidden'   name='form_fs_prod[" + lino + "][price]'   value='" + price + "' />" +
                "</td>";
        }

        // Add a diagnosis line item.
        function fs_append_diag(code_type, code, desc) {
            var telem = document.getElementById('fs_diags_table');
            // Adding 1000 because form_fs_bill[] is shared with services and we want to avoid collisions.
            var lino = telem.rows.length - 1 + 1000;
            var trelem = telem.insertRow(telem.rows.length);
            trelem.innerHTML =
                "<td class='text'>" + code + "&nbsp;</td>" +
                "<td class='text'>" + desc + "&nbsp;</td>" +
                "<td class='text' align='right'>" +
                "<input type='checkbox' name='form_fs_bill[" + lino + "][del]' value='1' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code_type]' value='" + code_type + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code]'      value='" + code + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][price]'     value='" + 0 + "' />" +
                "</td>";
        }

        // Respond to clicking a checkbox for adding (or removing) a specific service.
        function fs_service_clicked(cb) {
            var f = cb.form;
            // The checkbox value is a JSON array containing the service's code type, code, description,
            // and price for each price level.
            var a = JSON.parse(cb.value);
            if (!cb.checked) {
                // The checkbox was UNchecked.
                // Find last row with a matching code_type and code and set its del flag.
                var telem = document.getElementById('fs_services_table');
                var lino = telem.rows.length - 2;
                for (; lino >= 0; --lino) {
                    var pfx = "form_fs_bill[" + lino + "]";
                    if (f[pfx + "[code_type]"].value == a[0] && f[pfx + "[code]"].value == a[1]) {
                        f[pfx + "[del]"].checked = true;
                        break;
                    }
                }
                return;
            }
            $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                '?codetype=' + encodeURIComponent(a[0]) +
                '&code=' + encodeURIComponent(a[1]) +
                '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel.value) +
                '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
        }

        // Respond to clicking a checkbox for adding (or removing) a specific product.
        function fs_product_clicked(cb) {
            var f = cb.form;
            // The checkbox value is a JSON array containing the product's code type, code and selector.
            var a = JSON.parse(cb.value);
            if (!cb.checked) {
                // The checkbox was UNchecked.
                // Find last row with a matching product ID and set its del flag.
                var telem = document.getElementById('fs_products_table');
                var lino = telem.rows.length - 2;
                for (; lino >= 0; --lino) {
                    var pfx = "form_fs_prod[" + lino + "]";
                    if (f[pfx + "[code_type]"].value == a[0] && f[pfx + "[code]"].value == a[1]) {
                        f[pfx + "[del]"].checked = true;
                        break;
                    }
                }
                return;
            }
            $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                '?codetype=' + encodeURIComponent(a[0]) +
                '&code=' + encodeURIComponent(a[1]) +
                '&selector=' + encodeURIComponent(a[2]) +
                '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel.value) +
                '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
        }

        // Respond to clicking a checkbox for adding (or removing) a specific diagnosis.
        function fs_diag_clicked(cb) {
            var f = cb.form;
            // The checkbox value is a JSON array containing the diagnosis's code type, code, description.
            var a = JSON.parse(cb.value);
            if (!cb.checked) {
                // The checkbox was UNchecked.
                // Find last row with a matching code_type and code and set its del flag.
                var telem = document.getElementById('fs_diags_table');
                var lino = telem.rows.length - 2 + 1000;
                for (; lino >= 0; --lino) {
                    var pfx = "form_fs_bill[" + lino + "]";
                    if (f[pfx + "[code_type]"].value == a[0] && f[pfx + "[code]"].value == a[1]) {
                        f[pfx + "[del]"].checked = true;
                        break;
                    }
                }
                return;
            }
            $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                '?codetype=' + encodeURIComponent(a[0]) +
                '&code=' + encodeURIComponent(a[1]) +
                '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel ? f.form_fs_pricelevel.value : "") +
                '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
        }

        // Respond to selecting a package of codes.
        function fs_package_selected(sel) {
            var f = sel.form;
            // The option value is an encoded string of code types and codes.
            if (sel.value) {
                $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                    '?list=' + encodeURIComponent(sel.value) +
                    '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel ? f.form_fs_pricelevel.value : "") +
                    '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
            }
            sel.selectedIndex = 0;
        }

        // This is called back by code_attributes_ajax.php to complete the appending of a line item.
        function code_attributes_handler(codetype, code, desc, price, warehouses) {
            if (codetype == 'PROD') {
                fs_append_product(codetype, code, desc, price, warehouses);
            }
            else if (codetype == 'ICD9' || codetype == 'ICD10') {
                fs_append_diag(codetype, code, desc);
            }
            else {
                fs_append_service(codetype, code, desc, price);
            }
        }

        function warehouse_changed(sel) {
            if (!confirm(<?php echo xlj('Do you really want to change Warehouse?'); ?>)) {
                // They clicked Cancel so reset selection to its default state.
                for (var i = 0; i < sel.options.length; ++i) {
                    sel.options[i].selected = sel.options[i].defaultSelected;
                }
            }
        }

        <?php } // end if (isset($fs))

        if (function_exists($formname . '_javascript')) {
            call_user_func($formname . '_javascript');
        }
        ?>

    </script>
</head>

<body class="body_top"<?php if ($from_issue_form) {
    echo " style='background-color:var(--white)'";
                      } ?>>
<div class='container'>
    <?php
    echo "<form method='post' " .
        "action='$rootdir/forms/LBF/new.php?formname=" . attr_url($formname) . "&id=" . attr_url($formid) . "&portalid=" . attr_url($portalid) . "' " .
        "onsubmit='return validate(this)'>\n";

    $cmsportal_login = '';
    $portalres = false;


    if (!$from_trend_form) {
        $enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, p.cmsportal_login, " .
        "fe.date FROM " .
        "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
        "p.pid = ? AND f.pid = p.pid AND f.encounter = ? AND " .
        "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
        "fe.id = f.form_id LIMIT 1", array($pid, $visitid)); ?>

    <div class="container-responsive">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h3>
                        <?php echo text($formtitle) . " " . xlt('for') . ' ';
                        echo text($enrow['fname']) . ' ' . text($enrow['mname']) . ' ' . text($enrow['lname']);
                        echo ' ' . xlt('on') . ' ' . text(oeFormatShortDate(substr($enrow['date'], 0, 10))); ?>
                    </h3>
                    <?php
                    $firow = sqlQuery(
                        "SELECT issue_id, provider_id FROM forms WHERE " .
                        "formdir = ? AND form_id = ? AND deleted = 0",
                        array($formname, $formid)
                    );
                    $form_issue_id = empty($firow['issue_id']) ? 0 : intval($firow['issue_id']);
                    $default = empty($firow['provider_id']) ? $_SESSION['authUserID'] : intval($firow['provider_id']);

                    // Provider selector.
                    echo "&nbsp;&nbsp;";
                    echo xlt('Provider') . ": ";
                    // TBD: Refactor this function out of the FeeSheetHTML class as that is not the best place for it.
                    echo FeeSheetHtml::genProviderSelect('form_provider_id', '-- ' . xl("Please Select") . ' --', $default);

                    // If appropriate build a drop-down selector of issues of this type for this patient.
                    // We skip this if in an issue form tab because removing and adding visit form tabs is
                    // beyond the current scope of that code.
                    if (!empty($LBF_ISSUE_TYPE) && !$from_issue_form) {
                        echo "&nbsp;&nbsp;";
                        $query = "SELECT id, title, date, begdate FROM lists WHERE pid = ? AND type = ? " .
                            "ORDER BY COALESCE(begdate, date) DESC, id DESC";
                        $ires = sqlStatement($query, array($pid, $LBF_ISSUE_TYPE));
                        echo "<select name='form_issue_id'>\n";
                        echo " <option value='0'>-- " . xlt('Select Case') . " --</option>\n";
                        while ($irow = sqlFetchArray($ires)) {
                            $issueid = $irow['id'];
                            $issuedate = oeFormatShortDate(empty($irow['begdate']) ? $irow['date'] : $irow['begdate']);
                            echo " <option value='" . attr($issueid) . "'";
                            if ($issueid == $form_issue_id) {
                                echo " selected";
                            }
                            echo ">" . text("$issuedate " . $irow['title']) . "</option>\n";
                        }
                        echo "</select>\n";
                    }
                    ?>
                </div>
            </div>

            <?php $cmsportal_login = $enrow['cmsportal_login'];
    } // end not from trend form
    ?>

            <!-- This is where a chart might display. -->
            <div id="chart"></div>

            <?php
            $shrow = getHistoryData($pid);

            // Determine if this layout uses edit option "I" anywhere.
            // If not we default to only the first group being initially open.
            $tmprow = sqlQuery("SELECT form_id FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 AND edit_options LIKE '%I%' " .
                "LIMIT 1", array($formname));
            $some_group_is_open = !empty($tmprow['form_id']);

            $fres = sqlStatement("SELECT * FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 " .
                "ORDER BY group_id, seq", array($formname));
            $cell_count = 0;
            $item_count = 0;
            $display_style = 'block';

            // This string is the active group levels. Each leading substring represents an instance of nesting.
            $group_levels = '';

            // This indicates if </table> will need to be written to end the fields in a group.
            $group_table_active = false;

            // This is an array keyed on forms.form_id for other occurrences of this
            // form type.  The maximum number of such other occurrences to display is
            // in list_options.option_value for this form's list item.  Values in this
            // array are work areas for building the ending HTML for each displayed row.
            //
            $historical_ids = array();

            // True if any data items in this form can be graphed.
            $form_is_graphable = false;

            $condition_str = '';

            while ($frow = sqlFetchArray($fres)) {
                $this_group = $frow['group_id'];
                $titlecols = $frow['titlecols'];
                $datacols = $frow['datacols'];
                $data_type = $frow['data_type'];
                $field_id = $frow['field_id'];
                $list_id = $frow['list_id'];
                $edit_options = $frow['edit_options'];
                $source = $frow['source'];
                $jump_new_row = isOption($edit_options, 'J');
                $prepend_blank_row = isOption($edit_options, 'K');

                $graphable = isOption($edit_options, 'G') !== false;
                if ($graphable) {
                    $form_is_graphable = true;
                }

                // Accumulate action conditions into a JSON expression for the browser side.
                accumActionConditions($field_id, $condition_str, $frow['conditions']);

                $currvalue = '';

                if (isOption($edit_options, 'H') !== false) {
                    // This data comes from static history
                    if (isset($shrow[$field_id])) {
                        $currvalue = $shrow[$field_id];
                    }
                } else {
                    if (!$formid && $portalres) {
                        // Copying CMS Portal form data into this field if appropriate.
                        $currvalue = cms_field_to_lbf($data_type, $field_id, $portalres['fields']);
                    }

                    if ($currvalue === '') {
                        $currvalue = lbf_current_value($frow, $formid, $is_lbf ? 0 : $encounter);
                    }

                    if ($currvalue === false) {
                        continue; // column does not exist, should not happen
                    }

                    // Handle "P" edit option to default to the previous value of a form field.
                    if (!$from_trend_form && empty($currvalue) && isOption($edit_options, 'P') !== false) {
                        if ($source == 'F' && !$formid) {
                            // Form attribute for new form, get value from most recent form instance.
                            // Form attributes of existing forms are expected to have existing values.
                            $tmp = sqlQuery(
                                "SELECT encounter, form_id FROM forms WHERE " .
                                "pid = ? AND formdir = ? AND deleted = 0 " .
                                "ORDER BY date DESC LIMIT 1",
                                array($pid, $formname)
                            );
                            if (!empty($tmp['encounter'])) {
                                $currvalue = lbf_current_value($frow, $tmp['form_id'], $tmp['encounter']);
                            }
                        } elseif ($source == 'E') {
                            // Visit attribute, get most recent value as of this visit.
                            // Even if the form already exists for this visit it may have a readonly value that only
                            // exists in a previous visit and was created from a different form.
                            $tmp = sqlQuery(
                                "SELECT sa.field_value FROM form_encounter AS e1 " .
                                "JOIN form_encounter AS e2 ON " .
                                "e2.pid = e1.pid AND (e2.date < e1.date OR (e2.date = e1.date AND e2.encounter <= e1.encounter)) " .
                                "JOIN shared_attributes AS sa ON " .
                                "sa.pid = e2.pid AND sa.encounter = e2.encounter AND sa.field_id = ?" .
                                "WHERE e1.pid = ? AND e1.encounter = ? " .
                                "ORDER BY e2.date DESC, e2.encounter DESC LIMIT 1",
                                array($field_id, $pid, $visitid)
                            );
                            if (isset($tmp['field_value'])) {
                                $currvalue = $tmp['field_value'];
                            }
                        }
                    } // End "P" option logic.
                }

                $this_levels = $this_group;
                $i = 0;
                $mincount = min(strlen($this_levels), strlen($group_levels));
                while ($i < $mincount && $this_levels[$i] == $group_levels[$i]) {
                    ++$i;
                }
                // $i is now the number of initial matching levels.

                // If ending a group or starting a subgroup, terminate the current row and its table.
                if ($group_table_active && ($i != strlen($group_levels) || $i != strlen($this_levels))) {
                    end_row();
                    echo " </table>\n";
                    $group_table_active = false;
                }

                // Close any groups that we are done with.
                while (strlen($group_levels) > $i) {
                    $gname = $grparr[$group_levels]['grp_title'];
                    $group_levels = substr($group_levels, 0, -1); // remove last character
                    // No div for an empty group name.
                    if (strlen($gname)) {
                        echo "</div>\n";
                    }
                }

                // If there are any new groups, open them.
                while ($i < strlen($this_levels)) {
                    end_row();
                    if ($group_table_active) {
                        echo " </table>\n";
                        $group_table_active = false;
                    }
                    $group_levels .= $this_levels[$i++];
                    $gname = $grparr[substr($group_levels, 0, $i)]['grp_title'];
                    $subtitle = xl_layout_label($grparr[substr($group_levels, 0, $i)]['grp_subtitle']);

                    // Compute a short unique identifier for this group.
                    $group_seq = 'lbf' . $group_levels;
                    $group_name = $gname;

                    if ($some_group_is_open) {
                        // Must have edit option "I" in first item for its group to be initially open.
                        $display_style = isOption($edit_options, 'I') === false ? 'none' : 'block';
                    }

                    // If group name is blank, no checkbox or div.
                    if (strlen($gname)) {
                        echo "<br /><span class='bold'><input type='checkbox' name='form_cb_" . attr($group_seq) . "' value='1' " .
                            "onclick='return divclick(this," . attr_js('div_' . $group_seq) . ");'";
                        if ($display_style == 'block') {
                            echo " checked";
                        }

                        echo " /><b>" . text(xl_layout_label($group_name)) . "</b></span>\n";
                        echo "<div id='div_" . attr($group_seq) . "' class='section table-responsive' style='display:" . attr($display_style) . ";'>\n";
                    }

                    $group_table_active = true;
                    echo " <table border='0' cellspacing='0' cellpadding='0' class='lbfdata'>\n";

                    if ($subtitle) {
                        // There is a group subtitle so show it.
                        echo "<tr><td class='bold' style='color:#0000ff' colspan='" . attr($CPR) . "'>" . text($subtitle) . "</td></tr>\n";
                        echo "<tr><td class='bold' style='height:4pt' colspan='" . attr($CPR) . "'></td></tr>\n";
                    }

                    $display_style = 'none';

                    // Initialize historical data array and write date headers.
                    $historical_ids = array();
                    if ($formhistory > 0) {
                        echo " <tr>";
                        echo "<td colspan='" . attr($CPR) . "' align='right' class='bold'>";
                        if (empty($is_lbf)) {
                            // Including actual date per IPPF request 2012-08-23.
                            echo text(oeFormatShortDate(substr($enrow['date'], 0, 10)));
                            echo ' (' . xlt('Current') . ')';
                        }

                        echo "&nbsp;</td>\n";
                        $hres = sqlStatement(
                            "SELECT f.form_id, fe.date " .
                            "FROM forms AS f, form_encounter AS fe WHERE " .
                            "f.pid = ? AND f.formdir = ? AND " .
                            "f.form_id != ? AND f.deleted = 0 AND " .
                            "fe.pid = f.pid AND fe.encounter = f.encounter " .
                            "ORDER BY fe.date DESC, f.encounter DESC, f.date DESC " .
                            "LIMIT ?",
                            array($pid, $formname, $formid, $formhistory)
                        );
                        // For some readings like vitals there may be multiple forms per encounter.
                        // We sort these sensibly, however only the encounter date is shown here;
                        // at some point we may wish to show also the data entry date/time.
                        while ($hrow = sqlFetchArray($hres)) {
                            echo "<td colspan='" . attr($CPR) . "' align='right' class='bold'>&nbsp;" .
                                text(oeFormatShortDate(substr($hrow['date'], 0, 10))) . "</td>\n";
                            $historical_ids[$hrow['form_id']] = '';
                        }

                        echo " </tr>";
                    }
                }

                // Handle starting of a new row.
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0 || $prepend_blank_row || $jump_new_row) {
                    end_row();
                    if ($prepend_blank_row) {
                        echo "<tr><td class='text' colspan='" . attr($CPR) . "'>&nbsp;</td></tr>\n";
                    }
                    if (isOption($edit_options, 'RS')) {
                        echo " <tr class='RS'>";
                    } elseif (isOption($edit_options, 'RO')) {
                        echo " <tr class='RO'>";
                    } else {
                        echo " <tr>";
                    }

                    // Clear historical data string.
                    foreach ($historical_ids as $key => $dummy) {
                        $historical_ids[$key] = '';
                    }
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

// First item is on the "left-border"
                $leftborder = true;

// Handle starting of a new label cell.
                if ($titlecols > 0) {
                    end_cell();
                    if (isOption($edit_options, 'SP')) {
                        $datacols = 0;
                        $titlecols = $CPR;
                        echo "<td valign='top' colspan='" . attr($titlecols) . "'";
                    } else {
                        echo "<td valign='top' colspan='" . attr($titlecols) . "' nowrap";
                    }
                    echo " class='";
                    echo ($frow['uor'] == 2) ? "required" : "bold";
                    if ($graphable) {
                        echo " graph";
                    }

                    echo "'";

                    if ($cell_count > 0) {
                        echo " style='padding-left:10pt'";
                    }
                    // This ID is used by action conditions and also show_graph().
                    echo " id='label_id_" . attr($field_id) . "'";
                    echo ">";

                    foreach ($historical_ids as $key => $dummy) {
                        $historical_ids[$key] .= "<td valign='top' colspan='" . attr($titlecols) . "' class='text' nowrap>";
                    }

                    $cell_count += $titlecols;
                }

                ++$item_count;

                echo "<b>";
                if ($frow['title']) {
                    $tmp = xl_layout_label($frow['title']);
                    echo text($tmp);
                    // Append colon only if label does not end with punctuation.
                    if (strpos('?!.,:-=', substr($tmp, -1, 1)) === false) {
                        echo ':';
                    }
                } else {
                    echo "&nbsp;";
                }
                echo "</b>";

// Note the labels are not repeated in the history columns.

// Handle starting of a new data cell.
                if ($datacols > 0) {
                    end_cell();
                    if (isOption($edit_options, 'DS')) {
                        echo "<td valign='top' colspan='" . attr($datacols) . "' class='text RS'";
                    }
                    if (isOption($edit_options, 'DO')) {
                        echo "<td valign='top' colspan='" . attr($datacols) . "' class='text RO'";
                    } else {
                        echo "<td valign='top' colspan='" . attr($datacols) . "' class='text'";
                    }
                    // This ID is used by action conditions.
                    echo " id='value_id_" . attr($field_id) . "'";
                    if ($cell_count > 0) {
                        echo " style='padding-left:5pt'";
                    }

                    echo ">";

                    foreach ($historical_ids as $key => $dummy) {
                        $historical_ids[$key] .= "<td valign='top' align='right' colspan='" . attr($datacols) . "' class='text'>";
                    }

                    $cell_count += $datacols;
                }

                ++$item_count;

// Skip current-value fields for the display-only case.
                if (!$from_trend_form) {
                    if ($frow['edit_options'] == 'H') {
                        echo generate_display_field($frow, $currvalue);
                    } else {
                        generate_form_field($frow, $currvalue);
                    }
                }

// Append to historical data of other dates for this item.
                foreach ($historical_ids as $key => $dummy) {
                    $value = lbf_current_value($frow, $key, 0);
                    $historical_ids[$key] .= generate_display_field($frow, $value);
                }
            }

            // Close all open groups.
            if ($group_table_active) {
                end_row();
                echo " </table>\n";
                $group_table_active = false;
            }
            while (strlen($group_levels)) {
                $gname = $grparr[$group_levels]['grp_title'];
                $group_levels = substr($group_levels, 0, -1); // remove last character
                // No div for an empty group name.
                if (strlen($gname)) {
                    echo "</div>\n";
                }
            }

            $display_style = 'none';

            if (isset($LBF_SERVICES_SECTION) || isset($LBF_DIAGS_SECTION)) {
                $fs->loadServiceItems();
            }

            if (isset($LBF_SERVICES_SECTION)) {
                // Create the checkbox and div for the Services Section.
                echo "<br /><span class='bold'><input type='checkbox' name='form_cb_fs_services' value='1' " .
                    "onclick='return divclick(this, \"div_fs_services\");'";
                if ($display_style == 'block') {
                    echo " checked";
                }
                echo " /><b>" . xlt('Services') . "</b></span>\n";
                echo "<div id='div_fs_services' class='section' style='display:" . attr($display_style) . ";'>\n";
                echo "<center>\n";
                $display_style = 'none';

                // If there are associated codes, generate a checkbox for each one.
                if ($LBF_SERVICES_SECTION) {
                    echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
                    $cols = 3;
                    $tdpct = (int)(100 / $cols);
                    $count = 0;
                    $relcodes = explode(';', $LBF_SERVICES_SECTION);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        $codes_esc = attr($codestring);
                        $cbval = $fs->genCodeSelectorValue($codestring);
                        if ($count % $cols == 0) {
                            if ($count) {
                                echo " </tr>\n";
                            }
                            echo " <tr>\n";
                        }
                        echo "  <td width='" . attr($tdpct) . "%'>";
                        echo "<input type='checkbox' id='form_fs_services[$codes_esc]' " .
                            "onclick='fs_service_clicked(this)' value='" . attr($cbval) . "'";
                        if ($fs->code_is_in_fee_sheet) {
                            echo " checked";
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $title = lookup_code_descriptions($codestring);
                        $title = empty($title) ? $code : xl_list_label($title);
                        echo " />" . text($title);
                        echo "</td>\n";
                        ++$count;
                    }
                    if ($count) {
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                }

                // A row for Search, Add Package, Main Provider.
                $ctype = $GLOBALS['ippf_specific'] ? 'MA' : '';
                echo "<p class='bold'>";
                echo "<input type='button' value='" . xla('Search Services') . "' onclick='sel_related(null," . attr_js($ctype) . ")' />&nbsp;&nbsp;\n";
                $fscres = sqlStatement("SELECT * FROM fee_sheet_options ORDER BY fs_category, fs_option");
                if (sqlNumRows($fscres)) {
                    $last_category = '';
                    echo "<select onchange='fs_package_selected(this)'>\n";
                    echo " <option value=''>" . xlt('Add Package') . "</option>\n";
                    while ($row = sqlFetchArray($fscres)) {
                        $fs_category = $row['fs_category'];
                        $fs_option = $row['fs_option'];
                        $fs_codes = $row['fs_codes'];
                        if ($fs_category !== $last_category) {
                            if ($last_category) {
                                echo " </optgroup>\n";
                            }
                            echo " <optgroup label='" . xla(substr($fs_category, 1)) . "'>\n";
                            $last_category = $fs_category;
                        }
                        echo " <option value='" . attr($fs_codes) . "'>" . xlt(substr($fs_option, 1)) . "</option>\n";
                    }
                    if ($last_category) {
                        echo " </optgroup>\n";
                    }
                    echo "</select>&nbsp;&nbsp;\n";
                }
                echo xlt('Main Provider') . ": ";
                echo $fs->genProviderSelect("form_fs_provid", ' ', $fs->provider_id);
                echo "\n";
                echo "</p>\n";

                // Generate a line for each service already in this FS.
                echo "<table cellpadding='0' cellspacing='2' id='fs_services_table'>\n";
                echo " <tr>\n";
                echo "  <td class='bold' colspan='2'>" . xlt('Services Provided') . "&nbsp;</td>\n";
                echo "  <td class='bold'>" . xlt('Provider') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Price') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Delete') . "</td>\n";
                echo " </tr>\n";
                foreach ($fs->serviceitems as $lino => $li) {
                    // Skip diagnoses; those would be in the Diagnoses section below.
                    if ($code_types[$li['codetype']]['diag']) {
                        continue;
                    }
                    echo " <tr>\n";
                    echo "  <td class='text'>" . text($li['code']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" .
                        $fs->genProviderSelect("form_fs_bill[$lino][provid]", '-- ' . xl("Default") . ' --', $li['provid']) .
                        "  &nbsp;</td>\n";
                    echo "  <td class='text' align='right'>" . text(oeFormatMoney($li['price'])) . "&nbsp;</td>\n";
                    echo "  <td class='text' align='right'>\n" .
                        "   <input type='checkbox' name='form_fs_bill[" . attr($lino) . "][del]' " .
                        "value='1'" . ($li['del'] ? " checked" : "") . " />\n";
                    foreach ($li['hidden'] as $hname => $hvalue) {
                        echo "   <input type='hidden' name='form_fs_bill[" . attr($lino) . "][" . attr($hname) . "]' value='" . attr($hvalue) . "' />\n";
                    }
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                echo "</table>\n";
                echo "</center>\n";
                echo "</div>\n";
            } // End Services Section

            if (isset($LBF_PRODUCTS_SECTION)) {
                // Create the checkbox and div for the Products Section.
                echo "<br /><span class='bold'><input type='checkbox' name='form_cb_fs_products' value='1' " .
                    "onclick='return divclick(this, \"div_fs_products\");'";
                if ($display_style == 'block') {
                    echo " checked";
                }
                echo " /><b>" . xlt('Products') . "</b></span>\n";
                echo "<div id='div_fs_products' class='section' style='display:" . attr($display_style) . ";'>\n";
                echo "<center>\n";
                $display_style = 'none';

                // If there are associated codes, generate a checkbox for each one.
                if ($LBF_PRODUCTS_SECTION) {
                    echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
                    $cols = 3;
                    $tdpct = (int)(100 / $cols);
                    $count = 0;
                    $relcodes = explode(';', $LBF_PRODUCTS_SECTION);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        $codes_esc = attr($codestring);
                        $cbval = $fs->genCodeSelectorValue($codestring);
                        if ($count % $cols == 0) {
                            if ($count) {
                                echo " </tr>\n";
                            }
                            echo " <tr>\n";
                        }
                        echo "  <td width='" . attr($tdpct) . "%'>";
                        echo "<input type='checkbox' id='form_fs_products[$codes_esc]' " .
                            "onclick='fs_product_clicked(this)' value='" . attr($cbval) . "'";
                        if ($fs->code_is_in_fee_sheet) {
                            echo " checked";
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $crow = sqlQuery(
                            "SELECT name FROM drugs WHERE " .
                            "drug_id = ? ORDER BY drug_id LIMIT 1",
                            array($code)
                        );
                        $title = empty($crow['name']) ? $code : xl_list_label($crow['name']);
                        echo " />" . text($title);
                        echo "</td>\n";
                        ++$count;
                    }
                    if ($count) {
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                }

                // A row for Search
                $ctype = $GLOBALS['ippf_specific'] ? 'MA' : '';
                echo "<p class='bold'>";
                echo "<input type='button' value='" . xla('Search Products') . "' onclick='sel_related(null,\"PROD\")' />&nbsp;&nbsp;";
                echo "</p>\n";

                // Generate a line for each product already in this FS.
                echo "<table cellpadding='0' cellspacing='2' id='fs_products_table'>\n";
                echo " <tr>\n";
                echo "  <td class='bold'>" . xlt('Products Provided') . "&nbsp;</td>\n";
                echo "  <td class='bold'>" . xlt('Warehouse') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Quantity') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Price') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Delete') . "</td>\n";
                echo " </tr>\n";
                $fs->loadProductItems();
                foreach ($fs->productitems as $lino => $li) {
                    echo " <tr>\n";
                    echo "  <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" .
                        $fs->genWarehouseSelect("form_fs_prod[$lino][warehouse]", '', $li['warehouse'], false, $li['hidden']['drug_id'], true) .
                        "  &nbsp;</td>\n";
                    echo "  <td class='text' align='right'>" .
                        "<input type='text' name='form_fs_prod[" . attr($lino) . "][units]' size='3' value='" . attr($li['units']) . "' />" .
                        "&nbsp;</td>\n";
                    echo "  <td class='text' align='right'>" . text(oeFormatMoney($li['price'])) . "&nbsp;</td>\n";
                    echo "  <td class='text' align='right'>\n" .
                        "   <input type='checkbox' name='form_fs_prod[" . attr($lino) . "][del]' " .
                        "value='1'" . ($li['del'] ? " checked" : "") . " />\n";
                    foreach ($li['hidden'] as $hname => $hvalue) {
                        echo "   <input type='hidden' name='form_fs_prod[" . attr($lino) . "][" . attr($hname) . "]' value='" . attr($hvalue) . "' />\n";
                    }
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                echo "</table>\n";
                echo "</center>\n";
                echo "</div>\n";
            } // End Products Section

            if (isset($LBF_DIAGS_SECTION)) {
                // Create the checkbox and div for the Diagnoses Section.
                echo "<br /><span class='bold'><input type='checkbox' name='form_cb_fs_diags' value='1' " .
                    "onclick='return divclick(this, \"div_fs_diags\");'";
                if ($display_style == 'block') {
                    echo " checked";
                }
                echo " /><b>" . xlt('Diagnoses') . "</b></span>\n";
                echo "<div id='div_fs_diags' class='section' style='display:" . attr($display_style) . ";'>\n";
                echo "<center>\n";
                $display_style = 'none';

                // If there are associated codes, generate a checkbox for each one.
                if ($LBF_DIAGS_SECTION) {
                    echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
                    $cols = 3;
                    $tdpct = (int)(100 / $cols);
                    $count = 0;
                    $relcodes = explode(';', $LBF_DIAGS_SECTION);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        $codes_esc = attr($codestring);
                        $cbval = $fs->genCodeSelectorValue($codestring);
                        if ($count % $cols == 0) {
                            if ($count) {
                                echo " </tr>\n";
                            }
                            echo " <tr>\n";
                        }
                        echo "  <td width='" . attr($tdpct) . "%'>";
                        echo "<input type='checkbox' id='form_fs_diags[$codes_esc]' " .
                            "onclick='fs_diag_clicked(this)' value='" . attr($cbval) . "'";
                        if ($fs->code_is_in_fee_sheet) {
                            echo " checked";
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $title = lookup_code_descriptions($codestring);
                        $title = empty($title) ? $code : xl_list_label($title);
                        echo " />" . text($title);
                        echo "</td>\n";
                        ++$count;
                    }
                    if ($count) {
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                }

                // A row for Search.
                $ctype = collect_codetypes('diagnosis', 'csv');
                echo "<p class='bold'>";
                echo "<input type='button' value='" . xla('Search Diagnoses') . "' onclick='sel_related(null," . attr_js($ctype) . ")' />";
                echo "</p>\n";

                // Generate a line for each diagnosis already in this FS.
                echo "<table cellpadding='0' cellspacing='2' id='fs_diags_table'>\n";
                echo " <tr>\n";
                echo "  <td class='bold' colspan='2'>" . xlt('Diagnosis') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Delete') . "</td>\n";
                echo " </tr>\n";
                foreach ($fs->serviceitems as $lino => $li) {
                    // Skip anything that is not a diagnosis; those are in the Services section above.
                    if (!$code_types[$li['codetype']]['diag']) {
                        continue;
                    }
                    echo " <tr>\n";
                    echo "  <td class='text'>" . text($li['code']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
                    // The Diagnoses section shares the form_fs_bill array with the Services section.
                    echo "  <td class='text' align='right'>\n" .
                        "   <input type='checkbox' name='form_fs_bill[" . attr($lino) . "][del]' " .
                        "value='1'" . ($li['del'] ? " checked" : "") . " />\n";
                    foreach ($li['hidden'] as $hname => $hvalue) {
                        echo "   <input type='hidden' name='form_fs_bill[" . attr($lino) . "][" . attr($hname) . "]' value='" . attr($hvalue) . "' />\n";
                    }
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                echo "</table>\n";
                echo "</center>\n";
                echo "</div>\n";
            } // End Diagnoses Section

            ?>
            <br />

            <div class="col-12">
                <div class="btn-group">

                    <?php
                    if (!$from_trend_form && !$from_lbf_edit) {
                        // Generate price level selector if we are doing services or products.
                        if (isset($LBF_SERVICES_SECTION) || isset($LBF_PRODUCTS_SECTION)) {
                            echo xlt('Price Level') . ": ";
                            echo $fs->generatePriceLevelSelector('form_fs_pricelevel');
                            echo "&nbsp;&nbsp;";
                        }
                        ?>
                        <button type="submit" class="btn btn-secondary btn-save" name="bn_save"
                                value="<?php echo xla('Save'); ?>">
                            <?php echo xlt('Save'); ?>
                        </button>

                        &nbsp;
                        <button type='submit' class="btn btn-link" name='bn_save_continue'
                                value='<?php echo xla('Save and Continue') ?>'>
                            <?php echo xlt('Save and Continue'); ?>
                        </button>
                        <?php
                        if (!$from_issue_form) {
                            ?>
                            &nbsp;
                            <button type='submit' class="btn btn-link" name='bn_save_print'
                                    value='<?php echo xla('Save and Print') ?>'>
                                <?php echo xlt('Save and Print'); ?>
                            </button>
                            <?php
                            if (function_exists($formname . '_additional_buttons')) {
                                // Allow the plug-in to insert more action buttons here.
                                call_user_func($formname . '_additional_buttons');
                            }

                            if ($form_is_graphable) {
                                ?>
                                <button type='button' class="btn btn-secondary btn-graph"
                                        onclick="top.restoreSession();location='../../patient_file/encounter/trend_form.php?formname=<?php echo attr_url($formname); ?>'">
                                    <?php echo xlt('Show Graph') ?>
                                </button>
                                &nbsp;
                                <?php
                            } // end form is graphable
                            ?>
                            <button type='button' class="btn btn-link btn-cancel" onclick="verifyCancel()">
                                <?php echo xlt('Cancel'); ?>
                            </button>
                            <?php
                        } // end not from issue form
                        ?>
                        <?php
                    } elseif (!$from_lbf_edit) { // $from_trend_form is true but lbf edit doesn't want button
                        ?>
                        <button type='button' class="btn btn-secondary btn-back" onclick='window.history.back();'>
                            <?php echo xlt('Back') ?>
                        </button>
                        <?php
                    } // end from trend form
                    ?>
                </div>
                <hr>
            </div>
            <?php if (!$from_trend_form) { // end row and container divs ?>
        </div>
    </div>
<?php } ?>

    <input type='hidden' name='from_issue_form' value='<?php echo attr($from_issue_form); ?>'/>

    </form>

    <!-- include support for the list-add selectbox feature -->
    <?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>

    <script>

        // Array of action conditions for the checkSkipConditions() function.
        var skipArray = [
            <?php echo $condition_str; ?>
        ];

        <?php echo $date_init; ?>
        <?php
        if (function_exists($formname . '_javascript_onload')) {
            call_user_func($formname . '_javascript_onload');
        }

        if ($alertmsg) {
            echo "alert(" . js_escape($alertmsg) . ");\n";
        }
        ?>
    </script>
</div>
</body>
</html>
