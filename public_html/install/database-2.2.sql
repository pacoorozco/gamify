-- -----------------------------------------------------
-- Database structure: 2.2
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `image` text NOT NULL,
  `description` text,
  `amount_needed` int(10) unsigned NOT NULL,
  `time_period` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `badges`
--
LOCK TABLES `badges` WRITE;
INSERT INTO `badges` VALUES (1,'badge','uploads/badges/badge-prova.png','Badge de prova',5,0,'active','2015-01-01 08:00:00');
UNLOCK TABLES;

-- -----------------------------------------------------
-- Table `levels`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `levels`;
CREATE TABLE `levels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `experience_needed` int(10) unsigned NOT NULL,
  `image` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `levels`
--
LOCK TABLES `levels` WRITE;
INSERT INTO `levels` VALUES (1,'Novato',1,'levels/newbie_default.png');
UNLOCK TABLES;

-- -----------------------------------------------------
-- Table `members`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `username` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `role` enum('member','administrator') NOT NULL DEFAULT 'member',
  `level_id` int(10) unsigned NOT NULL DEFAULT '1',
  `session_id` varchar(32) DEFAULT NULL,
  `last_access` timestamp NULL DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `profile_image` varchar(250) DEFAULT NULL,
  `register_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `member_uuid` (`uuid`),
  KEY `level_id` (`level_id`),
  CONSTRAINT `members_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `members_badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `members_badges`;
CREATE TABLE `members_badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` int(10) unsigned NOT NULL,
  `id_badges` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `last_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`id_member`,`id_badges`),
  KEY `achieve_id` (`id_badges`),
  CONSTRAINT `members_badges_ibfk_2` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`),
  CONSTRAINT `members_badges_ibfk_3` FOREIGN KEY (`id_badges`) REFERENCES `badges` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `points`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `points`;
CREATE TABLE `points` (
  `id_member` int(10) unsigned NOT NULL,
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `points` int(11) NOT NULL,
  `memo` varchar(250) NOT NULL,
  KEY `id_member` (`id_member`),
  CONSTRAINT `points_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `questions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `question` text NOT NULL,
  `tip` varchar(255) DEFAULT NULL,
  `solution` text,
  `type` enum('single','multi') DEFAULT 'single',
  `status` enum('active','inactive','draft','hidden') DEFAULT 'active',
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `publish_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_uuid` (`uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `questions`
--
LOCK TABLES `questions` WRITE;
INSERT INTO `questions` VALUES (1,'3be0dcbd-990b-4287-b2ba-93133c5ab07d','Pregunta prova','','<p>Aquesta &eacute;s una pregunta de prova, respon la correcta :-D</p>','','<p>La resposta correcta &eacute;s \"Correcta\"</p>','single','active','2015-01-01 08:00:00','2015-01-01 08:00:00');
UNLOCK TABLES;

-- -----------------------------------------------------
-- Table `questions_badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `questions_badges`;
CREATE TABLE `questions_badges` (
  `question_id` int(10) unsigned NOT NULL,
  `badge_id` int(10) unsigned NOT NULL,
  `type` enum('success','fail','always') DEFAULT 'always',
  UNIQUE KEY `question-badge` (`question_id`,`badge_id`),
  KEY `badge_id` (`badge_id`),
  CONSTRAINT `questions_badges_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `questions_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `questions_badges`
--
LOCK TABLES `questions_badges` WRITE;
INSERT INTO `questions_badges` VALUES (1,1,'success');
UNLOCK TABLES;

-- -----------------------------------------------------
-- Table `questions_choices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `questions_choices`;
CREATE TABLE `questions_choices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `choice` varchar(255) NOT NULL,
  `correct` enum('yes','no') DEFAULT 'no',
  `points` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `questions_choices_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `questions_choices`
--
LOCK TABLES `questions_choices` WRITE;
INSERT INTO `questions_choices` VALUES (4,1,'Incorrecta 1','no',-1),(5,1,'Incorrecta 2','no',-1),(6,1,'Correcta','yes',5);
UNLOCK TABLES;

-- -----------------------------------------------------
-- Table `members_questions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `members_questions`;
CREATE TABLE `members_questions` (
  `id_member` int(10) unsigned NOT NULL,
  `id_question` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `answers` varchar(255) DEFAULT NULL,
  `last_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_member`,`id_question`),
  KEY `id_question` (`id_question`),
  CONSTRAINT `members_questions_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `members_questions_ibfk_2` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
SELECT members.id AS id, members.uuid, members.username AS username, members.email AS email, members.role AS role, members.last_access, members.level_id AS level_id, (SELECT name FROM levels WHERE id=members.level_id) AS level_name, (SELECT COUNT(*) FROM members_badges WHERE id_member=members.id AND status='completed') AS badges, members.disabled AS disabled, vtop.points AS total_points, vtop_month.points AS month_points 
FROM ((members LEFT JOIN vtop ON (members.id = vtop.id)) LEFT JOIN vtop_month ON (members.id = vtop_month.id));

-- -----------------------------------------------------
-- Create member 'admin' as an administrator
-- Create member 'user' as a member
-- -----------------------------------------------------
INSERT INTO `members` SET uuid='ebd78dc0-7252-4d65-9dc3-d4d36881a89d', username='admin', email='admin@domain.com', password=MD5('admin123'), role='administrator';
INSERT INTO `members` SET uuid='c3dc97c1-ef20-4682-916e-2f45ff1bc48c', username='user', email='user@domain.com', password=MD5('user123'), role='member';
