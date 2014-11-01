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
class Wik_Modules_Menu_Nav extends Wik_Web_Component
{

    /**
     * The default show method.
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        $menu = Wik_Db_PageLoader::findByName('Menu');
        if ($menu) {
            $text = Wik_Db_TextLoader::find($menu->getCurrentTextId());
            if ($text) {
                $template->replaceHTML('text', Wik_Util_TextFormatter::create($text)->getDomDocument()->saveHTML());
            }
        }

        if ($this->isUser()) {
            $template->setChoice('user');
        }
    }

}