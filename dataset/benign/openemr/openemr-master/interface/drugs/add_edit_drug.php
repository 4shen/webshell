<?php

 // Copyright (C) 2006-2017 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("drugs.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$alertmsg = '';
$drug_id = $_REQUEST['drug'];
$info_msg = "";
$tmpl_line_no = 0;

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    die(xlt('Not authorized'));
}

// Write a line of data for one template to the form.
//
function writeTemplateLine($selector, $dosage, $period, $quantity, $refills, $prices, $taxrates)
{
    global $tmpl_line_no;
    ++$tmpl_line_no;

    echo " <tr>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][selector]' value='" . attr($selector) . "' size='8' maxlength='100'>";
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][dosage]' value='" . attr($dosage) . "' size='6' maxlength='10'>";
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    generate_form_field(array(
    'data_type'   => 1,
    'field_id'    => 'tmpl[' . attr($tmpl_line_no) . '][period]',
    'list_id'     => 'drug_interval',
    'empty_title' => 'SKIP'
    ), $period);
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][quantity]' value='" . attr($quantity) . "' size='3' maxlength='7'>";
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][refills]' value='" . attr($refills) . "' size='3' maxlength='5'>";
    echo "</td>\n";
    foreach ($prices as $pricelevel => $price) {
        echo "  <td class='tmplcell'>";
        echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][price][" . attr($pricelevel) . "]' value='" . attr($price) . "' size='6' maxlength='12'>";
        echo "</td>\n";
    }

    $pres = sqlStatement("SELECT option_id FROM list_options " .
    "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
    while ($prow = sqlFetchArray($pres)) {
        echo "  <td class='tmplcell'>";
        echo "<input type='checkbox' name='form_tmpl[" . attr($tmpl_line_no) . "][taxrate][" . attr($prow['option_id']) . "]' value='1'";
        if (strpos(":$taxrates", $prow['option_id']) !== false) {
            echo " checked";
        }

        echo " /></td>\n";
    }

    echo " </tr>\n";
}
?>
<html>
<head>
<title><?php echo $drug_id ? xlt("Edit") : xlt("Add New");
echo ' ' . xlt('Drug'); ?></title>

<?php Header::setupHeader(["jquery-ui","opener"]); ?>

<style>
td {
    font-size: 0.8125rem;
}

<?php if ($GLOBALS['sell_non_drug_products'] == 2) { ?>
.drugsonly {
    display: none;
}
<?php } ?>

<?php if (empty($GLOBALS['ippf_specific'])) { ?>
.ippfonly {
    display: none;
}
<?php } ?>

</style>

<script>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.form_related_code.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.form_related_code.value = s;
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
 return document.forms[0].form_related_code.value.split(';');
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
 my_del_related(s, document.forms[0].form_related_code, false);
}

// This invokes the find-code popup.
function sel_related() {
 dlgopen('../patient_file/encounter/find_code_dynamic.php', '_blank', 900, 800);
}

</script>

</head>

<body class="body_top">
<?php
// If we are saving, then save and close the window.
// First check for duplicates.
//
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $drugName = trim($_POST['form_name']);
    if ($drugName === '') {
        $alertmsg = xl('Drug name is required');
    } else {
        $crow = sqlQuery(
            "SELECT COUNT(*) AS count FROM drugs WHERE " .
            "name = ? AND " .
            "form = ? AND " .
            "size = ? AND " .
            "unit = ? AND " .
            "route = ? AND " .
            "drug_id != ?",
            array(
                trim($_POST['form_name']),
                trim($_POST['form_form']),
                trim($_POST['form_size']),
                trim($_POST['form_unit']),
                trim($_POST['form_route']),
                $drug_id
            )
        );
        if ($crow['count']) {
            $alertmsg = xl('Cannot add this entry because it already exists!');
        }
    }
}

