

-- No longer used
drop table version;


-- Fix up User table

DROP INDEX email ON user;
CREATE INDEX email ON user (email);

ALTER TABLE user DROP image;
ALTER TABLE user ADD del TINYINT DEFAULT 0 NOT NULL;
-- ALTER TABLE user ADD last_login TIMESTAMP NULL;
ALTER TABLE user ADD notes TEXT;
ALTER TABLE user ADD role varchar(128) DEFAULT '' NOT NULL;
ALTER TABLE user
  MODIFY COLUMN created datetime NOT NULL AFTER del,
  MODIFY COLUMN modified datetime NOT NULL AFTER del,
  MODIFY COLUMN active tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If the user is inactive they cannot login' AFTER last_login,
--  MODIFY COLUMN last_login datetime AFTER active,
  MODIFY COLUMN hash varchar(64) NOT NULL DEFAULT '' COMMENT 'Used by the user activation system' AFTER active;



ALTER TABLE content ADD del TINYINT DEFAULT 0 NOT NULL;
ALTER TABLE page ADD del TINYINT DEFAULT 0 NOT NULL;
ALTER TABLE role ADD del TINYINT DEFAULT 0 NOT NULL;

-- TODO WE need a better solution to this
UPDATE user set user.role = 'user';
Update user a, user_role b
SET a.role = 'admin'
WHERE a.id = b.user_id AND role_id = 1 OR role_id = 2
;




-- TODO: Manually execute for myLive wiki

ALTER TABLE user
  MODIFY COLUMN role varchar(128) NOT NULL DEFAULT '' AFTER password,
  MODIFY COLUMN last_login datetime AFTER active;


rename table role to permission;
rename table user_role to permission_user;

ALTER TABLE permission_user DROP PRIMARY KEY;
ALTER TABLE permission_user CHANGE role_id permission_id int(11) unsigned NOT NULL;
ALTER TABLE permission_user ADD role varchar(64) DEFAULT '' NOT NULL;
ALTER TABLE permission_user
  MODIFY COLUMN user_id int(11) unsigned NOT NULL AFTER role,
  MODIFY COLUMN permission_id int(11) unsigned NOT NULL AFTER role;

DELETE FROM `permission_user` WHERE `permission_id` < 4;

DELETE FROM `permission` WHERE `id` = 1;
DELETE FROM `permission` WHERE `id` = 3;



-- ALTER TABLE permission_user ADD PRIMARY KEY (role, permission_id);


-- Manually run for Unimelb FVAS wiki

-- UPDATE `user` t SET t.`username` = 'admin' WHERE t.`id` = 1;
-- UPDATE `user` t SET t.`username` = 'spaddle' WHERE t.`id` = 3;
-- UPDATE `user` t SET t.`username` = 'mifsudm' WHERE t.`id` = 2;
-- UPDATE `user` t SET t.`username` = 'rzoma' WHERE t.`id` = 5;
