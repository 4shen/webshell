<%@ Language="VBScript" %>
<%
'####################################
'#									#
'#		   ����ASP̽�� V1.8         #
'#             040606               #
'#  �����غ� http://www.ajiang.net  #
'#	 �����ʼ� info@ajiang.net		#
'#									#
'#    ת�ر�����ʱ�뱣����Щ��Ϣ    #
'#								    #
'####################################

' ʹ�������������������WIN2003��ʾ����
Response.Buffer = true

' �������������
Dim ObjTotest(26,4)

ObjTotest(0,0) = "MSWC.AdRotator"
ObjTotest(1,0) = "MSWC.BrowserType"
ObjTotest(2,0) = "MSWC.NextLink"
ObjTotest(3,0) = "MSWC.Tools"
ObjTotest(4,0) = "MSWC.Status"
ObjTotest(5,0) = "MSWC.Counters"
ObjTotest(6,0) = "IISSample.ContentRotator"
ObjTotest(7,0) = "IISSample.PageCounter"
ObjTotest(8,0) = "MSWC.PermissionChecker"
ObjTotest(9,0) = "Scripting.FileSystemObject"
	ObjTotest(9,1) = "(FSO �ı��ļ���д)"
ObjTotest(10,0) = "adodb.connection"
	ObjTotest(10,1) = "(ADO ���ݶ���)"
	
ObjTotest(11,0) = "SoftArtisans.FileUp"
	ObjTotest(11,1) = "(SA-FileUp �ļ��ϴ�)"
ObjTotest(12,0) = "SoftArtisans.FileManager"
	ObjTotest(12,1) = "(SoftArtisans �ļ�����)"
ObjTotest(13,0) = "LyfUpload.UploadFile"
	ObjTotest(13,1) = "(���Ʒ���ļ��ϴ����)"
ObjTotest(14,0) = "Persits.Upload.1"
	ObjTotest(14,1) = "(ASPUpload �ļ��ϴ�)"
ObjTotest(15,0) = "w3.upload"
	ObjTotest(15,1) = "(Dimac �ļ��ϴ�)"

ObjTotest(16,0) = "JMail.SmtpMail"
	ObjTotest(16,1) = "(Dimac JMail �ʼ��շ�) <a href='http://www.ajiang.net'>�����ֲ�����</a>"
ObjTotest(17,0) = "CDONTS.NewMail"
	ObjTotest(17,1) = "(���� SMTP ����)"
ObjTotest(18,0) = "Persits.MailSender"
	ObjTotest(18,1) = "(ASPemail ����)"
ObjTotest(19,0) = "SMTPsvg.Mailer"
	ObjTotest(19,1) = "(ASPmail ����)"
ObjTotest(20,0) = "DkQmail.Qmail"
	ObjTotest(20,1) = "(dkQmail ����)"
ObjTotest(21,0) = "Geocel.Mailer"
	ObjTotest(21,1) = "(Geocel ����)"
ObjTotest(22,0) = "IISmail.Iismail.1"
	ObjTotest(22,1) = "(IISmail ����)"
ObjTotest(23,0) = "SmtpMail.SmtpMail.1"
	ObjTotest(23,1) = "(SmtpMail ����)"
	
ObjTotest(24,0) = "SoftArtisans.ImageGen"
	ObjTotest(24,1) = "(SA ��ͼ���д���)"
ObjTotest(25,0) = "W3Image.Image"
	ObjTotest(25,1) = "(Dimac ��ͼ���д���)"

public IsObj,VerObj,TestObj
public okOS,okCPUS,okCPU

'���Ԥ�����֧��������汾

dim i
for i=0 to 25
	on error resume next
	IsObj=false
	VerObj=""
	'dim TestObj
	TestObj=""
	set TestObj=server.CreateObject(ObjTotest(i,0))
	If -2147221005 <> Err then		'��л����iAmFisher�ı�����
		IsObj = True
		VerObj = TestObj.version
		if VerObj="" or isnull(VerObj) then VerObj=TestObj.about
	end if
	ObjTotest(i,2)=IsObj
	ObjTotest(i,3)=VerObj
next

'�������Ƿ�֧�ּ�����汾���ӳ���
sub ObjTest(strObj)
	on error resume next
	IsObj=false
	VerObj=""
	TestObj=""
	set TestObj=server.CreateObject (strObj)
	If -2147221005 <> Err then		'��л����iAmFisher�ı�����
		IsObj = True
		VerObj = TestObj.version
		if VerObj="" or isnull(VerObj) then VerObj=TestObj.about
	end if	
