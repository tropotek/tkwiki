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
class Wik_Modules_Page_View extends Wik_Web_Component
{

    /**
     * Enter description here...
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent('rss', 'doRss');
    }

    /**
     * The default init method
     *
     */
    function init()
    {

    }

    /**
     * Do Default
     *
     */
    function doDefault()
    {
        if (!$this->getWikiPage() || !$this->getWikiText()) {
            $this->edit($this->getRequest()->getParameter('pageName'));
        }
    }

    /**
     * Rss Feed
     *
     */
    function doRss()
    {
        //vd($this->getWikiPage());
        // TODO: Use proper RSS Error doc
        if (!$this->getWikiPage()) {
            echo "<error>Invalid WIKI Page.</error>";
            exit;
        }
        $list = Wik_Db_TextLoader::findByPageId($this->getWikiPage()->getId(), new Tk_Db_Tool(10, 0, '`created` DESC'));
        $rssRender = new Com_Xml_RssRenderer($list, $this->getWikiPage()->getTitle(), new Tk_Type_Url('/page/' . $this->getWikiPage()->getName()));
        $rssRender->show();
    }


    /**
     * Redirect to the edit url if content empty
     *
     * @param string $pageName
     */
    function edit($pageName)
    {
        if (!$this->isUser()) {
            if ($pageName == 'Home') {
                $url = new Tk_Type_Url('/login.html');
                $url->redirect();
            }
        } else {
            if ($pageName) {
                $url = new Tk_Type_Url('/edit.html');
                $url->set('pageName', $pageName);
                $url->redirect();
            }
        }
    }


    /**
     * The default show method.
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();


        $template->appendJsUrl(Tk_Type_Url::create('/lib/Wik/js/google-code-prettify/prettify.js'));
        $template->appendCssUrl(Tk_Type_Url::create('/lib/Wik/js/google-code-prettify/prettify.css'));
        //$template->appendCssUrl(Tk_Type_Url::create('/lib/Wik/js/google-code-prettify/desert.css'));
        $js = <<<JS
$(document).ready(function(){
  prettyPrint();
});
JS;
        $template->appendJs($js);


        if (!$this->getWikiPage()) {
        	return;
        }
        if (!$this->getWikiPage()->canRead($this->getUser())) {
            $template->setChoice('noRead');
            return;
        }
        if (!$this->getWikiPage() || !$this->getWikiText()) {
            $template->setChoice('error');
            return;
        }
        $template->setChoice('noError');


        if ($this->getWikiPage()->getEnableComment()) {
            $template->setChoice('comments');
        }


        if ($this->getWikiText()) {
            $html = Wik_Util_TextFormatter::create($this->getWikiText())->getDomDocument()->saveHTML();
            //vd($html, $this->getWikiText());
            $template->insertHtml('text', $html);
        }

        $rssUrl = Tk_Request::requestUri()->reset()->set('rss', 'rss');
        $template->setAttr('rssUrl', 'href', $rssUrl);

        if ($this->getWikiPage()->getCss()) {
            $template->appendCss($this->getWikiPage()->getCss());
        }
        if ($this->getWikiPage()->getJavascript()) {
            $template->appendJs($this->getWikiPage()->getJavascript());
        }



    }




}