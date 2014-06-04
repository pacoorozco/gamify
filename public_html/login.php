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
 * @package    Login
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_FULL);

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

        if (doLogin($username, $password)) {
            // go to previous referrer, if exists
            $nav = getPOSTVar('nav');
            // $nav is always urlencode()
            $nav = (!empty($nav)) ? urldecode($nav) : 'index.php';
            redirect($nav);
        } else {
            $errors[] = array(
                'type' => "error",
                'msg' => "Usuari o contrasenya incorrectes."
            );
        }
        break;
    default:
        if (userIsLoggedIn()) {
            // User is logged, so redirect to index.php
            redirect('index.php');
        }
}

// now rest of actions
require_once TEMPLATES_PATH . '/tpl_header.inc';

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

require_once TEMPLATES_PATH . '/tpl_footer.inc';
exit();

/*** FUNCTIONS ***/

function printLoginForm($username = '', $missatges = array())
{
    global $CONFIG;

    // get url to redirect after login
    if (isset($_SESSION['nav'])) {
        $nav = $_SESSION['nav'];
        unset($_SESSION['nav']);
    } else {
        $nav = getPOSTVar('nav');
    }
    ?>
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">Accedir</div>
                        <div style="float:right; position: relative; top:-10px"><a href="http://www.upcnet.es/CanviContrasenyaUPC" target="_blank">Has oblidat la contrasenya?</a></div>
                    </div>

                    <div style="padding-top:30px" class="panel-body" >

                        <p><?php echo getHTMLMessages($missatges); ?></p>

                        <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">
    <?php
    $usertext = 'usuari';
    $logintext = 'Accedir';

    if ('LDAP' == $CONFIG['authentication']['type']) {
        $usertext = 'usuari LDAP';
        $logintext = 'Accedir amb LDAP';
    }
    ?>
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input type="text" name="username" class="form-control" value="<?= $username; ?>" placeholder="<?= $usertext; ?>" required>
                            </div>

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="contrasenya" required>
                            </div>

                            <div style="margin-top:10px" class="form-group">
                                <div class="col-md-12">
                                    <input type="hidden" id="a" name="a" value="login">
                                    <input type="hidden" name="nav" value="<?= $nav; ?>">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-log-in"></span> <?= $logintext; ?></button>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12 control">
                                    <div style="border-top: 1px solid#888; padding-top:15px;">
                                        No has accedit mai?
                                        <a href="<?= $_SERVER['PHP_SELF']; ?>?a=register">
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
}

function doLogout()
{
    // destroy $_SESSION in order to logot
    \Pakus\Application\Session::destroySession();
}

function doLogin($username, $password)
{
    global $CONFIG, $db;

    $userLogged = false;

    // Comprovem que l'usuari consti com a membre
    $usuari = $db->getRow(
        sprintf(
            "SELECT uuid, id, username, email, password, profile_image FROM members "
            . "WHERE username='%s' AND disabled='0' LIMIT 1",
            $db->qstr($username)
        )
    );

    if (is_null($usuari)) {
        // L'usuari no existeix, o està deshabilitat.
        return false;
    }
    // L'usuari es correcte ara cal verificar la contrasenya
    // Implementem diversos sistemes d'autenticació
    switch ($CONFIG['authentication']['type']) {
        case 'LDAP':
            // we will use LDAP authentication
            $userLogged = getLDAPAuth(
                $usuari['username'],
                $password,
                $CONFIG['LDAP']['host'],
                $CONFIG['LDAP']['basedn'],
                $CONFIG['LDAP']['filter']
            );
            break;
        default:
            // we will use LOCAL authentication
            if (md5($password) == $usuari['password']) {
                // Usuari validat
                $userLogged = true;
            }
    }
    
    if ($userLogged) {
        // Get the user-agent string of the user.
        $userBrowser = $_SERVER['HTTP_USER_AGENT'];
        $randomString = getRandomString(15);

        $_SESSION['member'] = $usuari;
        $_SESSION['member']['login_string'] = hash(
            'sha512',
            $randomString . $userBrowser
        );

        // Actualitzem el camp last_access i guardem el token
        $db->update(
            'members',
            array(
                'session_id' => $randomString,
                'last_access' => time()
            ),
            sprintf("uuid='%s' LIMIT 1", $usuari['uuid'])
        );
    }

    return $userLogged;
}

function printRegisterForm($missatges = array())
{
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
                    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">
                            <p><?= getHTMLMessages($missatges); ?></p>
    <?php
    $usertext = 'usuari';

    if ('LDAP' == $CONFIG['authentication']['type']) {
        $usertext = 'usuari LDAP';
    }
    ?>
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="<?= $usertext; ?>" required>
                        </div>
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="contrasenya" required>
                        </div>
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            <input type="text" name="email" id="email" class="form-control" placeholder="adreça correu electrònic" required>
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
}

function doRegister($data = array())
{
    global $db, $CONFIG;

    $missatges = array();

    // check supplied data
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $missatges[] = array('type' => "error", 'msg' => "L'adreça electrònica no és correcta.");
        printRegisterForm($missatges);
    }

    // check if user exists
    if (getUserExists($data['username'])) {
        $missatges[] = array(
            'type' => "info",
            'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ja existeix al sistema."
        );
        printLoginForm($data['username'], $missatges);

        return false;
    }

    // check autoregistration
    switch ($CONFIG['authentication']['type']) {
        case 'LDAP':
            if (false === getLDAPAuth($data['username'], $data['password'])) {
                // User has not been validated.
                $missatges[] = array(
                    'type' => "error",
                    'msg' => "No hem pogut comprovar les credencials al LDAP. Revisa-les si us plau"
                );
                printRegisterForm($missatges);

                return false;
            }
            break;
        default:
            die("TODO: Registration for non LDAP users not implemented, yet!");
    }

    // User successfully validated.
    $userId = $db->insert(
        'members',
        array(
            'uuid' => getNewUUID(),
            'username' => $data['username'],
            'email' => $data['email']
        )
    );

    if (0 === $userId) {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear l'usuari.");
        printRegisterForm($missatges);

        return false;
    } else {
        // ACTION: Benvinguda
        doSilentAction($userId, 8);

        $missatges[] = array(
            'type' => "success",
            'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' creat correctament."
        );
        printLoginForm($data['username'], $missatges);

        return true;
    }
}

function getLDAPAuth($username, $password)
{
    global $CONFIG;

    if (empty($username)) {
        return false;
    }

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

    if (false === $bind) {
        return false;
    }

    // fitth is 'attrsonly', sixth is how many results. See ldap_search on php.net
    $sr = ldap_search($connect, $CONFIG['LDAP']['basedn'], $filter, array('mail', 'sn'), 0, 1);

    return ( false === ldap_get_entries($connect, $sr) ) ? false : true;
}
