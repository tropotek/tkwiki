<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @package Db
 */

/**
 * A column map 
 *
 * @package Db
 */
class Tk_Db_Map_Integer extends Tk_Db_Map_Interface
{
    
    /**
     * create a string
     *
     * @param string $propertyName 
     * @param array $columnNames (optional)
     * @return Tk_Db_Map_Integer
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    function getPropertyValue($row)
    {
        $name = $this->getColumnName();
        if (isset($row[$name])) {
            return (int)$row[$name];
        }
    }
    
    function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        if (isset($obj->$name)) {
            $value = (int)$obj->$name;
            return array($this->getColumnName() => $value);
        }
        return array($this->getColumnName() => 0);
    }
    
    
}
