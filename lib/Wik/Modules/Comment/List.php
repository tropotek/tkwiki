<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * View:
 * To call this module use the following component markup in the template:
 *   <div var="Wik_Modules_Comment_List" com-class="Wik_Modules_Comment_List"></div>
 *
 * @package Modules
 */
class Wik_Modules_Comment_List extends Wik_Web_Component
{

    /**
     * @var Tk_Loader_Collection
     */
    private $list = null;


    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        if (!$this->getWikiPage()) {
            $this->disable();
            return;
        }
        $this->addEvent('d', 'doDelete');
        $this->addEvent('comment_rss', 'doRss');

    }

    /**
     * init
     *
     */
    function init()
    {
        $tool = Tk_Db_Tool::createFromRequest($this->getId(), '`created` DESC', 20);

        $this->list = Wik_Db_CommentLoader::findByPageId($this->getWikiPage()->getId(), $tool);

        if ($this->list) {
            $this->addChild(Com_Ui_Pager::makeFromList($this->list));
            $this->addChild(Com_Ui_Limit::makeFromList($this->list));
        }
    }

    /**
     * Rss Feed
     *
     */
    function doRss()
    {
        if (!$this->getWikiPage()) {
            echo "<error>Invalid WIKI Page.</error>";
            exit;
        }
        $rssRender = new Com_Xml_RssRenderer($this->list, $this->getWikiPage()->getTitle(), new Tk_Type_Url('/page/' . $this->getWikiPage()->getName()));
        $rssRender->show();
    }


    function doDelete()
    {
        $comment = Wik_Db_CommentLoader::find(Tk_Request::get('d'));
        if ($comment) {
            $comment->setDeleted(true);
            $comment->save();
        }
        Tk_Request::requestUri()->delete('d')->redirect();
    }


    /**
     * Render
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();

        if ($this->list == null || $this->list->count() == 0) {
            $template->setChoice('error');
            return;
        }
        $template->setChoice('noError');

        $rssUrl = Tk_Request::requestUri()->reset();
        $rssUrl->set('comment_rss', 'rss');
        $template->setAttr('rssUrl', 'href', $rssUrl);

        if ($this->isAdmin()) {
            $template->setChoice('admin');
        }

        /* @var $obj Wik_Db_Comment */
        foreach ($this->list as $obj) {
            $repeat = $template->getRepeat('row');

            $repeat->insertText('titleUrl', $obj->getName());
            $repeat->setAttr('titleUrl', 'title', 'Author: ' . $obj->getName());

            if ($this->isAdmin()) {
                $repeat->setChoice('admin');

                $repeat->insertText('ip', ' - [IP: ' . $obj->getIp() . ']');
                $repeat->insertText('email', $obj->getEmail());

                $delUrl = Tk_Request::requestUri()->set('d', $obj->getId());
                $repeat->setAttr('delUrl', 'href', $delUrl);
                $repeat->setAttr('delUrl', 'onclick', "return confirm('Are you sure you want to delete this comment?');");
            }

            if ($obj->getWeb()) {
                $repeat->setAttr('titleUrl', 'target', '_blank');
                $repeat->setAttr('titleUrl', 'href', $obj->getWeb());
            }

            $repeat->insertHtml('content', strip_tags($obj->getComment(), '<pre>,<code>,<b>,<i>,<strong>,<em>,<strike>,<a>,<p>,<br>,<div>,<span>'));
            $repeat->setAttr('anchor', 'name', 'com_' . $obj->getId());
            $repeat->insertText('created', $obj->getCreated()->toString(Tk_Type_Date::F_LONG_DATETIME));

            $repeat->appendRepeat();
        }
    }





}