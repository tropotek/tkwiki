-- -----------------------------------
-- DkWiki install.sql
-- This is the db scheema for the DkWiki
-- 
-- 
-- 
-- 

-- --------------------------------------------------------
--
-- Table structure for table `page`
--
CREATE TABLE `page` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currentTextId` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The current content record to associate this page with',
  `userId` INT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'The original author of the page.',
  `groupId` INT(3) NOT NULL DEFAULT '1',
  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'This is the published TEXT',
  `name` VARCHAR( 255 ) NOT NULL COMMENT 'This is the name used by urls',
  `keywords` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'User defined meta keywrds for the INTernal search engine',
  `css` TEXT NOT NULL DEFAULT '' COMMENT 'Any user defined css inline styles',
  `javascript` TEXT NOT NULL DEFAULT '' COMMENT 'Any user defined inline javascript',
  `hits` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The page views per session',
  `size` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The page content size in bytes',
  `score` FLOAT NOT NULL DEFAULT '0' COMMENT '',
  `permissions` VARCHAR(3) NOT NULL DEFAULT '764',
  `enableComment` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE (`name`),
  FULLTEXT(`title`, `keywords`),
  KEY (`currentTextId`)
) ENGINE=MYISAM;

-- --------------------------------------------------------
--
-- Table structure for tsp_pageable `TEXT`
-- This is the TEXT for the pages it will contain past revisions etc
--
CREATE TABLE `text` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageId` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The current content record to associate this page with',
  `userId` INT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'The user who made the modifications (contributer)',
  `TEXT` LONGTEXT NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT(`TEXT`),
  KEY (`pageId`)
) ENGINE=MYISAM;

-- --------------------------------------------------------
--
-- Table structure for table `pageLink`
-- This table is a way to check for Orphaned pages
--
CREATE TABLE `pageLink` (
  `pageFrom` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `pageToName` VARCHAR(255) NOT NULL DEFAULT '',
  UNIQUE KEY `pageFrom` (`pageFrom`,`pageToName`),
  KEY `pageToName` (`pageToName`,`pageFrom`)
) ENGINE=MYISAM;

-- --------------------------------------------------------
--
-- Table structure for table `pageLock`
-- This table is a way to check for Orphaned pages
--
CREATE TABLE `pageLock` (
  `hash` VARCHAR(64) NOT NULL DEFAULT '',
  `pageId` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `userId` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `userIp` VARCHAR(32) NOT NULL DEFAULT '',
  `expire` DATETIME NOT NULL,
  UNIQUE KEY `hash` (`hash`),
  KEY (`pageId`),
  KEY (`userId`)
) ENGINE=MYISAM;

-- --------------------------------------------------------
-- 
-- Table structure for table `user`
-- 
CREATE TABLE `user` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  
  `name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `email` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `image` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Optional: The user avtar image 120x120',
  `active` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'If the user is inactive they cannot login',
  `username` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `password` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Required: ',
  `groupId` INT NOT NULL DEFAULT '0' COMMENT 'Required: ADMIN = 128, USER = 1',
  `hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Used by the user activation system',
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE (`email`),
  KEY (`groupId`),
  KEY (`hash`)
) ENGINE=MyISAM COMMENT = '';

-- --------------------------------------------------------
-- 
-- Table structure for table `comment`
-- 
CREATE TABLE `comment` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageId` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `userId` INT(11) UNSIGNED NOT NULL DEFAULT '0',
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

-- --------------------------------------------------------
-- 
-- Table structure for table `version`
-- 
CREATE TABLE `version` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(5) NOT NULL DEFAULT '1.0.0',
  `changelog` TEXT NOT NULL DEFAULT '',
  `modified` DATETIME NOT NULL, /* upgrade date */
  `created` DATETIME NOT NULL,  /* installed date */
  PRIMARY KEY  (`id`),
  UNIQUE (`version`)
) ENGINE=MyISAM COMMENT = '';


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
) ENGINE=MyISAM;

INSERT INTO `settings` VALUES (1, 'DkWiki', 'info@example.com', '', '', '', '', '', NOW(), NOW());





