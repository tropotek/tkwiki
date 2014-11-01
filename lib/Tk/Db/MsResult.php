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
class Tk_Db_MsResult extends Tk_Object implements Countable, Iterator
{
    
    /**
     * @var Tk_Db_MsDao
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
     * __construct
     * 
     * @param Tk_Db_MsDao $db
     * @param resource $query 
     */
    function __construct($db, $res)
    {
        $this->db = $db;
        $this->res = $res;
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
        //sqlsrv_free_stmt($this->res);
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
        $row = null;
        // SQLSRV_SCROLL_ABSOLUTE
        sqlsrv_fetch($this->res, SQLSRV_SCROLL_PRIOR, $this->idx);
        $row = sqlsrv_fetch_array($this->res, SQLSRV_FETCH_ASSOC);
        
        if (!$row) {
            if ($this->db->getError()) {
                throw new Tk_ExceptionSql($this->db->getError());
            }
            $this->isValid = false;
            return false;
        }
        return $row;
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
        //sqlsrv_fetch($this->res,SQLSRV_SCROLL_ABSOLUTE, $this->idx);
    }
    
    /**
     * Returns false if no errors or returns a MsSQL error message.
     * 
     * @return boolean Returns false if there are no error messages.
     */
    function valid()
    {
        $b = sqlsrv_fetch($this->res,SQLSRV_SCROLL_ABSOLUTE, $this->idx);
        return ($this->idx < $this->count()) && ($b != null);
    }
    
    /**
     * Returns the number of rows in result set.
     * 
     * @return integer
     */
    function count()
    {
        $rows = sqlsrv_num_rows($this->res);
        if ($rows === false) {
            throw new Tk_ExceptionSql($this->db->getError());
        }
        return $rows;
    }

}