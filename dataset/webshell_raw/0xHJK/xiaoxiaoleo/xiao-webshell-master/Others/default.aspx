<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hacked By LeeT BoY</title>
<script type="text/javascript">
			/* <![CDATA[ */
			(function() {
				var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
				s.type = 'text/javascript';
				s.async = true;
				s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
				t.parentNode.insertBefore(s, t);
			})();
			/* ]]> */
		</script>
<style type="text/css">h1.drop-shadow{text-shadow:4px 4px 8px #1AD98F}</style>
<script language="JavaScript">
<!-- 
//edit this message to say what you want
var message="you got hacked by leet boy | bdleet24@gmail.com."; 
 
function clickIE() {if (document.all) {noalert(message); return false;}}
function clickNS(e) {if 
(document.layers||(document.getElementById&&!document.all)) {
if (e.which==2||e.which==3) {noalert(message);return false;}}}
if (document.layers) 
{document.captureEvents(Event.MOUSEDOWN);document.onmousedown=clickNS;}
else{document.onmouseup=clickNS;document.oncontextmenu=clickIE;}
 
document.oncontextmenu=new Function("return false")
// -->
</script>
<script type="text/javascript">
/**
 * this is LeeT BoY

 */
var DAT=DAT||{};
DAT.GUI=function(a){a==void 0&&(a={});var b=!1;a.height==void 0?a.height=300:b=!0;var d=[],c=[],i=!0,f,h,j=this,g=!0,e=280;if(a.width!=void 0)e=a.width;var q=!1,k,p,n=0,r;this.domElement=document.createElement("div");this.domElement.setAttribute("class","guidat");this.domElement.style.width=e+"px";var l=a.height,m=document.createElement("div");m.setAttribute("class","guidat-controllers");m.style.height=l+"px";m.addEventListener("DOMMouseScroll",function(a){var b=this.scrollTop;a.wheelDelta?b+=a.wheelDelta:
a.detail&&(b+=a.detail);a.preventDefault&&a.preventDefault();a.returnValue=!1;m.scrollTop=b},!1);var o=document.createElement("a");o.setAttribute("class","guidat-toggle");o.setAttribute("href","#");o.innerHTML=g?"Close Controls":"Open Controls";var t=!1,C=0,x=0,u=!1,v,y,w,z,D=function(a){y=v;z=w;v=a.pageY;w=a.pageX;a=v-y;if(!g)if(a>0)g=!0,l=k=1,o.innerHTML=p||"Close Controls";else return;var b=z-w;if(a>0&&l>h){var d=DAT.GUI.map(l,h,h+100,1,0);a*=d}t=!0;C+=a;k+=a;l+=a;m.style.height=k+"px";x+=b;e+=
b;e=DAT.GUI.constrain(e,240,500);j.domElement.style.width=e+"px";A()};o.addEventListener("mousedown",function(a){y=v=a.pageY;z=w=a.pageX;u=!0;a.preventDefault();C=x=0;document.addEventListener("mousemove",D,!1);return!1},!1);o.addEventListener("click",function(a){a.preventDefault();return!1},!1);document.addEventListener("mouseup",function(a){u&&!t&&j.toggle();if(u&&t)if(x==0&&B(),k>h)clearTimeout(r),k=n=h,s();else if(m.children.length>=1){var b=m.children[0].offsetHeight;clearTimeout(r);n=Math.round(l/
b)*b-1;n<=0?(j.close(),k=b*2):(k=n,s())}document.removeEventListener("mousemove",D,!1);a.preventDefault();return u=t=!1},!1);this.domElement.appendChild(m);this.domElement.appendChild(o);if(a.domElement)a.domElement.appendChild(this.domElement);else if(DAT.GUI.autoPlace){if(DAT.GUI.autoPlaceContainer==null)DAT.GUI.autoPlaceContainer=document.createElement("div"),DAT.GUI.autoPlaceContainer.setAttribute("id","guidat"),document.body.appendChild(DAT.GUI.autoPlaceContainer);DAT.GUI.autoPlaceContainer.appendChild(this.domElement)}this.autoListenIntervalTime=
1E3/60;var E=function(){f=setInterval(function(){j.listen()},this.autoListenIntervalTime)};this.__defineSetter__("autoListen",function(a){(i=a)?c.length>0&&E():clearInterval(f)});this.__defineGetter__("autoListen",function(){return i});this.listenTo=function(a){c.length==0&&E();c.push(a)};this.unlistenTo=function(a){for(var b=0;b<c.length;b++)c[b]==a&&c.splice(b,1);c.length<=0&&clearInterval(f)};this.listen=function(a){var a=a||c,b;for(b in a)a[b].updateDisplay()};this.listenAll=function(){this.listen(d)};
this.autoListen=!0;var F=function(a,b){function d(){return a.apply(this,b)}d.prototype=a.prototype;return new d};this.add=function(){if(arguments.length==1){var a=[],c;for(c in arguments[0])a.push(j.add(arguments[0],c));return a}a=arguments[0];c=arguments[1];a:for(var e in d)if(d[e].object==a&&d[e].propertyName==c)break a;e=a[c];if(e==void 0)DAT.GUI.error(a+" either has no property '"+c+"', or the property is inaccessible.");else if(a=typeof e,e=G[a],e==void 0)DAT.GUI.error("Cannot create controller for data type '"+
a+"'");else{for(var f=[this],g=0;g<arguments.length;g++)f.push(arguments[g]);if(e=F(e,f)){m.appendChild(e.domElement);d.push(e);DAT.GUI.allControllers.push(e);a!="function"&&DAT.GUI.saveIndex<DAT.GUI.savedValues.length&&(e.setValue(DAT.GUI.savedValues[DAT.GUI.saveIndex]),DAT.GUI.saveIndex++);A();q||(k=h);if(!b)try{if(arguments.callee.caller==window.onload)l=n=k=h,m.style.height=l+"px"}catch(i){}return e}else DAT.GUI.error("Error creating controller for '"+c+"'.")}};var A=function(){h=0;for(var a in d)h+=
d[a].domElement.offsetHeight;m.style.overflowY=h-1>k?"auto":"hidden"},G={number:DAT.GUI.ControllerNumber,string:DAT.GUI.ControllerString,"boolean":DAT.GUI.ControllerBoolean,"function":DAT.GUI.ControllerFunction};this.reset=function(){};this.toggle=function(){g?this.close():this.open()};this.open=function(){o.innerHTML=p||"Close Controls";n=k;clearTimeout(r);s();B();g=!0};this.close=function(){o.innerHTML=p||"Open Controls";n=0;clearTimeout(r);s();B();g=!1};this.name=function(a){p=a;o.innerHTML=a};
this.appearanceVars=function(){return[g,e,k,m.scrollTop]};var s=function(){l=m.offsetHeight;l+=(n-l)*0.6;Math.abs(l-n)<1?l=n:r=setTimeout(s,1E3/30);m.style.height=Math.round(l)+"px";A()},B=function(){j.domElement.style.width=e-1+"px";setTimeout(function(){j.domElement.style.width=e+"px"},1)};if(DAT.GUI.guiIndex<DAT.GUI.savedAppearanceVars.length){e=parseInt(DAT.GUI.savedAppearanceVars[DAT.GUI.guiIndex][1]);j.domElement.style.width=e+"px";k=parseInt(DAT.GUI.savedAppearanceVars[DAT.GUI.guiIndex][2]);
q=!0;if(eval(DAT.GUI.savedAppearanceVars[DAT.GUI.guiIndex][0])==!0){var l=k,H=DAT.GUI.savedAppearanceVars[DAT.GUI.guiIndex][3];setTimeout(function(){m.scrollTop=H},0);if(DAT.GUI.scrollTop>-1)document.body.scrollTop=DAT.GUI.scrollTop;n=k;this.open()}DAT.GUI.guiIndex++}DAT.GUI.allGuis.push(this);if(DAT.GUI.allGuis.length==1&&(window.addEventListener("keyup",function(a){!DAT.GUI.supressHotKeys&&a.keyCode==72&&DAT.GUI.toggleHide()},!1),DAT.GUI.inlineCSS))a=document.createElement("style"),a.setAttribute("type",
"text/css"),a.innerHTML=DAT.GUI.inlineCSS,document.head.insertBefore(a,document.head.firstChild)};DAT.GUI.hidden=!1;DAT.GUI.autoPlace=!0;DAT.GUI.autoPlaceContainer=null;DAT.GUI.allControllers=[];DAT.GUI.allGuis=[];DAT.GUI.supressHotKeys=!1;DAT.GUI.toggleHide=function(){DAT.GUI.hidden?DAT.GUI.open():DAT.GUI.close()};DAT.GUI.open=function(){DAT.GUI.hidden=!1;for(var a in DAT.GUI.allGuis)DAT.GUI.allGuis[a].domElement.style.display="block"};
DAT.GUI.close=function(){DAT.GUI.hidden=!0;for(var a in DAT.GUI.allGuis)DAT.GUI.allGuis[a].domElement.style.display="none"};DAT.GUI.saveURL=function(){var a=DAT.GUI.replaceGetVar("saveString",DAT.GUI.getSaveString());window.location=a};DAT.GUI.scrollTop=-1;DAT.GUI.load=function(a){var a=a.split(","),b=parseInt(a[0]);DAT.GUI.scrollTop=parseInt(a[1]);for(var d=0;d<b;d++){var c=a.splice(2,4);DAT.GUI.savedAppearanceVars.push(c)}DAT.GUI.savedValues=a.splice(2,a.length)};DAT.GUI.savedValues=[];
DAT.GUI.savedAppearanceVars=[];DAT.GUI.getSaveString=function(){var a=[],b;a.push(DAT.GUI.allGuis.length);a.push(document.body.scrollTop);for(b in DAT.GUI.allGuis)for(var d=DAT.GUI.allGuis[b].appearanceVars(),c=0;c<d.length;c++)a.push(d[c]);for(b in DAT.GUI.allControllers)DAT.GUI.allControllers[b].type!="function"&&(d=DAT.GUI.allControllers[b].getValue(),DAT.GUI.allControllers[b].type=="number"&&(d=DAT.GUI.roundToDecimal(d,4)),a.push(d));return a.join(",")};
DAT.GUI.getVarFromURL=function(a){for(var b,d=window.location.href.slice(window.location.href.indexOf("?")+1).split("&"),c=0;c<d.length;c++)if(b=d[c].split("="),b!=void 0&&b[0]==a)return b[1];return null};
DAT.GUI.replaceGetVar=function(a,b){for(var d,c=window.location.href,i=window.location.href.slice(window.location.href.indexOf("?")+1).split("&"),f=0;f<i.length;f++)if(d=i[f].split("="),d!=void 0&&d[0]==a)return c.replace(d[1],b);if(window.location.href.indexOf("?")!=-1)return c+"&"+a+"="+b;return c+"?"+a+"="+b};DAT.GUI.saveIndex=0;DAT.GUI.guiIndex=0;DAT.GUI.showSaveString=function(){noalert(DAT.GUI.getSaveString())};
DAT.GUI.makeUnselectable=function(a){if(!(a==void 0||a.style==void 0)){a.onselectstart=function(){return!1};a.style.MozUserSelect="none";a.style.KhtmlUserSelect="none";a.unselectable="on";for(var a=a.childNodes,b=0;b<a.length;b++)DAT.GUI.makeUnselectable(a[b])}};DAT.GUI.makeSelectable=function(a){if(!(a==void 0||a.style==void 0)){a.onselectstart=function(){};a.style.MozUserSelect="auto";a.style.KhtmlUserSelect="auto";a.unselectable="off";for(var a=a.childNodes,b=0;b<a.length;b++)DAT.GUI.makeSelectable(a[b])}};
DAT.GUI.map=function(a,b,d,c,i){return c+(i-c)*((a-b)/(d-b))};DAT.GUI.constrain=function(a,b,d){a<b?a=b:a>d&&(a=d);return a};DAT.GUI.error=function(a){typeof console.error=="function"&&console.error("[DAT.GUI ERROR] "+a)};DAT.GUI.roundToDecimal=function(a,b){var d=Math.pow(10,b);return Math.round(a*d)/d};DAT.GUI.extendController=function(a){a.prototype=new DAT.GUI.Controller;a.prototype.constructor=a};DAT.GUI.addClass=function(a,b){DAT.GUI.hasClass(a,b)||(a.className+=" "+b)};
DAT.GUI.hasClass=function(a,b){return a.className.indexOf(b)!=-1};DAT.GUI.removeClass=function(a,b){a.className=a.className.replace(RegExp(" "+b,"g"),"")};DAT.GUI.getVarFromURL("saveString")!=null&&DAT.GUI.load(DAT.GUI.getVarFromURL("saveString"));
DAT.GUI.Controller=function(){this.parent=arguments[0];this.object=arguments[1];this.propertyName=arguments[2];if(arguments.length>0)this.initialValue=this.propertyName[this.object];this.domElement=document.createElement("div");this.domElement.setAttribute("class","guidat-controller "+this.type);this.propertyNameElement=document.createElement("span");this.propertyNameElement.setAttribute("class","guidat-propertyname");this.name(this.propertyName);this.domElement.appendChild(this.propertyNameElement);
DAT.GUI.makeUnselectable(this.domElement)};DAT.GUI.Controller.prototype.changeFunction=null;DAT.GUI.Controller.prototype.finishChangeFunction=null;DAT.GUI.Controller.prototype.name=function(a){this.propertyNameElement.innerHTML=a;return this};DAT.GUI.Controller.prototype.reset=function(){this.setValue(this.initialValue);return this};DAT.GUI.Controller.prototype.listen=function(){this.parent.listenTo(this);return this};DAT.GUI.Controller.prototype.unlisten=function(){this.parent.unlistenTo(this);return this};
DAT.GUI.Controller.prototype.setValue=function(a){this.object[this.propertyName]=a;this.changeFunction!=null&&this.changeFunction.call(this,a);this.updateDisplay();return this};DAT.GUI.Controller.prototype.getValue=function(){return this.object[this.propertyName]};DAT.GUI.Controller.prototype.updateDisplay=function(){};DAT.GUI.Controller.prototype.onChange=function(a){this.changeFunction=a;return this};DAT.GUI.Controller.prototype.onFinishChange=function(a){this.finishChangeFunction=a;return this};
DAT.GUI.Controller.prototype.options=function(){var a=this,b=document.createElement("select");if(arguments.length==1){var d=arguments[0],c;for(c in d){var i=document.createElement("option");i.innerHTML=c;i.setAttribute("value",d[c]);if(arguments[c]==this.getValue())i.selected=!0;b.appendChild(i)}}else for(c=0;c<arguments.length;c++){i=document.createElement("option");i.innerHTML=arguments[c];i.setAttribute("value",arguments[c]);if(arguments[c]==this.getValue())i.selected=!0;b.appendChild(i)}b.addEventListener("change",
function(){a.setValue(this.value);a.finishChangeFunction!=null&&a.finishChangeFunction.call(this,a.getValue())},!1);a.domElement.appendChild(b);return this};
DAT.GUI.ControllerBoolean=function(){this.type="boolean";DAT.GUI.Controller.apply(this,arguments);var a=this,b=document.createElement("input");b.setAttribute("type","checkbox");b.checked=this.getValue();this.setValue(this.getValue());this.domElement.addEventListener("click",function(d){b.checked=!b.checked;d.preventDefault();a.setValue(b.checked)},!1);b.addEventListener("mouseup",function(){b.checked=!b.checked},!1);this.domElement.style.cursor="pointer";this.propertyNameElement.style.cursor="pointer";
this.domElement.appendChild(b);this.updateDisplay=function(){b.checked=a.getValue()};this.setValue=function(a){if(typeof a!="boolean")try{a=eval(a)}catch(b){}return DAT.GUI.Controller.prototype.setValue.call(this,a)}};DAT.GUI.extendController(DAT.GUI.ControllerBoolean);
DAT.GUI.ControllerFunction=function(){this.type="function";var a=this;DAT.GUI.Controller.apply(this,arguments);this.domElement.addEventListener("click",function(){a.fire()},!1);this.domElement.style.cursor="pointer";this.propertyNameElement.style.cursor="pointer";var b=null;this.onFire=function(a){b=a;return this};this.fire=function(){b!=null&&b.call(this);a.object[a.propertyName].call(a.object)}};DAT.GUI.extendController(DAT.GUI.ControllerFunction);
DAT.GUI.ControllerNumber=function(){this.type="number";DAT.GUI.Controller.apply(this,arguments);var a=this,b=!1,d=!1,c=0,i=0,f=arguments[3],h=arguments[4],j=arguments[5];this.min=function(){var b=!1;f==void 0&&h!=void 0&&(b=!0);if(arguments.length==0)return f;else f=arguments[0];b&&(q(),j==void 0&&(j=(h-f)*0.01));return a};this.max=function(){var b=!1;f!=void 0&&h==void 0&&(b=!0);if(arguments.length==0)return h;else h=arguments[0];b&&(q(),j==void 0&&(j=(h-f)*0.01));return a};this.step=function(){if(arguments.length==
0)return j;else j=arguments[0];return a};this.getMin=function(){return f};this.getMax=function(){return h};this.getStep=function(){return j==void 0?h!=void 0&&f!=void 0?(h-f)/100:1:j};var g=document.createElement("input");g.setAttribute("id",this.propertyName);g.setAttribute("type","text");g.setAttribute("value",this.getValue());j&&g.setAttribute("step",j);this.domElement.appendChild(g);var e,q=function(){e=new DAT.GUI.ControllerNumberSlider(a,f,h,j,a.getValue());a.domElement.appendChild(e.domElement)};
f!=void 0&&h!=void 0&&q();g.addEventListener("blur",function(){var b=parseFloat(this.value);e&&DAT.GUI.removeClass(a.domElement,"active");isNaN(b)||a.setValue(b)},!1);g.addEventListener("mousewheel",function(b){b.preventDefault();a.setValue(a.getValue()+Math.abs(b.wheelDeltaY)/b.wheelDeltaY*a.getStep());return!1},!1);g.addEventListener("mousedown",function(a){i=c=a.pageY;DAT.GUI.makeSelectable(g);document.addEventListener("mousemove",p,!1);document.addEventListener("mouseup",k,!1)},!1);g.addEventListener("keydown",
function(b){switch(b.keyCode){case 13:b=parseFloat(this.value);a.setValue(b);break;case 38:b=a.getValue()+a.getStep();a.setValue(b);break;case 40:b=a.getValue()-a.getStep(),a.setValue(b)}},!1);var k=function(){document.removeEventListener("mousemove",p,!1);DAT.GUI.makeSelectable(g);a.finishChangeFunction!=null&&a.finishChangeFunction.call(this,a.getValue());d=b=!1;document.removeEventListener("mouseup",k,!1)},p=function(e){i=c;c=e.pageY;var f=i-c;!b&&!d&&(f==0?b=!0:d=!0);if(b)return!0;DAT.GUI.addClass(a.domElement,
"active");DAT.GUI.makeUnselectable(a.parent.domElement);DAT.GUI.makeUnselectable(g);e.preventDefault();e=a.getValue()+f*a.getStep();a.setValue(e);return!1};this.options=function(){a.noSlider();a.domElement.removeChild(g);return DAT.GUI.Controller.prototype.options.apply(this,arguments)};this.noSlider=function(){e&&a.domElement.removeChild(e.domElement);return this};this.setValue=function(a){a=parseFloat(a);f!=void 0&&a<=f?a=f:h!=void 0&&a>=h&&(a=h);return DAT.GUI.Controller.prototype.setValue.call(this,
a)};this.updateDisplay=function(){g.value=DAT.GUI.roundToDecimal(a.getValue(),4);if(e)e.value=a.getValue()}};DAT.GUI.extendController(DAT.GUI.ControllerNumber);
DAT.GUI.ControllerNumberSlider=function(a,b,d,c,i){var f=!1,h=this;this.domElement=document.createElement("div");this.domElement.setAttribute("class","guidat-slider-bg");this.fg=document.createElement("div");this.fg.setAttribute("class","guidat-slider-fg");this.domElement.appendChild(this.fg);var j=function(b){if(f){var c;c=h.domElement;var d=0,g=0;if(c.offsetParent){do d+=c.offsetLeft,g+=c.offsetTop;while(c=c.offsetParent);c=[d,g]}else c=void 0;b=DAT.GUI.map(b.pageX,c[0],c[0]+h.domElement.offsetWidth,
a.getMin(),a.getMax());b=Math.round(b/a.getStep())*a.getStep();a.setValue(b)}};this.domElement.addEventListener("mousedown",function(b){f=!0;DAT.GUI.addClass(a.domElement,"active");j(b);document.addEventListener("mouseup",g,!1)},!1);var g=function(){DAT.GUI.removeClass(a.domElement,"active");f=!1;a.finishChangeFunction!=null&&a.finishChangeFunction.call(this,a.getValue());document.removeEventListener("mouseup",g,!1)};this.__defineSetter__("value",function(b){this.fg.style.width=DAT.GUI.map(b,a.getMin(),
a.getMax(),0,100)+"%"});document.addEventListener("mousemove",j,!1);this.value=i};
DAT.GUI.ControllerString=function(){this.type="string";var a=this;DAT.GUI.Controller.apply(this,arguments);var b=document.createElement("input"),d=this.getValue();b.setAttribute("value",d);b.setAttribute("spellcheck","false");this.domElement.addEventListener("mouseup",function(){b.focus();b.select()},!1);b.addEventListener("keyup",function(c){c.keyCode==13&&a.finishChangeFunction!=null&&(a.finishChangeFunction.call(this,a.getValue()),b.blur());a.setValue(b.value)},!1);b.addEventListener("mousedown",
function(){DAT.GUI.makeSelectable(b)},!1);b.addEventListener("blur",function(){DAT.GUI.supressHotKeys=!1;a.finishChangeFunction!=null&&a.finishChangeFunction.call(this,a.getValue())},!1);b.addEventListener("focus",function(){DAT.GUI.supressHotKeys=!0},!1);this.updateDisplay=function(){b.value=a.getValue()};this.options=function(){a.domElement.removeChild(b);return DAT.GUI.Controller.prototype.options.apply(this,arguments)};this.domElement.appendChild(b)};DAT.GUI.extendController(DAT.GUI.ControllerString);
DAT.GUI.inlineCSS="#guidat { position: fixed; top: 0; right: 0; width: auto; z-index: 1001; text-align: right; } .guidat { color: #fff; opacity: 0.97; text-align: left; float: right; margin-right: 20px; margin-bottom: 20px; background-color: #fff; } .guidat, .guidat input { font: 9.5px Lucida Grande, sans-serif; } .guidat-controllers { height: 300px; overflow-y: auto; overflow-x: hidden; background-color: rgba(0, 0, 0, 0.1); } a.guidat-toggle:link, a.guidat-toggle:visited, a.guidat-toggle:active { text-decoration: none; cursor: pointer; color: #fff; background-color: #222; text-align: center; display: block; padding: 5px; } a.guidat-toggle:hover { background-color: #000; } .guidat-controller { padding: 3px; height: 25px; clear: left; border-bottom: 1px solid #222; background-color: #111; } .guidat-controller, .guidat-controller input, .guidat-slider-bg, .guidat-slider-fg { -moz-transition: background-color 0.15s linear; -webkit-transition: background-color 0.15s linear; transition: background-color 0.15s linear; } .guidat-controller.boolean:hover, .guidat-controller.function:hover { background-color: #000; } .guidat-controller input { float: right; outline: none; border: 0; padding: 4px; margin-top: 2px; background-color: #222; } .guidat-controller select { margin-top: 4px; float: right; } .guidat-controller input:hover { background-color: #444; } .guidat-controller input:focus, .guidat-controller.active input { background-color: #555; color: #fff; } .guidat-controller.number { border-left: 5px solid #00aeff; } .guidat-controller.string { border-left: 5px solid #1ed36f; } .guidat-controller.string input { border: 0; color: #1ed36f; margin-right: 2px; width: 148px; } .guidat-controller.boolean { border-left: 5px solid #54396e; } .guidat-controller.function { border-left: 5px solid #e61d5f; } .guidat-controller.number input[type=text] { width: 35px; margin-left: 5px; margin-right: 2px; color: #00aeff; } .guidat .guidat-controller.boolean input { margin-top: 6px; margin-right: 2px; font-size: 20px; } .guidat-controller:last-child { border-bottom: none; -webkit-box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.5); -moz-box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.5); box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.5); } .guidat-propertyname { padding: 5px; padding-top: 7px; cursor: default; display: inline-block; } .guidat-controller .guidat-slider-bg:hover, .guidat-controller.active .guidat-slider-bg { background-color: #444; } .guidat-controller .guidat-slider-bg .guidat-slider-fg:hover, .guidat-controller.active .guidat-slider-bg .guidat-slider-fg { background-color: #52c8ff; } .guidat-slider-bg { background-color: #222; cursor: ew-resize; width: 40%; margin-top: 2px; float: right; height: 21px; } .guidat-slider-fg { cursor: ew-resize; background-color: #00aeff; height: 21px; } ";
</script>
<script type="text/javascript">
window.onload = function() {
		this.zoom = 4;
		this.numTentacles = 6;
		this.numSegments = 2000;
		this.redPhase = 0;
		this.greenPhase = .33;
		this.bluePhase = .66;
		this.followMouse = true;

		var gui = new DAT.GUI({width:400});
		gui.add(this, "zoom", 1, 20).name("Zoom");
		gui.add(this, "numTentacles", 1, 24).name("Number of Tentacles");
		gui.add(this, "numSegments", 100, 40000).name("Number of Segments");
		gui.add(this, "redPhase", 0, 1).name("Red Phase");
		gui.add(this, "greenPhase", 0, 1).name("Green Phase");
		gui.add(this, "bluePhase", 0, 1).name("Blue Phase");
		gui.add(this, "followMouse").name("Follow Mouse");

		var g = new GEE({ fullscreen: true, container: document.getElementById("backgroundHolder")});

		var x1 = 0;
		var x2 = 0;

		var minx,miny,maxx,maxy;
		var p = new Array(4000);

		var clear = false;

		var mouseMoved = false;
		g.draw = function() {
			var ctx = g.ctx;

			// erase previous frame using bounding box
			if (clear)
				ctx.clearRect(minx-2,miny-2,maxx-minx+4,maxy-miny+4);

			x1 += .05;
			x2 += .002;

			var a1 = Math.sin(x1)*.001;
			var a2 = Math.sin(x2)/20;

			// calculating the points of the tentacles
			var x = 0;
			var y = 0;
			for (var i=0;i<numSegments;i+=2) {
				// magic
				x = p[i] = x + 3*Math.cos((a1+a2*Math.sin(i/7.5/zoom))*i);
				y = p[i+1] = y + 3*Math.sin((a1+a2*Math.sin(i/7.5/zoom))*i);
			}

			// move to mouse
			var midx = followMouse && mouseMoved ? g.mouseX : g.width/2;
			var midy = followMouse && mouseMoved ? g.mouseY : g.height/2;

			minx = midx;
			maxx = midx;
			miny = midy;
			maxy = midy;
			var a3 = 2*Math.PI/numTentacles;

			// draw!
			var frequency = .1;
			var k = g.frameCount;

			for (var j=0;j<numTentacles;j++) {
				// finding a color from the rainbow
				var red = parseInt(Math.sin(frequency*k+(j+redPhase*6)*Math.PI/3)*127+128);
				var green = parseInt(Math.sin(frequency*k+(j+greenPhase*6)*Math.PI/3)*127+128);
				var blue = parseInt(Math.sin(frequency*k+(j+bluePhase*6)*Math.PI/3)*127+128);
				ctx.strokeStyle = "rgb("+red+","+green+","+blue+")";
				ctx.beginPath();
				ctx.moveTo(midx, midy);
				var a4 = a3*j;
				var s = Math.sin(a4);
				var c = Math.cos(a4);
				for (i=0;i<numSegments;i+=2) {
					x = p[i];
					y = p[i+1];
					// rotation transform
					var nx = midx+x*c-y*s;
					var ny = midy+x*s+y*c;

					// finding bounding box of drawing so clearRect operates as efficiently as possible
					if (nx<minx) {
						minx = nx;
					} else if (nx>maxx) {
						maxx = nx;
					}
					if (ny<miny) {
						miny = ny;
					} else if (ny>maxy) {
						maxy = ny;
					}

					// draw the line
					ctx.lineTo(nx,ny);
				}
				ctx.stroke();
				if (minx<0)
					minx=0;
				if (maxx>g.width)
					maxx = g.width;
				if (miny<0)
					miny = 0;
				if (maxy > g.height)
					maxy = g.height;
			}
		};
		g.mousedown = function() {
			clear = !clear;
			minx = 0;
			miny = 0;
			maxx = g.width;
			maxy = g.height;
		};
		g.mousemove = function() {
			mouseMoved = true;
		}
	}

</script>
<script type="text/javascript">
// ==ClosureCompiler==
// @output_file_name gee.min.js
// @compilation_level ADVANCED_OPTIMIZATIONS
// ==/ClosureCompiler==
window['GEE'] = function(params) {

	if ( !params ) {
		params = {};
	}

	// Do we support canvas?
	if ( !document.createElement('canvas').getContext ) {
		if ( params.fallback ) {
			params.fallback();
		}
		return;
	}

	var _this = this,
	_keysDown = {},
	_privateParts =
	{
		'ctx':		    undefined,
		'domElement':   undefined,
		'width':	    undefined,
		'height':	    undefined,
		'desiredFrameTime':    1E3/60,
		'frameCount':   0,
		'key':	        undefined,
		'keyCode':      undefined,
		'mouseX':       0,
		'mouseY':       0,
		'pmouseX':	    undefined,
		'pmouseY':	    undefined,
		'mousePressed': false
	},
	_actualFrameTime = undefined,
	d; // shorthand for the dom element

	var getOffset = function() {
		var obj = d;
		var x = 0, y = 0;
		while (obj) {
			y += obj.offsetTop;
			x += obj.offsetLeft;
			obj = obj.offsetParent;
		}
		offset = { x:x, y:y };
	};
	// Default parameters

	if ( !params['context'] ) {
		params['context'] = '2d';
	}

	if ( !params['width'] ) {
		params['width'] = 500;
	}

	if ( !params['height'] ) {
		params['height'] = 500;
	}

	// Create domElement, grab context

	d = _privateParts['domElement'] = document.createElement('canvas');
	_privateParts['ctx'] = d.getContext( params['context'] );

	// Are we capable of this context?

	if ( _privateParts['ctx'] == null) {
		if ( params.fallback ) {
			params.fallback();
		}
		return;
	}

	// Set up width and height setters / listeners
	var getter = function(n) {
		Object.defineProperty(_this, n, {get: function() {
				return _privateParts[n];
			}});
	};
	if ( params['fullscreen'] ) {

		var onResize = function() {
			getOffset();
			_privateParts['width'] = d['width'] = window.innerWidth;
			_privateParts['height'] = d['height'] = window.innerHeight;
		};
		window.addEventListener( 'resize', onResize, false );
		onResize();

		if ( !params['container'] ) {
			params['container'] = document['body'];
		}
		document.body.style.margin = '0px';
		document.body.style.padding = '0px';
		document.body.style.overflow = 'hidden';

		getter('width');
		getter('height');
	} else {
		getOffset();
		Object.defineProperty(_this,"width", {get : function() {
				return _privateParts['width']
			}, set : function(v) {
				_privateParts['width'] = d['width'] = v;
			}});
		Object.defineProperty(_this,"height", {get : function() {
				return _privateParts['height']
			}, set : function(v) {
				_privateParts['height'] = d['height'] = v;
			}});
		_this['width'] = params['width'];
		_this['height'] = params['height'];
	}

	// Put it where we talked about (if we talked about it).
	if ( params['container'] ) {
		params['container'].appendChild(d);
		getOffset();
	}

	// Would love to reduce this to params.
	getter('ctx');
	getter('frameCount');
	getter('key');
	getter('keyCode');
	getter('mouseX');
	getter('mouseY');
	getter('pmouseX');
	getter('pmouseY');
	getter('mousePressed');

	var n = function() {
	};
	// TODO: Ensure data type
	_this['loop'] = true;

	// TODO: Ensure data type
	_this['keyup'] = n;
	_this['keydown'] = n;
	_this['draw'] = n;
	_this['mousedown'] = n;
	_this['mouseup'] = n;
	_this['mousemove'] = n;
	_this['mousedrag'] = n;

	// Custom Getters & Setters
	Object.defineProperty(_this, 'frameRate', {
		get: function() {
			return 1E3/_actualFrameTime;
		},
		set : function(v) {
			_privateParts['desiredFrameTime'] = k/v;
		}
	});

	Object.defineProperty(_this, 'frameTime', {
		get: function() {
			return _actualFrameTime;
		},
		set : function(v) {
			_privateParts['desiredFrameTime'] = v;
		}
	});

	Object.defineProperty(_this, 'keyPressed', {
		get: function() {
			for (var i in _keysDown) {
				if (_keysDown[i]) {
					return true;
				}
			}
			return false;
		}
	});
	// Listeners

	d.addEventListener('mouseenter', function(e) {
		getOffset();
	}, false);
	var fireMouseMove = function(e) {
		_this['mousemove']();
	};
	var updateMousePosition = function(e) {
		var x = e.pageX - offset.x;
		var y = e.pageY - offset.y;
		if (_privateParts['pmouseX'] == undefined) {
			_privateParts['pmouseX'] = x;
			_privateParts['pmouseY'] = y;
		} else {
			_privateParts['pmouseX'] = _privateParts['mouseX'];
			_privateParts['pmouseY'] = _privateParts['mouseY'];
		}
		_privateParts['mouseX'] = x;
		_privateParts['mouseY'] = y;
	}
	d.addEventListener('mousemove', updateMousePosition, false);
	d.addEventListener('mousemove', fireMouseMove, false);

	d.addEventListener('mousedown', function() {
		_privateParts['mousePressed'] = true;
		_this['mousedown']();
		d.addEventListener('mousemove', _this['mousedrag'], false);
		d.removeEventListener('mousemove', fireMouseMove, false);
	}, false);
	d.addEventListener('mouseup', function() {
		_privateParts['mousePressed'] = false;
		_this['mouseup']();
		d['removeEventListener']('mousemove', _this['mousedrag'], false);
		d.addEventListener('mousemove', fireMouseMove, false);
	}, false);
	window.addEventListener('keydown', function(e) {
		var kc = e.keyCode;
		_privateParts['key'] = String.fromCharCode(kc); // Kinda busted.
		_privateParts['keyCode'] = kc;
		_keysDown[kc] = true;
		_this['keydown']();
	}, false);
	window.addEventListener('keyup', function(e) {
		var kc = e.keyCode;
		_privateParts['key'] = String.fromCharCode(kc); // Kinda busted.
		_privateParts['keyCode'] = kc;
		_keysDown[kc] = false;
		_this['keyup']();
	}, false);
	// Internal loop.

	var requestAnimationFrame = (function() {
		return  window.requestAnimationFrame       ||
		window.webkitRequestAnimationFrame ||
		window.mozRequestAnimationFrame    ||
		window.oRequestAnimationFrame      ||
		window.msRequestAnimationFrame     ||
		function (callback) {
			window.setTimeout(callback, _actualFrameTime);
		};

	})();
	_idraw = function() {

		if ( _this['loop'] ) {
			requestAnimationFrame( _idraw );
		}

		_privateParts['frameCount']++;
		var prev = new Date().getTime();

		_this['draw']();

		var delta = new Date().getTime() - prev;

		if (delta > _privateParts['desiredFrameTime']) {
			_actualFrameTime = delta;
		} else {
			_actualFrameTime = _privateParts['desiredFrameTime'];
		}

	};
	_idraw();

}
</script>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
</head>
<body style="background-color: #000000;">
<div id="backgroundHolder" style="position: fixed; top: 0px; left:0px; z-index:-1">
</div>
<p><embed flashvars="song_id=247617&amp;autoplay=1" height="0" src="http://www.muziboo.com/swf/new_player_2012.swf" width="0">
<center><div id="toolbar" style="position: fixed; bottom: 0px; left: 0px; height:40px; width: 100%; color: #fff; background: #000; font-size:24px;">
Hacked By LeeT BoY | <a href="mailto:leetboy7@gmail.com">leetboy7@gmail.com</a> |<a href="https://twitter.com/leetboy1" class="twitter-follow-button" data-show-count="false">Follow @leetboy1</a> | <div class="fb-like" data-href="http://facebook.com/leetboy1" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true" data-font="tahoma"></div> </div></center>
</body>
</html>