<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Helper;

/**
 * Class BundleHelper.
 */
class BundleHelper
{
    private $coreBundles   = [];
    private $pluginBundles = [];
    private $allBundles    = [];

    /**
     * BundleHelper constructor.
     */
    public function __construct(array $coreBundles, array $pluginBundles)
    {
        $this->coreBundles   = $coreBundles;
        $this->pluginBundles = $pluginBundles;
        $this->allBundles    = array_merge($coreBundles, $pluginBundles);
    }

    /**
     * @param bool $includePlugins
     *
     * @return mixed
     */
    public function getMauticBundles($includePlugins = true)
    {
        return $includePlugins ? $this->allBundles : $this->coreBundles;
    }

    /**
     * Get's an array of details for enabled Mautic plugins.
     *
     * @return array
     */
    public function getPluginBundles()
    {
        return $this->pluginBundles;
    }

    /**
     * Gets an array of a specific bundle's config settings.
     *
     * @param        $bundleName
     * @param string $configKey
     * @param bool   $includePlugins
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getBundleConfig($bundleName, $configKey = '', $includePlugins = false)
    {
        // get the configs
        $configFiles = $this->getMauticBundles($includePlugins);

        // if no bundle name specified we throw
        if (!$bundleName) {
            throw new \Exception('Bundle name not supplied');
        }

        // check for the bundle config requested actually exists
        if (!array_key_exists($bundleName, $configFiles)) {
            throw new \Exception('Bundle '.$bundleName.' does not exist');
        }

        // get the specific bundle's configurations
        $bundleConfig = $configFiles[$bundleName]['config'];

        // no config key supplied so just return the bundle's config
        if (!$configKey) {
            return $bundleConfig;
        }

        // check that the key exists
        if (!array_key_exists($configKey, $bundleConfig)) {
            throw new \Exception('Key '.$configKey.' does not exist in bundle '.$bundleName);
        }

        // we didn't throw so we can send the key value
        return $bundleConfig[$configKey];
    }
}
