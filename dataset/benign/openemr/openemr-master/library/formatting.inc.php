<?php

/**
 * Formatting library.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function oeFormatMoney($amount, $symbol = false)
{
    $s = number_format(
        floatval($amount),
        $GLOBALS['currency_decimals'],
        $GLOBALS['currency_dec_point'],
        $GLOBALS['currency_thousands_sep']
    );
  // If the currency symbol exists and is requested, prepend it.
    if ($symbol && !empty($GLOBALS['gbl_currency_symbol'])) {
        $s = $GLOBALS['gbl_currency_symbol'] . " $s";
    }

    return $s;
}

function oeFormatShortDate($date = 'today', $showYear = true)
{
    if ($date === 'today') {
        $date = date('Y-m-d');
    }

    if (strlen($date) >= 10) {
        // assume input is yyyy-mm-dd
        if ($GLOBALS['date_display_format'] == 1) {      // mm/dd/yyyy, note year is added below
            $newDate = substr($date, 5, 2) . '/' . substr($date, 8, 2);
        } elseif ($GLOBALS['date_display_format'] == 2) { // dd/mm/yyyy, note year is added below
            $newDate = substr($date, 8, 2) . '/' . substr($date, 5, 2);
        }

        // process the year (add for formats 1 and 2; remove for format 0)
        if ($GLOBALS['date_display_format'] == 1 || $GLOBALS['date_display_format'] == 2) {
            if ($showYear) {
                $newDate .= '/' . substr($date, 0, 4);
            }
        } elseif (!$showYear) { // $GLOBALS['date_display_format'] == 0
            // need to remove the year
            $newDate = substr($date, 5, 2) . '-' . substr($date, 8, 2);
        } else { // $GLOBALS['date_display_format'] == 0
            // keep the year (so will simply be the original $date)
            $newDate = substr($date, 0, 10);
        }

        return $newDate;
    }

  // this is case if the $date does not have 10 characters
    return $date;
}

// 0 - Time format 24 hr
// 1 - Time format 12 hr
function oeFormatTime($time, $format = "global", $seconds = false)
{
    if (empty($time)) {
        return "";
    }

    $formatted = $time;

    if ($format === "global") {
        $format = $GLOBALS['time_display_format'];
    }


    if ($format == 1) {
        if ($seconds) {
            $formatted = date("g:i:s a", strtotime($time));
        } else {
            $formatted = date("g:i a", strtotime($time));
        }
    } else { // ($format == 0)
        if ($seconds) {
            $formatted = date("H:i:s", strtotime($time));
        } else {
            $formatted = date("H:i", strtotime($time));
        }
    }

    return $formatted;
}

/**
 * Returns the complete formatted datetime string according the global date and time format
 * @param $datetime
 * @return string
 */
function oeFormatDateTime($datetime, $formatTime = "global", $seconds = false)
{
    return oeFormatShortDate(substr($datetime, 0, 10)) . " " . oeFormatTime(substr($datetime, 11), $formatTime, $seconds);
}

/**
 * Returns the complete formatted datetime string according the global date and time format
 * @param $timestamp
 * @return string
 */
function oeTimestampFormatDateTime($timestamp)
{
    if (!$timestamp) {
        $timestamp = strtotime(date('Y-m-d H:i'));
    }

    if ($GLOBALS['time_display_format'] == 0) {
        $timeFormat = 'H:i';
    } else { // $GLOBALS['time_display_format'] == 1
        $timeFormat = 'g:i a';
    }

    if ($GLOBALS['date_display_format'] == 1) { // mm/dd/yyyy
        $newDate = date('m/d/Y ' . $timeFormat, $timestamp);
    } elseif ($GLOBALS['date_display_format'] == 2) { // dd/mm/yyyy
        $newDate = date('d/m/Y ' . $timeFormat, $timestamp);
    } else { // yyyy-mm-dd
        $newDate = date('Y-m-d ' . $timeFormat, $timestamp);
    }

    return $newDate;
}

// Format short date from time.
function oeFormatSDFT($time)
{
    return oeFormatShortDate(date('Y-m-d', $time));
}

// Format the body of a patient note.
function oeFormatPatientNote($note)
{
    $i = 0;
    while ($i !== false) {
        if (preg_match('/^\d\d\d\d-\d\d-\d\d/', substr($note, $i))) {
            $note = substr($note, 0, $i) . oeFormatShortDate(substr($note, $i, 10)) . substr($note, $i + 10);
        }

        $i = strpos($note, "\n", $i);
        if ($i !== false) {
            ++$i;
        }
    }

    return $note;
}

