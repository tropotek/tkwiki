<?php
namespace App\Auth;

use App\Db\Permission;
use App\Db\Page;
use Tk\ConfigTrait;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 *
 * @todo Implement the new Role and Permission objects from the BS lib
 * @TODO: All these methods should be moved to the Page object
 */
class Acl
{
    use ConfigTrait;


    /**
     * @var \Bs\Db\User
     */
    protected $user = null;



    /**
     * Access constructor.
     *
     * @param \Bs\Db\User $user
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
     * @param \Bs\Db\User $user
     * @return Acl
     */
    static function create($user)
    {
        $obj = new static($user);
        return $obj;
    }


    /**
     *
     * @return bool
     */
    public function canCreate()
    {
        if ($this->getAuthUser()->isAdmin() || $this->getAuthUser()->hasPermission(\App\Db\Permission::TYPE_MODERATOR))
            return true;
        return $this->getAuthUser()->hasPermission(\App\Db\Permission::PAGE_CREATE);
    }

    /**
     * @return string
     * @todo Find a better way to handle this
     */
    public function getGroup()
    {
        if ($this->getAuthUser()->isAdmin()) return \Bs\Db\User::TYPE_ADMIN;
        if ($this->getAuthUser()->hasPermission(\App\Db\Permission::TYPE_MODERATOR)) return \App\Db\Permission::TYPE_MODERATOR;
        return \Bs\Db\User::TYPE_MEMBER;
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function canView($page)
    {
        if ($this->isAuthor($page)) return true;
        $pa = self::create($page->getUser());

        switch($page->getPermission()) {
            case Page::PERMISSION_PUBLIC:
                return true;
            case Page::PERMISSION_PROTECTED:
                if ($this->getAuthUser()->hasPermission($pa->getGroup()))
                    return true;
                if ($this->getAuthUser()->hasPermission(\App\Db\Permission::TYPE_MODERATOR) && $pa->getGroup() == \Bs\Db\User::TYPE_MEMBER)
                    return true;
                if ($this->getAuthUser()->isAdmin())
                    return true;
                break;
            case Page::PERMISSION_PRIVATE:
                if ($this->getAuthUser()->isAdmin())
                    return true;
        }
        return false;
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function canEdit($page)
    {
        if ($page->getUrl() == \App\Db\Page::getHomeUrl() && !$this->getAuthUser()->isAdmin()) {
            return false;
        }
        if ($this->getAuthUser()->hasPermission(\App\Db\Permission::PAGE_EDIT) && $this->canView($page)) {
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
        if ($page->getUserId() && $page->getUrl() != \App\Db\Page::getHomeUrl() && $this->getAuthUser()->hasPermission(\App\Db\Permission::PAGE_DELETE) && $this->canView($page)) {
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
        if ($this->getAuthUser()->hasPermission(\App\Db\Permission::PAGE_EDIT_EXTRA) && $this->canView($page)) {
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
        if (!$this->getAuthUser()) return false;
        return ($this->getAuthUser()->getId() == $page->getUserId());
    }
}
