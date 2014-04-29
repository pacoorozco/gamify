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
$CONFIG['version'] = '2.7';

// Sets DEBUG mode based on parsed configuration
$CONFIG['site']['debug'] = isset($CONFIG['site']['debug']) ? true : false;

// We need to send mails, we use Swift
require_once('lib/swift_required.php');

/*** MAIN ***/

// Connects to DB and set a descriptor, this will be used later
$db = mysqli_connect( $CONFIG['mysql']['host'], $CONFIG['mysql']['user'],
            $CONFIG['mysql']['passwd'], $CONFIG['mysql']['database'] )
        or die( 'ERROR: No he pogut connectar amb la base de dades (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() );

/*** FUNCTIONS ***/
function pakus_GET($in, $default = '') {
        return isset($_GET[$in]) ? pakus_input($_GET[$in]) : $default;
} // END pakus_GET()

function pakus_POST($in, $default = '') {
        return isset($_POST[$in]) ? pakus_input($_POST[$in]) : $default;
} // END pakus_POST()

function pakus_REQUEST($in, $default = false) {
        return isset($_POST[$in]) ? pakus_POST($in) : ( isset($_GET[$in]) ? pakus_GET($in) : $default );
} // END pakus_REQUEST()

function pakus_input($in, $force_slashes=0, $max_length=0) {

    // If $in is array we process every value
    if (is_array($in)) {
        foreach ($in as &$element) {
            $element = pakus_input($element, $force_slashes=0, $max_length=0);
        }
        unset ($element);
    } else {
        // Strip whitespace
        $in = preg_replace('/&amp;(\#[0-9]+;)/', '&$1', trim($in));

        // Is value length 0 chars?
        if (strlen($in) == 0) return $in;

        // Add slashes
        if ($force_slashes) $in = addslashes($in);

        // Check length
        if ($max_length) $in = substr($in, 0, $max_length);
    }
    // Return processed value
    return $in;
} // END pakus_input()

 function time_elapsed_string($datetime, $full = false) {
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

    if (!$full) $string = array_slice($string, 0, 1);

    return $string ? 'fa '. implode(', ', $string) : 'ara mateix';
} // END time_elapsed_string()

function send_message( $subject, $missatge, $receiver = '' ) {
    global $db, $CONFIG;
    
    // If DEBUG mode is on, only send messages to 'debug_receiver'
    if ( $CONFIG['site']['debug'] ) {
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
       
    $service_url = $CONFIG['site']['base_url'];
    $html_message = $missatge;
    
    $mail_body = <<<SEND_MAIL
<html>
<body style="border:1px solid #222222; margin-left:auto; margin-right:auto; width:70%;">
<div style="background-color:#222222; height:60px; padding-bottom: 10px; text-align:center;">
    <img src="$service_url/images/logo-gow-long.png">
</div>
<div style="padding: 10px;">
$html_message
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
    ->setBody($mail_body, 'text/html')
    ;
    
    // If we send to a one user use To:, if they're multiple Bcc:
    if ( is_array($receiver) ) {
        $message->setBcc($receiver);
    } else {
        $message->setTo($receiver);
    }
    
    // Send the message
    return $mailer->send($message);
} // END send_mail()

function generate_uuid() {
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
 function user_exists($user) {
    if (is_int($user)) {
        return (get_username($user) === false ) ? false : true;
    } else {
        return (get_member_id($user) === false ) ? false : true;
    }
} // END user_exists()

function get_username ($user_id) {
    global $db;

    $query = sprintf( "SELECT username FROM members WHERE id='%d' LIMIT 1", intval($user_id) );
    $result = $db->query($query);
    
    // Si no s'ha trobat res, retornem FALSE
    if ($result->num_rows == 0 ) return false;
    
    $row = $result->fetch_assoc();
    return $row['username'];
} // END get_username()

function get_member_id ($username) {
    global $db;

    $query = sprintf( "SELECT id FROM members WHERE username='%s' LIMIT 1", $username );
    $result = $db->query($query);
    
    // Si no s'ha trobat res, retornem FALSE
    if ($result->num_rows == 0 ) return false;
    
    $row = $result->fetch_assoc();
    return $row['id'];
} // END get_member_id()

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
 function user_has_privileges($user_id, $privilege='administrator') {
    global $db;

    $query = sprintf( "SELECT username FROM members WHERE id='%d' AND role='%s' LIMIT 1", intval($user_id), $privilege );
    $result = $db->query($query);
    
    // Si no s'ha trobat res, retornem FALSE
    return ( $result->num_rows == 0 ) ? false : true;
} // END user_is_admin()
