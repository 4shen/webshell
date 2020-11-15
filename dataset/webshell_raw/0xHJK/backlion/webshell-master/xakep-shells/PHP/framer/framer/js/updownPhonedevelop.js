var sieunhantk_dy=100;
var sieunhantk_dtime=5;
var truyenkypost,timer;

function truyenkyvn()
{
timer=setInterval ("scrollwindow ()",30);
}
function truyenkysc()
{
clearInterval(timer);
}
function scrollwindow()
{
truyenkypost = document.documentElement.scrollTop || document.body.scrollTop;
window.scrollTo(0,++truyenkypost);
//window.alert(truyenkypost)
//if (truyenkypost>10)
//truyenkysc();
}
//document.onmousedown=sc;

function sieunhantk_gettop()
{
	try{
		if(window.pageYOffset!=undefined)
			return window.pageYOffset;
		return window.document.body.scrollTop;
	}catch(err)
	{
		try{
			return window.document.body.scrollTop;
		}catch(err2)
		{
			return 0;
		}
	}

}
function sieunhantk_croll_top()
{

	var sieunhantk_body_obj=window.document.body;
	var sieunhantk_cur_stop=sieunhantk_gettop();
	window.scrollBy (0,-sieunhantk_dy);
	var sieunhantk_new_stop=sieunhantk_gettop();
	if(sieunhantk_cur_stop>sieunhantk_new_stop)
		setTimeout("sieunhantk_croll_top()",sieunhantk_dtime);
	else
		document.getElementById("sieunhantk_scroll_down_img").style.display="block";
	return false;
}

function sieunhantk_croll_down()
{
	var sieunhantk_body_obj=window.document.body;
	var sieunhantk_cur_stop=sieunhantk_gettop();
	window.scrollBy (0,sieunhantk_dy);
	var sieunhantk_new_stop=sieunhantk_gettop();
	if(sieunhantk_cur_stop<sieunhantk_new_stop)
		setTimeout("sieunhantk_croll_down()",sieunhantk_dtime);
	else
		document.getElementById("sieunhantk_scroll_down_img").style.display="none";
	return false;
}

function sieunhantk_display_scroll_btn()
{
	var sieunhantk_body=window.document.body;
	var sieunhantk_height=sieunhantk_body.scrollHeight;
	var sieunhantk_top=sieunhantk_gettop();
	
	if(sieunhantk_top==0)
		document.getElementById("sieunhantk_scroll_up_img").style.display="none";
	else
		document.getElementById("sieunhantk_scroll_up_img").style.display="block";
	
}
setInterval("sieunhantk_display_scroll_btn()",100);
document.write('<div style="position: fixed; width: 64px; right: -20px; bottom: 65px;">	<a href="#" onclick="return sieunhantk_croll_top()"><img border="0" id="sieunhantk_scroll_up_img" width="45px" src="img/Button-Upload-icon.png" title="Back Top"></a><br /> <a href="#" onclick="return sieunhantk_croll_down()"><img border="0" width="45px" id="sieunhantk_scroll_down_img" src="img/Button-Download-icon.png" title="Back Bottom"></a></div></div>');
	