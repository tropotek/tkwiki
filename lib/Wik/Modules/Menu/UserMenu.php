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
class Wik_Modules_Menu_UserMenu extends Wik_Web_Component
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
        }
        if ($this->isUser()) {
            $template->setChoice('user');
        }
        if (!$this->getUser()) {
            if (Wik_Config::getUserRegistrationEnabled()) {
                $template->setChoice('register');
            }
            $template->setChoice('public');
        }

        $template->setChoice('show');
    }

}