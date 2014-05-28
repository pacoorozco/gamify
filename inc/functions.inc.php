<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: functions.inc.php 65 2014-04-21 18:09:54Z paco $
 *
 */

// Check if this is a valid include
defined('IN_SCRIPT') or die('Invalid attempt');

// Include core functions
require_once('inc/core.inc.php');

/*** MAIN ***/

// Start the session (pretty important!)
secureSessionStart();

function secureSessionStart() {
    // set a custom session name
    $sessionName = 'gamify_GoW';
    // sets the session name to the one set above.
    session_name($sessionName);
    // start the PHP session
    session_start();

    // store these values into the session so I can check on subsequent requests.
    $_SESSION['_USER_AGENT']            = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['_USER_ACCEPT']           = $_SERVER['HTTP_ACCEPT'];
    $_SESSION['_USER_ACCEPT_ENCODING']  = $_SERVER['HTTP_ACCEPT_ENCODING'];

    // Only use the first two blocks of the IP (loose IP check). Use a
    // netmask of 255.255.0.0 to get the first two blocks only.
    $_SESSION['_USER_LOOSE_IP'] = long2ip( ip2long($_SERVER['REMOTE_ADDR']) & ip2long("255.255.0.0") );
}

function secureSessionDestroy() {
    // destroy all $_SESSION variables and regenerate session_id
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

 function loginCheck() {
    global $db;

    // Run a quick check to see if we are an authenticated user or not
    // First, we set a 'is the user logged in' flag to false by default.
    $isUserLoggedIn = false;
    $query = sprintf("SELECT uuid, id, username, email, role, disabled, profile_image FROM members WHERE session_id='%s' LIMIT 1", $db->real_escape_string(session_id()));
    $result = $db->query($query);
    if ( 1 === $result->num_rows ) {
        $row = $result->fetch_assoc();
        $_SESSION['member'] = $row;
        // Si l'usuari esta deshabilitat no pot accedir
        $isUserLoggedIn = ($row['disabled'] == 1) ? false : true;
    }
    return $isUserLoggedIn;
 }

/**
 * uploadFile()
 *
 * This functions gets a $_FILES[] and move to our filesystem in a
 * secured way.
 *
 * Checks if some error has done while uploading.
 * Checks if filetype is allowed or no.
 *
 * @param   string  $file_field     Name of file upload in html form
 * @param   string  $destination    The directory where file will be moved
 * @param   array   $allowedTypes  An array containing tiletypes allowed
 *
 * This array has the form, this is the defaults one:
 * array(
 *      'jpg' => 'image/jpeg',
 *      'png' => 'image/png',
 *      'gif' => 'image/gif'
 *      );
 *
 * @return 	array   first value may be 'true' or 'false'.
 *                      second value is an string.
 *
 * On 'false' is an error message string
 * On 'true' is the generated filename.
 */
 function uploadFile($file_field, $destination, $allowedTypes = array()) {

    // Default allowed list of file to be uploaded
    if (empty($allowedTypes) || !is_array($allowedTypes)) {
        $allowedTypes = array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
            );
    }

    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (!isset($_FILES[$file_field]['error']) || is_array($_FILES[$file_field]['error']) ) {
        // Invalid parameters
        return array(false , 'Invalid parameters');
    }

    // Check $file['error'] value.
    switch ($_FILES[$file_field]['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            // No file sent
            return array(false, 'No file sent');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            // Exceeded filesize limit
            return array(false, 'Exceeded filesize limit');
        default:
            // Unknown errors
            return array(false, 'Unknown error');
    }

    // Check MIME Type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
            $finfo->file($_FILES[$file_field]['tmp_name']),
            $allowedTypes,
            true
            )) {
        // Invalid file format
        return array(false, 'Invalid file format');
    }

    // Generate a new filename (unique)
    $filename = sprintf('%s/%s.%s',
                    $destination,
                    getNewUUID(),
                    $ext);

    if (!move_uploaded_file($_FILES[$file_field]['tmp_name'], $filename)) {
        // Failed to move uploaded file
        return array(false, 'Failed to move uploaded file');
    }

    return array(true, $filename);
}

