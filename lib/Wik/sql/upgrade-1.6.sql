-- -----------------------------------
-- Run this SQL to upgrade the DB from 1.4 to 1.5
-- @project DkWiki
--  

-- --------------------------------------------------------
-- 
-- Table structure for table `version`
--
INSERT INTO `version` VALUES (
NULL , '1.6', '- Fixed orphan bug (Must resave pages [Menu/Home] to fix)
- Updated Forms to use the new Form module
- Updated Tabled to use the new Table module
- Updated login system to use the new Auth module
- Added new settings object
- Added notify email to SiteEmail on new comments cna be turned off in config.ini',
'2011-07-12 12:00:00', '2011-07-12 12:00:00');



-- --------------------------------------------------------
-- 
-- Table structure for table `settings`
-- 
CREATE TABLE `settings` (
  `id` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `siteEmail` VARCHAR(255) NOT NULL DEFAULT '',
  `contact` TEXT NOT NULL COMMENT '',
  `metaDescription` TEXT NOT NULL,
  `metaKeywords` TEXT NOT NULL,
  `footerScript` TEXT NOT NULL,
  `gmapKey` VARCHAR(255) NOT NULL DEFAULT '',
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE = MyISAM;

INSERT INTO `settings` VALUES (1, 'DkWiki', 'info@example.com', '', '', '', '', '', NOW(), NOW());

