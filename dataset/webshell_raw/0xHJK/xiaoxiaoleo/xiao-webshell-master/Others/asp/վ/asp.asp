<%
Session.CodePage        = 936
Server.ScriptTimeout        = 9999999        '��ֹ�ű���ʱ
Response.Expires         = -1
Response.ExpiresAbsolute    = Now() - 1
Response.cachecontrol         = "no-cache"
response.buffer            = True
Const FileExt            = ".html"        '������չ�� ��Ϊ������վ��֧��html�����������ʽ������Ѹ������
Const PassWord            = "mickey"    '�趨����
Const Ver            = "1.2.0"
%>
<%

Dim ScriptName
ScriptName=Request.ServerVariables("PATH_INFO")

Echo "<html>"
Echo "<head>"
Echo "<meta http-equiv=""Content-Type"" content=""text/html; charset=gb2312"">"
Echo "<meta http-equiv=""pragma"" content=""no-cache"">"
Echo "<title>ASPWebPack - ��վ�ļ����/�ָ�ϵͳ</title>"
Echo "<style>"
Echo ".navbar-text {    BORDER-RIGHT: #999999 1px;    BORDER-TOP: #999999 1px;    PADDING-LEFT: 10px;    FONT-SIZE: 26px;    BACKGROUND-IMAGE: BORDER-LEFT: #999999 1px;    COLOR: #ffffff;    BORDER-BOTTOM: #999999 1px;    BACKGROUND-REPEAT: no-repeat;    FONT-FAMILY: ����;}"
Echo "BODY, TD {    FONT-SIZE: 12px;}"
Echo ".tab-on {    BORDER-RIGHT: #cccccc 1px;    PADDING-RIGHT: 2px;    BORDER-TOP: #cccccc 1px solid;    PADDING-LEFT: 2px;    PADDING-BOTTOM: 2px;    BORDER-LEFT: #cccccc 1px solid;    WIDTH: 120px;    CURSOR: pointer;    COLOR: #000000;    PADDING-TOP: 2px;    BORDER-BOTTOM: #cccccc 1px;    BACKGROUND-COLOR: #ffffff;}"
Echo ".tab-off {    BORDER-RIGHT: #cccccc 1px;    PADDING-RIGHT: 2px;    BORDER-TOP: #cccccc 1px solid;    PADDING-LEFT: 2px;    PADDING-BOTTOM: 2px;    BORDER-LEFT: #cccccc 1px solid;    WIDTH: 120px;    CURSOR: pointer;    COLOR: #666666;    PADDING-TOP: 2px;    BORDER-BOTTOM: #cccccc 1px solid;    BACKGROUND-COLOR: #F9F9FD;}"
Echo ".tab-none {    BORDER-RIGHT: #cccccc 1px;    PADDING-RIGHT: 2px;    BORDER-TOP: #cccccc 1px;    PADDING-LEFT: 2px;    PADDING-BOTTOM: 2px;    BORDER-LEFT: #cccccc 1px solid;    PADDING-TOP: 2px;    BORDER-BOTTOM: #cccccc 1px solid;}"
Echo ".tab-content {    BORDER-RIGHT: #cccccc 1px solid;    PADDING-RIGHT: 5px;    BORDER-TOP: #cccccc 1px;    PADDING-LEFT: 5px;    PADDING-BOTTOM: 5px;    VERTICAL-ALIGN: top;    BORDER-LEFT: #cccccc 1px solid;    PADDING-TOP: 5px;    BORDER-BOTTOM: #cccccc 1px solid;    BACKGROUND-COLOR: #ffffff;}"
Echo ".Soft-content {    BORDER-RIGHT: #cccccc 1px solid;    PADDING-RIGHT: 5px;    BORDER-TOP: #cccccc 1px solid;    PADDING-LEFT: 5px;    PADDING-BOTTOM: 5px;    VERTICAL-ALIGN: top;    BORDER-LEFT: #cccccc 1px solid;    PADDING-TOP: 5px;    BORDER-BOTTOM: #cccccc 1px solid;    BACKGROUND-COLOR: #ffffff;}"
Echo ".hide-table {    DISPLAY: none;}"
Echo ".show-table {    DISPLAY: block;}"
Echo "li{width:100%; line-height:25px; text-overflow:ellipsis; white-space:nowrap; overflow:hidden;list-style:none;list-style-type:none;} "
Echo "input {color: #000000;background-color: #FFFFFF;border: 1px solid #CCCCCC;FONT-SIZE: 9pt;padding:2px;}"
Echo "</style>"
Echo "<script language=javascript>"
Echo "function switchCell(n, hash) {"
Echo "nc=document.getElementsByName(""navcell"");"
Echo "if(nc){"
Echo "t=document.getElementsByName(""tb"");"
Echo "for(i=0;i<nc.length;i++){"
Echo "nc.item(i).className=""tab-off"";"
Echo "t.item(i).className=""hide-table"";"
Echo "}"
Echo "nc.item(n-1).className=""tab-on"";"
Echo "t.item(n-1).className=""tab-content show-table"";"
Echo "}else if(navcell){"
Echo "for(i=0;i<navcell.length;i++){"
Echo "navcell[i].className=""tab-off"";"
Echo "tb[i].className=""hide-table"";"
Echo "}"
Echo "navcell[n-1].className=""tab-on"";"
Echo "tb[n-1].className=""tab-content show-table"";"
Echo "}"
Echo "if(hash){"
Echo "document.location=""#""+hash;"
Echo "}}"
Echo "</script>"
Echo "</head>"
Echo "<body>"

