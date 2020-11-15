using System;
using System.Collections.Generic;
using System.Text;
using System.Threading;
using System.IO;
using System.Web;


namespace MyModel
{
    public class SearchFileThread
    {
        Files f = new Files();
        HttpApplicationState ha = null;
        Thread t1 = null;
        List<object> list = new List<object>();
        string LastDirectory="";     //最后一个文件
     

        public SearchFileThread(HttpApplicationState h)
        {
            ha = h;
            if (ha["FileSearchThread"] == null)              //线程执行结果信息
            {
               // list.Add("<br>Threading Result:<br>");
                ha.Add("FileSearchThread", list);
            }


            
        }

     



        /// <summary>
        /// 搜索文件
        /// </summary>
        /// <param name="path"></param>
        /// <param name="str"></param>
        /// <param name="type"></param>
        public void SearchFile(string path,string str,int type)
        {
            string SearchType = "";
            if (type == 1)
            {
                SearchType = "Files";
            }
            if (type == 2)
            {
                SearchType = "Directroy";
            }
            if (type == 3)
            {
                SearchType = "Files And Directroy";
            }
            string ThreadInfo = "<br>Search Path:" + path + "<br>Search Key:" + str + "<br>Search Type:" + SearchType + "<br>Begin Time:" + DateTime.Now.ToString("yyyy-MM-dd hh:mm:ss");          //后台搜索线程的信息
            if (ha["FileSearchThreadInfo"] == null)
            {
                ha.Add("FileSearchThreadInfo", ThreadInfo);
            }
            else
            {
                ha["FileSearchThreadInfo"] = ThreadInfo;
            }


            if (f.DirDirectory(path).Length > 0)
            {
                LastDirectory = f.DirDirectory(path)[f.DirDirectory(path).Length - 1].ToString();
            }
            ThreadStart ts = delegate { OneThreadFunc(path, str, type); };
            t1=new Thread(ts);
            t1.Start();
        }


        /// <summary>
        /// 检查
        /// </summary>
        public void CheckTime()
        { 
        
        
        }





        /// <summary>
        /// 一个线程
        /// </summary>
        /// <param name="path"></param>
        /// <param name="str"></param>
        /// <param name="type"></param>
        public void OneThreadFunc(string path, string str, int type)
        {

            if (GetTheadState())
            {
                ha["FileSearchThread"] = list;
                ha["FileSearchThreadInfo"] += "<br>End Time:" + DateTime.Now.ToString("yyyy-MM-dd hh:mm:ss");
                ha["FileSearchThreadState"] = "0";
                t1.Suspend();
               // Thread.CurrentThread.Abort();
                return;
            }
            
            if (type == 1 || type == 3)
            {
                try
                {
                    foreach (string Fitem in f.DirFile(path))
                    {
                        if (Fitem.Contains(str))
                        {
                            addApp(Fitem);
                        }
                    }
                }
                catch (Exception ex)
                {
                    //throw new ApplicationException(ex.ToString());
                }
            }
            string[] dirs = null;
            try
            {
                dirs = f.DirDirectory(path);
            }
            catch (Exception ex)
            {
               // throw  new ApplicationException(ex.ToString());
            }
            foreach (string Ditem in dirs)
            {
                    if (type == 2 || type == 3)
                    {
                        if (Ditem.Contains(str))
                        {
                            addApp(Ditem);
                        }
                    }

                    Thread.Sleep(10);
                OneThreadFunc(Ditem, str, type); 
            }
            if (path == LastDirectory || LastDirectory=="")
            {
                ha["FileSearchThread"] = list;
                ha["FileSearchThreadState"] = "0";
                ha["FileSearchThreadInfo"] += "<br>End Time:" + DateTime.Now.ToString("yyyy-MM-dd hh:mm:ss");
                t1.Suspend();
                return;
                
            }
        }



