<?php

/**
 * Patient Tracker (Patient Flow Board)
 *
 * This program displays the information entered in the Calendar program ,
 * allowing the user to change status and view those changed here and in the Calendar
 * Will allow the collection of length of time spent in each status
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Terry Hill <terry@lilysystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Ray Magauran <magauran@medexbank.com>
 * @copyright Copyright (c) 2015-2017 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Ray Magauran <magauran@medexbank.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../globals.php";
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once "$srcdir/patient_tracker.inc.php";
require_once "$srcdir/user.inc";
require_once "$srcdir/MedEx/API.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// These settings are sticky user preferences linked to a given page.
// mdsupport - user_settings prefix
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_new_window = prevSetting($uspfx, 'setting_new_window', 'setting_new_window', ' ');
// flow board and recall board share bootstrap settings:
$setting_bootstrap_submenu = prevSetting('', 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ');
$setting_selectors = prevSetting($uspfx, 'setting_selectors', 'setting_selectors', 'block');
$form_apptcat = prevSetting($uspfx, 'form_apptcat', 'form_apptcat', '');
$form_apptstatus = prevSetting($uspfx, 'form_apptstatus', 'form_apptstatus', '');
$facility = prevSetting($uspfx, 'form_facility', 'form_facility', '');
$provider = prevSetting($uspfx, 'form_provider', 'form_provider', $_SESSION['authUserID']);

if (
    ($_POST['setting_new_window']) ||
    ($_POST['setting_bootstrap_submenu']) ||
    ($_POST['setting_selectors'])
) {
    // These are not form elements. We only ever change them via ajax, so exit now.
    exit();
}
if ($_POST['saveCALLback'] == "Save") {
    $sqlINSERT = "INSERT INTO medex_outgoing (msg_pc_eid,msg_pid,campaign_uid,msg_type,msg_reply,msg_extra_text)
                  VALUES
                (?,?,?,'NOTES','CALLED',?)";
    sqlQuery($sqlINSERT, array($_POST['pc_eid'], $_POST['pc_pid'], $_POST['campaign_uid'], $_POST['txtCALLback']));
}

//set default start date of flow board to value based on globals
if (!$GLOBALS['ptkr_date_range']) {
    $from_date = date('Y-m-d');
} elseif (!is_null($_REQUEST['form_from_date'])) {
    $from_date = DateToYYYYMMDD($_REQUEST['form_from_date']);
} elseif (($GLOBALS['ptkr_start_date']) == 'D0') {
    $from_date = date('Y-m-d');
} elseif (($GLOBALS['ptkr_start_date']) == 'B0') {
    if (date(w) == GLOBALS['first_day_week']) {
        //today is the first day of the week
        $from_date = date('Y-m-d');
    } elseif ($GLOBALS['first_day_week'] == 0) {
        //Sunday
        $from_date = date('Y-m-d', strtotime('previous sunday'));
    } elseif ($GLOBALS['first_day_week'] == 1) {
        //Monday
        $from_date = date('Y-m-d', strtotime('previous monday'));
    } elseif ($GLOBALS['first_day_week'] == 6) {
        //Saturday
        $from_date = date('Y-m-d', strtotime('previous saturday'));
    }
} else {
    //shouldnt be able to get here...
    $from_date = date('Y-m-d');
}

//set default end date of flow board to value based on globals
if ($GLOBALS['ptkr_date_range']) {
    if (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'Y') {
        $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
        $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d'), date('Y') + $ptkr_time);
    } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'M') {
        $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
        $ptkr_future_time = mktime(0, 0, 0, date('m') + $ptkr_time, date('d'), date('Y'));
    } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'D') {
        $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
        $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d') + $ptkr_time, date('Y'));
    }

    $to_date = date('Y-m-d', $ptkr_future_time);
    $to_date = !is_null($_REQUEST['form_to_date']) ? DateToYYYYMMDD($_REQUEST['form_to_date']) : $to_date;
} else {
    $to_date = date('Y-m-d');
}

$form_patient_name = !is_null($_POST['form_patient_name']) ? $_POST['form_patient_name'] : null;
$form_patient_id = !is_null($_POST['form_patient_id']) ? $_POST['form_patient_id'] : null;


$lres = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = ? AND activity=1", array('apptstat'));
while ($lrow = sqlFetchArray($lres)) {
    // if exists, remove the legend character
    if ($lrow['title'][1] == ' ') {
        $splitTitle = explode(' ', $lrow['title']);
        array_shift($splitTitle);
        $title = implode(' ', $splitTitle);
    } else {
        $title = $lrow['title'];
    }
    $statuses_list[$lrow['option_id']] = $title;
}

if ($GLOBALS['medex_enable'] == '1') {
    $query2 = "SELECT * FROM medex_icons";
    $iconed = sqlStatement($query2);
    while ($icon = sqlFetchArray($iconed)) {
        $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
    }
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $sql = "SELECT * FROM medex_prefs LIMIT 1";
    $preferences = sqlStatement($sql);
    $prefs = sqlFetchArray($preferences);
    $results = json_decode($prefs['status'], true);
    $logged_in = $results;
    if (!empty($prefs)) {
        foreach ($results['campaigns']['events'] as $event) {
            if ($event['M_group'] != 'REMINDER') {
                continue;
            }
            $icon = $icons[$event['M_type']]['SCHEDULED']['html'];
            if ($event['E_timing'] == '1') {
                $action = xl("before");
            }
            if ($event['E_timing'] == '2') {
                $action = xl("before (PM)");
            }
            if ($event['E_timing'] == '3') {
                $action = xl("after");
            }
            if ($event['E_timing'] == '4') {
                $action = xl("after (PM)");
            }
            $days = ($event['E_fire_time'] == '1') ? xl("day") : xl("days");
            $current_events .= $icon . " &nbsp; " . (int)$event['E_fire_time'] . " " . text($days) . " " . text($action) . "<br />";
        }
    } else {
        $current_events = $icons['SMS']['FAILED']['html'] . " " . xlt("Currently off-line");
    }
}

if (!$_REQUEST['flb_table']) {
    ?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'opener', 'purecss']); ?>
    <title><?php echo xlt('Flow Board'); ?></title>
    <script>
        <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>">
    <script src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>"></script>

    <link rel="shortcut icon" href="<?php echo $webroot; ?>/sites/default/favicon.ico" />
    <style>
        select {
            width: 170px;
        }

        .dialogIframe {
            border: none;
        }

        .scheduled {
            background-color: var(--light);
            padding: 5px;
        }

        .divTable {
            display: table;
            font-size: 0.9rem;
            background: var(--light);
            box-shadow: 2px 3px 9px var(--gray400);
            border-radius: 8px;
            padding: 10px;
            margin: 15px auto;
            overflow: hidden;
        }

        .head {
            font-size: 0.9rem;
            background: var(--light);
            box-shadow: 2px 3px 9px var(--gray400);
            border-radius: 8px;
            padding: 10px;
            margin: 10px auto;
            overflow: hidden;
            width: 85%;
        }

        .title {
            font-family: "Georgia", sans-serif;
            font-weight: bold;
            padding: 3px 10px;
            text-transform: uppercase;
            line-height: 1.5rem;
            border-bottom: 2px solid var(--body-color);
            margin: 0 auto;
            width: 70%;
        }

        input[type="text"] {
            text-align: center;
        }

        .ui-widget {
            font-size: 1.0rem;
        }

        a:hover {
            color: var(--body-color);
            text-decoration: none;
        }
    </style>

</head>

<body class="body_top">
    <?php
    if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu']))) {
        $logged_in = $MedEx->login();
        $MedEx->display->navigation($logged_in);
    }
    ?>
    <div class="container-fluid" style="margin-top: 20px;">
    <div class="row" id="flb_selectors" style="display:<?php echo attr($setting_selectors); ?>;">
        <div class="col-sm-12">
            <div class="showRFlow text-center" id="show_flows" style="margin: 20px auto;" name="kiosk_hide">
                <div class="title mb-4"><?php echo xlt('Flow Board'); ?></div>
                <div name="div_response" id="div_response" class="nodisplay"></div>
                <?php
                if ($GLOBALS['medex_enable'] == '1') {
                    $col_width = "3";
                } else {
                    $col_width = "4";
                    $last_col_width = "nodisplay";
                }
                ?>

                <form name="flb" id="flb" method="post">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="text-center row head align-items-center">
                        <div class="col-sm-<?php echo attr($col_width); ?> text-center">
                            <div class="form-group row justify-content-center">
                                <div class="col-xl-5 col-lg-9 col-md-11 mb-xl-0 mb-1">
                                    <select id="form_apptcat" name="form_apptcat" class="form-control form-control-sm" onchange="refineMe('apptcat');" title="">
                                        <?php
                                        $categories = fetchAppointmentCategories();
                                        echo "<option value=''>" . xlt("Visit Categories") . "</option>";
                                        while ($cat = sqlFetchArray($categories)) {
                                            echo "<option value='" . attr($cat['id']) . "'";
                                            if ($cat['id'] == $_POST['form_apptcat']) {
                                                echo " selected='true' ";
                                            }
                                            echo ">" . xlt($cat['category']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-xl-5 col-lg-9 col-md-11">
                                    <select id="form_apptstatus" name="form_apptstatus" class="form-control form-control-sm" onchange="refineMe();">
                                        <option value=""><?php echo xlt("Visit Status"); ?></option>

                                        <?php
                                        $apptstats = sqlStatement("SELECT * FROM list_options WHERE list_id = 'apptstat' AND activity = 1 ORDER BY seq");
                                        while ($apptstat = sqlFetchArray($apptstats)) {
                                            echo "<option value='" . attr($apptstat['option_id']) . "'";
                                            if ($apptstat['option_id'] == $_POST['form_apptstatus']) {
                                                echo " selected='true' ";
                                            }
                                            echo ">" . xlt($apptstat['title']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mx-md-1">
                                <input type="text" placeholder="<?php echo xla('Patient Name'); ?>" class="form-control form-control-sm" id="form_patient_name" name="form_patient_name" value="<?php echo ($form_patient_name) ? attr($form_patient_name) : ""; ?>" onKeyUp="refineMe();" />
                            </div>
                        </div>

                        <div class="col-sm-<?php echo attr($col_width); ?> text-center">
                            <div class="form-group row justify-content-center">
                                <div class="col-xl-5 col-lg-9 col-md-11 mb-xl-0 mb-1">
                                    <select class="form-control form-control-sm" id="form_facility" name="form_facility"
                                        <?php
                                        $fac_sql = sqlStatement("SELECT * FROM facility ORDER BY id");
                                        while ($fac = sqlFetchArray($fac_sql)) {
                                            $true = ($fac['id'] == $_POST['form_facility']) ? "selected=true" : '';
                                            $select_facs .= "<option value=" . attr($fac['id']) . " " . $true . ">" . text($fac['name']) . "</option>\n";
                                            $count_facs++;
                                        }
                                        if ($count_facs < '1') {
                                            echo "disabled";
                                        }
                                        ?> onchange="refineMe('facility');">
                                        <option value=""><?php echo xlt('All Facilities'); ?></option>
                                        <?php echo $select_facs; ?>
                                    </select>
                                </div>

                                <?php
                                // Build a drop-down list of ACTIVE providers.
                                $query = "SELECT id, lname, fname FROM users WHERE " .
                                    "authorized = 1  AND active = 1 AND username > '' ORDER BY lname, fname"; #(CHEMED) facility filter
                                $ures = sqlStatement($query);
                                while ($urow = sqlFetchArray($ures)) {
                                    $provid = $urow['id'];
                                    $select_provs .= "    <option value='" . attr($provid) . "'";
                                    if (isset($_POST['form_provider']) && $provid == $_POST['form_provider']) {
                                        $select_provs .= " selected";
                                    } elseif (!isset($_POST['form_provider']) && $_SESSION['userauthorized'] && $provid == $_SESSION['authUserID']) {
                                        $select_provs .= " selected";
                                    }
                                    $select_provs .= ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                    $count_provs++;
                                }
                                ?>

                                <div class="col-xl-5 col-lg-9 col-md-11">
                                    <select class="form-control form-control-sm" id="form_provider" name="form_provider" <?php
                                    if ($count_provs < '2') {
                                        echo "disabled";
                                    }
                                    ?> onchange="refineMe('provider');">
                                        <option value="" selected><?php echo xlt('All Providers'); ?></option>

                                        <?php
                                        echo $select_provs;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mx-md-1">
                                <input placeholder="<?php echo xla('Patient ID'); ?>" class="form-control form-control-sm" type="text" id="form_patient_id" name="form_patient_id" value="<?php echo ($form_patient_id) ? attr($form_patient_id) : ""; ?>" onKeyUp="refineMe();" />
                            </div>
                        </div>
                        <div class="col-sm-<?php echo attr($col_width); ?>">
                            <div class="input-append">

                                    <?php
                                    if ($GLOBALS['ptkr_date_range'] == '1') {
                                        $type = 'date';
                                        $style = '';
                                    } else {
                                        $type = 'hidden';
                                        $style = 'display:none;';
                                    } ?>
                                    <div class="form-group row mt-md-4" style="<?php echo $style; ?>">
                                        <label for="flow_from" class="col-lg-2 col-sm-3 col-2 col-form-label text-sm-right mx-sm-0 px-sm-0"><?php echo xlt('From'); ?>:</label>
                                        <div class="col-xl-3 col-lg-5 col-md-6 col-sm-7 col-4 ml-1 px-0">
                                            <input type="text" id="form_from_date" name="form_from_date" class="datepicker form-control form-control-sm text-center" value="<?php echo attr(oeFormatShortDate($from_date)); ?>"/>
                                        </div>
                                    </div>

                                    <div class="form-group row" style="<?php echo $style; ?>">
                                        <label for="flow_to" class="col-lg-2 col-sm-3 col-2 col-form-label text-sm-right mx-sm-0 px-sm-0"><?php echo xlt('To{{Range}}'); ?>:</label>
                                        <div class="col-xl-3 col-lg-5 col-md-6 col-sm-7 col-4 ml-1 px-0">
                                            <input type="text" id="form_to_date" name="form_to_date" class="datepicker form-control form-control-sm text-center" value="<?php echo attr(oeFormatShortDate($to_date)); ?>"/>
                                        </div>
                                    </div>

                                    <div class="form-group row" style="<?php echo $style; ?>">
                                        <div class="col-sm-4 col-3 offset-lg-1 offset-md-3 offset-2">
                                            <button id="filter_submit" class="btn btn-primary"><?php echo xlt('Filter'); ?></button>
                                        </div>
                                        <div class="col-sm-4 col-3">
                                            <input type="hidden" id="kiosk" name="kiosk" value="<?php echo attr($_REQUEST['kiosk']); ?>" />
                                        </div>
                                    </div>


                            </div>
                        </div>
                        <?php
                        if ($GLOBALS['medex_enable'] == '1') {
                            ?>
                        <div class="col-sm-<?php echo attr($col_width) . " " . attr($last_col_width); ?> text-center">
                                <div class="text-center row">
                                    <span class="font-weight-bold" style="text-decoration:underline; font-size:1.2em;">MedEx <?php echo xlt('Reminders'); ?></span><br/>
                                    <div class="text-left blockquote" style="width: 65%; margin: 5px auto;">
                                        <a href="https://medexbank.com/cart/upload/index.php?route=information/campaigns&amp;g=rem"
                                           target="_medex">
                                            <?php echo $current_events; ?>
                                        </a>
                                    </div>
                                </div>
                        </div>
                        <?php } ?>
                    </div>
                <div id="message" class="warning"></div>
            </div>
            </form>
        </div>
    </div>

    <div class="row-fluid">
        <div class="col-md-12">
            <div class="text-center row divTable" style="width: 85%; padding: 10px 10px 0; margin: 10px auto;">
                <div class="col-sm-12" id="loader">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-pulse fa-fw" style="font-size: 140px; color: var(--gray700); padding: 20px"></i>
                        <h2><?php echo xlt('Loading data'); ?>...</h2>
                    </div>
                </div>
                <div id="flb_table" name="flb_table">
            <?php
} else {
//end of if !$_REQUEST['flb_table'] - this is the table we fetch via ajax during a refreshMe() call
// get all appts for date range and refine view client side.  very fast...
    $appointments = array();
    $datetime = date("Y-m-d H:i:s");
    $appointments = fetch_Patient_Tracker_Events($from_date, $to_date, '', '', '', '', $form_patient_name, $form_patient_id);
    $appointments = sortAppointments($appointments, 'date', 'time');
    //grouping of the count of every status
    $appointments_status = getApptStatus($appointments);

    $chk_prov = array();  // list of providers with appointments
    // Scan appointments for additional info
    foreach ($appointments as $apt) {
        $chk_prov[$apt['uprovider_id']] = $apt['ulname'] . ', ' . $apt['ufname'] . ' ' . $apt['umname'];
    }

    ?>
                <div class="col-sm-12 text-center" style='margin:5px;'>
                <div class="d-none d-sm-block">
                    <span id="status_summary">
                        <?php
                        $statuses_output = "<span style='margin:0 10px;'><em>" . xlt('Total patients') . ':</em> <span class="badge">' . text($appointments_status['count_all']) . "</span></span>";
                        unset($appointments_status['count_all']);
                        foreach ($appointments_status as $status_symbol => $count) {
                            $statuses_output .= " | <span style='margin:0 10px;'><em>" . text(xl_list_label($statuses_list[$status_symbol])) . ":</em> <span class='badge'>" . text($count) . "</span></span>";
                        }
                        echo $statuses_output;
                        ?>
                    </span>
                </div>
                <div id="pull_kiosk_right" class="text-right">
                  <a id='setting_cog'><i class="fa fa-cog fa-2x fa-fw">&nbsp;</i></a>

                  <label for='setting_new_window' id='settings'>
                    <input type='checkbox' name='setting_new_window' id='setting_new_window' value='<?php echo attr($setting_new_window); ?>' <?php echo attr($setting_new_window); ?> />
                        <?php echo xlt('Open Patient in New Window'); ?>
                  </label>
                  <a id='refreshme'><i class="fa fa-sync fa-2x fa-fw">&nbsp;</i></a>
                  <span class="fa-stack fa-lg" id="flb_caret" onclick="toggleSelectors();" title="<?php echo xla('Show/Hide the Selection Area'); ?>" style="color:<?php echo $color = ($setting_selectors == 'none') ? 'var(--danger)' : 'var(--black)'; ?>;">
                    <i class="fa fa-square-o fa-stack-2x"></i>
                    <i id="print_caret" class='fa fa-caret-<?php echo $caret = ($setting_selectors == 'none') ? 'down' : 'up'; ?> fa-stack-1x'></i>
                  </span>

                  <a class='btn btn-primary text-white' onclick="print_FLB();"> <?php echo xlt('Print'); ?> </a>

                  <a class='btn btn-primary text-white' onclick="kiosk_FLB();"> <?php echo xlt('Kiosk'); ?> </a>
                </div>
            </div>

                <div class="col-sm-12 textclear">
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                    <thead>
                    <tr class="small font-weight-bold text-center table-primary">
                        <?php if ($GLOBALS['ptkr_show_pid']) { ?>
                            <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark" name="kiosk_hide">
                                <?php echo xlt('PID'); ?>
                            </td>
                        <?php } ?>
                        <td class="dehead text-center text-ovr-dark" style="max-width: 150px;">
                            <?php echo xlt('Patient'); ?>
                        </td>
                        <?php if ($GLOBALS['ptkr_visit_reason'] == '1') { ?>
                            <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark" name="kiosk_hide">
                                <?php echo xlt('Reason'); ?>
                            </td>
                        <?php } ?>
                        <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                            <td class="dehead text-center d-none d-md-table-cell text-ovr-dark" name="kiosk_hide">
                                <?php echo xlt('Encounter'); ?>
                            </td>
                        <?php } ?>

                        <?php if ($GLOBALS['ptkr_date_range'] == '1') { ?>
                            <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark" name="kiosk_hide">
                                <?php echo xlt('Appt Date'); ?>
                            </td>
                        <?php } ?>
                        <td class="dehead text-center text-ovr-dark">
                            <?php echo xlt('Appt Time'); ?>
                        </td>
                        <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark">
                            <?php echo xlt('Arrive Time'); ?>
                        </td>
                        <td class="dehead text-center d-block d-table-cell d-sm-none text-ovr-dark">
                            <?php echo xlt('Arrival'); ?>
                        </td>
                        <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark">
                            <?php echo xlt('Appt Status'); ?>
                        </td>
                        <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark">
                            <?php echo xlt('Current Status'); ?>
                        </td>
                        <td class="dehead text-center d-block d-table-cell d-sm-none text-ovr-dark">
                            <?php echo xlt('Current'); ?>
                        </td>
                        <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark" name="kiosk_hide">
                            <?php echo xlt('Visit Type'); ?>
                        </td>
                        <?php if (count($chk_prov) > 1) { ?>
                            <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark">
                                <?php echo xlt('Provider'); ?>
                            </td>
                        <?php } ?>
                        <td class="dehead text-center text-ovr-dark">
                            <?php echo xlt('Total Time'); ?>
                        </td>
                        <td class="dehead text-center d-none d-sm-table-cell text-ovr-dark">
                            <?php echo xlt('Check Out Time'); ?>
                        </td>
                        <td class="dehead text-center d-block d-table-cell d-sm-none text-ovr-dark">
                            <?php echo xlt('Out Time'); ?>
                        </td>
                        <?php
                        if ($GLOBALS['ptkr_show_staff']) { ?>
                            <td class="dehead text-center d-none d-md-table-cell text-ovr-dark" name="kiosk_hide">
                                <?php echo xlt('Updated By'); ?>
                            </td>
                            <?php
                        }
                        if ($_REQUEST['kiosk'] != '1') {
                            if ($GLOBALS['drug_screen']) { ?>
                                <td class="dehead center d-none d-sm-table-cell text-ovr-dark" name="kiosk_hide">
                                    <?php echo xlt('Random Drug Screen'); ?>
                                </td>
                                <td class="dehead center d-none d-sm-table-cell text-ovr-dark" name="kiosk_hide">
                                    <?php echo xlt('Drug Screen Completed'); ?>
                                </td>
                                <?php
                            }
                        } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $prev_appt_date_time = "";
                    foreach ($appointments as $appointment) {
                        // Collect appt date and set up squashed date for use below
                        $date_appt = $appointment['pc_eventDate'];
                        $date_squash = str_replace("-", "", $date_appt);
                        if (empty($appointment['room']) && ($logged_in) && ($setting_bootstrap_submenu != 'hide')) {
                            //Patient has not arrived yet, display MedEx Reminder info
                            //one icon per type of response.
                            //If there was a SMS dialog, display it as a mouseover/title
                            //Display date received also as mouseover title.
                            $other_title = '';
                            $title = '';
                            $icon2_here = '';
                            $icon_CALL = '';
                            $icon_4_CALL = '';
                            $appt['stage'] = '';
                            $icon_here = array();
                            $prog_text = '';
                            $CALLED = '';
                            $FINAL = '';
                            $icon_CALL = '';

                            $query = "SELECT * FROM medex_outgoing WHERE msg_pc_eid =? ORDER BY medex_uid asc";
                            $myMedEx = sqlStatement($query, array($appointment['eid']));
                            /**
                             * Each row for this pc_eid in the medex_outgoing table represents an event.
                             * Every event is recorded in $prog_text.
                             * A modality is represented by an icon (eg mail icon, phone icon, text icon).
                             * The state of the Modality is represented by the color of the icon:
                             *      CONFIRMED       =   green
                             *      READ            =   blue
                             *      FAILED          =   pink
                             *      SENT/in process =   yellow
                             *      SCHEDULED       =   white
                             * Icons are displayed in their highest state.
                             */
                            while ($row = sqlFetchArray($myMedEx)) {
                                // Need to convert $row['msg_date'] to localtime (stored as GMT) & then oeFormatShortDate it.
                                // I believe there is a new GLOBAL for server timezone???  If so, it will be easy.
                                // If not we need to import it from Medex through medex_preferences.  It should really be in openEMR though.
                                // Delete when we figure this out.
                                $other_title = '';
                                if (!empty($row['msg_extra_text'])) {
                                    $local = attr($row['msg_extra_text']) . " |";
                                }
                                $prog_text .= attr(oeFormatShortDate($row['msg_date'])) . " :: " . attr($row['msg_type']) . " : " . attr($row['msg_reply']) . " | " . $local . " |";

                                if ($row['msg_reply'] == 'Other') {
                                    $other_title .= $row['msg_extra_text'] . "\n";
                                    $icon_extra .= str_replace(
                                        "EXTRA",
                                        attr(oeFormatShortDate($row['msg_date'])) . "\n" . xla('Patient Message') . ":\n" . attr($row['msg_extra_text']) . "\n",
                                        $icons[$row['msg_type']]['EXTRA']['html']
                                    );
                                    continue;
                                } elseif ($row['msg_reply'] == 'CANCELLED') {
                                    $appointment[$row['msg_type']]['stage'] = "CANCELLED";
                                    $icon_here[$row['msg_type']] = '';
                                } elseif ($row['msg_reply'] == "FAILED") {
                                    $appointment[$row['msg_type']]['stage'] = "FAILED";
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['FAILED']['html'];
                                } elseif (($row['msg_reply'] == "CONFIRMED") || ($appointment[$row['msg_type']]['stage'] == "CONFIRMED")) {
                                    $appointment[$row['msg_type']]['stage'] = "CONFIRMED";
                                    $icon_here[$row['msg_type']]  = $icons[$row['msg_type']]['CONFIRMED']['html'];
                                } elseif ($row['msg_type'] == "NOTES") {
                                    $CALLED = "1";
                                    $FINAL = $icons['NOTES']['CALLED']['html'];
                                    $icon_CALL = str_replace("Call Back: COMPLETED", attr(oeFormatShortDate($row['msg_date'])) . " :: " . xla('Callback Performed') . " | " . xla('NOTES') . ": " . $row['msg_extra_text'] . " | ", $FINAL);
                                    continue;
                                } elseif (($row['msg_reply'] == "READ") || ($appointment[$row['msg_type']]['stage'] == "READ")) {
                                    $appointment[$row['msg_type']]['stage'] = "READ";
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'];
                                } elseif (($row['msg_reply'] == "SENT") || ($appointment[$row['msg_type']]['stage'] == "SENT")) {
                                    $appointment[$row['msg_type']]['stage'] = "SENT";
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'];
                                } elseif (($row['msg_reply'] == "To Send") || (empty($appointment['stage']))) {
                                    if (
                                        ($appointment[$row['msg_type']]['stage'] != "CONFIRMED") &&
                                        ($appointment[$row['msg_type']]['stage'] != "READ") &&
                                        ($appointment[$row['msg_type']]['stage'] != "SENT") &&
                                        ($appointment[$row['msg_type']]['stage'] != "FAILED")
                                    ) {
                                        $appointment[$row['msg_type']]['stage'] = "QUEUED";
                                        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'];
                                    }
                                }
                                //these are additional icons if present
                                if (($row['msg_reply'] == "CALL") && (!$CALLED)) {
                                    $icon_here = '';
                                    $icon_4_CALL = $icons[$row['msg_type']]['CALL']['html'];
                                    $icon_CALL = "<span onclick=\"doCALLback(" . attr_js($date_squash) . "," . attr_js($appointment['eid']) . "," . attr_js($appointment['pc_cattype']) . ")\">" . $icon_4_CALL . "</span>
                                    <span class='hidden' name='progCALLback_" . attr($appointment['eid']) . "' id='progCALLback_" . attr($appointment['eid']) . "'>
                                      <form id='notation_" . attr($appointment['eid']) . "' method='post'
                                      action='#'>
                                        <input type='hidden' name='csrf_token_form' value='<?php echo attr(CsrfUtils::collectCsrfToken()); ?>' />
                                        <h4>" . xlt('Call Back Notes') . ":</h4>
                                        <input type='hidden' name='pc_eid' id='pc_eid' value='" . attr($appointment['eid']) . "'>
                                        <input type='hidden' name='pc_pid' id='pc_pid' value='" . attr($appointment['pc_pid']) . "'>
                                        <input type='hidden' name='campaign_uid' id='campaign_uid' value='" . attr($row['campaign_uid']) . "'>
                                        <textarea name='txtCALLback' id='txtCALLback' rows=6 cols=20></textarea>
                                        <input type='submit' name='saveCALLback' id='saveCALLback' value='" . xla("Save") . "'>
                                      </form>
                                    </span>
                                      ";
                                } elseif ($row['msg_reply'] == "STOP") {
                                    $icon2_here .= $icons[$row['msg_type']]['STOP']['html'];
                                } elseif ($row['msg_reply'] == "Other") {
                                    $icon2_here .= $icons[$row['msg_type']]['Other']['html'];
                                } elseif ($row['msg_reply'] == "CALLED") {
                                    $icon2_here .= $icons[$row['msg_type']]['CALLED']['html'];
                                }
                            }
                            //if pc_apptstatus == '-', update it now to=status
                            if (!empty($other_title)) {
                                $appointment['messages'] = $icon2_here . $icon_extra;
                            }
                        }

                        // Collect variables and do some processing
                        $docname = $chk_prov[$appointment['uprovider_id']];
                        if (strlen($docname) <= 3) {
                            continue;
                        }
                        $ptname = $appointment['lname'] . ', ' . $appointment['fname'] . ' ' . $appointment['mname'];
                        $ptname_short = $appointment['fname'][0] . " " . $appointment['lname'][0];
                        $appt_enc = $appointment['encounter'];
                        $appt_eid = (!empty($appointment['eid'])) ? $appointment['eid'] : $appointment['pc_eid'];
                        $appt_pid = (!empty($appointment['pid'])) ? $appointment['pid'] : $appointment['pc_pid'];
                        if ($appt_pid == 0) {
                            continue; // skip when $appt_pid = 0, since this means it is not a patient specific appt slot
                        }
                        $status = (!empty($appointment['status']) && (!is_numeric($appointment['status']))) ? $appointment['status'] : $appointment['pc_apptstatus'];
                        $appt_room = (!empty($appointment['room'])) ? $appointment['room'] : $appointment['pc_room'];
                        $appt_time = (!empty($appointment['appttime'])) ? $appointment['appttime'] : $appointment['pc_startTime'];
                        $tracker_id = $appointment['id'];
                        // reason for visit
                        if ($GLOBALS['ptkr_visit_reason']) {
                            $reason_visit = $appointment['pc_hometext'];
                        }
                        $newarrive = collect_checkin($tracker_id);
                        $newend = collect_checkout($tracker_id);
                        $colorevents = (collectApptStatusSettings($status));
                        $bgcolor = $colorevents['color'];
                        $statalert = $colorevents['time_alert'];
                        // process the time to allow items with a check out status to be displayed
                        if (is_checkout($status) && (($GLOBALS['checkout_roll_off'] > 0) && strlen($form_apptstatus) != 1)) {
                            $to_time = strtotime($newend);
                            $from_time = strtotime($datetime);
                            $display_check_out = round(abs($from_time - $to_time) / 60, 0);
                            if ($display_check_out >= $GLOBALS['checkout_roll_off']) {
                                continue;
                            }
                        }

                        echo '<tr data-apptstatus="' . attr($appointment['pc_apptstatus']) . '"
                            data-apptcat="' . attr($appointment['pc_catid']) . '"
                            data-facility="' . attr($appointment['pc_facility']) . '"
                            data-provider="' . attr($appointment['uprovider_id']) . '"
                            data-pid="' . attr($appointment['pc_pid']) . '"
                            data-pname="' . attr($ptname) . '"
                            class="text-small"
                            bgcolor="' . attr($bgcolor) . '" >';
                        if ($GLOBALS['ptkr_show_pid']) {
                            ?>
                            <td class="detail d-none d-sm-table-cell" align="center" name="kiosk_hide">
                                <?php echo text($appt_pid); ?>
                            </td>
                            <?php
                        }

                        ?>
                        <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide">
                            <a href="#" onclick="return topatient(<?php echo attr_js($appt_pid); ?>,<?php echo attr_js($appt_enc); ?>)">
                                <?php echo text($ptname); ?></a>
                        </td>
                        <td class="detail text-center d-block d-table-cell d-sm-none" style="white-space: normal;" name="kiosk_hide">
                            <a href="#" onclick="return topatient(<?php echo attr_js($appt_pid); ?>,<?php echo attr_js($appt_enc); ?>)">
                                <?php echo text($ptname_short); ?></a>
                        </td>

                        <td class="detail text-center" style="white-space: normal;" name="kiosk_show">
                            <a href="#" onclick="return topatient(<?php echo attr_js($appt_pid); ?>,<?php echo attr_js($appt_enc); ?>)">
                                <?php echo text($ptname_short); ?></a>
                        </td>

                        <!-- reason -->
                        <?php if ($GLOBALS['ptkr_visit_reason']) { ?>
                            <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide">
                                <?php echo text($reason_visit) ?>
                            </td>
                        <?php } ?>
                        <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                            <td class="detail text-center d-none d-md-table-cell" name="kiosk_hide">
                                <?php
                                if ($appt_enc != 0) {
                                    echo text($appt_enc);
                                }
                                ?>
                            </td>
                        <?php } ?>
                        <?php if ($GLOBALS['ptkr_date_range'] == '1') { ?>
                            <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide">
                                <?php echo text(oeFormatShortDate($appointment['pc_eventDate']));
                                ?>
                            </td>
                        <?php } ?>
                        <td class="detail" align="center">
                            <?php echo text(oeFormatTime($appt_time)); ?>
                        </td>
                        <td class="detail text-center">
                            <?php
                            if ($newarrive) {
                                echo text(oeFormatTime($newarrive));
                            }
                            ?>
                        </td>
                        <td class="detail text-center d-none d-sm-table-cell">
                            <?php if (empty($tracker_id)) { //for appt not yet with tracker id and for recurring appt ?>
                            <a class="btn btn-primary btn-sm text-white" onclick="return calendarpopup(<?php echo attr_js($appt_eid) . "," . attr_js($date_squash); // calls popup for add edit calendar event?>)">
                            <?php } else { ?>
                                <a class="btn btn-primary btn-sm text-white" onclick="return bpopup(<?php echo attr_js($tracker_id); // calls popup for patient tracker status?>)">
                            <?php } ?>
                            <?php
                            if ($appointment['room'] > '') {
                                echo text(getListItemTitle('patient_flow_board_rooms', $appt_room));
                            } else {
                                echo text(getListItemTitle("apptstat", $status)); // drop down list for appointment status
                            }
                            ?>
                            </a>
                        </td>

                        <?php
                        //time in current status
                        $to_time = strtotime(date("Y-m-d H:i:s"));
                        $yestime = '0';
                        if (strtotime($newend) != '') {
                            $from_time = strtotime($newarrive);
                            $to_time = strtotime($newend);
                            $yestime = '0';
                        } else {
                            $from_time = strtotime($appointment['start_datetime']);
                            $yestime = '1';
                        }

                        $timecheck = round(abs($to_time - $from_time) / 60, 0);
                        if ($timecheck >= $statalert && ($statalert > '0')) { // Determine if the time in status limit has been reached.
                            echo "<td class='text-center  js-blink-infinite small' nowrap>  "; // and if so blink
                        } else {
                            echo "<td class='detail text-center' nowrap> "; // and if not do not blink
                        }
                        if (($yestime == '1') && ($timecheck >= 1) && (strtotime($newarrive) != '')) {
                            echo text($timecheck . ' ' . ($timecheck >= 2 ? xl('minutes') : xl('minute')));
                        } elseif ($icon_here || $icon2_here || $icon_CALL) {
                            echo "<span style='font-size:0.7rem;' onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . implode($icon_here) . $icon2_here . "</span> " . $icon_CALL;
                        } elseif ($logged_in) {
                            $pat = $MedEx->display->possibleModalities($appointment);
                            echo "<span style='font-size:0.7rem;' onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . $pat['SMS'] . $pat['AVM'] . $pat['EMAIL'] . "</span>";
                        }
                        //end time in current status
                        echo "</td>";
                        ?>
                        <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide">
                            <?php echo xlt($appointment['pc_title']); ?>
                        </td>
                        <?php
                        if (count($chk_prov) > 1) { ?>
                            <td class="detail text-center d-none d-sm-table-cell">
                                <?php echo text($docname); ?>
                            </td>
                            <?php
                        } ?>
                        <td class="detail text-center">
                            <?php
                            // total time in practice
                            if (strtotime($newend) != '') {
                                $from_time = strtotime($newarrive);
                                $to_time = strtotime($newend);
                            } else {
                                $from_time = strtotime($newarrive);
                                $to_time = strtotime(date("Y-m-d H:i:s"));
                            }
                            $timecheck2 = round(abs($to_time - $from_time) / 60, 0);
                            if (strtotime($newarrive) != '' && ($timecheck2 >= 1)) {
                                echo text($timecheck2 . ' ' . ($timecheck2 >= 2 ? xl('minutes') : xl('minute')));
                            }
                            // end total time in practice
                            echo text($appointment['pc_time']); ?>
                        </td>
                        <td class="detail text-center">


                        </td>
                        <?php
                        if ($GLOBALS['ptkr_show_staff'] == '1') {
                            ?>
                                <td class="detail text-center d-none d-md-table-cell" name="kiosk_hide">
                                    <?php echo text($appointment['user']) ?>
                                </td>
                            <?php
                        }
                        if ($GLOBALS['drug_screen']) {
                            if (strtotime($newarrive) != '') { ?>
                                <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide">
                                    <?php
                                    if ($appointment['random_drug_test'] == '1') {
                                        echo xlt('Yes');
                                    } else {
                                        echo xlt('No');
                                    } ?>
                                </td>
                                <?php
                            } else { ?>
                                <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide"></td>
                            <?php }
                            if (strtotime($newarrive) != '' && $appointment['random_drug_test'] == '1') { ?>
                                <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide">
                                    <?php
                                    if (strtotime($newend) != '') {
                                        // the following block allows the check box for drug screens to be disabled once the status is check out ?>
                                        <input type=checkbox disabled='disable' class="drug_screen_completed" id="<?php echo attr($appointment['pt_tracker_id']) ?>" <?php echo ($appointment['drug_screen_completed'] == "1") ? "checked" : ""; ?> />
                                        <?php
                                    } else {
                                        ?>
                                        <input type=checkbox class="drug_screen_completed" id='<?php echo attr($appointment['pt_tracker_id']) ?>' name="drug_screen_completed" <?php echo ($appointment['drug_screen_completed'] == "1") ? "checked" : ""; ?> />
                                        <?php
                                    } ?>
                                </td>
                                <?php
                            } else { ?>
                                <td class="detail text-center d-none d-sm-table-cell" name="kiosk_hide"></td>
                            <?php }
                        }
                        ?>
                        </tr>
                        <?php
                    } //end foreach
                    ?>
                    </tbody>
                </table>
                </div>

    <?php
}
if (!$_REQUEST['flb_table']) { ?>
                   </div>
                </div>
            </div>
        </div>
    </div><?php //end container ?>
    <!-- form used to open a new top level window when a patient row is clicked -->
    <form name='fnew' method='post' target='_blank' action='../main/main_screen.php?auth=login&site=<?php echo attr_url($_SESSION['site_id']); ?>'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <input type='hidden' name='patientID' value='0'/>
        <input type='hidden' name='encounterID' value='0'/>
    </form>

    <?php echo myLocalJS(); ?>
</body>
</html>
    <?php
} //end of second !$_REQUEST['flb_table']


