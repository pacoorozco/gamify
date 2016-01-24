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

// require_once realpath(dirname(__FILE__) . '/../../resources/lib/Bootstrap.class.inc');
require_once dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc';
\Pakus\Core\Bootstrap::init(APP_BOOTSTRAP_FULL);

use Slim\Slim;

Slim::registerAutoloader();

// User id from db - Global Variable
$user_id = null;

$app = new \Slim\Slim();

$app->get('/questions', function () use ($app) {

})->name('list');

$app->get('/questions/:uuid', function ($uuid) use ($app, $session) {
    require_once('quiz2.php');
    require_once TEMPLATES_PATH . '/tpl_header.inc';
    printAnswerQuestionForm($uuid);
    require_once TEMPLATES_PATH . '/tpl_footer.inc';

})->name('view');

$app->post('/questions/:uuid', function ($uuid) use ($app, $session) {
    require_once('quiz2.php');
    require_once TEMPLATES_PATH . '/tpl_header.inc';
    $choices = $app->request->params('choices');
    answerQuestion($uuid, $choices);
    require_once TEMPLATES_PATH . '/tpl_footer.inc';
})->name('answer');


$app->run();
