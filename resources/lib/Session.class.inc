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

namespace Pakus\Application;

/**
 * This Session class starts the PHP session (regardless of which handler is set)
 * and secures it by locking down the cookie, restricting the session to a
 * specific host and browser, and regenerating the ID.
 *
 * @code
 * $sess = new \Pakus\Application\Session('MyName');
 * $sess = new \Pakus\Application\Session('MyBlog_Admin', 0, '/myBlog/', 'www.example.com');
 * $sess = new \Pakus\Application\Session('My_Accounts_Bank', 0, '/', 'accounts.bank.com', true);
 * @endcode
 *
 */
class Session
{
    public function __construct($sessionName = null)
    {
        $this->startSession($sessionName);
    }

    /**
     * This function starts, validates and secures a session.
     *
     * @param   string  $sessionName    The name of the session.
     */
    protected function startSession($sessionName)
    {
        // sets the session name to the one set above.
        session_name($sessionName . '_session');

        // start the PHP session
        session_start();

        // Make sure the session hasn't expired, and destroy it if it has
        if ($this->validateSession()) {
            // Check to see if the session is new or a hijacking attempt
            if (!$this->preventHijacking()) {
                // Reset session data and regenerate id
                $_SESSION = array();
                $value = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                    ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                $this->set('IPaddress', $value);
                $this->set('userAgent', $_SERVER['HTTP_USER_AGENT']);
                $this->regenerateSession();
            }
        } else {
            $this->destroySession();
        }
    }

    /**
     * This function checks to make sure a session exists and is coming from
     * the proper host. On new visits and hacking attempts this function will
     * return false.
     *
     * @return  bool    TRUE on normal behavour, FALSE if Hijacking is detected
     */
    protected function preventHijacking()
    {
        if (!$this->issetKey('IPaddress') || !$this->issetKey('userAgent')) {
            return false;
        }

        $remoteIPAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? $_SERVER['HTTP_X_FORWARDED_FOR']
            : $_SERVER['REMOTE_ADDR'];

        if ($this->get('IPaddress') != $remoteIPAddress) {
            return false;
        }

        if ($this->get('userAgent') != $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }

        return true;
    }

    /**
     * This function regenerates a new ID and invalidates the old session.
     * This should be called whenever permission levels for a user change.
     *
     */
    public function regenerateSession()
    {
        // If this session is obsolete it means there already is a new id
        if ($this->get('OBSOLETE') == true) {
            return;
        }

        // Set current session to expire in 10 seconds
        $this->set('OBSOLETE', true);
        $this->set('EXPIRES', time() + 10);

        // Create new session without destroying the old one
        session_regenerate_id(false);

        // Grab current session ID and close both sessions to allow other scripts to use them
        $newSession = session_id();
        session_write_close();

        // Set session ID to the new one, and start it back up again
        session_id($newSession);
        session_start();

        // Now we unset the obsolete and expiration values for the session we want to keep
        $this->delete('OBSOLETE');
        $this->delete('EXPIRES');
    }

    /**
     * This function is used to see if a session has expired or not.
     *
     * @return  bool
     */
    protected function validateSession()
    {
        if ($this->issetKey('OBSOLETE') && !$this->issetKey('EXPIRES')) {
            return false;
        }

        if ($this->issetKey('EXPIRES') && $this->get('EXPIRES') < time()) {
            return false;
        }

        return true;
    }

    public function destroySession()
    {
        // destroy all $_SESSION variables and regenerate session_id
        session_unset();
        session_destroy();
        session_start();
    }

    /**
     * Set session variables
     *
     * @code
     * // store the userid in the session
     * Session::set('userid', $userid);
     *
     * // you can also store more complex values
     * Session::set('array', array('varA', 'varB', 'varC' => array('val1', 'val2'));
     *
     * // you can also use an array to set multiple values at the same time
     * Session::set(array(
     *     'userid' => $userid,
     *     'has_cookies' => $cookie
     * ));
     *
     * // You can also set a specific key into array
     * Session::set('array.varC', $value);
     *
     * // is the same as
     * Session::set('array', array(
     *     'varC' => $value
     * ));
     * @endcode
     *
     * @param   string  $name       name of the variable to set
     * @param   mixed   $value      value to set
     */
    public function set($name, $value = null)
    {
        if (strpos($name, '.') !== false) {
            $parts = explode('.', $name);

            switch (count($parts)) {
                case 2:
                    $_SESSION[$parts[0]][$parts[1]] = $value;
                    break;
                case 3:
                    $_SESSION[$parts[0]][$parts[1]][$parts[2]] = $value;
                    break;
                case 4:
                    $_SESSION[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = $value;
                    break;
                default:
                    $_SESSION = $value;
            }
        } else {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * Get session variables
     *
     * @code
     * // get the stored userid from the session
     * $userid = Session::get('userid');
     *
     * // you can retrieve the entire array stored
     * $arr = Session::get('array');
     *
     * // or get a specific key from the array
     * $arr = Session::get('array.varC');
     * @endcode
     *
     * @param   string  $name       name of the variable to get
     * @param   mixed   $default    the default value to return if $key doesn't exists
     *
     * @return  mixed   the value
     */
    public function get($name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        if (strpos($name, '.') !== false) {
            $parts = explode('.', $name);

            switch (count($parts)) {
                case 2:
                    if (isset($_SESSION[$parts[0]][$parts[1]])) {
                        return $_SESSION[$parts[0]][$parts[1]];
                    }
                    break;
                case 3:
                    if (isset($_SESSION[$parts[0]][$parts[1]][$parts[2]])) {
                        return $_SESSION[$parts[0]][$parts[1]][$parts[2]];
                    }
                    break;
                case 4:
                    if (isset($_SESSION[$parts[0]][$parts[1]][$parts[2]][$parts[3]])) {
                        return $_SESSION[$parts[0]][$parts[1]][$parts[2]][$parts[3]];
                    }
                    break;
            }
        }
        return $default;
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
     * Session::delete('userid');
     *
     * // you can also delete a specific key from the array
     * Session::delete('array.varC');
     * @endcode
     *
     * @param   string  $name       name of the variable to delete
     */
    public function delete($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }

        if (strpos($name, '.') !== false) {
            $parts = explode('.', $name);

            switch (count($parts)) {
                case 2:
                    if (isset($_SESSION[$parts[0]][$parts[1]])) {
                        unset($_SESSION[$parts[0]][$parts[1]]);
                    }
                    break;
                case 3:
                    if (isset($_SESSION[$parts[0]][$parts[1]][$parts[2]])) {
                        unset($_SESSION[$parts[0]][$parts[1]][$parts[2]]);
                    }
                    break;
                case 4:
                    if (isset($_SESSION[$parts[0]][$parts[1]][$parts[2]][$parts[3]])) {
                        unset($_SESSION[$parts[0]][$parts[1]][$parts[2]][$parts[3]]);
                    }
                    break;
            }
        }
    }
}
