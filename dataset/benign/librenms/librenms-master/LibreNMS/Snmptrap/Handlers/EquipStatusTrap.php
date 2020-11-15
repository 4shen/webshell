<?php
/**
 * equipStatusTrap.php
 *
 * -Description-
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
 * @copyright  2018 Vitali Kari
 * @author     Vitali Kari <vitali.kari@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class EquipStatusTrap implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $state = $trap->getOidData('EQUIPMENT-MIB::equipStatus.0');

        $severity = $this->getSeverity($state);
        Log::event('SNMP Trap: Equipment Status  ' . $state, $device->device_id, 'state', $severity);
    }

    private function getSeverity($state)
    {
        $severity_map = [
            'warning' => 4,
            'major' => 4,
            '5' => 4,
            '3' => 4,
            'critical' => 5,
            '4' => 5,
            'minor' => 3,
            '2' => 3,
            'nonAlarmed' => 1,
            '1' => 1,
        ];
        return $severity_map[$state] ?? 0;
    }
}
