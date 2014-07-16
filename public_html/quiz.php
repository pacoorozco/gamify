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
 * @package    Quiz
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Core\Bootstrap::init(APP_BOOTSTRAP_FULL);

// Page only for members
if (!userIsLoggedIn()) {
    // save referrer to $_SESSION['nav'] for redirect after login
    redirect('login.php', $includePreviousURL = true);
}

require_once TEMPLATES_PATH . '/tpl_header.inc';

// Que hem de fer?
$action = getREQUESTVar('a');

switch ($action) {
    case 'answerqz':
        $quizUUID = getREQUESTVar('item');
        printAnswerQuestionForm($quizUUID);
        break;
    case 'answer':
        $quizUUID = getPOSTVar('item');
        $choices = getPOSTVar('choices');
        answerQuestion($quizUUID, $choices);
        break;
    case 'seeqz':
        $quizUUID = getREQUESTVar('item');
        viewQuestionByUUID($quizUUID);
        break;
    case 'historic':
        printHistoricQuestionList();
        break;
    case 'list':
    default:
        printQuestionList();
}

require_once TEMPLATES_PATH . '/tpl_footer.inc';
exit();

/*** FUNCTIONS ***/
function answerQuestion($questionUUID, $answers)
{
    global $db, $session;

    $missatges = array();
    $question = $db->getRow(
        sprintf(
            "SELECT * FROM questions WHERE uuid='%s' LIMIT 1",
            $db->qstr($questionUUID)
        )
    );
    if (is_null($question)) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        printQuestionList();

        return false;
    }

    $userId = $session->get('member.id');

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $result = $db->getOne(
        sprintf(
            "SELECT id FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $userId,
            $question['id']
        )
    );

    if (!is_null($result)) {
        // L'usuari ja havia respost la pregunta
        viewQuestionByUUID($questionUUID);

        return;
    }

    // if user has not submitted any answer, shows the question again.
    if (empty($answers)) {
        $missatges[] = array(
            'type' => 'warning',
            'msg' => '<strong>Atenció</strong>: No has seleccionat cap resposta, torna-ho a provar.'
        );
        printAnswerQuestionForm($questionUUID, $missatges);

        return false;
    }

    // get question's choices, if none, return
    $choices = $db->getAll(
        sprintf(
            "SELECT * FROM questions_choices WHERE question_id='%d'",
            $question['id']
        )
    );

    if (is_null($choices)) {
        printQuestionList();

        return false;
    }

    // calculate points and success
    $points = 0;
    $success = false;
    foreach ($choices as $choice) {
        if (in_array($choice['id'], $answers)) {
            $points += $choice['points'];
            $success = $success || $choice['correct'];
        }
    }
    // minimun points for answer is '1'
    if ($points < 1) {
        $points = 1;
    }

    $type = 'fail';
    if (true === $success) {
        $type = 'success';
    }

    // ACTION: Badge RAPIDO
    $result = $db->getOne(
        sprintf(
            "SELECT id_member FROM members_questions WHERE id_question='%d'",
            $question['id']
        )
    );
    if (is_null($result)) {
        // Es el primero, hay que dar badge
        doSilentAction($userId, 1);
    }
    // ACTION: END

    $db->insert(
        'members_questions',
        array(
            'id_member' => intval($userId),
            'id_question' => intval($question['id']),
            'amount' => intval($points),
            'answers' => $db->qstr(implode(',', $answers))
        )
    );

    $missatges[] = array(
        'type' => "info",
        'msg' => sprintf(
            "<strong>Gràcies per la teva resposta</strong>. "
            . "La teva resposta ha obtingut una puntuació de <strong>%d punts</strong>.",
            $points
        )
    );

    $oldLevel = getUserLevelById($userId);
    doSilentAddExperience($userId, $points, 'respondre la pregunta: '. $question['name']);
    $newLevel = getUserLevelById($userId);

    if ($oldLevel != $newLevel) {
        $levelName = $db->getOne(
            sprintf("SELECT name FROM levels WHERE id='%d'", $newLevel)
        );
        $missatges[] = array(
            'type' => "success",
            'msg' => sprintf(
                "<strong>Enhorabona!</strong> Acabes de pujar de nivell. Ara ets un <strong>'%s'</strong>.",
                $levelName
            )
        );
    }

    // anem a veure si haig d'executar alguna acció
    $query = sprintf(
        "SELECT * FROM questions_badges WHERE question_id='%d' AND ( type='always' OR type='%s' )",
        $question['id'],
        $type
    );
    $result = $db->query($query);

    if ($result->num_rows > 0) {
        // hi ha accions a realitzar
        while ($row = $result->fetch_assoc()) {
            $actionResult = doSilentAction($userId, $row['badge_id']);
            if ( intval($actionResult) === intval($row['badge_id'])) {
                $badgeName = getBadgeNameById($row['badge_id']);
                $missatges[] = array(
                    'type' => "success",
                    'msg' => sprintf(
                        "<strong>Enhorabona!</strong> Acabes d'aconseguir la insíginia <strong>'%s'</strong>.",
                        $badgeName
                    )
                );
            }
        }
    }

    viewQuestionByUUID($questionUUID, $missatges);
}

