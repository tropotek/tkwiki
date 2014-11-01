<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Manager:
 * To call this widget use the following widget markup in the template:
 *   <div var="Wik_Modules_User_Manager" com-class="Wik_Modules_User_Manager"></div>
 *
 *
 * @package Modules
 */
class Wik_Modules_User_Manager extends Wik_Web_Component
{

    /**
     * __construct
     *
     * Add any events here: $this->addEvent($this->getEventKey('delete'), 'doDelete');
     */
    function __construct()
    {
        parent::__construct();
        $this->setAccessGroup(Wik_Db_User::GROUP_ADMIN);
    }

    /**
     * initalisation
     *
     */
    function init()
    {

        // Create Table structure
        $table = Table::create();
        $table->addCell(Table_Cell_Checkbox::create());
        $table->addCell(Table_Cell_Integer::create('id'));
        $table->addCell(Table_Cell_String::create('username'))->setKey()->setUrl(Tk_Type_Url::create('/userEdit.html'));
        $table->addCell(Table_Cell_String::create('name'));
        $table->addCell(Table_Cell_Email::create('email'));
        $table->addCell(Table_Cell_Boolean::create('active'));
        $table->addCell(Wik_Table_Cell_Group::create('group'));
        $table->addCell(Table_Cell_Date::create('created'));


        // Add any actions
        $table->addAction(Table_Action_Delete::create());
        $table->addAction(Table_Action_Url::create('Add User', Tk_Type_Url::create('/userEdit.html'), 'i16-userAdd'));

        // Add any Filters
        $table->addFilter(Form_Field_Text::create('keywords'));

        // Init the Table
        $table->init();

        // Get data from DB
        $filter = $table->getFilterValues();
        $list = Wik_Db_UserLoader::findFiltered($filter, $table->getDbTool());

        // Create the table renderer and insert it
        $this->insertRenderer($table->getRenderer($list), 'Table');


    }

    /**
     * Render the module
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();


    }

}
class Wik_Table_Cell_Group extends Table_Cell
{

    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        if ($obj->getGroupId() == Wik_Db_User::GROUP_USER) {
            return 'User';
        } else if ($obj->getGroupId() == Wik_Db_User::GROUP_ADMIN) {
            return 'Admin';
        }
        return $value;
    }

}

