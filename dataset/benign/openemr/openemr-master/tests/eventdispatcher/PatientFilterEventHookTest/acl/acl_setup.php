<?php

// Ensure this script is not called separately
if (
    (empty($_SESSION['acl_setup_unique_id'])) ||
    (empty($unique_id)) ||
    ($unique_id != $_SESSION['acl_setup_unique_id'])
) {
    die('Authentication Error');
}
unset($_SESSION['acl_setup_unique_id']);

use OpenEMR\Common\Acl\AclExtended;

AclExtended::addObjectSectionAcl('pfeh', 'PatientFilterEventHook');

AclExtended::addObjectAcl('pfeh', 'Fields Filter', 'field_filter', 'Fields Filter');
AclExtended::addObjectAcl('pfeh', 'Hook Filter', 'hooks', 'Hook Filter');
AclExtended::addObjectAcl('pfeh', 'Parameters', 'params', 'Parameters');

$physicians_write = AclExtended::getAclIdNumber('Physicians', 'write');
AclExtended::updateAcl($physicians_write, 'Physicians', 'pfeh', 'Fields Filter', 'field_filter', 'Fields Filter', 'write');
AclExtended::updateAcl($physicians_write, 'Physicians', 'pfeh', 'Hook Filter', 'hooks', 'Hook Filter', 'write');
AclExtended::updateAcl($physicians_write, 'Physicians', 'pfeh', 'Parameters', 'params', 'Parameters', 'write');

?>
<html>
<head>
    <title>PatientFilterEventHookTest ACL Setup</title>
    <link rel=STYLESHEET href="interface/themes/style_blue.css">
</head>
<body>
<b>OpenEMR[PatientFilterEventHookTest] ACL Setup</b>
<br>
All done configuring and installing access controls (php-GACL)!
</body>
</html>

