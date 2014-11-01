<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Edit:
 * To call this module use the following component markup in the template:
 *   <div var="Lst_Modules_User_Edit" com-class="Lst_Modules_User_Edit"></div>
 *
 * @package Modules
 */
class Wik_Auth_Register extends Com_Web_Component
{


    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();


    }

    /**
     * The default init method
     *
     */
    function init()
    {


        $form = Form::create('User');
        $form->addEvent(Form_Event_Register::create());

        $form->addField(Form_Field_Text::create('username'))->setLabel('Email');
        $form->addField(Form_Field_Password::create('password'));
        $form->addField(Form_Field_Captcha::create('valid'));

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

        if (Tk_Session::exists('rec-sent')) {
        	$rec = Tk_Session::getOnce('rec-sent');
	        if ($rec == 't') {
	            $template->setChoice('success');
	        }
        }
    }

}


class Form_Event_Register extends Form_ButtonEvent
{


    /**
     * Create an instance of this object
     *
     * @return Form_Event_Send
     */
    static function create()
    {
        $o = new self('register');
        return $o;
    }


    function execute()
    {
    	/* @var $user Wik_Db_User */
    	//$user = Auth::getEvent()->findByUsername($this->getForm()->getFieldValue('username'));
        $user = Auth::createUser($this->getForm()->getFieldValue('username'), $this->getForm()->getFieldValue('password'), Wik_Auth_Event::GROUP_USER);
        $user->setActive(false);

        $this->getForm()->addFieldErrors($user->getValidator()->getErrors());
        if ($this->getForm()->hasErrors()) {
            return false;
        }

        $user->save();

        Tk_Session::set('rec-sent', 't');

        $url = Tk_Request::requestUri()->delete('register');
        $this->setRedirect($url);
    }


}