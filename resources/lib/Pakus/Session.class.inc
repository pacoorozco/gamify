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
 * @package    Application
 * @class      Session
 * @version    1.1
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

namespace Pakus\Core;

/**
 * This Session class starts the PHP session (regardless of which handler is set)
 * and secures it by locking down the cookie, restricting the session to a
 * specific host and browser, and regenerating the ID.
 *
 * @code
 * $sess = new \Pakus\Core\Session('MyName');
 * $sess = new \Pakus\Core\Session('MyBlog_Admin', 0, '/myBlog/', 'www.example.com');
 * $sess = new \Pakus\Core\Session('My_Accounts_Bank', 0, '/', 'accounts.bank.com', true);
 * @endcode
 *
 */
class Session
{
    // Session name
    protected $name;

    // An array that contains cookie params
    protected $cookie;

    /**
     * Idle period. If the user is inactive for more than this period, the session must expire.
     * @var int
     */
    public static $inactivityMaxTime = 1800; //30 min.

    /**
     * Session Aging. After this period, the session must expire no matter what.
     * @var int
     */
    public static $expireMaxTime = 604800; //1 week.

    /**
     * sweep ratio for probablity function in expired session removal process
     * @var decimal
     */
    public static $SweepRatio = 0.75;

    public function __construct($name = 'MY_SESSION_NAME', $cookie = array())
    {
        $this->name = $name;
        $this->cookie = $cookie;

        /**
         * Configure cookie parameters, defaults values could be modified calling
         * the constructor with an array of it.
         */
        $this->cookie += array(
            'lifetime' => 0,
            'path'     => ini_get('session.cookie_path'),
            'domain'   => ini_get('session.cookie_domain'),
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true
        );

        $this->setup();
        $this->startSession();
    }

    protected function setup()
    {
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        session_name($this->name);

        session_set_cookie_params(
            $this->cookie['lifetime'],
            $this->cookie['path'],
            $this->cookie['domain'],
            $this->cookie['secure'],
            $this->cookie['httponly']
        );
    }

    /**
     *  Function to sweep expired session
     */
    private function clearExpiredSession($force = false)
    {
        if (!$force) {
            if (mt_rand(0, 1000) / 1000.0 > self::$SweepRatio) {
                return;
            }
        }

        return $this->regenerateSession();
    }

    /**
     * This function starts, validates and secures a session.
     *
     */
    protected function startSession()
    {
        // start the PHP session
        session_start();

        // function to clear expired sessions
        $this->clearExpiredSession();

        // make sure the session is valid, and destroy it if it's not
        if (! $this->validateSession()) {
            $this->destroySession();
        }

        $this->updateUserCookies();
    }

    /**
     * This function regenerates a new ID and invalidates the old session.
     * This should be called whenever permission levels for a user change.
     *
     */
    public function regenerateSession()
    {
        // If this session is obsolete it means there already is a new id.
        if ($this->get('_obsolete')) {
            return;
        }

        /**
         * Set current session to expire in 10 seconds, this time will be enough
         * whatever old session is doing.
         */
        $this->set('_obsolete', true);
        $this->set('_last_activity', time() + 10);

        // Create new session without destroying the old one
        session_regenerate_id(false);

        // Grab current session ID and close both sessions to allow other scripts to use them
        $newSession = session_id();
        session_write_close();

        // Set session ID to the new one, and start it back up again
        session_id($newSession);
        session_start();

        // Now we unset the obsolete and expiration values for the session we want to keep
        $this->delete('_obsolete');
        $this->delete('_last_activity');

        $this->updateUserCookies();
    }

    /**
     * This function checks is session is expired based on a value into the
     * session data.
     *
     * @return true | false     Return true if session has been inactive too much
     */
    public function isExpired()
    {
        $last = $this->issetKey('_last_activity')
            ? $this->get('_last_activity')
            : false;

        if ($last !== false && time() - $last > self::$inactivityMaxTime) {
            return true;
        }

        $this->set('_last_activity', time());

        return false;
    }

