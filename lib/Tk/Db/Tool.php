<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The TOOL object is named from the params (Total, Offset, OrderBy, Limit)
 *
 * This object manages a query's params $orderBy, $limit, $offset and $total
 * where total is the total number of records available without a limit.
 *
 * Useful for persistant storage of table data and record positions
 *
 * @package Tk
 */
class Tk_Db_Tool extends Tk_Object
{
    /**
     * This mode will retreive the total record count only when limit > 0
     */
    const MODE_TOTAL = 1;
    /**
     * This mode will force retrevial of the total ignoring limit
     */
    const MODE_FORCE_TOTAL = 1;
    
    /**
     * This mode will not get the total ignoring the limit value
     */
    const MODE_SUPRESS_TOTAL = 2;
    
    /**
     * The request parameter keys
     */
    const REQ_LIMIT = 'limit';
    const REQ_OFFSET = 'offset';
    const REQ_ORDER_BY = 'orderBy';
    
    /**
     * Limit the nuber of records retreived.
     * If > 0 then mapper should query for the total number of records
     *
     * @var integer
     */
    private $limit = 0;
    
    /**
     * The record to start retreval from
     *
     * @var integer
     */
    private $offset = 0;
    
    /**
     * @var string
     */
    private $orderBy = '`id` DESC';
    
    /**
     * The total number of available records
     * @var integer
     */
    private $totalRows = 0;
    
    /**
     * @var integer
     */
    private $mode = self::MODE_TOTAL;
    
    
    
    
    /**
     * __construct
     *
     * @param integer $limit
     * @param integer $offset
     * @param string $orderBy
     * @param integer $totalRows
     */
    function __construct($limit = 0, $offset = 0, $orderBy = '', $totalRows = 0)
    {
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setOrderBy($orderBy);
        if ($totalRows > 0) {
            $this->setTotalRows($totalRows);
        }
    }
    
    /**
     * Create a listParams object from a request object
     *
     * @param string $orderBy
     * @param integer $limit
     * @param integer $offset
     * @param integer $totalRows
     * @return Tk_Db_Tool
     */
    static function create($orderBy = '', $limit = 0, $offset = 0 , $totalRows = 0)
    {
        return new self($limit, $offset, $orderBy, $totalRows);
    }
    
    /**
     * Create a listParams object from a request object
     *
     * @param integer $eventKey This is used to create the unique request key
     * @param string $orderBy The default orderby to use
     * @return Tk_Db_Tool
     */
    static function createFromRequest($eventKey = null, $orderBy = '`id` DESC', $limit = 50, $ignoreSession = false)
    {
        $tool = new self($limit, 0, $orderBy);
        $tool->id = $eventKey;
        
        $sid = 'dbt-' . md5($tool->getEventKey(Tk_Request::requestUri()->getBasename()));
        if (!$ignoreSession && Tk_Session::exists($sid)) {
            $tool = Tk_Session::get($sid);
        }
        if (Tk_Request::exists($tool->getEventKey(self::REQ_OFFSET))) {
            $tool->setOffset(Tk_Request::get($tool->getEventKey(self::REQ_OFFSET)));
        }
        if (Tk_Request::exists($tool->getEventKey(self::REQ_LIMIT))) {
            $tool->setLimit(Tk_Request::get($tool->getEventKey(self::REQ_LIMIT)));
        }
        if (Tk_Request::exists($tool->getEventKey(self::REQ_ORDER_BY))) {
            $tool->setOrderBy(Tk_Request::get($tool->getEventKey(self::REQ_ORDER_BY)));
        }
        Tk_Session::set($sid, $tool);
        return $tool;
    }
    
    /**
     * Delete the Db tool from the session if it exists.
     *
     * @return boolean Returns true if the object was deleted from the session
     */
    static function clearSession()
    {
        $sid = $this->getEventKey(md5(Tk_Request::requestUri()->getBasename()) . '-dbt');
        if (Tk_Session::exists($sid)) {
            Tk_Session::delete($sid);
            return true;
        }
        return false;
    }
    
    /**
     * Reset the offest to 0
     *
     * @return Tk_Db_Tool
     */
    function reset()
    {
        $this->offset = 0;
        return $this;
    }
    
    /**
     * Set the order By value
     *
     * @param string $str
     * @return Tk_Db_Tool
     */
    function setOrderBy($str)
    {
        if (strstr(strtolower($str), 'field') === false) {
            $str = str_replace("'", "''", $str);
        }
        $this->orderBy = $str;
        return $this;
    }
    
    /**
     * Get the order by string for the DB queries
     *
     * @return string
     */
    function getOrderBy()
    {
        return $this->orderBy;
    }
    
    /**
     * Set the limit value
     *
     * @param integer $i
     * @return Tk_Db_Tool
     */
    function setLimit($i)
    {
        $this->limit = intval($i);
        if ($this->limit < 0) {
            $this->limit = 0;
        }
        return $this;
    }
    
    /**
     * Get the page limit for pagenators and queries
     *
     * @return integer
     */
    function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Set the offset value
     *
     * @param integer $i
     * @return Tk_Db_Tool
     */
    function setOffset($i)
    {
        $this->offset = intval($i);
        if ($this->offset < 0) {
            $this->offset = 0;
        }
        return $this;
    }
    
    /**
     * Get the record offset for pagenators and queries
     *
     * @return integer
     */
    function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Set the total value
     *
     * @param integer $i
     * @return Tk_Db_Tool
     */
    function setTotalRows($i)
    {
        $this->totalRows = intval($i);
        return $this;
    }
    
    /**
     * Get the total record count.
     * This value will be the avalible count without a limit.
     * If hasTotal() is false however this value will be the total number of
     * records retreived.
     *
     * Change the setMode() to change the behaviour of total value.
     *
     * @return integer
     */
    function getTotalRows()
    {
        return $this->totalRows;
    }
    
    /**
     * Set the mode value.
     * Use the constants:
     *   - self::MODE_TOTAL
     *   - self::MODE_FORCE_TOTAL
     *   - self::MODE_SUPRESS_TOTAL
     *
     * @param integer $i
     * @return Tk_Db_Tool
     */
    function setMode($i)
    {
        $i = intval($i);
        if ($i < 0) {
            $i = 0;
        }
        if ($i > 2) {
            $i = 2;
        }
        $this->mode = $i;
        return $this;
    }
    
    /**
     * Use this to test if the tool object contains the total value
     *
     * @return boolean
     */
    function hasTotal()
    {
        switch ($this->mode) {
            case self::MODE_TOTAL :
                if ($this->limit > 0) {
                    return true;
                }
            case self::MODE_FORCE_TOTAL :
                return true;
            case self::MODE_SUPRESS_TOTAL :
        }
        return false;
    }
    
    /**
     * Get the current page number based on the limit and offset
     *
     * @return integer
     */
    function getPageNo()
    {
        return ceil($this->offset / $this->limit) + 1;
    }

}