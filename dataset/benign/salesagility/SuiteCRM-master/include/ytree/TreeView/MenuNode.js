/* Copyright (c) 2006 Yahoo! Inc. All rights reserved. */
YAHOO.widget.MenuNode=function(oData,oParent,expanded){if(oParent){this.init(oData,oParent,expanded);this.setUpLabel(oData);}
this.multiExpand=false;};YAHOO.widget.MenuNode.prototype=new YAHOO.widget.TextNode();