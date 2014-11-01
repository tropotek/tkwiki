<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  This interface should be implemented in objects that
 *  use the Table_TreeRenderer object
 *
 * @package Table
 */
interface Table_TreeInterface
{
    
    /**
     * Get any children objects
     * Return an empty array if no children are defined
     *
     * @param Tk_Db_Tool $tool
     * @return array
     */
    function getChildren($tool = null);
    
    /**
     * Get the parent of this object if available
     * Return null if no parent available
     *
     * @return Com_Ui_Table_TreeTableInterface
     */
    function getParent();

}
