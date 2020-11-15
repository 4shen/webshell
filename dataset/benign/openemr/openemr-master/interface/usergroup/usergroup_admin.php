<?php

/**
 * This script Assign acl 'Emergency login'.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once("../globals.php");
require_once("$srcdir/auth.inc");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\UserService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'users')) {
    die(xlt('Access denied'));
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    //block non-administrator user from create administrator
    foreach ($_POST['access_group'] as $aro_group) {
        if (AclExtended::isGroupIncludeSuperuser($aro_group)) {
            die(xlt('Saving denied'));
        };
    }
    if ($_POST['mode'] === 'update') {
        //block non-administrator user from update administrator
        $user_service = new UserService();
        $user = $user_service->getUser($_POST['id']);
        $aro_groups = AclExtended::aclGetGroupTitles($user->getUsername());
        foreach ($aro_groups as $aro_group) {
            if (AclExtended::isGroupIncludeSuperuser($aro_group)) {
                die(xlt('Saving denied'));
            };
        }
    }
}

$alertmsg = '';
$bg_msg = '';
$set_active_msg = 0;
$show_message = 0;

/* Sending a mail to the admin when the breakglass user is activated only if $GLOBALS['Emergency_Login_email'] is set to 1 */
if (is_array($_POST['access_group'])) {
    $bg_count = count($_POST['access_group']);
    $mail_id = explode(".", $SMTP_HOST);
    for ($i = 0; $i < $bg_count; $i++) {
        if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['active'] == 'on') && ($_POST['pre_active'] == 0)) {
            if (($_POST['get_admin_id'] == 1) && ($_POST['admin_id'] != "")) {
                $res = sqlStatement("select username from users where id= ? ", array($_POST["id"]));
                $row = sqlFetchArray($res);
                $uname = $row['username'];
                $mail = new MyMailer();
                $mail->From = $GLOBALS["practice_return_email_path"];
                $mail->FromName = "Administrator OpenEMR";
                $text_body = "Hello Security Admin,\n\n The Emergency Login user " . $uname .
                    " was activated at " . date('l jS \of F Y h:i:s A') . " \n\nThanks,\nAdmin OpenEMR.";
                $mail->Body = $text_body;
                $mail->Subject = "Emergency Login User Activated";
                $mail->AddAddress($_POST['admin_id']);
                $mail->Send();
            }
        }
    }
}

