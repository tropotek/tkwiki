<?php
namespace App\Db\Traits;

use App\Db\Content;

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
     * @return int
     */
    public function getContentId()
    {
        $id = 0;
        if ($this->getContent())
            $id = $this->getContent()->getId();
        return $id;
    }

    /**
     * @return Content|null
     */
    public function getContent()
    {
        if (!$this->_content) {
            try {
                $this->_content = \App\Db\ContentMap::create()->findByPageId($this->getId(), \Tk\Db\Tool::create('created DESC', 1))->current();
//            /$this->_content = \App\Db\ContentMap::create()->find($this->getContentId());
            } catch (\Exception $e) { \Tk\Log::error($e->__toString());}
        }
        return $this->_content;
    }

}
