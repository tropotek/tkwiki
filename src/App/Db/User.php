<?php
namespace App\Db;


class User extends \Bs\Db\User
{
    /**
     * permission values
	 * permissions are bit masks that can include on or more bits
	 * requests for permission are ANDed with the user's permissions
	 * if the result is non-zero the user has permission.
     *
     * high-level permissions for specific roles
     */
	const PERM_ADMIN              = 0x00000001; // All permissions
	const PERM_SYSADMIN           = 0x00000002; // Change system settings
	const PERM_MANAGE_STAFF       = 0x00000004; // Manage staff users
    // NOTE: Users should only have read access in the WIKI at this time...
    const PERM_MANAGE_USER        = 0x00000008; // Manage base users
    const PERM_EDITOR             = 0x00000010; // Ability to edit/audit all site pages except private pages
	//                            0x00000010; // available

	/**
     * permission groups and descriptions
     */
	const PERMISSION_LIST = [
        self::PERM_ADMIN          => "Admin Full Access",
        self::PERM_SYSADMIN       => "Change Site Settings",
        self::PERM_MANAGE_STAFF   => "Manage Staff",
        self::PERM_MANAGE_USER    => "Manage Users",
        self::PERM_EDITOR         => "Content Editor",
    ];

    /**
     * Site staff user
     */
    const TYPE_STAFF = 'staff';

    /**
     * Base logged-in user type (Access to user pages)
     */
    const TYPE_USER = 'user';


}
