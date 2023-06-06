<?php
namespace App\Db\Traits;

use App\Db\Page;
use App\Db\PageMap;

trait PageTrait
{

    private ?Page $_page = null;


    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    public function setPageId(?int $pageId): static
    {
        $this->pageId = $pageId;
        return $this;
    }

    public function getPage(): ?Page
    {
        if (!$this->_page) {
            $this->_page = PageMap::create()->find($this->getPageId());
        }
        return $this->_page;
    }

}
