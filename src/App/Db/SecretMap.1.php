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

class SecretMap extends Mapper
{

    public function makeDataMaps(): void
    { 
        if (!$this->getDataMappers()->has(self::DATA_MAP_DB)) {
            $map = new DataMap();
            $map->addDataType(new Db\Integer('secretId', 'secret_id'));
            $map->addDataType(new Db\Integer('userId', 'user_id'));
            $map->addDataType(new Db\Integer('permission'));
            $map->addDataType(new Db\Text('name'));
            $map->addDataType(new Db\Text('url'));
            $map->addDataType(new Db\Text('username'));
            $map->addDataType(new Db\Text('password'));
            $map->addDataType(new Db\Text('otp'));
            $map->addDataType(new Db\Text('keys'));
            $map->addDataType(new Db\Text('notes'));
            $map->addDataType(new Db\Date('modified'));
            $map->addDataType(new Db\Date('created'));

            $this->addDataMap(self::DATA_MAP_DB, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_FORM)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('secretId'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Form\Text('name'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Text('username'));
            $map->addDataType(new Form\Text('password'));
            $map->addDataType(new Form\Text('otp'));
            $map->addDataType(new Form\Text('keys'));
            $map->addDataType(new Form\Text('notes'));

            $this->addDataMap(self::DATA_MAP_FORM, $map);
        }

        if (!$this->getDataMappers()->has(self::DATA_MAP_TABLE)) {
            $map = new DataMap();
            $map->addDataType(new Form\Integer('secretId'));
            $map->addDataType(new Form\Integer('userId'));
            $map->addDataType(new Form\Integer('permission'));
            $map->addDataType(new Form\Text('name'));
            $map->addDataType(new Form\Text('url'));
            $map->addDataType(new Form\Text('username'));
            $map->addDataType(new Form\Text('password'));
            $map->addDataType(new Form\Text('otp'));
            $map->addDataType(new Form\Text('keys'));
            $map->addDataType(new Form\Text('notes'));
            $map->addDataType(new Form\Date('modified'))->setDateFormat('d/m/Y h:i:s');
            $map->addDataType(new Form\Date('created'))->setDateFormat('d/m/Y h:i:s');

            $this->addDataMap(self::DATA_MAP_TABLE, $map);
        }
    }

    /**
     * @return Result|Secret[]
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
                $w .= sprintf('a.user_id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['userId'] = $filter['id'];
        }
        if (!empty($filter['userId'])) {
            $w = $this->makeMultiQuery($filter['userId'], 'a.user_id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }
        if (!empty($filter['permission'])) {
            $filter->appendWhere('a.permission = %s AND ', (int)$filter['permission']);
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
        }
        if (!empty($filter['url'])) {
            $filter->appendWhere('a.url = %s AND ', $this->quote($filter['url']));
        }
        if (!empty($filter['username'])) {
            $filter->appendWhere('a.username = %s AND ', $this->quote($filter['username']));
        }
        if (!empty($filter['password'])) {
            $filter->appendWhere('a.password = %s AND ', $this->quote($filter['password']));
        }
        if (!empty($filter['otp'])) {
            $filter->appendWhere('a.otp = %s AND ', $this->quote($filter['otp']));
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.user_id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}