<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A data map class that maps database tables and columns to
 *   Objects and properties.
 *
 * @package Tk
 */
class Tk_Loader_DataMap extends Tk_Object
{
    
    /**
     * @var array
     */
    private $propertyMaps = array();
    
    /**
     * @var array
     */
    private $idPropertyMaps = array();
    
    /**
     * @var string
     */
    private $class = '';
    
    /**
     * Table name for database, tag name for XML, etc
     * Depends on the mapper using the datamap
     *
     * @var string
     */
    private $dataSrc = '';
    
    /**
     * @var array
     */
    private $params = array();
    
    /**
     * __construct
     *
     * @param string $class
     * @param string $dataSrc If null uses the classname (EG: `Tk_Obj_TextStyle` $dataSrc would be `textStyle`)
     * @param array $params
     */
    function __construct($class, $dataSrc = '', $params = array())
    {
        if (!is_array($params)) {
            throw new Tk_ExceptionIllegalArgument('Object is not of type array.');
        }
        $this->params = $params;
        
        if (is_object($class)) {
            $class = get_class($class);
        } else if (is_array($class) && count($class) > 0) {
            $obj = current($class);
            $class = get_class($obj);
        }
        if (substr($class, -6) == 'Loader') {
            $class = substr($class, 0, -6);
        }
        if (substr($class, -6) == 'Mapper') {
            $class = substr($class, 0, -6);
        }
        $this->class = $class;
        $this->dataSrc = $dataSrc;
        if (!$this->dataSrc) {
            $this->dataSrc = lcFirst(substr($class, strrpos($class, '_') + 1));
        }
    }
    
    /**
     * Get the class for this data map
     *
     * @return string
     */
    function getClass()
    {
        return $this->class;
    }
    
    /**
     * Get the data source string for this datamap
     * This is usually the table name for a DB data source
     * @return string
     */
    function getDataSrc()
    {
        return $this->dataSrc;
    }
    
    /**
     * Add a an element to the params array
     *
     * @param string $name
     * @param mixed $value
     */
    function addParameter($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    /**
     * Get the mapper parameter array.
     * This can be used to send data to objects that use the mapper
     *
     * @return array
     */
    function getParams()
    {
        return $this->params;
    }
    
    /**
     * Returns a parameter in the params array.
     *
     * @param string $name
     * @return mixed
     */
    function getParameter($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
    }
    
    /**
     * Load all column maps into one array
     *
     * @return array
     */
    function getPropertyMaps()
    {
        return array_merge($this->getPropertyList(), $this->getIdPropertyList());
    }
    
    /**
     * Makes an object Id from the database data.
     *
     * If the object has a multi field primary key then the object Id is the
     * concatenation of the primary key fields values.
     *
     * @param array $row
     * @return string
     */
    function makeObjectId($row)
    {
        $id = '';
        foreach ($this->idPropertyMaps as $map) {
            $column = $map->getColumnName();
            $id .= $row[$column];
        }
        return $id;
    }
    
    /**
     * Gets the object ID columns.
     *
     * @return array An associative array of ID columns indexed by property.
     */
    function getIdPropertyList()
    {
        return $this->idPropertyMaps;
    }
    
    /**
     * Gets the list of property mappers.
     *
     * @return array
     */
    function getPropertyList()
    {
        return $this->propertyMaps;
    }
    
    /**
     * Gets a property map by its name
     *
     * @return Tk_Loader_PropertyMap
     */
    function getPropertyMap($name)
    {
        if (array_key_exists($name, $this->propertyMaps)) {
            return $this->propertyMaps[$name];
        }
    }
    
    /**
     * Adds an object ID property
     *
     * @param string $property The property name
     * @param string $propertyType The column type
     * @param string $column The column name
     */
    function addIdProperty($property, $propertyType = '', $column = '')
    {
        if ($property instanceof Tk_Db_Map_Interface) {
            $this->idPropertyMaps[$property->getPropertyName()] = $property;
        } else {
            $this->idPropertyMaps[$property] = $this->makePropertyMap($property, $propertyType, $column);
        }
    }
    
    /**
     * Add a property to this map
     *
     * @param integer $property The property type.
     * @param string $propertyType The property name.
     * @param string $column The column name.
     */
    function addProperty($property, $propertyType = '', $column = '')
    {
        if ($property instanceof Tk_Db_Map_Interface) {
            $this->propertyMaps[$property->getPropertyName()] = $property;
        } else {
            $this->propertyMaps[$property] = $this->makePropertyMap($property, $propertyType, $column);
        }
    }
    
    /**
     * Make a column map
     * If no property argument is supplied then the column name will be used.
     *
     * @param string $property The object property name
     * @param string $propertyType The data type, can be also of type 'Tk_Type_???'
     * @param string $column The source data property name, Use if wanting to map the data to another property
     * @return Tk_Loader_PropertyMap
     * @throws Tk_ExceptionRuntime
     * @deprecated
     */
    function makePropertyMap($property, $propertyType, $column = '')
    {
        $class = '';
        switch ($propertyType) {
            case Tk_Object::T_ENCRYPT_STRING :
                $class = 'Tk_Loader_EncryptStringPropertyMap';
                break;
            case Tk_Object::T_STRING :
                $class = 'Tk_Loader_StringPropertyMap';
                break;
            case Tk_Object::T_INTEGER :
                $class = 'Tk_Loader_IntegerPropertyMap';
                break;
            case Tk_Object::T_FLOAT :
                $class = 'Tk_Loader_FloatPropertyMap';
                break;
            case Tk_Object::T_BOOLEAN :
                $class = 'Tk_Loader_BooleanPropertyMap';
                break;
            case Tk_Object::T_ARRAY :
                $class = 'Tk_Loader_StringPropertyMap';
                break;
            default :
                $class = $propertyType . 'PropertyMap';
                if (!class_exists($class)) {
                    throw new Tk_ExceptionIllegalArgument("Could not find PropertyMap `$class'.");
                }
                if (!is_subclass_of($class, 'Tk_Loader_PropertyMap')) {
                    throw new Tk_ExceptionIllegalArgument($class . " is not a Tk_Loader_PropertyMap.");
                }
        }
        if (!$class) {
            throw new Tk_Exception('Invalid class map type.');
        }
        return new $class($property, $column);
    }

}