/* To refresh and save variables in mail frame */
if (isset($_POST["privatemode"]) && $_POST["privatemode"] == "user_admin") {
    if ($_POST["mode"] == "update") {
        if (isset($_POST["username"])) {
            $user_data = sqlFetchArray(sqlStatement("select * from users where id= ? ", array($_POST["id"])));
            sqlStatement("update users set username=? where id= ? ", array(trim($_POST["username"]), $_POST["id"]));
            sqlStatement("update `groups` set user=? where user= ?", array(trim($_POST["username"]), $user_data["username"]));
        }

        if ($_POST["taxid"]) {
            sqlStatement("update users set federaltaxid=? where id= ? ", array($_POST["taxid"], $_POST["id"]));
        }

        if ($_POST["state_license_number"]) {
            sqlStatement("update users set state_license_number=? where id= ? ", array($_POST["state_license_number"], $_POST["id"]));
        }

        if ($_POST["drugid"]) {
            sqlStatement("update users set federaldrugid=? where id= ? ", array($_POST["drugid"], $_POST["id"]));
        }

        if ($_POST["upin"]) {
            sqlStatement("update users set upin=? where id= ? ", array($_POST["upin"], $_POST["id"]));
        }

        if ($_POST["npi"]) {
            sqlStatement("update users set npi=? where id= ? ", array($_POST["npi"], $_POST["id"]));
        }

        if ($_POST["taxonomy"]) {
            sqlStatement("update users set taxonomy = ? where id= ? ", array($_POST["taxonomy"], $_POST["id"]));
        }

        if ($_POST["lname"]) {
            sqlStatement("update users set lname=? where id= ? ", array($_POST["lname"], $_POST["id"]));
        }

        if ($_POST["job"]) {
            sqlStatement("update users set specialty=? where id= ? ", array($_POST["job"], $_POST["id"]));
        }

        if ($_POST["mname"]) {
            sqlStatement("update users set mname=? where id= ? ", array($_POST["mname"], $_POST["id"]));
        }

        if ($_POST["facility_id"]) {
            sqlStatement("update users set facility_id = ? where id = ? ", array($_POST["facility_id"], $_POST["id"]));
            //(CHEMED) Update facility name when changing the id
            sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = ? AND users.id = ?", array($_POST["facility_id"], $_POST["id"]));
            //END (CHEMED)
        }

        if ($GLOBALS['restrict_user_facility'] && $_POST["schedule_facility"]) {
            sqlStatement("delete from users_facility
            where tablename='users'
            and table_id= ?
            and facility_id not in (" . add_escape_custom(implode(",", $_POST['schedule_facility'])) . ")", array($_POST["id"]));
            foreach ($_POST["schedule_facility"] as $tqvar) {
                sqlStatement("replace into users_facility set
                facility_id = ?,
                tablename='users',
                table_id = ?", array($tqvar, $_POST["id"]));
            }
        }

        if ($_POST["fname"]) {
            sqlStatement("update users set fname=? where id= ? ", array($_POST["fname"], $_POST["id"]));
        }

        if (isset($_POST['default_warehouse'])) {
            sqlStatement("UPDATE users SET default_warehouse = ? WHERE id = ?", array($_POST['default_warehouse'], $_POST["id"]));
        }

        if (isset($_POST['irnpool'])) {
            sqlStatement("UPDATE users SET irnpool = ? WHERE id = ?", array($_POST['irnpool'], $_POST["id"]));
        }

        if (!empty($_POST['clear_2fa'])) {
            sqlStatement("DELETE FROM login_mfa_registrations WHERE user_id = ?", array($_POST['id']));
        }

        if ($_POST["adminPass"] && $_POST["clearPass"]) {
            $authUtilsUpdatePassword = new AuthUtils();
            $success = $authUtilsUpdatePassword->updatePassword($_SESSION['authUserID'], $_POST['id'], $_POST['adminPass'], $_POST['clearPass']);
            if (!$success) {
                error_log(errorLogEscape($authUtilsUpdatePassword->getErrorMessage()));
                $alertmsg .= $authUtilsUpdatePassword->getErrorMessage();
            }
        }

        $tqvar  = $_POST["authorized"] ? 1 : 0;
        $actvar = $_POST["active"]     ? 1 : 0;
        $calvar = $_POST["calendar"]   ? 1 : 0;
        $portalvar = $_POST["portal_user"] ? 1 : 0;

        sqlStatement("UPDATE users SET authorized = ?, active = ?, " .
        "calendar = ?, portal_user = ?, see_auth = ? WHERE " .
        "id = ? ", array($tqvar, $actvar, $calvar, $portalvar, $_POST['see_auth'], $_POST["id"]));
      //Display message when Emergency Login user was activated
        $bg_count = count($_POST['access_group']);
        for ($i = 0; $i < $bg_count; $i++) {
            if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['pre_active'] == 0) && ($actvar == 1)) {
                $show_message = 1;
            }
        }

        if (($_POST['access_group'])) {
            for ($i = 0; $i < $bg_count; $i++) {
                if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['user_type']) == "" && ($_POST['check_acl'] == 1) && ($_POST['active']) != "") {
                    $set_active_msg = 1;
                }
            }
        }

        if ($_POST["comments"]) {
            sqlStatement("update users set info = ? where id = ? ", array($_POST["comments"], $_POST["id"]));
        }

        $erxrole = isset($_POST['erxrole']) ? $_POST['erxrole'] : '';
        sqlStatement("update users set newcrop_user_role = ? where id = ? ", array($erxrole, $_POST["id"]));

        if ($_POST["physician_type"]) {
            sqlStatement("update users set physician_type = ? where id = ? ", array($_POST["physician_type"], $_POST["id"]));
        }

        if ($_POST["main_menu_role"]) {
              $mainMenuRole = filter_input(INPUT_POST, 'main_menu_role');
              sqlStatement("update `users` set `main_menu_role` = ? where `id` = ? ", array($mainMenuRole, $_POST["id"]));
        }

        if ($_POST["patient_menu_role"]) {
            $patientMenuRole = filter_input(INPUT_POST, 'patient_menu_role');
            sqlStatement("update `users` set `patient_menu_role` = ? where `id` = ? ", array($patientMenuRole, $_POST["id"]));
        }

        if ($_POST["erxprid"]) {
            sqlStatement("update users set weno_prov_id = ? where id = ? ", array($_POST["erxprid"], $_POST["id"]));
        }

        if (isset($_POST["supervisor_id"])) {
            sqlStatement("update users set supervisor_id = ? where id = ? ", array((int)$_POST["supervisor_id"], $_POST["id"]));
        }

        // Set the access control group of user
        $user_data = sqlFetchArray(sqlStatement("select username from users where id= ?", array($_POST["id"])));
        AclExtended::setUserAro(
            $_POST['access_group'],
            $user_data["username"],
            (isset($_POST['fname']) ? $_POST['fname'] : ''),
            (isset($_POST['mname']) ? $_POST['mname'] : ''),
            (isset($_POST['lname']) ? $_POST['lname'] : '')
        );
    }
}

