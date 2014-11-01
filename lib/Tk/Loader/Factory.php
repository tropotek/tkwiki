<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A basic example of an object factory.
 *
 * <code>
 * <?php
 *    $loaderFactory = Tk_Loader_Factory::getInstance();
 * ?>
 * </code>
 *
 * @package Tk
 */
class Tk_Loader_Factory extends Tk_Object
{
    /**
     * The null Character.
     * @var string
     */
    const NC = "\0";
    
    /**
     * @var Tk_Loader_Factory
     */
    protected static $instance = null;
    
    /**
     * @var array
     */
    protected static $serialTemplates = array();
    
    /**
     * @var array
     */
    protected static $serialTemplatesChk = array();
    
    /**
     * @var array
     */
    private static $loaderList = array();
    
    /**
     * This is a constructor
     * If no request session or response parameters given the default Tk objects are used.
     *
     */
    protected function __construct()
    {
    }
    
    /**
     * Get an instance of the object factory
     *
     * @return Tk_Loader_Factory
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
            class_exists('Tk_Loader_PropertyMap');
        }
        return self::$instance;
    }
    
    /**
     * Get the object loader
     *
     * @return Tk_Loader_Interface
     */
    static function getLoader($class)
    {
        return self::getInstance()->makeLoader($class);
    }
    
    /**
     * Looks for a loder class $class . 'Loader' or you could use the full mapper classname 'Ext_Obj_ObjectLoader'
     *
     * @param string
     * @return Tk_Loader_Interface
     */
    static function makeLoader($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        } else if (is_array($class) && count($class) > 0) {
            $obj = current($class);
            $class = get_class($obj);
        }
        if (substr($class, -6) != 'Loader') {
            $class = $class . 'Loader';
        }
        
