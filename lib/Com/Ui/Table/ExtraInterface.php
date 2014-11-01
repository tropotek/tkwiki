<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The interface is the base for actions, filters and cells
 *
 * @package Com
 */
abstract class Com_Ui_Table_ExtraInterface extends Tk_Object
{
    
    /**
     * @var Com_Ui_Table_Base
     */
    protected $table = null;
    
    /**
     * Process any events or actions on execution
     *
     */
    function doProcess()
    {
    }
    
    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Com_Ui_Table_Base $table
     */
    function setTable($table)
    {
        $this->id = $table->getId();
        $this->table = $table;
    }
    
    /**
     * Get the parent table object
     *
     * @return Com_Ui_Table_Base
     */
    function getTable()
    {
        return $this->table;
    }
    
    /**
     * Get the request key for an event. This will include the component id
     * This can be nessasery to avoid event collisions when using multiple
     * instances of a component.
     *
     * @param string $event
     * @return string
     */
    function getEventKey($event)
    {
        return $event . '_' . $this->getId();
    }
}