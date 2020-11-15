<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Tests\Unit\DependencyInjection\Builder\Metadata;

use Mautic\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use Mautic\CoreBundle\DependencyInjection\Builder\Metadata\PermissionClassMetadata;
use PHPUnit\Framework\TestCase;

class PermissionClassMetadataTest extends TestCase
{
    public function testPermissionsFound()
    {
        $metadataArray = [
            'isPlugin'          => false,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MauticCoreBundle',
            'directory'         => __DIR__.'/../../../../../',
            'namespace'         => 'Mautic\\CoreBundle',
            'symfonyBundleName' => 'MauticCoreBundle',
            'bundleClass'       => '\\Mautic\\CoreBundle',
        ];

        $metadata                = new BundleMetadata($metadataArray);
        $permissionClassMetadata = new PermissionClassMetadata($metadata);
        $permissionClassMetadata->build();

        $this->assertTrue(isset($metadata->toArray()['permissionClasses']['core']));
        $this->assertCount(1, $metadata->toArray()['permissionClasses']);
    }

    public function testCompatibilityWithPermissionServices()
    {
        $metadataArray = [
            'isPlugin'          => false,
            'base'              => 'Asset',
            'bundle'            => 'AssetBundle',
            'relative'          => 'app/bundles/MauticAssetBundle',
            'directory'         => __DIR__.'/../../../../../../AssetBundle',
            'namespace'         => 'Mautic\\AssetBundle',
            'symfonyBundleName' => 'MauticAssetBundle',
            'bundleClass'       => '\\Mautic\\AssetBundle',
        ];

        $metadata                = new BundleMetadata($metadataArray);
        $permissionClassMetadata = new PermissionClassMetadata($metadata);
        $permissionClassMetadata->build();

        $this->assertTrue(isset($metadata->toArray()['permissionClasses']['asset']));
    }
}
