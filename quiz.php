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
if ( false === login_check() ) {
    // save referrer to $_SESSION['nav'] for after login redirect
    $_SESSION['nav'] = urlencode($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']);
    header('Location: login.php');
    exit;
}

require_once('inc/header.inc.php');
// Que hem de fer?
$action = pakus_REQUEST('a');

switch ($action) {
    case 'answerqz':
        $quiz_id = pakus_REQUEST('item');
        answer_qz($quiz_id);
        break;

    case 'answer':
        $quiz_id = pakus_POST('item');
        $choices = pakus_POST('choices');
        answer( $quiz_id, $choices );
        break;

    case 'seeqz':
        $quiz_id = pakus_REQUEST('item');
        see_qz($quiz_id);
        break;

    case 'historic':
        list_questions_historic();
        break;

    case 'list':
    default:
        list_questions();
}
    
require_once('inc/footer.inc.php');
exit();

/*** FUNCTIONS ***/
function answer( $question_uuid, $answers ) {
    global $db;
    
    $html_code = array();
      
    $query = sprintf( "SELECT * FROM questions WHERE uuid='%s' LIMIT 1", $db->real_escape_string($question_uuid) );
    $result = $db->query($query);
    
    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        list_questions();
        return false;        
    }
    
    $question = $result->fetch_assoc();
    $question_id = $question['id'];
    
    if ( !empty($question['solution']) ) {
        $html_code[] = sprintf("<p>La resposta correcta és:</p><pre>%s</pre>", $question['solution']);
    }
    
    // Mirem si la pregunta ha estat resposta per aquest usuari
    $query = sprintf( "SELECT * FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1", 
            $_SESSION['member']['id'], 
            $question_id 
            );
    
    $result = $db->query($query);
    if ( $result->num_rows > 0 ) {
        // L'usuari ja ha respost la pregunta 
        see_qz($question_uuid);
        return;
    } 
    
    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $question_id);
    $result = $db->query($query);
    
    if ( 0 == $result->num_rows ) {
        list_questions();
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
    $query = sprintf("SELECT id_member FROM members_questions WHERE id_question='%d'", intval($question_id));
    $result = $db->query($query);
    if ($result->num_rows === 0) {
        // Es el primero, hay que dar badge
        silent_action(intval($_SESSION['member']['id']), 1);
    }
    // END ACTION
    
    $query = sprintf( "INSERT INTO members_questions SET id_member='%d', id_question='%d', amount='%d'",
            intval($_SESSION['member']['id']),
            intval($question_id),
            intval($points)
            );
    
    $db->query($query);
    
    $old_level = get_user_level($_SESSION['member']['id']);
    silent_add_experience( $_SESSION['member']['id'], $points, 'respondre la pregunta: '. $question['name'] );
    $new_level = get_user_level($_SESSION['member']['id']);
    
    if ($old_level != $new_level) {
        $query = sprintf("SELECT name FROM levels WHERE id='%d'", $new_level);
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $html_code[] = sprintf("<p><strong>Enhorabona!</strong> Acabes de pujar de nivell. Ara ets un <strong>'%s'</strong>.</p>", $row['name']);
    }
    
    // anem a veure si haig d'executar alguna accio
    $query = sprintf("SELECT * FROM questions_badges WHERE question_id='%d' AND ( type='always' OR type='%s' )", 
            $question_id,
            $type 
            );
    $result = $db->query($query);
    
    if ( $result->num_rows > 0 ) {
        // hi ha accions a realitzar
        while ( $row = $result->fetch_assoc() ) {
            if (silent_action($_SESSION['member']['id'], $row['badge_id']) == $row['badge_id']) {
                $query = sprintf("SELECT name FROM badges WHERE id='%d'", $row['badge_id']);
                $result2 = $db->query($query);
                $row2 = $result2->fetch_assoc();
                $html_code[] = sprintf("<p><strong>Enhorabona!</strong> Acabes d'aconseguir la insíginia <strong>'%s'</strong>.</p>", $row2['name']);
            }
        }
    }  
    
    print_quiz_header('question');    
    ?>

    <div class="panel panel-default" width="70%">
        <div class="panel-heading"><h2>Gràcies per la teva resposta</h2></div>
        <div class="panel-body">
            <p>La teva resposta ha obtingut una puntuació de <strong><?php echo $points; ?> punts</strong>.</p>
            <?php echo implode(PHP_EOL, $html_code); ?>
        </div>
    </div>
    <?php
}

function answer_qz( $question_uuid ) {
    global $db;
      
    $query = sprintf( "SELECT * FROM questions WHERE uuid='%s' AND ( status='active' OR status='hidden' ) LIMIT 1", $db->real_escape_string($question_uuid) );
    $result = $db->query($query);
    
    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        list_questions();
        return false;        
    }
    
    $question = $result->fetch_assoc();
    $question_id = $question['id'];
    
    // Mirem si la pregunta ha estat resposta per aquest usuari
    $query = sprintf( "SELECT * FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1", 
            $_SESSION['member']['id'], 
            $question_id 
            );
    
    $result = $db->query($query);
    if ( ($result->num_rows > 0) || ('inactive' == $question['status']) ) {
        // L'usuari ja ha respost la pregunta o aquest està tancada
        see_qz($question_uuid);
        return;
    }    
    
    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $question_id);
    $result = $db->query($query);
    
    if ( 0 == $result->num_rows ) {
        list_questions();
        return false;        
    }  
    
    $question['choices'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $question['choices'][] = $row;
    }
    
    if ( empty($question['image']) ) {
        $question['image'] = 'images/question_default.png';
    }
    
    print_quiz_header('question');
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
                        $html_code = array();
                        foreach ($question['choices'] as $choice) {
                            $html_code[] = '<li class="list-group-item"><label>';
                            $html_code[] = sprintf( $option, $choice['id'] );
                            $html_code[] = $choice['choice'];
                            $html_code[] = '</label></li>';
                            
                        }
                        echo implode(PHP_EOL, $html_code);
                    ?>
                </ul>
                <a href="//kbtic.upcnet.es/search?SearchableText=<?php echo $question['tip']; ?>" title="Buscar la resposta a la KBTic" class="btn btn-default" target="_blank"role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a la KBTic</a>
                <a href="//www.google.es/search?q=<?php echo $question['tip']; ?>" title="Buscar la resposta a Google" class="btn btn-default" target="_blank" role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a Google</a>
                <input type="hidden" name="item" value="<?php echo $question_uuid; ?>">
                <input type="hidden" name="a" value="answer">
                <button type="submit" class="btn btn-success pull-right"><span class="glyphicon glyphicon-save"></span> Guardar resposta</button>                
            </form>
        </div>
    </div>
    <?php
} // END answer_qz()

