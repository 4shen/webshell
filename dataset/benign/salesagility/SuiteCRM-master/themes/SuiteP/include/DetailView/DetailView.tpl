{*
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */
*}

{{sugar_include type="smarty" file=$headerTpl}}
{sugar_include include=$includes}
<div class="detail-view">
    <div class="mobile-pagination">{$PAGINATION}</div>
    {*display tabs*}
    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}
    <ul class="nav nav-tabs">
        {{if $useTabs}}
        {{counter name="isection" start=0 print=false assign="isection"}}
        {{foreach name=section from=$sectionPanels key=label item=panel}}
        {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
        {* if tab *}
        {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true)}}
        {*if tab display*}
        {{counter name="tabCount" print=false}}
        {{if $tabCount == '0'}}
        <li role="presentation" class="active">
            <a id="tab{{$tabCount}}" data-toggle="tab" class="hidden-xs">
                {sugar_translate label='{{$label}}' module='{{$module}}'}
            </a>

            {* Count Tabs *}
            {{counter name="tabCountOnlyXS" start=-1 print=false assign="tabCountOnlyXS"}}
            {{foreach name=sectionOnlyXS from=$sectionPanels key=labelOnly item=panelOnlyXS}}
            {{capture name=label_upper_count_only assign=label_upper_count_only}}{{$labelOnly|upper}}{{/capture}}
            {{if (isset($tabDefs[$label_upper_count_only].newTab) && $tabDefs[$label_upper_count_only].newTab == true)}}
            {{counter name="tabCountOnlyXS" print=false}}
            {{/if}}
            {{/foreach}}

            {*
                For the mobile view, only show the first tab has a drop down when:
                * There is more than one tab set
                * When Acton Menu's are enabled
            *}
            <a id="xstab{{$tabCount}}" href="#" class="visible-xs first-tab{{if $tabCountOnlyXS > 0}}-xs{{/if}} dropdown-toggle" data-toggle="dropdown">
                {sugar_translate label='{{$label}}' module='{{$module}}'}
            </a>
            {{if $tabCountOnlyXS > 0}}
            <ul id="first-tab-menu-xs" class="dropdown-menu">
                {{counter name="tabCountXS" start=1 print=false assign="tabCountXS"}}
                {{foreach name=sectionXS from=$sectionPanels key=label item=panelXS}}
                {{capture name=label_upper_xs assign=label_upper_xs}}{{$label|upper}}{{/capture}}
                {{if (isset($tabDefs[$label_upper_xs].newTab) && $tabDefs[$label_upper_xs].newTab == true)}}
                <li role="presentation">
                    <a id="tab{{$tabCountXS}}" data-toggle="tab" onclick="changeFirstTab(this, 'tab-content-{{$tabCountXS}}');">
                        {sugar_translate label='{{$label}}' module='{{$module}}'}
                    </a>
                </li>
                {{/if}}
                {{counter name="tabCountXS" print=false}}
                {{/foreach}}
            </ul>
            {{/if}}

        </li>
        {{else}}
        <li role="presentation" class="hidden-xs">
            <a id="tab{{$tabCount}}" data-toggle="tab">
                {sugar_translate label='{{$label}}' module='{{$module}}'}
            </a>
        </li>
        {{/if}}
        {{else}}
        {* if panel skip*}
        {{/if}}
        {{/foreach}}
        {{else}}
        {*
            Since: SuieCRM 7.8
            When action menus are enabled and When there are only panels and there are not any tabs,
            make the first panel a tab so that the action menu looks correct. This is regardless of what the
            meta/studio defines the first panel should always be tab.
        *}
        {if $config.enable_action_menu and $config.enable_action_menu != false}
            {{foreach name=section from=$sectionPanels key=label item=panel}}
            {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
            {{counter name="tabCount" print=false}}
            {{if $tabCount == '0'}}
            <li role="presentation" class="active">
                <a id="tab{{$tabCount}}" data-toggle="tab" class="hidden-xs">
                    {sugar_translate label='{{$label}}' module='{{$module}}'}
                </a>
                <a id="xstab{{$tabCount}}" href="#" class="visible-xs first-tab dropdown-toggle" data-toggle="dropdown">
                    {sugar_translate label='{{$label}}' module='{{$module}}'}
                </a>
            </li>
            {{else}}
            {* if panel skip *}
            {{/if}}
        {{/foreach}}
        {/if}
        {{/if}}
        {if $config.enable_action_menu and $config.enable_action_menu != false}
        <li id="tab-actions" class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">{{$APP.LBL_LINK_ACTIONS}}<span class="suitepicon suitepicon-action-caret"></span></a>
            {{include file="themes/SuiteP/include/DetailView/actions_menu.tpl"}}
        </li>
        <li class="tab-inline-pagination">
            {{if $panelCount == 0}}
            {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
            {{if $SHOW_VCR_CONTROL}}
            {$PAGINATION}
            {{/if}}
            {{counter name="panelCount" print=false}}
            {{/if}}
        </li>
        {/if}
    </ul>
    {{counter name="tabCount" start=0 print=false assign="tabCount"}}
    <div class="clearfix"></div>
    {{if $useTabs}}
    {*<!-- TAB CONTENT USE TABS -->*}
    <div class="tab-content">
        {{else}}
        {*
           Since: SuieCRM 7.8
           When action menus are enabled and When there are only panels and there are not any tabs,
           make the first panel a tab so that the action menu looks correct. This is regardless of what the
           meta/studio defines the first panel should always be tab.
       *}
        {if $config.enable_action_menu and $config.enable_action_menu != false}
        {{if $tabCount == 0}}
        {*<!-- TAB CONTENT USE TABS -->*}
        <div class="tab-content">
            {{else}}
            {*<!-- TAB CONTENT DOESN'T USE TABS -->*}
            <div class="tab-content" style="padding: 0; border: 0;">
                {{/if}}
                {else}
                {*<!-- TAB CONTENT DOESN'T USE TABS -->*}
                <div class="tab-content" style="padding: 0; border: 0;">
                    {/if}
                    {{/if}}
                    {* Loop through all top level panels first *}
                    {{if $useTabs}}
                    {{foreach name=section from=$sectionPanels key=label item=panel}}
                    {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                    {{if isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true}}
                    {{if $tabCount == '0'}}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER active fade in" id='tab-content-{{$tabCount}}'>
                        {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                    </div>
                    {{else}}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER fade" id='tab-content-{{$tabCount}}'>
                        {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                    </div>
                    {{/if}}
                    {{/if}}
                    {{counter name="tabCount" print=false}}
                    {{/foreach}}
                    {{else}}
                    {*
                       Since: SuieCRM 7.8
                       When action menus are enabled and When there are only panels and there are not any tabs,
                       make the first panel a tab so that the action menu looks correct. This is regardless of what the
                       meta/studio defines the first panel should always be tab.
                   *}
                    {if $config.enable_action_menu and $config.enable_action_menu != false}
                        {{foreach name=section from=$sectionPanels key=label item=panel}}
                        {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                        {{if $tabCount == '0'}}
                        <div class="tab-pane-NOBOOTSTRAPTOGGLER active fade in" id='tab-content-{{$tabCount}}'>
                            {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                        </div>
                        {{else}}

                        {{/if}}
                    {{counter name="tabCount" print=false}}
                    {{/foreach}}
                    {else}
                    {*<!-- TAB CONTENT DOESN'T USE TABS -->*}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER panel-collapse"></div>
                    {/if}
                    {{/if}}
                </div>
                {*display panels*}
                <div class="panel-content">
                    <div>&nbsp;</div>
                    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}
                    {{counter name="panelCount" start=-1 print=false assign="panelCount"}}
                    {{foreach name=section from=$sectionPanels key=label item=panel}}

                    {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                    {* if tab *}
                    {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true && $useTabs)}}
                    {{counter name="tabCount" print=false}}
                    {*if tab skip*}
                    {{else}}
                    {* if panel display*}
                    {*if panel collasped*}
                    {{if (isset($tabDefs[$label_upper].panelDefault) && $tabDefs[$label_upper].panelDefault == "collapsed") }}
                    {*collapse panel*}
                    {{assign var='collapse' value="panel-collapse collapse"}}
                    {{assign var='collapsed' value="collapsed"}}
                    {{assign var='collapseIcon' value="glyphicon glyphicon-plus"}}
                    {{assign var='panelHeadingCollapse' value="panel-heading-collapse"}}
                    {{else}}
                    {*expand panel*}
                    {{assign var='collapse' value="panel-collapse collapse in"}}
                    {{assign var='collapseIcon' value="glyphicon glyphicon-minus"}}
                    {{assign var='panelHeadingCollapse' value=""}}
                    {{/if}}
                    {{if $label != "LBL_AOP_CASE_UPDATES"}}
                    {{assign var='panelId' value="top-panel-$panelCount"}}
                    {{else}}
                    {{assign var='panelId' value="LBL_AOP_CASE_UPDATES"}}
                    {{/if}}

                    {*
                       Since: SuieCRM 7.8
                       When action menus are enabled and When there are only panels and there are not any tabs,
                       make the first panel a tab so that the action menu looks correct. This is regardless of what the
                       meta/studio defines the first panel should always be tab.
                    *}
                    {if $config.enable_action_menu and $config.enable_action_menu != false}
                        {{if $panelCount == -1}}
                        {* skip panel as it has been converted to a tab*}
                        {{else}}
                        {* display panels as they have always been displayed *}
                        {{if $useTabs}}
                            {{if $tabCount == 0}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: block;">
                            {{else}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: none;">
                            {{/if}}
                        {{else}}
                            <div class="panel panel-default">
                        {{/if}}
                            <div class="panel-heading {{$panelHeadingCollapse}}">
                                <a class="{{$collapsed}}" role="button" data-toggle="collapse" href="#{{$panelId}}" aria-expanded="false">
                                    <div class="col-xs-10 col-sm-11 col-md-11">
                                        {sugar_translate label='{{$label}}' module='{{$module}}'}
                                    </div>
                                </a>
                            </div>
                            <div class="panel-body {{$collapse}} panelContainer" id="{{$panelId}}"  data-id="{{$label_upper}}">
                                <div class="tab-content">
                                    <!-- TAB CONTENT -->
                                    {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                                </div>
                            </div>
                        </div>
                        {{/if}}
                    {else}
                    {* display panels as they have always been displayed *}
                    {{if $useTabs}}
                            {{if $tabCount == 0}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: block;">
                            {{else}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: none;">
                            {{/if}}
                        {{else}}
                            <div class="panel panel-default">
                        {{/if}}
                        <div class="panel-heading {{$panelHeadingCollapse}}">
                            <a class="{{$collapsed}}" role="button" data-toggle="collapse" href="#{{$panelId}}" aria-expanded="false">
                                <div class="col-xs-10 col-sm-11 col-md-11">
                                    {sugar_translate label='{{$label}}' module='{{$module}}'}
                                </div>
                            </a>

                        </div>
                        <div class="panel-body {{$collapse}} panelContainer" id="{{$panelId}}" data-id="{{$label_upper}}">
                            <div class="tab-content">
                                <!-- TAB CONTENT -->
                                {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                            </div>
                        </div>
                    </div>
                    {/if}


                    {{/if}}
                    {{counter name="panelCount" print=false}}
                    {{/foreach}}
                </div>
            </div>

            {{include file=$footerTpl}}
            <script type="text/javascript" src="include/InlineEditing/inlineEditing.js"></script>
            <script type="text/javascript" src="modules/Favorites/favorites.js"></script>

            {literal}

                <script type="text/javascript">

                    let selectTabDetailView = function(tab) {
                        $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').hide();
                        $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').eq(tab).show().addClass('active').addClass('in');
                        $('#content div.detail-view div.panel-content div.panel.panel').hide();
                        $('#content div.panel-content div.panel.tab-panel-' + tab).show();
                    };

                    let selectTabOnError = function(tab) {
                        selectTabDetailView(tab);
                        $('#content ul.nav.nav-tabs > li').removeClass('active');
                        $('#content ul.nav.nav-tabs > li a').css('color', '');

                        $('#content ul.nav.nav-tabs > li').eq(tab).find('a').first().css('color', 'red');
                        $('#content ul.nav.nav-tabs > li').eq(tab).addClass('active');

                    };

                    var selectTabOnErrorInputHandle = function(inputHandle) {
                        var tab = $(inputHandle).closest('.tab-pane-NOBOOTSTRAPTOGGLER').attr('id').match(/^detailpanel_(.*)$/)[1];
                        selectTabOnError(tab);
                    };


                    $(function(){
                        $('#content ul.nav.nav-tabs > li > a[data-toggle="tab"]').click(function(e){
                            if(typeof $(this).parent().find('a').first().attr('id') != 'undefined') {
                                var tab = parseInt($(this).parent().find('a').first().attr('id').match(/^tab(.)*$/)[1]);
                                selectTabDetailView(tab);
                            }
                        });
                    });

                </script>

            {/literal}

