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
class Wik_Modules_Crumb_List extends Wik_Web_Component
{
    /**
     * @var Wik_Util_CrumbList
     */
    private $crumbs = null;

    /**
     * The default init method
     *
     */
    function init()
    {
        $this->crumbs = Wik_Util_CrumbList::getInstance(10);
        $name = Tk_Request::requestUri()->getBasename();
        $pos = strrpos($name, '.');
        if ($pos !== false) {
            $name = substr($name, 0, $pos);
        }
        if (strtolower($name) == 'index') {
            $name = 'Home';
        }
        $name = ucwords(str_replace('_', ' ', $name));

        if (Tk_Request::getInstance()->getRequestUri()->getBasename() != 'edit.html') {
            $this->crumbs->addUrl($name, Tk_Request::requestUri());
        }

        $comp = $this->addChild($this->crumbs);
    }

    /**
     * The default show method.
     * @param Dom_Template
     */
    function show()
    {
        $template = $this->getTemplate();


    }

}