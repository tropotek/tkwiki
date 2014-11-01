<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
 
/**
 * The dynamic table Controller
 *
 *
 * @requires Dom
 * @requires Tk
 * @requires Ui
 * @requires Form
 *
 * @package Table
 */
class Table extends Tk_Object
{
    const SID = 'Tbl-';
    
    /**
     * @var Table_Cell[]
     */
    protected $cellList = array();
    
    /**
     * @var Table_Action[]
     */
    protected $actionList = array();
    
    /**
     * @var Form_Field[]
     */
    protected $filterList = array();
    
    /**
     * @var Form
     */
    protected $form = null;
    
    /**
     * @var Tk_Db_Tool
     */
    protected $dbTool = null;
    
    /**
     * @var Table_Renderer
     */
    protected $renderer = null;
    
    
    
    /**
     * __construct
     *
     */
    function __construct()
    {
        static $i = 1;
        $this->id = $i++;
        
        $this->form = Form::create($this->getFormId());
        $this->form->setAction(Tk_Request::requestUri());
        $this->form->setContainer($this);
    }
    
    /**
     * Create a new form with a new form renderer
     *
     * @return Table
     */
    static function create()
    {
        $obj = new self();
        return $obj;
    }
    
    /**
     * Create a DbTool from the request using the table ID and
     * default parameters...
     *
     * @param string $orderBy
     * @param integer $limit
     * @return Tk_Db_Tool
     * @todo We need to look for an OrderBy Cell and prepend that to the $orderBy param or disable header sorting...?
     */
    function getDbTool($orderBy = '', $limit = 50)
    {
        if (!$this->dbTool) {
            $this->dbTool = Tk_Db_Tool::createFromRequest($this->getId(), $orderBy, $limit, true);
        }
        return $this->dbTool;
    }
    
    /**
     * Delete the Db Tool so it can be re-created to avoid caching issue
     * 
     * @return Table
     */
    function deleteDbTool() 
    {
        $this->dbTool == null;
        return $this;
    }
    
    
    /**
     * Get the table Renderer
     *
     * @param array $list
     * @return Table_Renderer
     */
    function getRenderer($list)
    {
    	if (!$this->renderer) {
            $this->execute($list);
            $this->renderer = Table_Renderer::create($this, $list);
    	}
        return $this->renderer;
    }
    
    /**
     * This method realy only needs to be called if you have any
     * filters in the tables form.
     *
     */
    function init()
    {
        if (count($this->getFilterList())) {
            $this->getForm()->addEvent(Table_Event_Search::create('search'));
            $this->getForm()->addEvent(Table_Event_Clear::create());
            /* @var $filter Table_Filter */
            foreach ($this->getFilterList() as $filter) {
                $this->getForm()->addField($filter);
            }
        }
        // Load filter field values from the session if exists
        if (Tk_Session::exists($this->getSessionHash())) {
            $arr = Tk_Session::get($this->getSessionHash());
            /* @var $filter Form_Field */
            foreach ($this->filterList as $filter) {
                $filter->getType()->loadFromArray($arr[$filter->getName()]);
            }
        }
        $this->getForm()->execute();
        $this->getForm()->loadFromArray(Tk_Request::getInstance()->getAllParameters());
        
    }
    
    /**
     * Get a unique session name for this table
     *
     * @return string
     */
    function getSessionHash()
    {
        return self::SID . md5($this->getEventKey(self::SID) . Tk_Request::requestUri()->getPath());
    }
    
    /**
     * Execute the command
     *
     * @param array $list
     * @return Table
     */
    function execute($list)
    {
        /* @var $action Table_Action */
        foreach ($this->getActionList() as $action) {
            if (Tk_Request::exists($action->getEventKey($action->getEvent()))) {
                $action->execute($list);
            }
        }
        
        // Execute Cell objects
        /* @var $cell Table_Cell */
        foreach ($this->getCellList() as $cell) {
            $cell->execute($list);
        }
        
        return $this;
    }
    
    /**
     * Get all the request key/value pairs for the filters
     *
     * @return array
     */
    function getFilterValues()
    {
        $array = array();
        /* @var $filter Form_Field */
        foreach ($this->filterList as $filter) {
            $array[$filter->getName()] = $filter->getValue();
        }
        return $array;
    }
    
    
    
    /**
     * Get this table's form object
     *
     * @return Form
     */
    function getForm()
    {
        return $this->form;
    }

    /**
     * Add an action to this table
     *
     * @param Table_Action $action
     * @return Table_Action
     */
    function addAction($action)
    {
        $action->setTable($this);
        $this->actionList[] = $action;
        return $action;
    }

    /**
     * Get the action list array
     *
     * @return array
     */
    function getActionList()
    {
        return $this->actionList;
    }
    
    /**
     * Add a filter to this table
     *
     * @param Form_Field $filter
     * @return Form_Field
     */
    function addFilter($filter)
    {
        $this->filterList[] = $filter;
        return $filter;
    }
    
    /**
     * Get the filter list array
     * Contains Form_Field objects
     *
     * @return array
     */
    function getFilterList()
    {
        return $this->filterList;
    }
    
    /**
     * Add a cell to this table
     *
     * @param Table_Cell $cell
     * @return Table_Cell
     */
    function addCell($cell)
    {
        $cell->setTable($this);
        $this->cellList[] = $cell;
        return $cell;
    }
    
    /**
     * Set the cells, init with the table
     *
     * @param Table_Cell[] $array
     * @return Table
     */
    function setCells($array)
    {
        foreach ($array as $cell) {
            $cell->setTable($this);
        }
        $this->cellList = $array;
        return $this;
    }
    
    /**
     * Get a cell from the array by its property name
     *
     * @param string $property
     */
    function getCell($property)
    {
        if (array_key_exists($property, $this->cellList)) {
            return $this->cellList[$property];
        }
    }
    
    /**
     * Get the cell list array
     *
     * @return array
     */
    function getCellList()
    {
        return $this->cellList;
    }
    
    /**
     * Get the HTML form ID string
     *
     * @return string
     */
    function getFormId()
    {
        return 'TableForm_' . $this->getId();
    }
    
    /**
     * Get the HTML table ID string
     *
     * @return string
     */
    function getTableId()
    {
        return 'Table_' . $this->getId();
    }
    
}
