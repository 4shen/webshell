<?php

//验证密码
$password = "best33-freehao123";

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>PHP在线解压缩程序 - freehao123专版 - Best33.com!</title>
<style type="text/css">
<!--
body,td{
	font-size: 14px;
	color: #000000;
}
a {
	color: #000066;
	text-decoration: none;
}
a:hover {
	color: #FF6600;
	text-decoration: underline;
}
.STYLE2 {color: #FF0000}
.STYLE3 {color: #6600FF}
.STYLE4 {color: #333333; font-weight: bold; }
-->
</style>
</head>

<body>
  <form name="myform" method="post" action="<?=$_SERVER[PHP_SELF];?>" enctype="multipart/form-data" onSubmit="return check_uploadObject(this);">
<?
	if(!$_REQUEST["myaction"]):
?>

<script language="javascript">
function check_uploadObject(form){
	if(form.password.value==''){
		alert('请输入密码.');
		return false;
	}
	return true;
}
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr>
      <td height="40" colspan="2" style="color:#FF9900"><p><font color="#FF0000">【在线解压】</font></p>
	  
    </tr>
    <tr>
      <td width="11%">选择ZIP文件: </td>
      <td width="89%"><select name="zipfile">
        <option value="" selected>- 请选择 -</option>
<?
  	$fdir = opendir('./');
	while($file=readdir($fdir)){
		if(!is_file($file)) continue;
		if(preg_match('/\.zip$/mis',$file)){
			echo "<option value='$file'>$file</option>\r\n";
		}
	}
?>
      </select></td>
    </tr>
    <tr>
      <td width="11%" nowrap>或上传文件: </td>
      <td width="89%"><input name="upfile" type="file" id="upfile" size="20"></td>
    </tr>
	<tr>
      <td>解压到目录: </td>
      <td><input name="todir" type="text" id="todir" value="unzip/" size="20"> 
      (必须有写入权限，请先创建该目录)</td>
    </tr>
	<tr>
      <td>验证密码: </td>
      <td><input name="password" type="password" id="password" size="15"> 
        （请打开源文件找到第4行处，将引号内内容修改为您的密码并保存并在此处填写！）</td>
    </tr>	
    <tr>
      <td><input name="myaction" type="hidden" id="myaction" value="dounzip"></td>
      <td><input type="submit" name="Submit" value=" 解 压 "></td>
    </tr>
  </table>

<?

elseif($_REQUEST["myaction"]=="dounzip"):


class zip
{

 var $total_files = 0;
 var $total_folders = 0; 

 function Extract ( $zn, $to, $index = Array(-1) )
 {
   $ok = 0; $zip = @fopen($zn,'rb');
   if(!$zip) return(-1);
   $cdir = $this->ReadCentralDir($zip,$zn);
   $pos_entry = $cdir['offset'];

   if(!is_array($index)){ $index = array($index);  }
   for($i=0; $index[$i];$i++){
   		if(intval($index[$i])!=$index[$i]||$index[$i]>$cdir['entries'])
		return(-1);
   }
   for ($i=0; $i<$cdir['entries']; $i++)
   {
     @fseek($zip, $pos_entry);
     $header = $this->ReadCentralFileHeaders($zip);
     $header['index'] = $i; $pos_entry = ftell($zip);
     @rewind($zip); fseek($zip, $header['offset']);
     if(in_array("-1",$index)||in_array($i,$index))
     	$stat[$header['filename']]=$this->ExtractFile($header, $to, $zip);
   }
   fclose($zip);
   return $stat;
 }

  function ReadFileHeader($zip)
  {
    $binary_data = fread($zip, 30);
    $data = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);

    $header['filename'] = fread($zip, $data['filename_len']);
    if ($data['extra_len'] != 0) {
      $header['extra'] = fread($zip, $data['extra_len']);
    } else { $header['extra'] = ''; }

    $header['compression'] = $data['compression'];$header['size'] = $data['size'];
    $header['compressed_size'] = $data['compressed_size'];
    $header['crc'] = $data['crc']; $header['flag'] = $data['flag'];
    $header['mdate'] = $data['mdate'];$header['mtime'] = $data['mtime'];

    if ($header['mdate'] && $header['mtime']){
     $hour=($header['mtime']&0xF800)>>11;$minute=($header['mtime']&0x07E0)>>5;
     $seconde=($header['mtime']&0x001F)*2;$year=(($header['mdate']&0xFE00)>>9)+1980;
     $month=($header['mdate']&0x01E0)>>5;$day=$header['mdate']&0x001F;
     $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
    }else{$header['mtime'] = time();}

    $header['stored_filename'] = $header['filename'];
    $header['status'] = "ok";
    return $header;
  }

 function ReadCentralFileHeaders($zip){
    $binary_data = fread($zip, 46);
    $header = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);

    if ($header['filename_len'] != 0)
      $header['filename'] = fread($zip,$header['filename_len']);
    else $header['filename'] = '';

    if ($header['extra_len'] != 0)
      $header['extra'] = fread($zip, $header['extra_len']);
    else $header['extra'] = '';

    if ($header['comment_len'] != 0)
      $header['comment'] = fread($zip, $header['comment_len']);
    else $header['comment'] = '';

    if ($header['mdate'] && $header['mtime'])
    {
      $hour = ($header['mtime'] & 0xF800) >> 11;
      $minute = ($header['mtime'] & 0x07E0) >> 5;
      $seconde = ($header['mtime'] & 0x001F)*2;
      $year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
      $month = ($header['mdate'] & 0x01E0) >> 5;
      $day = $header['mdate'] & 0x001F;
      $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
    } else {
      $header['mtime'] = time();
    }
    $header['stored_filename'] = $header['filename'];
    $header['status'] = 'ok';
    if (substr($header['filename'], -1) == '/')
      $header['external'] = 0x41FF0010;
    return $header;
 }

 function ReadCentralDir($zip,$zip_name){
	$size = filesize($zip_name);

	if ($size < 277) $maximum_size = $size;
	else $maximum_size=277;
	
	@fseek($zip, $size-$maximum_size);
	$pos = ftell($zip); $bytes = 0x00000000;
	
	while ($pos < $size){
		$byte = @fread($zip, 1); $bytes=($bytes << 8) | ord($byte);
		if ($bytes == 0x504b0506 or $bytes == 0x2e706870504b0506){ $pos++;break;} $pos++;
	}
	
	$fdata=fread($zip,18);
	
	$data=@unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size',$fdata);
	
	if ($data['comment_size'] != 0) $centd['comment'] = fread($zip, $data['comment_size']);
	else $centd['comment'] = ''; $centd['entries'] = $data['entries'];
	$centd['disk_entries'] = $data['disk_entries'];
	$centd['offset'] = $data['offset'];$centd['disk_start'] = $data['disk_start'];
	$centd['size'] = $data['size'];  $centd['disk'] = $data['disk'];
	return $centd;
  }

 function ExtractFile($header,$to,$zip){
	$header = $this->readfileheader($zip);
	
	if(substr($to,-1)!="/") $to.="/";
	if($to=='./') $to = '';	
	$pth = explode("/",$to.$header['filename']);
	$mydir = '';
	for($i=0;$i<count($pth)-1;$i++){
		if(!$pth[$i]) continue;
		$mydir .= $pth[$i]."/";
		if((!is_dir($mydir) && @mkdir($mydir,0777)) || (($mydir==$to.$header['filename'] || ($mydir==$to && $this->total_folders==0)) && is_dir($mydir)) ){
			@chmod($mydir,0777);
			$this->total_folders ++;
			echo "<input name='dfile[]' type='checkbox' value='$mydir' checked> <a href='$mydir' target='_blank'>目录: $mydir</a><br>";
		}
	}
	
	if(strrchr($header['filename'],'/')=='/') return;	

	if (!($header['external']==0x41FF0010)&&!($header['external']==16)){
		if ($header['compression']==0){
			$fp = @fopen($to.$header['filename'], 'wb');
			if(!$fp) return(-1);
			$size = $header['compressed_size'];
		
			while ($size != 0){
				$read_size = ($size < 2048 ? $size : 2048);
				$buffer = fread($zip, $read_size);
				$binary_data = pack('a'.$read_size, $buffer);
				@fwrite($fp, $binary_data, $read_size);
				$size -= $read_size;
			}
			fclose($fp);
			touch($to.$header['filename'], $header['mtime']);
		}else{
			$fp = @fopen($to.$header['filename'].'.gz','wb');
			if(!$fp) return(-1);
			$binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']),
			Chr(0x00), time(), Chr(0x00), Chr(3));
			
			fwrite($fp, $binary_data, 10);
			$size = $header['compressed_size'];
		
			while ($size != 0){
				$read_size = ($size < 1024 ? $size : 1024);
				$buffer = fread($zip, $read_size);
				$binary_data = pack('a'.$read_size, $buffer);
				@fwrite($fp, $binary_data, $read_size);
				$size -= $read_size;
			}
		
			$binary_data = pack('VV', $header['crc'], $header['size']);
			fwrite($fp, $binary_data,8); fclose($fp);
	
			$gzp = @gzopen($to.$header['filename'].'.gz','rb') or die("Cette archive est compress閑");
			if(!$gzp) return(-2);
			$fp = @fopen($to.$header['filename'],'wb');
			if(!$fp) return(-1);
			$size = $header['size'];
		
			while ($size != 0){
				$read_size = ($size < 2048 ? $size : 2048);
				$buffer = gzread($gzp, $read_size);
				$binary_data = pack('a'.$read_size, $buffer);
				@fwrite($fp, $binary_data, $read_size);
				$size -= $read_size;
			}
			fclose($fp); gzclose($gzp);
		
			touch($to.$header['filename'], $header['mtime']);
			@unlink($to.$header['filename'].'.gz');
			
		}
	}
	
	$this->total_files ++;
	echo "<input name='dfile[]' type='checkbox' value='$to$header[filename]' checked> <a href='$to$header[filename]' target='_blank'>文件: $to$header[filename]</a><br>";

	return true;
 }

// end class
}

	set_time_limit(0);

	if ($_POST['password'] != $password) die("输入的密码不正确，请重新输入。");
	if(!$_POST["todir"]) $_POST["todir"] = ".";
	$z = new Zip;
	$have_zip_file = 0;
	function start_unzip($tmp_name,$new_name,$checked){
		global $_POST,$z,$have_zip_file;
		$upfile = array("tmp_name"=>$tmp_name,"name"=>$new_name);
		if(is_file($upfile[tmp_name])){
			$have_zip_file = 1;
			echo "<br>正在解压: <input name='dfile[]' type='checkbox' value='$upfile[name]' ".($checked?"checked":"")."> $upfile[name]<br><br>";
			if(preg_match('/\.zip$/mis',$upfile[name])){
				$result=$z->Extract($upfile[tmp_name],$_POST["todir"]);
				if($result==-1){
					echo "<br>文件 $upfile[name] 错误.<br>";
				}
				echo "<br>完成,共建立 $z->total_folders 个目录,$z->total_files 个文件.<br><br><br>";
			}else{
				echo "<br>$upfile[name] 不是 zip 文件.<br><br>";			
			}
			if(realpath($upfile[name])!=realpath($upfile[tmp_name])){
				@unlink($upfile[name]);
				rename($upfile[tmp_name],$upfile[name]);
			}
		}
	}
	clearstatcache();
	
	start_unzip($_POST["zipfile"],$_POST["zipfile"],0);
	start_unzip($_FILES["upfile"][tmp_name],$_FILES["upfile"][name],1);

	if(!$have_zip_file){
		echo "<br>请选择或上传文件.<br>";
	}
