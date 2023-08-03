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
        return $this->selectFromFilter($this->makeQuery(Filter::create($filter)), $tool);
    }

    public function makeQuery(Filter $filter): Filter
    {
        $filter->appendFrom('%s a', $this->quoteParameter($this->getTable()));
        if (!empty($filter['search'])) {
            $kw = '%' . $this->escapeString($filter['search']) . '%';
            $w = sprintf('a.title LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.category LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['search'])) {
                $id = (int)$filter['search'];
                $w .= sprintf('a.page_id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['pageId'] = $filter['id'];
        }
        if (!empty($filter['pageId'])) {
            $w = $this->makeMultiQuery($filter['pageId'], 'a.page_id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.page_id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['author'])) {
            $filter->appendWhere('(a.user_id = %s) OR ', $this->quote($filter['author']));
        }

        if (!empty($filter['userId'])) {
            $w = $this->makeMultiQuery($filter['userId'], 'a.user_id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['template'])) {
            $filter->appendWhere('a.template = %s AND ', $this->quote($filter['template']));
        }

        if (!empty($filter['category'])) {
            $filter->appendWhere('a.category = %s AND ', $this->quote($filter['category']));
        }

        if (!empty($filter['title'])) {
            $filter->appendWhere('a.title = %s AND ', $this->quote($filter['title']));
        }

        if (!empty($filter['url'])) {
            $filter->appendWhere('a.url = %s AND ', $this->quote($filter['url']));
        }

        if (is_bool($filter['published'])) {
            $filter->appendWhere('a.published = %s AND ', (int)$filter['published']);
        }

        if (!empty($filter['permission'])) {
            $w = $this->makeMultiQuery($filter['permission'], 'a.permission');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (isset($filter['orphaned'])) {
            $homeUrl = $this->getRegistry()->get('wiki.page.default');
            $filter->appendFrom(' LEFT JOIN links b USING (url)');
            $filter->appendWhere('b.page_id IS NULL AND (a.url != %s) ',
                $this->quote($homeUrl)
            );
        }

        // Do a full text search on the content
        if (isset($filter['fullSearch'])) {
            $filter->appendFrom('  JOIN (
                SELECT MAX(created), content_id, page_id, html
                FROM content
                WHERE MATCH (html) AGAINST (%s IN NATURAL LANGUAGE MODE)
                GROUP BY page_id
            ) c ON (a.content_id = c.page_id)', $this->quote($filter['fullSearch'] ?? ''));
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