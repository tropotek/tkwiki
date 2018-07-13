




-- ---------------------------------------------------------
-- User roles/permissions, not related to page permissions
-- The role permissions superseeds page permissions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` TEXT,
  `del` TINYINT DEFAULT 0 NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `page`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 1,              -- The author of the page
  `type` VARCHAR(64) NOT NULL DEFAULT 'page',             -- The page type: `page`, `nav`, etc...
  `template` VARCHAR(255) NOT NULL DEFAULT '',            -- use a different page template if selected
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `url` VARCHAR(128) NOT NULL DEFAULT '',                 -- the base url of the page
  `permission` INT UNSIGNED NOT NULL DEFAULT 0,           -- Page permission 0 - public, 1 - protected, 2 - private
  `views` INT UNSIGNED NOT NULL DEFAULT 0,                -- Page views per (1 per session)
  `del` TINYINT DEFAULT 0 NOT NULL,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  UNIQUE KEY `url` (`url`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `content`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `content` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `html` LONGTEXT,
  `keywords` VARCHAR(255) NOT NULL DEFAULT '',            -- adds to the global meta keywords
  `description` VARCHAR(255) NOT NULL DEFAULT '',         -- adds to the global meta description
  `css` TEXT,
  `js` TEXT,
  `size` INT UNSIGNED NOT NULL DEFAULT 0,                 -- page size in bytes
  `del` TINYINT DEFAULT 0 NOT NULL,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  KEY `page_id` (`page_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;


-- --------------------------------------------------------
-- Table structure for table `links`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `links` (
  `page_id` INT UNSIGNED NOT NULL DEFAULT 0,              -- The containing page ID
  `page_url` VARCHAR(190) NOT NULL DEFAULT '',            -- The page url (we use url instead of id to cater for non-existing pages)
  UNIQUE KEY `page_from` (`page_id`, `page_url`)
) ENGINE=InnoDB;


-- --------------------------------------------------------
-- Table structure for table `lock`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lock` (
  `hash` VARCHAR(64) NOT NULL DEFAULT '',
  `page_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `ip` VARCHAR(32) NOT NULL DEFAULT '',
  `expire` DATETIME NOT NULL,
  PRIMARY KEY `hash` (`hash`),
  KEY `page_id` (`page_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

