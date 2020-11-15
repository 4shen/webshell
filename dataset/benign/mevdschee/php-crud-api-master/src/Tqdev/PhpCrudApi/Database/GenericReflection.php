<?php

namespace Tqdev\PhpCrudApi\Database;

use Tqdev\PhpCrudApi\Database\LazyPdo;

class GenericReflection
{
    private $pdo;
    private $driver;
    private $database;
    private $tables;
    private $typeConverter;

    public function __construct(LazyPdo $pdo, string $driver, string $database, array $tables)
    {
        $this->pdo = $pdo;
        $this->driver = $driver;
        $this->database = $database;
        $this->tables = $tables;
        $this->typeConverter = new TypeConverter($driver);
    }

    public function getIgnoredTables(): array
    {
        switch ($this->driver) {
            case 'mysql':
                return [];
            case 'pgsql':
                return ['spatial_ref_sys', 'raster_columns', 'raster_overviews', 'geography_columns', 'geometry_columns'];
            case 'sqlsrv':
                return [];
            case 'sqlite':
                return [];
        }
    }

    private function getTablesSQL(): string
    {
        switch ($this->driver) {
            case 'mysql':
                return 'SELECT "TABLE_NAME", "TABLE_TYPE" FROM "INFORMATION_SCHEMA"."TABLES" WHERE "TABLE_TYPE" IN (\'BASE TABLE\' , \'VIEW\') AND "TABLE_SCHEMA" = ? ORDER BY BINARY "TABLE_NAME"';
            case 'pgsql':
                return 'SELECT c.relname as "TABLE_NAME", c.relkind as "TABLE_TYPE" FROM pg_catalog.pg_class c LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE c.relkind IN (\'r\', \'v\') AND n.nspname <> \'pg_catalog\' AND n.nspname <> \'information_schema\' AND n.nspname !~ \'^pg_toast\' AND pg_catalog.pg_table_is_visible(c.oid) AND \'\' <> ? ORDER BY "TABLE_NAME";';
            case 'sqlsrv':
                return 'SELECT o.name as "TABLE_NAME", o.xtype as "TABLE_TYPE" FROM sysobjects o WHERE o.xtype IN (\'U\', \'V\') ORDER BY "TABLE_NAME"';
            case 'sqlite':
                return 'SELECT t.name as "TABLE_NAME", t.type as "TABLE_TYPE" FROM sqlite_master t WHERE t.type IN (\'table\', \'view\') AND \'\' <> ? ORDER BY "TABLE_NAME"';
        }
    }

    private function getTableColumnsSQL(): string
    {
        switch ($this->driver) {
            case 'mysql':
                return 'SELECT "COLUMN_NAME", "IS_NULLABLE", "DATA_TYPE", "CHARACTER_MAXIMUM_LENGTH" as "CHARACTER_MAXIMUM_LENGTH", "NUMERIC_PRECISION", "NUMERIC_SCALE", "COLUMN_TYPE" FROM "INFORMATION_SCHEMA"."COLUMNS" WHERE "TABLE_NAME" = ? AND "TABLE_SCHEMA" = ? ORDER BY "ORDINAL_POSITION"';
            case 'pgsql':
                return 'SELECT a.attname AS "COLUMN_NAME", case when a.attnotnull then \'NO\' else \'YES\' end as "IS_NULLABLE", pg_catalog.format_type(a.atttypid, -1) as "DATA_TYPE", case when a.atttypmod < 0 then NULL else a.atttypmod-4 end as "CHARACTER_MAXIMUM_LENGTH", case when a.atttypid != 1700 then NULL else ((a.atttypmod - 4) >> 16) & 65535 end as "NUMERIC_PRECISION", case when a.atttypid != 1700 then NULL else (a.atttypmod - 4) & 65535 end as "NUMERIC_SCALE", \'\' AS "COLUMN_TYPE" FROM pg_attribute a JOIN pg_class pgc ON pgc.oid = a.attrelid WHERE pgc.relname = ? AND \'\' <> ? AND a.attnum > 0 AND NOT a.attisdropped ORDER BY a.attnum;';
            case 'sqlsrv':
                return 'SELECT c.name AS "COLUMN_NAME", c.is_nullable AS "IS_NULLABLE", t.Name AS "DATA_TYPE", (c.max_length/2) AS "CHARACTER_MAXIMUM_LENGTH", c.precision AS "NUMERIC_PRECISION", c.scale AS "NUMERIC_SCALE", \'\' AS "COLUMN_TYPE" FROM sys.columns c INNER JOIN sys.types t ON c.user_type_id = t.user_type_id WHERE c.object_id = OBJECT_ID(?) AND \'\' <> ? ORDER BY c.column_id';
            case 'sqlite':
                return 'SELECT "name" AS "COLUMN_NAME", case when "notnull"==1 then \'no\' else \'yes\' end as "IS_NULLABLE", lower("type") AS "DATA_TYPE", 2147483647 AS "CHARACTER_MAXIMUM_LENGTH", 0 AS "NUMERIC_PRECISION", 0 AS "NUMERIC_SCALE", \'\' AS "COLUMN_TYPE" FROM pragma_table_info(?) WHERE \'\' <> ? ORDER BY "cid"';
        }
    }

