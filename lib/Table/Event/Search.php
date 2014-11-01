<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 *
 * @package Table
 */
class Table_Event_Search extends Form_ButtonEvent
{
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Table_Event_Search
     */
    static function create($name)
    {
        $obj = new self($name);
        return $obj;
    }
    
    function execute()
    {
        if ($this->getForm()->hasErrors()) {
            return;
        }
        
        $arr = array();
        /* @var $filter Form_Field */
        foreach ($this->getForm()->getContainer()->getFilterList() as $filter) {
            $arr[$filter->getName()] = $filter->getSubFieldValueList();
        }
        Tk_Session::set($this->getForm()->getContainer()->getSessionHash(), $arr);
        
        $url = Tk_Request::requestUri();
        // Test Code for c-net, should be used for all tables
        foreach ($this->getForm()->getContainer()->getFilterList() as $filter) {
            if (array_key_exists($filter->getName(), $url->getQueryFields())) {
                $url->delete($filter->getName());
            }
        }
        $this->setRedirect($url);
    }
}