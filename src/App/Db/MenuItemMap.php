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

class MenuItemMap extends Mapper
{

    public function makeDataMaps(): void
    {
        if (!$this->getDataMappers()->has(self::DATA_MAP_DB)) {
            $map = new DataMap();
            $map->addDataType(new Db\Integer('id'), 'key');
            $map->addDataType(new Db\Integer('parentId', 'parent_id'));
            $map->addDataType(new Db\Integer('pageId', 'page_id'));
            $map->addDataType(new Db\Integer('orderId', 'order_id'));
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

        if (isset($filter['parentId'])) {
            $filter->appendWhere('a.parent_id = %s AND ', (int)$filter['parentId']);
        }
        if (!empty($filter['pageId'])) {
            $filter->appendWhere('a.page_id = %s AND ', (int)$filter['pageId']);
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
        }


        return $filter;
    }

    public function updateItem(int $itemId, int $parentId, int $orderId, string $name): bool
    {
        $sql = <<<SQL
UPDATE menu_item SET parent_id = ?, order_id = ?, name = ? WHERE id = ?
SQL;
        $stm = $this->getDb()->prepare($sql);
        return $stm->execute([
            $parentId,
            $orderId,
            $name,
            $itemId
        ]);
    }

}