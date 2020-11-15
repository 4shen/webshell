<?php

function mw_is_installed()
{
    return Config::get('microweber.is_installed');
}

api_expose_admin('mw_save_framework_config_file', function ($params) {
    if (empty($params) or !is_admin()) {
        return;
    }
    $save_configs = array();
    foreach ($params as $k => $item) {
        if (is_array($item) and !empty($item)) {
            foreach ($item as $config_k => $config) {
                if (is_string($config_k)) {
                    if (is_numeric($config)) {
                        $config = intval($config);
                    }
                    Config::set($k . '.' . $config_k, $config);
                    $save_configs[] = $k;
                }
            }
        }
    }
    if (!empty($save_configs)) {
        Config::save($save_configs);
        return array('success' => 'Config is changed!');

    }
});