function printAnswerQuestionForm($questionUUID, $msg = array())
{
    global $db, $session;

    $question = $db->getRow(
        sprintf(
            "SELECT * FROM questions WHERE uuid='%s' "
            . "AND ( status='active' OR status='hidden' ) LIMIT 1",
            $db->qstr($questionUUID)
        )
    );
    if (is_null($question)) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        printQuestionList();

        return false;
    }

    $userId = $session->get('member.id');

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $result = $db->getOne(
        sprintf(
            "SELECT amount FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $userId,
            $question['id']
        )
    );

    if (!is_null($result)) {
        // L'usuari ja havia respost la pregunta o aquest està tancada
        viewQuestionByUUID($questionUUID);

        return;
    }

    // get question's choices, if none, return
    $question['choices'] = $db->getAll(
        sprintf(
            "SELECT * FROM questions_choices WHERE question_id='%d'",
            $question['id']
        )
    );

    if (is_null($question['choices'])) {
        printQuestionList();

        return false;
    }

    if (empty($question['image'])) {
        $question['image'] = 'images/default_question.png';
    }

    $option = '<input type="radio" name="choices[]" value="%d">';
    if ('multi' == $question['type']) {
        // we must use checkboxes
        $option = '<input type="checkbox" name="choices[]" value="%d">';
    }
    $htmlCode = array();
    // randomize answers to display
    shuffle($question['choices']);
    foreach ($question['choices'] as $choice) {
        $htmlCode[] = '<li class="list-group-item"><label>';
        $htmlCode[] = sprintf($option, $choice['id']);
        $htmlCode[] = $choice['choice'];
        $htmlCode[] = '</label></li>';
    }
    printQuestionHeader('question');
    require_once TEMPLATES_PATH . '/tpl_quiz_answer_form.inc';
}

function printQuestionHeader($a = 'list')
{
    ?>
    <h1>Participa</h1>
    <ul class="nav nav-tabs">
        <li<?= ('list' === $a) ? ' class="active"' : ''; ?>>
            <a href="<?= $_SERVER['PHP_SELF']; ?>?a=list">Contestar pendents</a>
        </li>
        <li<?= ('historic' === $a) ? ' class="active"' : ''; ?>>
            <a href="<?= $_SERVER['PHP_SELF']; ?>?a=historic">Revisar preguntes</a>
        </li>
        <li<?= ('question' === $a) ? ' class="active"' : ' class="disabled"'; ?>>
            <a href="#">Veure pregunta</a>
        </li>
    </ul>
    <?php
}

function printQuestionList()
{
    global $db, $session;
    $htmlCode = array();

    $questions = $db->getAll(
        sprintf(
            "SELECT * FROM questions WHERE status='active' AND id NOT IN "
            . "(SELECT id_question FROM members_questions WHERE id_member='%d')",
            $session->get('member.id')
        )
    );

    if (is_null($questions)) {
        // No hi ha cap pregunta pendent
        $htmlCode[] = getHTMLMessages(
            array(
                'type' => "info",
                'msg' => "<strong>Enhorabona</strong>. "
                . "No tens cap pregunta pendent. ¡Encara pots trobar com seguir participant!"
            )
        );
    } else {
        $htmlCode[] = '<div class="list-group">';
        $htmlCode[] = getHTMLQuestionLink($questions, 'answerqz');
        $htmlCode[] = '</div>';
    }
    printQuestionHeader('list');
    require_once TEMPLATES_PATH . '/tpl_quiz_list.inc';
}

function getHTMLQuestionLink($questionsList, $action)
{
    $htmlCode = array();
    foreach ($questionsList as $row) {
        $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] . '?a=' . $action .'&item='
            . $row['uuid'] . '" class="list-group-item">';
        $htmlCode[] = '<img src="'. $row['image'] .'" width="120" '
            . 'class="img-rounded" alt="'. $row['name'] .'">';
        $htmlCode[] = '<span class="h3">'. $row['name'] .'</span>';
        $htmlCode[] = '</a>';
    }
    return implode(PHP_EOL, $htmlCode);
}

