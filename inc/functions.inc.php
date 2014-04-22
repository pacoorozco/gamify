<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: functions.inc.php 65 2014-04-21 18:09:54Z paco $
 *
 */

// Check if this is a valid include
defined('IN_SCRIPT') or die('Invalid attempt');

// Reads configuration file, creates an array with values
$CONFIG = parse_ini_file('gamify.conf', true, INI_SCANNER_RAW);

// Put APP version
$CONFIG['version'] = '2.5';

// Sets DEBUG mode based on parsed configuration
$CONFIG['site']['debug'] = isset($CONFIG['site']['debug']) ? true : false;

/*** MAIN ***/

// Start the session (pretty important!)
secure_session_start();

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
        $in = preg_replace('/&amp;(\#[0-9]+;)/', '&$1', htmlspecialchars( trim($in) ));

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

function secure_session_start() {
    // set a custom session name
    $session_name = 'gamify_GoW';
    // sets the session name to the one set above.
    session_name($session_name);
    // start the PHP session 
    session_start();
    
    // check these values into session, prevent session hijacking
    // 20140413 (Paco) - If enable this Live Search doesn't work
    /*
    if ( isset($_SESSION['_USER_LOOSE_IP'])
            && isset($_SESSION['_USER_AGENT'])
            && isset($_SESSION['_USER_ACCEPT'])
            && isset($_SESSION['_USER_ACCEPT_ENCODING']) ) {
        
        if ( $_SESSION['_USER_LOOSE_IP'] != long2ip( ip2long($_SERVER['REMOTE_ADDR'] ) & ip2long("255.255.0.0") )
            || $_SESSION['_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']
            || $_SESSION['_USER_ACCEPT'] != $_SERVER['HTTP_ACCEPT']
            || $_SESSION['_USER_ACCEPT_ENCODING'] != $_SERVER['HTTP_ACCEPT_ENCODING']) {
            secure_session_destroy();
        }
    } else {
        secure_session_destroy();
    }
    */
    
    // store these values into the session so I can check on subsequent requests.
    $_SESSION['_USER_AGENT']            = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['_USER_ACCEPT']           = $_SERVER['HTTP_ACCEPT'];
    $_SESSION['_USER_ACCEPT_ENCODING']  = $_SERVER['HTTP_ACCEPT_ENCODING'];

    // Only use the first two blocks of the IP (loose IP check). Use a
    // netmask of 255.255.0.0 to get the first two blocks only.
    $_SESSION['_USER_LOOSE_IP'] = long2ip( ip2long($_SERVER['REMOTE_ADDR']) & ip2long("255.255.0.0") );   
} // END secure_session_start();

function secure_session_destroy() {
    // destroy all $_SESSION variables and regenerate session_id
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
} // END do_logout()

 function login_check() {
    global $db;

    // Run a quick check to see if we are an authenticated user or not
    // First, we set a 'is the user logged in' flag to false by default.
    $is_user_logged_in = false;
    $query = sprintf("SELECT id, username, email, role, disabled FROM members WHERE session_id='%s' LIMIT 1", $db->real_escape_string(session_id()));
    $result = $db->query($query);
    if ( 1 === $result->num_rows ) {
        $row = $result->fetch_assoc();
        $_SESSION['member'] = $row;
        // Si l'usuari esta deshabilitat no pot accedir
        $is_user_logged_in = ($row['disabled'] == 1) ? false : true;
    }
    return $is_user_logged_in;
 }
 
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

/*** HTML CODE FUNCTIONS ***/
function print_access_denied() {
    ?>
    <h1>Accés denegat</h1>
    <p class="lead">El teu usuari no te permissos per accedir a aquesta pàgina.</p>
    <?php
} // END print_access_denied()

/**
 * Return html code for formatted messages.
 *
 * @param array $messages (contains 'type' and 'msg')
 */
function get_html_messages($messages) {
    $html_code = array();
    
    // defines which css classes we'll use to every message type
    $css_classes = array( 
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'info' => 'alert alert-info',
        'warning' => 'alert alert-warning'
        );
    
    foreach ($messages as $msg) {
        // $html_code[] = '<p class="' . $css_classes[$msg['type']] . '">' . $msg['msg'] . '</p>';
        $html_code[] = '<div class="alert alert-'. $css_classes[$msg['type']] .' alert-dismissable">';
        $html_code[] = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        $html_code[] = $msg['msg'];
        $html_code[] = '</div>';

    }

    // Use PHP_EOL constant for insert \n after every line
    return implode(PHP_EOL, $html_code);
} // END get_html_messages()

function get_html_select_options( $available_options, $selected_option = '' ) {
    $html_code = array();
    foreach ( $available_options as $key => $value) {
        if ( $key == $selected_option ) {
            $html_code[] = '<option value="' . $key . '" selected="selected">' . $value . '</option>';
        } else {
            $html_code[] = '<option value="' . $key . '">' . $value . '</option>';
        }
    }
    return implode($html_code, PHP_EOL);
} // END get_html_select_options

function get_pending_quizs( $user_id ) {
    global $db;
    
    $query = sprintf( "SELECT count(*) as pending FROM questions AS q WHERE q.status='active' AND id NOT IN (SELECT id_question FROM members_questions WHERE id_member='%d')", intval($user_id) );
    $result = $db->query($query);   
    $row = $result->fetch_assoc();
    
    return ( $row['pending'] > 0 ) ? $row['pending'] : '';
} // END get_pending_quizs()
?>