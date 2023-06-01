<?php
namespace App\Db;

use App\Db\Traits\UserTrait;
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


    public int $id = 0;

    public int $userId = 0;

    public string $type = self::TYPE_PAGE;

    public string $title = '';

    public string $url = '';

    public int $views = 0;

    public int $permission = 0;

    public \DateTime $modified;

    public \DateTime $created;



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
            if ($comp) {
                if (preg_match('/(.+)(_([0-9]+))$/', $url, $regs)) {
                    $url = $regs[1] . '_' . ((int)$regs[3]+1);
                } else {
                    $url = $url.'_1';
                }
            }
        } while($comp);
        return $url;
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
        // Cannot delete first page adn first menu item
        $exclude = [1, 2];
        if (in_array($this->getId(), $exclude)) return 0;

        // remove page any locks (this could be redundant and left up to the expired cleanup)
//        LockMap::create()->unlock($this->getId());

        // delete all page links referred to by this page.
//        $this->getMapper()->deleteLinkByPageId($this->getId());

        // TODO: This may be redundant with new MYSQL foreign keys, test to see    ;-)
        // Remove all content
//        $contentList = \App\Db\ContentMap::create()->findByPageId($this->getId());
//        foreach ($contentList as $c) {
//            $c->delete();
//        }

        return parent::delete();
    }

    static public function findPage(string $url): ?Page
    {
        if ($url == self::DEFAULT_TAG) {
            $url = Config::instance()->get('wiki.page.default');
        }
        return PageMap::create()->findByUrl($url);
    }

    static public function getHomeUrl(): string
    {
        return \App\Config::getInstance()->get('wiki.page.default');
    }


    public function setType(string $type): Page
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
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

    /**
     * Get the page permission level as a string
     */
    public function getPermissionLabel(): string
    {
        return match ($this->permission) {
            self::PERMISSION_PRIVATE => 'private',
            self::PERMISSION_PROTECTED => 'protected',
            default => 'public',
        };
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->type) {
            $errors['type'] = 'Invalid value: type';
        }

        if (!$this->title) {
            $errors['title'] = 'Invalid value: title';
        }

//        if (!$this->permission) {
//            $errors['permission'] = 'Invalid value: permission';
//        }

        return $errors;
    }

}