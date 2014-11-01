<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * A property map for the Money object.
 * Used by the Object Loader system
 *
 * Restrictions: No currency type can be loaded, therefore only
 * Money Objects that use the system wide default Currency
 * can be loaded. See the Money object to determin the default
 * currency type.
 *
 * @package Tk
 */
class Tk_Type_MoneyPropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return 'Tk_Type_Money';
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        $value = intval($value);
        return new Tk_Type_Money($value);
    }
    
    function getColumnValue($row)
    {
        $str = null;
        $value = parent::getColumnValue($row);
        if ($value !== null) {
            $str = intval($value);
        }
        return $str;
    }
    
    function getSerialType()
    {
        return Tk_Object::T_INTEGER;
    }
    
    function getSerialName()
    {
        return 'amount';
    }
    
    function getSerialValue($row)
    {
        $value = parent::getSerialValue($row);
        if ($value !== null) {
            return intval($value);
        }
    }

}