call sub_Main()

Echo "</body>"
Echo "</html>"

If Trim(Session(ScriptName))=Trim(PassWord) Then
    if Request("Down")<>"" Then
        if LCase(Right(Request("Down"),Len(FileExt)))=LCase(FileExt) Then
            Rem �ļ�������
            Set objStream = server.CreateObject("ADODB.Stream")
            objStream.Type = 1
            objStream.Open
            objStream.LoadFromFile Server.MapPath(Request("Down"))

            Response.Clear 
            Response.Buffer = True
            Response.ContentType = "application/octet-stream"
            Response.AddHeader "Content-Disposition","attachment; filename=" & Request("Down")
            Do While Not objStream.EOS
                Response.BinaryWrite objStream.Read(1024*64)
                Response.Flush
                If Not Response.IsClientConnected Then
                        Exit Do 
                End If
            Loop
            objStream.Close
            Set objStream = Nothing
            Response.End
        end if
    end if

    Select Case Request("Action")
        Case "Pack"
                Call sub_Pack(request("Path"))
        Case "Recover"
                Call sub_Recover(request("Path"))
       Case "UPLoad"
               Call sub_UPLoad()
        Case "Delete"
            Call sub_Delete()
    End Select
End If

Sub sub_Main()
Echo "<table cellspacing=1 class=style1 cellpadding=1 width=400 align=center border=0>"
Echo "<tr>"
Echo "<td class=Soft-content>"

Call CheckPwd()

If Trim(Session(ScriptName))=Trim(PassWord) Then
    Select Case Request("Action")
        Case "Pack"
            Echo "<table width=""100%"">"
            Echo "<tr><td colspan=3 align=center>�����Ϣ</td></tr>"
            Echo "<tr><td>����ļ���</td><td id=""PackFile"" colspan=2></td></tr>"
            Echo "<tr><td>������ȣ�</td><td id=""Pro"">0%</td><td id=""FileNum"">0/0</td></tr>"
            Echo "<tr><td>���ڴ����</td></tr>"
            Echo "<tr><td colspan=3><li id=""FileName""></li></td></tr>"
            Echo "<tr><td align=""right"" colspan=3><input type=""button"" value=""����"" onclick=""document.location ='" & ScriptName & "';"" /></td></tr>"
            Echo "</table>"
            Echo vbcrlf
        Case "Recover"
            Echo "<table width=""100%"">"
            Echo "<tr><td colspan=3 align=center>��ѹ��Ϣ</td></tr>"
            Echo "<tr><td>����ļ���</td><td id=""PackFile"" colspan=2></td></tr>"
            Echo "<tr><td>��ѹ���ȣ�</td><td id=""Pro"">0%</td><td id=""FileNum"">0/0</td></tr>"
            Echo "<tr><td>���ڽ�ѹ��</td></tr>"
            Echo "<tr><td colspan=3><li id=""FileName""></li></td></tr>"
            Echo "<tr><td align=""right"" colspan=3><input type=""button"" value=""����"" onclick=""document.location ='" & ScriptName & "';"" /></td></tr>"
            Echo "</table>"
            Echo vbcrlf
        Case "Delete"
            Echo "<table width=""100%"">"
            Echo "<tr><td colspan=3 align=center>ɾ�����</td></tr>"
            Echo "<tr><td align=""right"" colspan=3><input type=""button"" value=""����"" onclick=""document.location ='" & ScriptName & "';"" /></td></tr>"
            Echo "</table>"
            Echo vbcrlf
        Case "UPLoad"
            Echo "<table width=""100%"">"
            Echo "<tr><td colspan=3 align=center>�����ϴ��ļ�</td></tr>"
            Echo "<tr><td align=""right"" colspan=3><input type=""button"" value=""����"" onclick=""document.location ='" & ScriptName & "';"" /></td></tr>"
            Echo "</table>"
            Echo vbcrlf
        case Else
            Call sub_putMain()
    End Select
    Echo "<script language=""javascript"" >"
    Echo "var fn=document.all(""PackFile"");"
    Echo "var f=document.all(""FileName"");"
    Echo "var p=document.all(""Pro"");"
    Echo "var n=document.all(""FileNum"");"
    Echo "</script>"
    Echo vbcrlf
End If
Echo "</td>    </tr>"
Echo "</table>"

End Sub

Sub CheckPWD()
If Request("PassWord")<>"" Then
   Session(ScriptName) = Trim(Request("PassWord"))
