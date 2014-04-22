<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: login.php 47 2014-04-12 06:03:33Z paco $
 *
 */

define('IN_SCRIPT', 1);
require_once('inc/functions.inc.php');

// Que hem de fer?
$action = pakus_REQUEST('a');

// first we use some action that user header(), none can be echoed before
switch ($action) {   
    case 'logout':
        do_logout();
        break;

    case 'login':
        $username = pakus_POST('username');
        $password = pakus_POST('password');
        $errors = array();

        if ( true === do_login($username, $password) ) {
            header('Location: index.php');
            exit();
        } else {
            $errors[] = array('type' => "error", 'msg' => "Usuari o contrasenya incorrectes.");
        }
        break;
}

// now rest of actions
require_once('inc/header.inc.php');

switch ($action) {
    case 'login':
        print_login_form($username, $errors);
        break;

    case 'register':
        print_register_form();
        break;
    
    case 'do_register':
        $data = array();
        $data['username'] = pakus_POST('username');
        $data['password'] = pakus_POST('password');
        $data['email'] = pakus_POST('email');
        do_register($data);
        break;
    
    case 'logout':
    default:
        print_login_form();
}

require_once('inc/footer.inc.php');
exit();   

/*** FUNCTIONS ***/

function print_login_form( $username = '', $missatges = array() ) {
?>
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                                     
            <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">Accedir</div>
                        <div style="float:right; position: relative; top:-10px"><a href="http://www.upcnet.es/CanviContrasenyaUPC" target="_blank">Has oblidat la contrasenya?</a></div>
                    </div>     

                    <div style="padding-top:30px" class="panel-body" >

                        <p><?php echo get_html_messages($missatges); ?></p>
                            
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">
                                    
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="usuari" required>  
                            </div>
                                
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="contrasenya" required>
                            </div>
                            
                            <div style="margin-top:10px" class="form-group">
                                <div class="col-md-12">
                                    <input type="hidden" id="a" name="a" value="login">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-log-in"></span> &nbsp; Accedir</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-md-12 control">
                                    <div style="border-top: 1px solid#888; padding-top:15px;">
                                        No has accedit mai?
                                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=register"> 
                                            Registra't ara!
                                        </a>
                                    </div>
                                </div>
                            </div>    
                        </form>     
                    </div>                     
            </div>  
        </div>

<?php
} // END print_login_form()

function do_logout() {
    global $db;
    
    // updates members to put session_id to NULL
    $query = sprintf( "UPDATE members SET session_id=NULL WHERE id='%d' LIMIT 1", intval($_SESSION['member']['id']) );
    $db->query($query);
    
    secure_session_destroy();
} // END do_logout()

function do_login($username, $password) {
    global $CONFIG, $db;

    // Primer fixem a FALS la resposta d'aquesta funcio
    $user_is_member = false;

    // Comprovem que l'usuari consti com a membre
    $query = sprintf("SELECT id, username, password, disabled FROM members WHERE username='%s' LIMIT 1", $db->real_escape_string($username));
    $result = $db->query($query);

    if ( 1 != $result->num_rows )
        return false;

    // L'usuari es correcte ara cal verificar la contrasenya
    $usuari = $result->fetch_assoc();

    // Si esta deshabilitat tampoc poc accedir
    if ( 1 == $usuari['disabled'] )
        return false;

    // we implement several auth types
    switch ( $CONFIG['authentication']['type'] ) {
        case 'LDAP':
            // we will use LDAP authentication
            if ( LDAP_auth( $usuari['username'], $password, 
                    $CONFIG['LDAP']['host'], $CONFIG['LDAP']['basedn'], $CONFIG['LDAP']['filter'] ) ) {
                // Usuari validat i es member
                $user_is_member = true;
            }
            break;
        default:
            // we will use LOCAL authentication
            if (md5($password) == $usuari['password']) {
                // Usuari validat i es membre
                $user_is_member = true;
            }
    }

    if ($user_is_member) {
        // Usuari validat, actualitzem session_id
        $query = sprintf("UPDATE members SET session_id='%s', last_access='%s' WHERE id='%d' LIMIT 1", 
                          $db->real_escape_string(session_id()), $db->real_escape_string(time()), intval($usuari['id']));
        $db->query($query);
    }

    return $user_is_member;
} // END do_login()

