<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic form renderer
 *
 *
 *
 *
 * @package Table
 */
class Table_Renderer extends Dom_Renderer
{
    
    /**
     * This is the main data records, must be an array of objects....
     *
     * @var ArrayIterator
     */
    protected $list = null;
    
    
    /**
     * @var Table
     */
    protected $table = null;
    
    
    /**
     * @var Form_StaticRenderer
     */
    protected $formRenderer = null;
    
    
    
    /**
     * Create the object instance
     *
     * @param Table $table
     * @param ArrayIterator
     */
    function __construct($table, $list)
    {
        $this->setTable($table);
        $this->list = $list;
    }
    
    /**
     * Create a new form with a new form renderer
     *
     * @param Table $table
     * @param ArrayIterator
     * @return Table_Renderer
     */
    static function create($table, $list)
    {
        $obj = new self($table, $list);
        return $obj;
    }

    /**
     * Get the item object list array
     *
     * @return ArrayIterator
     */
    function getList()
    {
        return $this->list;
    }
    
    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     * @return Table_Renderer
     */
    function setTable(Table $table)
    {
        $this->id = $table->getId();
        $this->table = $table;
        return $this;
    }
    
    /**
     * Get the table object
     *
     * @return Table
     */
    function getTable()
    {
    	return $this->table;
    }
    
    /**
     * Show
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        
        // Table Events
        if (count($this->table->getActionList()) > 0 || count($this->table->getFilterList()) > 0) {
            $template->setChoice('TableEvents');
        }
        
        $this->showActions($this->table->getActionList());
        $this->showFilters($this->table->getFilterList());
        
        // Table Header
        $this->showTh($this->table->getCellList());
        
        if ($this->list != null && count($this->list) ) {
            // Table Controls
            $results = Table_Ui_Results::createFromList($this->list);
            $results->show();
            $template->insertTemplate('Table_Ui_Results', $results->getTemplate());
            
            $pager = Table_Ui_Pager::createFromList($this->list);
            $pager->show();
            $template->insertTemplate('Table_Ui_Pager', $pager->getTemplate());
            
            $limit = Table_Ui_Limit::createFromList($this->list);
            $limit->show();
            $template->insertTemplate('Table_Ui_Limit', $limit->getTemplate());
            
            // Table Data
            $this->showTd($this->list);
            
            // Re-create template to allow form object to render elements
            $headerList = $template->getHeaderList();
            $tmp = Dom_Template::load($template->toString('xml', true));
            $tmp->setHeaderList($headerList);
            $this->setTemplate($tmp);
            $template = $this->getTemplate();
        } else {
            // Show no results message??
        }
        
        // create form renderer
        $this->formRenderer = new Form_StaticRenderer($this->table->getForm(), $template);
        $this->formRenderer->show($template);
        
    }
    
    /**
     * Render the action icons
     *
     * @param array $actionList
     */
    function showActions($actionList)
    {
        if (!count($actionList)) {
            return;
        }
        $template = $this->getTemplate();
        /* @var $action Table_Action */
        foreach ($actionList as $action) {
            $repeat = $template->getRepeat('action');
            $data = $action->getHtml($this->list);
            if ($data instanceof Dom_Template) {
                $repeat->insertTemplate('action', $data);
            } else {
                $repeat->insertHTML('action', $data);
            }
            $repeat->appendRepeat();
        }
        $template->setChoice('actions');
    }
    
    
    /**
     * Render the filter fields
     *
     * @param array $filterList
     */
    function showFilters($filterList)
    {
        if (!count($filterList)) {
            return;
        }
        $template = $this->getTemplate();
        
        /* @var $filter Form_Field */
        foreach ($filterList as $filter) {
            $repeat = $template->getRepeat('filter');
            $filter->show($filter->getTemplate());
            $repeat->insertTemplate('filter', $filter->getTemplate());
            $repeat->appendRepeat();
        }
        $template->setChoice('filters');
        $template->setChoice('filtersubmit');
    }
    
    
    /**
     * Render the table headers
     *
     * @param array $cellList
     */
    function showTh($cellList)
    {
        $template = $this->getTemplate();
        
        /* @var $cell Table_Cell */
        foreach ($cellList as $cell) {
            $repeat = $template->getRepeat('th');
            $class = '';
            if ($cell->getOrder() == Table_Cell::ORDER_ASC) {
                $class .= 'orderAsc ';
            } else if ($cell->getOrder() == Table_Cell::ORDER_DESC) {
                $class .= 'orderDesc ';
            }
            if ($cell->isKey()) {
                $class .= 'key ';
            }
            $class = trim($repeat->getAttr('th', 'class') . $class);
            $repeat->setAttr('th', 'class', $class);
            
            $data = $cell->getTh();
            if ($data === null) {
                $data = '&#160;';
            }
            if ($data instanceof Dom_Template) {
                $repeat->insertTemplate('th', $data);
            } else {
                $repeat->insertHTML('th', $data);
            }
            $repeat->appendRepeat();
        }
    }
    
