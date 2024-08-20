-- ------------------------------------------------------
-- SQL events
--
-- Files views.sql, procedures.sql, events.sql, triggers.sql
--  will be executed if they exist after install, update and migration
--
-- ------------------------------------------------------

-- Delete expired page locks
DROP EVENT IF EXISTS evt_delete_page_locks;
DELIMITER //
CREATE EVENT evt_delete_page_locks
  ON SCHEDULE EVERY 2 MINUTE
  COMMENT 'Delete expired page locks'
  DO
  BEGIN
    DELETE FROM `lock` WHERE expire < NOW();
  END
//
DELIMITER ;


