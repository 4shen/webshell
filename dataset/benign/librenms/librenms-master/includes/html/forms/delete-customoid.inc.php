<?php

header('Content-type: text/plain');

if (!Auth::user()->hasGlobalAdmin()) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

if (!is_numeric($_POST['customoid_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    if (dbDelete('customoids', '`customoid_id` =  ?', array($_POST['customoid_id']))) {
        echo 'Custom OID has been deleted.';
        exit;
    } else {
        echo 'ERROR: Custom OID has not been deleted.';
        exit;
    }
}
