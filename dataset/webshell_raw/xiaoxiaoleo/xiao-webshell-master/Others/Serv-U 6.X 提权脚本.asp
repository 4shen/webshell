ʹ�÷����������6.4���µı���Ĭ�ϼ��ɣ�ֻҪ�������Ҫ�޸�ִ�е�����ɣ����Ϊ6.4���ڡ��������ˡ�����21��Ȼ�����ڡ�������IP������д����������ʵIP�� 

<% end select
function Gpath()
on error resume next
err.clear
set f=Server.CreateObject("Scripting.FileSystemObject")
if err.number>0 then
gpath="c:"
exit function
end if
gpath=f.GetSpecialFolder(0)
gpath=lcase(left(gpath,2))
set f=nothing
end function
Function GName() 
If request.servervariables("SERVER_PORT")="80" Then 
GName="http://" & request.servervariables("server_name")&lcase(request.servervariables("script_name")) 
Else 
GName="http://" & request.servervariables("server_name")&":"&request.servervariables("SERVER_PORT")&lcase(request.servervariables("script_name")) 
End If 
End Function 
%>
 
