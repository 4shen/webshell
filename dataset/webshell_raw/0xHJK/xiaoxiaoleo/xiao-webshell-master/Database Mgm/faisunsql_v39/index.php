<?PHP
//���ò��֣� 
//ע�⣬�����û���ں����ע�͵ĵط����Ϲ���Ա�����֤����
//�벻Ҫ������ȷ�����ݿ��û��������룡
//����Ĭ���������У���������������д�ġ�

$db_host="localhost";    //���ݿ������
$db_username="root";     //���ݿ��û���
$db_password="";         //���ݿ�����
$db_dbname="";          //ѡ������ݿ�

$_POST["frametopheight"]=90;  //FrameTop �ĸ�

//�汾
define("VERSION",3.9);

error_reporting(1);
@set_time_limit(30);

if(!isset($_POST[dosubmit])){
	$_POST["db_host"]=$db_host;
	$_POST["db_username"]=$db_username;
	$_POST["db_password"]=$db_password;
	$_POST["db_dbname"]=$db_dbname;
}

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
if(!$_GET["framename"]){
	echo "<html>
	<head> <meta http-equiv='Content-Type' content='text/html; charset=gb2312'>
	<title>faisunSQL�Ե������ݿⱸ�ݳ��� �� Powerd By faisun</title>
	</head>
	<frameset rows='$_POST[frametopheight],*,0' frameborder='NO' border='0' framespacing='0' name='myframeset'>
		<frame src='$_SERVER[PHP_SELF]?action=topframe&framename=topframe' name='topFrame' scrolling='NO' noresize>
		<frame src='$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]&framename=main' name='mainFrame1'>
		<frame src='about:blank' name='mainFrame2'>  
	</frameset>
	<BODY></BODY>
	</html>";
	exit;
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
		background-color:#CC0000;
	}
	.tabledata, input{
		background-color:#FFD800;
	}
	input, .borderdiv {
		border:1px #CC0000 solid;
	}
	-->
	</style>";
}

//�������$_POST����
function echoAllPostValues($var,$subkey=""){
	for(reset($var);list($key,$val)=each($var);){
		$varname=($subkey?"{$subkey}[$key]":"$key");
		if(is_array($val)) echoAllPostValues($val,"$varname");
		else echo "\t<input name=\"$varname\" value=\"".htmlspecialchars(stripslashes("$val"))."\" type=\"hidden\">\n";
	}
}

//��ҳ��ͬ��ҳ��ͷ
function fheader(){
	global $_SERVER,$_POST;
	echo "<html>
	<head> 
	<meta http-equiv='Content-Type' content='text/html; charset=gb2312'>
	<title>faisunSQL�Ե������ݿⱸ�ݳ��� �� Powerd By faisun</title>
	".csssetting()."
	</head><body link='#0000FF' vlink='#0000FF' alink='#0000FF' bgcolor='#FFFFFF'>
	<center><script language='Javascript'>document.doshowmywin=1;</script>
	<form name='myform' method='post' action=''>\n";
	echoAllPostValues($_POST);
}

if($_POST["totalsize"]){
	$totalsize_chunk=num_bitunit($_POST["totalsize"]);
}

//��ҳ��ͬ��ҳ��β
function ffooter(){
	global $_POST;
	echo "<div id='pageendTag'></div>
	</form>
	<font color=red><B>���Ķ���<a href='$_SERVER[PHP_SELF]?action=readme' target='_blank'><font color=red>˵���ĵ�</font></a>��</B></font><br>
	<br>
	<B>faisunSQL�Ե������ݿⱸ�ݳ��� V".VERSION."</B><br>
	ʹ�������BUG�����뵽 <a href='http://www.faisun.com/bbs/' target='_blank'>�촰����</a> ����<br>
	������&copy;�� <a href='mailto:faisun@sina.com'>faisun</a> ��� <a href='http://www.faisun.com' target='_blank'>�촰</a> �ṩ<br>
	��ѳ��� ��ӭ����������<br>
	</center>
	</body></html>
	<script language='Javascript'>
	function showmywin(){
		if(!document.doshowmywin) return;
		if(top.myframeset&&this.window.name=='mainFrame1'){
			top.myframeset.rows='$_POST[frametopheight],*,0';
		}
		if(top.myframeset&&this.window.name=='mainFrame2'){
			top.myframeset.rows='$_POST[frametopheight],0,*';
		}
	}
	showmywin();
	</script>";
}

//��ʼ˵�����
function tabletext($ttext="",$twidth=400){
	return "<table width='$twidth' border='0' cellspacing='1' cellpadding='3' align=center><tr><td>$ttext</td></tr></table><br>\n";
}

