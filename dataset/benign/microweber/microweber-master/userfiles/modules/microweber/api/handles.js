mw.require('selector.js');

var dynamicModulesMenuTime = null;
var dynamicModulesMenu = function(e, el) {
    if(!mw.inaccessibleModules){
        mw.inaccessibleModules = document.createElement('div');
        mw.inaccessibleModules.className = 'mw-ui-btn-nav mwInaccessibleModulesMenu';
        document.body.appendChild(mw.inaccessibleModules);
        mw.$(mw.inaccessibleModules).on('mouseenter', function(){
            this._hovered = true;
        }).on('mouseleave', function(){
            this._hovered = false;
        });
    }

    var $el;
    if(e.type === 'moduleOver' || e.type === 'ModuleClick'){

        var parentModule = mw.tools.lastParentWithClass(el, 'module');
        var childModule = mw.tools.firstChildWithClass(el, 'module');

        $el = mw.$(el);
        if(!!parentModule && ( $el.offset().top - mw.$(parentModule).offset().top) < 10 ){
            el.__disableModuleTrigger = parentModule;
            $el.addClass('inaccessibleModule');
        }
        else if(!!childModule && ( mw.$(childModule).offset().top - $el.offset().top) < 10 ) {
            childModule.__disableModuleTrigger = el;
            mw.$(childModule).addClass('inaccessibleModule');
        }
        else{
            $el.removeClass('inaccessibleModule');
        }
    }


    var modules = mw.$(".inaccessibleModule", el);
    if(modules.length === 0){
        var parent = mw.tools.firstParentWithClass(el, 'module');
        if(parent){
            if(($el.offset().top - mw.$(parent).offset().top) < 10) {
                modules = mw.$([el]);
                el = parent;
                $el = mw.$(el);

            }
        }
    }
    if (e.type === 'ModuleClick') {
        mw.liveEditSelector.select(el);
    }

    if(modules.length && !mw.inaccessibleModules._hovered) {
        mw.inaccessibleModules.innerHTML = '';
    }
    modules.each(function(){
        var span = document.createElement('span');
        span.className = 'mw-handle-item mw-ui-btn mw-ui-btn-small';
        var type = mw.$(this).attr('data-type') || mw.$(this).attr('type');
        if(type){
            var title = mw.live_edit.registry[type] ? mw.live_edit.registry[type].title : type;
            title = title.replace(/\_/, ' ');
            span.innerHTML = mw.live_edit.getModuleIcon(type) + title;
            var el = this;
            span.onclick = function(){
                mw.tools.module_settings(el);
            };
            mw.inaccessibleModules.appendChild(span);
        }
    });
    if(modules.length > 0){
        var off = mw.$(el).offset();
        if(mw.tools.collision(el, mw.handleModule.wrapper)){
            off.top = parseFloat(mw.handleModule.wrapper.style.top) + 30;
            off.left = parseFloat(mw.handleModule.wrapper.style.left);
        }
        mw.inaccessibleModules.style.top = off.top + 'px';
        mw.inaccessibleModules.style.left = off.left + 'px';
        clearTimeout(dynamicModulesMenuTime);
        mw.$(mw.inaccessibleModules).show();
    }
    else{
        dynamicModulesMenuTime = setTimeout(function(){
            if(!mw.inaccessibleModules._hovered) {
                mw.$(mw.inaccessibleModules).hide();
            }

        }, 3000);

    }


    return $el[0];

};

var handleDomtreeSync = {};

