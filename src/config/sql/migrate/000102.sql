-- --------------------------------------------
-- @version 8.0.40
-- --------------------------------------------

ALTER TABLE user DROP COLUMN hash;
ALTER TABLE user ALTER COLUMN username SET DEFAULT '';
ALTER TABLE user ALTER COLUMN timezone SET DEFAULT '';

UPDATE user SET username = '' WHERE username IS NULL;
UPDATE user SET timezone = '' WHERE timezone IS NULL;