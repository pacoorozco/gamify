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
 * @package    Index
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

// require_once realpath(dirname(__FILE__) . '/../../resources/lib/Bootstrap.class.inc');
require_once dirname(__FILE__) . '/../../resources/lib/Bootstrap.class.inc';
\Pakus\Core\Bootstrap::init(APP_BOOTSTRAP_DATABASE);

use \Slim\Slim;

Slim::registerAutoloader();

// User id from db - Global Variable
$user_id = null;

$app = new \Slim\Slim();


/**
 * User Login
 * url - /login
 * method - POST
 * params - username, password
 */
$app->post('/login', function() use ($app) {

    require_once 'include/User.class.php';

    // check for required params
    verifyRequiredParams(array('username', 'password'));

    // reading post params
    $username = $app->request()->post('username');
    $password = $app->request()->post('password');
    $response = array();

    $user = new User();

    // check for correct email and password
    if ($user->checkLogin($username, $password)) {
        // get the user by email
        $userObject = $user->getByUsername($username);

        if ($userObject != null) {
            $response["error"] = false;
            $response['name'] = $userObject['username'];
            $response['apiKey'] = $userObject['api_key'];
            $response['createdAt'] = $userObject['register_time'];
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoResponse(200, $response);
});

$app->get('/questions', 'authenticate', function() {
    global $user_id;

    require_once 'include/Question.class.php';

    $response = array();
    $questionObject = new Question();

    // fetching all user tasks
    $result = $questionObject->getAllForUserId($user_id);

    $response["error"] = false;
    $response["questions"] = array();

    // looping through result and preparing tasks array
    foreach($result as $question) {
        $tmp = array();
        $tmp['uuid'] = $question['uuid'];
        $tmp['name'] = $question['name'];
        $tmp['status'] = $question['status'];
        $tmp["createdAt"] = $question['creation_time'];
        array_push($response["questions"], $tmp);
    }

    echoResponse(200, $response);
});


$app->run();

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param array $response Json response
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {

    require_once 'include/User.class.php';

    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $userObject = new User();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$userObject->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user = $userObject->getUserId($api_key);
            if ($user != null)
                $user_id = $user;
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}