<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Dk objects to a table
 *
 *
 * @package Com
 */
class Com_Ui_Table_Cell extends Com_Ui_Table_ExtraInterface
{
    const ORDER_NONE = '';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    
    const ALIGN_LEFT = 'left';
    const ALIGN_RIGHT = 'right';
    const ALIGN_CENTER = 'center';
    
    /**
     * @var string
     */
    protected $name = '';
    
    /**
     * @var string
     */
    protected $property = '';
    
    /**
     * @var boolean
     */
    protected $key = false;
    
    /**
     * @var string
     */
    protected $align = self::ALIGN_LEFT;
    
    /**
     * @var Tk_Type_Url
     */
    protected $actionUrl = null;
    
    /**
     * @var string
     */
    protected $actionUrlParam = '';
    
    /**
     * @var array
     */
    protected $rowClass = array();
    
    /**
     * Create the object instance
     *
     * @param string $name
     * @param string $property
     * @param Tk_Type_Url $actionUrl
     * @param string $actionUrlParam The param that holds the object id
     */
    function __construct($name, $property = '', $actionUrl = null, $actionUrlParam = '')
    {
        $this->name = $name;
        $this->property = $property;
        $this->actionUrl = $actionUrl;
        $this->actionUrlParam = $actionUrlParam;
    }
    
    /**
     * Process any events or actions on execution
     *
     */
    function doProcess()
    {
    }
    
    /**
     * get the column name
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * get the object property
     *
     * @return string
     */
    function getProperty()
    {
        return $this->property;
    }
    
    /**
     * Get the access method if it exists.
     *
     * @param Tk_Object $obj
     * @return string Returns an empty string on fail.
     */
    function getMethod($obj)
    {
        if (!$this->property) {
            return '';
        }
        $method = 'get' . ucfirst($this->property);
        if (!method_exists($obj, $method)) {
            $method = 'is' . ucfirst($this->property);
        }
        if (!method_exists($obj, $method)) {
            $method = 'has' . ucfirst($this->property);
        }
        if (!method_exists($obj, $method)) {
            return '';
        }
        return $method;
    }
    
    /**
     * get the parameter data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $str = '';
        $method = $this->getMethod($obj);
        if ($method) {
            $str = $obj->$method();
        }
        return htmlentities($str);
    }
    
    /**
     * get the table data from an object if available
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getTableData($obj)
    {
        $this->rowClass = array(); // reset row class list
        $str = '';
        if ($this->getActionUrl($obj)) {
            if ($this->actionUrlParam === '') {
                $pos = strrpos(get_class($obj), '_');
                $name = substr(get_class($obj), $pos + 1);
                $this->actionUrlParam = strtolower($name[0]) . substr($name, 1) . 'Id';
            }
            $url = $this->getActionUrl($obj);
            $url->set($this->actionUrlParam, $obj->getId());
            $str = '<a href="' . htmlentities($url->toString()) . '">' . $this->getPropertyData($obj) . '</a>';
        } else {
            $str = $this->getPropertyData($obj);
        }
        return $str . '';
    }
    
    /**
     * Get the table data from an object if available
     *  * Overide getName() to add new text to the header.
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getTableHeader()
    {
        $url = $this->getOrderByUrl();
        if ($url) {
            $str = '<a href="' . htmlentities($url->toString()) . '">' . $this->getName() . '</a>';
        } else {
            $str = '<a href="javascript:;">' . $this->getName() . '</a>';
        }
        return $str;
    }
    
    /**
     * Get the action url object.
     *
     * @param Tk_Object $obj
     * @return Tk_Type_Url
     */
    function getActionUrl($obj)
    {
        if ($this->actionUrl instanceof Tk_Type_Url) {
            return clone $this->actionUrl;
        }
    }
    
    /**
     * Set cell key status.
     * If true then this cell's width should be at maximum width
     *
     * @param boolean $b
     */
    function setKey($b)
    {
        $this->key = $b;
    }
    
