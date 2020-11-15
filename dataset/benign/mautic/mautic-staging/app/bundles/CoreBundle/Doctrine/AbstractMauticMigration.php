<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Doctrine;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\AbortMigration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractMauticMigration extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Supported platforms.
     *
     * @var array
     */
    protected $supported = ['mysql'];

    /**
     * Database prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Database platform.
     *
     * @var string
     */
    protected $platform;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @throws DBALException
     * @throws AbortMigration
     */
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        // Abort the migration if the platform is unsupported
        $this->abortIf(!in_array($platform, $this->supported), 'The database platform is unsupported for migrations');

        $function = $this->platform.'Up';

        if (method_exists($this, $function)) {
            $this->$function($schema);
        }
    }

    /**
     * @throws AbortMigration
     */
    public function down(Schema $schema): void
    {
        // Not supported
    }

    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container     = $container;
        $this->prefix        = $container->getParameter('mautic.db_table_prefix');
        $this->platform      = $this->connection->getDatabasePlatform()->getName();
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    /**
     * Finds/creates the local name for constraints and indexes.
     *
     * @param $table
     * @param $type
     * @param $suffix
     *
     * @return string
     */
    protected function findPropertyName($table, $type, $suffix)
    {
        static $schemaManager;
        static $tables = [];

        if (empty($schemaManager)) {
            $schemaManager = $this->connection->getSchemaManager();
        }

        // Prepend prefix
        $table = $this->prefix.$table;

        if (!array_key_exists($table, $tables)) {
            $tables[$table] = [];
        }

        $type   = strtolower($type);
        $suffix = strtolower(substr($suffix, -4));

        switch ($type) {
            case 'fk':
                if (!array_key_exists('fk', $tables[$table])) {
                    $keys = $schemaManager->listTableForeignKeys($table);
                    /** @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $k */
                    foreach ($keys as $k) {
                        $name                       = strtolower($k->getName());
                        $key                        = substr($name, -4);
                        $tables[$table]['fk'][$key] = $name;
                    }
                }

                $localName = $tables[$table]['fk'][$suffix];

                break;
            case 'idx':
            case 'uniq':
                if (!array_key_exists('idx', $tables[$table])) {
                    $tables[$table]['idx'] = [
                        'idx'  => [],
                        'uniq' => [],
                    ];

                    $indexes = $schemaManager->listTableIndexes($table);

                    /** @var \Doctrine\DBAL\Schema\Index $i */
                    foreach ($indexes as $i) {
                        $name   = strtolower($i->getName());
                        $isIdx  = stripos($name, 'idx');
                        $isUniq = stripos($name, 'uniq');

                        if (false !== $isIdx || false !== $isUniq) {
                            $key     = substr($name, -4);
                            $keyType = (false !== $isIdx) ? 'idx' : 'uniq';

                            $tables[$table]['idx'][$keyType][$key] = $name;
                        }
                    }
                }

                $localName = $tables[$table]['idx'][$type][$suffix];

                break;
        }

        return strtoupper($localName);
    }

    /**
     * Generate the  name for the property.
     *
     * @param $table
     * @param $type
     *
     * @return string
     */
    protected function generatePropertyName($table, $type, array $columnNames)
    {
        $columnNames = array_merge([$this->prefix.$table], $columnNames);
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

    /**
     * Generate index and foreign constraint.
     *
     * @param $table
     *
     * @return array [idx, fk]
     */
    protected function generateKeys($table, array $columnNames)
    {
        return [
            $this->generatePropertyName($table, 'idx', $columnNames),
            $this->generatePropertyName($table, 'fk', $columnNames),
        ];
    }

    /**
     * Use this when you're doing a migration that
     * purposely does not have any SQL statements,
     * such as when moving data using the query builder.
     */
    protected function suppressNoSQLStatementError()
    {
        $this->addSql('SELECT "This migration did not generate select statements." AS purpose');
    }
}
