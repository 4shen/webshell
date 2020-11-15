<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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




global $current_user;

$dashletData['MyCallsDashlet']['searchFields'] = array(
                                                       
                                                       'name'        => array('default' => ''),
                                                       'status'           => array('default' => array('Planned')),
                                                       'date_entered'       => array('default' => ''),
                                                       'date_start'             => array('default' => ''),
                                                       
                                                       'assigned_user_id' => array('type'    => 'assigned_user_name',
                                                                                      'label'   => 'LBL_ASSIGNED_TO',
                                                                                   'default' => $current_user->name));
$dashletData['MyCallsDashlet']['columns'] = array('set_complete' => array('width' => '5',
                                                                          'label' => 'LBL_LIST_CLOSE',
                                                                          'default' => true,
                                                                          'sortable' => false,
                                                                          'related_fields' => array('status','recurring_source')),
                                                  'name' => array('width'   => '40',
                                                                  'label'   => 'LBL_SUBJECT',
                                                                  'link'    => true,
                                                                  'default' => true),
                                                  'parent_name' => array('width' => '29',
                                                                         'label' => 'LBL_LIST_RELATED_TO',
                                                                         'sortable' => false,
                                                                         'dynamic_module' => 'PARENT_TYPE',
                                                                         'link' => true,
                                                                         'id' => 'PARENT_ID',
                                                                         'ACLTag' => 'PARENT',
                                                                         'related_fields' => array('parent_id', 'parent_type'),
                                                                         'default' => true,
                                                                        ),
                                                  
                                                  'duration' => array('width'    => '10',
                                                                      'label'    => 'LBL_DURATION',
                                                                      'sortable' => false,
                                                                      'related_fields' => array('duration_hours', 'duration_minutes')),
                                                  'direction' => array('width'   => '10',
                                                                       'label'   => 'LBL_DIRECTION'),
                                                  'date_start' => array('width'   => '15',
                                                                        'label'   => 'LBL_DATE',
                                                                        'default' => true,
                                                                        'related_fields' => array('time_start')),
                                            'set_accept_links'=> array('width'    => '10',
                                                                       'label'    => translate('LBL_ACCEPT_THIS', 'Meetings'),
                                                                       'sortable' => false,
                                                                        'related_fields' => array('status'),
                                                                        'default' => true),
                                                  'status' => array('width'   => '8',
                                                                    'label'   => 'LBL_STATUS',
                                                                    'default'  => true),
                                                  'date_entered' => array('width'   => '15',
                                                                          'label'   => 'LBL_DATE_ENTERED'),
                                                  'date_modified' => array('width'   => '15',
                                                                          'label'   => 'LBL_DATE_MODIFIED'),
                                                  'created_by' => array('width'   => '8',
                                                                        'label'   => 'LBL_CREATED'),
                                                  'assigned_user_name' => array('width'   => '8',
                                                                                'label'   => 'LBL_LIST_ASSIGNED_USER'),
                                                  );