function printHistoricQuestionList()
{
    global $db, $session;
    $htmlCode = array();

    $questions = $db->getAll(
        sprintf(
            "SELECT * FROM questions WHERE status='inactive' OR ( status='active' "
            . "AND id IN (SELECT id_question FROM members_questions WHERE id_member='%d') )",
            $session->get('member.id')
        )
    );

    if (is_null($questions)) {
        // No hi ha cap pregunta pendent
        $htmlCode[] = getHTMLMessages(
            array(
                'type' => "info",
                'msg' => "No hi ha cap pregunta a l'arxiu"
            )
        );
    } else {
        $htmlCode[] = '<div class="list-group">';
        $htmlCode[] = getHTMLQuestionLink($questions, 'seeqz');
        $htmlCode[] = '</div>';
    }
    printQuestionHeader('historic');
    require_once TEMPLATES_PATH . '/tpl_quiz_historic.inc';
}

function viewQuestionByUUID($questionUUID, $msg = array())
{
    global $db, $session;

    $question = $db->getRow(
        sprintf(
            "SELECT * FROM questions WHERE uuid='%s' AND status != 'draft' LIMIT 1",
            $db->qstr($questionUUID)
        )
    );

    if (is_null($question)) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        printQuestionList();

        return false;
    }

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $responses = $db->getRow(
        sprintf(
            "SELECT last_time, amount, answers FROM members_questions "
            . "WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $session->get('member.id'),
            $question['id']
        )
    );
    $answered = (is_null($responses)) ? false : true;

    if ((!$answered) && ('active' == $question['status'])) {
        // L'usuari no ha respost la pregunta i està oberta
        printAnswerQuestionForm($questionUUID);

        return true;
    }

    // get question's choices, if none, return
    $question['choices'] = $db->getAll(
        sprintf(
            "SELECT * FROM questions_choices WHERE question_id='%d'",
            $question['id']
        )
    );

    if (empty($question['image'])) {
        $question['image'] = 'images/default_question.png';
    }

    printQuestionHeader('question');
    ?>
    <p><?= getHTMLMessages($msg); ?></p>
    <div class="panel panel-default" width="70%">
        <div class="panel-heading">
            <h2><?= $question['name']; ?></h2>
        </div>
        <div class="panel-body">
            <img src="<?= $question['image']; ?>" width="120" class="img-rounded">
            <h4><?= $question['question']; ?></h4>
    <?php
    if (!$answered) {
        echo '<div class="alert alert-warning">'
            . '<p>No veus la solució per què no vas respondre aquesta pregunta.</p>'
            . '</div>';
    } else {
        if (!empty($question['solution'])) {
            echo '<div class="alert alert-success">'
            . '<p><strong>La resposta correcta és: </strong></p><p>'
            . $question['solution'] .'</p></div>';
        }
    }
    ?>
                <ul class="list-group">
                    <?= getHTMLQuestionChoices($question['choices'], $responses['answers'], $answered); ?>
                </ul>
    <?php
    if ($answered) {
        echo '<div class="alert alert-info"><p>Vas respondre aquesta pregunta el '
            . $responses['last_time'] . ' i vas obtindre <strong>'
            . $responses['amount'] .' punts</strong>.</p></div>';
    }
    ?>
        </div>
    </div>
    <?php
}

function getHTMLQuestionChoices($choices, $answers, $answered)
{
    $htmlCode = array();
    foreach ($choices as $choice) {
        $htmlCode[] = '<li class="list-group-item">';
        if (!$answered) {
            $htmlCode[] = '<span class="glyphicon glyphicon-question-sign"></span>';
        } else {
            if ('yes' == $choice['correct']) {
                $htmlCode[] = '<span class="glyphicon glyphicon-ok"></span>';
            } else {
                $htmlCode[] = '<span class="glyphicon glyphicon-remove"></span>';
            }
        }
        if ($choice['points'] > 0) {
            $htmlCode[] = '<span class="label label-success pull-right">' . $choice['points'] . ' punts</span>';
        } else {
            $htmlCode[] = '<span class="label label-danger pull-right">' . $choice['points'] . ' punts</span>';
        }
        if (in_array($choice['id'], explode(',', $answers))) {
            $htmlCode[] = '<span class="label label-info">La teva resposta</span>';
        }
        $htmlCode[] = $choice['choice'];
        $htmlCode[] = '</li>';
    }

    return implode(PHP_EOL, $htmlCode);
}
