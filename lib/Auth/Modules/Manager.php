<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Manager:
 * Use the following markup to call this module in the template:
 *   <div var="Lst_Modules_Content_Manager" com-class="Lst_Modules_Content_Manager"></div>
 *
 *
 * @package Modules
 */
class Auth_Modules_Manager extends Adm_ManagerComponent
{

    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->getCrumbs()->reset();
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
        $table->addCell(Table_Cell_String::create('username'))->setKey()->setUrl(Tk_Type_Url::create(Auth::getUserPath() . '/userEdit.html'));
        $table->addCell(Table_Cell_Group::create('groupId'))->setLabel('Group');
        $table->addCell(Table_Cell_Boolean::create('active'));
        $table->addCell(Table_Cell_Date::create('created'));


        // Add any actions
        $table->addAction(Table_Action_Delete::create());

        $table->addFilter(Form_Field_Text::create('keywords'));

        // Init the Table
        $table->init();

        // Get data from DB
        $filterValues = $table->getFilterValues();
        $list = Auth_Db_UserLoader::findFiltered($filterValues, $table->getDbTool());

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
        
        $this->getPage()->getTemplate()->insertText('_contentTitle', 'User Manager');

    }

}
class Table_Cell_Group extends Table_Cell
{
    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    function getPropertyData($property, $obj)
    {
        $value = parent::getPropertyData($property, $obj);
        if ($obj->getGroupId() == Auth_Event::GROUP_ADMIN) {
            return 'Administrator';
        }
        return $value;
    }
}