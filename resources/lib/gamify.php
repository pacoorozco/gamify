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
 * @package    Gamify
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

/**
 * Adds XP to an user. If a new level has reached it will notify to user
 *
 * @param integer $userId
 * @param integer $experience
 * @param string $memo
 * @return boolean
 */
function doSilentAddExperience($userId, $experience, $memo = '')
{
    global $db;

    // validate data
    $userId = intval($userId);
    $experience = intval($experience);
    $memo = empty($memo) ? 'alguna ra&oacute; desconeguda.' : $memo;

    if (!getUserExists($userId) || empty($experience)) {
        // Parametres incorrectes
        return false;
    }

    // get the current level, before adding points
    $oldLevel = getUserLevelById($userId);

    // adds experience to user
    $db->insert(
        'points',
        array(
            'id_member' => $userId,
            'points' => $experience,
            'memo' => $memo
        )
    );

    // get the current level, after adding points
    $newLevel = getUserLevelById($userId);

    if ($oldLevel != $newLevel) {
        // Updates level_id if something has changed
        if (!$db->update(
            'members',
            array(
                'level_id' => $newLevel
            ),
            sprintf("id='%d' LIMIT 1", $userId)
        )) {
            // ERROR
            return false;
        }
        // Send a mail to user in order to tell him/her, his/her new level
        notifyLevelToUser($userId, $newLevel);
    }
    return true;
}

/**
 * Adds an action to a user. It will notify if a badge is completed
 *
 * @param integer $userId
 * @param integer $badgeId
 * @param integer $amount
 * @return boolean
 */
function doSilentAction($userId, $badgeId, $amount = 1)
{
    global $db;

    if (!getUserExists($userId) || !getBadgeExists($badgeId)) {
        // L'usuari o el badge que ens han passat no existeixen
        return false;
    }

    $badgeAmountNeeded = $db->getOne(
        sprintf(
            "SELECT `amount_needed` FROM `badges` "
            . "WHERE `id`='%d' AND `status`='active' LIMIT 1",
            $badgeId
        )
    );
    $badgeAmountCompleted = $db->getOne(
        sprintf(
            "SELECT `amount` FROM `members_badges` "
            . "WHERE `id_member`='%d' AND `id_badges`='%d' LIMIT 1",
            $userId,
            $badgeId
        )
    );

    if (is_null($badgeAmountCompleted)) {
        $badgeAmountCompleted = 0;
        // this action has not been initiated to this user
        if (!$db->insert(
            'members_badges',
            array(
                'id_member' => $userId,
                'id_badges' => $badgeId,
                'amount' => 0
            )
        )) {
            // ERROR
            return false;
        }
    }
    // At this point action is initiated for user
    $badgeAmountCompleted = intval($badgeAmountCompleted);
    $oldStatus = ($badgeAmountCompleted >= $badgeAmountNeeded) ? 'completed' : 'active';
    $badgeAmountCompleted += intval($amount);
    $newStatus = ($badgeAmountCompleted >= $badgeAmountNeeded) ? 'completed' : 'active';

    if (!$db->update(
        'members_badges',
        array(
            'amount' => $badgeAmountCompleted,
            'status' => $newStatus
        ),
        sprintf("id_member='%d' AND id_badges='%d' LIMIT 1", $userId, $badgeId)
    )) {
        // ERROR
        return false;
    }

    if (($oldStatus != $newStatus) && ('completed' == $newStatus)) {
        // A badge has been completed, notify it to user
        $memo = sprintf(
            "desbloquejar la insígina: %s",
            getBadgeNameById($badgeId)
        );
        doSilentAddExperience($userId, 5, $memo);
        notifyBadgeToUser($userId, $badgeId);
        return $badgeId;
    }
    return true;
}

function notifyBadgeToUser($userId, $badgeId)
{
    global $CONFIG;

    $userEmail = getUserEmailById($userId);
    $badgeName = getBadgeNameById($badgeId);
    $badgeImage = getBadgeImageById($badgeId, true);

    $userProfile = sprintf(
        "%s/member.php?a=viewuser&item=%s",
        $CONFIG['site']['base_url'],
        getUserUUIDById($userId)
    );

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
    return sendMessage($subject, $mailBody, $userEmail);
}

