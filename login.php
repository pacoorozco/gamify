<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: login.php 47 2014-04-12 06:03:33Z paco $
 *
 */

define('IN_SCRIPT', 1);
require_once('inc/functions.inc.php');

// Que hem de fer?
$action = getREQUESTVar('a');

// first we use some action that user header(), none can be echoed before
switch ($action) {
    case 'logout':
        doLogout();
        break;

    case 'login':
        $username = getPOSTVar('username');
        $password = getPOSTVar('password');
        $errors = array();

        if ( true === doLogin($username, $password) ) {
            // go to previous referrer, if exists
            $nav = getPOSTVar('nav');
            $nav = (!empty($nav)) ? $nav : 'index.php';
            header('Location: ' . urldecode($nav));
            exit();
        } else {
            $errors[] = array('type' => "error", 'msg' => "Usuari o contrasenya incorrectes.");
        }
        break;

    default:
        if (true === loginCheck()) {
            // ja esta autenticat
            header('Location: index.php');
            exit();
        }
}

// now rest of actions
require_once('inc/header.inc.php');

switch ($action) {
    case 'login':
        printLoginForm($username, $errors);
        break;

    case 'register':
        printRegisterForm();
        break;

    case 'do_register':
        $data = array();
        $data['username'] = getPOSTVar('username');
        $data['password'] = getPOSTVar('password');
        $data['email'] = getPOSTVar('email');
        doRegister($data);
        break;

    case 'logout':
    default:
        printLoginForm();
}

require_once('inc/footer.inc.php');
exit();

/*** FUNCTIONS ***/

function printLoginForm( $username = '', $missatges = array() ) {
    global $CONFIG;

    // get after login url if exists
    $nav = getPOSTVar('nav');
    $nav = (!empty($nav)) ? $nav : $_SESSION['nav'];
    unset($_SESSION['nav']);
?>
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">Accedir</div>
                        <div style="float:right; position: relative; top:-10px"><a href="http://www.upcnet.es/CanviContrasenyaUPC" target="_blank">Has oblidat la contrasenya?</a></div>
                    </div>

                    <div style="padding-top:30px" class="panel-body" >

                        <p><?php echo getHTMLMessages($missatges); ?></p>

                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <?php
                                $usertext = 'usuari';
                                $logintext = 'Accedir';

                                if ($CONFIG['authentication']['LDAP']) {
                                    $usertext = 'usuari LDAP';
                                    $logintext = 'Accedir amb LDAP';
                                }
                                ?>
                                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="<?= $usertext; ?>" required>
                            </div>

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="contrasenya" required>
                            </div>

                            <div style="margin-top:10px" class="form-group">
                                <div class="col-md-12">
                                    <input type="hidden" id="a" name="a" value="login">
                                    <input type="hidden" name="nav" value="<?php echo $nav; ?>">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-log-in"></span> <?= $logintext; ?></button>
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

function doLogout() {
    global $db;

    // updates members to put session_id to NULL
    $query = sprintf( "UPDATE members SET session_id=NULL WHERE id='%d' LIMIT 1", intval($_SESSION['member']['id']) );
    $db->query($query);

    secureSessionDestroy();
} // END do_logout()

function doLogin($username, $password) {
    global $CONFIG, $db;

    // Primer fixem a FALS la resposta d'aquesta funcio
    $userIsMember = false;

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
            if ( getLDAPAuth( $usuari['username'], $password,
                    $CONFIG['LDAP']['host'], $CONFIG['LDAP']['basedn'], $CONFIG['LDAP']['filter'] ) ) {
                // Usuari validat i es member
                $userIsMember = true;
            }
            break;
        default:
            // we will use LOCAL authentication
            if (md5($password) == $usuari['password']) {
                // Usuari validat i es membre
                $userIsMember = true;
            }
    }

    if ($userIsMember) {
        // Usuari validat, actualitzem session_id
        $query = sprintf("UPDATE members SET session_id='%s', last_access='%s' WHERE id='%d' LIMIT 1",
                          $db->real_escape_string(session_id()), $db->real_escape_string(time()), intval($usuari['id']));
        $db->query($query);
    }

    return $userIsMember;
} // END do_login()

function printRegisterForm( $missatges = array() ) {
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
                            <p><?php echo getHTMLMessages($missatges); ?></p>

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

function doRegister( $data = array() ) {
    global $db, $CONFIG;

    $missatges = array();

    // check supplied data
    if ( ! filter_var($data['email'], FILTER_VALIDATE_EMAIL) ) {
        $missatges[] = array('type' => "error", 'msg' => "L'adreça electrònica no és correcta.");
        printRegisterForm($missatges);
    }

    // check if user exists
    if ( getUserExists($data['username']) ) {
        $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ja existeix al sistema.");
        printLoginForm($data['username'], $missatges);
        return false;
    }

    // check autoregistration
    switch ($CONFIG['authentication']['type']) {
        case 'LDAP':
            if ( false === getLDAPAuth( $data['username'], $data['password'] ) ) {
                // User has not been validated.
                $missatges[] = array('type' => "error", 'msg' => "No hem pogut comprovar les credencials al LDAP. Revisa-les si us plau");
                printRegisterForm($missatges);
                return false;
            }
            break;

        default:
            die("TODO: Registration for non LDAP users not implemented, yet!");
    }

    // User successfully validated.
    $query = sprintf("INSERT INTO members SET uuid='%s', username='%s', email='%s'",
            $db->real_escape_string(getNewUUID()),
            $db->real_escape_string($data['username']),
            $db->real_escape_string($data['email'])
            );
    $db->query($query);

    // Get new user_id or 0 on error.
    $userId = $db->insert_id;

    if ( 0 === $userId ) {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear l'usuari.");
        printRegisterForm($missatges);
        return false;
    } else {
        // ACTION: Benvinguda
        doSilentAction($userId, 8);
        
        $missatges[] = array('type' => "success", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' creat correctament.");
        printLoginForm($data['username'], $missatges);
        return true;
    }
}

function getLDAPAuth($username, $password) {
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

