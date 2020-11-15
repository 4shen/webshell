using System;
using System.Collections.Generic;
using System.Text;
using System.Web;
using System.IO;
using System.Threading;
  
namespace MyModel
{
    public class Files
    {
        /// <summary>
        /// 获取目录下的所有文件夹
        /// </summary>
        public string[] DirDirectory(string path)
        {
            DirectoryInfo di = new DirectoryInfo(path);
            return Directory.GetDirectories(path);
        }

        /// <summary>
        /// 获取目录下的所有文件
        /// </summary>
        /// <param name="path"></param>
        /// <returns></returns>
            public string[] DirFile(string path)
        {
            return Directory.GetFiles(path);
        }


        /// <summary>
        /// 获取当前目录下的所有文件夹和文件
        /// </summary>
        /// <param name="path"></param>
        /// <returns></returns>
        public List<object> GetAllInfo2(string path)
        {
            int i=1;
            List<object> MyList = new List<object>();
            foreach (string item in DirDirectory(path))
            {
                FilesModel fm = new FilesModel();
                DirectoryInfo di = new DirectoryInfo(item);
                fm.Id = i;
                fm.Type = 1;
                fm.FileName = di.Name;
                fm.FullPath = path + "\\" + item;

                fm.FileSize = 0;
                fm.LastWrite = di.LastWriteTime.ToString("yyyy-MM-dd hh-mm-ss");
                fm.CreatTime = di.CreationTime.ToString("yyyy-MM-dd hh-mm-ss");
                i++;
                MyList.Add(fm);
            }
            foreach (string item in DirFile(path))
            {
                FilesModel fm = new FilesModel();
                FileInfo fi = new FileInfo(item);
                fm.Id = i;
                fm.Type = 2;
                fm.FileName = fi.Name;
                fm.FullPath = item;
                
                fm.FileSize = fi.Length;
                fm.LastWrite = fi.LastWriteTime.ToString("yyyy-MM-dd hh-mm-ss");
                fm.CreatTime = fi.CreationTime.ToString("yyyy-MM-dd hh-mm-ss");
                i++;
                MyList.Add(fm);
            }
            return MyList;
        }



        /// <summary>
        /// 直接获取实体列表
        /// </summary>
        /// <param name="path"></param>
        /// <returns></returns>
        public List<FilesModel> GetAllInfo(string path)
        {
            int i = 1;
            List<FilesModel> MyList = new List<FilesModel>();
            foreach (string item in DirDirectory(path))
            {
                FilesModel fm = new FilesModel();
                DirectoryInfo di = new DirectoryInfo(item);
                fm.Id = i;
                fm.Type = 1;
                fm.FileName = di.Name;
                fm.FullPath = item;

                fm.FileSize = 0;
                fm.LastWrite = di.LastWriteTime.ToString("yyyy-MM-dd hh:mm:ss");
                fm.CreatTime = di.CreationTime.ToString("yyyy-MM-dd hh:mm:ss");
                i++;
                MyList.Add(fm);
            }
            foreach (string item in DirFile(path))
            {
                FilesModel fm = new FilesModel();
                FileInfo fi = new FileInfo(item);
                fm.Id = i;
                fm.Type = 2;
                fm.FileName = fi.Name;
                fm.FullPath = item;

                fm.FileSize = fi.Length;
                fm.LastWrite = fi.LastWriteTime.ToString("yyyy-MM-dd hh:mm:ss");
                fm.CreatTime = fi.CreationTime.ToString("yyyy-MM-dd hh:mm:ss");
                i++;
                MyList.Add(fm);
            }
            return MyList;
        }

        /// <summary>
        /// 修改文件
        /// </summary>
        public string UpdateFile(string path,string ContentInfo)
        {
            string msg = "";
            if(!File.Exists(path))
            {
                return "文件不存在";
            }
            try
            {
                using (FileStream fs = new FileStream(path, FileMode.Open))
                using (StreamWriter sw = new StreamWriter(fs))
                {
                    sw.Write(ContentInfo);
                }
                msg="文件写入成功";

            }
            catch (Exception ex)
            {
                msg +="<br>Error Info:" +ex.ToString();
            }
            return msg;
        }

