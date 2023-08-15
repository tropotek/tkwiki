<?php
namespace App\Db;

use Tk\DataMap\DataMap;
use Tk\Db\Mapper\Filter;
use Tk\Db\Mapper\Mapper;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

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
            $map->addDataType(new Db\TextEncrypt('url'));
            $map->addDataType(new Db\TextEncrypt('username'));
            $map->addDataType(new Db\TextEncrypt('password'));
            $map->addDataType(new Db\TextEncrypt('otp'));
            $map->addDataType(new Db\TextEncrypt('keys'));
            $map->addDataType(new Db\TextEncrypt('notes'));
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
    }

    /**
     * @return Result|Secret[]
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
            $w  = 'a.name LIKE :search OR ';
            $w .= 'a.url LIKE :search OR ';
            $w .= 'a.secret_id LIKE :search OR ';
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['secretId'] = $filter['id'];
        }
        if (!empty($filter['secretId'])) {
            $filter->appendWhere('(a.secret_id IN (:secretId)) AND ');
        }

        if (!empty($filter['exclude'])) {
            $filter->appendWhere('(a.secret_id NOT IN (:exclude)) AND ');
        }

        if (!empty($filter['author'])) {
            $filter->appendWhere('(a.user_id = :author) OR ');
        }

        if (!empty($filter['userId'])) {
            $filter->appendWhere('a.user_id = :userId AND ');
        }

        if (!empty($filter['permission'])) {
            $perm = 0;
            foreach ($filter['permission'] as $p) {
                $perm |= $p;
            }
            $filter['permission'] = $perm;
            $filter->appendWhere('a.permission = :permission AND ');
        }

        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = :name AND ');
        }

        if (!empty($filter['url'])) {
            $filter->appendWhere('a.url = :url AND ');
        }

        return $filter;
    }

}