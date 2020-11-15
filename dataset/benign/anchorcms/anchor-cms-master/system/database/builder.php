<?php

namespace System\database;

/**
 * Nano
 * Just another php framework
 *
 * @package    nano
 * @link       http://madebykieron.co.uk
 * @copyright  http://unlicense.org/
 */

/**
 * builder class
 *
 * @package System\database
 */
abstract class builder
{
    /**
     * bind
     *
     * @var
     */
    protected $bind;

    /**
     * table
     *
     * @var
     */
    protected $table;

    /**
     * offset
     *
     * @var
     */
    protected $offset;

    /**
     * connection
     *
     * @var
     */
    protected $connection;

    /**
     * join
     *
     * @var
     */
    protected $join;

    /**
     * groupby
     *
     * @var
     */
    protected $groupby;

    /**
     * limit
     *
     * @var
     */
    protected $limit;

    /**
     * sortby
     *
     * @var
     */
    protected $sortby;

    /**
     * where
     *
     * @var
     */
    protected $where;

    /**
     * Build table insert
     *
     * @param array
     *
     * @return string
     */
    public function build_insert($row)
    {
        $keys       = array_keys($row);
        $values     = $this->placeholders(count($row));
        $this->bind = array_values($row);

        return 'INSERT INTO ' . $this->wrap($this->table) . ' (' . $this->wrap($keys) . ') VALUES(' . $values . ')';
    }

    /**
     * Build placeholders to replace with values in a query
     *
     * @param int    $length
     * @param string $holder (optional) variable replacement character
     *
     * @return string
     */
    public function placeholders($length, $holder = '?')
    {
        $holders = [];

        for ($i = 0; $i < $length; $i++) {
            $holders[] = $holder;
        }

        return implode(', ', $holders);
    }

    /**
     * Wrap database tables and columns names
     *
     * @param string|array $column
     *
     * @return string
     */
    public function wrap($column)
    {
        if (is_array($column)) {
            $columns = [];

            foreach ($column as $c) {
                $columns[] = $this->wrap($c);
            }

            return implode(', ', $columns);
        }

        return $this->enclose($column);
    }

    /**
     * Enclose value with database connector escape characters
     *
     * @param string $value
     *
     * @return string
     */
    public function enclose($value)
    {
        $params        = [];
        $alias         = '';
        $alias_keyword = ' as ';

        if ($pos = strpos(strtolower($value), $alias_keyword)) {
            $alias = substr($value, $pos + strlen($alias_keyword));
            $value = substr($value, 0, $pos);
        }

        foreach (explode('.', $value) as $item) {
            if ($item == '*') {
                $params[] = $item;
            } else {
                // trim left if already escaped
                $item = $this->connection->lwrap . ltrim($item, $this->connection->lwrap);

                // trim right if already escaped
                $item = rtrim($item, $this->connection->rwrap) . $this->connection->rwrap;

                $params[] = $item;
            }
        }

        $value = implode('.', $params);

        if ($alias) {
            $value .= ' AS ' . $this->enclose($alias);
        }

        return $value;
    }

    /**
     * Build table update
     *
     * @param array $row
     *
     * @return string
     */
    public function build_update($row)
    {
        $placeholders = [];
        $values       = [];

        foreach ($row as $key => $value) {
            $placeholders[] = $this->wrap($key) . ' = ?';
            $values[]       = $value;
        }

        $update     = implode(', ', $placeholders);
        $this->bind = array_merge($values, $this->bind);

        return 'UPDATE ' . $this->wrap($this->table) . ' SET ' . $update . $this->build();
    }

    /**
     * Set a row offset on the query
     *
     * @return string
     */
    public function build()
    {
        $sql = '';

        if (count($this->join)) {
            $sql .= ' ' . implode(' ', $this->join);
        }

        if (count($this->where)) {
            $sql .= ' ' . implode(' ', $this->where);
        }

        if (count($this->groupby)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupby);
        }

        if (count($this->sortby)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->sortby);
        }

        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;

            if ($this->offset) {
                $sql .= ' OFFSET ' . $this->offset;
            }
        }

        return $sql;
    }

    /**
     * Build the select columns of the query
     *
     * @param array
     *
     * @return string
     */
    public function build_select($columns = null)
    {
        if (is_array($columns) and count($columns)) {
            $columns = $this->wrap($columns);
        } else {
            $columns = '*';
        }

        return 'SELECT ' . $columns . ' FROM ' . $this->wrap($this->table) . $this->build();
    }

    /**
     * Build a delete query
     *
     * @param array
     *
     * @return string
     */
    public function build_delete()
    {
        return 'DELETE FROM ' . $this->wrap($this->table) . $this->build();
    }

    /**
     * Build a select count query
     *
     * @return string
     */
    public function build_select_count()
    {
        return 'SELECT COUNT(*) FROM ' . $this->wrap($this->table) . $this->build();
    }
}
