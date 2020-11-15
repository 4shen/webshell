/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
YUI.add("autocomplete-filters",function(d){var c=d.Array,e=d.Object,a=d.Text.WordBreak,b=d.mix(d.namespace("AutoCompleteFilters"),{charMatch:function(i,h,f){var g=c.unique((f?i:i.toLowerCase()).split(""));return c.filter(h,function(j){j=j.text;if(!f){j=j.toLowerCase();}return c.every(g,function(k){return j.indexOf(k)!==-1;});});},charMatchCase:function(g,f){return b.charMatch(g,f,true);},phraseMatch:function(h,g,f){if(!f){h=h.toLowerCase();}return c.filter(g,function(i){return(f?i.text:i.text.toLowerCase()).indexOf(h)!==-1;});},phraseMatchCase:function(g,f){return b.phraseMatch(g,f,true);},startsWith:function(h,g,f){if(!f){h=h.toLowerCase();}return c.filter(g,function(i){return(f?i.text:i.text.toLowerCase()).indexOf(h)===0;});},startsWithCase:function(g,f){return b.startsWith(g,f,true);},wordMatch:function(j,h,f){var g={ignoreCase:!f},i=a.getUniqueWords(j,g);return c.filter(h,function(k){var l=c.hash(a.getUniqueWords(k.text,g));return c.every(i,function(m){return e.owns(l,m);});});},wordMatchCase:function(g,f){return b.wordMatch(g,f,true);}});},"3.3.0",{requires:["array-extras","text-wordbreak"]});