



# DROP TABLE IF EXISTS `user`;
# DROP TABLE IF EXISTS `role`;
# DROP TABLE IF EXISTS `user_role`;
# DROP TABLE IF EXISTS `data`;
# DROP TABLE IF EXISTS `page`;
# DROP TABLE IF EXISTS `content`;
# DROP TABLE IF EXISTS `pagelink`;
# DROP TABLE IF EXISTS `lock`;
# DROP TABLE IF EXISTS `version`;


-- --------------------------------------------------------


-- --------------------------------------------------------
-- Table structure for table `user`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `username` VARCHAR(64) NOT NULL DEFAULT '',
  `password` VARCHAR(64) NOT NULL DEFAULT '',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `hash` VARCHAR(64) NOT NULL DEFAULT '',
  `failed` INT(10) NOT NULL DEFAULT 0,
  `last_login` DATETIME,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- User roles/permissions, not related to page permissions
-- The role permissions superseeds page permissions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB;


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


-- --------------------------------------------------------
-- Table structure for table `page`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) unsigned AUTO_INCREMENT PRIMARY KEY,
  `content_id` int(11) unsigned NOT NULL DEFAULT '0',   -- The current content for the page
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',      -- The author of the page
  `type` varchar(64) NOT NULL DEFAULT 'page',           -- The page type: `page`, `menu`, etc...
  `template` varchar(255) NOT NULL DEFAULT '',          -- use a different page template if selected
  `title` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(128) NOT NULL,                          -- the base url of the page
  `views` int(11) unsigned NOT NULL DEFAULT '0',        -- Page views per (1 per session)
  `modified` datetime NOT NULL,
  `created` datetime NOT NULL,
  UNIQUE KEY `url` (`url`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `content`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `content` (
  `id` int(11) unsigned AUTO_INCREMENT PRIMARY KEY,
  `page_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  
  `html` longtext,
  `keywords` varchar(255) NOT NULL DEFAULT '',          -- adds to the global meta keywords
  `description` varchar(255) NOT NULL DEFAULT '',       -- adds to the global meta description  
  `css` TEXT,
  `js` TEXT,
  `size` int(11) unsigned NOT NULL DEFAULT '0',         -- page size in bytes 
  
  `created` datetime NOT NULL,
  KEY `page_id` (`page_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;


-- --------------------------------------------------------
-- Table structure for table `pagelink`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pagelink` (
  `page_from` int(11) unsigned NOT NULL DEFAULT '0',
  `page_to_name` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `page_from` (`page_from`, `page_to_name`),
  KEY `page_to_name` (`page_to_name`, `page_from`)
) ENGINE=InnoDB;

-- Proposed new pagelink
# DROP TABLE IF EXISTS `pagelink`;
# CREATE TABLE IF NOT EXISTS `pagelink` (
#   `page_from_id` int(11) unsigned NOT NULL DEFAULT '0',
#   `page_to_id` int(11) unsigned NOT NULL DEFAULT '0',
#   UNIQUE KEY `page_from_id` (`page_from_id`, `page_to_id`),
#   KEY `page_to_id` (`page_to_id`, `page_from_id`)
# ) ENGINE=InnoDB;


-- --------------------------------------------------------
-- Table structure for table `lock`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lock` (
  `hash` varchar(64) NOT NULL DEFAULT '',
  `page_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(32) NOT NULL DEFAULT '',
  `expire` datetime NOT NULL,
  PRIMARY KEY `hash` (`hash`),
  KEY `page_id` (`page_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `version`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `version` (
  `id` int(11) unsigned AUTO_INCREMENT PRIMARY KEY,
  `version` varchar(5) NOT NULL DEFAULT '1.0.0',
  `changelog` text NOT NULL,
  `modified` datetime NOT NULL,
  `created` datetime NOT NULL,
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB;

