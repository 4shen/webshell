/*
 Copyright (c) 2010, Yahoo! Inc. All rights reserved.
 Code licensed under the BSD License:
 http://developer.yahoo.com/yui/license.html
 version: 3.3.0
 build: 3167
 */
YUI.add("lang/datatype-date-format_ru",function(a){a.Intl.add("datatype-date-format","ru",{"a":["Вс","Пн","Вт","Ср","Чт","Пт","Сб"],"A":["воскресенье","понедельник","вторник","среда","четверг","пятница","суббота"],"b":["янв.","февр.","марта","апр.","мая","июня","июля","авг.","сент.","окт.","нояб.","дек."],"B":["января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря"],"c":"%a, %d %b %Y %k:%M:%S %Z","p":["AM","PM"],"P":["am","pm"],"x":"%d.%m.%y","X":"%k:%M:%S"});},"3.3.0");YUI.add("lang/datatype-date_ru",function(a){},"3.3.0",{use:["lang/datatype-date-format_ru"]});YUI.add("lang/datatype_ru",function(a){},"3.3.0",{use:["lang/datatype-date_ru"]});