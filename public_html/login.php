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
        if (doLogin(getPOSTVar('username'), getPOSTVar('password'))) {
            // go to previous referrer, if exists
            $nav = !empty(getPOSTVar('nav'))
                ? urldecode(getPOSTVar('nav'))
                : 'index.php';
            redirect($nav);
        } else {
            $errors = array(
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
    global $CONFIG, $session;

    // get url to redirect after login
    if ($session->issetKey('nav')) {
        $nav = $session->get('nav');
        $session->delete('nav');
    } else {
        $nav = getPOSTVar('nav');
    }
    $usertext = 'usuari';
    $logintext = 'Accedir';

    if ('LDAP' == $CONFIG['authentication']['type']) {
        $usertext = 'usuari LDAP';
        $logintext = 'Accedir amb LDAP';
    }
    require_once TEMPLATES_PATH . '/tpl_login_form.inc';
}

function doLogout()
{
    global $session;
    // destroy $_SESSION in order to logot
    $session->destroySession();
}

function doLogin($username, $password)
{
    global $CONFIG, $db, $session;

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

        $session->set('member', $usuari);
        $session->set(
            'member.login_string',
            hash(
                'sha512',
                $randomString . $userBrowser
            )
        );

        // Actualitzem el camp last_access i guardem el token
        $db->update(
            'members',
            array(
                'session_id' => $randomString,
                'last_access' => date('Y-m-d H:i:s')
            ),
            sprintf("uuid='%s' LIMIT 1", $usuari['uuid'])
        );
    }

    return $userLogged;
}

function printRegisterForm($missatges = array())
{
    $usertext = 'usuari';

    if ('LDAP' == $CONFIG['authentication']['type']) {
        $usertext = 'usuari LDAP';
    }
    require_once TEMPLATES_PATH . '/tpl_register_form.inc';
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
