<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use Tk\Db\Mapper\Model;

class MenuItem extends Model
{
    use PageTrait;

    const TYPE_ITEM     = 'item';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_DIVIDER  = 'divider';

    public int $id = 0;

    public ?int $parentId = null;

    public ?int $pageId = null;

    public int $orderId = 0;

    public string $type = self::TYPE_ITEM;

    public string $name = '';


    public function __construct()
    {

    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): MenuItem
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    public function setPageId(?int $pageId): MenuItem
    {
        $this->pageId = $pageId;
        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): MenuItem
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function isType(string $type): bool
    {
        return ($this->type == $type);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): MenuItem
    {
        $this->type = $type;
        return $this;
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
        return $this->getMapper()->findByParentId($this->getId())->count() > 0;
    }

}