<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Base class for Table objects
 *
 *
 * @package Table
 */
abstract class Table_Element extends Tk_Object
{
    
    /**
     * @var Table
     */
    protected $table = null;
    
    
    
    /**
     * Execute the Table Element
     */
    abstract function execute($list);
    
    
    
    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     */
    function setTable($table)
    {
        $this->id = $table->getId();
        $this->table = $table;
    }
    
    /**
     * Get the parent table object
     *
     * @return Table
     */
    function getTable()
    {
        return $this->table;
    }
    
    /**
     * Get the request key for an event.
     * This can be nessesary to avoid event collisions when using multiple Tables.
     *
     * @param string $event
     * @return string
     */
    function getEventKey($eventName)
    {
        return Tk::createEventKey($eventName, $this->table->getId());
    }
    
    
}