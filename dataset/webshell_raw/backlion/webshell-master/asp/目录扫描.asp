<HTML>
<HEAD>
<TITLE>Ŀ¼ɨ��</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=gb2312">
<STYLE TYPE="text/css">
a {text-decoration: none}
a:hover {text-decoration: underline; color: #FF9900}
select,textarea,pre,td,th,body,input{font-family: "����";font-size: 9pt}
.Edit {	border: 1px groove #666666;}
.but1 {font-size: 9pt; border-width: 1px; cursor: hand}
</STYLE>
</HEAD>

<BODY>
<%
Response.Buffer = True
Server.ScriptTimeOut=999999999
  
CONST_FSO="Script"&"ing.Fil"&"eSyst"&"emObject"


'��·������ \ 
function GetFullPath(path)
	GetFullPath = path
	if Right(path,1) <> "\" then GetFullPath = path&"\" '����ַ������ \ �ľͼ���
end function

'ɾ���ļ�
Function Deltextfile(filepath)
 On Error Resume Next
 Set objFSO = CreateObject(CONST_FSO) 
  if objFSO.FileExists(filepath) then '����ļ��Ƿ���� 
   objFSO.DeleteFile(filepath) 
  end if 
 Set objFSO = nothing
 Deltextfile = Err.Number '���ش����� 
End Function 


'���Ŀ¼�Ƿ��д 0 Ϊ�ɶ�д 1Ϊ��д������ɾ��
Function CheckDirIsOKWrite(DirStr)
	On Error Resume Next
	Set FSO = Server.CreateObject(CONST_FSO)
	filepath = GetFullPath(DirStr)&fso.GettempName
	FSO.CreateTextFile(filepath) 
	CheckDirIsOKWrite = Err.Number '���ش����� 
	if  ShowNoWriteDir and (CheckDirIsOKWrite =70) then
		Response.Write "[<font color=#0066FF>Ŀ¼</font>]"&DirStr&" [<font color=red>"&Err.Description&"</font>]<br>"
	end if
	set fout =Nothing
	set FSO = Nothing
	Deltextfile(filepath) 'ɾ����
	if CheckDirIsOKWrite=0 and Deltextfile(filepath)=70 then CheckDirIsOKWrite =1
end Function

'����ļ��Ƿ�����޸�(�˷������޸�����,���ܻ��е㲻׼������������)
function CheckFileWrite(filepath)
	On Error Resume Next
	Set FSO = Server.CreateObject(CONST_FSO)	
	set getAtt=FSO.GetFile(filepath)
	getAtt.Attributes = getAtt.Attributes
  CheckFileWrite = Err.Number 
	set FSO = Nothing
	set getAtt = Nothing  
end function

'���Ŀ¼�Ŀɶ�д��
function ShowDirWrite_Dir_File(Path,CheckFile,CheckNextDir)
	On Error Resume Next
	Set FSO = Server.CreateObject(CONST_FSO)
	B = FSO.FolderExists(Path)
	set FSO=nothing
	
  '�Ƿ�Ϊ��ʱĿ¼���Ƿ�Ҫ���
  IS_TEMP_DIR =	(instr(UCase(Path),"WINDOWS\TEMP")>0) and NoCheckTemp
  		
	if B=false then '�������Ŀ¼�ͽ����ļ����
	'==========================================================================
		Re = CheckFileWrite(Path) '����Ƿ��д
		if Re =0 then
			Response.Write "[�ļ�]<font color=red>"&Path&"</font><br>"
			b =true
			exit function
		else
			Response.Write "[<font color=red>�ļ�</font>]"&Path&" [<font color=red>"&Err.Description&"</font>]<br>"						
			exit function
		end if	
	'==========================================================================	
	end if
	

	
	Path = GetFullPath(Path) '�� \	
	
	re = CheckDirIsOKWrite(Path) '��ǰĿ¼Ҳ���һ��
	if (re =0) or (re=1) then
		Response.Write "[Ŀ¼]<font color=#0000FF>"& Path&"</font><br>"
	end if

Set FSO = Server.CreateObject(CONST_FSO)
set f = fso.getfolder(Path)



if (CheckFile=True) and (IS_TEMP_DIR=false) then
b=false
'======================================
for each file in f.Files
	Re = CheckFileWrite(Path&file.name) '����Ƿ��д
	if Re =0 then
		Response.Write "[�ļ�]<font color=red>"& Path&file.name&"</font><br>"
		b =true
	else
		if ShowNoWriteDir then Response.Write "[<font color=red>�ļ�</font>]"&Path&file.name&" [<font color=red>"&Err.Description&"</font>]<br>"			
	end if
next
if b then response.Flush '��������ݾ�ˢ�¿ͻ�����ʾ
'======================================
end if



'============= Ŀ¼��� ================
for each file in f.SubFolders
if CheckNextDir=false then '�Ƿ�����һ��Ŀ¼
	re = CheckDirIsOKWrite(Path&file.name)
	if (re =0) or (re=1) then
		Response.Write "[Ŀ¼]<font color=#0066FF>"& Path&file.name&"</font><br>"
	end if
end if
	
	if (CheckNextDir=True) and (IS_TEMP_DIR=false) then '�Ƿ�����һ��Ŀ¼
			ShowDirWrite_Dir_File Path&file.name,CheckFile,CheckNextDir '�ټ����һ��Ŀ¼
	end if
next
'======================================
Set FSO = Nothing
set f = Nothing
end function


if Request("Paths") ="" then
Paths_str="C:\WINDOWS"&chr(13)&chr(10)&"C:\Documents and Settings"&chr(13)&chr(10)&"C:\Program Files"&chr(13)&chr(10)&"C:\WINDOWS\PCHealth"&chr(13)&chr(10)&"C:\WINDOWS\system32"&chr(13)&chr(10)&"C:\WINDOWS\Registration"&chr(13)&chr(10)&"C:\WINDOWS\system32\spool"&chr(13)&chr(10)&"C:\WINDOWS\Tasks"&chr(13)&chr(10)&"C:\WINDOWS\7i24.com\FreeHost"&chr(13)&chr(10)&"C:\WINDOWS\Temp"&chr(13)&chr(10)&"C:\WINDOWS\system32\spool\PRINTERS"&chr(13)&chr(10)&"C:\WINDOWS\Registration\CRMLog"&chr(13)&chr(10)&"C:\WINDOWS\PCHealth\ERRORREP\QHEADLES"&chr(13)&chr(10)&"C:\WINDOWS\PCHealth\ERRORREP\QSIGNOFF"&chr(13)&chr(10)&"c:\windows\Microsoft.NET\Framework\v2.0.50727\Temporary ASP.NET Files\root\"&chr(13)&chr(10)&"c:\Program Files\Common Files"&chr(13)&chr(10)&"c:\Program Files\Common Files\DU Meter"&chr(13)&chr(10)&"C:\Program Files\Microsoft SQL Server\90\Shared"&chr(13)&chr(10)&"c:\Program Files\Keniu\Keniu Shadu\ProgramData"&chr(13)&chr(10)&"c:\Program Files\Keniu\Keniu Shadu\Temp"&chr(13)&chr(10)&"C:\Program Files\Microsoft SQL Server\90\Shared\ErrorDumps"&chr(13)&chr(10)&"c:\Program Files\KSafe\AppData\update"&chr(13)&chr(10)&"c:\Program Files\KSafe\AppData"&chr(13)&chr(10)&"c:\Program Files\KSafe\Temp\uptemp"&chr(13)&chr(10)&"c:\Program Files\KSafe\Temp"&chr(13)&chr(10)&"c:\Program Files\KSafe\webui\icon"&chr(13)&chr(10)&"c:\Program Files\Rising\RAV\XMLS"&chr(13)&chr(10)&"c:\Program Files\Rising\RAV"&chr(13)&chr(10)&"C:\Program Files\Zend\ZendOptimizer-3.3.0"&chr(13)&chr(10)&"C:\Program Files\Common Files\"&chr(13)&chr(10)&"c:\Program Files\Microsoft SQL Server\90\Shared\ErrorDumps"&chr(13)&chr(10)&"C:\Program Files\Symantec AntiVirus\SAVRT"&chr(13)&chr(10)&"C:\Program Files\Zend\ZendOptimizer-3.3.0\docs"&chr(13)&chr(10)&"c:\Program Files\Thunder Network\Thunder"&chr(13)&chr(10)&"D:\Program Files\Thunder Network\Thunder\ComDlls"&chr(13)&chr(10)&"D:\Program Files\Thunder Network\Thunder\Program"&chr(13)&chr(10)&"D:\Program Files\Adobe\Reader 9.0"&chr(13)&chr(10)&"D:\Program Files\Tencent"&chr(13)&chr(10)&"C:\Program Files\Symantec AntiVirus\SAVRT"&chr(13)&chr(10)&"C:\Program Files\Zend\ZendOptimizer-3.3.0\docs"&chr(13)&chr(10)&"C:\Program Files\360"&chr(13)&chr(10)&"C:\Program Files\360\360safe"&chr(13)&chr(10)&"C:\Program Files\360\360sd"&chr(13)&chr(10)&"C:\Program Files\360\360Se"&chr(13)&chr(10)&"c:\Program Files\360\360safe\deepscan\Section"&chr(13)&chr(10)&"c:\Program Files\360\360sd\AntiSection"&chr(13)&chr(10)&"c:\Program Files\360\360sd\deepscan\Section"&chr(13)&chr(10)&"C:\Program Files\Eset"&chr(13)&chr(10)&"C:\Program Files\ESET\ESET NOD32 Antivirus"&chr(13)&chr(10)&"C:\Program Files\WinRAR"&chr(13)&chr(10)&"C:\Documents and Settings\All Users"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\DRM"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\macfee\"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\360safe"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\360safe\360Disabled"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\360safe\softmgr"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\360SD"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\VMware"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\VMware\Compatibility"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\VMware\Compatibility\native"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\VMware\VMware Tools"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\VMware\VMware Tools\Unity"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\Network\Connections\Pbk"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\User Account Pictures"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\HTML Help"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\Media Index"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\McAfee\DesktopProtection"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\Adobe"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\kingsoft\kis\KCLT"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\Thunder Network"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\VMware"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\Xunlei"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\Knsd"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\Crypto\DSS\MachineKeys"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\Crypto\RSA\MachineKeys"&chr(13)&chr(10)&"c:\Documents and Settings\All Users\Application Data\Microsoft\Media Index"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\Hagel Technologies\DU Meter"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\ESET\ESET NOD32 Antivirus"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\ESET\ESET NOD32 Antivirus\Updfiles\"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\ESET"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Documents\My Music\"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Documents\My Music\Sample Playlists"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Documents\My Music\Sync Playlists"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Documents\My Music\�ҵĲ����б� Filters\"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\McAfee\DesktopProtection"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\Symantec\pcAnywhere"&chr(13)&chr(10)&"C:\Documents and Settings\All Users\Application Data\kingsoft\kis\KCLT\"&chr(13)&chr(10)&"C:\php\PEAR"&chr(13)&chr(10)&"C:\7i24.com\iissafe\log"&chr(13)&chr(10)&"C:\RECYCLER"&chr(13)&chr(10)&"e:\recycler"&chr(13)&chr(10)&"f:\recycler"&chr(13)&chr(10)&"c:\recycler"&chr(13)&chr(10)&"d:\recycler"&chr(13)&chr(10)&"C:\php\dev"&chr(13)&chr(10)&"d:\~1"&chr(13)&chr(10)&"e:\~1"&chr(13)&chr(10)&"C:\~1"&chr(13)&chr(10)&"C:\php\dev"&chr(13)&chr(10)&"C:\KnsdRecycle"&chr(13)&chr(10)&"C:\KnsdRecycle\update"&chr(13)&chr(10)&"C:\KRSHistory"&chr(13)&chr(10)&"C:\KSafeRecycle"&chr(13)&chr(10)&"C:\System Volume Information"&chr(13)&chr(10)&"c:\"&chr(13)&chr(10)&"d:\"&chr(13)&chr(10)&"e:\"&chr(13)&chr(10)&"f:\"&chr(13)&chr(10)&"g:\"&chr(13)&chr(10)&"h:\"
if Session("paths")<>"" then  Paths_str=Session("paths")
	Response.Write "<form id='form1' name='form1' method='post' action=''>"
	Response.Write "�˳�����Լ�����������Ŀ¼��д���,Ϊ��������ṩһЩ��ȫ�����Ϣ!<br>�����������Ŀ¼,������Զ������Ŀ¼<br>"	
	Response.Write "<textarea name='Paths' cols='80' rows='10' class='Edit'>"&Paths_str&"</textarea>"
	Response.Write "<br />"
	Response.Write "<input type='submit' name='button' value='��ʼ���' / class='but1'>"
	Response.Write "<label for='CheckNextDir'>"
	Response.Write "<input name='CheckNextDir' type='checkbox' id='CheckNextDir' checked='checked' />����Ŀ¼  "
	Response.Write "</label>"
	Response.Write "<label for='CheckFile'>"
	Response.Write "<input name='CheckFile' type='checkbox' id='CheckFile' checked='checked'  />�����ļ�"
	Response.Write "</label>"
	Response.Write "<label for='ShowNoWrite'>"
	Response.Write "<input name='ShowNoWrite' type='checkbox' id='ShowNoWrite'/>"
	Response.Write "�Խ�дĿ¼���ļ�</label>"
	Response.Write "<label for='NoCheckTemp'>"
	Response.Write "<input name='NoCheckTemp' type='checkbox' id='NoCheckTemp' checked='checked' />"
	Response.Write "�������ʱĿ¼</label>"	
	Response.Write "</form>"
else
Response.Write  "<a href=""?"">��������·��</a><br>"
CheckFile = (Request("CheckFile")="on")
CheckNextDir = (Request("CheckNextDir")="on")
ShowNoWriteDir = (Request("ShowNoWrite")="on")
NoCheckTemp = (Request("NoCheckTemp")="on")
Response.Write "��������Ҫһ����ʱ�����Ե�......<br>"
response.Flush

Session("paths") = Request("Paths")

PathsSplit=Split(Request("Paths"),chr(13)&chr(10)) 
For i=LBound(PathsSplit) To UBound(PathsSplit) 
if instr(PathsSplit(i),":")>0 then
	ShowDirWrite_Dir_File Trim(PathsSplit(i)),CheckFile,CheckNextDir
End If 
Next
Response.Write "[ɨ�����]<br>"
end if



%>
</BODY>  