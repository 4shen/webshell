<?php

namespace Microweber\Utils;

use Microweber\Providers\Modules;

$parser_cache_object = false; //global cache storage
$mw_replaced_edit_fields_vals = array();
$mw_replaced_edit_fields_vals_inner = array();
$mw_replaced_codes_tag = array();

$mw_replaced_textarea_tag = array();
$local_mw_replaced_modules_ids_grouped = array();
$local_mw_replaced_modules = array();

$mw_parser_nest_counter_level = 0;
$mw_parser_replace_inc = 0;
$mod_tag_replace_inc = 0;
$other_html_tag_replace_inc = 0;

class Parser
{
    public $app;
    public $page = array();
    public $params = array();

    private $mw_replaced_modules_tags = array();
    private $mw_replaced_modules_values = array();

    private $_mw_parser_passed_hashes = array();
    private $_mw_parser_passed_hashes_rel = array();
    private $_mw_parser_passed_replaces = array();

    private $_mw_parser_passed_replaces_inner = array();
    private $_mw_parser_replaced_tags = array();
    private $_mw_parser_replaced_html_comments = array();
    private $_replaced_modules_values = array();
    private $_replaced_modules = array();
    private $_replaced_codes = array();
    private $_replaced_input_tags = array();
    private $_replaced_input_tags_inner_loops = array();
    private $_existing_module_ids = array();
    private $_existing_module_ids_grouped = array();
    private $_existing_module_ids_map = array();
    private $_current_parser_rel = false;
    private $_current_parser_field = false;
    private $_current_parser_module_of_type = array();
    private $have_more = false;
    private $have_more_is_set = false;
    private $prev_module_data = array();
    private $iter_parent = array();
    private $_mw_edit_field_map = array();
    private $_additional_parsers = array();
    public $current_module_params = false;
    public $current_module = false;

    public function __construct($app = null)
    {
        if (!is_object($app)) {
            $this->app = mw();
        } else {
            $this->app = $app;
        }

        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

    }

    public function register($callback, $type='module'){

        if(!isset($this->_additional_parsers[$type])){
            $this->_additional_parsers[$type] = array();
        }
        $this->_additional_parsers[$type][] = $callback;
    }


    public function process($layout, $options = false, $coming_from_parent = false, $coming_from_parent_id = false, $previous_attrs = false)
    {
        static $first_known_mod;
        static $it_loop2;


        if (!$it_loop2) {
            $it_loop2 = 0;
        }

        global $mw_replaced_edit_fields_vals;
        // global $mod_tag_replace_inc;
        global $other_html_tag_replace_inc;
        global $mw_replaced_codes_tag;
        global $mw_replaced_textarea_tag;
        //global $local_mw_replaced_modules_ids_grouped;
        //  global $local_mw_replaced_modules;
        $coming_from_parent_strz1 = false;
        $root_module_id = false;
        $coming_from_parentz = false;
        $par_id_mod_count = 'global';
        $static_parser_mem_crc = 'global';


        $it = 0;
        $it_loop = 0;
        $it_loop1 = 0;

//d('have_more'.$this->have_more);

        $mod_tag_replace_inc = 0;
        $previous_attrs2 = $previous_attrs;
        if (!isset($parser_mem_crc)) {
            $parser_mem_crc = 'parser_' . crc32($layout) . content_id();
            if ($coming_from_parent_id) {
                $parser_modules_crc = 'parser_modules' . crc32($coming_from_parent_id) . content_id() . crc32(json_encode($previous_attrs));

            } else if ($previous_attrs) {
                $parser_modules_crc = 'parser_modules' . crc32($layout) . content_id() . crc32(json_encode($previous_attrs));

            } else {
                $parser_modules_crc = 'parser_modules' . crc32($layout) . content_id();

            }

        }
        $static_parser_mem_crc = $parser_mem_crc;

        if (!$static_parser_mem_crc) {
            //    $static_parser_mem_crc = $parser_mem_crc;
        }
        //$this->layout = $layout;
        static $process_started;
        $local_mw_replaced_modules = array();
        $local_mw_replaced_modules_ids_grouped = array();
        if ($process_started == false) {
            $process_started = true;

            $this->app->event_manager->trigger('parser.process', $layout);
        }

        if (isset($mw_replaced_edit_fields_vals[$parser_mem_crc])) {
            //d($parser_mem_crc);
            return $mw_replaced_edit_fields_vals[$parser_mem_crc];
        }

        global $mw_parser_replace_inc;
        $layout = str_replace('<?', '&lt;?', $layout);


        /*    $script_pattern = "/<textarea[^>]*>(.*)<\/textarea>/Uis";
            preg_match_all($script_pattern, $layout, $mw_script_matches);

            if (!empty($mw_script_matches)) {
                foreach ($mw_script_matches [0] as $key => $value) {
                    if ($value != '') {
                        $v1 = crc32($value) . $mw_parser_replace_inc++;
                        $v1 = '<tag-textarea>mw_replace_back_this_textarea_' . $v1 . '</tag-textarea>';
                        $layout = str_replace($value, $v1, $layout);
                        if (!isset($this->_replaced_input_tags[$v1])) {
                            $this->_replaced_input_tags[$v1] = $value;
                            $mw_replaced_textarea_tag[$v1] = $value;

                        }
                    }
                }
            }*/

        $layout = $this->_replace_tags_with_placeholders($layout);


        $script_pattern = "/<select[^>]*>(.*)<\/select>/Uis";
        preg_match_all($script_pattern, $layout, $mw_script_matches);
        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    $v1 = crc32($value);
                    $v1 = '<custom-replaced-tag-select1>mw_replace_back_this_select_' . $v1 . '</custom-replaced-tag-select1>';
                    $layout = str_replace($value, $v1, $layout);
                    if (!isset($this->_replaced_input_tags[$v1])) {
                        $this->_replaced_input_tags[$v1] = $value;

                    }
                    if (!isset($mw_replaced_codes_tag[$v1])) {
                        $mw_replaced_codes_tag[$v1] = $value;
                    }
                }
            }
        }


        $script_pattern = "/<style[^>]*>(.*)<\/style>/Uis";
        preg_match_all($script_pattern, $layout, $mw_script_matches);
        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    $v1 = crc32($value);
                    $v1 = '<custom-replaced-tag>mw_replace_back_this_style_' . $v1 . '</custom-replaced-tag>';
                    $layout = str_replace($value, $v1, $layout);
                    if (!isset($this->_replaced_input_tags[$v1])) {
                        $this->_replaced_input_tags[$v1] = $value;
                        $mw_replaced_textarea_tag[$v1] = $value;
                    }
                }
            }
        }


