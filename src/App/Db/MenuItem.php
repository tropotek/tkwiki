<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use Tk\Db\Mapper\Model;

class MenuItem extends Model
{
    use PageTrait;

    public int $id = 0;

    public int $parentId = 0;

    public int $pageId = 0;

    public int $orderId = 0;

    public string $name = '';


    public function __construct()
    {

    }

    public function setParentId(int $parentId): MenuItem
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setPageId(int $pageId): MenuItem
    {
        $this->pageId = $pageId;
        return $this;
    }

    public function getPageId(): int
    {
        return $this->pageId;
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

    public function setName(string $name): MenuItem
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasChildren(): bool
    {
        return $this->getMapper()->findByParentId($this->getId())->count() > 0;
    }

}