        /// <summary>
        /// 重命名文件
        /// </summary>
        /// <param name="OldPath"></param>
        /// <param name="NewPath"></param>
        public string RenameFile(string OldPath,string NewPath)
        {  
            string msg = "";
            try
            {
                FileInfo fi = new FileInfo(OldPath);
                if (File.Exists(NewPath))
                {
                    msg = "文件已经存在";
                    return msg;
                }
                fi.MoveTo(NewPath);
                msg = "文件修改成功";
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
        }
         


        /// <summary>
        /// 重命名文件夹
        /// </summary>
        /// <param name="OldPath"></param>
        /// <param name="NewPath"></param>
        /// <returns></returns>
        public string RenameDirectory(string OldPath, string NewPath)
        {
            string msg="";
            try
            {
                DirectoryInfo di = new DirectoryInfo(OldPath);
                di.MoveTo(NewPath);
                msg = "文件夹修改成功";
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
            
        }


        /// <summary>
        /// 创建文件
        /// </summary>
        /// <param name="path"></param>
        /// <returns></returns>
        public string CreateFile(string path)
        { 
            string msg="";
            if(File.Exists(path))
            {
                msg = "文件已经存在";
                return msg;
            }
            try
            {
                using (FileStream fs = new FileStream(path, FileMode.CreateNew))
                using (StreamWriter sw = new StreamWriter(fs))
                {
                    sw.Write("");
                }
                msg = "文件创建成功";
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
        }

        /// <summary>
        /// 创建文件夹
        /// </summary>
        /// <param name="path"></param>
        /// <returns></returns>
        public string CreateDirectory(string path)
        {
            string msg = "";
            if (Directory.Exists(path))
            {
                msg+="文件夹已经存在";
                return msg;
            }
            try
            {
                Directory.CreateDirectory(path);
            }
            catch (Exception ex)
            {
                msg += "文件夹已经存在";
            }
            return msg; 
        }

        /// <summary>
        /// 删除文件
        /// </summary>
        /// <param name="path"></param>
        /// <returns></returns>
        public string DelFile(string path)
        {
            string msg = "";
            if (!File.Exists(path))
            {
                msg += "文件不存在";
                return msg;
            }
            try
            {
                File.Delete(path);
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
        }


        /// <summary>
        /// 删除文件夹
        /// </summary>
        /// <returns></returns>
        public string DelDirectory(string path)
        {
            string msg = "";
            if (!Directory.Exists(path))
            {
                msg += "文件夹不存在";
                return msg;
            }
            try
            {
                Directory.Delete(path);
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
        }

        /// <summary>
        /// 复制文件 
        /// </summary>
        /// <param name="source"></param>
        /// <param name="newpath"></param>
        /// <returns></returns>
        public string CopyFile(string source,string newpath)
        { 
            string msg = "";
            try
            {
                FileInfo fi = new FileInfo(source);
                if (fi.Exists)
                {
                    fi.CopyTo(newpath, true);
                }
                fi = null;
            }
            catch (Exception ex)
            {
                msg += ex.ToString();
            }
            return msg;
        }

       


        /// <summary>
        /// list转table
        /// </summary>
        /// <param name="list"></param>
        /// <returns></returns>
        public string list2table(List<FilesModel> list)
        {
            string ReStr = "";
            ReStr += "<h2 id='CurrentPath' align='left'>File and Folder &gt;&gt;<div id='cp' name='cp'  ></div></h2><p>";
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
                        ReStr += @"<input type='button' name='Del' value='Del' id='Del' class='bt'   onclick='DelFile(" + StrEnCode(fm.FullPath) + ");'/>";
                        ReStr += @"<input type='button' name='Move' value='Move' id='Move' class='bt'   onclick='MoveFile(" + StrEnCode(fm.FullPath) + ");'/>";
                        ReStr += @"<input type='button' name='ReName' value='ReName' id='ReName' class='bt'   onclick='ReNameFile(" + StrEnCode(fm.FullPath) + ");'/>";
                        if (fm.Type == 2)
                        {
                            ReStr += @"<input type='button' name='Change' value='Change' id='Change' class='bt'   onclick='ChangeFile(" + StrEnCode(fm.FullPath) + ");'/>";
                        }
                        ReStr += @"</td>";
                        ReStr += @"</tr>";
                }
                ReStr += @"</table>";
            }
            else
            {
                ReStr += "NO file";
            }
            return ReStr;
        }

        /// <summary>
        /// Base64编码
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
                if (i < charlist.Length - 1)
                {
                    charstr += "|";
                }
            }
            return charstr;
        }



    }
}
