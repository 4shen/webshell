<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2019 SalesAgility Ltd.
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

namespace SuiteCRM\Robo\Plugin\Commands;

use DBManagerFactory;
use RepairAndClear;
use Robo\Tasks as RoboTasks;
use RuntimeException;
use VardefManager;

/**
 * Class RepairCommands
 *
 * @category RoboTasks
 * @package  SuiteCRM\Robo\Plugin\Commands
 * @author   Jose C. Massón <jose AT gcoop DOT coop>
 * @license  GNU GPLv3
 * @link     RepairCommands
 */
class RepairCommands extends RoboTasks
{
    /**
     * Synchronize database tables with vardefs.
     *
     * @param array $opts optional command line arguments
     * @option bool $no-execute - Set if you do not want the command to execute SQL at the end of the repair.
     * @throws \RuntimeException
     */
    public function repairDatabase(array $opts = ['no-execute' => false])
    {
        global $beanFiles;
        $this->say('Repairing database...');
        $db = DBManagerFactory::getInstance();
        $queries = [];
        VardefManager::clearVardef();

        foreach ($beanFiles as $bean_name => $file) {
            if (!file_exists($file)) {
                continue;
            }

            require_once $file;
            $GLOBALS['reload_vardefs'] = true;
            $focus = new $bean_name();

            if (isset($focus->disable_vardefs) && $focus->disable_vardefs === false && isset($focus->module_dir)) {
                include 'modules/'.$focus->module_dir.'/vardefs.php';
                $sql = $db->repairTable($focus, !$opts['no-execute']);

                if (!empty($sql)) {
                    $queries[] = $sql;
                }
            }
        }

        $total = count($queries);

        if (!$opts['no-execute']) {
            $this->say('Database synchronized with vardefs!');
            $this->say("Executed queries: {$total}");
            return;
        }

        $this->say("Execute the following queries {$total} in order to get database synchronized with vardefs");
        array_map('print_r', $queries);
    }



    /**
     * This Robo task rebuilds the CRM extension files found in custom/Extension.
     *
     * @param array $opts optional command line arguments
     * @option bool $show-output - Set if you want to see the rebuildExtensions() output.
     * @throws \RuntimeException
     */
    public function repairRebuildExtensions(array $opts = ['show-output' => false])
    {
        $this->say("Rebuilding Extensions...");
        require_once __DIR__ . '/../../../../modules/Administration/QuickRepairAndRebuild.php';
        global $current_user;
        $current_user->is_admin = '1';
        $tool = new RepairAndClear();
        $tool->show_output = $opts['show-output'];
        $tool->rebuildExtensions();

        if ($opts['show-output']) {
            echo "\n";
        }
        $this->say('Extensions rebuilt!');
    }


    /**
     * Rebuilds relationships defined in modules/MODULE/vardefs.php.
     * @param array $opts optional command line arguments
     * @option bool $show-output - Set if you want to see the RebuildRelationships output.
     * @throws \RuntimeException
     */
    public function repairRebuildRelationships(array $opts = ['show-output' => false])
    {
        $this->say('Rebuilding Relationships...');

        $_REQUEST['silent'] = 'no';

        if ($opts['show-output']) {
            unset($_REQUEST['silent']);
        }

        require_once __DIR__ . '/../../../../modules/Administration/RebuildRelationship.php';

        if ($opts['show-output']) {
            echo "\n";
        }
        $this->say('Relationships rebuilt!');
    }
}
