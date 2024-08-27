<?php
namespace App\Db\Traits;

use App\Db\Page;

trait PageTrait
{

    private ?Page $_page = null;

    public function getPage(): ?Page
    {
        if (!$this->_page && $this->pageId) {
            $this->_page = Page::find($this->pageId);
        }
        return $this->_page;
    }

}
