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
<form class="email-import-view" id="EditView" name="EditNonImported" method="POST" action="index.php?module=Emails&action=send">
    <input type="hidden" name="module" value="Emails">
    <input type="hidden" name="action" value="Import">
    <input type="hidden" name="record" value="">
    <input type="hidden" name="type" value="out">
    <input type="hidden" name="send" value="1">
    <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
    <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
    <input type="hidden" name="inbound_email_id" value="{$INBOUND_ID}">
<div id="EditView_tabs">
    {*display tabs*}
    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}

    <div class="clearfix"></div>
    {{if $useTabs}}
    <div class="tab-content">
        {{else}}
        <div class="tab-content" style="padding: 0; border: 0;">
            {{/if}}
            {{counter name="tabCount" start=0 print=false assign="tabCount"}}
            {* Loop through all top level panels first *}
            {{if $useTabs}}
            {{foreach name=section from=$sectionPanels key=label item=panel}}
            {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
            {{if isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true}}
            {{if $tabCount == '0'}}
            <div class="tab-pane-NOBOOTSTRAPTOGGLER active fade in" id='tab-content-{{$tabCount}}'>
                {{include file='themes/SuiteP/include/EditView/tab_panel_content.tpl'}}
            </div>
            {{else}}
            <div class="tab-pane-NOBOOTSTRAPTOGGLER fade" id='tab-content-{{$tabCount}}'>
                {{include file='themes/SuiteP/include/EditView/tab_panel_content.tpl'}}
            </div>
            {{/if}}
             {{counter name="tabCount" print=false}}
            {{/if}}
            {{/foreach}}
            {{else}}
            <div class="tab-pane panel-collapse">&nbsp;</div>
            {{/if}}
        </div>
        {*display panels*}
        <div class="panel-content">
            <div>&nbsp;</div>
            {{counter name="panelCount" start=-1 print=false assign="panelCount"}}
            {{foreach name=section from=$sectionPanels key=label item=panel}}
            {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
            {* if tab *}
            {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true && $useTabs)}}
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

            <div class="panel panel-default">
                <div class="panel-heading {{$panelHeadingCollapse}}">
                    <a class="{{$collapsed}}" role="button" data-toggle="collapse-edit" aria-expanded="false">
                        <div class="col-xs-10 col-sm-11 col-md-11">
                            {sugar_translate label='{{$label}}' module='{{$module}}'}
                        </div>
                    </a>
                </div>
                <div class="panel-body {{$collapse}}" id="detailpanel_{{$panelCount}}">
                    <div class="tab-content">
                        {{include file='themes/SuiteP/include/EditView/tab_panel_content.tpl'}}
                    </div>
                </div>
            </div>
            {{/if}}
            {{counter name="panelCount" print=false}}
            {{/foreach}}
        </div>
    </div>
{{sugar_include type='smarty' file=$footerTpl}}

{if $RETURN_MODULE}
    {literal}

        <script type="text/javascript">

        var selectTab = function(tab) {
            $('#EditView_tabs div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').hide();
            $('#EditView_tabs div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').eq(tab).show().addClass('active').addClass('in');
        };

        var selectTabOnError = function(tab) {
            selectTab(tab);
            $('#EditView_tabs ul.nav.nav-tabs li').removeClass('active');
            $('#EditView_tabs ul.nav.nav-tabs li a').css('color', '');

            $('#EditView_tabs ul.nav.nav-tabs li').eq(tab).find('a').first().css('color', 'red');
            $('#EditView_tabs ul.nav.nav-tabs li').eq(tab).addClass('active');

        };

        var selectTabOnErrorInputHandle = function(inputHandle) {
            var tab = $(inputHandle).closest('.tab-pane-NOBOOTSTRAPTOGGLER').attr('id').match(/^detailpanel_(.*)$/)[1];
            selectTabOnError(tab);
        };


        $(function(){
            $('#EditView_tabs ul.nav.nav-tabs li > a[data-toggle="tab"]').click(function(e){
                if(typeof $(this).parent().find('a').first().attr('id') != 'undefined') {
                    var tab = parseInt($(this).parent().find('a').first().attr('id').match(/^tab(.)*$/)[1]);
                    selectTab(tab);
                }
            });

            $('a[data-toggle="collapse-edit"]').click(function(e){
                if($(this).hasClass('collapsed')) {
                  // Expand panel
                    // Change style of .panel-header
                    $(this).removeClass('collapsed');
                    // Expand .panel-body
                    $(this).parents('.panel').find('.panel-body').removeClass('in').addClass('in');
                } else {
                  // Collapse panel
                    // Change style of .panel-header
                    $(this).addClass('collapsed');
                    // Collapse .panel-body
                    $(this).parents('.panel').find('.panel-body').removeClass('in').removeClass('in');
                }
            });
        });
        </script>

    {/literal}
    {/if}
</form>