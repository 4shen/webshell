<?php

/**
 * Clinical instructions form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
$check_res = $formid ? formFetch("form_clinical_instructions", $formid) : array();
?>
<html>
    <head>
        <title><?php echo xlt("Clinical Instructions"); ?></title>

        <?php Header::setupHeader(); ?>
    </head>
    <body class="body_top">
        <div class="container">
            <div class="row">
                <div class="page-header">
                    <h2><?php echo xlt('Clinical Instructions'); ?></h2>
                </div>
            </div>
            <div class="row">
                <form method="post" name="my_form" action="<?php echo $rootdir; ?>/forms/clinical_instructions/save.php?id=<?php echo attr_url($formid); ?>">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend class=""><?php echo xlt('Instructions'); ?></legend>
                            <div class="form-group">
                                <div class="col-sm-10 offset-sm-1">
                                    <textarea name="instruction" id ="instruction"  class="form-control" cols="80" rows="5" ><?php echo text($check_res['instruction']); ?></textarea>
                                </div>
                            </div>
                    </fieldset>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 offset-sm-1 position-override">
                            <div class="btn-group oe-opt-btn-group-pinch" role="group">
                                <button type='submit' onclick='top.restoreSession()' class="btn btn-secondary btn-save"><?php echo xlt('Save'); ?></button>
                                <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
