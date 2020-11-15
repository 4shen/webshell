<?php
/**
 * chatsworth-pdu.inc.php
 *
 * LibreNMS OS poller module for Legacy Chatsworth PDU
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

$serial = trim(snmp_get($device, '.1.3.6.1.4.1.30932.1.1.1.2.0', '-OQv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.30932.1.1.1.1.0', '-OQv'), '"');
