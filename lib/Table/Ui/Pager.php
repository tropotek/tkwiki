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
 * @package Table
 */
class Table_Ui_Pager extends Com_Web_Renderer
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
     * @var integer
     */
    private $maxPages = 10;

    /**
     * @var Tk_Type_Url
     */
    private $pageUrl = null;




    /**
     * Create the pagenator class
     *
     * @param integer $total The total number of records on all pages
     * @param integer $limit
     * @param integer $offset
     */
    function __construct($total = 0, $limit = 25, $offset = 0)
    {
        $this->total = intval($total);
        $this->limit = intval($limit);
        $this->offset = intval($offset);
        $this->pageUrl = Tk_Request::requestUri();
    }

    /**
     * Make a pager from a db tool object
     *
     * @param Tk_Db_Tool $tool
     * @return Table_Ui_Pager
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
     * @return Table_Ui_Pager
     */
    static function createFromList($list)
    {
        if ($list->getDbTool()) {
            return self::createFromTool($list->getDbTool());
        }
        return new self();
    }

    /**
     * show
     *
     */
    function show()
    {
        $currentPage = 0;
        $numPages = 1;
        $template = $this->getTemplate();

        if ($this->limit > -1 && $this->limit < $this->total) {
            $numPages = 0;
            $currentPage = 0;
            if ($this->limit > 0) {
                $numPages = ceil($this->total / $this->limit);
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
                $template->setChoice('Table_Ui_Pager');
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

            if ($this->offset < ($this->total - $this->limit)) {
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

    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<ul class="Table_Ui_Pager" choice="Table_Ui_Pager">
  <li class="start" var="start"><a href="javascript:;" var="startUrl" rel="nofollow">Start</a></li>
  <li class="back" var="back"><a href="javascript:;" var="backUrl">&lt;&lt;</a></li>
  <li repeat="page" var="page"><a href="javascript:;" var="pageUrl" rel="nofollow"></a></li>
  <li class="next" var="next"><a href="javascript:;" var="nextUrl">&gt;&gt;</a></li>
  <li class="end" var="end"><a href="javascript:;" var="endUrl" rel="nofollow">End</a></li>
</ul>';
        return Com_Web_Template::load($xmlStr);
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