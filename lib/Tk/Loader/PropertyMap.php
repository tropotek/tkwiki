<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This object is the base column mapper for object properties
 * so the object loader can serialize and unserialize objects from supplied arrays
 * 
 * @package Tk
 */
abstract class Tk_Loader_PropertyMap extends Tk_Object
{
    
    /**
     * @var string
     */
    protected $columnName = '';
    
    /**
     * @var string
     */
    protected $propertyName = '';
    
    /**
     * __construct
     *
     * @param string $propertyName The object property to map the column to.
     * @param string $columnName The table name to map from.
     */
    function __construct($propertyName, $columnName = '')
    {
        $this->propertyName = $propertyName;
        if (!$columnName) {
            $columnName = $propertyName;
        }
        $this->columnName = $columnName;
    }
    
    /**
     * The object's instance property type
     * eg: Tk_Object::T_STRING or an object 'Tk_Type_Date'
     *
     * @return string
     */
    function getPropertyType()
    {
        return Tk_Object::T_STRING;
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
     * Returns the object property value in the object's required property type form from the row.
     * Override this to return the data in its required object form.
     * (From the data source to the object)
     *
     * @param array $row
     * @return mixed
     */
    function getPropertyValue($row)
    {
        if (!array_key_exists($this->getColumnName(), $row)) {
            return;
        }
        return $row[$this->getColumnName()];
    }
    
    /**
     * This is the data source column name.
     * EG: $row('column1' => 10, 'column2' => 'string', 'column3' => 1.00);
     * The source column names are 'column1', 'column2' and 'column 3'
     *
     * @return string
     */
    function getColumnName()
    {
        return $this->columnName;
    }
    
    /**
     * Convert the object value to its native php type value
     * (From the object (unserialised row) to the data source)
     *
     * @param mixed $value
     * @return mixed A php native type
     * @see Tk_Object constants for types
     */
    function getColumnValue($row)
    {
        if (!array_key_exists($this->getPropertyName(), $row)) {
            return null;
        }
        
        $value = $row[$this->getPropertyName()];
        if (is_array($value)) {
            $value = $row[$this->getPropertyName()][$this->getSerialName()];
        }
        return $value;
    }
    
    /**
     * When serialising the property, the PHP native type to use.
     * By Default the property type is used.
     *
     * @return string
     * @see Tk_Object constants for types
     */
    function getSerialType()
    {
        return $this->getPropertyType();
    }
    
    /**
     * By Default the property name is used.
     * Useful for serialising object types, such as Tk_Type_Money (amount), Tk_Type_Date (timestamp), etc
     *
     * @return string
     */
    function getSerialName()
    {
        return $this->getPropertyName();
    }
    
    /**
     * Get the property value from the data source array.
     * This value should be of a PHP native type to match the serial type given.
     * (From Data Source into a new object)
     *
     * @param array $row
     * @return mixed
     * @see Tk_Object constants for types
     */
    function getSerialValue($row)
    {
        if (!array_key_exists($this->getColumnName(), $row)) {
            return;
        }
        $value = $row[$this->getColumnName()];
        if (is_array($value)) {
            $value = $row[$this->getColumnName()][$this->getSerialName()];
        }
        return $value;
    }

}

/**
 * A column map fo the string type
 * This object also removes any windows carrage/return ("\r\n") characers and replaces them
 * with unix return characters ("\n").
 *
 * @package Tk
 */
class Tk_Loader_StringPropertyMap extends Tk_Loader_PropertyMap
{
    function getPropertyType()
    {
        return Tk_Object::T_STRING;
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        if ($value === null) {
            return null;
        }
        return str_replace("\r\n", "\n", $value);
    }
    
    function getColumnValue($row)
    {
        $value = parent::getColumnValue($row);
        $value = str_replace("\r\n", "\n", $value);
        return $value;
    }
    
    function getSerialValue($row)
    {
        $value = parent::getSerialValue($row);
        return str_replace("\r\n", "\n", $value);
    }

}

/**
 * A column map fo the encrypted string type
 *
 * @package Tk
 */
class Tk_Loader_EncryptStringPropertyMap extends Tk_Loader_StringPropertyMap
{
    
    function getPropertyType()
    {
        return Tk_Object::T_ENCRYPT_STRING;
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        if ($value === null) {
            return null;
        }
        return Tk_Util_Encrypt::decrypt($value);
    }
    
    function getColumnValue($row)
    {
        return Tk_Util_Encrypt::encrypt(parent::getColumnValue($row));
    }
    
    function getSerialValue($row)
    {
        $value = parent::getSerialValue($row);
        return Tk_Util_Encrypt::decrypt($value);
    }
}

/**
 * A column map for th eInteger standard type
 *
 * @package Tk
 */
class Tk_Loader_IntegerPropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return Tk_Object::T_INTEGER;
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        if ($value === null) {
            return null;
        }
        return (double)$value;
    }
    
    function getColumnValue($row)
    {
        return (double)parent::getColumnValue($row);
    }
}

/**
 * A column map for the Float standard data type
 *
 * @package Tk
 */
class Tk_Loader_FloatPropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return Tk_Object::T_FLOAT;
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        if ($value === null) {
            return null;
        }
        return floatval($value);
    }
    
    function getColumnValue($row)
    {
        return floatval(parent::getColumnValue($row));
    }
}

/**
 * A column map for the boolean standard data type
 *
 * @package Tk
 */
class Tk_Loader_BooleanPropertyMap extends Tk_Loader_PropertyMap
{
    
    function getPropertyType()
    {
        return Tk_Object::T_BOOLEAN;
    }
    
    function getPropertyValue($row)
    {
        $value = parent::getPropertyValue($row);
        if ($value === null) {
            return null;
        }
        return intval($value) == 1 ? true : false;
    }
    
    function getColumnValue($row)
    {
        return (parent::getColumnValue($row) == true) ? 1 : 0;
    }

}