function oeFormatClientID($id)
{

  // TBD

    return $id;
}
//----------------------------------------------------
function DateFormatRead($mode = 'legacy')
{
    //For the 3 supported date format,the javascript code also should be twicked to display the date as per it.
    //Output of this function is given to 'ifFormat' parameter of the 'Calendar.setup'.
    //This will show the date as per the global settings.
    if ($GLOBALS['date_display_format'] == 0) {
        if ($mode == 'legacy') {
            return "%Y-%m-%d";
        } elseif ($mode == 'validateJS') {
            return "YYYY-MM-DD";
        } else { //$mode=='jquery-datetimepicker'
            return "Y-m-d";
        }
    } elseif ($GLOBALS['date_display_format'] == 1) {
        if ($mode == 'legacy') {
            return "%m/%d/%Y";
        } elseif ($mode == 'validateJS') {
            return "MM/DD/YYYY";
        } else { //$mode=='jquery-datetimepicker'
            return "m/d/Y";
        }
    } elseif ($GLOBALS['date_display_format'] == 2) {
        if ($mode == 'legacy') {
            return "%d/%m/%Y";
        } elseif ($mode == 'validateJS') {
            return "DD/MM/YYYY";
        } else { //$mode=='jquery-datetimepicker'
            return "d/m/Y";
        }
    }
}

function DateToYYYYMMDD($DateValue)
{
    //With the help of function DateFormatRead() now the user can enter date is any of the 3 formats depending upon the global setting.
    //But in database the date can be stored only in the yyyy-mm-dd format.
    //This function accepts a date in any of the 3 formats, and as per the global setting, converts it to the yyyy-mm-dd format.
    if (trim($DateValue) == '') {
        return '';
    }

    if ($GLOBALS['date_display_format'] == 0) {
        return $DateValue;
    } elseif ($GLOBALS['date_display_format'] == 1 || $GLOBALS['date_display_format'] == 2) {
        $DateValueArray = explode('/', $DateValue);
        if ($GLOBALS['date_display_format'] == 1) {
            return $DateValueArray[2] . '-' . $DateValueArray[0] . '-' . $DateValueArray[1];
        }

        if ($GLOBALS['date_display_format'] == 2) {
            return $DateValueArray[2] . '-' . $DateValueArray[1] . '-' . $DateValueArray[0];
        }
    }
}

function TimeToHHMMSS($TimeValue)
{
    //For now, just return the $TimeValue, since input fields are not formatting time.
    // This can be upgraded if decided to format input time fields.

    if (trim($TimeValue) == '') {
        return '';
    }

    return $TimeValue;
}


function DateTimeToYYYYMMDDHHMMSS($DateTimeValue)
{
    //This function accepts a timestamp in any of the selected formats, and as per the global setting, converts it to the yyyy-mm-dd hh:mm:ss format.

    // First deal with the date
    $fixed_date = DateToYYYYMMDD(substr($DateTimeValue, 0, 10));

    // Then deal with the time
    $fixed_time = TimeToHHMMSS(substr($DateTimeValue, 11));

    if (empty($fixed_date) && empty($fixed_time)) {
        return "";
    } else {
        return $fixed_date . " " . $fixed_time;
    }
}

// Returns age in a desired format:
//   0 = "xx month(s)" if < 2 years, else years
//   1 = Years      : just a number
//   2 = Months     : just a number
//   3 = Gestational: "xx week(s) y day(s)"
// $dobYMD is YYYYMMDD or YYYY-MM-DD
// $nowYMD is same format but optional
//
function oeFormatAge($dobYMD, $nowYMD = '', $format = 0)
{
  // Strip any dashes from the dates.
    $dobYMD = preg_replace('/-/', '', $dobYMD);
    $nowYMD = preg_replace('/-/', '', $nowYMD);
    $dobDay   = substr($dobYMD, 6, 2);
    $dobMonth = substr($dobYMD, 4, 2);
    $dobYear  = substr($dobYMD, 0, 4);

    if ($nowYMD) {
        $nowDay   = substr($nowYMD, 6, 2);
        $nowMonth = substr($nowYMD, 4, 2);
        $nowYear  = substr($nowYMD, 0, 4);
    } else {
        $nowDay   = date("d");
        $nowMonth = date("m");
        $nowYear  = date("Y");
    }

    if ($format == 3) {
        // Gestational age as weeks and days.
        $secs = mktime(0, 0, 0, $nowMonth, $nowDay, $nowYear) -
            mktime(0, 0, 0, $dobMonth, $dobDay, $dobYear);
        $days  = intval($secs / (24 * 60 * 60));
        $weeks = intval($days / 7);
        $days  = $days % 7;
        $age   = "$weeks " . ($weeks == 1 ? xl('week') : xl('weeks')) .
             " $days " . ($days  == 1 ? xl('day') : xl('days'));
    } else {
        // Years or months.
        $dayDiff   = $nowDay   - $dobDay;
        $monthDiff = $nowMonth - $dobMonth;
        $yearDiff  = $nowYear  - $dobYear;
        $ageInMonths = $yearDiff * 12 + $monthDiff;
        if ($dayDiff < 0) {
            --$ageInMonths;
        }

        if ($format == 1 || ($format == 0 && $ageInMonths >= 24)) {
            $age = $yearDiff;
            if ($monthDiff < 0 || ($monthDiff == 0 && $dayDiff < 0)) {
                --$age;
            }
        } else {
            $age = $ageInMonths;
            if ($format == 0) {
                $age .= ' ' . $ageInMonths == 1 ? xl('month') : xl('months');
            }
        }
    }

    return $age;
}