if (($_POST['form_save'] || $_POST['form_delete']) && !$alertmsg) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $new_drug = false;
    if ($drug_id) {
        if ($_POST['form_save']) { // updating an existing drug
            sqlStatement(
                "UPDATE drugs SET " .
                "name = ?, " .
                "ndc_number = ?, " .
                "drug_code = ?, " .
                "on_order = ?, " .
                "reorder_point = ?, " .
                "max_level = ?, " .
                "form = ?, " .
                "size = ?, " .
                "unit = ?, " .
                "route = ?, " .
                "cyp_factor = ?, " .
                "related_code = ?, " .
                "allow_multiple = ?, " .
                "allow_combining = ?, " .
                "active = ? " .
                "WHERE drug_id = ?",
                array(
                    trim($_POST['form_name']),
                    trim($_POST['form_ndc_number']),
                    trim($_POST['form_drug_code']),
                    trim($_POST['form_on_order']),
                    trim($_POST['form_reorder_point']),
                    trim($_POST['form_max_level']),
                    trim($_POST['form_form']),
                    trim($_POST['form_size']),
                    trim($_POST['form_unit']),
                    trim($_POST['form_route']),
                    trim($_POST['form_cyp_factor']),
                    trim($_POST['form_related_code']),
                    (empty($_POST['form_allow_multiple' ]) ? 0 : 1),
                    (empty($_POST['form_allow_combining']) ? 0 : 1),
                    (empty($_POST['form_active']) ? 0 : 1),
                    $drug_id
                )
            );
            sqlStatement("DELETE FROM drug_templates WHERE drug_id = ?", array($drug_id));
        } else { // deleting
            if (AclMain::aclCheckCore('admin', 'super')) {
                sqlStatement("DELETE FROM drug_inventory WHERE drug_id = ?", array($drug_id));
                sqlStatement("DELETE FROM drug_templates WHERE drug_id = ?", array($drug_id));
                sqlStatement("DELETE FROM drugs WHERE drug_id = ?", array($drug_id));
                sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector != ''", array($drug_id));
            }
        }
    } elseif ($_POST['form_save']) { // saving a new drug
        $new_drug = true;
        $drug_id = sqlInsert(
            "INSERT INTO drugs ( " .
            "name, ndc_number, drug_code, on_order, reorder_point, max_level, form, " .
            "size, unit, route, cyp_factor, related_code, " .
            "allow_multiple, allow_combining, active " .
            ") VALUES ( " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?)",
            array(
                trim($_POST['form_name']),
                trim($_POST['form_ndc_number']),
                trim($_POST['form_drug_code']),
                trim($_POST['form_on_order']),
                trim($_POST['form_reorder_point']),
                trim($_POST['form_max_level']),
                trim($_POST['form_form']),
                trim($_POST['form_size']),
                trim($_POST['form_unit']),
                trim($_POST['form_route']),
                trim($_POST['form_cyp_factor']),
                trim($_POST['form_related_code']),
                (empty($_POST['form_allow_multiple' ]) ? 0 : 1),
                (empty($_POST['form_allow_combining']) ? 0 : 1),
                (empty($_POST['form_active']) ? 0 : 1)
            )
        );
    }

    if ($_POST['form_save'] && $drug_id) {
        $tmpl = $_POST['form_tmpl'];
       // If using the simplified drug form, then force the one and only
       // selector name to be the same as the product name.
        if ($GLOBALS['sell_non_drug_products'] == 2) {
            $tmpl["1"]['selector'] = $_POST['form_name'];
        }

        sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector != ''", array($drug_id));
        for ($lino = 1; isset($tmpl["$lino"]['selector']); ++$lino) {
            $iter = $tmpl["$lino"];
            $selector = trim($iter['selector']);
            if ($selector) {
                $taxrates = "";
                if (!empty($iter['taxrate'])) {
                    foreach ($iter['taxrate'] as $key => $value) {
                        $taxrates .= "$key:";
                    }
                }

                sqlStatement(
                    "INSERT INTO drug_templates ( " .
                    "drug_id, selector, dosage, period, quantity, refills, taxrates " .
                    ") VALUES ( ?, ?, ?, ?, ?, ?, ? )",
                    array($drug_id, $selector, trim($iter['dosage']), trim($iter['period']),
                    trim($iter['quantity']),
                    trim($iter['refills']),
                    $taxrates)
                );

                // Add prices for this drug ID and selector.
                foreach ($iter['price'] as $key => $value) {
                         $value = $value + 0;
                    if ($value) {
                         sqlStatement(
                             "INSERT INTO prices ( " .
                             "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
                             "?, ?, ?, ? )",
                             array($drug_id, $selector, $key, $value)
                         );
                    }
                } // end foreach price
            } // end if selector is present
        } // end for each selector
       // Save warehouse-specific mins and maxes for this drug.
        sqlStatement("DELETE FROM product_warehouse WHERE pw_drug_id = ?", array($drug_id));
        foreach ($_POST['form_wh_min'] as $whid => $whmin) {
            $whmin = 0 + $whmin;
            $whmax = 0 + $_POST['form_wh_max'][$whid];
            if ($whmin != 0 || $whmax != 0) {
                sqlStatement("INSERT INTO product_warehouse ( " .
                "pw_drug_id, pw_warehouse, pw_min_level, pw_max_level ) VALUES ( " .
                "?, ?, ?, ? )", array($drug_id, $whid, $whmin, $whmax));
            }
        }
    } // end if saving a drug

  // Close this window and redisplay the updated list of drugs.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert('" . addslashes($info_msg) . "');\n";
    }

    echo " if (opener.refreshme) opener.refreshme();\n";
    if ($new_drug) {
        echo " window.location.href='add_edit_lot.php?drug=" . attr_url($drug_id) . "&lot=0'\n";
    } else {
        echo " window.close();\n";
    }

    echo "</script></body></html>\n";
    exit();
}

