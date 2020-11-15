mw.require('libs/cssjson/cssjson.js');


mw.liveeditCSSEditor = function (config) {
    var scope = this;
    config = config || {};
    config.document = config.document || document;
    var node = document.querySelector('link[href*="live_edit"]');
    var defaults = {
        cssUrl: node ? node.href : null,
        saveUrl: mw.settings.api_url + "current_template_save_custom_css"
    };
    this.settings = $.extend({}, defaults, config);

    this.json = null;

    this.getByUrl = function (url, callback) {
        return $.get(url, function (css) {
            callback.call(this, css)
        });
    };

    this.getLiveeditCSS = function () {
        if( this.settings.cssUrl ) {
            this.getByUrl( this.settings.cssUrl, function (css) {
                if(/<\/?[a-z][\s\S]*>/i.test(css)) {
                    scope.json = {};
                    scope._css = '';
                } else {
                    scope.json = CSSJSON.toJSON(css);
                    scope._css = css;
                }
                $(scope).trigger('ready');
            });
        }
        else {
            scope.json = {};
            scope._css = '';
            $(scope).trigger('ready');
        }
    };


    this._cssTemp = function (json) {
        var css = CSSJSON.toCSS(json);
        if(!mw.liveedit._cssTemp) {
            mw.liveedit._cssTemp = mw.tools.createStyle('#mw-liveedit-dynamic-temp-style', css, document.body);
            mw.liveedit._cssTemp.id = 'mw-liveedit-dynamic-temp-style';
        } else {
            mw.liveedit._cssTemp.innerHTML = css;
        }
    };

    this.changed = false;
    this._temp = {children: {}, attributes: {}};
    this.temp = function (node, prop, val) {
        this.changed = true;
        var sel = mw.tools.generateSelectorForNode(node);
        if(!this._temp.children[sel]) {
            this._temp.children[sel] = {};
        }
        if (!this._temp.children[sel].attributes ) {
            this._temp.children[sel].attributes = {};
        }
        this._temp.children[sel].attributes[prop] = val;
        this._cssTemp(this._temp);
    };

    this.timeOut = null;

    this.save = function () {
        this.json = $.extend(true, {}, this.json, this._temp);
        this._css = CSSJSON.toCSS(this.json).replace(/\.\./g, '.').replace(/\.\./g, '.');
    };

    this.publish = function (callback) {
        var css = {
            css_file_content: this.getValue()
        };
        $.post(this.settings.saveUrl, css, function (res) {
            scope.changed = false;
            if(callback) {
                callback.call(this, res);
            }
        });
    };

    this.publishIfChanged = function (callback) {
        if(this.changed) {
            this.publish(callback);
        }
    };

    this.getValue = function () {
        this.save();
        return this._css;
    };

    this.init = function () {
        this.getLiveeditCSS();
    };

    this.init();

};

