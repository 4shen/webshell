/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
YUI.add("dd-plugin",function(B){var A=function(C){C.node=((B.Widget&&C.host instanceof B.Widget)?C.host.get("boundingBox"):C.host);A.superclass.constructor.call(this,C);};A.NAME="dd-plugin";A.NS="dd";B.extend(A,B.DD.Drag);B.namespace("Plugin");B.Plugin.Drag=A;},"3.3.0",{requires:["dd-drag"],skinnable:false,optional:["dd-constrain","dd-proxy"]});