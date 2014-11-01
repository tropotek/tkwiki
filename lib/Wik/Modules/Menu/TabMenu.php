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
class Wik_Modules_Menu_TabMenu extends Wik_Web_Component
{
    /**
     * @var Wik_Db_Page
     */
    private $page = null;

    /**
     * The default init method
     *
     */
    function init()
    {
        $this->page = $this->getWikiPage();
    }

    /**
     * The default show method.
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        if ($this->isAdmin()) {
            $template->setChoice('admin');
            $this->getPage()->getTemplate()->setChoice('admin');
        }

        if ($this->isUser()) {
            $this->getPage()->getTemplate()->setChoice('logout');
            $template->setChoice('user');
            $this->getPage()->getTemplate()->setChoice('user');
        } else {
            $this->getPage()->getTemplate()->setChoice('login');
            $this->getPage()->getTemplate()->setChoice('public');
        }

        if (!$this->page) {
            return;
        }

        $template->setChoice('show');

        $url = new Tk_Type_Url('/edit.html');
        $url->set('pageName', $this->page->getName());
        $template->setAttr('editUrl', 'href', $url->toString());

        $url = new Tk_Type_Url('/page/' . $this->page->getName());
        $template->setAttr('viewUrl', 'href', $url->toString());

        $url = new Tk_Type_Url('/history.html');
        $url->set('pageName', $this->page->getName());
        $template->setAttr('historyUrl', 'href', $url->toString());
        if (!$this->getUser()) {
            return;
        }
        switch (Tk_Request::requestUri()->getBasename()) {
            case 'edit.html' :
                if (!$this->page->getLock()->isEditable($this->getUser()->getId())) {
                    $template->setAttr('edit', 'class', 'disabled');
                    $template->setAttr('editUrl', 'href', 'javascript:;');
                } else {
                    $template->setAttr('edit', 'class', 'selected');
                }

                break;
            case 'history.html' :
                $template->setAttr('history', 'class', 'selected');
                break;
            case 'settings.html' :
                $template->setAttr('settings', 'class', 'selected');
                break;
            case 'userManager.html' :
                $template->setAttr('userManager', 'class', 'selected');
                break;
            case 'orphaned.html' :
                $template->setAttr('orphaned', 'class', 'selected');
                break;
            default :
                $template->setAttr('view', 'class', 'selected');
        }

    }

}