<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Com_Type_DateCell
 *
 * @package Com
 */
class Com_Type_DateCell extends Com_Ui_Table_Cell
{
    
    /**
     * get the parameter data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $str = 'N/A';
        $method = $this->getMethod($obj);
        if ($method) {
            $date = $obj->$method();
            if ($date instanceof Tk_Type_Date) {
                //$str = $date->getIsoDate(true);
                $str = $date->toString(Tk_Type_Date::F_MED_DATE);
            }
        }
        return $str;
    }

}