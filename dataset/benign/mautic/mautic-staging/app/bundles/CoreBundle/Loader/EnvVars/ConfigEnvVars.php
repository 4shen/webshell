<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Loader\EnvVars;

use Symfony\Component\HttpFoundation\ParameterBag;

class ConfigEnvVars implements EnvVarsInterface
{
    public static function load(ParameterBag $config, ParameterBag $defaultConfig, ParameterBag $envVars): void
    {
        foreach ($config->all() as $key => $value) {
            if (!empty($value) && is_string($value) && preg_match('/getenv\((.*?)\)/', $value, $match)) {
                $value = (string) getenv($match[1]);
            }

            // JSON encode arrays
            $defaultValue = $defaultConfig->get($key);
            if (is_array($value) || is_array($defaultValue)) {
                $jsonValue = $value ? $value : $defaultValue;
                $value     = json_encode($jsonValue);
            }

            // Set the environment variable
            $envKey = sprintf('MAUTIC_%s', mb_strtoupper($key));
            $envVars->set($envKey, $value);
        }
    }
}
