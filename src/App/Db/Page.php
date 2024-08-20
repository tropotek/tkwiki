<?php
namespace App\Db;

use App\Factory;
use Bs\Db\User;
use Dom\Template;
use Tk\Registry;
use Tk\Uri;
use Bs\Db\Traits\UserTrait;
use Bs\Db\Traits\TimestampTrait;
use Tt\Db;
use Tt\DbFilter;
use Tt\DbModel;

class Page extends DbModel
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
     * NOTE: Admin users have all permissions at all times
     */
    const PERM_PRIVATE            = 9;
    const PERM_STAFF              = 2;
    const PERM_USER               = 1;
	const PERM_PUBLIC             = 0;

    const PERM_LIST = [
        self::PERM_PRIVATE   => 'Private',
        self::PERM_STAFF     => 'Staff',
        self::PERM_USER      => 'User',
        self::PERM_PUBLIC    => 'Public',
    ];

    const PERM_HELP = [
        self::PERM_PRIVATE   => 'VIEW: author, EDIT: author, DELETE: author',
        self::PERM_STAFF     => 'VIEW: staff users, EDIT: staff editors, DELETE: staff editors',
        self::PERM_USER      => 'VIEW: registered users, EDIT: staff, DELETE: staff',
        self::PERM_PUBLIC    => 'VIEW: anyone, EDIT: staff, DELETE: staff',
    ];

    public int    $pageId       = 0;
    public int    $userId       = 0;
    public string $category     = '';
    public string $title        = '';
    public string $url          = '';
    public int    $views        = 0;    // todo: implement the page counter and display it
    public int    $permission   = 0;
    public bool   $published    = true;
    public bool   $titleVisible = true;
    public \DateTime $modified;
    public \DateTime $created;

    private ?Content $_content = null;


    public function __construct()
    {
        $this->_TimestampTrait();
    }

    public function save(): void
    {
        $map = static::getDataMap();
        $values = $map->getArray($this);

        if (!$this->url) {
            $this->url = self::makePageUrl($this->title);
        }

        if ($this->pageId) {
            $values['page_id'] = $this->pageId;
            Db::update('page', 'page_id', $values);
        } else {
            unset($values['page_id']);
            Db::insert('page', $values);
            $this->pageId = Db::getLastInsertId();
        }

        $this->reload();
    }

    public function delete(): bool
    {
        // do not delete first page and first menu item
        if ($this->url == self::getHomeUrl()) {
            return false;
        }

        if (false !== Db::delete('page', ['page_id' => $this->pageId])) {
            self::deleteLinkByPageId($this->pageId);
            return true;
        }
        return false;
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
            $comp = Page::findByUrl($url);
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

    /**
     * Find all page links and add them to the links table, so we can track orphaned pages
     */
    public static function indexLinks(Page $page, string $html): void
    {
        try {
            $doc = Template::load($html)->getDocument(false);
            Page::deleteLinkByPageId($page->pageId);
            $nodeList = $doc->getElementsByTagName('a');
            /** @var \DOMElement $node */
            foreach ($nodeList as $node) {
                $regs = [];
                if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                    if (isset ($regs[1]) && $page->url != $regs[1]) {
                        Page::insertLink($page->pageId, $regs[1]);
                    }
                }
            }
        } catch (\Exception $e) { }
    }

    // todo refactor these to a single method, be sure to update the delete() method
    public static function getHomeUrl(): string
    {
        return Factory::instance()->getRegistry()->get('wiki.page.default', '');
    }

    public static function homeUrl(): Uri
    {
        return Uri::create(self::getHomeUrl());
    }

    public function getContent(): ?Content
    {
        if (!$this->_content) {
            $this->_content = Content::findCurrent($this->pageId);
        }
        return $this->_content;
    }
    public function getPageUrl(): Uri
    {
        return Uri::create('/'.trim($this->url, '/'));
    }
    /**
     * Get the page permission level as a string
     */
    public function getPermissionLabel(): string
    {
        return self::PERM_LIST[$this->permission] ?? '';
    }

    public static function findPage(string $url): ?Page
    {
        if ($url == self::DEFAULT_TAG) {
            $url = self::getHomeUrl();
        }
        return self::findByUrl($url);
    }

    public static function find(int $id): ?static
    {
        return Db::queryOne("
                SELECT *
                FROM page
                WHERE page_id = :id",
            compact('id'),
            self::class
        );
    }

    public static function findAll(): array
    {
        return Db::query("
            SELECT *
            FROM page",
            null,
            self::class
        );
    }

    public static function findByUrl($url): ?static
    {
        return self::findFiltered(['url' => $url])[0] ?? null;
    }

    public static function findFiltered(array|DbFilter $filter): array
    {
        $filter = DbFilter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'a.title LIKE :search OR ';
            $w .= 'a.category LIKE :search OR ';
            $w .= 'a.page_id LIKE :search OR ';
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['pageId'] = $filter['id'];
        }
        if (!empty($filter['pageId'])) {
            if (!is_array($filter['pageId'])) $filter['pageId'] = [$filter['pageId']];
            $filter->appendWhere('a.page_id IN :contentId AND ');
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = [$filter['exclude']];
            $filter->appendWhere('a.page_id NOT IN :exclude AND ');
        }


        if (!empty($filter['author'])) {
            if (!is_array($filter['author'])) $filter['author'] = [$filter['author']];
            $filter->appendWhere('a.user_id IN :author AND ');
        }

        if (!empty($filter['userId'])) {
            if (!is_array($filter['userId'])) $filter['userId'] = [$filter['userId']];
            $filter->appendWhere('a.user_id IN :userId AND ');
        }

        if (!empty($filter['template'])) {
            $filter->appendWhere('a.template = :template AND ');
        }

        if (!empty($filter['category'])) {
            $filter->appendWhere('a.category = :category AND ');
        }

        if (!empty($filter['title'])) {
            $filter->appendWhere('a.title = :title AND ');
        }

        if (!empty($filter['url'])) {
            $filter->appendWhere('a.url = :url AND ');
        }

        if (isset($filter['published'])) {
            $filter['published'] = truefalse($filter['published']);
            $filter->appendWhere('a.published = :published AND ');
        }

        if (!empty($filter['permission'])) {
            if (!is_array($filter['permission'])) $filter['permission'] = [$filter['permission']];
            $filter->appendWhere('a.permission IN :permission AND ');
        }

        // TODO: create a single query for this