//
//        $script_pattern = "/<option[^>]*>(.*)<\/option>/Uis";
//        preg_match_all($script_pattern, $layout, $mw_script_matches);
//        if (!empty($mw_script_matches)) {
//            foreach ($mw_script_matches [0] as $key => $value) {
//                if ($value != '') {
//                    $v1 = crc32($value);
//                    $v1 = '<custom-replaced-tag>mw_replace_back_this_option_' . $v1 . '</custom-replaced-tag>';
//                    $layout = str_replace($value, $v1, $layout);
//                    if (!isset($this->_replaced_input_tags[$v1])) {
//                        $this->_replaced_input_tags[$v1] = $value;
//                       $mw_replaced_textarea_tag[$v1] = $value;
//                    }
//                }
//            }
//        }


        $script_pattern = "/<!--(?!<!)[^\[>].*?-->/";
        preg_match_all($script_pattern, $layout, $mw_script_matches);
        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    $v1 = crc32($value) . '-' . $other_html_tag_replace_inc++;
                    $v1 = '<tag-comment>mw_replace_back_this_html_comment_code_' . $v1 . '</tag-comment>';
                    $layout = str_replace($value, $v1, $layout);
                    if (!isset($this->_mw_parser_replaced_html_comments[$v1])) {
                        $this->_mw_parser_replaced_html_comments[$v1] = $value;
                    }
                }
            }
        }


        $layout = str_replace('<microweber module=', '<module data-type=', $layout);
        $layout = str_replace('</microweber>', '', $layout);
        $layout = str_replace('></module>', '/>', $layout);

        $script_pattern = '/<module[^>]*>/Uis';
        preg_match_all($script_pattern, $layout, $mw_script_matches);
        if (!isset($local_mw_replaced_modules[$static_parser_mem_crc])) {
            $local_mw_replaced_modules[$static_parser_mem_crc] = array();
        }
        if (!empty($mw_script_matches)) {
            $matches1 = $mw_script_matches[0];
            foreach ($matches1 as $key => $value) {
                if ($value != '') {
                    //   dd($key);
                    $v1 = crc32($value) . '-' . $parser_modules_crc . $key;
                    $v1 = '<tag>mw_replace_back_this_module_' . $v1 . '</tag>';
                    if (!isset($local_mw_replaced_modules[$static_parser_mem_crc][$v1])) {
                        $layout = $this->_str_replace_first($value, $v1, $layout);

                        $local_mw_replaced_modules[$static_parser_mem_crc][$v1] = $value;
                    }
                }
            }
        }

        $should_parse_only_vars = false;
        if (isset($options['parse_only_vars']) and $options['parse_only_vars']) {
            $should_parse_only_vars = true;
        }

        if (!$should_parse_only_vars) {
            $layout = str_replace('<mw ', '<module ', $layout);
            $layout = str_replace('<editable ', '<div class="edit" ', $layout);
            $layout = str_replace('</editable>', '</div>', $layout);

            $layout = str_replace('<microweber module=', '<module data-type=', $layout);
            $layout = str_replace('</microweber>', '', $layout);
            $layout = str_replace('></module>', '/>', $layout);
            $replaced_scripts = array();

            $script_pattern = "/<script[^>]*>(.*)<\/script>/Uis";

            preg_match_all($script_pattern, $layout, $mw_script_matches);

            if (!empty($mw_script_matches)) {
                foreach ($mw_script_matches [0] as $key => $value) {
                    if ($value != '') {
                        $v1 = crc32($value);

                        $v1 = '<x-tag> mw_replace_back_this_script_' . $v1 . ' </x-tag>';
                        $layout = str_replace($value, $v1, $layout);
                        if (!isset($replaced_scripts[$v1])) {
                            $replaced_scripts[$v1] = $value;
                        }
                    }
                }
            }
//
            $script_pattern = "/<code[^>]*>(.*)<\/code>/Uis";
            preg_match_all($script_pattern, $layout, $mw_script_matches);

            if (!empty($mw_script_matches)) {
                foreach ($mw_script_matches [0] as $key => $value) {
                    if ($value != '') {
                        $v1 = crc32($value);
                        $v1 = '<tag>mw_replace_back_this_code_' . $v1 . '</tag>';
                        $layout = str_replace($value, $v1, $layout);
                        if (!isset($replaced_scripts[$v1])) {
                            $this->_replaced_codes[$v1] = $value;
                            $mw_replaced_codes_tag[$v1] = $value;
                        }
                    }
                }
            }


//


            $more = $this->_do_we_have_more_edit_fields_for_parse($layout);
            if ($more) {
                // bug ?
                $layout = $this->_replace_editable_fields($layout);
            }

            $layout = str_replace('<microweber module=', '<module data-type=', $layout);
            $layout = str_replace('</microweber>', '', $layout);
            $layout = str_replace('></module>', '/>', $layout);
            $script_pattern = '/<module[^>]*>/Uis';

            preg_match_all($script_pattern, $layout, $mw_script_matches);
            if (!empty($mw_script_matches)) {
                $matches1 = $mw_script_matches[0];
                foreach ($matches1 as $key => $value) {
                    if ($value != '') {
                        $v1 = crc32($value) . '-' . $parser_modules_crc . $key;

                        $v1 = '<tag>mw_replace_back_this_module_111' . $v1 . '</tag>';

                        if (!isset($local_mw_replaced_modules[$static_parser_mem_crc][$v1])) {
                            $layout = $this->_str_replace_first($value, $v1, $layout);
                            $local_mw_replaced_modules[$static_parser_mem_crc][$v1] = $value;
                        }
                    }
                }
            }


//d($local_mw_replaced_modules);
            $this->have_more = !empty($mw_script_matches);

            if (!empty($replaced_scripts)) {
                foreach ($replaced_scripts as $key => $value) {
                    if ($value != '') {
                        $layout = str_replace($key, $value, $layout);
                    }
                    unset($replaced_scripts[$key]);
                }
            }

            $parser_ed_field = array();
            if (isset($this->_mw_edit_field_map[$parser_mem_crc])) {
                //  dd($this->_mw_edit_field_map[$parser_mem_crc]);
            }

            if (is_array($local_mw_replaced_modules) and !empty($local_mw_replaced_modules)) {


                $attribute_pattern = '@
			(?P<name>\w+)# attribute name
			\s*=\s*
			(
				(?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote) # a quoted value
				| # or
				(?P<value_unquoted>[^\s"\']+?)(?:\s+|$)  # an unquoted value (terminated by whitespace or EOF)
				)
@xsi';

                $attribute_pattern = '@(?P<name>[a-z-_A-Z]+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi';
                $attribute_pattern = '@(?P<name>[a-z-_A-Z]+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi';

                $attrs = array();
                foreach ($local_mw_replaced_modules as $parse_key => $parse_item) {


                    $parent_of_iteration = false;


                    //$parse_item  = array_reverse($parse_item);
                    foreach ($parse_item as $key => $value) {
                        $replace_key = $key;
                        $replace_key2 = $parse_key . $key . $parser_mem_crc;
                        if (isset($this->mw_replaced_modules_values[$replace_key2])) {

                            //$layout = $this->_str_replace_first($key, $this->mw_replaced_modules_values[$replace_key2], $layout);
                            continue;
                        }



                        if ($value != '') {

                            $mw_attrs_key_value_seperator = "__MW_PARSER_ATTR_VAL__";

//                            if (isset($this->mw_replaced_modules_values[$replace_key])) {
////
////                                continue;
////                            }

                            $attrs = array();
                            if (preg_match_all($attribute_pattern, $value, $attrs1, PREG_SET_ORDER)) {
                                foreach ($attrs1 as $item) {
                                    $m_tag = trim($item[0], "\x22\x27");
                                    $m_tag = trim($m_tag, "\x27\x22");
                                    $m_tag = preg_replace('/=/', $mw_attrs_key_value_seperator, $m_tag, 1);


                                    $m_tag = explode($mw_attrs_key_value_seperator, $m_tag);

                                    $a = trim($m_tag[0], "''");
                                    $a = trim($a, '""');
                                    $b = trim($m_tag[1], "''");
                                    $b = trim($b, '""');
                                    if (isset($m_tag[2])) {
                                        $rest_pieces = $m_tag;
                                        if (isset($rest_pieces[0])) {
                                            unset($rest_pieces[0]);
                                        }
                                        if (isset($rest_pieces[1])) {
                                            unset($rest_pieces[1]);
                                        }
                                        $rest_pieces = implode($mw_attrs_key_value_seperator, $rest_pieces);
                                        $b = $b . $rest_pieces;
                                    }

                                    $attrs[$a] = $b;
                                }
                            }

                            $m_tag = ltrim($value, '<module');

                            $m_tag = rtrim($m_tag, '/>');
                            $m_tag = rtrim($m_tag);
                            $userclass = '';

                            $module_html_tag = 'div';

                            $module_html = "<__MODULE_HTML_TAG__ class='__USER_DEFINED_CLASS__ __MODULE_CLASS__ __WRAP_NO_WRAP__' __MODULE_ID__ __MODULE_NAME__";

                            $module_has_class = false;
                            if (!empty($attrs)) {
                                if (isset($attrs['module']) and $attrs['module']) {
                                    $attrs['data-type'] = $attrs['module'];
                                    unset($attrs['module']);
                                }
                                if (isset($attrs['parent-module'])) {
                                    $coming_from_parent = $attrs['parent-module'];
                                }
                                if (isset($attrs['parent-module-id'])) {
                                    $coming_from_parent_id = $attrs['parent-module-id'];
                                }


//                                if (isset($attrs['module-id']) and $attrs['module-id'] != false) {
//                                    $attrs['id'] = $attrs['module-id'];
//                                }


//                            if ($coming_from_parent == true) {
//                                $attrs['parent-module'] = $coming_from_parent;
//                            }
//                            if ($coming_from_parent_id == true) {
//                                $attrs['parent-module-id'] = $coming_from_parent_id;
//                            }
                                if (isset($attrs['type']) and $attrs['type']) {
                                    $attrs['data-type'] = $attrs['type'];
                                    unset($attrs['type']);
                                }

                                $z = 0;
                                $mod_as_element = false;
                                $mod_no_wrapper = false;
                                $module_name = false;

                                if (isset($attrs['data-module'])) {
                                    $attrs['data-type'] = $attrs['data-module'];
                                    unset($attrs['data-module']);
                                }
                                foreach ($attrs as $nn => $nv) {
                                    if ($nn == 'class') {
                                        $module_has_class = $userclass = $nv;
                                        if (strstr($nv, 'module-as-element')) {
                                            $mod_as_element = true;
                                            $userclass = str_replace('module-as-element', '', $userclass);
                                        }
                                        $userclass = str_replace(' module  module ', 'module ', $userclass);
                                        $userclass = str_replace('ui-sortable', '', $userclass);
                                        $userclass = str_replace('module-item', '', $userclass);
                                        $userclass = str_replace('module module module', 'module', $userclass);
                                        $userclass = str_replace('module  module ', 'module ', $userclass);
                                    } else {
                                        //  $module_html .= " {$nn}='{$nv}'  ";
                                    }

                                    if ($nn == 'module') {
                                        $module_name = $nv;
                                        $attrs['data-type'] = $module_name;
                                        unset($attrs[$nn]);
                                    }

                                    if ($nn == 'no_wrap') {
                                        $mod_no_wrapper = true;
                                        unset($attrs[$nn]);
                                    }
                                    if ($nn == 'data-no-wrap') {
                                        $mod_no_wrapper = true;
                                        unset($attrs[$nn]);
                                    }
                                    if ($nn == 'data-module-name') {
                                        $module_name = $nv;
                                        $attrs['data-type'] = $module_name;
                                        unset($attrs[$nn]);
                                    }
                                    if ($nn == 'data-module-name-enc') {
                                        unset($attrs[$nn]);
                                    }
                                    if ($nn == 'type') {
                                        $module_name = $nv;
                                        $attrs['data-type'] = $module_name;
                                        unset($attrs[$nn]);
                                    }

                                    if ($nn == 'data-type') {
                                        $module_name = $nv;
                                    }
                                    if ($nn == 'data-module') {
                                        $attrs['data-type'] = $module_name;
                                        $module_name = $nv;
                                    }
                                    ++$z;
                                }
                                $module_title = false;
                                if (!isset($module_name) or !$module_name) {
                                    $module_html = false;
                                    continue;
                                } else if (isset($module_name)) {
                                    $module_class = $this->module_css_class($module_name);
                                    $module_title = module_info($module_name);


                                    if (!isset($attrs['id'])) {

                                        global $mw_mod_counter;
                                        ++$mw_mod_counter;


                                        $mod_id = '';
                                        $mod_id2 = '';


                                        if (!defined('CONTENT_ID')) {
                                            $mod_id = $mod_id . '-uid-fixme-' . uniqid();

                                        }


                                        if (!$mod_id) {
                                            $mod_id = $module_class;
                                            if ($coming_from_parent_id and $coming_from_parent) {
                                                //  $mod_id = $module_name;

                                            }
                                            if ($coming_from_parent_id and !$coming_from_parent) {
                                                $mod_id = $mod_id . '-should-not-get-here-' . $coming_from_parent_id;

                                            } else {


                                            }

                                        }
                                        if ($coming_from_parent_id and $coming_from_parent) {
                                            $mod_id = $coming_from_parent_id . '-' . $module_name;

                                        }


                                        $mod_id = $this->_str_clean_mod_id($mod_id);


                                        static $last_content_id = null;
                                        if (!$coming_from_parent) {
                                            if (defined('CONTENT_ID') and CONTENT_ID == 0) {
                                                if ($last_content_id == null) {
                                                    $last_content_id = $this->app->database_manager->last_id('content');
                                                }
                                                $last_content_id = intval($last_content_id) + 1;
                                                $mod_id = $mod_id . '-' . $last_content_id;
                                            } elseif (defined('CONTENT_ID')) {
                                                $mod_id = $mod_id . '-' . CONTENT_ID;
                                            }
                                        }

                                        $it++;

//
//                                        $skip= 0;
//
//                                        if(!isset($this->_existing_module_ids_grouped[$coming_from_parent_id])){
//                                            $this->_existing_module_ids_grouped[$coming_from_parent_id] = array();
//                                        }
//                                        if(isset($this->_existing_module_ids_grouped[$coming_from_parent_id]) and isset($this->_existing_module_ids_grouped[$coming_from_parent_id][$mod_id])){
//                                            $skip= 1;
//                                        }
//                                         $skip= 0;


                                        if ($coming_from_parent_id != false) {
                                            $par_id_mod_count = $parse_key;
                                            //$par_id_mod_count =$parser_mem_crc. $parse_key.$key. $coming_from_parent.$coming_from_parent_id;
                                            //$par_id_mod_count = $coming_from_parent.$coming_from_parent_id;
                                            //   $par_id_mod_count = $static_parser_mem_crc;
                                            //    $par_id_mod_count = $parser_mem_crc;
                                            //    $par_id_mod_count = $parser_modules_crc;
                                            $par_id_mod_count = $coming_from_parent_id;


                                        }
                                        //   $par_id_mod_count = $parser_mem_crc;
                                        $par_id_mod_count = $parse_key;


                                        if ($this->_current_parser_rel and $this->_current_parser_rel) {
                                            //  $par_id_mod_count = $coming_from_parent_id.'ed-'.$this->_current_parser_rel.$this->_current_parser_rel;
                                            //    $par_id_mod_count = $par_id_mod_count.$this->_current_parser_rel.$this->_current_parser_rel;

                                        }


                                        if (!isset($local_mw_replaced_modules_ids_grouped[$par_id_mod_count])) {
                                            $local_mw_replaced_modules_ids_grouped[$par_id_mod_count] = array();
                                        }
                                        if (!isset($local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name])) {
                                            $local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name] = 0;
                                        }

                                        if (!isset($this->_existing_module_ids_grouped[$par_id_mod_count])) {
                                            $this->_existing_module_ids_grouped[$par_id_mod_count] = array();
                                        }

                                        // if (isset($this->_existing_module_ids[$mod_id])) {



                                        if (isset($local_mw_replaced_modules_ids_grouped[$par_id_mod_count]) and $local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name]) {

                                            $inc_mod_num = $local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name];


                                            $mod_id = $mod_id . '--' . $inc_mod_num;

                                        } else if (isset($this->_existing_module_ids_grouped[$par_id_mod_count][$mod_id])) {
                                            //    if ( !$skip) {
                                            ++$it_loop;
                                            $inc_mod_num = 0;


                                            if (isset($this->_current_parser_module_of_type[$par_id_mod_count])) {
                                                if (isset($this->_current_parser_module_of_type[$par_id_mod_count][$module_name])) {
                                                    $inc_mod_num = $this->_current_parser_module_of_type[$par_id_mod_count][$module_name];
//
                                                }

                                            } else {
                                                // $inc_mod_num = 1;
                                                //    $inc_mod_num = $it_loop;

                                            }

                                            if ($coming_from_parent_id) {
                                                //$mod_id =  $mod_id . '-' .$coming_from_parent_id;
                                            }

                                            //$mod_id = $mod_id . '--' . ++$it;
                                            if ($inc_mod_num) {
                                                $mod_id = $mod_id . '--' . $inc_mod_num;
                                            }

                                            // $mod_id = $mod_id . '--' . ++$this->_current_parser_module_of_type[$module_name];
                                            //  if (isset($this->_existing_module_ids[$mod_id])) {

                                            // if (isset($this->_existing_module_ids[$mod_id])) {
                                            if (isset($this->_existing_module_ids_grouped[$par_id_mod_count][$mod_id])) {
                                                // if (isset($this->_existing_module_ids_grouped[$coming_from_parent_id][$mod_id])) {


                                                $mod_id_probe = false;
                                                if ($coming_from_parent_id) {

                                                    $mod_id_probe = $mod_id;
                                                    //$mod_id_probe =  $mod_id . '-coming_from_parent_id' .$coming_from_parent_id;
                                                    //   $mod_id_probe = $coming_from_parent_id . '-' . $mod_id;
                                                    //   $mod_id_probe = str_replace('module-', '', $mod_id_probe);

                                                } else {
                                                    //   $mod_id = $mod_id . '-' . $last_content_id;
                                                }

                                                //if ($mod_id_probe and !isset($this->_existing_module_ids[$mod_id_probe])) {
                                                if ($mod_id_probe and !isset($this->_existing_module_ids_grouped[$par_id_mod_count][$mod_id])) {
                                                    $mod_id = $mod_id_probe;
                                                } else {
                                                    //  $mod_id = $mod_id . '--' .$it_loop;
                                                    //  $mod_id = $mod_id . '-' . $last_content_id;

                                                    if ($mod_id_probe and !isset($this->_existing_module_ids_grouped[$par_id_mod_count][$mod_id])) {
                                                        $mod_id = $mod_id_probe;
                                                    } else {


                                                        if (!$inc_mod_num and isset($local_mw_replaced_modules_ids_grouped[$par_id_mod_count]) and $local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name]) {

                                                            $inc_mod_num = $local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name];


                                                            $mod_id = $mod_id . '--' . $inc_mod_num;

                                                        } else {

                                                        }


                                                    }

                                                }


                                            }

                                        } else {
                                            if (!$it_loop and $coming_from_parent_id) {
                                                //  $mod_id = $mod_id . '-1asdds';
                                            }

                                            //  $mod_id = $mod_id . '-1asdds';
                                        }


                                        $this->_existing_module_ids[$mod_id] = $mod_id;

                                        $this->_existing_module_ids_grouped[$par_id_mod_count][$mod_id] = $mod_id;
                                        $local_mw_replaced_modules_ids_grouped[$par_id_mod_count][$module_name]++;

                                        // $this->_existing_module_ids_map[$parse_key.$replace_key] = $mod_id;
                                        //  $this->_current_parser_module_of_type[$par_id_mod_count][$module_name] = $mod_id;

                                        $attrs['id'] = $mod_id;

                                        $module_html = str_replace('__MODULE_ID__', "id='{$attrs['id']}'", $module_html);



                                    } else {
                                        $module_html = str_replace('__MODULE_ID__', '', $module_html);
                                    }


                                    $attrs2 = array();
                                    if (is_array($module_title) and isset($module_title['name'])) {
                                        $module_title['name'] = addslashes($module_title['name']);
                                        $module_html = str_replace('__MODULE_NAME__', ' data-mw-title="' . $module_title['name'] . '"', $module_html);
                                    } else {
                                        $module_html = str_replace('__MODULE_NAME__', '', $module_html);
                                    }


                                    if (strstr($module_name, 'admin')) {
                                        $module_html = str_replace('__WRAP_NO_WRAP__', '', $module_html);
                                    } else {
                                        $module_html = str_replace('__WRAP_NO_WRAP__', '', $module_html);
                                    }
                                    $module_name_url = $this->app->url_manager->slug($module_name);

                                    if ($mod_as_element == false) {
                                        if ($module_name == 'text' or $module_name == 'title' or $module_name == 'text/empty_element' or $module_name == 'text/multiple_columns') {
                                             $module_html = str_replace('__MODULE_CLASS__', 'layout-element ' . $module_name_url, $module_html);
                                        } else {
                                            $module_html = str_replace('__MODULE_CLASS__', 'module ' . $module_class, $module_html);
                                        }
                                        $userclass = str_replace(trim($module_class), '', $userclass);
                                        $userclass = trim(str_replace(' -module ', 'module ', $userclass));
                                        $userclass = trim(str_replace(' module ', ' ', $userclass));
                                        $userclass = trim(str_replace(' disabled module ', ' module ', $userclass));
                                        $module_class = trim(str_replace(' disabled module ', ' module ', $module_class));
                                        $userclass = trim(str_replace(' module module ', ' module ', $userclass));
                                        $userclass = trim(str_replace('module module ', 'module ', $userclass));
                                        $module_html = str_replace('__MODULE_CLASS_NAME__', '' . $module_class, $module_html);
                                       // $module_html = str_replace('__USER_DEFINED_CLASS__', $userclass, $module_html);

                                    } else {
                                        $userclass = trim(str_replace(' -module ', '', $userclass));

                                        $module_html = str_replace('__MODULE_CLASS__', 'element ' . $module_name_url, $module_html);
                                        $mod_no_wrapper = true;
                                    }
                                    $module_html = str_replace('__USER_DEFINED_CLASS__', $userclass, $module_html);

                                    if ($coming_from_parent == false) {

                                        $coming_from_parentz = $module_name;
                                        $coming_from_parent_strz1 = $attrs['id'];
                                        $previous_attrs2 = $attrs;
                                        $attrs['parent-module'] = $coming_from_parentz;
                                        $attrs['parent-module-id'] = $coming_from_parent_strz1;
                                        $this->prev_module_data = $attrs;

                                    } else {
                                        $par_id_mod_count = $coming_from_parent_id;
                                        $attrs['parent-module-id'] = $coming_from_parent_id;
                                        $attrs['parent-module'] = $coming_from_parent;
                                        $this->prev_module_data = $attrs;

                                        $coming_from_parentz = $module_name;
                                        $coming_from_parent_strz1 = $attrs['id'];
                                    }


//                                if (isset($attrs['parent-module-id']) and ($attrs['parent-module-id'] == $attrs['id'])) {
//                                    // if (!isset($attrs['module_settings'])) {
//                                    $attrs['parent-module'] = false;
//                                    $attrs['parent-module-id'] = false;
//                                    $coming_from_parent_strz1 = false;
//                                    $coming_from_parentz = false;
//                                    $previous_attrs2 = array();
//                                    $this->prev_module_data = array();
//
//                                    //  }
//                                }


                                    $attrs = array_filter($attrs, function ($value) {
                                        return ($value !== null && $value !== false && $value !== '');
                                    });
                                    if (is_array($previous_attrs2)) {

                                        $previous_attrs2 = array_filter($previous_attrs2, function ($value) {
                                            return ($value !== null && $value !== false && $value !== '');
                                        });

                                    }

                                    //   if($par_id_mod_count != 'global'){

                                    // }


                                    if (!isset($this->_current_parser_module_of_type[$par_id_mod_count])) {
                                        $this->_current_parser_module_of_type[$par_id_mod_count] = array();
                                    }
                                    if (!isset($this->_current_parser_module_of_type[$par_id_mod_count])) {
                                        $this->_current_parser_module_of_type[$par_id_mod_count] = array();
                                    }
                                    if (!isset($this->_current_parser_module_of_type[$par_id_mod_count][$module_name])) {
                                        $this->_current_parser_module_of_type[$par_id_mod_count][$module_name] = 0;
                                    }
                                    $this->_current_parser_module_of_type[$par_id_mod_count][$module_name]++;

                                    $mod_content = $this->load($module_name, $attrs);
                                    if($this->current_module and isset($this->current_module['settings'] ) and isset($this->current_module['settings']['html_tag']) and $this->current_module['settings']['html_tag']){
                                        $module_html_tag = $this->current_module['settings']['html_tag'];
                                    }

//

                                    $plain_modules = mw_var('plain_modules');

                                    if ($plain_modules != false) {
                                        if (!defined('MW_PLAIN_MODULES')) {
                                            define('MW_PLAIN_MODULES', true);
                                        }
                                    }
                                    foreach ($attrs as $nn => $nv) {

                                        if ($nn != 'class') {
                                            $pass = true;
                                            if ($mod_no_wrapper) {
                                                if ($nn == 'id') {
                                                    $pass = false;

                                                }
                                            }

                                            if ($pass /*and $nv*/) {
                                               // $module_html .= " {$nn}='{$nv}'  ";
                                                $module_html .= " {$nn}=\"{$nv}\"  ";
                                               // $module_html .= " {$nn}={$nv}  ";
                                            }
                                        }
                                    }

                                    $plain_modules = false;
                                    unset($local_mw_replaced_modules[$parse_key][$key]);


                                    if ($this->current_module /*and isset($this->current_module['module_type']) and $this->current_module['module_type']*/) {
                                        $mod_content = $this->_process_additional_module_parsers($mod_content, $this->current_module,$this->current_module_params);
                                    }


                                    $mod_content = $this->_replace_tags_with_placeholders($mod_content);


                                    $proceed_with_parse = $this->_do_we_have_more_for_parse($mod_content);

                                    if ($proceed_with_parse == true) {
                                        $this->have_more = true;
                                        preg_match_all('/.*?class=..*?edit.*?.[^>]*>/', $mod_content, $layoutmatches);
                                        if (!empty($layoutmatches) and isset($layoutmatches[0][0])) {

                                            $pq_mod_inner = \phpQuery::newDocument($mod_content);
                                            $els_mod_inner = $pq_mod_inner['.edit'];
                                            if (count($els_mod_inner)) {
                                                $mod_content = $this->_replace_editable_fields($mod_content, false, $layout);

                                            }
                                            unset($pq_mod_inner);

                                        }
                                        //  $mod_content2 = $mod_content;
                                        $proceed_with_parse = $this->_do_we_have_more_for_parse($mod_content);

                                        if ($proceed_with_parse == true) {
                                            $mod_content = $this->process($mod_content, $options, $coming_from_parentz, $coming_from_parent_strz1, $previous_attrs2);
                                        }


                                        if (strpos($mod_content, '<inner-edit-tag>mw_saved_inner_edit_from_parent_edit_field</inner-edit-tag>') !== false) {

                                            if (!isset($this->_mw_parser_passed_replaces_inner[$parse_key])) {
                                                $mod_content = $this->_replace_editable_fields($mod_content, false, $layout);
                                                $proceed_with_parse = $this->_do_we_have_more_for_parse($mod_content);
                                                if ($proceed_with_parse == true) {
                                                    $mod_content = $this->process($mod_content, $options, $coming_from_parentz, $coming_from_parent_strz1, $previous_attrs2);
                                                }
                                                $this->_mw_parser_passed_replaces_inner[$parse_key] = $mod_content;
                                            } else {
                                                $mod_content = $this->_mw_parser_passed_replaces_inner[$parse_key];
                                            }

                                        }


                                    } else {
                                        $this->have_more = false;
                                        $this->prev_module_data = array();
                                        $it_loop2 = 0;
                                        $coming_from_parent_str = '';

                                    }


                                    global $other_html_tag_replace_inc;

                                    if ($mod_no_wrapper == false) {
                                        $coming_from_parent_str = '';

                                        $module_html .= $coming_from_parent_str . '>' . $mod_content . '</__MODULE_HTML_TAG__>';


                                        $module_html = str_replace('__MODULE_HTML_TAG__', $module_html_tag, $module_html);


                                    } else {


                                        $module_html = $mod_content;
                                    }
                                }
                                $it_loop1++;
                                $it_loop2++;


//                                if (!isset($this->_current_parser_module_of_type[$par_id_mod_count])) {
//                                    $this->_current_parser_module_of_type[$par_id_mod_count] = array();
//                                }
//                                if (!isset($this->_current_parser_module_of_type[$par_id_mod_count])) {
//                                    $this->_current_parser_module_of_type[$par_id_mod_count] = array();
//                                }
//                                if (!isset($this->_current_parser_module_of_type[$par_id_mod_count][$module_name])) {
//                                    $this->_current_parser_module_of_type[$par_id_mod_count][$module_name] = 0;
//                                }
//                                $this->_current_parser_module_of_type[$par_id_mod_count][$module_name]++;
//                                //$this->_current_parser_module_of_type[$par_id_mod_count][$module_name]++;


                                $this->mw_replaced_modules_values[$parser_mem_crc] = $module_html;
                                $layout = $this->_str_replace_first($value, $module_html, $layout);
                                $layout = $this->_str_replace_first($replace_key, $module_html, $layout);


                            }
                        }

                        $layout = $this->_str_replace_first($key, $value, $layout);
                    }
                }
            }
        } else {
            $this->have_more = false;
            $this->prev_module_data = array();
            $it_loop2 = 0;
        }

