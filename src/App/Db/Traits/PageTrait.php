<?php
namespace App\Db\Traits;




use App\Db\Page;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait PageTrait
{

    /**
     * @var Page
     */
    private $_page = null;


    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @param int|Page $pageId
     * @return PageTrait
     */
    public function setPageId($pageId)
    {
        if ($pageId instanceof Page) $pageId = $pageId->getId();
        $this->pageId = (int)$pageId;
        return $this;
    }

    /**
     * @return Page|null
     * @throws \Exception
     */
    public function getPage()
    {
        if (!$this->_page)
            $this->_page = \App\Db\PageMap::create()->find($this->getPageId());
        return $this->_page;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validatePageId($errors = [])
    {
        if (!$this->getPageId()) {
            $errors['pageId'] = 'Invalid value: pageId';
        }
        return $errors;
    }


}
