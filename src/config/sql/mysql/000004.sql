-- --------------------------------------------
-- @version 3.0.0
--
-- @author: Michael Mifsud <info@tropotek.com>
-- --------------------------------------------

/*
-- TODO: Run the following before the upgrade
DROP TABLE _session;
DROP TABLE session;
rename table data to _data;
rename table migration to _migration;
rename table plugin to _plugin;
rename table user_group to _user_group;
rename table permission to _permission;
rename table permission_user to _permission_user;

DROP TABLE _user_group;
DROP TABLE _permission;
DROP TABLE _permission_user;
DROP TABLE _user_role;
DROP TABLE _user_role_id;
DROP TABLE _user_role_permission;
*/




/*
-- TODO: Run the following after the upgrade
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'type.moderator'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.create'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.edit'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.delete'
        FROM user a
        WHERE a.type = 'admin'
    )
;
REPLACE INTO user_permission (user_id, name)
    (
        SELECT a.id, 'perm.editExtra'
        FROM user a
        WHERE a.type = 'admin'
    )
;
*/


