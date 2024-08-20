<?php
namespace App\Db;

use Tk\DataMap\DataMap;
use Tk\Db\Mapper\Filter;
use Tk\Db\Mapper\Mapper;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Tk\DataMap\Table;

/**
 * @deprecated
 */
class PageMap extends Mapper
{

    public function makeDataMaps(): void
    {
        if (!$this->getDataMappers()->has(self::DATA_MAP_DB)) {
            $map = new DataMap();
            $map->addDataType(new Db\Integer('pageId', 'page_id'));
            $map->addDataType(new Db\Integer('userId', 'user_id'));
            $map->addDataType(new Db\Text('template'));
            $map->addDataType(new Db\Text('category'));
            $map->addDataType(new Db\Text('title'));
            $map->addDataType(new Db\Text('url'));
            $map->addDataType(new Db\Integer('views'));
            $map->addDataType(new Db\Integer('permission'));
            $map->addDataType(new Db\Boolean('titleVisible', 'title_visible'));
            $map->addDataType(new Db\Boolean('published'));
            $map->addDataType(new Db\Date('modified'));
            $map->addDataType(new Db\Date('created'));

            $this->addDataMap(self::DATA_MAP_DB, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_FORM)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('pageId'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('template'));
            $map->addDataType(new Form\Text('category'));
            $map->addDataType(new Form\Text('title'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Integer('views'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Form\Boolean('titleVisible'));
            $map->addDataType(new Form\Boolean('published'));

            $this->addDataMap(self::DATA_MAP_FORM, $map);
        }
    }

    public function find(mixed $id): null|\Tk\Db\Mapper\Model|Page
    {
        return parent::find($id);
    }

    public function findByUrl($url): null|\Tk\Db\Mapper\Model|Page
    {
        $filter = [
            'url' => $url
        ];
        return $this->findFiltered($filter)->current();
    }

    /**
     * @return Result|Page[]
     */
    public function findFiltered(array|Filter $filter, ?Tool $tool = null): Result
    {
        return $this->prepareFromFilter($this->makeQuery(Filter::create($filter)), $tool);
    }

    public function makeQuery(Filter $filter): Filter
    {
        $filter->appendFrom('%s a', $this->quoteParameter($this->getTable()));

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $this->getDb()->escapeString($filter['search']) . '%';
            $w  = 'a.title LIKE :search OR ';
            $w .= 'a.category LIKE :search OR ';
            $w .= 'a.page_id LIKE :search OR ';
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['pageId'] = $filter['id'];
        }
        if (!empty($filter['pageId'])) {
            $filter->appendWhere('(a.page_id IN (:pageId)) AND ');
        }

        if (!empty($filter['exclude'])) {
            $filter->appendWhere('(a.page_id NOT IN (:exclude)) AND ');
        }

        if (!empty($filter['author'])) {
            $filter->appendWhere('(a.user_id = :author) OR ');
        }

        if (!empty($filter['userId'])) {
            $filter->appendWhere('(a.user_id IN (:userId)) AND ');
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

        if (is_bool($filter['published'])) {
            $filter->appendWhere('a.published = :published AND ');
        }

        if (!empty($filter['permission'])) {
            $filter->appendWhere('(a.permission IN (:permission)) AND ');
        }

        if (isset($filter['orphaned'])) {
            $filter['homeUrl'] = $this->getRegistry()->get('wiki.page.default');
            $filter->appendFrom(' LEFT JOIN links b USING (url)');
            $filter->appendWhere('b.page_id IS NULL AND (a.url != :homeUrl) ');
        }

        // Do a full text search on the content
        if (isset($filter['fullSearch'])) {
            $filter->appendFrom('  JOIN (
                SELECT MAX(created), content_id, page_id, html
                FROM content
                WHERE MATCH (html) AGAINST (%s IN NATURAL LANGUAGE MODE)
                GROUP BY page_id
            ) c USING (page_id)', $this->quote($filter['fullSearch'] ?? ''));
            $filter->appendWhere('c.content_id IS NOT NULL');
        }

        return $filter;
    }

    /**
     * Get a list of all existing categories
     */
    public function getCategoryList(string $search = ''): array
    {
        $sql = <<<SQL
            SELECT DISTINCT a.category
            FROM page a
            WHERE a.category != ''
            AND a.category LIKE :search
        SQL;
        $stm = $this->getDb()->prepare($sql);
        $stm->execute([
            'search' => '%' . $search . '%'
        ]);
        return $stm->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Test if the supplied pageId is an orphaned page
     */
    public function isOrphan(int $pageId): bool
    {
        $homeUrl = $this->getRegistry()->get('wiki.page.default');
        $sql = <<<SQL
SELECT a.page_id
FROM page a LEFT JOIN links b USING (url)
WHERE b.page_id IS NULL AND (a.url != :homeUrl AND a.page_id = :pageId)
SQL;
        $stm = $this->getDb()->prepare($sql);
        $stm->execute(compact('homeUrl', 'pageId'));

        if ($stm->rowCount() > 0) return true;
        return false;
    }

    public function linkExists(int $pageId, string $pageUrl): bool
    {
        $sql = <<<SQL
SELECT COUNT(*) as i FROM links WHERE page_id = :pageId AND url = :pageUrl
SQL;
        $stm = $this->getDb()->prepare($sql);
        $stm->execute(compact('pageId', 'pageUrl'));
        $value = $stm->fetch();
        if (!$value) return false;
        return ($value->i > 0);
    }

    public function insertLink(int $pageId, string $pageUrl): int
    {
        if ($this->linkExists($pageId, $pageUrl)) {
            return false;
        }
        $sql = <<<SQL
INSERT INTO links VALUES (:pageId, :pageUrl)
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute(compact('pageId', 'pageUrl'));
    }

    public function deleteLink($pageId, $pageUrl): bool
    {
        if (!$this->linkExists($pageId, $pageUrl)) {
            return false;
        }
        $sql = <<<SQL
DELETE FROM links WHERE page_id = :pageId AND url = :pageUrl LIMIT 1
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute(compact('pageId', 'pageUrl'));
    }

    public function deleteLinkByPageId(int $pageId): bool
    {
        $sql = <<<SQL
DELETE FROM links WHERE page_id = :pageId
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute(compact('pageId'));
    }

}