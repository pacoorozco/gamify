<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: gamify.inc.php 65 2014-04-21 18:09:54Z paco $
 *
 */

// Check if this is a valid include
defined('IN_SCRIPT') or die('Invalid attempt');

/*** FUNCTIONS ***/
function doSilentAddExperience ( $userId, $experience, $memo = '' ) {
    global $db;

    // validate data
    $data['id'] = intval($userId);
    $data['experience'] = intval($experience);
    $data['memo'] = $memo;

    if ( false === getUserExists($data['id']) ) {
        // L'usuari que ens han passat no existeix, per tant tornem a mostrar la llista.
        return false;
    }

    if ( empty($data['experience']) ) {
        return false;
    }

    if ( empty($data['memo']) ) {
        $data['memo'] = "alguna ra&oacute; desconeguda.";
    }

    // get the current level, before adding points
    $query = sprintf("SELECT level_id FROM vmembers WHERE id = '%d' LIMIT 1", $data['id']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $oldLevel = $row['level_id'];

    // adds experience to user
    $query = sprintf("INSERT INTO points SET id_member='%d', points='%d', memo='%s'", $data['id'], $data['experience'], $db->real_escape_string($data['memo']));
    $result = $db->query($query);

    if ( !$result ) {
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

    if ( $oldLevel != $data['level_id'] ) {
        $query = sprintf( "UPDATE members SET level_id='%d' WHERE id = '%d' LIMIT 1", $data['level_id'], $data['id'] );
        $result = $db->query($query);
        // Send a mail to user in order to tell him/her, his/her new level
        notifyLevelToUser($userId, $data['level_id']);
    }

    return true;
}

function doSilentAction( $userId, $actionId ) {
    global $db;

    $missatges = array();

    // validate data
    $data = array();
    $data['id_member'] = intval($userId);
    $data['id_badge'] = intval($actionId);
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
                doSilentAddExperience( $userId, 5, 'desbloquejar la ins&iacute;gnia: '. $data['name'] );
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
}


function notifyBadgeToUser( $data = array() ) {
    global $CONFIG;

    $badgeName = $data['name'];
    $badgeImage = sprintf("%s/images/badges/%s", $CONFIG['site']['base_url'], $data['image']);
    $userProfile = sprintf("%s/member.php?a=viewuser&item=%s",
                             $CONFIG['site']['base_url'], getUserUUID($data['id_member']));

    $subject = 'Has aconseguit una nova insígnia a GoW!';
    $mailBody = <<<BADGE_MAIL
<div style="text-align:center;">
<h2>Enhorabona, acabes d'aconseguir una nova insígnia</h2>
<img src="$badgeImage">
<h3>Ins&iacute;gnia: $badgeName</h3>
<p style="padding-bottom: 10px;">Pots veure el teu perfil <a href="$userProfile">aquí</a>.</p>
</div>
BADGE_MAIL;

    // Send the message
    return sendMessage($subject, $mailBody, $data['email']);
}

function notifyLevelToUser($userId, $levelId) {
    global $CONFIG, $db;
    
    $query = sprintf("SELECT email FROM vmembers WHERE id='%d' LIMIT 1", $userId);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $userEmail = $row['email'];
     
    $query = sprintf(
            "SELECT name FROM levels WHERE id='%d' LIMIT 1",
            $levelId
            );
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    
    $levelName = $row['name'];
    $levelImage = getLevelImage($levelId, true);
    $userProfile = sprintf(
            "%s/member.php?a=viewuser&item=%s",
            $CONFIG['site']['base_url'],
            getUserUUID($userId)
            );

    $subject = 'Has pujat de nivell a GoW!';
    $mailBody = <<<LEVEL_MAIL
<div style="text-align:center;">
<h2>Enhorabona, acabes de pujar de nivell.</h2>
<img src="$levelImage">
<h3>Ets un $levelName</h3>
<p style="padding-bottom: 10px;">Pots veure el teu perfil <a href="$userProfile">aquí</a>.</p>
</div>
LEVEL_MAIL;

    // Send the message
    return sendMessage($subject, $mailBody, $userEmail);
}

function getLevelImage($levelId, $withURL = false) {
    global $db, $CONFIG;
    
    $query = sprintf(
            "SELECT image FROM levels WHERE id='%d' LIMIT 1",
            $levelId
            );
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    
    $imagePath = sprintf("%s/%s", $CONFIG['site']['uploads'], $row['image']);
    if (true === $withURL) {
        // returns absolute path
        return sprintf("%s/%s", $CONFIG['site']['base_url'], $imagePath);
    }
    return $imagePath;   
}

function getUserLevelById($userId) {
    global $db;

    $query = sprintf( "SELECT level_id FROM vmembers WHERE id='%d' LIMIT 1", intval($userId));
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['level_id'];
}

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
}

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
}

function getUserUUID( $userId ) {
    global $db;

    $query = sprintf("SELECT uuid FROM vmembers WHERE id='%d' LIMIT 1", $userId);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['uuid'];
}

function getUserId( $userUUID ) {
    global $db;

    $query = sprintf("SELECT id FROM vmembers WHERE uuid='%s' LIMIT 1", $userUUID);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['id'];
}

function getBadgeAssignements($badgeId) {
    global $db;

    $query = sprintf("SELECT COUNT(id_member) AS assignements FROM members_badges WHERE id_badges='%d' AND status='completed'", $badgeId);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['assignements'];    
}

function getLevelAssignements($levelId) {
    global $db;

    $query = sprintf("SELECT COUNT(id) AS assignements FROM vmembers WHERE level_id='%d'", $levelId);
    $result = $db->query($query);
    if (0 == $result->num_rows ) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['assignements'];    
}
