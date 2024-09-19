<?php
namespace App\Db;

use App\Factory;
use Bs\Db\User;
use Dom\Template;
use Tk\Uri;
use Bs\Db\Traits\UserTrait;
use Bs\Db\Traits\TimestampTrait;
use Tk\Db;
use Tk\Db\Filter;
use Tk\Db\Model;

class Page extends Model
{
    use TimestampTrait;
    use UserTrait;

    /**
     * The default tag string used for routes
     * This default/home page url can be looked up in the registry (wiki.page.home)
     */
    const DEFAULT_TAG = '__default';

    /**
     * Page permission values
     * NOTE: Admin users have all permissions, authors have all permissions to their pages
     */
    const PERM_PRIVATE  = 9;
    const PERM_STAFF    = 2;
    const PERM_MEMBER   = 1;
	const PERM_PUBLIC   = 0;

    const PERM_LIST = [
        self::PERM_PRIVATE   => 'Private',
        self::PERM_STAFF     => 'Staff',
        self::PERM_MEMBER    => 'User',
        self::PERM_PUBLIC    => 'Public',
    ];

    const STAFF_PERMS = [
        self::PERM_STAFF,
        self::PERM_MEMBER,
        self::PERM_PUBLIC,
    ];

    const MEMBER_VIEW_PERMS = [
        self::PERM_MEMBER,
        self::PERM_PUBLIC,
    ];

    public int    $pageId       = 0;
    public int    $userId       = 0;
    public int    $contentId    = 0;    // latest content ID
    public string $template     = '';
    public string $category     = '';
    public string $title        = '';
    public string $url          = '';
    public int    $views        = 0;    // todo: implement the page counter and increment it
    public int    $permission   = 0;
    public int    $linked       = 0;
    public bool   $publish      = true;
    public bool   $titleVisible = true;
    public bool   $isOrphaned   = false;
    public string $hash         = '';
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

        if (empty($this->url)) {
            $this->url = self::makePageUrl($this->title);
        }

        $values = $map->getArray($this);
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
        if ($this->url == self::getHomePage()->url) {
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
     * EG:
     *   Home, Home_1, .n
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

    public function getContent(): ?Content
    {
        if (is_null($this->_content)) {
            $this->_content = Content::find($this->contentId);
        }
        return $this->_content;
    }

    public function getUrl(): Uri
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

    /**
     * index all wiki links in this page
     */
    public static function indexPage(Page $page): void
    {
        $content = $page->getContent();
        if (is_null($content) || !trim($content->html)) return;

        // clear page links
        Db::delete('links', ['page_id' => 0]);
        Db::delete('links', ['page_id' => $page->pageId]);

        try {
            $doc = Template::load('<div>' . trim($content->html) . '</div>')->getDocument(false);
            Page::deleteLinkByPageId($page->pageId);
            $nodeList = $doc->getElementsByTagName('a');
            foreach ($nodeList as $node) {
                $regs = [];
                if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                    if (!isset($regs[1]) || $page->url == $regs[1]) continue;
                    self::insertLinkByUrl($page->pageId, $regs[1]);
                }
            }
        } catch (\Exception $e) { \Tk\Log::debug($e->__toString()); }
    }

    public static function getHomePage(): static
    {
        $homeId = intval(Factory::instance()->getRegistry()->get('wiki.page.home', 1));
        return self::find($homeId);
    }

    public static function findPage(string $url): ?Page
    {
        if ($url == self::DEFAULT_TAG) {
            $url = self::getHomePage()->url;
        }
        return self::findByUrl($url);
    }

    public static function find(int $id): ?static
    {
        return Db::queryOne("
            SELECT *
            FROM v_page
            WHERE page_id = :id",
            compact('id'),
            self::class
        );
    }

    public static function findAll(): array
    {
        return Db::query("
            SELECT *
            FROM v_page",
            null,
            self::class
        );
    }

    public static function findByHash(string $hash): ?static
    {
        $hash = trim($hash);
        if (empty($hash)) return null;

        return Db::queryOne("
            SELECT *
            FROM v_page
            WHERE hash = :hash",
            compact('hash'),
            self::class
        );
    }

    public static function findByUrl($url): ?static
    {
        return self::findFiltered(['url' => $url])[0] ?? null;
    }