        /// <summary>
        /// 获取状态
        /// </summary>
        public bool GetTheadState()
        {
            //if (ha["FileSearchThreadState"] != null)
            //{
            //    string ThreadInfo = ha["FileSearchThreadState"].ToString();          //是否终止线程|已创建线程数|已经结束线程数目
            //    if (ThreadInfo == "1")
            //    {
            //        return false;
            //    }
            //    else
            //    {
            //        return true;
            //    }
            //}
            //return true;
            return false;

        }


        #region 暂时不用
        /// <summary>
        /// 设置已经创建线程总数量
        /// </summary>
        /// <param name="i">1：线程数加一 0：结束线程数加一</param>
        //public void ThreadCountAdd()
        //{
        //    if (ha["FileSearchThreadCount"] != null)
        //    {
        //        ha.Lock();
        //        int CurrentThread = Convert.ToInt32(ha["FileSearchThreadCount"].ToString());          //是否终止线程|已创建线程数|已经结束线程数目
        //        CurrentThread++;
        //        ha["FileSearchThreadState"] = CurrentThread;
        //        ha.UnLock();
        //    }
        //}

        /// <summary>
        /// 设置已经结束线程总数量
        /// </summary>
        //public void ThreadCountDel()
        //{
        //    if (ha["FileSearchThreadEnd"] != null)
        //    {
        //        ha.Lock();
        //        int CurrentThread = Convert.ToInt32(ha["FileSearchThreadEnd"].ToString());          //是否终止线程|已创建线程数|已经结束线程数目
        //        CurrentThread++;
        //        ha["FileSearchThreadEnd"] = CurrentThread;
        //        ha.UnLock();
        //    }
        //}

        /// <summary>
        /// 文件搜索主线程方法
        /// </summary>
        /// <param name="path">路径</param>
        /// <param name="str">匹配字符</param>
        /// <param name="type">查找类型</param>
        public void FindFileFunc(string path, string str, int type)
        {
            foreach (string Ditem in f.DirDirectory(path))
            {
                Thread.Sleep(1000);
                ThreadStart ts = delegate { NewThreadFunc(Ditem, str, type); };
                Thread t = new Thread(ts);
                t.Start();
                if (type == 2 || type == 3)
                {
                    if (Ditem.Contains(str))
                    {
                        addApp(@"<bt>Directory:" + Ditem);
                    }
                }
                if (type == 1 || type == 3)
                {
                    foreach (string Fitem in f.DirFile(path))
                    {
                        if (Fitem.Contains(str))
                        {
                            addApp(@"<bt>File:" + Ditem);
                        }
                    }
                }
            }
            t1.Abort();
        }

        /// <summary>
        /// 文件搜索子线程方法
        /// </summary>
        /// <param name="path">路径</param>
        /// <param name="str">匹配字符</param>
        /// <param name="type">查找类型</param>
        public void NewThreadFunc(string path, string str, int type)
        {
            if (GetTheadState())
            {
                //ThreadCountDel();
                Thread.CurrentThread.Abort();
                return;
            }
            Thread t = null;
            foreach (string Ditem in f.DirDirectory(path))
            {
                Thread.Sleep(10);
                ThreadStart ts = delegate { NewThreadFunc(Ditem, str, type); };
                t = new Thread(ts);
                t.Start();
                //ThreadCountAdd();
                if (type == 2 || type == 3)
                {
                    if (Ditem.Contains(str))
                    {
                        addApp(Ditem);
                    }
                }
                if (type == 1 || type == 3)
                {
                    foreach (string Fitem in f.DirFile(path))
                    {
                        if (Fitem.Contains(str))
                        {
                            addApp(Ditem);
                        }
                    }
                }
            }
            t.Abort();
            //ThreadCountDel();
        }
        #endregion


        /// <summary>
        /// 添加信息
        /// </summary>
        /// <param name="str"></param>
        public void addApp(string str)
        {
            list.Add(str);
        }

    }
}
