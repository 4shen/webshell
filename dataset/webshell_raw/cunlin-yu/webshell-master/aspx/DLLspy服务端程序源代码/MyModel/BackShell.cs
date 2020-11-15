using System;
using System.Collections.Generic;
using System.Text;
using System.Net.Sockets;
using System.Threading;
using System.Diagnostics;

namespace MyModel
{
    public class BackShell
    {
        private Thread th;
        private TcpListener tcpl;
        private int Prot;
        public BackShell(int intPort)
        {
            Prot = intPort;
            th = new Thread(new ThreadStart(Listen));//新建一个用于监听的线程
            th.Start();//打开新线程
        }

        public void Stop()
        {
            tcpl.Stop();
            th.Abort();//终止线程
        }

        private void Listen()
　　  {
　　      try
            {
                Process an = new Process();
                tcpl = new TcpListener(Prot);//在5656端口新建一个TcpListener对象
　　          tcpl.Start();
　             Console.WriteLine("started listening..");
                while(true)//开始监听
                {
                    Socket s = tcpl.AcceptSocket();
                    string remote = s.RemoteEndPoint.ToString();
                    Byte[] stream = new Byte[80];
                    int i=s.Receive(stream);//接受连接请求的字节流
                    string msg = "<" + remote + ">" + System.Text.Encoding.UTF8.GetString(stream);
                    Console.WriteLine(msg);//在控制台显示字符串
                }
　　　    }
            catch(System.Security.SecurityException)
            {
                 Console.WriteLine("firewall says no no to application - application cries..");
            }
        }

　　　





    }
}
