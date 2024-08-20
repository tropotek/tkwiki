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
class MenuItemMap extends Mapper
{

    public function makeDataMaps(): void
    {
        if (!$this->getDataMappers()->has(self::DATA_MAP_DB)) {
            $map = new DataMap();
            $map->addDataType(new Db\Integer('menuItemId', 'menu_item_id'));
            $map->addDataType(new Db\Integer('parentId', 'parent_id'));
            $map->addDataType(new Db\Integer('pageId', 'page_id'));
            $map->addDataType(new Db\Integer('orderId', 'order_id'));
            $map->addDataType(new Db\Text('type'));
            $map->addDataType(new Db\Text('name'));

            $this->addDataMap(self::DATA_MAP_DB, $map);
        }
    }

    /**
     * @return Result|MenuItem[]
     */
    public function findByParentId(int $parentId, ?Tool $tool = null): Result
    {
        return $this->findFiltered(['parentId' => $parentId], $tool);
    }

    /**
     * @return Result|MenuItem[]
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
            $w = 'a.name LIKE :search OR ';
            $w .= 'a.menu_item_id LIKE :search OR ';
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['menuItemId'] = $filter['id'];
        }
        if (!empty($filter['menuItemId'])) {
            $filter->appendWhere('(a.menu_item_id IN (:menuItemId)) AND ');
        }

        if (!empty($filter['exclude'])) {
            $filter->appendWhere('(a.menu_item_id NOT IN (:exclude)) AND ');
        }

        if (isset($filter['parentId'])) {
            if (!$filter['parentId']) {
                $filter->appendWhere('a.parent_id IS NULL AND ');
            } else {
                $filter->appendWhere('a.parent_id = :parentId AND ');
            }
        }
        if (isset($filter['pageId'])) {
            if (!$filter['pageId']) {
                $filter->appendWhere('a.page_id IS NULL AND ');
            } else {
                $filter->appendWhere('a.page_id = :pageId AND ');
            }
        }
        if (!empty($filter['type'])) {
            $filter->appendWhere('(a.type IN (:type)) AND ');
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = :name AND ');
        }


        return $filter;
    }

    public function updateItem(int $menuItemId, ?int $parentId, int $orderId, string $name): bool
    {
        $sql = <<<SQL
UPDATE menu_item SET parent_id = :parentId, order_id = :orderId, name = :name WHERE menu_item_id = :menuItemId
SQL;
        $stm = $this->getDb()->prepare($sql);

        return $stm->execute(compact(
            'parentId',
            'orderId',
            'name',
            'menuItemId'
        ));
    }

}