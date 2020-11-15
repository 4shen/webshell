/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
YUI.add("jsonp",function(c){var b=c.Lang.isFunction;function a(){this._init.apply(this,arguments);}a.prototype={_requests:0,_init:function(d,f){this.url=d;f=(b(f))?{on:{success:f}}:f||{};var e=f.on||{};if(!e.success){e.success=this._defaultCallback(d,f);}this._config=c.merge({context:this,args:[],format:this._format,allowCache:false},f,{on:e});},_defaultCallback:function(){},send:function(){var d=this,g=c.Array(arguments,0,true),f=d._config,h=d._proxy||c.guid(),e;if(f.allowCache){d._proxy=h;d._requests++;}g.unshift(d.url,"YUI.Env.JSONP."+h);e=f.format.apply(d,g);if(!f.on.success){return d;}function i(j){return(b(j))?function(k){if(!f.allowCache||!--d._requests){delete YUI.Env.JSONP[h];}j.apply(f.context,[k].concat(f.args));}:null;}YUI.Env.JSONP[h]=i(f.on.success);c.Get.script(e,{onFailure:i(f.on.failure),onTimeout:i(f.on.timeout),timeout:f.timeout});return d;},_format:function(d,e){return d.replace(/\{callback\}/,e);}};c.JSONPRequest=a;c.jsonp=function(d,f){var e=new c.JSONPRequest(d,f);return e.send.apply(e,c.Array(arguments,2,true));};if(!YUI.Env.JSONP){YUI.Env.JSONP={};}},"3.3.0",{requires:["get","oop"]});