End sub
%>
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<TITLE>ASP̽��V1.8������http://www.ajiang.net</TITLE>
<style>
<!--
BODY
{
	FONT-FAMILY: ����;
	FONT-SIZE: 9pt
}
TD
{
	FONT-SIZE: 9pt
}
A
{
	COLOR: #000000;
	TEXT-DECORATION: none
}
A:hover
{
	COLOR: #3F8805;
	TEXT-DECORATION: underline
}
.input
{
	BORDER: #111111 1px solid;
	FONT-SIZE: 9pt;
	BACKGROUND-color: #F8FFF0
}
.backs
{
	BACKGROUND-COLOR: #3F8805;
	COLOR: #ffffff;

}
.backq
{
	BACKGROUND-COLOR: #EEFEE0
}
.backc
{
	BACKGROUND-COLOR: #3F8805;
	BORDER: medium none;
	COLOR: #ffffff;
	HEIGHT: 18px;
	font-size: 9pt
}
.fonts
{
	COLOR: #3F8805
}
-->
</STYLE>
</HEAD>
<BODY>
<a href="mailto:info@ajiang.net">����</a>��д��ASP̽��-<font class=fonts>V1.8</font><br><br>
<font class=fonts>�Ƿ�֧��ASP</font>
<br>���������������ʾ���Ŀռ䲻֧��ASP��
<br>1�����ʱ��ļ�ʱ��ʾ���ء�
<br>2�����ʱ��ļ�ʱ�������ơ�&lt;%@ Language="VBScript" %&gt;�������֡�
<br><br>

<font class=fonts>���������йز���</font>
<table border=0 width=450 cellspacing=0 cellpadding=0 bgcolor="#3F8805">
<tr><td>

	<table border=0 width=450 cellspacing=1 cellpadding=0>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;��������</td><td>&nbsp;<%=Request.ServerVariables("SERVER_NAME")%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;������IP</td><td>&nbsp;<%=Request.ServerVariables("LOCAL_ADDR")%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;�������˿�</td><td>&nbsp;<%=Request.ServerVariables("SERVER_PORT")%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;������ʱ��</td><td>&nbsp;<%=now%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;IIS�汾</td><td>&nbsp;<%=Request.ServerVariables("SERVER_SOFTWARE")%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;�ű���ʱʱ��</td><td>&nbsp;<%=Server.ScriptTimeout%> ��</td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;���ļ�·��</td><td>&nbsp;<%=Request.ServerVariables("PATH_TRANSLATED")%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;��������������</td><td>&nbsp;<%=ScriptEngine & "/"& ScriptEngineMajorVersion &"."&ScriptEngineMinorVersion&"."& ScriptEngineBuildVersion %></td>
	  </tr>
	<%getsysinfo()  '��÷���������%>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;������CPU����</td><td>&nbsp;<%=okCPUS%> ��</td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;������CPU����</td><td>&nbsp;<%=okCPU%></td>
	  </tr>
	  <tr bgcolor="#EEFEE0" height=18>
		<td align=left>&nbsp;����������ϵͳ</td><td>&nbsp;<%=okOS%></td>
	  </tr>
	</table>

</td></tr>
</table>
<br>
<font class=fonts>���֧�����</font>
<%
Dim strClass
	strClass = Trim(Request.Form("classname"))
	If "" <> strClass then
	Response.Write "<br>��ָ��������ļ������"
	Dim Verobj1
	ObjTest(strClass)
	  If Not IsObj then 
		Response.Write "<br><font color=red>���ź����÷�������֧�� " & strclass & " �����</font>"
	  Else
		if VerObj="" or isnull(VerObj) then 
			Verobj1="�޷�ȡ�ø�����汾"
		Else
			Verobj1="������汾�ǣ�" & VerObj
		End If
		Response.Write "<br><font class=fonts>��ϲ���÷�����֧�� " & strclass & " �����" & verobj1 & "</font>"
	  End If
	  Response.Write "<br>"
	end if
	%>


<br>�� IIS�Դ���ASP���
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
	<tr height=18 class=backs align=center><td width=320>�� �� �� ��</td><td width=130>֧�ּ��汾</td></tr>
	<%For i=0 to 10%>
	<tr height="18" class=backq>
		<td align=left>&nbsp;<%=ObjTotest(i,0) & "<font color=#888888>&nbsp;" & ObjTotest(i,1)%></font></td>
		<td align=left>&nbsp;<%
		If Not ObjTotest(i,2) Then 
			Response.Write "<font color=red><b>��</b></font>"
		Else
			Response.Write "<font class=fonts><b>��</b></font> <a title='" & ObjTotest(i,3) & "'>" & left(ObjTotest(i,3),11) & "</a>"
		End If%></td>
	</tr>
	<%next%>
