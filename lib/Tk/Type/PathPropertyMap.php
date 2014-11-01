<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A property map for the Path object.
 * Used by the Object Loader system
 *
 *
 * @package Tk
 */
class Tk_Type_PathPropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return 'Tk_Type_Path';
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        return new Tk_Type_Path($value);
    }
    
    function getColumnValue($row)
    {
        $str = null;
        $value = parent::getColumnValue($row);
        if ($value !== null) {
            $value = new Tk_Type_Path($value);
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
        return 'pathname';
    }
    
    function getSerialValue($row)
    {
        $value = parent::getSerialValue($row);
        if ($value !== null) {
            return $value;
        }
    }
}
?>
