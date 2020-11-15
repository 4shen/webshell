<?php
$name = 'postgres';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Rows/Sec';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

if (isset($vars['database'])) {
    $rrd_name_array=array('app', $name, $app_id, $vars['database']);
} else {
    $rrd_name_array=array('app', $name, $app_id);
}

$rrd_filename = rrd_name($device['hostname'], $rrd_name_array);

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Returned',
            'ds'       => 'ret',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Fetched',
            'ds'       => 'fetch',
            'colour'   => 'AA6C39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Inserted',
            'ds'       => 'ins',
            'colour'   => 'FFD1AA'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Updated',
            'ds'       => 'upd',
            'colour'   => '88CC88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Deleted',
            'ds'       => 'del',
            'colour'   => '28536C'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