function print_register_form( $missatges = array() ) {
?>
        <div id="signupbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Registre</div>
                    <div style="float:right; position: relative; top:-10px">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Ja tens usuari? Accedeix!</a>
                    </div>
                </div>  
                <div class="panel-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">                       
                            <p><?php echo get_html_messages($missatges); ?></p>
                                               
                        <div class="form-group">  
                            <label for="username" class="col-md-3 control-label">Usuari</label>
                            <div class="col-md-9">
                                <input type="text" name="username" id="username" class="form-control" placeholder="usuari" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="col-md-3 control-label">Contrasenya</label>
                            <div class="col-md-9">
                                <input type="password" id="password" class="form-control" name="password" placeholder="contrasenya" required>
                            </div>
                        </div>
                            
                        <div class="form-group">  
                            <label for="email" class="col-md-3 control-label">Adreça</label>
                            <div class="col-md-9">
                                <input type="text" name="email" id="email" class="form-control" placeholder="adreça electrónica" required>
                            </div>
                        </div>                            
                        
                        <div class="form-group">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" id="a" name="a" value="do_register">
                                <button type="submit" class="btn btn-info"><span class="glyphicon glyphicon-hand-right"></span> &nbsp Registrar</button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>   
         </div>  
<?php
} // END print_register_form()

function do_register( $data = array() ) {
    global $db, $CONFIG;
    
    $missatges = array();
    
    // check supplied data
    if ( ! filter_var($data['email'], FILTER_VALIDATE_EMAIL) ) {
        $missatges[] = array('type' => "error", 'msg' => "L'adreça electrònica no és correcta.");
        print_register_form($missatges);
    }
    
    // check if user exists
    if ( user_exists($data['username']) ) {
        $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ja existeix al sistema.");
        print_login_form($data['username'], $missatges);
        return false;
    }
    
    // check autoregistration
    switch ($CONFIG['authentication']['type']) {
        case 'LDAP':
            if ( false === LDAP_auth( $data['username'], $data['password'] ) ) {
                // User has not been validated.
                $missatges[] = array('type' => "error", 'msg' => "No hem pogut comprovar les credencials al LDAP. Revisa-les si us plau");
                print_register_form($missatges);
                return false;
            }
            break;
            
        default:
            die("TODO: Registration for non LDAP users not implemented, yet!");
    }
    
    // User successfully validated.
    $query = sprintf("INSERT INTO members SET username='%s', email='%s'", 
                     $db->real_escape_string($data['username']), $db->real_escape_string($data['email']) );
    $db->query($query);

    // Get new user_id or 0 on error.
    $user_id = $db->insert_id;
    
    if ( 0 === $user_id ) {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear l'usuari.");
        print_register_form($missatges);
        return false;
    } else {
        $missatges[] = array('type' => "success", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' creat correctament.");
        print_login_form($data['username'], $missatges);
        return true;
    } 
} // END do_register()

function LDAP_auth($username, $password) {
    global $CONFIG;

    if ( empty($username) ) return false;
   
    // creates filter and DN
    $filter = sprintf("(&(cn=%s)%s)", $username, $CONFIG['LDAP']['filter']);
    $dn = sprintf("cn=%s,%s", $username, $CONFIG['LDAP']['basedn']);
    
    // connect to ldaps server
    $connect = ldap_connect($CONFIG['LDAP']['host']);
    if (false === $connect) {
        die("ERROR: Could not connect to LDAP {$CONFIG['LDAP']['host']}!");
    }

    ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

    // binding to ldap server with username and password supplied
    $bind = @ldap_bind($connect, $dn, $password);

    if (false === $bind) return false;

    // fitth is 'attrsonly', sixth is how many results. See ldap_search on php.net
    $sr = ldap_search($connect, $CONFIG['LDAP']['basedn'], $filter, array('mail', 'sn'), 0, 1);   
    return ( false === ldap_get_entries($connect, $sr) ) ? false : true;
} // END LDAP_auth()
?>