<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * MigrationRepository Class.
 *
 * Repository used to store all migrations run against the database
 *
 * @author Louis Charette
 */
class DatabaseMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * @var Capsule
     */
    protected $db;

    /**
     * @var string The name of the migration table.
     */
    protected $table;

    /**
     * @var string The connection name
     */
    protected $connection;

    /**
     * Create a new database migration repository instance.
     *
     * @param Capsule $db
     * @param string  $table
     */
    public function __construct(Capsule $db, $table = 'migrations')
    {
        $this->table = $table;
        $this->db = $db;
    }

    /**
     * Get the list of ran migrations.
     *
     * @param int    $steps Number of batch to return
     * @param string $order asc|desc
     *
     * @return array An array of migration class names in the order they where ran
     */
    public function getMigrationsList($steps = -1, $order = 'asc')
    {
        return $this->getMigrations($steps, $order)->pluck('migration')->all();
    }

    /**
     * Get list of migrations.
     *
     * @param int    $steps Number of batch to return
     * @param string $order asc|desc
     *
     * @return array
     */
    public function getMigrations($steps = -1, $order = 'asc')
    {
        $query = $this->table();

        if ($steps > 0) {
            $batch = max($this->getNextBatchNumber() - $steps, 1);
            $query = $query->where('batch', '>=', $batch);
        }

        return $query->orderBy('id', $order)->get();
    }

    /**
     * Get details about a specific migration.
     *
     * @param string $migration The migration class
     *
     * @return \stdClass The migration info
     */
    public function getMigration($migration)
    {
        return $this->table()->where('migration', $migration)->first();
    }

    /**
     * Get the last migration batch in reserve order they were ran (last one first).
     *
     * @return array
     */
    public function getLast()
    {
        $query = $this->table()->where('batch', $this->getLastBatchNumber());

        return $query->orderBy('id', 'desc')->get()->pluck('migration')->all();
    }

    /**
     * Log that a migration was run.
     *
     * @param string $file
     * @param int    $batch
     * @param string $sprinkle
     */
    public function log($file, $batch, $sprinkle = '')
    {
        $record = ['migration' => $file, 'batch' => $batch, 'sprinkle' => $sprinkle];

        $this->table()->insert($record);
    }

    /**
     * Remove a migration from the log.
     *
     * @param string $migration
     */
    public function delete($migration)
    {
        $this->table()->where('migration', $migration)->delete();
    }

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return $this->table()->max('batch');
    }

    /**
     * Create the migration repository data store.
     */
    public function createRepository()
    {
        $this->getSchemaBuilder()->create($this->table, function (Blueprint $table) {
            // The migrations table is responsible for keeping track of which of the
            // migrations have actually run for the application. We'll create the
            // table to hold the migration file's path as well as the batch ID.
            $table->increments('id');
            $table->string('sprinkle');
            $table->string('migration');
            $table->integer('batch');
        });
    }

    /**
     * Delete the migration repository data store.
     */
    public function deleteRepository()
    {
        $this->getSchemaBuilder()->drop($this->table);
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists()
    {
        return $this->getSchemaBuilder()->hasTable($this->table);
    }

    /**
     * Get a query builder for the migration table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->getConnection()->table($this->table);
    }

    /**
     * Returns the schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Resolve the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->db->getConnection($this->connection);
    }

    /**
     * Set the information source to gather data.
     *
     * @param string $name The source name
     */
    public function setSource($name)
    {
        $this->connection = $name;
    }
}