function notifyLevelToUser($userId, $levelId)
{
    global $CONFIG;

    $userEmail = getUserEmailById($userId);
    $levelName = getLevelNameById($levelId);
    $levelImage = getLevelImageById($levelId, true);

    $userProfile = sprintf(
        "%s/member.php?a=viewuser&item=%s",
        $CONFIG['site']['base_url'],
        getUserUUIDById($userId)
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

function getUserEmailById($userId)
{
    global $db;
    return $db->getOne(
        sprintf(
            "SELECT `email` FROM `vmembers` WHERE `id`='%d' LIMIT 1",
            $userId
        )
    );
}

function getLevelNameById($levelId)
{
    global $db;
    return $db->getOne(
        sprintf(
            "SELECT `name` FROM `levels` WHERE `id`='%d' LIMIT 1",
            $levelId
        )
    );
}

function getBadgeImageById($badgeId, $withURL = false)
{
    global $db, $CONFIG;
    $image = $db->getOne(
        sprintf(
            "SELECT `image` FROM `badges` WHERE `id`='%d' LIMIT 1",
            intval($badgeId)
        )
    );
    $imagePath = sprintf("%s/%s", $CONFIG['site']['uploads'], $image);
    if ($withURL) {
        // returns absolute path
        return sprintf("%s/%s", $CONFIG['site']['base_url'], $imagePath);
    }
    return $imagePath;
}

function getLevelImageById($levelId, $withURL = false)
{
    global $db, $CONFIG;
    $image = $db->getOne(
        sprintf(
            "SELECT `image` FROM `levels` WHERE `id`='%d' LIMIT 1",
            intval($levelId)
        )
    );
    $imagePath = sprintf("%s/%s", $CONFIG['site']['uploads'], $image);
    if ($withURL) {
        // returns absolute path
        return sprintf("%s/%s", $CONFIG['site']['base_url'], $imagePath);
    }
    return $imagePath;
}

function getQuestionAverage($questionUUID)
{
    global $db;
    $query = sprintf(
        "SELECT AVG(t1.amount) AS average "
        . "FROM members_questions AS t1, questions AS t2 "
        . "WHERE t1.id_question = t2.id AND t2.uuid = '%s'",
        $questionUUID
    );
    $result = $db->query($query);
    if (0 == $result->num_rows) {
        return false;
    }
    $row = $result->fetch_assoc();

    return $row['average'];
}

function getQuestionResponses($questionUUID)
{
    global $db;
    return $db->getOne(
        sprintf(
            "SELECT COUNT(t1.id_member) AS responses "
            . "FROM members_questions AS t1, questions AS t2 "
            . "WHERE t1.id_question = t2.id AND t2.uuid = '%s'",
            $db->qstr($questionUUID)
        )
    );
}

function getQuestionLink($questionId)
{
    global $CONFIG;
    
    return sprintf(
        "%s/quiz.php?a=answerqz&item=%s",
        $CONFIG['site']['base_url'],
        getQuestionUUIDById($questionId)
    );
}

function getQuestionName($questionId)
{
    global $db;
    return $db->getOne(
        sprintf("SELECT name FROM questions WHERE id='%d' LIMIT 1", $questionId)
    );
}

function getQuestionUUIDById($questionId)
{
    global $db;
    return $db->getOne(
        sprintf("SELECT uuid FROM questions WHERE id='%d' LIMIT 1", $questionId)
    );
}

function getUserUUIDById($userId)
{
    global $db;
    return $db->getOne(
        sprintf("SELECT uuid FROM vmembers WHERE id='%d' LIMIT 1", $userId)
    );
}

function getUserId($userUUID)
{
    global $db;
    return $db->getOne(
        sprintf("SELECT id FROM vmembers WHERE uuid='%s' LIMIT 1", $db->qstr($userUUID))
    );
}

function getBadgeAssignements($badgeId)
{
    global $db;
    return $db->getOne(
        sprintf(
            "SELECT COUNT(id_member) AS assignements FROM members_badges "
            . "WHERE id_badges='%d' AND status='completed'",
            $badgeId
        )
    );
}

/**
 * Calculates user's level and returns it level_id
 *
 * @param integer $userId The user's id to calculates level
 * @return interger
 */
function getUserLevelById($userId)
{
    global $db;
    return $db->getOne(
        sprintf(
            "SELECT id FROM levels "
            . "WHERE experience_needed <= "
            . "(SELECT SUM(points) FROM points WHERE id_member=%d) "
            . "ORDER BY experience_needed DESC LIMIT 1",
            $userId
        )
    );
}

function getLevelAssignements($levelId)
{
    global $db;
    return $db->getOne(
        sprintf(
            "SELECT COUNT(id) AS assignements FROM vmembers WHERE level_id='%d'",
            $levelId
        )
    );
}

function userHasDesafiament($userId)
{
    global $db;
    
    $desafiament = $db->getOne(
        sprintf(
            "SELECT status FROM members_badges "
            . "WHERE id_member='%d' AND id_badges='29' AND status='completed' LIMIT 1",
            intval($userId)
        )
    );
    
    return !empty($desafiament);
}

function getPendingQuizs($userId)
{
    global $db;
    
    $sql = sprintf(
            "SELECT count(*) AS pending FROM questions "
            . "WHERE status='active' AND "
            . "id NOT IN (SELECT id_question FROM members_questions WHERE id_member='%d')",
            intval($userId)
        );
    
    // Primer cal mirar si l'usuari te el badge desafiament
    if (userHasDesafiament($userId)) {
        // Ha completat el desafiament
        $sql = sprintf(
            "SELECT count(*) AS pending FROM questions "
            . "WHERE status='active' AND creation_time > '2014-12-06 01:00:00' AND "
            . "id NOT IN (SELECT id_question FROM members_questions WHERE id_member='%d')",
            intval($userId)
        );
    } 
    
    $pending = $db->getOne($sql);

    return ( $pending > 0 ) ? $pending : '';
}

/**
 * Returns true if supplied user exists
 * $user parameter could be an user_id or username
 *
 * @param mixed $user
 * @return bool
 */
function getUserExists($user)
{
    if (is_numeric($user)) {
        return getUserNameById($user) ? true : false;
    } else {
        return getUserIdByName($user) ? true : false;
    }
}

/**
 * Returns the username from an user
 *
 * @param integer $userId
 * @return string FALSE if user doesn't exists
 */
function getUserNameById($userId)
{
    global $db;
    $username = $db->getOne(
        sprintf(
            "SELECT `username` FROM `vmembers` WHERE `id`='%d' LIMIT 1",
            intval($userId)
        )
    );
    return is_null($username) ? false : $username;
}

/**
 * Returns the user_id from an user
 *
 * @param string $username
 * @return integer FALSE if user doesn't exists
 */
function getUserIdByName($username)
{
    global $db;
    $userId = $db->getOne(
        sprintf(
            "SELECT `id` FROM `vmembers` WHERE `username`='%s' LIMIT 1",
            $db->qstr($username)
        )
    );
    return is_null($userId) ? false : $userId;
}

/**
 * Returns if a badge exists or not
 *
 * @param integer $badgeId
 * @return bool
 */
function getBadgeExists($badgeId)
{
    return getBadgeNameById($badgeId) ? true : false;
}

/**
 * Returns the name of a badge
 *
 * @param string $badgeId
 * @return integer FALSE if badge doesn't exists
 */
function getBadgeNameById($badgeId)
{
    global $db;
    $badgeName = $db->getOne(
        sprintf(
            "SELECT `name` FROM `badges` WHERE `id`='%d' LIMIT 1",
            intval($badgeId)
        )
    );
    return is_null($badgeName) ? false : $badgeName;
}

function getLevelExists($levelId)
{
    global $db;
    $levelName = $db->getOne(
        sprintf("SELECT name FROM levels WHERE id='%d' LIMIT 1", intval($levelId))
    );
    return is_null($levelName) ? false : true;
}
