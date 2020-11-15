-------------------------
XSS SHELL v0.6.2	- 7/5/2007
-------------------------
Ferruh Mavituna

-------------------------
WHAT IS XSS SHELL ?
-------------------------
XSS Shell is a powerful XSS backdoor and XSS zombie manager.  This concept was first presented by "XSS-Proxy - http://xss-proxy.sourceforge.net/". Normally during XSS attacks an attacker has one shot however,an XSS Shell can be used interactively to send requests and receive responses from a victim, it is also possible to backdoor the page and keep the connection open between the attacker and the victim. 

It is a good way of bypassing the following protections:
	- Bypassing IP Restrictions
	- NTLM / Basic Auth or any similar authentication
	- Session based custom protections

-------------------------
LICENCE
-------------------------
It is licensed under GPL, Check xssshell.asp for details.

-------------------------
FEATURES
-------------------------
XSS Shell has several features which can be used to gain complete access over the victim.  The new version supports XSS Tunnelling through an XSS Tunnel.

Most of the features can be enabled/disabled from the configuration section of the source code.

Key Features;
	- Regenerating Pages
	    - This is one of the key and advanced features of XSS Shell. XSS Shell re-renders the infected page and keeps the user in a virtual environment. Thus even when a user clicks on any of the links in the infected page they will be still under control! (within cross-domain restrictions) In normal XSS attacks when the user leaves the page nothing can be done.
	    - Secondly this feature keeps the session open so even the victims follow an outside link from the infected page session which is not going to timeout and the attacker will be still in charge.
	- Keylogger
	- Mouse Logger (click points + current DOM)

Built-in Commands;
	- Get Keylogger Data
	- Get Current Page (Current rendered DOM / such as a screenshot)
	- Get Cookie
	- Execute supplied javaScript (eval)
	- Get Clipboard (IE only)
	- Get internal IP address (Firefox + JVM only)
	- Check victim's visited URL history
	- Force to Crash victim's browser

-------------------------
INSTALL
-------------------------
XSS Shell is written in ASP. As the backend uses MS Access for portability and easy installation. It is possible to simply port it into any other server-side solution. 

You will require IIS 5 or above of it to work.

An installition video available : http://ferruh.mavituna.com/makale/xss-shell-install-video/

To Install The Admin Interface;
	1. Copy the "xssshell" folder into the web server
	2. Modify the hard coded password in db.asp [default password is : w00t]
	3. Access the admin interface from something like:
		  http://[YOURHOST]/xssshell/admin/
	4. Setup permissions for the database folder (write/read access for IUSR_<Machine_Name>)

To Configure The XSS Shell For Communication;
	1. Open xssshell.asp
	2. Set the "SERVER" variable to the location of XSSShell folder, i.e: "http://[YOURHOST]/xssshell/";

You should now be able to open the admin interface from your browser http://[YOURHOST]/xssshell/admin/ .
Testing should be possible by modifying the "sample_victim/default.asp" source code and replacing the "http://[YOURHOST]/xssshell/xssshell.asp" URL with a specific (i.e your own) XSS Shell URL. Open the "sample_victim" folder in another browser and may then be uploaded into another server.

Now a zombie in the admin interface should be visible. Write something into the "parameters" textarea and click "alert()". An alert message should appear in the victim's browser. 

