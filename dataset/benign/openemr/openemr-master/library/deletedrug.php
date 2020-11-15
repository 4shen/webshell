<?php

/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

require_once "../interface/globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

//ensure user has proper access
if (!AclMain::aclCheckCore('patient', 'rx', '', 'write')) {
    echo xlt('ACL Administration Not Authorized');
    exit;
}

$id = filter_input(INPUT_POST, 'drugId', FILTER_VALIDATE_INT);
$id = trim($id);
if (isset($id)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    try {
        $sql = "delete from prescriptions where id = ?";
        sqlQuery($sql, [$id]);
        echo xlt("Done");
    } catch (Exception $e) {
        echo 'Error Message: ' . text($e->getMessage());
    }
}
