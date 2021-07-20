-- ----------------------------------------------------------
-- Upgrade the version 1.6 wiki DB to the 2.0 wiki DB
-- Mysql Script only
--
--
-- Run this before running composer install/update
-- on the new sources
--
-- ----------------------------------------------------------

-- Update User table
ALTER TABLE `user` ADD `last_login` DATETIME NULL AFTER `hash`;
UPDATE `user` SET `hash` = MD5(CONCAT(`email`, NOW()));

-- ---------------------------------------------------------
-- User roles/permissions, not related to page permissions
-- The role permissions superseeds page permissions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL
) ENGINE=InnoDB;
ALTER TABLE `user_role` ADD PRIMARY KEY ( `user_id` , `role_id` ) ;

INSERT INTO `role` (`name`, `description`) VALUES
  ('admin', 'Manage site, groups, users, pages, etc no restrictions'),
  ('moderator', 'Manage assigned users and pages for assigned groups'),
  ('user', 'Manage user settings, pages only'),
  ('create', 'Create pages'),
  ('edit', 'Edit existing pages'),
  ('delete', 'Delete pages'),
  ('editExtra', 'Can edit page css, js, url and template options');



INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 1 as 'role_id'
  FROM `user` WHERE `groupId` = 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 2 as 'role_id'
  FROM `user` WHERE `groupId` = 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 3 as 'role_id'
  FROM `user` WHERE `groupId` = 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 4 as 'role_id'
  FROM `user` WHERE `groupId` = 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 5 as 'role_id'
  FROM `user` WHERE `groupId` = 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 6 as 'role_id'
  FROM `user` WHERE `groupId` = 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 7 as 'role_id'
  FROM `user` WHERE `groupId` = 128;


INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 3 as 'role_id'
  FROM `user` WHERE `groupId` != 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 4 as 'role_id'
  FROM `user` WHERE `groupId` != 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 5 as 'role_id'
  FROM `user` WHERE `groupId` != 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 6 as 'role_id'
  FROM `user` WHERE `groupId` != 128;
INSERT INTO `user_role` (`user_id`, `role_id`)
  SELECT id as 'user_id', 7 as 'role_id'
  FROM `user` WHERE `groupId` != 128;


-- page
ALTER TABLE `page` CHANGE `userId` `user_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `page` ADD `type` VARCHAR( 64 ) NOT NULL DEFAULT 'page' AFTER `user_id`;
ALTER TABLE `page` ADD `template` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `type`;
ALTER TABLE `page` CHANGE `name` `url` VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE `page` CHANGE `hits` `views` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';


UPDATE `page` SET `permissions` = '2' WHERE `permissions` = '700';
UPDATE `page` SET `permissions` = '1' WHERE `permissions` = '744';
UPDATE `page` SET `permissions` = '1' WHERE `permissions` = '760';
UPDATE `page` SET `permissions` = '0' WHERE `permissions` != '2' AND `permissions` != '1';
ALTER TABLE `page` CHANGE `permissions` `permission` INT NOT NULL DEFAULT 0;

UPDATE `page` SET `type` = 'nav' WHERE `url` = 'Menu';


-- content
RENAME TABLE `text` TO `content`;
ALTER TABLE `content` CHANGE `pageId` `page_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `content` CHANGE `userId` `user_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `content` CHANGE `TEXT` `html` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `content` ADD `modified` DATETIME NOT NULL AFTER `html`;
UPDATE `content` SET `modified` = `created`;
ALTER TABLE `content` ADD `keywords` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `html`;

ALTER TABLE `content` ADD `description` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `keywords`;
ALTER TABLE `content` ADD `css` TEXT AFTER `description`;
ALTER TABLE `content` ADD `js` TEXT AFTER `css`;
ALTER TABLE `content` ADD `size` int(11) unsigned NOT NULL DEFAULT '0' AFTER `js`;
ALTER TABLE `content` ADD INDEX ( `user_id` );

UPDATE `content` a, `page` b
SET
  a.`keywords` = b.`keywords`,
  a.`css` = b.`css`,
  a.`js` = b.`javascript`
WHERE a.`id` = b.`currentTextId`;

-- PageLink to links
RENAME TABLE `pageLink` TO `links`;
ALTER TABLE `links` CHANGE `pageFrom` `page_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `links` CHANGE `pageToName` `page_url` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';


-- TODO: You may have to adjust these to match your existing db