mw.Handle = function(options) {

    this.options = options || {};

    var scope = this;

    this._visible = true;
    this.visible = function () {
        return this._visible;
    };

    this.createWrapper = function() {
        this.wrapper = mwd.createElement('div');
        this.wrapper.id = this.options.id || ('mw-handle-' + mw.random());
        this.wrapper.className = 'mw-defaults mw-handle-item ' + (this.options.className || 'mw-handle-type-default');
        this.wrapper.contenteditable = false;
        mw.$(this.wrapper).on('mousedown', function () {
            mw.tools.addClass(this, 'mw-handle-item-mouse-down');
        });
        mw.$(document).on('mouseup', function () {
            mw.tools.removeClass(scope.wrapper, 'mw-handle-item-mouse-down');
        });
        mwd.body.appendChild(this.wrapper);
    };

    this.create = function() {
        this.createWrapper();
        this.createHandler();
        this.createMenu();
    };

    this.setTitle = function (icon, title) {
        this.handleIcon.innerHTML = icon;
        this.handleTitle.innerHTML = title;
    };

    this.hide = function () {
        mw.$(this.wrapper).hide().removeClass('active');
        this._visible = false;
        return this;
    };

    this.show = function () {
        mw.$(this.wrapper).show();
        this._visible = true;
        return this;
    };

    this.createHandler = function(){
        this.handle = mwd.createElement('span');
        this.handleIcon = mwd.createElement('span');
        this.handleTitle = mwd.createElement('span');
        this.handle.className = 'mw-handle-handler';
        this.handleIcon.className = 'mw-handle-handler-icon';
        this.handleTitle.className = 'mw-handle-handler-title';

        this.handle.appendChild(this.handleIcon);
        this.handle.appendChild(this.handleTitle);
        this.wrapper.appendChild(this.handle);

        this.handleTitle.onclick = function () {
            mw.$(scope.wrapper).toggleClass('active');
        };
        mw.$(mwd.body).on('click', function (e) {
            if(!mw.tools.hasParentWithId(e.target, scope.wrapper.id)){
                mw.$(scope.wrapper).removeClass('active');
            }
        });
    };

    this.menuButton = function (data) {
        var btn = mwd.createElement('span');
        btn.className = 'mw-handle-menu-item';
        if(data.icon) {
            var icon = mwd.createElement('span');
            icon.className = data.icon + ' mw-handle-menu-item-icon';
            btn.appendChild(icon);
        }
        btn.appendChild(mwd.createTextNode(data.title));
        if(data.className){
            btn.className += (' ' + data.className);
        }
        if(data.id){
            btn.id = data.id;
        }
        if(data.action){
            btn.onmousedown = function (e) {
                e.preventDefault();
            };
            btn.onclick = function (e) {
                e.preventDefault();
                data.action.call(scope, e, this, data);
            };
        }
        return btn;
    };

    this._defaultButtons = [

    ];

    this.createMenuDynamicHolder = function(item){
        var dn = mwd.createElement('div');
        dn.className = 'mw-handle-menu-dynamic' + (item.className ? ' ' + item.className : '');
        return dn;
    };
    this.createMenu = function(){
        this.menu = mwd.createElement('div');
        this.menu.className = 'mw-handle-menu ' + (this.options.menuClass ? this.options.menuClass : 'mw-handle-menu-default');
        if (this.options.menu) {
            for (var i = 0; i < this.options.menu.length; i++) {
                if(this.options.menu[i].title !== '{dynamic}') {
                    this.menu.appendChild(this.menuButton(this.options.menu[i])) ;
                }
                else {
                    this.menu.appendChild(this.createMenuDynamicHolder(this.options.menu[i])) ;
                }

            }
        }
        this.wrapper.appendChild(this.menu);
    };
    this.create();
    this.hide();
};

mw._activeModuleOver = {
    module: null,
    element: null
};

