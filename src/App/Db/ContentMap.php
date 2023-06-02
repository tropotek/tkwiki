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
            $map->addDataType(new Db\Integer('id'), 'key');
            $map->addDataType(new Db\Integer('pageId', 'page_id'));
            $map->addDataType(new Db\Integer('userId', 'user_id'));
            $map->addDataType(new Db\Text('html'));
            $map->addDataType(new Db\Text('keywords'));
            $map->addDataType(new Db\Text('description'));
            $map->addDataType(new Db\Text('css'));
            $map->addDataType(new Db\Text('js'));
            $map->addDataType(new Db\Date('modified'));
            $map->addDataType(new Db\Date('created'));

            $this->addDataMap(self::DATA_MAP_DB, $map);
        }
        
        if (!$this->getDataMappers()->has(self::DATA_MAP_FORM)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('id'), 'key');
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
            $map->addDataType(new Form\Integer('id'), 'key');
            $map->addDataType(new Form\Integer('pageId'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Text('html'));
            $map->addDataType(new Form\Text('keywords'));
            $map->addDataType(new Form\Text('description'));
            $map->addDataType(new Form\Text('css'));
            $map->addDataType(new Form\Text('js'));

            $this->addDataMap(self::DATA_MAP_TABLE, $map);
        }
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

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->escapeString($filter['keywords']) . '%';
            $w = '';
            //$w .= sprintf('a.name LIKE %s OR ', $this->quote($kw));
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

        if (!empty($filter['pageId'])) {
            $filter->appendWhere('a.page_id = %s AND ', (int)$filter['pageId']);
        }
        if (!empty($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }
        if (!empty($filter['html'])) {
            $filter->appendWhere('a.html = %s AND ', $this->quote($filter['html']));
        }
        if (!empty($filter['keywords'])) {
            $filter->appendWhere('a.keywords = %s AND ', $this->quote($filter['keywords']));
        }
        if (!empty($filter['description'])) {
            $filter->appendWhere('a.description = %s AND ', $this->quote($filter['description']));
        }


        return $filter;
    }

}