//        if (isset($filter['orphaned'])) {
//            $filter['homeUrl'] = Registry::instance()->get('wiki.page.default');
//            $filter->appendFrom(' LEFT JOIN links b USING (url)');
//            $filter->appendWhere('b.page_id IS NULL AND (a.url != :homeUrl) ');
//        }

        // TODO: create a single query for this
        // Do a full text search on the content
//        if (isset($filter['fullSearch'])) {
//            $filter->appendFrom('  JOIN (
//                SELECT MAX(created), content_id, page_id, html
//                FROM content
//                WHERE MATCH (html) AGAINST (%s IN NATURAL LANGUAGE MODE)
//                GROUP BY page_id
//            ) c USING (page_id)', $this->quote($filter['fullSearch'] ?? ''));
//            $filter->appendWhere('c.content_id IS NOT NULL');
//        }


        return Db::query("
            SELECT *
            FROM page a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }

    /**
     * Get a list of all existing categories
     */
    public static function getCategoryList(string $search = ''): array
    {
        $list = Db::queryList("
            SELECT DISTINCT category
            FROM page
            WHERE category != ''
            AND category LIKE :search",
            '',
            'category',
            [
                'search' => '%' . $search . '%'
            ]
        );
        return $list;
    }

    /**
     * Test if the supplied pageId is an orphaned page
     */
    public static function isOrphan(int $pageId): bool
    {
        $homeUrl = Registry::instance()->get('wiki.page.default');
        return Db::queryBool("
            SELECT count(*)
            FROM page a
                LEFT JOIN links b USING (url)
            WHERE b.page_id IS NULL
            AND (a.url != :homeUrl AND a.page_id = :pageId)",
            compact('homeUrl', 'pageId')
        );
    }

    public static function linkExists(int $pageId, string $pageUrl): bool
    {
        return Db::queryBool("
            SELECT count(*)
            FROM links
            WHERE page_id = :pageId
            AND url = :pageUrl",
            compact('pageId', 'pageUrl')
        );
    }

    public static function insertLink(int $pageId, string $pageUrl): int
    {
        if (self::linkExists($pageId, $pageUrl)) return false;
        return Db::insertIgnore('links', compact('pageId', 'pageUrl'));
    }

    public static function deleteLink($pageId, $pageUrl): bool
    {
        if (!self::linkExists($pageId, $pageUrl)) return false;
        return (false !== Db::delete('links', compact('pageId', 'pageUrl')));
    }

    public static function deleteLinkByPageId(int $pageId): bool
    {
        return (false !== Db::delete('links', compact('pageId')));
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->userId) {
            $errors['userId'] = 'Invalid user ID value';
        }
        if (!$this->title) {
            $errors['title'] = 'Please enter a title for the page';
        }
//        if (!$this->category) {
//            $errors['category'] = 'Please enter a category for the page';
//        }

        $comp = self::findByUrl($this->url);
        if ($comp && $comp->pageId != $this->pageId) {
            $errors['url'] = 'This url already exists, try again';
        }
        // Match any existing system routes
        if (self::routeExists($this->url)) {
            $errors['url'] = 'This url already exists, try again';
        }

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
        if ($this->permission == self::PERM_PUBLIC) return true;
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        if ($this->userId == $user->userId) return true;

        // Try this see if it works as expected
        if (!$this->published) return false;

        // Staff and users can view USER pages
        if ($this->permission == self::PERM_USER) {
            return ($user->isMember() || $user->isStaff());
        }

        // Staff can view STAFF pages
        if ($this->permission == self::PERM_STAFF) {
            return $user->isStaff();
        }

        return false;
    }

    public function canEdit(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isMember()) return false;
        if ($user->isAdmin()) return true;
        if ($this->userId == $user->userId) return true;

        // Only allow Editors to edit home page regardless of permissions
        if ($this->url == self::getHomeUrl()) {
            return $user->hasPermission(Permissions::PERM_EDITOR);
        }

        // Allow any staff to edit public or user pages
        if (
            $this->permission == self::PERM_PUBLIC ||
            $this->permission == self::PERM_USER
        ) {
            return $user->isStaff();
        }

        // Only Editors can edit staff pages
        if ($this->permission == self::PERM_STAFF) {
            return $user->hasPermission(Permissions::PERM_EDITOR);
        }

        return false;
    }

    public function canDelete(?User $user): bool
    {
        // Do not allow deletion of currently assigned home page
        if ($this->url == self::getHomeUrl()) {
            return false;
        }

        if (!$user) return false;
        if ($user->isMember()) return false;
        if ($user->isAdmin()) return true;
        if ($this->userId == $user->userId) return true;

        // Allow any staff to delete public or user pages
        if (
            $this->permission == self::PERM_PUBLIC ||
            $this->permission == self::PERM_USER
        ) {
            return $user->isStaff();
        }

        // Only Editors can delete staff pages
        if ($this->permission == self::PERM_STAFF) {
            return $user->hasPermission(Permissions::PERM_EDITOR);
        }

        return false;
    }

}