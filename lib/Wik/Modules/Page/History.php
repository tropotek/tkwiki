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
class Wik_Modules_Page_History extends Wik_Web_Component
{
    /**
     * @var Wik_Db_Page
     */
    private $page = null;

    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent('revert', 'doRevert');
    }

    /**
     * The default init method
     *
     */
    function init()
    {
        if (!Tk_Request::exists('pageName')) {
            Tk_Request::set('pageName', 'Home');
        }
        $this->page = Wik_Db_PageLoader::findByName(Tk_Request::get('pageName'));

        // Create a new page
        if ($this->page == null) {
            throw new Tk_ExceptionRuntime('No Revisions available.');
        }



        // Create Table structure
        $table = Table::create();
        //$table->addCell(Table_Cell_Checkbox::create());
        if ($this->isUser() && $this->page->getLock()->isEditable($this->getUser()->getId())) {
            $table->addCell(Ext_Table_Cell_Revert::create('revert'));
        }
        $table->addCell(Table_Cell_Integer::create('id'))->setLabel('RevisionId');
        $table->addCell(Ext_Table_Cell_User::create('userId'))->setKey()->setUrl(Tk_Type_Url::create('/index.html'));
        $table->addCell(Table_Cell_Date::create('created'));

        // Add any actions
        //$table->addAction(Table_Action_Delete::create());


        // Add any Filters
        //$table->addFilter(Form_Field_Text::create('keywords'));

        // Init the Table
        $table->init();

        // Get data from DB
        $filter = $table->getFilterValues();
        $list = Wik_Db_TextLoader::findByPageId($this->page->getId(), $table->getDbTool('`created` DESC'));

        // Create the table renderer and insert it
        $this->insertRenderer($table->getRenderer($list), 'Table');

    }

    /**
     * The default event handler.
     *
     */
    function doDefault()
    {

    }

    /**
     * The default event handler.
     *
     */
    function doRevert()
    {
        $text = Wik_Db_TextLoader::find(Tk_Request::get('revert'));
        if ($text == null) {
            throw new Tk_ExceptionNullPointer('Invalid `textId` please try another ID.');
        }
        $page = Wik_Db_PageLoader::find($text->getPageId());
        if ($page == null) {
            throw new Tk_ExceptionNullPointer('Invalid `pageId` please try another ID.');
        }

        if ($this->isUser()) {
            $page->setCurrentTextId($text->getId());
            $page->update();
        }

        $page->getPageUrl()->redirect();
    }

    /**
     * The default show method.
     *
     */
    function show()
    {
        $template = $this->getTemplate();



    }

}

class Ext_Table_Cell_Revert extends Table_Cell
{

    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }

    function getTd($obj)
    {
        return sprintf( '<a href="%s" title="Revert Back to this page" class="revertBtn" onclick="return confirm(\'Do you want to revert this page to revision %s?\');">Revert</a>', Tk_Request::requestUri()->set('revert', $obj->getId()), $obj->getId() );
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

