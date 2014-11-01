<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This is the loader interface and should be used on the object or its loader
 * to generate a datamap, other functions can be created to construct different maps
 * however there must at least be one data map function available
 *
 * getDataMap() can and will be called by default for all objects using the data loader,
 * if no dataMap is supplied.
 *
 * @package Tk
 */
class Tk_Loader_Collection extends Tk_Object implements Iterator, ArrayAccess, Countable
{
    
    /**
     * @var Tk_Loader_DataMap
     */
    protected $dataMap = null;
    /**
     * The raw dataSrc row data
     * @var array
     */
    protected $raw = array();
    /**
     * The created objects
     * @var array
     */
    protected $objects = array();
    
    /**
     * The collection size
     * @var integer
     */
    protected $total = 0;
    
    /**
     * The location pointer
     * @var integer
     */
    protected $idx = 0;
    
    /**
     * @var Tk_Db_Tool
     */
    private $dbTool = null;
    
    /**
     * Create a collection object
     *
     * @param Tk_Loader_DataMap $dataMap
     * @param array $raw
     */
    function __construct($dataMap = null, $raw = null)
    {
        if (!is_null($raw)) {
            $this->raw = $raw;
            $this->total = count($raw);
        }
        $this->dataMap = $dataMap;
    }
    
    /**
     * Return a standard PHP array of the objects
     *
     * @return array
     */
    function getArray()
    {
        $array = array();
        foreach ($this as $obj) {
            $array[] = $obj;
        }
        return $array;
    }
    
    
    function getRawArray()
    {
        return $this->raw;
    }
    
    
    /**
     * Create a collection object from an array of objects
     *
     * @param array $array
     * @return Tk_Loader_Collection
     * @todo Test this functionality
     */
    static function createFromObjectArray($array, $tool = null)
    {
        $c = new self();
        $c->objects = $array;
        $c->total = count($array);
        $c->idx = 0;
        $c->setDbTool($tool);
        return $c;
    }
    
    /**
     * Add an object to the collection.
     * Objects must be of the same type to be added to the collection
     *
     * @param Tk_Object $obj
     */
    function add(Tk_Object $obj)
    {
        if (get_class($obj) != $this->dataMap->getClass()) {
            throw new Tk_Exception('Invalid object type.');
        }
        $this->objects[$this->total] = $obj;
        $this->total++;
        return $this;
    }
    
    /**
     * Get an object from the collection.
     * Using lazy loading the object is not created until it is needed.
     *
     * @param integer $i
     * @return mixed
     */
    function get($i)
    {
        if ($i >= $this->total || $i < 0) {
            return null;
        }
        if (isset($this->objects[$i])) {
            return $this->objects[$i];
        }
        if (isset($this->raw[$i])) {
            $this->objects[$i] = Tk_Loader_Factory::loadObject($this->raw[$i], $this->dataMap);
            return $this->objects[$i];
        }
    }
    
    /**
     * Set the loo (limit, offset, orderBy, total) object
     *
     * @param Tk_Db_Tool $loot
     */
    function setDbTool($dbTool)
    {
        $this->dbTool = $dbTool;
        return $this;
    }
    
    /**
     * Get the loo (limit, offset, orderBy, total) object
     *
     * @return Tk_Db_Tool
     */
    function getDbTool()
    {
        return $this->dbTool;
    }
    
    /*   Iterator Interface   */
    function rewind()
    {
        $this->idx = 0;
        return $this;
    }
    
    /**
     * Return the element at the current index
     *
     * @return Tk_Object
     */
    function current()
    {
        return $this->get($this->idx);
    }
    
    function key()
    {
        return $this->idx;
    }
    
    /**
     * Return the next element and increment the counter
     *
     * @return Tk_Object
     */
    function next()
    {
        $obj = $this->get($this->idx);
        if ($obj) {
            $this->idx++;
        }
        return $obj;
    }
    
    function valid()
    {
        return (!is_null($this->current()));
    }
    
    /*   ArrayAccess Interface   */
    function offsetExists($i)
    {
        return (!is_null($this->get($i)));
    }
    
    function offsetSet($i, $obj)
    {
        $this->add($obj);
    }
    
    public function offsetGet($i)
    {
        return $this->get($i);
    }
    
    public function offsetUnset($i)
    {
        //throw new Tk_ExceptionLogic('Canot remove an object from a collection.');
        if (isset($this->raw[$i])) {
            unset($this->raw[$i]);
        }
        if (isset($this->objects[$i])) {
            unset($this->objects[$i]);
        }
        $this->total--;
        $this->idx = 0;
        $this->objects = array_merge($this->objects, array());
        $this->raw = array_merge($this->raw, array());
        return $this;
    }
    
    /*   Countable Interface   */
    function count()
    {
        return $this->total;
    }
}