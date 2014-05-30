<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id$
 *
 */

// Check if this is a valid include
defined('IN_SCRIPT') or die('Invalid attempt');

// Reads configuration file, creates an array with values
$CONFIG = parse_ini_file('gamify.conf', true, INI_SCANNER_RAW);

// Put APP version
$CONFIG['version'] = '2.8';

// Sets DEBUG mode based on parsed configuration
$CONFIG['site']['debug'] = isset($CONFIG['site']['debug']) ? true : false;

// We need to send mails, we use Swift
require_once 'lib/swift_required.php';
require_once 'inc/database.inc.php';

/*** MAIN ***/

// Connects to DB and set a descriptor, this will be used later
$db = new \Pakus\Database\DB(
    $CONFIG['mysql']['host'],
    $CONFIG['mysql']['user'],
    $CONFIG['mysql']['passwd'],
    $CONFIG['mysql']['database']
) or die(
    'ERROR: No he pogut connectar amb la base de dades ('
    . mysqli_connect_errno() . ') ' . mysqli_connect_error()
    );

/*** FUNCTIONS ***/
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
        if ( ! isset( $CONFIG['site']['debug_receiver'] ) ) {
            return true;
        }
        $receiver = $CONFIG['site']['debug_receiver'];
    }

    // If not receiver is submitted, a message will be sent to everybody
    if ( empty($receiver) ) {
        $query = "SELECT email FROM vmembers WHERE role = 'member'";
        $result = $db->query($query);

        $receiver = array();
        while ($row = $result->fetch_assoc()) {
            if ( !empty($row['email']) ) {
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
    ->setBody($mailBody, 'text/html')
    ;

    // If we send to a one user use To:, if they're multiple Bcc:
    if ( is_array($receiver) ) {
        $message->setBcc($receiver);
    } else {
        $message->setTo($receiver);
    }

    // Send the message
    return $mailer->send($message);
}

function getNewUUID()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

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
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
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

    $query = sprintf( "SELECT username FROM members WHERE id='%d' LIMIT 1", intval($userId) );
    $result = $db->query($query);

    // Si no s'ha trobat res, retornem FALSE
    if ($result->num_rows == 0 ) return false;

    $row = $result->fetch_assoc();

    return $row['username'];
}

function getUserIdByName($username)
{
    global $db;

    $query = sprintf( "SELECT id FROM members WHERE username='%s' LIMIT 1", $username );
    $result = $db->query($query);

    // Si no s'ha trobat res, retornem FALSE
    if ($result->num_rows == 0 ) return false;

    $row = $result->fetch_assoc();

    return $row['id'];
}
