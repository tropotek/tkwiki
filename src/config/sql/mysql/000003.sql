
# ##################################################
# NOTICE: if you get an error upgrading your wiki:
#     - Rename this file to .000003.sql
#     - run `composer update` or `bin/cmd ug`
#     - rename .000003.sql back to 000003.sql
#     - run `composer update` or `bin/cmd ug` again
#
# ##################################################


TRUNCATE `user_role`;
INSERT INTO `user_role` (name, type, description, static, modified, created) VALUES
  ('admin', 'admin', 'System administrator role', 1, NOW(), NOW()),
  ('user', 'user', 'Site default user role', 1, NOW(), NOW()),
  ('moderator', 'user', 'Site moderator user role', 1, NOW(), NOW())
;



TRUNCATE `user_permission`;
INSERT INTO `user_permission` (`role_id`, `name`)
VALUES
   (1, 'type.admin'),
   (1, 'type.moderator'),
   (1, 'type.user'),
   (1, 'perm.create'),
   (1, 'perm.edit'),
   (1, 'perm.delete'),
   (1, 'perm.editExtra'),

   (2, 'type.user'),

   (3, 'type.moderator'),
   (3, 'type.user'),
   (3, 'perm.create'),
   (3, 'perm.edit'),
   (3, 'perm.delete'),
   (3, 'perm.editExtra')
;

-- --------------------------------------------------------
-- Table structure for table `group`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `group` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',

  `css` TEXT,
  `js` TEXT,
  `size` INT UNSIGNED NOT NULL DEFAULT 0,                 -- page size in bytes

  `del` TINYINT DEFAULT 0 NOT NULL,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  UNIQUE `name` (`name`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `page_group` (
  `user_id` INT UNSIGNED NOT NULL,
  `page_id` INT UNSIGNED NOT NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `user_group` (
  `user_id` INT UNSIGNED NOT NULL,
  `group_id` INT UNSIGNED NOT NULL
) ENGINE=InnoDB;