exit;

function myLocalJS()
{
    ?>
    <script>
        var auto_refresh = null;
        //this can be refined to redact HIPAA material using @media print options.
        top.restoreSession();
        window.parent.$("[name='flb']").attr('allowFullscreen', 'true');
        <?php
        if ($_REQUEST['kiosk'] == '1') { ?>
            $("[name='kiosk_hide']").hide();
            $("[name='kiosk_show']").show();
        <?php } else { ?>
            $("[name='kiosk_hide']").show();
            $("[name='kiosk_show']").hide();
        <?php  }   ?>
        function print_FLB() {
            window.print();
        }

        function toggleSelectors() {
            if ($("#flb_selectors").css('display') === 'none') {
                $.post("<?php echo $GLOBALS['webroot'] . "/interface/patient_tracker/patient_tracker.php"; ?>", {
                    setting_selectors: 'block',
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }).done(
                    function (data) {
                        $("#flb_selectors").slideToggle();
                        if($("#flb_caret").hasClass('text-danger')) {
                          $("#flb_caret").removeClass('text-danger');
                        }
                        $("#flb_caret").addClass('text-body');
                });
            } else {
                $.post("<?php echo $GLOBALS['webroot'] . "/interface/patient_tracker/patient_tracker.php"; ?>", {
                    setting_selectors: 'none',
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }).done(
                    function (data) {
                        $("#flb_selectors").slideToggle();
                        if($("#flb_caret").hasClass('text-body')) {
                          $("#flb_caret").removeClass('text-body');
                        }
                        $("#flb_caret").addClass('text-danger');
                });
            }
            $("#print_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
        }

        /**
         * This function refreshes the whole flb_table according to our to/from dates.
         */
        function refreshMe(fromTimer) {

            if(typeof fromTimer === 'undefined' || !fromTimer) {
                //Show loader in the first loading or manual loading not by timer
                $("#flb_table").html('');
                $('#loader').show();
            }

            var startRequestTime = Date.now();
            top.restoreSession();
            var posting = $.post('../patient_tracker/patient_tracker.php', {
                flb_table: '1',
                form_from_date: $("#form_from_date").val(),
                form_to_date: $("#form_to_date").val(),
                form_facility: $("#form_facility").val(),
                form_provider: $("#form_provider").val(),
                form_apptstatus: $("#form_apptstatus").val(),
                form_patient_name: $("#form_patient_name").val(),
                form_patient_id: $("#form_patient_id").val(),
                form_apptcat: $("#form_apptcat").val(),
                kiosk: $("#kiosk").val(),
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }).done(
                function (data) {
                    //minimum 400 ms of loader (In the first loading or manual loading not by timer)
                    if((typeof fromTimer === 'undefined' || !fromTimer) && Date.now() - startRequestTime < 400 ){
                        setTimeout(drawTable, 500, data);
                    } else {
                        drawTable(data)
                    }
                });
        }

        function drawTable(data) {

            $('#loader').hide();
            $("#flb_table").html(data);
            if ($("#kiosk").val() === '') {
            $("[name='kiosk_hide']").show();
            $("[name='kiosk_show']").hide();
            } else {
            $("[name='kiosk_hide']").hide();
            $("[name='kiosk_show']").show();
            }

            refineMe();

            initTableButtons();

        }

        function refreshme() {
            // Just need this to support refreshme call from the popup used for recurrent appt
            refreshMe();
        }

        /**
         * This function hides all then shows only the flb_table rows that match our selection, client side.
         * It is called on initial load, on refresh and 'onchange/onkeyup' of a flow board parameter.
         */
        function refineMe() {
            var apptcatV = $("#form_apptcat").val();
            var apptstatV = $("#form_apptstatus").val();
            var facV = $("#form_facility").val();
            var provV = $("#form_provider").val();
            var pidV = String($("#form_patient_id").val());
            var pidRE = new RegExp(pidV, 'g');
            var pnameV = $("#form_patient_name").val();
            var pnameRE = new RegExp(pnameV, 'ig');

            //and hide what we don't want to show
            $('#flb_table tbody tr').hide().filter(function () {
                var d = $(this).data();
                meets_cat = (apptcatV === '') || (apptcatV == d.apptcat);
                meets_stat = (apptstatV === '') || (apptstatV == d.apptstatus);
                meets_fac = (facV === '') || (facV == d.facility);
                meets_prov = (provV === '') || (provV == d.provider);
                meets_pid = (pidV === '');
                if ((pidV > '') && pidRE.test(d.pid)) {
                    meets_pid = true;
                }
                meets_pname = (pnameV === '');
                if ((pnameV > '') && pnameRE.test(d.pname)) {
                    meets_pname = true;
                }
                return meets_pname && meets_pid && meets_cat && meets_stat && meets_fac && meets_prov;
            }).show();
        }

        // popup for patient tracker status
        function bpopup(tkid) {
            top.restoreSession();
            dlgopen('../patient_tracker/patient_tracker_status.php?tracker_id=' + encodeURIComponent(tkid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 250);
            return false;
        }

        // popup for calendar add edit
        function calendarpopup(eid, date_squash) {
            top.restoreSession();
            dlgopen('../main/calendar/add_edit_event.php?eid=' + encodeURIComponent(eid) + '&date=' + encodeURIComponent(date_squash), '_blank', 775, 500);
            return false;
        }

        // used to display the patient demographic and encounter screens
        function topatient(newpid, enc) {
            if ($('#setting_new_window').val() === 'checked') {
                openNewTopWindow(newpid, enc);
            }
            else {
                top.restoreSession();
                if (enc > 0) {
                    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid) + "&set_encounterid=" + encodeURIComponent(enc);
                }
                else {
                    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
                }
            }
        }

        function doCALLback(eventdate, eid, pccattype) {
            $("#progCALLback_" + eid).parent().removeClass('js-blink-infinite').css('animation-name', 'none');
            $("#progCALLback_" + eid).removeClass("hidden");
            clearInterval(auto_refresh);
        }

        // opens the demographic and encounter screens in a new window
        function openNewTopWindow(newpid, newencounterid) {
            document.fnew.patientID.value = newpid;
            document.fnew.encounterID.value = newencounterid;
            top.restoreSession();
            document.fnew.submit();
        }

        //opens the two-way SMS phone app
        /**
         * @return {boolean}
         */
        function SMS_bot(pid) {
            top.restoreSession();
            var from = <?php echo js_escape($from_date); ?>;
            var to = <?php echo js_escape($to_date); ?>;
            var oefrom = <?php echo js_escape(oeFormatShortDate($from_date)); ?>;
            var oeto = <?php echo js_escape(oeFormatShortDate($to_date)); ?>;
            window.open('../main/messages/messages.php?nomenu=1&go=SMS_bot&pid=' + encodeURIComponent(pid) + '&to=' + encodeURIComponent(to) + '&from=' + encodeURIComponent(from) + '&oeto=' + encodeURIComponent(oeto) + '&oefrom=' + encodeURIComponent(oefrom), 'SMS_bot', 'width=370,height=600,resizable=0');
            return false;
        }

        function kiosk_FLB() {
            $("#kiosk").val('1');
            $("[name='kiosk_hide']").hide();
            $("[name='kiosk_show']").show();
            var i = document.getElementById("flb_table");
            // go full-screen
            if (i.requestFullscreen) {
                i.requestFullscreen();
            } else if (i.webkitRequestFullscreen) {
                i.webkitRequestFullscreen();
            } else if (i.mozRequestFullScreen) {
                i.mozRequestFullScreen();
            } else if (i.msRequestFullscreen) {
                i.msRequestFullscreen();
            }
            // refreshMe();
        }

        $(function () {
            refreshMe();
            $("#kiosk").val('');
            $("[name='kiosk_hide']").show();
            $("[name='kiosk_show']").hide();

            onresize = function () {
                var state = 1 >= outerHeight - innerHeight ? "fullscreen" : "windowed";
                if (window.state === state) return;
                window.state = state;
                var event = document.createEvent("Event");
                event.initEvent(state, true, true);
                window.dispatchEvent(event);
            };

            addEventListener('windowed', function (e) {
                $("#kiosk").val('');
                $("[name='kiosk_hide']").show();
                $("[name='kiosk_show']").hide();
                //alert(e.type);
            }, false);
            addEventListener('fullscreen', function (e) {
                $("#kiosk").val('1');
                $("[name='kiosk_hide']").hide();
                $("[name='kiosk_show']").show();
                //alert(e.type);
            }, false);

            <?php if ($GLOBALS['pat_trkr_timer'] != '0') { ?>
                var reftime = <?php echo js_escape($GLOBALS['pat_trkr_timer']); ?>;
                var parsetime = reftime.split(":");
                parsetime = (parsetime[0] * 60) + (parsetime[1] * 1) * 1000;
                if (auto_refresh) clearInteral(auto_refresh);
                auto_refresh = setInterval(function () {
                    refreshMe(true) // this will run after every parsetime seconds
                }, parsetime);
            <?php } ?>

            $('.js-blink-infinite').each(function () {
                // set up blinking text
                var elem = $(this);
                setInterval(function () {
                    if (elem.css('visibility') === 'hidden') {
                        elem.css('visibility', 'visible');
                    } else {
                        elem.css('visibility', 'hidden');
                    }
                }, 500);
            });
            // toggle of the check box status for drug screen completed and ajax call to update the database
            $('body').on('click', '.drug_screen_completed', function () {
                top.restoreSession();
                if (this.checked) {
                    testcomplete_toggle = "true";
                } else {
                    testcomplete_toggle = "false";
                }
                $.post("../../library/ajax/drug_screen_completed.php", {
                    trackerid: this.id,
                    testcomplete: testcomplete_toggle,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                });
            });

            // mdsupport - Immediately post changes to setting_new_window
            $('body').on('click', '#setting_new_window', function () {
                $('#setting_new_window').val(this.checked ? 'checked' : ' ');
                $.post("<?php echo $GLOBALS['webroot'] . "/interface/patient_tracker/patient_tracker.php"; ?>", {
                    setting_new_window: $('#setting_new_window').val(),
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }).done(
                    function (data) {
                });
            });


            $('#filter_submit').click(function (e) {
                e.preventDefault;
                top.restoreSession;
                refreshMe();
            });

            $('[data-toggle="tooltip"]').tooltip();

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

        });

        function initTableButtons() {
            $('#refreshme').click(function () {
                refreshMe();
                refineMe();
            });

            $('#setting_cog').click(function () {
                $(this).css("display", "none");
                $('#settings').css("display", "inline");
            });

            $('#settings').css("display", "none");
        }

        initTableButtons();

    </script>
<?php } ?>
