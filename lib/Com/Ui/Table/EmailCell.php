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
class Com_Ui_Table_EmailCell extends Com_Ui_Table_Cell
{
    
    /**
     * Get the table data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $meth = 'get' . ucfirst($this->getProperty());
        $email = $obj->$meth();
        return '<a href="mailto:' . $email . '" >' . $email . '</a>';
    }
}