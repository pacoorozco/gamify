<?php
/*
 * @author Emilio Ampudia, emilio.ampudia -at- upcnet.es
 * @version $Id: quiz.php 65 2014-04-21 18:09:54Z paco $
 *
 */

define('IN_SCRIPT', 1);
require_once('inc/functions.inc.php');
require_once('inc/gamify.inc.php');

// Page only for members
if ( false === loginCheck() ) {
    // save referrer to $_SESSION['nav'] for after login redirect
    $_SESSION['nav'] = urlencode($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']);
    header('Location: login.php');
    exit;
}

require_once('inc/header.inc.php');
// Que hem de fer?
$action = getREQUESTVar('a');

switch ($action) {
    case 'answerqz':
        $quiz_id = getREQUESTVar('item');
        printAnswerQuestionForm($quiz_id);
        break;

    case 'answer':
        $quiz_id = getPOSTVar('item');
        $choices = getPOSTVar('choices');
        answerQuestion( $quiz_id, $choices );
        break;

    case 'seeqz':
        $quiz_id = getREQUESTVar('item');
        viewQuestionByUUID($quiz_id);
        break;

    case 'historic':
        printHistoricQuestionList();
        break;

    case 'list':
    default:
        printQuestionList();
}

require_once('inc/footer.inc.php');
exit();

/*** FUNCTIONS ***/
function answerQuestion( $questionUUID, $answers ) {
    global $db;

    $htmlCode = array();

    $query = sprintf( "SELECT * FROM questions WHERE uuid='%s' LIMIT 1", $db->real_escape_string($questionUUID) );
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        printQuestionList();
        return false;
    }

    $question = $result->fetch_assoc();
    $questionId = $question['id'];

    if ( !empty($question['solution']) ) {
        $htmlCode[] = sprintf("<p>La resposta correcta és:</p><pre>%s</pre>", $question['solution']);
    }

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $query = sprintf( "SELECT * FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $_SESSION['member']['id'],
            $questionId
            );

    $result = $db->query($query);
    if ( $result->num_rows > 0 ) {
        // L'usuari ja ha respost la pregunta
        viewQuestionByUUID($questionUUID);
        return;
    }

    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $questionId);
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        printQuestionList();
        return false;
    }

    $choices = array();
    while ( $row = $result->fetch_assoc() ) {
        $choices[] = $row;
    }

    // calculate points and success
    $points = 0;
    $success = false;
    foreach ( $choices as $choice ) {
        if ( in_array($choice['id'], $answers) ) {
            $points += $choice['points'];
            $success = $success || $choice['correct'];
        }
    }
    // minimun points for answer is '1'
    if ($points < 1 ) $points = 1;

    $type = 'fail';
    if ( true === $success ) {
        $type = 'success';
    }

    // ACTION: Badge RAPIDO
    $query = sprintf("SELECT id_member FROM members_questions WHERE id_question='%d'", intval($questionId));
    $result = $db->query($query);
    if ($result->num_rows === 0) {
        // Es el primero, hay que dar badge
        doSilentAction(intval($_SESSION['member']['id']), 1);
    }
    // END ACTION

    $query = sprintf( "INSERT INTO members_questions SET id_member='%d', id_question='%d', amount='%d'",
            intval($_SESSION['member']['id']),
            intval($questionId),
            intval($points)
            );

    $db->query($query);

    $oldLevel = getUserLevelById($_SESSION['member']['id']);
    doSilentAddExperience( $_SESSION['member']['id'], $points, 'respondre la pregunta: '. $question['name'] );
    $newLevel = getUserLevelById($_SESSION['member']['id']);

    if ($oldLevel != $newLevel) {
        $query = sprintf("SELECT name FROM levels WHERE id='%d'", $newLevel);
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $htmlCode[] = sprintf("<p><strong>Enhorabona!</strong> Acabes de pujar de nivell. Ara ets un <strong>'%s'</strong>.</p>", $row['name']);
    }

    // anem a veure si haig d'executar alguna accio
    $query = sprintf("SELECT * FROM questions_badges WHERE question_id='%d' AND ( type='always' OR type='%s' )",
            $questionId,
            $type
            );
    $result = $db->query($query);

    if ( $result->num_rows > 0 ) {
        // hi ha accions a realitzar
        while ( $row = $result->fetch_assoc() ) {
            if (doSilentAction($_SESSION['member']['id'], $row['badge_id']) == $row['badge_id']) {
                $query = sprintf("SELECT name FROM badges WHERE id='%d'", $row['badge_id']);
                $result2 = $db->query($query);
                $row2 = $result2->fetch_assoc();
                $htmlCode[] = sprintf("<p><strong>Enhorabona!</strong> Acabes d'aconseguir la insíginia <strong>'%s'</strong>.</p>", $row2['name']);
            }
        }
    }

    printQuestionHeader('question');
    ?>

    <div class="panel panel-default" width="70%">
        <div class="panel-heading"><h2>Gràcies per la teva resposta</h2></div>
        <div class="panel-body">
            <p>La teva resposta ha obtingut una puntuació de <strong><?php echo $points; ?> punts</strong>.</p>
            <?php echo implode(PHP_EOL, $htmlCode); ?>
        </div>
    </div>
    <?php
}

