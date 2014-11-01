<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @package Db
 */

/**
 * This object is the base column mapper for object properties
 * so the object loader can serialize and unserialize objects from supplied arrays
 * 
 * @package Db
 */
abstract class Tk_Db_Map_Interface extends Tk_Object
{
    
    /**
     * @var string
     */
    protected $columnNames = array();
    
    /**
     * @var string
     */
    protected $propertyName = '';
    
    
    
    /**
     * __construct
     *
     * @param string $propertyName  The object property to map the column to.
     * @param array $columnName     The DB column names to map the object to.
     */
    function __construct($propertyName, $columnNames = array())
    {
        $this->propertyName = $propertyName;
        
        if (!is_array($this->columnNames)) {
            $columnNames = array($columnNames);
        }
        if (!count($columnNames)) {
            $columnNames = array($propertyName);
        }
        $this->columnNames = $columnNames;
    }
    
    /**
     * The object's instance property name
     *
     * @return string
     */
    function getPropertyName()
    {
        return $this->propertyName;
    }
    
    /**
     * This is the data source column name.
     * EG: $row('column1' => 10, 'column2' => 'string', 'column3' => 1.00);
     * The source column names are 'column1', 'column2' and 'column 3'
     *
     * @return array
     */
    function getColumnNames()
    {
        return $this->columnNames;
    }
    
    /**
     * This is the data source column name.
     * EG: $row('column1' => 10, 'column2' => 'string', 'column3' => 1.00);
     * The source column names are 'column1', 'column2' and 'column 3'
     *
     * @return array
     */
    function getColumnName()
    {
        return $this->columnNames[0];
    }
    
    /**
     * Return an object form the DB source row
     *
     * @param array $row
     * @return mixed
     */
    function getPropertyValue($row)
    {
        $name = $this->getColumnName();
        if (isset($row[$name])) {
            return $row[$name];
        }
    }
    
    /**
     * Return an array map of values containing the db raw values
     *
     * @param Tk_Db_Object $obj
     * @return array
     */
    function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        if (isset($obj->$name)) {
            $value = $obj->$name;
            return array($this->getColumnName() => $value);
        }
        return array($this->getColumnName() => null);
    }
    

    
    
    
    
}
