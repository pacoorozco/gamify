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
 * @package    Api
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

define('IN_SCRIPT',1);

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_FULL);

$method = getGETVar('method');
$format = getGETVar('format');

$data = array();
$response = array();

switch ($method) {
    case 'question':
        $action = getGETVar('action');

        switch ($action) {
            case 'enable':
                $item = getGETVar('item');
                $response = setQuestionStatus($item, 'active');
                break;
            case 'disable':
                $item = getGETVar('item');
                $response = setQuestionStatus($item, 'inactive');
                break;
        }
        break;
}

switch ($format) {
    case 'php':
        /* Setting up PHP headers */
        header("content-type: text/php charset=utf-8");

        /* Printing the PHP serialized Object*/
        echo serialize($response);
        break;

    case 'json':
    default:
        /* Setting up JSON headers */
        header("content-type: text/json charset=utf-8");

        /* Printing the JSON Object */
        echo json_encode($response);
}

/*** FUNCTIONS ***/

function setQuestionStatus($item, $status)
{
    global $db;

    $results = array();

    // TODO - validate supplied data and error handling

    // set status value
    $query = sprintf("UPDATE questions SET status='%s' WHERE uuid='%s' LIMIT 1",
            $status,
            $item
            );
    $result = $db->query($query);

    if (! $result) {
        // Something wrong happens
        $results = array(
            'head' => array(
                'status' => '0',
                'error_number' => '500',
                'error_message' => 'Temporary Error. Our server might be down, please try again later.'
                ),
            'body' => array()
            );

        return $results;
    }

    // set status value
    $query = sprintf("SELECT status FROM questions WHERE uuid='%s' LIMIT 1",
            $item
            );
    $result = $db->query($query);
    $row = $result->fetch_assoc();

    $results = array(
        'body' => array(
            'uuid' => $item,
            'status' => $row['status']
            )
        );

    return $results;
}
