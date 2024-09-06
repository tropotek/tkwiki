-- --------------------------------------------
-- @version 8.0.40
-- --------------------------------------------

ALTER TABLE user DROP COLUMN hash;
ALTER TABLE user ALTER COLUMN username SET DEFAULT '';
ALTER TABLE user ALTER COLUMN timezone SET DEFAULT '';

UPDATE user SET username = '' WHERE username IS NULL;
UPDATE user SET timezone = '' WHERE timezone IS NULL;

-- What the F is this for mick?????
# UPDATE menu_item SET parent_id = 0 WHERE parent_id IS NULL;
# UPDATE menu_item SET page_id = 0 WHERE page_id IS NULL;
# ALTER TABLE menu_item MODIFY parent_id INT(11) UNSIGNED NOT NULL DEFAULT 0;
# ALTER TABLE menu_item MODIFY page_id INT(11) UNSIGNED NOT NULL DEFAULT 0;

-- update links table to use page id's only
DROP TABLE links;
CREATE TABLE IF NOT EXISTS links
(
  page_id INT(11) UNSIGNED NOT NULL DEFAULT 0,      -- content page id
  linked_id INT(11) UNSIGNED NOT NULL DEFAULT 0,    -- link page id
  PRIMARY KEY (page_id, linked_id),
  CONSTRAINT fk_links__page_id FOREIGN KEY (page_id) REFERENCES page (page_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_links__linked_id FOREIGN KEY (linked_id) REFERENCES page (page_id) ON DELETE CASCADE ON UPDATE CASCADE
);

UPDATE registry SET `key` = 'wiki.page.home', value = 1 WHERE `key` = 'wiki.page.default';



-- Remove the `admin` user we no longer want to use that as a default username
-- DO NOT run this in the migration, personal wiki DB only
# UPDATE page SET user_id = 2 WHERE 1;
# UPDATE content SET user_id = 2 WHERE 1;
# UPDATE secret SET user_id = 2 WHERE 1;
# DELETE FROM user WHERE user_id = 1;