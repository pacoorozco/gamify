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

define('IN_SCRIPT', 1);
require_once 'inc/functions.inc.php';
require_once 'inc/gamify.inc.php';

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

        if (true === doLogin($username, $password)) {
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
require_once 'inc/header.inc.php';

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

require_once 'inc/footer.inc.php';
exit();

/*** FUNCTIONS ***/

function printLoginForm($username = '', $missatges = array())
{
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
}

function doLogout()
{
    global $db;

    // updates members to put session_id to NULL
    $db->update(
        'members',
        array('session_id' => null),
        sprintf("id='%d' LIMIT 1", $_SESSION['member']['id'])
    );

    secureSessionDestroy();
} // END do_logout()

function doLogin($username, $password)
{
    global $CONFIG, $db;

    // Primer fixem a FALS la resposta d'aquesta funcio
    $userIsMember = false;

    // Comprovem que l'usuari consti com a membre
    $usuari = $db->getRow(
        sprintf(
            "SELECT id, username, password, disabled FROM members "
            . "WHERE username='%s' LIMIT 1",
            $db->qstr($username)
        )
    );

    if (is_null($usuari)) {
        return false;
    }
    // L'usuari es correcte ara cal verificar la contrasenya

    // Si esta deshabilitat tampoc poc accedir
    if (1 == $usuari['disabled']) {
        return false;
    }

    // we implement several auth types
    switch ($CONFIG['authentication']['type']) {
        case 'LDAP':
            // we will use LDAP authentication
            $userIsMember = getLDAPAuth(
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
                // Usuari validat i es membre
                $userIsMember = true;
            }
    }

    if ($userIsMember) {
        // Usuari validat, actualitzem session_id
        $db->update(
            'members',
            array(
                'session_id' => session_id(),
                'last_access' => time()
            ),
            sprintf("id='%d' LIMIT 1", $usuari['id'])
        );
    }

    return $userIsMember;
} // END do_login()

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
                        <div style="margin-bottom: 25px" class="form-group">
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