    /**
     * This function checks to make sure a session exists and is coming from
     * the proper host.  It relies in some session data and compares to a user
     * provided data.
     *
     * @return true | false     Return true if session has a correct fingerprint
     */
    public function isFingerprint()
    {
        // uses HTTP_X_FORWARDED_FOR in case it exists, for example proxied hits
        $remoteIPAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? $_SERVER['HTTP_X_FORWARDED_FOR']
            : $_SERVER['REMOTE_ADDR'];

        $hash = md5(
            $_SERVER['HTTP_USER_AGENT'] .
            $remoteIPAddress
        );

        if ($this->issetKey('_fingerprint')) {
            return $this->get('_fingerprint') === $hash;
        }

        $this->set('_fingerprint', $hash);
        return true;
    }

    /**
     * This function is used to see if a session is valid or not
     *
     * @return  bool
     */
    protected function validateSession()
    {
        return !$this->isExpired() && $this->isFingerprint();
    }

    public function destroySession()
    {
        if (session_id() === '') {
            return false;
        }

        // destroy all $_SESSION variables and regenerate session_id
        session_unset();
        $this->updateUserCookies(true);
        return session_destroy();
    }

    /**
     * Function to update/delete user session cookies
     * @param boolean $deleteCookie
     *     True indicates this function to DELETE the cookie from the user's browser.
     *     False indicates this function to CREATE the cookie in user's browser.
     */
    public function updateUserCookies($deleteCookie = false)
    {
        if ($deleteCookie === false) {
            // Not sure if its good implemented
            return;
            \setcookie(
                $this->name,
                session_id(),
                time() + Session::$expireMaxTime,
                $this->cookie['path'],
                $this->cookie['domain'],
                $this->cookie['secure'],
                $this->cookie['httponly']
            );
        } else {
            \setcookie(
                $this->name,
                null,
                time() - Session::$expireMaxTime,
                $this->cookie['path'],
                $this->cookie['domain'],
                $this->cookie['secure'],
                $this->cookie['httponly']
            );
        }
    }

    /**
     * Set session variables
     *
     * @code
     * // store the userid in the session
     * $session->set('userid', $userid);
     *
     * // you can also store more complex values
     * $session->set('array', array('varA', 'varB', 'varC' => array('val1', 'val2'));
     *
     * // you can also use an array to set multiple values at the same time
     * $session->set(array(
     *     'userid' => $userid,
     *     'has_cookies' => $cookie
     * ));
     *
     * // You can also set a specific key into array
     * $session->set('array.varC', $value);
     *
     * // is the same as
     * $session->set('array', array(
     *     'varC' => $value
     * ));
     * @endcode
     *
     * @param   string  $name       name of the variable to set
     * @param   mixed   $value      value to set
     */
    public function set($name, $value = null)
    {
        $parsed = explode('.', $name);
        $session =& $_SESSION;

        while (count($parsed) > 1) {
            $next = array_shift($parsed);
            if (!isset($session[$next]) || !is_array($session[$next])) {
                $session[$next] = array();
            }

            $session =& $session[$next];
        }

        $session[array_shift($parsed)] = $value;
    }

    /**
     * Get session variables
     *
     * @code
     * // get the stored userid from the session
     * $userid = $session->get('userid');
     *
     * // you can retrieve the entire array stored
     * $arr = $session->get('array');
     *
     * // or get a specific key from the array
     * $arr = $session->get('array.varC');
     * @endcode
     *
     * @param   string  $name       name of the variable to get
     * @param   mixed   $default    the default value to return if $key doesn't exists
     *
     * @return  mixed   the value
     */
    public function get($name, $default = null)
    {
        $parsed = explode('.', $name);
        $result = $_SESSION;

        while ($parsed) {
            $next = array_shift($parsed);

            if (isset($result[$next])) {
                $result = $result[$next];
            } else {
                return $default;
            }
        }

        return $result;
    }

    /**
     * Return if $name is set
     *
     * @param   string  $name
     *
     * @return  bool
     */
    public function issetKey($name)
    {
        return !is_null($this->get($name, null));
    }

    /**
     * Delete session variables
     *
     * @code
     * // delete the stored userid from the session
     * $session->delete('userid');
     *
     * // you can also delete a specific key from the array
     * $session->delete('array.varC');
     * @endcode
     *
     * @param   string  $name       name of the variable to delete
     */
    public function delete($name)
    {
        $parsed = explode('.', $name);
        $session =& $_SESSION;

        while (count($parsed) > 1) {
            $next = array_shift($parsed);
            if (!isset($session[$next]) || !is_array($session[$next])) {
                $session[$next] = array();
            }

            $session =& $session[$next];
        }

        unset($session[array_shift($parsed)]);
    }
}
