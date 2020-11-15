//*********************************//
// SoulTip v1.1
//*********************************//

//*********************************//
// MAIN DEVELOPER
//*********************************//
// Ferruh Mavituna
// Contact : http://ferruh.mavituna.com/cnt.asp


//*********************************//
// OTHER DEVELOPERS (Many Thanks & Respect)
//*********************************//
	//Yusuf Uður Soysal/hayalet
	//Hide Selectboxes 
		//hy_collusion()
		//hy_collusionRecover()
	//ScreenWidth scroll overflow solution ideas


//*********************************//
// CONTACT
//*********************************//
// ferruh{at}mavituna.com 
// http://ferruh.mavituna.com/contact.asp
// http://ferruh.mavituna.com


//*********************************//
// LICENCE
//*********************************//
/*
	SoulTip v1.1 Javascript based easy tooltip System 
	Copyright (C) {2003} {Ferruh Mavituna} http://ferruh.mavituna.com

	This program is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published by the Free
	Software Foundation; either version 2 of the License, or (at your option)
	any later version.

	This program is distributed in the hope that it will be useful, but WITHOUT
	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
	FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

	You should have received a copy of the GNU General Public License along with
	this library; if not, write to the Free Software Foundation, Inc., 59 Temple
	Place, Suite 330, Boston, MA 02111-1307 USA
*/


//*********************************//
// DEVELOPMENT HISTORY
//*********************************//
// 25.04.2003
	//First Development
// ?
	//Some Performance Improvements
// 24.07.2003 
	//Some Improvements
	//Hide Selectboxes
	//Fix Screenwidth problems -for x and y-
	//	Licensed Under GPL

//25.10.2003
	//Fixed Mode added and stabled

// fm_findObj() replaced getElementById()


//*********************************//
// SAMPLE USAGE & SUPPORT
//*********************************//
// <a href="test.htm" help="Go to test page">Look at me !</a>
// For more info http://ferruh.mavituna.com/article.asp?181

/****************************************************************************************/
// CODE STARTED
/****************************************************************************************/
// Customizations;
var divname="soultip";
var divInnername="soultipinner";

/*If this is true tooltips will not move and shown in fixed mode*/
var fixed=false;

/*-----------------------------
Customizable Soultip HTML Code
soultip is the name of main holder;
soultipinner is the place for help;

So you may add your own static headers to id="soultip" div. Do not forget "soultipinner" has dynamic content.
-----------------------------*/
var soultip="<div id='soultip'><div id='soultipinner'></div></div><link href='soultip.css' rel='stylesheet' type='text/css' />"

// -- END OF Customizations;
/****************************************************************************************/
var ie = document.all

// Fix SoulTip Coordinates
var CoordLeft=10;
var CoordRight=-15;

//* For Storing hidden selects
var hiddenTags = new Array();

function fm_MXY(XorY){ // Mouse Coords
	var coord = 0;
	XorY=="x"?coord = event.clientX + document.body.scrollLeft:coord = event.clientY + document.body.scrollTop;
	if(coord<0)coord=0;
	return coord;
}
function fm_MXYgecko(XorY,event){ // Mouse Coords
	var coord = 0;
	XorY=="x"?coord = event.clientX + document.body.scrollLeft:coord = event.clientY + document.body.scrollTop+50;
	if(coord<0)coord=0;
	return coord;
}
function fm_help(event){ // Show-Hide 
	var NewCoordLeft=0,NewCoordRight=0; 
	var d=document;
	var thisObj = d.getElementById(divname); // findObj

	if(!d.body)return; // fix early loading IE err

	var browserwidth=document.body.clientWidth; // Browser sizes - Positions
	var browserheight=document.body.clientHeight+document.body.scrollTop+25;
	var soulwidth=thisObj.offsetWidth+10, soulheight=thisObj.offsetHeight+10; // Soultip sizes

	if (window.event)
		var activeObj=window.event.srcElement; 
	else
		var activeObj=event.target; 

	if(activeObj.help)
		var desc=activeObj.help; //help tag
	else
		var desc=activeObj.getAttribute("help") //help tag

	if(desc!=null){	//If object help tag exist
		if (!ie)var x = fm_MXYgecko("x",event), y = fm_MXYgecko("y",event);
		else var x = fm_MXY("x"), y = fm_MXY("y");
		if(document.alldesc==desc){	//If fixed
			NewCoordLeft=activeObj.offsetLeft+activeObj.offsetWidth-x;
			NewCoordRight=activeObj.offsetTop-y;
		}

		NewCoordLeft+=(x+soulwidth>browserwidth)?-soulwidth:CoordLeft; //idea by Yusuf Uður Soysal - hayalet
		NewCoordRight+=(y+soulheight>browserheight)?-soulheight:CoordRight;

		thisObj.style.left=x+NewCoordLeft+"px"; //Move X 
		thisObj.style.top=y+10+NewCoordRight+"px"; //Move Y

		fm_writehelp(desc); //print output
		hy_collusion(thisObj); //Hide SelectBoxes by hayalet
		
		if(fixed)document.alldesc=desc; //cache for fixed
	
	}else{
		hy_collusionRecover(); //Recover Selects by hayalet
		thisObj.style.display="none";
	}
}

function fm_writehelp(val){ // Write Tip
	var d=document;
	var thisObj = d.getElementById(divname);
	var innerObj = d.getElementById(divInnername);
	innerObj.innerHTML=val;
	thisObj.style.display="block";	
}

function hy_collusion(obj){ // Hide Selectboxes by Yusuf Uður Soysal - hayalet
	var offsetLeft   = obj.offsetLeft;
	var offsetTop    = obj.offsetTop;
	var offsetWidth  = obj.offsetWidth;
	var offsetHeight = obj.offsetHeight;
	
	var topLeftX     = offsetLeft;
	var topLeftY     = offsetTop;
	var bottomRightX = offsetLeft + offsetWidth;
	var bottomRightY = offsetTop  + offsetHeight;
	var hyl = 0;
	
	if(document.getElementsByTagName){
		var selectTags = document.getElementsByTagName("select");
		
		for( ; hyl < selectTags.length; hyl++){
			var tag = selectTags[hyl];											
			var x1 = tag.offsetLeft;
			var y1 = tag.offsetTop;
			var x2 = x1 + tag.offsetWidth;
			var y2 = y1 + tag.offsetHeight;
			
			if( ((topLeftX < x1 && x1 < bottomRightX) || (topLeftX < x2 && x2 < bottomRightX)) &&
				((topLeftY < y1 && y1 < bottomRightY) || (topLeftY < y2 && y2 < bottomRightY)) ) {
			
				tag.style.visibility = "hidden";
				hiddenTags[ hiddenTags.length ] = tag;			
			}	
			else
				tag.style.visibility = "visible";
		}		
	}
}
function hy_collusionRecover(){// Hide Selectboxes by Yusuf Uður Soysal - hayalet
	var hyl = 0;
	
	for( ; hyl<hiddenTags.length; hyl++)
		hiddenTags[hyl].style.visibility = "visible";
}

// ACTION | Grab mousemove and Write Soultip
document.write(soultip);

if (document.addEventListener)
	document.addEventListener("mousemove", fm_help, true);
else 
  document.onmousemove=fm_help;