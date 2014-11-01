<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A Limit component allows the user to change the number of records per page.
 *
 *
 * @package Table
 */
class Table_Ui_Limit extends Com_Web_Renderer
{

    /**
     * @var integer
     */
    private $limit = 50;

    /**
     * @var integer[]
     */
    private $limits = array();



    /**
     * Create the object instance
     *
     * @param integer[] $limits
     */
    function __construct($limit = 50, $limits = array(10, 25, 50, 100, 0))
    {
        $this->limit = $limit;
        $this->limits = $limits;
    }

    /**
     * Make a pager from a db tool object
     *
     * @param Tk_Db_Tool $tool
     * @return Ui_Db_Limit
     */
    static function createFromTool(Tk_Db_Tool $tool, $limits = array(10, 25, 50, 100, 0))
    {
        $obj = new self($tool->getLimit(), $limits);
        $obj->id = $tool->getId();
        return $obj;
    }

    /**
     * Make a limit from a db list
     *
     * @param Tk_Loader_Collection $list
     * @return Table_Ui_Limit
     */
    static function createFromList($list, $limits = array(10, 25, 50, 100, 0))
    {
        if ($list->getDbTool()) {
            return self::createFromTool($list->getDbTool(), $limits);
        }
        return new self();
    }

    /**
     * Render the widget.
     *
     */
    function show()
    {
        if (count($this->limits) <= 0) {
            return;
        }
        $template = $this->getTemplate();
        $template->setChoice('Table_Ui_Limit');

        $pageUrl = Tk_Request::requestUri();
        foreach ($this->limits as $limit) {
            $repeat = $template->getRepeat('row');
            $pageUrl->set($this->getEventKey('limit'), $limit);
            $pageUrl->set($this->getEventKey('offset'), '0');
            $repeat->insertText('limit', $limit);
            if ($limit == '0') {
                $repeat->insertText('limit', 'All');
            $repeat->setAttr('limit', 'onclick', 'return confirm(\'WARNING: If there are many records this action could be slow.\')');
            }
            $repeat->setAttr('limit', 'href', $pageUrl->toString());
            if ($limit == $this->limit) {
                $repeat->setAttr('row', 'class', 'selected');
            }
            $repeat->appendRepeat();
        }
    }

    /**
     * makeTemplate
     *
     * @return Dom_Template
     */
    protected function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<ul class="Table_Ui_Limit" choice="Table_Ui_Limit">
  <li class="show">Page Limit:</li>
  <li repeat="row" var="row"><a href="javascript:;" var="limit" rel="nofollow">0</a></li>
</ul>';
        return Dom_Template::load($xmlStr);
    }



}