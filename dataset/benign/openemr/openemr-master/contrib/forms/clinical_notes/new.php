<?php

/**
 * clinical_notes new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$row = array();

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname)
{
    $tmp = $_POST[$rbname];
    if (! $tmp) {
        $tmp = '0';
    }

    return "$tmp";
}

function cbvalue($cbname)
{
    return $_POST[$cbname] ? '1' : '0';
}

function rbinput($name, $value, $desc, $colname)
{
    global $row;
    $ret  = "<input type='radio' name='" . attr($name) . "' value='" . attr($value) . "'";
    if ($row[$colname] == $value) {
        $ret .= " checked";
    }

    $ret .= " />" . text($desc);
    return $ret;
}

function rbcell($name, $value, $desc, $colname)
{
    return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbinput($name, $colname)
{
    global $row;
    $ret  = "<input type='checkbox' name='" . attr($name) . "' value='1'";
    if ($row[$colname]) {
        $ret .= " checked";
    }

    $ret .= " />";
    return $ret;
}

function cbcell($name, $desc, $colname)
{
    return "<td width='25%' nowrap>" . cbinput($name, $colname) . text($desc) . "</td>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
    $fu_timing = $_POST['fu_timing'];
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

 // If updating an existing form...
 //
    if ($formid) {
        $query = "UPDATE form_clinical_notes SET
         history = ?, 
         examination = ?,      
         plan = ?,           
         followup_required = ?,
         followup_timing = ?,                  
         WHERE id = ?";

        sqlStatement($query, array($_POST['form_history'], $_POST['form_examination'], $_POST['form_plan'], rbvalue('fu_required'), $fu_timing, $formid));
    } else { // If adding a new form...
        $query = "INSERT INTO form_clinical_notes ( " .
         "history, examination, plan, followup_required, followup_timing 
         ) VALUES ( ?, ?, ?, ?, ? )";

        $newid = sqlInsert($query, array($_POST['form_history'], $_POST['form_examination'], $_POST['form_plan'], rbvalue('fu_required'), $fu_timing));
        addForm($encounter, "Clinical Notes", $newid, "clinical_notes", $pid, $userauthorized);
    }

    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}

if ($formid) {
    $row = sqlQuery("SELECT * FROM form_clinical_notes WHERE " .
    "id = ? AND activity = '1'", array($formid));
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>

</head>

<body <?php echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/clinical_notes/new.php?id=<?php echo attr_url($formid) ?>"
 onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>This Encounter</b></td>
 </tr>

 <tr>
  <td width='5%'  nowrap> History </td>
  <td width='95%' nowrap>
   <textarea name='form_history' rows='7' style='width:100%'><?php echo text($row['history']) ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> Examination </td>
  <td nowrap>
   <textarea name='form_examination' rows='7' style='width:100%'><?php echo text($row['examination']) ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> Plan </td>
  <td nowrap>
   <textarea name='form_plan' rows='7' style='width:100%'><?php echo text($row['plan']) ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap>Follow Up</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='5%' nowrap>
        <?php echo rbinput('fu_required', '1', 'Required in:', 'followup_required') ?>
     </td>
     <td nowrap>
      <input type='text' name='fu_timing' size='10' style='width:100%'
       title='When to follow up'
       value='<?php echo attr($row['followup_timing']) ?>' />
     </td>
    </tr>
    <tr>
     <td colspan='2' nowrap>
        <?php echo rbinput('fu_required', '2', 'Pending investigation', 'followup_required') ?>
     </td>
    </tr>
    <tr>
     <td colspan='2' nowrap>
        <?php echo rbinput('fu_required', '0', 'None required', 'followup_required') ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="parent.closeTab(window.name, false)" />
</p>

</center>

</form>
</body>
</html>
