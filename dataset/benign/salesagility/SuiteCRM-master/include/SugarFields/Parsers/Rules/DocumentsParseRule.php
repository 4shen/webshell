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


require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class DocumentsParseRule extends BaseRule
{
    public function __construct()
    {
    }

    /**
     * @deprecated deprecated since version 7.6, PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code, use __construct instead
     */
    public function DocumentsParseRule()
    {
        $deprecatedMessage = 'PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code';
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->deprecated($deprecatedMessage);
        } else {
            trigger_error($deprecatedMessage, E_USER_DEPRECATED);
        }
        self::__construct();
    }


    public function preParse($panels, $view)
    {
        foreach ($panels as $name=>$panel) {
            foreach ($panel as $rowCount=>$row) {
                foreach ($row as $key=>$column) {
                    if ($this->matches($column, '/^related_doc_id$/')) {
                        $panels[$name][$rowCount][$key] = 'related_doc_name';
                    } else {
                        if ($this->matches($column, '/^related_doc_rev_id$/')) {
                            $panels[$name][$rowCount][$key] = ($view == 'EditView') ? 'related_doc_rev_number' : 'related_doc_name';
                        } else {
                            if ($this->matches($column, '/^user_date_format$/')) {
                                $panels[$name][$rowCount][$key] = 'active_date';
                            } else {
                                if ($this->matches($column, '/^is_template_checked$/')) {
                                    $panels[$name][$rowCount][$key] = 'is_template';
                                } else {
                                    if ($this->matches($column, '/^last_rev_creator$/')) {
                                        $panels[$name][$rowCount][$key] = 'last_rev_created_name';
                                    } else {
                                        if ($this->matches($column, '/^last_rev_date$/')) {
                                            $panels[$name][$rowCount][$key] = 'last_rev_create_date';
                                        } else {
                                            if ($this->matches($column, '/^save_file$/')) {
                                                $panels[$name][$rowCount][$key] = 'filename';
                                            } else {
                                                if ($this->matches($column, '/^subcategory$/')) {
                                                    $panels[$name][$rowCount][$key] = 'subcategory_id';
                                                } else {
                                                    if ($this->matches($column, '/^category$/')) {
                                                        $panels[$name][$rowCount][$key] = 'category_id';
                                                    } else {
                                                        if ($this->matches($column, '/^related_document_version$/')) {
                                                            $panels[$name][$rowCount][$key] = 'related_doc_rev_number';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } //foreach
            } //foreach
        } //foreach
    return $panels;
    }

    public function parsePanels(& $panels, $view)
    {
        foreach ($panels as $name=>$panel) {
            foreach ($panel as $rowCount=>$row) {
                foreach ($row as $key=>$column) {
                    if ($this->matches($column, '/related_doc_id/si') ||
                   $this->matches($column, '/related_doc_rev_id/si') ||
                   $this->matches($column, '/latest_revision/si') ||
                   $this->matches($column, '/file_name/si')) {
                        $panels[$name][$rowCount][$key] = '';
                    }
                } //foreach
            } //foreach
        } //foreach

       return $panels;
    }
}
