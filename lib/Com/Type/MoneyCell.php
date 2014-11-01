<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Com_Type_MoneyCell
 *
 * @package Com
 */
class Com_Type_MoneyCell extends Com_Ui_Table_Cell
{
    
    /**
     * get the parameter data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $str = '$0.00';
        $method = $this->getMethod($obj);
        if ($method) {
            $money = $obj->$method();
            if ($money instanceof Tk_Type_Money) {
                $str = $money->toString();
            }
        }
        return $str;
    }

}