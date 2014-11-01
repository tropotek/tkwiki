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
class Com_Ui_Table_BooleanCell extends Com_Ui_Table_Cell
{
    
    /**
     * get the parameter data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $str = 'No';
        $method = $this->getMethod($obj);
        if ($method) {
            $b = $obj->$method() === true ? true : false;
            if ($b) {
                $str = 'Yes';
            }
        }
        return $str;
    }

}