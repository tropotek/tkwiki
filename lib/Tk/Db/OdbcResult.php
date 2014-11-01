<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  Holds MsSQL Results from a query.
 * 
 * <code>
 * <?php
 *   $result = $db->query($sql);
 *   while($row = $result->getRow()) {
 *       foreach($row as $k => $e) {
 *           echo $e.", ";
 *       }
 *   }
 * ?>
 * </code>
 * 
 * @package Db
 */
class Tk_Db_OdbcResult extends Tk_Object implements Countable, Iterator
{
    
    /**
     * @var Tk_Db_OdbcDao
     */
    private $db = null;
    
    /**
     * @var resource
     */
    private $res = null;
    
    /**
     * @var integer
     */
    private $idx = 0;
    
    
    /**
     * @var boolean
     */
    private $isValid = true;
    
    /**
     * This is the bumbest API I have found so far.
     * I know this is not effecient however I am left with
     * no choice. This will hold all the results data
     *
     * @var array
     */
    private $data = array();
    
    
    
    /**
     * __construct
     * 
     * @param Tk_Db_OdbcDao $da
     * @param resource $query
     */
    function __construct($db, $queryRes)
    {
        $this->db = $db;
        $this->res = $queryRes;
        while (odbc_fetch_row($this->res)) {
            $arr = array();
            for ($y = 1; $y <= odbc_num_fields($this->res); $y++) {
                $name = odbc_field_name($this->res, $y);
                $arr[$name] = odbc_result($this->res, $y);
            }
            $this->data[] = $arr;
        }
    }
    
    function __destruct()
    {
        $this->free();
    }
    
    /**
     * Reset/clear this result esouce fom memory
     *
     */
    function free()
    {
        odbc_free_result($this->res);
        $this->isValid = false;
        $this->res = null;
        $this->idx = 0;
    }
    
    /**
     * Return the current result 
     * 
     * @return array
     */
    function current()
    {
        if ($this->idx >= $this->count()) {
            $this->isValid = false;
            return false;
        }
        return $this->data[$this->idx];
    }
    
    /**
     * Get the key value for the current result
     *
     * @return integer
     */
    function key()
    {
        return $this->idx;
    }
    
    /**
     * return the next result, if first call, returns the first result.
     *
     * @return array
     */
    function next()
    {
        $this->idx++;
    }
    
    /**
     * Rewind the internal pointer
     * 
     * @todo Use the offset value...
     */
    function rewind()
    {
        $this->isValid = ($this->count() > 0);
        $this->idx = 0;
    }
    
    /**
     * Returns false if no errors or returns a MySQL error message.
     * 
     * @return boolean Returns false if there are no error messages.
     */
    function valid()
    {
        return ($this->idx < $this->count());
    }
    
    /**
     * Returns the number of rows in result set.
     * 
     * @return integer
     */
    function count()
    {
        return count($this->data);
    }

}