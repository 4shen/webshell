<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$temp_data = snmp_get_multi_oid($device, ['switchMemorySize.0', 'switchMemoryBusy.0'], '-OUQs', 'DCN-MIB');
$mempool['total'] = $temp_data['switchMemorySize.0'];
$mempool['used'] = $temp_data['switchMemoryBusy.0'];
$mempool['free'] = $mempool['total'] - $mempool['used'];
unset($temp_data);
