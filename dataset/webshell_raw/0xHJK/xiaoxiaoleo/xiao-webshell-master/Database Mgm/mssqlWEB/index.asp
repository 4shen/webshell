<%
'BBSGOOD.COM��Ȩ����
'��ϵͳ���ʹ��,��������ҵĿ��,����ϵBBSGOOD.COM��Ȩ
'��ϵ�绰: 13606552007  QQ:38958768

if trim(request.QueryString("login"))="login" then
    Response.Cookies("ipdress")=trim(request.Form("ipdress"))
    Response.Cookies("dataname")=trim(request.Form("dataname"))
    Response.Cookies("username")=trim(request.Form("username"))
    Response.Cookies("password")=trim(request.Form("password"))
    LinkData
    if trim(request.Cookies("linkok"))="yes" then
        closedata
        Response.Redirect "frame.asp"
    end if
else
%>
<html>
<head>
<title>SQL Server���ݿ����߹���ϵͳ-BBSGOOD�ṩ�Ĳ�Ʒ</title>
<meta name="keywords" content="SQL Server���ݿ����߹���">
<meta name="description" content="SQL Server���ݿ����߹���ϵͳ-BBSGOOD�ṩ�Ĳ�Ʒ" />
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<style>
body {
	margin-top: 130px;
	background-image: url(images/bg.jpg);
	background-repeat: repeat-x;
}
body,td,th {
	font-family: ����;
	font-size: 12px;
	color: #333333;
}
</style>
</head>
<body>
<table width="601" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr style="line-height:25px">
    <td width="250" align="left" valign="top" style="padding-right:10px"><img src="images/wz.jpg" width="63" height="57" /><font color="#0099CC">SQL Server���ݿ����߹���ϵͳ<br />
      SQL Server Online Management(��дSSOM)<br />
      ��ϵͳ�������߹����Ѵ�����SQL Server(mssql)���ݿ�,Ŀǰ��Ҫ��������:<br />
      �������SQL���ݿ�,����<br />
      1.����,ɾ��,�޸����ݱ�<br />
      2.����,ɾ��,�޸�ÿ������ֶβ���<br />
      3.SQL���ִ������,����ִ�����е�SQL���,�����洢����,Ҳ���Լ��������롢���¡�ɾ����¼�Ȳ���<br />
      4.�������ݿ�ı���&nbsp;&nbsp;&nbsp;<a href="help.asp" target="_blank"><font color="#0099CC">�������</font></a></font></td>
    <td width="1" bgcolor="#dadada"></td>
    <td width="350" align="center" valign="top"><table width="90%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td height="80" colspan="2"><strong><font color="#006699" size="3"><img src="images/gl.gif" width="50" height="50" />SQL Server���ݿ�����½</font></strong></td>
      </tr>
      <tr><form action="index.asp?login=login" method="post" id=form1 name=form1>
        <td width="40%" height="50" align="left" valign="middle">���ݿ��ַ</td>
        <td width="60%" height="50" align="left" valign="middle"><input name="ipdress" type="text" size="20" />
              <br />
              <font color="#999999">һ��ΪIP��ַ</font></td>
      </tr>
      <tr>
        <td width="40%" height="40" align="left" valign="middle">���ݿ�����</td>
        <td width="60%" height="40" align="left" valign="middle"><input name="dataname" type="text" size="20" /></td>
      </tr>
      <tr>
        <td width="40%" height="40" align="left" valign="middle">���ʵ��ʺ�</td>
        <td width="60%" height="40" align="left" valign="middle"><input name="username" type="text" size="20" /></td>
      </tr>
      <tr>
        <td width="40%" height="40" align="left" valign="middle">���ʵ�����</td>
        <td width="60%" height="40" align="left" valign="middle"><input name="password" type="password" size="20" /></td>
      </tr>
      <tr>
        <td height="40" align="left" valign="middle"></td>
        <td height="40" align="left" valign="middle"><input type="image" src="images/dl.gif" width="82" height="23" /></td>
      </tr>
    </table></td></form>
  </tr>
  <tr style="line-height:25px">
    <td height="130" colspan="3" align="center" valign="bottom" style="padding-right:10px"><a href="http://www.bbsgood.com" target="_blank"><font color="#333333">BBSGOOD.COM</font></a>�ṩ�Ĳ�Ʒ SQL Server Online Management(��дSSOM) v1.0bate </td>
  </tr>
</table>
</body>
</html>
<%
end if

Sub LinkData()
    Dim ConnStr
    ConnStr = "Provider = Sqloledb; User ID = " & trim(request.Cookies("username")) & "; Password = " & trim(request.Cookies("password")) & "; Initial Catalog = " & trim(request.Cookies("dataname")) & "; Data Source = " & trim(request.Cookies("ipdress")) & ";"
    On Error Resume Next
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.open ConnStr
    If Err Then
	    err.Clear
	    Set Conn = Nothing
	    Response.Write "<script>alert('���ݿ����ӳ����������ӵĵ�ַ,���ݿ�����,�ʺ�,�����Ƿ���ȷ��');history.back(-1);</script>"
	    Response.End
	else
	    Response.Cookies("linkok")="yes"
    End If
End Sub
Sub CloseData()
    if IsObject(conn) then
        conn.Close
        set conn=nothing
    end if
End Sub
%>