        if (!array_key_exists($class, self::$loaderList)) {
            if (!class_exists($class)) {
                throw new Tk_ExceptionIllegalArgument("Could not find loader for class `$class'.");
            }
            self::$loaderList[$class] = new $class();
        }
        return self::$loaderList[$class];
    }
    
    /**
     * Get the object loader
     *
     * @param array $row
     * @param Tk_Loader_DataMap mapper
     * @return Tk_Object
     */
    static function loadObject($row, Tk_Loader_DataMap $dataMap)
    {
        if (is_array($row)) {
            if (array_key_exists($dataMap->getClass(), self::$serialTemplatesChk)) {
                if (self::$serialTemplatesChk[$dataMap->getClass()] != count($dataMap->getPropertyList())) {
                    unset(self::$serialTemplates[$dataMap->getClass()]);
                    unset(self::$serialTemplatesChk[$dataMap->getClass()]);
                }
            }
            Tk_Db_Factory::getInstance()->loadCount++;
            return self::getInstance()->doLoad($row, $dataMap);
        }
    }
    
    /**
     * Get the object's property values as an array of basic types
     *
     * @param Tk_Object $obj
     * @return array
     */
    static function getObjectValues($obj)
    {
        if (!$obj instanceof Tk_Object) {
            throw new Tk_ExceptionIllegalArgument('Obj is not of type Tk_Object.');
        }
        return self::getInstance()->doGetObjectValues(serialize($obj));
    }
    
    /**
     * Create a new object from the data in an array.
     * Loads a database row into a new object instance.
     *
     * Interface Data Needed: (mapper)
     *  - Class Name
     *  - Property Types (columnMap)
     *    - getPropertyType()
     *    - getSerialName()
     *    - getSerialType()
     *    - getSerialValue()
     *
     *
     * @param array $row
     * @param Tk_Loader_DataMap $dataMap
     * @return return object
     */
    function doLoad($row, Tk_Loader_DataMap $dataMap)
    {
        $template = $this->getSerialTemplate($dataMap->getPropertyMaps(), $dataMap->getClass());
        $serializedClass = $template[':class:'];
        /* @var $columnMap Tk_Loader_ColumnMap */
        foreach ($dataMap->getPropertyMaps() as $property => $columnMap) {
            $serializedClass .= $template[$property]['start'];
            $type = $template[$property]['type'];
            $end = $template[$property]['end'];
            
            $value = $columnMap->getSerialValue($row);
            $sType = substr($type, -2);
            if ($sType == 'b:') {
                $value = ($value == true) ? 1 : 0;
            } elseif ($sType == 's:') {
                $value = strlen($value) . ':"' . $value . '"';
            } elseif ($value === null) {
                $value = 'N';
                $type = '';
                $end = ';';
            }
            $serializedClass .= $type . $value . $end;
        }
        $serializedClass .= '}';
        $obj = unserialize($serializedClass);
        if ($obj === false) {
            throw new Tk_Exception('Error unserialising object: - Check variable names for correctness. ');
        }
        return $obj;
    }
    
    /**
     * generate the template that will be used to create the serialised object
     *
     * @param array $columnMaps
     * @param string $className
     * @return array
     */
    private function getSerialTemplate($columnMaps, $className)
    {
        static $template = null;
        if (array_key_exists($className, self::$serialTemplates)) {
            return self::$serialTemplates[$className];
        }
        
        $template = array();
        $properties = array();
        $class = new ReflectionClass($className);
        
        do {
            $properties = array_merge($properties, $class->getProperties());
            $class = $class->getParentClass();
        } while ($class != null);
        
        $template[':class:'] = "O:" . strlen($className) . ':"' . $className . '":' . count($columnMaps) . ':{';
        self::$serialTemplatesChk[$className] = count($columnMaps);
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!array_key_exists($name, $columnMaps)) {
                continue;
            }
            if ($property->isPrivate()) {
                $serializedName = "\0" . $property->getDeclaringClass()->getName() . "\0" . $name;
            } elseif ($property->isProtected()) {
                $serializedName = "\0*\0" . $name;
            } else {
                $serializedName = $name;
            }
            
            $start = 's:' . strlen($serializedName) . ':"' . $serializedName . '";';
            $columnMap = $columnMaps[$name];
            $type = $this->getSerialType($columnMap->getPropertyType(), $columnMap);
            
            if ($type{0} == 'O' || $type{0} == 'a') {
                $end = ";}";
            } else {
                $end = ";";
            }
            $template[$name] = array('start' => $start, 'type' => $type, 'end' => $end);
        }
        
        self::$serialTemplates[$className] = $template;
        
        return $template;
    }
    
    /**
     * Get the serial property type string for the column
     *
     * @param string $propertyType
     * @param Tk_Loader_ColumnMap $columnMap
     * @return string
     */
    private function getSerialType($propertyType, $columnMap)
    {
        switch ($propertyType) {
            case Tk_Object::T_BOOLEAN :
                $type = 'b:';
                break;
            case Tk_Object::T_FLOAT :
                $type = 'd:';
                break;
            case Tk_Object::T_INTEGER :
                $type = 'i:';
                break;
            case Tk_Object::T_ENCRYPT_STRING :
            case Tk_Object::T_STRING :
                $type = 's:';
                break;
            case Tk_Object::T_ARRAY :
                $type = 'a:';
                break;
            default :
                $objProperty = $columnMap->getSerialName();
                
                $objPropertyType = $this->getSerialType($columnMap->getSerialType(), $columnMap);
                $typeLen = strlen($propertyType);
                $type = 'O:' . $typeLen . ':"' . $propertyType . '":1:{s:' . (strlen($objProperty) + $typeLen + 2) . ':"' . "\0" . $propertyType . "\0" . $objProperty . '";' . $objPropertyType;
                break;
        }
        
        return $type;
    }
    
    /**
     * Get the property-value array from a serialised object
     *
     * @param string $serializedObj
     * @return array
     */
    private function doGetObjectValues($serializedObj)
    {
        $values = array();
        
        $pos = strpos($serializedObj, ':', 2);
        $len = intval(substr($serializedObj, 2, $pos));
        $class = substr($serializedObj, $pos + 2, $len);
        
        if (substr($serializedObj, 0, 1) == 'a') {
            $classPrefix = array();
        } else {
            $classPrefix = array("\0" . $class . "\0" => $len + 2);
            while ($class = get_parent_class($class)) {
                $classPrefix["\0" . $class . "\0"] = strlen($class) + 2;
            }
        }
        $serializedObj = substr($serializedObj, strpos($serializedObj, '{') + 1, -1);
        
        while ($serializedObj != '') {
            $pad = 0;
            if (substr($serializedObj, 0, 2) == 'i:') { // For array integer keys
                $len = $pos = strpos($serializedObj, ';');
                $name = substr($serializedObj, 2, $pos - 2);
                $pad = 1;
            } else {
                $pos = strpos($serializedObj, ':', 2);
                $len = intval(substr($serializedObj, 2, $pos));
                $name = substr($serializedObj, $pos + 2, $len);
                $pad = strlen($len) + 6;
            }
            
            foreach ($classPrefix as $prefix => $prefixLen) {
                if (substr($name, 0, $prefixLen) == $prefix) { // private vars
                    $name = substr($name, $prefixLen);
                    break;
                }
            }
            if (substr($name, 0, 3) == "\0*\0") { // protected vars
                $name = substr($name, 3);
            }
            
            $serializedObj = substr($serializedObj, $len + $pad);
            $type = $serializedObj{0};
            $value = null;
            
            switch ($type) {
                case 'a' : // array
                case 'O' : // object
                    $pos = $this->getObjectBlockEnd($serializedObj);
                    $value = substr($serializedObj, 0, $pos + 1);
                    $serializedObj = substr($serializedObj, $pos + 1);
                    $value = $this->doGetObjectValues($value);
                    break;
                case 'i' : // integer
                    $pos = strpos($serializedObj, ';', 2);
                    $value = intval(substr($serializedObj, 2, $pos - 2));
                    $serializedObj = substr($serializedObj, $pos + 1);
                    break;
                case 's' : // string
                    $pos = strpos($serializedObj, ':', 2);
                    $len = intval(substr($serializedObj, 2, $pos));
                    $value = substr($serializedObj, $pos + 2, $len);
                    $serializedObj = substr($serializedObj, $pos + $len + 4);
                    break;
                case 'd' : // float
                    $pos = strpos($serializedObj, ';', 2);
                    $value = floatval(substr($serializedObj, 2, $pos - 2));
                    $serializedObj = substr($serializedObj, $pos + 1);
                    break;
                case 'b' : // boolean
                    $pos = strpos($serializedObj, ';', 2);
                    $value = (substr($serializedObj, 2, $pos - 2)) == 1;
                    $serializedObj = substr($serializedObj, $pos + 1);
                    break;
                case 'r' : // This is a recursion indicator, can cause errors use clone on object to fix.
                    break;
                case 'N' : // NULL values
                    $pos = strpos($serializedObj, ';', 1);
                    $value = null;
                    $serializedObj = substr($serializedObj, $pos + 1);
                    break;
                default :
                    break;
            }
            $values[$name] = $value;
        }
        return $values;
    }
    
    /**
     * find the char position of the closing '}' in a serialized string
     *
     * @param string $serializedObj
     */
    function getObjectBlockEnd($serializedObj)
    {
        $pos = strpos($serializedObj, ':', 2);
        $len = intval(substr($serializedObj, 2, $pos));
        $class = substr($serializedObj, $pos + 2, $len);
        
        if (substr($serializedObj, 0, 1) == 'a') {
            $index = $len;
        } else {
            $index = intval(substr($serializedObj, $pos + $len + 4, strpos($serializedObj, '{')));
        }
        
        $totalPos = strpos($serializedObj, '{') + 1;
        $serializedObj = substr($serializedObj, strpos($serializedObj, '{') + 1);
        
        while ($index > 0) {
            if (substr($serializedObj, 0, 2) == 'i:') { // For array integer keys
                $len = $pos = strpos($serializedObj, ';');
                $pad = 1;
            } else {
                $pos = strpos($serializedObj, ':', 2);
                $len = intval(substr($serializedObj, 2, $pos));
                $pad = strlen($len) + 6;
            }
            
            $totalPos += $len + $pad;
            $serializedObj = substr($serializedObj, $len + $pad);
            $type = $serializedObj{0};
            
            switch ($type) {
                case 'a' : // array
                case 'O' : // object
                    $pos = $this->getObjectBlockEnd($serializedObj);
                    $serializedObj = substr($serializedObj, $pos + 1);
                    $totalPos += $pos + 1;
                    break;
                case 'i' : // integer
                    $pos = strpos($serializedObj, ';', 2);
                    $serializedObj = substr($serializedObj, $pos + 1);
                    $totalPos += $pos + 1;
                    break;
                case 's' : // string
                    $pos = strpos($serializedObj, ':', 2);
                    $len = intval(substr($serializedObj, 2, $pos));
                    $serializedObj = substr($serializedObj, $pos + $len + 4);
                    $totalPos += $pos + $len + 4;
                    break;
                case 'd' : // float
                    $pos = strpos($serializedObj, ';', 2);
                    $serializedObj = substr($serializedObj, $pos + 1);
                    $totalPos += $pos + 1;
                    break;
                case 'b' : // boolean
                    $pos = strpos($serializedObj, ';', 2);
                    $serializedObj = substr($serializedObj, $pos + 1);
                    $totalPos += $pos + 1;
                    break;
                case 'N' : // NULL values
                    $pos = strpos($serializedObj, ';', 1);
                    $serializedObj = substr($serializedObj, $pos + 1);
                    $totalPos += $pos + 1;
                    break;
                default :
                    throw new Tk_ExceptionIllegalArgument('Unknown argument, Fix your mapper or object: ' . $class);
            }
            $index--;
        }
        return $totalPos;
    }

}