<%
'BBSGOOD.COM��Ȩ����
'��ϵͳ���ʹ��,��������ҵĿ��,����ϵBBSGOOD.COM��Ȩ
'��ϵ�绰: 13606552007  QQ:38958768
%>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<script>
    function checkclick(msg){if(confirm(msg)){event.returnValue=true;}else{event.returnValue=false;}}
</script>
<style>
A:link   {color:#0000ff;font-style: normal; text-decoration: none; cursor: hand;}
A:visited   {color:#0000ff;font-style: normal; text-decoration: none;}
A:active    {color:#0000ff;font-style: normal; text-decoration: none;}
A:hover  {color:#0000ff;font-style:bold; text-decoration:underline;}
</style>
</head>
<body>
<%
dim i,rs,sql
if trim(request.Cookies("linkok"))="yes" then
    if not IsObject(conn) then
        LinkData
    end if
    Response.Write "<table border=""0"" width=""100%""><tr><td valign=""top"">"
    select case RequestNumSafe(request.QueryString("cz"))
    case 0
        %>
<font style="line-height:30px" size="2"><img src="images/glxttb.jpg" width="65" height="50"> <strong><font color="#FF6600">
        SQL Server���ݿ����߹���ϵͳ</font></strong><br /><font color="#666666">
        SQL Server Online Management(��дSSOM)<br />
        ����΢��Ĭ���ṩ����ҵ������,�ܶ��û�ʹ������,�в����û�Ҳû�а�װ������.����ܶ��û������ݿ��������Զ�����Ӹ��ص����߽����ݿ��������װ�ھ�������
        ʹ���ⲿ��ʹ�ù������˲��ٵ��鷳,SSOMϵͳ���԰�װ������ڲ���������,�����ⲿ�û�ֱ����(local)���ӾͿ��Թ�����
        ��mysql������������,Ŀǰ��phpmyadmin����������߹���,��SQL Server(mssql)Ҳ��Ҫ��������һ�����߹�����,��������BBSGOOD�Ŷӿ��������mssql�����SSOMϵͳ������.
        <br><br>
        ��ϵͳ�������߹����Ѵ�����SQL Server(mssql)���ݿ�,Ŀǰ��Ҫ��������:<br>
        �������SQL���ݿ�,����<br>
        1.����,ɾ��,�޸����ݱ�<br>
        2.����,ɾ��,�޸�ÿ������ֶβ���<br>
        3.SQL���ִ������,����ִ�����е�SQL���,�����洢����,Ҳ���Լ��������롢���¡�ɾ����¼�Ȳ���<br>
        4.�������ݿ�ı���<br><br>
        Ŀǰ�ò�Ʒ�״β��Է�������Ϊ�й���½��GB2312����<br><br>
        
        ʹ��˵��:<br>
        1.�����ݿ��ַһ����,���������ݿ��������IP��ַ,����ͱ�ϵͳ��ͬ���Ļ�,Ҳ������(local)������
          ����������ݿ�����,���ݿ���ʵ��ʺź�����,�����½����.<br>
        2.��½��,������������ݿ�,���ɹ������ݿ������еı�,�ֶ�,��¼������.<br>
        3.���������SQL���,����������sql�ű�,������Ҫ�Ĳ������������,������select����ѯ����¼.<br>
        4.������������ݿⱸ��,�Ϳ��Զ�������ݿ���б�����,ע�ⱸ�ݵ�·�������ݿ�����������·��.<br>
        <br>BBSGOOD.COM�ṩ�Ĳ�Ʒ</font></font>
        <%
    case 1
        set rsSchema=conn.openSchema(20) 
        rsSchema.movefirst 
        
        Response.Write "<form action=""main.asp?cz=4"" method=""post"">���� <input type=""text"" name=""crtablename"" size=""15""> <input type=""submit"" value="" �����±� ""></form>"
        Response.Write "(��)"&Request.Cookies("dataname")&"->�� &nbsp;&nbsp;<a href=""main.asp?cz=1"">�û���</a> &nbsp;&nbsp;<a href=""main.asp?cz=1&alltable=1"">���б�</a><table border=""0"" width=""700""><tr bgcolor=""#eeeeee"" height=""25""><td>����</td><td colspan=""4"">����</td><td>���¼��SQL��䴦��</td></tr>"
        Do Until rsSchema.EOF
            if request.QueryString("alltable")=1 then
                response.write "<tr><form action=""main.asp?cz=9&tablename2="&rsSchema("TABLE_NAME")&""" method=""post""><td><input type=""text"" name=""tablename"" value="""&rsSchema("TABLE_NAME")&""" size=""15""></td><td><input type=""submit"" value=""����""></td></form><td><a href=""?cz=2&tablename="&rsSchema("TABLE_NAME")&""">��Ʊ�</a></td><td><a href=""?cz=3&tablename="&rsSchema("TABLE_NAME")&""">�򿪱�</a></td><td><a onclick=checkclick('��ȷ��Ҫɾ���ñ������������������?') href=""?cz=6&tablename="&rsSchema("TABLE_NAME")&""">ɾ����</a></td><td><a href=""?cz=10&czsql=1&tablename="&rsSchema("TABLE_NAME")&""">��¼��ѯ</a>|<a href=""?cz=10&czsql=2&tablename="&rsSchema("TABLE_NAME")&""">����</a>|<a href=""?cz=10&czsql=3&tablename="&rsSchema("TABLE_NAME")&""">����</a>|<a href=""?cz=10&czsql=4&tablename="&rsSchema("TABLE_NAME")&""">ɾ��</a></td></tr>"
                Response.Write "<tr><td height=""1"" bgcolor=""#555555"" colspan=""6""></td></tr>"
            else
                if rsSchema("TABLE_TYPE")="TABLE" then 
                    response.write "<tr><form action=""main.asp?cz=9&tablename2="&rsSchema("TABLE_NAME")&""" method=""post""><td><input type=""text"" name=""tablename"" value="""&rsSchema("TABLE_NAME")&""" size=""15""></td><td><input type=""submit"" value=""����""></td></form><td><a href=""?cz=2&tablename="&rsSchema("TABLE_NAME")&""">��Ʊ�</a></td><td><a href=""?cz=3&tablename="&rsSchema("TABLE_NAME")&""">�򿪱�</a></td><td><a onclick=checkclick('��ȷ��Ҫɾ���ñ������������������?') href=""?cz=6&tablename="&rsSchema("TABLE_NAME")&""">ɾ����</a></td><td><a href=""?cz=10&czsql=1&tablename="&rsSchema("TABLE_NAME")&""">��¼��ѯ</a>|<a href=""?cz=10&czsql=2&tablename="&rsSchema("TABLE_NAME")&""">����</a>|<a href=""?cz=10&czsql=3&tablename="&rsSchema("TABLE_NAME")&""">����</a>|<a href=""?cz=10&czsql=4&tablename="&rsSchema("TABLE_NAME")&""">ɾ��</a></td></tr>"
                    Response.Write "<tr><td height=""1"" bgcolor=""#555555"" colspan=""6""></td></tr>"
                end if
            end if
            rsSchema.movenext
        Loop
        Response.Write "</table>"
        rsSchema.close
        set rsSchema=Nothing
    case 2
        dim fieldCount
        set rs=conn.execute("select * from ["&trim(request.QueryString("tablename"))&"]")
        fieldCount = rs.Fields.Count
        Response.Write "<form action=""main.asp?cz=5&tablename="&trim(request.QueryString("tablename"))&""" method=""post"" id=form1 name=form1>�ֶ��� <input type=""text"" name=""crfield"" size=""15""> <select name=""fieldtype"">"
        Response.Write "<option value="""">�ֶ�����</option>"
        Response.Write "<option value=""int"">int</option>"
        Response.Write "<option value=""bigint"">bigint</option>"
        Response.Write "<option value=""smallint"">smallint</option>"
        Response.Write "<option value=""varchar"">varchar</option>"
        Response.Write "<option value=""ntext"">ntext</option>"
        Response.Write "<option value=""float"">float</option>"
        Response.Write "<option value=""bit"">bit</option>"
        Response.Write "<option value=""nvarchar"">nvarchar</option>"
        Response.Write "<option value=""datetime"">datetime</option>"
        Response.Write "<option value=""image"">image</option>"
        Response.Write "<option value=""text"">text</option>"
        Response.Write "<option value=""nchar"">nchar</option>"
        Response.Write "<option value=""money"">money</option>"
        Response.Write "<option value=""smalldatetime"">smalldatetime</option>"
        Response.Write "<option value=""numeric"">numeric</option>"
        Response.Write "<option value=""varbinary"">varbinary</option>"
        Response.Write "<option value=""tinyint"">tinyint</option>"
        Response.Write "<option value=""timestamp"">timestamp</option>"
        Response.Write "<option value=""sql_variant"">sql_variant</option>"
        Response.Write "<option value=""real"">real</option>"
        
        Response.Write "</select> <input type=""submit"" value="" �½��ֶ� "" id=1 name=1></form>"
        Response.Write "<a href=""main.asp?cz=1"">(��)"&Request.Cookies("dataname")&"</a>->(��Ʊ�)"&trim(request.QueryString("tablename"))&"->��"&fieldCount&"���ֶ�"
        Response.Write "<table border=""0"" width=""500"">"
        Response.Write "<tr align=""center"" bgcolor=""#eeeeee"" height=""30""><td>�ֶ�����</td><td>�ֶ�����</td><td>�ֶγ���</td><td colspan=""2"">����</td></tr>"
        For i=0 to fieldCount - 1
            Response.Write "<tr align=""center""><td><form action=""main.asp?cz=7&tablename="&trim(request.QueryString("tablename"))&""" method=""post"">"
            Response.Write "<input type=""text"" name=""fieldsname"" value="""&rs.Fields(i).Name&""" size=""10""><input type=""hidden"" name=""fieldsname2"" value="""&rs.Fields(i).Name&"""></td><td>"
            Response.Write "<select name=""fieldtype"">"
            select case rs.Fields(i).type
            case 3
                Response.Write "<option value=""int"">int</option>"
            case 5
                Response.Write "<option value=""float"">float</option>"
            case 11
                Response.Write "<option value=""bit"">bit</option>"
            case 20
                Response.Write "<option value=""bigint"">bigint</option>"
            case 130
                Response.Write "<option value=""nchar"">nchar</option>"
            case 200
                Response.Write "<option value=""varchar"">varchar</option>"
            case 202
                Response.Write "<option value=""nvarchar"">nvarchar</option>"
            case 203
                Response.Write "<option value=""ntext"">ntext</option>"
            case 205
                Response.Write "<option value=""image"">image</option>"
            case 135
                Response.Write "<option value=""datetime"">datetime</option>"
            case else
                Response.Write "<option value="""">"&rs.Fields(i).type&"</option>"
            end select
            Response.Write "<option value=""int"">int</option>"
            Response.Write "<option value=""bigint"">bigint</option>"
            Response.Write "<option value=""smallint"">smallint</option>"
            Response.Write "<option value=""varchar"">varchar</option>"
            Response.Write "<option value=""ntext"">ntext</option>"
            Response.Write "<option value=""float"">float</option>"
            Response.Write "<option value=""bit"">bit</option>"
            Response.Write "<option value=""nvarchar"">nvarchar</option>"
            Response.Write "<option value=""datetime"">datetime</option>"
            Response.Write "<option value=""image"">image</option>"
            Response.Write "<option value=""text"">text</option>"
            Response.Write "<option value=""nchar"">nchar</option>"
            Response.Write "<option value=""money"">money</option>"
            Response.Write "<option value=""smalldatetime"">smalldatetime</option>"
            Response.Write "<option value=""numeric"">numeric</option>"
            Response.Write "<option value=""varbinary"">varbinary</option>"
            Response.Write "<option value=""tinyint"">tinyint</option>"
            Response.Write "<option value=""timestamp"">timestamp</option>"
            Response.Write "<option value=""sql_variant"">sql_variant</option>"
            Response.Write "<option value=""real"">real</option>"            
            Response.Write "</select>"
            Response.Write "</td><td><input name=""fieldssize"" type=""text"" value="""&rs.Fields(i).DefinedSize&""" size=""10""></td><td><input type=""submit"" value=""����""></td><td><a onclick=checkclick('��ȷ��Ҫɾ�����ֶΣ������������������?') href=""main.asp?cz=8&tablename="&trim(request.QueryString("tablename"))&"&fieldsname="&rs.Fields(i).Name&""">ɾ��</a></td></form></tr><tr><td height=""1"" bgcolor=""#555555"" colspan=""5""></td></tr>"
        Next
        Response.Write "</table>"
        rs.close
        set rs=nothing
    case 3
        Response.Write "<a href=""?cz=10&czsql=1&tablename="&trim(request.QueryString("tablename"))&""">���¼��ѯ</a> | <a href=""?cz=10&czsql=2&tablename="&trim(request.QueryString("tablename"))&""">����</a> | <a href=""?cz=10&czsql=3&tablename="&trim(request.QueryString("tablename"))&""">����</a> | <a href=""?cz=10&czsql=4&tablename="&trim(request.QueryString("tablename"))&""">ɾ��</a><br><br>"
        set rs=conn.execute("select top 50 * from ["&trim(request.QueryString("tablename"))&"]")
        fieldCount = rs.Fields.Count
        Response.Write "<a href=""main.asp?cz=1"">(��)"&Request.Cookies("dataname")&"</a>->(�򿪱�)"&trim(request.QueryString("tablename"))&"->��ʾǰ50����¼<br>"
        Response.Write "<table border=""0""><tr align=""center"" bgcolor=""#eeeeee"" height=""30"">"
        For i=0 to fieldCount - 1
            Response.Write "<td>"&rs.Fields(i).Name&"</td>"
        Next
        Response.Write "</tr>"
        while not rs.eof
            Response.Write "<tr>"
            For i=0 to fieldCount - 1
                Response.Write "<td><TEXTAREA rows=""2"" cols=""20"">"
                if ISEMPTY(rs(i)) then
                    'Response.Write rs(i)
                else
                    Response.Write rs(i)
                end if
                Response.Write "</TEXTAREA></td>"
            Next
            Response.Write "</tr>"
            'Response.Write "<tr><td height=""1"" bgcolor=""#555555"" colspan=""5""></td></tr>"
            rs.movenext
        wend
        rs.close
        set rs=nothing
        Response.Write "</table>"
    case 4
        dim crtablename
        crtablename=trim(request.Form("crtablename"))
        crtable("CREATE TABLE ["&crtablename&"] (ID int IDENTITY (1,1) not null PRIMARY key)")
        Response.Write "�½��ı�Ĭ�ϴ�����һ��ID�ֶ�,������������,����,������.<a href=""main.asp?cz=1"">����</a>"
    case 5
        dim crfield
        tablename=trim(request.QueryString("tablename"))
        crfield=trim(request.Form("crfield"))
        fieldtype=trim(request.Form("fieldtype"))
        select case fieldtype
        case ""
            Response.Write "��ѡ���ֶ�����"
        case "varchar"
            crtable("ALTER TABLE ["&tablename&"] ADD ["&crfield&"] varchar(255)")
        case else
            crtable("ALTER TABLE ["&tablename&"] ADD ["&crfield&"] "&fieldtype&"")
        end select
        Response.Write "<a href=""main.asp?cz=2&tablename="&tablename&""">����</a>"
    case 6
        tablename=trim(request.QueryString("tablename"))
        crtable("DROP TABLE ["&tablename&"]")
        Response.Write "<a href=""main.asp?cz=1"">����</a>"
    case 7
        dim fieldsname,fieldsname2,fieldssize,fieldar
        tablename=trim(request.QueryString("tablename"))
        fieldsname=trim(request.Form("fieldsname"))
        fieldsname2=trim(request.Form("fieldsname2")) 'ԭ����
        fieldtype=trim(request.Form("fieldtype"))
        crtable("sp_rename '"&tablename&"."&fieldsname2&"','"&fieldsname&"','column';") '�ֶ����޸�
        
        fieldssize=trim(request.Form("fieldssize"))
        fieldar=""
        select case fieldtype
        case "varchar","nvarchar"
            fieldar="("&fieldssize&")"
        end select
        if fieldssize=0 then fieldar="" end if
        crtable("ALTER TABLE ["&tablename&"] ALTER COLUMN ["&fieldsname&"] "&fieldtype&""&fieldar&"") '�ֶ����ʹ���
        Response.Write "<a href=""main.asp?cz=2&tablename="&tablename&""">����</a>"
    case 8
        tablename=trim(request.QueryString("tablename"))
        fieldsname=trim(request.QueryString("fieldsname"))
        crtable("Alter table ["&tablename&"] drop column ["&fieldsname&"]")
        Response.Write "<a href=""main.asp?cz=2&tablename="&tablename&""">����</a>"
    case 9
        dim tablename2
        tablename=trim(request.Form("tablename"))
        tablename2=trim(request.QueryString("tablename2"))
        crtable("EXEC sp_rename ["&tablename2&"],["&tablename&"]")
        Response.Write "<a href=""main.asp?cz=1"">����</a>"
    case 10
        Response.Write "��䰸��<br>"
        Response.Write "�������:<font color=""#2663e0"" style=""font-size: 10pt""> insert into ����(�ֶ�1,�ֶ�2)values('����1','����2')</font><br>"
        Response.Write "�������:<font color=""#2663e0"" style=""font-size: 10pt""> update ���� set �ֶ�1='����1',�ֶ�2='����2' where �ֶ�3='����3'</font><br>"
        Response.Write "ɾ�����:<font color=""#2663e0"" style=""font-size: 10pt""> delete from ���� where �ֶ�='����'</font><br>"
        Response.Write "��ѯ���:<font color=""#2663e0"" style=""font-size: 10pt""> select top ��ʾ�ļ�¼��Ŀ �ֶ�1,�ֶ�2 from ���� where �ֶ�1='����1'</font><br>"
        
        tablename=trim(request.QueryString("tablename"))
        if tablename<>"" then
            dim czsql
            czsql=""
            select case request.QueryString("czsql")
            case 1
                czsql="SELECT TOP 10 * FROM ["&tablename&"]"
            case 2
                czsql="INSERT INTO ["&tablename&"] ( ) VALUES ( )"
            case 3
                czsql="UPDATE ["&tablename&"] SET"
            case 4
                czsql="DELETE FROM ["&tablename&"]"
            end select
        end if
        Response.Write "<form action=""main.asp?cz=11"" method=""post"">������һ��SQL���<br><TEXTAREA rows=""5"" cols=""50"" name=""sqlstr"">"&czsql&"</TEXTAREA><br><input type=""submit"" value=""����ִ��""></form>"
        
        Response.Write "ע��:<br>1.��select��ѯ��¼��ʱ�����top���,��Ϊ�����¼�г�ǧ�������Ļ�,�ͻ������ʱ,�򲻿�������,����top�Ϳ�����ֹ��ʾ��������ѯ�Ľ��.<br>2.�ù��ܿ��������еĲ���,�����洢����"
    case 11
        if instr(1,trim(request.Form("sqlstr")),"select",1)>0 then
            On Error Resume Next
            set rs=conn.Execute(trim(request.Form("sqlstr")))
	        If Err Then
		        Response.write ""&Err.Description&"<br>"
            else
                Response.Write "ִ��:&nbsp;"&trim(request.Form("sqlstr"))&"&nbsp;&nbsp;�ɹ�<br>"
                Response.Write "<a href=""javascript:history.back(-1)"">����</a>"
                fieldCount = rs.Fields.Count
                Response.Write "<table border=""0""><tr align=""center"" bgcolor=""#eeeeee"" height=""30"">"
                For i=0 to fieldCount - 1
                    Response.Write "<td>"&rs.Fields(i).Name&"</td>"
                Next
                Response.Write "</tr>"
                while not rs.eof
                    Response.Write "<tr>"
                    For i=0 to fieldCount - 1
                        Response.Write "<td><TEXTAREA rows=""2"" cols=""20"" id=textarea1 name=textarea1>"
                        if ISEMPTY(rs(i)) then
                           'Response.Write rs(i)
                        else
                            Response.Write rs(i)
                        end if
                        Response.Write "</TEXTAREA></td>"
                    Next
                    Response.Write "</tr>"
                   'Response.Write "<tr><td height=""1"" bgcolor=""#555555"" colspan=""5""></td></tr>"
                    rs.movenext
                wend
                rs.close
                set rs=nothing
                Response.Write "</table>"
	        end if
        else
            crtable(trim(request.Form("sqlstr")))
            Response.Write "<a href=""javascript:history.back(-1)"">����</a>"
        end if
    case 12
    %>
      <table border="0"  cellspacing="0" cellpadding="5" height="1" width="90%" class="tableBorder">
      <form action="main.asp?cz=13" method="post">
      <tr><td>�������ݿ�</td></tr>
      <tr>
      <td height=25><b>ע�⣺</b><br>��������ݿⱸ���п��ܳ�ʱ�������ڷ������ٵ�ʱ�����<br><font color="#2663e0" style="font-size: 10pt">����·��Ӧ���������ݿ��������·��</font></td>
      </tr>
      <tr>
      <td>�������ݿ�λ�ü��ļ�����<input type="text" size=35 name="dbpath" value="d:\<%=date()%>.bak">&nbsp;
      <input type="submit" value="��ʼ����" id=submit1 name=submit1></td>
      </tr>
      </table>
    <%
    case 13
        dbpath = trim(Request.Form("dbpath"))
        If dbpath <> "" Then
            dim fso,Files
            Set fso = CreateObject("Scripting.FileSystemObject")
            If fso.FileExists(dbPath) Then
                Response.Write "<br>�������ı������ݿ��Ѿ����ڣ���ɾ������������ļ������б���"
            Else
                Response.Write "<br><br>���ڱ��ݵ�'"&dbPath&"'�����Ժ�...&nbsp;����ʱ�䰴���ݿ��С����<br><br>"
                Response.Flush
                dim srv,bak
                server.ScriptTimeout = 3600
                Set srv=Server.CreateObject("SQLDMO.SQLServer")
                srv.LoginTimeout = 3600
                srv.Connect trim(request.Cookies("ipdress")),trim(request.Cookies("username")), trim(request.Cookies("password"))
                Set bak = Server.CreateObject("SQLDMO.Backup")
                bak.Database=trim(request.Cookies("dataname"))
                bak.Devices=Files
                bak.Files=dbpath
                bak.SQLBackup srv
                if err.number>0 then
                    response.write err.number&"<font color=""red""><br>"
                    response.write err.description&"</font>"
                else
                    Response.Write "������ݿ��Ѿ����ݳɹ�"
                end if
            End If
        End If
    case 14
    %>
      <table border="0"  cellspacing="0" cellpadding="5" height="1" width="90%" class="tableBorder">
      <form action="main.asp?cz=15" method="post">
      <tr><td>��ԭ���ݿ�</td></tr>
      <tr>
      <td height=25><b>ע�⣺</b><br>��������ݿ⻹ԭ�п��ܳ�ʱ�������ڷ������ٵ�ʱ�����<br>��ԭ·��Ӧ���������ݿ��������·��</td>
      </tr>
      <tr>
      <td>��ԭ���ݿ�λ�ü��ļ�����<input type="text" size=35 name="dbpath" value="d:\<%=date()%>.bak">&nbsp;
      <input type="submit" value="��ʼ��ԭ"></td>
      </tr>
      </table>
    <%
    case 15
        closedata
        dbpath = trim(Request.Form("dbpath"))
        If dbpath <> "" Then
            Set fso = CreateObject("Scripting.FileSystemObject")
            If not fso.FileExists(dbPath) Then
                Response.Write "<br>û���ҵ������ݿ�ı����ļ�,������ݿ�·�������������Ƿ�����!(ע��·��Ϊ���ݿ��������·��)"
            Else
                Response.Write "<br><br>���ڴ�"&dbPath&"��ԭ,���Ժ�...&nbsp;��ԭʱ�䰴���ݿ��С����<br><br>"
                Response.Flush
                server.ScriptTimeout = 3600
                Set srv=Server.CreateObject("SQLDMO.SQLServer")
                srv.LoginTimeout = 3600
                srv.Connect trim(request.Cookies("ipdress")),trim(request.Cookies("username")), trim(request.Cookies("password"))
                Set bak = Server.CreateObject("SQLDMO.Restore")
                bak.Action=0
                bak.Database=trim(request.Cookies("dataname"))
                bak.Devices=Files
                bak.Files=dbpath
                bak.ReplaceDatabase=True
                bak.SQLRestore srv
                if err.number>0 then
                    response.write err.number&"<font color=""red""><br>"
                    response.write err.description&"</font>"
                else
                    Response.Write "������ݿ⻹ԭ�ɹ�"
                end if
            End If
        End If
        Response.End
    end select
    Response.Write "</td></tr></table>"
    closedata
end if

'----------------------------------------------------
Function RequestNumSafe(qudata)
    If isNumeric(qudata) then
        if qudata="" then
            RequestNumSafe=0
        else
            RequestNumSafe=qudata
        end if
    else
        RequestNumSafe=0
    end if
End Function

Function RequestCStringSafe(cstring)
    If Instr(1,cstring,"%")>0 or Instr(1,cstring,"=")>0 or Instr(1,cstring,"&")>0 or Instr(1,cstring,"#")>0 or Instr(1,cstring,">")>0 or Instr(1,cstring,"<")>0 or Instr(1,cstring,"'")>0 or Instr(1,cstring,";")>0 or Instr(1,cstring,"��")>0 or Instr(1,cstring,"`")>0 or Instr(1,cstring,"*")>0 or Instr(1,cstring,",")>0 then
        RequestCStringSafe=""
    else
        RequestCStringSafe=cstring
    end if
End Function

Sub LinkData()
    Dim ConnStr
    ConnStr = "Provider = Sqloledb; User ID = " & trim(request.Cookies("username")) & "; Password = " & trim(request.Cookies("password")) & "; Initial Catalog = " & trim(request.Cookies("dataname")) & "; Data Source = " & trim(request.Cookies("ipdress")) & ";"
    On Error Resume Next
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.open ConnStr
    If Err Then
	    err.Clear
	    Set Conn = Nothing
	    Response.Write "���ݿ����ӳ������������ִ���"
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

Function crtable(SqlCommand)
	On Error Resume Next
	Conn.Execute(SqlCommand)
	If Err Then
		Response.write ""&Err.Description&"<br>"
    else
        Response.Write "ִ��:&nbsp;"&SqlCommand&"&nbsp;&nbsp;�ɹ�<br>"
	end if
	Response.Flush
End Function
%>
</body>
</html>