<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use Tk\Db;
use Tk\Db\Filter;
use Tk\Db\Model;

class MenuItem extends Model
{
    use PageTrait;

    const TYPE_ITEM     = 'item';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_DIVIDER  = 'divider';

    public int    $menuItemId = 0;
    public ?int   $parentId   = null;
    public ?int   $pageId     = null;
    public int    $orderId    = 0;
    public string $type       = self::TYPE_ITEM;
    public string $name       = '';


    public function save(): void
    {
        $map = static::getDataMap();

        $values = $map->getArray($this);
        if ($this->menuItemId) {
            $values['menu_item_id'] = $this->menuItemId;
            Db::update('menu_item', 'menu_item_id', $values);
        } else {
            unset($values['menu_item_id']);
            Db::insert('menu_item', $values);
            $this->menuItemId = Db::getLastInsertId();
        }

        $this->reload();
    }

    public function delete(): bool
    {
        return (false !== Db::delete('menu_item', ['menu_item_id' => $this->menuItemId]));
    }

    public function isType(string $type): bool
    {
        return ($this->type == $type);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MenuItem
    {
        $this->name = $name;
        return $this;
    }

    public function hasChildren(): bool
    {
        return count(self::findByParentId($this->menuItemId)) > 0;
    }

    /**
     * @return array<int,MenuItem>
     */
    public static function findByParentId(int $parentId): array
    {
        if ($parentId == 0) {
            return Db::query("
                SELECT *
                FROM menu_item
                WHERE parent_id IS NULL
                ORDER BY order_id",
                [],
                self::class
            );
        }
        return Db::query("
            SELECT *
            FROM menu_item
            WHERE parent_id = :parentId
            ORDER BY order_id",
            compact('parentId'),
            self::class
        );
    }

    public static function find(int $id): ?static
    {
        return Db::queryOne("
                SELECT *
                FROM menu_item
                WHERE menu_item_id = :id",
            compact('id'),
            self::class
        );
    }

    /**
     * @return array<int,MenuItem>
     */
    public static function findAll(): array
    {
        return Db::query("
            SELECT *
            FROM menu_item
            ORDER BY order_id",
            null,
            self::class
        );
    }

    public static function updateItem(int $menuItemId, ?int $parentId, int $orderId, string $name): bool
    {
        $ok = Db::execute("
            UPDATE menu_item SET
                parent_id = :parentId,
                order_id = :orderId,
                name = :name
            WHERE menu_item_id = :menuItemId",
            compact(
                'parentId',
                'orderId',
                'name',
                'menuItemId'
            )
        );
        return (false !== $ok);
    }

    /**
     * @return array<int,MenuItem>
     */
    public static function findFiltered(array|Filter $filter): array
    {
        $filter = Filter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'LOWER(a.name) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.menu_item_id) LIKE LOWER(:search) OR ';
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['menuItemId'] = $filter['id'];
        }
        if (!empty($filter['menuItemId'])) {
            if (!is_array($filter['menuItemId'])) $filter['menuItemId'] = [$filter['menuItemId']];
            $filter->appendWhere('a.menu_item_id IN :menuItemId AND ');
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = [$filter['exclude']];
            $filter->appendWhere('a.example_id NOT IN :exclude AND ');
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
            if (!is_array($filter['type'])) $filter['type'] = [$filter['type']];
            $filter->appendWhere('a.type IN :type AND ');
        }

        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = :name AND ');
        }

        return Db::query("
            SELECT *
            FROM menu_item a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }
}