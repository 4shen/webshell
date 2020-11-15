using System;
using System.Collections.Generic;
using System.Text;
using System.Net.Sockets;

namespace MyModel
{
    public class ScanPort
    {
        private string _ip = "";
        private int jTdO = 0;
        private TimeSpan _timeSpent;
        private string QGcH = "Not scanned";
        public string ip
        {
            get { return _ip; }
        }
        public int port
        {
            get { return jTdO; }
        }
        public string status
        {
            get { return QGcH; }
        }
        public TimeSpan timeSpent
        {
            get { return _timeSpent; }
        }
        public ScanPort(string ip, int port)
        {
            _ip = ip;
            jTdO = port;
        }
        public void Scan(string _ip,int jTdO)
        {
            TcpClient iYap = new TcpClient();
            DateTime qYZT = DateTime.Now;
            try
            {
                iYap.Connect(_ip, jTdO);
                iYap.Close();
                QGcH = "<font color=green><b>Open</b></font>";
            }
            catch
            {
                QGcH = "<font color=red><b>Close</b></font>";
            }
            _timeSpent = DateTime.Now.Subtract(qYZT);
        }
    }
}
