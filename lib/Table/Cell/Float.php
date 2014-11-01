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
class Table_Cell_Float extends Table_Cell
{
    
    /**
     * @var integer
     */
    protected $places = 2;
    
    
    /**
     * Create a new cell
     *
     * @param string $property The name of the property to access in the row object $obj->$property
     * @param string $name If null the property name is used EG: 'propName' = 'Prop Name'
     * @return Table_Cell_String
     */
    static function create($property, $name = '', $places = 2)
    {
        $obj = new self($property, $name);
        $obj->places = $places;
        return $obj;
    }
    
    
    /**
     * Get the property value from the object using the supplied property name
     *
     * @param string $property
     * @param stdClass $obj
     * @return string
     */
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        if ($value) {
            // TODO: User sprintf() to get trailing zeros in returned string (EG: 0.00)
            $value = round((float)$value, $this->places);
        }
        return $value;
    }
    
    
}
