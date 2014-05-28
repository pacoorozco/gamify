<?php

/**
 * Database Abstraction Layer
 *
 * This file implements a Database Abstraction Layer in order to manage SQL
 * queries in more efficient way.
 *
 * LICENSE: Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 *
 * @category   Database
 * @copyright  Paco Orozco (paco@pacoorozco.info)
 * @license    http://creativecommons.org/licenses/by-sa/3.0/deed.en (CC BY-SA 3.0)
 * @version    1.0
 */

/**
 * Performs a query on the database
 * 
 * Returns FALSE on failure. 
 * For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return a 
 * mysqli_result object. 
 * For other successful queries mysqli_query() will return TRUE. 
 */
function getQuery($string, $debug=0) {
    global $db;
    
    if (1 === $debug)
        echo $string;

    if (2 === $debug)
        error_log($string);

    $result = $db->query($string);

    if (false === $result) {
        error_log("SQL error: " . $db->error . "\n\nOriginal query: $string\n");
    }
    return $result;
}

function good_query_list($sql, $debug=0)
{
    // this function require presence of good_query() function
    $result = good_query($sql, $debug);

    if($lst = mysql_fetch_row($result))
    {
	mysql_free_result($result);
	return $lst;
    }
    mysql_free_result($result);
    return false;
}

function good_query_assoc($sql, $debug=0)
{
    // this function require presence of good_query() function
    $result = good_query($sql, $debug);

    if($lst = mysql_fetch_assoc($result))
    {
	mysql_free_result($result);
	return $lst;
    }
    mysql_free_result($result);
    return false;
}

function good_query_value($sql, $debug=0)
{
    // this function require presence of good_query_list() function
    $lst = good_query_list($sql, $debug);
    return is_array($lst)?$lst[0]:false;
}

function good_query_table($sql, $debug=0)
{
    // this function require presence of good_query() function
    $result = good_query($sql, $debug);

    $table = array();
    if (mysql_num_rows($result) > 0)
    {
        $i = 0;
        while($table[$i] = mysql_fetch_assoc($result))
			$i++;
        unset($table[$i]);
    }
    mysql_free_result($result);
    return $table;
}


// MySQL connecting and disconnecting
function good_connect($host, $user, $pwd, $db)
{
	$link = @mysql_connect($host, $user, $pwd);
	if (!$link) {
		die('Can\'t connect to database server (check $host, $user, $pwd)');
		error_log('Can\'t connect to database server (check $host, $user, $pwd)');
	}

	$has_db = mysql_select_db($db);
	if (!$has_db)
	{
		die('Can\'t select database (check $db)');
		error_log('Can\'t select database (check $db)');
	}
}

function good_close()
{
	mysql_close();
}

// Another trivial functions

function good_row(&$result)
{
    return mysql_fetch_row($result);
}

function good_assoc(&$result)
{
    return mysql_fetch_assoc($result);
}

function good_num(&$result)
{
    return mysql_num_rows($result);
}

function good_free(&$result)
{
    return mysql_free_result($result);
}

// if you don't remember which functions
// do need MySQL resource, and which don't
// you may pass MySQL resource to all...
function good_last($notused = 0)
{
    return mysql_insert_id();
}

function good_affected($notused = 0)
{
    return mysql_affected_rows();
}



