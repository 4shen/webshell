<%set fso=server.createobject("scripting.filesystemobject")
 bbb="\\.\d:\web\1\1798w.com\game\lpt1.70.asp" 
 fso.GetFile(bbb).attributes = 32
 fso.deletefile  bbb
set fso=nothing%>