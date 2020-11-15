<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetCommand;
use UserFrosting\Tests\TestCase;

/**
 * MigrateResetCommand tests
 */
class BakeryMigrateResetCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        // Setup repository mock
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');
        $repository->shouldReceive('deleteRepository')->andReturn(null);

        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->twice()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('reset')->once()->with(false)->andReturn(['foo']);
        $migrator->shouldReceive('getNotes');
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testBasicCallWithNotthingToRollback()
    {
        // Setup repository mock
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');
        $repository->shouldReceive('deleteRepository')->andReturn(null);

        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->twice()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('reset')->once()->with(false)->andReturn([]);
        $migrator->shouldReceive('getNotes');
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testTheCommandMayBePretended()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('reset')->once()->with(true)->andReturn(['foo']);
        $migrator->shouldReceive('getNotes');
        $migrator->shouldNotReceive('getRepository');

        // Run command
        $commandTester = $this->runCommand($migrator, ['--pretend' => true]);
    }

    protected function runCommand($migrator, $input = [])
    {
        // Place the mock migrator inside the $ci
        $ci = $this->ci;
        $ci->migrator = $migrator;

        // Create the app, create the command, replace $ci and add the command to the app
        $app = new Application();
        $command = new MigrateResetCommand();
        $command->setContainer($ci);
        $app->add($command);

        // Add the command to the input to create the execute argument
        $execute = array_merge([
            'command' => $command->getName(),
        ], $input);

        // Execute command tester
        $commandTester = new CommandTester($command);
        $commandTester->execute($execute);

        return $commandTester;
    }
}
