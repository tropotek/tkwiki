

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












