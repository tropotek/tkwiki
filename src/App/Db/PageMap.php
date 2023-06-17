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
            $map->addDataType(new Db\Integer('id'));
            $map->addDataType(new Db\Integer('userId', 'user_id'));
            $map->addDataType(new Db\Text('template'));
            $map->addDataType(new Db\Text('title'));
            $map->addDataType(new Db\Text('url'));
            $map->addDataType(new Db\Integer('views'));
            $map->addDataType(new Db\Integer('permission'));
            $map->addDataType(new Db\Boolean('published'));
            $map->addDataType(new Db\Date('modified'));
            $map->addDataType(new Db\Date('created'));

            $this->addDataMap(self::DATA_MAP_DB, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_FORM)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('id'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('template'));
            $map->addDataType(new Form\Text('title'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Integer('views'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Form\Boolean('published'));

            $this->addDataMap(self::DATA_MAP_FORM, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_TABLE)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('id'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('template'));
            $map->addDataType(new Form\Text('title'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Integer('views'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Table\Boolean('published'));
            $map->addDataType(new Form\Date('modified'))->setDateFormat('d/m/Y h:i:s');
            $map->addDataType(new Form\Date('created'))->setDateFormat('d/m/Y h:i:s');

            $this->addDataMap(self::DATA_MAP_TABLE, $map);
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
            if (is_numeric($filter['search'])) {
                $id = (int)$filter['search'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['author'])) {
            $filter->appendWhere('(a.user_id = %s) OR ', $this->quote($filter['author']));
        }

        if (!empty($filter['id'])) {
            $w = $this->makeMultiQuery($filter['id'], 'a.id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['userId'])) {
            $w = $this->makeMultiQuery($filter['userId'], 'a.userId');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['template'])) {
            $filter->appendWhere('a.template = %s AND ', $this->quote($filter['template']));
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
                SELECT MAX(created), id, page_id, html
                FROM content
                WHERE MATCH (html) AGAINST (%s IN NATURAL LANGUAGE MODE)
                GROUP BY page_id
            ) c ON (a.id = c.page_id)', $this->quote($filter['fullSearch'] ?? ''));
            $filter->appendWhere('c.id IS NOT NULL');
        }

        return $filter;
    }

    /**
     * Test if the supplied pageId is an orphaned page
     */
    public function isOrphan(int $pageId): bool
    {
        $homeUrl = $this->getRegistry()->get('wiki.page.default');
        $sql = <<<SQL
SELECT a.id
FROM page a LEFT JOIN links b USING (url)
WHERE b.page_id IS NULL AND (a.url != ? AND a.id = ?)
SQL;
        $stm = $this->getDb()->prepare($sql);
        $stm->execute([
            $homeUrl,
            $pageId
        ]);

        if ($stm->rowCount() > 0) return true;
        return false;
    }

    public function linkExists(int $pageId, string $pageUrl): bool
    {
        $sql = <<<SQL
SELECT COUNT(*) as i FROM links WHERE page_id = ? AND url = ?
SQL;
        $stm = $this->getDb()->prepare($sql);
        $stm->execute([
            $pageId,
            $pageUrl
        ]);
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
INSERT INTO links VALUES (?, ?)
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute([
            $pageId,
            $pageUrl
        ]);
    }

    public function deleteLink($pageId, $pageUrl): bool
    {
        if (!$this->linkExists($pageId, $pageUrl)) {
            return false;
        }
        $sql = <<<SQL
DELETE FROM links WHERE page_id = ? AND url = ? LIMIT 1
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute([
            $pageId,
            $pageUrl
        ]);
    }

    public function deleteLinkByPageId(int $pageId): bool
    {
        $sql = <<<SQL
DELETE FROM links WHERE page_id = ?
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute([$pageId]);
    }

}