<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>ר��</title>
<style>
td{font-size:10pt;}
.size10{font-size:10pt}
</style>
</head>

<body>
<table><tr><td>���</td><td>����</td></tr>
<%@ page contentType="text/html;charset=gb2312"%>
<%@ page import="java.sql.*" %>
<%@ page import="java.util.*" %>
<%@ page import="java.lang.*"%>
<jsp:useBean id="reg" class="rock.myoracle_scroll"/>
<%
int pagesize=8;
int rownum;
int pagecount;
int intpage;
String strpage=request.getParameter("intpage");
if (strpage==null)
strpage="1";
intpage=Integer.parseInt(strpage);
String sql="select title from media_table";
ResultSet rs=reg.executequery(sql);
rs.last();
rownum=rs.getRow();
pagecount=(rownum+pagesize-1)/pagesize;
if (intpage>pagecount)
intpage=pagecount;
rs.absolute((intpage-1)*pagesize+1);
int i=0;
do{
String title=rs.getString("title");
i+=1;
%>
<tr><td><%=(intpage-1)*pagesize+i%></td><td><%=title%></td></tr>
<%
}while((rs.next())&&(i<pagesize));
rs.close();
%>
</table>
<div class="size10">��<%=intpage%>ҳ&nbsp��<%=pagecount%>ҳ
<%
if (intpage!=1){
%>
&nbsp<a href="rock.jsp?intpage=<%=intpage-1%>">��һҳ</a>
<%
}
if (intpage!=pagecount){
%>
&nbsp<a href="rock.jsp?intpage=<%=intpage+1%>">��һҳ</a>
<%
}
%>
</div>
</body>

</html>
