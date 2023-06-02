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
            $map->addDataType(new Db\Integer('id'), 'key');
            $map->addDataType(new Db\Integer('userId', 'user_id'));
            $map->addDataType(new Db\Text('type'));
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
            $map->addDataType(new Form\Integer('id'), 'key');
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('type'));
            $map->addDataType(new Form\Text('title'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Integer('views'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Form\Boolean('published'));

            $this->addDataMap(self::DATA_MAP_FORM, $map);
        }
        
        if (!$this->getDataMappers()->has(self::DATA_MAP_TABLE)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('id'), 'key');
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('type'));
            $map->addDataType(new Form\Text('title'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Integer('views'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Table\Boolean('published'));

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
            'url' => $url,
            'type' => Page::TYPE_PAGE
        ];
        return $this->findFiltered($filter)->current();
    }

    public function findNavPages(Tool $tool = null): Result
    {
        $filter = [
            'type' => Page::TYPE_NAV
        ];
        return $this->findFiltered($filter, $tool);
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

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.title LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
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

        if (!empty($filter['type'])) {
            $filter->appendWhere('a.type = %s AND ', $this->quote($filter['type']));
        }

        if (!empty($filter['title'])) {
            $filter->appendWhere('a.title = %s AND ', $this->quote($filter['title']));
        }

        if (!empty($filter['url'])) {
            $filter->appendWhere('a.url = %s AND ', $this->quote($filter['url']));
        }

        if (is_bool($filter['published'] ?? '')) {
            $filter->appendWhere('a.published = %s AND ', (int)$filter['published']);
        }

        if (!empty($filter['permission'])) {
            $w = $this->makeMultiQuery($filter['permission'], 'a.permission');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}