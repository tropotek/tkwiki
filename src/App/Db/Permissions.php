<?php

namespace App\Db;

class Permissions extends \Bs\Db\Permissions
{
    /**
     * Wiki user permission values
     */
    const PERM_EDITOR             = 0x10; // Ability to edit/audit non-private pages

	/**
     * permission groups and descriptions
     */
	const PERMISSION_LIST = [
        self::PERM_ADMIN            => "Admin",
        self::PERM_SYSADMIN         => "Manage Settings",
        self::PERM_MANAGE_STAFF     => "Manage Staff",
        self::PERM_MANAGE_MEMBER    => "Manage Users",
        self::PERM_EDITOR           => "Manage Content",
    ];

}