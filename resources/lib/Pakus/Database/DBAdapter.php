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
 * @package    Pakus.Database
 * @author     Paco Orozco <paco@pacoorozco.info>
 * @version    1.3
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

namespace Pakus\Database;

use mysqli;
use Pakus\Database\DBAdapterException;

/**
 * Database Abstraction Layer Class
 *
 * This class implements a Database Abstraction Layer in order to manage SQL
 * queries in more efficient way
 *
 * It uses a mysqli object, so you can use all mysqli functions calls with this
 * class object
 *
 */
class DBAdapter
{
    private $host;
    private $username;
    private $password;
    private $database;

    private $link;
    private $result;

    private $debug = false;

    /**
     * Constructor
     */
    public function __construct($host = '', $username = '', $password = '', $database = '')
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * Connect to MySQL
     */
    public function connect()
    {
        // connect only once
        if ($this->link !== null) {
            return $this->link;
        }

        $this->link = new \mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->link->connect_error) {
            throw new DBAdapterException('Error connecting to the server: ' . $this->link->connect_error);
        }
        return $this->link;
    }

    /**
     * Execute the specified query
     */
    public function query($query)
    {
        if (!is_string($query) || empty($query)) {
            throw new DBAdapterException('The specified query is not valid.');
        }

        try {
            // lazy connect to MySQL
            $this->connect();
            if ($this->debug) {
                error_log($query);
            }
            $this->result = $this->link->query($query);
            return $this->result;
        } catch (Exception $e) {
            throw new DBAdapterException(
                'Error executing the specified query: ' . $query . $e->getMessage()
            );
        }

    }

    /**
     * Returns the first row from database result and returns a PHP array
     *
     */
    public function getRow($query)
    {
        if (!is_string($query) || empty($query)) {
            throw new DBAdapterException('The specified query is not valid.');
        }

        try {
            // lazy connect to MySQL
            $this->connect();
            $this->result = $this->query($query);
            return $this->result->fetch_assoc();
        } catch (Exception $e) {
            throw new DBAdapterException(
                'Error executing the specified query ' . $query . $e->getMessage()
            );
        }
    }

    /**
     * Fetch a single row from the current result set (as an associative array)
     */
    public function fetch()
    {
        if ($this->result !== null) {
            if (($row = $this->result->fetch_array(MYSQLI_ASSOC)) !== false) {
                return $row;
            }
            $this->freeResult();
            return false;
        }
        return null;
    }

    /**
     * Returns the first field of the first row result
     *
     */
    public function getOne($query)
    {
        if (!is_string($query) || empty($query)) {
            throw new DBAdapterException('The specified query is not valid.');
        }

        try {
            // lazy connect to MySQL
            $this->connect();
            $this->result = $this->query($query);
            return reset($this->result->fetch_row());
        } catch (Exception $e) {
            throw new DBAdapterException(
                'Error executing the specified query ' . $query . $e->getMessage()
            );
        }
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
        if (!is_string($query) || empty($query)) {
            throw new DBAdapterException('The specified query is not valid.');
        }

        try {
            // lazy connect to MySQL
            $this->connect();
            $this->result = $this->query($query);
            $ret = array();
            while ($row = $this->result->fetch_assoc()) {
                $ret[] = $row;
            }
            return $ret;
        } catch (Exception $e) {
            throw new DBAdapterException(
                'Error executing the specified query ' . $query . $e->getMessage()
            );
        }
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
        if (!is_string($query) || empty($query)) {
            throw new DBAdapterException('The specified query is not valid.');
        }

        try {
            // lazy connect to MySQL
            $this->connect();
            $this->result = $this->query($query);
            $ret = array();
            while ($row = $this->result->fetch_assoc()) {
                $values = array_values($row);
                $ret[$values[0]] = $values[1];
            }
            return $ret;
        } catch (Exception $e) {
            throw new DBAdapterException(
                'Error executing the specified query ' . $query . $e->getMessage()
            );
        }
    }

    /**
     * Escapes and returns string to use in a query. Use instead
     * mysqli::real_escape_string()
     *
     * If given an array, calls qstr() method
     *
     * @param string $str
     * @return string Escaped string
     */
    public function qstr($str)
    {
        $this->connect();
        if (is_array($str)) {
            return $this->qstrArr($str);
        }

        if (is_string($str)) {
            $str = $this->link->real_escape_string($str);
        }

        return $str;
    }

    /**
     * Calls qstr() method for all values in given array and returns
     *
     * @param array $arr
     * @return array Array of escaped strings
     */
    private function qstrArr($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$this->qstr($key)] = $this->qstr($value);
        }

        return $arr;
    }

    /**
     * Quotes and returns string to use in a query
     *
     * If given an array, calls quoteArr() method
     *
     * @param string $str
     * @return string Quoted string
     */
    public function quote($value)
    {
        if (is_array($value)) {
            return $this->quoteArr($value);
        }
        if (empty($value) or is_string($value)) {
            return "'" . $value . "'";
        }
        return $value;
    }

    /**
     * Calls quote() method for all values in given array and returns
     *
     * @param array $arr
     * @return array Array of quoted strings
     */
    private function quoteArr($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$key] = $this->quote($value);
        }
        return $arr;
    }

    /**
     * Returns values between backticks '`'
     *
     * If you pass an array, backtickArr() is called
     *
     * @param string $value
     * @return string A backticked string
     */
    public function backtick($value)
    {
        if (is_array($value)) {
            return $this->backtickArr($value);
        }
        return "`" . $value . "`";
    }

    /**
     * Call backtick() method for all values in given array and returns
     *
     * @param array $arr
     * @return array Array of backticked strings
     */
    private function backtickArr($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$key] = $this->backtick($value);
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
    public function insert($table, array $data)
    {
        /**
         * Cleaning the key allows the developer to insert the entire
         * $_POST array should he wish to and still be safe from attacks.
         */
        $fields = implode(', ', $this->backtick($this->qstr(array_keys($data))));
        // Values should always be cleaned
        $values = implode(', ', $this->quote($this->qstr(array_values($data))));

        // Build the query string
        $query = 'INSERT INTO ' . $this->backtick($table) . ' (' . $fields . ') VALUES (' . $values . ')';
        $this->query($query);
        return $this->getInsertId();
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
    public function update($table, array $data, $where = false)
    {
        // Build the SET part of the query string
        $set = array();
        foreach ($data as $field => $value) {
            $set[] = $this->backtick($this->qstr($field)) . ' = ' .
                $this->quote($this->qstr($value));
        }
        $set = implode(',', $set);

        // Set the query string
        $query = 'UPDATE ' . $this->backtick($table) . ' SET ' . $set;

        // Add WHERE clause if given
        if (false !== $where) {
            $query .= " WHERE ". $where;
        }
        $this->query($query);
        return $this->getAffectedRows();
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
        $query = "DELETE FROM " . $this->backtick($table);

        // Add WHERE clause if given
        if (false !== $where) {
            $query .= " WHERE ". $where;
        }
        $this->query($query);
        return $this->getAffectedRows();
    }

    /**
     * Get the insertion ID
     */
    public function getInsertId()
    {
        return $this->link !== null ?
               $this->link->insert_id :
               null;
    }

    /**
     * Get the number of rows returned by the current result set
     */
    public function countRows()
    {
        return $this->result !== null ?
               $this->result->num_rows :
               0;
    }

    /**
     * Get the number of affected rows
     */
    public function getAffectedRows()
    {
        return $this->link !== null ?
               $this->link->affected_rows :
               0;
    }

    /**
     * Enable / disable DEBUG mode
     */
    public function setDebug($status = true)
    {
        $this->debug = $status;
    }

    /**
     * Close explicitly the database connection
     */
    public function disconnect()
    {
        if ($this->link !== null) {
            $this->link->close();
            $this->link = null;
            return true;
        }
        return false;
    }

    /**
     * Close automatically the database connection when the instance of the class is destroyed
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
