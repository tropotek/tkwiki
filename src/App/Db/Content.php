<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use App\Db\Traits\UserTrait;
use Bs\Traits\CreatedTrait;
use Tk\Db;
use Tk\Db\Filter;
use Tk\Db\Model;

class Content extends Model
{
    use CreatedTrait;
    use UserTrait;
    use PageTrait;

    public int    $contentId   = 0;
    public int    $pageId      = 0;
    public int    $userId      = 0;
    public string $html        = '';
    public string $keywords    = '';
    public string $description = '';
    public string $css         = '';
    public string $js          = '';
    public \DateTime $created;


    public function __construct()
    {
        $this->_CreatedTrait();
        $this->userId = User::getAuthUser()?->userId ?? 0;
    }

    public static function cloneContent(Content $src): Content
    {
        $dst = new self();
        $dst->userId = User::getAuthUser()?->userId ?? 0;

        $dst->pageId      = $src->pageId;
        $dst->html        = $src->html;
        $dst->keywords    = $src->keywords;
        $dst->description = $src->description;
        $dst->css         = $src->css;
        $dst->js          = $src->js;

        return $dst;
    }

    public function save(): void
    {
        $map = static::getDataMap();

        $values = $map->getArray($this);
        if ($this->contentId) {
            $values['content_id'] = $this->contentId;
            Db::update('content', 'content_id', $values);
        } else {
            unset($values['content_id']);
            Db::insert('content', $values);
            $this->contentId = Db::getLastInsertId();
        }

        $this->reload();
    }

    public function delete(): bool
    {
        return (false !== Db::delete('content', ['content_id' => $this->contentId]));
    }

    /**
     * compare this content to the supplied content and return true if they differ
     * Use this to check if a new content should be saved on edit
     */
    public function diff(Content $content): bool
    {
        if ($this->html != $content->html) {
            return true;
        }
        if ($this->keywords != $content->keywords) {
            return true;
        }
        if ($this->description != $content->description) {
            return true;
        }
        if ($this->css != $content->css) {
            return true;
        }
        if ($this->js != $content->js) {
            return true;
        }
        return false;
    }

    public static function find(int $id): ?self
    {
        return Db::queryOne("
                SELECT *
                FROM content
                WHERE content_id = :id",
            compact('id'),
            self::class
        );
    }

    /**
     * @return array<int,Content>
     */
    public static function findAll(): array
    {
        return Db::query("
            SELECT *
            FROM content",
            null,
            self::class
        );
    }

    /**
     * @return array<int,Content>
     */
    public static function findFiltered(array|Filter $filter): array
    {
        $filter = Filter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'LOWER(a.html) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.keywords) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.description) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.content_id) LIKE LOWER(:search) OR ';
            $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['contentId'] = $filter['id'];
        }
        if (!empty($filter['contentId'])) {
            if (!is_array($filter['contentId'])) $filter['contentId'] = [$filter['contentId']];
            $filter->appendWhere('a.content_id IN :contentId AND ');
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = [$filter['exclude']];
            $filter->appendWhere('a.example_id NOT IN :exclude AND ');
        }

        if (!empty($filter['pageId'])) {
        $filter->appendWhere('a.page_id = :pageId AND ');
        }

        if (!empty($filter['userId'])) {
            $filter->appendWhere('a.user_id = :userId AND ');
        }

        return Db::query("
            SELECT *
            FROM content a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }

    public function validate(): array
    {
        $errors = [];

        if (!$this->userId) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->pageId) {
            $errors['pageId'] = 'Invalid value: pageId';
        }

        return $errors;
    }

}