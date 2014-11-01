<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Dk objects to a table
 *
 *
 * @package Com
 */
interface Com_Ui_Table_TreeInterface
{
    
    /**
     * Get any children objects
     * Return an empty array if no children are defined
     *
     * @return array
     */
    function getChildren();
    
    /**
     * Get the parent of this object if available
     * Return null if no parent available
     *
     * @return Com_Ui_Table_TreeTableInterface
     */
    function getParent();

}