</table>

<br>�� �������ļ��ϴ��͹������
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
	<tr height=18 class=backs align=center><td width=320>�� �� �� ��</td><td width=130>֧�ּ��汾</td></tr>
	<%For i=11 to 15%>
	<tr height="18" class=backq>
		<td align=left>&nbsp;<%=ObjTotest(i,0) & "<font color=#888888>&nbsp;" & ObjTotest(i,1)%></font></td>
		<td align=left>&nbsp;<%
		If Not ObjTotest(i,2) Then 
			Response.Write "<font color=red><b>��</b></font>"
		Else
			Response.Write "<font class=fonts><b>��</b></font> <a title='" & ObjTotest(i,3) & "'>" & left(ObjTotest(i,3),11) & "</a>"
		End If%></td>
	</tr>
	<%next%>
</table>

<br>�� �������շ��ʼ����
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
	<tr height=18 class=backs align=center><td width=320>�� �� �� ��</td><td width=130>֧�ּ��汾</td></tr>
	<%For i=16 to 23%>
	<tr height="18" class=backq>
		<td align=left>&nbsp;<%=ObjTotest(i,0) & "<font color=#888888>&nbsp;" & ObjTotest(i,1)%></font></td>
		<td align=left>&nbsp;<%
		If Not ObjTotest(i,2) Then 
			Response.Write "<font color=red><b>��</b></font>"
		Else
			Response.Write "<font class=fonts><b>��</b></font> <a title='" & ObjTotest(i,3) & "'>" & left(ObjTotest(i,3),11) & "</a>"
		End If%></td>
	</tr>
	<%next%>
</table>

<br>�� ͼ�������
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
	<tr height=18 class=backs align=center><td width=320>�� �� �� ��</td><td width=130>֧�ּ��汾</td></tr>
	<%For i=24 to 25%>
	<tr height="18" class=backq>
		<td align=left>&nbsp;<%=ObjTotest(i,0) & "<font color=#888888>&nbsp;" & ObjTotest(i,1)%></font></td>
		<td align=left>&nbsp;<%
		If Not ObjTotest(i,2) Then 
			Response.Write "<font color=red><b>��</b></font>"
		Else
			Response.Write "<font class=fonts><b>��</b></font> <a title='" & ObjTotest(i,3) & "'>" & left(ObjTotest(i,3),11) & "</a>"
		End If%></td>
	</tr>
	<%next%>
</table>

<br>�� �������֧��������<br>
��������������������Ҫ���������ProgId��ClassId��
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
<FORM action=<%=Request.ServerVariables("SCRIPT_NAME")%> method=post id=form1 name=form1>
	<tr height="18" class=backq>
		<td align=center height=30><input class=input type=text value="" name="classname" size=40>
<INPUT type=submit value=" ȷ �� " class=backc id=submit1 name=submit1>
<INPUT type=reset value=" �� �� " class=backc id=reset1 name=reset1> 
</td>
	  </tr>
</FORM>
</table>

<%
Response.Flush



if ObjTest("Scripting.FileSystemObject") then

	set fsoobj=server.CreateObject("Scripting.FileSystemObject")

%>

<br><font class=fonts>������ز���</font>

<br>�� ������������Ϣ

<table class=backq border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
  <tr height="18" align=center class="backs">
	<td width="100">�̷��ʹ�������</td>
	<td width="50">����</td>
	<td width="80">���</td>
	<td width="60">�ļ�ϵͳ</td>
	<td width="80">���ÿռ�</td>
	<td width="80">�ܿռ�</td>
  </tr>
<%

	' ���Դ�����Ϣ���뷨���ԡ�COCOON ASP ̽�롱
	
	set drvObj=fsoobj.Drives
	for each d in drvObj
%>
  <tr height="18" align=center>
	<td align="right"><%=cdrivetype(d.DriveType) & " " & d.DriveLetter%>:</td>
<%
	if d.DriveLetter = "A" then	'Ϊ��ֹӰ������������������
		Response.Write "<td></td><td></td><td></td><td></td><td></td>"
	else
%>
	<td><%=cIsReady(d.isReady)%></td>
	<td><%=d.VolumeName%></td>
	<td><%=d.FileSystem%></td>
	<td align="right"><%=cSize(d.FreeSpace)%></td>
	<td align="right"><%=cSize(d.TotalSize)%></td>
<%
	end if
%>
  </tr>
<%
	next
%>
</td></tr>
</table>

<br>�� ��ǰ�ļ�����Ϣ
<%

