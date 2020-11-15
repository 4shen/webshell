<?php

/**
 * Dictation form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    cfapress <cfapress>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2013-2017 bradymiller <bradymiller@users.sourceforge.net>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html>
<head>
    <title><?php echo xlt("Dictation"); ?></title>

    <?php Header::setupHeader();?>
</head>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h2><?php echo xlt("Dictation"); ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form name="my_form" method=post action="<?php echo $rootdir;?>/forms/dictation/save.php?mode=new" onsubmit="return top.restoreSession()">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend><?php echo xlt('Dictation')?></legend>
                        <div class="form-group">
                            <div class="col-sm-10 offset-sm-1">
                                <textarea name="dictation" class="form-control" cols="80" rows="15" ></textarea>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Additional Notes'); ?></legend>
                        <div class="form-group">
                            <div class="col-sm-10 offset-sm-1">
                                <textarea name="additional_notes" class="form-control" cols="80" rows="5" ></textarea>
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
    </div>
</body>
</html>
