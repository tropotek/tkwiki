<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @package Color
 */

/**
 * A column map 
 * 
 * The 6 character hex value is expected to be stored in the DB.
 * 
 *
 * @package Color
 */
class Tk_Color_Map extends Tk_Db_Map_Interface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return Tk_Db_Map_Color
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    function getPropertyValue($row)
    {
        $name = $this->getColumnName();
        if (isset($row[$name])) {
            return Color::create($row[$name]);
        }
    }

    function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        if (isset($obj->$name)) {
            $value = $obj->$name->toString();
            $value = Db::getDb()->escapeString($value);
            return array($this->getColumnName() => enquote($value));
        }
        return array($this->getColumnName() => 'NULL');
    }
    

}
