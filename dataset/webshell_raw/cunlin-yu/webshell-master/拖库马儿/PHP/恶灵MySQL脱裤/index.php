<?PHP
//���ò��֣� 
//ע�⣬�����û���ں����ע�͵ĵط����Ϲ���Ա�����֤����
//�벻Ҫ������ȷ�����ݿ��û��������룡
//����Ĭ���������У���������������д�ġ�

$db_host="localhost";    //���ݿ������
$db_username="admin";     //���ݿ��û���
$db_password="rE4)-U[r{8viQ-^_c>>^";         //���ݿ�����
$db_dbname="wiki";          //ѡ������ݿ�


//���ݵͰ汾PHP
function requestValues(){
	return ' if(!isset($_POST)){ $_POST = $HTTP_POST_VARS; $_GET = $HTTP_GET_VARS; $_SERVER = $HTTP_SERVER_VARS;} ';
}

eval(requestValues());

$_POST["frametopheight"]=90;  //FrameTop �ĸ�

define("VERSION","4.1 ����ս�Ӱ�"); //�汾

error_reporting(1);
@set_time_limit(0);

function num_bitunit($num){
  $bitunit=array(' B',' KB',' MB',' GB');
  for($key=0;$key<count($bitunit);$key++){
	if($num>=pow(2,10*$key)-1){ //1023B ����ʾΪ 1KB
	  $num_bitunit_str=(ceil($num/pow(2,10*$key)*100)/100)." $bitunit[$key]";
	}
  }
  return $num_bitunit_str;
}

//frame �ֿ�����
function frameset_html(){
	global $_POST;
	return "if(!\$_GET[framename]){
		echo \"<html>
		<head> <meta http-equiv='Content-Type' content='text/html; charset=gb2312'>
		<title>����ս��SQL�Ե������ݿⱸ�ݳ��� �� Powerd By faisun</title>
		</head>
		<frameset rows='$_POST[frametopheight],*,0' frameborder='NO' border='0' framespacing='0' name='myframeset'>
			<frame src='\$_SERVER[PHP_SELF]?action=topframe&framename=topframe' name='topFrame' scrolling='NO' noresize>
			<frame src='\$_SERVER[PHP_SELF]?\$_SERVER[QUERY_STRING]&framename=main' name='mainFrame1'>
			<frame src='about:blank' name='mainFrame2'>  
		</frameset>
		<BODY></BODY>
		</html>\";
		exit;
	}";
}

function postvars_function(){
	return '
	function fsql_StrCode($string,$action="ENCODE"){
		global $_SERVER;
		if($string=="") return "";
		if($action=="ENCODE") $md5code=substr(md5($string),8,10);
		else{
			$md5code=substr($string,-10); 
			$string=substr($string,0,strlen($string)-10); 
		}
		$key = md5($md5code.$_SERVER["HTTP_USER_AGENT"].filemtime($_SERVER["SCRIPT_FILENAME"]));
		$string = ($action=="ENCODE"?$string:base64_decode($string));
		$len = strlen($key);
		$code = "";
		for($i=0; $i<strlen($string); $i++){
			$k = $i%$len;
			$code .= $string[$i]^$key[$k];
		}
		$code = ($action == "DECODE" ? (substr(md5($code),8,10)==$md5code?$code:NULL) : base64_encode($code)."$md5code");
		return $code;
	}
	if($_POST[faisunsql_postvars]){
		if($faisunsql_postvars=unserialize(fsql_StrCode($_POST[faisunsql_postvars],"DECODE"))){
			//print_r($faisunsql_postvars);
			foreach($faisunsql_postvars as $key=>$value){
				if(!isset($_POST[$key])) $_POST[$key] = $value;
			}
		}else{ die("<script language=\'JavaScript\'>alert(\'�����ĵ�����,�ύ��Ϣ�Ѷ�ʧ,��Ҫ���¿�ʼ.\');</script>"); }
		unset($_POST[faisunsql_postvars],$faisunsql_postvars,$key,$value);
	}';	
}

eval(frameset_html().postvars_function());

if($_POST["totalsize"]){
	$totalsize_chunk=num_bitunit($_POST["totalsize"]);
}

//css ��ʽ����
function csssetting(){
  return "<style type='text/css'>
	<!--
	body, td, input, a{
		color:#985b00;
		font-family: '����';
		font-size: 9pt;
	}
	body, td, a{
		line-height:180%; 
	}
	.tabletitle{
		color:#FFFFFF;
		background-color:#000000;
	}
	.tabledata{
		background-color:#FFEECC;
	}
	.tabledata_on{
		background-color:#FFFFCC;
	}	
	input, .borderdiv {
		border:1px inset;
	}
	.zl_radio{
		border:0px;
	}
	.zl_input{
		border:1px inset;
		background-color:#FFFFCC;
	}
	-->
	</style>";
}

//��ҳ��ͬ��ҳ��ͷ
function fheader(){
	global $_POST;
	$str = fsql_StrCode(serialize($_POST),"ENCODE");
	echo "<html>
	<head> 
	<meta http-equiv='Content-Type' content='text/html; charset=gb2312'>
	<title>faisunSQL�Ե������ݿⱸ�ݳ��� �� Powerd By faisun</title>
	".csssetting()."
	</head><body link='#0000FF' vlink='#0000FF' alink='#0000FF' bgcolor='#FFFFFF'>
	<center><script language='Javascript'>document.doshowmywin=1;</script>
	<form name='myform' method='post' action=''><input type='hidden' name='faisunsql_postvars' value='".$str."'>";
}

function showmywin_script(){
	global $_POST;
	return "<script language='Javascript'>
	function showmywin(){
		if(!document.doshowmywin) return;
		if(top.myframeset&&this.window.name=='mainFrame1'){
			top.myframeset.rows='$_POST[frametopheight],*,0';
		}
		if(top.myframeset&&this.window.name=='mainFrame2'){
			top.myframeset.rows='$_POST[frametopheight],0,*';
		}
	}
	document.body.onload=showmywin;
	</script>";
}

//��ҳ��ͬ��ҳ��β
function ffooter(){
	echo "<div id='pageendTag'></div></form>
	<font color=red><B>���Ķ���<a href='?action=readme' target='_blank'><font color=red>˵���ĵ�</font></a>��</B></font><br>
	<br><B><a href='http://www.x-xox-x.com' target='_blank'>����ս��SQL�Ե������ݿⱸ�ݳ��� V".VERSION."</a></B><br>
	ʹ�������BUG�����뵽 <a href='http://www.x-xox-x.com' target='_blank'>����ս��</a> ����<br>
	��ԭʼ����&copy;�� ADM ��� <a href='http://www.x-xox-x.com' target='_blank'>����ս��</a> �ṩ<br>
	��ѳ��� ��ӭ����������<br>
	</center>".showmywin_script()."</body></html>";
}

//��ʼ˵�����
function tabletext($ttext="",$twidth=400){
	return "<table width='$twidth' border='0' cellspacing='1' cellpadding='3' align=center><tr><td>$ttext</td></tr></table><br>\r\n";
}

