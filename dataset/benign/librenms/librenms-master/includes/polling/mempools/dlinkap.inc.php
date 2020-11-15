<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */

d_echo('Dlink AP');
$memory_oid = $device['sysObjectID'].'.5.1.4.0';
$perc = snmp_get($device, $memory_oid, '-OvQ');
if (is_numeric($perc)) {
    $mempool['perc']  = $perc;
    $mempool['used']  = $perc;
    $mempool['total'] = 100;
    $mempool['free']  = 100 - $perc;
}
