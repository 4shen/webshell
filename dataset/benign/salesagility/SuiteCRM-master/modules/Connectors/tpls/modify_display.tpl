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
<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Connectors/Connector.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>


{literal}

<script language="javascript">

var _sourceArray = new Array();

var SourceTabs = {

    init : function() {
         _tabView = new YAHOO.widget.TabView();

    	{/literal}
    		 {counter assign=source_count start=0 print=0}
	        {foreach name=connectors from=$SOURCES key=name item=source}
	            {counter assign=source_count}
		{literal}
		       	tab = new YAHOO.widget.Tab({
			        label: '{/literal}{$source.name}{literal} ',
			        dataSrc: {/literal}'index.php?module=Connectors&action=DisplayProperties&source_id={$source.id}'{literal},
			        cacheData: true,
			        {/literal}
			        {if $source_count == 1}
			        active: true
			        {else}
			        active: false
			        {/if}
			        {literal}
			    });
			    _tabView.addTab(tab);
			    tab.addListener('contentChange', SourceTabs.tabContentChanged);
			    _sourceArray[{/literal}{$source_count}{literal}-1] = '{/literal}{$source.id}{literal}';
		{/literal}
	       {/foreach}
		  {literal}
  		_tabView.appendTo('container');
  		//_tabView.addListener('contentChange', SourceTabs.tabContentChanged);
 		_tabView.addListener('beforeActiveTabChange', SourceTabs.tabIndexChanged);
    },

    tabContentChanged: function(info) {
    	tab = _tabView.get('activeTab');
        SUGAR.util.evalScript(tab.get('content'));
    },

    tabIndexChanged : function(info){

    },

    fitContainer: function() {
		_tabView = SourceTabs.getTabView();
		content_div = _tabView.getElementsByClassName('yui-content', 'div')[0];
		content_div.style.overflow='auto';
		content_div.style.height='405px';
    },

    getTabView : function() {
        return _tabView;
    }
}
YAHOO.util.Event.onDOMReady(SourceTabs.init);
</script>
{/literal}
<form name="ModifyDisplay" method="POST">
<input type="hidden" name="modify" value="true">
<input type="hidden" name="module" value="Connectors">
<input type="hidden" name="action" value="SaveModifyDisplay">

{counter assign=source_count start=0 print=0}
{foreach name=connectors from=$SOURCES key=name item=source}
{counter assign=source_count}
<input type="hidden" name="source{$source_count}" value="{$source.id}">
{/foreach}
<input type="hidden" name="display_values" value="">
<input type="hidden" name="display_sources" value="">
<input type="hidden" name="reset_to_default" value="">

<table border="0" class="actionsContainer">
<tr><td>
<input id="connectors_top_save" title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" onclick="calculateValues();" type="submit" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
<input id="connectors_top_cancel" title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.ModifyDisplay.action.value='ConnectorSettings'; document.ModifyDisplay.module.value='Connectors';" type="submit" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</td></tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td>
<div >
<div id="container" style="height: 465px">
</div>
</div>
</td></tr>
</table>
<table border="0" class="actionsContainer">
<tr><td>
<input id="connectors_bottom_save" title="{$APP.LBL_SAVE_BUTTON_LABEL}" class="button" onclick="calculateValues();" type="submit" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
<input id="connectors_bottom_cancel" title="{$APP.LBL_CANCEL_BUTTON_LABEL}" class="button" onclick="document.ModifyDisplay.action.value='ConnectorSettings'; document.ModifyDisplay.module.value='Connectors';" type="submit" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</td></tr>
</table>
</form>

<script type="text/javascript">
{literal}
YAHOO.util.Event.onDOMReady(SourceTabs.fitContainer);
{/literal}
</script>


<script type="text/javascript">
{literal}
function calculateValues() {
    tabview = SourceTabs.getTabView();
    display_vals = ''
    source_vals = '';
    sources = new Array();
    //Get the source divs
    elements = tabview.getElementsByClassName('sources_table', 'table');
    for(el in elements) {
        if(typeof elements[el] == 'function') {
           continue;
        }

        source_vals += ',' + elements[el].id;

    }

    //Get the enabled div elements
    elements = tabview.getElementsByClassName('enabled_module_workarea', 'div');
    for(el in elements) {
        if(typeof elements[el] == 'function') {
           continue;
        }

        //Get the li elements
 		enabled_list = YAHOO.util.Dom.getElementsByClassName('noBullet2', 'li', elements[el]);
        for(li in enabled_list) {
            if(typeof enabled_list[li] != 'function') {
                display_vals += ',' + enabled_list[li].getAttribute('id');
            }
        }
    }

    document.ModifyDisplay.display_values.value = display_vals != '' ? display_vals.substr(1,display_vals.length) : '';
    document.ModifyDisplay.display_sources.value = source_vals != '' ? source_vals.substr(1, source_vals.length) : '';
}

YAHOO.util.Event.onDOMReady(SourceTabs.fitContainer);
{/literal}
</script>