<?php
namespace Crater;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    public static function setSetting($key, $setting)
    {
        $old = self::whereOption($key)->first();

        if ($old) {
            $old->value = $setting;
            $old->save();
            return;
        }

        $set = new Setting();
        $set->option = $key;
        $set->value = $setting;
        $set->save();
    }

    public static function getSetting($key)
    {
        $setting = static::whereOption($key)->first();

        if ($setting) {
            return $setting->value;
        } else {
            return null;
        }
    }
}