    private function getTablePrimaryKeysSQL(): string
    {
        switch ($this->driver) {
            case 'mysql':
                return 'SELECT "COLUMN_NAME" FROM "INFORMATION_SCHEMA"."KEY_COLUMN_USAGE" WHERE "CONSTRAINT_NAME" = \'PRIMARY\' AND "TABLE_NAME" = ? AND "TABLE_SCHEMA" = ?';
            case 'pgsql':
                return 'SELECT a.attname AS "COLUMN_NAME" FROM pg_attribute a JOIN pg_constraint c ON (c.conrelid, c.conkey[1]) = (a.attrelid, a.attnum) JOIN pg_class pgc ON pgc.oid = a.attrelid WHERE pgc.relname = ? AND \'\' <> ? AND c.contype = \'p\'';
            case 'sqlsrv':
                return 'SELECT c.NAME as "COLUMN_NAME" FROM sys.key_constraints kc inner join sys.objects t on t.object_id = kc.parent_object_id INNER JOIN sys.index_columns ic ON kc.parent_object_id = ic.object_id and kc.unique_index_id = ic.index_id INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id WHERE kc.type = \'PK\' and t.object_id = OBJECT_ID(?) and \'\' <> ?';
            case 'sqlite':
                return 'SELECT "name" as "COLUMN_NAME" FROM pragma_table_info(?) WHERE "pk"=1 AND \'\' <> ?';
        }
    }

    private function getTableForeignKeysSQL(): string
    {
        switch ($this->driver) {
            case 'mysql':
                return 'SELECT "COLUMN_NAME", "REFERENCED_TABLE_NAME" FROM "INFORMATION_SCHEMA"."KEY_COLUMN_USAGE" WHERE "REFERENCED_TABLE_NAME" IS NOT NULL AND "TABLE_NAME" = ? AND "TABLE_SCHEMA" = ?';
            case 'pgsql':
                return 'SELECT a.attname AS "COLUMN_NAME", c.confrelid::regclass::text AS "REFERENCED_TABLE_NAME" FROM pg_attribute a JOIN pg_constraint c ON (c.conrelid, c.conkey[1]) = (a.attrelid, a.attnum) JOIN pg_class pgc ON pgc.oid = a.attrelid WHERE pgc.relname = ? AND \'\' <> ? AND c.contype  = \'f\'';
            case 'sqlsrv':
                return 'SELECT COL_NAME(fc.parent_object_id, fc.parent_column_id) AS "COLUMN_NAME", OBJECT_NAME (f.referenced_object_id) AS "REFERENCED_TABLE_NAME" FROM sys.foreign_keys AS f INNER JOIN sys.foreign_key_columns AS fc ON f.OBJECT_ID = fc.constraint_object_id WHERE f.parent_object_id = OBJECT_ID(?) and \'\' <> ?';
            case 'sqlite':
                return 'SELECT "from" AS "COLUMN_NAME", "table" AS "REFERENCED_TABLE_NAME" FROM pragma_foreign_key_list(?) WHERE \'\' <> ?';
        }
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }

