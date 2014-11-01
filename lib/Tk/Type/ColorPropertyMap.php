<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A property map for the Color object.
 * Used by the Object Loader system
 *
 *
 * @package Tk
 */
class Tk_Type_ColorPropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return 'Tk_Type_Color';
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        return new Tk_Type_Color($value);
    }
    
    function getColumnValue($row)
    {
        $str = null;
        $value = parent::getColumnValue($row);
        if ($value !== null) {
            $value = new Tk_Type_Color($value);
            $str = $value->toString();
        }
        return $str;
    }
    
    function getSerialType()
    {
        return Tk_Object::T_STRING;
    }
    
    function getSerialName()
    {
        return 'hex';
    }
    
    function getSerialValue($row)
    {
        $value = parent::getSerialValue($row);
        if ($value !== null) {
            return $value;
        }
    }
}