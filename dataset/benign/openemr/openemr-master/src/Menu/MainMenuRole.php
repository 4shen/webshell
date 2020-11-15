<?php

/**
 * MainMenuRole class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MainMenuRole extends MenuRole
{

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * Constructor
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        // This is where the magic happens to support special menu items.
        //   An empty menu_update_map array is created in MenuRole class
        //   constructor. Adding to this array will link special menu items
        //   to functions in this class.
        parent::__construct();
        $this->menu_update_map["Visit Forms"] = "updateVisitForms";
        $this->dispatcher = $dispatcher;
    }

    /**
     * Collect the Menu for logged in user.
     *
     * @return array representation of the Menu
     */
    public function getMenu()
    {
        // Collect the selected menu of user
        $mainMenuRole = $this->getMenuRole();

        // Load the selected menu
        if (preg_match("/.json$/", $mainMenuRole)) {
            // load custom menu (includes .json in id)
            $menu_parsed = json_decode(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/custom_menus/" . $mainMenuRole));
        } else {
            // load a standardized menu (does not include .json in id)
            $menu_parsed = json_decode(file_get_contents($GLOBALS['fileroot'] . "/interface/main/tabs/menu/menus/" . $mainMenuRole . ".json"));
        }

        // if error, then die and report error
        if (!$menu_parsed) {
            die("\nJSON ERROR: " . json_last_error());
        }

        $this->menuUpdateEntries($menu_parsed);
        $updatedMenuEvent = $this->dispatcher->dispatch(MenuEvent::MENU_UPDATE, new MenuEvent($menu_parsed));

        $menu_restrictions = array();
        $this->menuApplyRestrictions($updatedMenuEvent->getMenu(), $menu_restrictions);
        $updatedRestrictions = $this->dispatcher->dispatch(MenuEvent::MENU_RESTRICT, new MenuEvent($menu_restrictions));

        return $updatedRestrictions->getMenu();
    }

    /**
     * Build the html select element to list the MainMenuRole options.
     *
     * @var string $selected Current MainMenuRole for current users.
     * @return string Html select element to list the MainMenuRole options.
     */
    public function displayMenuRoleSelector($selected = "")
    {
        $output = "<select name='main_menu_role' id='main_menu_role' class='form-control'>";
        $output .= "<option value='standard' " . (($selected == "standard") ? "selected" : "") . ">" . xlt("Standard") . "</option>";
        $output .= "<option value='answering_service' " . (($selected == "answering_service") ? "selected" : "") . ">" . xlt("Answering Service") . "</option>";
        $output .= "<option value='front_office' " . (($selected == "front_office") ? "selected" : "") . ">" . xlt("Front Office") . "</option>";
        $customMenuDir = $GLOBALS['OE_SITE_DIR'] . "/documents/custom_menus";
        if (file_exists($customMenuDir)) {
            $dHandle = opendir($customMenuDir);
            while (false !== ($menuCustom = readdir($dHandle))) {
                // Only process files that contain *.json
                if (preg_match("/.json$/", $menuCustom)) {
                    $selectedTag = ($selected == $menuCustom) ? "selected" : "";
                    $output .= "<option value='" . attr($menuCustom) . "' " . $selectedTag . ">";
                    // Drop the .json and translate the name
                    $output .= xlt(substr($menuCustom, 0, -5));
                    $output .= "</option>";
                }
            }

            closedir($dHandle);
        }

        $output .= "</select>";
        return $output;
    }

    /**
     * Collect the MainMenuRole for logged in user.
     *
     * @return string Identifier for the MainMenuRole
     */
    private function getMenuRole()
    {
        $userService = new UserService();
        $user = $userService->getCurrentlyLoggedInUser();
        $mainMenuRole = $user->getMainMenuRole();
        if (empty($mainMenuRole)) {
            $mainMenuRole = "standard";
        }

        return $mainMenuRole;
    }

    // This creates menu entries for all encounter forms.
    //
    protected function updateVisitForms(&$menu_list)
    {
        $baseURL = "/interface/patient_file/encounter/load_form.php?formname=";
        $menu_list->children = array();

        $lres = sqlStatement("SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec " .
            "FROM layout_group_properties WHERE " .
            "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 " .
            "ORDER BY grp_seq, grp_title");

        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id']; // should start with LBF
            $title = $lrow['title'];
            $formURL = $baseURL . urlencode($option_id);
            $formEntry = new \stdClass();
            $formEntry->label = xl_form_title($title);
            $formEntry->url = $formURL;
            $formEntry->requirement = 2;
            $formEntry->target = 'enc';
            // Plug in ACO attribute, if any, of this LBF.
            if (!empty($lrow['grp_aco_spec'])) {
                $tmp = explode('|', $lrow['grp_aco_spec']);
                if (!empty($tmp[1])) {
                    $formEntry->acl_req = array($tmp[0], $tmp[1], 'write', 'addonly');
                }
            }
            array_push($menu_list->children, $formEntry);
        }

        // Traditional forms
        $reg = getRegistered(1, 'unlimited', 0, 'patient');
        if (!empty($reg)) {
            foreach ($reg as $entry) {
                $option_id = $entry['directory'];
                $title = trim($entry['nickname']);
                if ($option_id == 'fee_sheet') {
                    continue;
                }

                if ($option_id == 'newpatient') {
                    continue;
                }

                if (empty($title)) {
                    $title = $entry['name'];
                }

                $formURL = $baseURL . urlencode($option_id);
                $formEntry = new \stdClass();
                $formEntry->label = xl_form_title($title);
                $formEntry->url = $formURL;
                $formEntry->requirement = 2;
                $formEntry->target = 'enc';
                // Plug in ACO attribute, if any, of this form.
                $tmp = explode('|', $entry['aco_spec']);
                if (!empty($tmp[1])) {
                    $formEntry->acl_req = array($tmp[0], $tmp[1], 'write', 'addonly');
                }

                array_push($menu_list->children, $formEntry);
            }
        }
    }
}
