<?php

namespace App\Db\Traits;

use App\Db\Content;
use App\Db\ContentMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait ContentTrait
{

    /**
     * @var Content
     */
    private $_content = null;


    /**
     * Get the subject related to this object
     *
     * @return Content|null
     */
    public function getContent()
    {
        if (!$this->_content) {
            try {
                $this->_content = \App\Db\ContentMap::create()->findByPageId($this->id, \Tk\Db\Tool::create('created DESC', 1))->current();
                //$this->_content = ContentMap::create()->find($this->getContentId());
            } catch (Exception $e) { }
        }
        return $this->_content;
    }

}
