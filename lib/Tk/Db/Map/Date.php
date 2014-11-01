<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @package Date
 */

/**
 * A column map 
 *
 * @package Date
 */
class Tk_Db_Map_Date extends Tk_Db_Map_Interface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return Tk_Db_Map_Date
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    function getPropertyValue($row)
    {
        $name = $this->getColumnName();
        if (isset($row[$name])) {
            return Tk_Type_Date::parseIso($row[$name]);
        }
    }

    function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        if (isset($obj->$name)) {
            $value = $obj->$name->getIsoDate();
            return array($this->getColumnName() => enquote($value));
        }
        return array($this->getColumnName() => 'NULL');
    }
    

}