    public function getTables(): array
    {
        $sql = $this->getTablesSQL();
        $results = $this->query($sql, [$this->database]);
        $tables = $this->tables;
        $results = array_filter($results, function ($v) use ($tables) {
            return !$tables || in_array($v['TABLE_NAME'], $tables);
        });
        foreach ($results as &$result) {
            $map = [];
            switch ($this->driver) {
                case 'mysql':
                    $map = ['BASE TABLE' => 'table', 'VIEW' => 'view'];
                    break;
                case 'pgsql':
                    $map = ['r' => 'table', 'v' => 'view'];
                    break;
                case 'sqlsrv':
                    $map = ['U' => 'table', 'V' => 'view'];
                    break;
                case 'sqlite':
                    $map = ['table' => 'table', 'view' => 'view'];
                    break;
            }
            $result['TABLE_TYPE'] = $map[trim($result['TABLE_TYPE'])];
        }
        return $results;
    }

    public function getTableColumns(string $tableName, string $type): array
    {
        $sql = $this->getTableColumnsSQL();
        $results = $this->query($sql, [$tableName, $this->database]);
        if ($type == 'view') {
            foreach ($results as &$result) {
                $result['IS_NULLABLE'] = false;
            }
        }
        if ($this->driver == 'mysql') {
            foreach ($results as &$result) {
                // mysql does not properly reflect display width of types
                preg_match('|([a-z]+)(\(([0-9]+)(,([0-9]+))?\))?|', $result['DATA_TYPE'], $matches);
                $result['DATA_TYPE'] = $matches[1];
                if (!$result['CHARACTER_MAXIMUM_LENGTH']) {
                    if (isset($matches[3])) {
                        $result['NUMERIC_PRECISION'] = $matches[3];
                    }
                    if (isset($matches[5])) {
                        $result['NUMERIC_SCALE'] = $matches[5];
                    }
                }
            }
        }
        if ($this->driver == 'sqlite') {
            foreach ($results as &$result) {
                // sqlite does not properly reflect display width of types
                preg_match('|([a-z]+)(\(([0-9]+)(,([0-9]+))?\))?|', $result['DATA_TYPE'], $matches);
                if (isset($matches[1])) {
                    $result['DATA_TYPE'] = $matches[1];
                } else {
                    $result['DATA_TYPE'] = 'integer';
                }
                if (isset($matches[5])) {
                    $result['NUMERIC_PRECISION'] = $matches[3];
                    $result['NUMERIC_SCALE'] = $matches[5];
                } else if (isset($matches[3])) {
                    $result['CHARACTER_MAXIMUM_LENGTH'] = $matches[3];
                }
            }
        }
        return $results;
    }

    public function getTablePrimaryKeys(string $tableName): array
    {
        $sql = $this->getTablePrimaryKeysSQL();
        $results = $this->query($sql, [$tableName, $this->database]);
        $primaryKeys = [];
        foreach ($results as $result) {
            $primaryKeys[] = $result['COLUMN_NAME'];
        }
        return $primaryKeys;
    }

    public function getTableForeignKeys(string $tableName): array
    {
        $sql = $this->getTableForeignKeysSQL();
        $results = $this->query($sql, [$tableName, $this->database]);
        $foreignKeys = [];
        foreach ($results as $result) {
            $foreignKeys[$result['COLUMN_NAME']] = $result['REFERENCED_TABLE_NAME'];
        }
        return $foreignKeys;
    }

    public function toJdbcType(string $type, string $size): string
    {
        return $this->typeConverter->toJdbc($type, $size);
    }

    private function query(string $sql, array $parameters): array
    {
        $stmt = $this->pdo->prepare($sql);
        //echo "- $sql -- " . json_encode($parameters, JSON_UNESCAPED_UNICODE) . "\n";
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }
}
