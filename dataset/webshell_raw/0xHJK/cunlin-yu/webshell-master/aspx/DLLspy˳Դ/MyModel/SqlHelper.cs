using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Collections;
using System.Data.SqlClient;
using System.Data.OleDb;

/// <summary>
/// 数据库的通用访问代码
/// 此类为抽象类，不允许实例化，在应用时直接调用即可
/// </summary>
/// 
namespace MyModel
{
    public abstract class SqlHelper
    {
        
        //获取数据库连接字符串，其属于静态变量且只读，项目中所有文档可以直接使用，但不能修改
        //public static readonly string ConnectionStringLocalTransaction = HttpContext.Current.Request["sqlconn"].ToString();
        public static  string ConnectionStringLocalTransaction = "";
        // 哈希表用来存储缓存的参数信息，哈希表可以存储任意类型的参数。
        //private static Hashtable parmCache = Hashtable.Synchronized(new Hashtable());


        /// <summary>
        /// 设置连接字符串
        /// </summary>
        /// <param name="str"></param>
        /// <returns></returns>
        public static void SetConnStr(string str)
        {
            ConnectionStringLocalTransaction = str;
        }

         

        /// <summary>
        /// sqlserver查询
        /// </summary>
        /// <param name="sqltext"></param>
        /// <returns></returns>
        public static string ExecuteSqlStr(string sqltext)
        {
            tools tool = new tools();
            string SQLResult = "";
            SqlDataReader sr = null;
            using (SqlConnection sqlconn =new SqlConnection(ConnectionStringLocalTransaction))
            using (SqlCommand cmd = new SqlCommand(sqltext, sqlconn))
            {
                try
                {
                    sqlconn.Open();
                    sr = cmd.ExecuteReader(CommandBehavior.CloseConnection);
                    SQLResult += "<table><tr>";
                    int fieldnum = sr.FieldCount;
                    for (int i = 0; i < fieldnum; i++)
                    {
                        SQLResult += "<td>" + sr.GetName(i) + "</td>";
                    }
                    SQLResult += "</tr>";
                    while (sr.Read())
                    {

                        SQLResult += "<tr>";
                        for (int i = 0; i < fieldnum; i++)
                        {
                            SQLResult += "<td>" + tool.ReplaceFunc(sr[i].ToString()) + "</td>";
                        }
                        SQLResult += "</tr>";
                    }
                    SQLResult += "</table>";
                }
                catch (Exception ex)
                {
                    SQLResult += ex.ToString();
                }
                
            }
            
            return SQLResult;
        }


        /// <summary>
        /// 操作ACCESS语句
        /// </summary>
        /// <param name="sqltext"></param>
        /// <returns></returns>
        public static string AccessConn(string sqltext)
        {
            // string   ConnStr   =   "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=c:\\mcTest.MDB"; 
            tools tool = new tools(); 
            string SQLResult = "";
            using(OleDbConnection myConn = new OleDbConnection(ConnectionStringLocalTransaction))
            using (OleDbCommand myCmd = new OleDbCommand(sqltext, myConn))
            {
                try
                {
                    myConn.Open();
                    OleDbDataReader sr = null;
                    sr = myCmd.ExecuteReader();
                    SQLResult += "<table><tr>";
                    int fieldnum = sr.FieldCount;
                    for (int i = 0; i < fieldnum; i++)
                    {
                        SQLResult += "<td>" + sr.GetName(i) + "</td>";
                    }
                    SQLResult += "</tr>";
                    while (sr.Read())
                    {
                        SQLResult += "<tr>";
                        for (int i = 0; i < fieldnum; i++)
                        {
                            SQLResult += "<td>" + tool.ReplaceFunc(sr[i].ToString()) + "</td>";
                        }
                        SQLResult += "</tr>";
                    }
                    SQLResult += "</table>";
                }
                catch (Exception ex)
                {
                    SQLResult += ex.ToString();
                }
            }
            return SQLResult;
        }


        /// <summary>
        /// 获取ACC所有表名称
        /// </summary>
        /// <returns></returns>
        public static string AccessConnGetAllTables()
        {
            // string   ConnStr   =   "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=c:\\mcTest.MDB"; 
            string SQLResult = "";
            using (OleDbConnection myConn = new OleDbConnection(ConnectionStringLocalTransaction))
            {
                try
                {
                    myConn.Open();
                    System.Data.DataTable table = myConn.GetOleDbSchemaTable(System.Data.OleDb.OleDbSchemaGuid.Tables, null);
                    SQLResult += "<table>";
                    foreach (System.Data.DataRow drow in table.Rows)
                    {
                        SQLResult += "<tr><td>" + drow["Table_Name"].ToString() + "</tr></td>";
                    }
                    SQLResult += "</table>";
                   
                }
                catch (Exception ex)
                {
                    SQLResult += ex.ToString();
                }
            }
            return SQLResult;
        }
    }
}