/**
  * user_has_privileges($user_id, $privilege)
  *
  * Retorna TRUE si l'usuari te el privilegi demanat
  *
  * Parameters:
  *  $user_id: Potser és un identificador d'usuari
  *  $privilege: 'member' o 'administrator'
  *
  * Returns:
  *  $result:   True si és admin
  */
 function userHasPrivileges($userId, $privilege='administrator') {
    global $db;

    $query = sprintf( "SELECT username FROM members WHERE id='%d' AND role='%s' LIMIT 1", intval($userId), $privilege );
    $result = $db->query($query);

    // Si no s'ha trobat res, retornem FALSE
    return ( $result->num_rows == 0 ) ? false : true;
}

/*** HTML CODE FUNCTIONS ***/
function printAccessDenied() {
    ?>
    <h1>Accés denegat</h1>
    <p class="lead">El teu usuari no te permissos per accedir a aquesta pàgina.</p>
    <?php
}

/**
 * Return html code for formatted messages.
 *
 * @param array $messages (contains 'type' and 'msg')
 */
function getHTMLMessages($messages) {
    $htmlCode = array();

    // defines which css classes we'll use to every message type
    $cssClasses = array(
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'info' => 'alert alert-info',
        'warning' => 'alert alert-warning'
        );

    foreach ($messages as $msg) {
        $htmlCode[] = '<div class="alert alert-'. $cssClasses[$msg['type']] .' alert-dismissable">';
        $htmlCode[] = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        $htmlCode[] = $msg['msg'];
        $htmlCode[] = '</div>';

    }

    // Use PHP_EOL constant for insert \n after every line
    return implode(PHP_EOL, $htmlCode);
}

function getHTMLSelectOptions( $available_options, $selected_option = '' ) {
    $htmlCode = array();
    foreach ( $available_options as $key => $value) {
        if ( $key == $selected_option ) {
            $htmlCode[] = '<option value="' . $key . '" selected="selected">' . $value . '</option>';
        } else {
            $htmlCode[] = '<option value="' . $key . '">' . $value . '</option>';
        }
    }
    return implode($htmlCode, PHP_EOL);
}

function getHTMLDataTable($id) {
    $htmlCode = <<<END
        <script>
            head.ready(function() {
                $('$id').dataTable( {
                    "bSortClasses": false,
                    "aoColumnDefs": [
                        { "bSortable": false, "aTargets": [ -1 ] }
                    ],
                    "oLanguage": {
                        "sProcessing": "Processant...",
                        "sLengthMenu": "Mostra _MENU_ registres",
                        "sZeroRecords": "No s'han trobat registres.",
                        "sInfo": "Mostrant de _START_ a _END_ de _TOTAL_ registres",
                        "sInfoEmpty": "Mostrant de 0 a 0 de 0 registres",
                        "sInfoFiltered": "(filtrat de _MAX_ registres totals)",
                        "sInfoPostFix": "",
                        "sSearch": "Filtrar:",
                        "sUrl": "",
                        "oPaginate": {
                            "sFirst": "Primer",
                            "sNext": "",
                            "sPrevious": "",
                            "sLast": "&Uacute;ltim"
                        }
                    }
                } );
            } );
        </script>
END;
    return $htmlCode;
}

function getPendingQuizs( $user_id ) {
    global $db;

    $query = sprintf( "SELECT count(*) as pending FROM questions AS q WHERE q.status='active' AND id NOT IN (SELECT id_question FROM members_questions WHERE id_member='%d')", intval($user_id) );
    $result = $db->query($query);
    $row = $result->fetch_assoc();

    return ( $row['pending'] > 0 ) ? $row['pending'] : '';
}

function getRowFromQuery($query) {
    global $db;
    
    $result = $db->query($query);
    if ( 0 == $result->num_rows ) {
        return false;
    }
    return $result->fetch_assoc();
}

