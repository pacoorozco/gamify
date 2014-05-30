<?php

/**
 * Module to implement Quizs
 *
 *
 * This files implements Quizs on gamify!
 *
 * LICENSE: Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 *
 * @category   Pakus
 * @package    Quiz
 * @author     Paco Orozco <paco@pacorozco.info>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/deed.en (CC BY-SA 3.0)
 * @link       https://git.upcnet.es/bo/gamify
 */

define('IN_SCRIPT', 1);
require_once 'inc/functions.inc.php';
require_once 'inc/gamify.inc.php';

// Page only for members
if (false === loginCheck()) {
    // save referrer to $_SESSION['nav'] for after login redirect
    $_SESSION['nav'] = urlencode($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']);
    header('Location: login.php');
    exit;
}

require_once 'inc/header.inc.php';
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

require_once 'inc/footer.inc.php';
exit();

/*** FUNCTIONS ***/
function answerQuestion($questionUUID, $answers)
{
    global $db;

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

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $result = $db->getOne(
        sprintf(
            "SELECT id FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $_SESSION['member']['id'],
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
            'msg' => 'No has seleccionat cap resposta, torna-ho a provar.'
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
        doSilentAction(intval($_SESSION['member']['id']), 1);
    }
    // ACTION: END

    $db->insert(
        'members_questions',
        array(
            'id_member' => intval($_SESSION['member']['id']),
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

    $oldLevel = getUserLevelById($_SESSION['member']['id']);
    doSilentAddExperience($_SESSION['member']['id'], $points, 'respondre la pregunta: '. $question['name']);
    $newLevel = getUserLevelById($_SESSION['member']['id']);

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
            if (doSilentAction($_SESSION['member']['id'], $row['badge_id']) == $row['badge_id']) {
                $badgeName = $db->getOne(
                    sprintf("SELECT name FROM badges WHERE id='%d'", $row['badge_id'])
                );
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
    global $db;

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
    
    // Mirem si la pregunta ha estat resposta per aquest usuari
    $result = $db->getOne(
        sprintf(
            "SELECT id FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $_SESSION['member']['id'],
            $question['id']
        )
    );
    
    if (!is_null($result) || ('inactive' == $question['status'])) {
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
        $question['image'] = 'images/question_default.png';
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
            <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" role="form">
                <ul class="list-group">
    <?php
    $option = '<input type="radio" name="choices[]" value="%d">';
    if ('multi' == $question['type']) {
        // we must use checkboxes
        $option = '<input type="checkbox" name="choices[]" value="%d">';
    }
    $htmlCode = array();
    foreach ($question['choices'] as $choice) {
        $htmlCode[] = '<li class="list-group-item"><label>';
        $htmlCode[] = sprintf($option, $choice['id']);
        $htmlCode[] = $choice['choice'];
        $htmlCode[] = '</label></li>';
    }
    echo implode(PHP_EOL, $htmlCode);
    ?>
                </ul>
                <a href="//kbtic.upcnet.es/search?SearchableText=<?= $question['tip']; ?>" title="Buscar la resposta a la KBTic" class="btn btn-default" target="_blank"role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a la KBTic</a>
                <a href="//www.google.es/search?q=<?= $question['tip']; ?>" title="Buscar la resposta a Google" class="btn btn-default" target="_blank" role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a Google</a>
                <input type="hidden" name="item" value="<?= $questionUUID; ?>">
                <input type="hidden" name="a" value="answer">
                <button type="submit" class="btn btn-success pull-right"><span class="glyphicon glyphicon-save"></span> Guardar resposta</button>
            </form>
        </div>
    </div>
    <?php
}

function printQuestionHeader($a = 'list')
{
    ?>
            <h1>Participa</h1>

            <ul class="nav nav-tabs">
                <li<?= ('list' === $a) ? ' class="active"' : ''; ?>><a href="<?= $_SERVER['PHP_SELF']; ?>?a=list">Contestar pendents</a></li>
                <li<?= ('historic' === $a) ? ' class="active"' : ''; ?>><a href="<?= $_SERVER['PHP_SELF']; ?>?a=historic">Revisar preguntes</a></li>
                <li<?= ('question' === $a) ? ' class="active"' : ' class="disabled"'; ?>><a href="#">Veure pregunta</a></li>
            </ul>
    <?php
}

function printQuestionList()
{
    global $db;

    $message = array();

    printQuestionHeader('list');
    ?>
    <div class="panel panel-default" width="70%">
        <div class="panel-body">
            <div class="well">
                <h4>Estàs preparat per posar-te a prova?</h4>
                <p>Trobaras preguntes sobre diferents temes de la nostra organització, la nostra metodologia, eines, procediments... qualsevol cosa que serà molt util que coneguis.</p>
                <p>A més a més guanyaràs punts i insígnies per pujar de nivell, presumir amb els teus companys o simplement superar-te.</p>
            </div>
            <h4>Les teves preguntes pendents</h4>

    <?php
    $query = sprintf(
        "SELECT * FROM questions WHERE status='active' AND id NOT IN "
        . "(SELECT id_question FROM members_questions WHERE id_member='%d')",
        intval($_SESSION['member']['id'])
    );

    $result = $db->query($query);

    if (0 === $result->num_rows) {
        // No hi ha cap pregunta pendent
        $message[] = array(
            'type' => "info",
            'msg' => "<strong>Enhorabona</strong>. "
            . "No tens cap pregunta pendent. ¡Encara pots trobar com seguir participant!"
        );
        echo getHTMLMessages($message);
    } else {
        $htmlCode = array();

        $htmlCode[] = '<div class="list-group">';
        while ($row = $result->fetch_assoc()) {
            $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=answerqz&item='. $row['uuid'] .'" class="list-group-item">';
            $htmlCode[] = '<img src="'. $row['image'] .'" width="120" class="img-rounded" alt="'. $row['name'] .'">';
            $htmlCode[] = '<span class="h3">'. $row['name'] .'</span>';
            $htmlCode[] = '</a>';
        }
        $htmlCode[] = '</div>';

        echo implode(PHP_EOL, $htmlCode);
        unset($htmlCode);
    }
    ?>
        </div>
    </div>
    <?php
}

function printHistoricQuestionList()
{
    global $db;

    $message = array();

    printQuestionHeader('historic');
    ?>
    <div class="panel panel-default" width="70%">
        <div class="panel-body">
            <div class="well">
                <h4>Biblioteca de preguntes</h4>
                <p>Trobaràs totes les preguntes que s'han proposat, les que has respost i la teva puntuació.</p>
            </div>

    <?php
    $query = sprintf(
        "SELECT * FROM questions WHERE status='inactive' OR ( status='active' "
        . "AND id IN (SELECT id_question FROM members_questions WHERE id_member='%d') )",
        intval($_SESSION['member']['id'])
    );

    $result = $db->query($query);

    if (0 === $result->num_rows) {
        // No hi ha cap pregunta pendent
        $message[] = array(
            'type' => "info",
            'msg' => "No hi ha cap pregunta a l'arxiu"
        );
        echo getHTMLMessages($message);
    } else {
        $htmlCode = array();
        $htmlCode[] = '<div class="list-group">';
        while ($row = $result->fetch_assoc()) {
            $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=seeqz&item='. $row['uuid'] .'" class="list-group-item">';
            $htmlCode[] = '<img src="'. $row['image'] .'" width="120" class="img-rounded" alt="'. $row['name'] .'">';
            $htmlCode[] = '<span class="h3">'. $row['name'] .'</span>';
            $htmlCode[] = '</a>';
        }
        $htmlCode[] = '</div>';
        echo implode(PHP_EOL, $htmlCode);
        unset($htmlCode);
    }
    ?>
        </div>
    </div>
    <?php
}

function viewQuestionByUUID($questionUUID, $msg = array())
{
    global $db;

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
            "SELECT last_time, amount, answers FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $_SESSION['member']['id'],
            $question['id']
        )
    );
    $answered = (is_null($responses)) ? false : true;

    if ((!$answered) && ('active' == $question['status'])) {
        // L'usuari no ha respost la pregunta i està oberta
        printAnswerQuestionForm($questionUUID);
        return;
    }

    // get question's choices, if none, return
    $question['choices'] = $db->getAll(
        sprintf(
            "SELECT * FROM questions_choices WHERE question_id='%d'",
            $question['id']
        )
    );

    if (empty($question['image'])) {
        $question['image'] = 'images/question_default.jpg';
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
        echo '<div class="alert alert-warning"><p>No veus la solució per què no vas respondre aquesta pregunta.</p></div>';
    } else {
        if (!empty($question['solution'])) {
            echo '<div class="alert alert-success"><p><strong>La resposta correcta és: </strong></p><p>'. $question['solution'] .'</p></div>';
        }
    }
    ?>
                <ul class="list-group">
                    <?= getHTMLQuestionChoices($question['choices'], $responses['answers'], $answered); ?>
                </ul>
    <?php
    if ($answered) {
        echo '<div class="alert alert-info"><p>Vas respondre aquesta pregunta el ' . $responses['last_time'] . ' i vas obtindre <strong>' . $responses['amount'] .' punts</strong>.</p></div>';
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
