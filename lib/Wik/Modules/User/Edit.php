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
class Wik_Modules_User_Edit extends Wik_Web_Component
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

        $this->object = new Wik_Db_User();
        if ($this->getRequest()->getParameter('userId') != null) {
            $this->object = Wik_Db_UserLoader::find($this->getRequest()->getParameter('userId'));
        }

        $this->setAccessGroup(Wik_Db_User::GROUP_ADMIN);
    }

    /**
     * The default init method
     *
     */
    function init()
    {

        $form = Form::create('Staff', $this->object);
        $form->addDefaultEvents(Tk_Type_Url::create('/userManager.html'));
        $form->addEvent(Ext_Form_Event_Save::create());

        $list = array(array('-- Select --','0'), array('User','1'), array('Admin','128'));
        $form->addField(Form_Field_Select::create('groupId', $list));
        $form->addField(Form_Field_Text::create('username'))->setLabel('Email');
        $form->addField(Form_Field_Text::create('name'));
        $form->addField(Form_Field_Password::create('newPass'));
        $form->addField(Form_Field_Password::create('confPass'));
        $form->addField(Form_Field_File::create('image'))->setNotes('Recommended Image Size: 400x300.')->addEvent(Form_Event_File::create(400, 300));
        $form->addField(Form_Field_Checkbox::create('active'));

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

        if ($this->getForm()->getFieldValue('newPass') || !$user->getPassword()) {
            if (!preg_match('/^[a-zA-Z0-9]{4,32}$/', $this->getForm()->getFieldValue('newPass'))) {
                $this->getForm()->addFieldError('newPass', 'Invalid Password Format "a-zA-Z0-9" ');
            }
            if ($this->getForm()->getFieldValue('newPass') != $this->getForm()->getFieldValue('confPass')) {
                $this->getForm()->addFieldError('newPass', 'Please check both password fields are the same.');
            }

            if ($this->getForm()->hasErrors()) {
                return;
            }

            $user->setPassword(Auth::hashPassword($this->getForm()->getFieldValue('newPass')));

        }
    }


}