    /**
     * Is this cell a key cell
     *
     * @return boolean
     */
    function isKey()
    {
        return $this->key;
    }
    
    /**
     * Set the cell alignment
     *
     * Valid parameters are:
     * o Com_Ui_Table_Cell::ALIGN_CENTER
     * o Com_Ui_Table_Cell::ALIGN_LEFT
     * o Com_Ui_Table_Cell::ALIGN_RIGHT
     *
     * @param string $str
     */
    function setAlign($str)
    {
        if ($str != self::ALIGN_CENTER && $str != self::ALIGN_LEFT && $str != self::ALIGN_RIGHT) {
            throw new Tk_ExceptionIllegalArgument('Invalid alignment value');
        }
        $this->align = $str;
    }
    
    /**
     * get the alignment value of this cell
     *
     * Valid return values are:
     * o Com_Ui_Table_Cell::ALIGN_CENTER
     * o Com_Ui_Table_Cell::ALIGN_LEFT
     * o Com_Ui_Table_Cell::ALIGN_RIGHT
     *
     * @return string
     */
    function getAlign()
    {
        return $this->align;
    }
    
    /**
     * Add a row class for rendering
     *
     * @param string $class
     */
    function addRowClass($class)
    {
        $this->rowClass[$class] = $class;
    }
    
    /**
     * Get all the additional classes to attach to a row
     *
     * @return array
     */
    function getRowClassList()
    {
        return $this->rowClass;
    }
    
    /**
     * get the orderBy property/row
     *
     * @return string
     * @todo Keep an eye on this as I changed it to avoid SQL errors when fields do not exist
     */
    function getOrderByProperty()
    {
    	if ($this->getTable()->getList()) {
	    	$obj = current($this->getTable()->getList());
	    	if ($obj) {
	    		$arr = get_object_vars($obj);
	    		$method = 'set' . ucfirst($this->property);
	    		if (!method_exists($obj, $method)) {
	    			return '';
	    		}
	            return $this->property;
	    	}
    	}
        return '';
    }
    
    /**
     * Get the orderBy status of this cell
     *
     * @return string
     */
    function getOrderBy()
    {
        $pre = '`' . $this->getOrderByProperty() . '` ';
        
        $orderByStr = self::ORDER_NONE;
        if ($this->getTable()->getList() instanceof Tk_Loader_Collection || $this->getTable()->getList() instanceof Tk_Db_Array) {
            $orderByStr = $this->getTable()->getList()->getDbTool()->getOrderBy();
        }
        if ($orderByStr == $pre . self::ORDER_ASC) {
            return self::ORDER_ASC;
        } else if ($orderByStr == $pre . self::ORDER_DESC) {
            return self::ORDER_DESC;
        } else {
            return self::ORDER_NONE;
        }
    }
    
    /**
     * getOrderByUrl
     *
     * @param string $eventKey
     * @return Tk_Type_Url
     */
    function getOrderByUrl()
    {
        $pre = '`' . $this->getOrderByProperty() . '` ';
        
        $eventKey = $this->getEventKey('orderBy');
        $url = Tk_Request::getInstance()->getRequestUri();
        if ($this->getOrderByProperty() == '') {
            return null;
        }
        $url->delete($eventKey);
        
        $orderByStr = self::ORDER_NONE;
        if ($this->getTable()->getList() instanceof Tk_Loader_Collection || $this->getTable()->getList() instanceof Tk_Db_Array) {
            $orderByStr = $this->getTable()->getList()->getDbTool()->getOrderBy();
        }
        
        if ($orderByStr == $pre . self::ORDER_ASC) {
            $url->set($eventKey, $pre . self::ORDER_DESC);
        } else if ($orderByStr == $pre . self::ORDER_DESC) {
            $url->set($eventKey, '');
        } else {
            $url->set($eventKey, $pre . self::ORDER_ASC);
        }
        return $url;
    }

}