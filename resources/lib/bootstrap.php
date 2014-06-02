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
 * @package    Bootstrap
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

/**
 * The current system version.
 */
define('VERSION', '2.10-dev');

/**
 * First bootstrap phase: initialize configuration.
 */
define('GAMIFY_BOOTSTRAP_CONFIGURATION', 0);

/**
 * Second bootstrap phase: initialize database layer.
 */
define('GAMIFY_BOOTSTRAP_DATABASE', 1);

/**
 * Third bootstrap phase: initialize session handling.
 */
define('GAMIFY_BOOTSTRAP_SESSION', 2);

/**
 * Final bootstrap phase: All is fully loaded.
 */
define('GAMIFY_BOOTSTRAP_FULL', 3);

// Creating constants for heavily used paths makes things a lot easier.
// ex. require_once(LIBRARY_PATH . "Paginator.php")
defined("LIBRARY_PATH")
    or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/../lib'));
     
defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/../templates'));

// We need some libraries, we require all here.
require_once LIBRARY_PATH . '/Swift/swift_required.php';
require_once LIBRARY_PATH . '/functions.php';
require_once LIBRARY_PATH . '/gamify.php';

/**
 * Ensures gamify is bootstrapped to the specified phase.
 *
 * In order to bootstrap gamify from another PHP script, you can use this code:
 * @code
 *   define('GAMIFY_ROOT', '/path/to/gamify');
 *   require_once GAMIFY_ROOT . '/resources/lib/bootstrap.php';
 *   bootstrap(GAMIFY_BOOTSTRAP_FULL);
 * @endcode
 *
 * @param $phase
 *   A constant telling which phase to bootstrap to. When you bootstrap to a
 *   particular phase, all earlier phases are run automatically. Possible
 *   values:
 *   - GAMIFY_BOOTSTRAP_CONFIGURATION: Initializes configuration.
 *   - GAMIFY_BOOTSTRAP_DATABASE: Initializes the database layer.
 *   - GAMIFY_BOOTSTRAP_SESSION: Initializes session handling.
 *   - GAMIFY_BOOTSTRAP_FULL: Fully load. 
 * 
 * @param $new_phase
 *   A boolean, set to false if calling bootstrap() from inside a
 *   function called from bootstrap (recursion).
 *
 * @return
 *   The most recently completed phase.
 */
function bootstrap($phase = null, $new_phase = true)
{
    static $phases = array(
        GAMIFY_BOOTSTRAP_CONFIGURATION,
        GAMIFY_BOOTSTRAP_DATABASE,
        GAMIFY_BOOTSTRAP_SESSION,
        GAMIFY_BOOTSTRAP_FULL
    );

    static $final_phase;
    static $stored_phase = -1;

    // When not recursing, store the phase name so it's not forgotten while
    // recursing.
    if ($new_phase) {
        $final_phase = $phase;
    }
    if (isset($phase)) {
        // Call a phase if it has not been called before and is below the requested
        // phase.
        while ($phases && $phase > $stored_phase && $final_phase > $stored_phase) {
            $current_phase = array_shift($phases);

            // This function is re-entrant. Only update the completed phase when the
            // current call actually resulted in a progress in the bootstrap process.
            if ($current_phase > $stored_phase) {
                $stored_phase = $current_phase;
            }

            switch ($current_phase) {
                case GAMIFY_BOOTSTRAP_CONFIGURATION:
                    bootstrapConfiguration();
                    break;
                case GAMIFY_BOOTSTRAP_DATABASE:
                    bootstrapDatabase();
                    break;
                case GAMIFY_BOOTSTRAP_SESSION:
                    secureSessionStart();
                    break;
                case DRUPAL_BOOTSTRAP_FULL:
                    break;
            }
        }
    }
    return $stored_phase;
}

/**
 * Sets up the script environment and loads gamify.conf.
 */
function bootstrapConfiguration()
{
    global $CONFIG;
    
    // Where configuration in located
    $configFile = realpath(dirname(__FILE__) . '/../gamify.conf');
    
    // Reads configuration file, creates an array with values
    $CONFIG = parse_ini_file($configFile, true, INI_SCANNER_RAW);
    
    // Sets DEBUG mode based on parsed configuration
    $CONFIG['site']['debug'] = isset($CONFIG['site']['debug']) ? true : false;
}

function bootstrapDatabase()
{
    global $db, $CONFIG;
    
    require_once LIBRARY_PATH . '/DB.class.php';
    
    // Connects to DB and set a descriptor, this will be used later
    $db = new \Pakus\Database\DB(
        $CONFIG['mysql']['host'],
        $CONFIG['mysql']['user'],
        $CONFIG['mysql']['passwd'],
        $CONFIG['mysql']['database']
    ) or die(
        'ERROR: No he pogut connectar amb la base de dades ('
        . mysqli_connect_errno() . ') ' . mysqli_connect_error()
    );
}