?>
<input name="password" type="hidden" id="password" value="<?=$_POST['password'];?>">
<input name="myaction" type="hidden" id="myaction" value="dodelete">
<input name="按钮" type="button" value="返回" onClick="window.location='<?=$_SERVER[PHP_SELF];?>';">

<input type='button' value='反选' onclick='selrev();'> <input type='submit' onclick='return confirm("删除选定文件?");' value='删除选定'>

<script language='javascript'>
function selrev() {
	with(document.myform) {
		for(i=0;i<elements.length;i++) {
			thiselm = elements[i];
			if(thiselm.name.match(/dfile\[]/))	thiselm.checked = !thiselm.checked;
		}
	}
}
alert('完成.');
</script>
<?

elseif($_REQUEST["myaction"]=="dodelete"):
	set_time_limit(0);
	if ($_POST['password'] != $password) die("输入的密码不正确，请重新输入。");
	
	$dfile = $_POST["dfile"]; 
	echo "正在删除文件...<br><br>";
	if(is_array($dfile)){
		for($i=count($dfile)-1;$i>=0;$i--){
			if(is_file($dfile[$i])){
				if(@unlink($dfile[$i])){
					echo "已删除文件: $dfile[$i]<br>";
				}else{
					echo "删除文件失败: $dfile[$i]<br>";
				}
			}else{
				if(@rmdir($dfile[$i])){
					echo "已删除目录: $dfile[$i]<br>";
				}else{
					echo "删除目录失败: $dfile[$i]<br>";
				}				
			}
			
		}
	}
	echo "<br>完成<br><br><input type='button' value='返回' onclick=\"window.location='$_SERVER[PHP_SELF]';\"><br><br>
		 <script language='javascript'>('完成');</script>";

endif;

?>
  </form>
  <p>&nbsp;</p>
  <p align="center"> </p>
</body>
</html>