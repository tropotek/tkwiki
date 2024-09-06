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
  linked AS (
    SELECT
      linked_id AS page_id,
      COUNT(*) AS total
    FROM links
    GROUP BY linked_id
  )
SELECT
  p.*,
  IFNULL(l.total, 0) AS linked,
  IFNULL(l.total, 0) = 0 AND p.page_id != r.value AS is_orphaned
FROM page p
JOIN registry r ON (r.`key` = 'wiki.page.home')
LEFT JOIN linked l USING (page_id)
;

