<?php
namespace App\Auth;

use App\Db\Role;
use App\Db\RoleMap;
use App\Db\Page;
use App\Db\PageMap;

/**
 * Class RoleAccess
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @todo This does not feel like the best way to manage the page permissions,
 *       implement something else if you get a brainwave,,,,
 */
class Acl 
{

    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_USER = 'user';

    const ROLE_CREATE = 'create';
    const ROLE_EDIT = 'edit';
    const ROLE_DELETE = 'delete';
    const ROLE_EDIT_EXTRA = 'editExtra';
    
    
    /**
     * @var \App\Db\User
     */
    protected $user = null;
    
    

    /**
     * Access constructor.
     *
     * @param \App\Db\User $user
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
     * @param \App\Db\User $user
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
     * @todo Optimise this code....
     */
    public function hasRole($role)
    {
        if (!$this->user) return false;
        if (!is_array($role)) $role = array($role);

        foreach ($role as $r) {
            if (!$r instanceof Role) {
                $r = RoleMap::create()->findByName($r);
            }
            if ($r) {
                $obj = RoleMap::create()->findRole($r->id, $this->user->id);
                if ($obj && $obj->id = $r->id) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getRolesArray()
    {
        $roles = $this->getRoles();
        $arr = array();
        foreach ($roles as $role) {
            $arr[] = $role->name;
        }
        return $arr;
    }

    /**
     * @return \App\Db\Role[]
     */
    public function getRoles()
    {
        if (!$this->user) return [];
        $arr = \App\Db\RoleMap::create()->findByUserId($this->user->id);
        return $arr;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        if ($this->isAdmin()) return self::ROLE_ADMIN;
        if ($this->isModerator()) return self::ROLE_MODERATOR;
        if ($this->isUser()) return self::ROLE_USER;
        return '';
    }
    
    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     *
     * @return boolean
     */
    public function isModerator()
    {
        return $this->hasRole(self::ROLE_MODERATOR);
    }

    /**
     *
     * @return boolean
     */
    public function isUser()
    {
        return $this->hasRole(self::ROLE_USER);
    }
    
    /**
     * 
     * @return bool
     */
    public function canCreate()
    {
        if ($this->isAdmin() || $this->isModerator())
            return true;
        return $this->hasRole(self::ROLE_CREATE);
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
                if ($pa->getGroup() == $this->getGroup()) {
                    return true;
                }
                if ($this->isModerator() && $pa->getGroup() == self::ROLE_USER) {
                    return true;
                }
                if ($this->isAdmin()) {
                    return true;
                }
                break;
            case Page::PERMISSION_PRIVATE:
                if ($this->hasRole(self::ROLE_ADMIN)) {
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
        if ($this->hasRole(self::ROLE_EDIT) && $this->canView($page)) {
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
        if ($page->id && $page->url != \App\Db\Page::getHomeUrl() && $this->hasRole(self::ROLE_DELETE) && $this->canView($page)) {
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
        if ($this->hasRole(self::ROLE_EDIT_EXTRA) && $this->canView($page)) {
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