function printAnswerQuestionForm( $questionUUID ) {
    global $db;

    $query = sprintf( "SELECT * FROM questions WHERE uuid='%s' AND ( status='active' OR status='hidden' ) LIMIT 1", $db->real_escape_string($questionUUID) );
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        printQuestionList();
        return false;
    }

    $question = $result->fetch_assoc();
    $questionId = $question['id'];

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $query = sprintf( "SELECT * FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $_SESSION['member']['id'],
            $questionId
            );

    $result = $db->query($query);
    if ( ($result->num_rows > 0) || ('inactive' == $question['status']) ) {
        // L'usuari ja ha respost la pregunta o aquest està tancada
        viewQuestionByUUID($questionUUID);
        return;
    }

    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $questionId);
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        printQuestionList();
        return false;
    }

    $question['choices'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $question['choices'][] = $row;
    }

    if ( empty($question['image']) ) {
        $question['image'] = 'images/question_default.png';
    }

    printQuestionHeader('question');
    ?>
    <div class="panel panel-default" width="70%">
        <div class="panel-heading">
            <h2><?php echo $question['name']; ?></h2>
        </div>
        <div class="panel-body">
            <img src="<?php echo $question['image']; ?>" width="120" class="img-rounded">
            <h4><?php echo $question['question']; ?></h4>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" role="form">
                <ul class="list-group">
                    <?php
                        $option = '<input type="radio" name="choices[]" value="%d">';
                        if ( 'multi' == $question['type'] ) {
                            // we must use checkboxes
                            $option = '<input type="checkbox" name="choices[]" value="%d">';
                        }
                        $htmlCode = array();
                        foreach ($question['choices'] as $choice) {
                            $htmlCode[] = '<li class="list-group-item"><label>';
                            $htmlCode[] = sprintf( $option, $choice['id'] );
                            $htmlCode[] = $choice['choice'];
                            $htmlCode[] = '</label></li>';

                        }
                        echo implode(PHP_EOL, $htmlCode);
                    ?>
                </ul>
                <a href="//kbtic.upcnet.es/search?SearchableText=<?php echo $question['tip']; ?>" title="Buscar la resposta a la KBTic" class="btn btn-default" target="_blank"role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a la KBTic</a>
                <a href="//www.google.es/search?q=<?php echo $question['tip']; ?>" title="Buscar la resposta a Google" class="btn btn-default" target="_blank" role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a Google</a>
                <input type="hidden" name="item" value="<?php echo $questionUUID; ?>">
                <input type="hidden" name="a" value="answer">
                <button type="submit" class="btn btn-success pull-right"><span class="glyphicon glyphicon-save"></span> Guardar resposta</button>
            </form>
        </div>
    </div>
    <?php
}

function printQuestionHeader( $a = 'list' ) {
    ?>
            <h1>Participa</h1>

            <ul class="nav nav-tabs">
                <li<?php echo ( 'list' == $a ) ? ' class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=list">Contestar pendents</a></li>
                <li<?php echo ( 'historic' == $a ) ? ' class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=historic">Revisar preguntes</a></li>
                <li<?php echo ( 'question' == $a ) ? ' class="active"' : ' class="disabled"'; ?>><a href="#">Veure pregunta</a></li>
            </ul>
    <?php
}

function printQuestionList() {
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
            $query = sprintf( "SELECT * FROM questions WHERE status='active' AND id NOT IN (SELECT id_question FROM members_questions WHERE id_member='%d')",
                    intval($_SESSION['member']['id'])
                    );

            $result = $db->query($query);

            if ( 0 === $result->num_rows ) {
                // No hi ha cap pregunta pendent
                $message[] = array('type' => "info", 'msg' => "<strong>Enhorabona</strong>. No tens cap pregunta pendent. ¡Encara pots trobar com seguir participant!");
                echo getHTMLMessages($message);
            } else {
                $htmlCode = array();

                $htmlCode[] = '<div class="list-group">';
                while ( $row = $result->fetch_assoc() ) {
                    $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=answerqz&item='. $row['uuid'] .'" class="list-group-item">';
                    if ( empty($row['image']) ) {
                        $htmlCode[] = '<img data-src="holder.js/120x120" class="img-rounded" alt="'. $row['name'] .'">';
                    } else {
                        $htmlCode[] = '<img src="'. $row['image'] .'" width="120" class="img-rounded" alt="'. $row['name'] .'">';
                    }
                    $htmlCode[] = '<span class="h3">'. $row['name'] .'</span>';
                    $htmlCode[] = '</a>';
                }
                $htmlCode[] = '</div>';

                echo implode(PHP_EOL, $htmlCode);
                unset($htmlCode);
            }
}

