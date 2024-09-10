-- ------------------------------------------------------
-- SQL views
--
-- Files views.sql, procedures.sql, events.sql, triggers.sql
--  will be executed if they exist after install, update and migration
--
-- ------------------------------------------------------

-- \App\Db\Page
CREATE OR REPLACE VIEW v_page AS
WITH
  -- TODO: also look for links in the menu as they will not be a parent page_id
  linked AS (
    SELECT
      linked_id AS page_id,
      COUNT(*) AS total
    FROM links
    GROUP BY linked_id
  ),
  latest AS (
      SELECT
          page_id,
          content_id,
          ROW_NUMBER() OVER (PARTITION BY page_id ORDER BY created DESC) AS latest
      FROM content
  )
SELECT
  p.*,
  IFNULL(c.content_id, 0) AS content_id,
  IFNULL(l.total, 0) AS linked,
  IFNULL(l.total, 0) = 0 AND p.page_id != r.value AS is_orphaned,
  MD5(CONCAT(p.page_id, 'Page')) AS hash
FROM page p
LEFT JOIN linked l USING (page_id)
JOIN latest c ON (p.page_id = c.page_id AND c.latest = 1)
JOIN registry r ON (r.`key` = 'wiki.page.home')
;



CREATE OR REPLACE VIEW v_secret AS
SELECT
  s.*,
  MD5(CONCAT(s.secret_id, 'Secret')) AS hash
FROM
  secret s
;

