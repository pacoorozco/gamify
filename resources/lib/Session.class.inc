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
 * \Pakus\Application\Session::startSession('MyName');
 * \Pakus\Application\Session::startSession('MyBlog_Admin', 0, '/myBlog/', 'www.example.com');
 * \Pakus\Application\Session::startSession('My_Accounts_Bank', 0, '/', 'accounts.bank.com', true);
 * @endcode
 *
 */
class Session
{
    /**
     * This function starts, validates and secures a session.
     *
     * @param string $sessionName The name of the session.
     */
    public static function startSession($sessionName = '')
    {
        // sets the session name to the one set above.
        session_name($sessionName . '_session');

        // start the PHP session
        session_start();

        // Make sure the session hasn't expired, and destroy it if it has
        if (self::validateSession()) {
            // Check to see if the session is new or a hijacking attempt
            if (!self::preventHijacking()) {
                // Reset session data and regenerate id
                $_SESSION = array();
                $_SESSION['IPaddress'] = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                    ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
                self::regenerateSession();
            }
        } else {
            self::destroySession();
        }
    }

    /**
     * This function checks to make sure a session exists and is coming from
     * the proper host. On new visits and hacking attempts this function will
     * return false.
     *
     * @return bool TRUE on normal behavour, FALSE if Hijacking is detected
     */
    protected static function preventHijacking()
    {
        if (!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent'])) {
            return false;
        }

        $remoteIPAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? $_SERVER['HTTP_X_FORWARDED_FOR']
            : $_SERVER['REMOTE_ADDR'];

        if ($_SESSION['IPaddress'] != $remoteIPAddress) {
            return false;
        }

        if ($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }

        return true;
    }

    /**
     * This function regenerates a new ID and invalidates the old session.
     * This should be called whenever permission levels for a user change.
     *
     */
    public static function regenerateSession()
    {
        // If this session is obsolete it means there already is a new id
        if (isset($_SESSION['OBSOLETE']) && $_SESSION['OBSOLETE'] == true) {
            return;
        }

        // Set current session to expire in 10 seconds
        $_SESSION['OBSOLETE'] = true;
        $_SESSION['EXPIRES'] = time() + 10;

        // Create new session without destroying the old one
        session_regenerate_id(false);

        // Grab current session ID and close both sessions to allow other scripts to use them
        $newSession = session_id();
        session_write_close();

        // Set session ID to the new one, and start it back up again
        session_id($newSession);
        session_start();

        // Now we unset the obsolete and expiration values for the session we want to keep
        unset($_SESSION['OBSOLETE']);
        unset($_SESSION['EXPIRES']);
    }

    /**
     * This function is used to see if a session has expired or not.
     *
     * @return bool
     */
    protected static function validateSession()
    {
        if (isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES'])) {
            return false;
        }

        if (isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time()) {
            return false;
        }

        return true;
    }

    public static function destroySession()
    {
        // destroy all $_SESSION variables and regenerate session_id
        session_unset();
        session_destroy();
        session_start();
    }
}
