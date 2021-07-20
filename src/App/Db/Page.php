<?php
namespace App\Db;

use App\Db\Traits\ContentTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Bs\Db\User;
use Tk\Db\Map\Model;


/**
 * Class User
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Page extends Model implements \Tk\ValidInterface
{
    use ContentTrait;
    use UserTrait;
    use TimestampTrait;

    /**
     * The default tag string used for routes
     * This actual default page url should be looked up in the config
     * when this tag is returned from the router
     */
    const DEFAULT_TAG = '__default';

    /**
     * All users can read page
     * Only registered users with edit roles can edit
     */
    const PERMISSION_PUBLIC = 0;
    /**
     * All registered users can read page
     * Only registered users with edit roles can edit
     */
    const PERMISSION_PROTECTED = 1;
    /**
     * Only the author can edit and read the page
     */
    const PERMISSION_PRIVATE = 2;

    /**
     * This type is a standard content page
     */
    const TYPE_PAGE = 'page';
    /**
     * This type means that the page is to be used with the menu/nav only
     */
    const TYPE_NAV = 'nav';


    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $type = self::TYPE_PAGE;

    /**
     * @var string
     */
    public $template = '';

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var integer
     */
    public $views = 0;

    /**
     * @var integer
     */
    public $permission = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;



    /**
     * User constructor.
     *
     */
    public function __construct()
    {
        $this->_TimestampTrait();
    }

    /**
     * create a unique url by comparing to the
     * existing urls and adding to a tail count if duplicated exist.
     *
     * EG:
     *   Home
     *   Home_1
     *   Home_2
     *   ...
     *
     * @param $title
     * @return mixed|string
     * @throws \Exception
     */
    static public function makeUrl($title)
    {
        $url = preg_replace('/[^a-z0-9_-]/i', '_', $title);
        do {
            $comp = \App\Db\PageMap::create()->findByUrl($url);
            if ($comp) {
                if (preg_match('/(.+)(_([0-9]+))$/', $url, $regs)) {
                    $url = $regs[1] . '_' . ($regs[3]+1);
                } else {
                    $url = $url.'_1';
                }
            }
        } while($comp);
        return $url;
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->getUrl() && !$this->getId()) {
            $this->setUrl(self::makeUrl($this->getTitle()));
        }
        parent::save();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function delete()
    {
        // remove page any locks (this could be redundant and left up to the expired cleanup)
        \App\Config::getInstance()->getLockMap()->unlock($this->getId());

        // delete all page links referred to by this page.
        $this->getMapper()->deleteLinkByPageId($this->getId());

        // Remove all content
        $contentList = \App\Db\ContentMap::create()->findByPageId($this->getId());
        foreach ($contentList as $c) {
            $c->delete();
        }
        return parent::delete();
    }

    /**
     * This returns a \Tk\Uri object pointing to the page.
     *
     * @return \Tk\Uri
     */
    public function getPageUrl()
    {
        return \Tk\Uri::create('/'.trim($this->url, '/'));
    }

    /**
     *
     * @param string $url
     * @return Page
     */
    static public function findPage($url)
    {
        if ($url == self::DEFAULT_TAG) {
            $url = \App\Config::getInstance()->get('wiki.page.default');
        }
        $page = self::createMapper()->findByUrl($url);
        return $page;
    }

    /**
     * @return string
     */
    static public function getHomeUrl()
    {
        return \App\Config::getInstance()->get('wiki.page.default');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Page
     */
    public function setType(string $type): Page
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return Page
     */
    public function setTemplate(string $template): Page
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Page
     */
    public function setTitle(string $title): Page
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Page
     */
    public function setUrl(string $url): Page
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * @param int $views
     * @return Page
     */
    public function setViews(int $views): Page
    {
        $this->views = $views;
        return $this;
    }

    /**
     * @return int
     */
    public function getPermission(): int
    {
        return $this->permission;
    }

    /**
     * @param int $permission
     * @return Page
     */
    public function setPermission(int $permission): Page
    {
        $this->permission = $permission;
        return $this;
    }



    /**
     * Get the page permission level as a string
     * @return string
     */
    public function getPermissionLabel()
    {
        switch($this->permission) {
            case self::PERMISSION_PRIVATE;
                return 'private';
            case self::PERMISSION_PROTECTED;
                return 'protected';
        }
        return 'public';
    }

    /**
     * Validate this object's current state and return an array
     * with error messages. This will be useful for validating
     * objects for use within forms.
     *
     * @return array
     * @throws \Exception
     */
    public function validate()
    {
        $errors = array();

        if (!$this->getUserId()) {
            $errors['userId'] = 'Invalid user ID value';
        }
        if (!$this->getTitle()) {
            $errors['title'] = 'Please enter a title for your page';
        }
        if($this->getId()) {
            $comp = \App\Db\PageMap::create()->findByUrl($this->getUrl());
            if ($comp && $comp->getId() != $this->getId()) {
                $errors['url'] = 'This url already exists, try again';
            }
        }
        return $errors;
    }

    // ------------------- PAGE PERMISSION METHODS -----------------------


    /**
     * @param User $user
     * @return bool
     */
    public static function canCreate($user)
    {
        if ($user->isAdmin() || $user->hasPermission(\App\Db\Permission::TYPE_MODERATOR))
            return true;
        return $user->hasPermission(\App\Db\Permission::PAGE_CREATE);
    }

    /**
     * @param User $user
     * @return string
     */
    private function getGroup($user)
    {
        if ($user->isAdmin()) return \Bs\Db\User::TYPE_ADMIN;
        if ($user->hasPermission(\App\Db\Permission::TYPE_MODERATOR)) return \App\Db\Permission::TYPE_MODERATOR;
        return \Bs\Db\User::TYPE_MEMBER;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canView($user)
    {
        if ($this->getUserId() == $user->getId()) return true;
        //$pa = self::create($page->getUser());

        switch($this->getPermission()) {
            case Page::PERMISSION_PUBLIC:
                return true;
            case Page::PERMISSION_PROTECTED:
                if ($user->hasPermission($this->getGroup($this->getUser())))
                    return true;
                if ($user->hasPermission(\App\Db\Permission::TYPE_MODERATOR) && $this->getGroup($this->getUser()) == \Bs\Db\User::TYPE_MEMBER)
                    return true;
                if ($user->isAdmin())
                    return true;
                break;
            case Page::PERMISSION_PRIVATE:
                if ($user->isAdmin())
                    return true;
        }
        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canEdit($user)
    {
        if ($this->getUrl() == self::getHomeUrl() && !$user->isAdmin())
            return false;
        if ($user->hasPermission(\App\Db\Permission::PAGE_EDIT) && $this->canView($user))
            return true;
        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canDelete($user)
    {
        if ($this->getUserId() && $this->getUrl() != \App\Db\Page::getHomeUrl() &&
            $user->hasPermission(\App\Db\Permission::PAGE_DELETE) && $this->canView($user))
                return true;
        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canEditExtra($user)
    {
        if ($user->hasPermission(\App\Db\Permission::PAGE_EDIT_EXTRA) && $this->canView($user))
            return true;
        return false;
    }

}
