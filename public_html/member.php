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
 * @package    Member
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

// Que hem de fer?
$action = getREQUESTVar('a');

// There are some actions that doesn't need header / footer
switch ($action) {
    case 'search':
        // Que hem de buscar?
        echo getSearchResults(getGETVar('q'));
        exit();
        break;
    case 'upload':
        echo uploadProfilePicture();
        exit();
        break;
}

require_once TEMPLATES_PATH . '/tpl_header.inc';

switch ($action) {
    case 'viewuser':
    default:
        // if not suply any user to show, show the own ones
        $userUUID = getREQUESTVar('item');
        $userUUID = empty($userUUID)
            ? getUserUUIDById($session->get('member.id'))
            : getREQUESTVar('item');
        printProfile($userUUID);
}

require_once TEMPLATES_PATH . '/tpl_footer.inc';
exit();

/*** FUNCTIONS ***/
function getSearchResults($searchterm)
{
    global $db;

    $htmlCode = array();
    $htmlCode[] = '<ul class="list-unstyled list-group">';

    // Nomes farem cerques si busquen mes de tres caracters, aixo evita que sobrecarreguem la BDD
    if (!isset($searchterm[3])) {
        $htmlCode[] = '<li class="list-group-item list-group-item-info">'
            . 'Tecleja m&eacute;s de 3 car&agrave;cters per fer la cerca</li>';
    } else {
        $searchResult = $db->getAll(
            sprintf(
                "SELECT uuid, username FROM vmembers WHERE username LIKE '%%%s%%'",
                $db->qstr($searchterm)
            )
        );

        if (is_null($searchResult)) {
            // No s'ha trobat res
            $htmlCode[] = '<li class="list-group-item list-group-item-danger">'
                . 'No he trobat cap resultat</li>';
        } else {
            // Hem trobat informacio
            foreach ($searchResult as $row) {
                $htmlCode[] = '<li><a href="member.php?a=viewuser&item='
                    . $row['uuid'] . '" title="Veure ' . $row['username']
                    . '" class="list-group-item"><span class="glyphicon glyphicon-user"></span> '
                    . $row['username'] . "</a></li>";
            }
        }
    }
    $htmlCode[] = '</ul>';

    return implode($htmlCode, PHP_EOL);
}

/**
 * Prints profile page for a given user
 *
 * @param string $userUUID
 */
function printProfile($userUUID)
{
    global $db, $session;

    $row = $db->getRow(
        sprintf(
            "SELECT t1.id, t1.username, t1.total_points, t1.month_points, t1.last_access, "
            . "t1.level_id, t2.name AS level_name, t2.image AS level_image, t2.experience_needed "
            . "FROM vmembers AS t1, levels AS t2 "
            . "WHERE t1.level_id = t2.id AND t1.uuid='%s' LIMIT 1",
            $userUUID
        )
    );
    $userId = $row['id'];

    // check if user to view profile is admin
    $admin = userHasPrivileges($userId, 'administrator');

    $row['profile_image'] = $db->getOne(
        sprintf("SELECT profile_image FROM members WHERE id='%d' LIMIT 1", $userId)
    );
    
    if (empty($row['profile_image'])) {
        $row['profile_image'] = 'images/default_profile_pic.png';
    }

    if ($admin) {
        require_once TEMPLATES_PATH . '/tpl_profile_admin.inc';
    } else {
        $row2 = $db->getRow(
            sprintf(
                "SELECT * FROM levels WHERE experience_needed >= '%d' LIMIT 1",
                $row['total_points']
            )
        );

        $levelper= round($row['total_points'] / $row2['experience_needed'] * 100);
        $htmlEventsCode = array();
        $events = $db->getAll(
            sprintf(
                "SELECT * FROM points WHERE id_member='%d' ORDER BY creation_time DESC LIMIT 10",
                $userId
            )
        );

        foreach ($events as $row3) {
            $htmlEventsCode[] = sprintf(
                "<p>%s va rebre <strong>%d punts</strong> d'experiència per <em>%s</em></p>",
                getElapsedTimeString($row3['creation_time']),
                $row3['points'],
                $row3['memo']
            );
        }
        $badges = $db->getOne(
            sprintf(
                "SELECT COUNT(*) AS completed FROM members_badges "
                . "WHERE id_member='%d' AND status='completed'",
                $userId
            )
        );
        $htmlBadgesCode = getHTMLBadges($userId);
        require_once TEMPLATES_PATH . '/tpl_profile_member.inc';
    }
}

/**
 * Return HTML code to print badges of a give user
 *
 * @param int $userId Given user to show badges
 * @return string HTML code to echo
 */
function getHTMLBadges($userId)
{
    global $db, $session;

    $htmlCode = array();
    $currentUserId = $session->get('member.id');

    $badgeList = $db->getAll(
        sprintf(
            "SELECT t1.image, t1.name, t1.description, t1.amount_needed, "
            . "t2.amount, t2.status FROM badges AS t1, members_badges AS t2 "
            . "WHERE t2.id_member='%d' AND t1.id=t2.id_badges",
            $userId
        )
    );

    foreach ($badgeList as $row) {
        $progress = '';
        $achievesToBadge = $row['amount_needed'] - $row['amount'];
        if ($userId == $currentUserId
            && $row['amount_needed'] > 1
            && $achievesToBadge > 0) {
            $progress = sprintf(
                "\n\nMuy bien! Tienes %d %s, sólo te %s %d más.",
                $row['amount'],
                ($row['amount'] > 1) ? 'logros' : 'logro',
                ($achievesToBadge > 1) ? 'faltan' : 'falta',
                $achievesToBadge
            );
        }
        $title = sprintf("%s\n%s%s", $row['name'], $row['description'], $progress);
        $htmlCode[] = '<a href="#" title="' . $title . '">';
        $image = ('completed' == $row['status']) ? $row['image'] : 'images/default_badge_off.png';
        $htmlCode[] = '<img src="' . $image .'" alt="'. $row['name'] . '" class="img-thumbnail" width="80">';
        $htmlCode[] = '</a>';
    }

    return $htmlCode;
}

function uploadProfilePicture()
{
    global $CONFIG, $db, $session;

    # upload the file to the filesystem uploads dir
    $destinationPath = $CONFIG['site']['uploads'] . '/profiles';
    list($returnedValue, $returnedMessage) = uploadFile('uploadFile', $destinationPath);

    if (!$returnedValue) {
        return 'ERROR';
    }

    $userId = $session->get('member.id');

    // Deletes previous profile picture file
    $profileImage = $db->getOne(
        sprintf(
            "SELECT profile_image FROM members WHERE id='%d'",
            $userId
        )
    );
    if (file_exists($profileImage)) {
        unlink($profileImage);
    }

    // ACTION: Si es la primera vegada que puja una imatge... guanya un badge
    if (empty($profileImage)) {
        doSilentAction($userId, 19);
    }
    // END ACTION

    $db->update(
        'members',
        array(
            'profile_image' =>  $returnedMessage
        ),
        sprintf("id='%d' LIMIT 1", $userId)
    );

    // Modifica la imatge a la sessio actual
    $session->set('member.profile_image', $returnedMessage);

    return $returnedMessage;
}
