-- -----------------------------------
-- Run this SQL to upgrade the DB from 1.2 to 1.3
-- 
-- 

ALTER TABLE `page` ADD INDEX ( `currentTextId` );
ALTER TABLE `pageLock` ADD INDEX ( `pageId` );
ALTER TABLE `pageLock` ADD INDEX ( `userId` );  
ALTER TABLE `text` ADD INDEX ( `pageId` );
ALTER TABLE `user` ADD INDEX ( `group` );
ALTER TABLE `user` ADD INDEX ( `hash` );

OPTIMIZE TABLE `page` , `pageLink` , `pageLock` , `text` , `user`;

ALTER TABLE `page` ADD `userId` INT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'The original author of the page.' AFTER `currentTextId`;
ALTER TABLE `page` ADD INDEX ( `userId` );

ALTER TABLE `text` ADD `userId` INT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'The user who made the modifications (contributer)' AFTER `pageId`;
ALTER TABLE `text` ADD INDEX ( `userId` );



-- --------------------------------------------------------
-- 
-- Table structure for table `version`
-- 
CREATE TABLE `version` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  
  `version` VARCHAR(5) NOT NULL DEFAULT '1.0.0',
  `changelog` TEXT NOT NULL DEFAULT '',
  
  `modified` DATETIME NOT NULL, /* upgrade date */
  `created` DATETIME NOT NULL,  /* installed date */
  PRIMARY KEY  (`id`),
  UNIQUE (`version`)
) ENGINE=MyISAM COMMENT = '';


INSERT INTO `version` VALUES (
NULL , '1.0', '- Initial project release
- Updated Calls to Pager, Limit objects
- Updated calls to database
- Added new TinyMCE file manager plugin
', '2009-01-01 12:00:00', '2009-01-01 12:00:00'),
(NULL , '1.2', '- Added a basic user management system
', '2009-01-02 12:00:00', '2009-01-02 12:00:00'),
(NULL , '1.3', '- Updated login to stay on page you logged in on.
- Added new default template skin
- Added css skin directory
- Updated misc admin manager information to display correct values
- Added version table
- Added new install/upgrade script
- Updated TinyMCE filemanager plugin
- Added page RSS feed to allow users to monitor page updates.
- Optimised SQL tables
- Added publisher and contributor information to pages
- Updated page layout to include published avatar image
- Updated search engine
', '2009-04-01 14:54:23', '2009-04-01 14:54:23');


