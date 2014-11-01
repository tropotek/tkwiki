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
class Tk_Db_Map_DataUrl extends Tk_Db_Map_Interface
{
    /**
     *
     * @param string $propertyName
     * @param array $columnName
     * @return Tk_Db_Map_DataUrl
     */
    static function create($propertyName, $columnNames = array())
    {
        return new self($propertyName, $columnNames);
    }
    
    function getPropertyValue($row)
    {
        $name = $this->getColumnName();
        if (isset($row[$name])) {
            return Tk_Type_Url::createDataUrl($row[$name]);
        }
    }

    function getColumnValue($obj)
    {
        $name = $this->getPropertyName();
        if (isset($obj->$name)) {
            $base =Tk_Type_Url::createDataUrl('');
            $value = $obj->$name->getPath();
            if (strlen($base->getPath()) > 1) {
                $value = str_replace($base->getPath(), '', $value);
            }
            return array($this->getColumnName() => enquote($value));
        }
        return array($this->getColumnName() => enquote(''));
    }
    

}
