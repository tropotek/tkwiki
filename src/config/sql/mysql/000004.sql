-- --------------------------------------------
-- @version 3.0.0
--
-- @author: Michael Mifsud <info@tropotek.com>
-- --------------------------------------------

/*
-- TODO: Run the following before the upgrade
DROP TABLE _session;
DROP TABLE session;
rename table data to _data;
rename table migration to _migration;
rename table plugin to _plugin;

rename table user_group to _user_group;
rename table permission to _permission;
rename table permission_user to _permission_user;

DROP TABLE _user_group;
DROP TABLE _permission;
DROP TABLE _permission_user;
DROP TABLE _user_role;
DROP TABLE _user_role_id;
DROP TABLE _user_role_permission;
*/


REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'type.moderator'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.create'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.edit'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.delete'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.editExtra'
        FROM user a
        WHERE a.type = 'admin'
    )
;






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






