<?php

namespace daos;

/**
 * Interface for class providing SQL helpers.
 */
interface StatementsInterface {
    /**
     * null first for order by clause
     *
     * @param string $column column to concat
     * @param string $order
     *
     * @return string full statement
     */
    public static function nullFirst($column, $order);

    /**
     * sum statement for boolean columns
     *
     * @param string $column column to concat
     *
     * @return string full statement
     */
    public static function sumBool($column);

    /**
     * bool true statement
     *
     * @param string $column column to check for truth
     *
     * @return string full statement
     */
    public static function isTrue($column);

    /**
     * bool false statement
     *
     * @param string $column column to check for false
     *
     * @return string full statement
     */
    public static function isFalse($column);

    /**
     * check if CSV column matches a value.
     *
     * @param string $column CSV column to check
     * @param mixed $value value to search in CSV column
     *
     * @return string full statement
     */
    public static function csvRowMatches($column, $value);

    /**
     * check column against int list.
     *
     * @param string $column column to check
     * @param array $ints of string or int values to match column against
     *
     * @return ?string full statement
     */
    public static function intRowMatches($column, array $ints);

    /**
     * Return the statement required to update a datetime column to the current
     * datetime.
     *
     * @param string $column
     *
     * @return string full statement
     */
    public static function rowTouch($column);

    /**
     * Convert boolean into a representation recognized by the database engine.
     *
     * @param bool $bool
     *
     * @return string representation of boolean
     */
    public static function bool($bool);

    /**
     * Convert a date string into a representation suitable for comparison by
     * the database engine.
     *
     * @param string $datestr ISO8601 datetime
     *
     * @return string representation of datetime
     */
    public static function datetime($datestr);

    /**
     * Ensure row values have the appropriate PHP type. This assumes we are
     * using buffered queries (sql results are in PHP memory);.
     *
     * @param array $rows array of associative array representing row results
     * @param array $expectedRowTypes associative array mapping columns to PDO types
     *
     * @return array of associative array representing row results having
     *         expected types
     */
    public static function ensureRowTypes(array $rows, array $expectedRowTypes);

    /**
     * convert string array to string for storage in table row
     *
     * @param string[] $a
     *
     * @return string
     */
    public static function csvRow(array $a);
}
