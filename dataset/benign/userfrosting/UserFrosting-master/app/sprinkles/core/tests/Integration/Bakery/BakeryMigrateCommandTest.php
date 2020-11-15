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
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;
use UserFrosting\Tests\TestCase;

/**
 * MigrateCommand Test
 */
class BakeryMigrateCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testMigrationRepositoryCreatedWhenNecessary()
    {
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');

        $migrator->shouldReceive('repositoryExists')->once()->andReturn(false);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        $repository->shouldReceive('createRepository')->once();

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testTheCommandMayBePretended()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => true, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $commandTester = $this->runCommand($migrator, ['--pretend' => true]);
    }

    public function testStepMayBeSet()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => true])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $commandTester = $this->runCommand($migrator, ['--step' => true]);
    }

    protected function runCommand($migrator, $input = [])
    {
        // Place the mock migrator inside the $ci
        $ci = $this->ci;
        $ci->migrator = $migrator;

        // Create the app, create the command, replace $ci and add the command to the app
        $app = new Application();
        $command = new MigrateCommand();
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
