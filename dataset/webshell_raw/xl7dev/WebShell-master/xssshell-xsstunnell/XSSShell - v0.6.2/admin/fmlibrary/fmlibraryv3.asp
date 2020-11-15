<%
'// Last Edit 07.08.2003 (v3)
'// 2/20/2004
'// 31/10/2006 - All not required functions are removed from this release
'******************************************************************
'//Ferruh Mavituna ASP Library v3.3
'//http://ferruh.mavituna.com
'//ferruh {at} mavituna.com
'******************************************************************

' Get Country from IP

Function GetCountry(IP)
   GetCountry = "none"

   Dim IPAsArray, IPAsDouble
   IPAsArray = Split(IP, ".")
   IPAsDouble = (IPAsArray(0) * 16777216) + (IPAsArray(1) * 65536) + (IPAsArray(2) * 256) + IPAsArray(3)

   Dim RsCountry
   getRs RsCountry, "Select * From IPCountry Where " & IPAsDouble & " >= IPStart And " & IPAsDouble & " <= IPEnd"

   If Not RsCountry.EOF And Not RsCountry.BOF Then
      GetCountry = LCase(rsCountry("CountryFlag"))
   End If

   fmKill RsCountry
End Function

'******************************************************************
'fm_QNStr() v1.1 by Ferruh Mavituna
'******************************************************************
'//NFO//
'	Get Querystring get it as a numeric value, If it's not numeric it returns 0
'//ARGUMENTS//
'	Qstring = Querystring Name
'//RETURN//
'	Numeric value from Querystring
'//SAMPLES//
'	QueryId = fm_QNStr("id")
Function fm_QNStr(byVal Qstring)
	Qstring= Trim(Request.Querystring(Qstring))
	If NOT IsNumeric(Qstring) Then fm_QNStr = 0 Else fm_QNStr = Qstring
End Function

Function fm_NStr(byVal Qstring)
	If NOT IsNumeric(Qstring) Then fm_NStr = 0 Else fm_NStr = Qstring
End Function


'******************************************************************
'fm_SQL() v1.3 by Ferruh Mavituna
'******************************************************************
'//NFO//
'	Make Safe String for SQL Strings
'//ARGUMENTS//
'	str : String
'//SAMPLE// 
'	SELECT * FROM members WHERE user = '" & fm_SQL(formusername) & "'
Function fm_SQL(byVal Str) 
	fm_SQL=Replace(Str, "'", "''")
End Function


'******************************************************************
'// FM Insert v1.2
'tablename (String) = Database table name 
' values (Array) = Fields, Data, Type like (field,value,text,field, value, number...)

'// HISTORY
'// 05.10.2003
'// Isnumeric() Added
'******************************************************************
Function fm_Insert(byval tablename, byval values)
	Dim ExeNewRs, ExeNewRsFields, ExeNewRsValues, i

	'// Loop
	For i = 0 to Ubound(values) Step 3
		ExeNewRsFields = ExeNewRsFields & Trim(values(i))
		If Ubound(values)-2 <> i Then ExeNewRsFields = ExeNewRsFields & ", "

		'// Values
		Select Case Lcase(Trim(values(i+2)))
			Case "number"
				If NOT isNumeric(values(i+1)) Then values(i+1) = 0
				ExeNewRsValues = ExeNewRsValues & values(i+1)
			Case "", "text"
				ExeNewRsValues = ExeNewRsValues & "'" & fm_SQL(values(i+1)) & "'"
		End Select
		If Ubound(values)-2 <> i Then ExeNewRsValues = ExeNewRsValues & ", "
	Next

	Set ExeNewRs = Server.CreateObject("ADODB.Command")
	ExeNewRs.ActiveConnection = fmconnexe
	ExeNewRs.CommandText = "INSERT INTO " & tablename & "(" & ExeNewRsFields & ") VALUES (" & ExeNewRsValues & ")"
		
'--- DEBUG
'Response.Write "*" & ExeNewRs.CommandText
'Response.End
'--- DEBUG
	
	ExeNewRs.Execute
	ExeNewRs.ActiveConnection.Close

	Set ExeNewRs  = Nothing
End Function

'******************************************************************
'// FM Update
'tablename (String) = Database table name
'identifier (string)= id of table
'uniqueid (number) = id number
'values (Array) = Fields, Data, Type like (field,value,text,field, value, number...)
'******************************************************************
Function fm_Update(byval tablename, byval identifier, byval uniqueid,byval values)
	Dim ExeNewRs, ExeNewRsFields, ExeNewRsValues, i
	If Not IsNumeric(uniqueid) Then uniqueid = 0

	For i = 0 to Ubound(values) Step 3
		ExeNewRsFields = ExeNewRsFields & Trim(values(i))
		If Ubound(values)-2 <> i Then ExeNewRsFields = ExeNewRsFields & ", "

		'// Values
		Select Case Lcase(Trim(values(i+2)))
			Case "number"
				If Not IsNumeric(values(i+1)) Then values(i+1) = 0
				ExeNewRsValues = ExeNewRsValues & values(i) & " = " & values(i+1)

			Case "datenow"
				ExeNewRsValues =ExeNewRsValues & values(i) & " = Now()"

			Case "", "text"
				ExeNewRsValues =ExeNewRsValues & values(i) & " = '" & fm_SQL(values(i+1)) & "'"

		End Select
		If Ubound(values)-2 <> i Then ExeNewRsValues = ExeNewRsValues & ", "
	Next

	Set ExeNewRs = Server.CreateObject("ADODB.Command")
	ExeNewRs.ActiveConnection = fmconnexe
	ExeNewRs.CommandText = "UPDATE " & tablename & " SET " & ExeNewRsValues & " WHERE " & identifier & " = " & uniqueid

'//DEBUG
'	Response.Write ExeNewRs.CommandText : Response.End

	ExeNewRs.CommandType = 1
	ExeNewRs.CommandTimeout = 0
	ExeNewRs.Prepared = true
	ExeNewRs.Execute()
	ExeNewRs.ActiveConnection.Close

	Set ExeNewRs  = Nothing
End Function

'***fm_Rnd() v1.3 by Ferruh Mavituna
Function fm_RndNumeric(byVal seed)
	Randomize Timer
	If NOT isNumeric(seed) Then seed = 666
	fm_RndNumeric = Replace(Replace(Cstr(Time),":","")," ","") & "" &  CLng((Rnd*seed))
End Function


'*******************************************************
'// HTML Encode
'*******************************************************
Function fm_Encode(byVal Str)
	If Str="" OR isNull(Str) Then Exit Function
	fm_Encode=Server.HTMLEncode(Str)
End Function

'// Remote IP
Function RemoteIP()
	RemoteIP = Request.ServerVariables("REMOTE_HOST")
End Function

Function fmKill(Obj) '// Close RS
	If isObject(Obj) Then
		Obj.Close
		Set Obj=Nothing
	End If
End Function

Function rsEmpty(obj)
	rsEmpty=False
	If obj.EOF AND obj.BOF Then rsEmpty=True
End Function

Function getRs(byRef Obj, byVal SQL)
'	Response.Write SQL  : Response.End
	
	Set Obj=Server.CreateObject("ADODB.Recordset")
	Obj.Open SQL, fmconn, 2, 1
End Function

Function fm_RndNumeric2()
	Randomize
	fm_RndNumeric2 = CLng((Rnd*666139))+Session.SessionID
End Function

%>