/* To refresh and save variables in mail frame  - Arb*/
if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "new_user") {
        if ($_POST["authorized"] != "1") {
            $_POST["authorized"] = 0;
        }

        $calvar = $_POST["calendar"] ? 1 : 0;
        $portalvar = $_POST["portal_user"] ? 1 : 0;

        $res = sqlStatement("select distinct username from users where username != ''");
        $doit = true;
        while ($row = sqlFetchArray($res)) {
            if ($doit == true && $row['username'] == trim($_POST['rumple'])) {
                $doit = false;
            }
        }

        if ($doit == true) {
            $insertUserSQL =
            "insert into users set " .
            "username = '"         . add_escape_custom(trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))) .
            "', password = '"      . 'NoLongerUsed'                  .
            "', fname = '"         . add_escape_custom(trim((isset($_POST['fname']) ? $_POST['fname'] : ''))) .
            "', mname = '"         . add_escape_custom(trim((isset($_POST['mname']) ? $_POST['mname'] : ''))) .
            "', lname = '"         . add_escape_custom(trim((isset($_POST['lname']) ? $_POST['lname'] : ''))) .
            "', federaltaxid = '"  . add_escape_custom(trim((isset($_POST['federaltaxid']) ? $_POST['federaltaxid'] : ''))) .
            "', state_license_number = '"  . add_escape_custom(trim((isset($_POST['state_license_number']) ? $_POST['state_license_number'] : ''))) .
            "', newcrop_user_role = '"  . add_escape_custom(trim((isset($_POST['erxrole']) ? $_POST['erxrole'] : ''))) .
            "', physician_type = '"  . add_escape_custom(trim((isset($_POST['physician_type']) ? $_POST['physician_type'] : ''))) .
            "', main_menu_role = '"  . add_escape_custom(trim((isset($_POST['main_menu_role']) ? $_POST['main_menu_role'] : ''))) .
            "', patient_menu_role = '"  . add_escape_custom(trim((isset($_POST['patient_menu_role']) ? $_POST['patient_menu_role'] : ''))) .
            "', weno_prov_id = '"  . add_escape_custom(trim((isset($_POST['erxprid']) ? $_POST['erxprid'] : ''))) .
            "', authorized = '"    . add_escape_custom(trim((isset($_POST['authorized']) ? $_POST['authorized'] : ''))) .
            "', info = '"          . add_escape_custom(trim((isset($_POST['info']) ? $_POST['info'] : ''))) .
            "', federaldrugid = '" . add_escape_custom(trim((isset($_POST['federaldrugid']) ? $_POST['federaldrugid'] : ''))) .
            "', upin = '"          . add_escape_custom(trim((isset($_POST['upin']) ? $_POST['upin'] : ''))) .
            "', npi  = '"          . add_escape_custom(trim((isset($_POST['npi']) ? $_POST['npi'] : ''))) .
            "', taxonomy = '"      . add_escape_custom(trim((isset($_POST['taxonomy']) ? $_POST['taxonomy'] : ''))) .
            "', facility_id = '"   . add_escape_custom(trim((isset($_POST['facility_id']) ? $_POST['facility_id'] : ''))) .
            "', specialty = '"     . add_escape_custom(trim((isset($_POST['specialty']) ? $_POST['specialty'] : ''))) .
            "', see_auth = '"      . add_escape_custom(trim((isset($_POST['see_auth']) ? $_POST['see_auth'] : ''))) .
            "', default_warehouse = '" . add_escape_custom(trim((isset($_POST['default_warehouse']) ? $_POST['default_warehouse'] : ''))) .
            "', irnpool = '"       . add_escape_custom(trim((isset($_POST['irnpool']) ? $_POST['irnpool'] : ''))) .
            "', calendar = '"      . add_escape_custom($calvar) .
            "', portal_user = '"   . add_escape_custom($portalvar) .
            "', supervisor_id = '" . add_escape_custom((isset($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : 0)) .
            "'";

            $authUtilsNewPassword = new AuthUtils();
            $success = $authUtilsNewPassword->updatePassword(
                $_SESSION['authUserID'],
                0,
                $_POST['adminPass'],
                $_POST['stiltskin'],
                true,
                $insertUserSQL,
                trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
            );
            error_log(errorLogEscape($authUtilsNewPassword->getErrorMessage()));
            $alertmsg .= $authUtilsNewPassword->getErrorMessage();
            if ($success) {
                //set the facility name from the selected facility_id
                sqlStatement(
                    "UPDATE users, facility SET users.facility = facility.name WHERE facility.id = ? AND users.username = ?",
                    array(
                        trim((isset($_POST['facility_id']) ? $_POST['facility_id'] : '')),
                        trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                    )
                );

                sqlStatement(
                    "insert into `groups` set name = ?, user = ?",
                    array(
                        trim((isset($_POST['groupname']) ? $_POST['groupname'] : '')),
                        trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                    )
                );

                if (trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))) {
                              // Set the access control group of user
                              AclExtended::setUserAro(
                                  $_POST['access_group'],
                                  trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')),
                                  trim((isset($_POST['fname']) ? $_POST['fname'] : '')),
                                  trim((isset($_POST['mname']) ? $_POST['mname'] : '')),
                                  trim((isset($_POST['lname']) ? $_POST['lname'] : ''))
                              );
                }
            }
        } else {
            $alertmsg .= xl('User') . ' ' . trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')) . ' ' . xl('already exists.');
        }

        if ($_POST['access_group']) {
            $bg_count = count($_POST['access_group']);
            for ($i = 0; $i < $bg_count; $i++) {
                if ($_POST['access_group'][$i] == "Emergency Login") {
                      $set_active_msg = 1;
                }
            }
        }
    } elseif ($_POST["mode"] == "new_group") {
        $res = sqlStatement("select distinct name, user from `groups`");
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        $doit = 1;
        foreach ($result as $iter) {
            if ($doit == 1 && $iter["name"] == (trim((isset($_POST['groupname']) ? $_POST['groupname'] : ''))) && $iter["user"] == (trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')))) {
                $doit--;
            }
        }

        if ($doit == 1) {
            sqlStatement(
                "insert into `groups` set name = ?, user = ?",
                array(
                    trim((isset($_POST['groupname']) ? $_POST['groupname'] : '')),
                    trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                )
            );
        } else {
            $alertmsg .= "User " . trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')) .
            " is already a member of group " . trim((isset($_POST['groupname']) ? $_POST['groupname'] : '')) . ". ";
        }
    }
}

