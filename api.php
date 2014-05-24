<?php


define('IN_SCRIPT',1);

require_once('inc/core.inc.php');

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

function setQuestionStatus($item, $status) {
    global $db;

    $results = array();

    // TODO - validate supplied data and error handling

    // set status value
    $query = sprintf("UPDATE questions SET status='%s' WHERE uuid='%s' LIMIT 1",
            $status,
            $item
            );
    $result = $db->query($query);

    if ( ! $result ) {
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
} // END question_set_status()
