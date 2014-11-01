<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This is a base DB object usfull for mapper functionality
 * to DB classes.
 *
 * NOTICE: All model objects should not use `private` for db field properties. 
 * This will cause fields not to load from the DB, only public and protected
 * properties are supported with this data mapper.
 * 
 * 
 * @package Tk
 */
abstract class Tk_Db_Object extends Tk_Object
{

    /**
     * @var array
     */
    private $_data = array();

    /**
     * @var array
     */
    private $_getterIgnore = array();

    /**
     * @var array
     */
    private $_setterIgnore = array();


    /**
     * Getter
     * This is used for anon objects and to facilitate data type loading
     * 
     * @param string $var
     * @return mixed
     */
    function __get($var)
    {
        // Check for custom getter method
        $getMethod = 'get' . ucfirst($var);
        if (method_exists($this, $getMethod) && !array_key_exists($var, $this->_getterIgnore)) {
            $this->_getterIgnore[$var] = 1;     // Tell this function to ignore the overload on further calls for this variable
            $result = $this->$getMethod();      // Call custom getter
            unset($this->_getterIgnore[$var]);  // Remove ignore rule
            return $result;
        } else {    // Handle default way
            if (property_exists($this, $var)) {
                return $this->$var;
            }
            if (isset($this->_data[$var])) {
                return $this->_data[$var];
            }
        }
    }

    /**
     * Setter
     * This is used for anon objects and to facilitate data type loading
     * 
     * @param string $var
     * @param mixed $value
     * @return mixed
     */
    public function __set($var, $value)
    {
        // Check for custom setter method (override)
        $setMethod = 'set' . ucfirst($var);
        if (method_exists($this, $setMethod) && !array_key_exists($var, $this->_setterIgnore)) {
            $this->_setterIgnore[$var] = 1; // Tell this function to ignore the overload on further calls for this variable
            $result = $this->$setMethod($value); // Call custom setter
            unset($this->_setterIgnore[$var]); // Remove ignore rule
            return $result;
        } else {    // Handle default way
            if ($var == 'id') {
                $this->id = $value;
            } else if (property_exists($this, $var)) {
                // Model DB properties should not be private.
                // Could throw an exception here
                $this->$var = $value;  //TODO: use reflect for private
            } else {
                $this->_data[$var] = $value;
            }
        }
    }
    
    
    
    /**
	 * Enable isset() for object properties
	 */
	public function __isset($key)
	{
		return ($this->$key !== null) ? true : false;
	}
    
    
    
    
    /**
     * Return this object's unique ID
     * If the id value has not been set, then one will be assigned by its internal counter.
     *
     * @return string
     */
    function getId()
    {
        return $this->id;
    }
    
    
    /**
     * Insert the object into storage.
     * By default this is a database
     *
     * @return integer The object insert ID
     */
    function insert()
    {
        $this->getDbMapper()->insert($this);
        if (property_exists($this, 'orderBy')) {
            $this->orderBy = $this->getId();
            $this->save();
        }
        return $this->getId();
    }
    
    /**
     * Update the object in storage
     * By default this is a database
     *
     * @return integer
     */
    function update()
    {
        return $this->getDbMapper()->update($this);
    }
    
    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     *
     * @return integer
     */
    function save()
    {
        if ($this->getId() > 0) {
            return $this->update();
        }
        return $this->insert();
    }
    
    /**
     * Update the object in storage
     * By default this is a database
     *
     * @return integer
     */
    function delete()
    {
        return $this->getDbMapper()->delete($this);
    }
    
    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if is 0
     *
     * @return integer
     */
    function getVolitileId()
    {
        return $this->getDbMapper()->getVolitileId($this);
    }
    
    /**
     * Try to validate an object if as validator object available
     *
     * @return Tk_Util_Validator
     */
    function getValidator()
    {
        $class = get_class($this) . 'Validator';
        if (class_exists($class)) {
            return new $class($this);
        }
    }
    
    /**
     * Try to validate an object if as validator object available
     *
     * @return boolean
     */
    function isValid()
    {
        $class = get_class($this) . 'Validator';
        if (class_exists($class)) {
            $valid = new $class($this);
            return $valid->isValid();
        }
        return true;
    }
    
    /**
     * Get this object's Data Loader Object if it exists.
     *
     * @return Tk_Loader_Interface
     */
    function getObjectLoader()
    {
        return Tk_Loader_Factory::getLoader(get_class($this));
    }
    
    /**
     * Get the object's DB mapper
     *
     * @return Tk_Db_Mapper
     */
    function getDbMapper()
    {
        return Tk_Db_Factory::getDbMapper($this->getObjectLoader()->getDataMap());
    }
}