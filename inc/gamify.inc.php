<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: gamify.inc.php 65 2014-04-21 18:09:54Z paco $
 *
 */

// Check if this is a valid include
defined('IN_SCRIPT') or die('Invalid attempt');

/*** FUNCTIONS ***/
function doSilentAddExperience ( $user_id, $experience, $memo = '' ) {
    global $db;

    // validate data
    $data['id'] = intval($user_id);
    $data['experience'] = intval($experience);
    $data['memo'] = $memo;

    if ( false === user_exists($data['id']) ) {
        // L'usuari que ens han passat no existeix, per tant tornem a mostrar la llista.
        // $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquest usuari.");
        return false;
    }

    if ( empty($data['experience']) ) {
        // $missatges[] = array('type' => "error", 'msg' => "El camp experiència és obligatori.");
        return false;
    }

    if ( empty($data['memo']) ) {
        $data['memo'] = "alguna ra&oacute; desconeguda.";
    }

    // get the current level, before adding points
    $query = sprintf("SELECT level_id FROM vmembers WHERE id = '%d' LIMIT 1", $data['id']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $old_level = $row['level_id'];

    // adds experience to user
    $query = sprintf("INSERT INTO points SET id_member='%d', points='%d', memo='%s'", $data['id'], $data['experience'], $db->real_escape_string($data['memo']));
    $result = $db->query($query);

    if ( !$result ) {
        // $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de l'usuari '<strong>". $data['username'] ."</strong>'.");
        return false;
    }

    // get the current level, after adding points
    $query = sprintf("SELECT id, username, email, total_points FROM vmembers WHERE id = '%d' LIMIT 1", $data['id']);
    $result = $db->query($query);
    $data = $result->fetch_assoc();

    $query = sprintf("SELECT id, name, image FROM levels WHERE experience_needed = (SELECT MAX(experience_needed) FROM levels WHERE experience_needed <= '%d') LIMIT 1", $data['total_points']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $data['level_id'] = $row['id'];
    $data['name'] = $row['name'];
    $data['image'] = $row['image'];

    if ( $old_level != $data['level_id'] ) {
        $query = sprintf( "UPDATE members SET level_id='%d' WHERE id = '%d' LIMIT 1", $data['level_id'], $data['id'] );
        $result = $db->query($query);
        // Send a mail to user in order to tell him/her, his/her new level
        notifyLevelToUser($data);
    }

    return true;
} // END silent_add_experience()

function doSilentAction( $user_id, $action_id ) {
    global $db;

    $missatges = array();

    // validate data
    $data = array();
    $data['id_member'] = intval($user_id);
    $data['id_badge'] = intval($action_id);
    $data['amount'] = 1;

    // Get user data from DB
    $query = sprintf( "SELECT username, email FROM vmembers WHERE id='%d' LIMIT 1", $data['id_member'] );
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // L'usuari que ens han passat no existeix.
        return false;
    }

    $row = $result->fetch_assoc();
    $data['username'] = $row['username'];
    $data['email'] = $row['email'];

    // Get badge data from DB
    $query = sprintf( "SELECT name, amount_needed, image FROM badges WHERE id='%d' AND status='active' LIMIT 1", $data['id_badge'] );
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // La insígnia que ens han passat no existeix.
        return false;
    }

    $row = $result->fetch_assoc();
    $data['name'] = $row['name'];
    $data['image'] = $row['image'];
    $data['amount_needed'] = $row['amount_needed'];

    $status = 'active';
    $query = sprintf("SELECT * FROM members_badges WHERE id_member='%d' AND id_badges='%d' LIMIT 1", $data['id_member'], $data['id_badge']);
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // this action has not been initiated to this user
        if ( $data['amount'] >= $data['amount_needed'] ) {
            $status = 'completed';
        }
        $query = sprintf("INSERT INTO members_badges SET id_member='%d', id_badges='%d', amount='%d', last_time='%d', status='%s'",
                          $data['id_member'], $data['id_badge'], $data['amount'], time(), $status );

        if ( $db->query($query) && ( 'completed' == $status ) ) {
                doSilentAddExperience( $user_id, 5, 'desbloquejar la ins&iacute;gnia: '. $data['name'] );
                // send a mail to user in order to tell him/her, his/her new badge
                notifyBadgeToUser($data);
                return $data['id_badge'];
        }
        return false;
    }

    $row = $result->fetch_assoc();
    $data['id'] = $row['id'];

    // checking if badge is not completed yet
    if ( 'active' == $row['status'] ) {
        // update amount in order to complete this badge.
        $data['amount'] += $row['amount'];

        // TODO - check if needed period of time is passed

        // check if badge has completed
        if ( $data['amount'] >= $data['amount_needed'] ) {
            // complete badge
            $status = 'completed';

            $query = sprintf( "UPDATE members_badges SET amount='%d', status='%s', last_time='%d' WHERE id='%d' LIMIT 1",
                               $data['amount'], $status, time(), $data['id'] );

            if ( $db->query($query) ) {
                // send a mail to user in order to tell him/her, his/her new achievement
                notifyBadgeToUser($data);
                return $data['id_badge'];
            } else {
                return false;
            }
        } else {
            // update amount of this badges
            $query = sprintf( "UPDATE members_badges SET amount='%d', last_time='%d' WHERE id = '%d' LIMIT 1",
                               $data['amount'], time(), $data['id'] );

            $db->query($query);
            return false;
        }
    } else {
        return false;
    }
} // silent_action()


