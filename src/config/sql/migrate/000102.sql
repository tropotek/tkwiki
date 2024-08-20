-- --------------------------------------------
-- @version 8.0.40
-- --------------------------------------------

ALTER TABLE user DROP COLUMN hash;
ALTER TABLE user ALTER COLUMN username SET DEFAULT '';
ALTER TABLE user ALTER COLUMN timezone SET DEFAULT '';