if (isset($_GET["mode"])) {
  /*******************************************************************
  // This is the code to delete a user.  Note that the link which invokes
  // this is commented out.  Somebody must have figured it was too dangerous.
  //
  if ($_GET["mode"] == "delete") {
    $res = sqlStatement("select distinct username, id from users where id = '" .
      $_GET["id"] . "'");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;

    // TBD: Before deleting the user, we should check all tables that
    // reference users to make sure this user is not referenced!

    foreach($result as $iter) {
      sqlStatement("delete from `groups` where user = '" . $iter["username"] . "'");
    }
    sqlStatement("delete from users where id = '" . $_GET["id"] . "'");
  }
  *******************************************************************/

    if ($_GET["mode"] == "delete_group") {
        $res = sqlStatement("select distinct user from `groups` where id = ?", array($_GET["id"]));
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        foreach ($result as $iter) {
            $un = $iter["user"];
        }

        $res = sqlStatement("select name, user from `groups` where user = ? " .
        "and id != ?", array($un, $_GET["id"]));

        // Remove the user only if they are also in some other group.  I.e. every
        // user must be a member of at least one group.
        if (sqlFetchArray($res) != false) {
              sqlStatement("delete from `groups` where id = ?", array($_GET["id"]));
        } else {
              $alertmsg .= "You must add this user to some other group before " .
                "removing them from this group. ";
        }
    }
}
// added for form submit's from usergroup_admin_add and user_admin.php
// sjp 12/29/17
if (isset($_REQUEST["mode"])) {
    exit(text(trim($alertmsg)));
}