mw._initHandles = {
    getNodeHandler:function (node) {
        if(mw._activeElementOver === node){
            return mw.handleElement
        } else if(mw._activeModuleOver === node) {
            return mw.handleModule
        } else if(mw._activeRowOver === node) {
            return mw.handleColumns;
        }
    },
    getAllNodes: function (but) {
        var all = [
            mw._activeModuleOver,
            mw._activeRowOver,
            mw._activeElementOver
        ];
        all = all.filter(function (item) {
            return !!item && item.nodeType === 1;
        });
        return all;
    },
    getAll: function (but) {
        var all = [
            mw.handleModule,
            mw.handleColumns,
            mw.handleElement
        ];
        all = but ? all.filter(function (x) {
            return x !== but;
        }) :  all;
        return all.filter(function (item) {
            if(item){
                return item.visible();
            }

        });
    },
    hideAll:function (but) {
        this.getAll(but).forEach(function (item) {
            item.hide();
        });
    },
    collide: function(a, b) {
        return !(
            ((a.y + a.height) < (b.y)) ||
            (a.y > (b.y + b.height)) ||
            ((a.x + a.width) < b.x) ||
            (a.x > (b.x + b.width))
        );
    },
    _manageCollision: false,
    manageCollision:function () {

        var scope = this,
            max = 35,
            skip = [];

        scope.getAll().forEach(function (curr) {
            var master = curr, masterRect;
            //if (skip.indexOf(master) === -1){
            scope.getAll(curr).forEach(function (item) {
                masterRect = master.wrapper.getBoundingClientRect();
                var irect = item.wrapper.getBoundingClientRect();
                if (scope.collide(masterRect, irect)) {
                    skip.push(item);
                    var topMore = item === mw.handleElement ? 10 : 0;
                    item.wrapper.style.top = (parseInt(master.wrapper.style.top, 10) + topMore) + 'px';
                    item.wrapper.style.left = ((parseInt(master.wrapper.style.left, 10) + masterRect.width) + 10) + 'px';
                    master = curr;
                }
            });
        });

        var cloner = mwd.querySelector('.mw-cloneable-control');
        if(cloner) {
            scope.getAll().forEach(function (curr) {
                masterRect = curr.wrapper.getBoundingClientRect();
                var clonerect = cloner.getBoundingClientRect();

                if (scope.collide(masterRect, clonerect)) {
                    cloner.style.top = curr.wrapper.style.top;
                    cloner.style.left = ((parseInt(curr.wrapper.style.left, 10) + masterRect.width) + 10) + 'px';
                }
            });
        }
    },

    elements: function(){
        mw.handleElement = new mw.Handle({
            id: 'mw-handle-item-element',
            className:'mw-handle-type-element',
            menu:[
                {
                    title: 'Edit HTML',
                    icon: 'mw-icon-code',
                    action: function () {
                        mw.editSource(mw._activeElementOver);
                    }
                },
                {
                    title: 'Edit Style',
                    icon: 'mw-icon-edit',
                    action: function () {
                        mw.liveEditSettings.show();
                        mw.sidebarSettingsTabs.set(3);
                        if(mw.cssEditorSelector){
                            mw.liveEditSelector.active(true);
                            mw.liveEditSelector.select(mw._activeElementOver);
                        } else{
                            mw.$(mw.liveEditWidgets.cssEditorInSidebarAccordion()).on('load', function () {
                                setTimeout(function(){
                                    mw.liveEditSelector.active(true);
                                    mw.liveEditSelector.select(mw._activeElementOver);
                                }, 333);
                            });
                        }
                        mw.liveEditWidgets.cssEditorInSidebarAccordion();
                    }
                },
                {
                    title: 'Remove',
                    icon: 'mw-icon-bin',
                    className:'mw-handle-remove',
                    action: function () {
                        mw.drag.delete_element(mw._activeElementOver);
                        mw.handleElement.hide()
                    }
                }
            ]
        });

        mw.$(mw.handleElement.wrapper).draggable({
            handle: mw.handleElement.handleIcon,
            cursorAt: {
                //top: -30
            },
            start: function() {
                mw.isDrag = true;
                mw.dragCurrent = mw.ea.data.currentGrabbed = mw._activeElementOver;

                handleDomtreeSync.start = mw.dragCurrent.parentNode;

                if(!mw.dragCurrent.id){
                    mw.dragCurrent.id = 'element_' + mw.random();
                }
                mw.$(mw.dragCurrent).invisible().addClass("mw_drag_current");
                mw.trigger("AllLeave");
                mw.drag.fix_placeholders();
                mw.$(mwd.body).addClass("dragStart");
                mw.image_resizer._hide();
                mw.wysiwyg.change(mw.dragCurrent);
                mw.smallEditor.css("visibility", "hidden");
                mw.smallEditorCanceled = true;
            },
            stop: function() {
                mw.$(mwd.body).removeClass("dragStart");

                if(mw.liveEditDomTree) {
                    mw.liveEditDomTree.refresh(handleDomtreeSync.start)
                }
            }
        });

        mw.$(mw.handleElement.wrapper).mouseenter(function() {
        }).click(function() {
            if (!$(mw._activeElementOver).hasClass("element-current")) {
                mw.$(".element-current").removeClass("element-current");

                if (mw._activeElementOver.nodeName === 'IMG') {

                    mw.trigger("ImageClick", mw._activeElementOver);
                } else {
                    mw.trigger("ElementClick", mw._activeElementOver);
                }
            }

        });

        mw.on("ElementOver", function(a, element) {
            mw._activeElementOver = element;
            mw.$(".mw_edit_delete, .mw_edit_delete_element, .mw-sorthandle-moveit, .column_separator_title").show();
            if (!mw.ea.canDrop(element)) {
                mw.$(".mw_edit_delete, .mw_edit_delete_element, .mw-sorthandle-moveit, .column_separator_title").hide();
                return false;
            }
            var el = mw.$(element);

            var o = el.offset();

            var pleft = parseFloat(el.css("paddingLeft"));
            var left_spacing = o.left;
            if (mw.tools.hasClass(element, 'jumbotron')) {
                left_spacing = left_spacing + pleft;
            }
            if(left_spacing<0){
                left_spacing = 0;
            }
            //todo: another icon
            var isSafe = false; // mw.tools.parentsOrCurrentOrderMatchOrOnlyFirst(element, ['safe-mode', 'regular-mode']);
            var _icon = isSafe ? '<svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 504.03 440" height="17" class="safe-element-svg"><path fill="green" d="M252,2.89C178.7,2.89,102.4,19.44,102.4,19.44A31.85,31.85,0,0,0,76.76,50.69v95.59c0,165.67,159.7,234.88,159.7,234.88A31.65,31.65,0,0,0,252,385.27a32.05,32.05,0,0,0,15.56-4.11c.06,0,159.69-69.21,159.69-234.88V50.69a31.82,31.82,0,0,0-25.64-31.25S325.33,2.89,252,2.89Zm95.59,95.59a15.94,15.94,0,0,1,11.26,27.2L238.45,246.11a16,16,0,0,1-11.33,4.73,15.61,15.61,0,0,1-11.2-4.73l-55-55a15.93,15.93,0,0,1,22.53-22.53l43.69,43.82L336.34,103.15a16,16,0,0,1,11.27-4.67Zm0,0"/></svg>' : '<span class="mw-icon-drag"></span>';

            var icon = '<span class="mw-handle-element-title-icon '+(isSafe ? 'tip' : '')+'"  '+(isSafe ? ' data-tip="Current element is protected \n  from accidental deletion" data-tipposition="top-left"' : '')+' >'+ _icon +'</span>';

            var title = '';

            mw.handleElement.setTitle(icon, title);

            if(el.hasClass('allow-drop')){
                mw.handleElement.hide();
            } else{
                mw.handleElement.show();
            }


            mw.$(mw.handleElement.wrapper).css({
                top: o.top - 10,
                left: left_spacing
            }).removeClass('active');

            if(!element.id) {
                element.id = "element_" + mw.random();
            }

            mw.dropable.removeClass("mw_dropable_onleaveedit");
            mw._initHandles.manageCollision();

        });


    },
    modules: function () {

        var handlesModuleConfig = {
            id: 'mw-handle-item-module',
            menu:[
                {
                    title: 'Settings',
                    icon: 'mw-icon-gear',
                    action: function () {
                        mw.drag.module_settings(mw._activeModuleOver,"admin");
                        mw.handleModule.hide();
                    }
                },
                {
                    title: 'Move Up',
                    icon: 'mw-icon-arrow-up-b',
                    className:'mw_handle_module_up',
                    action: function () {
                        mw.drag.replace($(mw._activeModuleOver), 'prev');
                        mw.handleModule.hide()
                    }
                },
                {
                    title: 'Move Down',
                    icon: 'mw-icon-arrow-down-b',
                    className:'mw_handle_module_down',
                    action: function () {
                        mw.drag.replace($(mw._activeModuleOver), 'next');
                        mw.handleModule.hide()
                    }
                },
                {
                    title: '{dynamic}',
                    className:'mw_handle_module_submodules'
                },
                {
                    title: '{dynamic}',
                    className:'mw_handle_module_spacing'
                },


                {
                    title: 'Reset',
                    icon: 'mw-icon-reload',
                    className:'mw-handle-remove',
                    action: function () {
                        if(mw._activeModuleOver && mw._activeModuleOver.id){
                            mw.tools.confirm_reset_module_by_id(mw._activeModuleOver.id)
                        }
                    }
                },
                {
                    title: 'Remove',
                    icon: 'mw-icon-bin',
                    className:'mw-handle-remove',
                    action: function () {
                        mw.drag.delete_element(mw._activeModuleOver);
                        mw.handleModule.hide();
                    }
                }
            ]
        };
        var handlesModuleConfigActive = {
            id: 'mw-handle-item-module-active',
            menu:[
                {
                    title: 'Settings',
                    icon: 'mw-icon-gear',
                    action: function () {
                        mw.drag.module_settings(getActiveDragCurrent(),"admin");
                        $(mw.handleModuleActive.wrapper).removeClass('active');
                    }
                },
                {
                    title: 'Move Up',
                    icon: 'mw-icon-arrow-up-b',
                    className:'mw_handle_module_up',
                    action: function () {
                        mw.drag.replace($(getActiveDragCurrent()), 'prev');
                    }
                },
                {
                    title: 'Move Down',
                    icon: 'mw-icon-arrow-down-b',
                    className:'mw_handle_module_down',
                    action: function () {
                        mw.drag.replace($(getActiveDragCurrent()), 'next');
                    }
                },
                {
                    title: '{dynamic}',
                    className:'mw_handle_module_submodules'
                },
                {
                    title: '{dynamic}',
                    className:'mw_handle_module_spacing'
                },
                {
                    title: 'Reset',
                    icon: 'mw-icon-reload',
                    className:'mw-handle-remove',
                    action: function () {
                        if(mw._activeModuleOver && mw._activeModuleOver.id){
                            mw.tools.confirm_reset_module_by_id(mw._activeModuleOver.id)
                        }
                    }
                },
                {
                    title: 'Remove',
                    icon: 'mw-icon-bin',
                    className:'mw-handle-remove',
                    action: function () {
                        mw.drag.delete_element(getActiveDragCurrent());
                        mw.handleModuleActive.hide();
                    }
                }
            ]
        };

        var getActiveDragCurrent = function () {
            //var el = mw.liveEditSelector && mw.liveEditSelector.selected ?  mw.liveEditSelector.selected[0] : null;
            var el = mw.liveEditSelector.activeModule;
            if (el && el.nodeType === 1) {
                return el;
            }
            if(mw.handleModuleActive._target) {
                return mw.handleModuleActive._target;
            }
        };

        var getDragCurrent = function () {
            if(mw._activeModuleOver){
                return mw._activeModuleOver;
            }
        };
        var dragConfig = function (curr, handle) {
            return {
                handle: handle.handleIcon,
                distance:20,
                cursorAt: {
                    //top: -30
                },
                start: function() {
                    mw.isDrag = true;
                    mw.dragCurrent = curr();
                    handleDomtreeSync.start = mw.dragCurrent.parentNode;
                    if (!mw.dragCurrent.id) {
                        mw.dragCurrent.id = 'module_' + mw.random();
                    }
                    if(mw.liveEditTools.isLayout(mw.dragCurrent)){
                        mw.$(mw.dragCurrent).css({
                            opacity:0
                        }).addClass("mw_drag_current");
                    } else {
                        mw.$(mw.dragCurrent).invisible().addClass("mw_drag_current");
                    }

                    mw.trigger("AllLeave");
                    mw.drag.fix_placeholders();
                    mw.$(mwd.body).addClass("dragStart");
                    mw.image_resizer._hide();
                    mw.wysiwyg.change(mw.dragCurrent);
                    mw.smallEditor.css("visibility", "hidden");
                    mw.smallEditorCanceled = true;
                },
                stop: function() {
                    mw.$(mwd.body).removeClass("dragStart");
                    if(mw.liveEditDomTree) {
                        mw.liveEditDomTree.refresh(handleDomtreeSync.start)
                    }
                }
            };
        };

        mw.handleModule = new mw.Handle(handlesModuleConfig);
        mw.handleModuleActive = new mw.Handle(handlesModuleConfigActive);

        mw.handleModule.type = 'hover';
        mw.handleModuleActive.type = 'active';

        mw.handleModule._hideTime = null;
        mw
            .$(mw.handleModule.wrapper)
            .draggable(dragConfig(getDragCurrent, mw.handleModule))
            .on("mousedown", function(e){
                mw.liveEditSelectMode = 'none';
            });


        mw
            .$(mw.handleModuleActive.wrapper)
            .draggable(dragConfig(getActiveDragCurrent, mw.handleModuleActive))
            .on("mousedown", function(e){
                mw.liveEditSelectMode = 'none';
            });


        var positionModuleHandle = function(e, pelement, handle){


            var element ;

            if(handle.type === 'hover') {
                element = dynamicModulesMenu(e, pelement) || pelement;
                mw._activeModuleOver = element;
            } else {
                //pelement = mw.tools.lastMatchesOnNodeOrParent(pelement, ['.module']);

                element = dynamicModulesMenu(e, pelement) || pelement;
                handle._target = pelement;
            }



            mw.$(".mw-handle-menu-dynamic", handle.wrapper).empty();
            mw.$('.mw_handle_module_up,.mw_handle_module_down').hide();
            var $el, hasedit;
            if(element && element.getAttribute('data-type') === 'layouts'){
                $el = mw.$(element);
                hasedit = mw.tools.parentsOrCurrentOrderMatchOrOnlyFirst($el[0].parentNode,['edit', 'module']);

                if(hasedit){
                    if($el.prev('[data-type="layouts"]')[0]){
                        mw.$('.mw_handle_module_up').show();
                    }
                    if($el.next('[data-type="layouts"]')[0]){
                        mw.$('.mw_handle_module_down').show();
                    }
                }
            }

            var el = mw.$(element);
            var o = el.offset();
            var width = el.width();
            var pleft = parseFloat(el.css("paddingLeft"));

            var lebar =  mwd.querySelector("#live_edit_toolbar");
            var minTop = lebar ? lebar.offsetHeight : 0;
            if(mw.templateTopFixed) {
                var ex = document.querySelector(mw.templateTopFixed);
                if(ex && !ex.contains(el[0])){
                    minTop += ex.offsetHeight;
                }
            }

            var marginTop =  30;
            var topPos = o.top;

            if(topPos<minTop){
                topPos = minTop;
            }
            var ws = mw.$(window).scrollTop();
            if(topPos<(ws+minTop)){
                topPos=(ws+minTop);
                marginTop =  -15;
                if(el[0].offsetHeight <100){
                    topPos = o.top+el[0].offsetHeight;
                    marginTop =  0;
                }
            }

            var handleLeft = o.left + pleft;
            if (handleLeft < 0) {
                handleLeft = 0;
            }

            var topPosFinal = topPos + marginTop;
            var $lebar = mw.$(lebar), $leoff = $lebar.offset();

            var outheight = el.outerHeight();

            if(topPosFinal < ($leoff.top + $lebar.height())){
                topPosFinal = (o.top + outheight) - (outheight > 100 ? 0 : handle.wrapper.clientHeight);
            }

            if(el.attr('data-type') === 'layouts') {
                topPosFinal = o.top + 10;
                handleLeft = handleLeft + 10;
            }

            clearTimeout(handle._hideTime);
            handle.show();
            mw.$(handle.wrapper)
                .removeClass('active')
                .css({
                    top: topPosFinal,
                    left: handleLeft,
                    //width: width,
                    //marginTop: marginTop
                }).addClass('mw-active-item');




            var canDrag = mw.tools.parentsOrCurrentOrderMatchOrOnlyFirst(element.parentNode, ['edit', 'module'])
                && mw.tools.parentsOrCurrentOrderMatchOrOnlyFirstOrNone(element, ['allow-drop', 'nodrop']);
            if(canDrag){
                mw.$(handle.wrapper).removeClass('mw-handle-no-drag');
            } else {
                mw.$(handle.wrapper).addClass('mw-handle-no-drag');
            }
            if(typeof(el) == 'undefined'){
                return;
            }
            var title = el.dataset("mw-title");
            var id = el.attr("id");



            var module_type = (el.dataset("type") || el.attr("type"));
            if(typeof(module_type) == 'undefined'){
                return;
            }

            var cln = el[0].querySelector('.cloneable');
            if(cln || mw.tools.hasClass(el[0], 'cloneable')){
                if(($(cln).offset().top - el.offset().top) < 20){
                    mw.tools.addClass(mw.drag._onCloneableControl, 'mw-module-near-cloneable');
                } else {
                    mw.tools.removeClass(mw.drag._onCloneableControl, 'mw-module-near-cloneable');
                }
            }

            var mod_icon = mw.live_edit.getModuleIcon(module_type);
            var mod_handle_title = (title ? title : mw.msg.settings);
            /*if(module_type === 'layouts'){
                mod_handle_title = '';
            }*/

            handle.setTitle(mod_icon, mod_handle_title);
            if(!handle){
                return;
            }

            mw.tools.classNamespaceDelete(handle, 'module-active-');
            mw.tools.addClass(handle, 'module-active-' + module_type.replace(/\//g, '-'));

            if (mw.live_edit_module_settings_array && mw.live_edit_module_settings_array[module_type]) {

                var new_el = mwd.createElement('div');
                new_el.className = 'mw_edit_settings_multiple_holder';

                var settings = mw.live_edit_module_settings_array[module_type];
                mw.$(settings).each(function () {
                    if (this.view) {
                        var new_el = mwd.createElement('a');
                        new_el.className = 'mw_edit_settings_multiple';
                        new_el.title = this.title;
                        new_el.draggable = 'false';
                        var btn_id = 'mw_edit_settings_multiple_btn_' + mw.random();
                        new_el.id = btn_id;
                        if (this.type && this.type === 'tooltip') {
                            new_el.href = 'javascript:mw.drag.current_module_settings_tooltip_show_on_element("' + btn_id + '","' + this.view + '", "tooltip"); void(0);';

                        } else {
                            new_el.href = 'javascript:mw.drag.module_settings(undefined,"' + this.view + '"); void(0);';
                        }
                        var icon = '';
                        if (this.icon) {
                            icon = '<i class="mw-edit-module-settings-tooltip-icon ' + this.icon + '"></i>';
                        }
                        new_el.innerHTML =  (icon + '<span class="mw-edit-module-settings-tooltip-btn-title">' + this.title+'</span>');
                        mw.$(".mw_handle_module_spacing", handle.wrapper).append(new_el);
                    }
                });
            } else {

            }

            /*************************************/


            if(!element.id) {
                element.id = "module_" + mw.random();
            }
            mw._initHandles.manageCollision();
        };

        mw.on('ModuleClick', function(e, pelement){
            positionModuleHandle(e, pelement, mw.handleModuleActive);
        });

        mw.on('moduleOver', function (e, pelement) {
            positionModuleHandle(e, pelement, mw.handleModule);
            if(mw._activeModuleOver === mw.handleModuleActive._target) {
                mw.handleModule.hide();
            }

            var nodes = [];
            mw.$('.module', pelement).each(function () {

                var type = this.getAttribute('data-type');

                var hastitle = mw.live_edit.registry[type] ? mw.live_edit.registry[type].title : false;
                var icon = mw.live_edit.getModuleIcon(type);
                if(!icon){
                    icon  = '<span class="mw-icon-gear mw-handle-menu-item-icon"></span>';
                }
                mw.log(icon);
                if(hastitle){
                    var menuitem = '<span class="mw-handle-menu-item dynamic-submodule-handle" data-module="'+this.id+'">'
                        + icon
                        + hastitle.replace(/_/g, ' ')
                        + '</span>';


                    nodes.push(menuitem);
                 }

            });
            $('.mw_handle_module_submodules').html(nodes.join(''));
            mw.$('.dynamic-submodule-handle').on('click', function () {
                mw.tools.module_settings('#' + this.dataset.module);
            });
        });
    },
    columns:function(){
        mw.handleColumns = new mw.Handle({
            id: 'mw-handle-item-columns',
            // className:'mw-handle-type-element',
            menu:[
                {
                    title: 'One column',
                    action: function () {
                        mw.drag.create_columns(this,1);
                    }
                },
                {
                    title: '2 columns',
                    action: function () {
                        mw.drag.create_columns(this,2);
                    }
                },
                {
                    title: '3 columns',
                    action: function () {
                        mw.drag.create_columns(this,3);
                    }
                },
                {
                    title: '4 columns',
                    action: function () {
                        mw.drag.create_columns(this,4);
                    }
                },
                {
                    title: '5 columns',
                    action: function () {
                        mw.drag.create_columns(this,5);
                    }
                },
                {
                    title: 'Remove',
                    icon: 'mw-icon-bin',
                    className:'mw-handle-remove',
                    action: function () {
                        mw.drag.delete_element(mw._activeRowOver, function () {
                            mw.$(mw.drag.columns.resizer).hide();
                            mw.handleColumns.hide();
                        });
                    }
                }
            ]
        });
        mw.handleColumns.setTitle('<span class="mw-handle-columns-icon"></span>', '');

        mw.$(mw.handleColumns.wrapper).draggable({
            handle: mw.handleColumns.handleIcon,
            cursorAt: {
                //top: -30
            },
            start: function() {
                mw.isDrag = true;
                var curr = mw._activeRowOver ;
                mw.dragCurrent = mw.ea.data.currentGrabbed = curr;
                handleDomtreeSync.start = mw.dragCurrent.parentNode;
                mw.dragCurrent.id == "" ? mw.dragCurrent.id = 'element_' + mw.random() : '';
                mw.$(mw.dragCurrent).invisible().addClass("mw_drag_current");
                mw.trigger("AllLeave");
                mw.drag.fix_placeholders();
                mw.$(mwd.body).addClass("dragStart");
                mw.image_resizer._hide();
                mw.wysiwyg.change(mw.dragCurrent);
                mw.smallEditor.css("visibility", "hidden");
                mw.smallEditorCanceled = true;
                mw.$(mw.drag.columns.resizer).hide()
            },
            stop: function() {
                mw.$(mwd.body).removeClass("dragStart");
                if(mw.liveEditDomTree) {
                    mw.liveEditDomTree.refresh(handleDomtreeSync.start)
                }
            }
        });

        mw.on("RowOver", function(a, element) {

            mw._activeRowOver = element;
            var el = mw.$(element);
            var o = el.offset();
            var width = el.width();
            var pleft = parseFloat(el.css("paddingLeft"));
            var htop = o.top - 35;
            var left = o.left;

            if (htop < 55 && mwd.getElementById('live_edit_toolbar') !== null) {
                htop = 55;
                left = left - 100;
            }
            if (htop < 0 && mwd.getElementById('live_edit_toolbar') === null) {
                htop = 0;
                //   var left = left-50;
            }


            mw.handleColumns.show()

            mw.$(mw.handleColumns.wrapper).css({
                top: htop,
                left: left,
                //width: width
            });
            mw._initHandles.manageCollision();

            var size = mw.$(element).children(".mw-col").length;
            mw.$("a.mw-make-cols").removeClass("active");
            mw.$("a.mw-make-cols").eq(size - 1).addClass("active");
             if(!element.id){
                 element.id = "element_row_" + mw.random() ;
             }




        });
    },
    nodeLeave: function () {
        var scope = this;

        mw.on("ElementLeave", function(e, target) {
            mw.handleElement.hide();
        });
        mw.on("ModuleLeave", function(e, target) {
            clearTimeout(mw.handleModule._hideTime);
            mw.handleModule._hideTime = setTimeout(function () {
                mw.handleModule.hide();
            }, 3000);

            //.removeClass('mw-active-item');
        });
        mw.on("RowLeave", function(e, target) {
            //mw.handleColumns.hide();
        });
    }
};




$(document).ready(function () {

    mw._initHandles.modules();
    mw._initHandles.elements();
    mw._initHandles.columns();
    mw._initHandles.nodeLeave();



});