-- ALTER TABLE `links` DROP INDEX `pageFrom`;
-- ALTER TABLE `links` DROP INDEX `pageToName`;

-- ALTER TABLE `links` ADD PRIMARY KEY( `page_id`, `page_url`);
ALTER TABLE `links` ADD UNIQUE KEY `page_from` (`page_id` , `page_url`);

-- The final result should be this:
-- CREATE TABLE IF NOT EXISTS `links` (
--   `page_id` int(11) unsigned NOT NULL DEFAULT '0',  -- The containing page ID
--   `page_url` varchar(255) NOT NULL DEFAULT '',      -- The page url (we use url instead of id to cater for non-existing pages)
--   UNIQUE KEY `page_from` (`page_id`, `page_url`)
-- ) ENGINE=InnoDB;


-- ----------------------------------------------------------



-- lock
RENAME TABLE `pageLock` TO `lock` ;

ALTER TABLE `lock` CHANGE `pageId` `page_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `lock` CHANGE `userId` `user_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `lock` CHANGE `userIp` `ip` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';

-- New Setting table

-- --------------------------------------------------------
-- Table structure for table `data`
-- This is the replacement for the `settings` table
-- Use foreign_id = 0 and foreign_key = `system` for site settings (suggestion only)
-- Can be used for other object data using the foreign_id and foreign_key
-- foreign_key can be a class namespace or anything describing the data group
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `data` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `foreign_id` INT NOT NULL DEFAULT 0,
  `foreign_key` VARCHAR(128) NOT NULL DEFAULT '',
  `key` VARCHAR(255) NOT NULL DEFAULT '',
  `value` TEXT,
  UNIQUE KEY `foreign_fields` (`foreign_id`, `foreign_key`, `key`)
) ENGINE=InnoDB;

INSERT INTO _data (`foreign_id`, `foreign_key`, `key`, `value`) VALUES
  (0, 'system', 'site.title', 'TkWiki II'),
  (0, 'system', 'site.email', 'tkwiki@example.com'),
  (0, 'system', 'site.meta.keywords', ''),
  (0, 'system', 'site.meta.description', ''),
  (0, 'system', 'site.global.js', ''),
  (0, 'system', 'site.global.css', ''),
  (0, 'system', 'wiki.page.default', 'Home'),
  (0, 'system', 'wiki.page.home.lock', 'wiki.page.home.lock');

UPDATE _data a, `settings` b
set a.`value` = b.`title` WHERE a.`key` = 'site.title';
UPDATE _data a, `settings` b
set a.`value` = b.`siteEmail` WHERE a.`key` = 'site.email';
UPDATE _data a, `settings` b
set a.`value` = b.`title` WHERE a.`key` = 'site.title';
UPDATE _data a, `settings` b
set a.`value` = b.`metaDescription` WHERE a.`key` = 'site.meta.description';
UPDATE _data a, `settings` b
set a.`value` = b.`metaKeywords` WHERE a.`key` = 'site.meta.keywords';
UPDATE _data a, `settings` b
set a.`value` = b.`footerScript` WHERE a.`key` = 'site.global.js';


ALTER TABLE `user` DROP `groupId`;
ALTER TABLE `page` DROP `currentTextId`;
ALTER TABLE `page` DROP `groupId`;
ALTER TABLE `page` DROP `keywords`;
ALTER TABLE `page` DROP `css`;
ALTER TABLE `page` DROP `javascript`;
ALTER TABLE `page` DROP `score`;
ALTER TABLE `page` DROP `enableComment`;
DROP TABLE comment;
DROP TABLE settings;



-- Setup the migration table

DROP TABLE IF EXISTS _migration;
CREATE TABLE IF NOT EXISTS `migration` (
  `path` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`path`)
) ENGINE=InnoDB;
INSERT INTO _migration (`path`, `created`) VALUES
  ('/src/config/sql/mysql/000000.sql', '2016-08-31 02:46:36'),
  ('/src/config/sql/mysql/000001.sql', '2016-08-31 02:46:36'),
  ('/vendor/ttek/tk-site/config/sql/mysql/000000.sql', '2016-08-31 02:46:36');


-- --------------------------------------
-- Table data: version
-- --------------------------------------
INSERT INTO `version` (`version`, `changelog`,`modified`, `created`) VALUES
  ('2.0', '- Version 2.0 released
- Completely re-written codebase to use new PHP5.3+
- Added new postgress DB files',
   '2016-06-01 12:00:00', '2016-06-01 12:00:00');
