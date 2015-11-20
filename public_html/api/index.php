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

require_once realpath(dirname(__FILE__) . '/../../resources/lib/Bootstrap.class.inc');
\Pakus\Core\Bootstrap::init(APP_BOOTSTRAP_DATABASE);

use \Slim\Slim;

Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/questions', 'getAllQuestions');
$app->get('/question/:id', 'getOneQuestion');

$app->run();

function getAllQuestions() {
    global $db;

    $app = \Slim\Slim::getInstance();

    $sql = sprintf(
        "SELECT uuid, name FROM questions WHERE status='active'"
    );

    $questions = $db->getAll($sql);

    if ($questions) {
        $app->response->setStatus(200);
        $app->response()->headers->set('Content-Type', 'application/json');
        echo json_encode($questions);
    } else {
        $app->response()->setStatus(404);
        echo '{"error":{"text": ERROR}}';
    }
}

function getOneQuestion($id) {
    global $db;

    $app = \Slim\Slim::getInstance();

    $sql = sprintf(
        "SELECT uuid, name, question FROM questions WHERE status='active' AND uuid='%s'",
        $id
    );

    $question = $db->getRow($sql);

    if ($question) {
        $app->response->setStatus(200);
        $app->response()->headers->set('Content-Type', 'application/json');
        echo json_encode($question);
    } else {
        $app->response()->setStatus(404);
        echo '{"error":{"text": ERROR}}';
    }

}

