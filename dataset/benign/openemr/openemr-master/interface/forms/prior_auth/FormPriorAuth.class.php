<?php

/**
 * prior auth form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

/**
 * class PriorAuth
 *
 */
class FormPriorAuth extends ORDataObject
{

    /**
     *
     * @access public
     */



    /**
     *
     * @access private
     */

    var $id;
    var $date;
    var $pid;
    var $activity;
    var $prior_auth_number;
    var $comments;


    /**
     * Constructor sets all Form attributes to their default value
     */

    function __construct($id = "", $_prefix = "")
    {
        parent::__construct();

        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
        }

        $this->_table = "form_prior_auth";
        $this->date = date("Y-m-d H:i:s");
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
        $this->prior_auth_number = "";
        if ($id != "") {
            $this->populate();
        }
    }

    function __toString()
    {
        return "ID: " . $this->id . "\n";
    }

    function set_id($id)
    {
        if (!empty($id) && is_numeric($id)) {
            $this->id = $id;
        }
    }
    function get_id()
    {
        return $this->id;
    }
    function set_pid($pid)
    {
        if (!empty($pid) && is_numeric($pid)) {
            $this->pid = $pid;
        }
    }
    function get_pid()
    {
        return $this->pid;
    }
    function set_activity($tf)
    {
        if (!empty($tf) && is_numeric($tf)) {
            $this->activity = $tf;
        }
    }
    function get_activity()
    {
        return $this->activity;
    }


    function set_comments($string)
    {
        $this->comments = $string;
    }

    function get_comments()
    {
        return $this->comments;
    }

    function set_prior_auth_number($string)
    {
        $this->prior_auth_number = $string;
    }

    function get_prior_auth_number()
    {
        return $this->prior_auth_number;
    }


    function get_date()
    {
        return $this->date;
    }
}   // end of Form
