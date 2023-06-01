-- ------------------------------------------------------
-- All project views
--
-- Files views.sql, procedures.sql, events.sql, triggers.sql
--  will be executed if they exist after install, update and migration
--
-- They can be executed from the cli commands:
--  o `./bin/cmd migrate`
--  o `composer update`
--
-- ------------------------------------------------------

-- Show only active users
# CREATE OR REPLACE ALGORITHM=MERGE VIEW v_links AS
# SELECT page_id, url
# FROM page
# WHERE 1
# ;


-- TODO Find all orphaned pages
# CREATE OR REPLACE ALGORITHM=MERGE VIEW v_orphans AS
# SELECT page_id, url
# FROM page
# WHERE 1  // TODO:
# ;


