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
class Auth_Modules_Password extends Adm_Component
{

    /**
     * @var Ext_Db_User
     */
    private $user = null;

    protected $local = false;


    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();

        if (Tk_Request::exists('userId')) {
            $this->user = Auth::getEvent()->findUser(Tk_Request::get('userId'));
        } else if (Auth::getUser()) {
            // Big Security issue here, consider revising or only use in a considered secure environment
            $this->user = Auth::getUser();
            $this->local = true;
        }

        if (!$this->user) {
        	throw new Tk_Exception('Invalid User Account');
        }
    }


    /**
     * The default init method
     *
     */
    function init()
    {
        // Create Form Object
        $form = Form::create('User', $this->user);
        $form->addEvent(Form_Event_Update::create());

        $form->addField(Form_Field_Hidden::create('username'))->setValue($this->user->getUsername());

        $form->addField(Form_Field_Password::create('oldPass'))->setRequired(true)->setAutocomplete(false)->setLabel('Current');
        $form->addField(Form_Field_Password::create('newPass'))->setRequired(true)->setAutocomplete(false)->setLabel('New');
        $form->addField(Form_Field_Password::create('confPass'))->setRequired(true)->setAutocomplete(false)->setLabel('Confirm');

        $this->setForm($form);
        if (Tk_Session::exists('pwmessage')) {
            $form->addMessage(Tk_Session::getOnce('pwmessage'));
        }
    }

    /**
     * Render the component
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        

        if ($this->local) {
            $template->setChoice('local');
        }

    }
}

class Form_Event_Update extends Form_ButtonEvent
{

    static function create()
    {
        $o = new self('update');
        return $o;
    }


    function redirect()
    {
        $url = Tk_Request::requestUri()->delete('update');
        $url->redirect();
    }

    function execute()
    {
    	/* @var $user Ext_Db_User */
    	$user = $this->getForm()->getObject();
    	$form = $this->getForm();

    	if (Auth::hashPassword($form->getFieldValue('oldPass')) != $user->getPassword()) {
    		$form->addFieldError('oldPass', 'Invalid Current Password');
    	}
    	if (!$form->getFieldValue('newPass') || !$form->getFieldValue('confPass')) {
    		$form->addFieldError('newPass', 'Please Enter A New Password');
    	}
        if (!preg_match('/^[a-z0-9_-]{4,16}$/i', $form->getFieldValue('newPass'))) {
            $form->addFieldError('newPass', 'Invalid Password, Alpha Numeric characters only');
        }
        if (!preg_match('/^[a-z0-9_-]{4,16}$/i', $form->getFieldValue('confPass'))) {
            $form->addFieldError('confPass', 'Invalid Password, Alpha Numeric characters only');
        }
        if ($form->getFieldValue('newPass') != $form->getFieldValue('confPass')) {
        	$form->addFieldError('newPass', 'Passwords Do Not Match');
        }

        if ($this->getForm()->hasErrors()) {
            return false;
        }
        Auth::changePassword($user, $form->getFieldValue('newPass'));

        Tk_Session::set('pwmessage', "Password successfully updated.");

        Tk_Request::requestUri()->redirect();
    }


}