if ($drug_id) {
    $row = sqlQuery("SELECT * FROM drugs WHERE drug_id = ?", array($drug_id));
    $tres = sqlStatement("SELECT * FROM drug_templates WHERE " .
    "drug_id = ? ORDER BY selector", array($drug_id));
} else {
    $row = array(
    'name' => '',
    'active' => '1',
    'allow_multiple' => '1',
    'allow_combining' => '',
    'ndc_number' => '',
    'on_order' => '0',
    'reorder_point' => '0',
    'max_level' => '0',
    'form' => '',
    'size' => '',
    'unit' => '',
    'route' => '',
    'cyp_factor' => '',
    'related_code' => '',
    );
}
$title = $drug_id ? xl("Update Drug") : xl("Add Drug");
?>
<h3 class="ml-1"><?php echo text($title);?></h3>
<form class="form" method='post' name='theform' action='add_edit_drug.php?drug=<?php echo attr_url($drug_id); ?>'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<center>

<table class="table table-borderless w-100">

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Name'); ?>:</td>
  <td>
   <input class="form-control w-100" size='40' name='form_name' maxlength='80' value='<?php echo attr($row['name']) ?>' />
  </td>
 </tr>

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Active{{Drug}}'); ?>:</td>
  <td>
   <input type='checkbox' name='form_active' value='1'<?php
    if ($row['active']) {
            echo ' checked';
    } ?> />
  </td>
 </tr>

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Allow'); ?>:</td>
  <td>
   <input type='checkbox' name='form_allow_multiple' value='1'<?php
    if ($row['allow_multiple']) {
        echo ' checked';
    } ?> />
    <?php echo xlt('Multiple Lots'); ?> &nbsp;
   <input type='checkbox' name='form_allow_combining' value='1'<?php
    if ($row['allow_combining']) {
        echo ' checked';
    } ?> />
    <?php echo xlt('Combining Lots'); ?>
  </td>
 </tr>

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('NDC Number'); ?>:</td>
  <td>
   <input class="form-control w-100" size='40' name='form_ndc_number' maxlength='20' value='<?php echo attr($row['ndc_number']) ?>' onkeyup='maskkeyup(this,"<?php echo attr(addslashes($GLOBALS['gbl_mask_product_id'])); ?>")' onblur='maskblur(this,"<?php echo attr(addslashes($GLOBALS['gbl_mask_product_id'])); ?>")' />
  </td>
 </tr>
<tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Drug Code'); ?>:</td>
  <td>
   <input class="form-control" size='5' name='form_drug_code' maxlength='10'
    value='<?php echo attr($row['drug_code']) ?>' />
  </td>
</tr>
 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('On Order'); ?>:</td>
  <td>
   <input class="form-control" size='5' name='form_on_order' maxlength='7' value='<?php echo attr($row['on_order']) ?>' />
  </td>
 </tr>

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Limits'); ?>:</td>
  <td>
   <table>
    <tr>
     <td class="align-top text-nowrap">&nbsp;</td>
     <td class="align-top text-nowrap"><?php echo xlt('Global'); ?></td>
<?php
// One column header per warehouse title.
$pwarr = array();
$pwres = sqlStatement(
    "SELECT lo.option_id, lo.title, " .
    "pw.pw_min_level, pw.pw_max_level " .
    "FROM list_options AS lo " .
    "LEFT JOIN product_warehouse AS pw ON " .
    "pw.pw_drug_id = ? AND " .
    "pw.pw_warehouse = lo.option_id WHERE " .
    "lo.list_id = 'warehouse' AND lo.activity = 1 ORDER BY lo.seq, lo.title",
    array($drug_id)
);
while ($pwrow = sqlFetchArray($pwres)) {
    $pwarr[] = $pwrow;
    echo "     <td class='align-top text-nowrap'>" . text($pwrow['title']) . "</td>\n";
}
?>
    </tr>
    <tr>
     <td class="align-top text-nowrap"><?php echo xlt('Min'); ?>&nbsp;</td>
     <td class="align-top">
      <input class="form-control" size='5' name='form_reorder_point' maxlength='7' value='<?php echo attr($row['reorder_point']) ?>' title='<?php echo xla('Reorder point, 0 if not applicable'); ?>' />&nbsp;&nbsp;
     </td>
<?php
foreach ($pwarr as $pwrow) {
    echo "     <td class='align-top'>";
    echo "<input class='form-control' name='form_wh_min[" .
    attr($pwrow['option_id']) .
    "]' value='" . attr(0 + $pwrow['pw_min_level']) . "' size='5' " .
    "title='" . xla('Warehouse minimum, 0 if not applicable') . "' />";
    echo "&nbsp;&nbsp;</td>\n";
}
?>
    </tr>
    <tr>
     <td class="align-top text-nowrap"><?php echo xlt('Max'); ?>&nbsp;</td>
     <td>
      <input class='form-control' size='5' name='form_max_level' maxlength='7' value='<?php echo attr($row['max_level']) ?>' title='<?php echo xla('Maximum reasonable inventory, 0 if not applicable'); ?>' />
     </td>
<?php
foreach ($pwarr as $pwrow) {
    echo "     <td class='align-top'>";
    echo "<input class='form-control' name='form_wh_max[" .
    attr($pwrow['option_id']) .
    "]' value='" . attr(0 + $pwrow['pw_max_level']) . "' size='5' " .
    "title='" . xla('Warehouse maximum, 0 if not applicable') . "' />";
    echo "</td>\n";
}
?>
    </tr>
   </table>
  </td>
 </tr>

 <tr class='drugsonly'>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Form'); ?>:</td>
  <td>
<?php
 generate_form_field(array('data_type' => 1,'field_id' => 'form','list_id' => 'drug_form','empty_title' => 'SKIP'), $row['form']);
?>
  </td>
 </tr>

 <tr class='drugsonly'>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Pill Size'); ?>:</td>
  <td>
   <input class="form-control" size='5' name='form_size' maxlength='7' value='<?php echo attr($row['size']) ?>' />
  </td>
 </tr>

 <tr class='drugsonly'>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Units'); ?>:</td>
  <td>
<?php
 generate_form_field(array('data_type' => 1,'field_id' => 'unit','list_id' => 'drug_units','empty_title' => 'SKIP'), $row['unit']);
?>
  </td>
 </tr>

 <tr class='drugsonly'>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Route'); ?>:</td>
  <td>
