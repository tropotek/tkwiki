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
 * @package Com
 * @deprecated Use Table_Ui_Results
 */
class Com_Ui_Results extends Com_Web_Component
{
    /**
     * @var integer
     */
    private $size = 0;

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
     * @param integer $size
     * @param integer $limit
     * @param integer $offset
     */
    function __construct($size = 0, $limit = 25, $offset = 0)
    {
        $this->size = intval($size);
        $this->limit = intval($limit);
        $this->offset = intval($offset);
        parent::__construct();
    }


    /**
     * Make a pager from a db tool object
     *
     * @param Tk_Db_Tool $tool
     * @param Tk_Type_Url $url
     * @return Ext_Ui_Results
     */
    static function createFromTool(Tk_Db_Tool $tool)
    {
        return new self($tool->getTotalRows(), $tool->getLimit(), $tool->getOffset());
    }


    /**
     * Make a results object from a db list
     *
     * @param Tk_Loader_Collection $list
     * @return Com_Ui_Results
     */
    static function makeFromList($list)
    {
        if ($list->getDbTool()) {
            return new self($list->getDbTool()->getTotalRows(), $list->getDbTool()->getLimit(), $list->getDbTool()->getOffset());
        }
        return new self();
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<div class="_resultsWrapper">
  <div class="Com_Ui_Results clearfix">
    <span var="from"></span> -
    <span var="to"></span> records of
    <span var="total"></span> total
  </div>
</div>';
        $template = Com_Web_Template::load($xmlStr);
        return $template;
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
        if ($to > $this->size) {
            $to = $this->size;
        }
        $template->insertText('to', $to);
        $template->insertText('total', $this->size);
    }
}