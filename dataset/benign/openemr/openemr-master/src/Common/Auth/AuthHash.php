<?php

/**
 * AuthHash class.
 *
 *   Hashing:
 *     1. This class can be run in 1 of 2 modes:
 *         -auth:  Hashing of passwords used for user authentication. The algorithm used for this mode can be chosen at
 *                  Administration->Globals->Security->'Hash Algorithm for Authentication'. This chosen algorithm will be used
 *                  for the following:
 *                    - Main login
 *                    - Patient Portal login
 *                    - API authentication (when user is requesting a API token)
 *                  These use cases are only for when users login, so are relatively infrequent, and the passwords
 *                  are under the users control (ie. good chance are not strong password); thus an expensive, time
 *                  consuming hash mechanism makes sense in this mode.
 *         -token: Hashing of part of user token that is used for verifying the token. The algorithm used for this mode
 *                  can be chosen at Administration->Globals->Security->'Hash Algorithm for Token'. This use case is for anytime
 *                  a API token is sent to OpenEMR which can be very frequent. Also,the token that is hashed is 32
 *                  random characters (very strong) and has a limited lifespan; thus an expensive, time consuming hash mechanism
 *                  can be avoided in this mode.
 *         -other: If no mode is chosen, then will default to auth mode.
 *     2. The passwordVerify function is static and is a wrapper for the php password_verify() function that will allow a
 *         debugging mode (Administration->Globals->Security->Debug Hash Verification Time) to measure the time it takes
 *         to verify the hash to allow fine tuning of chosen algorithm and algorithm options.
 *     3. The algorithms and algorithm options can be found in the Administration->Globals->Security settings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

class AuthHash
{
    private $mode;          // Supports 2 modes, 'auth' and 'token'
                            //  Note this is used to collect the mode specific algorithm options from globals

    private $algo;          // Algorithm setting from globals
    private $algo_constant; // Standard algorithm constant

    private $options;       // Standardized array of options

    public function __construct($mode)
    {
        // Set the mode and collect the pertinent algorithm setting from globals
        if ($mode == 'auth') {
            $this->mode = 'auth';
        } elseif ($mode == 'token') {
            $this->mode = 'token';
        } else {
            // if no mode or other mode is given, then will default to 'auth' mode
            $this->mode = 'auth';
        }
        $this->algo = $GLOBALS['gbl_' . $this->mode . '_hash_algo'];

        // If set to php default algorithm, then figure out what it is.
        //  This basically resolves what PHP is using as PASSWORD_DEFAULT,
        //  which has been PASSWORD_BCRYPT since PHP 5.5. In future PHP versions,
        //  though, it will likely change to one of the Argon2 algorithms. And
        //  in this case, the below block of code will automatically support this
        //  transition.
        if ($this->algo == "DEFAULT") {
            if (PASSWORD_DEFAULT == PASSWORD_BCRYPT) {
                $this->algo = "BCRYPT";
            } elseif (PASSWORD_DEFAULT == PASSWORD_ARGON2I) {
                $this->algo = "ARGON2I";
            } elseif (PASSWORD_DEFAULT == PASSWORD_ARGON2ID) {
                $this->algo = "ARGON2ID";
            } else {
                // $this->algo will stay "DEFAULT", which should never happen.
                // But if this does happen, will then not support any custom
                // options in below code since not sure what the algorithm is.
            }
        }

        // Ensure things don't break by only using a supported algorithm
        if (($this->algo == "ARGON2ID") && (!defined('PASSWORD_ARGON2ID'))) {
            // argon2id not supported, so will try argon2i instead
            $this->algo = "ARGON2I";
            error_log("OpenEMR WARNING: ARGON2ID not supported, so using ARGON2I instead");
        }
        if (($this->algo == "ARGON2I") && (!defined('PASSWORD_ARGON2I'))) {
            // argon2i not supported, so will use bcrypt instead
            $this->algo = "BCRYPT";
            error_log("OpenEMR WARNING: ARGON2I not supported, so using BCRYPT instead");
        }

        // Now can safely set up the algorithm and algorithm options
        if (($this->algo == "ARGON2ID") || ($this->algo == "ARGON2I")) {
            // Argon2
            if ($this->algo == "ARGON2ID") {
                // Using argon2ID
                $this->algo_constant = PASSWORD_ARGON2ID;
            }
            if ($this->algo == "ARGON2I") {
                // Using argon2I
                $this->algo_constant = PASSWORD_ARGON2I;
            }
            // Set up Argon2 options
            $temp_array = [];
            if (($GLOBALS['gbl_' . $this->mode . '_argon_hash_memory_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_' . $this->mode . '_argon_hash_memory_cost']))) {
                $temp_array['memory_cost'] = $GLOBALS['gbl_' . $this->mode . '_argon_hash_memory_cost'];
            }
            if (($GLOBALS['gbl_' . $this->mode . '_argon_hash_time_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_' . $this->mode . '_argon_hash_time_cost']))) {
                $temp_array['time_cost'] = $GLOBALS['gbl_' . $this->mode . '_argon_hash_time_cost'];
            }
            if (($GLOBALS['gbl_' . $this->mode . '_argon_hash_thread_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_' . $this->mode . '_argon_hash_thread_cost']))) {
                $temp_array['threads'] = $GLOBALS['gbl_' . $this->mode . '_argon_hash_thread_cost'];
            }
            if (!empty($temp_array)) {
                $this->options = $temp_array;
            }
        } elseif ($this->algo == "BCRYPT") {
            // Bcrypt - Using bcrypt and set up bcrypt options
            $this->algo_constant = PASSWORD_BCRYPT;
            if (($GLOBALS['gbl_' . $this->mode . '_bcrypt_hash_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_' . $this->mode . '_bcrypt_hash_cost']))) {
                $this->options = ['cost' => $GLOBALS['gbl_' . $this->mode . '_bcrypt_hash_cost']];
            }
        } else {
            // This should never happen.
            //  Will only happen if unable to map the DEFAULT setting above or if using a invalid setting other than
            //   BCRYPT, ARGON2I, or ARGON2ID.
            // If this happens, then will just go with PHP Default (ie. go with default php algorithm and options).
            $this->algo_constant = PASSWORD_DEFAULT;
            error_log("OpenEMR WARNING: Unable to resolve hashing preference, so using PHP Default");
        }
    }

    public function passwordHash(&$password)
    {
        if (empty($this->options)) {
            return password_hash($password, $this->algo_constant);
        } else {
            return password_hash($password, $this->algo_constant, $this->options);
        }
    }

    public function passwordNeedsRehash($hash)
    {
        if (empty($this->options)) {
            return password_needs_rehash($hash, $this->algo_constant);
        } else {
            return password_needs_rehash($hash, $this->algo_constant, $this->options);
        }
    }

    // To improve performance, this function is run as static since
    //  requires no defines from the class. The goal of this wrapper is
    //  to provide the execution timing debugging feature to allow
    //  tuning of the hashing (can turn the debugging feature on
    //  at Administration->Globals->Security->Debug Hash Verification Time).
    public static function passwordVerify(&$password, $hash)
    {
        if ($GLOBALS['gbl_debug_hash_verify_execution_time']) {
            // Reporting collection time to allow fine tuning of hashing algorithm
            $millisecondsStart = round(microtime(true) * 1000);
        }

        $valid = password_verify($password, $hash);

        if ($GLOBALS['gbl_debug_hash_verify_execution_time']) {
            // Reporting collection time to allow fine tuning of hashing algorithm
            $millisecondsStop = round(microtime(true) * 1000);
            error_log("Password hash verification execution time was following (milliseconds): " . errorLogEscape($millisecondsStop - $millisecondsStart));
        }

        return $valid;
    }
}
