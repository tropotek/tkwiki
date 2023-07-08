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

class ContentMap extends Mapper
{

    public function makeDataMaps(): void
    {
        if (!$this->getDataMappers()->has(self::DATA_MAP_DB)) {
            $map = new DataMap();
            $map->addDataType(new Db\Integer('contentId', 'content_id'));
            $map->addDataType(new Db\Integer('pageId', 'page_id'));
            $map->addDataType(new Db\Integer('userId', 'user_id'));
            $map->addDataType(new Db\Text('html'));
            $map->addDataType(new Db\Text('keywords'));
            $map->addDataType(new Db\Text('description'));
            $map->addDataType(new Db\Text('css'));
            $map->addDataType(new Db\Text('js'));
            $map->addDataType(new Db\Date('created'));

            $this->addDataMap(self::DATA_MAP_DB, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_FORM)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('contentId'));
            $map->addDataType(new Form\Integer('pageId'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('html'));
            $map->addDataType(new Form\Text('keywords'));
            $map->addDataType(new Form\Text('description'));
            $map->addDataType(new Form\Text('css'));
            $map->addDataType(new Form\Text('js'));

            $this->addDataMap(self::DATA_MAP_FORM, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_TABLE)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('contentId'));
            $map->addDataType(new Form\Integer('pageId'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('html'));
            $map->addDataType(new Form\Text('keywords'));
            $map->addDataType(new Form\Text('description'));
            $map->addDataType(new Form\Text('css'));
            $map->addDataType(new Form\Text('js'));
            $map->addDataType(new Form\Date('created'))->setDateFormat('d/m/Y h:i:s');

            $this->addDataMap(self::DATA_MAP_TABLE, $map);
        }
    }


    /**
     * @return Result|Content[]
     */
    public function findByPageId(int $pageId, ?Tool $tool = null): Result
    {
        return $this->findFiltered(['pageId' => $pageId], $tool);
    }

    /**
     * @return Result|Content[]
     */
    public function findByUserId(int $userId, ?Tool $tool = null): Result
    {
        return $this->findFiltered(['userId' => $userId], $tool);
    }

    /**
     * @return Result|Content[]
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
            $w = '';
            //$w .= sprintf('a.name LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['search'])) {
                $id = (int)$filter['search'];
                $w .= sprintf('a.content_id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['contentId'] = $filter['id'];
        }
        if (!empty($filter['contentId'])) {
            $w = $this->makeMultiQuery($filter['contentId'], 'a.content_id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.content_id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['pageId'])) {
            $filter->appendWhere('a.page_id = %s AND ', (int)$filter['pageId']);
        }

        if (!empty($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }


        return $filter;
    }


    /**
     * returns an array of \stdClass objects with the user_id, modified, created fields:
     *
     * Array (
     *   [0] => stdClass Object (
     *     [user_id] => 114
     *     [modified] => 2016-06-23 08:37:27
     *     [created] => 2016-06-23 08:37:27
     *   )
     * )
     * TODO: rewrite this query me thinks////
     */
    public function findContributors(int $pageId): array
    {
        // pgsql
        $sql = sprintf('SELECT DISTINCT ON (user_id) user_id, created FROM %s WHERE page_id = %s ORDER BY user_id, created DESC ',
            $this->getDb()->quoteParameter($this->getTable()), (int)$pageId);
        // Mysql
        if($this->getDb()->getDriver() == 'mysql') {
            $sql = sprintf('SELECT DISTINCT user_id, created FROM %s WHERE page_id = %s GROUP BY user_id ORDER BY user_id, created DESC ',
                $this->getDb()->quoteParameter($this->getTable()), (int)$pageId);
        }

        $stmt = $this->getDb()->query($sql);
        $res = array();
        foreach($stmt as $row) {
            $res[] = $row;
        }
        return $res;
    }

}