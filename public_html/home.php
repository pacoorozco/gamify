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
 * @package    Home
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Core\Bootstrap::init(APP_BOOTSTRAP_FULL);

require_once TEMPLATES_PATH . '/tpl_header.inc';

if (userIsLoggedIn()) {
    // Home for members!
    $htmlMonthTop = getHTMLRankingTable(
        $db->getAll(
            "SELECT * FROM vmembers "
            . "ORDER BY month_points DESC, badges DESC, username ASC"
        ),
        'month_points'
    );
    $htmlTop = getHTMLRankingTable(
        $db->getAll(
            "SELECT * FROM vmembers "
            . "ORDER BY total_points DESC, badges DESC, username ASC"
        ),
        'total_points'
    );
    $htmlTop10 = getHTMLRankingTable(
        $db->getAll(
            "SELECT * FROM vmembers "
            . "ORDER BY total_points DESC, badges DESC, username ASC"
        ),
        'total_points',
	10
    );
    require_once TEMPLATES_PATH . '/tpl_home_member.inc';
} else {
    // Home for anonymous
    $usertext = 'usuari';
    $logintext = 'Accedir';
    if ('LDAP' == $CONFIG['authentication']['type']) {
        $usertext = 'usuari LDAP';
        $logintext = 'Accedir amb LDAP';
    }
    require_once TEMPLATES_PATH . '/tpl_home_anonymous.inc';
}

require_once TEMPLATES_PATH . '/tpl_footer.inc';
exit();

/*** FUNCTIONS ***/
function getHTMLRankingTable($users = array(), $show = 'total_points', $top3 = 3)
{
    global $db, $session;

    $htmlCode = array();
    $htmlReturn = array();
    $top10 = 10;

    $position = 1;
    $currentranking = 0;
    $toprest = $top10 - $top3;

    foreach ($users as $row) {
        $row['points'] = $row[$show];
        $currentuser = '';
        if ($row['username'] == $session->get('member.username')) {
            $currentuser = "class='info'";
            $currentranking = $position;
        }
        $htmlCode[] = '<tr ' . $currentuser . '>';
        if ($position <= $top3) {
            $htmlCode[] = '<td class="text-center" style=" vertical-align: middle;">';
            $htmlCode[] = '<span class="badge alert-warning">';
            $htmlCode[] = sprintf(
                '<h%d>&nbsp; %d &nbsp;</h%d>',
                ($position + 2),
                $position,
                ($position + 2)
            );
            $htmlCode[] = '</span>';
            $htmlCode[] = '</td>';
        } else {
            $htmlCode[] = '<td class="text-center"  style=" vertical-align: middle;">' . $position . '</td>';
        }
        $htmlCode[] = '<td style=" vertical-align: middle;">';
        $htmlCode[] = '<a href="member.php?a=viewuser&item=' . $row['uuid'] . '">' . $row['username'] . '</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '<td style=" vertical-align: middle;">' . $row['points'] . '</td>';
        $htmlCode[] = '<td style=" vertical-align: middle;">' . $row['level_name'] . '</td>';
        $badges = ($row['badges'] > 0) ? '<span class="badge">' . $row['badges'] . '</span>' : '';
        $htmlCode[] = '<td style=" vertical-align: middle;" class="text-center">' . $badges . '</td>';
        $htmlCode[] = '</tr>';

        $ranking[$position] = implode(PHP_EOL, $htmlCode);
        unset($htmlCode);
        $position++;
    }

    if ($currentranking > 0 && $currentranking <= $top3) {
        for ($i = 1; $i <= $top10; $i++) {
            $htmlReturn[] = isset($ranking[$i]) ? $ranking[$i] : '';
        }
    } else {
        for ($i = 1; $i <= $top3; $i++) {
            $htmlReturn[] = isset($ranking[$i]) ? $ranking[$i] : '';
        }

        $htmlReturn[] = '<tr><td colspan="5" class="text-center">...</td></tr>';

        if ($currentranking + $toprest < $position) {
            $init = $currentranking - 1;
            $end = $currentranking+$toprest-2;
        } else {
            $init = $position-$toprest;
            $end = $position;
        }
        for ($i = $init; $i <= $end; $i++) {
            $htmlReturn[] = isset($ranking[$i]) ? $ranking[$i] : '';
        }
    }
    return $htmlReturn;
}
