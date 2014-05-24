<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: upgrade.php 65 2014-04-21 18:09:54Z paco $
 *
 */

define('IN_SCRIPT',1);

// Llegim la configuracio de ssham i creem un array
$CONFIG = parse_ini_file('../gamify.conf', TRUE);

// Connectem amb la base de dades, i deixem obert el descriptor
$db = mysqli_connect( $CONFIG['mysql']['host'], $CONFIG['mysql']['user'],
            $CONFIG['mysql']['passwd'], $CONFIG['mysql']['database'] );
if ( !$db )
    die( '[ERROR] No he pogut connectar amb la base de dades (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() );

// Definim totes les taules que cal crear en aquesta versio. 2.0
$table = array();

$table['questions'] = "CREATE TABLE questions (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `question` text NOT NULL,
  `tip` varchar(255) DEFAULT NULL,
  `solution` text,
  `type` enum('single','multi') DEFAULT 'single',
  `status` enum('active','inactive','draft','hidden') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_uuid` (`uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

$table['questions_badges'] = "CREATE TABLE questions_badges (
  `question_id` int(10) unsigned NOT NULL,
  `badge_id` int(10) unsigned NOT NULL,
  `type` enum('success','fail','always') DEFAULT 'always',
  UNIQUE KEY `question-badge` (`question_id`,`badge_id`),
  KEY `badge_id` (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$table['questions_choices'] = "CREATE TABLE questions_choices (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `choice` varchar(255) NOT NULL,
  `correct` enum('yes','no') DEFAULT 'no',
  `points` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

$table['members_questions'] = "CREATE TABLE IF NOT EXISTS `members_questions` (
  `id_member` int(10) unsigned NOT NULL,
  `id_question` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `last_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_member`,`id_question`),
  KEY `id_question` (`id_question`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

printf("<h1>Migrant a la versio: 2.x</h1>\n");

printf("<pre>\n");

// El.liminem les taules si existeixen, primer les que tenen CONSTRAINTS
printf( "Creant les taules de la versio: 2.0\n");

$query = "DROP TABLE IF EXISTS members_questions, questions_choices, questions_badges, questions;";
$db->query($query)
        or die('[ERROR] No he pogut el.liminar les taules:  members_questions, questions_choices, questions_badges, questions (' . $db->errno .
 ') ' . $db->error );

foreach ($table as $current_table => $create_query) {
    $query = "DROP TABLE IF EXISTS ". $current_table .";";
    $db->query($query)
            or die('[ERROR] No he pogut el.liminar la taula: '. $current_table .' (' . $db->errno . ') ' . $db->error );

    $db->query($create_query)
            or die('[ERROR] Creant la taula: '. $current_table .' (' . $db->errno . ') ' . $db->error );

    printf( "   Taula '%s' creada.\n", $current_table);
}

printf("   Afegint FOREIGN KEYS... ");

$foreign = array();
$foreign[] = "ALTER TABLE `questions_badges`
  ADD CONSTRAINT questions_badges_ibfk_1 FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT questions_badges_ibfk_2 FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE ON UPDATE CASCADE;";
$foreign[] = "ALTER TABLE `questions_choices`
  ADD CONSTRAINT questions_choices_ibfk_1 FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE ON UPDATE CASCADE;";
$foreign[] = "ALTER TABLE `members_questions`
  ADD CONSTRAINT `members_questions_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `members_questions_ibfk_2` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

foreach ($foreign as $query) {
    $db->query($query);
}

printf("completed!\n");

printf( "\nMigrant dades de les taules anteriors...\n");

// Primer cal migrar la taula [questions]
$query = "SELECT * FROM members_quizs";
$result = $db->query($query);
$pre_migracio_q = $result->num_rows;


printf( "   quizs -> questions: ");
$query = "SELECT * FROM quizs";
$result2 = $db->query($query);
$pre_migracio = $result2->num_rows;

while ($data = $result2->fetch_assoc()) {
    // Per cada pregunta
    $data['type'] = ( 1 === intval($data['multianswer']) ) ? 'multi' : 'single';
    $data['status'] = ( 1 === intval($data['open']) ) ? 'active' : 'inactive';

    $query = sprintf("INSERT INTO questions SET uuid='%s', name='%s', image='%s', question='%s', tip='%s', solution='%s', type='%s', status='%s'",
            $db->real_escape_string(getNewUUID()),
            $db->real_escape_string($data['name']),
            $db->real_escape_string($data['url_image']),
            $db->real_escape_string($data['text']),
            $db->real_escape_string($data['tip']),
            $db->real_escape_string($data['correct_answer']),
            $data['type'],
            $data['status']
            );
    $db->query($query);
    $question_id = $db->insert_id;

    $data['choices'] = array();
    $data['points'] = array();

    $data['choices'][] = $data['question1'];
    $data['points'][] = $data['answer1'];

    $data['choices'][] = $data['question2'];
    $data['points'][] = $data['answer2'];

    $data['choices'][] = $data['question3'];
    $data['points'][] = $data['answer3'];

    $data['choices'][] = $data['question4'];
    $data['points'][] = $data['answer4'];

    $data['choices'][] = $data['question5'];
    $data['points'][] = $data['answer5'];

    // put choices into its table
    foreach ( $data['choices'] as $key => $value ) {

        // validate supplied data
        if ( empty($value) ) continue;
        $points = intval($data['points'][$key]);

        $correct = 'no';
        if ( $points > 0 ) {
            $correct = 'yes';
        }

        $query = sprintf( "INSERT INTO questions_choices SET question_id='%d', choice='%s', correct='%s', points='%d'",
                $question_id,
                $db->real_escape_string($value),
                $correct,
                $points
                );
        $db->query($query);
    }

    $data['actions'] = array();
    $data['actions'][] = $data['id_badge1'];
    $data['actions'][] = $data['id_badge2'];

    // put actions into its table
    foreach ( $data['actions'] as $value ) {
        $value = intval($value);
        if ( empty($value) ) continue;

        $query = sprintf( "INSERT INTO questions_badges SET question_id='%d', badge_id='%d', type='always'",
                $question_id,
                $value
                );
        $db->query($query);
    }

    $query = sprintf("SELECT * FROM members_quizs WHERE id_quiz='%d'", $data['id']);
    $result = $db->query($query);

    $values = array();
    while ($row = $result->fetch_assoc()) {
        $values[] = "('". $row['id_member'] ."', '". $question_id ."', '". $row['amount'] ."', FROM_UNIXTIME(". $row['last_time'] ."))";
    }
    $query = "INSERT INTO members_questions (id_member, id_question, amount, last_time) VALUES ". implode(',', $values);
    $db->query($query);


}

$query = "SELECT * FROM questions";
$result = $db->query($query);
$post_migracio = $result->num_rows;

printf( "migrats %s registres de %s totals.\n", $post_migracio, $pre_migracio );

printf( "   members_quizs -> members_questions: ");
$query = "SELECT * FROM members_questions";
$result = $db->query($query);
$post_migracio_q = $result->num_rows;
printf( "migrats %s registres de %s totals.\n", $post_migracio_q, $pre_migracio_q );

printf( "   updating members: ");

$query = "ALTER TABLE `members` ADD `uuid` CHAR( 36 ) NOT NULL AFTER `id`";
$db->query($query);

$query = sprintf("SELECT id FROM members");
$result2 = $db->query($query);

while ($row = $result2->fetch_assoc()) {
    $query = sprintf("UPDATE members SET uuid='%s' WHERE id='%d' LIMIT 1",
            $db->real_escape_string(getNewUUID()),
            $row['id']
            );
    $db->query($query);
}

$query = "ALTER TABLE `members` ADD UNIQUE `member_uuid` ( `uuid` )";
$db->query($query);

printf("updated!\n");

printf("</pre>\n");

printf("<h2>L'actualitzacio a la v2.x ha estat un exit</h2>\n");

exit();

/*** FUNCTIONS ***/
function getNewUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
