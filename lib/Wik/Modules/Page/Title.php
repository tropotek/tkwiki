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
class Wik_Modules_Page_Title extends Wik_Web_Component
{

    function __construct()
    {
        parent::__construct();
        $this->addEvent('writable', 'doWritable');
    }

    /**
     * The default init method
     *
     */
    function init()
    {

    }

    function doWritable()
    {
        $page = Wik_Db_PageLoader::find(Tk_Request::get('writable'));
        if ($page && !$page->canWrite($this->getUser()) && $page->getUserId() == $this->getUser()->getId()) {
            $page->setPermissions($page->getPermissions() | '200');
            $page->update();
        }
        $url = Tk_Request::requestUri()->delete('writable');
        $url->redirect();
    }


    /**
     * The default show method.
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();


        if ($this->getWikiPage()) {
            $template->insertText('title', $this->getWikiPage()->getTitle());
            $title = $this->getPage()->getTemplate()->getTitleText();
            $this->getPage()->getTemplate()->setTitleText($title . ' - ' . $this->getWikiPage()->getTitle());
            $template->insertText('pageId', $this->getWikiPage()->getId());

            $users = Wik_Db_UserLoader::findContributers($this->getWikiPage()->getId());
            if ($users->count()) {
                $list = '';
                foreach ($users as $u) {
                    $list .= $u->getName() . ', ';
                }
                $list = substr($list, 0, -2);
                $template->insertText('contrib', $list);
                $template->setChoice('contrib');
            }

            if ($this->getWikiPage()->canWrite($this->getUser())) {
		if (basename(Tk_Request::requestUri()->getPath()) == 'edit.html') {
		    $template->setChoice('save');
		}  else {
                $url = new Tk_Type_Url('/edit.html');
                $url->set('pageName', $this->getWikiPage()->getName());
                $template->setAttr('editUrl', 'href', $url->toString());

                $url = new Tk_Type_Url('/page/' . $this->getWikiPage()->getName());
                $template->setAttr('viewUrl', 'href', $url->toString());

                $url = new Tk_Type_Url('/history.html');
                $url->set('pageName', $this->getWikiPage()->getName());
                $template->setAttr('historyUrl', 'href', $url->toString());

                $template->setChoice('edit');
                }
            }

            if ($this->getWikiPage()->canDelete($this->getUser())) {
                $url = new Tk_Type_Url('/edit.html');
                $url->set('delete', $this->getWikiPage()->getId());
                $template->setAttr('deleteUrl', 'href', $url->toString());
                $template->setChoice('delete');
            }

            if ($this->getUser() && !$this->getWikiPage()->canWrite($this->getUser()) && $this->getWikiPage()->getUserId() == $this->getUser()->getId() ) {
                $template->setAttr('writable', 'href', Tk_Request::requestUri()->set('writable', $this->getWikiPage()->getId()) );
                $template->setChoice('writable');
            }
            $list = Wik_Db_CommentLoader::findByPageId($this->getWikiPage()->getId());
            $template->insertText('commentCount', $list->count());

            if ($this->getUser() && !$this->getWikiPage()->getLock()->isEditable($this->getUser()->getId())) {
                $template->setChoice('locked');
            }

            $user = Wik_Db_UserLoader::find($this->getWikiPage()->getUserId());
            if ($user) {
                $template->insertText('userId', $user->getUsername());
            }
            if ($this->getWikiPage()->getGroupId() == Wik_Db_User::GROUP_ADMIN) {
                $template->insertText('groupId', 'Administrator');
            }
            if ($this->getWikiPage()->getGroupId() == Wik_Db_User::GROUP_USER) {
                $template->insertText('groupId', 'Users');
            }

            $template->insertText('permissions', $this->getWikiPage()->getPermissions());

            $template->setChoice('page');
        }

        if ($this->getWikiText()) {
            $template->insertText('textId', $this->getWikiText()->getId());
            if ($this->getWikiText()->getCreated()) {
                $template->insertText('modified', $this->getWikiText()->getCreated()->toString(Tk_Type_Date::F_LONG_DATETIME));
            }
            $template->setChoice('text');
        }

        if ($this->isUser()) {
            $template->setChoice('user');
        }


        $basename = Tk_Request::requestUri()->getBasename();
        if ($basename == 'history.html' || $basename == 'edit.html') {
            return;
        }

        if (!$this->getWikiPage()) {
        	return;
        }

        if (!$this->getWikiPage()->canWrite($this->getUser())) {
            return;
        }

        if ($this->getWikiPage()->getCurrentTextId() != $this->getWikiText()->getId() && $this->getWikiText()) {
            $template->setChoice('revision');

            $url = new Tk_Type_Url('/history.html');
            $url->set('pageName', $this->getWikiPage()->getName());
            $template->setAttr('backUrl', 'href', $url->toString());
            $template->insertText('textId', $this->getWikiText()->getId());

            if (
                $this->getUser() && $this->getWikiPage()->getLock()->isEditable($this->getUser()->getId()))
            {
                $template->setChoice('revert');
                $url = new Tk_Type_Url('/history.html');
                $url->set('revert', $this->getWikiText()->getId());
                $template->setAttr('revertUrl', 'href', $url->toString());
            }

        } else if ($this->getWikiPage() && $this->getWikiPage()->isOrphan()) {
            $template->setChoice('orphaned');

            $url = new Tk_Type_Url('/orphaned.html');
            $template->setAttr('backUrl', 'href', $url->toString());

            if ($this->getUser() && $this->getWikiPage()->getLock()->isEditable($this->getUser()->getId())) {
                $template->setChoice('delete');
                $url = new Tk_Type_Url('/orphaned.html');
                $url->set('delete', $this->getWikiPage()->getId());
                $template->setAttr('deleteUrl', 'href', $url->toString());
            }
        }


    }

}
