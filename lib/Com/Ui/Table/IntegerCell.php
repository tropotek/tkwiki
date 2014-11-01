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
class Com_Ui_Table_IntegerCell extends Com_Ui_Table_Cell
{
    
    /**
     * get the parameter data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $str = 0 . '';
        $method = $this->getMethod($obj);
        if ($method) {
            $str = $obj->$method() . '';
        }
        return $str;
    }

}