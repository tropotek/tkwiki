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
class Table_Cell_Email extends Table_Cell
{
    
    /**
     * Create a new cell
     *
     * @param string $property The name of the property to access in the row object $obj->$property
     * @param string $name If null the property name is used EG: 'propName' = 'Prop Name'
     * @return Table_Cell_String
     */
    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    
    /**
     * get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * @param Tk_Object $obj
     * @return Dom_Template Alternativly you can return a plain HTML string
     */
    function getTd($obj)
    {
        $this->rowClass = array(); // reset row class list
        
        $email = $this->getPropertyData($this->property, $obj);
        return '<a href="mailto:' . $email . '" title="Compose an email to this address.">' . $email . '</a>';
    }
    
}
