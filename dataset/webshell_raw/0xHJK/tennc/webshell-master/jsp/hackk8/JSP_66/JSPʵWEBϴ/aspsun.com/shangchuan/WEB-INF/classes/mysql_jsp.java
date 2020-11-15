/*
 * Author: ashui
 * Created: 02/21/2002 10:42:11
 * Modified: 02/21/2002 10:42:11
 */

import java.sql.*;
import java.util.*;
public class mysql_jsp
{
	String sdbdriver="org.gjt.mm.mysql.Driver";
	String sconnstr="jdbc:mysql://localhost/xcb";
	Connection con=null;
	ResultSet rs=null;
	int afint;
	public mysql_jsp(){
		try{
			Class.forName(sdbdriver).newInstance();
		}catch(Exception e){
			System.out.println("can't find the driver!");
		}
	}
	public ResultSet executequery(String sql){
		try{
			con=DriverManager.getConnection(sconnstr,"","");
			Statement stmt=con.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE,ResultSet.CONCUR_READ_ONLY);
			rs=stmt.executeQuery(sql);
		}catch(SQLException e){
			System.out.println("can't executeQuery");
		}
		return rs;
	}
	public int executeupdate(String sql) throws SQLException{
		con=DriverManager.getConnection(sconnstr,"","");
		Statement stmt=con.createStatement();
		afint=stmt.executeUpdate(sql);
		return afint;
	}

}

