<?php
/**
 * fxm.inc.php
 *
 * LibreNMS OS poller module for Alpha FXM
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

$fxm_tmp  = snmp_get_multi_oid($device, ['upsIdentProductCode.0', 'upsIdentUPSSoftwareVersion.0'], '-OUQs', 'Argus-Power-System-MIB');
$hardware = $fxm_tmp['upsIdentProductCode.0'];
$version  = $fxm_tmp['upsIdentUPSSoftwareVersion.0'];
unset($fxm_tmp);
