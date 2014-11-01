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
 * @package Web
 */
class Wik_Web_Component extends Com_Web_Component
{
    /**
     * @var integer
     */
    private $accessGroup = 0;
    
    
    /**
     * @var Wik_Db_Page
     */
    private $wikiPage = null;
    
    /**
     * @var Wik_Db_Text
     */
    private $wikiText = null;
    
    
    
    /**
     * The Component Event Engine Lies HERE!
     * Execute this component and its children
     * Only call this on the parent/page component, usualy in
     * a front controller
     *
     * @return boolean
     */
    function execute()
    {
        $user = $this->getUser();
        $gid = 0;
        if ($user) {
            $gid = $user->getGroupId();
        }
        if ($gid < $this->getAccessGroup()) {
            $this->setEnabled(false);
            return false;
        }
        return parent::execute();
    }
    
    /**
     * Does the logged in user have Admin permissions?
     *
     * @return boolean
     * @deprecated 2.0 Use Page permissions
     */
    function isAdmin()
    {
        $user = $this->getUser();
        if ($user && $user->getGroupId() >= Wik_Db_User::GROUP_ADMIN) {
            return true;
        }
        return false;
    }
    
    function getUser()
    {
        return Auth::getUser();
    }
    
    /**
     * Does the logged in user have User permissions?
     *
     * @return boolean
     * @deprecated 2.0 Use page permissions
     */
    function isUser()
    {
        $user = $this->getUser();
        if ($user && $user->getGroupId() >= Wik_Db_User::GROUP_USER) {
            return true;
        }
        return false;
    }
    
    
    /**
     * Set the allowed access group for the module.
     * If the users groupId is => the access group they can execute it.
     *
     * Use the user class constants:
     *  o Wik_Db_User::GROUP_PUBLIC
     *  o Wik_Db_User::GROUP_USER
     *  o Wik_Db_User::GROUP_ADMIN
     *
     * @param integer $i
     */
    function setAccessGroup($i)
    {
        $this->accessGroup = $i;
    }
    
    /**
     * get the allowed access group for the module.
     *
     * @return integer
     */
    function getAccessGroup()
    {
        return $this->accessGroup;
    }
    
    /**
     * Get the page from the request
     *
     * @return Wik_Db_Page
     */
    function getWikiPage()
    {
        if (!$this->wikiPage) {
            if ($this->getRequest()->exists('textId')) {
                $text = Wik_Db_TextLoader::find($this->getRequest()->getParameter('textId'));
                $this->wikiPage = Wik_Db_PageLoader::find($text->getPageId());
            } else if ($this->getRequest()->exists('pageName')) {
                $this->wikiPage = Wik_Db_PageLoader::findByName($this->getRequest()->getParameter('pageName'));
            } else if ($this->getRequest()->exists('pageId')) {
                $this->wikiPage = Wik_Db_PageLoader::find($this->getRequest()->getParameter('pageId'));
            }
//            else {
//                return Wik_Db_PageLoader::findByName('Home');
//            }
        }
        return $this->wikiPage;
    }
    
    
    /**
     * Get the current/requested page text
     *
     * @return Wik_Db_Text
     */
    function getWikiText()
    {
        if (!$this->wikiText) {
            if ($this->getRequest()->exists('textId')) {
                $this->wikiText = Wik_Db_TextLoader::find($this->getRequest()->getParameter('textId'));
            } else if ($this->getWikiPage()) {
                $this->wikiText = Wik_Db_TextLoader::find($this->getWikiPage()->getCurrentTextId());
            }
        }
        return $this->wikiText;
    }
    
    
    
}