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
class Auth_Modules_Recover extends Com_Web_Component
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
        $form->addEvent(Form_Event_Recover::create());
        $form->addField(Form_Field_Text::create('username'));

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
	        if ($rec == 'f') {
	            $template->setChoice('fail');
	        }
        }
    }

}


class Form_Event_Recover extends Form_ButtonEvent
{

	private $pass = '';

    /**
     * Create an instance of this object
     *
     * @return Form_Event_Send
     */
    static function create()
    {
        $o = new self('recover');
        return $o;
    }


    function execute()
    {
    	/* @var $user Lst_Db_User */
    	$user = Auth::getEvent()->findByUsername($this->getForm()->getFieldValue('username'));

        if (!$user) {
            $this->getForm()->addFieldError('username', 'Invalid Account Username');
            return;
        }
    	if (!$user->getActive()) {
    		$this->getForm()->addFieldError('username', 'Account Not Active, Contact Site Administration.');
            // ToDo send user a new activation email...
    	}

        if ($this->getForm()->hasErrors()) {
            return false;
        }

        Auth::changePassword($user);

        Auth::getEvent()->getLoginUrl()->redirect();

//        $url = Tk_Request::requestUri()->delete('recover');
//        $this->setRedirect($url);
    }


}