<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A property map for the Date object.
 * Used by the Object Loader system
 *
 * @package Tk
 */
class Tk_Type_DatePropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return 'Tk_Type_Date';
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        $date = null;
        if ($value != null) {
            $date = Tk_Type_Date::parseIso($value);
        }
        return $date;
    }
    
    function getColumnValue($row)
    {
        $str = null;
        $value = parent::getColumnValue($row);
        if ($value != null) {
            $date = Tk_Type_Date::parseIso($value);
            if ($date == null) {
                $date = new Tk_Type_Date($value);
            }
            $str = $date->getIsoDate(true);
        }
        return $str;
    }
    
    function getSerialType()
    {
        return Tk_Object::T_INTEGER;
    }
    
    function getSerialName()
    {
        return 'timestamp';
    }
    
    function getSerialValue($row)
    {
        $value = parent::getSerialValue($row);
        if ($value !== null && $value != '0000-00-00 00:00:00') {
            $date = Tk_Type_Date::parseIso($value);
            return $date->getTimestamp();
        }
    }

}
