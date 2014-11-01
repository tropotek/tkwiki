<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic table Cell
 *
 *
 * @package Table
 */
class Table_Cell_Money extends Table_Cell
{
    
    /**
     * Create a new cell
     *
     * @param string $property The name of the property to access in the row object $obj->$property
     * @param string $name If null the property name is used EG: 'propName' = 'Prop Name'
     * @return Table_Cell_Money
     */
    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    
    
    /**
     * Get the property value from the object using the supplied property name
     *
     * @param string $property
     * @param Tk_Type_Money $obj
     * @return string
     */
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        $str = "";
        if ($value instanceof Tk_Type_Money) {
            $str = $value->toString();
        }
        return $str;
    }
    
}
