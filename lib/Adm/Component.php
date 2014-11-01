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
 * @package Adm
 */
class Adm_Component extends Com_Web_Component
{
    
    function __construct()
    {
        parent::__construct();
        $this->setSecure(true);
    }
    
    
    /**
     * execute
     *
     * @return boolean
     */
    function execute()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        $this->addCrumb();
        $ret = parent::execute();
        return $ret;
    }
    
    /**
     * Render
     *
     * @return boolean
     */
    function render()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        if ($this->getCrumbs()->get()) {
            $this->getTemplate()->setAttr('back', 'href', $this->getCrumbs()->get());
        }
        $ret = parent::render();
        return $ret;
    }
    
    /**
     * Post init
     *
     */
    function postInit()
    {
        if ($this->getPage()->getTemplate()->keyExists('var', Com_Util_CrumbStack::CRUMB_VAR)) {
            //$this->getCrumbStack()->init($this->getPage());
        } else if ($this->getPage()->getTemplate()->keyExists('var', '_breadcrumbs') && $this->getCrumbs()->get()) {
            $var = '_breadcrumbs';
            $template = $this->getPage()->getTemplate();
            if ($template->keyExists('var', $var)) {
                if ($this->getCrumbs()->count()) {
                    $template->setChoice($var);
                    $template->replaceHTML($var, $this->getCrumbs()->getListHtml());
                }
            }
        }
    }
    
    /**
     * Add a crumb to the stack, if no parameters are given then the 
     * Request URI is used by default.
     * NOTE: Override this in your module to change any breadcrumb behaviour
     * 
     * @param type $url
     * @param type $name
     */
    function addCrumb($url = null, $name = '')
    {
        
        if ($this->getParent()->getId() != $this->getPage()->getId()) {
            return;
        }
        if (!$url) {
            $this->getCrumbs()->add(Tk_Request::requestUri());
            return;
        }
        $this->getCrumbs()->add($url, $name);
    }
    
    /**
     * Get the breadcrumbs object
     *
     * @return Adm_Breadcrumbs
     */
    function getCrumbs($name = 'admin')
    {
        $c = Adm_Breadcrumbs::getInstance($name);
        if (!$c->count()) {
            $dir = '/admin';
            if (Tk::moduleExists('Auth')) {
                $dir = Auth::getUserPath();
            }
            $c->add(Tk_Type_Url::create($dir . '/index.html'));
        }
        return $c;
    }
    
    
}