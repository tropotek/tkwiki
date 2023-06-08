<?php
namespace App\Db;

use App\Db\Traits\UserTrait;
use App\Factory;
use Bs\Db\Traits\TimestampTrait;
use Tk\Config;
use Tk\Db\Mapper\Model;
use Tk\Uri;

class Page extends Model
{
    use TimestampTrait;
    use UserTrait;

    /**
     * The default tag string used for routes
     * This actual default page url should be looked up in the config
     * when this tag is returned from the router
     */
    const DEFAULT_TAG = '__default';

    /**
     * Page permission values
     */
	const PERM_PUBLIC             = 0; // Public Pages (public can view, User::PERM_EDITOR can edit)
    const PERM_USER               = 1; // User Pages (User::TYPE_USER can view, User::PERM_EDITOR can edit)
	const PERM_STAFF              = 2; // Protected (User::TYPE_STAFF can view, User::PERM_EDITOR can edit)
    const PERM_PRIVATE            = 999; // Private (User::TYPE_STAFF, only author can view/edit)

    const PERM_LIST = [
        self::PERM_PUBLIC   => 'Public',
        self::PERM_USER     => 'User',
        self::PERM_STAFF    => 'Staff',
        self::PERM_PRIVATE  => 'Private',
    ];


    public int $id = 0;

    public int $userId = 0;

    public string $template = '';

    public string $title = '';

    public string $url = '';

    public int $views = 0;

    public int $permission = 0;

    public bool $published = true;

    public \DateTime $modified;

    public \DateTime $created;

    private ?Content $_content = null;


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
     */
    public static function makePageUrl(string $title): string
    {
        $url = preg_replace('/[^a-z0-9_-]/i', '_', $title);
        do {
            $comp = \App\Db\PageMap::create()->findByUrl($url);
            if ($comp || self::routeExists($url)) {
                if (preg_match('/(.+)(_([0-9]+))$/', $url, $regs)) {
                    $url = $regs[1] . '_' . ((int)$regs[3]+1);
                } else {
                    $url = $url.'_1';
                }
            }
        } while($comp || self::routeExists($url));
        return $url;
    }

    public static function findPage(string $url): ?Page
    {
        if ($url == self::DEFAULT_TAG) {
            $url = Factory::instance()->getRegistry()->get('wiki.page.default');
        }
        return PageMap::create()->findByUrl($url);
    }

    public static function getHomeUrl(): string
    {
        return Factory::instance()->getRegistry()->get('wiki.page.default');
    }

    public static function homeUrl(): Uri
    {
        return Uri::create(Factory::instance()->getRegistry()->get('wiki.page.default'));
    }

    public function update(): int
    {
        if (!$this->getUrl()) {
            $this->setUrl(self::makePageUrl($this->getTitle()));
        }
        return parent::update();
    }

    public function delete(): int
    {
        // Cannot delete first page and first menu item
        if ($this->getUrl() == self::getHomeUrl()) {
            return false;
        }

        // delete all page links referred to by this page.
        $this->getMapper()->deleteLinkByPageId($this->getId());

        return parent::delete();
    }

    public function getContent(): ?Content
    {
        if (!$this->_content) {
            $this->_content = ContentMap::create()->findByPageId($this->id, \Tk\Db\Tool::create('created DESC', 1))->current();
        }
        return $this->_content;
    }


    public function setTemplate(string $template): Page
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTitle(string $title): Page
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setUrl(string $url): Page
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getPageUrl(): Uri
    {
        return Uri::create('/'.trim($this->getUrl(), '/'));
    }

    public function setViews(int $views): Page
    {
        $this->views = $views;
        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setPermission(int $permission): Page
    {
        $this->permission = $permission;
        return $this;
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): Page
    {
        $this->published = $published;
        return $this;
    }

    /**
     * Get the page permission level as a string
     */
    public function getPermissionLabel(): string
    {
        return match ($this->permission) {
            self::PERM_PRIVATE => 'private',
            self::PERM_STAFF => 'staff',
            self::PERM_USER => 'users',
            default => 'public',
        };
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->getUserId()) {
            $errors['userId'] = 'Invalid user ID value';
        }
        if (!$this->getTitle()) {
            $errors['title'] = 'Please enter a title for your page';
        }
        //if  ($this->getId()) {
            $comp = PageMap::create()->findByUrl($this->getUrl());
            if ($comp && $comp->getId() != $this->getId()) {
                $errors['url'] = 'This url already exists, try again';
            }
            // Match any existing system routes
            if (self::routeExists($this->getUrl())) {
                $errors['url'] = 'This url already exists, try again';
            }
        //}

        return $errors;
    }

    /**
     * If not matched to the wiki-catch-all route,
     * then the page exists in the routing table already
     */
    public static function routeExists(string $url): bool
    {
        try {
            $match = Factory::instance()->getRouteMatcher()->match($url);
            $route = $match['_route'];
            return ($route != 'routeswiki-catch-all');
        } catch (\Exception $e) {  }
        return false;
    }

    // ------------------- PAGE PERMISSION METHODS -----------------------

    public static function canCreate(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isAdmin() || $user->isStaff()) return true;
        return false;
    }

    public function canView(?User $user): bool
    {
        if ($this->getPermission() == self::PERM_PUBLIC) return true;
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        if ($this->getUserId() == $user->getId()) return true;

        // Staff and users can view USER pages
        if ($this->getPermission() == self::PERM_USER) {
            return ($user->isUser() || $user->isStaff());
        }

        // Staff can view STAFF pages
        if ($this->getPermission() == self::PERM_STAFF) {
            return $user->isStaff();
        }

        return false;
    }

    public function canEdit(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isUser()) return false;
        if ($user->isAdmin()) return true;
        if ($this->getUserId() == $user->getId()) return true;

        // Only allow Editors to edit home page regardless of permissions
        if ($this->getUrl() == self::getHomeUrl()) {
            return $user->hasPermission(User::PERM_EDITOR);
        }

        // Allow any staff to edit public or user pages
        if (
            $this->getPermission() == self::PERM_PUBLIC ||
            $this->getPermission() == self::PERM_USER
        ) {
            return $user->isStaff();
        }

        // Only Editors can edit staff pages
        if ($this->getPermission() == self::PERM_STAFF) {
            return $user->hasPermission(User::PERM_EDITOR);
        }

        return false;
    }

    public function canDelete(?User $user): bool
    {
        // Do not allow deletion of currently assigned home page
        if ($this->getUrl() == self::getHomeUrl()) {
            return false;
        }

        if (!$user) return false;
        if ($user->isUser()) return false;
        if ($user->isAdmin()) return true;
        if ($this->getUserId() == $user->getId()) return true;

        // Allow any staff to delete public or user pages
        if (
            $this->getPermission() == self::PERM_PUBLIC ||
            $this->getPermission() == self::PERM_USER
        ) {
            return $user->isStaff();
        }

        // Only Editors can delete staff pages
        if ($this->getPermission() == self::PERM_STAFF) {
            return $user->hasPermission(User::PERM_EDITOR);
        }

        return false;
    }

}