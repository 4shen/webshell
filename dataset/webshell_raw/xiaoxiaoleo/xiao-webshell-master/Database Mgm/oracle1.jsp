<%@ page language="java" import="java.util.*"  pageEncoding="GBK"%>
<%@ page import="oracle.jdbc.*"%>
<%@ page import="java.sql.*" %>
<%
String path = request.getContextPath();
String basePath = request.getScheme()+"://"+request.getServerName()+":"+request.getServerPort()+path+"/";
%>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <base href="<%=basePath%>">
    
    <title>JSP ORACLE</title>
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="cache-control" content="no-cache">
        <meta http-equiv="expires" content="0">    
        <meta http-equiv="keywords" content="keyword1,keyword2,keyword3">
        <meta http-equiv="description" content="This is my page">
        <!--
        <link rel="stylesheet" type="text/css" href="styles.css" mce_href="styles.css">
        -->
  </head>
  
  <body> 
    <%
        String  url  =  "http://"  +  request.getServerName()  +  ":"  +  request.getServerPort()  +  request.getContextPath()+request.getServletPath();
        Class.forName("oracle.jdbc.driver.OracleDriver").newInstance();
        ResultSet rs=null;
        ResultSet rs_column=null;
        ResultSet rs_dump=null;
            String oraUrl="jdbc:oracle:thin:@x.x.x.x:1521:orcl";
            String oraUser="";
            String oraPWD="";
            try
            {
                    DriverManager.registerDriver(new oracle.jdbc.driver.OracleDriver());
            }        catch (SQLException e)
            {
                out.print("filed!!");
            }
            try
            {
                    Connection conn=DriverManager.getConnection(oraUrl,oraUser,oraPWD);
                conn.setAutoCommit(false);        
                if (request.getParameter("table") == null || request.getParameter("table").equals(""))
                {
                        out.print("ALL TABLES:<br>");
                        Statement stmt=conn.createStatement(ResultSet.TYPE_SCROLL_SENSITIVE,ResultSet.CONCUR_UPDATABLE);
                            rs=stmt.executeQuery("select table_name from all_tables");
                            while(rs.next())
                        {
                                out.print("<a href=");out.print(url);out.print("?table=");out.print(rs.getString(1));
                                out.print(" target=_blank>");out.print(rs.getString(1));out.print("</a><br>");
                        }
                            rs.close();
                            stmt.close();
                }
                else 
                {
				        String sql_count="select count(*) from all_tab_columns where Table_Name='"+request.getParameter("table")+"'";
						String sql_column="select * from all_tab_columns where Table_Name='"+request.getParameter("table")+"'";						
						String sql_dump="select * from "+request.getParameter("table")+" where rownum <= "+request.getParameter("max")+" minus select * from "+request.getParameter("table")+" where rownum < "+request.getParameter("min");
						Statement stmt_count=conn.createStatement(ResultSet.TYPE_SCROLL_SENSITIVE,ResultSet.CONCUR_UPDATABLE);
                        Statement stmt_column=conn.createStatement(ResultSet.TYPE_SCROLL_SENSITIVE,ResultSet.CONCUR_UPDATABLE);
                        Statement stmt_dump=conn.createStatement(ResultSet.TYPE_SCROLL_SENSITIVE,ResultSet.CONCUR_UPDATABLE);
                        rs=stmt_count.executeQuery(sql_count);
                        rs_column=stmt_column.executeQuery(sql_column); 
                        rs_dump= stmt_dump.executeQuery(sql_dump);
                        conn.commit();
                        int count=0;
                            while(rs.next())
                            {
                                count=Integer.parseInt(rs.getString(1));
                                    //out.print(count);
                            }
                        int n=1;
                        out.print("<table border='1'>");out.print("<tr>");
                        while(rs_column.next())
                        {
                                out.print("<td>");out.print(rs_column.getString(3));out.print("</td>");
                                n+=1;
                                
                        }
                        out.print("<tr>");
                            while(rs_dump.next())
                            {
                                
                                out.print("<tr>");
                                n=1;
                                while(n<=count)
                                {
                                        out.print("<td>");
                                        out.print(rs_dump.getString(n));
                                        out.print("</td>");
                                        n+=1;
                                }
                                out.print("</tr>");
                            }
                        rs_dump.close();
                        rs_column.close();
                        rs.close();
                            stmt_count.close();
                            stmt_column.close();
                            stmt_dump.close();
                }
                    conn.close();
            } catch (SQLException e)
            {
                    System.out.println(e.toString());
                    out.print(e.toString());
            }
     %>
  </body>
</html>
