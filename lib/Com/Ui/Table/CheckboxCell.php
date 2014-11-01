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
class Com_Ui_Table_CheckboxCell extends Com_Ui_Table_Cell
{
    
    protected $cbName = 'cb';
    
    /**
     * Get the table data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        return '<input type="checkbox" name="' . $this->getEventKey('cb') . '[]" value="' . $obj->getId() . '" />';
    }
    
    /**
     * get teh table data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getTableHeader()
    {
        $str = '<input type="checkbox" name="' . $this->getEventKey('cb') . '" onchange="selectAllCheckbox(this);"
                             onmouseover="setStatusText(\'Select all records\');"
                             onmouseout="setStatusText();"/>';
        return $str;
    }
}