-------------------------
SECURITY & EXTRA CONFIGURATION
-------------------------
To setup in a secure way you should carryout the following as well
	1. Copy "db" to a secure place (below root)
	2. Configure "database path" from "xssshell/db.asp" (it's recommended to not enable parent paths and use full path for db path)
	3. Configure your web server to not show any error.

If file names are changed;
Be sure to check the "ME", "CONNECTOR", "COMMANDS_URL" variables in xssshell.asp. 

-------------------------
HOW CAN YOU EXTEND?
-------------------------
First implement the new functionality to xssshell.asp
	1. Add new enum for your control
		- Set a name and unique number like "CMD_GETCOOKIE"
		  var CMD_SAMPLE = 78;
		
		- Set datatype for your response (generally TEXT), 
		  dataTypes[CMD_SAMPLE] = TEXT;
		
	2. Write the function and add it to the page
		- function cmdSample(){return "yeah working !"}
	
	3. Call it
		- Go inside to "function processGivenCommand(cmd)"
		- Add a new case such as "case CMD_SAMPLE:"
	
	4. Report it back
		- Inside the case call log;
	          "log(cmdSample(), dataTypes[cmd.cmd], cmd.attackID, "waitAndRun()");"
		
Secondly Implement it to admin interface;
	1. In db.asp just add a new element to "Commands" array (command name, command unique number, description). 
	    i.e. "cmdSample()",78,"Command sample ! which just returns a message"

There are parameters and lots of helpers in the code.  Investigate other commands for reference. 
Enable the debug feature in order to debug the new commands easily. Debug has several levels, which can be increased in number to get more detailed debug information.


-------------------------
KNOWN BUGS;
-------------------------
	- Keylogger is not working on IE
	- Not working on Konqueror


-------------------------
CHANGELOG
-------------------------
	- v0.2 (14/08/2006)
	    - Communication Changes
	    - Working well in cross-site domains issues
	    - New commands added

	- v0.3 (18/08/2006)
	    - Frameset Feature
	    - Several changes & Bug Fixes
	
	- v0.3.1 (30/10/2006)
	    - Clean-up in files and folders

	- v0.3.5 (31/10/2006)
	    - Improvements and fixes relating to victim management
	    - Password Protected admin pages

	- v0.3.7 (01/11/2006)
	    - Visited Link checker command
	    - Spell checks
	    - Save / Eval parsing bugs fixed
	    - History Checker added
	    - Minor fixes & improvements especially in tunnel
            - Some admin interface makeover

	- v0.3.8 (01/11/2006)
	    - getURL Command() - no post etc. support yet...
	    - Post support added to getURL() command!

	- v0.3.9 (02/11/2006)
	    - Connection drop timeout check. If the XSS Shell server is down or connection dropped because of the victim it will try to repair itself.
	    - DoS Command and Crash command added

	- v0.4.0 (02.04.2007)
	    - XSS Tunnel Changes

	- v0.5.0 (03.04.2007)
	    - XSS Tunnel Release

	- v0.5.1 (04.04.2007)
	    - Base64 in get responses

	- v0.5.2 (08.04.2007)
	    - Base64 removed because of binary handling bugs
	    - Custom binary encoding (binEncode()) added
	
	- v0.5.3 (08.04.2007)
	    - All eval() calls removed and replaced with proper callbacks, finally!
	    - New HTTP Response builder to forward all traffic to the proxy
	
	- v0.5.4 (15/04/2007)
	    - New callback related bugs fixed

	- v0.5.5 (21/04/2007)
	    - CMD_GETURL command added
	    - pushCommand() cvallback implementation
	    - Debug messages modified
	    - Random Iframe_Id generationg started for multi-threading
	
	- v0.5.6 (22/04/2007)
	    - Multithreading
	    - Tweaked processing flow
	    - Couple of bug fixes
	    - New command for getting current location of Victim
	
	- v0.5.7 (21/06/2007)
	    - Binary Working in IE
	    - XMLHTTP replaces changes with a new code and is slightly refactored
	    - Source Code Makeup
	    - Installition and default folder changes
	    - Readme update
	    - JS libraries for admin updated to fix some random js errors
	
	- v0.6.0 (26/06/2007)
	    - Finally working in all IE6,IE7,FF with full binary support
	    - Remote VB Call addedd
	    - VB Handler added
	    - Optional calls added
	    - Non required commented code cleaned
	    - Couple of simple refactoring

	- v0.6.1 (7/4/2007)
		- Small fixes and improvements related with script loading
		- Dynamic load support
		- 1 ms delay added to document.write while regeneratign frames to fix generic document.write bug
		- Now can be loaded before the whole load or after
		- Not load itself more than once in any case
	- v0.6.2 (7/5/2007)
		- Debug related noisy messages changed to debug calls


TEST STATUS
	18/08/2006
		Working IE 6+, FF 1.5+
		Keylogger and Mouse Logger are not working in IE due to the new frame improvements

	08/04/2007
		Working IE 6+, IE7+ FF 2.0.0.3+
		Keylogger and Mouse Logger are the same

	26/06/2007
		Binary Reading and XSS Tunnel work in FF 2, IE6, IE7

POTENTIAL TODO;
	/ Attach keylogger in every page changes (still not working for IE)
	- Better keylogger (include active focused elements, handle special charsets etc.)
	- Hijack forms and grab a copy of data to bypass all kind of stuff (including client based MD5 hashing, virtual keyboards, of course SSL and event Java based secure input implementations.)

KNOWN ISSUES;
	- Keylogger is not working on IE
		

-------------------------
JS libraries for administration interface and Code Snippets
-------------------------
moo.ajax		- moofx.mad4milk.net
script.aculo.us		- (http://script.aculo.us, http://mir.aculo.us)
Marcus Granado		- XHR Binary transfer code snippet
Anonymous		- XHR Binary transfer code snippet for IE


-------------------------
CONTACT
-------------------------
ferruh-at-mavituna.com
http://ferruh.mavituna.com