<?php

/**
 * find_code_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$info_msg = "";
$codetype = $_REQUEST['codetype'];
if (!empty($codetype)) {
    $allowed_codes = split_csv_line($codetype);
}

$form_code_type = $_POST['form_code_type'];

// Determine which code type will be selected by default.
$default = '';
if (!empty($form_code_type)) {
    $default = $form_code_type;
} elseif (!empty($allowed_codes) && count($allowed_codes) == 1) {
    $default = $allowed_codes[0];
} elseif (!empty($_REQUEST['default'])) {
    $default = $_REQUEST['default'];
}

// This variable is used to store the html element
// of the target script where the selected code
// will be stored in.
$target_element = $_GET['target_element'];
?>
<html>
<head>
<title><?php echo xlt('Code Finder'); ?></title>
<?php Header::setupHeader('opener'); ?>

<style>
td {
    font-size: 13px;
}
</style>

<script>

 // Standard function
 function selcode(codetype, code, selector, codedesc) {
  if (opener.closed || ! opener.set_related) {
   alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
  }
  else {
   var msg = opener.set_related(codetype, code, selector, codedesc);
   if (msg) alert(msg);
      dlgclose();
   return false;
  }
 }

 // TBD: The following function is not necessary. See
 // interface/forms/LBF/new.php for an alternative method that does not require it.
 // Rod 2014-04-15

 // Standard function with additional parameter to select which
 // element on the target page to place the selected code into.
 function selcode_target(codetype, code, selector, codedesc, target_element) {
  if (opener.closed || ! opener.set_related_target)
   alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
  else
   opener.set_related_target(codetype, code, selector, codedesc, target_element);
     dlgclose();
  return false;
 }

</script>

</head>
<?php
    $focus = "document.theform.search_term.select();";
?>
<body class="body_top" OnLoad="<?php echo $focus; ?>">

<?php
$string_target_element = "";
if (!empty($target_element)) {
    $string_target_element = "?target_element=" . attr_url($target_element) . "&";
} else {
    $string_target_element = "?";
}
?>
<?php if (!empty($allowed_codes)) { ?>
  <form method='post' name='theform' action='find_code_popup.php<?php echo $string_target_element ?>codetype=<?php echo attr_url($codetype) ?>'>
<?php } else { ?>
  <form method='post' name='theform' action='find_code_popup.php<?php echo $string_target_element ?>'>
<?php } ?>
  <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<table class='table-borderless' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1">
  </td>
 </tr>

 <tr class="head bg-light form-inline font-weight-bold">
  <td>
  <div class="form-group">
<?php
if (!empty($allowed_codes)) {
    if (count($allowed_codes) === 1) {
        echo "<input class='form-control' type='text' name='form_code_type' value='" . attr($codetype) . "' size='5' readonly />\n";
    } else {
        ?>
   <select class='form-control' name='form_code_type'>
        <?php
        foreach ($allowed_codes as $code) {
            $selected_attr = ($default == $code) ? " selected='selected'" : '';
            ?>
<option value='<?php echo attr($code) ?>'<?php echo $selected_attr ?>><?php echo xlt($code_types[$code]['label']) ?></option>
            <?php
        }
        ?>
   </select>
        <?php
    }
} else {
  // No allowed types were specified, so show all.
    echo "   <select class='form-control' name='form_code_type'";
    echo ">\n";
    foreach ($code_types as $key => $value) {
        echo "    <option value='" . attr($key) . "'";
        if ($default == $key) {
            echo " selected";
        }

        echo ">" . xlt($value['label']) . "</option>\n";
    }

    echo "    <option value='PROD'";
    if ($default == 'PROD') {
        echo " selected";
    }

    echo ">" . xlt("Product") . "</option>\n";
    echo "   </select>&nbsp;&nbsp;\n";
}
?>


   <label for="searchTerm" class="mt-3"><?php echo xlt('Search for:'); ?></label>
   <input type='text' class='form-control' name='search_term' size='12' id="searchTerm" value='<?php echo attr($_REQUEST['search_term']); ?>' title='<?php echo xla('Any part of the desired code or its description'); ?>' />

   <center>
   <input type='submit' class='btn btn-primary mt-3' name='bn_search' value='<?php echo xla('Search'); ?>' />

    <?php if (!empty($target_element)) { ?>
     <input type='button' class='btn btn-primary mt-3' value='<?php echo xla('Erase'); ?>' onclick="selcode_target('', '', '', '', <?php echo attr_js($target_element); ?>)" />
    <?php } else { ?>
     <input type='button' class='btn btn-danger mt-3' value='<?php echo xla('Erase'); ?>' onclick="selcode('', '', '', '')" />
    <?php } ?>

    </center>
    </div>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php
if ($_REQUEST['bn_search'] || $_REQUEST['search_term']) {
    if (!$form_code_type) {
        $form_code_type = $codetype;
    }
    ?>

<table class='border-0'>
<tr>
<td class='font-weight-bold'><?php echo xlt('Code'); ?></td>
<td class='font-weight-bold'><?php echo xlt('Description'); ?></td>
</tr>
    <?php
    $search_term = $_REQUEST['search_term'];
    $res = main_code_set_search($form_code_type, $search_term);
    if ($form_code_type == 'PROD') { // Special case that displays search for products/drugs
        while ($row = sqlFetchArray($res)) {
            $drug_id = $row['drug_id'];
            $selector = $row['selector'];
            $desc = $row['name'];
            $anchor = "<a href='' " .
            "onclick='return selcode(\"PROD\", " . attr_js($drug_id) . ", " . attr_js($selector) . ", " . attr_js($desc) . ")'>";
            echo " <tr>";
            echo "  <td>$anchor" . text($drug_id . ":" . $selector) . "</a></td>\n";
            echo "  <td>$anchor" . text($desc) . "</a></td>\n";
            echo " </tr>";
        }
    } else {
        while ($row = sqlFetchArray($res)) { // Display normal search
            $itercode = $row['code'];
            $itertext = trim($row['code_text']);
            if (!empty($target_element)) {
                // add a 5th parameter to function to select the target element on the form for placing the code.
                $anchor = "<a href='' " .
                "onclick='return selcode_target(" . attr_js($form_code_type) . ", " . attr_js($itercode) . ", \"\", " . attr_js($itertext) . ", " . attr_js($target_element) . ")'>";
            } else {
                $anchor = "<a href='' " .
                "onclick='return selcode(" . attr_js($form_code_type) . ", " . attr_js($itercode) . ", \"\", " . attr_js($itertext) . ")'>";
            }

            echo " <tr>";
            echo "  <td>$anchor" . text($itercode) . "</a></td>\n";
            echo "  <td>$anchor" . text($itertext) . "</a></td>\n";
            echo " </tr>";
        }
    }
    ?>
</table>

<?php } ?>

</center>
</form>
</body>
</html>
