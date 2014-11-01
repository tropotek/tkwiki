<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This object is the base for all Tk objects
 *
 * Note: This object cannot have a constructor as it locks in the
 * permission of public,private,protected.
 *
 * @package Tk
 */
class Tk_Object
{
    /* Common Data Types For The TkLib */
    const T_BOOLEAN = 'boolean';
    const T_INTEGER = 'integer';
    const T_FLOAT = 'float';
    const T_STRING = 'string';
    const T_ENCRYPT_STRING = 'encryptString';
    const T_ARRAY = 'array';
    
    /**
     * @var mixed
     * @todo this needs to be made private in V2 of the TkLib
     * We will need a new DB object loader first
     */
    public $id = null;
    
    
    
    
    
    
    /**
     * 
     * Return this object's unique ID
     * If the id value has not been set, then one will be assigned by its internal counter.
     *
     * @return string
     */
    function getId()
    {
        static $idx = 0;
        if ($this->id === null) {
            $this->id = $idx++;
        }
        return $this->id;
    }
    
    
    /**
     * Get this objects default event key
     *
     * @param string $eventName
     * @return string
     */
    function getEventKey($eventName)
    {
        return Tk::createEventKey($eventName, $this->getId());
    }
    
    /**
     * Return a string representation of this object
     *
     * @return string
     */
    function __toString()
    {
        if (method_exists($this, 'toString')) {
            return $this->toString();
        }
        $str = print_r($this, true);
        return $str;
    }
    
	/**
	 * Return JSON-encoded string rep of the object (convenience function)
	 * 
     * @return string
	 */
	public function toJson()
	{
		return json_encode($this);
	}
    
    /**
     * Get a list of constant name value pairs for this class
     *
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @return array
     */
    function getConstants($prefix = '')
    {
        return self::getClassConstants(get_class($this), $prefix);
    }
    
    /**
     * Get a list of constant name value pairs for a passed class name
     *
     * @param string $class A
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @return array
     */
    static function getClassConstants($prefix = '')
    {
        $oReflect = new ReflectionClass(get_called_class());
        $constList = $oReflect->getConstants();
        if (!$prefix) {
            return $constList;
        }
        $retList = array();
        foreach ($constList as $k => $v) {
            if (substr($k, 0, strlen($prefix)) == $prefix) {
                $retList[$v] = $k;
            }
        }
        return $retList;
    }

}