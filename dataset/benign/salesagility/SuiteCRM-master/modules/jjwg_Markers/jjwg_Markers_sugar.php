<?php

class jjwg_Markers_sugar extends Basic
{
    public $new_schema = true;
    public $module_dir = 'jjwg_Markers';
    public $object_name = 'jjwg_Markers';
    public $table_name = 'jjwg_markers';
    public $importable = true;
    public $disable_row_level_security = true;
    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $city;
    public $state;
    public $country;
    public $jjwg_maps_lat;
    public $jjwg_maps_lng;
    public $marker_image;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @deprecated deprecated since version 7.6, PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code, use __construct instead
     */
    public function jjwg_Markers_sugar()
    {
        $deprecatedMessage = 'PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code';
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->deprecated($deprecatedMessage);
        } else {
            trigger_error($deprecatedMessage, E_USER_DEPRECATED);
        }
        self::__construct();
    }


    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL': return true;
        }
        return false;
    }
}
