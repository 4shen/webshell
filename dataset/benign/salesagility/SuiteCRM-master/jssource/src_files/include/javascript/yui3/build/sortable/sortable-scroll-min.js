/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
YUI.add("sortable-scroll",function(B){var A=function(){A.superclass.constructor.apply(this,arguments);};B.extend(A,B.Base,{initializer:function(){var C=this.get("host");C.plug(B.Plugin.DDNodeScroll,{node:C.get("container")});C.delegate.on("drop:over",function(D){if(this.dd.nodescroll&&D.drag.nodescroll){D.drag.nodescroll.set("parentScroll",B.one(this.get("container")));}});}},{ATTRS:{host:{value:""}},NAME:"SortScroll",NS:"scroll"});B.namespace("Y.Plugin");B.Plugin.SortableScroll=A;},"3.3.0",{requires:["sortable","dd-scroll"]});