<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use App\Db\Traits\UserTrait;
use Tk\Db;
use Tk\Db\Filter;
use Tk\Db\Model;

class Content extends Model
{
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

    public \DateTimeImmutable $created;


    public function __construct()
    {
        $this->created  = new \DateTimeImmutable();
        $this->userId = User::getAuthUser()->userId ?? 0;
    }

    public static function cloneContent(Content $src): Content
    {
        $dst = new self();
        $dst->userId = User::getAuthUser()->userId ?? 0;

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

    /**
     * @return array<int,Content>
     */
    public static function findFiltered(array|Filter $filter): array
    {
        $filter = Filter::create($filter);
        $filter->appendFrom(static::getPrimaryTable() . ' a');

        if (!empty($filter['search'])) {
            $filter['lSearch'] = '%' . strtolower($filter['search']) . '%';
            $w  = "a.content_id = :search ";
            $w .= "OR LOWER(CONCAT_WS(' ', a.html, a.keywords, a.description)) LIKE :lSearch ";
            if ($w) $filter->appendWhere('AND (%s)', $w);
        }

        if (!empty($filter['id'])) {
            $filter['contentId'] = $filter['id'];
        }
        if (!empty($filter['contentId'])) {
            if (!is_array($filter['contentId'])) $filter['contentId'] = [$filter['contentId']];
            $filter->appendWhere('AND a.content_id IN :contentId');
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = [$filter['exclude']];
            $filter->appendWhere('AND a.example_id NOT IN :exclude');
        }

        if (!empty($filter['pageId'])) {
        $filter->appendWhere('AND a.page_id = :pageId');
        }

        if (!empty($filter['userId'])) {
            $filter->appendWhere('AND a.user_id = :userId');
        }

        return Db::query("
            SELECT *
            FROM {$filter->getSql()}",
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