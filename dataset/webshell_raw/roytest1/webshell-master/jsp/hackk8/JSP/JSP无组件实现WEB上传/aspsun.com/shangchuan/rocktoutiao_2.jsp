
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>标题</title>
<style>
table{font-size:10pt;}
</style>
</head>
<body>
<%@page contentType="text/html;charset=gb2312"%>
<%@ page language="java" import="com.jspsmart.upload.*"%>
<%@ page import="java.sql.*"%>
<jsp:useBean id="reg" scope="page" class="mysql_jsp"/>	
<%
String editid="0";
String edittitle="";
String editsubtitle="";
String editoperator="";
String editkeyword="";
String editcontent="";
String editauthor="";
String id=request.getParameter("id");
String edit=request.getParameter("edit");
String delete=request.getParameter("delete");
if(delete!=null){
String sql="delete from sz_shouye where id="+id;
int m=reg.executeupdate(sql);
if (m>0) out.println("OK!");
%>
<a target="right" href="rocktoutiao_2.jsp">返回</a>
<%
}else{ 
if(edit!=null){
String sql2="select id,title,sub_title,keyword,author,source,operator,addition,content from sz_shouye where id="+id;
ResultSet rs=reg.executequery(sql2);
if (rs.next()){
 editid=rs.getString("id");
 edittitle=rs.getString("title");
 editsubtitle=rs.getString("sub_title");
 editkeyword=rs.getString("keyword");
 editcontent=rs.getString("content");
 editoperator=rs.getString("operator");
 editauthor=rs.getString("author");
}
rs.close();
}
	
String sql="select id,title from sz_shouye  ";
ResultSet rs=reg.executequery(sql);
out.println("<table><tr><td>序号</td><td colspan='4'>标题</td></tr>");
int count=0;
while(rs.next()){
 id=rs.getString("id");
 String title=rs.getString("title");
 count+=1;
 %>
 <tr><td><%=count%></td><td><%=title%></td><td><a target="right" href="rocktoutiao_2.jsp?edit=yes&id=<%=id%>">编辑</a></td><td><a target="right" href="rocktoutiao_2.jsp?delete=yes&id=<%=id%>">删除</a></td><td><a href="toutiao_photo.jsp?id=<%=id%>" target="_blank">查看照片</a></td></tr>
 <%
 }
rs.close();
out.println("</table>");%>


  <form method="post" action="rocktoutiao_submit.jsp" target="right" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<%=editid%>">
  <table border="0" cellpadding="0" cellspacing="0" width="471" height="252">
    <tr>
      <td width="194" height="63">标题：</td>
      <td width="277" height="63"><input type="text" name="title" value="<%=edittitle%>" size="46"></td>
    </tr>
<tr>
      <td width="194" height="63">副标题：</td>
      <td width="277" height="63"><input type="text" name="subtitle" value="<%=editsubtitle%>" size="46"></td>
    </tr>
    <tr>
     <td width="194" height="46">小照片名：</td>
      <td width="277" height="46"><input type="file" name="photoname" size="40">(<font color="red">说明：照片名必须为英文！照片大小为200象素×150象素(或者150×200)左右！</font>)</td>
    </tr>
  <tr>
      <td width="194" height="63">大照片名：</td>
      <td width="277" height="63"><input type="file" name="source" size="40">(<font color="red">说明：照片名必须为英文！照片大小为400象素×300象素(或者300×400)左右！</font>)</td>
    </tr>
 <tr>
      <td width="194" height="63">作者：</td>
      <td width="277" height="63"><input type="text" name="author" value="<%=editauthor%>" size="46"></td>
    </tr>
<tr>
      <td width="194" height="63">操作者：</td>
      <td width="277" height="63"><input type="text" name="operator" value="<%=editoperator%>" size="46"></td>
    </tr>
    <tr>
     <td width="194" height="65">关键内容：</td>
      <td width="277" height="65"><textarea name="keyword" rows=5 cols="46"><%=editkeyword%></textarea></td>
    </tr>
    <tr>
      <td width="194" height="78">具体内容：</td>
      <td width="430" height="300"><textarea name="content" rows="20" cols="46"><%=editcontent%></textarea></td>
    </tr>
    <tr><td><input type="submit" name="submit" value="确定"></td><td><input type="reset" value="取消">
  </table>
</form>
<%
}
%>
</body>

</html>

