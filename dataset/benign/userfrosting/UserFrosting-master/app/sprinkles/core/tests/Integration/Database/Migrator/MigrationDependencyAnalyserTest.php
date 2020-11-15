<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationDependencyAnalyser;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;
use UserFrosting\Tests\TestCase;

class MigrationDependencyAnalyserTest extends TestCase
{
    /**
     * @var MigrationLocator The migration locator instance.
     */
    protected $locator;

    public function testAnalyser()
    {
        $migrations = [
            'UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ];

        $expected = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ];

        $analyser = new MigrationDependencyAnalyser($migrations, []);

        $this->assertEquals($expected, $analyser->getFulfillable());
        $this->assertEquals([], $analyser->getUnfulfillable());
    }

    public function testAnalyserWithInvalidClass()
    {
        $migrations = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\Foo',
        ];

        $analyser = new MigrationDependencyAnalyser($migrations, []);

        $this->expectException(BadClassNameException::class);
        $analyser->analyse();
    }

    public function testAnalyserWithReordered()
    {
        $analyser = new MigrationDependencyAnalyser([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
            'UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ], []);

        $this->assertEquals([], $analyser->getUnfulfillable());
        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
        ], $analyser->getFulfillable());
    }

    public function testAnalyserWithUnfulfillable()
    {
        $migrations = [
            'UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\UnfulfillableTable',
        ];

        $analyser = new MigrationDependencyAnalyser($migrations, []);

        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ], $analyser->getFulfillable());

        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\UnfulfillableTable' => '\UserFrosting\Tests\Integration\Migrations\NonExistingMigration',
        ], $analyser->getUnfulfillable());
    }
}
