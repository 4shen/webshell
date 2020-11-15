<?php
/*
 * LibreNMS
 *
 * ptp600.inc.php
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
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */
use LibreNMS\RRD\RrdDefinition;

$version = $device['sysDescr'];
$masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CANOPY-SYS-MIB"));
$hardware = 'PTP 600 '.$masterSlaveMode;