Response.Flush


	dPath = server.MapPath("./")
	set dDir = fsoObj.GetFolder(dPath)
	set dDrive = fsoObj.GetDrive(dDir.Drive)
%>
�ļ���: <%=dPath%>
<table class=backq border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
  <tr height="18" align="center" class="backs">
	<td width="75">���ÿռ�</td>
	<td width="75">���ÿռ�</td>
	<td width="75">�ļ�����</td>
	<td width="75">�ļ���</td>
	<td width="150">����ʱ��</td>
  </tr>
  <tr height="18" align="center">
	<td><%=cSize(dDir.Size)%></td>
	<td><%=cSize(dDrive.AvailableSpace)%></td>
	<td><%=dDir.SubFolders.Count%></td>
	<td><%=dDir.Files.Count%></td>
	<td><%=dDir.DateCreated%></td>
  </tr>
</td></tr>
</table>

<br>�� �����ļ������ٶȲ���<br>
<%
Response.Flush


	' �����ļ���д���뷨���ԡ��Գ����ӡ�
	
	Response.Write "�����ظ�������д���ɾ���ı��ļ�50��..."

	dim thetime3,tempfile,iserr

iserr=false
	t1=timer
	tempfile=server.MapPath("./") & "\aspchecktest.txt"
	for i=1 to 50
		Err.Clear

		set tempfileOBJ = FsoObj.CreateTextFile(tempfile,true)
		if Err <> 0 then
			Response.Write "�����ļ�����<br><br>"
			iserr=true
			Err.Clear
			exit for
		end if
		tempfileOBJ.WriteLine "Only for test. Ajiang ASPcheck"
		if Err <> 0 then
			Response.Write "д���ļ�����<br><br>"
			iserr=true
			Err.Clear
			exit for
		end if
		tempfileOBJ.close
		Set tempfileOBJ = FsoObj.GetFile(tempfile)
		tempfileOBJ.Delete 
		if Err <> 0 then
			Response.Write "ɾ���ļ�����<br><br>"
			iserr=true
			Err.Clear
			exit for
		end if
		set tempfileOBJ=nothing
	next
	t2=timer
if iserr <> true then
	thetime3=cstr(int(( (t2-t1)*10000 )+0.5)/10)
	Response.Write "...����ɣ�<font color=red>" & thetime3 & "����</font>��<br>"
	Response.Flush

%>
<table class=backq border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
  <tr height=18 align=center class="backs">
	<td width=320>�� �� �� �� �� �� ��</td>
	<td width=130>���ʱ��(����)</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.hdidc.com">��������hdidc.com����<font color=#888888> ˫��ǿ2.4,1GddrEcc,SCSI36.4G)</font></a></td><td>&nbsp;32��75</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.gdxf.net/wzkj/index.htm">�·���Ϣ�۸���ASP+CGI�ռ�</a></td><td>&nbsp;46��62</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.68l.com/">68����</a></td><td>&nbsp;78</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.diy5.com">��5�ռ�diy5.com����ǿ����<font color=#888888>(P42.4,2GddrEcc,SCSI72.8G)</font></a></td><td>&nbsp;46��78</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.100u.com?come=aspcheck&keyword=��������"
	>���ſƼ� 100u ����</a></td><td>&nbsp;31��62</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.3366.com.cn"
	>�����������</a></td><td>&nbsp;31��62</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<font color=red>��̨������: <%=Request.ServerVariables("SERVER_NAME")%></font>&nbsp;</td><td>&nbsp;<font color=red><%=thetime3%></font></td>
  </tr>
</table>
<%
end if

Response.Flush
	
	set fsoobj=nothing

end if%>
<br>
<font class=fonts>ASP�ű����ͺ������ٶȲ���</font><br>
<%
Response.Flush

	'��л����ͬѧ¼ http://www.5719.net �Ƽ�ʹ��timer����
	'��Ϊֻ����50��μ��㣬����ȥ�����Ƿ����ѡ���ֱ�Ӽ��
	
	Response.Write "����������ԣ����ڽ���50��μӷ�����..."
	dim t1,t2,lsabc,thetime,thetime2
	t1=timer
	for i=1 to 500000
		lsabc= 1 + 1
	next
	t2=timer
	thetime=cstr(int(( (t2-t1)*10000 )+0.5)/10)
	Response.Write "...����ɣ�<font color=red>" & thetime & "����</font>��<br>"


	Response.Write "����������ԣ����ڽ���20��ο�������..."
	t1=timer
	for i=1 to 200000
		lsabc= 2^0.5
	next
	t2=timer
	thetime2=cstr(int(( (t2-t1)*10000 )+0.5)/10)
	Response.Write "...����ɣ�<font color=red>" & thetime2 & "����</font>��<br>"
