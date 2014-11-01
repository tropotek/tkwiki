<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A pager component that pagenates table data.
 *
 *
 * @package Com
 * @deprecated Use Table_Ui_Pager
 */
class Com_Ui_Pager extends Com_Web_Component
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
     * @var integer
     */
    private $maxPages = 10;

    /**
     * @var Tk_Type_Url
     */
    private $pageUrl = null;

    private $ignoreEventKey = false;

    /**
     * Create the pagenator class
     *
     * @param integer $size The total number of records on all pages
     * @param integer $limit
     * @param integer $offset
     * @param Tk_Type_Url $pageUrl The url to use for the page links
     */
    function __construct($size = 0, $limit = 25, $offset = 0, $pageUrl = null)
    {
        $this->size = intval($size);
        $this->limit = intval($limit);
        $this->offset = intval($offset);
        if (!$pageUrl) {
            $this->pageUrl = Tk_Request::getInstance()->getRequestUri();
        }
        parent::__construct();
    }


    /**
     * Make a pager from a db tool object
     *
     * @param Tk_Db_Tool $tool
     * @param Tk_Type_Url $url
     * @return Com_Ui_Pager
     */
    static function createFromTool(Tk_Db_Tool $tool, $url = null)
    {
        return new self($tool->getTotalRows(), $tool->getLimit(), $tool->getOffset(), $url);
    }

    /**
     * This will ignore and not place the event key value (widgetId)
     *
     * @param $b
     */
    function setIgnoreEventKey($b = true)
    {
        $this->ignoreEventKey = $b;
        return $this;
    }

    /**
     * Make a pager from a db list
     *
     * @param Tk_Loader_Collection $list
     * @return Com_Ui_Pager
     * @deprecated Use ::createFromTool()
     */
    static function makeFromList($list)
    {
        if ($list->getDbTool()) {
            return new self($list->getDbTool()->getTotalRows(), $list->getDbTool()->getLimit(), $list->getDbTool()->getOffset());
        }
        return new self();
    }

    /**
     * Make a pager from a db tool object
     *
     * @param Tk_Db_Tool $tool
     * @param Tk_Type_Url $url
     * @return Com_Ui_Pager
     * @deprecated Use ::createFromTool()
     */
    static function makeFromDbTool(Tk_Db_Tool $tool, $url = null)
    {
        return self::createFromTool($tool, $url);
    }

    /**
     * Set this ID same as the parent component ID
     *
     * @param Com_Web_Component $component
     */
    function setParent(Com_Web_Component $component)
    {
        if (!$this->ignoreEventKey) {
            $this->id = $component->getId();
        }
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
<div class="_pagerWrapper" choice="Com_Ui_Pager">
  <ul class="Com_Ui_Pager clearfix">
    <li class="start" var="start"><a href="javascript:;" var="startUrl">Start</a></li>
    <li class="back" var="back"><a href="javascript:;" var="backUrl">&lt;&lt;</a></li>
    <li repeat="page" var="page"><a href="javascript:;" var="pageUrl"></a></li>
    <li class="next" var="next"><a href="javascript:;" var="nextUrl">&gt;&gt;</a></li>
    <li class="end" var="end"><a href="javascript:;" var="endUrl">End</a></li>
  </ul>
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

        $currentPage = 0;
        $numPages = 1;

        if ($this->limit > -1 && $this->limit < $this->size) {
            $numPages = 0;
            $currentPage = 0;
            if ($this->limit > 0) {
                $numPages = ceil($this->size / $this->limit);
                $currentPage = ceil($this->offset / $this->limit);
            }

            $startPage = 0;
            $endPage = $this->maxPages;
            $center = floor($this->maxPages / 2);
            if ($currentPage > $center) {
                $startPage = $currentPage - $center;
                $endPage = $startPage + $this->maxPages;
            }
            if ($startPage > $numPages - $this->maxPages) {
                $startPage = $numPages - $this->maxPages;
                $endPage = $numPages;
            }

            if ($startPage < 0) {
                $startPage = 0;
            }
            if ($endPage >= $numPages) {
                $endPage = $numPages;
            }

            if ($numPages > 0) {
                $template->setChoice('Com_Ui_Pager');
                if ($this->getParent()) {
                    $this->getParent()->getTemplate()->setChoice('_Com_Ui_Pager');
                }
            }

            $pageUrl = $this->pageUrl;
            $pageUrl->delete($this->getEventKey('offset'));

            for($i = $startPage; $i < $endPage; $i++) {
                $repeat = $template->getRepeat('page');
                $repeat->insertText('pageUrl', $i + 1);
                $repeat->setAttr('pageUrl', 'title', 'Page ' . ($i + 1));
                $pageUrl->set($this->getEventKey('offset'), $i * $this->limit);
                $repeat->setAttr('pageUrl', 'href', $pageUrl->toString());
                if ($i == $currentPage) {
                    $repeat->setAttr('page', 'class', 'selected');
                    $repeat->setAttr('pageUrl', 'title', 'Current Page ' . ($i + 1));
                }
                $repeat->appendRepeat();
            }

            if ($this->offset >= $this->limit) {
                $pageUrl->set($this->getEventKey('offset'), $this->offset - $this->limit);
                $template->setAttr('backUrl', 'href', $pageUrl->toString());
                $template->setAttr('backUrl', 'title', 'Previous Page');
                $pageUrl->set($this->getEventKey('offset'), 0);
                $template->setAttr('startUrl', 'href', $pageUrl->toString());
                $template->setAttr('startUrl', 'title', 'Start Page');
            } else {
                $template->setAttr('start', 'class', 'start off');
                $template->setAttr('back', 'class', 'back off');
            }

            if ($this->offset < ($this->size - $this->limit)) {
                $pageUrl->set($this->getEventKey('offset'), $this->offset + $this->limit);
                $template->setAttr('nextUrl', 'href', $pageUrl->toString());
                $template->setAttr('nextUrl', 'title', 'Next Page');
                $pageUrl->set($this->getEventKey('offset'), ($numPages - 1) * $this->limit);
                $template->setAttr('endUrl', 'href', $pageUrl->toString());
                $template->setAttr('endUrl', 'title', 'Last Page');
            } else {
                $template->setAttr('end', 'class', 'end off');
                $template->setAttr('next', 'class', 'next off');
            }
        }
    }


    function getEventKey($event)
    {
        if ($this->ignoreEventKey) {
            return $event;
        }
        return parent::getEventKey($event);
    }


    /**
     * Set the maximum number of page values to display
     * Default: 10 page numbers
     *
     * @param integer $i
     */
    function setMaxPages($i)
    {
        $this->maxPages = $i;
        return $this;
    }

    /**
     * Set the new page Url, all pager urls will be createde from this url
     *
     * @param Tk_Type_Url $url
     */
    function setPageUrl(Tk_Type_Url $url)
    {
        $this->pageUrl = $url;
        return $this;
    }
}