$form_inactive = empty($_POST['form_inactive']) ? false : true;

?>
<html>
<head>
<title><?php echo xlt('User / Groups');?></title>

<?php Header::setupHeader(['common']); ?>

<script>

$(function () {

    tabbify();

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 'modal-mlg', 450, '', '', {
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

</script>

</head>
<body class="body_top">

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-title">
                <h2><?php echo xlt('User / Groups');?></h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="btn-group">
                <a href="usergroup_admin_add.php" class="medium_modal btn btn-secondary btn-add"><?php echo xlt('Add User'); ?></a>
                <a href="facility_user.php" class="btn btn-secondary btn-show"><?php echo xlt('View Facility Specific User Information'); ?></a>
            </div>
            <form name='userlist' method='post' style="display: inline;" class="form-inline" class="float-right" action='usergroup_admin.php' onsubmit='return top.restoreSession()'>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="checkbox">
                    <label for="form_inactive">
                        <input type='checkbox' class="form-control" id="form_inactive" name='form_inactive' value='1' onclick='submit()' <?php echo ($form_inactive) ? 'checked ' : ''; ?>>
                        <?php echo xlt('Include inactive users'); ?>
                    </label>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <?php
            if ($set_active_msg == 1) {
                echo "<div class='alert alert-danger'>" . xlt('Emergency Login ACL is chosen. The user is still in active state, please de-activate the user and activate the same when required during emergency situations. Visit Administration->Users for activation or de-activation.') . "</div><br />";
            }

            if ($show_message == 1) {
                echo "<div class='alert alert-danger'>" . xlt('The following Emergency Login User is activated:') . " " . "<b>" . text($_GET['fname']) . "</b>" . "</div><br />";
                echo "<div class='alert alert-danger'>" . xlt('Emergency Login activation email will be circulated only if following settings in the interface/globals.php file are configured:') . " \$GLOBALS['Emergency_Login_email'], \$GLOBALS['Emergency_Login_email_id']</div>";
            }

            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Username'); ?></th>
                        <th><?php echo xlt('Real Name'); ?></th>
                        <th><?php echo xlt('Additional Info'); ?></th>
                        <th><?php echo xlt('Authorized'); ?></th>
                        <th><?php echo xlt('MFA'); ?></th>
                        <?php
                        $checkPassExp = false;
                        if (($GLOBALS['password_expiration_days'] != 0) && (check_integer($GLOBALS['password_expiration_days'])) && (check_integer($GLOBALS['password_grace_time']))) {
                            $checkPassExp = true;
                            echo '<th>' . xlt('Password Expiration') . '</th>';
                        }
                        ?>
                    </tr>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users WHERE username != '' ";
                        if (!$form_inactive) {
                            $query .= "AND active = '1' ";
                        }

                        $query .= "ORDER BY username";
                        $res = sqlStatement($query);
                        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                            $result4[$iter] = $row;
                        }

                        foreach ($result4 as $iter) {
                            if ($iter["authorized"]) {
                                $iter["authorized"] = xl('yes');
                            } else {
                                $iter["authorized"] = xl('no');
                            }

                            $mfa = sqlQuery(
                                "SELECT `method` FROM `login_mfa_registrations` " .
                                "WHERE `user_id` = ? AND (`method` = 'TOTP' OR `method` = 'U2F')",
                                [$iter['id']]
                            );
                            if (!empty($mfa['method'])) {
                                $isMfa = xl('yes');
                            } else {
                                $isMfa = xl('no');
                            }

                            if ($checkPassExp) {
                                $current_date = date("Y-m-d");
                                $userSecure = privQuery("SELECT `last_update_password` FROM `users_secure` WHERE `id` = ?", [$iter['id']]);
                                $pwd_expires = date("Y-m-d", strtotime($userSecure['last_update_password'] . "+" . $GLOBALS['password_expiration_days'] . " days"));
                                $grace_time = date("Y-m-d", strtotime($pwd_expires . "+" . $GLOBALS['password_grace_time'] . " days"));
                            }

                            print "<tr>
                                <td><b><a href='user_admin.php?id=" . attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) .
                                "' class='medium_modal' onclick='top.restoreSession()'>" . text($iter["username"]) . "</a></b>" . "&nbsp;</td>
                                <td>" . text($iter["fname"]) . ' ' . text($iter["lname"]) . "&nbsp;</td>
                                <td>" . text($iter["info"]) . "&nbsp;</td>
                                <td align='left'><span>" . text($iter["authorized"]) . "</td>
                                <td align='left'><span>" . text($isMfa) . "</td>";
                            if ($checkPassExp) {
                                echo '<td>';
                                if (AuthUtils::useActiveDirectory($iter["username"])) {
                                    // LDAP bypasses expired password mechanism
                                    echo '<div class="alert alert-success" role="alert">' . xlt('Not Applicable') . '</div>';
                                } elseif (strtotime($current_date) > strtotime($grace_time)) {
                                    echo '<div class="alert alert-danger" role="alert">' . xlt('Expired') . '</div>';
                                } elseif (strtotime($current_date) > strtotime($pwd_expires)) {
                                    echo '<div class="alert alert-warning" role="alert">' . xlt('Grace Period') . '</div>';
                                } else {
                                    echo '<div class="alert alert-success" role="alert">' . text(oeFormatShortDate($pwd_expires)) . '</div>';
                                }
                                echo '</td>';
                            }
                            print "</tr>\n";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            if (empty($GLOBALS['disable_non_default_groups'])) {
                $res = sqlStatement("select * from `groups` order by name");
                for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                    $result5[$iter] = $row;
                }

                foreach ($result5 as $iter) {
                    $grouplist[$iter["name"]] .= text($iter["user"]) .
                        "(<a class='link_submit' href='usergroup_admin.php?mode=delete_group&id=" .
                        attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . xlt('Remove') . "</a>), ";
                }

                foreach ($grouplist as $groupname => $list) {
                    print "<span class='bold'>" . text($groupname) . "</span><br />\n<span>" .
                        substr($list, 0, strlen($list) - 2) . "</span><br />\n";
                }
            }
            ?>
        </div>
    </div>
</div>
<script>
<?php
if ($alertmsg = trim($alertmsg)) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}
?>
</script>
</body>
</html>
