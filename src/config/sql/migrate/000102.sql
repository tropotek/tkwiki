-- --------------------------------------------
-- @version 8.0.40
-- --------------------------------------------

ALTER TABLE user DROP COLUMN hash;
ALTER TABLE user ALTER COLUMN username SET DEFAULT '';
ALTER TABLE user ALTER COLUMN timezone SET DEFAULT '';

UPDATE user SET username = '' WHERE username IS NULL;
UPDATE user SET timezone = '' WHERE timezone IS NULL;

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
