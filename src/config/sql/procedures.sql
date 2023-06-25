-- ------------------------------------------------------
-- All project procedures and functions
--
-- Files views.sql, procedures.sql, events.sql, triggers.sql
--  will be executed if they exist after install, update and migration
--
-- They can be executed from the cli commands:
--  o `./bin/cmd migrate`
--  o `composer update`
--
-- ------------------------------------------------------

-- compares two date ranges and checks for overlap (inclusive)
-- start dates must be before end date
# DROP FUNCTION IF EXISTS dates_overlap;
# CREATE FUNCTION dates_overlap(
# 	start1 DATE,
# 	end1 DATE,
# 	start2 DATE,
# 	end2 DATE
# ) RETURNS BOOLEAN DETERMINISTIC
# 	RETURN GREATEST(start1, start2) <= LEAST(end1, end2)
# ;


-- return extension given a filename
-- returns extension lower-cased, null if no extension found
# DROP FUNCTION IF EXISTS filename_ext;
# DELIMITER //
# CREATE FUNCTION filename_ext(filename VARCHAR(400))
#   RETURNS VARCHAR(4) DETERMINISTIC
# BEGIN
#   SET @ext = SUBSTRING_INDEX(filename, '.', -1);
#   IF @ext = filename THEN
#     -- no . found
#     SET @ext = NULL;
#   END IF;
#   RETURN LOWER(@ext);
# END //
# DELIMITER ;

