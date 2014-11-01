-- -----------------------------------
-- Run this SQL to upgrade the DB from 1.4 to 1.5
-- @project DkWiki
--  

-- --------------------------------------------------------
-- 
-- Table structure for table `version`
--
INSERT INTO `version` VALUES (
NULL , '1.5', '- Fixed orphan bug (Must resave pages [Menu/Home] to fix)
- Fixed CSS for image caption
- Fixed Page error for non-logged in users when a page does not exist,
- Replaced lib/Ext regex functions with preg_match equivalents (ready for PHP5.3)
- Upgraded TkLib/ComLib/JsLib sub-libs',
'2010-08-14 12:00:00', '2010-08-14 12:00:00');



-- --------------------------------------------------------
-- 
-- Table structure for table `comment`
-- 
CREATE TABLE `comment` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageId` INT(11) UNSIGNED NOT NULL default '0',
  `userId` INT(11) UNSIGNED NOT NULL default '0',
  `ip` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `web` VARCHAR(255) NOT NULL DEFAULT '',
  `comment` TEXT NOT NULL,
  `deleted` TINYINT NOT NULL DEFAULT '0',
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `pageId` (`pageId`)
) ENGINE=MyISAM COMMENT = '';


ALTER TABLE `page` ADD `enableComment` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `permissions`;
UPDATE `page` SET `enableComment` = '0' WHERE `id` = 1 LIMIT 1 ;

