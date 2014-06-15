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
 * @package    Upgrade
 * @author     Paco Orozco <paco_@_pacoorozco.info>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

require_once realpath(dirname(__FILE__) . '/../../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_DATABASE);

printf("<h1>Migrant a la versio: 2.2</h1>\n");

printf("<pre>\n");
printf("Modificant les taules de la versio: 2.1\n");

$tables = array();
$tables[] = "ALTER TABLE `badges` ADD `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
$tables[] = "ALTER TABLE `members` ADD `register_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
$tables[] = "ALTER TABLE `members` CHANGE `last_access` `last_access` TIMESTAMP NULL;";
$tables[] = "ALTER TABLE `members_badges` CHANGE `last_time` `last_time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
$tables[] = "ALTER TABLE `members_questions` CHANGE `last_time` `last_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
$tables[] = "ALTER TABLE `points` CHANGE `date` `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
$tables[] = "ALTER TABLE `questions` ADD `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
$tables[] = "ALTER TABLE `questions` ADD `publish_time` TIMESTAMP NULL;";

foreach ($tables as $query) {
    $db->query($query) 
        or printf("ERROR: Executing %s\n\tError code: ", $query, $db->error);
}

printf("completed!\n");

printf("\nMigrant dades...\n");
printf("   members.register_time: ");

$members = $db->getAll(
    "SELECT id FROM members WHERE register_time=0"
);
$pre_migracio_q = count($members);

$migrats = 0;
foreach ($members as $member) {  
    $member_id = $member['id'];

    $creation_time = $db->getOne(
        sprintf(
            "SELECT creation_time FROM points WHERE id_member='%d' ORDER BY creation_time ASC LIMIT 1", 
            $member_id
        )
    );
    
    if(is_null($creation_time)) {
        continue;
    }
    
    $migrats++;
    
    $db->update(
        'members',
        array(
            'register_time' => $creation_time
        ),
        sprintf("id='%d' LIMIT 1", $member_id)
    );
}
printf("updated (".$migrats."/".$pre_migracio_q.")!\n");

printf("   questions.creation_time: ");

$questions = $db->getAll(
    "SELECT id FROM questions WHERE creation_time=0"
);
$pre_migracio_q = count($questions);

$migrats = 0;
foreach ($questions as $question) {
    $question_id = $question['id'];
    $creation_time = $db->getOne(
        sprintf(
            "SELECT last_time FROM members_questions WHERE id_question='%d' ORDER BY last_time ASC LIMIT 1", 
            $question_id
        )
    );
    
    if(is_null($creation_time)) {
        continue;
    }
    
    $migrats++;
    
    $db->update(
        'questions',
        array(
            'creation_time' => $creation_time,
            'publish_time' => $creation_time
        ),
        sprintf("id='%d' LIMIT 1", $question_id)
    );
}
printf("updated (".$migrats."/".$pre_migracio_q.")!\n");

printf("   badges.creation_time: ");

$creation_time = $db->getOne(
    "SELECT creation_time FROM points ORDER BY creation_time ASC LIMIT 1"
);

$db->update(
    'badges',
    array(
        'creation_time' => $creation_time,
    ),
    "creation_time=0"
);

printf("updated!\n");


$tables = array();
$tables[] = "DROP VIEW IF EXISTS `vtop_month`;";
$tables[] = "CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vtop_month AS 
SELECT members.id AS id, SUM(points.points) AS points 
FROM (members LEFT JOIN points ON (members.id = points.id_member))
WHERE points.creation_time > DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY members.id;";
$tables[] = "DROP VIEW IF EXISTS `vmembers`;";
$tables[] = "CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vmembers AS
SELECT members.id AS id, members.uuid, members.username AS username, members.email AS email, members.role AS role, members.level_id AS level_id, (SELECT name FROM levels WHERE id=members.level_id) AS level_name, (SELECT COUNT(*) FROM members_badges WHERE id_member=members.id AND status='completed') AS badges, members.disabled AS disabled, vtop.points AS total_points, vtop_month.points AS month_points 
FROM ((members LEFT JOIN vtop ON (members.id = vtop.id)) LEFT JOIN vtop_month ON (members.id = vtop_month.id));";

printf("Creant vistes\n");

foreach ($tables as $query) {
    $db->query($query) 
        or printf("ERROR: Executing %s\n\tError code: ", $query, $db->error);
}

printf("completed!\n");

printf("</pre>\n");
printf("<h2>L'actualitzacio a la v2.2 ha estat un exit</h2>\n");

exit();
