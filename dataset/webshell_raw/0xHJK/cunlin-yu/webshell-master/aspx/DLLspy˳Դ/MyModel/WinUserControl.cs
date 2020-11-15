using System;
using System.Collections.Generic;
using System.Text;
using System.DirectoryServices;
using System.IO;
using System.Security.AccessControl;
using System.Collections;

namespace MyModel
{
    public class WinUserControl
    {

        /// <summary>
        /// 目录权限
        /// </summary>
        public enum FloderRights
        {
            FullControl,
            Read,
            Write
        }

        /// <summary>
        /// 创建Windows帐户
        /// </summary>
        /// <param name="pathname"></param>
        /// <returns></returns>
        public   string CreateLocalUser(string username, string password, string groupname)
        {
            string str = "";
            try
            {
                DirectoryEntry localMachine = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer");
                var newUser = localMachine.Children.Add(username, "user");
                newUser.Invoke("SetPassword", new object[] { password });
                newUser.Invoke("Put", new object[] { "Description", groupname });
                newUser.CommitChanges();
                DirectoryEntry admins = localMachine.Children.Find(groupname, "Group"); //加入的组
                admins.Invoke("Add", new Object[] { newUser.Path.ToString() });
                admins.CommitChanges();
                str += "Add User:" + username + " Commit!";
                localMachine.Close();
                newUser.Close();
                admins.Close();
            }
            catch (Exception ex)
            {
                str += ex.ToString();
            }
            return str;
        }


        ///// <summary>
        ///// 获取所有用户信息
        ///// </summary>
        ///// <returns></returns>
        //public string GetAllLocalUser()
        //{
        //    string str = "";
        //    try
        //    {
        //        DirectoryEntry localMachine = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer");
        //        object members = localMachine.Invoke("Members", null);
        //        foreach (object member in (IEnumerable)members)
        //        {
        //            DirectoryEntry userInGroup = new DirectoryEntry(member);
        //            str+=userInGroup.Username+"|"+userInGroup.SchemaClassName+"<br>";
        //        }

        //    }
        //    catch (Exception ex)
        //    {
        //        str += ex.ToString();
        //    }
        //    return str;
        //}