<?php
 generate_form_field(array('data_type' => 1,'field_id' => 'route','list_id' => 'drug_route','empty_title' => 'SKIP'), $row['route']);
?>
  </td>
 </tr>

 <tr class='ippfonly'>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('CYP Factor'); ?>:</td>
  <td>
   <input class="form-control" size='10' name='form_cyp_factor' maxlength='20' value='<?php echo attr($row['cyp_factor']) ?>' />
  </td>
 </tr>

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo xlt('Relate To'); ?>:</td>
  <td>
   <input class="form-control w-100" type='text' size='50' name='form_related_code' value='<?php echo attr($row['related_code']) ?>' onclick='sel_related()' title='<?php echo xla('Click to select related code'); ?>' readonly />
  </td>
 </tr>

 <tr>
  <td class="align-top text-nowrap font-weight-bold"><?php echo $GLOBALS['sell_non_drug_products'] == 2 ? xlt('Fees') : xlt('Templates'); ?>:</td>
  <td>
   <table class='border-0 w-100'>
    <tr>
     <td class='drugsonly font-weight-bold'><?php echo xlt('Name'); ?></td>
     <td class='drugsonly font-weight-bold'><?php echo xlt('Schedule'); ?></td>
     <td class='drugsonly font-weight-bold'><?php echo xlt('Interval'); ?></td>
     <td class='drugsonly font-weight-bold'><?php echo xlt('Qty'); ?></td>
     <td class='drugsonly font-weight-bold'><?php echo xlt('Refills'); ?></td>
<?php
// Show a heading for each price level.  Also create an array of prices
// for new template lines.
$emptyPrices = array();
$pres = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
    $emptyPrices[$prow['option_id']] = '';
    echo "     <td class='font-weight-bold'>" .
    generate_display_field(array('data_type' => '1','list_id' => 'pricelevel'), $prow['option_id']) .
    "</td>\n";
}

// Show a heading for each tax rate.
$pres = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
    echo "     <td class='font-weight-bold'>" .
        generate_display_field(array('data_type' => '1','list_id' => 'taxrate'), $prow['option_id']) .
        "</td>\n";
}
?>
    </tr>
<?php
  $blank_lines = $GLOBALS['sell_non_drug_products'] == 2 ? 1 : 3;
if ($tres) {
    while ($trow = sqlFetchArray($tres)) {
        $blank_lines = $GLOBALS['sell_non_drug_products'] == 2 ? 0 : 1;
        $selector = $trow['selector'];
      // Get array of prices.
        $prices = array();
        $pres = sqlStatement(
            "SELECT lo.option_id, p.pr_price " .
            "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
            "p.pr_id = ? AND p.pr_selector = ? AND " .
            "p.pr_level = lo.option_id " .
            "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq",
            array($drug_id, $selector)
        );
        while ($prow = sqlFetchArray($pres)) {
            $prices[$prow['option_id']] = $prow['pr_price'];
        }

        writeTemplateLine(
            $selector,
            $trow['dosage'],
            $trow['period'],
            $trow['quantity'],
            $trow['refills'],
            $prices,
            $trow['taxrates']
        );
    }
}

for ($i = 0; $i < $blank_lines; ++$i) {
    $selector = $GLOBALS['sell_non_drug_products'] == 2 ? $row['name'] : '';
    writeTemplateLine($selector, '', '', '', '', $emptyPrices, '');
}
?>
   </table>
  </td>
 </tr>

</table>
<div class="btn-group">
<input type='submit' class="btn btn-primary" name='form_save' value='<?php echo  $drug_id ? xla('Update') : xla('Add') ; ?>' />

<?php if (AclMain::aclCheckCore('admin', 'super') && $drug_id) { ?>
<input class="btn btn-danger" type='submit' name='form_delete' value='<?php echo xla('Delete'); ?>' />
<?php } ?>
<input type='button' class="btn btn-secondary" value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</div>

</center>
</form>

<script>
<?php
if ($alertmsg) {
    echo "alert('" . addslashes($alertmsg) . "');\n";
}
?>
</script>

</body>
</html>
