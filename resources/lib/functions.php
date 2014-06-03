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
 * @package    Functions
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

/**
 * Checks if a user is logged in based on its $_SESSION.
 *
 * @return boolean Returns TRUE if users is logged, FALSE otherways.
 */
function checkLoggedIn()
{
    global $db;

    // Check if all session variables are set
    if (isset(
        $_SESSION['member']['uuid'],
        $_SESSION['member']['username'],
        $_SESSION['member']['login_string']
    )) {
        // Get the user's password from database, only for enabled users
        $userPassword = $db->getOne(
            sprintf(
                "SELECT `password` FROM `members` "
                . "WHERE `uuid`='%s' AND `disabled`='0' LIMIT 1",
                $db->qstr($_SESSION['member']['uuid'])
            )
        );

        if (empty($userPassword)) {
            // User's doesn't exists or is disabled, so not logged
            return false;
        }

        $loginCheck = hash('sha512', $userPassword . $_SERVER['HTTP_USER_AGENT']);
        if ($loginCheck == $_SESSION['member']['login_string']) {
            // User is logged in!
            return true;
        }
    }
    // User is not logged in
    return false;
}

function redirect($url, $includeCurrentURL = false) {
    if($includeCurrentURL) {
            // save referrer to $_SESSION['nav'] for redirect later
            $_SESSION['nav'] = urlencode($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']);
	}
	header('Location: ' . $url);
	exit();
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
function uploadFile($file_field, $destination, $allowedTypes = array())
{

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
    if (!isset($_FILES[$file_field]['error']) || is_array($_FILES[$file_field]['error'])) {
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
    $filename = sprintf(
        '%s/%s.%s',
        $destination,
        getNewUUID(),
        $ext
    );

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
function userHasPrivileges($userId, $privilege = 'administrator')
{
    global $db;

    $query = sprintf("SELECT username FROM members WHERE id='%d' AND role='%s' LIMIT 1", intval($userId), $privilege);
    $result = $db->query($query);

    // Si no s'ha trobat res, retornem FALSE
    return ( $result->num_rows == 0 ) ? false : true;
}

/*** HTML CODE FUNCTIONS ***/
function printAccessDenied()
{
    $htmlCode = array();
    $htmlCode[] = '<h1>Accés denegat</h1>';
    $htmlCode[] = '<p class="lead">El teu usuari no te permissos per accedir a aquesta pàgina.</p>';
    echo implode(PHP_EOL, $htmlCode);
}

/**
 * Return html code for formatted messages.
 *
 * @param array $messages (contains 'type' and 'msg')
 */
function getHTMLMessages($messages)
{
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

function getHTMLSelectOptions($available_options, $selected_option = '')
{
    $htmlCode = array();
    foreach ($available_options as $key => $value) {
        if ($key == $selected_option) {
            $htmlCode[] = '<option value="' . $key . '" selected="selected">' . $value . '</option>';
        } else {
            $htmlCode[] = '<option value="' . $key . '">' . $value . '</option>';
        }
    }

    return implode($htmlCode, PHP_EOL);
}

function getHTMLDataTable($id)
{
    $htmlCode = <<<END
        <script>
            head.ready(function () {
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

function getPendingQuizs($userId)
{
    global $db;

    $pending = $db->getOne(
        sprintf(
            "SELECT count(*) AS pending FROM questions "
            . "WHERE status='active' AND "
            . "id NOT IN (SELECT id_question FROM members_questions WHERE id_member='%d')",
            $userId
        )
    );

    return ( $pending > 0 ) ? $pending : '';
}

function getGETVar($in, $default = '')
{
        return isset($_GET[$in]) ? getSanitizedInput($_GET[$in]) : $default;
}

function getPOSTVar($in, $default = '')
{
        return isset($_POST[$in]) ? getSanitizedInput($_POST[$in]) : $default;
}

function getREQUESTVar($in, $default = false)
{
        return isset($_POST[$in]) ? getPOSTVar($in) : ( isset($_GET[$in]) ? getGETVar($in) : $default );
}

function getSanitizedInput($in, $force_slashes = 0, $maxLength = 0)
{

    // If $in is array we process every value
    if (is_array($in)) {
        foreach ($in as &$element) {
            $element = getSanitizedInput($element, $force_slashes = 0, $maxLength = 0);
        }
        unset ($element);
    } else {
        // Strip whitespace
        $in = preg_replace('/&amp;(\#[0-9]+;)/', '&$1', trim($in));

        // Is value length 0 chars?
        if (strlen($in) == 0) {
            return $in;
        }

        // Add slashes
        if ($force_slashes) {
            $in = addslashes($in);
        }

        // Check length
        if ($maxLength) {
            $in = substr($in, 0, $maxLength);
        }
    }
    // Return processed value
    return $in;
}

function getElapsedTimeString($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => array('any', 'anys'),
        'm' => array('mes', 'mesos'),
        'w' => array('setmana', 'setmanes'),
        'd' => array('dia', 'dies'),
        'h' => array('hora', 'hores'),
        'i' => array('minut', 'minuts'),
        's' => array('segon', 'segons')
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . ($diff->$k > 1 ? $v[1] : $v[0]);
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? 'fa '. implode(', ', $string) : 'ara mateix';
}

function sendMessage($subject, $missatge, $receiver = '')
{
    global $db, $CONFIG;

    // If DEBUG mode is on, only send messages to 'debug_receiver'
    if ($CONFIG['site']['debug']) {
        if (!isset($CONFIG['site']['debug_receiver'])) {
            return true;
        }
        $receiver = $CONFIG['site']['debug_receiver'];
    }

    // If not receiver is submitted, a message will be sent to everybody
    if (empty($receiver)) {
        $query = "SELECT email FROM vmembers WHERE role = 'member'";
        $result = $db->query($query);

        $receiver = array();
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['email'])) {
                $receiver[] = $row['email'];
            }
        }
    }

    $serviceURL = $CONFIG['site']['base_url'];
    $htmlCode = $missatge;

    $mailBody = <<<SEND_MAIL
<html>
<body style="border:1px solid #222222; margin-left:auto; margin-right:auto; width:70%;">
<div style="background-color:#222222; height:60px; padding-bottom: 10px; text-align:center;">
    <img src="$serviceURL/images/logo-gow-long.png">
</div>
<div style="padding: 10px;">
$htmlCode
</div>
</body>
</html>
SEND_MAIL;

    // Create the Transport
    $transport = Swift_SmtpTransport::newInstance('localhost', 25);

    // Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);

    // Create the message
    $message = Swift_Message::newInstance()

    // Give the message a subject
    ->setSubject($subject)

    // Set the From address with an associative array
    ->setFrom(array('noreply@upcnet.es' => 'GoW! - Game of Work'))

    // Give it a body
    ->setBody($mailBody, 'text/html');

    // If we send to a one user use To:, if they're multiple Bcc:
    if (is_array($receiver)) {
        $message->setBcc($receiver);
    } else {
        $message->setTo($receiver);
    }

    // Send the message
    return $mailer->send($message);
}

function getNewUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,
        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}
/**
  * user_exists($user)
  *
  * Retorna TRUE si l'usuari existeix
  *
  * Parameters:
  *  $user: Potser un identificador d'usuari o un nom d'usuari
  *
  * Returns:
  *  $result:   True si existeix, false en cas contrari
  */
function getUserExists($user)
{
    if (is_int($user)) {
        return (getUserNameById($user) === false ) ? false : true;
    } else {
        return (getUserIdByName($user) === false ) ? false : true;
    }
}

function getUserNameById($userId)
{
    global $db;

    return $db->getOne(
        sprintf("SELECT username FROM members WHERE id='%d' LIMIT 1", intval($userId))
    );
}

function getUserIdByName($username)
{
    global $db;

    return $db->getOne(
        sprintf("SELECT id FROM members WHERE username='%s' LIMIT 1", $username)
    );
}
