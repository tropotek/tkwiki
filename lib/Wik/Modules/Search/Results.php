<?php

/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A base component object.
 *
 *
 * @package Modules
 */
class Wik_Modules_Search_Results extends Wik_Web_Component
{
    const keywords = 'search-keywords';

    /**
     * @var Tk_Loader_Collection
     */
    private $list = null;

    /**
     * The default init method
     *
     */
    function init()
    {

        $keywords = $this->getRequest()->getParameter(self::keywords);

        $tool = Tk_Db_Tool::createFromRequest($this->getId(), '`score` DESC', 15);
        if (!$this->getRequest()->exists($this->getEventKey('offset'))) {
            $tool->reset();
        }
        $this->list = Wik_Db_PageLoader::textSearch($keywords, 'WITH QUERY EXPANSION', $tool);
        if ($this->list->count() <= 0) {
            $this->list = Wik_Db_PageLoader::textSearch($keywords, 'IN BOOLEAN MODE', $tool);
        }

        $pager = Com_Ui_Pager::makeFromList($this->list);
        $this->addChild($pager);
    }

    /**
     * The default show method.
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        if ($this->list == null || $this->list->count() <= 0) {
            $template->setChoice('error');
            return;
        }
        $template->setChoice('noError');
        $template->insertText('keywords', '`' . $this->getRequest()->getParameter(self::keywords) . '`');
        /* @var $page Wik_Db_Page */
        foreach ($this->list as $page) {
            $repeat = $template->getRepeat('row');

            $url = $page->getPageUrl();

            $repeat->insertHtml('title', $page->getTitle());
            $repeat->setAttr('title', 'href', $url->toString());
            $repeat->insertText('url', substr($url->toString(), 0, 128));
            $repeat->setAttr('url', 'href', $url->toString());
            $repeat->setAttr('url', 'title', $url->toString());

            $repeat->insertText('modified', $page->getModified()->toString(Tk_Type_Date::F_LONG_DATETIME));

            $text = Wik_Db_TextLoader::find($page->getCurrentTextId());

            $repeat->replaceHTML('content', $this->clean($text->getText()));

            $repeat->insertText('size', Tk_Type_Path::bytes2String($page->getSize()));
            //$repeat->insertText('score', round((($page->getScore()/6)*100), 2) . '%');
            $repeat->insertText('score', round((($page->getScore())), 2));

            $repeat->appendRepeat();
        }
    }

    /**
     *
     * @param unknown_type $str
     * @return unknown
     */
    private function clean($str)
    {
        $str = strip_tags($str);
        $str = substr($str, 0, 250);
        $str = str_replace(array('&amp;', '&nbsp;'), array('', ''), $str);

        $arr = explode(' ', $this->getRequest()->getParameter(self::keywords));
        foreach ($arr as $key) {
            $pattern = "/(?![< ].*?)(" . preg_quote($key, '/') . ")(?![^<>]*?>)/si";
            $replacement = "<strong>\\1</strong>";
            $str = preg_replace($pattern, $replacement, $str);
        }
        return $str;
    }

}