End If
If Trim(Session(ScriptName))<>Trim(PassWord) Then
    Echo "<table class=Soft-content cellspacing=5 cellpadding=0 width=100% align=center border=0 name=tb>"
    Echo "<tr>"
    Echo "<td class=td_heading valign=top>"
    Echo "<table width=100% border=0 align=center cellpadding=0 cellspacing=0>"
    Echo "<form id=Frm_Enter name=Frm_Enter method=post action=""" & ScriptName &""">"
    Echo "<input type=hidden name=Action value=Enter />"
    Echo "<tr>"
    Echo "<td height=32>PassWord��</td>"
    Echo "<td>"
    Echo "<input type=password name=PassWord value=""" & Session(ScriptName) & """ /></td>"
    Echo "<td><input type=submit value=Enter /></td>"
    Echo "</tr>"
    Echo "</form>"
    Echo "</table>"
    Echo "</td>"
    Echo "</tr>"
    Echo "</table>"
End If
End Sub

sub sub_putMain()
    Echo "<table cellspacing=0 cellpadding=0 width=100% align=center border=0>"
    Echo "<tr>"
    Echo "<td class=tab-on id=navcell onclick=switchCell(1) name=navcell align=center>�������</td>"
    Echo "<td class=tab-off id=navcell onclick=switchCell(2) name=navcell align=center>�ָ�����</td>"
    Echo "<td class=tab-off id=navcell onclick=switchCell(3) name=navcell align=center>�ϴ����</td>"
    Echo "<td class=""tab-off"" id=""navcell"" onclick=""switchCell(4)"" name=""navcell"" align=center>�������</td>"
    Echo "<td class=""tab-off"" id=""navcell"" onclick=""switchCell(5)"" name=""navcell""  align=center>�������</td>"
    Echo "<td class=""tab-none""> </td>"
    Echo "</tr>"
    Echo "</table>"
    Echo "<table class=tab-content id=tb cellspacing=5 cellpadding=0 width=100% align=center border=0 name=tb>"
    Echo "<tr>"
    Echo "<td class=td_heading valign=top>"
    Echo "<table width=100% border=0 align=center cellpadding=0 cellspacing=0>"
    Echo "<form id=Frm_Pack name=Frm_Pack method=post action=""" & ScriptName & """>"
    Echo "<input type=hidden name=Action value=Pack />"
    Echo "<tr>"
    Echo "<td height=32>��������·����</td>"
    Echo "<td>"
    Echo "<input size=40 type=text name=Path value="""&server.MapPath("/")&""" /></td>"
    Echo "</tr>"
    Echo "<tr>"
    Echo "<td height=32>��ǰ����·����</td>"
    Echo "<td>"&server.MapPath("./")&"</td>"
    Echo "</tr>"
    Echo "<tr>"
    Echo "<td height=32>ѹ�����ļ�����</td>"
    Echo "<td>"
    Echo "<input size=28 type=text name=FileName value="&request.ServerVariables("HTTP_HOST")&" />  "
    Echo "<a onclick=sztime() href=#><font color=#0000ff>��ʱ�����</font></a>"
    Echo "</td>"
    Echo "</tr>"
    Echo "<tr>"
    Echo "<td height=32 align=center colspan=2>"
    Echo "<input type=submit name=Submit value=������� /></td>"
    Echo "</tr>"
    Echo "</form>"
    Echo "</table>"
    Echo "<script language=""javascript"">"
    Echo "function sztime(i){"
    Echo "var Digital=new Date();"
    Echo "var year=Digital.getYear();"
    Echo "var month=Digital.getMonth();"
    Echo "var date=Digital.getDate();"
    Echo "var hours=Digital.getHours();"
    Echo "var minutes=Digital.getMinutes();"
    Echo "var seconds=Digital.getSeconds();"
    Echo "document.Frm_Pack.FileName.value=""""+year+""-""+(month+1)+""-""+date+""(""+hours+minutes+seconds+"")"";"
    Echo "}"
    Echo "</script>"
    Echo "<!--���ݴ��--></td>"
    Echo "</tr>"
