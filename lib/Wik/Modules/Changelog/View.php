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
class Wik_Modules_Changelog_View extends Wik_Web_Component
{

    /**
     * The default show method.
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();

        $sql = "SELECT * FROM `version` ORDER BY `id` DESC";
        $db = Tk_Db_Factory::getDb();
        $result = $db->query($sql);
        foreach ($result as $row) {
            $repeat = $template->getRepeat('verBox');

            $date = Tk_Type_Date::parseIso($row['created']);
            $repeat->insertText('created', $date->toString(Tk_Type_Date::F_LONG_DATETIME));

            $repeat->insertText('version', 'Ver ' . $row['version'] . ': ');
            $repeat->insertText('changelog', $row['changelog']);
            $repeat->appendRepeat();
        }

    }

}