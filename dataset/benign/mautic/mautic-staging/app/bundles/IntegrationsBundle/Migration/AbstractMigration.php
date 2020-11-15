<?php

declare(strict_types=1);

/*
* @copyright   2019 Mautic, Inc. All rights reserved
* @author      Mautic, Inc.
*
* @link        https://mautic.com
*
* @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

namespace Mautic\IntegrationsBundle\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;

abstract class AbstractMigration implements MigrationInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $tablePrefix;

    /**
     * @var string[]
     */
    private $queries = [];

    public function __construct(EntityManager $entityManager, string $tablePrefix)
    {
        $this->entityManager = $entityManager;
        $this->tablePrefix   = $tablePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldExecute(): bool
    {
        return $this->isApplicable($this->entityManager->getConnection()->getSchemaManager()->createSchema());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(): void
    {
        $this->up();

        if (!$this->queries) {
            return;
        }

        $connection = $this->entityManager->getConnection();

        foreach ($this->queries as $sql) {
            $stmt = $connection->prepare($sql);
            $stmt->execute();
        }
    }

    /**
     * Generate the ALTER TABLE query that adds the foreign key.
     *
     * @param string[] $columns
     * @param string[] $referenceColumns
     * @param string   $suffix           usually a 'ON DELETE ...' statement
     */
    protected function generateAlterTableForeignKeyStatement(
        string $table,
        array $columns,
        string $referenceTable,
        array $referenceColumns,
        string $suffix = ''
    ): string {
        return "ALTER TABLE {$this->concatPrefix($table)} 
            ADD CONSTRAINT {$this->generatePropertyName($table, 'fk', $columns)} 
            FOREIGN KEY ({$this->columnsToString($columns)}) 
            REFERENCES {$this->concatPrefix($referenceTable)} ({$this->columnsToString($referenceColumns)}) {$suffix}
        ";
    }

    /**
     * @param string[] $columns
     */
    protected function generateIndexStatement(string $table, array $columns): string
    {
        return "INDEX {$this->generatePropertyName($table, 'idx', $columns)} ({$this->columnsToString($columns)})";
    }

    /**
     * @param string[] $columns
     */
    protected function columnsToString(array $columns): string
    {
        return implode(',', $columns);
    }

    /**
     * Generate the name for the property.
     *
     * This method was copied from AbstractMauticMigration.
     *
     * @param string[] $columnNames
     */
    protected function generatePropertyName(string $table, string $type, array $columnNames): string
    {
        $columnNames = array_merge([$this->tablePrefix.$table], $columnNames);
        $hash        = implode(
            '',
            array_map(
                function ($column) {
                    return dechex(crc32($column));
                },
                $columnNames
            )
        );

        return substr(strtoupper($type.'_'.$hash), 0, 63);
    }

    protected function addSql(string $sql): void
    {
        $this->queries[] = $sql;
    }

    /**
     * Concatenates table/index prefix to the provided name.
     */
    protected function concatPrefix(string $name): string
    {
        return $this->tablePrefix.$name;
    }

    /**
     * Define in the child migration whether the migration should be executed.
     * Check if the migration is applied in the schema already.
     */
    abstract protected function isApplicable(Schema $schema): bool;

    /**
     * Define queries for migration up.
     */
    abstract protected function up(): void;
}