//��ʼһ�����
function tablestart($ttitle="",$twidth=400){
	return "<table width='1' border='0' cellspacing='0' cellpadding='0' align=center class='tabletitle'>
	<tr><td class='tabletitle'><strong>&nbsp;$ttitle</strong></td></tr> <tr><td>
	<table width='$twidth' border='0' cellspacing='1' cellpadding='2' align=center>";
}

//�������ݵ����
function tabledata($data,$widths=""){
	$pdata=explode("|",$data);
	$pwidths=explode("|",$widths);
	$str="<tr class='tabledata'>\n";
	for(@reset($pdata);@list($key,$val)=@each($pdata);){
		$str.="\t<td style='padding-left:4px' ".(intval($pwidths[$key])?"width='$pwidths[$key]'":"")." nowrap>$pdata[$key]</td>\n";
	}
	$str.="</tr>\n";
	return $str;
}

//����һ�����
function tableend(){
	return "</table></td></tr></table><BR>\n";
}

//��ť��ʽ
function fbutton($type="submit",$name="Submit",$value="ȷ��",$script=""){
	$imagebg="faisunsql_files/buttonbg.gif";
	$height=17;
	$margin=5;
	$forecolor="#A15309";
	$alphacolor=($forecolor=="#123456"?"#654321":"#123456");
	echo "<label style='background:url($imagebg) 0 0;'><label style='width:$margin'></label>";
	echo "<input type='$type' name='$name' value='$value' style='background:$alphacolor;filter:chroma(color=$alphacolor);color:$forecolor;border:0;height:$height' onfocus='this.blur()' $script></label>";
	echo "<label style='background:url($imagebg) 100% 0;height:$height;width:$margin'></label>\n";
}

//topFrame
if($_GET["action"]=="topframe"&&$_GET["framename"]=="topframe"){
	fheader();
	echo "<center><a href='http://www.faisun.com' target='_blank'><img src='faisunsql_files/faisunsqllogo.gif' border=0 width=300 height=71></a></center>";
	ffooter();
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
	exit;
}

if(!defined("IS_ADMIN") and !isset($_POST['db_dbname'])){die("���ڳ���ͷ������д����ȷ�����ã���û�м����κι���Ա�����֤���롣Ϊ�˰�ȫ���벻Ҫ������ȷ�����ݿ��û��������룡");}

//ѡ��Ҫ���ݵ����ݱ�
if (!isset($_POST['action'])){
	fheader();
	echo tabletext("�����г����Ǹ����ݿ������е����ݱ�<br>Ĭ�������ȫ����������Ҳ����ѡ��ֻ��������һ���ֱ�",500);
	echo tablestart("��ѡ��Ҫ���ݵ����ݱ�",500);
	echo tabledata("<strong>����</strong>|<strong>ע��</strong>|<strong>��¼��</strong></td>|<strong>��С</strong></td>|<center><strong>��ѡ</strong><input type='checkbox' name='checkbox' value='' onclick='selrev();'></center>","30%|30%|17%|17%|16%");
	$query=mysql_query("SHOW TABLE STATUS");
	while ($currow=mysql_fetch_array($query)){
		echo tabledata("$currow[Name]|$currow[Comment]|$currow[Rows]|".num_bitunit($currow[Data_length])."|<center><input name='table[$currow[Name]]' type='checkbox' value='yes' datalength='$currow[Data_length]' checked onclick='getsize()'></center>");
	}
	echo tabledata("<B>Ŀǰѡ�����ܴ�С��</B>|&nbsp;|&nbsp;|<input type=hidden name=totalsize value='0'><B><label id='totalsizetxt'></label></B>|<center><B>��ѡ</B><input type='checkbox' name='checkbox' value='' onclick='selrev()'></center>");
	echo tableend();
	echo "<script language='JavaScript'>
	<!--
	  function selrev() {
		with(myform) {
			for(i=0;i<elements.length;i++) {
				thiselm = elements[i];
				if(thiselm.name.match(/table\[\w+\]/))
					thiselm.checked = !thiselm.checked;
			}
		}
		getsize();
	  }
	
	  function num_bitunit(num){
		 bitunit=new Array(' B',' KB',' MB',' GB');
		 for(key=0;key<bitunit.length;key++){
		   if(num>=Math.pow(2,10*key)-1){ //1023B ����ʾΪ 1KB
			  num_bitunit_str=(Math.ceil(num/Math.pow(2,10*key)*100)/100)+' '+bitunit[key];
		   }	 
		 }
		 return num_bitunit_str;
	  }
	
	  function getsize(){
		ts=0;
		with(document.myform) {
			for(i=0;i<elements.length;i++) {
				thiselm = elements[i];
				if(thiselm.datalength&&thiselm.checked)
					ts += parseInt(thiselm.datalength);
			}
		totalsize.value=ts;
	
		totalsizetxt.innerHTML=num_bitunit(ts);
		
		}
	  }
	  getsize();
	-->
	</script>
	<input name='action' type='hidden' id='action' value='selecttype'>";
	fbutton('submit','dosubmit','��һ��',"onclick=getsize()");
	fbutton('reset','doreset','����',"onmouseup=setTimeout('getsize()',100)");
	ffooter();
}

