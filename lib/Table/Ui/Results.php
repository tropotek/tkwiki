<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A component to render the results pager data...
 *
 *
 * @package Table
 */
class Table_Ui_Results extends Com_Web_Renderer
{

    /**
     * @var integer
     */
    private $total = 0;

    /**
     * @var integer
     */
    private $limit = 0;

    /**
     * @var integer
     */
    private $offset = 0;



    /**
     * Create the object instance
     *
     * @param integer $total
     * @param integer $limit
     * @param integer $offset
     */
    function __construct($total = 0, $limit = 25, $offset = 0)
    {
        $this->total = intval($total);
        $this->limit = intval($limit);
        $this->offset = intval($offset);
    }

    /**
     * Make a pager from a db tool object
     *
     * @param Tk_Db_Tool $tool
     * @return Table_Ui_Results
     */
    static function createFromTool(Tk_Db_Tool $tool)
    {
        $obj = new self($tool->getTotalRows(), $tool->getLimit(), $tool->getOffset());
        $obj->id = $tool->getId();
        return $obj;
    }

    /**
     * Make a results object from a db list
     *
     * @param Tk_Loader_Collection $list
     * @return Table_Ui_Results
     */
    static function createFromList($list)
    {
        if ($list->getDbTool()) {
            return self::createFromTool($list->getDbTool());
        }
        return new self();
    }

    /**
     * Render the widget.
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        $template->insertText('from', $this->offset + 1);
        $to = $this->offset + $this->limit;
        if ($to > $this->total) {
            $to = $this->total;
        }
        $template->insertText('to', $to);
        $template->insertText('total', $this->total);
    }


    /**
     * makeTemplate
     *
     * @return Dom_Template
     */
    protected function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<p class="Table_Ui_Results">
  <span var="from"></span> -
  <span var="to"></span> records of
  <span var="total"></span> total
</p>';
        return Dom_Template::load($xmlStr);
    }
}