    /**
     * @return array<int,Page>
     */
    public static function findViewable(array|Filter $filter): array
    {
        $filter = Filter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'LOWER(a.title) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.category) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.page_id) LIKE LOWER(:search) OR ';
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['userId']) && isset($filter['permission'])) {
            if (!is_array($filter['userId'])) $filter['userId'] = [$filter['userId']];
            $filter->appendWhere('(a.user_id IN :userId OR ');
            if (!is_array($filter['permission'])) $filter['permission'] = [$filter['permission']];
            $filter->appendWhere('a.permission IN :permission) AND ');
        } elseif (!empty($filter['userId'])) {
            if (!is_array($filter['userId'])) $filter['userId'] = [$filter['userId']];
            $filter->appendWhere('a.user_id IN :userId AND ');
        } elseif (isset($filter['permission'])) {
            if (!is_array($filter['permission'])) $filter['permission'] = [$filter['permission']];
            $filter->appendWhere('a.permission IN :permission AND ');
        }

        if (!empty($filter['category'])) {
            $filter->appendWhere('a.category = :category AND ');
        }

        if (!empty($filter['isOrphaned'])) {
            $filter['isOrphaned'] = truefalse($filter['isOrphaned']);
            $filter->appendWhere('a.is_orphaned = :isOrphaned AND ');
        }

        $filter->appendWhere('a.publish AND ');

        if (!empty($filter['fullSearch'])) {
            $filter->appendWhere('MATCH (c.html) AGAINST (:fullSearch IN NATURAL LANGUAGE MODE) AND ');

            return Db::query("
                SELECT *
                FROM v_page a
                JOIN (
                    SELECT content_id, html
                    FROM content
                    WHERE MATCH (html) AGAINST (:fullSearch IN NATURAL LANGUAGE MODE)
                ) c USING (content_id)
                {$filter->getSql()}",
                $filter->all(),
                self::class
            );
        }

        return Db::query("
            SELECT *
            FROM v_page a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }

    /**
     * @return array<int,Page>
     */
    public static function findFiltered(array|Filter $filter): array
    {
        $filter = Filter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'LOWER(a.title) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.category) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.page_id) LIKE LOWER(:search) OR ';
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

        if (!empty($filter['publish'])) {
            $filter['publish'] = truefalse($filter['publish']);
            $filter->appendWhere('a.publish = :publish AND ');
        }

        if (!empty($filter['isOrphaned'])) {
            $filter['isOrphaned'] = truefalse($filter['isOrphaned']);
            $filter->appendWhere('a.is_orphaned = :isOrphaned AND ');
        }

        if (isset($filter['permission']) && is_numeric($filter['permission'])) {
            if (!is_array($filter['permission'])) $filter['permission'] = [$filter['permission']];
            $filter->appendWhere('a.permission IN :permission AND ');
        }

        return Db::query("
            SELECT *
            FROM v_page a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }

    /**
     * Get a list of all existing categories
     * @return array<int,string>
     */
    public static function getCategoryList(string $search = ''): array
    {
        return Db::queryList("
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
    }

    public static function linkExists(int $pageId, int $linkedId): bool
    {
        return Db::queryBool("
            SELECT count(*)
            FROM links
            WHERE page_id = :pageId
            AND linked_id = :linkedId",
            compact('pageId', 'linkedId')
        );
    }

    public static function insertLinkByUrl(int $page_id, string $url): int
    {
        $linked = self::findByUrl($url);
        if (!$linked || self::linkExists($page_id, $linked->pageId)) return 0;
        $linked_id = $linked->pageId;
        return Db::insertIgnore('links', compact('page_id', 'linked_id'));
    }

    public static function insertLink(int $page_id, int $linked_id): int
    {
        if (self::linkExists($page_id, $linked_id)) return 0;
        return Db::insertIgnore('links', compact('page_id', 'linked_id'));
    }

    public static function deleteLinkByPageId(int $page_id): bool
    {
        return (false !== Db::delete('links', compact('page_id')));
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

    public static function canCreate(?User $user): bool
    {
        if (!$user || $user->isMember()) return false;
        return true;
    }

    public function canView(?User $user): bool
    {
        if ($this->permission == self::PERM_PUBLIC) return true;
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        if ($this->userId == $user->userId) return true;

        // Staff and members can view MEMBER pages
        if ($this->permission == self::PERM_MEMBER) return ($user->isMember() || $user->isStaff());
        // Staff can view STAFF pages
        if ($this->permission == self::PERM_STAFF) return $user->isStaff();

        return false;
    }

    public function canEdit(?User $user): bool
    {
        if (!$user || $user->isMember()) return false;
        if ($user->isAdmin()) return true;
        if ($this->permission == self::PERM_PUBLIC) return true;
        if ($this->userId == $user->userId) return true;

        // Staff can edit MEMBER, STAFF pages
        if (in_array($this->permission, [self::PERM_MEMBER, self::PERM_STAFF])) return $user->isStaff();

        return false;
    }

}