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
 * @package Com
 * @deprecated Use Table_Ui_Limit
 */
class Com_Ui_Limit extends Com_Web_Component
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
    function __construct($limit = 50, $limits = array(10, 25, 50, 100))
    {
        $this->limit = $limit;
        $this->limits = $limits;
        parent::__construct();
    }

    /**
     * Make a limit from a db list
     *
     * @param Tk_Loader_Collection $list
     * @return Com_Ui_Limit
     */
    static function makeFromList($list, $limits = array(10, 25, 50, 100))
    {
        if ($list->getDbTool()) {
            return new self($list->getDbTool()->getLimit(), $limits);
        }
        return new self();
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
        $xmlStr = '<?xml version="1.0"?>
<div class="_limitWrapper">
  <ul class="Com_Ui_Limit clearfix" choice="Com_Ui_Limit">
    <li class="show">Page Limit:</li>
    <li repeat="row" var="row"><a href="javascript:;" var="limit" rel="nofollow">10</a></li>
  </ul>
</div>';
        $template = Com_Web_Template::load($xmlStr);
        return $template;
    }

    /**
     * init
     *
     */
    function init()
    {

    }

    /**
     * Render the widget.
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        if (count($this->limits) <= 0) {
            return;
        }
        $template->setChoice('Com_Ui_Limit');

        $pageUrl = Tk_Request::getInstance()->getRequestUri();
        $pageUrl->delete($this->getEventKey('limit'));
        foreach ($this->limits as $limit) {
            $repeat = $template->getRepeat('row');
            $pageUrl->set($this->getEventKey('limit'), $limit);
            $pageUrl->set($this->getEventKey('offset'), 0);
            $repeat->insertText('limit', $limit);
            $repeat->setAttr('limit', 'href', $pageUrl->toString());
            if ($limit == $this->limit) {
                $repeat->setAttr('row', 'class', 'selected');
            }
            $repeat->appendRepeat();
        }
    }

}