        public DirectoryEntry GetOne(string username)
        {
            DirectoryEntry delUser=new DirectoryEntry();
            using (DirectoryEntry dirEntry = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer"))
            {
                //删除存在用户
                delUser = dirEntry.Children.Find(username, "user");
            }
            return delUser;
        }

        /// <summary>
        /// 获取账户信息
        /// </summary>
        /// <param name="username"></param>
        /// <returns></returns>
        public string GetUser(string username)
        {
            DirectoryEntry objDirEnt = GetOne(username);
            StringBuilder sbUserInfo = new StringBuilder();
            try
            {
                sbUserInfo.Append("Name = " + objDirEnt.Name + "<br>");
                sbUserInfo.Append("Path = " + objDirEnt.Path + "<br>" + "<br>");
                sbUserInfo.Append("SchemaClassName = " + objDirEnt.SchemaClassName + "<br>");
                sbUserInfo.Append("-------------------" + "<br>");
                sbUserInfo.Append("Properties:" + "<br>");
                foreach (String Key in objDirEnt.Properties.PropertyNames)
                {
                    sbUserInfo.AppendFormat("\t{0} = ", Key);
                    sbUserInfo.Append("");
                    foreach (Object objValue in objDirEnt.Properties[Key])
                    {
                        sbUserInfo.AppendFormat("\t\t{0}" + "<br>", objValue);
                    }
                }
            }
            catch (Exception ex)
            {
                sbUserInfo.Append(ex.ToString());
            }
            if (sbUserInfo == null || sbUserInfo.ToString() == "")
            {
                sbUserInfo.Append("No Shell or No User");
            }
            return sbUserInfo.ToString();
        }

        /// <summary>
        /// 转换
        /// </summary>
        /// <param name="objDirEnt"></param>
        /// <returns></returns>
        public string DirectoryEntry2str(DirectoryEntry objDirEnt)
        {
            StringBuilder sbUserInfo = new StringBuilder();
            sbUserInfo.Append("Name = " + objDirEnt.Name + "<br>");
            sbUserInfo.Append("Path = " + objDirEnt.Path + "<br>");
            sbUserInfo.Append("-------------------" + "<br>");
            sbUserInfo.Append("Properties:" + "<br>");
            foreach (String Key in objDirEnt.Properties.PropertyNames)
            {
                sbUserInfo.AppendFormat("\t{0} = ", Key);
                
                foreach (Object objValue in objDirEnt.Properties[Key])
                {
                    sbUserInfo.AppendFormat("\t\t{0}" + "<br>", objValue);
                }
            }
            sbUserInfo.Append("<hr  size='2' /><p>");
            return sbUserInfo.ToString();
        }

        /// <summary>
        /// 获取所有用户信息
        /// </summary>
        /// <returns></returns>
        public string GetAllUser()
        {
            string alluser = "";
            using (DirectoryEntry localMachine = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer"))
            {
                foreach (DirectoryEntry  item in localMachine.Children)
                {
                    if(item.SchemaClassName=="User")
                    {
                        alluser += DirectoryEntry2str(item).ToString();
                    }
                    
                }
            
            }
            return alluser;
        }

        /// <summary>
        /// 更改Windows帐户密码
        /// </summary>
        /// <param name="username"></param>
        /// <param name="oldPwd"></param>
        /// <param name="newPwd"></param>
        public  string ChangeWinUserPasswd(string username, string oldPwd, string newPwd)
        {
            string msg = "";
            try
            {
                DirectoryEntry dirEntry = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer");
                DirectoryEntry userEntry = dirEntry.Children.Find(username, "user");
                object[] password = new object[] { newPwd, oldPwd };
                object ret = userEntry.Invoke("ChangePassword", password);
                userEntry.CommitChanges();
                msg += "ChangePassword Commit!";
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
        }

        /// <summary>
        /// 给目录添加用户和权限
        /// </summary>
        /// <param name="pathname"></param>
        /// <param name="username"></param>
        /// <param name="qx"></param>
        public  void AddPathRights(string pathname, string username, FloderRights qx)
        {
            DirectoryInfo dirinfo = new DirectoryInfo(pathname);
            if ((dirinfo.Attributes & FileAttributes.ReadOnly) != 0)
            {
                dirinfo.Attributes = FileAttributes.Normal;
            }
            //取得访问控制列表
            DirectorySecurity dirsecurity = dirinfo.GetAccessControl();
            // string strDomain = Dns.GetHostName();
            switch (qx)
            {
                case FloderRights.FullControl:
                    dirsecurity.AddAccessRule(new FileSystemAccessRule(username, FileSystemRights.FullControl, AccessControlType.Allow));
                    break;
                case FloderRights.Read:
                    dirsecurity.AddAccessRule(new FileSystemAccessRule(username, FileSystemRights.Read, AccessControlType.Allow));
                    break;
                case FloderRights.Write:
                    dirsecurity.AddAccessRule(new FileSystemAccessRule(username, FileSystemRights.Write, AccessControlType.Allow));
                    break;
                default:
                    dirsecurity.AddAccessRule(new FileSystemAccessRule(username, FileSystemRights.FullControl, AccessControlType.Deny));
                    break;
            }

            dirinfo.SetAccessControl(dirsecurity);

            //取消目录从父继承
            DirectorySecurity dirSecurity = System.IO.Directory.GetAccessControl(pathname);
            dirSecurity.SetAccessRuleProtection(true, false);
            System.IO.Directory.SetAccessControl(pathname, dirSecurity);

            //AccessControlType.Allow允许访问受保护对象//Deny拒绝访问受保护对象
            //FullControl、Read 和 Write 完全控制,读,写
            //FileSystemRights.Write写入//Delete删除 //DeleteSubdirectoriesAndFiles删除文件夹和文件//ListDirectory读取
            //Modify读写删除-修改//只读打开文件和复制//
        }

        /// <summary>
        /// 判断Windows用户是否存在
        /// </summary>
        /// <param name="username"></param>
        /// <returns></returns>
        public  bool ExistWinUser(string username)
        {
            try
            {
                using (DirectoryEntry dirEntry = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer"))
                {
                    //删除存在用户
                    var delUser = dirEntry.Children.Find(username, "user");
                    return delUser != null;
                }
            }
            catch
            {
                return false;
            }
        }

        /// <summary>
        /// 删除Windows用户
        /// </summary>
        /// <param name="username"></param>
        /// <returns></returns>
        public  bool DeleteWinUser(string username)
        {
            try
            {
                using (DirectoryEntry dirEntry = new DirectoryEntry("WinNT://" + Environment.MachineName + ",computer"))
                {
                    //删除存在用户
                    var delUser = dirEntry.Children.Find(username, "user");
                    if (delUser != null)
                    {
                        dirEntry.Children.Remove(delUser);
                    }
                }
                return true;
            }
            catch
            {
                return false;
            }
        }
    }
}