function printHistoricQuestionList() {
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

            $query = sprintf( "SELECT * FROM questions WHERE status='inactive' OR ( status='active' AND id IN (SELECT id_question FROM members_questions WHERE id_member='%d') )",
                    intval($_SESSION['member']['id'])
                    );

            $result = $db->query($query);

            if ( 0 === $result->num_rows ) {
                // No hi ha cap pregunta pendent
                $message[] = array('type' => "info", 'msg' => "No hi ha cap pregunta a l'arxiu");
                echo getHTMLMessages($message);
            } else {
                $htmlCode = array();

                $htmlCode[] = '<div class="list-group">';
                while ( $row = $result->fetch_assoc() ) {
                    $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=seeqz&item='. $row['uuid'] .'" class="list-group-item">';
                    if ( empty($row['image']) ) {
                        $htmlCode[] = '<img data-src="holder.js/120x120" class="img-rounded" alt="'. $row['name'] .'">';
                    } else {
                        $htmlCode[] = '<img src="'. $row['image'] .'" width="120" class="img-rounded" alt="'. $row['name'] .'">';
                    }
                    $htmlCode[] = '<span class="h3">'. $row['name'] .'</span>';
                    $htmlCode[] = '</a>';
                }
                $htmlCode[] = '</div>';

                echo implode(PHP_EOL, $htmlCode);
                unset($htmlCode);
            }
}

function viewQuestionByUUID($questionUUID) {
    global $db;

    $query = sprintf( "SELECT * FROM questions WHERE uuid='%s' AND status != 'draft' LIMIT 1", $db->real_escape_string($questionUUID) );
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        printQuestionList();
        return false;
    }

    $question = $result->fetch_assoc();
    $questionId = $question['id'];

    // Mirem si la pregunta ha estat resposta per aquest usuari
    $query = sprintf( "SELECT * FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1",
            $_SESSION['member']['id'],
            $questionId
            );

    $result = $db->query($query);
    $answered = true;
    if ( 0 == $result->num_rows ) {
        // L'usuari no ha respost la pregunta
        $answered = false;
    }

    if ( ( false === $answered ) && ('active' == $question['status']) ) {
        // L'usuari no ha respost la pregunta i està oberta
        printAnswerQuestionForm($questionUUID);
        return;
    }

    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $questionId);
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        printQuestionList();
        return false;
    }

    $question['choices'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $question['choices'][] = $row;
    }

    if ( empty($question['image']) ) {
        $question['image'] = 'images/question_default.jpg';
    }

    printQuestionHeader('question');
    ?>
    <div class="panel panel-default" width="70%">
        <div class="panel-heading">
            <h2><?php echo $question['name']; ?></h2>
        </div>
        <div class="panel-body">
            <img src="<?php echo $question['image']; ?>" width="120" class="img-rounded">
            <h4><?php echo $question['question']; ?></h4>
                <ul class="list-group">
                    <?php
                        $htmlCode = array();
                        foreach ($question['choices'] as $choice) {
                            $htmlCode[] = '<li class="list-group-item">';
                            if ( true === $answered ) {
                                if ( 'yes' == $choice['correct'] ) {
                                    $htmlCode[] = '<span class="glyphicon glyphicon-ok"></span>';
                                } else {
                                    $htmlCode[] = '<span class="glyphicon glyphicon-remove"></span>';
                                }
                            } else {
                                $htmlCode[] = '<span class="glyphicon glyphicon-question-sign"></span>';
                            }
                            $htmlCode[] = $choice['choice'];
                            $htmlCode[] = '</li>';

                        }
                        echo implode(PHP_EOL, $htmlCode);
                    ?>
                </ul>
            <?php
            if ( true === $answered ) {
                if ( !empty($question['solution']) ) {
                // nomes mostrem la resposta si l'usuari ha respost la pregunta
                echo '<div class="alert alert-info"><p><strong>La resposta correcta és: </strong></p><p>'. $question['solution'] .'</p></div>';
                }
            } else {
                echo '<div class="alert alert-warning"><p>No veus la solució per què no vas respondre aquesta pregunta.</p></div>';
            }
            ?>
        </div>
    </div>
    <?php
}

