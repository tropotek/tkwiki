<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @package Money
 */

/**
 * A column map 
 * 
 * All money is assumed to be stored in the DB in it units
 * For AUD $1.00 will be save in the DB as 100.
 * 
 *
 * @package Money
 */
class Tk_Db_Map_Money extends Tk_Db_Map_Interface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return Tk_Db_Map_Money
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    function getPropertyValue($row)
    {
        $name = $this->getColumnName();
        if (isset($row[$name])) {
            return Tk_Type_Money::create($row[$name]);
        }
    }

    function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        if (isset($obj->$name)) {
            $value = (int)$obj->$name->getAmount();
            return array($this->getColumnName() => $value);
        }
        return array($this->getColumnName() => 0);
    }
    
    
}
