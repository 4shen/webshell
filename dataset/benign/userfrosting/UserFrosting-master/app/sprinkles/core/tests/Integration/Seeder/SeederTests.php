<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Integration\Seeder;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Container\ContainerInterface;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;
use UserFrosting\Sprinkle\Core\Database\Seeder\SeedInterface;
use UserFrosting\Tests\TestCase;
use Slim\Container;

class SeederTests extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Container $fakeCi
     */
    protected $fakeCi;

    /**
     * Setup our fake ci
     */
    public function setUp(): void
    {
        // Boot parent TestCase
        parent::setUp();

        // We must create our own CI with a custom locator for theses tests
        $this->fakeCi = new Container();

        // Register services stub
        $serviceProvider = new ServicesProviderStub();
        $serviceProvider->register($this->fakeCi);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return Seeder
     */
    public function testSeeder()
    {
        $seeder = new Seeder($this->fakeCi);
        $this->assertInstanceOf(Seeder::class, $seeder);

        return $seeder;
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testgetSeeds(Seeder $seeder)
    {
        $seeds = $seeder->getSeeds();
        $this->assertIsArray($seeds);
        $this->assertCount(3, $seeds);
        $this->assertEquals([
            [
                'name'     => 'Seed1',
                'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Seed1',
                'sprinkle' => 'Core',
            ],
            [
                'name'     => 'Seed2',
                'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Seed2',
                'sprinkle' => 'Core',
            ],
            [
                'name'     => 'Test/Seed',
                'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Test\\Seed',
                'sprinkle' => 'Core',
            ],
        ], $seeds);
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testGetSeed(Seeder $seeder)
    {
        $seed = $seeder->getSeed('Seed1');
        $this->assertIsArray($seed);
        $this->assertEquals([
            'name'     => 'Seed1',
            'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Seed1',
            'sprinkle' => 'Core',
        ], $seed);
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testUnfoundGetSeed(Seeder $seeder)
    {
        $this->expectException(\Exception::class);
        $seeder->getSeed('FakeSeed');
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testGetSeedClass(Seeder $seeder)
    {
        $seed = $seeder->getSeedClass('Seed1');
        $this->assertInstanceOf(SeedInterface::class, $seed);
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testGetSeedClassNotSeedInterface(Seeder $seeder)
    {
        $this->expectException(\Exception::class);
        $seeder->getSeedClass('Seed2'); // This class is not an instance of SeedInterface
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testGetSeedClassException(Seeder $seeder)
    {
        $this->expectException(\Exception::class);
        $seeder->getSeedClass('Test/Seed'); // The namespace in this class is wrong
    }

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    public function testExecuteSeed(Seeder $seeder)
    {
        // Get a fake seed
        $seed = m::mock('\UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed');
        $seed->shouldReceive('run');

        $seeder->executeSeed($seed);
    }
}

/**
 * ServicesProviderStub
 */
class ServicesProviderStub
{
    /**
     * @param ContainerInterface $container A DI container implementing ArrayAccess and psr-container.
     */
    public function register(ContainerInterface $container)
    {
        /**
         * @return \UserFrosting\UniformResourceLocator\ResourceLocator
         */
        $container['locator'] = function ($c) {
            $locator = new ResourceLocator(\UserFrosting\SPRINKLES_DIR);
            $locator->registerStream('seeds', '', 'Seeder/Seeds/');
            $locator->registerLocation('Core', 'core/tests/Integration/');

            return $locator;
        };
    }
}