function notifyBadgeToUser( $data = array() ) {
    global $CONFIG;

    $badge_name = $data['name'];
    $badge_image = sprintf("%s/images/badges/%s", $CONFIG['site']['base_url'], $data['image']);
    $user_profile = sprintf("%s/member.php?a=viewuser&item=%s",
                             $CONFIG['site']['base_url'], getUserUUID($data['id_member']));

    $subject = 'Has aconseguit una nova insígnia a GoW!';
    $mail_body = <<<BADGE_MAIL
<div style="text-align:center;">
<h2>Enhorabona, acabes d'aconseguir una nova insígnia</h2>
<img src="$badge_image">
<h3>Ins&iacute;gnia: $badge_name</h3>
<p style="padding-bottom: 10px;">Pots veure el teu perfil <a href="$user_profile">aquí</a>.</p>
</div>
BADGE_MAIL;

    // Send the message
    return send_message($subject, $mail_body, $data['email']);
} // END notify_badge_2_user()

function notifyLevelToUser( $data = array() ) {
    global $CONFIG;

    $level_name = $data['name'];
    $level_image = sprintf("%s/images/levels/%s", $CONFIG['site']['base_url'], $data['image']);
    $user_profile = sprintf("%s/member.php?a=viewuser&item=%s",
                             $CONFIG['site']['base_url'], getUserUUID($data['id_member']));

    $subject = 'Has pujat de nivell a GoW!';
    $mail_body = <<<LEVEL_MAIL
<div style="text-align:center;">
<h2>Enhorabona, acabes de pujar de nivell.</h2>
<img src="$level_image">
<h3>Ets un $level_name</h3>
<p style="padding-bottom: 10px;">Pots veure el teu perfil <a href="$user_profile">aquí</a>.</p>
</div>
LEVEL_MAIL;

    // Send the message
    return send_message($subject, $mail_body, $data['email']);
} // END notify_badge_2_user()

function getUserLevelById($user_id) {
    global $db;

    $query = sprintf( "SELECT level_id FROM vmembers WHERE id='%d' LIMIT 1", intval($user_id));
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['level_id'];
} // END get_user_level()

function getQuestionAverage( $questionUUID ) {
    global $db;
    
    $query = sprintf("SELECT AVG(t1.amount) AS average
        FROM members_questions AS t1, questions AS t2
        WHERE t1.id_question = t2.id
        AND t2.uuid = '%s'", $questionUUID);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['average'];
} // END getQuestionStats()

function getQuestionResponses( $questionUUID ) {
    global $db;
    
    $query = sprintf("SELECT COUNT(t1.id_member) AS responses
        FROM members_questions AS t1, questions AS t2
        WHERE t1.id_question = t2.id
        AND t2.uuid = '%s'", $questionUUID);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['responses'];
} // END getQuestionStats()

function getUserUUID( $userId ) {
    global $db;
    
    $query = sprintf("SELECT uuid FROM vmembers WHERE id='%d' LIMIT 1", $userId);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['uuid'];
} // END getUserUUID()

function getUserId( $userUUID ) {
    global $db;
    
    $query = sprintf("SELECT id FROM vmembers WHERE uuid='%s' LIMIT 1", $userUUID);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['id'];
} // END getUserId()

