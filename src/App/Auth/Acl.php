<?php
namespace App\Auth;

use App\Db\Permission;
use App\Db\Page;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @todo Implement the new Role and Permission objects from the BS lib
 */
class Acl
{


    /**
     * @var \Bs\Db\User
     */
    protected $user = null;



    /**
     * Access constructor.
     *
     * @param \Bs\Db\User|\Bs\Db\UserIface $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * A static constructor so we can call this method inline:
     * Eg:
     *   - Access::create($user)->isAdmin();
     *
     * @param \Bs\Db\User|\Bs\Db\UserIface $user
     * @return Acl
     */
    static function create($user)
    {
        $obj = new static($user);
        return $obj;
    }

    /**
     *
     * @param string|array $role
     * @return boolean
     * @deprecated Use hasPermission
     */
    public function hasRole($role)
    {
        if (!$this->user) return false;
        return $this->user->hasPermission($role);
//
//        if (!is_array($role)) $role = array($role);
//
//        foreach ($role as $r) {
//            try {
//                if (!$r instanceof Permission) {
//                    $r = PermissionMap::create()->findByName($r);
//                }
//                if ($r) {
//                    $obj = PermissionMap::create()->findRole($r->id, $this->user->id);
//                    if ($obj && $obj->id = $r->id) {
//                        return true;
//                    }
//                }
//            } catch (\Exception $e) {
//                \Tk\Log::warning(__FILE__ . ': ' . $e->getMessage());
//            }
//        }
//        return false;
    }

    /**
     * @return array
     */
//    public function getRolesArray()
//    {
//        $roles = $this->getRoles();
//        $arr = array();
//        foreach ($roles as $role) {
//            $arr[] = $role->name;
//        }
//        return $arr;
//    }

    /**
     * @return Permission[]|\Tk\Db\Map\ArrayObject
     */
//    public function getRoles()
//    {
//        $arr = array();
//        if ($this->user) {
//            try {
//                $arr = \App\Db\PermissionMap::create()->findByUserId($this->user->id);
//            } catch (\Exception $e) {
//                \Tk\Log::warning(__FILE__ . ': ' . $e->getMessage());
//                $arr = array();
//            }
//        }
//        return $arr;
//    }

    /**
     * @return string
     * @todo Find a better way to handle this
     */
    public function getGroup()
    {
        if ($this->isAdmin()) return \App\Db\Permission::TYPE_ADMIN;
        if ($this->isModerator()) return \App\Db\Permission::TYPE_MODERATOR;
        return \App\Db\Permission::TYPE_USER;
//        if ($this->isAdmin()) return \App\Db\Role::TYPE_ADMIN;
//        if ($this->isModerator()) return \App\Db\Role::TYPE_MODERATOR;
//        if ($this->isUser()) return \App\Db\ROLE::TYPE_USER;
//        return 'type.user';
    }

    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if (!$this->user) return false;
        return $this->user->hasPermission(\App\Db\Permission::TYPE_ADMIN);
    }

    /**
     *
     * @return boolean
     */
    public function isModerator()
    {
        if (!$this->user) return false;
        return $this->user->hasPermission(\App\Db\Permission::TYPE_MODERATOR);
    }

    /**
     *
     * @return boolean
     */
    public function isUser()
    {
        if (!$this->user) return false;
        return $this->user->hasPermission(\App\Db\Permission::TYPE_USER);
    }

    /**
     *
     * @return bool
     */
    public function canCreate()
    {
        if ($this->isAdmin() || $this->isModerator())
            return true;
        return $this->user->hasPermission(\App\Db\Permission::PAGE_CREATE);
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function canView($page)
    {
        if ($this->isAuthor($page)) return true;
        $pa = self::create($page->getUser());

        switch($page->permission) {
            case Page::PERMISSION_PUBLIC:
                return true;
            case Page::PERMISSION_PROTECTED:
                if ($this->user->hasPermission($pa->getGroup())) {
                    return true;
                }
                if ($this->isModerator() && $pa->getGroup() == \App\Db\Permission::TYPE_USER) {
                    return true;
                }
                if ($this->isAdmin()) {
                    return true;
                }
                break;
            case Page::PERMISSION_PRIVATE:
                if ($this->isAdmin()) {
                    return true;
                }
        }
        return false;
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function canEdit($page)
    {
        if ($page->url == \App\Db\Page::getHomeUrl() && !$this->isAdmin()) {
            return false;
        }
        if ($this->user->hasPermission(\App\Db\Permission::PAGE_EDIT) && $this->canView($page)) {
            return true;
        }
        return false;
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function canDelete($page)
    {
        if ($page->id && $page->url != \App\Db\Page::getHomeUrl() && $this->user->hasPermission(\App\Db\Permission::PAGE_DELETE) && $this->canView($page)) {
            return true;
        }
        return false;
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function canEditExtra($page)
    {
        if ($this->user->hasPermission(\App\Db\Permission::PAGE_EDIT_EXTRA) && $this->canView($page)) {
            return true;
        }
        return false;
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function isAuthor($page)
    {
        if (!$this->user) return false;
        return ($this->user->id == $page->userId);
    }
}
