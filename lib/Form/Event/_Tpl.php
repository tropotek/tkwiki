<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * {description}
 * 
 * @package Form
 */
class Form_Event_Template extends Form_Event
{
    
    /**
     * Create a new Event object
     *
     * @return Form_Event_Template
     */
    static function create()
    {
        $obj = new self();
        return $obj;
    }
    
    function init()
    {
        $this->addTriger(self::TRIGER_ON_ALL);
        //$this->setTrigerList(array('add', 'save', 'update'));
    }
    /**
     * NOTE: Never save an object unless you know what you are doing
     */
    function execute()
    {
        /* @var $object Tk_Db_Object */
        $object = $this->getObject();
        
        
    }
}