//ѡ�񵼳���ʽ
if($_POST['action']=="selecttype"){
	fheader();
	intval($_POST["totalsize"])>1024*1024 ? $partsaveck="checked" : $downloadck="checked";
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
			if(back_type[0].checked && ".intval($_POST["totalsize"]).">1024*1024 && !confirm(\"��Ҫ�������������Ƚ϶ࣨ{$totalsize_chunk}��������ѡ����ļ�������ʽ��\\n�����ȷ���������������ļ�����ȡ�������ظ��ġ�\"))
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
		echo tabledata("���Ŀ¼��|<input name='dir' value='{$_POST[db_dbname]}data' type='text' size=10>|��Ա���������Ŀ¼��������д��Ȩ��");
		echo tabledata("���ļ�����|<input name='filename' value='sqlback' type='text' size=10>.php|������չ����");
		echo tabledata("�����ļ���ʽ��|<input name='extension' value='php' type='radio' checked>.php <input name='extension' value='zip' type='radio'>.zip <input name='extension' value='gz' type='radio'>.gz |.zip .gz����ѹ���ļ�,ռ�ռ��ٵ��׳���,<br>�Ұ�ȫ�Բ���,�ױ���������");
		echo tabledata("ÿ�������ļ���С��|<input name='filesize' value='100' type='text' size=10>|��λ KB��1 MB = 1024 KB");
		echo tabledata("����һҳʱ������|<input name='nextpgtimeout' value='0' type='text' size=10>|�룬�����Ŀռ䲻֧��Ƶ���ύ�����һ��");   
		echo tabledata("���ݵ������룺|<input name='back_pass' value='' type='password' size=10>|�����HTTP����.php�ļ�ʱ������������룬");   
		echo tabledata("ȷ�ϵ������룺|<input name='back_pass2' value='' type='password' size=10>|Ϊ�˰�ȫ�벻Ҫ����̫�򵥡�");
		echo tableend();
		echo tablestart("����Ĭ�����ã�����ʱ�Կɸ��ģ���",500);
		echo tabledata("��������|<input name='back_host' value='$_POST[db_host]' type='text'>");
		echo tabledata("���ݿ⣺|<input name='back_dbname' value='$_POST[db_dbname]' type='text'>");  
		echo tabledata("�����ݿⲻ����ʱ�Զ�������|<input name='back_autocreate' value=' checked ' type='checkbox' checked>");  
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
		echo tabletext("��ѡ���˵��ļ�������ʽ���������� $totalsize_chunk �ֽڡ�<br><br>��һ���������ݵ���ʱ��Ĭ�ϲ�����",500);
		echo tablestart("���ļ�����-����Ĭ�����ã�����ʱ�Կɸ��ģ���",500);
		echo tabledata("��������|<input name='back_host' value='$_POST[db_host]' type='text'>");
		echo tabledata("���ݿ⣺|<input name='back_dbname' value='$_POST[db_dbname]' type='text'>");
		echo tabledata("�����ļ���ʽ��|<input name='extension' value='php' type='radio' checked>.php <input name='extension' value='zip' type='radio'>.zip <input name='extension' value='gz' type='radio'>.gz");
		echo tabledata("�����ݿⲻ����ʱ�Զ�������|<input name='back_autocreate' value=' checked ' type='checkbox' checked>");
		echo tabledata("��ʾ������̣�|<input name='back_showlog' value=' checked ' type='checkbox'>");
		echo tableend();
		echo "<input name='action' type='hidden' id='action' value='databackup'>";
		fbutton('submit','dosubmit','����');
		fbutton('reset','doreset','����');
		ffooter();
	}
}

if($_POST['action']=="databackup"){

	function escape_string($str){
		$str=mysql_escape_string($str);
		$str=str_replace('\\\'','\'\'',$str);
		$str=str_replace("\\\\","\\\\\\\\",$str);
		$str=str_replace('$','\$',$str);
		return $str;
	}

	function sqldumptable($table,$part=0) {
		if($part) global $_POST,$lastcreate_temp,$tableid;
		$dumpstring = "";

		//structure
		if(($tableid==0 and $_POST[page]==1) or $tableid>$lastcreate_temp or $part==0){
			@mysql_query("SET SQL_QUOTE_SHOW_CREATE = 1");
			$query=mysql_query("SHOW CREATE TABLE `$table`");
			$row=mysql_fetch_row($query);
			$dumpstring.="create(\"$table\",\"".str_replace("\n","\\n",str_replace("\"","\\\"",$row[1]))."\");\n\n";
			if($tableid!=0 and $_POST[page]!=1 and $part) $_POST[lastcreate]++;
			$_POST[lastinsert]=0;
			dealdata($dumpstring);
		}
		mysql_free_result($query);

		//data
		$query = mysql_query("SELECT count(*) as count FROM `$table` ");
		$count = mysql_fetch_array($query);
		$query = mysql_query("SELECT * FROM `$table` limit $_POST[lastinsert],$count[count] ");
		$numfields = mysql_num_fields($query);
		while ($row = mysql_fetch_row($query)) {
			$dumpstring = "insert(\"$table\",\"";
			for ($i=0;$i<$numfields;$i++) {
				if (!isset($row[$i]) or is_null($row[$i])) {
					$row[$i] = "NULL";
				}else {
					$row[$i] = '\''.escape_string($row[$i]).'\'';
				}
			}
			$dumpstring .= implode(",",$row);
			$dumpstring .= "\");\n\n";

			if($part){ $_POST[lastinsert]++; }
			dealdata($dumpstring);
		}
		mysql_free_result($query);
	}

	function timeformat($time){
		return substr("0".floor($time/3600),-2).":".substr("0".floor(($time%3600)/60),-2).":".substr("0".floor($time%60),-2);
	}

	if($_POST[back_type]=="partsave"): ////////////////////////   Save Data ////////////////////////////

		if($_POST[extension]=="zip" or $_POST[extension]=="gz"){
			include("faisunsql_files/zipclass.php");
			if(@function_exists('gzcompress')){
				$fsqlzip=new PHPzip;
				$gzfimename="$_POST[dir]/$_POST[filename].$_POST[extension]";
			}
			else{ fheader();echo "<BR><BR><center>ѹ���ļ���ʽ��Ҫϵͳ֧�֡�</center><BR><BR>";ffooter();exit; }
		}

		if(!$_POST[lastcreate]) $_POST[lastcreate]=0;
		if(!$_POST[lastinsert]) $_POST[lastinsert]=0;
		if(!$_POST[page]) $_POST[page]=0;
		$lastcreate_temp=$_POST[lastcreate];
		$tablearr=array();

		for(@reset($_POST[table]);count($_POST[table])&&@list($key,$val)=@each($_POST[table]);) {
			if ($val=="yes") {
				$tablearr[]=$key;
			}
		}

		if(!is_dir("$_POST[dir]") and !@mkdir("$_POST[dir]",0777)){
			fheader();echo "<BR><BR><center>Ŀ¼'$_POST[dir]'�������Ҳ����Զ�����������Ŀ¼Ȩ�ޣ�Ȩ��Ϊ 777 ����д�ļ�����</center><BR><BR>";ffooter();exit;
		}

		//�Ƿ��ж�����ļ�
		$dfileNo=0;
		$open=opendir($_POST["dir"]);
		$delhtml="";
		while($afilename=readdir($open) and !$_POST[filedeled]){
			$checked="";
			if(preg_match("/^{$_POST[filename]}/ui",$afilename)){
				$checked="checked";
			}if(is_file("$_POST[dir]/$afilename")){
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
				if(!$fsqlzip->startfile("$gzfimename.tmp")){
					fheader();echo "��ͼ��Ŀ¼'$_POST[dir]'д��ѹ���ļ�ʱ������������Ŀ¼Ȩ�ޣ�";ffooter();exit;
				}
				$fsqlzip->addfile(implode('',file("faisunsql_files/faisunsqllogo.gif")),"{$_POST[filename]}_faisunsqllogo.gif","$gzfimename.tmp");
			}else{
				if(!@copy("faisunsql_files/faisunsqllogo.gif","$_POST[dir]/{$_POST[filename]}_faisunsqllogo.gif")){
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
			if(!file_exists("$gzfimename.tmp")){
				fheader();echo "����Ϊѹ���ļ�����ɣ�������Ҫ�������µ�����";ffooter();exit;
			}
		}
		//���������
		$_POST[finByte]=0;
		$query=mysql_query("SHOW TABLE STATUS");
		for($i=0;$i<=$_POST[lastcreate];$i++){
			while($rows=mysql_fetch_array($query)){
				if($rows['Name']==$tablearr[$i]){
					$_POST[finByte]+=$rows['Data_length'];
					break;
				}
			}
		}
		$_POST[finByte]-=($rows['Rows']-$_POST[lastinsert])*$rows['Avg_row_length']; 
		if(!$_POST["StartTime"])$_POST["StartTime"]=time();

		function writefile($data,$method='a'){
			global $_POST,$fsqlzip;
			$file="$_POST[dir]/{$_POST[filename]}_pg{$_POST[page]}.php";
			if(isset($fsqlzip)){
				$fsqlzip->filedata .= $data;
			}else{
				$fp=fopen($file,"$method");
				flock($fp,2);
				fwrite($fp,$data);
			}
		}

		// ��ʼ����һҳ
		$data="<?\nif(!defined('VERSION')){echo \"<meta http-equiv=refresh content='0;URL={$_POST[filename]}.php'>\";exit;}\n";
		writefile("$data","w");

		$current_size=0;
		function dealdata($data){
			global $_POST,$current_size,$tablearr,$fsqlzip,$gzfimename;
			$current_size+=strlen($data);
			writefile($data);
			if($current_size>=intval($_POST["filesize"])*1024){
				$current_size=0;
				writefile("\n?".">");

				if(isset($fsqlzip)){
					$fsqlzip->addfile($fsqlzip->filedata,"{$_POST[filename]}_pg{$_POST[page]}.php","$gzfimename.tmp");
				}

				$_POST[page]=intval($_POST[page])+1;

				$finish=intval($_POST[finByte]/$_POST[totalsize]*100);

				fheader();
				echo tablestart("���ڴ����ݿ�'$_POST[db_dbname]'�е������ݡ���",500);

				$str1="<br>-= �������ݱ������ =- <div class='borderdiv' style='width:150px;height:100px;overflow:auto;' align=left>";

				for(reset($tablearr);list($key,$val)=each($tablearr);){
					if($key<$_POST[lastcreate]){
						$str1.="�� $val<BR>\n";
					}else if($key==$_POST[lastcreate]){
						$str1.="<a href='#' id='finisheditem'> </a></div>
						<br>-= �������ݱ��������� =- <div class='borderdiv' style='width:150px;height:100px;overflow:auto;' align=left>
						<font style='color:#FF0000'>�� $val</font><br>\n";
					}else{
						$str1.="�� $val<br>\n";
					}
				}
				$str1.="</div><BR>";

				$str2=tablestart("����״̬",300);
				$str2.=tabledata("�������ݣ�|".num_bitunit($_POST[totalsize])."","100|200");
				$str2.=tabledata("���ѵ�����|".num_bitunit($_POST[finByte])."");
				$str2.=tabledata("ÿҳ������|".num_bitunit(intval($_POST[finByte]/$_POST[page]))."");
				$str2.=tabledata("����ʱ������|".floatval($_POST[nextpgtimeout])." ��");
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
				echo "<script language='Javascript'>
				function checkerror(frame){
					if(top.mainFrame1.location.href!=top.mainFrame2.location.href||(frame.document && !frame.document.all.postingTag && frame.document.all.pageendTag)){
						postingTag.innerHTML='faisunSQL:�ύ���ִ���.�����Զ�<a href=\'javascript:myform.submit();\'>�����ύ</a>...';
						myform.submit();
					}
				}
				if(top.myframeset&&this.window.name=='mainFrame1'){
					myform.target='mainFrame2';
					setInterval('checkerror(top.mainFrame2)',10000+1000*".floatval($_POST[nextpgtimeout]).");
				}
				if(top.myframeset&&this.window.name=='mainFrame2'){
					myform.target='mainFrame1';
					setInterval('checkerror(top.mainFrame1)',10000+1000*".floatval($_POST[nextpgtimeout]).");
				}
				setTimeout('myform.submit();',1000*".floatval("$_POST[nextpgtimeout]").");
				finisheditem.focus();
				document.body.onload=new Function('finisheditem.focus();');
				</script>";
				exit();
			}
		}

		//��������
		for($i=$_POST[lastcreate];$i<count($tablearr);$i++){
			$tableid=$i;
			sqldumptable($tablearr[$i],1);
		}

		//��������ļ�
		$data="echo '<center><BR><BR><BR><BR>��ɡ��������ݶ��Ѿ��������ݿ��С�</center>'; exit; ?".">";

		writefile("$data");
		if(isset($fsqlzip)){
			$fsqlzip->addfile($fsqlzip->filedata,"{$_POST[filename]}_pg{$_POST[page]}.php","$gzfimename.tmp");
		}

		//�����ļ�����
		$data='<?

		$usedumppass=1;  //��������ʱ�Ƿ�ʹ�õ������롣����������˵������룬���ֵ��Ϊ 0 ��HTTP��ʽ���������ļ�����ȡ���������롣

		define("VERSION",'.VERSION.');
		@set_time_limit(30);

		$md5pass="'.md5($_POST[back_pass]).'";

		if($_GET["action"]=="downphp"){
			if(!file_exists("$_GET[phpfile]")||$_GET["db_pass"]!=$md5pass) exit;
			header("Content-disposition: filename=$_GET[phpfile]");
			header("Content-type: unknown/unknown");
			readfile("$_GET[phpfile]");
			exit;
		}

		$_POST["frametopheight"]='.$_POST["frametopheight"].';
		if(!$_GET["framename"]){
			echo "<html>
			<head><meta http-equiv=\'Content-Type\' content=\'text/html; charset=gb2312\'>
			<title>faisunSQL�Ե������ݿⱸ�ݳ��� �� Powerd By faisun</title>
			</head>
			<frameset rows=\'$_POST[frametopheight],*,0\' frameborder=\'NO\' border=\'0\' framespacing=\'0\' name=\'myframeset\'>
				<frame src=\'$_SERVER[PHP_SELF]?action=topframe&framename=topframe\' name=\'topFrame\' scrolling=\'NO\' noresize>
				<frame src=\'$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]&framename=main\' name=\'mainFrame1\'>
				<frame src=\'about:blank\' name=\'mainFrame2\'>  
			</frameset>
			<BODY></BODY>
			</html>";
			exit;
		}
		if($_GET["framename"]=="topframe"&&$_GET["action"]=="topframe"){
			echo "<html><head>
			<title>faisunSQL</title></head><body>
			<center><a href=\'http://www.faisun.com\' target=\'_blank\'><img src=\''.$_POST[filename].'_faisunsqllogo.gif\' border=0 width=300 height=71></a></center>
			</body></html>";exit;
		}
		?'.'><html><head>
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		<title>faisunSQL ���ݿ��Ե������ �� Powerd By faisun</title>'.csssetting().'</head>
		<body link="#0000FF" vlink="#0000FF" alink="#0000FF">
		<center>
		<font color=red>���ļ��� faisun ��д�� <a href="http://www.faisun.com" target="_blank">faisunSQL�Ե������ݿⱸ�ݳ��� V'.VERSION.'</a> ����</font><HR size=1>
		<script language="Javascript">
			document.doshowmywin=1;
			function showmywin(){
				if(!document.doshowmywin) return;
				if(top.myframeset&&this.window.name=="mainFrame1"){
					top.myframeset.rows="<?=$_POST[frametopheight];?>,*,0";
				}
				if(top.myframeset&&this.window.name=="mainFrame2"){
					top.myframeset.rows="<?=$_POST[frametopheight];?>,0,*";
				}
			}
			document.body.onload=showmywin;
		</script>
		<?
		$showmywin0=$_POST[loadpage]?"<script language=Javascript>document.doshowmywin=0;</script></body>":"";
		
		if($_GET["action"]=="downall"){
			echo "<form name=\"actionform\" method=\"post\" action=\"\">";
			if($_POST[db_pass]!=$md5pass and ($_POST[db_pass]=md5($_POST[db_pass]))!=$md5pass){
			?'.'>
		����Ϊ�����ݵİ�ȫ,HTTP��ʽ���������ļ���������ȷ�ĵ������룬�������������ݿ⵼��ʱ�Ѵ�����<BR>
		�����������룺<input name="db_pass" value="" type="password"> <input name="action" type="submit" value=" ȷ�� ">
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
		<input name="db_pass" value="<?=$_POST[db_pass];?'.'>" type="hidden">
		<input name="deleteallfiles" type="submit" value="ɾ�������ļ�" onclick="return confirm(\'ɾ���������б����ļ���ȷ����\');">  
		
		<BR><BR>
		<?
		echo "<a href=\"'.$_POST[filename].'.php?action=downphp&phpfile='.$_POST[filename].'.php&db_pass=$_POST[db_pass]\">'.$_POST[filename].'.php</a><BR>
		<a href=\"'.$_POST[filename].'_faisunsqllogo.gif\">'.$_POST[filename].'_faisunsqllogo.gif</a><BR>
		<input type=\"hidden\" name=\"files[-1]\" value=\"'.$_POST[filename].'.php\">
		<input type=\"hidden\" name=\"files[0]\" value=\"'.$_POST[filename].'_faisunsqllogo.gif\">
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
		?'.'>
		<form name="configform" method="post" action="">'.
		tablestart('������Ϣһ��').
		tabledata("������������|".num_bitunit($_POST[totalsize])."","50%|50%").
		tabledata("�������ݱ�|".count($_POST[table])." ��").
		tabledata("ÿҳ���������ļ�|�� ".num_bitunit($_POST["filesize"]*1024)."").
		tabledata("�����ļ�����|".$_POST[page]." ҳ").
		tabledata("�ļ�������|".($_POST[page]+2)." ��").
		tabledata("����ʱ�䣺|".date("Y-m-d H:i")."").
		tableend().
		tablestart('�������ݿ�����').
		tabledata('��������|<input name="db_host" value="'.$_POST[back_host].'" type="text">',"50%|50%").
		tabledata('���ݿ⣺|<input name="db_dbname" value="'.$_POST[back_dbname].'" type="text">').
		tabledata('�����ݿⲻ����ʱ�Զ�����|<input name="db_autocreate" value="1" type="checkbox" '.$_POST[back_autocreate].'>').
		tabledata('�û�����|<input name="db_username" value="" type="text">').
		tabledata('�ܡ��룺|<input name="db_password" value="" type="password">').
		tabledata('����һҳʱ������|<input name="nextpgtimeout" value="'.floatval($_POST[nextpgtimeout]).'" type="text"> ��').
		tabledata('�������룺|<input name="db_pass" value="" type="password">').
		tableend().
		'<center><input name="action" type="submit" value=" ���� "></center>
		</form>
		<center>
		<a href="'.$_POST[filename].'.php?action=downall" target="_blank">�������HTTP��ʽ���������ļ�</a>.
		</center>
		<?
		exit;
		}
		if($_POST[usedumppass] and md5($_POST[db_pass])!=$md5pass) die("<div id=pageendTag></div>�������벻��ȷ������������˵������룬��ѱ�Դ�ļ���ͷ�� \$usedumppass ��ֵ��Ϊ 0 �� $showmywin0");
		@mysql_connect($_POST[db_host],$_POST[db_username],$_POST[db_password]) or die("<div id=pageendTag></div><BR><BR><center>�������ӷ����������ӳ�ʱ���뷵�ؼ���������á�</center> $showmywin0");
		if(!@mysql_select_db($_POST[db_dbname])){
			if(!$_POST[db_autocreate]){echo "<div id=pageendTag></div><BR><BR><center>���ݿ�[{$_POST[db_dbname]}]�����ڣ��뷵�ؼ���������á�</center> $showmywin0";exit;	}
			if(!mysql_query("CREATE DATABASE `$_POST[db_dbname]`")){echo "<div id=pageendTag></div><BR><BR><center>���ݿ�[{$_POST[db_dbname]}]���������Զ�����ʧ�ܣ��뷵�ؼ���������á�</center> $showmywin0";exit;}
			mysql_select_db("$_POST[db_dbname]");
		}
		function query($sql){
		  if(!mysql_query($sql)){
			echo "<BR><BR><font color=red>MySQL�����������ܷ����˳����BUG��<a href=\"mailto:faisun@sina.com\">�뱨�濪���ߡ�</a>
				 <BR>�汾��V'.VERSION.'<BR>��䣺<XMP>$sql</XMP>������Ϣ�� ".mysql_error()." </font>" ;exit;}
		}
		function create($table,$sql){
			query("DROP TABLE IF EXISTS `$table`;");
			query($sql);
		}
		function insert($table,$data){
			query("REPLACE INTO `$table` VALUES ($data);");
		}
		
		$totalpage='.$_POST[page].';
		if(!$_POST[loadpage]){$_POST[loadpage]=1;}
		include("'.$_POST[filename].'_pg$_POST[loadpage].php");
		echo "<center><form name=myform method=\'post\' action=\'\'>";
		$_POST[loadpage]++;
		for(reset($_POST);list($key,$val)=each($_POST);){
			echo "<input name=\"$key\" value=\"".htmlspecialchars(stripslashes($val))."\" type=\"hidden\">\n";
		}
		echo "<BR><BR>���ڵ������ݵ����ݿ�\'$_POST[db_dbname]\'����<BR><BR>��ҳ������ɣ� �����Զ�����<a href=\'javascript:myform.submit();\'>�� $_POST[loadpage] ҳ</a>���� $totalpage ҳ����
		<BR><BR>(���ǽ��̳��ò����������벻Ҫ�������ҳ�����ӡ�)";
		?'.'>
		<BR><BR><B><div id="postingTag"></div></B>
		<script language="Javascript">
			function checkerror(frame){
				if(top.mainFrame1.location.href!=top.mainFrame2.location.href||(frame.document && !frame.document.all.postingTag && frame.document.all.pageendTag)){
					postingTag.innerHTML="faisunSQL:�ύ���ִ���.�����Զ�<a href=\"javascript:myform.submit();\">�����ύ</a>...";
					myform.submit();
				}
			}
		
			if(top.myframeset&&this.window.name=="mainFrame1"){
				myform.target="mainFrame2";
				setInterval("checkerror(top.mainFrame2)",10000+1000*<?=floatval($_POST[nextpgtimeout]);?>);
			}
			if(top.myframeset&&this.window.name=="mainFrame2"){
				myform.target="mainFrame1";
				setInterval("checkerror(top.mainFrame1)",10000+1000*<?=floatval($_POST[nextpgtimeout]);?>);
			}
			setTimeout("myform.submit();",1000*<?=floatval("$_POST[nextpgtimeout]");?>);
		</script>
		<div id="pageendTag"></div>
		</form></center>
		</body></html>
		';

		//д�������ļ�
		if(isset($fsqlzip)){
			$fsqlzip->addfile($data,"$_POST[filename].php","$gzfimename.tmp");
			rename("$gzfimename.tmp","$gzfimename");
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
			<BR><BR>��Щ�ļ���ѹ��Ϊ'$gzfimename',���ļ���ʽ�ױ���������,��þ���ɾ��.
			<BR><BR>����ѹ���ļ���ѹ��,���ڷ������ɷ���Ŀ¼��������'$_POST[filename].php'���ɽ����ݵ��롣
			<BR><BR>��FTP��ʽ��<a href='$gzfimename' target='_blank'><H3>��HTTP��ʽ���������ļ�</H3></a>
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

		$echo_string = '<?
		@set_time_limit(30);
		?'.'>
		<html><head> <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		<title>faisunSQL ���ݿ��Ե������ �� Powerd By faisun</title>'.csssetting().'</head>
		<body link="#0000FF" vlink="#0000FF" alink="#0000FF">
		<center>
		<font color=red>���ļ��� faisun ��д�� <a href="http://www.faisun.com" target="_blank">faisunSQL�Ե������ݿⱸ�ݳ��� V'.VERSION.'</a> ����</font><HR size=1>
		</center>
		<?
		if(!$_POST["action"]){
			?'.'>
			<form name="configform" method="post" action="">'.
			tablestart('������Ϣһ��').
			tabledata("������������|".num_bitunit($_POST[totalsize])."").
			tabledata("�������ݱ�|".count($_POST[table])." ��").
			tabledata("����ʱ�䣺|".date("Y-m-d H:i")."").
			tableend().
			tablestart("�������ݿ�����").
			tabledata('��������|<input name="db_host" value="'.$_POST[back_host].'" type="text">').
			tabledata('���ݿ⣺|<input name="db_dbname" value="'.$_POST[back_dbname].'" type="text">').
			tabledata('�����ݿⲻ����ʱ�Զ�������|<input name="db_autocreate" value="1" type="checkbox" '.$_POST[back_autocreate].'>').
			tabledata('��ʾ������̣�|<input name="back_showlog" value="1" type="checkbox" '.$_POST[back_showlog].'>').
			tabledata('�û�����|<input name="db_username" value="" type="text">').
			tabledata('�ܡ��룺|<input name="db_password" value="" type="password">').
			tableend().
			'<center><input name="action" type="submit" value=" ���� "></center>
			</form></body></html>
			<?
			exit;
		}

		@mysql_connect($_POST[db_host],$_POST[db_username],$_POST[db_password]) or die("<BR><BR><center>�������ӷ����������ӳ�ʱ���뷵�ؼ���������á�</center>");
		if(!@mysql_select_db($_POST[db_dbname])){
			if(!$_POST[db_autocreate]){echo "<BR><BR><center>���ݿ� [{$_POST[db_dbname]}]�����ڣ��뷵�ؼ���������á�</center>";exit;	}
			if(!mysql_query("CREATE DATABASE `$_POST[db_dbname]`")){echo "<BR><BR><center>���ݿ�[{$_POST[db_dbname]}]���������Զ�����ʧ�ܣ��뷵�ؼ���������á�</center>";exit;}
			else if($_POST[back_showlog]) echo "<BR><BR>�������ݿ� [{$_POST[db_dbname]}]...OK.";
			mysql_select_db("$_POST[db_dbname]");
		}
		function query($sql){
			if(!mysql_query($sql)) echo "<BR><font color=red>MySQL�����������ܷ����˳����BUG��<a href=\"mailto:faisun@sina.com\">�뱨�濪���ߡ�</a>
										 <BR>�汾��V'.VERSION.'<BR>��䣺<XMP>$sql</XMP><BR>������Ϣ�� ".mysql_error()." </font>" ;
		}
		function create($table,$sql){
			global $_POST,$firstinsert;
			if($_POST[back_showlog]){$firstinsert=1;echo "\n<BR><BR>�������ݱ� [$table]...";}
			query("DROP TABLE IF EXISTS `$table`;");
			query($sql);
		}
		function insert($table,$data){
			global $_POST,$firstinsert;
			if($_POST[back_showlog] and $firstinsert){$firstinsert=0; echo "\n<BR>������ݵ����ݱ� [$table]...";}
			query("INSERT INTO `$table` VALUES ($data);");
		}
		';
		////// ��ͷ���ֽ��� ////////

		function dealdata($data){
			global $echo_string;
			$echo_string .= "$data";
		}
		for(@reset($_POST[table]);@count($_POST[table])&&@list($key,$val)=@each($_POST[table]);) {
			if ($val=="yes") {
				sqldumptable($key,0);
			}
		}

		$echo_string .= "echo \"<BR><BR>��ɡ����������ѳɹ����뵽 [{\$_POST[db_dbname]}]��\"; ?"."></body></html>";

		if($extension){ $echo_string = gzencode($echo_string); }

		header("Content-disposition: filename=$_POST[db_dbname](".date("Ymj",time()).")_faisunsql.php{$extension}");
		header("Content-type: unknown/unknown");
		echo $echo_string;

		exit;
	endif;
}
?>