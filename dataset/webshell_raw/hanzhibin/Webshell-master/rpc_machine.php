<?php
    require "globals.php";
    require_once "include/jsonRPCServer.php";
    require_once "include/rpc_pdo.php";
    require "include/rpc_machine.php";
    $m = new machine();

    //$a=$m->machine_list();
    //$a=$m->machine_get("linux-64r5");
    //$a=$m->machine_search('zbhan');
    //echo json_encode($a);
    
    jsonRPCServer::handle($m) or print 'no request';
?>



