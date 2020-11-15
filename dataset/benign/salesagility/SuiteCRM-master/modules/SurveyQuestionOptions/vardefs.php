<?php
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

$dictionary['SurveyQuestionOptions'] = array(
    'table'              => 'surveyquestionoptions',
    'audited'            => true,
    'inline_edit'        => true,
    'duplicate_merge'    => true,
    'fields'             => array(

        'sort_order'                                    => array(
            'required'                  => false,
            'name'                      => 'sort_order',
            'vname'                     => 'LBL_SORT_ORDER',
            'type'                      => 'int',
            'massupdate'                => 0,
            'no_default'                => false,
            'comments'                  => '',
            'help'                      => '',
            'importable'                => 'true',
            'duplicate_merge'           => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited'                   => false,
            'inline_edit'               => true,
            'reportable'                => true,
            'unified_search'            => false,
            'merge_filter'              => 'disabled',
            'len'                       => '255',
            'size'                      => '20',
            'enable_range_search'       => false,
            'disable_num_format'        => '',
            'min'                       => false,
            'max'                       => false,
        ),
        "surveyquestionoptions_surveyquestionresponses" => array(
            'name'         => 'surveyquestionoptions_surveyquestionresponses',
            'type'         => 'link',
            'relationship' => 'surveyquestionoptions_surveyquestionresponses',
            'source'       => 'non-db',
            'module'       => 'SurveyQuestionResponses',
            'bean_name'    => 'SurveyQuestionResponses',
            'vname'        => 'LBL_SURVEYQUESTIONOPTIONS_SURVEYQUESTIONRESPONSES_FROM_SURVEYQUESTIONRESPONSES_TITLE',
        ),
        "survey_question"                               => array(
            'name'         => 'survey_question',
            'type'         => 'link',
            'relationship' => 'surveyquestions_surveyquestionoptions',
            'source'       => 'non-db',
            'module'       => 'SurveyQuestions',
            'bean_name'    => 'SurveyQuestions',
            'vname'        => 'LBL_SURVEYQUESTIONS_SURVEYQUESTIONOPTIONS_FROM_SURVEYQUESTIONS_TITLE',
            'id_name'      => 'survey_question_id',
            'link_type'    => 'one',
            'side'         => 'left',
        ),
        "surveyquestions_surveyquestionoptions_name"    => array(
            'name'    => 'surveyquestions_surveyquestionoptions_name',
            'type'    => 'relate',
            'source'  => 'non-db',
            'vname'   => 'LBL_SURVEYQUESTIONS_SURVEYQUESTIONOPTIONS_FROM_SURVEYQUESTIONS_TITLE',
            'save'    => true,
            'id_name' => 'survey_question_id',
            'link'    => 'survey_question',
            'table'   => 'surveyquestions',
            'module'  => 'SurveyQuestions',
            'rname'   => 'name',
        ),
        "survey_question_id"                            => array(
            'name'       => 'survey_question_id',
            'type'       => 'id',
            'reportable' => false,
            'vname'      => 'LBL_SURVEYQUESTIONS_SURVEYQUESTIONOPTIONS_FROM_SURVEYQUESTIONOPTIONS_TITLE',
        ),
    ),
    'relationships'      => array(),
    'optimistic_locking' => true,
    'unified_search'     => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef(
    'SurveyQuestionOptions',
    'SurveyQuestionOptions',
    array('basic', 'assignable', 'security_groups')
);
