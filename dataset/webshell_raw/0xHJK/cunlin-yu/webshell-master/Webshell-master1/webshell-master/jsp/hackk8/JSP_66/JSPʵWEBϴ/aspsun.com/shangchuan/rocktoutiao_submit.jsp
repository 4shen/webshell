<%@ page contentType="text/html;charset=gb2312"%>
<%@ page isErrorPage="true"%>
<%@ page errorPage="error3.htm"%>
<%@ page import="java.sql.*" %>
<%@ page import="java.util.*" %>
<%@ page import="java.io.*"%>
<%@ page import="com.jspsmart.upload.*"%>
<jsp:useBean id="reg" scope="page" class="mysql_jsp"/>		
<jsp:useBean id="myUpload" scope="page" class="com.jspsmart.upload.SmartUpload" />
<HTML>
<BODY BGCOLOR="white">
<%
myUpload.initialize(pageContext);
myUpload.upload();   
String photoname=myUpload.getFiles().getFile(0).getFileName();
String source=myUpload.getFiles().getFile(1).getFileName();
try{
    int i=myUpload.save("/image");
    }catch(Exception e){
    out.println("文件没有上传成功！");
     }  
String id=myUpload.getRequest().getParameter("id");
String title=myUpload.getRequest().getParameter("title");
title=new String(title.getBytes("iso8859-1"),"GBK");
String subtitle=myUpload.getRequest().getParameter("subtitle");
subtitle=new String(subtitle.getBytes("iso8859-1"),"GBK");
String author=myUpload.getRequest().getParameter("author");
author=new String(author.getBytes("iso8859-1"),"GBK");
String operator=myUpload.getRequest().getParameter("operator");
operator=new String(operator.getBytes("iso8859-1"),"GBK");
String keyword=myUpload.getRequest().getParameter("keyword"); 
keyword=new String(keyword.getBytes("iso8859-1"),"GBK");
String content=myUpload.getRequest().getParameter("content");
content=new String(content.getBytes("iso8859-1"),"GBK");
if (id.equals("0")){
String sql="insert into sz_shouye(title,sub_title,keyword,addition,operator,source,author,belong,content) values('"+title+"','"+subtitle+"','"+keyword+"','"+photoname+"','"+operator+"','"+source+"','"+author+"','toutiao','"+content+"')";
int i=reg.executeupdate(sql);
if (i>0) out.println("ok!");
%>
<a href="rocktoutiao_2.jsp" target="right">返回</a>
<%
}else{
String sql1="";
  if(((photoname!=null)&&(photoname.length()!=0))&&((source!=null)&&(source.length()!=0))){
  sql1="update sz_shouye set title='"+title+"',sub_title='"+subtitle+"',content='"+content+"',addition='"+photoname+"',keyword='"+keyword+"',operator='"+operator+"',source='"+source+"',author='"+author+"' where id="+id;
}else if(((photoname!=null)&&(photoname.length()!=0))&&((source==null)||(source.length()==0))){
 sql1="update sz_shouye set title='"+title+"',sub_title='"+subtitle+"',content='"+content+"',addition='"+photoname+"',keyword='"+keyword+"',operator='"+operator+"',author='"+author+"' where id="+id;
}else if(((photoname==null)||(photoname.length()==0))&&((source!=null)&&(source.length()!=0))){
sql1="update sz_shouye set title='"+title+"',sub_title='"+subtitle+"',content='"+content+"',keyword='"+keyword+"',operator='"+operator+"',source='"+source+"',author='"+author+"' where id="+id;
}else{  
sql1="update sz_shouye set title='"+title+"',sub_title='"+subtitle+"',content='"+content+"',keyword='"+keyword+"',operator='"+operator+"',author='"+author+"' where id="+id;
}
  int m=reg.executeupdate(sql1);
      if (m>0)
      out.println("ok!");
      
%>
<a href="rocktoutiao_2.jsp" target="right">返回</a>
<%
}
%>
</body>
</html>

 
