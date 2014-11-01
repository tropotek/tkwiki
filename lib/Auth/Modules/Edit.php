<?php
/*      -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Edit:
 * Use the following markup to call this module in the template:
 *   <div var="Lst_Modules_Content_Edit" com-class="Lst_Modules_Content_Edit"></div>
 *
 * @package Modules
 */
class Auth_Modules_Edit extends Adm_EditComponent
{

    /**
     * @var Auth_Db_User
     */
    private $user = null;


    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();

        $this->user = new Auth_Db_User();
        $this->user->setGroupId(Auth_Event::GROUP_ADMIN);
        $this->user->setActive(true);
        if (Tk_Request::get('userId') > 0) {
            $this->user = Auth_Db_UserLoader::find(Tk_Request::get('userId'));
        }
    }

    /**
     * The default init method
     *
     */
    function init()
    {

        $form = Form::create('User', $this->user);
        $form->addDefaultEvents($this->getCrumbs()->get());
        $form->addEvent(Auth_Form_Event_Password::create());

        $form->addField(Form_Field_Text::create('username'))->setRequired(true);
        $form->addField(Form_Field_Checkbox::create('active'));
        $form->addField(Form_Field_Password::create('newPass'));
        $form->addField(Form_Field_Password::create('confPass'));

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


class Auth_Form_Event_Password extends Form_Event
{

    /**
     * Create a new Event object
     *
     * @return Auth_Form_Event_Password
     */
    static function create()
    {
        $obj = new self();
        return $obj;
    }

    function init()
    {
        $this->setTrigerList(array('add', 'save', 'update'));
    }

    function execute()
    {
        /* @var $object Auth_Db_User */
        $object = $this->getForm()->getObject();

        if ($this->getForm()->getFieldValue('newPass') || !$object->getPassword()) {
            if (!preg_match('/^[a-zA-Z0-9]{4,32}$/', $this->getForm()->getFieldValue('newPass'))) {
                $this->getForm()->addFieldError('newPass', 'Invalid Password Format "a-zA-Z0-9" ');
            }
            if ($this->getForm()->getFieldValue('newPass') != $this->getForm()->getFieldValue('confPass')) {
                $this->getForm()->addFieldError('newPass', 'Please check both password fields are the same.');
            }

            if ($this->getForm()->hasErrors()) {
                return;
            }

            $object->setPassword(Auth::hashPassword($this->getForm()->getFieldValue('newPass')));

        }
    }
}