using System;
using System.Collections.Generic;
using System.Text;
using System.Threading;
using System.IO;
using System.Web;


namespace MyModel
{
    public class MyThread
    {
        Files f = new Files();
        HttpApplicationState ha = null;
        Thread t1 = null;
        public MyThread(HttpApplicationState h)
        {
            ha = h;
            if (ha["FileSearchThread"] == null)              //线程执行结果信息
            {
                List<object> list = new List<object>();
                list.Add("Threading.........<br>");
                ha.Add("FileSearchThread", list);
            }
            if (ha["FileSearchThreadCount"] == null)
            {
                int ThreadCount = 0;          //已创建线程数
                ha.Add("FileSearchThreadCount", ThreadCount);
            }
            if (ha["FileSearchThreadEnd"] == null)
            {
                int ThreadEndCount = 0;          //已经结束线程数目
                ha.Add("FileSearchThreadEnd", ThreadEndCount);
            }

            if (ha["FileSearchThreadState"] == null)
            {
                string ThreadInfo = "1";          //是否终止线程(1：不终止 0：终止)|已创建线程数|已经结束线程数目
                ha.Add("FileSearchThreadState", ThreadInfo);
            }
        }
        public void SearchFile(string path,string str,int type)
        {
            ThreadStart ts = delegate { OneThreadFunc(path, str, type); };
            t1=new Thread(ts);
            t1.Start();
        }

        ///// <summary>
        ///// 文件搜索主线程方法
        ///// </summary>
        ///// <param name="path">路径</param>
        ///// <param name="str">匹配字符</param>
        ///// <param name="type">查找类型</param>
        //public void FindFileFunc(string path, string str, int type)
        //{
        //    foreach (string Ditem in f.DirDirectory(path))
        //    {
        //        Thread.Sleep(1000);
        //        ThreadStart ts = delegate { NewThreadFunc(Ditem, str, type); };
        //        Thread t = new Thread(ts);
        //        t.Start();
        //        if(type==2||type==3)
        //        {
        //            if (Ditem.Contains(str))
        //            {
        //                addApp(@"<bt>Directory:" + Ditem);
        //            }
        //        }
        //        if (type == 1 || type == 3)
        //        {
        //            foreach (string Fitem in f.DirFile(path))
        //            {
        //                if (Fitem.Contains(str))
        //                {
        //                    addApp(@"<bt>File:" +Ditem);
        //                }
        //            }
        //        }
        //    }
        //    t1.Abort();
        //}


        ///// <summary>
        ///// 文件搜索子线程方法
        ///// </summary>
        ///// <param name="path">路径</param>
        ///// <param name="str">匹配字符</param>
        ///// <param name="type">查找类型</param>
        //public void NewThreadFunc(string path, string str, int type)
        //{
        //    if (GetTheadState())
        //    {
        //        ThreadCountDel();
        //        Thread.CurrentThread.Abort();
        //        return;
        //    }
        //    Thread t = null;
        //    foreach (string Ditem in f.DirDirectory(path))
        //    {
        //        Thread.Sleep(10);
        //        ThreadStart ts = delegate { NewThreadFunc(Ditem, str, type); };
        //        t = new Thread(ts);
        //        t.Start();
        //        ThreadCountAdd();
        //        if (type == 2 || type == 3)
        //        {
        //            if (Ditem.Contains(str))
        //            {
        //                addApp(Ditem);
        //            }
        //        }
        //        if (type == 1 || type == 3)
        //        {
        //            foreach (string Fitem in f.DirFile(path))
        //            {
        //                if (Fitem.Contains(str))
        //                {
        //                    addApp(Ditem);
        //                }
        //            }
        //        }
        //    }
        //    t.Abort();
        //    ThreadCountDel();
        //}



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
               // ThreadCountDel();
                Thread.CurrentThread.Abort();
                return;
            }
           // Thread t = null;
            foreach (string Ditem in f.DirDirectory(path))
            {
                Thread.Sleep(10);
               // ThreadStart ts = delegate { NewThreadFunc(Ditem, str, type); };
              //  t = new Thread(ts);
             //   t.Start();
               // ThreadCountAdd();
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
                OneThreadFunc(Ditem, str, type);
            }
           // t.Abort();
          //  ThreadCountDel();
        }



        /// <summary>
        /// 获取状态
        /// </summary>
        public bool GetTheadState()
        {
            if (ha["FileSearchThreadState"] != null)
            {
                string ThreadInfo = ha["FileSearchThreadState"].ToString();          //是否终止线程|已创建线程数|已经结束线程数目
                if (ThreadInfo == "1")
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
            return true;

        }


        /// <summary>
        /// 设置已经创建线程总数量
        /// </summary>
        /// <param name="i">1：线程数加一 0：结束线程数加一</param>
        public void ThreadCountAdd()
        {
            if (ha["FileSearchThreadCount"] != null)
            {
                ha.Lock();
                int CurrentThread = Convert.ToInt32(ha["FileSearchThreadCount"].ToString());          //是否终止线程|已创建线程数|已经结束线程数目
                CurrentThread++;
                ha["FileSearchThreadState"] = CurrentThread;
                ha.UnLock();
            }
        }

        /// <summary>
        /// 设置已经结束线程总数量
        /// </summary>
        public void ThreadCountDel()
        {
            if (ha["FileSearchThreadEnd"] != null)
            {
                ha.Lock();
                int CurrentThread = Convert.ToInt32(ha["FileSearchThreadEnd"].ToString());          //是否终止线程|已创建线程数|已经结束线程数目
                CurrentThread++;
                ha["FileSearchThreadEnd"] = CurrentThread;
                ha.UnLock();
            }
        }

        /// <summary>
        /// 添加信息
        /// </summary>
        /// <param name="str"></param>
        public void addApp(string str)
        {   
            List<object> list = new List<object>();
            ha.Lock();
            list = (List<object>)ha["FileSearchThread"];
            list.Add(str);
            ha["FileSearchThread"] = list;
            ha.UnLock();
        }

    }
}
