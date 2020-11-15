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

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use SuiteCRM\Utility\SuiteValidator;

/**
 * Class Folder
 *
 * private model class for ListViewDataEmails::getListViewData()
 * represent a fake SugarBean:
 * in legacy logic, Folder ID equals to an Inbound Email ID
 */
class Folder
{

    /**
     * private
     * @var DBManager $db
     */
    public $db;

    /**
     * private, use Folder::getId() instead
     * @var string UUID in folders table
     */
    public $id;

    /**
     * private, use Folder::getType() instead
     * @var string folder type
     */
    protected $type;

    /**
     * Folder constructor.
     */
    public function __construct()
    {
        $this->db = DBManagerFactory::getInstance();
        $this->id = null;
        $this->type = "inbound";
    }

    /**
     * @param int|string $folderId - (should be string, int type is legacy)
     * @param bool $encode (legacy, unused)
     * @param bool $deleted (legacy, unused)
     * @return null|string (folder ID)
     * @throws SuiteException
     */
    public function retrieve($folderId = -1)
    {
        $isValidator = new SuiteValidator();
        if ($isValidator->isValidId($folderId)) {
            $result = $this->db->query("SELECT * FROM folders WHERE id='" . $folderId . "'");
            $row = $this->db->fetchByAssoc($result);

            // get the root of the tree
            // is the id of the root node is the same as the inbound email id

            if (empty($row['parent_folder'])) {

                // root node (inbound)

                $this->id = $row['id'];
            } else {

                // child node

                $this->id = $row['parent_folder'];
                $this->type = $row['folder_type'];
                $this->mailbox = $row['name'];
            }
        } else {
            throw new SuiteException("Invalid or empty Email Folder ID");
        }

        return $this->id;
    }

    /**
     * @param array $request
     * @return Folder
     * @throws SuiteException
     */
    public function retrieveFromRequest($request)
    {
        if (isset($request['folders_id']) && !empty($request['folders_id'])) {
            $foldersId = $request['folders_id'];
            $this->retrieve($foldersId);
        } else {
            $GLOBALS['log']->warn("Empty or undefined Email Folder ID");
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }
}