//��ʼһ�����
function tablestart($ttitle="",$twidth=400){
	return "<table width='1' border='0' cellspacing='0' cellpadding='0' align=center class='tabletitle'>
	<tr><td class='tabletitle'><strong>&nbsp;$ttitle</strong></td></tr> <tr><td>
	<table width='$twidth' border='0' cellspacing='1' cellpadding='2' align=center>";
}
//print_r($_POST);
//�������ݵ����
function tabledata($data,$widths=""){
	$pdata=explode("|",$data);
	$pwidths=explode("|",$widths);
	$str="<tr class='tabledata' onmouseover='this.className=\"tabledata_on\";' onmouseout='this.className=\"tabledata\";'>\r\n";
	for(@reset($pdata);@list($key,$val)=@each($pdata);){
		$str.="\t<td style='padding-left:4px' ".(intval($pwidths[$key])?"width='$pwidths[$key]'":"")." nowrap>$pdata[$key]</td>\r\n";
	}
	$str.="</tr>\r\n";
	return $str;
}

//����һ�����
function tableend(){
	return "</table></td></tr></table><BR>\r\n";
}

//��ť��ʽ
function fbutton($type="submit",$name="Submit",$value="ȷ��",$script="",$return=0){
	$str="<input type='$type' name='$name' value='$value' class='tabletitle' style='border:3px double #000000' $script> ";
	if($return) return $str;else echo $str;
}

//topFrame
if($_GET["action"]=="topframe"&&$_GET["framename"]=="topframe"){
	fheader();
	echo "<center><a href='http://www.x-xox-x.com' target='_blank'><img src='faisunsql_files/logo.png' border=0 width=300 height=71></a></center>";
	echo "</font></center></body></html>";
	exit;
}

//˵���ĵ�
if($_GET["action"]=="readme"){
	fheader();
	echo tablestart("˵���ĵ�");
	echo tabledata(implode('',file("faisunsql_files/readme.htm")));
	echo tableend();
	ffooter();
	exit;
}

/* 
������ڿ�ͷ���ò�����д����ȷ�����ã�
��������������Ϲ���Ա�����֤��
������������������������ڴ����У�
define("IS_ADMIN","yes");  //���ڼ����Ƿ���˹���Ա�����֤���롣
*/

if(!isset($_POST[dosubmit])){
	$_POST["db_host"]=$db_host;
	$_POST["db_username"]=$db_username;
	$_POST["db_password"]=$db_password;
	$_POST["db_dbname"]=$db_dbname;
}

