<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Edit:
 * To call this module use the following component markup in the template:
 *   <div var="Wik_Modules_User_Edit" com-class="Wik_Modules_User_Edit"></div>
 *
 * @package Modules
 */
class Wik_Modules_User_EditDetails extends Wik_Web_Component
{

    /**
     * @var Wik_Db_User
     */
    private $object = null;

    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();

        $this->setAccessGroup(Wik_Db_User::GROUP_USER);
    }

    /**
     * The default init method
     *
     */
    function init()
    {
        $this->object = $this->getUser();

        $form = Form::create('Staff', $this->object);
        $form->addDefaultEvents(Tk_Type_Url::create('/index.html'));
        $form->addEvent(Ext_Form_Event_Save::create());

        $form->addField(Form_Field_Text::create('name'));
        $form->addField(Form_Field_Text::create('username'))->setLabel('Email');
        $form->addField(Form_Field_File::create('image'))->setNotes('Recommended Image Size: 400x300.')->addEvent(Form_Event_File::create(400, 300));

        $this->setForm($form);

    }


    /**
     * Render the component
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();


    }

}
class Ext_Form_Event_Save extends Form_Event
{


    /**
     * Create an instance of this object
     *
     * @return Form_Event_Send
     */
    static function create()
    {
        $o = new self();
        return $o;
    }

    function init()
    {
        $this->setTrigerList(array('add', 'save','update'));
    }


    function execute()
    {
    	/* @var $user Wik_Db_User */
    	$user = $this->getObject();

        $user->setEmail($user->getUsername());


    }


}