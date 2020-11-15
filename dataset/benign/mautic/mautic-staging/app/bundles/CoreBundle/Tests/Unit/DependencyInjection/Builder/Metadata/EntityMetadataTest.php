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
use Mautic\CoreBundle\DependencyInjection\Builder\Metadata\EntityMetadata;
use PHPUnit\Framework\TestCase;

class EntityMetadataTest extends TestCase
{
    /**
     * @var BundleMetadata
     */
    private $metadata;

    protected function setUp()
    {
        $metadataArray = [
            'isPlugin'          => true,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MauticCoreBundle',
            'directory'         => __DIR__.'/../../../../../',
            'namespace'         => 'Mautic\\CoreBundle',
            'symfonyBundleName' => 'MauticCoreBundle',
            'bundleClass'       => '\\Mautic\\CoreBundle',
        ];

        $this->metadata = new BundleMetadata($metadataArray);
    }

    public function testOrmAndSerializerConfigsFound()
    {
        $entityMetadata = new EntityMetadata($this->metadata);
        $entityMetadata->build();

        $this->assertEquals(
            [
                'dir'       => 'Entity',
                'type'      => 'staticphp',
                'prefix'    => 'Mautic\\CoreBundle\\Entity',
                'mapping'   => true,
                'is_bundle' => true,
            ],
            $entityMetadata->getOrmConfig()
        );

        $this->assertEquals(
            [
                'namespace_prefix' => 'Mautic\\CoreBundle\\Entity',
                'path'             => '@MauticCoreBundle/Entity',
            ],
            $entityMetadata->getSerializerConfig()
        );
    }
}