    /**
     * Render the table data rows
     *
     * @param array $list
     */
    function showTd($list)
    {
        $template = $this->getTemplate();
        
        $idx = 0;
        /* @var $obj Tk_Object */
        foreach ($list as $obj) {
            $repeatRow = $template->getRepeat('tr');
            
            $rowClassArr = $this->insertRow($obj, $repeatRow);
            $rowClass = 'r_' . $idx . ' ' . $repeatRow->getAttr('tr', 'class') . ' ';
            if (count($rowClassArr) > 0) {
                $rowClass .= implode(' ', $rowClassArr);
            }
            $rowClass = trim($rowClass);
            if ($idx % 2) {
                $repeatRow->setAttr('tr', 'class', 'odd ' . $rowClass);
            } else {
                $repeatRow->setAttr('tr', 'class', 'even ' . $rowClass);
            }
            $idx++;
            $repeatRow->appendRepeat();
        }
    }
    
    /**
     * Insert an object's cells into a row
     *
     * @param Tk_Object $obj
     * @param Dom_Template $template The row repeat template
     * @return array
     */
    protected function insertRow($obj, $template)
    {
        $rowClassArr = array();
        /* @var $cell Table_Cell */
        foreach ($this->table->getCellList() as $i => $cell) {
            if ($i == 0) {
                $cell->clearRowClass();
            }
            $cell->clearClass();
            $repeat = $template->getRepeat('td');
            $data = $cell->getTd($obj);
            if ($data === null) {
                $data = '&#160;';
            }
            
            $class = '';
            $class .= 'm' . ucfirst($cell->getProperty()) . ' ';
            if (count($cell->getClassList())) {
                $class .= implode(' ', $cell->getClassList()) . ' ';
            }
            if ($cell->isKey()) {
                $class .= 'key ';
            }
            //$class .= $cell->getAlign() . ' ';
            $class = trim($repeat->getAttr('td', 'class') . ' ' . $class);
            $repeat->setAttr('td', 'class', $class);
            $repeat->setAttr('td', 'title', $cell->getLabel());
            $rowClassArr = array_merge($rowClassArr, $cell->getRowClassList());
            
            if ($data instanceof Dom_Template) {
                $repeat->insertTemplate('td', $data);
            } else {
                $repeat->insertHTML('td', $data);
            }
            $repeat->appendRepeat();
        }
        return $rowClassArr;
    }
    
    
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $formId = $this->table->getFormId();
        $tableId = $this->table->getTableId();
        
        $xmlStr = '<?xml version="1.0"?>' . <<<XML
<div class="Table" id="$tableId">
  <form id="$formId" method="post" action="#">
  
    <!-- Table Header -->
    <div class="TableEvents" choice="TableEvents">
      <div class="filters" choice="filters">
        <div class="filter" repeat="filter" var="filter"></div>
        <div class="filter" choice="filtersubmit">
          <label>&nbsp;</label>
          <!-- TODO: change these buttons to Form_ButtonEvent objects -->
          <input type="submit" name="search" id="fid-search" value="Search" var="search" />
          <input type="submit" name="clear" id="fid-search" value="Show All" var="clear" />
          
        </div>
      </div>
      <div class="actions" choice="actions">
        <span repeat="action" var="action"></span>
      </div>
    </div>
    
    
    <!-- Table Controls -->
    <div class="TableControls">
      <div class="control results">
        <div var="Table_Ui_Results">&#160;</div>
      </div>
      <div class="control pager">
        <div var="Table_Ui_Pager">&#160;</div>
      </div>
      <div class="control limit">
        <div var="Table_Ui_Limit">&#160;</div>
      </div>
    </div>
    
    <!-- Table -->
    <table border="0" cellpadding="0" cellspacing="0" class="tableData" var="tableData">
      <thead>
        <tr>
          <th var="th" repeat="th"></th>
        </tr>
      </thead>
      <tbody>
        <tr var="tr" repeat="tr">
          <td var="td" repeat="td"></td>
        </tr>
      </tbody>
    </table>
    
    <!-- Table Controls -->
    <div class="TableControls">
      <div class="control results">
        <div var="Table_Ui_Results">&#160;</div>
      </div>
      <div class="control pager">
        <div var="Table_Ui_Pager">&#160;</div>
      </div>
      <div class="control limit">
        <div var="Table_Ui_Limit">&#160;</div>
      </div>
    </div>
    
  </form>
</div>
XML;
        
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
    
}