%>
<table class=backq border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#3F8805" width="450">
  <tr height=18 align=center class="backs">
	<td width=320>�����յķ����������ʱ��(����)</td>
    <td width=65>��������</td><td width=65>��������</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.100u.com?come=aspcheck&keyword=��������"
	>���ſƼ� 100u ����, <font color=#888888>2003-11-1</font></a></td><td>&nbsp;181��233</td><td>&nbsp;156��218</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.west263.com/index.asp?ads=ajiang"
	>�������� west263 ����, <font color=#888888>2003-11-1</font></a></td><td>&nbsp;171��233</td><td>&nbsp;156��171</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.linkwww.com "
	>�����Ƽ� linkwww ����,  <font color=#888888>2003-11-1</font></a></td><td>&nbsp;181��203</td><td>&nbsp;171</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.9s5.com/"
	>������www.9s5.comȫ����(ASP+PHP+JSP)����,<font color=#888888>2003-11-1</font></a></td><td>&nbsp;171��187</td><td>&nbsp;156��171</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.dnsmy.com/"
	>��Ѷ���� Dnsmy ����, <font color=#888888>2003-11-1</font></a></td><td>&nbsp;155��180</td><td>&nbsp;122��172</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<a href="http://www.hostidc.net"
	>���ݻ��� Hostidc.net ����, <font color=#888888>2004-3-28</font></a></td><td>&nbsp;156��171</td><td>&nbsp;140��156</td>
  </tr>
  <tr height=18>
	<td align=left>&nbsp;<font color=red>��̨������: <%=Request.ServerVariables("SERVER_NAME")%></font>&nbsp;</td><td>&nbsp;<font color=red><%=thetime%></font></td><td>&nbsp;<font color=red><%=thetime2%></font></td>
  </tr>
</table>
<br>
<table border=0 width=450 cellspacing=0 cellpadding=0>
<tr><td align=center>
<b>[<a href="http://www.ajiang.net/products/aspcheck/serverlist.asp#notice">���ѡ�˵��</a>]
&nbsp;[<a href="http://www.ajiang.net/products/aspcheck/serverlist.asp">����ռ��̼�ʱʵ������</a>]
&nbsp;[<a href="http://www.ajiang.net/products/aspcheck/">�鿴�������°�</a>]</b>
</td></tr>
</table>
<br>
<table border=0 width=450 cellspacing=0 cellpadding=0>
<tr><td align=center>
��ӭ���� �������غ� <a href="http://www.ajiang.net">http://www.ajiang.net</a>
<br>�������ɰ���(<a href="mailto:info@ajiang.net?subject=����̽��">info@ajiang.net</a>)��д��ת��ʱ�뱣����Щ��Ϣ
</td></tr>
</table>
</BODY>
</HTML>

<%
function cdrivetype(tnum)
    Select Case tnum
        Case 0: cdrivetype = "δ֪"
        Case 1: cdrivetype = "���ƶ�����"
        Case 2: cdrivetype = "����Ӳ��"
        Case 3: cdrivetype = "�������"
        Case 4: cdrivetype = "CD-ROM"
        Case 5: cdrivetype = "RAM ����"
    End Select
end function

function cIsReady(trd)
    Select Case trd
		case true: cIsReady="<font class=fonts><b>��</b></font>"
		case false: cIsReady="<font color='red'><b>��</b></font>"
	End Select
end function

function cSize(tSize)
    if tSize>=1073741824 then
		cSize=int((tSize/1073741824)*1000)/1000 & " GB"
    elseif tSize>=1048576 then
    	cSize=int((tSize/1048576)*1000)/1000 & " MB"
    elseif tSize>=1024 then
		cSize=int((tSize/1024)*1000)/1000 & " KB"
	else
		cSize=tSize & "B"
	end if
end function

sub getsysinfo()
  on error resume next
  Set WshShell = server.CreateObject("WScript.Shell")
  Set WshSysEnv = WshShell.Environment("SYSTEM")
  okOS = cstr(WshSysEnv("OS"))
  okCPUS = cstr(WshSysEnv("NUMBER_OF_PROCESSORS"))
  okCPU = cstr(WshSysEnv("PROCESSOR_IDENTIFIER"))
  if isnull(okCPUS) then
    okCPUS = Request.ServerVariables("NUMBER_OF_PROCESSORS")
  elseif okCPUS="" then
    okCPUS = Request.ServerVariables("NUMBER_OF_PROCESSORS")
  end if
  if Request.ServerVariables("OS")="" then okOS=okOS & "(������ Windows Server 2003)"
end sub
%>