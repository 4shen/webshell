<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CachetHQ\Cachet\Settings\Repository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Jenssegers\Date\Date;

if (!function_exists('setting')) {
    /**
     * Get a setting, or the default value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    function setting($name, $default = null)
    {
        static $settings = [];

        if (isset($settings[$name])) {
            return $settings[$name];
        }

        return $settings[$name] = app(Repository::class)->get($name, $default);
    }
}

if (!function_exists('set_active')) {
    /**
     * Set active class if request is in path.
     *
     * @param string $path
     * @param array  $classes
     * @param string $active
     *
     * @return string
     */
    function set_active($path, array $classes = [], $active = 'active')
    {
        if (Request::is($path)) {
            $classes[] = $active;
        }

        $class = e(implode(' ', $classes));

        return empty($classes) ? '' : "class=\"{$class}\"";
    }
}

if (!function_exists('formatted_date')) {
    /**
     * Formats a date with the user timezone and the selected format.
     *
     * @param string $date
     *
     * @return \Jenssegers\Date\Date
     */
    function formatted_date($date)
    {
        $dateFormat = Config::get('setting.date_format', 'jS F Y');

        return (new Date($date))->format($dateFormat);
    }
}

if (!function_exists('color_darken')) {
    /**
     * Darken a color.
     *
     * @param string $hex
     * @param int    $percent
     *
     * @return string
     */
    function color_darken($hex, $percent)
    {
        $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
        $new_hex = '#';

        if (strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }

        for ($i = 0; $i < 3; $i++) {
            $dec = hexdec(substr($hex, $i * 2, 2));
            $dec = min(max(0, $dec + $dec * $percent), 255);
            $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
        }

        return $new_hex;
    }
}

if (!function_exists('color_contrast')) {
    /**
     * Calculates colour contrast.
     *
     * https://24ways.org/2010/calculating-color-contrast/
     *
     * @param string $hexcolor
     *
     * @return string
     */
    function color_contrast($hexcolor)
    {
        $r = ctype_xdigit(substr($hexcolor, 0, 2));
        $g = ctype_xdigit(substr($hexcolor, 2, 2));
        $b = ctype_xdigit(substr($hexcolor, 4, 2));
        $yiq = (($r * 100) + ($g * 400) + ($b * 114)) / 1000;

        return ($yiq >= 128) ? 'black' : 'white';
    }
}

if (!function_exists('cachet_route_generator')) {
    /**
     * Generate the route string.
     *
     * @param string $name
     * @param string $method
     * @param string $domain
     *
     * @return string
     */
    function cachet_route_generator($name, $method = 'get', $domain = 'core')
    {
        return "{$domain}::{$method}:{$name}";
    }
}

if (!function_exists('cachet_route')) {
    /**
     * Generate a URL to a named route, which resides in a given domain.
     *
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param string $domain
     *
     * @return string
     */
    function cachet_route($name, $parameters = [], $method = 'get', $domain = 'core')
    {
        return app('url')->route(
            cachet_route_generator($name, $method, $domain),
            $parameters,
            true
        );
    }
}

if (!function_exists('cachet_redirect')) {
    /**
     * Create a new redirect response to a named route, which resides in a given domain.
     *
     * @param string $name
     * @param array  $parameters
     * @param int    $status
     * @param array  $headers
     * @param string $method
     * @param string $domain
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function cachet_redirect($name, $parameters = [], $status = 302, $headers = [], $method = 'get', $domain = 'core')
    {
        $url = cachet_route($name, $parameters, $method, $domain);

        return app('redirect')->to($url, $status, $headers);
    }
}

if (!function_exists('execute')) {
    /**
     * Send the given command to the dispatcher for execution.
     *
     * @param object $command
     *
     * @return void
     */
    function execute($command)
    {
        return app(Dispatcher::class)->dispatchNow($command);
    }
}
