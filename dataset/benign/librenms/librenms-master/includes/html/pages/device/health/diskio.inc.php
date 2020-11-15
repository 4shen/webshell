<?php

$row = 1;

foreach (get_disks($device['device_id']) as $drive) {
    if (is_integer($row / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    $fs_url = 'device/device='.$device['device_id'].'/tab=health/metric=diskio/';

    $graph_array_zoom['id']     = $drive['diskio_id'];
    $graph_array_zoom['type']   = 'diskio_ops';
    $graph_array_zoom['width']  = '400';
    $graph_array_zoom['height'] = '125';
    $graph_array_zoom['from'] = \LibreNMS\Config::get('time.twoday');
    $graph_array_zoom['to'] = \LibreNMS\Config::get('time.now');

    $overlib_link = overlib_link($fs_url, $drive['diskio_descr'], generate_graph_tag($graph_array_zoom), null);

    $types = array(
              'diskio_bits',
              'diskio_ops',
             );

    foreach ($types as $graph_type) {
        $graph_array         = array();
        $graph_array['id']   = $drive['diskio_id'];
        $graph_array['type'] = $graph_type;
        if ($graph_array['type']=="diskio_ops") {
            $graph_type_title="Ops/sec";
        }
        if ($graph_array['type']=="diskio_bits") {
            $graph_type_title="bps";
        }
        echo "<div class='panel panel-default'>
                <div class='panel-heading'>
                <h3 class='panel-title'>$overlib_link - $graph_type_title</h3>
            </div>";
        echo "<div class='panel-body'>";
            include 'includes/html/print-graphrow.inc.php';
        echo '</div></div>';
    }

    $row++;
}
