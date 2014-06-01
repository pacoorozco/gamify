-- -----------------------------------------------------
-- Database structure: 1.0
-- $Id: database-1.0.sql 65 2014-04-21 18:09:54Z paco $
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `badges`;
CREATE TABLE IF NOT EXISTS `badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `image` text NOT NULL,
  `description` text,
  `amount_needed` int(10) unsigned NOT NULL,
  `time_period` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) 
ENGINE=InnoDB  
DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Table `levels`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `levels`;
CREATE TABLE IF NOT EXISTS `levels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `experience_needed` int(10) unsigned NOT NULL,
  `image` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) 
ENGINE=InnoDB  
DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Table `members`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE IF NOT EXISTS `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `role` enum('member','administrator') NOT NULL DEFAULT 'member',
  `level_id` int(10) unsigned NOT NULL DEFAULT '1',
  `session_id` varchar(32) DEFAULT NULL,
  `last_access` varchar(32) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `level_id` (`level_id`)
) 
ENGINE=InnoDB  
DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Table `members_badges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `members_badges`;
CREATE TABLE IF NOT EXISTS `members_badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` int(10) unsigned NOT NULL,
  `id_badges` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `last_time` int(10) unsigned NOT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`id_member`,`id_badges`),
  KEY `achieve_id` (`id_badges`)
) 
ENGINE=InnoDB  
DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Table `points`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `points`;
CREATE TABLE IF NOT EXISTS `points` (
  `id_member` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `points` int(11) NOT NULL,
  `memo` varchar(250) CHARACTER SET utf8 NOT NULL,
  KEY `id_member` (`id_member`)
) 
ENGINE=InnoDB 
DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- View `vmembers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vmembers`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vmembers` AS select `members`.`id` AS `id`,`members`.`username` AS `username`,`members`.`email` AS `email`,`members`.`password` AS `password`,`members`.`role` AS `role`,`members`.`level_id` AS `level_id`,`members`.`session_id` AS `session_id`,`members`.`last_access` AS `last_access`,`members`.`disabled` AS `disabled`,`vtop`.`points` AS `total_points`,`vtop_week`.`points` AS `week_points`,`vtop_month`.`points` AS `month_points` from (((`members` left join `vtop` on((`members`.`id` = `vtop`.`id`))) left join `vtop_week` on((`members`.`id` = `vtop_week`.`id`))) left join `vtop_month` on((`members`.`id` = `vtop_month`.`id`)));

-- -----------------------------------------------------
-- View `vtop`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vtop`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vtop` AS select `members`.`id` AS `id`,sum(`points`.`points`) AS `points` from (`members` left join `points` on((`members`.`id` = `points`.`id_member`))) group by `members`.`id`;

-- -----------------------------------------------------
-- View `vtop_month`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vtop_month`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vtop_month` AS select `members`.`id` AS `id`,sum(`points`.`points`) AS `points` from (`members` left join `points` on(((`members`.`id` = `points`.`id_member`) and (timestampdiff(MONTH,`points`.`date`,now()) < 1) and (year(`points`.`date`) = year(now()))))) group by `members`.`id`;

-- -----------------------------------------------------
-- View `vtop_week`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vtop_week`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vtop_week` AS select `members`.`id` AS `id`,sum(`points`.`points`) AS `points` from (`members` left join `points` on(((`members`.`id` = `points`.`id_member`) and (timestampdiff(DAY,`points`.`date`,now()) < 7) and (year(`points`.`date`) = year(now()))))) group by `members`.`id`;

ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`);

ALTER TABLE `members_badges`
  ADD CONSTRAINT `members_badges_ibfk_2` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `members_badges_ibfk_3` FOREIGN KEY (`id_badges`) REFERENCES `badges` (`id`);

ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `members` (`id`);

-- -----------------------------------------------------
-- Create first member 'admin' as an administrator
-- -----------------------------------------------------
INSERT INTO `levels` SET level_name='Novato', experience_needed='1';
INSERT INTO `members` SET username='admin', email='name@domain.com', password=MD5('hola123'), role='administrator';
