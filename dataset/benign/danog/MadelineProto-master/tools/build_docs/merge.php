<?php

use danog\MadelineProto\Lang;

/**
 * Merge extracted docs.
 *
 * @return void
 */
function mergeExtracted(): void
{
    foreach (\json_decode(\file_get_contents('extracted.json'), true) as $key => $value) {
        $key = \preg_replace(['|flags\.\d+[?]|', '/Vector[<].*/'], ['', 'Vector t'], $key);
        $key = \str_replace('param_hash_type_int', 'param_hash_type_Vector t', $key);
        Lang::$lang['en'][$key] = $value;
    }
    foreach (Lang::$lang['en'] as $key => $value) {
        if ($value === '') {
            unset(Lang::$lang['en'][$key]);
        }
    }
    foreach (\json_decode(\file_get_contents('docs/template/disallow.json'), true) as $key => $value) {
        Lang::$lang['en'][$key] = $value;
    }
}