Echo "</table>"
Echo "<table class=hide-table id=tb cellspacing=5 cellpadding=0 width=100% align=center border=0 name=tb>"
Echo "<tr>"
Echo "<td class=td_heading valign=top>"
Echo "<table width=100% border=0 align=center cellpadding=0 cellspacing=0>"
Echo "<form id=Frm_Recover name=Frm_Recover onsubmit=""return confirm('ԭ�ļ����ᱻ����,ȷʵҪ��ѹ���ļ���ָ��Ŀ¼��')"" method=post action="""&ScriptName&""">"
Echo "<input type=hidden name=Action value=Recover />"
Echo "<tr>"
Echo "<td height=32>��������·����</td>"
Echo "<td>"
Echo "<input size=40 type=text name=Path value="""&server.MapPath("/")&""" /></td>"
Echo "</tr>"
Echo "<tr>"
Echo "<td height=32>��ǰ����·����</td>"
Echo "<td>"&server.MapPath("./")&"</td>"
Echo "</tr>"
Echo "<tr>"
Echo "<td height=32>ѹ�����ļ�����</td>"
Echo "<td><select name=FileName>"&GetFileList("./")&"</select></td>"
Echo "</tr>"
Echo "<tr>"
Echo "<td height=32 align=center colspan=2>"
Echo "<input type=submit name=Submit value=�ָ����� /></td>"
Echo "</tr>"
Echo "</form>"
Echo "</table>"
Echo "<!-- �ָ����� --></td>"
Echo "</tr>"
Echo "</table>"
Echo "<table class=hide-table id=tb cellspacing=5 cellpadding=0 width=100% align=center border=0 name=tb>"
    Echo "<tr>"
        Echo "<td class=""td_heading"" valign=""top"">"
        Echo "<table width=""100%"" border=""0"" align=""center"" cellpadding=""0"" cellspacing=""0"">"
        Echo "<form method=post action="""&ScriptName&"?action=UPLoad"" enctype=""multipart/form-data"">"
        Echo "<tr>"
        Echo "<td height=32 align=left>�����ļ���</td>"
        Echo "</tr>"
        Echo "<tr>"
        Echo "<td height=32>"
        Echo "<input size=42 type=file name=UpFile value="""" /></td>"
        Echo "</tr>"
        Echo "<tr>"
        Echo "<td height=32> </td>"
        Echo "</tr>"
        Echo "<tr>"
        Echo "<td height=32 align=center>"
        Echo "<input type=submit name=Submit value=�ϴ����� /> </td>"
        Echo "</tr>"
        Echo "</form>"
        Echo "</table>"
        Echo "</td>"
        Echo "</tr>"
        Echo "</table>"
        Echo "<table class=hide-table id=tb cellspacing=5 cellpadding=0 width=100% align=center border=0 name=tb>"
        Echo "<tr>"
        Echo "<td class=td_heading valign=top>"
        Echo "<table width=100% border=0 align=center cellpadding=0 cellspacing=0>"
        Echo "<form id=Frm_Delete name=Frm_Delete onsubmit=""return confirm('ȷ��Ҫɾ���ļ���')"" method=post action='" & ScriptName & "'>"
        Echo "<input type=hidden name=Action value=Delete />"
        Echo "<tr><td height=32>ѹ�����ļ�����</td></tr>"
        Echo "<tr><td height=32><select style=width: 100% name=FileName>" & GetFileList("./") & "</select> </td></tr>"
        Echo "<tr><td height=32> </td></tr>"
        Echo "<tr><td height=32 align=center colspan=2>"
        Echo "<input type=submit name=Submit value=ɾ������ /> "
        Echo "<input type=button name=Submit onclick=""document.location ='" & ScriptName &"?down='+document.Frm_Delete.all('FileName').value;"" value=�������� /></td>"
        Echo "</tr>"
        Echo "</form>"
        Echo "</table>"
        Echo "</td>"
        Echo "</tr>"
        Echo "</table>"
        Echo "<table class=hide-table id=tb cellspacing=5 cellpadding=0 width=100% align=center border=0 name=tb>"
        Echo "<tr>"
        Echo "<td class=td_heading valign=top>"
        Echo "<div align=center><b>ASPWebPack - ��վ�ļ����/�ָ�ϵͳ</b></div>"
        Echo "<br />"
        Echo "<div>ӵ���� ASPWebPack���ϴ�������վ����ֻ��һ��������ɡ�</div>"
        Echo "<div>ԭ�����ߣ�Cool-Co [YuLv] ��ϵ������QQ:1240041</div>"
        Echo "<div>����Ľ���½��</div>"
        Echo "<div>����汾��"&Ver&" </div>"
        Echo "<div>�ҵ�Blog��<a href=http://www.5luyu.cn target=""_blank"">http://www.5luyu.cn</a></div>"
        Echo "<br />"
        Echo "<div>���֧��������</div>"
        Echo "<div>"
        on error resume next
        err.clear
        Call Server.CreateObject("Scripting.Dictionary")
        Echo "Scripting.Dictionary: "
        if Err Then
            Echo "��֧��"
        else
            Echo "֧��" 
        End if        
        Echo "<br/>" 
        err.clear
        Call  Server.CreateObject("Scripting.FileSystemObject")
        Echo "Scripting.FileSystemObject: "
        if Err Then
            Echo "��֧��"
        else
            Echo "֧��" 
        End if        
        Echo "<br/>" 
        err.clear
        Call server.CreateObject("ADODB.Stream")
        Echo "ADODB.Stream: "
        if Err Then
            Echo "��֧��"
        else
            Echo "֧��" 
        End if        
        'Echo "<br/>" 
        err.clear
        on error goto 0
        Echo "</div>"
        Echo "<br />"
        Echo "</td>"
        Echo "</tr>"
        Echo "</table>"
end sub
Rem ##########################
Rem # �ϴ�����ļ�
Rem ##########################
Sub sub_UPLoad()
    Response.Flush
    Dim FileName    
    Dim ObjData,F_File
    FileName="A_" & Year(Now) & "-" & Month(Now) & "-" & Day(Now) & "(" & Hour(Now) & Minute(Now) & Second(Now) & ")" & FileExt
    Set ObjData=New Upload_File
    'ObjData.GetDate(-1)
    Set F_File=ObjData.File("UpFile")
    IF F_File.FileSize = 0 Then
        Echo("<script>alert('�ļ����ݲ���Ϊ�գ��뷵�������ϴ�');</script>")
    Else
        F_File.SaveAs Server.MapPath("./"&FileName)
        Echo("<script>alert('�ļ��ϴ���ϣ��ļ�����:"&FileName&"');</script>")
    End IF
    Set F_File=Nothing
    Set ObjData=Nothing
    response.End()
End Sub

Rem ##########################
Rem # ����ļ�
Rem ##########################
Sub sub_Pack(byVal sPath)
    Response.Flush
    Dim FileName 
    filename = Request("FileName")
    If sPath = "" Then
        Echo("<script>alert('������·����');</script>")
        response.End()
    End If
    If filename = "" Then
        Echo("<script>alert('�������ļ�����');</script>")
        response.End()
    End If
    if LCase(Right(FileName,Len(FileExt)))<>LCase(FileExt) Then
        FileName = FileName & LCase(FileExt)
    End If
       Echo "<script language=""javascript"" >"
    Echo "fn.innerText='" & Replace(Server.MapPath(filename),"\","\\")  & "';"
    Echo "</script>"
    Dim r
    Set r = New CCPack
    r.rootpath = sPath
    r.AddDir sPath
    r.packname = Server.MapPath(filename)
    r.Pack
    if err then
        Response.clear
        Echo("<script>alert('" & Err.Description & "');</script>")
        Response.End
    end if
    Set r = Nothing
    Echo("<script>alert('������ݳɹ���');</script>")
End Sub

Rem ##########################
Rem # ��ѹ���
Rem ##########################
Sub sub_Recover(ByVal sPath)
    Response.Flush
    Dim FileName 
    filename = Request("FileName")
    If sPath = "" Then
        Echo("<script>alert('������·����');history.back(-1);</script>")
        response.End()
    End If
    If filename = "" Then
        Echo("<script>alert('��ѡ��ѹ�����ļ�����');history.back(-1);</script>")
        response.End()
    End If
       Echo "<script language=""javascript"" >"
    Echo "fn.innerText='" & Replace(Server.MapPath(filename),"\","\\")  & "';"
    Echo "</script>"

    Dim fso
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    If Not fso.FileExists(Server.MapPath(filename)) Then
        Echo("<script>alert('��ѹ�����ļ��������ڣ�');history.back(-1);</script>")
        response.End()
    End If
    Set fso = Nothing

    Dim r
    Set r = New CCPack
    r.rootpath = sPath 
    r.packname = Server.MapPath(filename)
    r.UnPack
    Echo(Err.Description)
    Set r = Nothing
    Echo("<script>alert('�ָ����ݳɹ���');</script>")
End Sub

Rem ##########################
Rem # ɾ������ļ�
Rem ##########################
Sub sub_Delete()
    Response.Flush
    Dim FileName 
    filename = Request("FileName")
    If filename = "" Then
        Echo("<script>alert('�������ļ�����');history.back(-1);</script>")
        response.End()
    End If
    if LCase(Right(FileName,Len(FileExt)))<>LCase(FileExt) Then
        FileName = FileName & LCase(FileExt)
    End If
    Call DeleteFile(filename)
    Echo("<script>alert('����ļ�ɾ���ɹ���');</script>")
End Sub


Rem ##########################
Rem # ȡ���ļ��б� For Select
Rem ##########################
function GetFileList(byVal sPath)
    Dim fso, f, fc
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    Set f = fso.GetFolder(server.MapPath(sPath))
    For Each fc in f.Files
        if Right(lcase(fc.Name),Len(FileExt))=lCase(FileExt) then
            GetFileList = GetFileList & "<option value="""&fc.Name&""" >"&fc.Name&"</option>"
        end if
    Next
    if len(GetFileList)=0 Then
        GetFileList = GetFileList & "<option value="""" selected=""selected"" >û���ļ�</option>"
    End If
    Set fc = Nothing
    Set f = Nothing
    Set fso = Nothing