//        if (!empty($this->_replaced_codes)) {
//            foreach ($this->_replaced_codes as $key => $value) {
//                if ($value != '') {
//                    $layout = str_replace($key, $value, $layout);
//                }
//                unset($this->_replaced_codes[$key]);
//            }
//        }


        if (!empty($mw_replaced_codes_tag)) {
            foreach ($mw_replaced_codes_tag as $key => $value) {
                if ($value != '') {
                    $layout = str_replace($key, $value, $layout);
                }
            }
        }


        if (!empty($this->mw_replaced_modules_values)) {

            $search = array_keys($this->mw_replaced_modules_values);
            $reps = array_values($this->mw_replaced_modules_values);
            $layout = str_replace($search, $reps, $layout);
            $reps_arr = array();
            $reps_arr2 = array();
//            foreach ($this->mw_replaced_modules_values as $key => $value) {
//
//                if ($value != '') {
//                    $reps_arr[] = $key;
//                    $reps_arr2[] = $value;
//                    $layout = $this->_str_replace_first($key, $value, $layout);
//
//                    // $layout = str_replace($key, $value, $layout);
//                }
//            }
            //   $layout = str_replace($reps_arr, $reps_arr2, $layout);
        }


        //if (!$this->have_more or $root_module_id) {
        if (!empty($this->_mw_parser_replaced_html_comments)) {
            foreach ($this->_mw_parser_replaced_html_comments as $key => $value) {
                if ($value != '') {
                    $layout = str_replace($key, $value, $layout);
                }
                //unset($this->_mw_parser_replaced_html_comments[$key]);
            }
        }
        //}


        // if (!$coming_from_parent) {
        if (!empty($this->_replaced_input_tags)) {
            foreach ($this->_replaced_input_tags as $key => $value) {
                if ($value != '') {
                    $layout = str_replace($key, $value, $layout);
                }
                unset($this->_replaced_input_tags[$key]);
            }
        }

        // }

        //  if (!$coming_from_parent) {

        /*   if (!empty($mw_replaced_textarea_tag)) {
               foreach ($mw_replaced_textarea_tag as $key => $value) {
                   if ($value != '') {
                       $layout = str_replace($key, $value, $layout);
                   }
                   unset($mw_replaced_textarea_tag[$key]);
               }
           }*/


        // if (!$coming_from_parent_id) {
        if (!empty($this->_replaced_input_tags_inner_loops)) {
            foreach ($this->_replaced_input_tags_inner_loops as $key => $value) {
                if ($value != '') {
                    $layout = $this->_str_replace_first($key, $value, $layout);

                    // $layout = str_replace($key, $value, $layout);
                    //  $this->_replaced_input_tags_inner_loops[$key] = 1;
                }
                //    unset($this->_replaced_input_tags_inner_loops[$key]);
            }
        }

        //  }


        // }

        //  if ($coming_from_parent) {
