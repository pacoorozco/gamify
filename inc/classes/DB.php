<?php

/**
 * Database Abstraction Layer Class
 *
 *
 * This class implements a Database Abstraction Layer in order to manage SQL
 * queries in more efficient way.
 *
 * LICENSE: Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 *
 * @category   Pakus
 * @package    Database
 * @license    http://creativecommons.org/licenses/by-sa/3.0/deed.en (CC BY-SA 3.0)
 * @version    1.0
 * @link       https://git.upcnet.es/bo/gamify
 */
namespace Pakus\Database;

/**
 * Database Abstraction Layer Class
 *
 * This class implements a Database Abstraction Layer in order to manage SQL
 * queries in more efficient way.
 *
 * @category   Pakus
 * @package    Database
 * @author     Paco Orozco (paco@pacoorozco.info)
 * @license    http://creativecommons.org/licenses/by-sa/3.0/deed.en (CC BY-SA 3.0)
 * @version    1.0
 * @link       https://git.upcnet.es/bo/gamify
 */
class DB extends \mysqli
{

    /**
     * Returns the first row from database result and returns PHP array.
     */
    public function getRow($query)
    {
        $result = $this->query($query);

        if (!$result) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Returns the first field of the first row.
     */
    public function getOne($query)
    {
        $result = $this->query($query);

        if (!$result) {
            return null;
        }

        $row = $result->fetch_row();

        return is_array($row) ? reset($row) : false;
    }

    /**
     * Returns an array populated with all the selected rows.
     * Note: do not use this on a large result sets as you may run out of memory.
     * Use query() method instead and iterate through returned result.
     */
    public function getAll($query)
    {
        $result = $this->query($query);
        $ret = array();

        if (!$result) {
            return null;
        }

        while ($row = $result->fetch_assoc()) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Returns an array where all keys are the first fields of a row and values
     * are the second ones.
     * Note: do not use this on a large result sets as you may run out of memory.
     * Use query() method instead and iterate through returned result.
     */
    public function getAssoc($query)
    {
        $result = $this->query($query);
        $ret = array();

        if (!$result) {
            return null;
        }

        while ($row = $result->fetch_assoc()) {
            $values = array_values($row);

            $ret[$values[0]] = $values[1];
        }

        return $ret;
    }

    /**
     * Escapes and quotes and returns string to use in a query.
     * If given an array, calls qstr() method.
     */
    public function qstr($str)
    {
        if (is_array($str)) {
            return $this->qstrArr($str);
        }

        if (is_string($str)) {
            $str = $this->real_escape_string($str);
        }

        return $str;
    }

    /**
     * Calls qstr() method for all values in given array and returns.
     */
    public function qstrArr($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$this->qstr($key)] = $this->qstr($value);
        }

        return $arr;
    }

    /**
     * Inserts data into database.
     * The first perameter is the table you wish to insert data into and the
     * second is an associative array.
     * The key is a string defining the column of the table to input into and
     * the value being the information to input.
     * Returns ID of the inserted record.
     */
    public function insert($table, $arr = array())
    {
        /**
         * Cleaning the key allows the developer to insert the entire
         * $_POST array should he wish to and still be safe from attacks.
         */
        $keys = '`' . implode("`, `", $this->qstr(array_keys($arr))) . '`';
        // Values should always be cleaned
        $values = "'" . implode("', '", $this->qstr(array_values($arr))) . "'";

        // Build the query string
        $query = "INSERT INTO `" . $table . "` (" . $keys . ") VALUES (" . $values . ")";
        $this->query($query);

        return $this->insert_id;
    }

    /**
     * Updates data into database
     * The update method works much in the same way as the insert method,
     * except it takes an additional parameter which is the WHERE clause of the
     * SQL query string.
     */
    public function update($table, $arr = array(), $where = false)
    {
        // Start the query string
        $query = "UPDATE `" . $table . "` SET ";

        // Build the SET part of the query string
        foreach ($arr as $key => $value) {
            $query .= '`'.$this->qstr($key)."` = '".$this->qstr($value)."', ";
        }
        $query = rtrim($query, ', ');

        // Add WHERE clause if given
        if (false !== $where) {
            $query .= " WHERE ". $where;
        }

        return $this->query($query);
    }
}
