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
class Wik_Modules_Settings_Orphaned extends Wik_Web_Component
{

    private $list = null;

    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent('delete', 'doDelete');

        $this->setAccessGroup(Wik_Db_User::GROUP_USER);
    }

    /**
     * The default init method
     *
     */
    function init()
    {
        // Create Table structure
        $table = Table::create();
        $table->addCell(Table_Cell_Checkbox::create());
        $table->addCell(Table_Cell_Integer::create('id'));
        $table->addCell(Table_Cell_String::create('title'))->setKey()->setUrl(Tk_Type_Url::create('/index.html'));
        $table->addCell(Ext_Table_Cell_User::create('user'));
        $table->addCell(Ext_Table_Cell_Perm::create('permissions'));
        $table->addCell(Table_Cell_Date::create('created'));

        // Add any actions
        $table->addAction(Table_Action_Delete::create());

        // Add any Filters
        $table->addFilter(Form_Field_Text::create('keywords'));

        // Init the Table
        $table->init();

        // Get data from DB
        $filter = $table->getFilterValues();
        if ($this->getUser()->getGroupId() == Wik_Db_User::GROUP_ADMIN && Tk_Request::requestUri()->getBasename() == 'orphaned.html') {
            $this->list = Wik_Db_PageLoader::findOrphanedPages($table->getDbTool());
        } else {
            $this->list = Wik_Db_PageLoader::findByUserId($this->getUser()->getId(), $table->getDbTool());
        }
        //$list = Wik_Db_UserLoader::findFiltered($filter, $table->getDbTool());

        // Create the table renderer and insert it
        $this->insertRenderer($table->getRenderer($this->list), 'Table');



    }

    /**
     * The default show method.
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        $title = $this->getPage()->getTemplate()->getTitleText();
        $this->getPage()->getTemplate()->setTitleText($title . ' - Orphaned Pages');

        if ($this->getUser()->getGroupId() != Wik_Db_User::GROUP_ADMIN || Tk_Request::requestUri()->getBasename() != 'orphaned.html') {
            $template->insertText('title', 'My Pages');
        }
    }

}
class Ext_Table_Cell_Perm extends Table_Cell
{

    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        $str = $obj->getPermisssionsString();
        if ($str)
            return $str;
        return $value;
    }

}
class Ext_Table_Cell_User extends Table_Cell
{

    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        $user = Wik_Db_UserLoader::find($obj->getUserId());
        if ($user)
            return $user->getUsername();
        return $value;
    }

}