end function

Function Init(byval rootpath)
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    If Not fso.FolderExists(rootpath) Then
        fso.CreateFolder(rootpath)
    End If
    Set fso = Nothing
End Function

'==================================================
'��������DeleteFile
'��  �ã�ɾ���ļ�
'��  ����Url ------ Զ���ļ�URL
'==================================================
Function DeleteFile(Byval url)
    Dim fso
    Set fso = CreateObject("Scripting.FileSystemObject")
    If (fso.FileExists(server.MapPath(url))) Then
        fso.DeleteFile(server.MapPath(url))
    End If
    Set fso = Nothing
End Function

Sub echo(s)
    Response.Write(s)
End Sub
'==================================================
'��  ����CCPack
'��  �ã�asp�����
'��  Դ��CSDN
'��  �ģ�Cool-Co
'˵  ��: Unicode��
'==================================================
Class CCPack
    Dim Files, packname, rootpath, fso

    Private Sub Class_Initialize
        Randomize
        Dim ranNum
        ranNum = Int(90000 * Rnd) + 10000
        packname = Year(Now)&Month(Now)&Day(Now)&Hour(Now)&Minute(Now)&Second(Now)&ranNum&".asp"&Year(Now)
        rootpath = Server.MapPath("./")
        Set Files = server.CreateObject("Scripting.Dictionary")
        Set fso = Server.CreateObject("Scripting.FileSystemObject")
    End Sub

    Private Sub Class_Terminate
        Set fso = Nothing
        Set Files =Nothing
    End Sub

    '��Ӹ��ļ����µ������ļ��м��ļ�
    Public Sub AddDir(byval obj)
        Dim f, subf
        If fso.FolderExists(obj) Then
            Set f = fso.GetFolder(obj)
            '��ӱ��ļ���
            Add(f.Path)
            '�������ļ���
            For Each subf in f.SubFolders
                AddDir(subf.Path)
            Next
            Set subf = Nothing
            Set f = Nothing
        End If
    End Sub

    '��ӵ����ļ��򵥸��ļ��м����ļ����µ������ļ�
    Public Sub Add(byval obj)
        Dim f, fc
        If fso.FileExists(obj) Then
            Set f = fso.GetFile(obj)
            Files.Add obj, f.Size
        ElseIf fso.FolderExists(obj) Then
            Files.Add obj, -1
            Set f = fso.GetFolder(obj)
            For Each fc in f.Files
                Add(fc.Path)
            Next
            Set fc = Nothing
            Set f = Nothing
        End If
    End Sub

    '���
    Public Sub Pack()
        Dim Str, ObjPack, ObjRead, a, b, buf,bf,FileDB,FDBLen
        Set ObjPack= server.CreateObject("ADODB.Stream")        
        Set ObjRead= server.CreateObject("ADODB.Stream")
        ObjPack.Open        
        ObjRead.Open
        ObjPack.Type = 1
        ObjRead.Type = 1
        a = Files.Keys
        b = Files.Items
        bf=( (Files.Count) +1)/100
        For i = 0 To Files.Count -1
        'If b(i)>= 0 Then
        If b(i)> 0 Then
            ObjPack.LoadFromFile(a(i))
            If Not ObjPack.EOS Then ObjRead.Write(ObjPack.Read)
        End If
        If b(i) = -1 Then a(i)=a(i) & "\"
        a(i) = replace(a(i),rootpath,"\",1,-1,1)  
        a(i) = replace(a(i),"\\","\",1,-1,1)  
        FileDB = FileDB & b(i) & ">" & a(i) & "*"

        Echo "<script language=""javascript"" >"
            Echo "f.innerText='" & Replace(a(i),"\","\\") & "';"
            Echo "p.innerText='" & clng(i / bf) & "%';"
            Echo "n.innerText='" & (i+1) & "/" & Files.Count & "';"
            Echo "</script>"
            Response.Flush
            Rem �û���ֹ
            If Not Response.IsClientConnected Then Exit For
        Next
    FDBLen = LenB(FileDB)
        Str = CStr(Strright("000000000" & FDBLen, 10)) & FileDB
        buf = TextToStream(Str)
        ObjPack.Position = 0
    ObjPack.Write buf 
        ObjRead.Position = 0
        Do While Not ObjRead.EOS
            ObjPack.Write ObjRead.Read(1024*64)
            Rem �û���ֹ
            If Not Response.IsClientConnected Then Exit Do 
        Loop
        ObjPack.SetEOS
        ObjPack.SaveToFile(packname), 2
        Set buf = Nothing
        Set ObjRead= Nothing
        Set ObjPack= Nothing
    End Sub

    '��ѹ
    Public Sub UnPack
        Dim Size, ObjPack, ObjWrite, arr, i, buf,bf
        If Not fso.FolderExists(rootpath) Then
            fso.CreateFolder(rootpath)
        End If
        Set ObjPack = server.CreateObject("ADODB.Stream")
        Set ObjWrite= server.CreateObject("ADODB.Stream")
        ObjPack.Open        
        ObjWrite.Open
        ObjPack.Type = 1
        ObjWrite.Type = 1
        'ת���ļ���С
        ObjPack.LoadFromFile(packname)
        ObjPack.Position=0
        if not IsNumeric(StreamToText(ObjPack.Read(22))) then
            Echo("<script>alert('�ļ���ʽ����ȷ,ϵͳ�޷���ѹ��');</script>")
            response.End
        Else
            ObjPack.Position=0
        End if
        Size = Clng(StreamToText(ObjPack.Read(22)))
        arr = Split(StreamToText(ObjPack.Read(Size)), "*")
        bf=( (UBound(arr)) +1)/100
        For i = 0 To UBound(arr) -1
            arrFile = Split(arr(i), ">")
            If arrFile(0) < 0 Then
                myfind(rootpath&arrFile(1))'ȷ���ļ�����
            ElseIf arrFile(0) >= 0 Then
                ObjWrite.Position = 0
                buf = ObjPack.Read(arrFile(0))
                If Not IsNull(buf) Then ObjWrite.Write(buf)
                ObjWrite.SetEOS
                ObjWrite.SaveToFile(rootpath&arrFile(1)), 2
            End If
               Echo "<script>"
            Echo "f.innerText='" & Replace(rootpath & arrFile(1),"\","\\") & "';"
            Echo "p.innerText='" & clng(i / bf) & "%';"
            Echo "n.innerText='" & (i+1) & "/" & UBound(arr) & "';"
            Echo "</script>"
               Echo vbcrlf
            Response.Flush
            Rem �û���ֹ
            If Not Response.IsClientConnected Then Exit for
        Next
        Set buf = Nothing
        Set ObjWrite = Nothing
        Set ObjPack = Nothing
    End Sub

    'Stream Text ����
    Public Function StreamToText(byval stream)
        Dim sm
        If IsNull(stream) Then
            StreamToText = ""
        Else
            Set sm = server.CreateObject("ADODB.Stream")
            sm.Open
            sm.Type = 1
            sm.Write(stream)
            sm.Position = 0
            sm.Type = 2
            sm.Position = 0
            StreamToText = sm.ReadText()
            sm.Close
            Set sm = Nothing
        End If
    End Function

    Public Function TextToStream(byval text)
        Dim sm
        If text = "" Then
            TextToStream = "" '����
        Else
            Set sm = server.CreateObject("ADODB.Stream")
            sm.Open
            sm.Type = 2
            sm.WriteText(text)
            sm.Position = 0
            sm.Type = 1
            sm.Position = 0
            TextToStream = sm.Read
            sm.Close
            Set sm = Nothing
        End If
    End Function

    '��ѹʱ ȷ���ļ��д���myfind��myfso
    Function myfso(byval Path)
        Dim f
        If Not fso.FolderExists(Path) Then
            Set f = fso.CreateFolder(Path)
        End If
        Set f = Nothing
    End Function

    Function myfind(byval Path)
        Dim paths, subpath, i
        '��Ŀ¼���(\)
        If Right(Path, 1)<>"\" Then Path = Path&"\"
        Path = Replace(Replace(Path, "/", "\"), "\\", "\")
        paths = Split(Path, "\")
        For i = 0 To UBound(paths) -1
            subpath = subpath & paths(i) & "\"
            If CStr(Left(subpath, Len(rootpath))) = CStr(rootpath) Then
                myfso(subpath)
            End If
        Next
    End Function

    Function Strright(byval Str, byval L)
        Dim Temp_Str, I, Test_Str
        Temp_Str = Len(Str)
        For i = Temp_Str To 1 step -1
            Test_Str = (Mid(Str, I, 1))
            Strright = Test_Str&Strright
            If Asc(Test_Str)>0 Then
                lens = lens + 1
            Else
                lens = lens + 2
            End If
            If lens>= L Then Exit For
        Next
    End Function
    function iif(e-xpression,returntrue,returnfalse)
    if e-xpression=0 then
        iif=returnfalse
    else
        iif=returntrue
    end if
    end function
End Class

'====== Upload_5xSoft Class ====================================
'  ����ASP������ϴ��� 2.0
'     ��Ȩ��ԭ�������С�
'     ˴���� 2005-10-20 �޸�
'============================================================
Dim Data_5xsoft
Class Upload_File
    dim objForm,objFile,Version
    Public function Form(strForm)
       strForm=lcase(strForm)
       if not objForm.exists(strForm) then
         Form=""
       else
         Form=objForm(strForm)
       end if
     end function
    
    Public function File(strFile)
       strFile=lcase(strFile)
       if not objFile.exists(strFile) then
         set File=new FileInfo
       else
         set File=objFile(strFile)
       end if
     end function
    
    
    Private Sub Class_Initialize
      dim RequestData,sStart,vbCrlf,sInfo,iInfoStart,iInfoEnd,tStream,iStart,theFile
      dim iFileSize,sFilePath,sFileType,sFormValue,sFileName
      dim iFindStart,iFindEnd
      dim iFormStart,iFormEnd,sFormName
      Version="����HTTP�ϴ����� Version 2.0"
      set objForm=Server.CreateObject("Scripting.Dictionary")
      set objFile=Server.CreateObject("Scripting.Dictionary")
      if Request.TotalBytes<1 then Exit Sub
      set tStream = Server.CreateObject("adodb.stream")
      set Data_5xsoft = Server.CreateObject("adodb.stream")
      Data_5xsoft.Type = 1
      Data_5xsoft.Mode =3
      Data_5xsoft.Open
      Data_5xsoft.Write  Request.BinaryRead(Request.TotalBytes)
      Data_5xsoft.Position=0
      RequestData =Data_5xsoft.Read 
    
      iFormStart = 1
      iFormEnd = LenB(RequestData)
      vbCrlf = chrB(13) & chrB(10)
      sStart = MidB(RequestData,1, InStrB(iFormStart,RequestData,vbCrlf)-1)
      iStart = LenB (sStart)
      iFormStart=iFormStart+iStart+1
      while (iFormStart + 10) < iFormEnd 
        iInfoEnd = InStrB(iFormStart,RequestData,vbCrlf & vbCrlf)+3
        tStream.Type = 1
        tStream.Mode =3
        tStream.Open
        Data_5xsoft.Position = iFormStart
        Data_5xsoft.CopyTo tStream,iInfoEnd-iFormStart
        tStream.Position = 0
        tStream.Type = 2
        tStream.Charset ="UTF-8"
        sInfo = tStream.ReadText
        tStream.Close
        'ȡ�ñ���Ŀ����
        iFormStart = InStrB(iInfoEnd,RequestData,sStart)
        iFindStart = InStr(22,sInfo,"name=""",1)+6
        iFindEnd = InStr(iFindStart,sInfo,"""",1)
        sFormName = lcase(Mid (sinfo,iFindStart,iFindEnd-iFindStart))
        '������ļ�
        if InStr (45,sInfo,"filename=""",1) > 0 then
            set theFile=new FileInfo
            'ȡ���ļ���
            iFindStart = InStr(iFindEnd,sInfo,"filename=""",1)+10
            iFindEnd = InStr(iFindStart,sInfo,"""",1)
            sFileName = Mid (sinfo,iFindStart,iFindEnd-iFindStart)
            theFile.FileName=getFileName(sFileName)
            theFile.FilePath=getFilePath(sFileName)
            theFile.FileExt=getFileExt(sFileName)
            'ȡ���ļ�����
            iFindStart = InStr(iFindEnd,sInfo,"Content-Type: ",1)+14
            iFindEnd = InStr(iFindStart,sInfo,vbCr)
            theFile.FileType =Mid (sinfo,iFindStart,iFindEnd-iFindStart)
            theFile.FileStart =iInfoEnd
            theFile.FileSize = iFormStart -iInfoEnd -3
            theFile.FormName=sFormName
            if not objFile.Exists(sFormName) then
              objFile.add sFormName,theFile
            end if
        else
        '����Ǳ���Ŀ
            tStream.Type =1
            tStream.Mode =3
            tStream.Open
            Data_5xsoft.Position = iInfoEnd 
            Data_5xsoft.CopyTo tStream,iFormStart-iInfoEnd-3
            tStream.Position = 0
            tStream.Type = 2
            tStream.Charset ="gb2312"
                sFormValue = tStream.ReadText 
                tStream.Close
            if objForm.Exists(sFormName) then
              objForm(sFormName)=objForm(sFormName)&", "&sFormValue          
            else
              objForm.Add sFormName,sFormValue
            end if
        end if
        iFormStart=iFormStart+iStart+1
        wend
      RequestData=""
      set tStream =nothing
    End Sub
    
    Private Sub Class_Terminate  
     if Request.TotalBytes>0 then
        objForm.RemoveAll
        objFile.RemoveAll
        set objForm=nothing
        set objFile=nothing
        Data_5xsoft.Close
        set Data_5xsoft =nothing
     end if
    End Sub
       
     
     Private function getFilePath(FullPath)
      If FullPath <> "" Then
       GetFilePath = left(FullPath,InStrRev(FullPath, "\"))
      Else
       GetFilePath = ""
      End If
     End  function
     
     Private function getFileName(FullPath)
      If FullPath <> "" Then
       GetFileName = mid(FullPath,InStrRev(FullPath, "\")+1)
      Else
       GetFileName = ""
      End If
     End  function
     
     Private function getFileExt(FullPath)
      If FullPath <> "" Then
            GetFileExt = mid(FullPath,InStrRev(FullPath, ".")+1)
        Else
            GetFileExt = ""
      End If
     End function
     
    End Class
    
    Class FileInfo
      dim FormName,FileName,FilePath,FileSize,FileType,FileStart,FileExt
      Private Sub Class_Initialize 
        FileName = ""
        FilePath = ""
        FileSize = 0
        FileStart= 0
        FormName = ""
        FileType = ""
        FileExt=""
      End Sub
      
     Public function SaveAs(FullPath)
        dim dr,ErrorChar,i
        SaveAs=true
        if trim(fullpath)="" or FileStart=0 or FileName="" or right(fullpath,1)="/" then exit function
        set dr=CreateObject("Adodb.Stream")
        dr.Mode=3
        dr.Type=1
        dr.Open
        Data_5xsoft.position=FileStart
        Data_5xsoft.copyto dr,FileSize
        dr.SaveToFile FullPath,2
        dr.Close
        set dr=nothing 
        SaveAs=false
      end function
End Class

%>
