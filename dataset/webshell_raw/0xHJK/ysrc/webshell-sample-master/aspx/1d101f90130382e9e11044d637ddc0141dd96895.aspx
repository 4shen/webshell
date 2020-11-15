<%@ Page Language="C#" Debug="false" %>
<%@ import Namespace="System.Data"%>
<%@ import Namespace="System.Data.OleDb"%>
<%@ import Namespace="System.Data.Common"%>
<%@ Import Namespace="System.Data.SqlClient"%>
<script runat="server">
protected void Page_Load(object sender,EventArgs e)
{
    Response.Buffer = true;
        Server.ScriptTimeout = 2147483647;
    
    string sConnStr = "Driver={Sql Server};Server=192.168.1.5;Uid=mssql�û���;Pwd=mssql����;Database=����"; //�����������ַ���
    string sSQL = "SELECT * FROM [����].dbo.[����]";  //�����ǵ��ĸ����ĸ������䣬����д������䣬�ð�ǷֺŸ���
    
    DataSet ds = Query(sSQL, sConnStr);
    
    if(ds.Tables.Count < 1)
    {
        Response.Write("���ؽ��Ϊ�ա�");
    }
    else
    {
        for (int i = 0; i < ds.Tables.Count; i++ )
        {
            DataTable dt = ds.Tables[i];
            
            //�������
            Response.Write(dt.TableName + "\r\n");
            //����ֶ���
            for(int j = 0; j < dt.Columns.Count; j++)
            {
                Response.Write(dt.Columns[j].ColumnName + "\t");
            }
            Response.Write("\r\n");
            
            //���������
            for (int j = 0; j < dt.Rows.Count; j++)
            {
                if (j % 100 == 0) Response.Flush();

                for (int k = 0; k < dt.Columns.Count; k++)
                {
                    Response.Write(dt.Rows[j][k] + "\t");
                }
                Response.Write("\r\n");
            }
        }
    }
}

/// <summary>
/// ִ�в�ѯ��䣬����DataSet
/// </summary>
/// <param name="SQL">��ѯ���</param>
/// <param name="ConnStr">�����ַ���</param>
/// <returns>DataSet</returns>
public static DataSet Query(string SQL, string ConnStr)
{
    using (SqlConnection connection = new SqlConnection(ConnStr))
    {
        DataSet ds = new DataSet();
        try
        {
            connection.Open();
            SqlDataAdapter command = new SqlDataAdapter(SQL, connection);
            command.Fill(ds, "ds");
        }
        catch (System.Data.SqlClient.SqlException ex)
        {
            throw new Exception(ex.Message);
        }
        return ds;
    }
}
</script>