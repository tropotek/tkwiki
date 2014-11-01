-- -----------------------------------
-- Run this SQL to upgrade the DB from 1.0 to 1.2
-- 
-- 


-- --------------------------------------------------------
-- 
-- Table structure for table `user`
-- 
CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  
  `name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `email` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `image` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Optional: The user avtar image 120x120',
  `active` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'If the user is inactive they cannot login',
  
  `username` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `password` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `group` INT NOT NULL DEFAULT '0' COMMENT 'Required: ADMIN = 128, USER = 1',
  `hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Used by the user activation system',
  
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE (`email`)
) ENGINE=MyISAM COMMENT = '';

INSERT INTO `user` (`id`, `name`, `email`, `image`, `active`, `username`, `password`, `group`, `modified`, `created`) VALUES
(NULL, 'Administrator', 'admin@domain.com', '', 1, 'admin', 'password', 128, NOW(), NOW()),
(NULL, 'User', 'user@domain.com', '', 1, 'user', 'password', 1, NOW(), NOW());

