-- --------------------------------------------
-- @version 8.0.40
-- --------------------------------------------

ALTER TABLE user DROP COLUMN hash;
ALTER TABLE user ALTER COLUMN username SET DEFAULT '';
ALTER TABLE user ALTER COLUMN timezone SET DEFAULT '';

UPDATE user SET username = '' WHERE username IS NULL;
UPDATE user SET timezone = '' WHERE timezone IS NULL;

# UPDATE menu_item SET parent_id = 0 WHERE parent_id IS NULL;
# UPDATE menu_item SET page_id = 0 WHERE page_id IS NULL;
# ALTER TABLE menu_item MODIFY parent_id INT(11) UNSIGNED NOT NULL DEFAULT 0;
# ALTER TABLE menu_item MODIFY page_id INT(11) UNSIGNED NOT NULL DEFAULT 0;





