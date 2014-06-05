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
 * @package    Index
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

//require_once 'home.php';
require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_FULL);

$router = new AltoRouter();
$router->setBasePath('/test/paco');
/* Setup the URL routing. This is production ready. */
 
// Main routes that non-customers see
$router->map('GET','/', 'home.php', 'home');
$router->map('GET','/home/', 'home.php', 'home-home');
$router->map('GET','/quizs/', 'quiz.php', 'quiz');
$router->map('GET','/members/', 'member.php', 'member');
$router->map('GET','/admin/', 'admin.php', 'admin');
 
// Special (payments, ajax processing, etc)
//$router->map('GET','/charge/[*:customer_id]/','charge.php','charge');
//$router->map('GET','/pay/[*:status]/','payment_results.php','payment-results');
 
// API Routes
//$router->map('GET','/api/[*:key]/[*:name]/', 'json.php', 'api');
 
/* Match the current request */
$match = $router->match();
if($match) {
  require $match['target'];
}
else {
  header("HTTP/1.0 404 Not Found");
  require_once TEMPLATES_PATH . '/tpl_error404.inc';
}
?>