//            if (!empty($mw_replaced_textarea_tag)) {
//                foreach ($mw_replaced_textarea_tag as $key => $value) {
//                    if ($value != '') {
//                        $layout = str_replace($key, $value, $layout);
//                    }
//                }
//            }
        // }

        if (!empty($mw_replaced_codes_tag)) {
            foreach ($mw_replaced_codes_tag as $key => $value) {
                if ($value != '') {
                    $layout = str_replace($key, $value, $layout);
                }
            }
        }


        if (!$coming_from_parent) {
            $layout = $this->replace_url_placeholders($layout);
        }

        $layout = str_replace('{rand}', uniqid() . rand(), $layout);
        $layout = str_replace('{SITE_URL}', $this->app->url_manager->site(), $layout);
        $layout = str_replace('{MW_SITE_URL}', $this->app->url_manager->site(), $layout);
        $layout = str_replace('%7BSITE_URL%7D', $this->app->url_manager->site(), $layout);
//        //  $mw_replaced_edit_fields_vals[$parser_mem_crc] = $layout;

        return $layout;
    }

    public $filter = array();

    public function filter($callback)
    {
        $this->filter[] = $callback;
    }

    private function _replace_editable_fields($layout, $no_cache = false, $from_parent = false)
    {
        global $mw_replaced_edit_fields_vals;
        global $mw_parser_nest_counter_level;
        global $mw_replaced_edit_fields_vals_inner;
        if (!isset($parser_mem_crc)) {
            $parser_mem_crc = 'parser_' . crc32($layout) . content_id();
            //   $parser_modules_crc = 'parser_modules' . crc32($layout) . content_id();
        }

        if (isset($this->_mw_parser_passed_replaces[$parser_mem_crc]) and !$no_cache) {
            if (isset($this->_mw_edit_field_map[$parser_mem_crc]) and isset($this->_mw_edit_field_map[$parser_mem_crc]['field']) and isset($this->_mw_edit_field_map[$parser_mem_crc]['rel'])) {
                $this->_current_parser_field = $this->_mw_edit_field_map[$parser_mem_crc]['field'];
                $this->_current_parser_rel = $this->_mw_edit_field_map[$parser_mem_crc]['rel'];
            }

            return $this->_mw_parser_passed_replaces[$parser_mem_crc];
        }
//        if (isset($mw_replaced_edit_fields_vals[$parser_mem_crc]) and !$no_cache) {
//            // return false;
//
//      //      return $mw_replaced_edit_fields_vals[$parser_mem_crc];
//        }
//        if ($no_cache and $from_parent) {
//            //    dd($parser_mem_crc, $layout,$from_parent);
//        }
//        if($from_parent){
//            $pq = \phpQuery::newDocument($from_parent);
//            $els = $pq['.edit'];
//
//            foreach ($els as $elem) {
//                $el_html = pq($elem)->html();
//                $layout = $el_html;
//            }
//        }

        if ($layout != '') {

            ++$mw_parser_nest_counter_level;
            $replaced_code_tags = array();
            $replaced_html_comment_tags = array();
            if ($this->_mw_parser_passed_replaces == null) {
                $this->_mw_parser_passed_replaces = array();
            }

            $mw_found_elems = '';
            $mw_found_elems_arr = array();
            $mw_elements_array = array('orig', $layout);
            $cached = false;


            $script_pattern = "/<pre[^>]*>(.*)<\/pre>/Uis";
            preg_match_all($script_pattern, $layout, $mw_script_matches);
            if (!empty($mw_script_matches)) {
                foreach ($mw_script_matches [0] as $key => $value) {
                    if ($value != '') {
                        $v1 = crc32($value);
                        $v1 = '<!-- mw_replace_back_this_pre_' . $v1 . ' -->';
                        $layout = str_replace($value, $v1, $layout);
                        if (!isset($replaced_code_tags[$v1])) {
                            $replaced_code_tags[$v1] = $value;
                        }
                    }
                }
            }

            $script_pattern = "/<code[^>]*>(.*)<\/code>/Uis";
            preg_match_all($script_pattern, $layout, $mw_script_matches);
            if (!empty($mw_script_matches)) {
                foreach ($mw_script_matches [0] as $key => $value) {
                    if ($value != '') {
                        $v1 = crc32($value);
                        $v1 = '<!-- mw_replace_back_this_pre_code_' . $v1 . ' -->';
                        $layout = str_replace($value, $v1, $layout);
                        if (!isset($replaced_code_tags[$v1])) {
                            $replaced_code_tags[$v1] = $value;
                        }
                    }
                }
            }


            $ch = mw_var($parser_mem_crc);
            if ($cached != false) {
                $mw_elements_array = $cached;
            } elseif ($ch != false) {
                $layout = $ch;
            } else {
                $pq = \phpQuery::newDocument($layout);
                $els = $pq['.edit'];
                $is_editable = true;
                foreach ($els as $elem) {

                    // iteration returns PLAIN dom nodes, NOT phpQuery objects
                    $tagName = $elem->tagName;
                    $name = pq($elem)->attr('field');
                    if (strval($name) == '') {
                        $name = pq($elem)->attr('data-field');
                    }
                    $rel = pq($elem)->attr('rel');
                    if ($rel == false) {
                        $rel = pq($elem)->attr('data-rel');
                        if ($rel == false) {
                            $rel = 'page';
                        }
                    }
                    $option_group = pq($elem)->attr('data-option_group');
                    if ($option_group == false) {
                        $option_group = 'editable_region';
                    }
                    $data_id = pq($elem)->attr('data-id');
                    if ($data_id == false) {
                        $data_id = pq($elem)->attr('rel-id');
                    }
                    if ($data_id == false) {
                        $data_id = pq($elem)->attr('rel_id');
                    }
                    if ($data_id == false) {
                        $data_id = pq($elem)->attr('data-rel-id');
                    }
                    $option_mod = pq($elem)->attr('data-module');
                    if ($option_mod == false) {
                        $option_mod = pq($elem)->attr('data-type');
                    }

                    if ($option_mod == false) {
                        $option_mod = pq($elem)->attr('type');
                    }
                    $name = trim($name);
                    $get_global = false;
                    $field = $name;
                    $use_id_as_field = $name;
                    if ($rel == 'global') {

                        $get_global = true;
                    } else {
                        $get_global = false;
                    }

                    $try_inherited = false;

                    if ($rel == 'content' or $rel == 'page' or $rel == 'post') {


                        if ($rel == 'page') {
                            if (!isset($data_id) or $data_id == false) {
                                $data_id = PAGE_ID;
                            }
                        }
                        if ($rel == 'post') {
                            if (!isset($data_id) or $data_id == false) {
                                $data_id = POST_ID;
                            }
                            if (!isset($data_id) or $data_id == false) {
                                $data_id = PAGE_ID;
                            }
                        }
                        if (!isset($data_id) or $data_id == false) {
                            $data_id = content_id();
                        }

                        $get_global = false;
                        $data_id = intval($data_id);
                        $data = $this->app->content_manager->get_by_id($data_id);
                        if ($field != 'content' and $field != 'content_body' and $field != 'title') {
                            $data[$field] = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}&rel_id=" . $data_id);
                        }


                    } elseif ($rel == 'inherit') {
                        $get_global = false;
                        if (!isset($data_id) or $data_id == false) {
                            $data_id = PAGE_ID;
                        }
//                        $data_inh_check = $this->app->content_manager->get_by_id($data_id);
//
//                        if (isset($data_inh_check['id']) and isset($data_inh_check['layout_file']) and (trim($data_inh_check['layout_file']) != '') and $data_inh_check['layout_file'] != 'inherit') {
//                            $inh = $data_inh_check['id'];
//                        } else {
//                            $inh = $this->app->content_manager->get_inherited_parent($data_id);
//                        }
                        $inh = $this->app->content_manager->get_inherited_parent($data_id);

                        if ($inh != false and intval($inh) != 0) {
                            $try_inherited = true;
                            $data_id = $inh;
                            // $rel = 'content';
                            $data = $this->app->content_manager->get_by_id($data_id);
                        } else {
                            // $rel = 'content';
                            $data = $this->app->content_manager->get_page($data_id);
                        }

                        if ($field != 'content' and $field != 'content_body' and $field != 'title') {
                            $data[$field] = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}&rel_id=" . $data_id);
                        }
                    } elseif ($rel == 'global') {
                        $get_global = 1;
                        $cont_field = false;
                    } elseif ($rel == 'module') {

                        $data[$field] = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}");

                    }/* elseif (isset($attr['post'])) {
                        $get_global = false;
                        $data = $this->app->content_manager->get_by_id($attr['post']);
                        if ($data == false) {
                            $data = $this->app->content_manager->get_page($attr['post']);
                        }
                    } elseif (isset($attr['category'])) {
                        $get_global = false;
                        $data = $this->app->category_manager->get_by_id($attr['category']);
                    } elseif (isset($attr['global'])) {
                        $get_global = true;
                    }*/
                    $cf = false;
                    $field_content = false;
                    $field_content_modified_date = false;
                    $orig_rel = $rel;


                    $this->_current_parser_rel = $rel;
                    $this->_current_parser_field = $field;

                    if (!empty($this->filter)) {
                        foreach ($this->filter as $filter) {
                            if (isset($data)) {
                                $new_data = call_user_func($filter, $data, $elem);
                                if (is_array($new_data) and !empty($new_data)) {
                                    $data = array_merge($data, $new_data);
                                }
                            }
                        }
                    }

                    if (isset($data[$field])) {
                        if (isset($data[$field])) {
                            //  $field_content = $data[$field];
                        }
                    } else {


                        if ($rel == 'page') {
                            $rel = 'content';
                        }
                        if ($rel == 'post') {
                            $rel = 'content';
                        }


                        $cont_field = false;
                        if (isset($data_id) and $data_id != 0 and trim($data_id) != '' and trim($field) != '') {
                            $cont_field = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}&rel_id=$data_id");
                            if ($cont_field == false and $try_inherited == true) {
                                $inh = $this->app->content_manager->get_inherited_parent($data_id);
                                if ($inh != false and intval($inh) != 0 and $inh != $data_id) {
                                    $data_id = $inh;
                                    $cont_field2 = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}&rel_id=$inh");
                                    if ($cont_field2 != false) {
                                        $rel = 'content';
                                        $data = $this->app->content_manager->get_by_id($inh);
                                        $cont_field = $cont_field2;
                                    }
                                }
                            }
                        } else {

                            if (isset($data_id) and trim($data_id) != '' and $field_content == false and isset($rel) and isset($field) and trim($field) != '') {
                                $cont_field = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}&rel_id=$data_id");
                                if ($cont_field != false) {
                                    $field_content = $cont_field;
                                }
                            } else {


                                $field_content = $cont_field = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}");


                            }
                        }

                        if ($cont_field != false) {
                            $field_content = $cont_field;
                        }



                    }
                    if ($rel == 'global') {
                        $field_content = false;
                        $get_global = 1;


                        $cont_field = $this->app->content_manager->edit_field("rel_type={$rel}&field={$field}");


                        if ($cont_field == false) {
                            if ($option_mod != false) {
                                $cont_field = $this->app->content_manager->edit_field("rel_type={$option_group}&field={$field}");
                            } else {
                                $cont_field = $this->app->content_manager->edit_field("rel_type={$option_group}&field={$field}");
                            }
                        } else {
                            $cont_field = $field_content = $cont_field;
                        }
                    }

                    $edit_field_content = false;

                    if (isset($data[$field])) {

                        $edit_field_content = $data[$field];
                    }
                    if ($use_id_as_field != false) {
                        if (isset($data[$use_id_as_field])) {
                            $edit_field_content = $data[$use_id_as_field];

                        }
                    }
                    if (!$edit_field_content) {
                        if (isset($cont_field['value'])) {
                            $edit_field_content = $cont_field['value'];
                        }
                    }


                    if(isset($data['updated_at'])){
                        $field_content_modified_date  = $data['updated_at'];
                     }

                    $this->_current_parser_rel = $rel;

                    $no_edit = false;

                    if ($field == 'content' and template_var('content') != false) {
//                        $field_content = template_var('content');
//                        template_var('content', false);
//                        $no_edit = template_var('no_edit');
                    }
                    // d($parser_mem_crc);


                    //$mw_replaced_edit_fields_vals[$parser_mem_crc] = $edit_field_content;

                    if ($edit_field_content) {
                        $field_content = $edit_field_content;

                    }
                    if ($field_content != false and $field_content != '' and is_string($field_content)) {


                        $parser_mem_crc2 = 'parser_field_content_' . $field . $rel . $data_id . crc32($field_content);

                        $ch2 = mw_var($parser_mem_crc);

                        if ($ch2 == false) {
                            $this->_mw_parser_passed_hashes[] = $parser_mem_crc2;
                            $this->_mw_parser_passed_hashes_rel[$rel][] = $parser_mem_crc2;
                            if ($field_content != false and $field_content != '') {
                                $mw_replaced_edit_fields_vals[$parser_mem_crc2] = $ch2;
                                $parser_mem_crc3 = 'mw_replace_back_this_editable_' . $parser_mem_crc2 . '';

                                $elem_clone = $elem->cloneNode();

                                $mw_found_elems = ',' . $parser_mem_crc2;
                                $mw_found_elems_arr[$parser_mem_crc2] = $field_content;
                                // $rep = pq($elem)->html();
                                $rep = pq($elem)->html();
                                //  $rep = trim($rep);
                                //   $rep = preg_replace("/(^\s+)|(\s+$)/us", "", $rep);


                                if ($no_edit != false or (isset($data) and isset($data['no_edit']) and $data['no_edit'] != false)) {
                                    $is_editable = false;
                                    if ($is_editable === false) {
                                        pq($elem)->removeClass('edit');
                                    } else {
                                    }
                                    $is_editable = 1;
                                }
                                //   $parser_mem_crc2_inner = 'parser_' . crc32($rep) . content_id();

                                if (strstr($field_content, '<inner-edit-tag>mw_saved_inner_edit_from_parent_edit_field</inner-edit-tag>')) {
                                    // $field_content = $this->_replace_editable_fields($field_content);
                                    $field_content = $this->_replace_editable_fields($field_content, $no_cache = false, $from_parent = $layout);
                                    if ($field_content) {
                                        pq($elem_clone)->html($field_content);
                                    }
                                } else {
                                    pq($elem_clone)->html($field_content);
                                }

                                if($field_content_modified_date and $this->app->user_manager->is_admin()){
                                    pq($elem_clone)->attr('itemprop','dateModified');
                                    pq($elem_clone)->attr('content',date("Y M d",strtotime($field_content_modified_date)));
                                  //  pq($elem_clone)->attr('itemscope','');
                                  //  pq($elem_clone)->attr('itemtype','http://schema.org/CreativeWork');

                                }
                                pq($elem)->replaceWith($elem_clone);

                                $mw_replaced_edit_fields_vals_inner[$parser_mem_crc3] = array('s' => $rep, 'r' => $field_content, 'rel' => $rel, 'field' => $field);
                                $this->_mw_edit_field_map[$parser_mem_crc] = array(
                                    'field' => $field,
                                    'rel' => $rel,
                                );
                            }
                        } else {

                        }
                        mw_var($parser_mem_crc2, 1);

//                        if(strstr($field_content,'<inner-edit-tag>mw_saved_inner_edit_from_parent_edit_field</inner-edit-tag>')){
//                            $field_content = $this->_replace_editable_fields($field_content);
//
//                        }

                    } else {
                        $el_html = pq($elem)->html();
                        if (strstr($el_html, '<inner-edit-tag>mw_saved_inner_edit_from_parent_edit_field</inner-edit-tag>')) {
                            pq($elem)->html('<!-- edit_field_not_found_in_database -->');
//dd($el_html);
                        }

                    }
                }

                $layout = $pq->htmlOuter();

                $pq->__destruct();


                $pq = null;

                unset($pq);
                if (!empty($mw_replaced_edit_fields_vals_inner)) {
                    $reps_arr = array();
                    $reps_arr2 = array();

                    foreach ($mw_replaced_edit_fields_vals_inner as $k => $v) {
                        if (isset($v['s'])) {
                            $reps_arr[] = $v['s'];
                            $reps_arr2[] = $v['r'];

                            $layout = $this->_str_replace_first($v['s'], $v['r'], $layout);
                            // $layout = str_ireplace($v['s'], $v['r'], $layout, $repc);

                            unset($mw_replaced_edit_fields_vals_inner[$k]);
                        }
                    }
                    // $layout = str_replace($reps_arr, $reps_arr2, $layout,$repc);
                }

                mw_var($parser_mem_crc, $layout);
                if ($mw_found_elems != '') {
                    $mw_elements_array['new'] = $layout;
                    $mw_elements_array['to_replace'] = $mw_found_elems;
                    $mw_elements_array['elems'] = $mw_found_elems_arr;
                } else {
                    $mw_elements_array['new'] = $layout;
                }
            }
        }
        if (isset($mw_elements_array) and !empty($mw_elements_array)) {
            if (isset($mw_elements_array['elems']) and isset($mw_elements_array['to_replace']) and isset($mw_elements_array['new'])) {
                $modified_layout = $mw_elements_array['new'];
                $reps = $mw_elements_array['elems'];

                $c = 1;
                foreach ($reps as $elk => $value) {
                    $elk_crc = crc32($elk);

                    $global_holder_hash = 'replaced' . $elk_crc;

                    if (!isset($mw_replaced_edit_fields_vals[$global_holder_hash])) {
                        $this->_mw_parser_passed_replaces[] = $elk_crc;
                        $mw_replaced_edit_fields_vals[$global_holder_hash] = $modified_layout;

                        if ($value != '') {
                            $val_rep = $value;
                            $have_more = $this->_do_we_have_more_edit_fields_for_parse($value);
                            if ($have_more) {
                                $val_rep = $this->_replace_editable_fields($val_rep, $no_cache = false, $from_parent = $layout);
                            }


                            $rep = 'mw_replace_back_this_editable_' . $elk . '';
                            $ct = 1;

                            //   $modified_layout = str_replace($rep, $val_rep, $modified_layout,$ct);
                            $modified_layout = $this->_str_replace_first($rep, $val_rep, $modified_layout);
                        }
                    } else {
                        $rep = 'mw_replace_back_this_editable_' . $elk . '';
                        $modified_layout = $this->_str_replace_first($rep, $value, $modified_layout);

                        // $modified_layout = str_replace($rep, $value, $modified_layout);
                    }
                }

                $layout = $modified_layout;
                $mw_replaced_edit_fields_vals[$parser_mem_crc] = $layout;
            }

            if (!empty($replaced_code_tags)) {
                foreach ($replaced_code_tags as $key => $value) {
                    if ($value != '') {
                        $layout = str_replace($key, $value, $layout);
                    }
                    unset($replaced_code_tags[$key]);
                }
            }

            if ($no_cache == false) {
                //    $this->app->cache_manager->save($layout, $parser_mem_crc, 'content_fields/global/parser');
            }
        }


        //  $layout = $this->replace_url_placeholders($layout);

        $this->_mw_parser_passed_replaces[$parser_mem_crc] = $layout;
        $mw_replaced_edit_fields_vals[$parser_mem_crc] = $layout;

        return $layout;
    }

    public function replace_url_placeholders($layout)
    {
        if (defined('TEMPLATE_URL')) {
            $replaces = array(
                '{TEMPLATE_URL}',
                '{THIS_TEMPLATE_URL}',
                '{DEFAULT_TEMPLATE_URL}',
                '%7BTEMPLATE_URL%7D',
                '%7BTHIS_TEMPLATE_URL%7D',
                '%7BDEFAULT_TEMPLATE_URL%7D',
            );


            $replaces_vals = array(
                TEMPLATE_URL,
                THIS_TEMPLATE_URL,
                DEFAULT_TEMPLATE_URL,
                TEMPLATE_URL,
                THIS_TEMPLATE_URL,
                DEFAULT_TEMPLATE_URL
            );

            //        $layout = str_replace($replaces, $replaces_vals, $layout);
            $layout = str_replace_bulk($replaces, $replaces_vals, $layout);
        }
        return $layout;
    }

    public function make_tags($layout, $options = array())
    {

        if ($layout == '') {
            return $layout;
        }
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        $pq = \phpQuery::newDocument($layout);


        $remove_clases = ['changed', 'inaccessibleModule', 'module-over', 'currentDragMouseOver', 'mw-webkit-drag-hover-binded'];

        foreach ($pq ['.edit.changed'] as $elem) {
            $attrs = $elem->attributes;
            $tag = $elem->tagName;


            $module_html = '<' . $tag . ' ';
            if (!empty($attrs)) {
                foreach ($attrs as $attribute_name => $attribute_node) {
                    $v = $attribute_node->nodeValue;
                    if ($attribute_name == 'class') {
                        foreach ($remove_clases as $remove_class) {
                            $v = str_replace(' ' . $remove_class, '', $v);
                        }
                    }
                    $module_html .= " {$attribute_name}='{$v}'  ";
                }
            }
            $module_html .= '><inner-edit-tag>mw_saved_inner_edit_from_parent_edit_field</inner-edit-tag><' . $tag . '/>';
            pq($elem)->replaceWith($module_html);

        }

        $layout = $pq->htmlOuter();

        $pq = \phpQuery::newDocument($layout);

        foreach ($pq ['.module'] as $elem) {
            $name = pq($elem)->attr('module');
            $attrs = $elem->attributes;
            $module_html = '<module ';
            if (!empty($attrs)) {
                foreach ($attrs as $attribute_name => $attribute_node) {
                    $v = $attribute_node->nodeValue;
                    if ($attribute_name == 'class') {
                        foreach ($remove_clases as $remove_class) {
                            $v = str_replace(' ' . $remove_class, '', $v);
                        }
                    }


                    $module_html .= " {$attribute_name}='{$v}'  ";
                }
            }
            $module_html .= ' />';
            pq($elem)->replaceWith($module_html);
        }
        $layout = $pq->htmlOuter();
        $layout = str_replace("\u00a0", ' ', $layout);
        $layout = str_replace('<?', '&lt;?', $layout);
        $layout = str_replace('?>', '?&gt;', $layout);


        if (isset($options['change_module_ids']) and $options['change_module_ids']) {
            $script_pattern = '/<module[^>]*>/Uis';
            preg_match_all($script_pattern, $layout, $mw_script_matches);
            if (!empty($mw_script_matches)) {
                $matches1 = $mw_script_matches[0];

                foreach ($matches1 as $key => $value) {
                    if ($value != '') {
                        $attrs = $this->_extract_module_tag_attrs($value);
                        $suffix = date("Ymdhis");
                        if (isset($attrs['parent-module-id'])) {
                            $attrs['parent-module-id'] = $attrs['parent-module-id'] . $suffix;
                        }
                        if (isset($attrs['id'])) {
                            $attrs['id'] = $attrs['id'] . $suffix;
                        }

                        if ($attrs) {
                            $module_tags = '<module ';
                            foreach ($attrs as $nn => $nv) {
                                $module_tags .= " {$nn}='{$nv}' ";
                            }
                            $module_tags .= "/>";
                            $layout = $this->_str_replace_first($value, $module_tags, $layout);

                        }

                    }
                }
            }
        }

        return $layout;
    }

    public function modify_html_preg($layout, $preg_match_all, $content = '', $action = 'append')
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        $string_html = $layout;
        $m = preg_match_all($preg_match_all, $string_html, $match);
        if ($m) {
            $match_html = $match[0];
            for ($j = 0; $j < $m; ++$j) {
                if (trim($action) == 'append') {
                    $string_html = str_replace($match_html[$j], $match_html[$j] . $content, $string_html);
                } else {
                    $string_html = str_replace($match_html[$j], $content . $match_html[$j], $string_html);
                }
            }
        }
        $layout = $string_html;

        return $layout;
    }

    public function modify_html($layout, $selector, $content = '', $action = 'append')
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        $pq = \phpQuery::newDocument($layout);

        $els = $pq[$selector];
        foreach ($els as $elem) {
            pq($elem)->$action($content);
        }
        $layout = $pq->htmlOuter();

        return $layout;
    }

    public function clean_word($html_to_save)
    {
        if (strstr($html_to_save, '<!--[if gte mso')) {
            // word mess up tags
            $tags = extract_tags($html_to_save, 'xml', $selfclosing = false, $return_the_entire_tag = true, $charset = 'UTF-8');

            $matches = $tags;
            if (!empty($matches)) {
                foreach ($matches as $m) {
                    $html_to_save = str_replace($m['full_tag'], '', $html_to_save);
                }

                $html_to_save = str_replace('<!--[if gte mso 8]><![endif]-->', '', $html_to_save);

                $html_to_save = str_replace('<!--[if gte mso 9]><![endif]-->', '', $html_to_save);
                $html_to_save = str_replace('<!--[if gte mso 10]><![endif]-->', '', $html_to_save);
                $html_to_save = str_replace('<!--[if gte mso 11]><![endif]-->', '', $html_to_save);
                $html_to_save = str_replace('class="MsoNormal"', '', $html_to_save);
            }
        }
        $html_to_save = str_replace('class="exec"', '', $html_to_save);
        $html_to_save = str_replace('style=""', '', $html_to_save);

        $html_to_save = str_replace('ui-draggable', '', $html_to_save);
        $html_to_save = str_replace('class="ui-droppable"', '', $html_to_save);
        $html_to_save = str_replace('ui-droppable', '', $html_to_save);
        $html_to_save = str_replace('mw_edited', '', $html_to_save);
        $html_to_save = str_replace('_moz_dirty=""', '', $html_to_save);
        $html_to_save = str_replace('ui-droppable', '', $html_to_save);
        $html_to_save = str_replace('<br >', '<br />', $html_to_save);
        $html_to_save = str_replace('<br>', '<br />', $html_to_save);
        $html_to_save = str_replace(' class=""', '', $html_to_save);
        $html_to_save = str_replace(' class=" "', '', $html_to_save);

        // $html_to_save = preg_replace('/<!--(.*)-->/Uis', '', $html_to_save);

        return $html_to_save;
    }

    public function get_by_id($html_element_id = false, $layout)
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        if ($html_element_id == false) {
            if (isset($_REQUEST['embed_id'])) {
                $html_element_id = trim($_REQUEST['embed_id']);
            }
        }

        if ($html_element_id != false and trim($html_element_id) != '') {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';
            $pq = \phpQuery::newDocument($layout);
            foreach ($pq ['#' . $html_element_id] as $elem) {
                $isolated_el = pq($elem)->htmlOuter();
                $isolated_body = pq('body')->eq(0)->html($isolated_el);
                $body_new = $isolated_body->htmlOuter();
                $layout = pq(0)->htmlOuter();
            }
        }

        return $layout;
    }

    public function isolate_head($l)
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';
        $pq = \phpQuery::newDocument($l);
        $l = pq('head')->eq(0)->html();

        return $l;
    }

    public function query($l, $selector = 'body', $return_function = 'htmlOuter')
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';
        $pq = \phpQuery::newDocument($l);
        $res = array();
        foreach ($pq [$selector] as $elem) {
            $l = pq($elem)->$return_function();
            $res[] = $l;
        }

        return $res;
    }

    public function get_html($l, $selector = 'body')
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';
        $pq = \phpQuery::newDocument($l);
        foreach ($pq [$selector] as $elem) {
            $l = pq($elem)->htmlOuter();

            return $l;
        }

        return false;
    }

    public function isolate_content_field($l, $strict = false)
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        $pq = \phpQuery::newDocument($l);
        $found = false;

        foreach ($pq ['[data-mw=main]'] as $elem) {
            if ($found == false) {
                $l = pq($elem)->htmlOuter();
                $found = true;
            }
        }
        if ($found == false) {
            foreach ($pq ['[field=content]:last'] as $elem) {
                $l = pq($elem)->htmlOuter();

                $found = true;
            }
        }

        if ($found == false) {
            foreach ($pq ['[field=content_body]:last'] as $elem) {
                $l = pq($elem)->htmlOuter();
                $found = true;
            }
        }

        if ($strict == true and $found == false) {
            return false;
        }

        return $l;
    }

    public function isolate_content_field_old($l)
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        $pq = \phpQuery::newDocument($l);
        $found = false;
        foreach ($pq ['[field=content][rel=content]:last'] as $elem) {
            $l = pq($elem)->htmlOuter();

            $found = true;
        }

        if ($found == false) {
            foreach ($pq ['[field=content_body][rel=content]:last'] as $elem) {
                $l = pq($elem)->htmlOuter();
                $found = true;
            }
        }

        return $l;
    }

    public function setInnerHTML($DOM, $element, $content)
    {
        $DOMInnerHTML = new \DOMDocument();
        $DOMInnerHTML->loadHTML('<?xml encoding="UTF-8">' . $content);
        $contentNode = $DOMInnerHTML->getElementsByTagName('body')->item(0)->firstChild;

        $contentNode = $DOM->importNode($contentNode, true);
        //$element->appendChild($contentNode);
        //  $element->parentNode->appendChild($contentNode);
        $fragment = $DOM->createDocumentFragment();
        $fragment->appendChild($contentNode);
        $element->replaceChild($fragment, $element->cloneNode(true));

        //$fragment = $DOM->createDocumentFragment();
        //$fragment->appendChild($contentNode);

        // $element->parentNode->replaceChild($element, $contentNode);

        //$element->appendChild($contentNode);
        //  $element->replaceChild($contentNode, $DOMInnerHTML);
        // $contentNode->innerHTML = ($content);
        // $DOMInnerHTML->saveXML();
        return $contentNode;
    }

    public function load($module_name, $attrs = array())
    {
        $is_element = false;
        $custom_view = false;
        if (isset($attrs['view'])) {
            $custom_view = $attrs['view'];
            $custom_view = trim($custom_view);
            $custom_view = str_replace('\\', '/', $custom_view);
            $attrs['view'] = $custom_view = str_replace('..', '', $custom_view);
        }

        if ($custom_view != false and strtolower($custom_view) == 'admin') {
            if ($this->app->user_manager->is_admin() == false) {
                mw_error('Not logged in as admin');
            }
        }

        $module_name = trim($module_name);
        $module_name = str_replace('\\', '/', $module_name);
        $module_name = str_replace('..', '', $module_name);
        // prevent hack of the directory
        $module_name = reduce_double_slashes($module_name);

        $module_namei = $module_name;

        if (strstr($module_name, 'admin')) {
            $module_namei = str_ireplace('\\admin', '', $module_namei);
            $module_namei = str_ireplace('/admin', '', $module_namei);
        }

        //$module_namei = str_ireplace($search, $replace, $subject)


        if (!defined('ACTIVE_TEMPLATE_DIR')) {
            $this->app->content_manager->define_constants();
        }

        $module_in_template_dir = ACTIVE_TEMPLATE_DIR . 'modules/' . $module_name . '';
        $module_in_template_dir = normalize_path($module_in_template_dir, 1);
        $module_in_template_file = ACTIVE_TEMPLATE_DIR . 'modules/' . $module_name . '.php';
        $module_in_template_file = normalize_path($module_in_template_file, false);

        $try_file1 = false;

        $mod_d = $module_in_template_dir;
        $mod_d1 = normalize_path($mod_d, 1);
        $try_file1zz = $mod_d1 . 'index.php';
        $in_dir = false;

        if ($custom_view == true) {
            $try_file1zz = $mod_d1 . trim($custom_view) . '.php';
        } else {
            $try_file1zz = $mod_d1 . 'index.php';
        }

        if (is_dir($module_in_template_dir) and is_file($try_file1zz)) {
            $try_file1 = $try_file1zz;

            $in_dir = true;
        } elseif (is_file($module_in_template_file)) {
            $try_file1 = $module_in_template_file;
            $in_dir = false;
        } else {
            $module_in_default_dir = modules_path() . $module_name . '';
            $module_in_default_dir = normalize_path($module_in_default_dir, 1);
            // d($module_in_default_dir);
            $module_in_default_file = modules_path() . $module_name . '.php';
            $module_in_default_file_custom_view = modules_path() . $module_name . '_' . $custom_view . '.php';

            $element_in_default_file = elements_path() . $module_name . '.php';
            $element_in_default_file = normalize_path($element_in_default_file, false);

            //

            $module_in_default_file = normalize_path($module_in_default_file, false);

            if (is_file($module_in_default_file)) {
                $in_dir = false;
                if ($custom_view == true and is_file($module_in_default_file_custom_view)) {
                    $try_file1 = $module_in_default_file_custom_view;
                } else {
                    $try_file1 = $module_in_default_file;
                }
            } else {
                if (is_dir($module_in_default_dir)) {
                    $in_dir = true;
                    $mod_d1 = normalize_path($module_in_default_dir, 1);

                    if ($custom_view == true) {
                        $try_file1 = $mod_d1 . trim($custom_view) . '.php';
                    } else {
                        $try_file1 = $mod_d1 . 'index.php';
                    }
                } elseif (is_file($element_in_default_file)) {
                    $in_dir = false;
                    $is_element = true;

                    $try_file1 = $element_in_default_file;
                }
            }
        }
        //
        if (isset($try_file1) != false and $try_file1 != false and is_file($try_file1)) {
            if (isset($attrs) and is_array($attrs) and !empty($attrs)) {
                $attrs2 = array();
                foreach ($attrs as $attrs_k => $attrs_v) {
                    $attrs_k2 = substr($attrs_k, 0, 5);
                    if (strtolower($attrs_k2) == 'data-') {
                        $attrs_k21 = substr($attrs_k, 5);
                        $attrs2[$attrs_k21] = $attrs_v;
                    } elseif (!isset($attrs['data-' . $attrs_k])) {
                        $attrs2['data-' . $attrs_k] = $attrs_v;
                    }

                    $attrs2[$attrs_k] = $attrs_v;
                }
                $attrs = $attrs2;
            }
            $config = array();
            $config['path_to_module'] = $config['mp'] = $config['path'] = normalize_path((dirname($try_file1)) . '/', true);
            $config['the_module'] = $module_name;
            $config['module'] = $module_name;
            $module_name_dir = dirname($module_name);
            $config['module_name'] = $module_name_dir;

            $config['module_name_url_safe'] = $this->module_name_encode($module_name);

            $find_base_url = $this->app->url_manager->current(1);
            if ($pos = strpos($find_base_url, ':' . $module_name) or $pos = strpos($find_base_url, ':' . $config['module_name_url_safe'])) {
                $find_base_url = substr($find_base_url, 0, $pos) . ':' . $config['module_name_url_safe'];
            }
            $config['url'] = $find_base_url;

            $config['url_main'] = $config['url_base'] = strtok($find_base_url, '?');

            if ($in_dir != false) {
                $mod_api = str_replace('/admin', '', $module_name);
            } else {
                $mod_api = str_replace('/admin', '', $module_name_dir);
            }

            $config['module_api'] = $this->app->url_manager->site('api/' . $mod_api);
            $config['module_view'] = $this->app->url_manager->site('module/' . $module_name);
            $config['ns'] = str_replace('/', '\\', $module_name);
            $config['module_class'] = $this->module_css_class($module_name);

            $config['url_to_module'] = $this->app->url_manager->link_to_file($config['path_to_module']);

            if (isset($attrs['id'])) {
                $attrs['id'] = str_replace('__MODULE_CLASS_NAME__', $config['module_class'], $attrs['id']);

                $template = false;
            }


//            if($module_name == '.'){
//                return;
//            }


            $installed_module = $this->app->modules->get('single=1&ui=any&module=' . $module_name);
            if($installed_module and isset($installed_module['settings'])){
                $config['settings']  = $installed_module['settings'];
            }

//            $is_installed = $this->app->modules->is_installed($module_name);
//
//            if(!$is_installed){
//                d($module_name);
//                return '';
//            }


            $modules_dir_default = modules_path() . $module_name;
            $modules_dir_default = normalize_path($modules_dir_default, true);
            $module_name_root = mw()->modules->locate_root_module($module_name);
            $modules_dir_default_root = modules_path() . $module_name_root;
            $modules_dir_default_root = normalize_path($modules_dir_default_root, true);



            if ($module_name_root and is_dir($modules_dir_default_root) and is_file($modules_dir_default_root . 'config.php')) {
                $is_installed = $this->app->modules->is_installed($module_name_root);
                if (!$is_installed) {
                    return '';

                }
            }


            if (isset($installed_module['installed']) and $installed_module['installed'] != '' and intval($installed_module['installed']) != 1) {
                return '';
            }
            if (isset($installed_module['type']) and !isset($config['module_type'])) {
                $config['module_type'] = $installed_module['type'];
            } else {
                $config['module_type'] = null;
            }

            //$config['url_to_module'] = rtrim($config['url_to_module'], '///');
            $lic = $this->app->modules->license($module_name);
            //  $lic = 'valid';
            if ($lic != false) {
                $config['license'] = $lic;
            }

            if (isset($attrs['module-id']) and $attrs['module-id'] != false) {
                $attrs['id'] = $attrs['module-id'];
            }

            if (!isset($attrs['id'])) {
                global $mw_mod_counter;
                ++$mw_mod_counter;
                //  $seg_clean = $this->app->url_manager->segment(0);
                $seg_clean = $this->app->url_manager->segment(0, url_current());


                if (defined('IS_HOME')) {
                    $seg_clean = '';
                }
                $seg_clean = str_replace('%20', '-', $seg_clean);
                $seg_clean = str_replace(' ', '-', $seg_clean);
                $seg_clean = str_replace('.', '', $seg_clean);
                $attrs1 = crc32(serialize($attrs) . $seg_clean . $mw_mod_counter);
                $attrs1 = str_replace('%20', '-', $attrs1);
                $attrs1 = str_replace(' ', '-', $attrs1);
                $attrs['id'] = ($config['module_class'] . '-' . $attrs1);
            }
            if (isset($attrs['id']) and strstr($attrs['id'], '__MODULE_CLASS_NAME__')) {
                $attrs['id'] = str_replace('__MODULE_CLASS_NAME__', $config['module_class'], $attrs['id']);
                //$attrs['id'] = ('__MODULE_CLASS__' . '-' . $attrs1);
            }

            //load scripts and css
            $module_css = '';
            $module_css_file = dirname($try_file1) . DS . 'module.css';
            if (is_file($module_css_file)) {
                $module_css = @file_get_contents($module_css_file);

                if ($module_css) {
                    $module_css = str_replace('#module', '#' . url_title($attrs['id']), $module_css);
                }
            }


            $l1 = new \Microweber\View($try_file1);
            $l1->config = $config;
            $l1->app = $this->app;

            if (!isset($attrs['module'])) {
                $attrs['module'] = $module_name;
            }

//            if (!isset($attrs['parent-module'])) {
//                $attrs['parent-module'] = $module_name;
//            }
//
//            if (!isset($attrs['parent-module-id'])) {
//                $attrs['parent-module-id'] = $attrs['id'];
//            }
//            $mw_restore_get = mw_var('mw_restore_get');
//            if ($mw_restore_get != false and is_array($mw_restore_get)) {
//                $l1->_GET = $mw_restore_get;
//                $_GET = $mw_restore_get;
//            }
            if (defined('MW_MODULE_ONDROP')) {
                if (!isset($attrs['ondrop'])) {
                    $attrs['ondrop'] = true;
                }
            }
            $l1->params = $attrs;


            if ($config) {
                $this->current_module = ($config);
            }
            if ($attrs) {
                $this->current_module_params = ($attrs);
            }
            if (isset($attrs['view']) && (trim($attrs['view']) == 'empty')) {
                $module_file = EMPTY_MOD_STR;
            } elseif (isset($attrs['view']) && (trim($attrs['view']) == 'admin')) {
                $module_file = $l1->__toString();
            } else {
                if (isset($attrs['display']) && (trim($attrs['display']) == 'custom')) {
                    $module_file = $l1->__get_vars();

                    return $module_file;
                } elseif (isset($attrs['format']) && (trim($attrs['format']) == 'json')) {
                    $module_file = $l1->__get_vars();
                    header('Content-type: application/json');
                    exit(json_encode($module_file));
                } else {
                    $module_file = $l1->__toString();
                }
            }
            //	$l1 = null;
            unset($l1);
            if ($lic != false and isset($lic['error']) and ($lic['error'] == 'no_license_found')) {
                $lic_l1_try_file1 = MW_ADMIN_VIEWS_DIR . 'activate_license.php';
                $lic_l1 = new \Microweber\View($lic_l1_try_file1);

                $lic_l1->config = $config;
                $lic_l1->params = $attrs;

                $lic_l1e_file = $lic_l1->__toString();
                unset($lic_l1);
                $module_file = $lic_l1e_file . $module_file;
            }

            if ($module_css and $module_file and is_string($module_file)) {
                $module_file .= "
                <style>"

                    . $module_css .


                    "</style>

                ";
            }


            // $mw_loaded_mod_memory[$function_cache_id] = $module_file;
            return $module_file;
        } else {
            //define($cache_content, FALSE);
            // $mw_loaded_mod_memory[$function_cache_id] = false;
            return false;
        }
    }

    public function replace_non_cached_modules_with_placeholders($layout)
    {
        //   $non_cached
        $non_cached = $this->app->modules->get('allow_caching=0&ui=any');
        $has_changes = false;
//dd($non_cached);
        if (!$non_cached or $layout == '') {
            return $layout;
        }
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpQuery.php';

        $pq = \phpQuery::newDocument($layout);


        $remove_clases = ['changed', 'inaccessibleModule', 'module-over', 'currentDragMouseOver', 'mw-webkit-drag-hover-binded'];
        $found_mods = array();
        $found_mods_non_cached = array();
        foreach ($pq ['.module'] as $elem) {
            $attrs = $elem->attributes;
            $tag = $elem->tagName;


            $module_html = '<' . $tag . ' ';
            if (!empty($attrs)) {
                $mod_name = false;
                $mod_name_is_cached = true;
                foreach ($attrs as $attribute_name => $attribute_node) {
                    $v = $attribute_node->nodeValue;
                    if ($attribute_name == 'type'
                        or $attribute_name == 'data-type'
                        or $attribute_name == 'type'
                    ) {
                        $mod_name = $v;
                        $found_mods[] = $mod_name;
                    }
                }
                foreach ($non_cached as $mod) {
                    if (isset($mod['module'])
                        and $mod_name
                        and $mod_name == $mod['module']
                    ) {
                        $has_changes = true;
                        $mod_name_is_cached = false;

                        $found_mods_non_cached[] = $mod_name;
                    }
                }


                if (!$mod_name_is_cached and $mod_name and $has_changes) {


                    foreach ($attrs as $attribute_name => $attribute_node) {

                        $v = $attribute_node->nodeValue;


                        if ($attribute_name == 'class') {
                            $v = str_replace('module ', 'mw-lazy-load-module module ', $v);
                        }


                        $module_html .= " {$attribute_name}='{$v}'  ";
                        $has_changes = true;


                    }

                    if ($has_changes) {
                        $module_html .= '><!-- Loading module ' . $mod_name . ' --><' . $tag . '/>';

                        pq($elem)->replaceWith($module_html);
                    }


                }

            }


        }
        if ($has_changes) {
            $layout = $pq->htmlOuter();
        }

        return $layout;

    }

    private function _process_additional_module_parsers($layout, $module, $params)
    {
        $type = 'module';
        if(isset($this->_additional_parsers[$type]) and $this->_additional_parsers[$type]){
            $parsers_callbacks = $this->_additional_parsers[$type];
            foreach($parsers_callbacks as $parser_callback){
                if (is_callable($parser_callback)) {
                    $res = call_user_func($parser_callback, $layout,$module, $params);
                    if($res){
                        $layout = $res;
                    }
                }
            }
        }
        return $layout;
    }

    public function optimize_asset_loading_order($layout)
    {

        return $layout;
        $replaced = array();
        $pq = \phpQuery::newDocument($layout);
        $srcs = array();
        $srcs_css = array();

        foreach ($pq ['script'] as $elem) {
            $src = pq($elem)->attr('src');
            // <script type="text/javascript/defer">

            if ($src and !strstr($src, 'apijs')) {

                //  pq($elem)->attr('type', 'text/javascript/defer');
                //pq($elem)->attr('type', 'text/delayscript');
                $srcs[] = $src;
                pq($elem)->replaceWith('');
            }
            if ($src) {
//                $replaced[] = pq($elem)->htmlOuter();
//                pq($elem)->replaceWith('');
            } else {

                //     pq($elem)->attr('defer', 'defer');

//                 $base = pq($elem)->html();
//                $base = base64_encode($base);
//                pq($elem)->attr('src', 'data:text/javascript;base64,'.$base);
//                pq($elem)->html('');
            }

        }
//        foreach ($pq ['link'] as $elem) {
//            $src = pq($elem)->attr('href');
//            if($src){
//                $srcs_css[] = $src;
//                pq($elem)->replaceWith('');
//            }
//        }


        $layout = $pq->htmlOuter();
        $load_deffered = "<script>
   $( document ).ready(function() {


	$('script[type=\"text/javascript/defer\"]').each(function(){
		$(this).clone().attr('type', 'application/javascript').insertAfter(this);	
		$(this).remove();
	});
});
</script>";

        $load_deffered = "<script>
   $( document ).ready(function() {

	var scripts = document.getElementsByTagName(\"script\")

    for (var i = 0; i < scripts.length; i++) {
        var type = scripts[i].getAttribute(\"type\");
        if (type && type.toLowerCase() == 'text/delayscript') {
            scripts[i].parentNode.replaceChild((function (delayscript) {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.innerHTML = delayscript.innerHTML;

                return script;
            })(scripts[i]), scripts[i]);
        }
    }
});
</script>";


        if ($srcs) {
            $srsc_str = '';
            foreach ($srcs as $src) {
                $srsc_str .= 'mw.require("' . $src . '")' . "\n";
            }
            $srsc_str = "<script>
$srsc_str
            </script>";
            $layout = str_ireplace('</body>', $srsc_str . '</body>', $layout, $c);

        }

//        if($srcs_css){
//            $srsc_str = '';
//            foreach ($srcs_css as $src){
//                $srsc_str .= 'mw.moduleCSS("'.$src.'")'."\n";
//            }
//            $srsc_str = "<script>
//$srsc_str
//            </script>";
//            $layout = str_ireplace('</body>', $srsc_str . '</body>', $layout, $c);
//
//        }

        $layout = str_ireplace('</head>', $load_deffered . '</head>', $layout, $c);

        // $replaced[] = $load_deffered;

        if ($replaced) {
            //$replaced = array_unique($replaced);


            $replaced_str = implode("\n", $replaced);
            $c = 1;
            // $layout = str_ireplace('</head>', $replaced_str . '</head>', $layout, $c);


            $layout = str_ireplace('</body>', $replaced_str . '</body>', $layout, $c);
            // $layout = str_ireplace('</body>', $load_deffered . '</body>', $layout, $c);

        }

        return $layout;

        $replaced = array();
        $pq = \phpQuery::newDocument($layout);
//        foreach ($pq ['script'] as $elem) {
//               $src = pq($elem)->attr('src');
//
//            $replaced[] = pq($elem)->htmlOuter();
//            pq($elem)->replaceWith('');
//
//
//        }
        foreach ($pq ['script'] as $elem) {
            $src = pq($elem)->attr('src');
            if ($src) {
                $replaced[] = pq($elem)->htmlOuter();
                pq($elem)->replaceWith('');
            } else {

                pq($elem)->attr('defer', 'defer');

                $base = pq($elem)->html();
                $base = base64_encode($base);
                pq($elem)->attr('src', 'data:text/javascript;base64,' . $base);
                pq($elem)->html('');
            }

        }


        $layout = $pq->htmlOuter();

        if ($replaced) {
            //$replaced = array_unique($replaced);
            $replaced_str = implode("\n", $replaced);
            $c = 1;
            // $layout = str_ireplace('</head>', $replaced_str . '</head>', $layout, $c);
            $layout = str_ireplace('</body>', $replaced_str . '</body>', $layout, $c);

        }
        return $layout;
    }

    public function module_name_decode($module_name)
    {
        $module_name = str_replace('__', '/', $module_name);

        return $module_name;
    }

    public function module_name_encode($module_name)
    {
        $module_name = str_replace('/', '__', $module_name);
        $module_name = str_replace('\\', '__', $module_name);

        return $module_name;
    }

    public function module_css_class($module_name)
    {
        $module_class = str_replace('/', '-', $module_name);
        $module_class = str_replace('\\', '-', $module_class);
        $module_class = str_replace(' ', '-', $module_class);
        $module_class = str_replace('%20', '-', $module_class);
        $module_class = str_replace('_', '-', $module_class);
        $module_class = 'module-' . $module_class;

        return $module_class;
    }

    private function _str_replace_first($search, $replace, $subject)
    {
        if ($search == false || $replace == false) {
            return $subject;
        }
        if (!is_string($search)) {
            return $subject;
        }

        $pos = strpos($subject, (string)$search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    private function _do_we_have_more_edit_fields_for_parse($layout)
    {
        $proceed_with_parse = false;
        preg_match_all('/.*?class=..*?edit.*?.[^>]*>/', $layout, $modinner);
        $proceed_with_parse = false;
        if (!empty($modinner) and isset($modinner[0][0])) {
            $proceed_with_parse = true;
        }
        return $proceed_with_parse;
    }

    private function _do_we_have_more_for_parse($mod_content)
    {

        $proceed_with_parse = false;

        if ($this->_do_we_have_more_edit_fields_for_parse($mod_content)) {
            $proceed_with_parse = true;
        } else {
            preg_match_all('/<module.*[^>]*>/', $mod_content, $modinner);
            if (!empty($modinner) and isset($modinner[0][0])) {

                $proceed_with_parse = true;
            }


//            else {
//                preg_match_all('/<mw.*[^>]*>/', $mod_content, $modinner);
//                if (!empty($modinner) and isset($modinner[0][0])) {
//                    $proceed_with_parse = true;
//                } else {
//                    preg_match_all('/<microweber.*[^>]*>/', $mod_content, $modinner);
//                    if (!empty($modinner) and isset($modinner[0][0])) {
//                        $proceed_with_parse = true;
//                    } else {
////                        preg_match_all('/<tag.*[^>]*>/', $mod_content, $modinner);
////                        if (!empty($modinner) and isset($modinner[0][0])) {
////                            $proceed_with_parse = true;
////                        }
//                    }
//                }
//            }
        }
        return $proceed_with_parse;
    }

    private function _extract_module_tag_attrs($module_tag)
    {
        $value = $module_tag;
        $attrs = array();
        $attribute_pattern = '@(?P<name>[a-z-_A-Z]+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi';
        $mw_attrs_key_value_seperator = "__MW_PARSER_ATTR_VAL__";
        if (preg_match_all($attribute_pattern, $value, $attrs1, PREG_SET_ORDER)) {
            foreach ($attrs1 as $item) {
                $m_tag = trim($item[0], "\x22\x27");
                $m_tag = trim($m_tag, "\x27\x22");
                $m_tag = preg_replace('/=/', $mw_attrs_key_value_seperator, $m_tag, 1);


                $m_tag = explode($mw_attrs_key_value_seperator, $m_tag);

                $a = trim($m_tag[0], "''");
                $a = trim($a, '""');
                $b = trim($m_tag[1], "''");
                $b = trim($b, '""');
                if (isset($m_tag[2])) {
                    $rest_pieces = $m_tag;
                    if (isset($rest_pieces[0])) {
                        unset($rest_pieces[0]);
                    }
                    if (isset($rest_pieces[1])) {
                        unset($rest_pieces[1]);
                    }
                    $rest_pieces = implode($mw_attrs_key_value_seperator, $rest_pieces);
                    $b = $b . $rest_pieces;
                }

                $attrs[$a] = $b;
            }
        }
//d($attrs);
        if ($attrs) {
            return $attrs;
        }
    }


    private function _replace_tags_with_placeholders($mod_content)
    {

        global $mw_parser_replace_inc;
        global $mw_replaced_codes_tag;
        global $mw_replaced_edit_fields_vals;
        global $other_html_tag_replace_inc;
        global $mw_replaced_codes_tag;

        global $mw_replaced_textarea_tag;


        $script_pattern = "/<script[^>]*>(.*)<\/script>/Uis";
        preg_match_all($script_pattern, $mod_content, $mw_script_matches);

        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    $v1 = crc32($value);
                    $v1 = '<tag>mw_replace_back_this_script_mod_inner_' . $v1 . '</tag>';
                    $mod_content = str_replace($value, $v1, $mod_content);
                    if (!isset($replaced_scripts[$v1])) {
                        $mw_replaced_codes_tag[$v1] = $value;

                    }
                }
            }
        }

        $script_pattern = "/<code[^>]*>(.*)<\/code>/Uis";
        preg_match_all($script_pattern, $mod_content, $mw_script_matches);

        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    $v1 = crc32($value);
                    $v1 = '<tag>mw_replace_back_this_code_mod_inner_' . $v1 . '</tag>';
                    $mod_content = str_replace($value, $v1, $mod_content);
                    if (!isset($replaced_scripts[$v1])) {
                        $mw_replaced_codes_tag[$v1] = $value;
                    }
                }
            }
        }


        $script_pattern = "/<textarea[^>]*>(.*)<\/textarea>/Uis";
        preg_match_all($script_pattern, $mod_content, $mw_script_matches);

        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    //   $v1 = crc32($value);
                    $v1 = crc32($value) . '-' . $mw_parser_replace_inc++ . '-';

                    $v1 = '<tag-textarea>mw_replace_back_this_textarea_inner_loop11_' . $v1 . '</tag-textarea>';

                    $mod_content = $this->_str_replace_first($value, $v1, $mod_content);

                    if (!isset($this->_replaced_input_tags_inner_loops[$v1])) {
                        $this->_replaced_input_tags_inner_loops[$v1] = $value;
                        //    $mw_replaced_textarea_tag[$v1] = $value;
                    }
                }
            }
        }


        $script_pattern = "/<style[^>]*>(.*)<\/style>/Uis";
        preg_match_all($script_pattern, $mod_content, $mw_script_matches);
        if (!empty($mw_script_matches)) {
            foreach ($mw_script_matches [0] as $key => $value) {
                if ($value != '') {
                    $v1 = crc32($value);
                    $v1 = '<custom-replaced-tag>mw_replace_back_this_style_inner_' . $v1 . '</custom-replaced-tag>';
                    $mod_content = str_replace($value, $v1, $mod_content);
                    if (!isset($this->_replaced_input_tags[$v1])) {
                        $this->_replaced_input_tags[$v1] = $value;
                        $mw_replaced_codes_tag[$v1] = $value;
                    }
                }
            }
        }


        return $mod_content;
    }


    private function _str_clean_mod_id($mod_id)
    {
        $mod_id = str_replace(' ', '-', $mod_id);
        $mod_id = str_replace('/', '-', $mod_id);
        $mod_id = str_replace('\\', '-', $mod_id);
        $mod_id = str_replace('_', '-', $mod_id);
        $mod_id = str_replace(';', '-', $mod_id);
        $mod_id = str_replace('.', '-', $mod_id);
        $mod_id = str_replace('#', '-', $mod_id);
        //   $mod_id = str_replace('--', '', $mod_id);
        $mod_id = strtolower($mod_id);
        $mod_id = trim($mod_id);
        return $mod_id;
    }
}
