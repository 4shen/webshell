/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
YUI.add("yql",function(B){var A=function(E,F,D,C){if(!D){D={};}D.q=E;if(!D.format){D.format=B.YQLRequest.FORMAT;}if(!D.env){D.env=B.YQLRequest.ENV;}this._params=D;this._opts=C;this._callback=F;};A.prototype={_jsonp:null,_opts:null,_callback:null,_params:null,send:function(){var C="",D=((this._opts&&this._opts.proto)?this._opts.proto:B.YQLRequest.PROTO);B.each(this._params,function(G,F){C+=F+"="+encodeURIComponent(G)+"&";});D+=((this._opts&&this._opts.base)?this._opts.base:B.YQLRequest.BASE_URL)+C;var E=(!B.Lang.isFunction(this._callback))?this._callback:{on:{success:this._callback}};if(E.allowCache!==false){E.allowCache=true;}if(!this._jsonp){this._jsonp=B.jsonp(D,E);}else{this._jsonp.url=D;if(E.on&&E.on.success){this._jsonp._config.on.success=E.on.success;}this._jsonp.send();}return this;}};A.FORMAT="json";A.PROTO="http";A.BASE_URL=":/"+"/query.yahooapis.com/v1/public/yql?";A.ENV="http:/"+"/datatables.org/alltables.env";B.YQLRequest=A;B.YQL=function(D,E,C){return new B.YQLRequest(D,E,C).send();};},"3.3.0",{requires:["jsonp"]});