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

// Reads configuration file, creates an array with values
$CONFIG = parse_ini_file(realpath(dirname(__FILE__) . '/../gamify.conf'), true, INI_SCANNER_RAW);

// Put APP version
$CONFIG['version'] = '2.9';

// Sets DEBUG mode based on parsed configuration
$CONFIG['site']['debug'] = isset($CONFIG['site']['debug']) ? true : false;

// Creating constants for heavily used paths makes things a lot easier.
// ex. require_once(LIBRARY_PATH . "Paginator.php")
defined("LIBRARY_PATH")
    or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/../lib'));
     
defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/../templates'));

// We need some libraries, we require all here.
require_once LIBRARY_PATH . '/Swift/swift_required.php';
require_once LIBRARY_PATH . '/DB.class.php';
require_once LIBRARY_PATH . '/functions.php';
require_once LIBRARY_PATH . '/gamify.php';

/*** MAIN ***/

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

// Start the session (pretty important!)
secureSessionStart();
