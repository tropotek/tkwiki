-- --------------------------------------------------------
-- Project: safari.com
-- Author: Michael Mifsud
-- Site: http://www.tropotek.com/
-- 
-- 
-- 



-- --------------------------------------------------------
-- 
-- Table structure for table `user`
-- 
CREATE TABLE `user` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  
  `username` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Username is user assumed to be a valid email',
  `password` VARCHAR(64) NOT NULL DEFAULT '',
  `groupId` INT UNSIGNED NOT NULL DEFAULT '4' COMMENT 'Required: 0 = Public, 8 = User, 64 = Admin',
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Used by the user activation system',
  
  `lastLogin` DATETIME,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`username`),
  KEY (`hash`)
) ENGINE=MyISAM COMMENT = '';

INSERT INTO `user` (`id` ,`username` ,`password` ,`groupId` ,`active` ,`hash` ,`lastLogin` ,`modified` ,`created`) VALUES 
(NULL , 'admin@example.com', MD5( 'password' ) , '64', '1', MD5( 'admin1' ) , NULL , NOW( ) , NOW( ));
