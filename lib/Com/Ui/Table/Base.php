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
class Com_Ui_Table_Base extends Com_Web_Component
{
    
    const MSG_CLASS_ERROR = 'error';
    const MSG_CLASS_WARNING = 'warning';
    const MSG_CLASS_NOTICE = 'notice';
    
    /**
     * @var Tk_Loader_Collection
     */
    protected $list = null;
    
    /**
     * @var Com_Ui_Table_Cell[]
     */
    protected $cells = array();
    
    /**
     * @var Com_Ui_Table_Action[]
     */
    protected $actions = array();
    
    /**
     * @var Com_Ui_Table_ActionInterface[]
     */
    protected $filters = array();
    
    
    
    /**
     * Create the object instance
     *
     * @param array $list
     */
    function __construct($list)
    {
        $this->list = $list;
        parent::__construct();
    }
    
    /**
     * Execute this component
     * Execute any process code in the cells, actions or filters.
     * This can be used to execute events in these objects
     *
     * @return mixed
     */
    function execute()
    {
        parent::execute();
        foreach ($this->cells as $cell) {
            $cell->doProcess();
        }
        foreach ($this->actions as $action) {
            $action->doProcess();
        }
        foreach ($this->filters as $action) {
            $action->doProcess();
        }
    }
    
    /**
     * Return the class name of the items we are dealing with in the list.
     * Returns null if not defined
     *
     * @return string
     */
    function getListClass()
    {
        if ($this->list->count()) {
            $obj = $this->list->current();
            return get_class($obj);
        }
        return '';
    }
    
    /**
     * Set this ID same as the parent component ID
     *
     * @param Com_Web_Component $component
     */
    function setParent(Com_Web_Component $component)
    {
        $this->id = $component->getId();
        parent::setParent($component);
    }
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        
        if ($this->hasTemplate()) {
            return;
        }
        
        /* NOTE: The form here must use a GET request to avoid the
         * reload message of the browser when a POST request is performed
         *
         * ADDITIONAL: Changed to POST, all admin back buttons must not use browser/javascript back/history
         * This is so we can use the get string for other data
         */
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="Com_Ui_Table_Base">
  <form id="%s" method="post" var="formId">
    <div class="message" var="message" choice="message"></div>
    <div class="actions" choice="actions">
      <div class="actionRow">
        <div class="left" choice="action">
          <span var="actionCell" repeat="actionCell"></span>
          %s
        </div>
        <!--
        <div class="right" choice="filter">
          <a class="toggle" href="javascript:;" title="Action Link" onclick="$(\'#filterRow\').toggle();">Hide Filter</a>
        </div>
        -->
      </div>
      <div class="filterRow" id="filterRow" choice="filter">
        <div var="filterCell" repeat="filterCell"></div>
        %s
      </div>
      <div class="clear"></div>
    </div>
    
    <div class="pager">
      <table border="0" cellpadding="0" cellspacing="0"><tr>
        <td class="r">
          <div var="Com_Ui_Results" />
        </td>
        <td class="p">
          <div var="Com_Ui_Pager" />
        </td>
        <td class="l">
          <div var="Com_Ui_Limit" />
        </td>
      </tr></table>
    </div>
    
    <table border="0" cellpadding="0" cellspacing="0" class="manager" var="manager">
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
    
    <div class="pager">
      <table border="0" cellpadding="0" cellspacing="0"><tr>
        <td class="r">
          <div var="Com_Ui_Results" />
        </td>
        <td class="p">
          <div var="Com_Ui_Pager" />
        </td>
        <td class="l">
          <div var="Com_Ui_Limit" />
        </td>
      </tr></table>
    </div>
    
  </form>
  <div class="clear" />
</div>
', $this->getFormId(), $this->getActionHtml(), $this->getFilterHtml());
        
        $template = Com_Web_Template::load($xmlStr);
        
        // Setup form
        $form = new Com_Form_Object($this->getFormId());
        $this->setForm($form);
        
        /* @var $action Com_Ui_Table_Action */
        foreach ($this->actions as $action) {
            $action->setFormFields($form);
        }
        /* @var $action Com_Ui_Table_Action */
        foreach ($this->filters as $action) {
            $action->setFormFields($form);
        }
        
