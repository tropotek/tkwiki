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
class Table_Event_Clear extends Form_ButtonEvent
{
    
    /**
     * Create an instance of this object
     *
     * @return Table_Event_Clear
     */
    static function create()
    {
        $obj = new self('clear');
        $obj->setLabel('Clear All');
        return $obj;
    }
    
    /**
     * (non-PHPdoc)
     * @see Form_Event::execute()
     */
    function execute()
    {
        $table = $this->getForm()->getContainer();
        if (Tk_Session::exists($table->getSessionHash())) {
            Tk_Session::delete($table->getSessionHash());
        }
//        $url = Tk_Request::requestUri();
//        // Delete any filds in the the GET query string (c-net test)
//        foreach ($table->getFilterList() as $filter) {
//            if (array_key_exists($filter->getName(), $url->getQueryFields())) {
//                $url->delete($filter->getName());
//            }
//        }
        $this->setRedirect(Tk_Request::requestUri());
    }
    
}