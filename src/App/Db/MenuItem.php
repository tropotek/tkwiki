<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use Tt\Db;
use Tt\DbFilter;
use Tt\DbModel;

class MenuItem extends DbModel
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


    /**
     * Find all page links and add them to the links table
     * so we can track orphaned pages
     */
    public static function indexLinks(): void
    {
        $items = MenuItem::findFiltered(['type' => self::TYPE_ITEM]);
        Page::deleteLinkByPageId(0);
        /** @var MenuItem $item */
        foreach ($items as $item) {
            $page = $item->getPage();
            if ($page) {
                Page::insertLink(0, $page->url);
            }
        }

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

    public static function findByParentId(int $parentId): array
    {
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

    public static function findFiltered(array|DbFilter $filter): array
    {
        $filter = DbFilter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'a.name LIKE :search OR ';
            $w .= 'a.menu_item_id LIKE :search OR ';
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