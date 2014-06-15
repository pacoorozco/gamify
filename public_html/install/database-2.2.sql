-- -----------------------------------------------------
-- Database structure: 2.2
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS badges;
CREATE TABLE badges (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `image` text NOT NULL,
  `description` text,
  `amount_needed` int(10) unsigned NOT NULL,
  `time_period` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `levels`
-- -----------------------------------------------------
DROP TABLE IF EXISTS levels;
CREATE TABLE levels (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `experience_needed` int(10) unsigned NOT NULL,
  `image` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `members`
-- -----------------------------------------------------
DROP TABLE IF EXISTS members;
CREATE TABLE members (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `username` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `role` enum('member','administrator') NOT NULL DEFAULT 'member',
  `level_id` int(10) unsigned NOT NULL DEFAULT '1',
  `session_id` varchar(32) DEFAULT NULL,
  `last_access` varchar(32) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `profile_image` VARCHAR( 250 ) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `member_uuid` (`uuid`),
  KEY `level_id` (`level_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `members_badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS members_badges;
CREATE TABLE members_badges (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` int(10) unsigned NOT NULL,
  `id_badges` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `last_time` int(10) unsigned NOT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`id_member`,`id_badges`),
  KEY `achieve_id` (`id_badges`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `points`
-- -----------------------------------------------------
DROP TABLE IF EXISTS points;
CREATE TABLE points (
  `id_member` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `points` int(11) NOT NULL,
  `memo` varchar(250) NOT NULL,
  KEY `id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `questions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS questions;
CREATE TABLE questions (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `questions_badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS questions_badges;
CREATE TABLE questions_badges (
  `question_id` int(10) unsigned NOT NULL,
  `badge_id` int(10) unsigned NOT NULL,
  `type` enum('success','fail','always') DEFAULT 'always',
  UNIQUE KEY `question-badge` (`question_id`,`badge_id`),
  KEY `badge_id` (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `questions_choices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS questions_choices;
CREATE TABLE questions_choices (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `choice` varchar(255) NOT NULL,
  `correct` enum('yes','no') DEFAULT 'no',
  `points` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `members_questions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `members_questions` (
  `id_member` int(10) unsigned NOT NULL,
  `id_question` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `answers` varchar(255) DEFAULT NULL,
  `last_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_member`,`id_question`),
  KEY `id_question` (`id_question`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- View `vtop`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `vtop`;
CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vtop AS
SELECT members.id AS id, SUM(points.points) AS points 
FROM (members LEFT JOIN points ON (members.id = points.id_member))
GROUP BY members.id;

-- -----------------------------------------------------
-- View `vtop_month`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `vtop_month`;
CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vtop_month AS 
SELECT members.id AS id, SUM(points.points) AS points 
FROM (members LEFT JOIN points ON (members.id = points.id_member))
WHERE points.creation_time > DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY members.id;

-- -----------------------------------------------------
-- View `vmembers`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `vmembers`;
CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vmembers AS
SELECT members.id AS id, members.uuid, members.username AS username, members.email AS email, members.role AS role, members.level_id AS level_id, (SELECT name FROM levels WHERE id=members.level_id) AS level_name, (SELECT COUNT(*) FROM members_badges WHERE id_member=members.id AND status='completed') AS badges, members.disabled AS disabled, vtop.points AS total_points, vtop_month.points AS month_points 
FROM ((members LEFT JOIN vtop ON (members.id = vtop.id)) LEFT JOIN vtop_month ON (members.id = vtop_month.id));

ALTER TABLE `members`
  ADD CONSTRAINT members_ibfk_3 FOREIGN KEY (level_id) REFERENCES `levels` (id);

ALTER TABLE `members_badges`
  ADD CONSTRAINT members_badges_ibfk_2 FOREIGN KEY (id_member) REFERENCES members (id),
  ADD CONSTRAINT members_badges_ibfk_3 FOREIGN KEY (id_badges) REFERENCES badges (id);

ALTER TABLE `points`
  ADD CONSTRAINT points_ibfk_1 FOREIGN KEY (id_member) REFERENCES members (id);

ALTER TABLE `questions_badges`
  ADD CONSTRAINT questions_badges_ibfk_1 FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT questions_badges_ibfk_2 FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `questions_choices`
  ADD CONSTRAINT questions_choices_ibfk_1 FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `members_questions`
  ADD CONSTRAINT `members_questions_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `members_questions_ibfk_2` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Changes from DB 2.1
-- -----------------------------------------------------
ALTER TABLE `badges` ADD `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `members` ADD `register_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; 
ALTER TABLE `members` CHANGE `last_access` `last_access` TIMESTAMP NULL;
ALTER TABLE `members_badges` CHANGE `last_time` `last_time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `members_questions` CHANGE `last_time` `last_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `points` CHANGE `date` `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `questions` ADD `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `questions` ADD `publish_time` TIMESTAMP NULL;

-- -----------------------------------------------------
-- Create first member 'admin' as an administrator
-- -----------------------------------------------------
INSERT INTO `levels` SET name='Novato', experience_needed='1', image='levels/newbie_default.png';
INSERT INTO `members` SET uuid='ebd78dc0-7252-4d65-9dc3-d4d36881a89d', username='admin', email='name@domain.com', password=MD5('hola123'), role='administrator';