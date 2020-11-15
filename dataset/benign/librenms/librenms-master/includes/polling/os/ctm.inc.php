<?php

/*
 * LibreNMS Last Mile Gear CTM Polling module
 *
 * Copyright (c) 2018 Paul Heinrichs <pdheinrichs@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


$hardware = $device['sysDescr'];
$version = snmp_get($device, '1.3.6.1.4.1.25868.1.1.0', '-Ovqs');
