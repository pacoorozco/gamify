<?php

/**
 * This file is part of gamify project.
 * Copyright (C) 2014  Paco Orozco <paco_@_pacoorozco.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * @category   Pakus
 * @package    Database
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

namespace Pakus\Database;

/**
 * Database Abstraction Layer Class
 *
 * This class implements a Database Abstraction Layer in order to manage SQL
 * queries in more efficient way
 * 
 * It uses a mysqli object, so you can use all mysqli functions calls with this
 * class object
 *
 * @category   Pakus
 * @package    Database
 * @author     Paco Orozco <paco@pacoorozco.info>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/deed.en (CC BY-SA 3.0)
 * @version    1.1
 * @link       https://git.upcnet.es/bo/gamify
 */
class DB extends \mysqli
{

    /**
     * Returns the first row from database result and returns a PHP array
     * 
     * @param string $query
     * @return array|null Array with row values and row names as keys on success, null on failure
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
     * Returns the first field of the first row result
     * 
     * @param string $query
     * @return string|null String with the first value on success, null on failure
     */
    public function getOne($query)
    {
        $result = $this->query($query);

        if (!$result) {
            return null;
        }

        $row = $result->fetch_row();

        return is_array($row) ? reset($row) : null;
    }

    /**
     * Returns an array populated with all the selected rows
     * 
     * Note: do not use this on a large result sets as you may run out of memory.
     * Use query() method instead and iterate through returned result
     * 
     * 
     * @param string $query
     * @return array|null Array containing an array with row values and 
     *                    row names as keys on success, null on failure
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
     * are the second ones
     * 
     * Note: do not use this on a large result sets as you may run out of memory.
     * Use query() method instead and iterate through returned result
     * 
     * @param string $query
     * @return array|null Array where all keys are the first fields of a row 
     *                    and values are the second ones on success, null on failure
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
     * Escapes and quotes and returns string to use in a query. Use instead
     * mysqli::real_escape_string()
     * 
     * If given an array, calls qstr() method
     * 
     * @param string $str
     * @return string Escaped string
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
     * Calls qstr() method for all values in given array and returns
     * 
     * @param array $arr
     * @return array Array of escaped strings
     */
    public function qstrArr($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$this->qstr($key)] = $this->qstr($value);
        }

        return $arr;
    }

    /**
     * Inserts data into database
     * 
     * The first perameter is the table you wish to insert data into and the
     * second is an associative array
     * 
     * The key is a string defining the column of the table to input into and
     * the value being the information to input
     * 
     * Returns ID of the inserted record
     * 
     * All values will be passed to qstr() to make it safe
     * 
     * In example:
     * 
     * $insertedRecordId = $db->insert(
     *         'table_name',
     *         array(
     *             'column1' => 'value1',
     *             'column2' => 'value2',
     *             'column3' => 'value3
     *         )
     *     );
     * 
     * @param string $table
     * @param array associative array 'column' => 'value'
     * @return integer last inserted record on DB
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
     * 
     * The update method works much in the same way as the insert method,
     * except it takes an additional parameter which is the WHERE clause of the
     * SQL query string
     * 
     * All values will be passed to qstr() to make it safe
     * 
     * In example:
     * 
     * $result = $db->update(
     *         'table_name',
     *         array(
     *             'column1' => 'value1',
     *             'column2' => 'value2',
     *             'column3' => 'value3
     *         ),
     *         'id = 5 AND status = true'
     *     );
     * 
     * @param string $table
     * @param array associative array 'column' => 'value'
     * @param string where clause, is left optional in case wants to update all
     * @return bool true on success, false on failure
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
    
    /**
     * Deletes record from database
     * 
     * 
     * In example:
     * 
     * $result = $db->update(
     *         'table_name',
     *         array(
     *             'column1' => 'value1',
     *             'column2' => 'value2',
     *             'column3' => 'value3
     *         ),
     *         'id = 5 AND status = true'
     *     );
     * 
     * @param string $table
     * @param string Where clause, is left optional in case wants to delete all
     * @return bool true on success, false on failure
     */
    public function delete($table, $where = false)
    {
        // Start the query string
        $query = "DELETE FROM `" . $table . "` ";

        // Add WHERE clause if given
        if (false !== $where) {
            $query .= " WHERE ". $where;
        }

        return $this->query($query);
    }
}
