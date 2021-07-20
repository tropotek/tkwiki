<?php
namespace App\Db;


use Bs\Db\User;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Bs\Db\Permission
{

    const TYPE_MODERATOR = 'type.moderator';

    /**
     *
     */
    const PAGE_CREATE = 'perm.create';

    /**
     *
     */
    const PAGE_EDIT = 'perm.edit';

    /**
     *
     */
    const PAGE_DELETE = 'perm.delete';

    /**
     *
     */
    const PAGE_EDIT_EXTRA = 'perm.editExtra';


    /**
     * @param string $type (optional) If set returns only the permissions for that user type otherwise returns all permissions
     * @return array|string[]
     */
    public function getAvailablePermissionList($type = '')
    {
        $arr = array();
        switch ($type) {
            case User::TYPE_ADMIN;
                $arr = array(
                    'Manage Site Config' => self::MANAGE_SITE,
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE,
                    'Moderator' => self::TYPE_MODERATOR,
                    'Create Page' => self::PAGE_CREATE,
                    'Edit Page' => self::PAGE_EDIT,
                    'Delete Page' => self::PAGE_DELETE,
                );
                break;
            case User::TYPE_MEMBER:
                $arr = array();
                break;
            default:
                $arr = array(
                    'Manage Site Config' => self::MANAGE_SITE,
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE,
                    'Moderator' => self::TYPE_MODERATOR,
                    'Create Page' => self::PAGE_CREATE,
                    'Edit Page' => self::PAGE_EDIT,
                    'Delete Page' => self::PAGE_DELETE,
                    //'Edit Extra Page' => self::PAGE_EDIT_EXTRA,

                );
        }
        return $arr;
    }

    /**
     * @param string $type (optional) If set returns only the permissions for that user type otherwise returns all permissions
     * @return array|string[]
     */
    public function getDefaultUserPermissions($type = '')
    {
        $list = array();
        if ($type == User::TYPE_ADMIN) {
            $list = array(
                'Manage Site Config' => self::MANAGE_SITE,
                'Manage Site Plugins' => self::MANAGE_PLUGINS,
                'Can Masquerade' => self::CAN_MASQUERADE
            );
        }
        return $list;
    }


}