// ���ñ�
if(!@mysql_connect($_POST[db_host],$_POST[db_username],$_POST[db_password])||!@mysql_select_db($_POST[db_dbname])){
	fheader();
	
	if(isset($_POST['finByte']) and isset($_POST['db_dbname'])){
		echo "�������ݿⳬʱ,��<a href='javascript:submitme();'>ˢ������</a>.<font id='timeescapepls'>10</font>����Զ�����.<BR>Ҳ���������ò���,������������.<BR>";
		echo "
		<script language='JavaScript'>
		document.doshowmywin=0;
		retryTime=10;
		function timeescape(){
			if(!retryTime) return;
			timeescapepls.innerHTML=retryTime--;
		}setInterval('timeescape()',1000);
		function submitme(){
			for(i=myform.elements.length-1;i>=0;i--){if(myform.elements[i].name==\"action\")break;}
			myform.elements[i].value=\"databackup\";
			myform.submit();
		}
		setTimeout('submitme()',retryTime*1000);
		</script>";
	}
	else if(isset($_POST[dosubmit])){
		echo "<script language='JavaScript'>alert('�������ݿ����,������������.');</script>";
	}
	echo tabletext("������ȷ���������������ݿ⡣<br> ����޷��������ݿ⣬����ϵ������������Ա�Ի����ȷ����ֵ��");
	echo tablestart("������������");
	echo tabledata("������������|<input name='db_host' value='$_POST[db_host]' type='text'>");
	echo tabledata("Ҫ���������ݿ⣺|<input name='db_dbname' value='$_POST[db_dbname]' type='text'>");
	echo tabledata("���ݿ��û�����|<input name='db_username' value='$_POST[db_username]' type='text'>");
	echo tabledata("���ݿ����룺|<input name='db_password' value='$_POST[db_password]' type='password'>");
	echo tableend();
	fbutton('submit','dosubmit','����');
	fbutton('reset','doreset','����');
	ffooter();
	//�°汾���
	echo "<script src='http://www.softpure.com/soft/faisunsql/version.php?v=".VERSION."'></script>";	
	exit;
}
 //��ȡ���ݿ�汾��תΪ4λ����
 $serverVersion = str_replace(".","",mysql_get_server_info()); 
 $serverVersion = substr(intval($serverVersion),0,4);
 while (strlen($serverVersion) < 4) $serverVersion =$serverVersion."0";
 $_POST[mysql_old_version] = $serverVersion;



//mysql_query("SET NAMES gb2312;");

if(!defined("IS_ADMIN") and !isset($_POST['db_dbname'])){die("���ڳ���ͷ������д����ȷ�����ã���û�м����κι���Ա�����֤���롣Ϊ�˰�ȫ���벻Ҫ������ȷ�����ݿ��û��������룡");}

//ѡ��Ҫ���ݵ����ݱ�
if (!isset($_POST['action'])){
	$currow=mysql_fetch_array(mysql_query("select version() as v"));
	$_POST['mysql_version']=$currow['v'];
	fheader();
	echo tabletext("�����г����Ǹ����ݿ������е����ݱ�<br>Ĭ�������ȫ����������Ҳ����ѡ��ֻ��������һ���ֱ�",500);
	echo tablestart("��ѡ��Ҫ���ݵ����ݱ� &nbsp; (��ǰ���ݿ�汾: $_POST[mysql_version])",500);
	echo tabledata("<center><B><a href='#' onclick='selrev();return false;'>[��ѡ]</a></B></center>|<strong>����</strong>|<strong>ע��</strong>|<strong>��¼��</strong>|<strong>��С</strong>","10%|30%|30%|17%|23%");
	$query=mysql_query("SHOW TABLE STATUS");
	while ($currow=mysql_fetch_array($query)){
		echo tabledata("<center><input name='fsqltable[{$currow[Name]}]' id='fsqltable_$currow[Name]' type='checkbox' value='".($currow[Data_length]+$currow[Index_length]).",".$currow[Avg_row_length]."' checked onclick='getsize()'></center>|<label for='fsqltable_$currow[Name]'>$currow[Name]</label>|$currow[Comment]|$currow[Rows]|".num_bitunit($currow[Data_length]+$currow[Index_length])."");
	}
	echo tabledata("<center><B><a href='#' onclick='selrev();return false;'>[��ѡ]</a></B></center>|<B>Ŀǰѡ�����ܴ�С��</B>|&nbsp;|&nbsp;|<B><label id='totalsizetxt'></label></B>");
	
	echo tableend();
	if($serverVersion >=4100 ){
	 
		echo tabledata("<B>���ݿ��ַ���:</B><label><input type='radio' name='charset' class='zl_radio' value='' checked/>Ĭ��</label> |<label><input type='radio' name='charset' value='gbk' class='zl_radio'/>GBK</label> |<label><input type='radio' name='charset' value='big5'  class='zl_radio'/>BIG5</label> |<label><input type='radio' name='charset' value='utf8' class='zl_radio'/>UTF8</label>|<label><input type='radio' name='charset' value='other'  class='zl_radio' id='zl_radio_other'/>����: </label><input type='text' name='charset_other'  class='zl_input' onclick='javascript:zl_radio();'/><p />");
	}
	echo "<script language='JavaScript'>
	<!--
	  function selrev() {
		with(myform) {
			for(i=0;i<elements.length;i++) {
				thiselm = elements[i];
				if(thiselm.name.match(/fsqltable\[\w+\]/))
					thiselm.checked = !thiselm.checked;
			}
		}
		getsize();
	  }
	
	  function num_bitunit(num){
		 var bitunit=new Array(' B',' KB',' MB',' GB');
		 for(key=0;key<bitunit.length;key++){
		   if(num>=Math.pow(2,10*key)-1){ //1023B ����ʾΪ 1KB
			  num_bitunit_str=(Math.ceil(num/Math.pow(2,10*key)*100)/100)+' '+bitunit[key];
		   }	 
		 }
		 return num_bitunit_str;
	  }
	
	  function getsize(){
		var ts=0;
		with(document.myform) {
			for(i=0;i<elements.length;i++) {
				thiselm = elements[i];
				if(thiselm.name.match(/fsqltable\[\w+\]/))
					ts += parseInt(thiselm.value);
			}
			totalsizetxt.innerHTML=num_bitunit(ts);
		}
	  }
	  function zl_radio(){		  
		  document.getElementById(\"zl_radio_other\").checked= true;
	  }
	  getsize();
	-->
	</script>
	
	<input name='action' type='hidden' id='action' value='selecttype'>";
	fbutton('submit','dosubmit','��һ��');
	fbutton('reset','doreset','����',"onmouseup=setTimeout('getsize()',100)");
	ffooter();
}

//ѡ�񵼳���ʽ
if($_POST['action']=="selecttype"){
	$_POST["totalsize"]=0;
	for(@reset($_POST[fsqltable]);count($_POST[fsqltable])&&@list($key,$val)=@each($_POST[fsqltable]);) {
		$_POST["totalsize"]+=$val;
	}
	fheader();
	$_POST["totalsize"]>1024*1024 ? $partsaveck="checked" : $downloadck="checked";
	echo tabletext("ѡ�񵼳����ļ����Ƕ��ļ���<br>
					���ݿ�̫��Ļ�������ѡ����ļ�������ʽ��<br>
					ϵͳ�������Ҫ�����������Ĵ�С�������Ƽ���Ĭ��ֵ��<br>
					������޷��ж�������������С����Ĭ��ѡ�����ɡ�",500);
	echo tablestart("��ѡ�񵼳���ʽ",500);
	echo tabledata("������ʽ��|<br>
					<input name='back_type' value='download' type='radio' $downloadck>���ɵ����ļ������� (���ݵ��������ϴ�ʱ������ʹ��)<br>
					<input name='back_type' value='partsave' type='radio' $partsaveck>��Ϊ����ļ��������ڷ����� <br><br>");
	echo tableend();
	echo "
	<script language='JavaScript'>
	function confirmit(){
		with(myform){
			if(back_type[0].checked && ".intval($_POST["totalsize"]).">1024*1024 && !confirm(\"��Ҫ�������������Ƚ϶ࣨ{$totalsize_chunk}��������ѡ����ļ�������ʽ��\\r\\n�����ȷ���������������ļ�����ȡ�������ظ��ġ�\"))
				return false;
		}
		return true;
	}
	myform.onsubmit=new Function('return confirmit();');
	</script>
	<input name='action' type='hidden' id='action' value='selectoption'>";
	fbutton('submit','dosubmit','��һ��');
	fbutton('reset','doreset','����');
	ffooter();
}


if($_POST['action']=="selectoption"){
	if($_POST['back_type']=="partsave"){//���ļ�����ѡ��
		fheader();
		echo tabletext("��ѡ���˶��ļ�������ʽ���������� $totalsize_chunk �ֽڡ�<br><br>
						�������ļ�������һ�����ļ��Ͷ�������ļ���������ͬһ��Ŀ¼�¡�<br>
						ÿ�������ļ����˹��󣬷���������ɵ������볬ʱ�������õ�ԽС�򵼳���ҳ��Խ�ࡣ<br>
						�������������ݿ⵼��ʱ��HTTP��ʽ���������ļ�ʱʹ�ã�������μǡ�",500);
		echo tablestart("����ѡ�",500);
		echo tabledata("���Ŀ¼��|<input name='dir' value='{$_POST[db_dbname]}data' type='text' size=20>|��Ա���������Ŀ¼��������д��Ȩ��");
		echo tabledata("���ļ�����|<input name='filename' value='index' type='text' size=16>.php|������չ����");
		echo tabledata("�����ļ���ʽ��|<input name='extension' value='php' type='radio' checked>.php <input name='extension' value='zip' type='radio'>.zip |.zip ��ѹ���ļ�,ռ�ռ��ٵ��׳���,<br>�Ұ�ȫ�Բ���,�ױ���������");
		echo tabledata("ÿ�������ļ���С��|<input name='filesize' value='1000' type='text' size=10>|��λ KB��1 MB = 1024 KB");
		echo tabledata("����һҳʱ������|<input name='nextpgtimeout' value='0' type='text' size=10>|�룬�����Ŀռ䲻֧��Ƶ���ύ�����һ��");   
		echo tabledata("���ݵ������룺|<input name='back_pass' value='' type='password' size=10>|�����HTTP����.php�ļ�ʱ������");   
		echo tabledata("ȷ�ϵ������룺|<input name='back_pass2' value='' type='password' size=10>|&nbsp;");
		echo tableend();
		echo "
		<script language='JavaScript'>
		function confirmit(){
			with(myform){
				if(back_pass.value==''||back_pass.value!=back_pass2.value){
					alert('�������벻��Ϊ���������������������ͬ��');
					return false;
				}
			}
			return true;
		}
		myform.onsubmit=new Function('return confirmit();');
		</script>
		<input name='action' type='hidden' id='action' value='databackup'>";
		fbutton('submit','dosubmit','��һ��');
		fbutton('reset','doreset','����');
		ffooter();
	}

	if($_POST['back_type']=="download"){//���ļ�����ѡ��
		fheader();
		echo tabletext("��ѡ���˵��ļ�������ʽ���������� $totalsize_chunk �ֽڡ�",500);
		echo tablestart("���ļ�������",500);
		echo tabledata("�����ļ�����|<input name='sqlfilename' value='$_POST[db_dbname](".date("Ymd").")_faisunsql.php' type='text' size='40'>");
		echo tabledata("�����ļ���ʽ��|<input name='extension' value='php' type='radio' checked>.php <input name='extension' value='zip' type='radio'>.zip <input name='extension' value='gz' type='radio'>.gz");
		echo tableend();
		echo "<input name='action' type='hidden' id='action' value='databackup'>";
		fbutton('submit','dosubmit','����');
		fbutton('reset','doreset','����');
		ffooter();
	}
}

if($_POST['action']=="databackup"){
	$charset = $_POST["charset"] == "other" ? $_POST["charset_other"] : $_POST["charset"];
	if(!empty($charset) ){
		mysql_query("SET NAMES ".$charset);
	}
	
	function escape_string($str){
		$str=mysql_escape_string($str);
		$str=str_replace('\\\'','\'\'',$str);
		$str=str_replace("\\\\","\\\\\\\\",$str);
		$str=str_replace('$','\$',$str);
		return $str;
	}

	function sqldumptable($table,$tableid,$part=0) {
		if($part) global $lastcreate_temp,$current_size,$_POST;;

		//structure
		if($tableid>=intval($_POST[nextcreate]) or $part==0){
			@mysql_query("SET SQL_QUOTE_SHOW_CREATE = 1");
			$query=mysql_query("SHOW CREATE TABLE `$table`");
			$row=mysql_fetch_row($query);
			$sql=str_replace("\n","\\n",str_replace("\"","\\\"",$row[1]));
			$sql=preg_replace("/^(CREATE\s+TABLE\s+`$table`)/mis","",$sql);
			$dumpstring="create(\"$table\",\"$sql\");\r\n\r\n";
			$_POST[nextcreate]++;
			dealdata($dumpstring);
			mysql_free_result($query);
		}		

		//data
		$query = mysql_query("SELECT count(*) as count FROM `$table` ");
		$count = mysql_fetch_array($query);
		$query = mysql_query("SELECT * FROM `$table` limit ".intval($_POST[lastinsert]).",$count[count] ");
		$numfields = mysql_num_fields($query);
		$dump_values = "";
		while ($row = mysql_fetch_row($query)) {
			$dump_values .= ($dump_values?",\r\n":"")."(";
			for ($i=0;$i<$numfields;$i++) {
				if(stristr(mysql_field_flags($query,$i),"BINARY")){ //�����ƴ���
					$row[$i] = '\''."\".base64_decode('".base64_encode(addslashes($row[$i]))."').\"".'\'';
				}else if (!isset($row[$i]) or is_null($row[$i])) {
					$row[$i] = "NULL";
				}else {
					$row[$i] = '\''.escape_string($row[$i]).'\'';
				}
			}
			$dump_values .= implode(",",$row).")";
			$value_stop = 0;
			$value_len = strlen($dump_values);

			$_POST[lastinsert]++;

			if($value_len>100000 || ($part && $current_size+$value_len>=intval($_POST["filesize"])*1024)){ //0.1M ����
				$dumpstring = "insert(\"$dump_values\");\r\n\r\n";
				dealdata($dumpstring);
				$dump_values = "";
			}
		}
		if($dump_values){
			$dumpstring = "insert(\"$dump_values\");\r\n\r\n";
			dealdata($dumpstring);
		}
		mysql_free_result($query);
		
		//end of table
		$dumpstring = "tableend(\"$table\");\r\n\r\n";
		dealdata($dumpstring);
		$_POST[tabledumping]++;
		$_POST[lastinsert]=0;
	}

	function timeformat($time){
		return substr("0".floor($time/3600),-2).":".substr("0".floor(($time%3600)/60),-2).":".substr("0".floor($time%60),-2);
	}
	
	function mysql_functions(){
		global $_POST,$charset;
		return '
		$mysql_old_version = "'.$_POST[mysql_old_version].'";
		$old_charset = "'.$charset.'";
		$old_charset_other = "'.$_POST["charset_other"].'";
		@mysql_connect($_POST[db_host],$_POST[db_username],$_POST[db_password]) or die("<div id=pageendTag></div><BR><BR><center>�������ӷ����������ӳ�ʱ���뷵�ؼ���������á�</center> $showmywin0");
		//��ȡ���ݿ�汾��תΪ4λ����
		$serverVersion = str_replace(".","",mysql_get_server_info()); 
		$serverVersion = substr(intval($serverVersion),0,4);
		while (strlen($serverVersion) < 4) $serverVersion =$serverVersion."0";
		$charset = $_POST["charset"] == "other" ? $_POST["charset_other"] : $_POST["charset"];
		if($serverVersion >= 4100  && !empty($charset) ){
			mysql_query("SET NAMES ".$charset);		
		}
		if(!@mysql_select_db($_POST[db_dbname])){
			global $_POST;
			if(!$_POST[db_autocreate]){echo "<div id=pageendTag></div><BR><BR><center>���ݿ�[{$_POST[db_dbname]}]�����ڣ��뷵�ؼ���������á�</center> $showmywin0";exit;	}
			if(!mysql_query("CREATE DATABASE `$_POST[db_dbname]`")){echo "<div id=pageendTag></div><BR><BR><center>���ݿ�[{$_POST[db_dbname]}]���������Զ�����ʧ�ܣ��뷵�ؼ���������á�</center> $showmywin0";exit;}
			mysql_select_db("$_POST[db_dbname]");
		}
		function query($sql){
			global $_POST;
			if(!mysql_query($sql)){
				echo "<BR><BR><font color=red>MySQL�����������ܷ����˳����BUG��<a href=\"mailto:faisun@sina.com\">�뱨�濪���ߡ�</a>
				  	<BR>�汾��V'.VERSION.'<BR>��䣺<XMP>$sql</XMP>������Ϣ�� ".mysql_error()." </font>" ;
				if(trim($_POST[db_temptable])) query("DROP TABLE IF EXISTS `$_POST[db_temptable]`;");
				exit;
			}
		}
		function create($table,$sql){
			global $_POST,$mysql_old_version,$serverVersion,$charset;
			if(!trim($_POST[db_temptable])){
				do{
					$_POST[db_temptable]="_faisunsql".rand(100,10000);
				}while(@mysql_query("select * from `$_POST[db_temptable]`"));
			}
			if($mysql_old_version < 4100 && $serverVersion >= 4100 && !empty($charset)){
				$zl_query = "CREATE TABLE `$_POST[db_temptable]` $sql DEFAULT CHARSET=$charset";
				
			}
			elseif($mysql_old_version >= 4100 && $serverVersion < 4100){
				$pattern = "/DEFAULT[\s]+CHARSET[\s]*=[\s]*(\w+)/i";
				$replacement = "";
				$sql = preg_replace($pattern, $replacement, $sql);
				//$pattern2 = "/character[\s]+set[\s]+([\w\d]+)/i";
				//$sql = preg_replace($pattern2, $replacement, $sql);
				//echo $sql."<br>";
				$zl_query = "CREATE TABLE `$_POST[db_temptable]` $sql ";
								
			}
			elseif($mysql_old_version >= 4100 && $serverVersion >= 4100 && $old_charset!=$charset){
				$pattern = "/(DEFAULT[\s]+CHARSET[\s]*=[\s]*)([\w\d_-]+)/i";
				$replacement = "\${1}".$charset;
				$sql = preg_replace($pattern, $replacement, $sql);
				$pattern2 = "/(character[\s]+set[\s]+)([\w\d_-]+)/i";
				$sql = preg_replace($pattern2, $replacement, $sql);
				//echo $sql."<br>";
				$zl_query = "CREATE TABLE `$_POST[db_temptable]` $sql ";
								
			}
			else{
				$zl_query = "CREATE TABLE `$_POST[db_temptable]` $sql";
			}
			
			query($zl_query);
			if(!$_POST[db_safttemptable]) query("DROP TABLE IF EXISTS `$table`;");
		}
		function insert($data){
			global $_POST,$old_charset,$charset;
			if(function_exists("iconv")){
				if(($old_charset == "gbk" || $old_charset == "gb2312") && $charset == "utf8"){
					$data = iconv("GB2312", "UTF-8", $data);
				}
				elseif($old_charset == "utf8" && ($charset == "gbk" || $charset == "gb2312")){
					$data = iconv("UTF-8", "GB2312", $data);
				}
				elseif($old_charset == "big5" && $charset == "utf8"){
					$data = iconv("BIG5", "UTF-8", $data);
				}
				elseif($old_charset == "utf8" && $charset == "big5"){
					$data = iconv("UTF-8", "BIG5", $data);
				}
				elseif(($old_charset == "gbk" || $old_charset == "gb2312") && $charset == "big5"){
					$data = iconv("GB2312", "BIG5", $data);
				}
				elseif($old_charset == "big5" && ($charset == "gbk" || $charset == "gb2312")){
					$data = iconv("BIG5", "GB2312", $data);
				}
			}
			query("INSERT IGNORE INTO `$_POST[db_temptable]` VALUES $data;");
		}
		function tableend($table){
			global $_POST;
			if($_POST[db_safttemptable]) query("DROP TABLE IF EXISTS `$table`;");
			query("ALTER TABLE `$_POST[db_temptable]` RENAME `$table`");
		}';
	}
	
	function auto_submit_script(){
		return "echo \"<script language='Javascript'>
				try{finisheditem.focus();}catch(e){}
				function checkerror(frame){
					if(top.mainFrame1.location.href!=top.mainFrame2.location.href||(frame.document && !frame.document.all.postingTag && frame.document.all.pageendTag)){
						postingTag.innerHTML='faisunSQL:�ύ���ִ���.�����Զ�<a href=\'javascript:myform.submit();\'>�����ύ</a>...';
						myform.submit();
					}
				}
				nextpgtimeout = parseFloat('\$_POST[nextpgtimeout]')?parseFloat('\$_POST[nextpgtimeout]'):0;
				if(top.myframeset&&this.window.name=='mainFrame1'){
					myform.target='mainFrame2';
					setInterval('checkerror(top.mainFrame2)',10000+1000*nextpgtimeout);
				}
				if(top.myframeset&&this.window.name=='mainFrame2'){
					myform.target='mainFrame1';
					setInterval('checkerror(top.mainFrame1)',10000+1000*nextpgtimeout);
				}
				setTimeout('myform.submit();',1000*nextpgtimeout);
				</script>\";
		";
	}
	$zl_charset_choose = "Ŀ�����ݿ��ַ���:<br />(MySQL 3.X/4.0.X ����Ĭ��)|<label><input type='radio' name='charset' class='zl_radio' value='' ";
	if(empty($_POST["charset"])) $zl_charset_choose .=" checked ";
	$zl_charset_choose .= "/>Ĭ��</label> <label><input type='radio' name='charset' value='gbk' class='zl_radio' ";
	if($_POST["charset"] == 'gbk') $zl_charset_choose .=" checked ";
	$zl_charset_choose .= "/>GBK</label> <label><input type='radio' name='charset' value='big5'  class='zl_radio' ";
	if($_POST["charset"] == 'big5') $zl_charset_choose .=" checked ";
	$zl_charset_choose .= "/>BIG5</label>
	<label><input type='radio' name='charset' value='utf8' class='zl_radio' ";
	if($_POST["charset"] == 'utf8') $zl_charset_choose .=" checked ";
	$zl_charset_choose .= "/>UTF8</label><br />
	<label><input type='radio' name='charset' value='other'  class='zl_radio' id='zl_radio_other' ";
	if($_POST["charset"] == 'other') $zl_charset_choose .=" checked ";
	$zl_charset_choose .= "/>����: </label><input type='text' size='20' name='charset_other'  class='zl_input' onclick='javascript:zl_radio();' ";
	if(!empty($_POST["charset_other"])) $zl_charset_choose .=" value='".$_POST["charset_other"]."' ";
	$zl_charset_choose .= "/><p />";
	if($_POST[back_type]=="partsave"): ////////////////////////   Save Data ////////////////////////////

		if($_POST[extension]=="zip"){
			include("faisunsql_files/zipclass.php");
			if(@function_exists('gzcompress')){
				$fsqlzip=new PHPzip;
				$fsqlzip->gzfilename="$_POST[dir]/$_POST[filename].$_POST[extension]";
			}
			else{ fheader();echo "<BR><BR><center>ѹ���ļ���ʽ��Ҫϵͳ֧�֡�</center><BR><BR>";ffooter();exit; }
		}

		if(!$_POST[tabledumping]) $_POST[tabledumping]=0; //���ڵ����ı�
		if(!$_POST[nextcreate]) $_POST[nextcreate]=0; //�������ı�
		if(!$_POST[lastinsert]) $_POST[lastinsert]=0;
		if(!$_POST[page]) $_POST[page]=0;

		if(!is_dir("$_POST[dir]") and !@mkdir("$_POST[dir]",0777)){
			fheader();echo "<BR><BR><center>Ŀ¼'$_POST[dir]'�������Ҳ����Զ�����������Ŀ¼Ȩ�ޣ�Ȩ��Ϊ 777 ����д�ļ�����</center><BR><BR>";ffooter();exit;
		}
		@chmod("$_POST[dir]",0777);

		//�Ƿ��ж�����ļ�
		$dfileNo=0;
		$open=opendir($_POST["dir"]);
		$delhtml="";
		while($afilename=readdir($open) and !$_POST[filedeled]){
			$checked="";
			if(substr($afilename,0,strlen($_POST[filename]))==$_POST[filename]){
				$checked="checked";
			}
			if(is_file("$_POST[dir]/$afilename")){
				$delhtml.=tabledata("$afilename|".date("Y-m-d",filectime("$_POST[dir]/$afilename"))."|".num_bitunit(filesize("$_POST[dir]/$afilename"))."|<center><input name='dfile[$dfileNo]' type='checkbox' value='$_POST[dir]/$afilename' $checked></center>");
				$dfileNo++;
			}
		}

		//�����ļ�����
		if($dfileNo){
			$_POST[filedeled]=1;
			fheader();
			echo tabletext("'$_POST[dir]/'�������ļ��Ѵ��ڣ����ǿ��ܱ����ǻ��Ϊ������ļ���<br>��������ѡ���ɾ�����ǻ򷵻���һ�������趨��",500);
			echo tablestart("ѡ��Ҫɾ�����ļ���",500);
			echo tabledata("<strong>�ļ���</strong>|<strong>�޸�����</strong>|<strong>��С</strong>|<center><strong>��ѡ</strong><input type='checkbox' name='checkbox' value='' onclick='selrev();'></center>","31%|32%|21%|16%");
			echo $delhtml;
			echo tableend();
			echo "
			<script language='JavaScript'>
			function selrev() {
				with(myform) {
					for(i=0;i<elements.length;i++) {
						thiselm = elements[i];
						if(thiselm.name.match(/dfile\[\w+\]/))	thiselm.checked = !thiselm.checked;
					}
				}
			}
			</script>";
			fbutton('submit','dosubmit','ɾ��������');
			fbutton('reset','doreset','����');
			fbutton('button','dogoback','�����޸�','onclick=\'history.back();\'');
			ffooter();
			exit;
		}

		//ɾ�������ļ�
		if($_POST[filedeled]==1){
			for(@reset($_POST["dfile"]);@list($key,$val)=@each($_POST["dfile"]);){
				if($val) unlink($val);
			}
			unset($_POST["dfile"]);
		}
		$_POST[filedeled]=2;

		//��ʼ����ǰ��Ԥ����
		if($_POST[page]==0){
			//д��ͼƬ
			if(isset($fsqlzip)){
				/* ��д����ʱ�ļ� .tmp��ȫ�������ٸ���Ϊ��ʽ�ļ���
				   ʵ���ϣ� PHPzip ��ÿ����һ���ļ���ѹ���ļ����������ġ�
				   ������������Ϊ�˷�ֹ�������������ļ�����ͬ���ļ���
				*/
				if(!$fsqlzip->startfile("$fsqlzip->gzfilename.tmp")){
					fheader();echo "��ͼ��Ŀ¼'$_POST[dir]'д��ѹ���ļ�ʱ������������Ŀ¼Ȩ�ޣ�";ffooter();exit;
				}
				$fsqlzip->addfile(implode('',file("faisunsql_files/logo.png")),"{$_POST[filename]}_logo.png","$fsqlzip->gzfilename.tmp");
			}else{
				if(!@copy("faisunsql_files/logo.png","$_POST[dir]/{$_POST[filename]}_logo.png")){
					fheader();echo "��ͼ��Ŀ¼'$_POST[dir]'д��LOGOͼƬʱ������������Ŀ¼Ȩ�ޣ�";ffooter();exit;
				}
			}

			$_POST[page]=1;
			fheader();
			echo tablestart("Ŀ¼Ȩ����ȷ");
			echo tabledata("<br>�����ԣ���Ŀ¼����д���ļ���LOGOͼƬ�ѳɹ�д�롣<br>���濪ʼ�������ݲ������ڷ������С�<br><br>");
			echo tableend();
			fbutton('submit','dosubmit','��ʼ�Զ�����');
			ffooter();
			exit;
		}

		if(isset($fsqlzip)){
			clearstatcache();
			if(!file_exists("$fsqlzip->gzfilename.tmp")){
				fheader();echo "����Ϊѹ���ļ�����ɣ�������Ҫ�������µ�����";ffooter();exit;
			}
		}
		
		if(!$_POST["StartTime"]) $_POST["StartTime"]=time();

		$writefile_data = '';
		
		function writefile($data,$method='w'){
			global $fsqlzip,$_POST;;
			$file = "{$_POST[filename]}_pg{$_POST[page]}.php";
			if(isset($fsqlzip)){
				$fsqlzip->addfile($data,"$file","$fsqlzip->gzfilename.tmp");
			}else{
				$fp=fopen("$_POST[dir]/$file","$method");
				flock($fp,2);
				fwrite($fp,$data);
			}
		}
		

		$current_size = 0;
		function dealdata($data){
			global $current_size,$tablearr,$writefile_data,$_POST;;
			$current_size += strlen($data);
			$writefile_data .= $data;
			if($current_size>=intval($_POST["filesize"])*1024){
				$current_size=0;
				$writefile_data .= "\r\n?".">";

				writefile($writefile_data,"w");

				$_POST[page]=intval($_POST[page])+1;

				fheader();
				echo tablestart("���ڴ����ݿ�'$_POST[db_dbname]'�е������ݡ���",500);

				$str1="<br>-= �������ݱ������ =- <div class='borderdiv' style='width:150px;height:100px;overflow:auto;' align=left>";
				
				$finishByte=0;
				for(reset($tablearr);list($key,$val)=each($tablearr);){
					if($key<$_POST[tabledumping]){
						$str1.="�� $val<BR>\r\n";
						$finishByte+=$_POST[fsqltable][$val];
					}else if($key==$_POST[tabledumping]){
						$str1.="<a href='#' id='finisheditem'> </a></div>
						<br>-= �������ݱ��������� =-
						<div class='borderdiv' style='width:150px;height:100px;overflow:auto;' align=left>
						<font style='color:#FF0000'>�� $val</font><br>\r\n";
						$finishByte+=$_POST[lastinsert]*substr(strstr($_POST[fsqltable][$val],','),1);
						$finish=intval($finishByte/$_POST[totalsize]*100);						
					}else{
						$str1.="�� $val<br>\r\n";
					}
				}
				$str1.="</div><BR>";

				$str2=tablestart("����״̬",300);
				$str2.=tabledata("�������ݣ�|".num_bitunit($_POST[totalsize])."","100|200");
				$str2.=tabledata("���ѵ�����|".num_bitunit($finishByte)."");
				$str2.=tabledata("ÿҳ������|".num_bitunit(intval($finishByte/$_POST[page]))."");
				$str2.=tabledata("����ʱ������|$_POST[nextpgtimeout] ��");
				$str2.=tabledata("ÿҳ���������ļ�|�� ".num_bitunit($_POST["filesize"]*1024)."");
				$str2.=tabledata("�����������ļ���|".($_POST[page]-1)." ��");
				$str2.=tabledata("�����Զ����룺|<a href='javascript:myform.submit();'>�� $_POST[page] ҳ</a>");
				$str2.=tabledata("����ʱ��|".timeformat(time()-$_POST["StartTime"])."");
				$str2.=tabledata("����ɣ�|{$finish}% ");
				$str2.=tabledata("��ɽ��ȣ�|<table width=100% height=12  border=0 cellspacing=1 cellpadding=0 class='tabletitle' align=center><tr><td width='$finish%'><div></div></td><td width='".(100-$finish)."%'  class='tabledata'><div></div></td></tr></table>");
				$str2.=tableend();
				$str2.="<B><div id='postingTag'></div></B>";
				echo tabledata("$str1|$str2");
				echo tableend();
				ffooter();
				eval(auto_submit_script());
				exit();
			}
		}


		// ��ʼ����һҳ
		$writefile_data = "<?\r\nif(!defined('VERSION')){echo \"<meta http-equiv=refresh content='0;URL={$_POST[filename]}.php'>\";exit;}\r\n";

		$tablearr=array();
		for(@reset($_POST[fsqltable]);count($_POST[fsqltable])&&@list($key,$val)=@each($_POST[fsqltable]);) {
			$tablearr[]=$key;
		}
		
		for($i=$_POST[tabledumping];$i<count($tablearr);$i++){
			sqldumptable($tablearr[$i],$i,1);  //������
		}
		
		//��������ļ�
		$data="echo '<center><BR><BR><BR><BR>��ɡ��������ݶ��Ѿ��������ݿ��С�</center>'; exit; ?".">";

		$writefile_data .= "$data";
		writefile($writefile_data,"w");
		
		//�����ļ�����
		$data='<?

		$usedumppass=1;  //��������ʱ�Ƿ�ʹ�õ������롣����������˵������룬���ֵ��Ϊ 0 ��HTTP��ʽ���������ļ�����ȡ���������롣

		define("VERSION","'.VERSION.'");
		error_reporting(1);
		@set_time_limit(0);
		$md5pass="'.md5($_POST[back_pass]).'";

		'.requestValues().'

		if($_GET["action"]=="downphp"){
			if(!file_exists("$_GET[phpfile]")||$_GET["db_pass"]!=$md5pass) exit;
			header("Content-disposition: filename=$_GET[phpfile]");
			header("Content-type: unknown/unknown");
			readfile("$_GET[phpfile]");
			exit;
		}
		'.frameset_html().postvars_function().'
		
		if($_GET["framename"]=="topframe"&&$_GET["action"]=="topframe"){
			echo "<html><body><center><a href=\'http://www.x-xox-x.com\' target=\'_blank\'><img src=\''.$_POST[filename].'_logo.png\' border=0 width=300 height=71></a></center></body></html>";
			exit;
		}
		?'.'><html><head>
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		<title>faisunSQL ���ݿ��Ե������ �� Powerd By faisun</title>'.csssetting().'</head>
		<body link="#0000FF" vlink="#0000FF" alink="#0000FF">
		<center>
		<font color=red>���ļ��� faisun ��д�� <a href="http://www.x-xox-x.com" target="_blank">faisunSQL�Ե������ݿⱸ�ݳ��� V'.VERSION.'</a> ����</font><HR size=1>
		<script language="Javascript">document.doshowmywin=1;</script>		
		'.showmywin_script().'
		<?
		$showmywin0=$_POST[loadpage]?"<script language=Javascript>document.doshowmywin=0;</script></body>":"";
		
		if($_GET["action"]=="downall"){
			echo "<form name=\"actionform\" method=\"post\" action=\"\">";
			if($_POST[db_pass]!=$md5pass and ($_POST[db_pass]=md5($_POST[db_pass]))!=$md5pass){
			?'.'>
		����Ϊ�����ݵİ�ȫ,HTTP��ʽ���������ļ���������ȷ�ĵ������룬�������������ݿ⵼��ʱ�Ѵ�����<BR>
		�����������룺<input name="db_pass" value="" type="password"> '.fbutton('submit','action','ȷ��','',1).'
			</form>
			<?
			exit;
		}
		if(!empty($_POST["deleteallfiles"])){
			for(reset($_POST["files"]);@list($key,$value)=@each($_POST["files"]);){
				if(@unlink($value)){
					echo "��ɾ���� $value <br>";
				}else{
					echo "<b>ɾ��ʧ�ܣ� $value </b><br>";
				}
			}
			echo "<br>��ɡ�";
			exit;
		}
		?'.'>
		�����������й��ļ�,�������װ��FlashGet�����,�����Ե���Ҽ���ѡ��Download All by FlashGet�����ء�<br>
		�������������
		<input name="db_pass" value="<?=$_POST[db_pass];?'.'>" type="hidden">'.fbutton('submit','deleteallfiles','ɾ�������ļ�','onclick="return confirm(\'ɾ���������б����ļ���ȷ����\');"',1).'

		<BR><BR>
		<?
		echo "<a href=\"'.$_POST[filename].'.php?action=downphp&phpfile='.$_POST[filename].'.php&db_pass=$_POST[db_pass]\">'.$_POST[filename].'.php</a><BR>
		<a href=\"'.$_POST[filename].'_logo.png\">'.$_POST[filename].'_logo.png</a><BR>
		<input type=\"hidden\" name=\"files[-1]\" value=\"'.$_POST[filename].'.php\">
		<input type=\"hidden\" name=\"files[0]\" value=\"'.$_POST[filename].'_logo.png\">
		";
		$i=1;
		while(file_exists($afile="'.$_POST[filename].'_pg{$i}.php")){
			 echo "<a href=\"'.$_POST[filename].'.php?action=downphp&phpfile=$afile&db_pass=$_POST[db_pass]\">$afile</a><BR>
			 <input type=\"hidden\" name=\"files[$i]\" value=\"$afile\">
			 ";
			 $i++;
		}
		echo "</form></body></html>";
		exit;
		}

		if(!$_POST["action"] and !$_GET["action"]){
		$iconv = function_exists("iconv") ? "֧��":"��֧��";
		?'.'><center><form name="configform" method="post" action="">'.
		tablestart('������Ϣһ��').
		tabledata("������������|".num_bitunit($_POST[totalsize])."","50%|50%").
		tabledata("�������ݱ�|".count($_POST[table])).
		tabledata("ÿҳ���������ļ�|�� ".num_bitunit($_POST["filesize"]*1024)).
		tabledata("�����ļ�����|".$_POST[page]).
		tabledata("�ļ�������|".($_POST[page]+2)).
		tabledata("����ʱ�䣺|".date("Y-m-d H:i")).
		tabledata("ԭ���ݿ�汾��|".$_POST[mysql_version]).
		tabledata("ԭʼ�����ַ�����|".strtoupper($_POST[charset])).
		tabledata("����ת�����ܣ�|<?echo \$iconv;?>").
		tableend().
		tablestart('�������ݿ�����').
		tabledata('��������|<input name="db_host" value="'.$_POST[db_host].'" type="text">',"50%|50%").
		tabledata('���ݿ⣺|<input name="db_dbname" value="'.$_POST[db_dbname].'" type="text">').
		tabledata('�����ݿⲻ����ʱ�Զ�����|<input name="db_autocreate" value="1" type="checkbox" checked>').
		tabledata('�û�����|<input name="db_username" value="root" type="text">').
		tabledata('�ܡ��룺|<input name="db_password" value="" type="password">').
		tabledata('����һҳʱ������|<input name="nextpgtimeout" value="'.$_POST[nextpgtimeout].'" type="text"> ��').
		tabledata('�������룺|<input name="db_pass" value="" type="password">').
		tabledata('��ȫ����ʱ��(<a href="javascript:alert(\'ʹ����ʱ�����������������ݺ���ɾ��ԭ��,Ҫ��ʱռ�����ݿ�ռ�.\');" title="����">?</a>)��|<input name="db_safttemptable" type="checkbox" id="db_safttemptable" value="yes" checked>').
		tabledata($zl_charset_choose).
		tableend().
		fbutton('submit','action','����','',1).
		'</form><a href="'.$_POST[filename].'.php?action=downall" target="_blank">�������HTTP��ʽ���������ļ�</a>.
		</center>
		<?
		exit;
		}
		if($usedumppass and md5($_POST[db_pass])!=$md5pass) die("<div id=pageendTag></div>�������벻��ȷ������������˵������룬��ѱ�Դ�ļ���ͷ�� \$usedumppass ��ֵ��Ϊ 0 �� $showmywin0");
		'.mysql_functions().'
		
		$totalpage='.$_POST[page].';
		if(!$_POST[loadpage]){$_POST[loadpage]=1;}
		include("'.$_POST[filename].'_pg$_POST[loadpage].php");
		echo "<center><form name=myform method=\'post\' action=\'\'>";
		$_POST[loadpage]++;

		echo "<input type=\'hidden\' name=\'faisunsql_postvars\' value=\'".fsql_StrCode(serialize($_POST),"ENCODE")."\'>
		<BR><BR>���ڵ������ݵ����ݿ�\'$_POST[db_dbname]\'����<BR><BR>��ҳ������ɣ� �����Զ�����<a href=\'javascript:myform.submit();\'>�� $_POST[loadpage] ҳ</a>���� $totalpage ҳ����
		<BR><BR>(���ǽ��̳��ò����������벻Ҫ�������ҳ�����ӡ�)";
		?'.'>
		<BR><BR><B><div id="postingTag"></div></B>
		<? '.auto_submit_script().' ?'.'>
		<div id="pageendTag"></div>
		</form></center>
		</body></html>
		';

		//д�������ļ�
		if(isset($fsqlzip)){
			$fsqlzip->addfile($data,"$_POST[filename].php","$fsqlzip->gzfilename.tmp");
			rename("$fsqlzip->gzfilename.tmp","$fsqlzip->gzfilename");
		}else{
			$file="$_POST[dir]/$_POST[filename].php";
			$fp=fopen($file,"w");
			flock($fp,2);
			fwrite($fp,$data);
			fclose($fp);
		}

		//��ʾ�������
		fheader();
		if(isset($fsqlzip)){
			echo tabletext("<BR><BR>ȫ����ɣ���ʱ ".timeformat(time()-$_POST["StartTime"])." ��
			<BR><BR>���ݿ�'$_POST[db_dbname]'��ȫ�����浽�ļ���'$_POST[dir]'�У��� ".intval($_POST[page])." ҳ��".(intval($_POST[page])+2)." ���ļ���
			<BR><BR>��Щ�ļ���ѹ��Ϊ'$fsqlzip->gzfilename',���ļ���ʽ�ױ���������,��þ���ɾ��.
			<BR><BR>����ѹ���ļ���ѹ��,���ڷ������ɷ���Ŀ¼��������'$_POST[filename].php'���ɽ����ݵ��롣
			<BR><BR>��FTP��ʽ��<a href='$fsqlzip->gzfilename' target='_blank'><H3>��HTTP��ʽ���������ļ�</H3></a>
			<BR><BR>",500);
		}else{
			echo tabletext("<BR><BR>ȫ����ɣ���ʱ ".timeformat(time()-$_POST["StartTime"])." ��
			<BR><BR>���ݿ�'$_POST[db_dbname]'��ȫ�����浽�ļ���'$_POST[dir]'�У��� ".intval($_POST[page])." ҳ��".(intval($_POST[page])+2)." ���ļ���
			<BR><BR>�����ļ������ڷ������ɷ���Ŀ¼��������'$_POST[filename].php'���ɽ����ݵ��롣
			<BR><BR>��FTP��ʽ��<a href='$_POST[dir]/{$_POST[filename]}.php?action=downall' target='_blank'><H3>��HTTP��ʽ���������ļ�</H3></a>
			����<a href='$_POST[dir]/{$_POST[filename]}.php' target='_blank'><H3>���б����ļ� {$_POST[filename]}.php </H3></a>ʱҲ����ִ����ӡ�
			<BR><BR>",500);
		
		}
		echo "<div id='postingTag'></div>";
		ffooter();
		exit;

	elseif($_POST[back_type]=="download"): ////////////////////////   Sent Data ////////////////////////////

		$extension="";
		if($_POST[extension]=="zip" or $_POST[extension]=="gz"){
			if(@function_exists('gzencode')){ $extension=".$_POST[extension]"; }
			else{ fheader();echo "<BR><BR><center>ѹ���ļ���ʽ��Ҫϵͳ֧�֡�</center><BR><BR>";ffooter();exit; }
		}

		
		$echo_string = '<?	error_reporting(1);	@set_time_limit(0); '.requestValues().' ?'.'>
		<html><head> <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		<title>faisunSQL ���ݿ��Ե������ �� Powerd By faisun</title>'.csssetting().'
		<script language="JavaScript">
		<!--		  
		  function zl_radio(){		  
			  document.getElementById("zl_radio_other").checked= true;
		  }
		-->
		</script>
		</head>
		<body link="#0000FF" vlink="#0000FF" alink="#0000FF">
		<center>
		<font color=red>���ļ��� ADM ��д�� <a href="http://www.x-xox-x.com" target="_blank">����ս��SQL�Ե������ݿⱸ�ݳ��� V'.VERSION.'</a> ����</font><HR size=1>
		</center>
		<?
		
		if(!$_POST["action"]){
			$iconv = function_exists("iconv") ? "֧��":"��֧��";
			?'.'>
			<form name="configform" method="post" action="">'.
			tablestart('������Ϣһ��').
			tabledata("������������|".num_bitunit($_POST[totalsize])).
			tabledata("�������ݱ�|".count($_POST[table])).
			tabledata("����ʱ�䣺|".date("Y-m-d H:i")).
			tabledata("ԭ���ݿ�汾��|".$_POST[mysql_version]).
			tabledata("ԭʼ�����ַ�����|".strtoupper($_POST[charset])).
			tabledata("����ת�����ܣ�|<?echo \$iconv;?>").
			tableend().
			tablestart("�������ݿ�����").
			tabledata('��������|<input name="db_host" value="'.$_POST[db_host].'" type="text">').
			tabledata('���ݿ⣺|<input name="db_dbname" value="'.$_POST[db_dbname].'" type="text">').
			tabledata('�����ݿⲻ����ʱ�Զ�������|<input name="db_autocreate" value="1" type="checkbox" checked>').
			tabledata('�û�����|<input name="db_username" value="" type="text">').
			tabledata('�ܡ��룺|<input name="db_password" value="" type="password">').
			tabledata('��ȫ����ʱ��(<a href="javascript:alert(\'ʹ����ʱ�����������������ݺ���ɾ��ԭ��,Ҫ��ʱռ�����ݿ�ռ�.\');" title="����">?</a>)��|<input name="db_safttemptable" type="checkbox" id="db_safttemptable" value="yes" checked>').
			tabledata($zl_charset_choose).
			tableend().
			'<center><input name="action" type="submit" value=" ���� "></center>
			</form></body></html>
			<?
			exit;
		}
		'.mysql_functions()."\r\n\r\n";
		////// ��ͷ���ֽ��� ////////

		function dealdata($data){
			global $echo_string;
			$echo_string .= "$data";
		}

		for(@reset($_POST[fsqltable]);count($_POST[fsqltable])&&@list($key,$val)=@each($_POST[fsqltable]);) {
			sqldumptable($key,0,0);
		}

		$echo_string .= "echo \"<BR><BR>��ɡ����������ѳɹ����뵽 [{\$_POST[db_dbname]}]��\"; ?"."></body></html>";

		if($extension){ $echo_string = gzencode($echo_string); }

		header("Content-disposition: filename=$_POST[sqlfilename]{$extension}");
		header("Content-type: unknown/unknown");
		echo $echo_string;

		exit;
	endif;
}
?>