        return $template;
    }
    
    /**
     * Initalise the table template to gain access to the form controls
     *
     */
    function initForm()
    {
        $template = $this->getTemplate();
        $this->getForm()->loadFromRequest();
        return $this;
    }
    
    /**
     * Send a message to display above the table.
     *
     * @param string $message
     * @param string $class
     */
    function setMessage($message, $class = self::MSG_CLASS_NOTICE)
    {
        $this->getTemplate()->replaceHTML('message', $message);
        $this->getTemplate()->setAttr('message', 'class', $class);
        $this->getTemplate()->setChoice('message');
    }
    
    /**
     * Init the component
     *
     */
    function init()
    {
        if ($this->list instanceof Tk_Loader_Collection || $this->list instanceof Tk_Db_Array) {
            $cLimit = Com_Ui_Limit::makeFromList($this->list);
            $this->addChild($cLimit);
            
            $cPager = Com_Ui_Pager::makeFromList($this->list);
            $this->addChild($cPager);
            
            $cResults = Com_Ui_Results::makeFromList($this->list);
            $this->addChild($cResults);
        }
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $template
     */
    function show()
    {
        
        if (count($this->actions) > 0 || count($this->filters) > 0) {
            $template->setChoice('actions');
        }
        
        if (count($this->actions) > 0) {
            $template->setChoice('action');
        }
        if (count($this->filters) > 0) {
            $template->setChoice('filter');
        }
        
        if ($this->list == null) {
            return;
        }
        $template->setChoice('table');
        $template->setAttr('manager', 'id', '_table' . $this->getId());
        $this->showTh($template);
        $this->showTd($template, $this->list);
        
//        // Fix to avoid get url issues.
//        $domForm = $template->getForm($this->getFormId());
//        foreach (Tk_Request::getInstance()->getRequestUri()->getQueryFields() as $k => $v) {
//            $domForm->appendHiddenElement($k, $v);
//        }
        
    }
    
    /**
     * show the action icons
     *
     */
    function getActionHtml()
    {
        $html = '';
        /* @var $action Com_Ui_Table_Action */
        foreach ($this->actions as $action) {
            
            $html .= $action->getHtml() . "\n";
        }
        return $html;
    }
    
    /**
     * show the action icons
     *
     */
    function getFilterHtml()
    {
        $html = '';
        /* @var $action Com_Ui_Table_Action */
        foreach ($this->filters as $action) {
            $html .= $action->getHtml() . "\n";
        }
        return $html;
    }
    
    /**
     * Render the table headers
     *
     * @param Dom_Template $template
     */
    function showTh(Dom_Template $template)
    {
        /* @var $cell Com_Ui_Table_Cell */
        foreach ($this->cells as $cell) {
            $repeat = $template->getRepeat('th');
            $class = $repeat->getAttr('th', 'class');
            $repeat->insertHtml('th', $cell->getTableHeader());
            
            if ($cell->getOrderBy() == Com_Ui_Table_Cell::ORDER_ASC) {
                $class .= ' orderAsc';
            } else if ($cell->getOrderBy() == Com_Ui_Table_Cell::ORDER_DESC) {
                $class .= ' orderDesc';
            }
            if ($cell->isKey()) {
                $class .= ' key';
            }
            $repeat->setAttr('th', 'class', $class);
            $repeat->appendRepeat();
        }
    }
    
    /**
     * Render the table data rows
     *
     * @param Dom_Template $template
     * @param array $list
     */
    function showTd(Dom_Template $template, $list)
    {
        $idx = 0;
        /* @var $obj Tk_Object */
        foreach ($list as $obj) {
            $repeatRow = $template->getRepeat('tr');
            
            $rowClassArr = $this->insertRow($obj, $repeatRow);
            $rowClass = 'r_' . $idx . ' ' . $repeatRow->getAttr('tr', 'class') . ' ';
            if (count($rowClassArr) > 0) {
                $rowClass .= implode(' ', $rowClassArr);
            }
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
     * @param Dom_Template $template
     * @return array
     */
    protected function insertRow($obj, $template)
    {
        $rowClassArr = array();
        /* @var $cell Com_Ui_Table_Cell */
        foreach ($this->cells as $cell) {
            $repeat = $template->getRepeat('td');
            
            $class = $repeat->getAttr('td', 'class');
            
            $data = $cell->getTableData($obj);
            if ($data === null) {
                $data = '&nbsp;';
            }
            $repeat->replaceHTML('td', $data);
            
            if ($cell->getProperty()) {
                $class .= ' m' . ucfirst($cell->getProperty());
            }
            if ($cell->isKey()) {
                $class .= ' key';
            }
            $class .= ' ' . $cell->getAlign();
            $repeat->setAttr('td', 'class', $class);
            $repeat->setAttr('td', 'title', $cell->getName());
            $rowClassArr = array_merge($rowClassArr, $cell->getRowClassList());
            $repeat->appendRepeat();
        }
        return $rowClassArr;
    }
    
    /**
     * Add an action to this table
     *
     * @param Com_Ui_Table_ActionInterface $action
     */
    function addAction(Com_Ui_Table_ActionInterface $action)
    {
        $action->setTable($this);
        $this->actions[] = $action;
    }
    
    /**
     * Add an action to this table
     *
     * @param Com_Ui_Table_ActionInterface $action
     */
    function addFilter(Com_Ui_Table_ActionInterface $action)
    {
        $action->setTable($this);
        $this->filters[] = $action;
    }
    
    /**
     * Add a cell to this table
     *
     * @param Com_Ui_Table_Cell $cell
     */
    function addCell($cell)
    {
        $cell->setTable($this);
        $this->cells[] = $cell;
    }
    
    /**
     * Set the cells, init with the table
     *
     * @param Com_Ui_Table_Cell[] $array
     */
    function setCells($array)
    {
        foreach ($array as $cell) {
            $cell->setTable($this);
        }
        $this->cells = $array;
    }
    
    /**
     * Get a cell from the array by its property name
     *
     * @param string $property
     */
    function getCell($property)
    {
        if (array_key_exists($property, $this->cells)) {
            return $this->cells[$property];
        }
    }
    
    /**
     * Get the list of items given to this table
     *
     * @return Tk_Loader_Collection
     */
    function getList()
    {
        return $this->list;
    }
    
    /**
     * Get the form id after rendering
     * Useful for calling javascript function that do operations on this form.
     *
     * @return string
     */
    function getFormId()
    {
        return 'Table_' . $this->getId();
    }
    
    
}