function print_quiz_header( $a = 'list' ) {
    ?>
            <h1>Participa</h1>
            
            <ul class="nav nav-tabs">
                <li<?php echo ( 'list' == $a ) ? ' class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=list">Contestar pendents</a></li>   
                <li<?php echo ( 'historic' == $a ) ? ' class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=historic">Revisar preguntes</a></li>   
                <li<?php echo ( 'question' == $a ) ? ' class="active"' : ' class="disabled"'; ?>><a href="#">Veure pregunta</a></li>   
            </ul>
    <?php
} // END print_quiz_header()

function list_questions() {
    global $db;
    
    $message = array();
    
    print_quiz_header('list');
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
                echo get_html_messages($message);
            } else {
                $html_code = array();
                
                $html_code[] = '<div class="list-group">';
                while ( $row = $result->fetch_assoc() ) {
                    $html_code[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=answerqz&item='. $row['uuid'] .'" class="list-group-item">';
                    if ( empty($row['image']) ) {
                        $html_code[] = '<img data-src="holder.js/120x120" class="img-rounded" alt="'. $row['name'] .'">';
                    } else {
                        $html_code[] = '<img src="'. $row['image'] .'" width="120" class="img-rounded" alt="'. $row['name'] .'">';
                    }
                    $html_code[] = '<span class="h3">'. $row['name'] .'</span>';
                    $html_code[] = '</a>';
                }
                $html_code[] = '</div>';
                
                echo implode(PHP_EOL, $html_code);
                unset($html_code);
            }   
} // END list_questions()

function list_questions_historic() {
    global $db;
    
    $message = array();
    
    print_quiz_header('historic');
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
                echo get_html_messages($message);
            } else {
                $html_code = array();
                
                $html_code[] = '<div class="list-group">';
                while ( $row = $result->fetch_assoc() ) {
                    $html_code[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=seeqz&item='. $row['uuid'] .'" class="list-group-item">';
                    if ( empty($row['image']) ) {
                        $html_code[] = '<img data-src="holder.js/120x120" class="img-rounded" alt="'. $row['name'] .'">';
                    } else {
                        $html_code[] = '<img src="'. $row['image'] .'" width="120" class="img-rounded" alt="'. $row['name'] .'">';
                    }
                    $html_code[] = '<span class="h3">'. $row['name'] .'</span>';
                    $html_code[] = '</a>';
                }
                $html_code[] = '</div>';
                
                echo implode(PHP_EOL, $html_code);
                unset($html_code);
            }        
}

function see_qz($question_uuid) {
    global $db;
    
    $query = sprintf( "SELECT * FROM questions WHERE uuid='%s' AND status != 'draft' LIMIT 1", $db->real_escape_string($question_uuid) );
    $result = $db->query($query);
    
    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        list_questions();
        return false;        
    }
    
    $question = $result->fetch_assoc();
    $question_id = $question['id'];
    
    // Mirem si la pregunta ha estat resposta per aquest usuari
    $query = sprintf( "SELECT * FROM members_questions WHERE id_member='%d' AND id_question='%d' LIMIT 1", 
            $_SESSION['member']['id'], 
            $question_id 
            );
    
    $result = $db->query($query);
    $answered = true;
    if ( 0 == $result->num_rows ) {
        // L'usuari no ha respost la pregunta 
        $answered = false;
    }    
    
    if ( ( false === $answered ) && ('active' == $question['status']) ) {
        // L'usuari no ha respost la pregunta i està oberta
        answer_qz($question_uuid);
        return;
    }       
    
    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $question_id);
    $result = $db->query($query);
    
    if ( 0 == $result->num_rows ) {
        list_questions();
        return false;        
    }  
    
    $question['choices'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $question['choices'][] = $row;
    }
    
    if ( empty($question['image']) ) {
        $question['image'] = 'images/question_default.jpg';
    }
    
    print_quiz_header('question');
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
                        $html_code = array();
                        foreach ($question['choices'] as $choice) {
                            $html_code[] = '<li class="list-group-item">';
                            if ( true === $answered ) {
                                if ( 'yes' == $choice['correct'] ) {
                                    $html_code[] = '<span class="glyphicon glyphicon-ok"></span>';
                                } else {
                                    $html_code[] = '<span class="glyphicon glyphicon-remove"></span>';
                                }
                            } else {
                                $html_code[] = '<span class="glyphicon glyphicon-question-sign"></span>'; 
                            }
                            $html_code[] = $choice['choice'];
                            $html_code[] = '</li>';
                            
                        }
                        echo implode(PHP_EOL, $html_code);
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
} // END see_qz()
?>