<?php
namespace App\Db;


class User extends \Bs\Db\User
{
    /**
     * Wiki user permission values
     */
    const PERM_EDITOR             = 0x00000010; // Ability to edit/audit all site pages except private pages

	/**
     * permission groups and descriptions
     */
	const PERMISSION_LIST = [
        self::PERM_ADMIN            => "Admin",
        self::PERM_SYSADMIN         => "Manage Settings",
        self::PERM_MANAGE_STAFF     => "Manage Staff",
        self::PERM_MANAGE_USER      => "Manage Users",
        self::PERM_EDITOR           => "Manage Content",
    ];


}
