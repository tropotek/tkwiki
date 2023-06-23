<?php

namespace App\Db\Traits;

use App\Db\Page;
use App\Db\PageMap;
use Exception;

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
     * @param int $pageId
     * @return $this
     */
    public function setPageId($pageId)
    {
        $this->pageId = (int)$pageId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Page|null
     */
    public function getPage()
    {
        if (!$this->_page) {
            try {
                $this->_page = PageMap::create()->find($this->getPageId());
            } catch (Exception $e) {
            }
        }
        return $this->_page;
    }

    /**
     * TODO: asdsadasdadas asd asasdas
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
